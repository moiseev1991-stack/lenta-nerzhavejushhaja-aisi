<?php
/**
 * Sitemap: главная, все страницы категорий, все страницы товаров.
 * Подключается из public/index.php при запросе sitemap.xml (доступны $pdo).
 * Базовый URL берётся из config site_url (канонический адрес сайта).
 */
$config = require __DIR__ . '/../config.php';
$base = rtrim($config['site_url'] ?? 'https://lenta-nerzhavejushhaja-aisi.ru', '/');
$today = date('Y-m-d');

$stmt = $pdo->query('SELECT slug, updated_at FROM categories WHERE is_active = 1 ORDER BY slug');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query('
    SELECT p.slug AS product_slug, c.slug AS category_slug, p.updated_at
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE c.is_active = 1
    ORDER BY c.slug, p.slug
');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

function sitemapLastmod($updatedAt, $default) {
    if (empty($updatedAt)) return $default;
    $ts = strtotime($updatedAt);
    return $ts ? date('Y-m-d', $ts) : $default;
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= htmlspecialchars($base . '/') ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
<?php foreach ($categories as $cat): ?>
    <url>
        <loc><?= htmlspecialchars($base . '/' . $cat['slug'] . '/') ?></loc>
        <lastmod><?= sitemapLastmod($cat['updated_at'] ?? null, $today) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
<?php endforeach; ?>
<?php foreach ($products as $row): ?>
    <url>
        <loc><?= htmlspecialchars($base . '/' . $row['category_slug'] . '/' . $row['product_slug'] . '/') ?></loc>
        <lastmod><?= sitemapLastmod($row['updated_at'] ?? null, $today) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>
</urlset>
