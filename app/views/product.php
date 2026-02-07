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
    <?php include __DIR__ . '/partials/catalog_header.php'; ?>
    <div class="container">
        <div class="product__inner">
            <!-- Левая колонка: фото -->
            <div class="product__left">
                <div class="product__image">
                    <?php if ($product['image']): ?>
                        <img src="<?= base_url($product['image']) ?>" alt="<?= e($product['name']) ?>">
                    <?php else: ?>
                        <div class="product__placeholder">Нет фото</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Информация (H1 по SEO-шаблону: тип + AISI + марка + размер) -->
            <div class="product__info">
                <h1 class="product__name"><?= e($pageH1) ?></h1>
                
                <div class="product__price-block">
                    <div class="product__price"><?= format_price($product['price_per_kg']) ?></div>
                    <div class="product__stock">
                        <?= $product['in_stock'] ? '✓ В наличии' : 'Под заказ' ?>
                    </div>
                </div>

                <table class="product__specs">
                    <tr>
                        <td>Толщина</td>
                        <td><?= $product['thickness'] ? e($product['thickness'] . ' мм') : '—' ?></td>
                    </tr>
                    <?php if ($product['width']): ?>
                    <tr>
                        <td>Ширина</td>
                        <td><?= e($product['width'] . ' мм') ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Состояние</td>
                        <td><?= isset($conditionLabels[$product['condition']]) ? e($conditionLabels[$product['condition']]) : '—' ?></td>
                    </tr>
                    <tr>
                        <td>Пружинные свойства</td>
                        <td><?= $product['spring'] ? 'Да' : 'Нет' ?></td>
                    </tr>
                    <tr>
                        <td>Поверхность</td>
                        <td><?= $product['surface'] ? e($product['surface']) : '—' ?></td>
                    </tr>
                    <?php if ($product['lead_time']): ?>
                    <tr>
                        <td>Срок поставки</td>
                        <td><?= e($product['lead_time']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>

                <div class="product__cta" id="request" role="region" aria-label="Действия с товаром">
                    <button type="button" class="btn btn--primary btn--large product__cta-btn js-open-request-modal">Оставить заявку</button>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/partials/related_products.php'; ?>
</div>

