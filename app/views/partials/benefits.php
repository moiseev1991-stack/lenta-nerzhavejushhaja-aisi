<?php
/**
 * Блок преимуществ (УТП). Варианты: category (строка/свайп), product (список).
 * Использует app/data/benefits.php.
 */
$benefitsVariant = isset($benefitsVariant) ? $benefitsVariant : 'category';
$benefitsItems = isset($benefitsItems) ? $benefitsItems : (function () {
    $path = dirname(__DIR__, 2) . '/data/benefits.php';
    return is_file($path) ? require $path : [];
})();

// Lucide-style иконки 24x24, stroke 2
$benefitsIcons = [
    'truck' => '<path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v2"/><path d="M14 9h4l3 3v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><path d="M15 18H9"/><circle cx="17" cy="18" r="2"/>',
    'package' => '<path d="M16.5 9.4l-9-5.19"/><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22" x2="12" y2="12"/>',
    'scissors' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/>',
    'arrow-left-right' => '<path d="M16 3h5v5"/><path d="M8 21H3v-5"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/>',
    'layers' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/>',
];

if (empty($benefitsItems)) return;

$isCategory = ($benefitsVariant === 'category');
$blockClass = 'usp-block usp-block--' . $benefitsVariant;
?>
<section class="<?= e($blockClass) ?>" aria-label="Преимущества">
    <?php if ($isCategory): ?>
        <div class="usp-block__track" role="list">
            <?php foreach ($benefitsItems as $item):
                $iconKey = $item['icon'] ?? 'package';
                $svgPath = $benefitsIcons[$iconKey] ?? $benefitsIcons['package'];
            ?>
                <div class="usp-block__item" role="listitem">
                    <span class="usp-block__icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $svgPath ?></svg>
                    </span>
                    <span class="usp-block__text" title="<?= e($item['title']) ?>"><?= e($item['title']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <h3 class="usp-block__title">Преимущества</h3>
        <ul class="usp-block__list">
            <?php foreach ($benefitsItems as $item):
                $iconKey = $item['icon'] ?? 'package';
                $svgPath = $benefitsIcons[$iconKey] ?? $benefitsIcons['package'];
            ?>
                <li class="usp-block__list-item">
                    <span class="usp-block__icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $svgPath ?></svg>
                    </span>
                    <span class="usp-block__text" title="<?= e($item['title']) ?>"><?= e($item['title']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
