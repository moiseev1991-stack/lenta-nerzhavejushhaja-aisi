<?php
// Простой тест для проверки работы PHP и подключения к БД

echo "<h1>Тест PHP</h1>";
echo "PHP версия: " . phpversion() . "<br>";
echo "Работает!<br><br>";

// Проверка SQLite
if (extension_loaded('pdo_sqlite')) {
    echo "✓ PDO SQLite доступен<br>";
} else {
    echo "✗ PDO SQLite НЕ доступен<br>";
}

if (extension_loaded('sqlite3')) {
    echo "✓ SQLite3 доступен<br>";
} else {
    echo "✗ SQLite3 НЕ доступен<br>";
}

echo "<br>";

// Проверка подключения к БД
try {
    $dbPath = __DIR__ . '/../storage/database.sqlite';
    echo "Путь к БД: " . $dbPath . "<br>";
    
    if (file_exists($dbPath)) {
        echo "✓ Файл БД существует<br>";
        
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM categories');
        $result = $stmt->fetch();
        echo "✓ Подключение к БД успешно<br>";
        echo "Категорий в БД: " . $result['count'] . "<br>";
    } else {
        echo "✗ Файл БД НЕ существует<br>";
    }
} catch (Exception $e) {
    echo "✗ Ошибка подключения к БД: " . $e->getMessage() . "<br>";
}

echo "<br><a href='/'>Вернуться на главную</a>";
?>
