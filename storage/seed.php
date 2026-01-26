<?php

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';

$pdo = db();

// Читаем схему
$sql = file_get_contents(__DIR__ . '/init.sql');
$pdo->exec($sql);

// Категории
$categories = [
    ['slug' => 'aisi-201', 'name' => 'AISI 201'],
    ['slug' => 'aisi-301', 'name' => 'AISI 301'],
    ['slug' => 'aisi-304', 'name' => 'AISI 304'],
    ['slug' => 'aisi-304l', 'name' => 'AISI 304L'],
    ['slug' => 'aisi-310', 'name' => 'AISI 310'],
    ['slug' => 'aisi-310s', 'name' => 'AISI 310S'],
    ['slug' => 'aisi-316', 'name' => 'AISI 316'],
    ['slug' => 'aisi-316l', 'name' => 'AISI 316L'],
    ['slug' => 'aisi-316ti', 'name' => 'AISI 316Ti'],
    ['slug' => 'aisi-321', 'name' => 'AISI 321'],
    ['slug' => 'aisi-409', 'name' => 'AISI 409'],
    ['slug' => 'aisi-420', 'name' => 'AISI 420'],
    ['slug' => 'aisi-430', 'name' => 'AISI 430'],
    ['slug' => 'aisi-431', 'name' => 'AISI 431'],
    ['slug' => 'aisi-441', 'name' => 'AISI 441'],
    ['slug' => 'aisi-904l', 'name' => 'AISI 904L'],
];

$stmt = $pdo->prepare('
    INSERT OR IGNORE INTO categories (slug, name, h1, title, description, intro, is_active, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?)
');

$now = nowIso();

foreach ($categories as $cat) {
    $h1 = $cat['name'] . ' — нержавеющая лента';
    $title = $cat['name'] . ' — купить нержавеющую ленту | Каталог AISI';
    $description = 'Купить нержавеющую ленту ' . $cat['name'] . ' по выгодной цене. Широкий выбор размеров и состояний.';
    $intro = 'Лента нержавеющая марки ' . $cat['name'] . ' для различных применений.';
    
    $stmt->execute([
        $cat['slug'],
        $cat['name'],
        $h1,
        $title,
        $description,
        $intro,
        $now,
        $now,
    ]);
}

echo "Категории созданы\n";

// Получаем ID категорий для товаров
$catIds = [];
$stmt = $pdo->query('SELECT id, slug FROM categories');
while ($row = $stmt->fetch()) {
    $catIds[$row['slug']] = $row['id'];
}

// Товары для AISI 316L
$products_316l = [
    ['name' => 'Лента нержавеющая 0.2x10 мм AISI 316L мягкая BA', 'thickness' => 0.2, 'width' => 10, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 450.00],
    ['name' => 'Лента нержавеющая 0.3x12 мм AISI 316L мягкая BA', 'thickness' => 0.3, 'width' => 12, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 460.00],
    ['name' => 'Лента нержавеющая 0.4x15 мм AISI 316L нагартованная 2B', 'thickness' => 0.4, 'width' => 15, 'condition' => 'hard', 'spring' => 0, 'surface' => '2B', 'price' => 470.00],
    ['name' => 'Лента нержавеющая 0.5x20 мм AISI 316L мягкая BA', 'thickness' => 0.5, 'width' => 20, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 480.00],
    ['name' => 'Лента нержавеющая 0.6x25 мм AISI 316L нагартованная BA', 'thickness' => 0.6, 'width' => 25, 'condition' => 'hard', 'spring' => 0, 'surface' => 'BA', 'price' => 490.00],
    ['name' => 'Лента нержавеющая 0.25x10 мм AISI 316L мягкая 2B', 'thickness' => 0.25, 'width' => 10, 'condition' => 'soft', 'spring' => 0, 'surface' => '2B', 'price' => 455.00],
    ['name' => 'Лента нержавеющая 0.35x12 мм AISI 316L нагартованная BA', 'thickness' => 0.35, 'width' => 12, 'condition' => 'hard', 'spring' => 0, 'surface' => 'BA', 'price' => 465.00],
    ['name' => 'Лента нержавеющая 0.1x8 мм AISI 316L мягкая BA', 'thickness' => 0.1, 'width' => 8, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 440.00],
    ['name' => 'Лента нержавеющая 0.7x30 мм AISI 316L нагартованная 2B', 'thickness' => 0.7, 'width' => 30, 'condition' => 'hard', 'spring' => 0, 'surface' => '2B', 'price' => 500.00],
    ['name' => 'Лента нержавеющая 0.15x10 мм AISI 316L мягкая BA', 'thickness' => 0.15, 'width' => 10, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 445.00],
];

// Товары для AISI 304
$products_304 = [
    ['name' => 'Лента нержавеющая 0.2x10 мм AISI 304 мягкая BA', 'thickness' => 0.2, 'width' => 10, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 380.00],
    ['name' => 'Лента нержавеющая 0.3x12 мм AISI 304 нагартованная BA', 'thickness' => 0.3, 'width' => 12, 'condition' => 'hard', 'spring' => 0, 'surface' => 'BA', 'price' => 390.00],
    ['name' => 'Лента нержавеющая 0.4x15 мм AISI 304 мягкая 2B', 'thickness' => 0.4, 'width' => 15, 'condition' => 'soft', 'spring' => 0, 'surface' => '2B', 'price' => 400.00],
    ['name' => 'Лента нержавеющая 0.5x20 мм AISI 304 нагартованная BA', 'thickness' => 0.5, 'width' => 20, 'condition' => 'hard', 'spring' => 0, 'surface' => 'BA', 'price' => 410.00],
    ['name' => 'Лента нержавеющая 0.6x25 мм AISI 304 мягкая BA', 'thickness' => 0.6, 'width' => 25, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 420.00],
    ['name' => 'Лента нержавеющая 0.25x10 мм AISI 304 нагартованная 2B', 'thickness' => 0.25, 'width' => 10, 'condition' => 'hard', 'spring' => 0, 'surface' => '2B', 'price' => 385.00],
    ['name' => 'Лента нержавеющая 0.35x12 мм AISI 304 мягкая BA', 'thickness' => 0.35, 'width' => 12, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 395.00],
    ['name' => 'Лента нержавеющая 0.1x8 мм AISI 304 нагартованная BA', 'thickness' => 0.1, 'width' => 8, 'condition' => 'hard', 'spring' => 0, 'surface' => 'BA', 'price' => 370.00],
    ['name' => 'Лента нержавеющая 0.7x30 мм AISI 304 мягкая 2B', 'thickness' => 0.7, 'width' => 30, 'condition' => 'soft', 'spring' => 0, 'surface' => '2B', 'price' => 430.00],
    ['name' => 'Лента нержавеющая 0.15x10 мм AISI 304 нагартованная BA', 'thickness' => 0.15, 'width' => 10, 'condition' => 'hard', 'spring' => 0, 'surface' => 'BA', 'price' => 375.00],
];

// Товары для AISI 201
$products_201 = [
    ['name' => 'Лента нержавеющая 0.2x10 мм AISI 201 мягкая BA', 'thickness' => 0.2, 'width' => 10, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 320.00],
    ['name' => 'Лента нержавеющая 0.3x12 мм AISI 201 нагартованная BA', 'thickness' => 0.3, 'width' => 12, 'condition' => 'hard', 'spring' => 0, 'surface' => 'BA', 'price' => 330.00],
    ['name' => 'Лента нержавеющая 0.4x15 мм AISI 201 мягкая 2B', 'thickness' => 0.4, 'width' => 15, 'condition' => 'soft', 'spring' => 0, 'surface' => '2B', 'price' => 340.00],
    ['name' => 'Лента нержавеющая 0.5x20 мм AISI 201 нагартованная BA', 'thickness' => 0.5, 'width' => 20, 'condition' => 'hard', 'spring' => 0, 'surface' => 'BA', 'price' => 350.00],
    ['name' => 'Лента нержавеющая 0.6x25 мм AISI 201 мягкая BA', 'thickness' => 0.6, 'width' => 25, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 360.00],
    ['name' => 'Лента нержавеющая 0.25x10 мм AISI 201 нагартованная 2B', 'thickness' => 0.25, 'width' => 10, 'condition' => 'hard', 'spring' => 0, 'surface' => '2B', 'price' => 325.00],
    ['name' => 'Лента нержавеющая 0.35x12 мм AISI 201 мягкая BA', 'thickness' => 0.35, 'width' => 12, 'condition' => 'soft', 'spring' => 0, 'surface' => 'BA', 'price' => 335.00],
    ['name' => 'Лента нержавеющая 0.1x8 мм AISI 201 нагартованная BA', 'thickness' => 0.1, 'width' => 8, 'condition' => 'hard', 'spring' => 0, 'surface' => 'BA', 'price' => 310.00],
    ['name' => 'Лента нержавеющая 0.7x30 мм AISI 201 мягкая 2B', 'thickness' => 0.7, 'width' => 30, 'condition' => 'soft', 'spring' => 0, 'surface' => '2B', 'price' => 370.00],
    ['name' => 'Лента нержавеющая 0.15x10 мм AISI 201 нагартованная BA', 'thickness' => 0.15, 'width' => 10, 'condition' => 'hard', 'spring' => 0, 'surface' => 'BA', 'price' => 315.00],
];

$stmt = $pdo->prepare('
    INSERT OR IGNORE INTO products 
    (category_id, slug, name, h1, title, description, thickness, width, condition, spring, surface, price_per_kg, in_stock, lead_time, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)
');

$insertProduct = function($product, $categoryId, $categorySlug) use ($stmt, $now) {
    $slug = slugify($product['name']);
    $h1 = $product['name'];
    $title = $product['name'] . ' | Каталог AISI';
    $description = 'Купить ' . mb_strtolower($product['name']) . ' по выгодной цене.';
    $leadTime = '7-14 дней';
    
    $stmt->execute([
        $categoryId,
        $slug,
        $product['name'],
        $h1,
        $title,
        $description,
        $product['thickness'],
        $product['width'],
        $product['condition'],
        $product['spring'],
        $product['surface'],
        $product['price'],
        $leadTime,
        $now,
        $now,
    ]);
};

if (isset($catIds['aisi-316l'])) {
    foreach ($products_316l as $product) {
        $insertProduct($product, $catIds['aisi-316l'], 'aisi-316l');
    }
    echo "Товары для AISI 316L созданы\n";
}

if (isset($catIds['aisi-304'])) {
    foreach ($products_304 as $product) {
        $insertProduct($product, $catIds['aisi-304'], 'aisi-304');
    }
    echo "Товары для AISI 304 созданы\n";
}

if (isset($catIds['aisi-201'])) {
    foreach ($products_201 as $product) {
        $insertProduct($product, $catIds['aisi-201'], 'aisi-201');
    }
    echo "Товары для AISI 201 созданы\n";
}

echo "Seed завершен!\n";
