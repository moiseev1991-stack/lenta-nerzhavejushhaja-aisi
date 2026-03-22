<?php
$productBlocksData = $productBlocksData ?? [];
$pagesData = require __DIR__ . '/../../data/pages.php';
$contactsData = $pagesData['contacts'] ?? [];
$branches = $contactsData['branches'] ?? [];
$grade = e($product['category_name'] ?? '');
$specs = function_exists('seo_product_specs') ? seo_product_specs($product) : '';
$gradeSpecs = trim($grade . ' ' . $specs);
$maxCities = (int)($productBlocksData['cities_short'] ?? 7);
$cityNames = array_slice(array_map(function ($b) { return $b['city']; }, $branches), 0, $maxCities);
?>
<section class="product-block-compact">
    <h3 class="product-block-compact__title">Что влияет на стоимость</h3>
    <ul class="product-block-compact__list">
        <?php foreach ($productBlocksData['price_factors'] ?? [] as $f): ?>
            <li><?= e($f) ?></li>
        <?php endforeach; ?>
    </ul>
</section>
<?php if (!empty($cityNames)): ?>
<section class="product-block-compact">
    <h3 class="product-block-compact__title">Наличие и отгрузка</h3>
    <p class="product-block-compact__text"><?= e(implode(', ', $cityNames)) ?> и другие города. Уточняйте наличие на ближайшем складе.</p>
    <a href="<?= base_url('delivery/') ?>" class="product-block-compact__link">Подробнее о доставке →</a>
</section>
<?php endif; ?>
