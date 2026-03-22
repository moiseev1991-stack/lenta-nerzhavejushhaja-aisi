<?php
$productBlocksData = $productBlocksData ?? [];
$grade = e($product['category_name'] ?? '');
$specs = function_exists('seo_product_specs') ? seo_product_specs($product) : '';
$gradeSpecs = trim($grade . ' ' . $specs);
?>
<section class="product-block-compact">
    <h3 class="product-block-compact__title">Доставка и отгрузка</h3>
    <p class="product-block-compact__text"><?= e($productBlocksData['delivery_short'] ?? '') ?></p>
    <a href="<?= base_url('delivery/') ?>" class="product-block-compact__link">Подробнее →</a>
</section>
<section class="product-block-compact">
    <h3 class="product-block-compact__title">Оплата</h3>
    <p class="product-block-compact__text"><?= e($productBlocksData['payment_short'] ?? '') ?></p>
    <a href="<?= base_url('payment/') ?>" class="product-block-compact__link">Подробнее →</a>
</section>
<section class="product-block-compact">
    <h3 class="product-block-compact__title">Контакты и заказ</h3>
    <p class="product-block-compact__text"><?= e($productBlocksData['contacts_short'] ?? '') ?></p>
    <p><a href="tel:+78002003943">+7 (800) 200-39-43</a> · <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a></p>
</section>
<section class="product-block-compact">
    <h3 class="product-block-compact__title">Резка, упаковка, отмотка</h3>
    <p class="product-block-compact__text"><?= e($productBlocksData['cutting_short'] ?? $productBlocksData['cutting_block'] ?? '') ?></p>
</section>
