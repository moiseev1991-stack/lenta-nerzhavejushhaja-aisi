<?php
// Определяем тип страницы и данные
$isHome = isset($allCategories) && isset($featuredProducts) && !isset($category) && !isset($product);
$isProduct = isset($product);
$isCategory = isset($category);
$pageTitle = '';
$pageDescription = '';
$pageH1 = '';
$jsonLd = [];

if ($isHome) {
    $config = require __DIR__ . '/../config.php';
    $pageTitle = $config['site_name'];
    $pageDescription = 'Каталог нержавеющих лент AISI. Широкий ассортимент марок стали.';
}

if ($isProduct) {
    $pageTitle = $product['title'] ?: $product['name'];
    $pageDescription = $product['description'] ?: '';
    $pageH1 = $product['h1'] ?: $product['name'];
    
    // JSON-LD Product
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['name'],
        'sku' => (string)$product['id'],
        'image' => $product['image'] ? base_url($product['image']) : null,
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'RUB',
            'price' => (string)$product['price_per_kg'],
            'availability' => $product['in_stock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        ],
    ];
    
    // BreadcrumbList для товара
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Главная',
                'item' => base_url(),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $product['category_name'],
                'item' => base_url($product['category_slug'] . '/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $product['name'],
                'item' => base_url('product/' . $product['slug'] . '/'),
            ],
        ],
    ];
}

if ($isCategory) {
    $pageTitle = $category['title'] ?: $category['name'];
    $pageDescription = $category['description'] ?: '';
    $pageH1 = $category['h1'] ?: $category['name'];
    
    // BreadcrumbList для категории
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Главная',
                'item' => base_url(),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $category['name'],
                'item' => base_url($category['slug'] . '/'),
            ],
        ],
    ];
}

$config = require __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?: $config['site_name']) ?></title>
    <?php if ($pageDescription): ?>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= base_url('assets/styles.css') ?>">
    <?php if (!empty($jsonLd)): ?>
    <script type="application/ld+json">
    <?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
    <?php endif; ?>
</head>
<body>
    <header class="top">
        <div class="container">
            <div class="header__inner">
                <a href="<?= base_url() ?>" class="logo"><?= e($config['site_name']) ?></a>
                <nav class="header__nav">
                    <a href="<?= base_url('aisi-304/') ?>">AISI 304</a>
                    <a href="<?= base_url('aisi-316l/') ?>">AISI 316L</a>
                    <a href="<?= base_url('aisi-201/') ?>">AISI 201</a>
                    <a href="<?= base_url('admin/login') ?>" class="admin-link">Админ</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <?php if ($isHome): ?>
            <?php require __DIR__ . '/home.php'; ?>
        <?php elseif ($isProduct): ?>
            <?php require __DIR__ . '/product.php'; ?>
        <?php elseif ($isCategory): ?>
            <?php require __DIR__ . '/category.php'; ?>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= e($config['site_name']) ?></p>
        </div>
    </footer>
</body>
</html>
