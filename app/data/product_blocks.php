<?php
/**
 * Данные для карточек товара: sidebar, короткие блоки доставки/оплаты/контактов/резки.
 * Подключается в product.php и product_blocks.php partial.
 */
return [
    'sidebar_items' => [
        [
            'key' => 'phone',
            'icon' => 'phone',
            'text' => null, // заполняется из config
            'href' => 'tel:+78002003943',
            'is_button' => false,
        ],
        [
            'key' => 'email',
            'icon' => 'mail',
            'text' => null,
            'href' => 'mailto:ev18011@yandex.ru',
            'is_button' => false,
        ],
        [
            'key' => 'vat',
            'icon' => 'check',
            'text' => 'Работаем с НДС',
            'href' => null,
            'is_button' => false,
        ],
        [
            'key' => 'delivery',
            'icon' => 'truck',
            'text' => 'Доставка по всей России',
            'href' => null,
            'is_button' => false,
        ],
        [
            'key' => 'pickup',
            'icon' => 'package',
            'text' => 'Самовывоз / ТК / курьер',
            'href' => null,
            'is_button' => false,
        ],
        [
            'key' => 'branches',
            'icon' => 'map-pin',
            'text' => 'Отгрузка из филиалов',
            'href' => null,
            'is_button' => false,
        ],
        [
            'key' => 'cutting',
            'icon' => 'scissors',
            'text' => 'Резка в размер / отмотка от 1 м',
            'href' => null,
            'is_button' => false,
        ],
    ],

    'delivery_short' => 'Самовывоз бесплатно, курьер по Москве от 500 ₽, ТК и Почта — по тарифам. Мин. заказ 10 000 ₽.',

    'payment_short' => 'Физлица: наличные, карта, онлайн. Юрлица: счёт с НДС или без, отсрочка для крупных партий.',

    'contacts_short' => 'Ответим за 15 минут, счёт в течение дня. Пн–Пт 9:00–18:00, Сб 10:00–15:00.',

    'cutting_short' => 'Резка от 2,5 мм, отмотка от 1 м. Упаковка под перевозку. Сертификаты EN 10204 с каждой поставкой.',
    'cutting_block' => 'Резка от 2,5 мм, отмотка от 1 м. Упаковка под перевозку. Сертификаты EN 10204 с каждой поставкой.',

    'price_factors' => [
        'объём заказа',
        'марка стали',
        'толщина',
        'ширина',
        'состояние',
        'поверхность',
        'доп. услуги',
    ],

    'cities_short' => 7,
];
