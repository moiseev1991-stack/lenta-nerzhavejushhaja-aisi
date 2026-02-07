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

// Убираем начальный и конечный слэш
$requestPath = trim($requestPath, '/');

// Логотип из папки img/ в корне проекта (не из public/img/)
if ($requestPath === 'img/logo_aisi_lenta_full.png') {
    $logoFile = __DIR__ . '/../img/logo_aisi_lenta_full.png';
    if (file_exists($logoFile) && is_file($logoFile)) {
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400');
        readfile($logoFile);
        exit;
    }
}

// Картинки секции «Товарные группы» на /bonus/ (img/bonus_groups/ в корне проекта)
if (preg_match('#^img/bonus_groups/([a-zA-Z0-9_\-]+\.(png|webp))$#', $requestPath, $m)) {
    $filename = basename($m[1]);
    $imgFile = __DIR__ . '/../img/bonus_groups/' . $filename;
    if (is_file($imgFile)) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($ext === 'webp' ? 'image/webp' : 'image/png'));
        header('Cache-Control: public, max-age=86400');
        readfile($imgFile);
        exit;
    }
}

// Загруженные в админке картинки товаров (public/uploads/)
if (preg_match('#^uploads/([a-zA-Z0-9_\-\.]+)$#', $requestPath, $m)) {
    $filename = $m[1];
    if (preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $filename)) {
        $uploadFile = __DIR__ . '/uploads/' . $filename;
        if (file_exists($uploadFile) && is_file($uploadFile)) {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
            header('Content-Type: ' . ($types[$ext] ?? 'image/jpeg'));
            header('Cache-Control: public, max-age=86400');
            readfile($uploadFile);
            exit;
        }
    }
}

// Картинки товаров из img/product_images_named (корень проекта). Имена могут содержать пробелы, «—», кириллицу.
if (preg_match('#^img/product_images_named/(.+)$#', $requestPath, $m)) {
    $filename = basename(rawurldecode($m[1]));
    if ($filename !== '' && strpos($filename, '..') === false && preg_match('/\.(jpg|jpeg|png)$/i', $filename)) {
        $imgFile = __DIR__ . '/../img/product_images_named/' . $filename;
        if (file_exists($imgFile) && is_file($imgFile)) {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            header('Content-Type: ' . ($ext === 'png' ? 'image/png' : 'image/jpeg'));
            header('Cache-Control: public, max-age=86400');
            readfile($imgFile);
            exit;
        }
    }
}

// Роутинг
if ($requestPath === '' || $requestPath === '/') {
    // Главная страница
    $config = require __DIR__ . '/../app/config.php';
    
    // Получаем все категории (сортируем по возрастанию марки AISI)
    $stmt = $pdo->query('SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name');
    $allCategories = $stmt->fetchAll();
    sort_aisi_categories($allCategories);
    
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
    $imagesDir = __DIR__ . '/../img/product_images_named';
    foreach ($featuredProducts as &$fp) {
        resolve_product_image($fp, $imagesDir);
    }
    unset($fp);
    
    $homeTextHtml = get_site_setting('home_text_html') ?? '';
    $homeTitle = get_site_setting('home_title') ?? '';
    $homeH1 = get_site_setting('home_h1') ?? '';
    $homeDescription = get_site_setting('home_description') ?? '';
    
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
    
    if ($adminPath === 'home_text') {
        require __DIR__ . '/../app/admin/home_text.php';
        exit;
    }

    if ($adminPath === 'bonus_page') {
        require __DIR__ . '/../app/admin/bonus_page.php';
        exit;
    }

    if ($adminPath === 'restore_db') {
        require __DIR__ . '/../app/admin/restore_db.php';
        exit;
    }
    
    // 404 для админки
    http_response_code(404);
    $is404 = true;
    $pageTitle = '404 — Страница не найдена';
    require __DIR__ . '/../app/views/layout.php';
    exit;
}

// Технические SEO файлы
if ($requestPath === 'robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    require __DIR__ . '/../app/views/robots.txt.php';
    exit;
}

if ($requestPath === 'sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    require __DIR__ . '/../app/views/sitemap.xml.php';
    exit;
}

// Страница «Получить бонус» из таблицы pages
if ($requestPath === 'bonus' || $requestPath === 'bonus/') {
    // На старых БД может не быть таблицы pages — создаём при первом обращении
    $pdo->exec('CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT UNIQUE NOT NULL,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        updated_at TEXT
    )');

    $stmt = $pdo->prepare('SELECT title, content FROM pages WHERE slug = ? LIMIT 1');
    $stmt->execute(['bonus']);
    $bonusPage = $stmt->fetch();
    if (!$bonusPage) {
        $defaultContent = '<h2>Описание программы лояльности «Железная ставка»</h2>'
            . '<p>С каждой покупки металлопроката на сайте <strong>lenta-nerzhavejushhaja-aisi.ru</strong> вы получаете бонусы — <strong>1%</strong> от суммы заказа.<br>Для акционных товаров из категории <strong>«металлопрокат 2-го сорта»</strong> общий накопительный бонус может достигать <strong>10%</strong>.</p>'
            . '<p>Накопленные бонусы можно использовать, чтобы уменьшить стоимость следующих покупок на сайте <strong>http://lenta-nerzhavejushhaja-aisi.ru/</strong>.<br>Для первого использования необходимо накопить <strong>10&nbsp;000 бонусов</strong> (<strong>1 бонус = 1 рубль</strong>).</p>'
            . '<p>Если товар возвращается, бонусы, начисленные за эту покупку, <strong>не возвращаются</strong>.<br>Бонусы <strong>нельзя</strong> передавать другим пользователям, <strong>нельзя</strong> суммировать между аккаунтами и <strong>нельзя</strong> дарить.</p>'
            . '<p><strong>Уважаемые партнёры!</strong><br>Мы запускаем программу лояльности «Железная ставка». Начисление бонусов действует <strong>с декабря 2022 года и в течение всего 2023 года</strong> — за покупки материалов, участвующих в акции.</p>'
            . '<p>Программа также распространяется на <strong>чёрный, цветной и нержавеющий металлопрокат</strong>.</p>';

        $bonusPage = [
            'title' => 'Получить бонус',
            'content' => $defaultContent,
        ];
    }
    $pageTitle = $bonusPage['title'] . ' — ' . ($config['site_name'] ?? 'Лист нержавейки AISI');
    $pageDescription = 'Программа лояльности «Железная ставка»: бонусы до 10% за покупки металлопроката, условия и возможности использования.';
    $pageH1 = $bonusPage['title'];
    $isBonusPage = true;
    require __DIR__ . '/../app/views/layout.php';
    exit;
}

// Сервисные страницы (проверяем до категорий и товаров)
$servicePagesData = require __DIR__ . '/../app/data/pages.php';

// Убираем слэш в конце для проверки
$servicePageKey = rtrim($requestPath, '/');

// Список известных сервисных страниц
$knownServicePages = ['contacts', 'delivery', 'payment', 'about', 'price', 'privacy-policy'];

if (in_array($servicePageKey, $knownServicePages) && isset($servicePagesData[$servicePageKey])) {
    $pageData = $servicePagesData[$servicePageKey];
    $pageTitle = $pageData['title'];
    $pageDescription = $pageData['description'];
    $pageH1 = $pageData['h1'];
    $pageContent = $pageData['content'] ?? '';
    $isServicePage = true;
    require __DIR__ . '/../app/views/layout.php';
    exit;
}

// Старый URL товара /product/{slug}/ — редирект 301 на /{grade_slug}/{slug}/
if (preg_match('#^product/([^/]+)/?$#', $requestPath, $matches)) {
    $stmt = $pdo->prepare('SELECT c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.slug = ?');
    $stmt->execute([$matches[1]]);
    $row = $stmt->fetch();
    if ($row) {
        header('Location: ' . base_url($row['category_slug'] . '/' . $matches[1] . '/'), true, 301);
        exit;
    }
    http_response_code(404);
    $is404 = true;
    $pageTitle = '404 — Страница не найдена';
    require __DIR__ . '/../app/views/layout.php';
    exit;
}

// Товар: /{grade_slug}/{product_slug}/ (например /aisi-904l/lenta-nerzhaveyuschaya-.../)
if (preg_match('#^(aisi-[^/]+)/([^/]+)/?$#', $requestPath, $matches)) {
    $gradeSlug = $matches[1];
    $productSlug = $matches[2];
    
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ? AND is_active = 1');
    $stmt->execute([$gradeSlug]);
    $category = $stmt->fetch();
    if (!$category) {
        http_response_code(404);
        $is404 = true;
        $pageTitle = '404 — Страница не найдена';
        unset($category, $product);
        require __DIR__ . '/../app/views/layout.php';
        exit;
    }
    
    $stmt = $pdo->prepare('
        SELECT p.*, c.slug as category_slug, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ? AND p.category_id = ?
    ');
    $stmt->execute([$productSlug, $category['id']]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Подстановка картинки по product_slug (часть URL без /aisi-XXX/): ищем {slug}.jpg
        resolve_product_image($product, __DIR__ . '/../img/product_images_named');
    }
    
    if (!$product) {
        // Товар с таким slug есть, но в другой категории — редирект на правильный URL
        $stmtAlt = $pdo->prepare('SELECT c.slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.slug = ?');
        $stmtAlt->execute([$productSlug]);
        $row = $stmtAlt->fetch();
        if ($row) {
            header('Location: ' . base_url($row['slug'] . '/' . $productSlug . '/'), true, 301);
            exit;
        }
        http_response_code(404);
        $is404 = true;
        $pageTitle = '404 — Страница не найдена';
        unset($category, $product);
        require __DIR__ . '/../app/views/layout.php';
        exit;
    }
    
    $minPrice = null;
    $stmt = $pdo->query('SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name');
    $categories = $stmt->fetchAll();
    
    $relatedProducts = get_related_products($pdo, $product, 4);
    $imagesDir = __DIR__ . '/../img/product_images_named';
    foreach ($relatedProducts['items'] as &$rp) {
        resolve_product_image($rp, $imagesDir);
    }
    unset($rp);
    
    require __DIR__ . '/../app/views/layout.php';
    exit;
}

// Категория: /{category_slug}/
// Проверяем только если путь не пустой и не содержит слэшей внутри
if ($requestPath && strpos($requestPath, '/') === false) {
    $slug = $requestPath;
    
    // Проверяем что это категория (начинается с aisi-)
    if (strpos($slug, 'aisi-') !== 0) {
        http_response_code(404);
        $is404 = true;
        $pageTitle = '404 — Страница не найдена';
        require __DIR__ . '/../app/views/layout.php';
        exit;
    }
    
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ? AND is_active = 1');
    $stmt->execute([$slug]);
    $category = $stmt->fetch();
    
    if (!$category) {
        http_response_code(404);
        $is404 = true;
        $pageTitle = '404 — Страница не найдена';
        require __DIR__ . '/../app/views/layout.php';
        exit;
    }
    
    // Получаем все категории для чипсов (сортируем по возрастанию марки AISI)
    $stmt = $pdo->query('SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name');
    $allCategories = $stmt->fetchAll();
    sort_aisi_categories($allCategories);
    
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
    
    // Сортировка по цене: price_asc (по умолчанию) или price_desc. Товары без цены (NULL/0) — в конце.
    $sortOrder = isset($_GET['sort']) && $_GET['sort'] === 'price_desc' ? 'price_desc' : 'price_asc';
    $sql .= ' ORDER BY (CASE WHEN (price_per_kg IS NULL OR price_per_kg = 0) THEN 1 ELSE 0 END), price_per_kg ' . ($sortOrder === 'price_desc' ? 'DESC' : 'ASC');
    
    $config = require __DIR__ . '/../app/config.php';
    $perPage = (int) ($config['catalog_per_page'] ?? 24);
    $perPage = $perPage < 1 ? 24 : $perPage;
    
    // Подсчёт всего по тем же условиям (без LIMIT)
    $countSql = 'SELECT COUNT(*) FROM products WHERE category_id = ? AND in_stock = 1';
    $countParams = [$category['id']];
    if (!empty($filters['thickness'])) {
        $placeholders = implode(',', array_fill(0, count($filters['thickness']), '?'));
        $countSql .= ' AND thickness IN (' . $placeholders . ')';
        $countParams = array_merge($countParams, $filters['thickness']);
    }
    if (!empty($filters['condition'])) {
        $placeholders = implode(',', array_fill(0, count($filters['condition']), '?'));
        $countSql .= ' AND condition IN (' . $placeholders . ')';
        $countParams = array_merge($countParams, $filters['condition']);
    }
    if ($filters['spring'] !== null) {
        $countSql .= ' AND spring = ?';
        $countParams[] = $filters['spring'];
    }
    if (!empty($filters['surface'])) {
        $placeholders = implode(',', array_fill(0, count($filters['surface']), '?'));
        $countSql .= ' AND surface IN (' . $placeholders . ')';
        $countParams = array_merge($countParams, $filters['surface']);
    }
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($countParams);
    $totalProducts = (int) $stmtCount->fetchColumn();
    $totalPages = $totalProducts > 0 ? (int) ceil($totalProducts / $perPage) : 1;
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    if ($page > $totalPages && $totalProducts > 0) {
        http_response_code(404);
        $is404 = true;
        $pageTitle = '404 — Страница не найдена';
        require __DIR__ . '/../app/views/layout.php';
        exit;
    }
    
    $sql .= ' LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Подстановка картинок по product_slug: для каждого товара без image ищем {slug}.jpg
    $imagesDir = __DIR__ . '/../img/product_images_named';
    foreach ($products as &$p) {
        resolve_product_image($p, $imagesDir);
    }
    unset($p);
    
    $categoryBaseUrl = base_url($category['slug'] . '/');
    $pagination = [
        'total' => $totalProducts,
        'per_page' => $perPage,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'base_url' => $categoryBaseUrl,
        'query_params' => $_GET,
    ];
    
    // URL для переключения сортировки (сохраняем текущие фильтры и страницу)
    $getAsc = $_GET; $getAsc['sort'] = 'price_asc'; $getAsc['page'] = 1;
    $getDesc = $_GET; $getDesc['sort'] = 'price_desc'; $getDesc['page'] = 1;
    $categorySortUrlAsc = $categoryBaseUrl . '?' . http_build_query(array_filter($getAsc, function ($v) { return $v !== '' && $v !== null; }));
    $categorySortUrlDesc = $categoryBaseUrl . '?' . http_build_query(array_filter($getDesc, function ($v) { return $v !== '' && $v !== null; }));
    
    // Минимальная цена для "от X ₽/кг" (по текущей странице)
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
http_response_code(404);
$is404 = true;
$pageTitle = '404 — Страница не найдена';
require __DIR__ . '/../app/views/layout.php';
exit;
