<?php
/**
 * Миграция: поле content_format для статьи категории (markdown | html).
 * Для существующих записей: если в content_body есть HTML-теги — ставим 'html', иначе 'markdown'.
 * Запуск: php storage/migrations/add_content_format.php (из корня проекта)
 */
$base = dirname(__DIR__, 2);
require $base . '/app/db.php';

$pdo = db();

$stmt = $pdo->query("PRAGMA table_info(categories)");
$existing = [];
while ($row = $stmt->fetch()) {
    $existing[$row['name']] = true;
}

if (!isset($existing['content_format'])) {
    $pdo->exec("ALTER TABLE categories ADD COLUMN content_format TEXT DEFAULT 'markdown'");
    echo "Added column: content_format\n";
} else {
    echo "Column content_format already exists.\n";
}

// Backfill: записи с пустым content_format или NULL — выставить по эвристике
$stmt = $pdo->query("SELECT id, content_body, content_format FROM categories WHERE content_body IS NOT NULL AND content_body != ''");
$update = $pdo->prepare("UPDATE categories SET content_format = ? WHERE id = ?");
$backfilled = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $current = trim((string) ($row['content_format'] ?? ''));
    if ($current !== '' && $current !== 'markdown' && $current !== 'html') {
        $current = '';
    }
    if ($current === '') {
        $body = $row['content_body'];
        $hasHtml = (bool) preg_match('/<(?:p|table|div|h[1-6]|ul|ol|li|tr|td|th|thead|tbody)\s?[\s>]/i', $body);
        $format = $hasHtml ? 'html' : 'markdown';
        $update->execute([$format, $row['id']]);
        $backfilled++;
    }
}
echo "Backfilled content_format for {$backfilled} categories.\n";
echo "Done.\n";
