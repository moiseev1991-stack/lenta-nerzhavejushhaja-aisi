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
            
            // Настройки кодировки для SQLite
            $pdo->exec('PRAGMA encoding = "UTF-8"');
            
            // Включаем foreign keys
            $pdo->exec('PRAGMA foreign_keys = ON');
            
            // Если таблиц нет (новый сервер/деплой) — создаём по init.sql
            $hasCategories = $pdo->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='categories'")->fetch();
            if (!$hasCategories) {
                $initFile = dirname($dbPath) . '/init.sql';
                if (is_readable($initFile)) {
                    $sql = file_get_contents($initFile);
                    $pdo->exec($sql);
                }
            }
        }
        
        return $pdo;
    }
}
