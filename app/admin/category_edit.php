<?php
require_admin();

require __DIR__ . '/../db.php';
require __DIR__ . '/../helpers.php';

$pdo = db();

$categoryId = $_GET['id'] ?? null;
$category = null;
$errors = [];
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
    $slug = trim($_POST['slug'] ?? '');
    $name = trim($_POST['name'] ?? '');
    
    if (!$slug) $errors[] = 'Slug обязателен';
    if (!$name) $errors[] = 'Название обязательно';
    
    if ($slug) {
        // Проверка уникальности slug
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? AND id != ?');
        $stmt->execute([$slug, $categoryId ?: 0]);
        if ($stmt->fetch()) {
            $errors[] = 'Slug уже используется';
        }
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
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if (!$categoryId) {
            $categoryId = $pdo->lastInsertId();
        }
        
        $success = 'Категория сохранена';
        // Перезагружаем категорию
        $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch();
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
                        <div class="form-group">
                            <label>Slug *</label>
                            <input type="text" name="slug" value="<?= e($category['slug'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Название *</label>
                            <input type="text" name="name" value="<?= e($category['name'] ?? '') ?>" required>
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
