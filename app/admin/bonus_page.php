<?php
require_admin();

require __DIR__ . '/../db.php';
require __DIR__ . '/../helpers.php';

$pdo = db();
$success = '';
$errors = [];

$defaultContent = <<<HTML
<h2>Описание программы лояльности «Железная ставка»</h2>
<p>С каждой покупки металлопроката на сайте <strong>lenta-nerzhavejushhaja-aisi.ru</strong> вы получаете бонусы — <strong>1%</strong> от суммы заказа.<br>Для акционных товаров из категории <strong>«металлопрокат 2-го сорта»</strong> общий накопительный бонус может достигать <strong>10%</strong>.</p>
<p>Накопленные бонусы можно использовать, чтобы уменьшить стоимость следующих покупок на сайте <strong>http://lenta-nerzhavejushhaja-aisi.ru/</strong>.<br>Для первого использования необходимо накопить <strong>10&nbsp;000 бонусов</strong> (<strong>1 бонус = 1 рубль</strong>).</p>
<p>Если товар возвращается, бонусы, начисленные за эту покупку, <strong>не возвращаются</strong>.<br>Бонусы <strong>нельзя</strong> передавать другим пользователям, <strong>нельзя</strong> суммировать между аккаунтами и <strong>нельзя</strong> дарить.</p>
<p><strong>Уважаемые партнёры!</strong><br>Мы запускаем программу лояльности «Железная ставка». Начисление бонусов действует <strong>с декабря 2022 года и в течение всего 2023 года</strong> — за покупки материалов, участвующих в акции.</p>
<p>Программа также распространяется на <strong>чёрный, цветной и нержавеющий металлопрокат</strong>.</p>
HTML;

// Гарантируем наличие таблицы и записи bonus
try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT UNIQUE NOT NULL,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        updated_at TEXT
    )');
    $now = nowIso();
    $stmtInit = $pdo->prepare('INSERT OR IGNORE INTO pages (slug, title, content, updated_at) VALUES (?, ?, ?, ?)');
    $stmtInit->execute(['bonus', 'Получить бонус', $defaultContent, $now]);
} catch (Throwable $e) {
    $errors[] = 'Ошибка инициализации страницы: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $title = trim((string)($_POST['title'] ?? ''));
    $content = (string)($_POST['content'] ?? '');

    if ($title === '') {
        $errors[] = 'Заголовок не может быть пустым.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE pages SET title = ?, content = ?, updated_at = ? WHERE slug = ?');
        $stmt->execute([
            mb_substr($title, 0, 255),
            $content,
            nowIso(),
            'bonus',
        ]);
        $success = 'Страница сохранена.';
    }
}

$stmt = $pdo->prepare('SELECT title, content, updated_at FROM pages WHERE slug = ? LIMIT 1');
$stmt->execute(['bonus']);
$page = $stmt->fetch() ?: ['title' => 'Получить бонус', 'content' => $defaultContent, 'updated_at' => null];

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница «Получить бонус» — Админка</title>
    <link rel="stylesheet" href="<?= base_url('assets/styles.css') ?>">
</head>
<body>
    <div class="admin-layout">
        <header class="admin-header">
            <div class="container">
                <div class="admin-header__inner">
                    <h1>Админка</h1>
                    <nav class="admin-nav">
                        <a href="<?= base_url('admin/products') ?>">Товары</a>
                        <a href="<?= base_url('admin/categories') ?>">Категории</a>
                        <a href="<?= base_url('admin/home_text') ?>">Текст на главной</a>
                        <a href="<?= base_url('admin/bonus_page') ?>">Страница: Получить бонус</a>
                        <a href="<?= base_url('admin/logout') ?>">Выход</a>
                    </nav>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <div class="container">
                <div class="admin-card">
                    <div class="admin-card__header">
                        <h2>Страница «Получить бонус»</h2>
                        <a href="<?= base_url('bonus/') ?>" target="_blank" class="btn btn--ghost">Открыть страницу</a>
                    </div>

                    <?php if ($success): ?>
                        <p class="admin-message admin-message--success"><?= e($success) ?></p>
                    <?php endif; ?>
                    <?php foreach ($errors as $err): ?>
                        <p class="admin-message admin-message--error"><?= e($err) ?></p>
                    <?php endforeach; ?>

                    <form method="post" class="admin-form">
                        <fieldset class="admin-fieldset">
                            <legend class="admin-fieldset__legend">Основное</legend>
                            <div class="form-group">
                                <label for="title">Заголовок (H1 / Title)</label>
                                <input type="text" name="title" id="title" class="form-control" maxlength="255" value="<?= e($page['title']) ?>">
                            </div>
                            <div class="form-group">
                                <label for="content">HTML-контент страницы</label>
                                <textarea name="content" id="content" rows="22" class="form-control"><?= e($page['content']) ?></textarea>
                                <p class="form-hint">
                                    Допустим произвольный HTML. Текст в примере содержит все обязательные условия (1%, до 10%, 10&nbsp;000 бонусов и т.п.) — при редактировании не меняйте числа.
                                </p>
                            </div>
                            <?php if (!empty($page['updated_at'])): ?>
                            <p class="form-hint">Последнее изменение: <?= e($page['updated_at']) ?></p>
                            <?php endif; ?>
                        </fieldset>
                        <div class="form-row form-row--actions">
                            <button type="submit" class="btn btn--primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

