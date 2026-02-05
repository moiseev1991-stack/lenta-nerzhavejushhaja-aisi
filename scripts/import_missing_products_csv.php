<?php
/**
 * Импорт недостающих товаров из CSV (разделитель ;, кодировка windows-1251).
 * Upsert по slug, привязка картинок из img/product_images_named.
 * После импорта — проставление цен по правилам для марок 310/310s, 316Ti, 409, 420, 431, 441.
 *
 * Запуск:
 *   php scripts/import_missing_products_csv.php
 *   php scripts/import_missing_products_csv.php --csv="E:\cod\...\docs\Lenta_Import_ADD_missing_categories.csv" --images="E:\cod\...\img\product_images_named"
 *   php scripts/import_missing_products_csv.php --dry-run
 */

$baseDir = realpath(__DIR__ . '/..');
$defaultCsv = $baseDir . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'Lenta_Import_ADD_missing_categories.csv';
$defaultImages = $baseDir . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'product_images_named';

$csvPath = $defaultCsv;
$imagesDir = $defaultImages;
$dryRun = false;
$fixImagesOnly = false;

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--dry-run') {
        $dryRun = true;
        continue;
    }
    if ($arg === '--fix-images-only') {
        $fixImagesOnly = true;
        continue;
    }
    if (strpos($arg, '--csv=') === 0) {
        $csvPath = trim(substr($arg, 6), '"\'');
        if (strpos($csvPath, '/') !== 0 && strpos($csvPath, ':\\') !== 1) {
            $csvPath = $baseDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $csvPath);
        }
        continue;
    }
    if (strpos($arg, '--images=') === 0) {
        $imagesDir = trim(substr($arg, 9), '"\'');
        if (strpos($imagesDir, '/') !== 0 && strpos($imagesDir, ':\\') !== 1) {
            $imagesDir = $baseDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imagesDir);
        }
        continue;
    }
}

require $baseDir . '/app/config.php';
require $baseDir . '/app/db.php';
require $baseDir . '/app/helpers.php';

$IMAGE_WEB_PREFIX = '/img/product_images_named/';

$stats = [
    'created' => 0,
    'updated' => 0,
    'skipped' => 0,
    'images_missing' => 0,
    'errors' => 0,
];
$errors = [];
$imagesMissingList = [];
$maxImagesMissingLog = 20;

if (!is_file($csvPath)) {
    fwrite(STDERR, "Ошибка: CSV не найден: {$csvPath}\n");
    exit(1);
}

$pdo = db();

// Режим «только починка картинок»: для всех товаров с пустым image подставить путь по slug.jpg
if ($fixImagesOnly) {
    if (!is_dir($imagesDir)) {
        fwrite(STDERR, "Ошибка: папка с картинками не найдена: {$imagesDir}\n");
        exit(1);
    }
    $IMAGE_WEB_PREFIX = '/img/product_images_named/';
    $stmt = $pdo->query("SELECT id, slug, image FROM products WHERE image IS NULL OR image = ''");
    $toRepair = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $now = date('Y-m-d H:i:s');
    $updImg = $pdo->prepare('UPDATE products SET image = ?, updated_at = ? WHERE id = ?');
    $imagesRepaired = 0;
    foreach ($toRepair as $row) {
        $slug = $row['slug'];
        $filename = (strpos($slug, '.') !== false) ? $slug : $slug . '.jpg';
        $fullPath = $imagesDir . DIRECTORY_SEPARATOR . $filename;
        if (is_file($fullPath)) {
            $webPath = $IMAGE_WEB_PREFIX . $filename;
            $updImg->execute([$webPath, $now, $row['id']]);
            $imagesRepaired++;
        }
    }
    echo "Проставлено изображений: {$imagesRepaired} (всего товаров без фото было: " . count($toRepair) . ").\n";
    exit(0);
}

// Маппинг состояния
$conditionMap = [
    'мягкая' => 'soft', 'myagkaya' => 'soft', 'soft' => 'soft',
    'нагартованная' => 'hard', 'nagartovannaya' => 'hard', 'hard' => 'hard',
    'полугартованная' => 'semi_hard', 'polugartovannaya' => 'semi_hard', 'semi_hard' => 'semi_hard',
    'высоконагартованная' => 'hard', 'vysokonagartovannaya' => 'hard', // в БД одна градация
];

function normalizeCondition($v) {
    global $conditionMap;
    $v = mb_strtolower(trim((string) $v));
    return $conditionMap[$v] ?? $v;
}

// Категория: в CSV "304L" или "310" -> slug aisi-304l, name "AISI 304L"
function categoryFromCsv($csvCategory) {
    $s = preg_replace('/\s+/', '', trim((string) $csvCategory));
    if ($s === '') return [null, null];
    $slug = 'aisi-' . mb_strtolower($s);
    $name = 'AISI ' . $s;
    return [$slug, $name];
}

function getOrCreateCategory(PDO $pdo, $categorySlug, $categoryName, &$stats) {
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
    $stmt->execute([$categorySlug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return (int) $row['id'];

    $h1 = $categoryName . ' — нержавеющая лента';
    $title = $categoryName . ' — купить нержавеющую ленту | Каталог AISI';
    $desc = 'Купить нержавеющую ленту ' . $categoryName . ' по выгодной цене.';
    $intro = 'Лента нержавеющая марки ' . $categoryName . '.';
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO categories (slug, name, h1, title, description, intro, is_active, created_at, updated_at) VALUES (?,?,?,?,?,?,1,?,?)');
    $stmt->execute([$categorySlug, $categoryName, $h1, $title, $desc, $intro, $now, $now]);
    $stats['created_categories'] = ($stats['created_categories'] ?? 0) + 1;
    return (int) $pdo->lastInsertId();
}

function isPlaceholder($v) {
    $v = trim((string) $v);
    if ($v === '') return true;
    if (mb_strtolower($v) === 'сам впишу') return true;
    if (mb_strpos($v, 'сам впишу') !== false) return true;
    return false;
}

// Открываем CSV с кодировкой windows-1251
$fh = fopen($csvPath, 'rb');
if (!$fh) {
    fwrite(STDERR, "Ошибка: не удалось открыть файл CSV.\n");
    exit(1);
}

// Читаем первую строку (заголовки) и перекодируем в UTF-8
$headerLine = fgets($fh);
if ($headerLine === false) {
    fclose($fh);
    fwrite(STDERR, "Ошибка: пустой CSV.\n");
    exit(1);
}
$headerLine = mb_convert_encoding($headerLine, 'UTF-8', 'Windows-1251');
$headerCols = str_getcsv($headerLine, ';');
$colIndex = [];
foreach ($headerCols as $i => $h) {
    $key = mb_strtolower(trim($h));
    $colIndex[$key] = $i;
}
$getCol = function($variants) use ($colIndex) {
    foreach ($variants as $v) {
        if (isset($colIndex[$v])) return $colIndex[$v];
        foreach (array_keys($colIndex) as $h) {
            if (mb_strpos($h, $v) !== false) return $colIndex[$h];
        }
    }
    return null;
};

$colCategory = $getCol(['категория']);
$colName = $getCol(['название']);
$colSlug = $getCol(['slug', 'ссылка', 'slug *']);
$colH1 = $getCol(['h1']);
$colTitle = $getCol(['title', 'title (seo)']);
$colDesc = $getCol(['description', 'description (seo)']);
$colThickness = $getCol(['толщина', 'толщина (мм)', 'толщина, мм']);
$colWidth = $getCol(['ширина', 'ширина (мм)', 'ширина, мм']);
$colCondition = $getCol(['состояние']);
$colSurface = $getCol(['поверхность']);
$colImage = $getCol(['изображение', 'image']);
// Если колонка "Изображение" не найдена по имени — берём последнюю (в этом CSV там имя файла)
if ($colImage === null && count($headerCols) > 0) {
    $colImage = count($headerCols) - 1;
}

if ($colSlug === null || $colName === null || $colCategory === null) {
    fclose($fh);
    fwrite(STDERR, "Ошибка: в CSV не найдены обязательные колонки (категория, название, slug). Найдены: " . implode(', ', array_keys($colIndex)) . "\n");
    exit(1);
}

$stats['created_categories'] = 0;

if (!$dryRun) {
    $pdo->beginTransaction();
}

try {
    $rowNum = 1;
    while (($line = fgets($fh)) !== false) {
        $rowNum++;
        $line = mb_convert_encoding($line, 'UTF-8', 'Windows-1251');
        $row = str_getcsv($line, ';');
        if (count($row) < 3) continue;

        $slug = trim($row[$colSlug] ?? '');
        $name = trim($row[$colName] ?? '');
        $catRaw = trim($row[$colCategory] ?? '');

        if ($slug === '' || $name === '') {
            $stats['skipped']++;
            continue;
        }

        list($categorySlug, $categoryName) = categoryFromCsv($catRaw);
        if ($categorySlug === null) {
            $errors[] = "Строка {$rowNum}: не удалось определить категорию из «{$catRaw}».";
            $stats['errors']++;
            $stats['skipped']++;
            continue;
        }

        if (!$dryRun) {
            $categoryId = getOrCreateCategory($pdo, $categorySlug, $categoryName, $stats);
        } else {
            $categoryId = 0;
        }

        $thickness = null;
        if ($colThickness !== null && isset($row[$colThickness]) && $row[$colThickness] !== '') {
            $thickness = (float) str_replace(',', '.', $row[$colThickness]);
        }
        $width = null;
        if ($colWidth !== null && isset($row[$colWidth]) && $row[$colWidth] !== '') {
            $width = (float) str_replace(',', '.', $row[$colWidth]);
        }
        $condition = null;
        if ($colCondition !== null && isset($row[$colCondition]) && $row[$colCondition] !== '') {
            $condition = normalizeCondition($row[$colCondition]);
        }
        $surface = ($colSurface !== null && isset($row[$colSurface])) ? trim($row[$colSurface]) : null;
        $spring = 0; // в CSV нет колонки "Пружинные свойства" в примере — по умолчанию 0

        $h1 = ($colH1 !== null && isset($row[$colH1])) ? trim($row[$colH1]) : '';
        $title = ($colTitle !== null && isset($row[$colTitle])) ? trim($row[$colTitle]) : '';
        $description = ($colDesc !== null && isset($row[$colDesc])) ? trim($row[$colDesc]) : '';

        if (isPlaceholder($h1)) $h1 = $name;
        if (isPlaceholder($title)) $title = $name . ' | Каталог AISI';
        if (isPlaceholder($description)) $description = 'Купить ' . mb_strtolower($name) . ' по выгодной цене.';

        $imagePath = null;
        $imageFile = ($colImage !== null && isset($row[$colImage])) ? trim($row[$colImage], " \t\r\n") : '';
        if ($imageFile !== '') {
            $imageFile = basename($imageFile);
            if (strpos($imageFile, '.') === false) $imageFile .= '.jpg';
            $fullPath = $imagesDir . DIRECTORY_SEPARATOR . $imageFile;
            if (is_file($fullPath)) {
                $imagePath = $IMAGE_WEB_PREFIX . $imageFile;
            }
        }
        // Всегда пробуем подставить картинку по slug.jpg, если ещё не нашли
        if ($imagePath === null) {
            $slugJpg = $slug . (strpos($slug, '.') !== false ? '' : '.jpg');
            $fullPathBySlug = $imagesDir . DIRECTORY_SEPARATOR . $slugJpg;
            if (is_file($fullPathBySlug)) {
                $imagePath = $IMAGE_WEB_PREFIX . $slugJpg;
            }
        }
        if ($imagePath === null && $imageFile !== '') {
            $stats['images_missing']++;
            if (count($imagesMissingList) < $maxImagesMissingLog) {
                $imagesMissingList[] = ['slug' => $slug, 'file' => $imageFile];
            }
        }

        $now = date('Y-m-d H:i:s');
        $pricePerKg = null; // цена по запросу
        $leadTime = null;

        if (!$dryRun) {
            $stmt = $pdo->prepare('SELECT id, h1, title, description, image FROM products WHERE slug = ?');
            $stmt->execute([$slug]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $upd = [];
                $upd[] = 'category_id = ?';
                $upd[] = 'name = ?';
                $upd[] = 'thickness = ?';
                $upd[] = 'width = ?';
                $upd[] = 'condition = ?';
                $upd[] = 'surface = ?';
                $upd[] = 'spring = ?';
                $upd[] = 'updated_at = ?';
                $params = [$categoryId, $name, $thickness, $width, $condition, $surface, $spring, $now];

                if (!isPlaceholder($h1)) { $upd[] = 'h1 = ?'; $params[] = $h1; }
                if (!isPlaceholder($title)) { $upd[] = 'title = ?'; $params[] = $title; }
                if (!isPlaceholder($description)) { $upd[] = 'description = ?'; $params[] = $description; }
                if ($imagePath !== null) { $upd[] = 'image = ?'; $params[] = $imagePath; }

                $params[] = $existing['id'];
                $sql = 'UPDATE products SET ' . implode(', ', $upd) . ' WHERE id = ?';
                $pdo->prepare($sql)->execute($params);
                $stats['updated']++;
            } else {
                $stmt = $pdo->prepare('INSERT INTO products (category_id, slug, name, h1, title, description, thickness, width, condition, spring, surface, price_per_kg, in_stock, lead_time, image, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,?,?,?,?)');
                $stmt->execute([
                    $categoryId, $slug, $name, $h1, $title, $description,
                    $thickness, $width, $condition, $spring, $surface,
                    $pricePerKg, $leadTime, $imagePath, $now, $now,
                ]);
                $stats['created']++;
            }
        } else {
            $stats['created']++; // в dry-run считаем как будто создали
        }
    }
    fclose($fh);

    if (!$dryRun) {
        $pdo->commit();
    }
} catch (Throwable $e) {
    if (!$dryRun) $pdo->rollBack();
    fclose($fh);
    fwrite(STDERR, "Ошибка: " . $e->getMessage() . "\n");
    exit(1);
}

// ——— Проставление цен по правилам (только если не dry-run) ———
// 310/310s — от 420, 316Ti — 600, 409 — 140, 420 — 230, 431 — 250, 441 — 210
$priceRules = [
    'aisi-310' => 420.0,
    'aisi-310s' => 420.0,
    'aisi-316ti' => 600.0,
    'aisi-409' => 140.0,
    'aisi-420' => 230.0,
    'aisi-431' => 250.0,
    'aisi-441' => 210.0,
];

if (!$dryRun && !empty($priceRules)) {
    foreach ($priceRules as $catSlug => $price) {
        $stmt = $pdo->prepare('UPDATE products SET price_per_kg = ? WHERE category_id = (SELECT id FROM categories WHERE slug = ?) AND (price_per_kg IS NULL OR price_per_kg = 0)');
        $stmt->execute([$price, $catSlug]);
        $updated = $stmt->rowCount();
        if ($updated > 0) {
            echo "Цены проставлены для {$catSlug}: {$updated} товаров, цена {$price} ₽/кг\n";
        }
    }
}

// ——— Починка картинок: у ВСЕХ товаров с пустым image подставить путь по slug.jpg ———
$imagesRepaired = 0;
if (!$dryRun && is_dir($imagesDir)) {
    $stmt = $pdo->query("SELECT id, slug, image FROM products WHERE image IS NULL OR image = ''");
    $toRepair = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $now = date('Y-m-d H:i:s');
    $updImg = $pdo->prepare('UPDATE products SET image = ?, updated_at = ? WHERE id = ?');
    foreach ($toRepair as $row) {
        $slug = $row['slug'];
        $filename = (strpos($slug, '.') !== false) ? $slug : $slug . '.jpg';
        $fullPath = $imagesDir . DIRECTORY_SEPARATOR . $filename;
        if (is_file($fullPath)) {
            $webPath = $IMAGE_WEB_PREFIX . $filename;
            $updImg->execute([$webPath, $now, $row['id']]);
            $imagesRepaired++;
        }
    }
    if ($imagesRepaired > 0) {
        echo "Изображения проставлены для {$imagesRepaired} товаров (по slug.jpg).\n";
    }
}

// Диагностика: что реально в БД у одного товара AISI 441 (чтобы видеть, записался ли image)
if (!$dryRun) {
    $sample = $pdo->query("SELECT p.slug, p.image FROM products p JOIN categories c ON p.category_id = c.id WHERE c.slug = 'aisi-441' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($sample) {
        $img = $sample['image'] ?? '(null)';
        echo "\n[Проверка БД] Пример товара AISI 441: slug=" . $sample['slug'] . ", image=" . (strlen((string)$img) > 60 ? substr($img, 0, 60) . '...' : $img) . "\n";
    }
}

// ——— Итог ———
echo "\n--- Итог импорта ---\n";
echo "created: " . $stats['created'] . "\n";
echo "updated: " . $stats['updated'] . "\n";
echo "skipped: " . $stats['skipped'] . "\n";
echo "images_missing: " . $stats['images_missing'] . "\n";
if (isset($imagesRepaired) && $imagesRepaired > 0) {
    echo "images_repaired: " . $imagesRepaired . "\n";
}
echo "errors: " . $stats['errors'] . "\n";
if (isset($stats['created_categories'])) {
    echo "categories_created: " . $stats['created_categories'] . "\n";
}
if (!empty($imagesMissingList)) {
    echo "\nПервые " . count($imagesMissingList) . " отсутствующих изображений:\n";
    foreach ($imagesMissingList as $m) echo "  " . $m['slug'] . " -> " . $m['file'] . "\n";
}
if (!empty($errors)) {
    echo "\nОшибки:\n";
    foreach (array_slice($errors, 0, 30) as $e) echo "  " . $e . "\n";
    if (count($errors) > 30) echo "  ... всего " . count($errors) . "\n";
}
if ($dryRun) echo "\n[DRY-RUN: запись в БД не выполнялась]\n";
echo "\nГотово.\n";
