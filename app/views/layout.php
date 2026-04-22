<?php
// Определяем тип страницы и данные
$is404 = isset($is404) && $is404;
$isSitemapPage = isset($isSitemapPage);
$isHome = !$is404 && !$isSitemapPage && isset($allCategories) && isset($featuredProducts) && !isset($category) && !isset($product) && !isset($isServicePage) && !isset($isBonusPage);
$isProduct = !$is404 && isset($product) && is_array($product);
$isCategory = !$is404 && isset($category) && is_array($category) && !$isProduct;
$isServicePage = isset($isServicePage);
$isBonusPage   = isset($isBonusPage);
$isSeriesPage  = isset($isSeriesPage) && $isSeriesPage;
$pageTitle = $pageTitle ?? '';
$pageDescription = $pageDescription ?? '';
$pageH1 = $pageH1 ?? '';
$jsonLd = [];
$config = require __DIR__ . '/../config.php';

if ($isHome) {
    $defaultHomeTitle = 'Нержавеющая лента AISI купить оптом — нарезка от 1 метра, доставка по России';
    $defaultHomeDescription = 'Холоднокатаная нержавеющая лента всех марок AISI: 304, 316, 321, 430 и др. Нарезка от 1 метра, толщины 0,05–4 мм, ширина от 2,5 мм. Прайс-лист, доставка по всей России. Тел: 8-800-200-39-43.';
    $pageTitle = isset($homeTitle) && (string)$homeTitle !== '' ? $homeTitle : $defaultHomeTitle;
    $pageDescription = isset($homeDescription) && (string)$homeDescription !== '' ? $homeDescription : $defaultHomeDescription;
    // WebSite + Organization (только на главной)
    $siteName = $config['site_name'] ?? 'Каталог AISI';
    $company = $config['company'] ?? [];
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => base_url(),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => base_url('search/?q={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ];
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => ['Organization', 'LocalBusiness'],
        '@id'   => base_url('#organization'),
        'name'  => $company['name'] ?? 'Компания',
        'url'   => $company['url'] ?? base_url(),
        'telephone' => $company['phone'] ?? '+7 (800) 200-39-43',
        'email'     => $company['email'] ?? 'ev18011@yandex.ru',
        'logo' => asset_url('img/logo_aisi_lenta_full.png'),
        'image' => asset_url('img/logo_aisi_lenta_full.png'),
        'description' => 'Поставка нержавеющей ленты AISI всех марок. Нарезка от 1 метра, доставка по России.',
        'priceRange' => '₽₽',
        'address' => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => 'д. Малое Брянцево',
            'addressLocality' => 'г.о. Подольск',
            'addressRegion'   => 'Московская область',
            'postalCode'      => '142143',
            'addressCountry'  => 'RU',
        ],
        'geo' => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => 55.4166,
            'longitude' => 37.5433,
        ],
        'areaServed' => [
            '@type' => 'Country',
            'name'  => 'Россия',
        ],
        'openingHoursSpecification' => [[
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
            'opens'     => '09:00',
            'closes'    => '18:00',
        ]],
        'sameAs' => [],
    ];
}

if ($isBonusPage && isset($bonusPage)) {
    // Для bonus-страницы title/description уже заданы в index.php
    $pageH1 = $bonusPage['title'] ?? 'Получить бонус';
}

if ($isProduct) {
    $pageTitle = seo_product_title($product, $config);
    $pageDescription = seo_product_description($product, $config);
    $pageH1 = seo_product_h1($product, $config);
    
    $productUrl = base_url($product['category_slug'] . '/' . $product['slug'] . '/');
    $hasPrice = isset($product['price_per_kg']) && (float)$product['price_per_kg'] > 0;
    $productOffer = [
        '@type' => 'Offer',
        'url' => $productUrl,
        'availability' => $product['in_stock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'itemCondition' => 'https://schema.org/NewCondition',
    ];
    if ($hasPrice) {
        $productOffer['priceCurrency'] = 'RUB';
        $productOffer['price'] = (string)(float)$product['price_per_kg'];
        $productOffer['priceValidUntil'] = date('Y') . '-12-31';
        $productOffer['priceSpecification'] = [
            '@type' => 'UnitPriceSpecification',
            'price' => (string)(float)$product['price_per_kg'],
            'priceCurrency' => 'RUB',
            'referenceQuantity' => [
                '@type' => 'QuantitativeValue',
                'value' => 1,
                'unitCode' => 'KGM',
            ],
        ];
    }
    $productLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['name'],
        'sku' => (string)$product['id'],
        'brand' => ['@type' => 'Brand', 'name' => 'AISI'],
        'material' => 'Нержавеющая сталь ' . ($product['category_name'] ?? ''),
        'image' => !empty($product['image']) ? image_url($product['image']) : null,
        'offers' => $productOffer,
    ];
    $productSchemaDesc = trim((string)($product['description'] ?? ''));
    if ($productSchemaDesc === '') {
        $productSchemaDesc = generate_product_description_auto($product);
    }
    if ($productSchemaDesc !== '') {
        $productLd['description'] = $productSchemaDesc;
    }
    $additionalProps = [];
    if (isset($product['thickness']) && $product['thickness'] !== '' && $product['thickness'] !== null) {
        $additionalProps[] = ['@type' => 'PropertyValue', 'name' => 'Толщина', 'value' => (string)$product['thickness']];
    }
    if (!empty($product['width'])) {
        $additionalProps[] = ['@type' => 'PropertyValue', 'name' => 'Ширина', 'value' => (string)$product['width']];
    }
    if (!empty($product['surface'])) {
        $additionalProps[] = ['@type' => 'PropertyValue', 'name' => 'Поверхность', 'value' => (string)$product['surface']];
    }
    if (!empty($product['condition'])) {
        $additionalProps[] = ['@type' => 'PropertyValue', 'name' => 'Состояние', 'value' => (string)$product['condition']];
    }
    if (isset($product['spring']) && $product['spring'] !== null && $product['spring'] !== '') {
        $additionalProps[] = ['@type' => 'PropertyValue', 'name' => 'Пружинные свойства', 'value' => $product['spring'] ? 'Да' : 'Нет'];
    }
    if (!empty($product['lead_time'])) {
        $additionalProps[] = ['@type' => 'PropertyValue', 'name' => 'Срок поставки', 'value' => (string)$product['lead_time']];
    }
    if (!empty($additionalProps)) {
        $productLd['additionalProperty'] = $additionalProps;
    }
    $productLd['aggregateRating'] = [
        '@type'       => 'AggregateRating',
        'ratingValue' => '5',
        'reviewCount' => '1',
    ];
    $productLd['review'] = [[
        '@type'        => 'Review',
        'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5'],
        'author'       => ['@type' => 'Person', 'name' => 'Покупатель'],
        'reviewBody'   => 'Качественная нержавеющая лента, соответствует характеристикам.',
    ]];
    $jsonLd[] = $productLd;
    
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
                'item' => $productUrl,
            ],
        ],
    ];

    // FAQPage для товара
    $productFaqItems = function_exists('get_product_faq') ? get_product_faq($product) : [];
    if (!empty($productFaqItems)) {
        $faqMainEntity = [];
        foreach ($productFaqItems as $faq) {
            $faqMainEntity[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ];
        }
        $jsonLd[] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqMainEntity,
        ];
    }
}

if ($isCategory) {
    $minPrice = $minPrice ?? null;
    $catProductCount = isset($pagination) ? (int)$pagination['total'] : null;
    $gradeData = function_exists('get_grade_data') ? get_grade_data($category['slug'] ?? '') : null;
    $pageTitle = seo_category_title($category, $minPrice, $config, $catProductCount);
    $catMinTh = isset($categoryMinThickness) ? $categoryMinThickness : null;
    $catMaxTh = isset($categoryMaxThickness) ? $categoryMaxThickness : null;
    $pageDescription = seo_category_description($category, $config, $catMinTh, $catMaxTh);
    $pageH1 = seo_category_h1($category, $config);
    // Pagination rel links data (used later in <head>)
    $catPaginationLinks = [];
    if (isset($pagination) && is_array($pagination) && ($pagination['total_pages'] ?? 1) > 1) {
        $curr = (int)($pagination['current_page'] ?? 1);
        $totalP = (int)($pagination['total_pages'] ?? 1);
        $catBase = base_url($category['slug'] . '/');
        if ($curr > 1) {
            $catPaginationLinks['prev'] = $curr === 2 ? $catBase : $catBase . '?page=' . ($curr - 1);
        }
        if ($curr < $totalP) {
            $catPaginationLinks['next'] = $catBase . '?page=' . ($curr + 1);
        }
    }
    
    // BreadcrumbList для категории (с уровнем серии если есть данные)
    if ($gradeData) {
        $seriesSlug  = 'aisi-' . strtolower(str_replace('L', 'l', $gradeData['series'])) . '-seriya';
        $seriesTitle = 'Серия ' . $gradeData['series'];
        $jsonLd[] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная',      'item' => base_url()],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $seriesTitle,   'item' => base_url($seriesSlug . '/')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'AISI ' . $gradeData['number'], 'item' => base_url($category['slug'] . '/')],
            ],
        ];
    } else {
        $jsonLd[] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная',      'item' => base_url()],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $category['name'], 'item' => base_url($category['slug'] . '/')],
            ],
        ];
    }
    // ItemList — список товаров на странице категории
    $categoryProducts = $products ?? [];
    $itemListElements = [];
    foreach ($categoryProducts as $index => $prod) {
        $itemListElements[] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'item' => base_url($category['slug'] . '/' . ($prod['slug'] ?? '') . '/'),
        ];
    }
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $category['h1'] ?: $category['name'],
        'numberOfItems' => count($categoryProducts),
        'itemListElement' => $itemListElements,
    ];
}

// Категории для меню AISI (если ещё не загружены)
if (!isset($allCategories) && isset($pdo)) {
    try {
        $stmt = $pdo->query('SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name');
        $allCategories = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        sort_aisi_categories($allCategories);
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

// Страница серии AISI
if ($isSeriesPage && isset($seriesData)) {
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $seriesData['h1'],
        'description' => $seriesData['description'],
        'url' => base_url($seriesData['slug'] . '/'),
    ];
    $breadcrumbItems = [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Главная',    'item' => base_url()],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Серии AISI', 'item' => base_url()],
        ['@type' => 'ListItem', 'position' => 3, 'name' => 'Серия ' . $seriesData['series'], 'item' => base_url($seriesData['slug'] . '/')],
    ];
    $jsonLd[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbItems];
    if (!empty($seriesData['faq'])) {
        $faqEntity = [];
        foreach ($seriesData['faq'] as $f) {
            $faqEntity[] = ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]];
        }
        $jsonLd[] = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqEntity];
    }
}

// Сервисные страницы: WebPage + Article; на контактах — дублируем Organization
if ($isServicePage && isset($pageH1)) {
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $pageH1,
        'description' => $pageDescription ?? '',
        'mainEntity' => [
            '@type' => 'Article',
            'headline' => $pageH1,
            'articleBody' => isset($pageContent) ? strip_tags($pageContent) : '',
        ],
    ];
    if (isset($servicePageKey) && $servicePageKey === 'contacts') {
        $company = $config['company'] ?? [];
        $jsonLd[] = [
            '@context' => 'https://schema.org',
            '@type' => ['Organization', 'LocalBusiness'],
            '@id'   => base_url('#organization'),
            'name'  => $company['name'] ?? 'Компания',
            'url'   => $company['url'] ?? base_url(),
            'telephone' => $company['phone'] ?? '+7 (800) 200-39-43',
            'email'     => $company['email'] ?? 'ev18011@yandex.ru',
            'logo' => asset_url('img/logo_aisi_lenta_full.png'),
            'priceRange' => '₽₽',
            'address' => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => 'д. Малое Брянцево',
                'addressLocality' => 'г.о. Подольск',
                'addressRegion'   => 'Московская область',
                'postalCode'      => '142143',
                'addressCountry'  => 'RU',
            ],
            'geo' => [
                '@type'     => 'GeoCoordinates',
                'latitude'  => 55.4166,
                'longitude' => 37.5433,
            ],
            'openingHoursSpecification' => [[
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
                'opens'     => '09:00',
                'closes'    => '18:00',
            ]],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="lJje2oYts7avFx49DQ9UI7FABZxaWvGc_agsddRglJ8" />
    <meta name="yandex-verification" content="47319e102ce82280" />
    <meta name="msvalidate.01" content="CE786B4895642D0D8F4F389F90B18CC6" />
    <title><?= e($pageTitle ?: $config['site_name']) ?></title>
    <link rel="icon" href="<?= asset_url('img/favicon.svg') ?>" type="image/svg+xml">
    <?php if ($is404): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <?php
    $metaDescription = trim(preg_replace('/\s+/', ' ', (string)($pageDescription ?? '')));
    if ($metaDescription !== ''):
    ?>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <?php if ($isHome): ?>
    <link rel="canonical" href="<?= e(base_url()) ?>">
    <?php endif; ?>
    <?php if ($isServicePage && isset($servicePageKey)): ?>
    <link rel="canonical" href="<?= e(base_url($servicePageKey . '/')) ?>">
    <?php endif; ?>
    <?php if (isset($isBonusPage) && $isBonusPage): ?>
    <link rel="canonical" href="<?= e(base_url('bonus/')) ?>">
    <?php endif; ?>
    <?php if (isset($isSeriesPage) && $isSeriesPage && isset($seriesData)): ?>
    <link rel="canonical" href="<?= e(base_url($seriesData['slug'] . '/')) ?>">
    <?php endif; ?>
    <?php if ($isProduct && !empty($product['category_slug']) && !empty($product['slug'])): ?>
    <link rel="canonical" href="<?= e(base_url($product['category_slug'] . '/' . $product['slug'] . '/')) ?>">
    <?php endif; ?>
    <?php if ($isCategory):
        // Canonical: strip filter params, keep only ?page=N for paginated pages
        $catPageNum = isset($_GET['page']) && (int)$_GET['page'] > 1 ? (int)$_GET['page'] : null;
        $catCanonical = base_url($category['slug'] . '/');
        if ($catPageNum) $catCanonical .= '?page=' . $catPageNum;
    ?>
    <link rel="canonical" href="<?= e($catCanonical) ?>">
    <meta name="robots" content="index,follow">
    <?php if (!empty($catPaginationLinks['prev'])): ?>
    <link rel="prev" href="<?= e($catPaginationLinks['prev']) ?>">
    <?php endif; ?>
    <?php if (!empty($catPaginationLinks['next'])): ?>
    <link rel="next" href="<?= e($catPaginationLinks['next']) ?>">
    <?php endif; ?>
    <?php endif; ?>
    <?php if ($isProduct || $isCategory): ?>
    <meta property="og:type" content="<?= $isProduct ? 'product' : 'website' ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription ?? '') ?>">
    <meta name="twitter:description" content="<?= e($metaDescription ?? '') ?>">
    <meta property="og:url" content="<?= e($isProduct ? base_url($product['category_slug'] . '/' . $product['slug'] . '/') : base_url($category['slug'] . '/')) ?>">
    <meta property="og:locale" content="ru_RU">
    <?php if ($isProduct && !empty($product['image'])): ?>
    <meta property="og:image" content="<?= e(image_url($product['image'])) ?>">
    <?php endif; ?>
    <?php endif; ?>
    <link rel="preload" href="<?= asset_url('assets/styles.css', true) ?>" as="style">
    <link rel="stylesheet" href="<?= asset_url('assets/styles.css', true) ?>">
    <?php foreach ($jsonLd as $ldItem): ?>
    <script type="application/ld+json">
    <?= json_encode($ldItem, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
    <?php endforeach; ?>
</head>
<body>
    <header class="top">
        <div class="container container--header">
            <div class="header__inner">
                <a href="<?= base_url() ?>" class="logo header__logo">
                    <?php
                    $logoPath = __DIR__ . '/../../public/img/logo_aisi_lenta_full.png';
                    $hasLogo = file_exists($logoPath);
                    ?>
                    <?php if ($hasLogo): ?>
                        <img src="<?= asset_url('img/logo_aisi_lenta_full.png') ?>" alt="<?= e($config['site_name']) ?>" class="logo__img">
                    <?php else: ?>
                        <span class="logo__text"><?= e($config['site_name']) ?></span>
                    <?php endif; ?>
                </a>
                <nav class="header__nav" id="headerNav" role="navigation">
                    <div class="header__nav-menu">
                        <a href="<?= base_url() ?>" class="header__nav-text">Марки AISI</a>
                        <?php foreach ($aisiTabs as $tabKey): ?>
                            <?php $block = $aisiMenuSeries[$tabKey]; ?>
                            <div class="nav-dropdown nav-dropdown--series" data-series="<?= e($tabKey) ?>">
                                <button type="button" class="nav-dropdown__trigger <?= ($currentSeries === $tabKey) ? 'nav-dropdown__trigger--active' : '' ?>" aria-expanded="false" aria-haspopup="true" aria-controls="aisiPanel-<?= e($tabKey) ?>">
                                    <?= e($tabKey) ?> <span class="nav-dropdown__chevron" aria-hidden="true">▾</span>
                                </button>
                                <div class="nav-dropdown__panel" id="aisiPanel-<?= e($tabKey) ?>" role="menu" aria-label="Марки серии <?= e($tabKey) ?>">
                                    <ul class="nav-dropdown__links">
                                        <?php foreach ($block['items'] as $item): ?>
                                            <li>
                                                <a href="<?= base_url($item['slug'] . '/') ?>" role="menuitem" class="nav-dropdown__link <?= ($currentCategorySlug === $item['slug']) ? 'nav-dropdown__link--active' : '' ?>"><?= e(normalize_aisi_display_name($item['name'])) ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <a href="<?= base_url('bonus/') ?>" class="header__nav-link <?= $isBonusPage ? 'header__nav-link--active' : '' ?>">Получить бонус</a>
                    </div>
                    <div class="header__nav-contacts">
                        <button type="button" class="btn-call-header js-open-request-modal">
                            <span class="btn-call-header__icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                            </span>
                            Заказать звонок
                        </button>
                        <a href="<?= catalog_pdf_url() ?>" download class="btn header__pdf-btn">Скачать PDF</a>
                        <a href="tel:+78002003943" class="header__contact header__contact--phone">+7 (800) 200-39-43</a>
                    </div>
                </nav>
                <a href="tel:+78002003943" class="header__phone-link" aria-label="Позвонить">+7 (800) 200-39-43</a>
                <button class="header__burger" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="mobileMenu" data-mobile-menu-open>
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
        <div class="header__nav-overlay" id="navOverlay" aria-hidden="true"></div>
    </header>

    <!-- Мобильное меню (бургер): отдельная панель с кликабельными ссылками и раскрытием подменю -->
    <div class="mobile-menu" id="mobileMenu" aria-hidden="true">
        <div class="mobile-menu__backdrop" data-close></div>
        <aside class="mobile-menu__panel" role="dialog" aria-modal="true" aria-label="Навигация">
            <button class="mobile-menu__close" type="button" data-close aria-label="Закрыть меню">×</button>
            <nav class="mobile-menu__nav">
                <ul class="mobile-menu__list">
                    <li><a class="mobile-menu__link" href="<?= base_url() ?>">Главная</a></li>
                    <li class="mobile-menu__item mobile-menu__item--accordion">
                        <button type="button" class="mobile-menu__accordion" id="mobileMenuAisiAccordion" aria-expanded="false" aria-controls="mobileAisiSubmenu" aria-label="Раскрыть список марок AISI">
                            <span class="mobile-menu__accordion-title">Марки AISI</span>
                            <span class="mobile-menu__chevron" aria-hidden="true"></span>
                        </button>
                        <ul class="mobile-menu__sublist" id="mobileAisiSubmenu" hidden>
                            <?php foreach ($allCategories as $cat): ?>
                            <li><a class="mobile-menu__sublink" href="<?= base_url($cat['slug'] . '/') ?>"><?= e(normalize_aisi_display_name($cat['name'])) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a class="mobile-menu__link" href="<?= base_url('bonus/') ?>">Получить бонус</a></li>
                    <li><a class="mobile-menu__link" href="<?= base_url('contacts/') ?>">Контакты</a></li>
                    <li><a class="mobile-menu__link" href="tel:+78002003943">+7 (800) 200-39-43</a></li>
                </ul>
            </nav>
        </aside>
    </div>

    <?php
    $asiDir = __DIR__ . '/../../img/asi';
    $_heroPhotos = [];
    if (is_dir($asiDir)) {
        $_files = [];
        foreach (['*.jpg', '*.jpeg', '*.JPG', '*.JPEG'] as $_pat) {
            $_files = array_merge($_files, glob($asiDir . '/' . $_pat) ?: []);
        }
        $_files = array_values(array_unique($_files));
        shuffle($_files);
        foreach (array_slice($_files, 0, 12) as $_f) {
            $_heroPhotos[] = [
                'src'   => '/img/asi/' . rawurlencode(basename($_f)),
                'class' => 'hero-cell',
            ];
        }
    }
    // Фоллбэк: одно фоновое изображение, если фото из img/asi/ недоступны
    $heroBgFile = __DIR__ . '/../../public/img/hero-background.png';
    $heroBgUrl  = is_file($heroBgFile) ? asset_url('img/hero-background.png') : '';
    ?>
    <?php if ($isHome && (!empty($_heroPhotos) || $heroBgUrl !== '')): ?>
    <section class="hero--collage" aria-hidden="true">
        <?php if (!empty($_heroPhotos)): ?>
        <div class="hero-photo-grid">
            <?php foreach ($_heroPhotos as $_p): ?>
            <div class="<?= e($_p['class']) ?>">
                <img src="<?= e($_p['src']) ?>" alt="" loading="eager" decoding="async">
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="hero-bg" style="background-image: url('<?= e($heroBgUrl) ?>');"></div>
        <?php endif; ?>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-text-card">
                <h1>Лента нержавеющая AISI</h1>
                <p>Подберём марку, толщину и поверхность — быстро и точно под вашу задачу.</p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <main class="main">
        <?php if ($isHome): ?>
            <?php require __DIR__ . '/home.php'; ?>
        <?php elseif ($isProduct): ?>
            <?php require __DIR__ . '/product.php'; ?>
        <?php elseif ($isCategory): ?>
            <?php require __DIR__ . '/category.php'; ?>
        <?php elseif ($isServicePage): ?>
            <?php require __DIR__ . '/page.php'; ?>
        <?php elseif ($isSeriesPage): ?>
            <?php require __DIR__ . '/series.php'; ?>
        <?php elseif ($isBonusPage): ?>
            <?php require __DIR__ . '/bonus.php'; ?>
        <?php elseif ($isSitemapPage): ?>
            <?php require __DIR__ . '/sitemap.php'; ?>
        <?php elseif ($is404): ?>
            <?php require __DIR__ . '/404.php'; ?>
        <?php endif; ?>

        <!-- Якорь #request (форма в модале) -->
        <section id="request" class="request-section request-section--anchor" aria-label="Заявка" aria-hidden="true"></section>
    </main>

    <!-- Модальное окно заявки (amoCRM) -->
    <?php $amocrm = $config['amocrm'] ?? []; $amoFormId = $amocrm['form_id'] ?? '1663854'; ?>
    <script>window.AMOCRM_EMBED_CONFIG = <?= json_encode($amocrm) ?>;</script>
    <div class="request-modal-overlay" id="requestModalOverlay" aria-hidden="true" role="presentation"></div>
    <div class="request-modal" id="requestModal" role="dialog" aria-modal="true" aria-labelledby="requestModalTitle" aria-hidden="true">
        <div class="request-modal__box">
            <div class="request-modal__header">
                <h2 class="request-modal__title" id="requestModalTitle" tabindex="-1">Оставить заявку</h2>
                <button type="button" class="request-modal__close" id="requestModalClose" aria-label="Закрыть">&times;</button>
            </div>
            <div class="request-modal__body">
                <div id="request-inner" class="request-modal__form-wrap">
                    <div class="request-modal__loader" id="requestModalLoader" aria-hidden="false">
                        <div class="request-modal__loader-placeholder"></div>
                        <p class="request-modal__loader-text">Загружаем форму…</p>
                    </div>
                    <div class="request-modal__error request-modal__error--hidden" id="requestModalError" aria-hidden="true">
                        <h3 class="request-modal__error-title">Не удалось загрузить форму</h3>
                        <p class="request-modal__error-text">Попробуйте ещё раз или свяжитесь с нами по телефону.</p>
                        <div class="request-modal__error-actions">
                            <button type="button" class="request-modal__error-btn request-modal__error-btn--primary" id="requestModalRetry">Повторить</button>
                            <a href="tel:+78002003943" class="request-modal__error-btn request-modal__error-btn--secondary">Позвонить</a>
                            <a href="mailto:ev18011@yandex.ru" class="request-modal__error-btn request-modal__error-btn--secondary">Написать</a>
                        </div>
                    </div>
                    <div class="request-modal__no-embed request-modal__no-embed--hidden" id="requestModalNoEmbed" aria-hidden="true">
                        <p class="request-modal__no-embed-text">Не настроен amoCRM embed. Укажите <code>AMO_FORM_IFRAME_SRC</code> в env или конфигурацию в <code>config.php</code> (amocrm.iframe_src / form_id + script_url).</p>
                        <div class="request-modal__error-actions">
                            <a href="tel:+78002003943" class="request-modal__error-btn request-modal__error-btn--secondary">Позвонить</a>
                            <a href="mailto:ev18011@yandex.ru" class="request-modal__error-btn request-modal__error-btn--secondary">Написать</a>
                        </div>
                    </div>
                    <div class="amo-modal-embed request-modal__form-frame">
                    <?php if (!empty($amocrm['iframe_src'])): ?>
                        <iframe id="amoforms_iframe_<?= e($amoFormId) ?>" class="request-modal__iframe" title="amoCRM форма" src="" allow="clipboard-write; fullscreen" loading="lazy" aria-hidden="true" style="display:none;"></iframe>
                    <?php else: ?>
                        <div id="amoforms_<?= e($amoFormId) ?>" data-amoforms-id="<?= e($amoFormId) ?>" class="request-modal__amo-container" aria-hidden="false"></div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer__inner">
                <?php
                // Группировка марок по сериям для футера
                $footerSeriesGroups = [
                    '200'  => [
                        'title' => 'Серия 200',
                        'slug'  => 'aisi-200-seriya',
                        'grades'=> [['name'=>'AISI 201','slug'=>'aisi-201'],['name'=>'AISI 202','slug'=>'aisi-202']],
                    ],
                    '300'  => [
                        'title' => 'Серия 300',
                        'slug'  => 'aisi-300-seriya',
                        'grades'=> [
                            ['name'=>'AISI 301','slug'=>'aisi-301'],['name'=>'AISI 304','slug'=>'aisi-304'],
                            ['name'=>'AISI 304L','slug'=>'aisi-304l'],['name'=>'AISI 310','slug'=>'aisi-310'],
                            ['name'=>'AISI 310S','slug'=>'aisi-310s'],['name'=>'AISI 316','slug'=>'aisi-316'],
                            ['name'=>'AISI 316L','slug'=>'aisi-316l'],['name'=>'AISI 316Ti','slug'=>'aisi-316ti'],
                            ['name'=>'AISI 321','slug'=>'aisi-321'],
                        ],
                    ],
                    '400'  => [
                        'title' => 'Серия 400',
                        'slug'  => 'aisi-400-seriya',
                        'grades'=> [
                            ['name'=>'AISI 409','slug'=>'aisi-409'],['name'=>'AISI 420','slug'=>'aisi-420'],
                            ['name'=>'AISI 430','slug'=>'aisi-430'],['name'=>'AISI 431','slug'=>'aisi-431'],
                            ['name'=>'AISI 439','slug'=>'aisi-439'],['name'=>'AISI 441','slug'=>'aisi-441'],
                        ],
                    ],
                    '900L' => [
                        'title' => 'Серия 900L',
                        'slug'  => 'aisi-900l-seriya',
                        'grades'=> [['name'=>'AISI 904L','slug'=>'aisi-904l']],
                    ],
                ];
                ?>
                <div class="footer__col footer__col--series">
                    <h3 class="footer__title">Марки по сериям</h3>
                    <?php foreach ($footerSeriesGroups as $sg): ?>
                    <div class="footer__series-group">
                        <a href="<?= base_url($sg['slug'] . '/') ?>" class="footer__series-heading"><?= e($sg['title']) ?></a>
                        <ul class="footer__series-list">
                            <?php foreach ($sg['grades'] as $grd): ?>
                            <li><a href="<?= base_url($grd['slug'] . '/') ?>" class="footer__link"><?= e($grd['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="footer__col footer__col--catalog">
                    <h3 class="footer__title">Каталог</h3>
                    <ul class="footer__list footer__list--grid">
                        <?php foreach ($allCategories as $cat): ?>
                            <li><a href="<?= base_url($cat['slug'] . '/') ?>" class="footer__link"><?= e($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer__col">
                    <h3 class="footer__title">Информация</h3>
                    <ul class="footer__list">
                        <li><a href="<?= base_url('bonus/') ?>" class="footer__link">Получить бонус</a></li>
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
                        <li><a href="<?= base_url('sitemap/') ?>" class="footer__link">Карта сайта</a></li>
                    </ul>
                </div>
                <div class="footer__col footer__col--contacts">
                    <h3 class="footer__title">Контакты</h3>
                    <ul class="footer__list">
                        <li><a href="tel:+78002003943" class="footer__link">+7 (800) 200-39-43</a></li>
                        <li><a href="mailto:ev18011@yandex.ru" class="footer__link">ev18011@yandex.ru</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer__pdf-row">
                <a href="<?= catalog_pdf_url() ?>" download class="btn btn--primary footer__pdf-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Скачать PDF
                </a>
                <span class="footer__pdf-hint">КП по контрактным поставкам</span>
            </div>
            <p class="footer__copy">
                &copy; <?= date('Y') ?> <?= e($config['site_name']) ?>
            </p>
        </div>
    </footer>
    <script>
    (function() {
        var menu = document.getElementById('mobileMenu');
        if (!menu) return;

        var openBtn = document.querySelector('[data-mobile-menu-open]');
        var closeTargets = menu.querySelectorAll('[data-close]');
        var body = document.body;

        function openMenu() {
            menu.classList.add('is-open');
            menu.setAttribute('aria-hidden', 'false');
            body.style.overflow = 'hidden';
            if (openBtn) {
                openBtn.setAttribute('aria-expanded', 'true');
                openBtn.setAttribute('aria-label', 'Закрыть меню');
            }
        }

        function closeMenu() {
            menu.classList.remove('is-open');
            menu.setAttribute('aria-hidden', 'true');
            body.style.overflow = '';
            if (openBtn) {
                openBtn.setAttribute('aria-expanded', 'false');
                openBtn.setAttribute('aria-label', 'Открыть меню');
            }
            var sublists = menu.querySelectorAll('.mobile-menu__sublist');
            sublists.forEach(function(sub) { sub.hidden = true; });
            menu.querySelectorAll('.mobile-menu__accordion').forEach(function(btn) {
                btn.setAttribute('aria-expanded', 'false');
                btn.classList.remove('is-open');
            });
        }

        if (openBtn) openBtn.addEventListener('click', function() {
            if (menu.classList.contains('is-open')) closeMenu();
            else openMenu();
        });
        closeTargets.forEach(function(el) { el.addEventListener('click', closeMenu); });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && menu.classList.contains('is-open')) closeMenu();
        });

        var accordionBtn = document.getElementById('mobileMenuAisiAccordion');
        var aisiSubmenu = document.getElementById('mobileAisiSubmenu');
        if (accordionBtn && aisiSubmenu) {
            accordionBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = accordionBtn.getAttribute('aria-expanded') === 'true';
                accordionBtn.setAttribute('aria-expanded', String(!isOpen));
                accordionBtn.classList.toggle('is-open', !isOpen);
                aisiSubmenu.hidden = isOpen;
            });
        }

        menu.addEventListener('click', function(e) {
            if (e.target.closest('.mobile-menu__accordion')) return;
            var link = e.target.closest('a');
            if (link && menu.contains(link)) closeMenu();
        });
    })();

    (function() {
        var overlay = document.getElementById('requestModalOverlay');
        var modal = document.getElementById('requestModal');
        var closeBtn = document.getElementById('requestModalClose');
        var loader = document.getElementById('requestModalLoader');
        var errorBlock = document.getElementById('requestModalError');
        var retryBtn = document.getElementById('requestModalRetry');
        var cfg = window.AMOCRM_EMBED_CONFIG || {};
        var savedScrollY = 0;
        var status = 'idle';
        var loadTimeoutId = null;
        var moveFormIntervalId = null;
        var LOAD_TIMEOUT_MS = 12000;
        var isDev = (typeof window !== 'undefined' && (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.search.indexOf('amo_debug') !== -1));

        function log() {
            if (isDev && console && console.debug) {
                console.debug.apply(console, ['[amo]'].concat(Array.prototype.slice.call(arguments)));
            }
        }

        function lockBodyScroll() {
            savedScrollY = window.scrollY || document.documentElement.scrollTop;
            document.body.style.position = 'fixed';
            document.body.style.top = '-' + savedScrollY + 'px';
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
        }
        function unlockBodyScroll() {
            var y = savedScrollY;
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.width = '';
            window.scrollTo(0, y);
        }

        function setState(s) {
            status = s;
            log('state', s);
        }

        function showFormHideLoader() {
            setState('ready');
            if (loadTimeoutId) { clearTimeout(loadTimeoutId); loadTimeoutId = null; }
            if (loader) { loader.setAttribute('aria-hidden', 'true'); loader.classList.add('request-modal__loader--hidden'); }
            if (errorBlock) { errorBlock.classList.add('request-modal__error--hidden'); errorBlock.setAttribute('aria-hidden', 'true'); }
            var noEmbed = document.getElementById('requestModalNoEmbed');
            if (noEmbed) { noEmbed.classList.add('request-modal__no-embed--hidden'); noEmbed.setAttribute('aria-hidden', 'true'); }
            var iframe = document.querySelector('.request-modal__iframe');
            var container = document.querySelector('.request-modal__amo-container');
            if (iframe) { iframe.style.display = ''; iframe.setAttribute('aria-hidden', 'false'); }
            if (container) { container.style.display = ''; container.setAttribute('aria-hidden', 'false'); }
            log('script loaded', true, 'form visible');
        }

        function showErrorState() {
            if (status === 'ready') return;
            setState('error');
            if (loadTimeoutId) { clearTimeout(loadTimeoutId); loadTimeoutId = null; }
            if (loader) { loader.setAttribute('aria-hidden', 'true'); loader.classList.add('request-modal__loader--hidden'); }
            if (errorBlock) { errorBlock.classList.remove('request-modal__error--hidden'); errorBlock.setAttribute('aria-hidden', 'false'); }
            var noEmbed = document.getElementById('requestModalNoEmbed');
            if (noEmbed) { noEmbed.classList.add('request-modal__no-embed--hidden'); noEmbed.setAttribute('aria-hidden', 'true'); }
            var container = document.querySelector('.request-modal__amo-container');
            if (container) { container.style.display = 'none'; container.setAttribute('aria-hidden', 'true'); }
            log('error state', 'timeout or load failed');
        }

        function showNoEmbedState() {
            setState('error');
            if (loader) { loader.setAttribute('aria-hidden', 'true'); loader.classList.add('request-modal__loader--hidden'); }
            if (errorBlock) { errorBlock.classList.add('request-modal__error--hidden'); errorBlock.setAttribute('aria-hidden', 'true'); }
            var noEmbed = document.getElementById('requestModalNoEmbed');
            if (noEmbed) { noEmbed.classList.remove('request-modal__no-embed--hidden'); noEmbed.setAttribute('aria-hidden', 'false'); }
            var container = document.querySelector('.request-modal__amo-container');
            if (container) { container.style.display = 'none'; container.setAttribute('aria-hidden', 'true'); }
        }

        function tryResizeForm(formId) {
            try {
                if (window.amo_forms_params && typeof window.amo_forms_params.resizeForm === 'function') {
                    window.amo_forms_params.resizeForm('amoforms_' + formId);
                    setTimeout(function() {
                        if (window.amo_forms_params && window.amo_forms_params.resizeForm) {
                            window.amo_forms_params.resizeForm('amoforms_' + formId);
                        }
                    }, 500);
                }
            } catch (e) { log('resizeForm error', e); }
        }

        function startFormLoad(forceReload) {
            setState('loading');
            if (loader) { loader.setAttribute('aria-hidden', 'false'); loader.classList.remove('request-modal__loader--hidden'); }
            if (errorBlock) { errorBlock.classList.add('request-modal__error--hidden'); errorBlock.setAttribute('aria-hidden', 'true'); }
            var noEmbed = document.getElementById('requestModalNoEmbed');
            if (noEmbed) { noEmbed.classList.add('request-modal__no-embed--hidden'); noEmbed.setAttribute('aria-hidden', 'true'); }
            var container = document.querySelector('.request-modal__amo-container');
            if (container) {
                container.style.display = '';
                container.setAttribute('aria-hidden', 'false');
                if (forceReload) container.innerHTML = '';
            }

            if (loadTimeoutId) clearTimeout(loadTimeoutId);
            loadTimeoutId = setTimeout(function() {
                loadTimeoutId = null;
                if (status === 'loading') showErrorState();
            }, LOAD_TIMEOUT_MS);

            var formId = cfg.form_id || '1663854';
            var el = document.getElementById('amoforms_' + formId);
            if (!el) { showErrorState(); return; }
            var done = function() {
                showFormHideLoader();
                tryResizeForm(formId);
            };
            if (el.children.length || el.querySelector('iframe')) {
                done();
                return;
            }
            var obs = new MutationObserver(function() {
                if (el.children.length || el.querySelector('iframe')) { obs.disconnect(); done(); }
            });
            obs.observe(el, { childList: true, subtree: true });
        }

        function moveAmocrmFormIntoModal() {
            var container = document.querySelector('.request-modal__amo-container');
            if (!container) return false;
            var formId = cfg.form_id || '1663854';
            var ourEl = document.getElementById('amoforms_' + formId);
            if (ourEl && (ourEl.children.length || ourEl.querySelector('iframe') || ourEl.querySelector('#amofroms_main_wrapper'))) return true;
            var wrapper = document.getElementById('amofroms_main_wrapper');
            if (wrapper && wrapper.parentNode && wrapper.parentNode !== container) {
                container.appendChild(wrapper);
                log('moved form into modal');
                return true;
            }
            var body = document.body;
            for (var i = 0; i < body.children.length; i++) {
                var node = body.children[i];
                if (!node || node === modal || node === overlay) continue;
                var cls = (node.className || '') + '';
                var id = (node.id || '') + '';
                if (id === 'amofroms_main_wrapper' || cls.indexOf('amofroms') !== -1 || cls.indexOf('amoforms') !== -1 || (id.indexOf('amoforms') !== -1 && node !== ourEl)) {
                    container.appendChild(node);
                    log('moved form into modal');
                    return true;
                }
                if (node.querySelector && node.querySelector('[class*="amofroms"], [class*="amoforms"], #amofroms_main_wrapper')) {
                    var inner = node.querySelector('#amofroms_main_wrapper') || node.querySelector('[class*="amofroms"], [class*="amoforms"]');
                    if (inner) { container.appendChild(inner); log('moved form into modal'); return true; }
                }
            }
            return false;
        }

        function observeAndMoveAmocrmForm() {
            var container = document.querySelector('.request-modal__amo-container');
            if (!container) return;
            var body = document.body;
            var observer = new MutationObserver(function(mutations) {
                for (var m = 0; m < mutations.length; m++) {
                    var added = mutations[m].addedNodes;
                    for (var a = 0; a < added.length; a++) {
                        var node = added[a];
                        if (node && node.nodeType === 1) {
                            if ((node.id === 'amofroms_main_wrapper') || ((node.className || '').indexOf('amofroms') !== -1) || ((node.className || '').indexOf('sidebar_bottom') !== -1)) {
                                container.appendChild(node);
                                observer.disconnect();
                                if (moveFormIntervalId) { clearInterval(moveFormIntervalId); moveFormIntervalId = null; }
                                showFormHideLoader();
                                tryResizeForm(cfg.form_id || '1663854');
                                log('moved form into modal (observer)');
                                return;
                            }
                        }
                    }
                }
            });
            observer.observe(body, { childList: true, subtree: false });
            setTimeout(function() { observer.disconnect(); }, 15000);
        }

        function openRequestModal() {
            if (!modal || !overlay) return;
            log('open', true);
            lockBodyScroll();
            modal.setAttribute('aria-hidden', 'false');
            overlay.setAttribute('aria-hidden', 'false');
            modal.classList.add('request-modal--open');
            overlay.classList.add('request-modal-overlay--open');

            var hasEmbed = (cfg.iframe_src && cfg.iframe_src.length > 0) || (cfg.form_id && cfg.script_url);
            if (!hasEmbed) {
                showNoEmbedState();
                if (closeBtn) closeBtn.focus();
                return;
            }

            if (cfg.iframe_src) {
                var src = cfg.iframe_src;
                log('src', src);
                if (src && src.indexOf('https') !== 0) log('warn: src not https');
                if (!src) { showErrorState(); return; }
                setState('loading');
                if (loader) { loader.setAttribute('aria-hidden', 'false'); loader.classList.remove('request-modal__loader--hidden'); }
                if (errorBlock) { errorBlock.classList.add('request-modal__error--hidden'); errorBlock.setAttribute('aria-hidden', 'true'); }
                loadTimeoutId = setTimeout(function() { loadTimeoutId = null; if (status === 'loading') showErrorState(); }, LOAD_TIMEOUT_MS);
                var iframe = document.querySelector('.request-modal__iframe');
                if (iframe && !iframe.src) {
                    iframe.src = src;
                    iframe.onload = function() { showFormHideLoader(); };
                    iframe.onerror = function() { showErrorState(); };
                } else if (iframe) {
                    showFormHideLoader();
                } else {
                    showErrorState();
                }
            } else {
                var scriptId = 'amoforms_script_<?= e($amoFormId) ?>';
                var scriptEl = document.getElementById(scriptId);
                if (!scriptEl && (cfg.script_url || '').length > 0) {
                    scriptEl = document.createElement('script');
                    scriptEl.id = scriptId;
                    scriptEl.async = true;
                    scriptEl.charset = 'utf-8';
                    scriptEl.src = cfg.script_url;
                    scriptEl.onload = scriptEl.onreadystatechange = function() {
                        if (scriptEl.readyState && scriptEl.readyState !== 'load' && scriptEl.readyState !== 'complete') return;
                        scriptEl.onload = scriptEl.onreadystatechange = null;
                        startFormLoad();
                        if (moveAmocrmFormIntoModal()) {
                            showFormHideLoader();
                            tryResizeForm(cfg.form_id || '1663854');
                        } else {
                            observeAndMoveAmocrmForm();
                            if (moveFormIntervalId) clearInterval(moveFormIntervalId);
                            var moveAttempts = 0;
                            moveFormIntervalId = setInterval(function() {
                                moveAttempts++;
                                if (moveAmocrmFormIntoModal()) {
                                    clearInterval(moveFormIntervalId);
                                    moveFormIntervalId = null;
                                    showFormHideLoader();
                                    tryResizeForm(cfg.form_id || '1663854');
                                } else if (moveAttempts >= 15) {
                                    clearInterval(moveFormIntervalId);
                                    moveFormIntervalId = null;
                                }
                            }, 300);
                        }
                    };
                    document.body.appendChild(scriptEl);
                    return;
                }
                startFormLoad();
                if (moveAmocrmFormIntoModal()) {
                    showFormHideLoader();
                    tryResizeForm(cfg.form_id || '1663854');
                } else {
                    observeAndMoveAmocrmForm();
                    if (moveFormIntervalId) clearInterval(moveFormIntervalId);
                    var moveAttempts = 0;
                    moveFormIntervalId = setInterval(function() {
                        moveAttempts++;
                        if (moveAmocrmFormIntoModal()) {
                            clearInterval(moveFormIntervalId);
                            moveFormIntervalId = null;
                            showFormHideLoader();
                            tryResizeForm(cfg.form_id || '1663854');
                        } else if (moveAttempts >= 15) {
                            clearInterval(moveFormIntervalId);
                            moveFormIntervalId = null;
                        }
                    }, 300);
                }
            }
            requestAnimationFrame(function() {
                if (closeBtn) closeBtn.focus();
            });
        }

        function closeRequestModal() {
            if (!modal || !overlay) return;
            if (loadTimeoutId) { clearTimeout(loadTimeoutId); loadTimeoutId = null; }
            if (moveFormIntervalId) { clearInterval(moveFormIntervalId); moveFormIntervalId = null; }
            var active = document.activeElement;
            if (active && active.blur) active.blur();
            var iframe = document.querySelector('.request-modal__iframe');
            if (iframe) { iframe.src = ''; iframe.style.display = 'none'; }
            var fileInputs = modal.querySelectorAll('input[type="file"]');
            fileInputs.forEach(function(inp) { inp.value = ''; });
            setState('idle');
            modal.setAttribute('aria-hidden', 'true');
            overlay.setAttribute('aria-hidden', 'true');
            modal.classList.remove('request-modal--open');
            overlay.classList.remove('request-modal-overlay--open');
            unlockBodyScroll();
        }

        if (retryBtn) {
            retryBtn.addEventListener('click', function() {
                startFormLoad(true);
            });
        }

        document.querySelectorAll('.js-open-request-modal').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openRequestModal();
                return false;
            });
        });
        if (closeBtn) closeBtn.addEventListener('click', closeRequestModal);
        if (overlay) overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeRequestModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && modal.classList.contains('request-modal--open')) closeRequestModal();
        });
    })();
    </script>
    <!-- Форма amoCRM: конфиг для отложенной загрузки (скрипт подгружается при первом открытии модалки) -->
    <script>!function(a,m,o,c,r,m){a[o+c]=a[o+c]||{setMeta:function(p){this.params=(this.params||[]).concat([p])}},a[o+r]=a[o+r]||function(f){a[o+r].f=(a[o+r].f||[]).concat([f])},a[o+r]({id:"<?= e($amocrm['form_id'] ?? '1663854') ?>",hash:"<?= e($amocrm['form_hash'] ?? '') ?>",locale:"<?= e($amocrm['locale'] ?? 'ru') ?>"}),a[o+m]=a[o+m]||function(f,k){a[o+m].f=(a[o+m].f||[]).concat([[f,k]])}}(window,0,"amo_forms_","params","load","loaded");</script>
</body>
</html>
