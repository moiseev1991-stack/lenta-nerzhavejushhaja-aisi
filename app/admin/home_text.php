<?php
require_admin();

require __DIR__ . '/../helpers.php';

$success = '';
$errors = [];
$warnings = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = $_POST['home_text_html'] ?? '';
    $allowed = '<p><br><h2><h3><h4><ul><ol><li><a><strong><em><b><i><span>';
    $homeTextHtml = trim(strip_tags($raw, $allowed));
    set_site_setting('home_text_html', $homeTextHtml);

    $homeTitle = mb_substr(trim((string)($_POST['home_title'] ?? '')), 0, 512);
    $homeH1 = mb_substr(trim((string)($_POST['home_h1'] ?? '')), 0, 255);
    $homeDescription = mb_substr(trim((string)($_POST['home_description'] ?? '')), 0, 1000);
    set_site_setting('home_title', $homeTitle);
    set_site_setting('home_h1', $homeH1);
    set_site_setting('home_description', $homeDescription);

    if (mb_strlen($homeTitle) > 80) {
        $warnings[] = 'Title длиннее 80 символов — поисковики могут обрезать.';
    }
    if (mb_strlen($homeDescription) > 180) {
        $warnings[] = 'Description длиннее 180 символов — поисковики могут обрезать.';
    }
    if ($homeH1 === '') {
        $warnings[] = 'H1 пустой — на главной будет использован дефолт «Лента нержавеющая AISI».';
    }

    $success = 'Сохранено.';
}

$homeTextHtml = get_site_setting('home_text_html') ?? '';
$homeTitle = get_site_setting('home_title') ?? '';
$homeH1 = get_site_setting('home_h1') ?? '';
$homeDescription = get_site_setting('home_description') ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Текст на главной — Админка</title>
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
                        <h2>Главная страница: SEO и контент</h2>
                        <a href="<?= base_url() ?>" target="_blank" class="btn btn--ghost">Открыть главную</a>
                    </div>

                    <?php if ($success): ?>
                        <p class="admin-message admin-message--success"><?= e($success) ?></p>
                    <?php endif; ?>
                    <?php foreach ($errors as $err): ?>
                        <p class="admin-message admin-message--error"><?= e($err) ?></p>
                    <?php endforeach; ?>
                    <?php foreach ($warnings as $w): ?>
                        <p class="admin-message admin-message--warning"><?= e($w) ?></p>
                    <?php endforeach; ?>

                    <form method="post" class="admin-form">
                        <fieldset class="admin-fieldset">
                            <legend class="admin-fieldset__legend">SEO и заголовки</legend>
                            <div class="form-group">
                                <label for="home_title">Title (meta title)</label>
                                <input type="text" name="home_title" id="home_title" class="form-control" value="<?= e($homeTitle) ?>" maxlength="512" placeholder="Лента нержавеющая AISI — купить, цены, наличие">
                                <span class="form-hint">Рекомендуем 40–70 символов. Сейчас: <span id="home_title_count"><?= mb_strlen($homeTitle) ?></span></span>
                            </div>
                            <div class="form-group">
                                <label for="home_h1">H1 на главной</label>
                                <input type="text" name="home_h1" id="home_h1" class="form-control" value="<?= e($homeH1) ?>" maxlength="255" placeholder="Лента нержавеющая AISI">
                                <span class="form-hint">Заголовок в hero-блоке на главной. Если пусто — будет «Лента нержавеющая AISI».</span>
                            </div>
                            <div class="form-group">
                                <label for="home_description">Meta description</label>
                                <textarea name="home_description" id="home_description" rows="4" class="form-control" maxlength="1000" placeholder="Подберём нержавеющую ленту по марке AISI, толщине, ширине и поверхности. Отмотка от 1 метра. Резка от 2,5 мм."><?= e($homeDescription) ?></textarea>
                                <span class="form-hint">Рекомендуем 120–160 символов. Сейчас: <span id="home_description_count"><?= mb_strlen($homeDescription) ?></span></span>
                            </div>
                        </fieldset>

                        <fieldset class="admin-fieldset">
                            <legend class="admin-fieldset__legend">HTML-текстовый блок</legend>
                            <p class="admin-hint">Блок показывается между «Категории» и «Популярные товары». Можно использовать теги: <code>&lt;p&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;h3&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;ol&gt;</code>, <code>&lt;li&gt;</code>, <code>&lt;a&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;em&gt;</code>.</p>
                            <div class="form-group">
                                <label for="home_text_html">HTML-текст</label>
                                <textarea name="home_text_html" id="home_text_html" rows="14" class="form-control"><?= e($homeTextHtml) ?></textarea>
                            </div>
                        </fieldset>

                        <div class="form-row form-row--actions">
                            <button type="submit" class="btn btn--primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
    (function() {
        var titleInput = document.getElementById('home_title');
        var titleCount = document.getElementById('home_title_count');
        var descTextarea = document.getElementById('home_description');
        var descCount = document.getElementById('home_description_count');
        function countChars(el, counter) {
            if (!el || !counter) return;
            function update() { counter.textContent = (el.value || '').length; }
            el.addEventListener('input', update);
            el.addEventListener('change', update);
        }
        countChars(titleInput, titleCount);
        countChars(descTextarea, descCount);
    })();
    </script>
</body>
</html>
