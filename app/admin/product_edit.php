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
$success = '';

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
    $categoryId = $_POST['category_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    
    if (!$categoryId) $errors[] = 'Выберите категорию';
    if (!$name) $errors[] = 'Название обязательно';
    
    if (!$slug && $name) {
        $slug = slugify($name);
    }
    
    if ($slug) {
        // Проверка уникальности slug
        $stmt = $pdo->prepare('SELECT id FROM products WHERE slug = ? AND id != ?');
        $stmt->execute([$slug, $productId ?: 0]);
        if ($stmt->fetch()) {
            $errors[] = 'Slug уже используется';
        }
    }
    
    if (empty($errors)) {
        $data = [
            'category_id' => $categoryId,
            'slug' => $slug,
            'name' => $name,
            'h1' => trim($_POST['h1'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'thickness' => $_POST['thickness'] ? (float)$_POST['thickness'] : null,
            'width' => $_POST['width'] ? (float)$_POST['width'] : null,
            'condition' => $_POST['condition'] ?? null,
            'spring' => isset($_POST['spring']) ? (int)$_POST['spring'] : 0,
            'surface' => $_POST['surface'] ?? null,
            'price_per_kg' => (float)($_POST['price_per_kg'] ?? 0),
            'in_stock' => isset($_POST['in_stock']) ? 1 : 0,
            'lead_time' => trim($_POST['lead_time'] ?? ''),
        ];
        
        // Загрузка изображения
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            
            // Проверка размера
            if ($file['size'] > $config['upload_max_size']) {
                $errors[] = 'Файл слишком большой (макс. 5MB)';
            } else {
                // Проверка типа
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($mimeType, $config['upload_allowed_types']) || 
                    !in_array($ext, $config['upload_allowed_extensions'])) {
                    $errors[] = 'Недопустимый тип файла';
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
                        $errors[] = 'Ошибка загрузки файла';
                    }
                }
            }
        } elseif ($product && $product['image']) {
            // Сохраняем старое изображение
            $data['image'] = $product['image'];
        }
        
        if (empty($errors)) {
            if ($productId) {
                // Обновление
                $data['updated_at'] = nowIso();
                $sql = 'UPDATE products SET 
                        category_id = ?, slug = ?, name = ?, h1 = ?, title = ?, description = ?,
                        thickness = ?, width = ?, condition = ?, spring = ?, surface = ?,
                        price_per_kg = ?, in_stock = ?, lead_time = ?, image = ?, updated_at = ?
                        WHERE id = ?';
                $params = array_merge(
                    array_values($data),
                    [$productId]
                );
            } else {
                // Создание
                $data['created_at'] = nowIso();
                $data['updated_at'] = nowIso();
                $sql = 'INSERT INTO products 
                        (category_id, slug, name, h1, title, description,
                         thickness, width, condition, spring, surface,
                         price_per_kg, in_stock, lead_time, image, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                $params = array_values($data);
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            if (!$productId) {
                $productId = $pdo->lastInsertId();
            }
            
            $success = 'Товар сохранен';
            // Перезагружаем товар
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
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
                        <a href="/admin/products">Товары</a>
                        <a href="/admin/categories">Категории</a>
                        <a href="/admin/logout">Выход</a>
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
                        <div class="form-group">
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
                        </div>

                        <div class="form-group">
                            <label>Название *</label>
                            <input type="text" name="name" value="<?= e($product['name'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Slug *</label>
                            <input type="text" name="slug" value="<?= e($product['slug'] ?? '') ?>" required>
                            <small>Автогенерируется из названия, если пусто</small>
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
                            <div class="form-group">
                                <label>Толщина (мм)</label>
                                <input type="number" name="thickness" step="0.01" value="<?= e($product['thickness'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label>Ширина (мм)</label>
                                <input type="number" name="width" step="0.01" value="<?= e($product['width'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Состояние</label>
                            <select name="condition">
                                <option value="">—</option>
                                <option value="soft" <?= ($product && $product['condition'] === 'soft') ? 'selected' : '' ?>>Мягкая</option>
                                <option value="hard" <?= ($product && $product['condition'] === 'hard') ? 'selected' : '' ?>>Нагартованная</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="spring" value="1" 
                                       <?= ($product && $product['spring']) ? 'checked' : '' ?>>
                                Пружинные свойства
                            </label>
                        </div>

                        <div class="form-group">
                            <label>Поверхность</label>
                            <select name="surface">
                                <option value="">—</option>
                                <option value="BA" <?= ($product && $product['surface'] === 'BA') ? 'selected' : '' ?>>BA</option>
                                <option value="2B" <?= ($product && $product['surface'] === '2B') ? 'selected' : '' ?>>2B</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Цена за кг (₽) *</label>
                            <input type="number" name="price_per_kg" step="0.01" 
                                   value="<?= e($product['price_per_kg'] ?? '') ?>" required>
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
