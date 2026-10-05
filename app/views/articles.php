<?php
/** Список статей справочника. Данные: $blogArticles. */
?>
<div class="article article--list">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= base_url() ?>">Главная</a>
            <span>/</span>
            <span>Справочник</span>
        </nav>
        <h1 class="article__title"><?= e($pageH1) ?></h1>
        <p class="article__lead">Как выбрать марку нержавеющей стали, чем отличаются 304 и 316, какие аналоги у AISI по ГОСТ и что учесть при заказе ленты. Короткие ответы в начале, таблицы и выводы по делу.</p>

        <?php if (empty($blogArticles)): ?>
            <p>Статьи появятся здесь в ближайшее время.</p>
        <?php else: ?>
        <ul class="article-list">
            <?php foreach ($blogArticles as $_a): ?>
            <li class="article-list__item">
                <h2 class="article-list__title"><a href="<?= e(base_url('spravochnik/' . $_a['slug'] . '/')) ?>"><?= e($_a['h1']) ?></a></h2>
                <p class="article-list__desc"><?= e($_a['description']) ?></p>
                <p class="article-list__meta">Обновлено: <?= e(format_ru_date(isset($_a['updated']) ? $_a['updated'] : $_a['published'])) ?></p>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <div class="article-cta article-cta--slim">
            <span class="article-cta__slim-text">Не нашли нужную марку или размер? Подберём за 15 минут.</span>
            <button type="button" class="btn btn--primary js-open-request-modal">Узнать наличие и цену</button>
        </div>
    </div>
</div>
