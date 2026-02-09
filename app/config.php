<?php

return [
    // Логин и хеш пароля. На сервере лучше задать через переменные окружения ADMIN_USER и ADMIN_PASS_HASH.
    'admin_user' => getenv('ADMIN_USER') ?: 'admin',
    'admin_pass_hash' => getenv('ADMIN_PASS_HASH') ?: '$2y$10$8Aq/GZZwHpneygV2cpi8Iefm0MLyph9AWVJ1ZW85aTFoTZqGq..5i',
    
    'site_name' => 'Каталог AISI',
    /** Канонический URL сайта (для robots.txt Sitemap и URL в sitemap.xml). Задайте SITE_URL в env при необходимости. */
    'site_url' => getenv('SITE_URL') ?: 'https://lenta-nerzhavejushhaja-aisi.ru',
    'company' => [
        'name' => 'Компания',
        'url' => 'https://example.com',
        'phone' => '+7 (800) 200-39-43',
        // Задел для будущей Organization microdata
    ],
    'seo' => [
        'product_type' => 'Лента нержавеющая',
        'city_default' => 'Москве и РФ',
    ],
    
    'upload_dir' => __DIR__ . '/../public/uploads',
    'upload_max_size' => 5 * 1024 * 1024, // 5MB
    'upload_allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
    'upload_allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    
    'db_path' => __DIR__ . '/../storage/database.sqlite',

    /** Количество товаров на странице каталога (desktop + mobile) */
    'catalog_per_page' => 24,

    /* amoCRM форма: один источник правды для embed. iframe_src: задайте env AMO_FORM_IFRAME_SRC или укажите URL здесь */
    'amocrm' => [
        'form_id' => '1663854',
        'form_hash' => '81d6c52b4028728d57c87d1d9872cb22',
        'locale' => 'ru',
        'script_url' => 'https://forms.amocrm.ru/forms/assets/js/amoforms.js?1770385476',
        'iframe_src' => getenv('AMO_FORM_IFRAME_SRC') ?: null,
    ],
];
