<?php
// Доступные толщины для фильтра (из всех товаров категории)
$defaultThicknesses = [0.05, 0.1, 0.15, 0.2, 0.25, 0.3, 0.35, 0.4, 0.5, 0.6, 0.7];
$availableThicknesses = isset($availableThicknesses) ? $availableThicknesses : $defaultThicknesses;
?>

<div class="category-page">
    <!-- Hero блок -->
    <section class="category-hero">
        <div class="container">
            <div class="category-hero__inner">
                <div class="category-hero__left">
                    <div class="category-hero__header">
                        <h1><?= e($category['h1'] ?: $category['name']) ?></h1>
                        <span class="category-hero__count">Найдено: <?= count($products) ?></span>
                    </div>
                    <?php if ($category['intro']): ?>
                    <p class="category-hero__intro" id="categoryIntro"><?= e($category['intro']) ?></p>
                    <?php endif; ?>
                    
                    <div class="category-hero__chips">
                        <span class="chips-label">Подкатегории:</span>
                        <div class="chips" id="chipsContainer">
                            <?php 
                            $chipsCount = 0;
                            $maxVisibleChips = 10;
                            foreach ($allCategories as $cat): 
                                $chipsCount++;
                                $isHidden = $chipsCount > $maxVisibleChips;
                            ?>
                                <a href="<?= base_url($cat['slug'] . '/') ?>" 
                                   class="chip <?= $cat['slug'] === $category['slug'] ? 'chip--active' : '' ?> <?= $isHidden ? 'chip--hidden' : '' ?>"
                                   data-chip-index="<?= $chipsCount ?>">
                                    <?= e($cat['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($allCategories) > $maxVisibleChips): ?>
                        <button type="button" class="chips-toggle" id="chipsToggle">
                            <span class="chips-toggle__show">Показать все марки</span>
                            <span class="chips-toggle__hide" style="display: none;">Скрыть</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <aside class="category-hero__right">
                    <div class="hero-card">
                        <?php if ($minPrice): ?>
                        <div class="badge badge--green">от <?= format_price($minPrice) ?></div>
                        <?php endif; ?>
                        <h3>Изготовим ленту по вашим размерам:</h3>
                        <ul class="hero-card__list">
                            <li>Точность ±0.01 мм</li>
                            <li>Минимальная ширина от 5 мм</li>
                        </ul>
                        <a href="#request" class="btn btn--primary">Оставить заявку</a>
                        
                        <div class="hero-card__benefits" id="benefitsContainer">
                            <button type="button" class="benefits-toggle" id="benefitsToggle">
                                <span class="benefits-toggle__show">Показать преимущества</span>
                                <span class="benefits-toggle__hide" style="display: none;">Скрыть преимущества</span>
                            </button>
                            <div class="benefits-content" id="benefitsContent" style="display: none;">
                                <div class="benefit">
                                    <svg class="benefit__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                        <path d="M10 2L3 7v11h4v-6h6v6h4V7l-7-5z"/>
                                    </svg>
                                    <span>Собственное производство</span>
                                </div>
                                <div class="benefit">
                                    <svg class="benefit__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                        <path d="M10 10a3 3 0 100-6 3 3 0 000 6zM10 12c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                    <span>Склад в центре Санкт-Петербурга</span>
                                </div>
                                <div class="benefit">
                                    <svg class="benefit__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                        <circle cx="10" cy="10" r="8"/>
                                        <path d="M10 6v4l3 2"/>
                                    </svg>
                                    <span>Быстрая отгрузка</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- Каталог с фильтрами -->
    <section class="catalog">
        <div class="container">
            <div class="catalog__inner">
                <!-- Фильтры -->
                <aside class="catalog__filters">
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

                <!-- Товары -->
                <div class="catalog__products">
                    <div class="catalog__toolbar">
                        <div class="toolbar__left">
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

// Управление чипсами (показать/скрыть все марки)
(function() {
    const toggle = document.getElementById('chipsToggle');
    const chips = document.querySelectorAll('.chip--hidden');
    
    if (!toggle || chips.length === 0) return;
    
    toggle.addEventListener('click', function() {
        const isHidden = chips[0].style.display === 'none' || chips[0].style.display === '';
        
        chips.forEach(function(chip) {
            chip.style.display = isHidden ? 'inline-block' : 'none';
        });
        
        const showText = toggle.querySelector('.chips-toggle__show');
        const hideText = toggle.querySelector('.chips-toggle__hide');
        
        if (isHidden) {
            showText.style.display = 'none';
            hideText.style.display = 'inline';
        } else {
            showText.style.display = 'inline';
            hideText.style.display = 'none';
        }
    });
    
    // Изначально скрываем
    chips.forEach(function(chip) {
        chip.style.display = 'none';
    });
})();

// Управление преимуществами в CTA блоке
(function() {
    const toggle = document.getElementById('benefitsToggle');
    const content = document.getElementById('benefitsContent');
    
    if (!toggle || !content) return;
    
    // На десктопе при высоте экрана < 900px сворачиваем по умолчанию
    if (window.innerHeight < 900 && window.innerWidth >= 980) {
        content.style.display = 'none';
    }
    
    toggle.addEventListener('click', function() {
        const isHidden = content.style.display === 'none';
        
        content.style.display = isHidden ? 'block' : 'none';
        
        const showText = toggle.querySelector('.benefits-toggle__show');
        const hideText = toggle.querySelector('.benefits-toggle__hide');
        
        if (isHidden) {
            showText.style.display = 'none';
            hideText.style.display = 'inline';
        } else {
            showText.style.display = 'inline';
            hideText.style.display = 'none';
        }
    });
})();
</script>
