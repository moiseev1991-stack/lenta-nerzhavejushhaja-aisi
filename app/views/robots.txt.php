<?php $config = require __DIR__ . '/../config.php'; $base = rtrim($config['site_url'] ?? '', '/'); ?>
User-agent: *
Allow: /$
Allow: /aisi-

Disallow: /admin/
Disallow: /bonus
Disallow: /contacts
Disallow: /delivery
Disallow: /payment
Disallow: /about
Disallow: /price
Disallow: /privacy-policy
Disallow: /assets/
Disallow: /uploads/
Disallow: /*?

Sitemap: <?= $base ?>/sitemap.xml
