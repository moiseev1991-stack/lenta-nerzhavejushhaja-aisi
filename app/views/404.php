<?php
$config = require __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Страница не найдена</title>
    <link rel="stylesheet" href="<?= base_url('assets/styles.css') ?>">
</head>
<body>
    <div class="container" style="padding: 4rem 1rem; text-align: center;">
        <h1>404</h1>
        <p>Страница не найдена</p>
        <a href="<?= base_url('aisi-316l/') ?>" class="btn btn--primary">Перейти в каталог</a>
    </div>
</body>
</html>
