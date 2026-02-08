<?php
$servicePages = [
    'contacts' => 'Контакты',
    'delivery' => 'Доставка',
    'payment' => 'Оплата',
    'about' => 'О компании',
    'price' => 'Прайс-лист',
    'privacy-policy' => 'Политика конфиденциальности',
];
$sitemapCategories = $sitemapCategories ?? [];
$sitemapProducts = $sitemapProducts ?? [];
$productsByCategory = [];
foreach ($sitemapProducts as $row) {
    $productsByCategory[$row['category_slug']][] = $row;
}
?>
<div class="sitemap-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= base_url() ?>">Главная</a>
            <span>/</span>
            <span>Карта сайта</span>
        </nav>
        <h1 class="sitemap-page__title"><?= e($pageH1) ?></h1>
        <p class="sitemap-page__intro">Все разделы и страницы сайта в одном месте.</p>

        <section class="sitemap-section" aria-labelledby="sitemap-main">
            <h2 id="sitemap-main" class="sitemap-section__title">Основные разделы</h2>
            <ul class="sitemap-section__list">
                <li><a href="<?= base_url() ?>">Главная</a></li>
                <li><a href="<?= base_url('bonus/') ?>">Получить бонус</a></li>
            </ul>
        </section>

        <section class="sitemap-section" aria-labelledby="sitemap-catalog">
            <h2 id="sitemap-catalog" class="sitemap-section__title">Каталог по маркам AISI</h2>
            <ul class="sitemap-section__list sitemap-section__list--categories">
                <?php foreach ($sitemapCategories as $cat): ?>
                <li class="sitemap-category">
                    <a href="<?= base_url($cat['slug'] . '/') ?>"><?= e(normalize_aisi_display_name($cat['name'])) ?></a>
                    <?php if (!empty($productsByCategory[$cat['slug']])): ?>
                    <ul class="sitemap-products">
                        <?php foreach ($productsByCategory[$cat['slug']] as $p): ?>
                        <li><a href="<?= base_url($cat['slug'] . '/' . $p['product_slug'] . '/') ?>"><?= e($p['product_name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="sitemap-section" aria-labelledby="sitemap-info">
            <h2 id="sitemap-info" class="sitemap-section__title">Информация</h2>
            <ul class="sitemap-section__list">
                <?php foreach ($servicePages as $slug => $label): ?>
                <li><a href="<?= base_url($slug . '/') ?>"><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
</div>
