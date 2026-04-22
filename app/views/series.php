<?php
/**
 * Шаблон страницы серии AISI (200/300/400/900L).
 * Ожидает $seriesData из series_data.php.
 */
$seriesData   = $seriesData ?? [];
$series       = $seriesData['series'] ?? '';
$seriesSlug   = $seriesData['slug'] ?? '';
$allGradeData = [];
if (function_exists('get_grade_data')) {
    foreach (($seriesData['grades'] ?? []) as $gs) {
        $gd = get_grade_data($gs);
        if ($gd) $allGradeData[$gs] = $gd;
    }
}
$otherSeriesMeta = [
    '200'  => ['title' => 'Серия 200 — аустенитные Mn/N', 'slug' => 'aisi-200-seriya'],
    '300'  => ['title' => 'Серия 300 — аустенитные Cr-Ni', 'slug' => 'aisi-300-seriya'],
    '400'  => ['title' => 'Серия 400 — ферритные и мартенситные', 'slug' => 'aisi-400-seriya'],
    '900L' => ['title' => 'Серия 900L — супераустенитные', 'slug' => 'aisi-900l-seriya'],
];
?>
<div class="series-page">
    <div class="container">

        <!-- Хлебные крошки -->
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= base_url() ?>">Главная</a>
            <span>/</span>
            <span>Серии AISI</span>
            <span>/</span>
            <span>Серия <?= e($series) ?></span>
        </nav>

        <!-- H1 -->
        <h1 class="series-page__h1"><?= e($pageH1 ?? $seriesData['h1'] ?? '') ?></h1>

        <!-- Вводный блок -->
        <section class="series-page__intro">
            <h2 class="series-page__section-title">Что такое сталь AISI <?= e($series) ?> серии</h2>
            <div class="series-page__intro-text">
                <?= $seriesData['intro'] ?? '' ?>
            </div>
        </section>

        <!-- Таблица марок серии -->
        <section class="series-page__grades-table">
            <h2 class="series-page__section-title">Марки серии <?= e($series) ?></h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Марка</th>
                            <th>Тип</th>
                            <th>Основные легирующие</th>
                            <th>ГОСТ-аналог</th>
                            <th>Страница</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($seriesData['grades_table'] ?? [] as $row): ?>
                        <tr>
                            <td><strong>AISI <?= e($row['number']) ?></strong></td>
                            <td><?= e($row['type']) ?></td>
                            <td><?= e($row['main_elements']) ?></td>
                            <td><?= e($row['gost']) ?></td>
                            <td><a href="<?= base_url($row['slug'] . '/') ?>" class="link">Открыть →</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Карточки марок -->
        <?php if (!empty($seriesData['grades'])): ?>
        <section class="series-page__grade-cards">
            <h2 class="series-page__section-title">Каталог лент серии <?= e($series) ?></h2>
            <div class="series-grade-grid">
                <?php foreach ($seriesData['grades'] as $gradeSlug): ?>
                <?php $gd = $allGradeData[$gradeSlug] ?? null; ?>
                <a href="<?= base_url($gradeSlug . '/') ?>" class="series-grade-card">
                    <div class="series-grade-card__number">AISI <?= e($gd['number'] ?? strtoupper(str_replace('aisi-', '', $gradeSlug))) ?></div>
                    <?php if ($gd): ?>
                    <div class="series-grade-card__type"><?= e($gd['type']) ?></div>
                    <div class="series-grade-card__gost">ГОСТ: <?= e($gd['gost']) ?></div>
                    <div class="series-grade-card__magnetic"><?= $gd['magnetic'] ? 'Магнитная' : 'Немагнитная' ?></div>
                    <?php endif; ?>
                    <span class="series-grade-card__cta">Подробнее →</span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Сравнение марок -->
        <section class="series-page__comparison">
            <h2 class="series-page__section-title">Сравнение марок серии <?= e($series) ?></h2>
            <div class="series-page__text">
                <?= $seriesData['comparison_text'] ?? '' ?>
            </div>
        </section>

        <!-- Применение -->
        <section class="series-page__applications">
            <h2 class="series-page__section-title">Применение лент AISI <?= e($series) ?> серии</h2>
            <div class="series-page__text">
                <?= $seriesData['applications_text'] ?? '' ?>
            </div>
        </section>

        <!-- Поверхности и состояния поставки -->
        <section class="series-page__surfaces">
            <h2 class="series-page__section-title">Поверхности и состояния поставки</h2>
            <div class="series-page__text">
                <?= $seriesData['surfaces_text'] ?? '' ?>
            </div>
        </section>

        <!-- FAQ -->
        <?php if (!empty($seriesData['faq'])): ?>
        <section class="series-page__faq">
            <h2 class="series-page__section-title">Часто задаваемые вопросы о серии <?= e($series) ?></h2>
            <div class="faq-list">
                <?php foreach ($seriesData['faq'] as $i => $faq): ?>
                <details class="faq-item" <?= $i === 0 ? 'open' : '' ?>>
                    <summary class="faq-item__question"><?= e($faq['q']) ?></summary>
                    <p class="faq-item__answer"><?= e($faq['a']) ?></p>
                </details>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Другие серии AISI -->
        <section class="series-page__other-series">
            <h2 class="series-page__section-title">Другие серии AISI</h2>
            <div class="other-series-grid">
                <?php foreach ($seriesData['other_series'] ?? [] as $otherKey): ?>
                <?php if (isset($otherSeriesMeta[$otherKey])): ?>
                <?php $om = $otherSeriesMeta[$otherKey]; ?>
                <a href="<?= base_url($om['slug'] . '/') ?>" class="other-series-card">
                    <strong>Серия <?= e($otherKey) ?></strong>
                    <span><?= e($om['title']) ?></span>
                </a>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- CTA -->
        <section class="series-page__cta">
            <div class="cta-block">
                <h2 class="cta-block__title">Заказать ленту AISI <?= e($series) ?> серии</h2>
                <p class="cta-block__text">Нарезка от 1 метра, толщины 0,05–4 мм, ширина от 2,5 мм. Ответим за 15 минут.</p>
                <div class="cta-block__actions">
                    <button type="button" class="btn btn--primary js-open-request-modal">Оставить заявку</button>
                    <a href="tel:+78002003943" class="btn btn--ghost">+7 (800) 200-39-43</a>
                </div>
            </div>
        </section>

    </div>
</div>
