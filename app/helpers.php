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
        if ($text === null || $text === '') {
            return '';
        }
        $text = (string) $text;
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

/** Нормализация slug (как slugify, ограничение длины 200) */
if (!function_exists('normalize_slug')) {
    function normalize_slug($text) {
        $slug = slugify($text);
        return $slug === '' ? '' : mb_substr($slug, 0, 200);
    }
}

/**
 * Возвращает уникальный slug: если занят — добавляет суффикс -2, -3, …
 * $pdo — PDO, $table — 'products' или 'categories', $excludeId — id текущей записи (0 при создании).
 */
if (!function_exists('ensure_unique_slug')) {
    function ensure_unique_slug(PDO $pdo, $slug, $table, $excludeId = 0) {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return '';
        }
        $slug = normalize_slug($slug) ?: $slug;
        $slug = mb_substr($slug, 0, 200);
        $idCol = 'id';
        $slugCol = 'slug';
        
        $base = $slug;
        $n = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE {$slugCol} = ? AND {$idCol} != ?");
            $stmt->execute([$slug, (int) $excludeId]);
            if (!$stmt->fetch()) {
                return $slug;
            }
            $n++;
            $slug = $base . '-' . $n;
            if (mb_strlen($slug) > 200) {
                $slug = mb_substr($base, 0, 200 - 1 - strlen((string)$n)) . '-' . $n;
            }
        }
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
        if ($price === null || $price === '' || !is_numeric($price) || (float) $price <= 0) {
            return 'Цена по запросу';
        }
        return number_format((float) $price, 2, '.', ' ') . ' ₽/кг';
    }
}

/** Файл настроек сайта (ключ-значение) */
if (!defined('SITE_SETTINGS_FILE')) {
    define('SITE_SETTINGS_FILE', __DIR__ . '/../storage/site_settings.json');
}

if (!function_exists('get_site_setting')) {
    function get_site_setting($key) {
        $path = SITE_SETTINGS_FILE;
        if (!file_exists($path)) {
            return null;
        }
        $data = @json_decode(file_get_contents($path), true);
        return isset($data[$key]) ? $data[$key] : null;
    }
}

if (!function_exists('set_site_setting')) {
    function set_site_setting($key, $value) {
        $path = SITE_SETTINGS_FILE;
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $data = file_exists($path) ? (array) @json_decode(file_get_contents($path), true) : [];
        $data[$key] = $value;
        return file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false;
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
