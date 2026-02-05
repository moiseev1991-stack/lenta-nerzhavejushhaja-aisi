<?php
/**
 * Миграция: поля «статья / большой текст» для категорий.
 * Запуск: php storage/migrations/add_category_article_fields.php
 * (из корня проекта)
 */
$base = dirname(__DIR__, 2);
require $base . '/app/db.php';

$pdo = db();

$columns = [
    'content_title'    => 'TEXT',
    'content_body'     => 'TEXT',
    'content_is_active' => 'INTEGER DEFAULT 0',
    'content_updated_at' => 'TEXT',
];

$stmt = $pdo->query("PRAGMA table_info(categories)");
$existing = [];
while ($row = $stmt->fetch()) {
    $existing[$row['name']] = true;
}

foreach ($columns as $name => $def) {
    if (isset($existing[$name])) {
        echo "Column {$name} already exists, skip.\n";
        continue;
    }
    $pdo->exec("ALTER TABLE categories ADD COLUMN {$name} {$def}");
    echo "Added column: {$name}\n";
}

echo "Done.\n";
