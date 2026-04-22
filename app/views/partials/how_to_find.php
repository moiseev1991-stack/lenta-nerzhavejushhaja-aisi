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
        'map_src'    => 'https://yandex.ru/map-widget/v1/?ll=43.9557%2C56.2379&z=16&pt=43.9557,56.2379,pm2grl&l=map',
        'route_url'  => 'https://yandex.ru/maps/?rtext=~56.2379,43.9557&rtt=auto',
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
        'map_src'    => 'https://yandex.ru/map-widget/v1/?ll=37.6728%2C55.7058&z=16&pt=37.6728,55.7058,pm2grl&l=map',
        'route_url'  => 'https://yandex.ru/maps/?rtext=~55.7058,37.6728&rtt=auto',
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
        'map_src'    => 'https://yandex.ru/map-widget/v1/?ll=30.3897%2C59.8516&z=16&pt=30.3897,59.8516,pm2grl&l=map',
        'route_url'  => 'https://yandex.ru/maps/?rtext=~59.8516,30.3897&rtt=auto',
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
