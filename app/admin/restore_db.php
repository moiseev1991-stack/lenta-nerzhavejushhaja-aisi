<?php
require_admin();

require __DIR__ . '/../config.php';
require __DIR__ . '/../helpers.php';

$config = require __DIR__ . '/../config.php';
$dbPath = $config['db_path'];
$storageDir = dirname($dbPath);

$message = '';
$isError = false;
$maxSize = 100 * 1024 * 1024; // 100 MB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_FILES['db_file']['tmp_name']) || !is_uploaded_file($_FILES['db_file']['tmp_name'])) {
        $message = 'Файл не загружен. Выберите файл database.sqlite.';
        $isError = true;
    } elseif ($_FILES['db_file']['size'] > $maxSize) {
        $message = 'Файл слишком большой (макс. 100 МБ).';
        $isError = true;
    } elseif ($_FILES['db_file']['size'] < 100) {
        $message = 'Файл слишком маленький, это не база SQLite.';
        $isError = true;
    } else {
        $tmp = $_FILES['db_file']['tmp_name'];
        $header = file_get_contents($tmp, false, null, 0, 16);
        if ($header !== "SQLite format 3\0") {
            $message = 'Файл не является базой SQLite (неверный формат).';
            $isError = true;
        } else {
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            if (file_exists($dbPath)) {
                $backup = $storageDir . '/database.sqlite.bak_' . date('Ymd_His');
                copy($dbPath, $backup);
            }
            if (move_uploaded_file($tmp, $dbPath)) {
                @chmod($dbPath, 0644);
                $message = 'База данных успешно заменена. Категории и товары должны отобразиться на сайте.';
            } else {
                $message = 'Не удалось сохранить файл. Проверьте права на папку storage/ на сервере.';
                $isError = true;
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
    <title>Восстановление базы — Админка</title>
    <link rel="stylesheet" href="<?= base_url('public/assets/styles.css') ?>">
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
                        <a href="<?= base_url('admin/restore_db') ?>" class="admin-nav__active">Восстановление базы</a>
                        <a href="<?= base_url('admin/logout') ?>">Выход</a>
                    </nav>
                </div>
            </div>
        </header>
        <main class="admin-main container">
            <h2>Восстановление базы данных</h2>
            <p>Загрузите файл <strong>database.sqlite</strong> с вашего компьютера (из папки <code>storage/</code> проекта). Текущая база на сервере будет сохранена с резервной копией.</p>
            <?php if ($message): ?>
                <div class="alert <?= $isError ? 'alert--error' : 'alert--success' ?>"><?= e($message) ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data" class="form">
                <div class="form-group">
                    <label for="db_file">Файл database.sqlite</label>
                    <input type="file" name="db_file" id="db_file" accept=".sqlite,.sqlite3,.db" required>
                </div>
                <button type="submit" class="btn btn--primary">Загрузить и заменить базу</button>
            </form>
            <p><a href="<?= base_url('admin/products') ?>" class="btn btn--ghost">← К товарам</a></p>
        </main>
    </div>
</body>
</html>
