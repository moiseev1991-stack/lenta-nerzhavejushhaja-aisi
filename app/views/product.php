<?php
$conditionLabels = [
    'soft' => 'Мягкая',
    'hard' => 'Нагартованная',
    'semi_hard' => 'Полугартованная'
];
?>

<?php
ob_start();
?><nav class="breadcrumbs" aria-label="Хлебные крошки">
    <a href="<?= base_url() ?>">Главная</a>
    <span>/</span>
    <a href="<?= base_url($product['category_slug'] . '/') ?>"><?= e($product['category_name']) ?></a>
    <span>/</span>
    <span><?= e($product['name']) ?></span>
</nav><?php
$heroBreadcrumbs = ob_get_clean();
?>
<div class="product-page">
    <div class="container">
        <?php $productBlocksData = require __DIR__ . '/../data/product_blocks.php'; ?>
        <?php
        $stmt = $pdo->query('SELECT slug, name FROM categories WHERE is_active = 1 ORDER BY name');
        $allCategories = $stmt->fetchAll();
        sort_aisi_categories($allCategories);
        ?>
        <div class="product-top">
            <div class="product-top__breadcrumbs"><?= $heroBreadcrumbs ?></div>
            <div class="product-top__chips">
                <?php foreach ($allCategories as $cat): ?>
                    <a href="<?= base_url($cat['slug'] . '/') ?>"
                       class="chip <?= $cat['slug'] === $product['category_slug'] ? 'chip--active' : '' ?>">
                        <?= e(normalize_aisi_display_name($cat['name'])) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Единая карточка товара: фото | центр | коммерческий блок -->
        <div class="product-card-full">
            <div class="product-card-full__photo">
                <?php
                $placeholderUrl = asset_url('img/placeholder-product.svg');
                if ($product['image']): ?>
                    <img src="<?= image_url($product['image']) ?>" alt="<?= e($product['name']) ?>"
                         onerror="this.onerror=null;this.src='<?= e($placeholderUrl) ?>';this.classList.add('product-card-full__placeholder');">
                <?php else: ?>
                    <img src="<?= $placeholderUrl ?>" alt="" class="product-card-full__placeholder">
                <?php endif; ?>
            </div>
            <div class="product-card-full__center">
                <h1 class="product-card-full__name"><?= e($pageH1) ?></h1>
                <div class="product-card-full__price-block">
                    <span class="product-card-full__price"><?= format_price($product['price_per_kg']) ?></span>
                    <span class="product-card-full__stock"><?= $product['in_stock'] ? '✓ В наличии' : 'Под заказ' ?></span>
                </div>
                <table class="product-card-full__specs">
                    <tr><td>Толщина</td><td><?= $product['thickness'] ? e($product['thickness'] . ' мм') : '—' ?></td></tr>
                    <?php if ($product['width']): ?><tr><td>Ширина</td><td><?= e($product['width'] . ' мм') ?></td></tr><?php endif; ?>
                    <tr><td>Состояние</td><td><?= isset($conditionLabels[$product['condition']]) ? e($conditionLabels[$product['condition']]) : '—' ?></td></tr>
                    <tr><td>Пружинные</td><td><?= $product['spring'] ? 'Да' : 'Нет' ?></td></tr>
                    <tr><td>Поверхность</td><td><?= $product['surface'] ? e($product['surface']) : '—' ?></td></tr>
                    <?php if ($product['lead_time']): ?><tr><td>Срок</td><td><?= e($product['lead_time']) ?></td></tr><?php endif; ?>
                </table>
                <div class="product-card-full__cta" id="request">
                    <button type="button" class="btn btn--primary btn--large js-open-request-modal">Запросить счёт</button>
                </div>
            </div>
            <div class="product-card-full__aside">
                <?php include __DIR__ . '/partials/product_sidebar.php'; ?>
            </div>
        </div>

        <?php
        $autoDesc = generate_product_description_compact($product);
        $grade = e($product['category_name'] ?? '');
        $categoryHref = base_url(($product['category_slug'] ?? '') . '/');
        ?>
        <div class="product-desc-compact">
            <p><?= e($autoDesc) ?></p>
            <p><a href="<?= $categoryHref ?>">Все размеры ленты <?= $grade ?> →</a></p>
        </div>

        <div class="product-grid-2x2">
            <?php include __DIR__ . '/partials/product_blocks.php'; ?>
        </div>

        <div class="product-grid-2x2 product-grid-2x2--secondary">
            <?php include __DIR__ . '/partials/product_blocks_extra.php'; ?>
        </div>

        <?php include __DIR__ . '/partials/product_faq.php'; ?>
        <?php include __DIR__ . '/partials/product_links.php'; ?>
    </div>
    <?php include __DIR__ . '/partials/related_products.php'; ?>
</div>
