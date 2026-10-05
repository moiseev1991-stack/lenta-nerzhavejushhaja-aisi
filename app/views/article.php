<?php
/**
 * Страница статьи справочника.
 * Данные: $blogArticle (app/data/articles/*.php), $articleStock (наличие по маркам), $blogArticles (все статьи).
 */
$_stock = isset($articleStock) ? $articleStock : [];
$_body = render_article_body($blogArticle['body'], $_stock);
$_updated = isset($blogArticle['updated']) ? $blogArticle['updated'] : $blogArticle['published'];

// Похожие статьи: сначала явно указанные, затем остальные; максимум 3
$_related = [];
foreach ((isset($blogArticle['related']) ? $blogArticle['related'] : []) as $_rs) {
    if (isset($blogArticles[$_rs]) && $_rs !== $blogArticle['slug']) {
        $_related[$_rs] = $blogArticles[$_rs];
    }
}
foreach ($blogArticles as $_rs => $_ra) {
    if ($_rs !== $blogArticle['slug'] && !isset($_related[$_rs])) {
        $_related[$_rs] = $_ra;
    }
}
$_related = array_slice($_related, 0, 3, true);
?>
<article class="article">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
            <a href="<?= base_url() ?>">Главная</a>
            <span>/</span>
            <a href="<?= base_url('spravochnik/') ?>">Справочник</a>
            <span>/</span>
            <span><?= e($blogArticle['h1']) ?></span>
        </nav>

        <div class="article__main">
            <h1 class="article__title"><?= e($pageH1) ?></h1>
            <p class="article__meta">
                Подготовил: ИП Галанов А.&nbsp;О. (Каталог AISI) · Обновлено: <time datetime="<?= e($_updated) ?>"><?= e(format_ru_date($_updated)) ?></time>
            </p>

            <div class="article__summary">
                <strong>Коротко:</strong> <?= $blogArticle['summary'] ?>
            </div>

            <div class="article-cta article-cta--slim">
                <span class="article-cta__slim-text">Нужна нержавеющая лента? Ответим за 15 минут.</span>
                <button type="button" class="btn btn--primary js-open-request-modal">Узнать наличие и цену</button>
            </div>

            <div class="article__body">
                <?= $_body ?>
            </div>

            <?php if (!empty($blogArticle['faq'])): ?>
            <section class="article-faq" aria-labelledby="article-faq-title">
                <h2 id="article-faq-title">Вопросы и ответы</h2>
                <?php foreach ($blogArticle['faq'] as $_f): ?>
                <h3><?= e($_f['q']) ?></h3>
                <p><?= $_f['a'] ?></p>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>

            <?= article_cta_html(
                isset($blogArticle['cta_title']) ? $blogArticle['cta_title'] : '',
                isset($blogArticle['cta_text']) ? $blogArticle['cta_text'] : ''
            ) ?>

            <?php if (!empty($_related)): ?>
            <section class="article-related" aria-labelledby="article-related-title">
                <h2 id="article-related-title">Читайте также</h2>
                <ul class="article-related__list">
                    <?php foreach ($_related as $_ra): ?>
                    <li>
                        <a href="<?= e(base_url('spravochnik/' . $_ra['slug'] . '/')) ?>"><?= e($_ra['h1']) ?></a>
                        <span><?= e($_ra['description']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <?php endif; ?>
        </div>
    </div>
</article>
