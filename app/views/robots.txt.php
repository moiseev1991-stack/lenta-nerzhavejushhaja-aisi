<?php $config = require __DIR__ . '/../config.php'; $base = rtrim($config['site_url'] ?? '', '/'); ?>
User-agent: *
Allow: /$
Allow: /aisi-
Allow: /bonus
Allow: /bonus/
Allow: /about
Allow: /about/
Allow: /price
Allow: /price/
Allow: /delivery
Allow: /delivery/
Allow: /payment
Allow: /payment/
Allow: /contacts
Allow: /contacts/
Allow: /sitemap
Allow: /sitemap/
Allow: /privacy-policy
Allow: /privacy-policy/

Disallow: /admin/
Disallow: /assets/
Disallow: /uploads/
Disallow: /*?

Sitemap: <?= $base ?>/sitemap.xml
