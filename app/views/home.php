<?php
// Главная страница - показывает товары из разных категорий
?>

<div class="home-page">
    <!-- Hero блок главной -->
    <section class="hero hero--home">
        <div class="container">
            <div class="hero__inner hero__inner--centered">
                <div class="hero__logo">
                    <h1 class="hero__title">Лента нержавеющая AISI</h1>
                    <p class="hero__subtitle">Подберём марку, толщину и поверхность — быстро и точно под вашу задачу.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Категории -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Категории</h2>
            <div class="categories-grid">
                <?php foreach ($allCategories as $cat): ?>
                    <a href="<?= base_url($cat['slug'] . '/') ?>" class="category-card">
                        <div class="category-card__name"><?= e($cat['name']) ?></div>
                        <div class="category-card__link">Смотреть товары →</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Популярные товары -->
    <section class="products-section">
        <div class="container">
            <h2 class="section-title">Популярные товары</h2>
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <a href="<?= base_url('product/' . $product['slug'] . '/') ?>" class="product-card">
                        <div class="product-card__heart">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                <path d="M10 17.5c-1.5-1.5-5-4-5-7.5a3 3 0 016 0 3 3 0 016 0c0 3.5-3.5 6-5 7.5z"/>
                            </svg>
                        </div>
                        <div class="product-card__image">
                            <?php if ($product['image']): ?>
                                <img src="<?= base_url($product['image']) ?>" alt="<?= e($product['name']) ?>">
                            <?php else: ?>
                                <div class="product-card__placeholder">Нет фото</div>
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
