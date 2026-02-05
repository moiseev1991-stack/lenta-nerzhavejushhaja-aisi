<?php
require_admin();

require __DIR__ . '/../db.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../helpers.php';

$pdo = db();
$config = require __DIR__ . '/../config.php';

$productId = $_GET['id'] ?? null;
$product = null;
$errors = [];
$fieldErrors = [];
$success = '';

$ALLOWED_CONDITION = ['', 'soft', 'hard', 'semi_hard'];
$ALLOWED_SURFACE = ['', 'BA', '2B', '4N'];
$THICKNESS_MAX = 10;
$WIDTH_MAX = 2000;

// Загрузка товара
if ($productId) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        redirect('/admin/products');
    }
}

// Категории
$stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
$categories = $stmt->fetchAll();

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    
    if ($action === 'delete' && $productId) {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        redirect('/admin/products');
    }
    
    // Валидация
    $categoryId = trim($_POST['category_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $slugInput = trim($_POST['slug'] ?? '');
    
    if (!$categoryId) {
        $fieldErrors['category_id'] = 'Выберите категорию';
        $errors[] = 'Выберите категорию';
    }
    if ($name === '') {
        $fieldErrors['name'] = 'Название обязательно';
        $errors[] = 'Название обязательно';
    }
    
    if ($name !== '' && $slugInput === '') {
        $slug = ensure_unique_slug($pdo, slugify($name), 'products', $productId ?: 0);
    } elseif ($slugInput !== '') {
        $slug = normalize_slug($slugInput);
        if ($slug === '') {
            $fieldErrors['slug'] = 'Введите корректный slug или оставьте пустым для автогенерации';
            $errors[] = $fieldErrors['slug'];
            $slug = '';
        } else {
            $slug = ensure_unique_slug($pdo, $slug, 'products', $productId ?: 0);
        }
    } else {
        $slug = '';
    }
    if ($slug === '' && empty($fieldErrors['name'])) {
        $fieldErrors['slug'] = 'Slug обязателен. Оставьте поле пустым — он будет сгенерирован из названия.';
        $errors[] = $fieldErrors['slug'];
    }
    
    $thicknessRaw = $_POST['thickness'] ?? '';
    $thickness = $thicknessRaw !== '' ? (float) str_replace(',', '.', $thicknessRaw) : null;
    if ($thickness !== null && ($thickness <= 0 || $thickness > 10)) {
        $fieldErrors['thickness'] = $thickness <= 0 ? 'Толщина должна быть больше 0' : 'Толщина не более 10 мм';
        $errors[] = $fieldErrors['thickness'];
    }
    $widthRaw = $_POST['width'] ?? '';
    $width = $widthRaw !== '' ? (float) str_replace(',', '.', $widthRaw) : null;
    if ($width !== null && $width < 0) {
        $fieldErrors['width'] = 'Ширина не может быть отрицательной';
        $errors[] = $fieldErrors['width'];
    }
    $priceRaw = $_POST['price_per_kg'] ?? '';
    $price_per_kg_val = $priceRaw !== '' ? (float) str_replace(',', '.', $priceRaw) : 0;
    if ($price_per_kg_val < 0) {
        $fieldErrors['price_per_kg'] = 'Цена не может быть отрицательной';
        $errors[] = $fieldErrors['price_per_kg'];
    }
    $condition = $_POST['condition'] ?? '';
    if (!in_array($condition, $ALLOWED_CONDITION, true)) {
        $fieldErrors['condition'] = 'Недопустимое значение состояния';
        $errors[] = $fieldErrors['condition'];
    }
    $surface = $_POST['surface'] ?? '';
    if (!in_array($surface, $ALLOWED_SURFACE, true)) {
        $fieldErrors['surface'] = 'Недопустимое значение поверхности';
        $errors[] = $fieldErrors['surface'];
    }
    
    if (empty($errors)) {
        $data = [
            'category_id' => $categoryId,
            'slug' => $slug,
            'name' => $name,
            'h1' => trim($_POST['h1'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'thickness' => $thickness,
            'width' => $width !== null && $width >= 0 ? $width : null,
            'condition' => in_array($condition, $ALLOWED_CONDITION, true) ? ($condition ?: null) : null,
            'spring' => isset($_POST['spring']) ? 1 : 0,
            'surface' => in_array($surface, $ALLOWED_SURFACE, true) ? ($surface ?: null) : null,
            'price_per_kg' => $price_per_kg_val,
            'in_stock' => isset($_POST['in_stock']) ? 1 : 0,
            'lead_time' => trim($_POST['lead_time'] ?? ''),
        ];
        
        // Загрузка изображения
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            
            // Проверка размера
            if ($file['size'] > $config['upload_max_size']) {
                $fieldErrors['image'] = 'Файл слишком большой (макс. 5MB)';
                $errors[] = $fieldErrors['image'];
            } else {
                // Проверка типа
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($mimeType, $config['upload_allowed_types']) || 
                    !in_array($ext, $config['upload_allowed_extensions'])) {
                    $fieldErrors['image'] = 'Недопустимый тип файла (JPG, PNG, WebP)';
                    $errors[] = $fieldErrors['image'];
                } else {
                    // Создаем директорию если нет
                    if (!is_dir($config['upload_dir'])) {
                        mkdir($config['upload_dir'], 0755, true);
                    }
                    
                    // Генерируем имя файла
                    $newId = $productId ?: time();
                    $filename = 'p_' . $newId . '_' . time() . '.' . $ext;
                    $filepath = $config['upload_dir'] . '/' . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $data['image'] = '/uploads/' . $filename;
                    } else {
                        $fieldErrors['image'] = 'Ошибка загрузки файла';
                        $errors[] = $fieldErrors['image'];
                    }
                }
            }
        } elseif ($product && !empty($product['image'])) {
            $data['image'] = $product['image'];
        } else {
            $data['image'] = null;
        }
        
        if (empty($errors)) {
            if ($productId) {
                $data['updated_at'] = nowIso();
                $sql = 'UPDATE products SET 
                        category_id = ?, slug = ?, name = ?, h1 = ?, title = ?, description = ?,
                        thickness = ?, width = ?, condition = ?, spring = ?, surface = ?,
                        price_per_kg = ?, in_stock = ?, lead_time = ?, image = ?, updated_at = ?
                        WHERE id = ?';
                $params = [
                    $data['category_id'], $data['slug'], $data['name'], $data['h1'], $data['title'], $data['description'],
                    $data['thickness'], $data['width'], $data['condition'], $data['spring'], $data['surface'],
                    $data['price_per_kg'], $data['in_stock'], $data['lead_time'], $data['image'], $data['updated_at'],
                    $productId
                ];
            } else {
                $data['created_at'] = nowIso();
                $data['updated_at'] = nowIso();
                $sql = 'INSERT INTO products 
                        (category_id, slug, name, h1, title, description,
                         thickness, width, condition, spring, surface,
                         price_per_kg, in_stock, lead_time, image, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                $params = [
                    $data['category_id'], $data['slug'], $data['name'], $data['h1'], $data['title'], $data['description'],
                    $data['thickness'], $data['width'], $data['condition'], $data['spring'], $data['surface'],
                    $data['price_per_kg'], $data['in_stock'], $data['lead_time'], $data['image'], $data['created_at'], $data['updated_at']
                ];
            }
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                if (!$productId) {
                    $productId = (int) $pdo->lastInsertId();
                }
                $success = 'Товар сохранён';
                $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
            } catch (PDOException $e) {
                if ((int) $e->getCode() === 23000 || strpos($e->getMessage(), 'UNIQUE') !== false) {
                    $fieldErrors['slug'] = 'Slug уже занят. Измените или оставьте пустым для автогенерации.';
                    $errors[] = $fieldErrors['slug'];
                } else {
                    $errors[] = 'Ошибка сохранения: ' . e($e->getMessage());
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $productId ? 'Редактировать' : 'Создать' ?> товар — Админка</title>
    <link rel="stylesheet" href="<?= base_url('assets/styles.css') ?>">
</head>
<body>
    <div class="admin-layout">
        <header class="admin-header">
            <div class="container">
                <div class="admin-header__inner">
                    <h1>Админка</h1>
                    <nav class="admin-nav">
                        <a href="<?= base_url('admin/products') ?>">Товары</a>
                        <a href="<?= base_url('admin/categories') ?>">Категории</a>
                        <a href="<?= base_url('admin/home_text') ?>">Текст на главной</a>
                        <a href="<?= base_url('admin/bonus_page') ?>">Страница: Получить бонус</a>
                        <a href="<?= base_url('admin/logout') ?>">Выход</a>
                    </nav>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <div class="container">
                <div class="admin-card">
                    <div class="admin-card__header">
                        <h2><?= $productId ? 'Редактировать товар' : 'Создать товар' ?></h2>
                        <a href="/admin/products" class="btn btn--ghost">Назад к списку</a>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert--error">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert--success"><?= e($success) ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="admin-form">
                        <div class="form-group <?= !empty($fieldErrors['category_id']) ? 'form-group--error' : '' ?>">
                            <label>Категория *</label>
                            <select name="category_id" required>
                                <option value="">Выберите категорию</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                            <?= ($product && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($fieldErrors['category_id'])): ?>
                                <span class="form-error"><?= e($fieldErrors['category_id']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= !empty($fieldErrors['name']) ? 'form-group--error' : '' ?>">
                            <label>Название *</label>
                            <input type="text" name="name" value="<?= e($product['name'] ?? $_POST['name'] ?? '') ?>" required>
                            <?php if (!empty($fieldErrors['name'])): ?>
                                <span class="form-error"><?= e($fieldErrors['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= !empty($fieldErrors['slug']) ? 'form-group--error' : '' ?>">
                            <label>Slug</label>
                            <input type="text" name="slug" value="<?= e($product['slug'] ?? $_POST['slug'] ?? '') ?>" placeholder="Оставьте пустым для автогенерации">
                            <small>Автогенерируется из названия, если пусто. Уникален среди товаров.</small>
                            <?php if (!empty($fieldErrors['slug'])): ?>
                                <span class="form-error"><?= e($fieldErrors['slug']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>H1</label>
                            <input type="text" name="h1" value="<?= e($product['h1'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Title (SEO)</label>
                            <input type="text" name="title" value="<?= e($product['title'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Description (SEO)</label>
                            <textarea name="description" rows="3"><?= e($product['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group <?= !empty($fieldErrors['thickness']) ? 'form-group--error' : '' ?>">
                                <label>Толщина (мм)</label>
                                <input type="number" name="thickness" step="0.01" min="0" max="10" value="<?= e($product['thickness'] ?? $_POST['thickness'] ?? '') ?>">
                                <?php if (!empty($fieldErrors['thickness'])): ?>
                                    <span class="form-error"><?= e($fieldErrors['thickness']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group <?= !empty($fieldErrors['width']) ? 'form-group--error' : '' ?>">
                                <label>Ширина (мм)</label>
                                <input type="number" name="width" step="0.01" min="0" value="<?= e($product['width'] ?? $_POST['width'] ?? '') ?>">
                                <?php if (!empty($fieldErrors['width'])): ?>
                                    <span class="form-error"><?= e($fieldErrors['width']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group <?= !empty($fieldErrors['condition']) ? 'form-group--error' : '' ?>">
                            <label>Состояние</label>
                            <select name="condition">
                                <option value="">—</option>
                                <option value="soft" <?= ($product && $product['condition'] === 'soft') ? 'selected' : '' ?>>Мягкая</option>
                                <option value="hard" <?= ($product && $product['condition'] === 'hard') ? 'selected' : '' ?>>Нагартованная</option>
                                <option value="semi_hard" <?= ($product && $product['condition'] === 'semi_hard') ? 'selected' : '' ?>>Полугартованная</option>
                            </select>
                            <?php if (!empty($fieldErrors['condition'])): ?>
                                <span class="form-error"><?= e($fieldErrors['condition']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="spring" value="1" 
                                       <?= ($product && $product['spring']) ? 'checked' : '' ?>>
                                Пружинные свойства
                            </label>
                        </div>

                        <div class="form-group <?= !empty($fieldErrors['surface']) ? 'form-group--error' : '' ?>">
                            <label>Поверхность</label>
                            <select name="surface">
                                <option value="">—</option>
                                <option value="BA" <?= ($product && $product['surface'] === 'BA') ? 'selected' : '' ?>>BA</option>
                                <option value="2B" <?= ($product && $product['surface'] === '2B') ? 'selected' : '' ?>>2B</option>
                                <option value="4N" <?= ($product && $product['surface'] === '4N') ? 'selected' : '' ?>>4N</option>
                            </select>
                            <?php if (!empty($fieldErrors['surface'])): ?>
                                <span class="form-error"><?= e($fieldErrors['surface']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= !empty($fieldErrors['price_per_kg']) ? 'form-group--error' : '' ?>">
                            <label>Цена за кг (₽) *</label>
                            <input type="number" name="price_per_kg" step="0.01" min="0" 
                                   value="<?= e($product['price_per_kg'] ?? $_POST['price_per_kg'] ?? '') ?>" required>
                            <?php if (!empty($fieldErrors['price_per_kg'])): ?>
                                <span class="form-error"><?= e($fieldErrors['price_per_kg']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="in_stock" value="1" 
                                       <?= ($product && $product['in_stock']) ? 'checked' : '' ?>>
                                В наличии
                            </label>
                        </div>

                        <div class="form-group">
                            <label>Срок поставки</label>
                            <input type="text" name="lead_time" value="<?= e($product['lead_time'] ?? '') ?>" 
                                   placeholder="например: 7-14 дней">
                        </div>

                        <div class="form-group">
                            <label>Изображение</label>
                            <?php if ($product && $product['image']): ?>
                                <div class="image-preview">
                                    <img src="<?= base_url($product['image']) ?>" alt="Превью">
                                    <p>Текущее: <?= e($product['image']) ?></p>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                            <small>JPG, PNG, WebP, макс. 5MB</small>
                            <?php if (!empty($fieldErrors['image'])): ?>
                                <span class="form-error"><?= e($fieldErrors['image']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="action" value="save" class="btn btn--primary">Сохранить</button>
                            <?php if ($productId): ?>
                                <button type="submit" name="action" value="delete" class="btn btn--danger" 
                                        onclick="return confirm('Удалить товар?')">Удалить</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
