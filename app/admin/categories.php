<?php
require_admin();

require __DIR__ . '/../db.php';
require __DIR__ . '/../helpers.php';

$pdo = db();

$stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Категории — Админка</title>
    <link rel="stylesheet" href="<?= asset_url('assets/styles.css') ?>">
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
                        <a href="<?= base_url('admin/restore_db') ?>">Восстановление базы</a>
                        <a href="<?= base_url('admin/logout') ?>">Выход</a>
                    </nav>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <div class="container">
                <div class="admin-card">
                    <div class="admin-card__header">
                        <h2>Категории</h2>
                        <a href="/admin/category_edit" class="btn btn--primary">Добавить категорию</a>
                    </div>

                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Slug</th>
                                <th>Активна</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?= $cat['id'] ?></td>
                                    <td><?= e($cat['name']) ?></td>
                                    <td><?= e($cat['slug']) ?></td>
                                    <td><?= $cat['is_active'] ? '✓' : '✗' ?></td>
                                    <td>
                                        <a href="/admin/category_edit?id=<?= $cat['id'] ?>" class="btn btn--small">Редактировать</a>
                                        <a href="<?= base_url($cat['slug'] . '/') ?>" target="_blank" class="btn btn--small btn--ghost">Открыть</a>
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
