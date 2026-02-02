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
    
    if ($action === 'delete' && $categoryId) {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
        redirect('/admin/categories');
    }
    
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
        ];
        
        if ($categoryId) {
            // Обновление
            $data['updated_at'] = nowIso();
            $sql = 'UPDATE categories SET 
                    slug = ?, name = ?, h1 = ?, title = ?, description = ?, intro = ?, is_active = ?, updated_at = ?
                    WHERE id = ?';
            $params = array_merge(
                array_values($data),
                [$categoryId]
            );
        } else {
            // Создание
            $data['created_at'] = nowIso();
            $data['updated_at'] = nowIso();
            $sql = 'INSERT INTO categories 
                    (slug, name, h1, title, description, intro, is_active, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $params = array_values($data);
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
</body>
</html>
