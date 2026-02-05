<?php

return [
    'admin_user' => 'admin',
    'admin_pass_hash' => '$2y$10$M9wewMjEQ2n6MuW.Po70XOQPzqJIUCa4Oihj8zQ76YlIUIdqlcXH2', // admin123
    
    'site_name' => 'Каталог AISI',
    'company' => [
        'name' => 'Компания',
        'url' => 'https://example.com',
        // Задел для будущей Organization microdata
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
        'script_url' => 'https://forms.amocrm.ru/forms/assets/js/amoforms.js?1770113409',
        'iframe_src' => getenv('AMO_FORM_IFRAME_SRC') ?: null,
    ],
];
