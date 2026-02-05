<?php
require_admin();

require __DIR__ . '/../db.php';
require __DIR__ . '/../helpers.php';

$pdo = db();

$categoryId = $_GET['id'] ?? null;
$category = null;
$errors = [];
$fieldErrors = [];
$success = '';

// Загрузка категории
if ($categoryId) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();
    if (!$category) {
        redirect('/admin/categories');
    }
}

// Обработка сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    
    if ($action === 'clear_format') {
        $contentFormat = (trim($_POST['content_format'] ?? '') === 'html') ? 'html' : 'markdown';
        $cleaned = strip_article_formatting(trim($_POST['content_body'] ?? ''), $contentFormat);
        $category = $category ?: [];
        $category['content_body'] = $cleaned;
        $category['content_format'] = $contentFormat;
        $category['content_title'] = trim($_POST['content_title'] ?? $category['content_title'] ?? '');
        $category['content_is_active'] = isset($_POST['content_is_active']) ? 1 : 0;
        $category['name'] = $_POST['name'] ?? $category['name'] ?? '';
        $category['slug'] = $_POST['slug'] ?? $category['slug'] ?? '';
        $category['h1'] = $_POST['h1'] ?? $category['h1'] ?? '';
        $category['title'] = $_POST['title'] ?? $category['title'] ?? '';
        $category['description'] = $_POST['description'] ?? $category['description'] ?? '';
        $category['intro'] = $_POST['intro'] ?? $category['intro'] ?? '';
        $category['is_active'] = isset($_POST['is_active']) ? 1 : 0;
        $success = 'Форматирование очищено. Проверьте текст и нажмите «Сохранить».';
    } elseif ($action === 'delete' && $categoryId) {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
        redirect('/admin/categories');
    }
    
    if ($action !== 'clear_format') {
    // Валидация
    $name = trim($_POST['name'] ?? '');
    $slugInput = trim($_POST['slug'] ?? '');
    
    if ($name === '') {
        $fieldErrors['name'] = 'Название обязательно';
        $errors[] = $fieldErrors['name'];
    }
    
    if ($slugInput === '' && $name !== '') {
        $slug = ensure_unique_slug($pdo, slugify($name), 'categories', $categoryId ?: 0);
    } elseif ($slugInput !== '') {
        $slug = normalize_slug($slugInput);
        if ($slug === '') {
            $fieldErrors['slug'] = 'Введите корректный slug или оставьте пустым для автогенерации из названия';
            $errors[] = $fieldErrors['slug'];
            $slug = '';
        } else {
            $slug = ensure_unique_slug($pdo, $slug, 'categories', $categoryId ?: 0);
        }
    } else {
        $slug = '';
    }
    if ($slug === '' && empty($fieldErrors['name'])) {
        $fieldErrors['slug'] = 'Slug обязателен. Оставьте поле пустым — он будет сгенерирован из названия.';
        $errors[] = $fieldErrors['slug'];
    }
    
    if (empty($errors)) {
        $data = [
            'slug' => $slug,
            'name' => $name,
            'h1' => trim($_POST['h1'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'intro' => trim($_POST['intro'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'content_title' => trim($_POST['content_title'] ?? ''),
            'content_body' => trim($_POST['content_body'] ?? ''),
            'content_format' => (trim($_POST['content_format'] ?? '') === 'html') ? 'html' : 'markdown',
            'content_is_active' => isset($_POST['content_is_active']) ? 1 : 0,
        ];
        
        if ($categoryId) {
            $data['updated_at'] = nowIso();
            $data['content_updated_at'] = $data['content_body'] !== '' ? nowIso() : null;
            $sql = 'UPDATE categories SET 
                    slug = ?, name = ?, h1 = ?, title = ?, description = ?, intro = ?, is_active = ?,
                    content_title = ?, content_body = ?, content_format = ?, content_is_active = ?, content_updated_at = ?, updated_at = ?
                    WHERE id = ?';
            $params = [
                $data['slug'], $data['name'], $data['h1'], $data['title'], $data['description'], $data['intro'], $data['is_active'],
                $data['content_title'], $data['content_body'], $data['content_format'], $data['content_is_active'], $data['content_updated_at'], $data['updated_at'],
                $categoryId
            ];
        } else {
            $data['created_at'] = nowIso();
            $data['updated_at'] = nowIso();
            $data['content_updated_at'] = $data['content_body'] !== '' ? nowIso() : null;
            $sql = 'INSERT INTO categories 
                    (slug, name, h1, title, description, intro, is_active, content_title, content_body, content_format, content_is_active, content_updated_at, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $params = [
                $data['slug'], $data['name'], $data['h1'], $data['title'], $data['description'], $data['intro'], $data['is_active'],
                $data['content_title'], $data['content_body'], $data['content_format'], $data['content_is_active'], $data['content_updated_at'],
                $data['created_at'], $data['updated_at']
            ];
        }
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if (!$categoryId) {
                $categoryId = (int) $pdo->lastInsertId();
            }
            $success = 'Категория сохранена';
            $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
            $stmt->execute([$categoryId]);
            $category = $stmt->fetch();
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000 || strpos($e->getMessage(), 'UNIQUE') !== false) {
                $fieldErrors['slug'] = 'Slug уже занят. Измените или оставьте пустым для автогенерации.';
                $errors[] = $fieldErrors['slug'];
            } else {
                $errors[] = 'Ошибка сохранения: ' . e($e->getMessage());
            }
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $categoryId ? 'Редактировать' : 'Создать' ?> категорию — Админка</title>
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
                        <h2><?= $categoryId ? 'Редактировать категорию' : 'Создать категорию' ?></h2>
                        <a href="/admin/categories" class="btn btn--ghost">Назад к списку</a>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert--error">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert--success"><?= e($success) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="admin-form">
                        <div class="form-group <?= !empty($fieldErrors['name']) ? 'form-group--error' : '' ?>">
                            <label>Название *</label>
                            <input type="text" name="name" value="<?= e($category['name'] ?? $_POST['name'] ?? '') ?>" required>
                            <?php if (!empty($fieldErrors['name'])): ?>
                                <span class="form-error"><?= e($fieldErrors['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group <?= !empty($fieldErrors['slug']) ? 'form-group--error' : '' ?>">
                            <label>Slug</label>
                            <input type="text" name="slug" value="<?= e($category['slug'] ?? $_POST['slug'] ?? '') ?>" placeholder="Оставьте пустым для автогенерации">
                            <small>Автогенерируется из названия, если пусто. Уникален среди категорий.</small>
                            <?php if (!empty($fieldErrors['slug'])): ?>
                                <span class="form-error"><?= e($fieldErrors['slug']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>H1</label>
                            <input type="text" name="h1" value="<?= e($category['h1'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Title (SEO)</label>
                            <input type="text" name="title" value="<?= e($category['title'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label>Description (SEO)</label>
                            <textarea name="description" rows="3"><?= e($category['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Intro (описание на странице)</label>
                            <textarea name="intro" rows="3"><?= e($category['intro'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" value="1" 
                                       <?= ($category && $category['is_active']) ? 'checked' : '' ?>>
                                Активна
                            </label>
                        </div>

                        <hr class="form-divider">
                        <h3 class="form-section-title">Контент / Статья (низ страницы)</h3>
                        <div class="form-group">
                            <label>Формат текста</label>
                            <div class="form-radio-group">
                                <label class="form-radio-label">
                                    <input type="radio" name="content_format" value="markdown"
                                           <?= (($category['content_format'] ?? $_POST['content_format'] ?? 'markdown') === 'markdown') ? 'checked' : '' ?>>
                                    Markdown / Текст
                                </label>
                                <label class="form-radio-label">
                                    <input type="radio" name="content_format" value="html"
                                           <?= (($category['content_format'] ?? $_POST['content_format'] ?? '') === 'html') ? 'checked' : '' ?>>
                                    HTML
                                </label>
                            </div>
                            <p class="form-hint form-hint--format form-hint--markdown">Поддерживается Markdown: заголовки (#, ##, ###), жирный (**), списки (- ), ссылки [текст](url).</p>
                            <p class="form-hint form-hint--format form-hint--html" style="display:none;">Можно вставлять HTML. Разрешены безопасные теги (таблицы поддерживаются).</p>
                        </div>
                        <div class="form-group">
                            <label>Заголовок статьи</label>
                            <input type="text" name="content_title" value="<?= e($category['content_title'] ?? $_POST['content_title'] ?? '') ?>" placeholder="Например: Описание марки AISI 304">
                        </div>
                        <div class="form-group">
                            <label>Текст статьи</label>
                            <textarea name="content_body" rows="14" class="admin-textarea--wide" id="contentBody"><?= e($category['content_body'] ?? $_POST['content_body'] ?? '') ?></textarea>
                            <?php
                            $body = $category['content_body'] ?? $_POST['content_body'] ?? '';
                            $words = $body ? count(preg_split('/\s+/u', trim($body), -1, PREG_SPLIT_NO_EMPTY)) : 0;
                            $chars = mb_strlen($body);
                            ?>
                            <small class="form-meta">Символов: <?= $chars ?>, слов: <?= $words ?></small>
                            <div class="form-group__actions">
                                <button type="submit" name="action" value="clear_format" class="btn btn--ghost btn--sm">Очистить форматирование</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="content_is_active" value="1"
                                       <?= ($category && !empty($category['content_is_active'])) ? 'checked' : '' ?>>
                                Показывать на странице
                            </label>
                        </div>
                        <?php if (!empty($category['content_updated_at'])): ?>
                            <small class="form-meta">Обновлено: <?= e($category['content_updated_at']) ?></small>
                        <?php endif; ?>

                        <div class="form-actions">
                            <button type="submit" name="action" value="save" class="btn btn--primary">Сохранить</button>
                            <?php if ($categoryId): ?>
                                <button type="submit" name="action" value="delete" class="btn btn--danger" 
                                        onclick="return confirm('Удалить категорию?')">Удалить</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
    (function() {
        var formatMarkdown = document.querySelector('input[name="content_format"][value="markdown"]');
        var formatHtml = document.querySelector('input[name="content_format"][value="html"]');
        var hintMarkdown = document.querySelector('.form-hint--markdown');
        var hintHtml = document.querySelector('.form-hint--html');
        function toggleHint() {
            var isHtml = formatHtml && formatHtml.checked;
            if (hintMarkdown) hintMarkdown.style.display = isHtml ? 'none' : 'block';
            if (hintHtml) hintHtml.style.display = isHtml ? 'block' : 'none';
        }
        if (formatMarkdown) formatMarkdown.addEventListener('change', toggleHint);
        if (formatHtml) formatHtml.addEventListener('change', toggleHint);
        toggleHint();
    })();
    </script>
</body>
</html>
