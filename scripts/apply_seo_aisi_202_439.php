<?php
/**
 * Запись в SQLite SEO для aisi-202 и aisi-439 из app/data/bundled_category_seo.php
 * (удобно для локальной БД; на проде то же содержимое подставляется из файла при пустом content_body).
 */
$db = __DIR__ . '/../storage/database.sqlite';
$pdo = new PDO('sqlite:' . $db);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$bundled = require __DIR__ . '/../app/data/bundled_category_seo.php';
$now = gmdate('Y-m-d H:i:s');

$stmt = $pdo->prepare('UPDATE categories SET
    title = ?,
    description = ?,
    content_body = ?,
    content_format = ?,
    content_is_active = ?,
    content_updated_at = ?,
    updated_at = ?
    WHERE slug = ?');

foreach (['aisi-202', 'aisi-439'] as $slug) {
    if (!isset($bundled[$slug])) {
        fwrite(STDERR, "Missing bundled data for $slug\n");
        exit(1);
    }
    $b = $bundled[$slug];
    $stmt->execute([
        $b['title'],
        $b['description'],
        $b['content_body'],
        $b['content_format'],
        (int) $b['content_is_active'],
        $now,
        $now,
        $slug,
    ]);
}

echo "Updated aisi-202 and aisi-439.\n";
