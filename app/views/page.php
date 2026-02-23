<?php
// Шаблон для сервисных страниц
?>
<div class="container" style="padding: 2rem 1rem;">
    <h1><?= e($pageH1) ?></h1>
    
    <?php if (!empty($pageContent)): ?>
        <div class="page-content">
            <?= $pageContent ?>
        </div>
    <?php else: ?>
        <div class="page-content">
            <p>Содержимое страницы будет добавлено позже.</p>
        </div>
    <?php endif; ?>

    <?php if (isset($servicePageKey) && $servicePageKey === 'about'): ?>
    <!-- Прайс/каталог PDF на странице О компании -->
    <section class="home-pdf-section" style="margin-top: 2rem;">
        <h2 class="section-title">Прайс/каталог (PDF)</h2>
        <div class="pdf-viewer">
            <iframe src="<?= e(asset_url('files/metallinvest_lenta_shtrips.pdf')) ?>#view=FitH" width="100%" height="900" class="pdf-viewer__iframe" loading="lazy" title="Просмотр каталога PDF"></iframe>
        </div>
        <p class="pdf-viewer__fallback">Если PDF не отображается, <a href="<?= e(asset_url('files/metallinvest_lenta_shtrips.pdf')) ?>" target="_blank" rel="noopener">откройте в новой вкладке</a>.</p>
        <p><a class="btn btn--primary" href="<?= e(asset_url('files/metallinvest_lenta_shtrips.pdf')) ?>" download>Скачать PDF</a></p>
    </section>
    <?php endif; ?>
</div>
