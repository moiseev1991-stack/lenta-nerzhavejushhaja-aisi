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
            <!-- Изображение -->
            <div class="product__image">
                <?php if ($product['image']): ?>
                    <img src="<?= base_url($product['image']) ?>" alt="<?= e($product['name']) ?>">
                <?php else: ?>
                    <div class="product__placeholder">Нет фото</div>
                <?php endif; ?>
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
                    <div class="product__cta-buttons">
                        <a href="#request" class="btn btn--primary btn--large">Оставить заявку</a>
                        <button type="button" 
                                class="btn btn--secondary btn--large" 
                                id="productCtaCustomOrder"
                                title="Запросить изготовление/сроки"
                                data-prefill="<?= e('Под заказ: ' . $product['name'] . ' / ' . ($product['category_name'] ?? '') . ($product['thickness'] ? ' / ' . $product['thickness'] . ' мм' : '') . ($product['width'] ? ' / ' . $product['width'] . ' мм' : '') . ($product['surface'] ? ' / ' . $product['surface'] : '')) ?>">
                            Под заказ
                        </button>
                    </div>
                    <p class="product__cta-hint" id="productCtaHint" aria-live="polite" hidden>Запрос по товару (под заказ). Укажите детали в форме ниже или свяжитесь с нами.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var btn = document.getElementById('productCtaCustomOrder');
    var block = document.getElementById('request');
    var hint = document.getElementById('productCtaHint');
    if (!btn || !block) return;
    btn.addEventListener('click', function() {
        block.setAttribute('data-request-type', 'custom_order');
        if (hint) {
            hint.removeAttribute('hidden');
            hint.style.display = '';
        }
        block.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (hint) setTimeout(function() { hint.setAttribute('hidden', ''); hint.style.display = 'none'; }, 4000);
    });
})();
</script>
