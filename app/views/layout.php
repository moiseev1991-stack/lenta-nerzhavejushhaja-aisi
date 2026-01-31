<?php
// Определяем тип страницы и данные
$isHome = isset($allCategories) && isset($featuredProducts) && !isset($category) && !isset($product) && !isset($isServicePage) && !isset($isAnalogs) && !isset($isAnalogPage);
$isProduct = isset($product);
$isCategory = isset($category);
$isServicePage = isset($isServicePage);
$isAnalogs = isset($isAnalogs);
$isAnalogPage = isset($isAnalogPage);
$pageTitle = $pageTitle ?? '';
$pageDescription = $pageDescription ?? '';
$pageH1 = $pageH1 ?? '';
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

// Категории для меню AISI (если ещё не загружены)
if (!isset($allCategories) && isset($pdo)) {
    try {
        $stmt = $pdo->query('SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name');
        $allCategories = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Throwable $e) {
        $allCategories = [];
    }
}
if (!isset($allCategories)) $allCategories = [];

// Группировка по сериям для dropdown (порядок: 200, 300, 400, 900L, other)
$aisiSeriesOrder = ['200', '300', '400', '900L', 'other'];
$aisiMenuSeries = [];
foreach ($allCategories as $c) {
    $series = aisi_series_from_slug($c['slug']);
    if (!isset($aisiMenuSeries[$series])) {
        $aisiMenuSeries[$series] = ['key' => $series, 'title' => 'Серия ' . $series, 'items' => []];
    }
    $aisiMenuSeries[$series]['items'][] = $c;
}
// Серии для верхнего меню (только те, у кого есть марки)
$aisiTabs = array_filter($aisiSeriesOrder, function ($k) use ($aisiMenuSeries) { return !empty($aisiMenuSeries[$k]['items']); });
$aisiTabs = array_values($aisiTabs);

$currentCategorySlug = isset($category) && isset($category['slug']) ? $category['slug'] : null;
$currentSeries = $currentCategorySlug ? aisi_series_from_slug($currentCategorySlug) : null;
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
                <?php
                $logoPath = __DIR__ . '/../../img/logo_aisi_lenta_full.png';
                $hasLogo = file_exists($logoPath);
                ?>
                <a href="<?= base_url() ?>" class="logo">
                    <?php if ($hasLogo): ?>
                        <img src="<?= base_url('img/logo_aisi_lenta_full.png') ?>" alt="<?= e($config['site_name']) ?>" class="logo__img">
                    <?php else: ?>
                        <span class="logo__text"><?= e($config['site_name']) ?></span>
                    <?php endif; ?>
                </a>
                <button class="header__burger" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="headerNav">
                    <span></span><span></span><span></span>
                </button>
                <nav class="header__nav" id="headerNav" role="navigation">
                    <span class="header__nav-text">Марки AISI</span>
                    <?php foreach ($aisiTabs as $tabKey): ?>
                        <?php $block = $aisiMenuSeries[$tabKey]; ?>
                        <?php if ($tabKey === '200'): ?>
                            <?php $first = $block['items'][0] ?? null; if ($first): ?>
                                <a href="<?= base_url($first['slug'] . '/') ?>" class="header__nav-link <?= ($currentCategorySlug === $first['slug']) ? 'header__nav-link--active' : '' ?>">201</a>
                            <?php endif; ?>
                        <?php elseif ($tabKey === '900L'): ?>
                            <?php $first = $block['items'][0] ?? null; if ($first): ?>
                                <a href="<?= base_url($first['slug'] . '/') ?>" class="header__nav-link <?= ($currentCategorySlug === $first['slug']) ? 'header__nav-link--active' : '' ?>">904L</a>
                            <?php endif; ?>
                        <?php else: ?>
                        <div class="nav-dropdown nav-dropdown--series" data-series="<?= e($tabKey) ?>">
                            <button type="button" class="nav-dropdown__trigger <?= ($currentSeries === $tabKey) ? 'nav-dropdown__trigger--active' : '' ?>" aria-expanded="false" aria-haspopup="true" aria-controls="aisiPanel-<?= e($tabKey) ?>">
                                <?= e($tabKey) ?> <span class="nav-dropdown__chevron" aria-hidden="true">▾</span>
                            </button>
                            <div class="nav-dropdown__panel" id="aisiPanel-<?= e($tabKey) ?>" role="menu" aria-label="Марки серии <?= e($tabKey) ?>">
                                <ul class="nav-dropdown__links">
                                    <?php foreach ($block['items'] as $item): ?>
                                        <li>
                                            <a href="<?= base_url($item['slug'] . '/') ?>" role="menuitem" class="<?= ($currentCategorySlug === $item['slug']) ? 'nav-dropdown__link--active' : '' ?>"><?= e($item['name']) ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <a href="<?= base_url('admin/login') ?>" class="admin-link">Админ</a>
                </nav>
                <div class="header__contacts">
                    <a href="tel:+74951060741">+7 (495) 106-07-41</a>
                    <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a>
                </div>
            </div>
        </div>
        <div class="header__nav-overlay" id="navOverlay" aria-hidden="true"></div>
    </header>

    <main class="main">
        <?php if ($isHome): ?>
            <?php require __DIR__ . '/home.php'; ?>
        <?php elseif ($isProduct): ?>
            <?php require __DIR__ . '/product.php'; ?>
        <?php elseif ($isCategory): ?>
            <?php require __DIR__ . '/category.php'; ?>
        <?php elseif ($isAnalogs || $isAnalogPage): ?>
            <?php require __DIR__ . '/analogs.php'; ?>
        <?php elseif ($isServicePage): ?>
            <?php require __DIR__ . '/page.php'; ?>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer__inner">
                <div class="footer__col footer__col--catalog">
                    <h3 class="footer__title">Каталог</h3>
                    <ul class="footer__list footer__list--grid">
                        <?php foreach ($allCategories as $cat): ?>
                            <li><a href="<?= base_url($cat['slug'] . '/') ?>" class="footer__link"><?= e($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="<?= base_url('analogi/') ?>" class="footer__link">Аналоги</a></li>
                    </ul>
                </div>
                <div class="footer__col">
                    <h3 class="footer__title">Информация</h3>
                    <ul class="footer__list">
                        <li><a href="<?= base_url('about/') ?>" class="footer__link">О компании</a></li>
                        <li><a href="<?= base_url('price/') ?>" class="footer__link">Прайс-лист</a></li>
                        <li><a href="<?= base_url('delivery/') ?>" class="footer__link">Доставка</a></li>
                        <li><a href="<?= base_url('payment/') ?>" class="footer__link">Оплата</a></li>
                        <li><a href="<?= base_url('contacts/') ?>" class="footer__link">Контакты</a></li>
                    </ul>
                </div>
                <div class="footer__col">
                    <h3 class="footer__title">Документы</h3>
                    <ul class="footer__list">
                        <li><a href="<?= base_url('privacy-policy/') ?>" class="footer__link">Политика конфиденциальности</a></li>
                        <li><a href="<?= base_url('sitemap.xml') ?>" class="footer__link">Карта сайта</a></li>
                    </ul>
                </div>
                <div class="footer__col footer__col--contacts">
                    <h3 class="footer__title">Контакты</h3>
                    <ul class="footer__list">
                        <li><a href="tel:+74951060741" class="footer__link">+7 (495) 106-07-41</a></li>
                        <li><a href="mailto:ev18011@yandex.ru" class="footer__link">ev18011@yandex.ru</a></li>
                    </ul>
                </div>
            </div>
            <p class="footer__copy">
                &copy; <?= date('Y') ?> <?= e($config['site_name']) ?>
            </p>
        </div>
    </footer>
    <script>
    (function() {
        var nav = document.getElementById('headerNav');
        var burger = document.querySelector('.header__burger');
        var overlay = document.getElementById('navOverlay');
        var body = document.body;

        function closeAllDropdowns() {
            if (!nav) return;
            var dropdowns = nav.querySelectorAll('.nav-dropdown--series');
            dropdowns.forEach(function(d) {
                d.classList.remove('nav-dropdown--open');
                var t = d.querySelector('.nav-dropdown__trigger');
                if (t) t.setAttribute('aria-expanded', 'false');
            });
        }

        function openMobileMenu() {
            body.classList.add('menu-open');
            if (burger) { burger.setAttribute('aria-expanded', 'true'); burger.setAttribute('aria-label', 'Закрыть меню'); }
            if (overlay) overlay.setAttribute('aria-hidden', 'false');
            document.documentElement.style.overflow = 'hidden';
        }
        function closeMobileMenu() {
            body.classList.remove('menu-open');
            if (burger) { burger.setAttribute('aria-expanded', 'false'); burger.setAttribute('aria-label', 'Открыть меню'); }
            if (overlay) overlay.setAttribute('aria-hidden', 'true');
            document.documentElement.style.overflow = '';
        }
        function toggleMobileMenu() {
            if (body.classList.contains('menu-open')) closeMobileMenu();
            else openMobileMenu();
        }

        if (burger && nav) {
            burger.addEventListener('click', function() { toggleMobileMenu(); });
        }
        if (overlay) {
            overlay.addEventListener('click', closeMobileMenu);
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllDropdowns();
                if (body.classList.contains('menu-open')) closeMobileMenu();
            }
        });

        if (nav) {
            var dropdowns = nav.querySelectorAll('.nav-dropdown--series');
            dropdowns.forEach(function(container) {
                var trigger = container.querySelector('.nav-dropdown__trigger');
                var panel = container.querySelector('.nav-dropdown__panel');
                if (!trigger || !panel) return;
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var isOpen = container.classList.contains('nav-dropdown--open');
                    closeAllDropdowns();
                    if (!isOpen) {
                        container.classList.add('nav-dropdown--open');
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                });
                panel.querySelectorAll('a[role="menuitem"]').forEach(function(a) {
                    a.addEventListener('click', function() { closeAllDropdowns(); if (body.classList.contains('menu-open')) closeMobileMenu(); });
                });
            });
            document.addEventListener('click', function(e) {
                if (!nav.contains(e.target) && !(burger && burger.contains(e.target))) closeAllDropdowns();
            });
        }
    })();
    </script>
</body>
</html>
