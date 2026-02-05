<?php
/**
 * Выводит список URL товаров без картинок (нет в БД и нет файла {slug}.jpg в img/product_images_named).
 * Запуск: php scripts/list_products_without_images.php
 */
$base = dirname(__DIR__);
require $base . '/app/db.php';
require $base . '/app/helpers.php';

$pdo = db();
$imagesDir = $base . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'product_images_named';

$stmt = $pdo->query("
    SELECT p.slug AS product_slug, p.image AS db_image, c.slug AS category_slug
    FROM products p
    JOIN categories c ON c.id = p.category_id
    WHERE c.is_active = 1
    ORDER BY c.slug, p.slug
");

$without = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $hasDb = !empty(trim((string) $row['db_image']));
    if ($hasDb) continue;
    $path = $imagesDir . DIRECTORY_SEPARATOR . $row['product_slug'] . '.jpg';
    if (is_file($path)) continue;
    $without[] = '/' . $row['category_slug'] . '/' . $row['product_slug'] . '/';
}

foreach ($without as $url) {
    echo $url . "\n";
}
