<?php
require_admin();

require __DIR__ . '/../db.php';
require __DIR__ . '/../helpers.php';

$pdo = db();
$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'apply') {
    try {
        $pdo->beginTransaction();

        // 1) Очистка плейсхолдеров в products.description.
        //    Освобождает поле для автогенератора generate_product_description_auto(),
        //    который соберёт богатое описание по марке + параметрам.
        $patterns = [
            'Купить%по выгодной цене.',
            'Купить лента нержавеющая%',
        ];
        $cleared = 0;
        foreach ($patterns as $p) {
            $stmt = $pdo->prepare('UPDATE products SET description = "" WHERE description LIKE ?');
            $stmt->execute([$p]);
            $cleared += $stmt->rowCount();
        }
        $messages[] = 'Очищено описаний-плейсхолдеров: ' . $cleared;

        // 2) AISI 301 — пружинная сталь по определению. Проставляем spring=1 всем товарам этой марки.
        $stmt = $pdo->prepare('UPDATE products SET spring = 1
                               WHERE category_id IN (SELECT id FROM categories WHERE slug = ?)
                                 AND (spring IS NULL OR spring = 0)');
        $stmt->execute(['aisi-301']);
        $messages[] = 'Установлен spring=1 у товаров AISI 301: ' . $stmt->rowCount();

        $pdo->commit();
        $messages[] = 'Готово. Изменения применены.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errors[] = 'Ошибка: ' . $e->getMessage();
    }
}

// Превью того, что будет сделано (без правок).
$previewClear = (int)$pdo->query("SELECT COUNT(*) FROM products
    WHERE description LIKE 'Купить%по выгодной цене.'
       OR description LIKE 'Купить лента нержавеющая%'")->fetchColumn();

$previewSpring = (int)$pdo->query("SELECT COUNT(*) FROM products
    WHERE category_id IN (SELECT id FROM categories WHERE slug = 'aisi-301')
      AND (spring IS NULL OR spring = 0)")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO cleanup — Админка</title>
    <link rel="stylesheet" href="<?= asset_url('assets/styles.css') ?>">
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
                        <a href="<?= base_url('admin/restore_db') ?>">Восстановление базы</a>
                        <a href="<?= base_url('admin/seo_cleanup') ?>" class="active">SEO cleanup</a>
                        <a href="<?= base_url('admin/logout') ?>">Выход</a>
                    </nav>
                </div>
            </div>
        </header>

        <main class="container" style="padding: 24px 0;">
            <h2>SEO cleanup: одноразовая прочистка данных</h2>

            <?php foreach ($messages as $m): ?>
                <div style="background:#e8f5e9;border:1px solid #66bb6a;padding:12px;margin:8px 0;border-radius:4px;">
                    <?= e($m) ?>
                </div>
            <?php endforeach; ?>
            <?php foreach ($errors as $err): ?>
                <div style="background:#ffebee;border:1px solid #ef5350;padding:12px;margin:8px 0;border-radius:4px;">
                    <?= e($err) ?>
                </div>
            <?php endforeach; ?>

            <section style="background:#fff;border:1px solid #ddd;border-radius:6px;padding:20px;margin:16px 0;">
                <h3 style="margin-top:0;">Что будет сделано</h3>
                <ol style="line-height:1.7;">
                    <li>
                        Очищены плейсхолдер-описания вида «Купить лента нержавеющая ... по выгодной цене.»
                        у товаров — после этого автогенератор соберёт полноценное описание из марки + параметров.
                        <br>
                        <strong>Затронет товаров:</strong> <?= $previewClear ?>
                    </li>
                    <li>
                        Проставлены <code>spring = 1</code> всем товарам категории AISI 301
                        (это пружинная марка, текущее <code>spring = 0</code> противоречит позиционированию).
                        <br>
                        <strong>Затронет товаров:</strong> <?= $previewSpring ?>
                    </li>
                </ol>
                <p style="color:#666;font-size:14px;">
                    Операция идемпотентна: повторный запуск не сделает ничего, если данные уже почищены.
                    Транзакция: при ошибке откатывается всё.
                </p>
            </section>

            <?php if ($previewClear === 0 && $previewSpring === 0): ?>
                <div style="background:#e3f2fd;border:1px solid #2196f3;padding:12px;border-radius:4px;">
                    Нечего применять — данные уже в чистом состоянии.
                </div>
            <?php else: ?>
                <form method="post" onsubmit="return confirm('Применить SEO cleanup? Изменения в БД.');">
                    <input type="hidden" name="action" value="apply">
                    <button type="submit" class="btn btn--primary"
                            style="background:#1976d2;color:#fff;padding:12px 24px;border:0;border-radius:4px;font-size:16px;cursor:pointer;">
                        Применить
                    </button>
                </form>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
