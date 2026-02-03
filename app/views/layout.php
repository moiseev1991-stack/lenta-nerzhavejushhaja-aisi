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
    $defaultHomeTitle = 'Лента нержавеющая AISI — каталог нержавеющей ленты по маркам';
    $defaultHomeDescription = 'Каталог нержавеющей ленты AISI 200/300/400/900L. Подбор по толщине, ширине, состоянию и поверхности. Отмотка от 1 метра, резка от 2,5 мм.';
    $pageTitle = isset($homeTitle) && (string)$homeTitle !== '' ? $homeTitle : $defaultHomeTitle;
    $pageDescription = isset($homeDescription) && (string)$homeDescription !== '' ? $homeDescription : $defaultHomeDescription;
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
    <?php if ($pageDescription ?? ''): ?>
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
        <div class="container container--header">
            <div class="header__inner">
                <a href="<?= base_url() ?>" class="logo header__logo">
                    <?php
                    $logoPath = __DIR__ . '/../../img/logo_aisi_lenta_full.png';
                    $hasLogo = file_exists($logoPath);
                    ?>
                    <?php if ($hasLogo): ?>
                        <img src="<?= base_url('img/logo_aisi_lenta_full.png') ?>" alt="<?= e($config['site_name']) ?>" class="logo__img">
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
                                                <a href="<?= base_url($item['slug'] . '/') ?>" role="menuitem" class="nav-dropdown__link <?= ($currentCategorySlug === $item['slug']) ? 'nav-dropdown__link--active' : '' ?>"><?= e($item['name']) ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="header__nav-contacts">
                        <a href="tel:+74951060741" class="header__contact header__contact--phone">+7 (495) 106-07-41</a>
                        <a href="mailto:ev18011@yandex.ru" class="header__contact header__contact--email">ev18011@yandex.ru</a>
                        <a href="<?= base_url('admin/login') ?>" class="admin-link">Админ</a>
                    </div>
                </nav>
                <a href="tel:+74951060741" class="header__phone-link" aria-label="Позвонить">+7 (495) 106-07-41</a>
                <button class="header__burger" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="headerNav">
                    <span></span><span></span><span></span>
                </button>
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
                            <a href="tel:+74951060741" class="request-modal__error-btn request-modal__error-btn--secondary">Позвонить</a>
                            <a href="mailto:ev18011@yandex.ru" class="request-modal__error-btn request-modal__error-btn--secondary">Написать</a>
                        </div>
                    </div>
                    <div class="request-modal__no-embed request-modal__no-embed--hidden" id="requestModalNoEmbed" aria-hidden="true">
                        <p class="request-modal__no-embed-text">Не настроен amoCRM embed. Укажите <code>AMO_FORM_IFRAME_SRC</code> в env или конфигурацию в <code>config.php</code> (amocrm.iframe_src / form_id + script_url).</p>
                        <div class="request-modal__error-actions">
                            <a href="tel:+74951060741" class="request-modal__error-btn request-modal__error-btn--secondary">Позвонить</a>
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
    <!-- Форма amoCRM: скрипты при загрузке страницы (как в оригинальном коде вставки) -->
    <script>!function(a,m,o,c,r,m){a[o+c]=a[o+c]||{setMeta:function(p){this.params=(this.params||[]).concat([p])}},a[o+r]=a[o+r]||function(f){a[o+r].f=(a[o+r].f||[]).concat([f])},a[o+r]({id:"<?= e($amocrm['form_id'] ?? '1663854') ?>",hash:"<?= e($amocrm['form_hash'] ?? '') ?>",locale:"<?= e($amocrm['locale'] ?? 'ru') ?>"}),a[o+m]=a[o+m]||function(f,k){a[o+m].f=(a[o+m].f||[]).concat([[f,k]])}}(window,0,"amo_forms_","params","load","loaded");</script>
    <script id="amoforms_script_<?= e($amoFormId) ?>" async="async" charset="utf-8" src="<?= e($amocrm['script_url'] ?? 'https://forms.amocrm.ru/forms/assets/js/amoforms.js?1770113409') ?>"></script>
</body>
</html>
