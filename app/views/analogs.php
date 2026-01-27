<?php
// Шаблон для страницы /analogi/
$analogsData = require __DIR__ . '/../data/analogs.php';
?>
<div class="container" style="padding: 2rem 1rem;">
    <h1><?= e($pageH1) ?></h1>
    
    <?php if (!empty($pageIntro)): ?>
        <p><?= e($pageIntro) ?></p>
    <?php endif; ?>
    
    <?php if (isset($analogsData['matrix']) && !empty($analogsData['matrix'])): ?>
        <table style="width: 100%; border-collapse: collapse; margin: 2rem 0;">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">AISI</th>
                    <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">ГОСТ (аналог)</th>
                    <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Примечание</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($analogsData['matrix'] as $row): ?>
                    <tr>
                        <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong><?= e($row['aisi']) ?></strong></td>
                        <td style="padding: 0.75rem; border: 1px solid #ddd;"><?= e($row['gost']) ?></td>
                        <td style="padding: 0.75rem; border: 1px solid #ddd;"><?= e($row['note']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <?php if (!empty($pageBullets)): ?>
        <ul style="margin: 1.5rem 0; padding-left: 2rem;">
            <?php foreach ($pageBullets as $bullet): ?>
                <li style="margin: 0.5rem 0;"><?= e($bullet) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    
    <?php if (isset($analogsData['pages']) && !empty($analogsData['pages'])): ?>
        <div style="margin-top: 2rem;">
            <h2>Страницы по маркам:</h2>
            <ul style="padding-left: 2rem;">
                <?php foreach ($analogsData['pages'] as $key => $page): ?>
                    <?php if ($key !== 'analogi' && strpos($key, 'analogi-') === 0): ?>
                        <li style="margin: 0.5rem 0;">
                            <a href="<?= base_url($page['slug'] . '/') ?>"><?= e($page['h1']) ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
