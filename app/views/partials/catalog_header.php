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
?>
<section class="category-hero">
    <div class="container">
        <div class="category-hero__inner">
            <div class="category-hero__left">
                <div class="category-hero__header">
                    <h1><?= e($category['h1'] ?: $category['name']) ?></h1>
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
                    <span class="chips-label">Подкатегории:</span>
                    <div class="chips" id="chipsContainer">
                        <?php foreach ($allCategories as $cat): ?>
                            <a href="<?= base_url($cat['slug'] . '/') ?>"
                               class="chip <?= $cat['slug'] === $category['slug'] ? 'chip--active' : '' ?>">
                                <?= e($cat['name']) ?>
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
                </div>
            </aside>
        </div>
        <?php $benefitsVariant = 'category'; include __DIR__ . '/benefits.php'; ?>
    </div>
</section>
