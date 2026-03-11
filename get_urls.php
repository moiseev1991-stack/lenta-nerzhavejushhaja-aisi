<?php
$pdo = new PDO('sqlite:storage/database.sqlite');
$rows = $pdo->query("SELECT p.slug, c.slug as cat FROM products p JOIN categories c ON c.id=p.category_id WHERE p.in_stock=1 ORDER BY RANDOM() LIMIT 150")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "https://lenta-nerzhavejushhaja-aisi.ru/" . $r['cat'] . "/" . $r['slug'] . "/\n";
}
