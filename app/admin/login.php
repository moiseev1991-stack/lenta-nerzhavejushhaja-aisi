<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../config.php';
require __DIR__ . '/../helpers.php';

$error = '';
$storageDir = dirname(__DIR__) . '/../storage';
$lockFile = $storageDir . '/admin_login_attempts.json';
$maxAttempts = 5;
$lockoutMinutes = 15;

function getAttemptsByIp($lockFile) {
    if (!is_file($lockFile)) return [];
    $data = @json_decode(file_get_contents($lockFile), true);
    return is_array($data) ? $data : [];
}

function getLoginAttempts($lockFile, $ipKey) {
    $all = getAttemptsByIp($lockFile);
    return $all[$ipKey] ?? ['count' => 0, 'until' => 0];
}

function saveLoginAttempts($lockFile, $ipKey, $data) {
    if (!is_dir(dirname($lockFile))) @mkdir(dirname($lockFile), 0755, true);
    $all = getAttemptsByIp($lockFile);
    $all[$ipKey] = $data;
    file_put_contents($lockFile, json_encode($all), LOCK_EX);
}

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$ip = trim(explode(',', $ip)[0]);
$ipKey = md5($ip);
$attemptsData = getLoginAttempts($lockFile, $ipKey);
$now = time();

if ($attemptsData['until'] > $now) {
    $error = 'Слишком много неудачных попыток. Попробуйте через ' . ceil(($attemptsData['until'] - $now) / 60) . ' мин.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $config = require __DIR__ . '/../config.php';

    if ($username === $config['admin_user'] && password_verify($password, $config['admin_pass_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        saveLoginAttempts($lockFile, $ipKey, ['count' => 0, 'until' => 0]);
        redirect('/admin/products');
    } else {
        $attemptsData['count'] = ($attemptsData['count'] ?? 0) + 1;
        $attemptsData['until'] = $attemptsData['count'] >= $maxAttempts ? $now + ($lockoutMinutes * 60) : 0;
        saveLoginAttempts($lockFile, $ipKey, $attemptsData);
        $error = 'Неверный логин или пароль';
        if ($attemptsData['count'] >= $maxAttempts) {
            $error .= '. Вход временно заблокирован на ' . $lockoutMinutes . ' мин.';
        }
    }
}

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
    <link rel="stylesheet" href="<?= asset_url('assets/styles.css') ?>">
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
        </div>
    </div>
</body>
</html>
