<?php
/**
 * Блок "Самовывоз и отгрузка" — региональный.
 * Показывает данные только выбранного региона (управляется JS-переключателем).
 */
if (!isset($_regions) || empty($_regions)) {
    $htfConfig    = require __DIR__ . '/../../config.php';
    $_htfRegions  = $htfConfig['regions'] ?? [];
    $_htfDefault  = $htfConfig['region_default'] ?? 'nn';
} else {
    $_htfRegions = $_regions;
    $_htfDefault = $_regionDefault ?? 'nn';
}

$_htfData = [
    'nn' => [
        'city'       => 'Нижний Новгород',
        'address'    => 'Московское ш., 320Б, корп. 1',
        'phone'      => '+7 (831) 211-97-56',
        'tel'        => '+78312119756',
        'map_src'    => 'https://yandex.ru/map-widget/v1/?ll=43.833424%2C56.303605&z=17&pt=43.833424,56.303605,pm2rdl&l=map',
        'route_url'  => 'https://yandex.com/maps/-/CPClyM7a',
        'route_steps'=> [
            'Двигайтесь по Московскому шоссе',
            'Поворот на дом 320Б, корп. 1',
            'Въезд на территорию склада',
        ],
    ],
    'msk' => [
        'city'       => 'Москва',
        'address'    => 'ул. Южнопортовая, 7А, стр. 2',
        'phone'      => '+7 (495) 023-77-64',
        'tel'        => '+74950237764',
        'map_src'    => 'https://yandex.ru/map-widget/v1/?ll=37.693417%2C55.708440&z=17&pt=37.693417,55.708440,pm2rdl&l=map',
        'route_url'  => 'https://yandex.com/maps/-/CPClu84z',
        'route_steps'=> [
            'Двигайтесь по ул. Южнопортовой',
            'Поворот на дом 7А (строение 2)',
            'Въезд на территорию склада',
        ],
    ],
    'spb' => [
        'city'       => 'Санкт-Петербург',
        'address'    => 'Московское ш., 161, лит. А (Шушары)',
        'phone'      => '+7 (812) 426-56-38',
        'tel'        => '+78124265638',
        'map_src'    => 'https://yandex.ru/map-widget/v1/?ll=30.425016%2C59.772968&z=17&pt=30.425016,59.772968,pm2rdl&l=map',
        'route_url'  => 'https://yandex.com/maps/-/CPCluLp8',
        'route_steps'=> [
            'Двигайтесь по Московскому шоссе на юг (п. Шушары)',
            'Поворот на дом 161 (литер А)',
            'Въезд на территорию склада',
        ],
    ],
];
?>
<section class="how-to-find" aria-labelledby="how-to-find-title">
    <div class="container">
        <h2 id="how-to-find-title">Самовывоз и отгрузка</h2>

        <?php if (!empty($_htfRegions)): ?>
        <div class="how-to-find__region-tabs">
            <?php foreach ($_htfRegions as $_rKey => $_r): ?>
            <button type="button"
                class="region-switcher__btn how-to-find__tab<?= $_rKey === $_htfDefault ? ' region-switcher__btn--active' : '' ?>"
                data-region="<?= htmlspecialchars($_rKey, ENT_QUOTES) ?>"
                data-phone="<?= htmlspecialchars($_r['phone'], ENT_QUOTES) ?>"
                data-tel="<?= htmlspecialchars($_r['tel'], ENT_QUOTES) ?>"
            ><?= htmlspecialchars($_r['label'], ENT_QUOTES, 'UTF-8') ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php foreach ($_htfRegions as $_rKey => $_r):
            $d = $_htfData[$_rKey] ?? null;
            if (!$d) continue;
            $isDefault = ($_rKey === $_htfDefault);
        ?>
        <div class="how-to-find__inner how-to-find__region-block"
             data-region="<?= htmlspecialchars($_rKey, ENT_QUOTES) ?>"
             <?= !$isDefault ? 'hidden' : '' ?>>

            <div class="how-to-find__map">
                <iframe
                    src="<?= htmlspecialchars($d['map_src'], ENT_QUOTES) ?>"
                    class="how-to-find__iframe"
                    width="100%"
                    height="100%"
                    frameborder="0"
                    allowfullscreen
                    loading="lazy"
                    style="border:none;display:block"
                    title="Склад в <?= htmlspecialchars($d['city'], ENT_QUOTES) ?>">
                </iframe>
            </div>

            <div class="how-to-find__info">
                <div class="how-to-find__address">
                    <strong>Адрес</strong>
                    <p><?= htmlspecialchars($d['city'] . ', ' . $d['address'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p>
                        <a href="tel:<?= htmlspecialchars($d['tel'], ENT_QUOTES) ?>"
                           style="color:#2e7d32;font-weight:600;text-decoration:none"
                        ><?= htmlspecialchars($d['phone'], ENT_QUOTES) ?></a>
                    </p>
                </div>
                <div class="how-to-find__route">
                    <strong>Как проехать</strong>
                    <ol>
                        <?php foreach ($d['route_steps'] as $step): ?>
                        <li><?= htmlspecialchars($step, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <a href="<?= htmlspecialchars($d['route_url'], ENT_QUOTES) ?>"
                   target="_blank" rel="noopener" class="btn-route">Построить маршрут →</a>
            </div>

        </div>
        <?php endforeach; ?>

    </div>
</section>
