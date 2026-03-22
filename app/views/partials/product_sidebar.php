<?php
$config = $config ?? [];
$productBlocksData = $productBlocksData ?? [];
$sidebarItems = $productBlocksData['sidebar_items'] ?? [];
$company = $config['company'] ?? [];
$phone = $company['phone'] ?? '+7 (800) 200-39-43';
$email = 'ev18011@yandex.ru';

$sidebarIcons = [
    'phone' => '<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>',
    'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
    'file-text' => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
    'check' => '<path d="M20 6L9 17l-5-5"/>',
    'truck' => '<path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v2"/><path d="M14 9h4l3 3v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><path d="M15 18H9"/><circle cx="17" cy="18" r="2"/>',
    'package' => '<path d="M16.5 9.4l-9-5.19"/><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22" x2="12" y2="12"/>',
    'map-pin' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>',
    'scissors' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/>',
];

foreach ($sidebarItems as &$item) {
    if ($item['key'] === 'phone') { $item['text'] = $phone; $item['href'] = 'tel:' . preg_replace('/\D/', '', $phone); }
    elseif ($item['key'] === 'email') { $item['text'] = $email; $item['href'] = 'mailto:' . $email; }
}
unset($item);
?>
<div class="product-sidebar-compact" aria-label="Контакты и условия">
    <?php foreach ($sidebarItems as $item):
        $iconKey = $item['icon'] ?? 'package';
        $svgPath = $sidebarIcons[$iconKey] ?? $sidebarIcons['package'];
        $text = $item['text'] ?? '';
        $href = $item['href'] ?? null;
        $isButton = !empty($item['is_button']);
        $class = $item['class'] ?? '';
    ?>
    <div class="product-sidebar-compact__row">
        <span class="product-sidebar-compact__icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $svgPath ?></svg></span>
        <?php if ($isButton): ?>
            <button type="button" class="product-sidebar-compact__btn <?= e($class) ?>"><?= e($text) ?></button>
        <?php elseif ($href): ?>
            <a href="<?= e($href) ?>" class="product-sidebar-compact__link"><?= e($text) ?></a>
        <?php else: ?>
            <span class="product-sidebar-compact__text"><?= e($text) ?></span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
