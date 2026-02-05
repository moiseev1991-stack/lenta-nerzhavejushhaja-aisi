<?php
/**
 * Контент страницы 404 (вставляется в layout в <main>).
 * Ожидает: base_url(), e() из helpers.
 */
?>
<section class="not-found" aria-label="Страница не найдена">
    <div class="container">
        <div class="not-found__inner">
            <h1 class="not-found__title"><span class="not-found__code">404</span> — страница не найдена</h1>
            <p class="not-found__text">Похоже, вы перешли по неверной ссылке или страница была удалена.</p>
            <p class="not-found__hint">Попробуйте перейти в нужный раздел:</p>
            <div class="not-found__actions">
                <a href="<?= base_url() ?>" class="btn btn--primary not-found__btn">На главную</a>
                <a href="<?= base_url() ?>" class="btn btn--ghost not-found__btn">Марки AISI</a>
            </div>
            <ul class="not-found__links">
                <li><a href="<?= base_url() ?>">Марки AISI</a></li>
                <li><a href="<?= base_url('delivery/') ?>">Доставка</a></li>
                <li><a href="<?= base_url('payment/') ?>">Оплата</a></li>
                <li><a href="<?= base_url('contacts/') ?>">Контакты</a></li>
                <li><a href="<?= base_url('bonus/') ?>">Получить бонус</a></li>
            </ul>
        </div>
    </div>
</section>
