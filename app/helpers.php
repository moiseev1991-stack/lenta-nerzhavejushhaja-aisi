<?php

if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Подставить картинку товару, если в БД пусто: по product_slug ищется файл в img/product_images_named.
 * Пробует: {slug}.jpg, {slug}.png, {slug} — копия.jpg (и варианты с пробелами).
 * $product передаётся по ссылке и может получить поле image.
 * $imagesDir — полный путь к папке с картинками.
 */
if (!function_exists('resolve_product_image')) {
    function resolve_product_image(array &$product, $imagesDir) {
        if (!empty($product['image'])) return;
        $slug = isset($product['slug']) ? trim($product['slug']) : '';
        if ($slug === '') return;
        $dir = rtrim($imagesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $candidates = [
            $slug . '.jpg',
            $slug . '.png',
            $slug . ' — копия.jpg',
            $slug . ' - копия.jpg',
        ];
        foreach ($candidates as $filename) {
            $path = $dir . $filename;
            if (is_file($path)) {
                $product['image'] = '/img/product_images_named/' . $filename;
                return;
            }
        }
        // Поиск по началу имени (напр. slug в имени "slug — копия.jpg" или "slug-1.jpg")
        foreach (['jpg', 'jpeg', 'png'] as $ext) {
            $list = @glob($dir . $slug . '*.' . $ext);
            if (!empty($list) && is_file($list[0])) {
                $product['image'] = '/img/product_images_named/' . basename($list[0]);
                return;
            }
        }
    }
}

/**
 * Если у товара картинка из /uploads/ и файла нет на диске — подставить из img/product_images_named по slug.
 * $uploadsDir — полный путь к public/uploads (например __DIR__ . '/uploads' из public/index.php).
 */
if (!function_exists('ensure_product_image')) {
    function ensure_product_image(array &$product, $imagesDir, $uploadsDir) {
        if (empty($product['image'])) {
            resolve_product_image($product, $imagesDir);
            return;
        }
        $img = ltrim($product['image'], '/');
        if (strpos($img, 'uploads/') !== 0) {
            return;
        }
        // Сохраняем полный относительный путь внутри uploads/ (включая подпапки вроде products/)
        $subpath = substr($img, strlen('uploads/'));
        $path = rtrim($uploadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subpath);
        if (!is_file($path)) {
            $product['image'] = '';
            resolve_product_image($product, $imagesDir);
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

/** URL для изображений (uploads/, img/...) — всегда через asset_url с учётом base_path */
if (!function_exists('image_url')) {
    function image_url($path) {
        $path = ltrim($path ?? '', '/');
        if (strpos($path, 'public/') === 0) {
            $path = substr($path, 7);
        }
        return $path !== '' ? asset_url($path) : '';
    }
}

if (!function_exists('asset_url')) {
    function asset_url($path, $versioned = false) {
        static $basePath = null;
        if ($basePath === null) {
            $configPath = __DIR__ . '/config.php';
            $basePath = '';
            if (is_file($configPath)) {
                $cfg = include $configPath;
                if (is_array($cfg) && array_key_exists('base_path', $cfg)) {
                    $basePath = (string)($cfg['base_path'] ?? '');
                }
            }
        }
        $path = ltrim($path ?? '', '/');
        $url = base_url($basePath . $path);
        if ($versioned) {
            $fullPath = realpath(__DIR__ . '/../public/' . $path);
            if ($fullPath && is_file($fullPath)) {
                $url .= '?v=' . filemtime($fullPath);
            }
        }
        return $url;
    }
}

if (!function_exists('base_url')) {
    function base_url($path = '') {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $protocol = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        
        // Для встроенного PHP сервера используем просто host
        $base = '';
        
        $path = ltrim($path, '/');
        return $protocol . '://' . $host . ($path ? '/' . $path : '');
    }
}

if (!function_exists('resolve_files_disk_path')) {
    /**
     * Путь к файлу из URL files/<имя>: учитывает дубликаты с/без .pdf (Windows «без расширения»).
     * Для КП из config также ищет в корне репозитория.
     */
    function resolve_files_disk_path(string $filename): ?string {
        if ($filename === '' || strpos($filename, '..') !== false) {
            return null;
        }
        $variants = [$filename];
        if (preg_match('/\.pdf$/i', $filename)) {
            $variants[] = preg_replace('/\.pdf$/i', '', $filename);
        } else {
            $variants[] = $filename . '.pdf';
        }
        $variants = array_values(array_unique($variants));

        $publicDir = __DIR__ . '/../public/files/';
        foreach ($variants as $v) {
            $p = $publicDir . $v;
            if (is_file($p)) {
                return $p;
            }
        }

        $config = require __DIR__ . '/config.php';
        $cat = basename((string) ($config['catalog_pdf'] ?? ''));
        $catStem = $cat !== '' ? preg_replace('/\.pdf$/i', '', $cat) : '';
        $reqStem = preg_replace('/\.pdf$/i', '', $filename);
        $isCatalog = ($cat !== '' && $reqStem === $catStem);

        if ($isCatalog) {
            $root = __DIR__ . '/../';
            foreach ($variants as $v) {
                $p = $root . $v;
                if (is_file($p)) {
                    return $p;
                }
            }
        }

        return null;
    }
}

if (!function_exists('catalog_pdf_fs_path')) {
    /** Файл КП на диске (см. resolve_files_disk_path). */
    function catalog_pdf_fs_path(): ?string {
        $config = require __DIR__ . '/config.php';
        $name = basename((string) ($config['catalog_pdf'] ?? 'kp-po-kontraktnym-postavkam.pdf'));
        return resolve_files_disk_path($name);
    }
}

if (!function_exists('catalog_pdf_url')) {
    /**
     * URL к PDF каталога — с тем же base_path, что и у CSS/JS (asset_url), иначе iframe даёт 404/HTML.
     */
    function catalog_pdf_url(): string {
        $config = require __DIR__ . '/config.php';
        $name = basename((string) ($config['catalog_pdf'] ?? 'kp-po-kontraktnym-postavkam.pdf'));
        return asset_url('files/' . $name);
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

/**
 * Цена для SEO title: "от X XXX ₽/кг" или "Цена по запросу"
 */
if (!function_exists('seo_price_string')) {
    function seo_price_string($pricePerKg) {
        if ($pricePerKg === null || $pricePerKg === '' || !is_numeric($pricePerKg) || (float) $pricePerKg <= 0) {
            return 'Цена по запросу';
        }
        return 'от ' . number_format((float) $pricePerKg, 0, '.', ' ') . ' ₽/кг';
    }
}

/**
 * Спеки товара для SEO: толщина и опционально ширина (мм)
 */
if (!function_exists('seo_product_specs')) {
    function seo_product_specs(array $product) {
        $parts = [];
        if (isset($product['thickness']) && $product['thickness'] !== null && $product['thickness'] !== '') {
            $t = (float) $product['thickness'];
            $parts[] = str_replace('.', ',', $t == (int) $t ? (string) (int) $t : (string) $t) . ' мм';
        }
        if (!empty($product['width']) && is_numeric($product['width'])) {
            $w = (float) $product['width'];
            $parts[] = str_replace('.', ',', $w == (int) $w ? (string) (int) $w : (string) $w) . ' мм';
        }
        return implode(' × ', $parts);
    }
}

/** Нормализовать марку для SEO: всегда возвращает «AISI {МАРКА}» в верхнем регистре */
if (!function_exists('seo_grade_part')) {
    function seo_grade_part($grade) {
        $g = trim($grade ?? 'AISI');
        return 'AISI ' . ltrim(preg_replace('/^aisi\s*/i', '', $g));
    }
}

/**
 * SEO Title для карточки товара.
 * При наличии цены: [Тип] AISI [Марка] ([ГОСТ]) [Спеки] [состояние] [поверхность] — от [Цена] ₽/кг | [Компания]
 * Без цены:        [Тип] AISI [Марка] ([ГОСТ]) [Спеки] [состояние] [поверхность] — нарезка от 1 м | [Компания]
 * ГОСТ-аналог подставляется из grades_data.php (например AISI 304 → 08Х18Н10).
 */
if (!function_exists('seo_product_title')) {
    function seo_product_title(array $product, array $config) {
        $type  = $config['seo']['product_type'] ?? 'Лента нержавеющая';
        $grade = seo_grade_part($product['category_name'] ?? 'AISI');
        $gradeData = get_grade_data($product['category_slug'] ?? '');
        $gostPart = ($gradeData && !empty($gradeData['gost'])) ? ' (' . $gradeData['gost'] . ')' : '';
        $specs = seo_product_specs($product);
        $condition = trim((string)($product['condition'] ?? ''));
        $condLabel = $condition !== '' && function_exists('seo_condition_label') ? seo_condition_label($condition) : '';
        $surface = trim((string)($product['surface'] ?? ''));
        $hasPrice = isset($product['price_per_kg']) && (float)$product['price_per_kg'] > 0;
        $company = $config['company']['name'] ?? 'Каталог AISI';

        $head = trim($type . ' ' . $grade . $gostPart);
        $parts = array_filter([$head, $specs, $condLabel, $surface], function ($p) { return $p !== ''; });
        $middle = implode(' ', $parts);

        if ($hasPrice) {
            $tail = '— от ' . number_format((float)$product['price_per_kg'], 0, '.', ' ') . ' ₽/кг';
        } else {
            $tail = '— нарезка от 1 м, доставка по РФ';
        }
        return $middle . ' ' . $tail . ' | ' . $company;
    }
}

/**
 * Автогенерация уникального описания для карточки товара из 7 блоков.
 * Описание уникально для каждого SKU за счёт комбинации параметров.
 */
if (!function_exists('generate_product_description_auto')) {
    function generate_product_description_auto(array $product): string {
        $grade     = strtoupper(trim((string)($product['category_name'] ?? '')));
        $thickness = isset($product['thickness']) && $product['thickness'] !== '' ? (float)$product['thickness'] : null;
        $width     = isset($product['width'])     && $product['width']     !== '' ? (float)$product['width']     : null;
        $surface   = trim((string)($product['surface']   ?? ''));
        $condition = trim((string)($product['condition'] ?? ''));
        $price     = isset($product['price_per_kg']) && (float)$product['price_per_kg'] > 0 ? (float)$product['price_per_kg'] : null;
        $inStock   = !empty($product['in_stock']);

        // Блок 1 — вводное предложение по марке
        $intros = [
            'AISI 201'   => 'Аустенитная нержавеющая лента с марганцем вместо никеля, бюджетный аналог серии 300; соответствует 12Х15Г9НД по ГОСТ 5632.',
            'AISI 202'   => 'Аустенитная лента серии 200 с частичной заменой никеля марганцем и азотом; аналог 12Х17Г9АН4 по ГОСТ 5632.',
            'AISI 301'   => 'Аустенитная лента с высокой степенью упрочнения наклёпом — идеальна для пружинных и упругих элементов; аналог 12Х17Н7.',
            'AISI 304'   => 'Аустенитная нержавеющая лента с высокой коррозионной стойкостью — универсальная базовая марка; аналог 08Х18Н10 по ГОСТ 5632.',
            'AISI 304L'  => 'Аустенитная лента с пониженным содержанием углерода (≤ 0,03 %), исключает межкристаллитную коррозию в сварных узлах; аналог 03Х18Н11.',
            'AISI 310'   => 'Жаростойкая аустенитная лента для эксплуатации до 1100 °C, высокое содержание Cr и Ni; аналог 20Х23Н18 по ГОСТ 5632.',
            'AISI 310S'  => 'Жаростойкая лента с пониженным углеродом — лучшая свариваемость при сохранении жаропрочности до 1100 °C; близкий аналог 10Х23Н18.',
            'AISI 316'   => 'Аустенитная кислотостойкая лента с молибденом (2–3 %), стойкая к хлоридам и морской воде; аналог 08Х17Н13М2 по ГОСТ 5632.',
            'AISI 316L'  => 'Кислотостойкая лента с молибденом и пониженным углеродом (≤ 0,03 %) для сварных конструкций в агрессивных средах; аналог 03Х17Н14М3.',
            'AISI 316Ti' => 'Кислотостойкая лента с молибденом и титановой стабилизацией — выдерживает нагрев и агрессивные среды одновременно; аналог 10Х17Н13М2Т.',
            'AISI 321'   => 'Жаростойкая Ti-стабилизированная лента для работы при 450–850 °C без риска МКК; аналог 12Х18Н10Т по ГОСТ 5632.',
            'AISI 409'   => 'Ферритная лента с минимальным содержанием хрома (10,5–11,75 %), бюджетная марка для выхлопных систем; близкий аналог 08Х13.',
            'AISI 420'   => 'Мартенситная лента, закаливается до высокой твёрдости — применяется для ножей, пружин и режущего инструмента; аналог 20Х13/30Х13.',
            'AISI 430'   => 'Базовая ферритная магнитная лента с хорошей стойкостью к атмосферным воздействиям и умеренным средам; аналог 12Х17 по ГОСТ 5632.',
            'AISI 431'   => 'Мартенситная лента с добавлением никеля — повышенная прочность и твёрдость после термообработки; аналог 14Х17Н2 по ГОСТ 5632.',
            'AISI 439'   => 'Ферритная Ti-стабилизированная лента для выхлопных систем и теплообменников; близкий аналог 08Х17Т по ГОСТ 5632.',
            'AISI 441'   => 'Ферритная лента с двойной стабилизацией Ti+Nb — повышенная термостойкость в автомобильных и теплообменных применениях; близкий аналог 08Х17Т.',
            'AISI 904L'  => 'Супераустенитная лента с высоким содержанием Ni (23–28 %) и молибдена (4–5 %) — исключительная стойкость к серной кислоте и морской воде; аналог 06ХН28МДТ.',
        ];
        // Нормализуем название марки для поиска
        $gradeKey = preg_replace('/^AISI\s+/i', 'AISI ', $grade);
        $intro = $intros[$gradeKey] ?? ('Нержавеющая лента ' . $gradeKey . ' — коррозионностойкая сталь.');

        // Блок 2 — параметры
        $parts = [];
        if ($thickness !== null) {
            $parts[] = 'Толщина ' . rtrim(rtrim(number_format($thickness, 4, ',', ''), '0'), ',') . ' мм';
        }
        if ($width !== null) {
            $parts[] = 'ширина ' . rtrim(rtrim(number_format($width, 2, ',', ''), '0'), ',') . ' мм';
        }
        if ($surface !== '') {
            $parts[] = 'поверхность ' . $surface;
        }
        $conditionLabels = ['soft' => 'мягкая', 'hard' => 'нагартованная', 'semi_hard' => 'полугартованная'];
        $condLabel = $conditionLabels[$condition] ?? $condition;
        if ($condLabel !== '') {
            $parts[] = 'состояние — ' . $condLabel;
        }
        $paramsBlock = count($parts) ? ucfirst(implode(', ', $parts)) . '.' : '';

        // Блок 3 — расшифровка поверхности
        $surfaceDescriptions = [
            'BA'  => 'Поверхность BA (bright annealed) — зеркально-гладкая, высокая отражательная способность после светлого отжига в защитной атмосфере.',
            '2B'  => 'Поверхность 2B — ровная матовая после холодной прокатки, рекристаллизационного отжига и лёгкого дрессировочного прохода.',
            '2BA' => 'Поверхность 2BA — промежуточный вариант: признаки светлого отжига при сохранении матовости 2B.',
            '4N'  => 'Поверхность 4N — шлифованная, равномерный матовый блеск без направленной структуры.',
        ];
        $surfaceBlock = isset($surfaceDescriptions[$surface]) ? $surfaceDescriptions[$surface] : '';

        // Блок 4 — расшифровка состояния
        $conditionDescriptions = [
            'soft'      => 'Мягкое (отожжённое) состояние обеспечивает максимальную пластичность для глубокой вытяжки, гибки и штамповки.',
            'hard'      => 'Нагартованное состояние — повышенная твёрдость и упругость за счёт пластической деформации при холодной прокатке.',
            'semi_hard' => 'Полугартованное состояние — промежуточный баланс пластичности и упругости; подходит для формования с умеренными деформациями.',
        ];
        $conditionBlock = isset($conditionDescriptions[$condition]) ? $conditionDescriptions[$condition] : '';

        // Блок 5 — применение по диапазону толщины
        $applicationBlock = '';
        if ($thickness !== null) {
            if ($thickness <= 0.20) {
                $applicationBlock = 'Тонкая лента — для экранирования, прокладок, обмотки, облицовки и точных пружинных элементов.';
            } elseif ($thickness <= 0.50) {
                $applicationBlock = 'Подходит для кожухов, коробов, бандажных лент и несложной штамповки.';
            } elseif ($thickness <= 0.80) {
                $applicationBlock = 'Монтажные перфоленты, элементы вентиляции, кабельные трассы и несущие кронштейны лёгкой серии.';
            } elseif ($thickness <= 1.50) {
                $applicationBlock = 'Кронштейны, хомуты, траверсы и силовые монтажные полосы повышенной жёсткости.';
            } else {
                $applicationBlock = 'Силовые детали и крепления повышенной жёсткости для нагруженных узлов.';
            }
        }

        // Блок 6 — расчётная масса 1 п.м.
        $weightBlock = '';
        if ($thickness !== null && $width !== null) {
            $densities = [
                'AISI 201' => 7800, 'AISI 202' => 7800,
                'AISI 301' => 7930, 'AISI 304' => 7930, 'AISI 304L' => 7930,
                'AISI 310' => 7930, 'AISI 310S' => 7930,
                'AISI 316' => 7930, 'AISI 316L' => 7930, 'AISI 316Ti' => 7930,
                'AISI 321' => 7930,
                'AISI 409' => 7700, 'AISI 420' => 7700, 'AISI 430' => 7700,
                'AISI 431' => 7700, 'AISI 439' => 7700, 'AISI 441' => 7700,
                'AISI 904L' => 7990,
            ];
            $density = $densities[$gradeKey] ?? 7900;
            $massPerMeter = round($thickness / 1000 * $width / 1000 * $density, 4);
            $massStr = rtrim(rtrim(number_format($massPerMeter, 4, ',', ' '), '0'), ',');
            $weightBlock = 'Теоретическая масса 1 п.м.: ' . $massStr . ' кг (плотность ' . number_format($density, 0, ',', ' ') . ' кг/м³).';
        }

        // Блок 7 — сервис и как заказать
        $serviceBlock = 'Отматываем от 1 метра, режем в размер от 2,5 мм. Склад в Москве, доставка по РФ. Отправьте заявку — ответим за 15 минут, подготовим счёт.';

        $blocks = array_filter([
            $intro,
            $paramsBlock,
            $surfaceBlock,
            $conditionBlock,
            $applicationBlock,
            $weightBlock,
            $serviceBlock,
        ], function($b) { return $b !== ''; });

        return implode(' ', $blocks);
    }
}

/**
 * Компактное описание товара для карточки (600–900 знаков).
 * 1–2 абзаца: что это, для чего, как заказать.
 */
if (!function_exists('generate_product_description_compact')) {
    function generate_product_description_compact(array $product, int $maxLen = 850): string {
        $full = generate_product_description_auto($product);
        if (mb_strlen($full) <= $maxLen) {
            return $full;
        }
        $sentences = preg_split('/(?<=[.!?])\s+/u', $full, -1, PREG_SPLIT_NO_EMPTY);
        $result = '';
        foreach ($sentences as $s) {
            if (mb_strlen($result . $s) + 1 <= $maxLen) {
                $result .= ($result ? ' ' : '') . $s;
            } else {
                break;
            }
        }
        return $result ?: mb_substr($full, 0, $maxLen - 3) . '…';
    }
}

/**
 * SEO Description для карточки товара.
 * Использует поле description из БД как override, затем автогенерацию.
 */
if (!function_exists('seo_product_description')) {
    function seo_product_description(array $product, array $config) {
        $overrideDesc = trim((string) ($product['description'] ?? ''));
        if ($overrideDesc !== '') {
            return $overrideDesc;
        }
        return generate_product_description_auto($product);
    }
}

/**
 * SEO H1 для товара: [Тип] AISI [Марка] ([ГОСТ]) [Спеки] [состояние] [поверхность]
 * ГОСТ-аналог берётся из grades_data.php — необходим для ранжирования по русским марочным запросам.
 */
if (!function_exists('seo_product_h1')) {
    function seo_product_h1(array $product, array $config) {
        $type  = $config['seo']['product_type'] ?? 'Лента нержавеющая';
        $grade = seo_grade_part($product['category_name'] ?? 'AISI');
        $gradeData = get_grade_data($product['category_slug'] ?? '');
        $gostPart = ($gradeData && !empty($gradeData['gost'])) ? ' (' . $gradeData['gost'] . ')' : '';
        $specs = seo_product_specs($product);
        $condition = trim((string)($product['condition'] ?? ''));
        $condLabel = $condition !== '' && function_exists('seo_condition_label') ? seo_condition_label($condition) : '';
        $surface = trim((string)($product['surface'] ?? ''));

        $head = trim($type . ' ' . $grade . $gostPart);
        $parts = array_filter([$head, $specs, $condLabel, $surface], function ($p) { return $p !== ''; });
        return implode(' ', $parts);
    }
}

/**
 * Возвращает данные марки AISI из grades_data.php по slug категории.
 * Кешируется статически. Возвращает null если марка не найдена.
 */
if (!function_exists('get_grade_data')) {
    function get_grade_data(string $slug): ?array {
        static $allGrades = null;
        if ($allGrades === null) {
            $path = __DIR__ . '/data/grades_data.php';
            $allGrades = is_file($path) ? (require $path) : [];
        }
        return $allGrades[$slug] ?? null;
    }
}

/**
 * SEO Title для категории. Использует поле title из БД как override.
 * Если передан $productCount и grade data с ГОСТ-аналогом — использует расширенный шаблон.
 */
if (!function_exists('seo_category_title')) {
    function seo_category_title(array $category, $minPrice, array $config, ?int $productCount = null) {
        $override = trim((string)($category['title'] ?? ''));
        if ($override !== '') {
            return $override;
        }
        $grade = seo_grade_part($category['name'] ?? 'AISI');
        $gradeData = get_grade_data($category['slug'] ?? '');
        if ($gradeData && isset($gradeData['gost']) && $gradeData['gost'] !== '') {
            $gost = $gradeData['gost'];
            $countPart = $productCount !== null && $productCount > 0 ? ' | ' . $productCount . ' типоразмеров' : '';
            return $grade . ' — купить, цена, аналог ' . $gost . $countPart;
        }
        $siteDomain = preg_replace('#^https?://#', '', rtrim($config['site_url'] ?? 'lenta-nerzhavejushhaja-aisi.ru', '/'));
        return trim('Лента нержавеющая ' . $grade) . ' купить — цены, размеры, доставка по России | ' . $siteDomain;
    }
}

/**
 * SEO Description для категории. Использует поле description из БД как override.
 * $minThickness/$maxThickness — опциональные мин/макс толщины из БД для динамической мета.
 */
if (!function_exists('seo_category_description')) {
    function seo_category_description(array $category, array $config, $minThickness = null, $maxThickness = null) {
        $override = trim((string)($category['description'] ?? ''));
        if ($override !== '') {
            return $override;
        }
        $grade = seo_grade_part($category['name'] ?? 'AISI');
        $phone = $config['company']['phone'] ?? '8-800-200-39-43';
        $gradeNum = preg_replace('/^AISI\s*/i', '', $grade);
        $thRange = '';
        if ($minThickness !== null && $maxThickness !== null && $minThickness != $maxThickness) {
            $thRange = 'Толщины от ' . $minThickness . ' до ' . $maxThickness . ' мм, ';
        } elseif ($minThickness !== null) {
            $thRange = 'Толщины от ' . $minThickness . ' мм, ';
        } else {
            $thRange = 'Толщины 0,05–4 мм, ';
        }
        return 'Нержавеющая лента AISI ' . $gradeNum . ' в наличии. ' . $thRange . 'ширины от 2,5 мм. Нарезка от 1 метра, доставка по России. Цена по запросу. ' . $phone . '.';
    }
}

/**
 * SEO H1 для категории: [Тип] AISI [Марка]
 */
if (!function_exists('seo_category_h1')) {
    function seo_category_h1(array $category, array $config) {
        $override = trim((string)($category['h1'] ?? ''));
        if ($override !== '') {
            return $override;
        }
        $type = $config['seo']['product_type'] ?? 'Лента нержавеющая';
        $grade = seo_grade_part($category['name'] ?? 'AISI');
        return trim($type . ' ' . $grade);
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
 * Подставляет SEO из app/data/bundled_category_seo.php.
 * title/description применяются всегда (перезаписывают DB).
 * content_body/content_format/content_is_active — только если в DB нет content_body.
 */
if (!function_exists('merge_bundled_category_seo')) {
    function merge_bundled_category_seo(array &$category) {
        static $bundled = null;
        if ($bundled === null) {
            $path = __DIR__ . '/data/bundled_category_seo.php';
            $bundled = is_file($path) ? require $path : [];
        }
        $slug = (string)($category['slug'] ?? '');
        if ($slug === '' || !isset($bundled[$slug])) {
            return;
        }
        foreach (['title', 'description'] as $key) {
            if (isset($bundled[$slug][$key])) {
                $category[$key] = $bundled[$slug][$key];
            }
        }
        if (trim((string)($category['content_body'] ?? '')) !== '') {
            return;
        }
        foreach (['content_body', 'content_format', 'content_is_active'] as $key) {
            if (isset($bundled[$slug][$key])) {
                $category[$key] = $bundled[$slug][$key];
            }
        }
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

/**
 * Похожие/популярные товары для страницы товара.
 * Возвращает ['items' => array до 4 товаров с category_slug, category_name, 'is_popular_fallback' => true если все из fallback].
 */
if (!function_exists('get_related_products')) {
    function get_related_products(PDO $pdo, array $product, $limit = 4) {
        $cid = (int) $product['category_id'];
        $pid = (int) $product['id'];
        $th = $product['thickness'] !== null && $product['thickness'] !== '' ? (float) $product['thickness'] : null;
        $w = $product['width'] !== null && $product['width'] !== '' ? (float) $product['width'] : null;
        $surf = isset($product['surface']) && trim((string) $product['surface']) !== '' ? trim($product['surface']) : null;
        $orderClause = 'ORDER BY (ABS(COALESCE(p.thickness, 0) - ' . ($th !== null ? (float) $th : '0') . ') + ABS(COALESCE(p.width, 0) - ' . ($w !== null ? (float) $w : '0') . '))';
        $related = [];
        $excludeIds = [$pid];

        // Шаг A: та же марка (category), та же поверхность, по близости толщина/ширина
        $sqlA = "SELECT p.*, c.slug AS category_slug, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.category_id = ? AND p.id != ?";
        $paramsA = [$cid, $pid];
        if ($surf !== null && $surf !== '') {
            $sqlA .= " AND p.surface = ?";
            $paramsA[] = $surf;
        } else {
            $sqlA .= " AND (p.surface IS NULL OR p.surface = '')";
        }
        $sqlA .= " " . $orderClause . " LIMIT " . (int) $limit;
        $stmt = $pdo->prepare($sqlA);
        $stmt->execute($paramsA);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $related[] = $row;
            $excludeIds[] = (int) $row['id'];
        }

        $fromSimilar = count($related);

        // Шаг B: та же категория, без фильтра по поверхности, добить до limit
        if (count($related) < $limit) {
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $sqlB = "SELECT p.*, c.slug AS category_slug, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.category_id = ? AND p.id NOT IN ($placeholders) " . $orderClause . " LIMIT " . ((int) $limit - count($related));
            $stmt = $pdo->prepare($sqlB);
            $stmt->execute(array_merge([$cid], $excludeIds));
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $related[] = $row;
                $excludeIds[] = (int) $row['id'];
            }
        }

        // Шаг C: популярные (с картинкой, в наличии, с ценой), добить слоты
        if (count($related) < $limit) {
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $sqlC = "SELECT p.*, c.slug AS category_slug, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.category_id = ? AND p.id NOT IN ($placeholders) ORDER BY (CASE WHEN p.image IS NOT NULL AND p.image != '' THEN 1 ELSE 0 END) DESC, (CASE WHEN p.in_stock = 1 THEN 1 ELSE 0 END) DESC, (CASE WHEN p.price_per_kg IS NOT NULL AND p.price_per_kg > 0 THEN 1 ELSE 0 END) DESC, p.id LIMIT " . ((int) $limit - count($related));
            $stmt = $pdo->prepare($sqlC);
            $stmt->execute(array_merge([$cid], $excludeIds));
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $related[] = $row;
            }
        }

        return [
            'items' => array_slice($related, 0, $limit),
            'is_popular_fallback' => ($fromSimilar === 0 && count($related) > 0),
        ];
    }
}

/**
 * Товары с той же толщиной в других марках (для перелинковки).
 * Возвращает до $limit товаров из других категорий с той же толщиной.
 */
if (!function_exists('get_product_links_same_thickness')) {
    function get_product_links_same_thickness(PDO $pdo, array $product, int $limit = 3): array {
        $th = $product['thickness'] !== null && $product['thickness'] !== '' ? (float)$product['thickness'] : null;
        if ($th === null) return [];
        $cid = (int)$product['category_id'];
        $pid = (int)$product['id'];
        $stmt = $pdo->prepare('
            SELECT p.*, c.slug AS category_slug, c.name AS category_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.category_id != ? AND p.id != ? AND p.thickness = ? AND p.in_stock = 1
            ORDER BY c.slug
            LIMIT ?
        ');
        $stmt->execute([$cid, $pid, $th, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Другие размеры той же марки (та же категория, другая толщина/ширина).
 * Возвращает до $limit товаров, исключая текущий.
 */
if (!function_exists('get_product_links_other_sizes')) {
    function get_product_links_other_sizes(PDO $pdo, array $product, int $limit = 3): array {
        $cid = (int)$product['category_id'];
        $pid = (int)$product['id'];
        $stmt = $pdo->prepare('
            SELECT p.*, c.slug AS category_slug, c.name AS category_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.category_id = ? AND p.id != ? AND p.in_stock = 1
            ORDER BY p.thickness, p.width
            LIMIT ?
        ');
        $stmt->execute([$cid, $pid, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * FAQ для карточки товара. Вопросы с подстановкой марки и спеок.
 * Возвращает [['question' => ..., 'answer' => ...], ...].
 */
if (!function_exists('get_product_faq')) {
    function get_product_faq(array $product): array {
        $grade = seo_grade_part($product['category_name'] ?? 'AISI');
        $specs = seo_product_specs($product);
        $gradeSpecs = trim($grade . ' ' . $specs);

        return [
            [
                'question' => 'Есть ли лента ' . $gradeSpecs . ' в наличии?',
                'answer' => 'Наличие уточняйте по телефону +7 (800) 200-39-43 или по email. Менеджер ответит в течение 15 минут. Отгрузка со склада в Москве и филиалов.',
            ],
            [
                'question' => 'Можно ли заказать резку в размер?',
                'answer' => 'Да, режем в размер от 2,5 мм. Отмотка от 1 метра. Упаковка под перевозку и сертификаты качества предоставляются с заказом.',
            ],
            [
                'question' => 'Работаете ли с НДС?',
                'answer' => 'Да, работаем с НДС и без НДС. Счёт выставляем в течение рабочего дня. Реквизиты и условия оплаты — на странице «Способы оплаты».',
            ],
            [
                'question' => 'Есть ли доставка транспортной компанией?',
                'answer' => 'Да, доставляем по всей России: самовывоз, курьер по Москве, ТК, Почта России. Стоимость и сроки зависят от региона. Подробнее — на странице «Доставка».',
            ],
            [
                'question' => 'Как быстро выставляется счёт?',
                'answer' => 'Счёт выставляем в течение рабочего дня после получения заявки. Обычно в течение 1–2 часов в рабочее время (пн–пт 9:00–18:00).',
            ],
            [
                'question' => 'Можно ли купить небольшую партию?',
                'answer' => 'Да, отматываем от 1 метра. Минимальная сумма заказа для доставки — 10 000 ₽. При самовывозе ограничений по минимальной партии нет.',
            ],
        ];
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

/* ───────────────────────── Справочник (статьи) ───────────────────────── */

/**
 * Все статьи справочника из app/data/articles/*.php (ключ — slug), по возрастанию 'order'.
 * Статьи лежат в файлах, а не в БД: SQLite в репозиторий не попадает и при деплое не обновляется.
 */
if (!function_exists('get_articles')) {
    function get_articles() {
        static $all = null;
        if ($all === null) {
            $all = [];
            $files = glob(__DIR__ . '/data/articles/*.php');
            foreach (($files ?: []) as $f) {
                $a = require $f;
                if (is_array($a) && !empty($a['slug'])) {
                    $all[$a['slug']] = $a;
                }
            }
            uasort($all, function ($x, $y) {
                $ox = isset($x['order']) ? (int) $x['order'] : 999;
                $oy = isset($y['order']) ? (int) $y['order'] : 999;
                return $ox === $oy ? strcmp((string) $x['slug'], (string) $y['slug']) : $ox - $oy;
            });
        }
        return $all;
    }
}

if (!function_exists('get_article')) {
    function get_article($slug) {
        $all = get_articles();
        return isset($all[$slug]) ? $all[$slug] : null;
    }
}

/** «2026-10-05» → «5 октября 2026» */
if (!function_exists('format_ru_date')) {
    function format_ru_date($ymd) {
        $months = ['', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        $ts = strtotime((string) $ymd);
        if (!$ts) {
            return '';
        }
        return (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}

/**
 * Наличие по маркам для блока «В наличии» в статье: число типоразмеров и диапазоны размеров.
 * Возвращает [slug => ['name'=>, 'count'=>, 'th_min'=>, 'th_max'=>, 'w_min'=>, 'w_max'=>]].
 */
if (!function_exists('get_grades_stock')) {
    function get_grades_stock(PDO $pdo, array $slugs) {
        $out = [];
        foreach ($slugs as $slug) {
            $stmt = $pdo->prepare('
                SELECT c.name AS name, COUNT(p.id) AS cnt,
                       MIN(CASE WHEN p.thickness > 0 THEN p.thickness END) AS th_min,
                       MAX(p.thickness) AS th_max,
                       MIN(CASE WHEN p.width > 0 THEN p.width END) AS w_min,
                       MAX(p.width) AS w_max
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.id AND p.in_stock = 1
                WHERE c.slug = ? AND c.is_active = 1
                GROUP BY c.id
            ');
            $stmt->execute([$slug]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && (int) $row['cnt'] > 0) {
                $out[$slug] = [
                    'name'   => normalize_aisi_display_name($row['name']),
                    'count'  => (int) $row['cnt'],
                    'th_min' => $row['th_min'],
                    'th_max' => $row['th_max'],
                    'w_min'  => $row['w_min'],
                    'w_max'  => $row['w_max'],
                ];
            }
        }
        return $out;
    }
}

if (!function_exists('article_num')) {
    /** 0.05 → «0,05», 4.0 → «4» */
    function article_num($v) {
        $v = (float) $v;
        return str_replace('.', ',', rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.'));
    }
}

/** Склонение «типоразмер» по числу. */
if (!function_exists('article_sizes_word')) {
    function article_sizes_word($n) {
        $n = (int) $n;
        if ($n % 10 === 1 && $n % 100 !== 11) {
            return 'типоразмер';
        }
        if ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 12 || $n % 100 > 14)) {
            return 'типоразмера';
        }
        return 'типоразмеров';
    }
}

/** Блок призыва к заявке (кнопка открывает модалку amoCRM). */
if (!function_exists('article_cta_html')) {
    function article_cta_html($title = '', $text = '') {
        $config = require __DIR__ . '/config.php';
        $phone = isset($config['company']['phone']) ? $config['company']['phone'] : '+7 (800) 200-39-43';
        $title = $title !== '' ? $title : 'Подберём марку и размеры под вашу задачу';
        $text = $text !== '' ? $text : 'Ответим за 15 минут. Отматываем от 1 метра, режем от 2,5 мм, доставляем по всей России.';
        return '<aside class="article-cta">'
            . '<p class="article-cta__title">' . e($title) . '</p>'
            . '<p class="article-cta__text">' . e($text) . '</p>'
            . '<div class="article-cta__actions">'
            . '<button type="button" class="btn btn--primary btn--large js-open-request-modal">Узнать наличие и цену</button>'
            . '<a class="article-cta__phone" href="tel:+78002003943">' . e($phone) . '</a>'
            . '</div></aside>';
    }
}

/** Блок «В наличии» по маркам статьи. */
if (!function_exists('article_stock_html')) {
    function article_stock_html(array $stock) {
        if (empty($stock)) {
            return '';
        }
        $html = '<div class="article-stock"><p class="article-stock__title">В наличии на складе</p><ul class="article-stock__list">';
        foreach ($stock as $slug => $s) {
            $parts = [$s['count'] . ' ' . article_sizes_word($s['count'])];
            if ($s['th_min'] !== null && $s['th_max'] !== null) {
                $parts[] = 'толщина ' . article_num($s['th_min']) . ($s['th_min'] != $s['th_max'] ? '–' . article_num($s['th_max']) : '') . ' мм';
            }
            if ($s['w_min'] !== null && $s['w_max'] !== null && $s['w_max'] > 0) {
                $parts[] = 'ширина ' . article_num($s['w_min']) . ($s['w_min'] != $s['w_max'] ? '–' . article_num($s['w_max']) : '') . ' мм';
            }
            $html .= '<li><a href="' . e(base_url($slug . '/')) . '"><strong>Лента ' . e($s['name']) . '</strong></a> — ' . e(implode(', ', $parts)) . '</li>';
        }
        $html .= '</ul></div>';
        return $html;
    }
}

/**
 * Подстановка плейсхолдеров в тексте статьи:
 *   {{CTA}}, {{STOCK}}, {{ANALOGS_TABLE}}, {{FACTS:aisi-304}}, {{CHEM:aisi-304}}, {{MECH:aisi-304}}.
 * Таблицы строятся из app/data/grades_data.php — единый источник данных по маркам.
 */
if (!function_exists('render_article_body')) {
    function render_article_body($html, array $stock = []) {
        $html = str_replace('{{CTA}}', article_cta_html(), $html);
        $html = str_replace('{{STOCK}}', article_stock_html($stock), $html);
        $html = str_replace('{{ORDER_BLOCK}}',
            '<h2>Что указать в заявке</h2><ol>'
            . '<li>Марку стали, например AISI 304.</li>'
            . '<li>Толщину и ширину в миллиметрах.</li>'
            . '<li>Состояние (мягкая, нагартованная) и поверхность (2B, BA, 2BA, 4N).</li>'
            . '<li>Длину в метрах или вес в килограммах.</li>'
            . '<li>Город и способ получения: самовывоз (Нижний Новгород, Москва, Санкт-Петербург) или доставка.</li>'
            . '</ol><p>Ответим за 15 минут: подтвердим наличие и подготовим счёт.</p>', $html);

        if (strpos($html, '{{ANALOGS_TABLE}}') !== false) {
            $path = __DIR__ . '/data/grades_data.php';
            $grades = is_file($path) ? require $path : [];
            $t = '<div class="article-table-wrap"><table class="article-table"><thead><tr>'
                . '<th>AISI</th><th>ГОСТ 5632</th><th>EN (номер)</th><th>JIS</th><th>Тип стали</th></tr></thead><tbody>';
            foreach ($grades as $slug => $g) {
                $t .= '<tr><td><a href="' . e(base_url($slug . '/')) . '">AISI ' . e($g['number']) . '</a></td>'
                    . '<td>' . e($g['gost']) . '</td>'
                    . '<td>' . e($g['en_number']) . '</td>'
                    . '<td>' . e($g['jis']) . '</td>'
                    . '<td>' . e($g['type']) . '</td></tr>';
            }
            $t .= '</tbody></table></div>';
            $html = str_replace('{{ANALOGS_TABLE}}', $t, $html);
        }

        $html = preg_replace_callback('/\{\{(FACTS|CHEM|MECH):([a-z0-9\-]+)\}\}/', function ($m) {
            $g = get_grade_data($m[2]);
            if (!$g) {
                return '';
            }
            if ($m[1] === 'FACTS') {
                $rows = [
                    ['Тип стали', $g['type']],
                    ['Аналог по ГОСТ 5632', $g['gost']],
                    ['EN', $g['en_number'] . ' (' . $g['en_name'] . ')'],
                    ['JIS', $g['jis']],
                    ['Плотность', str_replace('.', ',', (string) $g['density']) . ' г/см³'],
                    ['Магнитность', $g['magnetic'] ? 'магнитная' : 'не магнитится в состоянии поставки'],
                ];
                $t = '<div class="article-table-wrap"><table class="article-table article-table--kv"><tbody>';
                foreach ($rows as $r) {
                    $t .= '<tr><th>' . e($r[0]) . '</th><td>' . e($r[1]) . '</td></tr>';
                }
                return $t . '</tbody></table></div>';
            }
            if ($m[1] === 'CHEM') {
                $t = '<div class="article-table-wrap"><table class="article-table"><thead><tr><th>Элемент</th><th>Содержание, %</th><th>Роль</th></tr></thead><tbody>';
                foreach ($g['chemical'] as $c) {
                    $t .= '<tr><td>' . e($c['element']) . '</td><td>' . e($c['range']) . '</td><td>' . e($c['note']) . '</td></tr>';
                }
                return $t . '</tbody></table></div>';
            }
            $t = '<div class="article-table-wrap"><table class="article-table article-table--kv"><tbody>';
            foreach ($g['mechanical'] as $r) {
                $t .= '<tr><th>' . e($r['property']) . '</th><td>' . e($r['value']) . '</td></tr>';
            }
            return $t . '</tbody></table></div>';
        }, $html);

        $html = preg_replace_callback('/\{\{(SIZES|CATALOG_SUMMARY|RANGE_TABLE|THIN_TABLE|SURFACE_COUNTS|WEIGHT_TABLE|DENSITY_TABLE)(?::([^}]*))?\}\}/', function ($m) {
            return article_data_block($m[1], isset($m[2]) ? $m[2] : '');
        }, $html);

        return $html;
    }
}

/* ─────── Блоки статей с данными каталога ─────── */

if (!function_exists('article_db_all')) {
    function article_db_all($sql, array $params = []) {
        $st = db()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}

/** Подпись состояния поставки (в БД встречаются и коды, и русские значения). */
if (!function_exists('article_cond_label')) {
    function article_cond_label($c) {
        $map = ['soft' => 'мягкая', 'semi_hard' => 'полунагартованная', 'hard' => 'нагартованная', 'extra_hard' => 'высоконагартованная'];
        $c = trim((string) $c);
        return isset($map[$c]) ? $map[$c] : $c;
    }
}

if (!function_exists('article_sort_labels')) {
    /** Упорядочить подписи состояний: мягкая → высоконагартованная; остальное в конце. */
    function article_sort_labels(array $labels) {
        $order = ['мягкая' => 1, 'полунагартованная' => 2, 'нагартованная' => 3, 'высоконагартованная' => 4];
        $labels = array_values(array_unique($labels));
        usort($labels, function ($a, $b) use ($order) {
            $oa = isset($order[$a]) ? $order[$a] : 9;
            $ob = isset($order[$b]) ? $order[$b] : 9;
            return $oa === $ob ? strcmp($a, $b) : $oa - $ob;
        });
        return $labels;
    }
}

if (!function_exists('article_table')) {
    /** Таблица статьи: $heads — заголовки, $rows — массив строк (значения уже экранированы или HTML-безопасны). */
    function article_table(array $heads, array $rows, $extraClass = '') {
        $t = '<div class="article-table-wrap"><table class="article-table' . ($extraClass !== '' ? ' ' . $extraClass : '') . '"><thead><tr>';
        foreach ($heads as $h) {
            $t .= '<th>' . e($h) . '</th>';
        }
        $t .= '</tr></thead><tbody>';
        foreach ($rows as $r) {
            $t .= '<tr>';
            foreach ($r as $cell) {
                $t .= '<td>' . $cell . '</td>';
            }
            $t .= '</tr>';
        }
        return $t . '</tbody></table></div>';
    }
}

if (!function_exists('article_data_block')) {
    /**
     * Блоки с данными из БД каталога и grades_data.php. Вызывается из render_article_body().
     *   SIZES:slug                       — все позиции марки (толщина, ширина, состояние, поверхность)
     *   CATALOG_SUMMARY:all|slug1,slug2  — сводка по маркам: позиции, диапазоны размеров, поверхности, состояния
     *   RANGE_TABLE:t1-t2:w1-w2:slugs    — размеры в заданных диапазонах толщины и ширины по маркам
     *   THIN_TABLE                       — тонкая лента (до 0,5 мм включительно) по маркам
     *   SURFACE_COUNTS                   — число позиций каталога по поверхностям
     *   WEIGHT_TABLE                     — масса 1 м и метры в 1 кг (плотность AISI 304)
     *   DENSITY_TABLE                    — плотность по маркам
     */
    function article_data_block($type, $arg = '') {
        $knownSurfaces = ['2B', 'BA', '2BA', '4N'];

        if ($type === 'SIZES') {
            $rows = article_db_all('
                SELECT p.thickness, p.width, p.condition, p.surface
                FROM products p JOIN categories c ON c.id = p.category_id
                WHERE c.slug = ? AND c.is_active = 1 AND p.in_stock = 1
                ORDER BY p.thickness, p.width
            ', [$arg]);
            if (empty($rows)) {
                return '';
            }
            $out = [];
            foreach ($rows as $r) {
                $out[] = [e(article_num($r['thickness'])), e(article_num($r['width'])), e(article_cond_label($r['condition'])), e($r['surface'])];
            }
            return article_table(['Толщина, мм', 'Ширина, мм', 'Состояние', 'Поверхность'], $out);
        }

        if ($type === 'CATALOG_SUMMARY') {
            $slugs = $arg === 'all' ? [] : array_filter(array_map('trim', explode(',', $arg)));
            $where = '';
            $params = [];
            if (!empty($slugs)) {
                $where = ' AND c.slug IN (' . implode(',', array_fill(0, count($slugs), '?')) . ')';
                $params = array_values($slugs);
            }
            $base = article_db_all('
                SELECT c.slug, c.name, COUNT(p.id) AS cnt,
                       MIN(CASE WHEN p.thickness > 0 THEN p.thickness END) AS tmin, MAX(p.thickness) AS tmax,
                       MIN(CASE WHEN p.width > 0 THEN p.width END) AS wmin, MAX(p.width) AS wmax
                FROM categories c JOIN products p ON p.category_id = c.id AND p.in_stock = 1
                WHERE c.is_active = 1' . $where . ' GROUP BY c.id
            ', $params);
            if (empty($base)) {
                return '';
            }
            sort_aisi_categories($base);
            $sc = [];
            foreach (article_db_all('
                SELECT c.slug, p.surface, p.condition FROM categories c JOIN products p ON p.category_id = c.id AND p.in_stock = 1
                WHERE c.is_active = 1' . $where . ' GROUP BY c.slug, p.surface, p.condition
            ', $params) as $r) {
                $sc[$r['slug']]['s'][] = $r['surface'];
                $sc[$r['slug']]['c'][] = article_cond_label($r['condition']);
            }
            $out = [];
            foreach ($base as $b) {
                $gd = get_grade_data($b['slug']);
                $surf = [];
                $other = false;
                foreach ((isset($sc[$b['slug']]['s']) ? array_unique($sc[$b['slug']]['s']) : []) as $s) {
                    if (in_array($s, $knownSurfaces, true)) {
                        $surf[] = $s;
                    } elseif ($s !== '' && $s !== null) {
                        $other = true;
                    }
                }
                usort($surf, function ($a, $b2) use ($knownSurfaces) {
                    return array_search($a, $knownSurfaces) - array_search($b2, $knownSurfaces);
                });
                $surfTxt = implode(', ', $surf) . ($other ? ($surf ? ' и др.' : 'др.') : '');
                $cond = article_sort_labels(isset($sc[$b['slug']]['c']) ? $sc[$b['slug']]['c'] : []);
                $out[] = [
                    '<a href="' . e(base_url($b['slug'] . '/')) . '">' . e(normalize_aisi_display_name($b['name'])) . '</a>',
                    e($gd ? $gd['type'] : ''),
                    e((string) $b['cnt']),
                    e(article_num($b['tmin']) . ($b['tmin'] != $b['tmax'] ? '–' . article_num($b['tmax']) : '')),
                    e(article_num($b['wmin']) . ($b['wmin'] != $b['wmax'] ? '–' . article_num($b['wmax']) : '')),
                    e($surfTxt),
                    e(implode(', ', $cond)),
                ];
            }
            return article_table(['Марка', 'Тип стали', 'Позиций', 'Толщина, мм', 'Ширина, мм', 'Поверхности', 'Состояния'], $out);
        }

        if ($type === 'RANGE_TABLE') {
            $parts = explode(':', $arg);
            if (count($parts) < 3) {
                return '';
            }
            $t = array_map('floatval', explode('-', $parts[0]));
            $w = array_map('floatval', explode('-', $parts[1]));
            $slugs = array_filter(array_map('trim', explode(',', $parts[2])));
            $out = [];
            foreach ($slugs as $slug) {
                $rows = article_db_all('
                    SELECT DISTINCT p.thickness, p.width, c.name
                    FROM products p JOIN categories c ON c.id = p.category_id
                    WHERE c.slug = ? AND c.is_active = 1 AND p.in_stock = 1
                      AND p.thickness BETWEEN ? AND ? AND p.width BETWEEN ? AND ?
                    ORDER BY p.thickness, p.width
                ', [$slug, $t[0], $t[1], $w[0], $w[1]]);
                if (empty($rows)) {
                    continue;
                }
                $sizes = [];
                foreach ($rows as $r) {
                    $sizes[] = article_num($r['thickness']) . '×' . article_num($r['width']);
                }
                $more = count($sizes) > 30 ? ' и ещё ' . (count($sizes) - 30) : '';
                $out[] = [
                    '<a href="' . e(base_url($slug . '/')) . '">' . e(normalize_aisi_display_name($rows[0]['name'])) . '</a>',
                    e(implode(', ', array_slice($sizes, 0, 30)) . $more),
                    e((string) count($rows)),
                ];
            }
            return empty($out) ? '' : article_table(['Марка', 'Размеры в каталоге (толщина × ширина, мм)', 'Позиций'], $out);
        }

        if ($type === 'THIN_TABLE') {
            $base = article_db_all('
                SELECT c.slug, c.name, COUNT(p.id) AS cnt, MIN(p.thickness) AS tmin, MAX(p.thickness) AS tmax,
                       MIN(CASE WHEN p.width > 0 THEN p.width END) AS wmin, MAX(p.width) AS wmax
                FROM categories c JOIN products p ON p.category_id = c.id AND p.in_stock = 1
                WHERE c.is_active = 1 AND p.thickness > 0 AND p.thickness <= 0.5 GROUP BY c.id
            ');
            if (empty($base)) {
                return '';
            }
            sort_aisi_categories($base);
            $out = [];
            foreach ($base as $b) {
                $out[] = [
                    '<a href="' . e(base_url($b['slug'] . '/')) . '">' . e(normalize_aisi_display_name($b['name'])) . '</a>',
                    e((string) $b['cnt']),
                    e(article_num($b['tmin']) . ($b['tmin'] != $b['tmax'] ? '–' . article_num($b['tmax']) : '')),
                    e(article_num($b['wmin']) . ($b['wmin'] != $b['wmax'] ? '–' . article_num($b['wmax']) : '')),
                ];
            }
            return article_table(['Марка', 'Позиций до 0,5 мм', 'Толщина, мм', 'Ширина, мм'], $out);
        }

        if ($type === 'SURFACE_COUNTS') {
            $rows = article_db_all('SELECT surface, COUNT(*) AS cnt FROM products WHERE in_stock = 1 GROUP BY surface');
            $cnt = [];
            foreach ($rows as $r) {
                $cnt[$r['surface']] = (int) $r['cnt'];
            }
            $out = [];
            foreach ($knownSurfaces as $s) {
                if (isset($cnt[$s])) {
                    $out[] = [e($s), e((string) $cnt[$s])];
                }
            }
            return article_table(['Поверхность', 'Позиций в каталоге'], $out);
        }

        if ($type === 'WEIGHT_TABLE') {
            $g = get_grade_data('aisi-304');
            $rho = $g ? (float) $g['density'] : 7.93;
            $ts = [0.1, 0.2, 0.3, 0.5, 0.8, 1.0, 1.5, 2.0];
            $ws = [10, 20, 50, 100, 200];
            $heads = ['Толщина, мм'];
            foreach ($ws as $w) {
                $heads[] = 'ширина ' . $w . ' мм';
            }
            $gram = [];
            $meters = [];
            foreach ($ts as $t) {
                $r1 = [e(article_num($t))];
                $r2 = [e(article_num($t))];
                foreach ($ws as $w) {
                    $perM = $t * $w * $rho; // г на 1 погонный метр
                    $r1[] = e(str_replace('.', ',', rtrim(rtrim(number_format($perM, 1, '.', ''), '0'), '.')));
                    $r2[] = e(str_replace('.', ',', number_format(1000 / $perM, $perM > 100 ? 1 : 0, '.', '')));
                }
                $gram[] = $r1;
                $meters[] = $r2;
            }
            return '<p><strong>Масса 1 погонного метра, граммов</strong> (плотность ' . e(str_replace('.', ',', (string) $rho)) . ' г/см³)</p>'
                . article_table($heads, $gram)
                . '<p><strong>Сколько метров ленты в 1 килограмме</strong></p>'
                . article_table($heads, $meters);
        }

        if ($type === 'DENSITY_TABLE') {
            $path = __DIR__ . '/data/grades_data.php';
            $grades = is_file($path) ? require $path : [];
            $base = isset($grades['aisi-304']['density']) ? (float) $grades['aisi-304']['density'] : 7.93;
            $out = [];
            foreach ($grades as $slug => $g) {
                $pct = ($g['density'] / $base) * 100;
                $out[] = [
                    '<a href="' . e(base_url($slug . '/')) . '">AISI ' . e($g['number']) . '</a>',
                    e(str_replace('.', ',', number_format((float) $g['density'], 2, '.', ''))),
                    e(str_replace('.', ',', number_format($pct, 1, '.', '')) . '%'),
                ];
            }
            return article_table(['Марка', 'Плотность, г/см³', 'Масса относительно AISI 304'], $out);
        }

        return '';
    }
}
