<?php

if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('nowIso')) {
    function nowIso() {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('redirect')) {
    function redirect($to) {
        // Если путь уже начинается с http - используем как есть
        if (strpos($to, 'http') === 0) {
            header('Location: ' . $to);
            exit;
        }
        
        // Иначе формируем полный URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        
        // Убираем начальный слэш если есть, потом добавляем
        $to = ltrim($to, '/');
        $url = $protocol . '://' . $host . '/' . $to;
        
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('base_url')) {
    function base_url($path = '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        
        // Для встроенного PHP сервера используем просто host
        $base = '';
        
        $path = ltrim($path, '/');
        return $protocol . '://' . $host . ($path ? '/' . $path : '');
    }
}

if (!function_exists('slugify')) {
    function slugify($text) {
        $text = mb_strtolower($text, 'UTF-8');
        
        // Транслитерация кириллицы
        $translit = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ];
        
        $text = strtr($text, $translit);
        
        // Оставляем только латиницу, цифры, дефисы и подчеркивания
        $text = preg_replace('/[^a-z0-9\-_]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');
        
        return $text;
    }
}

if (!function_exists('require_admin')) {
    function require_admin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['admin'])) {
            redirect('/admin/login');
        }
    }
}

if (!function_exists('format_price')) {
    function format_price($price) {
        return number_format($price, 2, '.', ' ') . ' ₽/кг';
    }
}

/**
 * Определяет серию AISI по slug категории (aisi-304 -> 300, aisi-904l -> 900L).
 */
if (!function_exists('aisi_series_from_slug')) {
    function aisi_series_from_slug($slug) {
        if (preg_match('/^aisi-(\d+)/i', $slug, $m)) {
            $num = (int) $m[1];
            if ($num >= 900) return '900L';
            $first = (int) substr((string) $num, 0, 1);
            return (string) ($first * 100);
        }
        return 'other';
    }
}
