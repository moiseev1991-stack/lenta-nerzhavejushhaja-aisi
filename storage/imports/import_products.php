<?php
/**
 * Импорт товаров из Excel в SQLite: создание категорий, товаров, привязка фото из img/product_images_named.
 * В БД хранится web-путь: /img/product_images_named/<filename>.jpg (файлы не копируются).
 *
 * Запуск: php storage/imports/import_products.php [storage/imports/products.xlsx]
 * Требуется: composer install (phpoffice/phpspreadsheet)
 */

$baseDir = realpath(__DIR__ . '/../..');
if (!$baseDir) {
    fwrite(STDERR, "Ошибка: не удалось определить корень проекта.\n");
    exit(1);
}

$xlsxPath = isset($argv[1]) ? $argv[1] : __DIR__ . '/products.xlsx';
if (strpos($xlsxPath, '/') !== 0 && strpos($xlsxPath, ':\\') !== 1) {
    $xlsxPath = $baseDir . '/' . trim($xlsxPath, '/\\');
}
$xlsxPath = realpath($xlsxPath) ?: $xlsxPath;

$imagesDir = $baseDir . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'product_images_named';
$logPath = __DIR__ . DIRECTORY_SEPARATOR . 'import_log.txt';

$errors = [];
$missingImages = [];
$stats = [
    'created_categories' => 0,
    'created_products' => 0,
    'updated_products' => 0,
    'missing_images' => 0,
    'errors' => 0,
];

$log = function ($line) use ($logPath) {
    file_put_contents($logPath, $line . "\n", FILE_APPEND | LOCK_EX);
};

// Очистить лог
file_put_contents($logPath, date('Y-m-d H:i:s') . " — старт импорта\n", LOCK_EX);

if (!is_file($baseDir . '/vendor/autoload.php')) {
    $msg = "Ошибка: выполните composer install в корне проекта (нужен phpoffice/phpspreadsheet).\n";
    fwrite(STDERR, $msg);
    $log($msg);
    exit(1);
}

require $baseDir . '/vendor/autoload.php';
require $baseDir . '/app/config.php';
require $baseDir . '/app/db.php';
require $baseDir . '/app/helpers.php';

if (!is_file($xlsxPath)) {
    $msg = "Ошибка: файл не найден: {$xlsxPath}\n";
    fwrite(STDERR, $msg);
    $log($msg);
    exit(1);
}

$pdo = db();

// ——— Список имён картинок (без расширения) ———
$imageSlugs = [];
if (is_dir($imagesDir)) {
    foreach (new DirectoryIterator($imagesDir) as $f) {
        if ($f->isDot() || !$f->isFile()) continue;
        $name = $f->getFilename();
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) continue;
        $imageSlugs[pathinfo($name, PATHINFO_FILENAME)] = $ext;
    }
}

// ——— Чтение Excel ———
try {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsxPath);
    $sheet = $spreadsheet->getSheet(0);
    $highestRow = $sheet->getHighestDataRow();
    $highestCol = $sheet->getHighestDataColumn();
} catch (Throwable $e) {
    $msg = "Ошибка чтения Excel: " . $e->getMessage();
    fwrite(STDERR, $msg . "\n");
    $log($msg);
    exit(1);
}

$lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
$headerRow = 1;
$colIndexByName = [];
for ($col = 1; $col <= $lastColIndex; $col++) {
    $val = trim((string) $sheet->getCell([$col, $headerRow])->getValue());
    $key = mb_strtolower($val);
    $colIndexByName[$key] = $col - 1;
}

// Вывод колонок в лог
$log("Колонки листа 1: " . implode(', ', array_keys($colIndexByName)));

$getCol = function ($variants) use ($colIndexByName) {
    foreach ($variants as $v) {
        if (isset($colIndexByName[$v])) return $colIndexByName[$v];
        foreach (array_keys($colIndexByName) as $h) {
            if (strpos($h, $v) !== false) return $colIndexByName[$h];
        }
    }
    return null;
};

$colName    = $getCol(['название', 'name', 'наименование', 'товар']);
$colAisi    = $getCol(['aisi', 'марка', 'марка стали']);
$colImage   = $getCol(['image', 'image_filename', 'фото', 'картинка', 'файл']);
$colThick   = $getCol(['толщина', 'thickness']);
$colWidth   = $getCol(['ширина', 'width']);
$colCond    = $getCol(['состояние', 'condition']);
$colSurface = $getCol(['поверхность', 'surface']);
$colSlug    = $getCol(['slug', 'url', 'человекопонятный url']);
if ($colName === null && $lastColIndex >= 1) $colName = 0;
$hasNameCol = ($colName !== null);

function aisiToCategorySlug($aisi) {
    $aisi = preg_replace('/\s+/', '', $aisi);
    if (preg_match('/aisi[-]?(\d+[a-z]*)/i', $aisi, $m)) {
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

$conditionMap = [
    'мягкая' => 'soft', 'myagkaya' => 'soft', 'soft' => 'soft',
    'нагартованная' => 'hard', 'nagartovannaya' => 'hard', 'hard' => 'hard',
    'полугартованная' => 'semi_hard', 'polugartovannaya' => 'semi_hard', 'semi_hard' => 'semi_hard',
];

function normalizeCondition($v) {
    global $conditionMap;
    $v = mb_strtolower(trim((string) $v));
    return $conditionMap[$v] ?? $v;
}

function getCategoryId(PDO $pdo, $categorySlug, &$stats) {
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
    $stmt->execute([$categorySlug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return (int) $row['id'];

    $name = 'AISI ' . preg_replace('/^aisi-/i', '', $categorySlug);
    $name = ucfirst(strtolower($name));
    $h1 = $name . ' — нержавеющая лента';
    $title = $name . ' — купить нержавеющую ленту | Каталог AISI';
    $desc = 'Купить нержавеющую ленту ' . $name . ' по выгодной цене.';
    $intro = 'Лента нержавеющая марки ' . $name . '.';
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO categories (slug, name, h1, title, description, intro, is_active, created_at, updated_at) VALUES (?,?,?,?,?,?,1,?,?)');
    $stmt->execute([$categorySlug, $name, $h1, $title, $desc, $intro, $now, $now]);
    $stats['created_categories']++;
    return (int) $pdo->lastInsertId();
}

function getCellValue($sheet, $row, $colIndex) {
    if ($colIndex === null) return null;
    return trim((string) $sheet->getCell([$colIndex + 1, $row])->getValue());
}

// Web-путь к картинке (без копирования)
$IMAGE_WEB_PREFIX = '/img/product_images_named/';

if ($highestRow < 2) {
    $log("Нет строк с данными.");
    $log("Summary: created_categories=0, created_products=0, updated_products=0, missing_images=0, errors=0");
    echo "Готово. Лог: {$logPath}\n";
    exit(0);
}

$pdo->beginTransaction();
try {
    for ($row = 2; $row <= $highestRow; $row++) {
        $name = $hasNameCol ? getCellValue($sheet, $row, $colName) : null;
        $aisiRaw = ($colAisi !== null) ? getCellValue($sheet, $row, $colAisi) : null;
        $imageFilename = ($colImage !== null) ? getCellValue($sheet, $row, $colImage) : null;
        $thickness = ($colThick !== null) ? getCellValue($sheet, $row, $colThick) : null;
        $width = ($colWidth !== null) ? getCellValue($sheet, $row, $colWidth) : null;
        $condition = ($colCond !== null) ? getCellValue($sheet, $row, $colCond) : null;
        $surface = ($colSurface !== null) ? getCellValue($sheet, $row, $colSurface) : null;
        $slugFromExcel = ($colSlug !== null) ? getCellValue($sheet, $row, $colSlug) : null;

        $categorySlug = null;
        if ($aisiRaw) $categorySlug = aisiToCategorySlug($aisiRaw);
        if (!$categorySlug && $name) {
            $a = extractAisiFromText($name);
            if ($a) $categorySlug = aisiToCategorySlug($a);
        }

        $conditionNorm = ($condition !== null && $condition !== '') ? normalizeCondition($condition) : null;
        $surfaceVal = ($surface !== null && $surface !== '') ? trim($surface) : null;
        $thicknessVal = ($thickness !== null && $thickness !== '') ? (float) str_replace(',', '.', $thickness) : null;
        $widthVal = ($width !== null && $width !== '') ? (float) str_replace(',', '.', $width) : null;
        if ($conditionNorm === null && $name) {
            $n = mb_strtolower($name);
            if (strpos($n, 'мягк') !== false) $conditionNorm = 'soft';
            elseif (strpos($n, 'нагарт') !== false) $conditionNorm = 'hard';
        }

        $slug = $slugFromExcel ? normalize_slug($slugFromExcel) : null;
        $imageSlug = null;

        if ($imageFilename !== null && $imageFilename !== '') {
            $imageFilename = basename($imageFilename);
            $base = pathinfo($imageFilename, PATHINFO_FILENAME);
            if (isset($imageSlugs[$base])) {
                $imageSlug = $base;
                $ext = $imageSlugs[$base];
            }
        }

        if ($slug === null) {
            $slugCandidate = $name ? slugify($name) : null;
            if ($slugCandidate && isset($imageSlugs[$slugCandidate])) {
                $imageSlug = $slugCandidate;
                $slug = $slugCandidate;
            }
        }

        if ($slug === null && $categorySlug && $thicknessVal !== null && $widthVal !== null) {
            $aisiPart = str_replace('aisi-', '', $categorySlug);
            $t = str_replace('.', '-', (string) $thicknessVal);
            $w = (string) (int) $widthVal;
            $condPart = ($conditionNorm === 'soft') ? 'myagkaya' : (($conditionNorm === 'hard') ? 'nagartovannaya' : '');
            $surfPart = $surfaceVal ? mb_strtolower(preg_replace('/\s+/', '-', $surfaceVal)) : '';
            $candidate = 'lenta-nerzhaveyuschaya-' . $t . 'x' . $w . '-mm-aisi-' . $aisiPart . ($condPart ? '-' . $condPart : '') . ($surfPart ? '-' . $surfPart : '');
            if (isset($imageSlugs[$candidate])) {
                $imageSlug = $candidate;
                $slug = $candidate;
            }
        }

        if ($slug === null && $name) {
            foreach (array_keys($imageSlugs) as $is) {
                $aisiFromName = $name ? extractAisiFromText($name) : null;
                $aisiFromImg = extractAisiFromSlug($is);
                if ($aisiFromName && $aisiFromImg && mb_strtolower($aisiFromName) === mb_strtolower($aisiFromImg)) {
                    $imageSlug = $is;
                    $slug = $is;
                    break;
                }
            }
        }

        if ($slug === null) {
            $slug = ($name ? normalize_slug($name) : null) ?: ('product-' . $row);
            $slug = normalize_slug($slug) ?: ('product-' . $row);
            $slug = ensure_unique_slug($pdo, $slug, 'products', 0);
        }

        if (!$categorySlug && $imageSlug) {
            $a = extractAisiFromSlug($imageSlug);
            if ($a) $categorySlug = aisiToCategorySlug($a);
        }
        if (!$categorySlug) {
            $errors[] = "Строка {$row}: не удалось определить категорию AISI.";
            $stats['errors']++;
            continue;
        }

        $categoryId = getCategoryId($pdo, $categorySlug, $stats);

        $productName = $name ?: ($imageSlug ? ucfirst(mb_strtolower(str_replace('-', ' ', $imageSlug))) : 'Товар ' . $slug);
        if (!$productName) $productName = 'Товар ' . $slug;

        $imagePath = null;
        if ($imageSlug) {
            $ext = $imageSlugs[$imageSlug] ?? 'jpg';
            $filename = $imageSlug . '.' . $ext;
            $fullPath = $imagesDir . DIRECTORY_SEPARATOR . $filename;
            if (is_file($fullPath)) {
                $imagePath = $IMAGE_WEB_PREFIX . $filename;
            } else {
                $missingImages[] = ['slug' => $slug, 'filename' => $filename];
                $stats['missing_images']++;
                $log("MISSING_IMAGE: {$slug} -> {$filename}");
            }
        }

        $stmt = $pdo->prepare('SELECT id, image FROM products WHERE slug = ?');
        $stmt->execute([$slug]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        $now = date('Y-m-d H:i:s');

        if ($existing) {
            if ($imagePath !== null) {
                $upd = $pdo->prepare('UPDATE products SET image = ?, updated_at = ? WHERE id = ?');
                $upd->execute([$imagePath, $now, $existing['id']]);
                $stats['updated_products']++;
            }
            continue;
        }

        $h1 = $productName;
        $title = $productName . ' | Каталог AISI';
        $description = 'Купить ' . mb_strtolower($productName) . ' по выгодной цене.';
        $pricePerKg = null; // по запросу

        try {
            $stmt = $pdo->prepare('INSERT INTO products (category_id, slug, name, h1, title, description, thickness, width, condition, spring, surface, price_per_kg, in_stock, lead_time, image, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,0,?,?,1,?,?,?,?)');
            $stmt->execute([
                $categoryId,
                $slug,
                $productName,
                $h1,
                $title,
                $description,
                $thicknessVal,
                $widthVal,
                $conditionNorm,
                $surfaceVal,
                $pricePerKg,
                null,
                $imagePath,
                $now,
                $now,
            ]);
            $stats['created_products']++;
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000 || strpos($e->getMessage(), 'UNIQUE') !== false) {
                if ($imagePath !== null) {
                    $stmt = $pdo->prepare('SELECT id, image FROM products WHERE slug = ?');
                    $stmt->execute([$slug]);
                    $ex = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($ex && empty($ex['image'])) {
                        $pdo->prepare('UPDATE products SET image = ?, updated_at = ? WHERE id = ?')->execute([$imagePath, $now, $ex['id']]);
                        $stats['updated_products']++;
                    }
                }
            } else {
                $errors[] = "Строка {$row}: " . $e->getMessage();
                $stats['errors']++;
            }
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    $errors[] = "Критическая ошибка: " . $e->getMessage();
    $stats['errors']++;
    $log("ROLLBACK: " . $e->getMessage());
}

$summary = sprintf(
    "Summary: created_categories=%d, created_products=%d, updated_products=%d, missing_images=%d, errors=%d",
    $stats['created_categories'],
    $stats['created_products'],
    $stats['updated_products'],
    $stats['missing_images'],
    $stats['errors']
);
$log($summary);
if (!empty($errors)) {
    $log("Errors:");
    foreach ($errors as $e) $log("  - " . $e);
}

echo "\n--- Отчёт импорта ---\n";
echo $summary . "\n";
if (!empty($errors)) {
    echo "Ошибки:\n";
    foreach ($errors as $e) echo "  - " . $e . "\n";
}
if (!empty($missingImages)) {
    echo "Отсутствующие картинки (первые 10): ";
    foreach (array_slice($missingImages, 0, 10) as $m) echo $m['slug'] . " -> " . $m['filename'] . "; ";
    if (count($missingImages) > 10) echo "… всего " . count($missingImages);
    echo "\n";
}
echo "Лог: {$logPath}\n";
echo "Готово.\n";
