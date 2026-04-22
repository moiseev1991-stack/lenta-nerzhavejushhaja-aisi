<?php
$defaultThicknesses = get_filter_thicknesses();
$products  = $products ?? [];
$pagination = $pagination ?? null;
$productsCount = $pagination ? (int)$pagination['total'] : count($products);
$showArticle = !empty($category['content_is_active']) && trim((string)($category['content_body'] ?? '')) !== '';

// Данные марки из grades_data.php
$gradeData = function_exists('get_grade_data') ? get_grade_data($category['slug'] ?? '') : null;
$gradeNum  = $gradeData['number'] ?? '';
$gradeSeries = $gradeData['series'] ?? '';
$seriesSlug = $gradeSeries ? 'aisi-' . strtolower(str_replace('L', 'l', $gradeSeries)) . '-seriya' : '';

// Хлебные крошки (с серией если есть gradeData)
ob_start();
?>
<nav class="breadcrumbs" aria-label="Хлебные крошки">
    <a href="<?= base_url() ?>">Главная</a>
    <span>/</span>
    <?php if ($gradeData && $gradeSeries): ?>
    <a href="<?= base_url($seriesSlug . '/') ?>">Серия <?= e($gradeSeries) ?></a>
    <span>/</span>
    <span>AISI <?= e($gradeNum) ?></span>
    <?php else: ?>
    <span><?= e($category['name']) ?></span>
    <?php endif; ?>
</nav>
<?php
$heroBreadcrumbs = ob_get_clean();
?>
<div class="category-page">
    <?php include __DIR__ . '/partials/catalog_header.php'; ?>

    <?php if ($gradeData): ?>
    <!-- Ключевые характеристики + интро -->
    <section class="grade-key-facts">
        <div class="container">
            <div class="grade-key-facts__inner">
                <div class="grade-key-facts__table-wrap">
                    <table class="data-table data-table--compact">
                        <tbody>
                            <tr><th>Марка</th><td>AISI <?= e($gradeNum) ?></td></tr>
                            <tr><th>Серия</th><td><?= e($gradeSeries) ?></td></tr>
                            <tr><th>Тип стали</th><td><?= e($gradeData['type']) ?></td></tr>
                            <tr><th>ГОСТ-аналог</th><td><?= e($gradeData['gost']) ?></td></tr>
                            <tr><th>EN-аналог</th><td><?= e($gradeData['en_name'] ?? '') ?> / <?= e($gradeData['en_number'] ?? '') ?></td></tr>
                            <tr><th>JIS-аналог</th><td><?= e($gradeData['jis'] ?? '') ?></td></tr>
                            <?php
                            $cr = '';
                            $ni = '';
                            $mo = '';
                            foreach ($gradeData['chemical'] ?? [] as $chem) {
                                if ($chem['element'] === 'Cr') $cr = $chem['range'];
                                if ($chem['element'] === 'Ni') $ni = $chem['range'];
                                if ($chem['element'] === 'Mo') $mo = $chem['range'];
                            }
                            ?>
                            <?php if ($cr): ?><tr><th>Cr, %</th><td><?= e($cr) ?></td></tr><?php endif; ?>
                            <?php if ($ni): ?><tr><th>Ni, %</th><td><?= e($ni) ?></td></tr><?php endif; ?>
                            <?php if ($mo): ?><tr><th>Mo, %</th><td><?= e($mo) ?></td></tr><?php endif; ?>
                            <tr><th>Плотность</th><td><?= e($gradeData['density'] ?? '') ?> г/см³</td></tr>
                            <tr><th>Магнитность</th><td><?= ($gradeData['magnetic'] ?? false) ? 'Да' : 'Нет' ?></td></tr>
                            <tr><th>Коррозионная стойкость</th><td><?= e($gradeData['corrosion_rating'] ?? '') ?></td></tr>
                            <tr><th>Свариваемость</th><td><?= ($gradeData['weldable'] ?? true) ? 'Да' : 'Ограниченная' ?></td></tr>
                            <tr><th>Макс. температура</th><td><?= e($gradeData['max_temp'] ?? '') ?> °C</td></tr>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($gradeData['intro'])): ?>
                <div class="grade-key-facts__intro">
                    <p><?= e($gradeData['intro']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

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
                            <span class="toolbar__count">Найдено: <?= $pagination ? (int)$pagination['total'] : count($products) ?></span>
                        </div>
                        <div class="toolbar__right">
                            <?php
                            $sortOrder = $sortOrder ?? 'price_asc';
                            $urlAsc  = $categorySortUrlAsc  ?? (base_url($category['slug'] . '/') . '?sort=price_asc');
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
                                        <?php
                                        $ph = asset_url('img/placeholder-product.svg');
                                        if ($product['image']): ?>
                                            <img src="<?= image_url($product['image']) ?>" alt="<?= e($product['name']) ?>"
                                                 onerror="this.onerror=null;this.src='<?= e($ph) ?>';this.classList.add('product-card__placeholder-img');">
                                        <?php else: ?>
                                            <img src="<?= $ph ?>" alt="" class="product-card__placeholder-img">
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-card__content">
                                        <h3 class="product-card__name"><?= e($product['name']) ?></h3>
                                        <div class="product-card__meta">
                                            <?php
                                            $meta = [];
                                            if ($product['thickness']) $meta[] = $product['thickness'] . ' мм';
                                            if ($product['condition'] === 'soft')      $meta[] = 'Мягкая';
                                            if ($product['condition'] === 'hard')      $meta[] = 'Нагартованная';
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
                            $qp   = $pagination['query_params'];
                            $pageUrl = function ($num) use ($base, $qp) {
                                $qp['page'] = $num;
                                return $base . '?' . http_build_query(array_filter($qp, function ($v) { return $v !== '' && $v !== null; }));
                            };
                            $curr   = (int)$pagination['current_page'];
                            $totalP = (int)$pagination['total_pages'];
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

                <!-- Фильтры (оффканвас на мобилке) -->
                <aside class="catalog__filters" id="catalogFilters" role="dialog" aria-label="Фильтры" aria-modal="true">
                    <div class="catalog__filters-header">
                        <h2 class="catalog__filters-title">Фильтры</h2>
                        <button type="button" class="catalog__filters-close" id="filtersClose" aria-label="Закрыть фильтры">&times;</button>
                    </div>
                    <form method="GET" action="" class="filters-form" id="filtersForm">
                        <input type="hidden" name="page" value="1">
                        <details class="filter-group" open>
                            <summary class="filter-group__title">Толщина ленты, мм</summary>
                            <div class="filter-group__content">
                                <input type="text" class="filter-search" placeholder="Найти" id="thicknessSearch" autocomplete="off">
                                <div class="filter-list" id="thicknessList">
                                    <?php foreach ($defaultThicknesses as $th): ?>
                                        <?php $checked = in_array($th, $filters['thickness']); ?>
                                        <label class="filter-item">
                                            <input type="checkbox" name="th[]" value="<?= $th ?>" <?= $checked ? 'checked' : '' ?>>
                                            <span><?= $th ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </details>

                        <details class="filter-group" open>
                            <summary class="filter-group__title">Состояние стали</summary>
                            <div class="filter-group__content">
                                <?php
                                $conditions = ['soft' => 'Мягкая', 'hard' => 'Нагартованная', 'semi_hard' => 'Полугартованная'];
                                foreach ($conditions as $val => $label):
                                    $checked = in_array($val, $filters['condition']);
                                ?>
                                <label class="filter-item">
                                    <input type="checkbox" name="cond[]" value="<?= $val ?>" <?= $checked ? 'checked' : '' ?>>
                                    <span><?= $label ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </details>

                        <details class="filter-group">
                            <summary class="filter-group__title">Пружинные свойства</summary>
                            <div class="filter-group__content">
                                <label class="filter-item">
                                    <input type="radio" name="spring" value="" <?= $filters['spring'] === null ? 'checked' : '' ?>>
                                    <span>Любые</span>
                                </label>
                                <label class="filter-item">
                                    <input type="radio" name="spring" value="1" <?= $filters['spring'] === 1 ? 'checked' : '' ?>>
                                    <span>Да</span>
                                </label>
                                <label class="filter-item">
                                    <input type="radio" name="spring" value="0" <?= $filters['spring'] === 0 ? 'checked' : '' ?>>
                                    <span>Нет</span>
                                </label>
                            </div>
                        </details>

                        <details class="filter-group">
                            <summary class="filter-group__title">Поверхность</summary>
                            <div class="filter-group__content">
                                <?php
                                $surfaces = ['BA' => 'BA', '2B' => '2B', '4N' => '4N'];
                                foreach ($surfaces as $val => $label):
                                    $checked = in_array($val, $filters['surface']);
                                ?>
                                <label class="filter-item">
                                    <input type="checkbox" name="surf[]" value="<?= $val ?>" <?= $checked ? 'checked' : '' ?>>
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

    <?php if ($gradeData): ?>

    <!-- Характеристики -->
    <?php if (!empty($gradeData['characteristics'])): ?>
    <section class="grade-section">
        <div class="container">
            <h2 class="grade-section__title">Характеристики AISI <?= e($gradeNum) ?></h2>
            <div class="grade-section__text">
                <p><?= e($gradeData['characteristics']) ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Химический состав -->
    <?php if (!empty($gradeData['chemical'])): ?>
    <section class="grade-section">
        <div class="container">
            <h2 class="grade-section__title">Химический состав AISI <?= e($gradeNum) ?></h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Элемент</th>
                            <th>Содержание, %</th>
                            <th>Влияние на свойства</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gradeData['chemical'] as $chem): ?>
                        <tr>
                            <td><strong><?= e($chem['element']) ?></strong></td>
                            <td><?= e($chem['range']) ?></td>
                            <td><?= e($chem['note']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Механические свойства -->
    <?php if (!empty($gradeData['mechanical'])): ?>
    <section class="grade-section">
        <div class="container">
            <h2 class="grade-section__title">Механические свойства AISI <?= e($gradeNum) ?></h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Параметр</th>
                            <th>Значение</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gradeData['mechanical'] as $mech): ?>
                        <tr>
                            <td><?= e($mech['property']) ?></td>
                            <td><strong><?= e($mech['value']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Применение -->
    <?php if (!empty($gradeData['applications'])): ?>
    <section class="grade-section">
        <div class="container">
            <h2 class="grade-section__title">Применение AISI <?= e($gradeNum) ?></h2>
            <div class="grade-section__text">
                <p><?= e($gradeData['applications']) ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Аналоги -->
    <section class="grade-section">
        <div class="container">
            <h2 class="grade-section__title">Аналоги AISI <?= e($gradeNum) ?></h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Система</th>
                            <th>Обозначение</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>AISI / ASTM</td><td><strong>AISI <?= e($gradeNum) ?></strong></td></tr>
                        <tr><td>ГОСТ (Россия)</td><td><?= e($gradeData['gost']) ?></td></tr>
                        <tr><td>EN (Европа)</td><td><?= e($gradeData['en_name'] ?? '') ?> / <?= e($gradeData['en_number'] ?? '') ?></td></tr>
                        <?php if (!empty($gradeData['jis'])): ?>
                        <tr><td>JIS (Япония)</td><td><?= e($gradeData['jis']) ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($gradeData['din']) && $gradeData['din'] !== ($gradeData['en_name'] ?? '')): ?>
                        <tr><td>DIN (Германия)</td><td><?= e($gradeData['din']) ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Отличия от похожих марок -->
    <?php if (!empty($gradeData['comparison_text']) || !empty($gradeData['comparison_grades'])): ?>
    <section class="grade-section">
        <div class="container">
            <h2 class="grade-section__title">Отличия AISI <?= e($gradeNum) ?> от похожих марок</h2>
            <?php if (!empty($gradeData['comparison_text'])): ?>
            <div class="grade-section__text">
                <p><?= e($gradeData['comparison_text']) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($gradeData['comparison_grades'])): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Марка</th>
                            <th>Ключевые отличия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>AISI <?= e($gradeNum) ?></strong></td>
                            <td>Текущая марка (базовая)</td>
                        </tr>
                        <?php foreach ($gradeData['comparison_grades'] as $cSlug => $cText): ?>
                        <tr>
                            <td><a href="<?= base_url($cSlug . '/') ?>" class="link">AISI <?= e(strtoupper(str_replace('aisi-', '', $cSlug))) ?></a></td>
                            <td><?= e($cText) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Поверхности и состояния поставки -->
    <section class="grade-section">
        <div class="container">
            <h2 class="grade-section__title">Поверхности и состояния поставки</h2>
            <div class="grade-section__surfaces">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr><th>Поверхность</th><th>Описание</th><th>Применение</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>2B</strong></td>
                                <td>Матовая ровная, после холодной прокатки и дрессировки</td>
                                <td>Промышленные детали, монтажные изделия, штамповка</td>
                            </tr>
                            <tr>
                                <td><strong>BA</strong></td>
                                <td>Зеркально-гладкая, светлый отжиг в защитной атмосфере</td>
                                <td>Декор, архитектура, пищевое оборудование, медицина</td>
                            </tr>
                            <tr>
                                <td><strong>2BA</strong></td>
                                <td>Промежуточный вариант 2B + элементы светлого отжига</td>
                                <td>Гигиенические применения с умеренными требованиями к блеску</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="grade-section__conditions">
                    <h3>Состояния поставки</h3>
                    <ul class="grade-list">
                        <li><strong>Мягкая (отожжённая)</strong> — максимальная пластичность для глубокой вытяжки, гибки и штамповки.</li>
                        <li><strong>Нагартованная</strong> — повышенная твёрдость и упругость за счёт холодной деформации. Подходит для пружин и упругих элементов.</li>
                        <li><strong>Полугартованная</strong> — промежуточный баланс пластичности и упругости для умеренных деформаций.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Стандарты и сертификация -->
    <section class="grade-section">
        <div class="container">
            <h2 class="grade-section__title">Стандарты и сертификация</h2>
            <div class="grade-section__text">
                <p>Нержавеющая лента AISI <?= e($gradeNum) ?> поставляется в соответствии с основными международными стандартами:</p>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Стандарт</th><th>Область применения</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>ASTM A240 / A480</strong></td><td>США: листы, полосы и ленты из нержавеющей стали</td></tr>
                        <tr><td><strong>EN 10088-2</strong></td><td>Европа: коррозионностойкие стали, листы и полосы</td></tr>
                        <tr><td><strong>ГОСТ 4986-79</strong></td><td>Россия: лента из нержавеющей и жаропрочной стали</td></tr>
                        <tr><td><strong>ГОСТ 5632</strong></td><td>Россия: марочный состав нержавеющих сталей (аналог — <?= e($gradeData['gost']) ?>)</td></tr>
                        <tr><td><strong>JIS G4305</strong></td><td>Япония: холоднокатаные листы и полосы из нержавеющей стали</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="grade-section__text" style="margin-top:1rem;">
                <p>С каждой партией предоставляется сертификат качества с химическим составом, механическими свойствами и плавочным номером. По запросу — сертификаты EN 10204 3.1 (заводской) или 2.2 (тест-отчёт).</p>
            </div>
        </div>
    </section>

    <!-- Как выбрать толщину и ширину -->
    <?php if (!empty($gradeData['how_to_choose'])): ?>
    <section class="grade-section">
        <div class="container">
            <h2 class="grade-section__title">Как выбрать толщину и ширину ленты AISI <?= e($gradeNum) ?></h2>
            <div class="grade-section__text">
                <p><?= e($gradeData['how_to_choose']) ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- FAQ -->
    <?php if (!empty($gradeData['faq'])): ?>
    <section class="grade-section grade-section--faq">
        <div class="container">
            <h2 class="grade-section__title">Часто задаваемые вопросы об AISI <?= e($gradeNum) ?></h2>
            <div class="faq-list">
                <?php foreach ($gradeData['faq'] as $fi => $faq): ?>
                <details class="faq-item" <?= $fi === 0 ? 'open' : '' ?>>
                    <summary class="faq-item__question"><?= e($faq['q']) ?></summary>
                    <p class="faq-item__answer"><?= e($faq['a']) ?></p>
                </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Похожие марки -->
    <?php if (!empty($gradeData['similar_grades'])): ?>
    <section class="grade-section">
        <div class="container">
            <h2 class="grade-section__title">Похожие марки</h2>
            <div class="similar-grades-grid">
                <?php foreach ($gradeData['similar_grades'] as $simSlug): ?>
                <?php $simData = function_exists('get_grade_data') ? get_grade_data($simSlug) : null; ?>
                <a href="<?= base_url($simSlug . '/') ?>" class="similar-grade-card">
                    <strong class="similar-grade-card__name">AISI <?= e($simData['number'] ?? strtoupper(str_replace('aisi-', '', $simSlug))) ?></strong>
                    <?php if ($simData): ?>
                    <span class="similar-grade-card__type"><?= e($simData['type']) ?></span>
                    <span class="similar-grade-card__gost"><?= e($simData['gost']) ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA / форма заявки -->
    <section class="grade-section grade-section--cta">
        <div class="container">
            <div class="cta-block">
                <h2 class="cta-block__title">Заказать ленту AISI <?= e($gradeNum) ?></h2>
                <p class="cta-block__text">Нарезка от 1 метра, толщины 0,05–4 мм, ширина от 2,5 мм. Поверхности 2B и BA. Ответим за 15 минут.</p>
                <div class="cta-block__actions">
                    <button type="button" class="btn btn--primary js-open-request-modal">Оставить заявку</button>
                    <a href="tel:+78002003943" class="btn btn--ghost">+7 (800) 200-39-43</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Перелинковка на серию -->
    <?php if ($seriesSlug): ?>
    <section class="grade-section grade-section--series-link">
        <div class="container">
            <p class="grade-section__text">
                Все марки серии <?= e($gradeSeries) ?>: <a href="<?= base_url($seriesSlug . '/') ?>" class="link">Лента AISI <?= e($gradeSeries) ?> серии →</a>
            </p>
        </div>
    </section>
    <?php endif; ?>

    <?php endif; // end if gradeData ?>

    <?php include __DIR__ . '/partials/how_to_find.php'; ?>
</div>

<script>
(function() {
    const searchInput = document.getElementById('thicknessSearch');
    const list = document.getElementById('thicknessList');
    if (!searchInput || !list) return;
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        list.querySelectorAll('.filter-item').forEach(function(item) {
            item.style.display = item.textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });
})();

(function() {
    var toggle  = document.getElementById('filtersToggle');
    var closeBtn = document.getElementById('filtersClose');
    var overlay  = document.getElementById('filtersOverlay');
    var body     = document.body;

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
    if (toggle)  toggle.addEventListener('click', function() {
        body.classList.contains('filters-open') ? closeFilters() : openFilters();
    });
    if (closeBtn) closeBtn.addEventListener('click', closeFilters);
    if (overlay)  overlay.addEventListener('click', closeFilters);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && body.classList.contains('filters-open')) closeFilters();
    });
})();
</script>
