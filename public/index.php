<?php

// Устанавливаем кодировку UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: text/html; charset=UTF-8');

// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Обработка фатальных ошибок
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "<h1>Fatal Error</h1>";
        echo "<pre>" . print_r($error, true) . "</pre>";
    }
});

try {
    session_start();

    require __DIR__ . '/../app/config.php';
    require __DIR__ . '/../app/db.php';
    require __DIR__ . '/../app/helpers.php';

    $pdo = db();
} catch (Exception $e) {
    die("<h1>Ошибка инициализации</h1><pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>");
} catch (Error $e) {
    die("<h1>Fatal Error</h1><pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>");
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Убираем начальный слэш
$requestPath = ltrim($requestPath, '/');

// Роутинг
if ($requestPath === '' || $requestPath === '/') {
    // Главная страница
    $config = require __DIR__ . '/../app/config.php';
    
    // Получаем все категории
    $stmt = $pdo->query('SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name');
    $allCategories = $stmt->fetchAll();
    
    // Получаем популярные товары (случайные из разных категорий, до 12 штук)
    $stmt = $pdo->query('
        SELECT p.*, c.slug as category_slug, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.in_stock = 1
        ORDER BY (RANDOM())
        LIMIT 12
    ');
    $featuredProducts = $stmt->fetchAll();
    
    require __DIR__ . '/../app/views/layout.php';
    exit;
}

// Админка
if (strpos($requestPath, 'admin/') === 0) {
    $adminPath = substr($requestPath, 6); // убираем 'admin/'
    
    if ($adminPath === 'login') {
        require __DIR__ . '/../app/admin/login.php';
        exit;
    }
    
    if ($adminPath === 'logout') {
        require __DIR__ . '/../app/admin/logout.php';
        exit;
    }
    
    // Остальные админ-страницы требуют авторизации
    require_admin();
    
    if ($adminPath === '' || $adminPath === 'products') {
        require __DIR__ . '/../app/admin/products.php';
        exit;
    }
    
    if ($adminPath === 'product_edit') {
        require __DIR__ . '/../app/admin/product_edit.php';
        exit;
    }
    
    if ($adminPath === 'categories') {
        require __DIR__ . '/../app/admin/categories.php';
        exit;
    }
    
    if ($adminPath === 'category_edit') {
        require __DIR__ . '/../app/admin/category_edit.php';
        exit;
    }
    
    // 404 для админки
    require __DIR__ . '/../app/views/404.php';
    exit;
}

// Товар: /product/{slug}/
if (preg_match('#^product/([^/]+)/?$#', $requestPath, $matches)) {
    $slug = $matches[1];
    
    $stmt = $pdo->prepare('
        SELECT p.*, c.slug as category_slug, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ?
    ');
    $stmt->execute([$slug]);
    $product = $stmt->fetch();
    
    if (!$product) {
        require __DIR__ . '/../app/views/404.php';
        exit;
    }
    
    // Получаем все категории для хлебных крошек
    $stmt = $pdo->query('SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name');
    $categories = $stmt->fetchAll();
    
    require __DIR__ . '/../app/views/layout.php';
    exit;
}

// Категория: /{category_slug}/
if (preg_match('#^([^/]+)/?$#', $requestPath, $matches)) {
    $slug = $matches[1];
    
    // Проверяем что это категория (начинается с aisi-)
    if (strpos($slug, 'aisi-') !== 0) {
        require __DIR__ . '/../app/views/404.php';
        exit;
    }
    
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ? AND is_active = 1');
    $stmt->execute([$slug]);
    $category = $stmt->fetch();
    
    if (!$category) {
        require __DIR__ . '/../app/views/404.php';
        exit;
    }
    
    // Получаем все категории для чипсов
    $stmt = $pdo->query('SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name');
    $allCategories = $stmt->fetchAll();
    
    // Фильтры из GET
    $filters = [
        'thickness' => [],
        'condition' => [],
        'spring' => null,
        'surface' => [],
    ];
    
    // Парсим толщины
    if (!empty($_GET['th'])) {
        if (is_array($_GET['th'])) {
            $filters['thickness'] = array_map('floatval', $_GET['th']);
        } else {
            $filters['thickness'] = array_map('floatval', explode(',', $_GET['th']));
        }
    }
    
    // Парсим состояния
    if (!empty($_GET['cond'])) {
        if (is_array($_GET['cond'])) {
            $filters['condition'] = $_GET['cond'];
        } else {
            $filters['condition'] = explode(',', $_GET['cond']);
        }
    }
    
    // Пружинность
    if (isset($_GET['spring'])) {
        $filters['spring'] = (int)$_GET['spring'];
    }
    
    // Поверхность
    if (!empty($_GET['surf'])) {
        if (is_array($_GET['surf'])) {
            $filters['surface'] = $_GET['surf'];
        } else {
            $filters['surface'] = explode(',', $_GET['surf']);
        }
    }
    
    // Запрос товаров с фильтрами
    $sql = 'SELECT * FROM products WHERE category_id = ? AND in_stock = 1';
    $params = [$category['id']];
    
    if (!empty($filters['thickness'])) {
        $placeholders = implode(',', array_fill(0, count($filters['thickness']), '?'));
        $sql .= ' AND thickness IN (' . $placeholders . ')';
        $params = array_merge($params, $filters['thickness']);
    }
    
    if (!empty($filters['condition'])) {
        $placeholders = implode(',', array_fill(0, count($filters['condition']), '?'));
        $sql .= ' AND condition IN (' . $placeholders . ')';
        $params = array_merge($params, $filters['condition']);
    }
    
    if ($filters['spring'] !== null) {
        $sql .= ' AND spring = ?';
        $params[] = $filters['spring'];
    }
    
    if (!empty($filters['surface'])) {
        $placeholders = implode(',', array_fill(0, count($filters['surface']), '?'));
        $sql .= ' AND surface IN (' . $placeholders . ')';
        $params = array_merge($params, $filters['surface']);
    }
    
    // Сортировка по цене ASC
    $sql .= ' ORDER BY price_per_kg ASC';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Минимальная цена для "от X ₽/кг"
    $minPrice = null;
    if (!empty($products)) {
        $minPrice = min(array_column($products, 'price_per_kg'));
    }
    
    // Доступные значения для фильтров (из всех товаров категории)
    $stmt = $pdo->prepare('SELECT DISTINCT thickness, condition, spring, surface FROM products WHERE category_id = ?');
    $stmt->execute([$category['id']]);
    $availableFilters = $stmt->fetchAll();
    
    $availableThicknesses = array_unique(array_column($availableFilters, 'thickness'));
    sort($availableThicknesses);
    
    require __DIR__ . '/../app/views/layout.php';
    exit;
}

// 404
require __DIR__ . '/../app/views/404.php';
