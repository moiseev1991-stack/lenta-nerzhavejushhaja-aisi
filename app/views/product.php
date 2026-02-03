<?php
$conditionLabels = [
    'soft' => 'Мягкая',
    'hard' => 'Нагартованная',
    'semi_hard' => 'Полугартованная'
];
?>

<div class="product-page">
    <div class="container">
        <!-- Хлебные крошки -->
        <nav class="breadcrumbs">
            <a href="<?= base_url() ?>">Главная</a>
            <span>/</span>
            <a href="<?= base_url($product['category_slug'] . '/') ?>"><?= e($product['category_name']) ?></a>
            <span>/</span>
            <span><?= e($product['name']) ?></span>
        </nav>

        <div class="product__inner">
            <!-- Левая колонка: фото + преимущества -->
            <div class="product__left">
                <div class="product__image">
                    <?php if ($product['image']): ?>
                        <img src="<?= base_url($product['image']) ?>" alt="<?= e($product['name']) ?>">
                    <?php else: ?>
                        <div class="product__placeholder">Нет фото</div>
                    <?php endif; ?>
                </div>
                <?php $benefitsVariant = 'product'; include __DIR__ . '/partials/benefits.php'; ?>
            </div>

            <!-- Информация -->
            <div class="product__info">
                <h1><?= e($product['h1'] ?: $product['name']) ?></h1>
                
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
</div>

