<?php
/**
 * Импорт товаров из CSV в SQLite + привязка картинок из img/product_images_named.
 * Composer не нужен. Сохраните Excel как CSV (разделитель — запятая, кодировка UTF-8).
 *
 * Запуск: php scripts/import_products_from_csv.php
 *        php scripts/import_products_from_csv.php путь/к/файлу.csv
 */

$importsDir = __DIR__ . '/../storage/imports';
$defaultCsv = $importsDir . '/products.csv';

// Путь к CSV: из аргумента или products.csv, или любой .csv в storage/imports/
if (!empty($argv[1]) && is_file($argv[1])) {
    $csvPath = $argv[1];
} elseif (is_file($defaultCsv)) {
    $csvPath = $defaultCsv;
} else {
    $csvPath = null;
    if (is_dir($importsDir)) {
        foreach (scandir($importsDir) as $f) {
            if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'csv') {
                $csvPath = $importsDir . DIRECTORY_SEPARATOR . $f;
                break;
            }
        }
    }
}

$imagesSrcDir = __DIR__ . '/../img/product_images_named';
$uploadsDir = __DIR__ . '/../public/uploads/products';

$errors = [];
$stats = [
    'rows_read' => 0,
    'categories_created' => 0,
    'products_created' => 0,
    'products_skipped' => 0,
    'products_updated_image' => 0,
    'images_copied' => 0,
];

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';

if (!$csvPath || !is_file($csvPath)) {
    $hint = '';
    if (is_dir($importsDir)) {
        $found = [];
        foreach (scandir($importsDir) as $f) {
            if ($f === '.' || $f === '..') continue;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if ($ext === 'xlsx' || $ext === 'xls') $found[] = $f;
        }
        if (!empty($found)) {
            $hint = "\nВ папке storage/imports/ есть Excel-файл(ы): " . implode(', ', $found) . ".\nСохраните его в Excel как CSV: Файл → Сохранить как → CSV UTF-8 (разделители — запятые) → products.csv в ту же папку.\n\n";
        }
    }
    $shownPath = realpath($importsDir) ?: $importsDir;
    fwrite(STDERR, "Ошибка: файл не найден: " . $shownPath . DIRECTORY_SEPARATOR . "products.csv\n");
    fwrite(STDERR, $hint);
    fwrite(STDERR, "1) Откройте Excel с товарами → Файл → Сохранить как\n");
    fwrite(STDERR, "2) Тип файла: «CSV (разделители — запятые)» или «CSV UTF-8»\n");
    fwrite(STDERR, "3) Сохраните как: storage/imports/products.csv\n\n");
    fwrite(STDERR, "Или укажите путь к CSV: php scripts/import_products_from_csv.php путь\\к\\файлу.csv\n");
    exit(1);
}

$pdo = db();

// Бэкап БД
$dbPath = realpath(__DIR__ . '/../storage/database.sqlite');
if ($dbPath && is_file($dbPath)) {
    $bakPath = __DIR__ . '/../storage/database.sqlite.bak_' . date('Ymd_His');
    if (copy($dbPath, $bakPath)) {
        echo "Бэкап БД: {$bakPath}\n";
    }
}

if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$imageSlugs = [];
if (is_dir($imagesSrcDir)) {
    foreach (new DirectoryIterator($imagesSrcDir) as $f) {
        if ($f->isDot() || !$f->isFile()) continue;
        $name = $f->getFilename();
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) continue;
        $imageSlugs[pathinfo($name, PATHINFO_FILENAME)] = true;
    }
}

$conditionMap = [
    'мягкая' => 'soft', 'myagkaya' => 'soft', 'soft' => 'soft',
    'нагартованная' => 'hard', 'nagartovannaya' => 'hard', 'hard' => 'hard',
    'полугартованная' => 'semi_hard', 'polugartovannaya' => 'semi_hard', 'semi_hard' => 'semi_hard',
];

function aisiToCategorySlug($aisi) {
    $aisi = trim(preg_replace('/\s+/', '', $aisi));
    if ($aisi === '') return null;
    if (preg_match('/aisi[-]?(\d+[a-z]*)/i', $aisi, $m)) {
        return 'aisi-' . strtolower($m[1]);
    }
    // В CSV может быть только номер марки: 321, 304, 316L
    if (preg_match('/^(\d+[a-z]*)$/i', $aisi, $m)) {
        return 'aisi-' . strtolower($m[1]);
    }
    return null;
}

function extractAisiFromText($text) {
    if (preg_match('/aisi\s*(\d+[a-z]*)/ui', $text, $m)) {
        return 'AISI ' . $m[1];
    }
    return null;
}

function extractAisiFromSlug($slug) {
    if (preg_match('/aisi-(\d+[a-z]*)/i', $slug, $m)) {
        return 'AISI ' . $m[1];
    }
    return null;
}

function getCategoryId(PDO $pdo, $categorySlug, &$stats) {
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
    $stmt->execute([$categorySlug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return (int) $row['id'];
    $name = 'AISI ' . preg_replace('/^aisi-/i', '', $categorySlug);
    $name = ucfirst(strtolower($name));
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO categories (slug, name, h1, title, description, intro, is_active, created_at, updated_at) VALUES (?,?,?,?,?,?,1,?,?)');
    $stmt->execute([$categorySlug, $name, $name . ' — нержавеющая лента', $name . ' — купить нержавеющую ленту | Каталог AISI', 'Купить нержавеющую ленту ' . $name . '.', 'Лента нержавеющая марки ' . $name . '.', $now, $now]);
    $stats['categories_created']++;
    return (int) $pdo->lastInsertId();
}

// Читаем CSV (первая строка — заголовки)
$fp = fopen($csvPath, 'r');
if (!$fp) {
    fwrite(STDERR, "Не удалось открыть файл CSV.\n");
    exit(1);
}

// Определить разделитель: Excel в русской локали часто сохраняет с ";"
$firstLine = fgets($fp);
$delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';
rewind($fp);

// Конвертация Windows-1251 → UTF-8, если строка не валидный UTF-8
$toUtf8 = function ($s) {
    if ($s === null || $s === '') return $s;
    $s = (string) $s;
    if (mb_check_encoding($s, 'UTF-8')) return $s;
    if (function_exists('mb_convert_encoding')) {
        $t = @mb_convert_encoding($s, 'UTF-8', 'Windows-1251');
        if ($t !== false) return $t;
    }
    return $s;
};

$header = fgetcsv($fp, 0, $delimiter);
if (!$header) {
    fwrite(STDERR, "CSV пустой или неверный формат.\n");
    fclose($fp);
    exit(1);
}
// Убрать BOM из первой ячейки (Excel часто сохраняет CSV с BOM)
if (isset($header[0]) && substr($header[0], 0, 3) === "\xEF\xBB\xBF") {
    $header[0] = substr($header[0], 3);
}
$header = array_map(function ($h) use ($toUtf8) { return mb_strtolower(trim($toUtf8((string) $h))); }, $header);
$colIndex = [];
$variants = [
    'name' => ['название', 'name', 'наименование', 'товар', 'h1'],
    'aisi' => ['aisi', 'марка', 'код', 'номер'],
    'price' => ['цена', 'price'],
    'thickness' => ['толщина', 'thickness'],
    'width' => ['ширина', 'width'],
    'condition' => ['состояние', 'condition'],
    'surface' => ['поверхность', 'surface'],
];
foreach ($variants as $key => $v) {
    foreach ($v as $v1) {
        foreach ($header as $i => $h) {
            if ($h === $v1 || strpos($h, $v1) !== false) {
                $colIndex[$key] = $i;
                break 2;
            }
        }
    }
    if (!isset($colIndex[$key]) && $key === 'name') {
        $colIndex['name'] = 0;
    }
}

$rowNum = 1;
while (($cells = fgetcsv($fp, 0, $delimiter)) !== false) {
    $cells = array_map($toUtf8, $cells);
    $rowNum++;
    $stats['rows_read']++;
    $name = isset($colIndex['name']) && isset($cells[$colIndex['name']]) ? trim($cells[$colIndex['name']]) : null;
    $aisiRaw = isset($colIndex['aisi']) && isset($cells[$colIndex['aisi']]) ? trim($cells[$colIndex['aisi']]) : null;
    $priceVal = isset($colIndex['price']) && isset($cells[$colIndex['price']]) ? trim($cells[$colIndex['price']]) : null;
    $thickness = isset($colIndex['thickness']) && isset($cells[$colIndex['thickness']]) ? trim($cells[$colIndex['thickness']]) : null;
    $width = isset($colIndex['width']) && isset($cells[$colIndex['width']]) ? trim($cells[$colIndex['width']]) : null;
    $condition = isset($colIndex['condition']) && isset($cells[$colIndex['condition']]) ? trim($cells[$colIndex['condition']]) : null;
    $surface = isset($colIndex['surface']) && isset($cells[$colIndex['surface']]) ? trim($cells[$colIndex['surface']]) : null;

    if (!$name && !$aisiRaw) continue;

    $categorySlug = null;
    if ($aisiRaw) $categorySlug = aisiToCategorySlug($aisiRaw);
    if (!$categorySlug && $name) {
        $a = extractAisiFromText($name);
        if ($a) $categorySlug = aisiToCategorySlug($a);
    }

    $conditionNorm = null;
    if ($condition !== null && $condition !== '') {
        $c = mb_strtolower($condition);
        $conditionNorm = $conditionMap[$c] ?? $c;
    }
    if ($conditionNorm === null && $name) {
        $n = mb_strtolower($name);
        if (strpos($n, 'мягк') !== false) $conditionNorm = 'soft';
        elseif (strpos($n, 'нагарт') !== false) $conditionNorm = 'hard';
    }
    $surfaceVal = ($surface !== null && $surface !== '') ? trim($surface) : null;
    $thicknessVal = ($thickness !== null && $thickness !== '') ? (float) str_replace(',', '.', $thickness) : null;
    $widthVal = ($width !== null && $width !== '') ? (float) str_replace(',', '.', $width) : null;

    $slug = null;
    $imageSlug = null;
    $slugCandidate = $name ? slugify($name) : null;
    if ($slugCandidate && isset($imageSlugs[$slugCandidate])) {
        $imageSlug = $slugCandidate;
        $slug = $slugCandidate;
    }
    if ($slug === null && $categorySlug && $thicknessVal !== null && $widthVal !== null) {
        $aisiPart = str_replace('aisi-', '', $categorySlug);
        $t = str_replace('.', '-', (string) $thicknessVal);
        $w = (string) (int) $widthVal;
        $condPart = ($conditionNorm === 'soft') ? 'myagkaya' : (($conditionNorm === 'hard') ? 'nagartovannaya' : '');
        $surfPart = $surfaceVal ? mb_strtolower($surfaceVal) : '';
        $candidate = 'lenta-nerzhaveyuschaya-' . $t . 'x' . $w . '-mm-aisi-' . $aisiPart . ($condPart ? '-' . $condPart : '') . ($surfPart ? '-' . $surfPart : '');
        if (isset($imageSlugs[$candidate])) {
            $imageSlug = $candidate;
            $slug = $candidate;
        }
    }
    if ($slug === null && $name) {
        $aisiFromName = extractAisiFromText($name);
        foreach (array_keys($imageSlugs) as $is) {
            if ($aisiFromName && extractAisiFromSlug($is) && mb_strtolower($aisiFromName) === mb_strtolower(extractAisiFromSlug($is))) {
                $imageSlug = $is;
                $slug = $is;
                break;
            }
        }
    }
    if ($slug === null) {
        $slug = $slugCandidate ?: ('product-' . $rowNum);
        $slug = normalize_slug($slug) ?: ('product-' . $rowNum);
        $slug = ensure_unique_slug($pdo, $slug, 'products', 0);
    }
    if (!$categorySlug && $imageSlug) {
        $a = extractAisiFromSlug($imageSlug);
        if ($a) $categorySlug = aisiToCategorySlug($a);
    }
    if (!$categorySlug) {
        $errors[] = "Строка {$rowNum}: не удалось определить категорию AISI.";
        continue;
    }

    $categoryId = getCategoryId($pdo, $categorySlug, $stats);
    $productName = $name ?: ('Товар ' . $slug);
    $pricePerKg = null;
    if ($priceVal !== null && $priceVal !== '') {
        $pricePerKg = (float) preg_replace('/[^\d.,]/', '', str_replace(',', '.', $priceVal));
    }
    if ($conditionNorm === null && $imageSlug) {
        if (strpos($imageSlug, 'myagkaya') !== false) $conditionNorm = 'soft';
        elseif (strpos($imageSlug, 'nagartovannaya') !== false) $conditionNorm = 'hard';
    }
    if ($surfaceVal === null && $imageSlug) {
        if (strpos($imageSlug, '-ba') !== false) $surfaceVal = 'BA';
        elseif (strpos($imageSlug, '-2b') !== false) $surfaceVal = '2B';
    }

    $now = date('Y-m-d H:i:s');
    $h1 = $productName;
    $title = $productName . ' | Каталог AISI';
    $description = 'Купить ' . mb_strtolower($productName) . ' по выгодной цене.';

    $stmt = $pdo->prepare('SELECT id, image FROM products WHERE slug = ?');
    $stmt->execute([$slug]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    $imagePath = null;
    if ($imageSlug) {
        $srcPath = $imagesSrcDir . DIRECTORY_SEPARATOR . $imageSlug . '.jpg';
        if (!is_file($srcPath)) $srcPath = $imagesSrcDir . DIRECTORY_SEPARATOR . $imageSlug . '.png';
        $ext = is_file($srcPath) ? pathinfo($srcPath, PATHINFO_EXTENSION) : null;
        if ($ext) {
            $destFile = $slug . '.' . $ext;
            $destPath = $uploadsDir . DIRECTORY_SEPARATOR . $destFile;
            if (!is_file($destPath) && copy($srcPath, $destPath)) {
                $stats['images_copied']++;
            }
            $imagePath = 'uploads/products/' . $destFile;
        }
    }

    if ($existing) {
        $stats['products_skipped']++;
        if ($imagePath && empty($existing['image'])) {
            $pdo->prepare('UPDATE products SET image = ?, updated_at = ? WHERE id = ?')->execute([$imagePath, $now, $existing['id']]);
            $stats['products_updated_image']++;
        }
        continue;
    }

    try {
        $pdo->prepare('INSERT INTO products (category_id, slug, name, h1, title, description, thickness, width, condition, spring, surface, price_per_kg, in_stock, lead_time, image, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,0,?,?,1,?,?,?,?)')->execute([
            $categoryId, $slug, $productName, $h1, $title, $description,
            $thicknessVal, $widthVal, $conditionNorm, $surfaceVal, $pricePerKg, null, $imagePath, $now, $now
        ]);
        $stats['products_created']++;
    } catch (PDOException $e) {
        if ((int) $e->getCode() === 23000 || strpos($e->getMessage(), 'UNIQUE') !== false) {
            $stats['products_skipped']++;
        } else {
            $errors[] = "Строка {$rowNum}: " . $e->getMessage();
        }
    }
}
fclose($fp);

echo "\n--- Отчёт импорта ---\n";
echo "Строк прочитано: " . $stats['rows_read'] . "\n";
echo "Категорий создано: " . $stats['categories_created'] . "\n";
echo "Товаров создано: " . $stats['products_created'] . "\n";
echo "Товаров пропущено: " . $stats['products_skipped'] . "\n";
echo "Товаров обновлено (картинка): " . $stats['products_updated_image'] . "\n";
echo "Картинок скопировано: " . $stats['images_copied'] . "\n";
if (!empty($errors)) {
    echo "\nОшибки:\n";
    foreach (array_slice($errors, 0, 20) as $e) echo "  - " . $e . "\n";
    if (count($errors) > 20) echo "  ... и ещё " . (count($errors) - 20) . "\n";
}
echo "\nГотово.\n";
