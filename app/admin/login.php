<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../config.php';
require __DIR__ . '/../helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $config = require __DIR__ . '/../config.php';
    
    if ($username === $config['admin_user'] && password_verify($password, $config['admin_pass_hash'])) {
        $_SESSION['admin'] = true;
        redirect('/admin/products');
    } else {
        $error = 'Неверный логин или пароль';
    }
}

// Если уже авторизован - редирект
if (!empty($_SESSION['admin'])) {
    redirect('/admin/products');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админку</title>
    <link rel="stylesheet" href="<?= base_url('public/assets/styles.css') ?>">
</head>
<body>
    <div class="admin-login">
        <div class="admin-login__card">
            <h1>Вход в админку</h1>
            <?php if ($error): ?>
                <div class="alert alert--error"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Логин</label>
                    <input type="text" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn--primary btn--block">Войти</button>
            </form>
            <p class="admin-login__hint">По умолчанию: admin / admin123</p>
        </div>
    </div>
</body>
</html>
