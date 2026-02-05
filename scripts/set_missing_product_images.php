<?php
/**
 * Проставляет изображения товарам из списка URL — только тем, у кого фото отсутствует.
 * Формат в БД: /img/product_images_named/<slug>.jpg
 * Запуск: php scripts/set_missing_product_images.php
 */
$base = dirname(__DIR__);
require $base . '/app/db.php';
require $base . '/app/helpers.php';

$imagesDir = $base . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'product_images_named';
$IMAGE_PATH_PREFIX = '/img/product_images_named/';

$urls = [
    '/aisi-304/lenta-nerzhaveyuschaya-0-25x10-mm-aisi-304-nagartovannaya-2b/',
    '/aisi-304/lenta-nerzhaveyuschaya-0-2x10-mm-aisi-304-myagkaya-ba/',
    '/aisi-304/lenta-nerzhaveyuschaya-0-35x12-mm-aisi-304-myagkaya-ba/',
    '/aisi-304/lenta-nerzhaveyuschaya-0-3x12-mm-aisi-304-nagartovannaya-ba/',
    '/aisi-304/lenta-nerzhaveyuschaya-0-4x15-mm-aisi-304-myagkaya-2b/',
    '/aisi-304/lenta-nerzhaveyuschaya-0-6x25-mm-aisi-304-myagkaya-ba/',
    '/aisi-304/lenta-nerzhaveyuschaya-0-7x30-mm-aisi-304-myagkaya-2b/',
    '/aisi-316l/lenta-nerzhaveyuschaya-0-15x10-mm-aisi-316l-myagkaya-ba/',
    '/aisi-316l/lenta-nerzhaveyuschaya-0-1x8-mm-aisi-316l-myagkaya-ba/',
    '/aisi-316l/lenta-nerzhaveyuschaya-0-25x10-mm-aisi-316l-myagkaya-2b/',
    '/aisi-316l/lenta-nerzhaveyuschaya-0-2x10-mm-aisi-316l-myagkaya-ba/',
    '/aisi-316l/lenta-nerzhaveyuschaya-0-35x12-mm-aisi-316l-nagartovannaya-ba/',
    '/aisi-316l/lenta-nerzhaveyuschaya-0-3x12-mm-aisi-316l-myagkaya-ba/',
    '/aisi-316l/lenta-nerzhaveyuschaya-0-4x15-mm-aisi-316l-nagartovannaya-2b/',
    '/aisi-316l/lenta-nerzhaveyuschaya-0-5x20-mm-aisi-316l-myagkaya-ba/',
    '/aisi-316l/lenta-nerzhaveyuschaya-0-6x25-mm-aisi-316l-nagartovannaya-ba/',
    '/aisi-316l/lenta-nerzhaveyuschaya-0-7x30-mm-aisi-316l-nagartovannaya-2b/',
];

$updated = [];
$skipped = [];
$errors = [];

$pdo = db();
$findBySlug = $pdo->prepare('SELECT id, slug, image FROM products WHERE slug = ?');
$updateImage = $pdo->prepare('UPDATE products SET image = ?, updated_at = ? WHERE id = ?');

foreach ($urls as $url) {
    $url = trim($url, '/');
    $segments = explode('/', $url);
    $slug = end($segments);
    if ($slug === '') {
        $errors[] = ['slug' => $url, 'reason' => 'не удалось извлечь slug из URL'];
        continue;
    }

    $findBySlug->execute([$slug]);
    $product = $findBySlug->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        $errors[] = ['slug' => $slug, 'reason' => 'товар не найден'];
        continue;
    }

    if (!empty(trim((string) $product['image']))) {
        $skipped[] = $slug;
        continue;
    }

    $filePath = $imagesDir . DIRECTORY_SEPARATOR . $slug . '.jpg';
    if (!is_file($filePath)) {
        $errors[] = ['slug' => $slug, 'reason' => 'нет файла ' . $slug . '.jpg в img/product_images_named'];
        continue;
    }

    $imageValue = $IMAGE_PATH_PREFIX . $slug . '.jpg';
    $updateImage->execute([$imageValue, nowIso(), $product['id']]);
    $updated[] = $slug;
}

// Отчёт
echo "=== Отчёт по проставлению изображений ===\n\n";
echo "✅ Обновлено: " . count($updated) . "\n";
foreach ($updated as $s) {
    echo "   - " . $s . "\n";
}
echo "\n⏭ Пропущено (фото уже было): " . count($skipped) . "\n";
foreach ($skipped as $s) {
    echo "   - " . $s . "\n";
}
echo "\n⚠️ Ошибки (нет файла / товар не найден): " . count($errors) . "\n";
foreach ($errors as $e) {
    echo "   - " . $e['slug'] . " — " . $e['reason'] . "\n";
}
echo "\n";
