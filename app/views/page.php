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
</div>
