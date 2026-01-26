<?php

if (!function_exists('db')) {
    function db() {
        static $pdo = null;
        
        if ($pdo === null) {
            $config = require __DIR__ . '/config.php';
            $dbPath = $config['db_path'];
            
            // Создаем директорию storage если её нет
            $storageDir = dirname($dbPath);
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Включаем foreign keys
            $pdo->exec('PRAGMA foreign_keys = ON');
        }
        
        return $pdo;
    }
}
