<?php
$relatedData = $relatedProducts ?? ['items' => [], 'is_popular_fallback' => false];
$items = $relatedData['items'];
if (empty($items)) return;
$sectionTitle = $relatedData['is_popular_fallback'] ? 'Популярные товары' : 'Похожие товары';

// JSON-LD ItemList для блока похожих товаров — даёт Google дополнительный сигнал кросс-линковки.
$relatedItemListElements = [];
foreach ($items as $idx => $rp) {
    $relatedItemListElements[] = [
        '@type'    => 'ListItem',
        'position' => $idx + 1,
        'url'      => base_url(($rp['category_slug'] ?? '') . '/' . ($rp['slug'] ?? '') . '/'),
        'name'     => $rp['name'] ?? '',
    ];
}
$relatedItemListLd = [
    '@context'        => 'https://schema.org',
    '@type'           => 'ItemList',
    'name'            => $sectionTitle,
    'numberOfItems'   => count($items),
    'itemListElement' => $relatedItemListElements,
];
?>
<script type="application/ld+json">
<?= json_encode($relatedItemListLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
<section class="related-products" aria-labelledby="related-products-title">
    <div class="container">
        <h2 class="related-products__title" id="related-products-title"><?= e($sectionTitle) ?></h2>
        <div class="related-products__grid">
            <?php foreach ($items as $rp): ?>
                <a href="<?= base_url($rp['category_slug'] . '/' . $rp['slug'] . '/') ?>" class="related-products__card">
                    <div class="related-products__image">
                        <?php
                        $ph = asset_url('img/placeholder-product.svg');
                        if (!empty($rp['image'])): ?>
                            <img src="<?= image_url($rp['image']) ?>" alt="<?= e($rp['name']) ?>" width="200" height="140" loading="lazy"
                                 onerror="this.onerror=null;this.src='<?= e($ph) ?>';this.classList.add('related-products__placeholder-img');">
                        <?php else: ?>
                            <img src="<?= $ph ?>" alt="" width="200" height="140" loading="lazy" class="related-products__placeholder-img">
                        <?php endif; ?>
                    </div>
                    <div class="related-products__content">
                        <h3 class="related-products__name"><?= e($rp['name']) ?></h3>
                        <div class="related-products__price"><?= format_price($rp['price_per_kg']) ?></div>
                        <span class="related-products__link">Подробнее</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
