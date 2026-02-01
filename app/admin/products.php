<?php
require_admin();

require __DIR__ . '/../db.php';
require __DIR__ . '/../helpers.php';

$pdo = db();

// Фильтр по категории
$categoryFilter = $_GET['category_id'] ?? null;
$sql = 'SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id';
$params = [];

if ($categoryFilter) {
    $sql .= ' WHERE p.category_id = ?';
    $params[] = $categoryFilter;
}

$sql .= ' ORDER BY p.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Категории для фильтра
$stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Товары — Админка</title>
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
                        <a href="<?= base_url('admin/logout') ?>">Выход</a>
                    </nav>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <div class="container">
                <div class="admin-card">
                    <div class="admin-card__header">
                        <h2>Товары</h2>
                        <a href="/admin/product_edit" class="btn btn--primary">Добавить товар</a>
                    </div>

                    <form method="GET" class="admin-filter">
                        <select name="category_id" onchange="this.form.submit()">
                            <option value="">Все категории</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Slug</th>
                                <th>Категория</th>
                                <th>Цена</th>
                                <th>Наличие</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= $product['id'] ?></td>
                                    <td><?= e($product['name']) ?></td>
                                    <td><?= e($product['slug']) ?></td>
                                    <td><?= e($product['category_name']) ?></td>
                                    <td><?= format_price($product['price_per_kg']) ?></td>
                                    <td><?= $product['in_stock'] ? '✓' : '✗' ?></td>
                                    <td>
                                        <a href="/admin/product_edit?id=<?= $product['id'] ?>" class="btn btn--small">Редактировать</a>
                                        <a href="<?= base_url('product/' . $product['slug'] . '/') ?>" target="_blank" class="btn btn--small btn--ghost">Открыть</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
