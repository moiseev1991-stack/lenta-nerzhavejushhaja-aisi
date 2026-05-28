<?php $config = require __DIR__ . '/../config.php'; $base = rtrim($config['site_url'] ?? '', '/'); $host = preg_replace('#^https?://#', '', $base); ?>
User-agent: *
Disallow: /admin/
Disallow: /cart/
Disallow: /order/
Disallow: /search/
Allow: /*?page=
Disallow: /*?*
Allow: /public/

User-agent: Yandex
Disallow: /admin/
Disallow: /cart/
Disallow: /order/
Disallow: /search/
Allow: /*?page=
Disallow: /*?*
Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term&yclid&gclid&fbclid
Clean-param: th&cond&surf&spring&sort

Sitemap: <?= $base ?>/sitemap.xml
Host: <?= $host ?>
