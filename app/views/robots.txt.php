<?php $config = require __DIR__ . '/../config.php'; $base = rtrim($config['site_url'] ?? '', '/'); $host = preg_replace('#^https?://#', '', $base); ?>
User-agent: *
Disallow: /admin/
Disallow: /cart/
Disallow: /order/
Disallow: /search/
Disallow: /*?*
Allow: /public/

User-agent: Yandex
Disallow: /admin/
Disallow: /cart/
Disallow: /order/
Disallow: /search/
Disallow: /*?*
Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term&yclid&gclid&fbclid

Sitemap: <?= $base ?>/sitemap.xml
Host: <?= $host ?>
