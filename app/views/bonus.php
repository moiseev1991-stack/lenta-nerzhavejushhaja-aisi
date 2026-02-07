<?php
// Страница «Получить бонус» — программа лояльности, контент из таблицы pages (slug=bonus).
// Ожидает массив $bonusPage с ключами title, content.
?>

<div class="bonus-page">
    <section class="bonus-hero">
        <div class="container">
            <div class="bonus-hero__inner">
                <div class="bonus-hero__content">
                    <h1 class="bonus-hero__title"><?= e($bonusPage['title'] ?? 'Получайте бонусы за покупки') ?></h1>
                    <p class="bonus-hero__subtitle">
                        Возвращаем до 10% от суммы заказа бонусами — используйте их для оплаты следующих покупок и услуг.
                    </p>
                    <div class="bonus-hero__actions">
                        <button type="button" class="btn btn--primary btn--large js-open-request-modal bonus-hero__cta">
                            Стать участником
                        </button>
                        <button type="button" class="btn btn--ghost js-open-request-modal bonus-hero__secondary">
                            Задать вопрос
                        </button>
                    </div>
                </div>
                <div class="bonus-hero__visual">
                    <div class="bonus-hero-image" aria-hidden="true">
                        <img src="<?= base_url('public/img/bonus_groups/s.webp') ?>" alt="Баннер программы лояльности" class="bonus-hero-image__img" width="400" height="240" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bonus-section">
        <div class="container">
            <div class="bonus-card">
                <h2 class="bonus-card__title">Описание программы лояльности «Железная ставка»</h2>
                <div class="bonus-card__body">
                    <?= $bonusPage['content'] ?? '' ?>
                </div>
            </div>
        </div>
    </section>

    <section class="bonus-section">
        <div class="container">
            <h2 class="bonus-section__title">Товарные группы в программе</h2>
            <div class="bonus-groups">
                <?php
                $groups = [
                    ['label' => 'Лист',   'img' => 'group-list.png',  'alt' => 'Лист нержавеющий'],
                    ['label' => 'Труба',  'img' => 'group-truba.png', 'alt' => 'Труба нержавеющая'],
                    ['label' => 'Рулон',  'img' => 'group-rulon.png',  'alt' => 'Рулон нержавеющий'],
                    ['label' => 'Лента',  'img' => 'group-lenta.png', 'alt' => 'Лента нержавеющая'],
                    ['label' => 'Круг / пруток', 'img' => 'krug-prutok.webp', 'alt' => 'Круг / пруток'],
                ];
                foreach ($groups as $item):
                    $label = $item['label'];
                    $img = $item['img'] ?? null;
                    $alt = $item['alt'] ?? '';
                ?>
                    <article class="bonus-group-card">
                        <div class="bonus-group-card__image">
                            <?php if ($img): ?>
                                <img src="<?= base_url('public/img/bonus_groups/' . $img) ?>" alt="<?= e($alt) ?>" width="160" height="96" loading="lazy" class="bonus-group-card__img" onerror="this.style.display='none';var p=this.nextElementSibling;if(p)p.style.display='block';">
                                <div class="bonus-group-card__placeholder" style="display:none" aria-hidden="true"></div>
                            <?php else: ?>
                                <div class="bonus-group-card__placeholder" aria-hidden="true"></div>
                            <?php endif; ?>
                        </div>
                        <h3 class="bonus-group-card__label"><?= e($label) ?></h3>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="bonus-section__note">
                Также программа распространяется на чёрный, цветной и нержавеющий металлопрокат!
            </p>
        </div>
    </section>

    <section class="bonus-section">
        <div class="container">
            <h2 class="bonus-section__title">На что можно потратить бонусы?</h2>
            <ol class="bonus-list">
                <li>Оплачивать следующий закуп материала.</li>
                <li>Оплачивать условия доставки по всей России.</li>
                <li>Заказывать услуги по металлообработке: резка, шлифовка и другие операции.</li>
                <li>Другое — обсуждается индивидуально по телефону или на встрече с менеджером.</li>
            </ol>
        </div>
    </section>

    <section class="bonus-section bonus-section--cta" id="join">
        <div class="container">
            <div class="bonus-cta">
                <h2 class="bonus-cta__title">
                    Станьте участником программы лояльности — забирайте бонусы до 10%
                </h2>
                <div class="bonus-cta__actions">
                    <button type="button" class="btn btn--primary btn--large js-open-request-modal">
                        Стать участником
                    </button>
                    <button type="button" class="btn btn--ghost js-open-request-modal">
                        Задать вопрос
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>

