<?php
/**
 * Импорт товаров из Excel (products.xlsx) в SQLite + привязка картинок из img/product_images_named.
 * Идемпотентность: по slug; при повторном запуске дубли не создаются, дозаполняется image если пусто.
 *
 * Запуск: php scripts/import_products_from_xlsx.php
 * Требуется: composer install (phpoffice/phpspreadsheet)
 */

// Пути (от корня проекта)
$xlsxPath = __DIR__ . '/../storage/imports/products.xlsx';
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

if (!is_file(__DIR__ . '/../vendor/autoload.php')) {
    fwrite(STDERR, "Ошибка: выполните composer install в корне проекта (нужен phpoffice/phpspreadsheet).\n");
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/db.php';
require __DIR__ . '/../app/helpers.php';

if (!is_file($xlsxPath)) {
    fwrite(STDERR, "Ошибка: файл не найден: {$xlsxPath}\n");
    exit(1);
}

$pdo = db();

// ——— Бэкап БД ———
$dbPath = realpath(__DIR__ . '/../storage/database.sqlite');
if ($dbPath && is_file($dbPath)) {
    $bakPath = __DIR__ . '/../storage/database.sqlite.bak_' . date('Ymd_His');
    if (!copy($dbPath, $bakPath)) {
        fwrite(STDERR, "Предупреждение: не удалось создать бэкап БД.\n");
    } else {
        echo "Бэкап БД: {$bakPath}\n";
    }
}

// ——— Каталог загрузок и список имён картинок ———
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
        $imageSlugs[] = pathinfo($name, PATHINFO_FILENAME);
    }
}
$imageSlugs = array_flip($imageSlugs); // slug => 1 для быстрого поиска

// ——— Чтение Excel ———
try {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsxPath);
    $sheet = $spreadsheet->getSheet(0);
    $highestRow = $sheet->getHighestDataRow();
    $highestCol = $sheet->getHighestDataColumn();
} catch (Throwable $e) {
    fwrite(STDERR, "Ошибка чтения Excel: " . $e->getMessage() . "\n");
    exit(1);
}

if ($highestRow < 2) {
    echo "В первом листе нет строк с данными (только заголовок или пусто).\n";
    report($stats, $errors);
    exit(0);
}

// Заголовки (первая строка)
$headerRow = 1;
$colIndexByName = [];
$lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
for ($col = 1; $col <= $lastColIndex; $col++) {
    $val = trim((string) $sheet->getCell([$col, $headerRow])->getValue());
    $key = mb_strtolower($val);
    $colIndexByName[$key] = $col - 1; // 0-based
}

// Поиск колонок по разным вариантам названий
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
$colPrice   = $getCol(['цена', 'price', 'руб']);
$colThick   = $getCol(['толщина', 'thickness']);
$colWidth   = $getCol(['ширина', 'width']);
$colCond    = $getCol(['состояние', 'condition']);
$colSurface = $getCol(['поверхность', 'surface']);

// Если нет явной колонки названия — используем первую колонку как название
if ($colName === null && $lastColIndex >= 1) {
    $colName = 0;
}
$hasNameCol = ($colName !== null);

// ——— Вспомогательные функции ———

function aisiToCategorySlug($aisi) {
    $aisi = preg_replace('/\s+/', '', $aisi);
    if (preg_match('/aisi[-]?(\d+[a-z]*)/i', $aisi, $m)) {
        $s = strtolower($m[1]);
        return 'aisi-' . $s;
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
        $n = $m[1];
        return 'AISI ' . (strlen($n) > 2 ? $n : $n); // 316l -> AISI 316l
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
    $stats['categories_created']++;
    return (int) $pdo->lastInsertId();
}

function getCellValue($sheet, $row, $colIndex) {
    if ($colIndex === null) return null;
    return trim((string) $sheet->getCell([$colIndex + 1, $row])->getValue());
}

// ——— Обработка строк ———
for ($row = 2; $row <= $highestRow; $row++) {
    $stats['rows_read']++;

    $name = $hasNameCol ? getCellValue($sheet, $row, $colName) : null;
    $aisiRaw = ($colAisi !== null) ? getCellValue($sheet, $row, $colAisi) : null;
    $priceVal = ($colPrice !== null) ? getCellValue($sheet, $row, $colPrice) : null;
    $thickness = ($colThick !== null) ? getCellValue($sheet, $row, $colThick) : null;
    $width = ($colWidth !== null) ? getCellValue($sheet, $row, $colWidth) : null;
    $condition = ($colCond !== null) ? getCellValue($sheet, $row, $colCond) : null;
    $surface = ($colSurface !== null) ? getCellValue($sheet, $row, $colSurface) : null;

    $slug = null;
    $imageSlug = null;

    // Категорию определяем заранее (из колонки AISI, названия или позже из картинки)
    $categorySlug = null;
    if ($aisiRaw) {
        $categorySlug = aisiToCategorySlug($aisiRaw);
    }
    if (!$categorySlug && $name) {
        $a = extractAisiFromText($name);
        if ($a) $categorySlug = aisiToCategorySlug($a);
    }

    // Нормализуем condition/surface для построения slug картинки (до использования в кандидате)
    $conditionNorm = ($condition !== null && $condition !== '') ? normalizeCondition($condition) : null;
    $surfaceVal = ($surface !== null && $surface !== '') ? trim($surface) : null;
    $thicknessVal = ($thickness !== null && $thickness !== '') ? (float) str_replace(',', '.', $thickness) : null;
    $widthVal = ($width !== null && $width !== '') ? (float) str_replace(',', '.', $width) : null;
    if ($conditionNorm === null && $name) {
        $n = mb_strtolower($name);
        if (strpos($n, 'мягк') !== false) $conditionNorm = 'soft';
        elseif (strpos($n, 'нагарт') !== false) $conditionNorm = 'hard';
    }

    // Вариант 1: slug из названия совпадает с именем файла картинки
    $slugCandidate = $name ? slugify($name) : null;
    if ($slugCandidate && isset($imageSlugs[$slugCandidate])) {
        $imageSlug = $slugCandidate;
        $slug = $slugCandidate;
    }

    // Вариант 2: по толщине, ширине, AISI, состояние, поверхность строим имя файла и ищем картинку
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
    // Вариант 2b: по AISI из названия — первая подходящая картинка с тем же AISI
    if ($slug === null && $name) {
        $aisiFromName = extractAisiFromText($name);
        foreach (array_keys($imageSlugs) as $is) {
            $aisiFromImg = extractAisiFromSlug($is);
            if ($aisiFromName && $aisiFromImg && mb_strtolower($aisiFromName) === mb_strtolower($aisiFromImg)) {
                $imageSlug = $is;
                $slug = $is;
                break;
            }
        }
    }

    // Вариант 3: slug только из названия (или product-{row})
    if ($slug === null) {
        $slug = $slugCandidate ?: ('product-' . $row);
        $slug = normalize_slug($slug) ?: ('product-' . $row);
        $slug = ensure_unique_slug($pdo, $slug, 'products', 0);
    }

    if (!$categorySlug && $imageSlug) {
        $a = extractAisiFromSlug($imageSlug);
        if ($a) $categorySlug = aisiToCategorySlug($a);
    }
    if (!$categorySlug) {
        $errors[] = "Строка {$row}: не удалось определить категорию AISI.";
        continue;
    }

    $categoryId = getCategoryId($pdo, $categorySlug, $stats);

    $productName = $name;
    if (!$productName && $imageSlug) {
        $productName = str_replace('-', ' ', $imageSlug);
        $productName = ucfirst(mb_strtolower($productName));
    }
    if (!$productName) {
        $productName = 'Товар ' . $slug;
    }

    $pricePerKg = null;
    if ($priceVal !== null && $priceVal !== '') {
        $pricePerKg = (float) preg_replace('/[^\d.,]/', '', str_replace(',', '.', $priceVal));
    }

    if ($conditionNorm === null && $imageSlug) {
        if (strpos($imageSlug, 'myagkaya') !== false) $conditionNorm = 'soft';
        elseif (strpos($imageSlug, 'nagartovannaya') !== false) $conditionNorm = 'hard';
        elseif (strpos($imageSlug, 'polugartovannaya') !== false) $conditionNorm = 'semi_hard';
    }
    if ($surfaceVal === null && $imageSlug) {
        if (strpos($imageSlug, '-ba') !== false || preg_match('/-ba\./', $imageSlug)) $surfaceVal = 'BA';
        elseif (strpos($imageSlug, '-2b') !== false) $surfaceVal = '2B';
    }

    $h1 = $productName;
    $title = $productName . ' | Каталог AISI';
    $description = 'Купить ' . mb_strtolower($productName) . ' по выгодной цене.';
    $now = date('Y-m-d H:i:s');

    // Проверка существования по slug
    $stmt = $pdo->prepare('SELECT id, image FROM products WHERE slug = ?');
    $stmt->execute([$slug]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    $imagePath = null;
    $ext = 'jpg';
    if ($imageSlug) {
        $srcPath = $imagesSrcDir . DIRECTORY_SEPARATOR . $imageSlug . '.jpg';
        if (!is_file($srcPath)) {
            $srcPath = $imagesSrcDir . DIRECTORY_SEPARATOR . $imageSlug . '.png';
            $ext = 'png';
        }
        if (is_file($srcPath)) {
            $destFile = $slug . '.' . $ext;
            $destPath = $uploadsDir . DIRECTORY_SEPARATOR . $destFile;
            if (!is_file($destPath)) {
                if (copy($srcPath, $destPath)) {
                    $stats['images_copied']++;
                } else {
                    $errors[] = "Строка {$row}: не удалось скопировать картинку для slug {$slug}.";
                }
            }
            $imagePath = 'uploads/products/' . $destFile;
        } else {
            $errors[] = "Строка {$row}: нет файла картинки для slug {$imageSlug}.";
        }
    }

    if ($existing) {
        $stats['products_skipped']++;
        if ($imagePath && (empty($existing['image']))) {
            $upd = $pdo->prepare('UPDATE products SET image = ?, updated_at = ? WHERE id = ?');
            $upd->execute([$imagePath, $now, $existing['id']]);
            $stats['products_updated_image']++;
        }
        continue;
    }

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
        $stats['products_created']++;
    } catch (PDOException $e) {
        if ((int) $e->getCode() === 23000 || strpos($e->getMessage(), 'UNIQUE') !== false) {
            $stats['products_skipped']++;
            if ($imagePath) {
                $stmt = $pdo->prepare('SELECT id, image FROM products WHERE slug = ?');
                $stmt->execute([$slug]);
                $ex = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($ex && empty($ex['image'])) {
                    $pdo->prepare('UPDATE products SET image = ?, updated_at = ? WHERE id = ?')->execute([$imagePath, $now, $ex['id']]);
                    $stats['products_updated_image']++;
                }
            }
        } else {
            $errors[] = "Строка {$row}: " . $e->getMessage();
        }
    }
}

function report($stats, $errors) {
    echo "\n--- Отчёт импорта ---\n";
    echo "Строк в Excel прочитано: " . $stats['rows_read'] . "\n";
    echo "Категорий создано: " . $stats['categories_created'] . "\n";
    echo "Товаров создано: " . $stats['products_created'] . "\n";
    echo "Товаров пропущено (уже были): " . $stats['products_skipped'] . "\n";
    echo "Товаров обновлено (только картинка): " . $stats['products_updated_image'] . "\n";
    echo "Картинок скопировано: " . $stats['images_copied'] . "\n";
    if (!empty($errors)) {
        echo "\nОшибки/предупреждения:\n";
        foreach ($errors as $e) echo "  - " . $e . "\n";
    }
}

report($stats, $errors);
echo "\nГотово.\n";
