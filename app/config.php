<?php

$host = $_SERVER['HTTP_HOST'] ?? '';
$isProdDomain = ($host !== '' && strpos($host, 'lenta-nerzhavejushhaja-aisi.ru') !== false);
$envBasePath = getenv('BASE_PATH');

return [
    /** Префикс для статики. '' когда document root = public/ (см. DEPLOY.md). 'public/' если root = корень проекта. */
    'base_path' => ($envBasePath !== false) ? $envBasePath : ($isProdDomain ? 'public/' : ''),

    // Логин и хеш пароля. На сервере лучше задать через переменные окружения ADMIN_USER и ADMIN_PASS_HASH.
    'admin_user' => getenv('ADMIN_USER') ?: 'admin',
    'admin_pass_hash' => getenv('ADMIN_PASS_HASH') ?: '$2y$10$8Aq/GZZwHpneygV2cpi8Iefm0MLyph9AWVJ1ZW85aTFoTZqGq..5i',
    
    'site_name' => 'Каталог AISI',
    /** Канонический URL сайта (для robots.txt Sitemap и URL в sitemap.xml). Задайте SITE_URL в env при необходимости. */
    'site_url' => getenv('SITE_URL') ?: 'https://lenta-nerzhavejushhaja-aisi.ru',
    'company' => [
        'name'  => 'Каталог AISI',
        'url'   => getenv('SITE_URL') ?: 'https://lenta-nerzhavejushhaja-aisi.ru',
        'phone' => '+7 (800) 200-39-43',
        'email' => 'info@lenta-nerzhavejushhaja-aisi.ru',
    ],
    'regions' => [
        'nn'  => [
            'label'   => 'Нижний Новгород',
            'phone'   => '+7 (831) 211-97-56',
            'tel'     => '+78312119756',
            'address' => 'Московское ш., 320Б, корп. 1',
            'map_ll'  => '43.833424,56.303605',
            'map_pt'  => '43.833424,56.303605',
            'map_z'   => '17',
        ],
        'msk' => [
            'label'   => 'Москва',
            'phone'   => '+7 (495) 023-77-64',
            'tel'     => '+74950237764',
            'address' => 'ул. Южнопортовая, 7А, стр. 2',
            'map_ll'  => '37.693417,55.708440',
            'map_pt'  => '37.693417,55.708440',
            'map_z'   => '17',
        ],
        'spb' => [
            'label'   => 'Санкт-Петербург',
            'phone'   => '+7 (812) 426-56-38',
            'tel'     => '+78124265638',
            'address' => 'Московское ш., 161, лит. А (Шушары)',
            'map_ll'  => '30.425016,59.772968',
            'map_pt'  => '30.425016,59.772968',
            'map_z'   => '17',
        ],
    ],
    'region_default' => 'nn',
    'seo' => [
        'product_type' => 'Лента нержавеющая',
        'city_default' => 'Москве и РФ',
    ],
    
    'upload_dir' => __DIR__ . '/../public/uploads',
    'upload_max_size' => 5 * 1024 * 1024, // 5MB
    'upload_allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
    'upload_allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    
    'db_path' => __DIR__ . '/../storage/database.sqlite',

    /** Имя PDF «КП по контрактным поставкам»: сначала public/files/, иначе корень репозитория */
    'catalog_pdf' => 'kp-po-kontraktnym-postavkam.pdf',

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
