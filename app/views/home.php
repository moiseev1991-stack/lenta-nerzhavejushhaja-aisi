<?php
// Главная страница - показывает товары из разных категорий
?>

<div class="home-page">
    <!-- Hero блок главной -->
    <section class="hero hero--home">
        <div class="container">
            <div class="hero__inner hero__inner--centered">
                <div class="hero__logo">
                    <h1 class="hero__title"><?= e(isset($homeH1) && (string)$homeH1 !== '' ? $homeH1 : 'Лента нержавеющая AISI') ?></h1>
                    <p class="hero__subtitle">Подберём марку, толщину и поверхность — быстро и точно под вашу задачу.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- УТП (инфографика) -->
    <section class="usp">
        <div class="usp__container container">
            <h2 class="usp__title">Почему выбирают нас</h2>
            <div class="usp__track">
                <div class="usp__card">
                    <div class="usp__icon" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <h3 class="usp__h" title="Отматываем от 1 метра">Отматываем от 1 метра</h3>
                    <p class="usp__p">Подберём партию под задачу — без переплаты за лишний объём.</p>
                </div>
                <div class="usp__card">
                    <div class="usp__icon" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5.42 9.42L2 12l3.42 2.58"/><path d="M18.58 9.42L22 12l-3.42 2.58"/><path d="M2 12h20"/><path d="M8 6l4-4 4 4"/><path d="M16 18l-4 4-4-4"/></svg>
                    </div>
                    <h3 class="usp__h" title="Прецизионная резка">Прецизионная резка</h3>
                    <p class="usp__p">Чистая кромка и повторяемость размеров по всей партии.</p>
                </div>
                <div class="usp__card">
                    <div class="usp__icon" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8v14M4 8h16M4 8l4-4M4 8l4 4M20 8l-4-4M20 8l-4 4"/></svg>
                    </div>
                    <h3 class="usp__h" title="Ширина резки от 2,5 мм">Ширина резки от 2,5 мм</h3>
                    <p class="usp__p">Узкая резка под производственные и штамповочные задачи.</p>
                </div>
                <div class="usp__card">
                    <div class="usp__icon" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                    </div>
                    <h3 class="usp__h" title="Толщины от 0,05 до 4 мм">Толщины от 0,05 до 4 мм</h3>
                    <p class="usp__p">От тонких лент до более толстых — под разные применения.</p>
                </div>
            </div>
            <div class="usp__cta">
                <button type="button" class="usp__cta-btn js-open-request-modal">Подобрать ленту под задачу</button>
                <p class="usp__cta-subtitle">
                    <span class="usp__cta-subtitle-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    </span>
                    <span class="usp__cta-subtitle-text">Быстро подберём марку, толщину и поверхность под вашу задачу</span>
                </p>
            </div>
        </div>
    </section>

    <!-- Категории -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Категории</h2>
            <div class="categories-grid">
                <?php foreach ($allCategories as $cat):
                    $seriesBadge = aisi_series_from_slug($cat['slug']);
                    $showBadge = ($seriesBadge !== 'other');
                ?>
                    <a href="<?= base_url($cat['slug'] . '/') ?>" class="category-card">
                        <?php if ($showBadge): ?><span class="series-badge"><?= e($seriesBadge) ?></span><?php endif; ?>
                        <div class="category-card__name"><?= e(normalize_aisi_display_name($cat['name'])) ?></div>
                        <div class="category-card__link">Смотреть товары →</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if (!empty($homeTextHtml)): ?>
    <section class="home-text">
        <div class="container">
            <div class="home-text__content">
                <?= $homeTextHtml ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- PDF прайс-лист / каталог -->
    <section class="pdf-section">
        <div class="container">
            <div class="pdf-section__inner">
                <div class="pdf-section__header">
                    <h2 class="pdf-section__title">Прайс-лист нержавеющей ленты</h2>
                    <a href="<?= base_url('files/metallinvest_lenta_shtrips.pdf') ?>" download class="btn btn--primary pdf-section__btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Скачать PDF
                    </a>
                </div>
                <div class="pdf-viewer">
                    <iframe
                        src="<?= base_url('files/metallinvest_lenta_shtrips.pdf') ?>#view=FitH"
                        width="100%"
                        height="700"
                        class="pdf-viewer__iframe"
                        loading="lazy"
                        title="Прайс-лист нержавеющей ленты AISI"
                    ></iframe>
                </div>
                <p class="pdf-viewer__fallback">Если PDF не отображается, <a href="<?= base_url('files/metallinvest_lenta_shtrips.pdf') ?>" target="_blank" rel="noopener">откройте в новой вкладке</a>.</p>
                <a href="<?= base_url('files/metallinvest_lenta_shtrips.pdf') ?>" download class="btn btn--primary pdf-viewer__download">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Скачать PDF
                </a>
            </div>
        </div>
    </section>

    <!-- Популярные товары -->
    <section class="products-section">
        <div class="container">
            <h2 class="section-title">Популярные товары</h2>
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <a href="<?= base_url($product['category_slug'] . '/' . $product['slug'] . '/') ?>" class="product-card">
                        <div class="product-card__heart">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                <path d="M10 17.5c-1.5-1.5-5-4-5-7.5a3 3 0 016 0 3 3 0 016 0c0 3.5-3.5 6-5 7.5z"/>
                            </svg>
                        </div>
                        <div class="product-card__image">
                            <?php
                            $ph = base_url('public/img/placeholder-product.svg');
                            if ($product['image']): ?>
                                <img src="<?= base_url($product['image']) ?>" alt="<?= e($product['name']) ?>"
                                     onerror="this.onerror=null;this.src='<?= e($ph) ?>';this.classList.add('product-card__placeholder-img');">
                            <?php else: ?>
                                <img src="<?= $ph ?>" alt="" class="product-card__placeholder-img">
                            <?php endif; ?>
                        </div>
                        <div class="product-card__content">
                            <h3 class="product-card__name"><?= e($product['name']) ?></h3>
                            <div class="product-card__meta">
                                <?php 
                                $meta = [];
                                if ($product['thickness']) $meta[] = $product['thickness'] . ' мм';
                                if ($product['condition'] === 'soft') $meta[] = 'Мягкая';
                                if ($product['condition'] === 'hard') $meta[] = 'Нагартованная';
                                if ($product['condition'] === 'semi_hard') $meta[] = 'Полугартованная';
                                if ($product['surface']) $meta[] = $product['surface'];
                                ?>
                                <?= e(implode(' • ', $meta)) ?>
                            </div>
                            <div class="product-card__footer">
                                <div class="product-card__price"><?= format_price($product['price_per_kg']) ?></div>
                                <div class="product-card__stock">
                                    <?= $product['in_stock'] ? 'В наличии' : 'Под заказ' ?>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
