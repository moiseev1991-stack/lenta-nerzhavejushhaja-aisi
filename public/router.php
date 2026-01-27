<?php

// Устанавливаем кодировку UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Router для встроенного PHP сервера
// Отдает статику как есть, остальное прокидывает в index.php

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Убираем начальный слэш для проверки файла
$requestPath = ltrim($requestPath, '/');

// Если это файл и он существует - отдаем как есть
if ($requestPath && $requestPath !== 'index.php' && $requestPath !== 'router.php') {
    $filePath = __DIR__ . '/' . $requestPath;
    
    // Если это PHP файл (кроме index.php и router.php) - выполняем его
    if (file_exists($filePath) && is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
        require $filePath;
        exit;
    }
    
    // Если это статический файл (CSS, JS, изображения) - отдаем как есть
    if (file_exists($filePath) && is_file($filePath)) {
        return false; // Отдать статику
    }
}

// Иначе прокидываем в index.php
require __DIR__ . '/index.php';
