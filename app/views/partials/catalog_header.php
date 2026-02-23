<?php
/**
 * Единая шапка каталога: заголовок категории, подкатегории (плашки AISI), преимущества, CTA.
 * Используется на странице категории и на странице товара.
 * Ожидает: $category (slug, name, h1, intro), $allCategories, $minPrice (опционально).
 */
$category = $category ?? null;
$allCategories = $allCategories ?? [];
$minPrice = $minPrice ?? null;
if (!$category) return;
sort_aisi_categories($allCategories);
?>
<section class="category-hero">
    <div class="container">
        <div class="category-hero__inner">
            <div class="category-hero__left">
                <div class="category-hero__header">
                    <?php if (!isset($product)): ?>
                    <h1><?= e($pageH1) ?></h1>
                    <?php else: ?>
                    <p class="category-hero__breadcrumb-title"><?= e($category['name']) ?></p>
                    <?php endif; ?>
                    <?php if (isset($productsCount)): ?>
                    <span class="category-hero__count">Найдено: <?= (int) $productsCount ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($heroBreadcrumbs)): ?>
                <div class="category-hero__breadcrumbs"><?= $heroBreadcrumbs ?></div>
                <?php endif; ?>
                <?php if (!empty($category['intro'])): ?>
                <p class="category-hero__intro" id="categoryIntro"><?= e($category['intro']) ?></p>
                <?php endif; ?>
                <div class="category-hero__chips">
                    <div class="chips" id="chipsContainer">
                        <?php foreach ($allCategories as $cat): ?>
                            <a href="<?= base_url($cat['slug'] . '/') ?>"
                               class="chip <?= $cat['slug'] === $category['slug'] ? 'chip--active' : '' ?>">
                                <?= e(normalize_aisi_display_name($cat['name'])) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <aside class="category-hero__right">
                <div class="hero-card hero-card--compact">
                    <?php if ($minPrice !== null && $minPrice !== '' && (float)$minPrice > 0): ?>
                    <div class="hero-card__price">от <?= format_price($minPrice) ?></div>
                    <?php endif; ?>
                    <button type="button" class="btn btn--primary hero-card__cta js-open-request-modal">Оставить заявку</button>
                    <p class="hero-card__subtitle">Ответим за 15 минут, подберём марку и размеры</p>
                    <p class="hero-card__pdf" style="margin-top: 12px;"><a class="btn btn--primary btn--block" href="<?= e(base_url('files/metallinvest_lenta_shtrips.pdf')) ?>" download>Скачать PDF</a></p>
                </div>
            </aside>
        </div>
        <?php $benefitsVariant = 'category'; include __DIR__ . '/benefits.php'; ?>
    </div>
</section>
