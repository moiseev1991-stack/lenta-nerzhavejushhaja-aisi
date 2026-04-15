<?php

// Устанавливаем кодировку UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

require_once dirname(__DIR__) . '/app/helpers.php';

// Router для встроенного PHP сервера
// Отдает статику как есть, остальное прокидывает в index.php

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Убираем начальный слэш для проверки файла
$requestPath = ltrim($requestPath, '/');
if (strpos($requestPath, 'public/') === 0) {
    $requestPath = substr($requestPath, 7);
}

// Фотографии продукции для hero-коллажа (img/asi/ в корне проекта)
if (preg_match('#^img/asi/(.+)$#', $requestPath, $m)) {
    $filename = basename(rawurldecode($m[1]));
    if ($filename !== '' && strpos($filename, '..') === false && preg_match('/\.(jpg|jpeg|png)$/i', $filename)) {
        $imgFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'asi' . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($imgFile) && is_file($imgFile)) {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            header('Content-Type: ' . ($ext === 'png' ? 'image/png' : 'image/jpeg'));
            header('Cache-Control: public, max-age=604800');
            readfile($imgFile);
            exit;
        }
    }
}

// Картинки товаров из img/product_images_named (в корне проекта, не в public/)
if (preg_match('#^img/product_images_named/(.+)$#', $requestPath, $m)) {
    $filename = basename(rawurldecode($m[1]));
    if ($filename !== '' && strpos($filename, '..') === false && preg_match('/\.(jpg|jpeg|png)$/i', $filename)) {
        $imgFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'product_images_named' . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($imgFile) && is_file($imgFile)) {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            header('Content-Type: ' . ($ext === 'png' ? 'image/png' : 'image/jpeg'));
            header('Cache-Control: public, max-age=604800');
            readfile($imgFile);
            exit;
        }
    }
}

// public/files/* — PDF с корректным MIME; файл может быть без суффикса .pdf на диске
if (preg_match('#^files/([a-zA-Z0-9_\-\.]+)$#', $requestPath, $m)) {
    $fname = basename($m[1]);
    if ($fname !== '' && strpos($fname, '..') === false) {
        $full = resolve_files_disk_path($fname);
        if ($full !== null && is_file($full)) {
            $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];
            $allowed = ($ext === '') || isset($mimeTypes[$ext]);
            if ($allowed) {
                header_remove('Content-Type');
                $mime = ($ext === '') ? 'application/pdf' : $mimeTypes[$ext];
                header('Content-Type: ' . $mime);
                header('Content-Disposition: inline; filename="' . basename($full) . '"');
                header('Content-Length: ' . filesize($full));
                header('Cache-Control: public, max-age=86400');
                header('X-Content-Type-Options: nosniff');
                readfile($full);
                exit;
            }
        }
    }
}

// Если это файл и он существует - отдаем как есть
if ($requestPath && $requestPath !== 'index.php' && $requestPath !== 'router.php') {
    // Проверяем в __DIR__ (public/) и в document root (может быть корень проекта при -t .)
    $filePath = __DIR__ . '/' . $requestPath;
    $docRootPath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $requestPath);

    $resolvedPath = '';
    if (file_exists($filePath) && is_file($filePath)) {
        $resolvedPath = $filePath;
    } elseif ($docRootPath !== $filePath && file_exists($docRootPath) && is_file($docRootPath)) {
        $resolvedPath = $docRootPath;
    }

    // Если это PHP файл (кроме index.php и router.php) - выполняем его
    if ($resolvedPath !== '' && pathinfo($resolvedPath, PATHINFO_EXTENSION) === 'php') {
        require $resolvedPath;
        exit;
    }

    // Если это статический файл (CSS, JS, изображения) - отдаем как есть
    if ($resolvedPath !== '') {
        return false; // Отдать статику
    }
}

// Иначе прокидываем в index.php
require __DIR__ . '/index.php';
