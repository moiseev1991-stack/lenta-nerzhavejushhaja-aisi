<?php
/**
 * Список товаров без фото и подстановка картинок.
 *
 * Логика (как из адреса страницы товара):
 *   URL: /aisi-201/lenta-nerzhaveyuschaya-0-1x8-mm-aisi-201-nagartovannaya-ba/
 *   → убираем подкатегорию /aisi-201/ и слэши → product_slug = lenta-nerzhaveyuschaya-0-1x8-mm-aisi-201-nagartovannaya-ba
 *   → поиск по файлу Lenta_Import_ADD_missing_categories.csv по этому slug (колонка Slug → колонка Изображение)
 *   → если файл с таким именем есть в img/product_images_named — подставляем; иначе пробуем {slug}.jpg
 *
 * Результат: список оставшихся без фото в docs/products_without_photo.txt
 */

$baseDir = realpath(__DIR__ . '/..');
require $baseDir . '/app/config.php';
require $baseDir . '/app/db.php';

$pdo = db();
$imagesDir = $baseDir . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'product_images_named';
$csvPath = $baseDir . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'Lenta_Import_ADD_missing_categories.csv';
$outListPath = $baseDir . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'products_without_photo.txt';

$IMAGE_WEB_PREFIX = '/img/product_images_named/';

// Все товары без фото
$stmt = $pdo->query("SELECT p.id, p.slug, p.name, c.slug as category_slug, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.image IS NULL OR p.image = '' ORDER BY c.name, p.name");
$withoutPhoto = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Товаров без фото в БД: " . count($withoutPhoto) . "\n";

// Карта slug -> имя файла из CSV (последняя колонка — Изображение)
$csvImageBySlug = [];
if (is_file($csvPath)) {
    $fh = fopen($csvPath, 'rb');
    $headerLine = fgets($fh);
    $headerLine = mb_convert_encoding($headerLine, 'UTF-8', 'Windows-1251');
    $headers = str_getcsv($headerLine, ';');
    $colSlug = null;
    $colImage = null;
    foreach ($headers as $i => $h) {
        $k = mb_strtolower(trim($h));
        if (strpos($k, 'slug') !== false) $colSlug = $i;
        if (strpos($k, 'изображение') !== false || $k === 'image') $colImage = $i;
    }
    if ($colImage === null && count($headers) > 0) $colImage = count($headers) - 1;
    while (($line = fgets($fh)) !== false) {
        $line = mb_convert_encoding($line, 'UTF-8', 'Windows-1251');
        $row = str_getcsv($line, ';');
        $slug = isset($row[$colSlug]) ? trim($row[$colSlug]) : '';
        $img = ($colImage !== null && isset($row[$colImage])) ? trim($row[$colImage], " \t\r\n") : '';
        if ($slug !== '' && $img !== '') {
            $img = basename($img);
            if (strpos($img, '.') === false) $img .= '.jpg';
            $csvImageBySlug[$slug] = $img;
        }
    }
    fclose($fh);
    echo "В CSV загружено slug->файл: " . count($csvImageBySlug) . " записей.\n";
}

$fixed = 0;
$stillMissing = [];

foreach ($withoutPhoto as $row) {
    $slug = $row['slug'];
    $filename = null;

    if (isset($csvImageBySlug[$slug])) {
        $try = $csvImageBySlug[$slug];
        if (is_file($imagesDir . DIRECTORY_SEPARATOR . $try)) {
            $filename = $try;
        }
    }
    if ($filename === null) {
        $try = $slug . '.jpg';
        if (is_file($imagesDir . DIRECTORY_SEPARATOR . $try)) {
            $filename = $try;
        }
    }

    if ($filename !== null) {
        $webPath = $IMAGE_WEB_PREFIX . $filename;
        $pdo->prepare('UPDATE products SET image = ?, updated_at = ? WHERE id = ?')->execute([$webPath, date('Y-m-d H:i:s'), $row['id']]);
        $fixed++;
        echo "  [OK] {$row['category_name']} / {$row['slug']} -> {$filename}\n";
    } else {
        $stillMissing[] = $row;
    }
}

// Опционально: общая заглушка для оставшихся (положите файл img/placeholder-product.jpg или public/img/placeholder-product.jpg)
$placeholderPath = $baseDir . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'placeholder-product.jpg';
$placeholderWeb = '/img/placeholder-product.jpg';
if (!is_file($placeholderPath)) {
    $placeholderPath = $baseDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'placeholder-product.jpg';
}
if (is_file($placeholderPath) && count($stillMissing) > 0) {
    $now = date('Y-m-d H:i:s');
    $upd = $pdo->prepare('UPDATE products SET image = ?, updated_at = ? WHERE id = ?');
    foreach ($stillMissing as $r) {
        $upd->execute([$placeholderWeb, $now, $r['id']]);
        $fixed++;
    }
    echo "Подставлена заглушка placeholder-product.jpg для " . count($stillMissing) . " товаров.\n";
    $stillMissing = [];
}

echo "\nПроставлено фото: {$fixed}.\n";
echo "Осталось без фото: " . count($stillMissing) . "\n";

// Сохраняем список оставшихся без фото в docs/products_without_photo.txt
$lines = [];
$lines[] = "Список товаров без фото (после попытки подстановки из папки img/product_images_named и CSV docs/Lenta_Import_ADD_missing_categories.csv)";
$lines[] = "Эти товары из сида (AISI 201, 304, 316L), в CSV их нет; файлов по slug в папке тоже нет.";
$lines[] = "Чтобы подставить общую заглушку: положите изображение img/placeholder-product.jpg или public/img/placeholder-product.jpg и запустите скрипт снова.";
$lines[] = "";
$lines[] = "Дата: " . date('Y-m-d H:i:s');
$lines[] = "Всего: " . count($stillMissing);
$lines[] = "";
foreach ($stillMissing as $r) {
    $lines[] = sprintf("%s | %s | %s | slug: %s", $r['category_name'], $r['name'], $r['slug'], $r['slug']);
}
$lines[] = "";
$lines[] = "--- по категориям ---";
$byCat = [];
foreach ($stillMissing as $r) {
    $c = $r['category_name'];
    if (!isset($byCat[$c])) $byCat[$c] = [];
    $byCat[$c][] = $r['name'] . ' (slug: ' . $r['slug'] . ')';
}
foreach ($byCat as $cat => $items) {
    $lines[] = "";
    $lines[] = $cat . " (" . count($items) . ")";
    foreach ($items as $item) $lines[] = "  - " . $item;
}

file_put_contents($outListPath, implode("\n", $lines));
echo "\nСписок оставшихся без фото сохранён: docs/products_without_photo.txt\n";
