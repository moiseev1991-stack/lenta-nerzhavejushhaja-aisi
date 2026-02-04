<?php
// Диапазон толщин для фильтра: от 0,05 до 4 мм
$defaultThicknesses = [0.05, 0.1, 0.12, 0.15, 0.2, 0.25, 0.27, 0.3, 0.35, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1, 1.2, 1.4, 1.5, 2, 2.5, 3, 3.5, 4];
$availableThicknesses = isset($availableThicknesses) ? $availableThicknesses : $defaultThicknesses;
?>

<?php $productsCount = count($products); ?>
<div class="category-page">
    <?php include __DIR__ . '/partials/catalog_header.php'; ?>

    <!-- Каталог с фильтрами -->
    <section class="catalog">
        <div class="container">
            <div class="catalog__filters-overlay" id="filtersOverlay" aria-hidden="true"></div>
            <div class="catalog__inner">
                <!-- Товары (первыми на мобилке) -->
                <div class="catalog__products">
                    <div class="catalog__toolbar">
                        <div class="toolbar__left">
                            <button type="button" class="catalog__filters-toggle" id="filtersToggle" aria-label="Открыть фильтры" aria-expanded="false" aria-controls="filtersForm">Фильтры</button>
                            <span class="toolbar__count">Найдено: <?= count($products) ?></span>
                        </div>
                        <div class="toolbar__right">
                            <span class="toolbar__sort">Сортировка: Цена ↑</span>
                        </div>
                    </div>

                    <?php if (empty($products)): ?>
                        <p class="catalog__empty">Товары не найдены</p>
                    <?php else: ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
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
                    <?php endif; ?>
                </div>

                <!-- Фильтры (на мобилке — оффканвас) -->
                <aside class="catalog__filters" id="catalogFilters" role="dialog" aria-label="Фильтры" aria-modal="true">
                    <div class="catalog__filters-header">
                        <h2 class="catalog__filters-title">Фильтры</h2>
                        <button type="button" class="catalog__filters-close" id="filtersClose" aria-label="Закрыть фильтры">&times;</button>
                    </div>
                    <form method="GET" action="" class="filters-form" id="filtersForm">
                        <!-- Толщина -->
                        <details class="filter-group" open>
                            <summary class="filter-group__title">Толщина ленты, мм</summary>
                            <div class="filter-group__content">
                                <input type="text" 
                                       class="filter-search" 
                                       placeholder="Найти" 
                                       id="thicknessSearch"
                                       autocomplete="off">
                                <div class="filter-list" id="thicknessList">
                                    <?php foreach ($defaultThicknesses as $th): ?>
                                        <?php $checked = in_array($th, $filters['thickness']); ?>
                                        <label class="filter-item">
                                            <input type="checkbox" 
                                                   name="th[]" 
                                                   value="<?= $th ?>" 
                                                   <?= $checked ? 'checked' : '' ?>>
                                            <span><?= $th ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </details>

                        <!-- Состояние -->
                        <details class="filter-group" open>
                            <summary class="filter-group__title">Состояние стали</summary>
                            <div class="filter-group__content">
                                <?php 
                                $conditions = [
                                    'soft' => 'Мягкая',
                                    'hard' => 'Нагартованная',
                                    'semi_hard' => 'Полугартованная'
                                ];
                                foreach ($conditions as $val => $label): 
                                    $checked = in_array($val, $filters['condition']);
                                ?>
                                <label class="filter-item">
                                    <input type="checkbox" 
                                           name="cond[]" 
                                           value="<?= $val ?>" 
                                           <?= $checked ? 'checked' : '' ?>>
                                    <span><?= $label ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </details>

                        <!-- Пружинные свойства -->
                        <details class="filter-group">
                            <summary class="filter-group__title">Пружинные свойства</summary>
                            <div class="filter-group__content">
                                <label class="filter-item">
                                    <input type="radio" 
                                           name="spring" 
                                           value="" 
                                           <?= $filters['spring'] === null ? 'checked' : '' ?>>
                                    <span>Любые</span>
                                </label>
                                <label class="filter-item">
                                    <input type="radio" 
                                           name="spring" 
                                           value="1" 
                                           <?= $filters['spring'] === 1 ? 'checked' : '' ?>>
                                    <span>Да</span>
                                </label>
                                <label class="filter-item">
                                    <input type="radio" 
                                           name="spring" 
                                           value="0" 
                                           <?= $filters['spring'] === 0 ? 'checked' : '' ?>>
                                    <span>Нет</span>
                                </label>
                            </div>
                        </details>

                        <!-- Поверхность -->
                        <details class="filter-group">
                            <summary class="filter-group__title">Поверхность</summary>
                            <div class="filter-group__content">
                                <?php 
                                $surfaces = [
                                    'BA' => 'BA',
                                    '2B' => '2B',
                                    '4N' => '4N'
                                ];
                                foreach ($surfaces as $val => $label): 
                                    $checked = in_array($val, $filters['surface']);
                                ?>
                                <label class="filter-item">
                                    <input type="checkbox" 
                                           name="surf[]" 
                                           value="<?= $val ?>" 
                                           <?= $checked ? 'checked' : '' ?>>
                                    <span><?= $label ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </details>

                        <div class="filters-form__actions">
                            <button type="submit" class="btn btn--primary">Применить</button>
                            <a href="<?= base_url($category['slug'] . '/') ?>" class="btn btn--ghost">Сбросить</a>
                        </div>
                    </form>
                </aside>
            </div>
        </div>
    </section>
</div>

<script>
// Поиск по толщине
(function() {
    const searchInput = document.getElementById('thicknessSearch');
    const list = document.getElementById('thicknessList');
    
    if (!searchInput || !list) return;
    
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const items = list.querySelectorAll('.filter-item');
        
        items.forEach(function(item) {
            const text = item.textContent.toLowerCase();
            if (text.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
})();

// Мобильные фильтры: открыть/закрыть оффканвас
(function() {
    var toggle = document.getElementById('filtersToggle');
    var closeBtn = document.getElementById('filtersClose');
    var overlay = document.getElementById('filtersOverlay');
    var body = document.body;

    function openFilters() {
        body.classList.add('filters-open');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
        if (overlay) overlay.setAttribute('aria-hidden', 'false');
        document.documentElement.style.overflow = 'hidden';
    }
    function closeFilters() {
        body.classList.remove('filters-open');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        if (overlay) overlay.setAttribute('aria-hidden', 'true');
        document.documentElement.style.overflow = '';
    }

    if (toggle) toggle.addEventListener('click', function() {
        if (body.classList.contains('filters-open')) closeFilters();
        else openFilters();
    });
    if (closeBtn) closeBtn.addEventListener('click', closeFilters);
    if (overlay) overlay.addEventListener('click', closeFilters);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && body.classList.contains('filters-open')) closeFilters();
    });
})();
</script>
