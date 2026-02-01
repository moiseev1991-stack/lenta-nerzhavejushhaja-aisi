<?php
require_admin();

require __DIR__ . '/../helpers.php';

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = $_POST['home_text_html'] ?? '';
    $allowed = '<p><br><h2><h3><h4><ul><ol><li><a><strong><em><b><i><span>';
    $homeTextHtml = trim(strip_tags($raw, $allowed));
    set_site_setting('home_text_html', $homeTextHtml);
    $success = 'Текст сохранён.';
}

$homeTextHtml = get_site_setting('home_text_html') ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Текст на главной — Админка</title>
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
                        <h2>Текстовый блок на главной</h2>
                        <a href="<?= base_url() ?>" target="_blank" class="btn btn--ghost">Открыть главную</a>
                    </div>

                    <?php if ($success): ?>
                        <p class="admin-message admin-message--success"><?= e($success) ?></p>
                    <?php endif; ?>
                    <?php foreach ($errors as $err): ?>
                        <p class="admin-message admin-message--error"><?= e($err) ?></p>
                    <?php endforeach; ?>

                    <p class="admin-hint">Блок показывается между «Категории» и «Популярные товары». Можно использовать теги: <code>&lt;p&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;h3&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;ol&gt;</code>, <code>&lt;li&gt;</code>, <code>&lt;a&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;em&gt;</code>.</p>

                    <form method="post" class="admin-form">
                        <div class="form-row">
                            <label for="home_text_html">HTML-текст</label>
                            <textarea name="home_text_html" id="home_text_html" rows="14" class="form-control"><?= e($homeTextHtml) ?></textarea>
                        </div>
                        <div class="form-row form-row--actions">
                            <button type="submit" class="btn btn--primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
