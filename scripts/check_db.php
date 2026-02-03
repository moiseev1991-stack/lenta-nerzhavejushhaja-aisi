<?php
require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/db.php';
$pdo = db();
$products = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$categories = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
echo "Товаров в БД: $products\nКатегорий: $categories\n";
