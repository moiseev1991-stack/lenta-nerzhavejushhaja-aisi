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
];
