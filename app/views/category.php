<?php
// Фиксированный список толщин для фильтра «Толщина ленты, мм»
$defaultThicknesses = get_filter_thicknesses();
$products = $products ?? [];
?>
<?php
$pagination = $pagination ?? null;
$productsCount = $pagination ? (int) $pagination['total'] : count($products);
$showArticle = !empty($category['content_is_active']) && trim((string)($category['content_body'] ?? '')) !== '';
ob_start();
?><nav class="breadcrumbs" aria-label="Хлебные крошки">
    <a href="<?= base_url() ?>">Главная</a>
    <span>/</span>
    <span><?= e($category['name']) ?></span>
</nav><?php
$heroBreadcrumbs = ob_get_clean();
?>
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
                            <span class="toolbar__count">Найдено: <?= $pagination ? (int) $pagination['total'] : count($products) ?></span>
                        </div>
                        <div class="toolbar__right">
                            <?php
                            $sortOrder = $sortOrder ?? 'price_asc';
                            $urlAsc = $categorySortUrlAsc ?? (base_url($category['slug'] . '/') . '?sort=price_asc');
                            $urlDesc = $categorySortUrlDesc ?? (base_url($category['slug'] . '/') . '?sort=price_desc');
                            $sortUrl = $sortOrder === 'price_asc' ? $urlDesc : $urlAsc;
                            ?>
                            <a href="<?= e($sortUrl) ?>" class="toolbar__sort">Сортировка: Цена <?= $sortOrder === 'price_asc' ? '↑' : '↓' ?></a>
                        </div>
                    </div>

                    <?php if (empty($products)): ?>
                        <p class="catalog__empty">Товары не найдены</p>
                        <?php if ($showArticle): ?>
                            <p class="catalog__to-article">
                                <a href="#category-article" class="catalog__to-article-link">К описанию</a>
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <a href="<?= base_url($category['slug'] . '/' . $product['slug'] . '/') ?>" class="product-card">
                                    <div class="product-card__heart">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                            <path d="M10 17.5c-1.5-1.5-5-4-5-7.5a3 3 0 016 0 3 3 0 016 0c0 3.5-3.5 6-5 7.5z"/>
                                        </svg>
                                    </div>
                                    <div class="product-card__image">
                                        <?php if ($product['image']): ?>
                                            <img src="<?= base_url($product['image']) ?>" alt="<?= e($product['name']) ?>">
                                        <?php else: ?>
                                            <img src="<?= base_url('public/img/placeholder-product.svg') ?>" alt="" class="product-card__placeholder-img">
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
                        <?php
                        if ($pagination && $pagination['total_pages'] > 1):
                            $base = $pagination['base_url'];
                            $qp = $pagination['query_params'];
                            $pageUrl = function ($num) use ($base, $qp) {
                                $qp['page'] = $num;
                                return $base . '?' . http_build_query(array_filter($qp, function ($v) { return $v !== '' && $v !== null; }));
                            };
                            $curr = (int) $pagination['current_page'];
                            $totalP = (int) $pagination['total_pages'];
                        ?>
                        <nav class="pagination" aria-label="Пагинация каталога">
                            <ul class="pagination__list">
                                <li>
                                    <?php if ($curr > 1): ?>
                                        <a href="<?= e($pageUrl($curr - 1)) ?>" class="pagination__link pagination__prev" aria-label="Назад">← Назад</a>
                                    <?php else: ?>
                                        <span class="pagination__link pagination__link--disabled" aria-disabled="true">← Назад</span>
                                    <?php endif; ?>
                                </li>
                                <?php for ($i = 1; $i <= $totalP; $i++): ?>
                                    <li>
                                        <?php if ($i === $curr): ?>
                                            <span class="pagination__link pagination__link--current" aria-current="page"><?= $i ?></span>
                                        <?php else: ?>
                                            <a href="<?= e($pageUrl($i)) ?>" class="pagination__link"><?= $i ?></a>
                                        <?php endif; ?>
                                    </li>
                                <?php endfor; ?>
                                <li>
                                    <?php if ($curr < $totalP): ?>
                                        <a href="<?= e($pageUrl($curr + 1)) ?>" class="pagination__link pagination__next" aria-label="Вперёд">Вперёд →</a>
                                    <?php else: ?>
                                        <span class="pagination__link pagination__link--disabled" aria-disabled="true">Вперёд →</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                        <?php if ($showArticle): ?>
                            <p class="catalog__to-article">
                                <a href="#category-article" class="catalog__to-article-link">К описанию</a>
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Фильтры (на мобилке — оффканвас) -->
                <aside class="catalog__filters" id="catalogFilters" role="dialog" aria-label="Фильтры" aria-modal="true">
                    <div class="catalog__filters-header">
                        <h2 class="catalog__filters-title">Фильтры</h2>
                        <button type="button" class="catalog__filters-close" id="filtersClose" aria-label="Закрыть фильтры">&times;</button>
                    </div>
                    <form method="GET" action="" class="filters-form" id="filtersForm">
                        <input type="hidden" name="page" value="1">
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

    <?php if ($showArticle):
        $format = trim((string)($category['content_format'] ?? ''));
        if ($format === 'html') {
            $articleHtml = sanitize_category_content_html($category['content_body']);
        } else {
            $articleHtml = sanitize_category_content_html(markdown_to_html($category['content_body']));
        }
        $articleTitle = trim((string)($category['content_title'] ?? ''));
    ?>
    <section id="category-article" class="category-article">
        <div class="container">
            <div class="category-article__inner">
                <?php if ($articleTitle !== ''): ?>
                    <h2 class="category-article__title"><?= e($articleTitle) ?></h2>
                <?php endif; ?>
                <div class="category-article__content">
                    <?= $articleHtml ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
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
