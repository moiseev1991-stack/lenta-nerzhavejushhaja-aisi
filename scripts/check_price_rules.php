<?php
/**
 * Проверка правил цен по категориям:
 * 310/310s — от 420 ₽/кг
 * 316Ti — от 600 ₽/кг
 * 409 — от 140 ₽/кг
 * 420 — от 230 ₽/кг
 * 431 — от 250 ₽/кг
 * 441 — от 210 ₽/кг
 */

$baseDir = realpath(__DIR__ . '/..');
require $baseDir . '/app/config.php';
require $baseDir . '/app/db.php';

$pdo = db();

$rules = [
    'aisi-310'  => 420.0,
    'aisi-310s' => 420.0,
    'aisi-316ti'=> 600.0,
    'aisi-409'  => 140.0,
    'aisi-420'  => 230.0,
    'aisi-431'  => 250.0,
    'aisi-441'  => 210.0,
];

echo "--- Проверка цен по правилам ---\n\n";

$stmtCat = $pdo->prepare('SELECT id, name FROM categories WHERE slug = ?');
$stmtStats = $pdo->prepare('
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN price_per_kg = ? THEN 1 ELSE 0 END) as ok,
        SUM(CASE WHEN price_per_kg IS NULL OR price_per_kg = 0 THEN 1 ELSE 0 END) as empty,
        MIN(price_per_kg) as min_price,
        MAX(price_per_kg) as max_price
    FROM products
    WHERE category_id = ?
');
$stmtWrong = $pdo->prepare('SELECT slug, price_per_kg FROM products WHERE category_id = ? AND (price_per_kg IS NULL OR price_per_kg = 0 OR price_per_kg != ?) LIMIT 5');

$allOk = true;
foreach ($rules as $slug => $expectedPrice) {
    $stmtCat->execute([$slug]);
    $cat = $stmtCat->fetch(PDO::FETCH_ASSOC);
    if (!$cat) {
        echo "  [ ] {$slug}: категория не найдена\n";
        $allOk = false;
        continue;
    }

    $stmtStats->execute([$expectedPrice, $cat['id']]);
    $s = $stmtStats->fetch(PDO::FETCH_ASSOC);
    $total = (int) $s['total'];
    $ok = (int) $s['ok'];
    $empty = (int) $s['empty'];
    $min = $s['min_price'] !== null ? (float) $s['min_price'] : null;
    $max = $s['max_price'] !== null ? (float) $s['max_price'] : null;

    $bad = $total - $ok;
    $status = ($bad === 0 && $total > 0) ? '[OK]' : '[!!]';
    if ($bad > 0) $allOk = false;

    echo "{$status} {$slug} (ожидаемо {$expectedPrice} ₽/кг)\n";
    echo "    Товаров: {$total}, с правильной ценой: {$ok}, без/неверной: {$bad} (пустых: {$empty})\n";
    if ($min !== null) echo "    В БД: min={$min} ₽/кг, max={$max} ₽/кг\n";

    if ($bad > 0) {
        $stmtWrong->execute([$cat['id'], $expectedPrice]);
        $wrong = $stmtWrong->fetchAll(PDO::FETCH_ASSOC);
        echo "    Примеры с неверной ценой:\n";
        foreach ($wrong as $w) {
            $p = $w['price_per_kg'] === null ? 'NULL' : $w['price_per_kg'];
            echo "      - {$w['slug']} => {$p}\n";
        }
    }
    echo "\n";
}

echo $allOk ? "Итог: все категории соответствуют правилам.\n" : "Итог: есть расхождения (см. выше).\n";
