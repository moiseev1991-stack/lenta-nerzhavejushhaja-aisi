<?php
$sameThickness = isset($pdo) && function_exists('get_product_links_same_thickness') ? get_product_links_same_thickness($pdo, $product, 3) : [];
$otherSizes = isset($pdo) && function_exists('get_product_links_other_sizes') ? get_product_links_other_sizes($pdo, $product, 3) : [];
$categoryUrl = base_url(($product['category_slug'] ?? '') . '/');
$grade = e($product['category_name'] ?? '');
?>
<div class="product-links-compact">
    <a href="<?= $categoryUrl ?>" class="product-links-compact__main">Все размеры ленты <?= $grade ?> →</a>
    <?php if (!empty($otherSizes) || !empty($sameThickness)): ?>
    <div class="product-links-compact__grid">
        <?php if (!empty($otherSizes)): ?>
        <div class="product-links-compact__col">
            <span class="product-links-compact__label">Другие размеры <?= $grade ?></span>
            <ul>
                <?php foreach ($otherSizes as $p): ?><li><a href="<?= base_url($p['category_slug'] . '/' . $p['slug'] . '/') ?>"><?= e($p['name']) ?></a></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (!empty($sameThickness)): ?>
        <div class="product-links-compact__col">
            <span class="product-links-compact__label">Та же толщина в других марках</span>
            <ul>
                <?php foreach ($sameThickness as $p): ?><li><a href="<?= base_url($p['category_slug'] . '/' . $p['slug'] . '/') ?>"><?= e($p['category_name'] . ' — ' . $p['name']) ?></a></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
