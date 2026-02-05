<?php

if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Подставить картинку товару, если в БД пусто: по product_slug (часть URL без категории)
 * ищется файл {slug}.jpg в папке img/product_images_named.
 * $product передаётся по ссылке и может получить поле image.
 * $imagesDir — полный путь к папке с картинками (например __DIR__ . '/../img/product_images_named').
 */
if (!function_exists('resolve_product_image')) {
    function resolve_product_image(array &$product, $imagesDir) {
        if (!empty($product['image'])) return;
        $slug = isset($product['slug']) ? trim($product['slug']) : '';
        if ($slug === '') return;
        $filename = $slug . '.jpg';
        $path = rtrim($imagesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path)) {
            $product['image'] = '/img/product_images_named/' . $filename;
        }
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
 * Ключ сортировки категорий AISI по возрастанию (201, 202, 304, 304L, 310, 316Ti, 904L и т.д.).
 */
if (!function_exists('aisi_category_sort_key')) {
    function aisi_category_sort_key($slug) {
        $slug = (string) $slug;
        if (!preg_match('/^aisi-(\d+)(.*)$/i', $slug, $m)) return '9999';
        return sprintf('%04d', (int) $m[1]) . mb_strtolower(trim($m[2]));
    }
}

/**
 * Сортирует массив категорий по возрастанию марки AISI (по slug).
 */
if (!function_exists('sort_aisi_categories')) {
    function sort_aisi_categories(array &$categories) {
        usort($categories, function ($a, $b) {
            return strcasecmp(aisi_category_sort_key($a['slug'] ?? ''), aisi_category_sort_key($b['slug'] ?? ''));
        });
    }
}

/**
 * Нормализует отображаемое название марки: «Aisi 202» → «AISI 202», «AISI 304L» без изменений.
 */
if (!function_exists('normalize_aisi_display_name')) {
    function normalize_aisi_display_name($name) {
        $name = trim((string) $name);
        if ($name === '') return $name;
        return preg_replace('/^Aisi\s/i', 'AISI ', $name);
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

/**
 * Фиксированный список значений для фильтра «Толщина ленты, мм».
 * Единый источник правды для шаблонов и логики.
 */
/**
 * Конвертирует базовый Markdown в HTML. Поддерживает: заголовки (##, ###), жирный (**),
 * списки (- ), ссылки [text](url), параграфы (двойной перенос).
 */
if (!function_exists('markdown_to_html')) {
    function markdown_to_html($text) {
        if ($text === null || trim((string) $text) === '') {
            return '';
        }
        $s = (string) $text;
        $s = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        // Ссылки [text](url)
        $s = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($m) {
            $url = $m[2];
            $t = $m[1];
            if (preg_match('/^https?:\/\//i', $url)) {
                return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener">' . $t . '</a>';
            }
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $t . '</a>';
        }, $s);
        // Жирный **text**
        $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s);
        $lines = explode("\n", $s);
        $out = [];
        $i = 0;
        $n = count($lines);
        while ($i < $n) {
            $line = $lines[$i];
            $trimmed = trim($line);
            if ($trimmed === '') {
                $i++;
                continue;
            }
            if (preg_match('/^### (.+)$/', $trimmed, $m)) {
                $out[] = '<h3>' . $m[1] . '</h3>';
                $i++;
                continue;
            }
            if (preg_match('/^## (.+)$/', $trimmed, $m)) {
                $out[] = '<h2>' . $m[1] . '</h2>';
                $i++;
                continue;
            }
            if (preg_match('/^# (.+)$/', $trimmed, $m)) {
                $out[] = '<h1>' . $m[1] . '</h1>';
                $i++;
                continue;
            }
            if (preg_match('/^- (.+)$/', $trimmed)) {
                $list = [];
                while ($i < $n && preg_match('/^- (.+)$/', trim($lines[$i]), $m)) {
                    $list[] = '<li>' . $m[1] . '</li>';
                    $i++;
                }
                $out[] = '<ul>' . implode('', $list) . '</ul>';
                continue;
            }
            $para = [$trimmed];
            $i++;
            while ($i < $n && trim($lines[$i]) !== '' && !preg_match('/^#+\s/', trim($lines[$i])) && !preg_match('/^- /', trim($lines[$i]))) {
                $para[] = trim($lines[$i]);
                $i++;
            }
            $out[] = '<p>' . implode(' ', $para) . '</p>';
        }
        return implode("\n", $out);
    }
}

/**
 * Допустимые теги для вывода контента категории. Санитизация: белый список тегов,
 * для <a> — только безопасный href (http/https или относительный), при target="_blank" добавляется rel="noopener noreferrer".
 */
if (!function_exists('sanitize_category_content_html')) {
    function sanitize_category_content_html($html) {
        if ($html === null || trim((string) $html) === '') {
            return '';
        }
        $html = (string) $html;
        $allowed = '<p><br><h1><h2><h3><h4><strong><b><em><i><u><s><blockquote><ul><ol><li><a><table><thead><tbody><tr><th><td><div><span><pre><code>';
        $html = strip_tags($html, $allowed);
        // Санитизация ссылок: только безопасный href, rel при target="_blank"
        $html = preg_replace_callback('/<a\s+([^>]*)>/i', function ($m) {
            $attrs = $m[1];
            $href = '';
            $title = '';
            $rel = '';
            $target = '';
            if (preg_match('/href\s*=\s*["\']([^"\']*)["\']/i', $attrs, $h)) {
                $url = trim($h[1]);
                if (preg_match('/^(https?:|\/|#)/i', $url) && !preg_match('/^\s*javascript:/i', $url) && !preg_match('/^\s*data:/i', $url)) {
                    $href = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                } else {
                    $href = '#';
                }
            }
            if (preg_match('/title\s*=\s*["\']([^"\']*)["\']/i', $attrs, $t)) {
                $title = ' title="' . htmlspecialchars($t[1], ENT_QUOTES, 'UTF-8') . '"';
            }
            if (preg_match('/target\s*=\s*["\']_blank["\']/i', $attrs)) {
                $target = ' target="_blank"';
                $rel = ' rel="noopener noreferrer"';
            } elseif (preg_match('/rel\s*=\s*["\']([^"\']*)["\']/i', $attrs, $r)) {
                $rel = ' rel="' . htmlspecialchars($r[1], ENT_QUOTES, 'UTF-8') . '"';
            }
            return '<a href="' . $href . '"' . $title . $rel . $target . '>';
        }, $html);
        return $html;
    }
}

/**
 * Очистка форматирования: удаление inline-стилей, class, id и прочего мусора (Word/копипаст).
 * Для HTML: убираем атрибуты style, class, id у всех тегов.
 */
if (!function_exists('strip_article_formatting')) {
    function strip_article_formatting($html, $format = 'html') {
        if ($format === 'html') {
            $html = preg_replace('/\s+style\s*=\s*["\'][^"\']*["\']/i', '', $html);
            $html = preg_replace('/\s+class\s*=\s*["\'][^"\']*["\']/i', '', $html);
            $html = preg_replace('/\s+id\s*=\s*["\'][^"\']*["\']/i', '', $html);
            $html = preg_replace('/\s+lang\s*=\s*["\'][^"\']*["\']/i', '', $html);
        }
        return trim($html);
    }
}

if (!function_exists('get_filter_thicknesses')) {
    function get_filter_thicknesses() {
        return [
            0.05,
            0.08,
            0.1,
            0.12,
            0.15,
            0.2,
            0.25,
            0.3,
            0.4,
            0.5,
            0.6,
            0.7,
            0.8,
            1.0,
            1.2,
            1.5,
            2.0,
            2.5,
            3.0,
            4.0,
        ];
    }
}
