<?php
return [
    'contacts' => [
        'h1' => 'Контакты',
        'title' => 'Контакты — офисы в Нижнем Новгороде, Москве и Санкт-Петербурге | Каталог AISI',
        'description' => 'Офисы и склады нержавеющей ленты AISI в Нижнем Новгороде, Москве и Санкт-Петербурге. Телефоны, адреса, карты.',
        'content' => '
<div class="contacts-page">

    <p class="contacts-page__intro">Выберите ваш город — отобразятся адрес, телефон и карта ближайшего офиса.</p>

    <!-- Нижний Новгород -->
    <div class="office-card" data-region="nn">
        <div class="office-card__info">
            <h2 class="office-card__city">Нижний Новгород</h2>
            <ul class="office-card__details">
                <li class="office-card__row">
                    <span class="office-card__label">Адрес</span>
                    <span class="office-card__value">Московское ш., 320Б, корп. 1</span>
                </li>
                <li class="office-card__row">
                    <span class="office-card__label">Телефон</span>
                    <span class="office-card__value"><a href="tel:+78312119756" class="office-card__phone">+7 (831) 211-97-56</a></span>
                </li>
                <li class="office-card__row">
                    <span class="office-card__label">Email</span>
                    <span class="office-card__value"><a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a></span>
                </li>
                <li class="office-card__row">
                    <span class="office-card__label">Режим работы</span>
                    <span class="office-card__value">Пн–Пт 9:00–18:00, Сб 10:00–15:00</span>
                </li>
            </ul>
            <a href="https://yandex.ru/maps/?text=%D0%9D%D0%B8%D0%B6%D0%BD%D0%B8%D0%B9+%D0%9D%D0%BE%D0%B2%D0%B3%D0%BE%D1%80%D0%BE%D0%B4+%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%BE%D0%B5+320%D0%91"
               target="_blank" rel="noopener" class="btn btn--ghost office-card__map-link">Построить маршрут →</a>
        </div>
        <div class="office-card__map">
            <iframe
                src="https://yandex.ru/map-widget/v1/?ll=43.9557%2C56.2379&z=16&pt=43.9557,56.2379,pm2grl&l=map"
                width="100%" height="100%" frameborder="0" allowfullscreen loading="lazy"
                style="border:none;display:block;min-height:320px;"
                title="Офис в Нижнем Новгороде"></iframe>
        </div>
    </div>

    <!-- Москва -->
    <div class="office-card" data-region="msk" hidden>
        <div class="office-card__info">
            <h2 class="office-card__city">Москва</h2>
            <ul class="office-card__details">
                <li class="office-card__row">
                    <span class="office-card__label">Адрес</span>
                    <span class="office-card__value">Южнопортовая ул., 7А стр. 2</span>
                </li>
                <li class="office-card__row">
                    <span class="office-card__label">Телефон</span>
                    <span class="office-card__value"><a href="tel:+74950237764" class="office-card__phone">+7 (495) 023-77-64</a></span>
                </li>
                <li class="office-card__row">
                    <span class="office-card__label">Email</span>
                    <span class="office-card__value"><a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a></span>
                </li>
                <li class="office-card__row">
                    <span class="office-card__label">Режим работы</span>
                    <span class="office-card__value">Пн–Пт 9:00–18:00, Сб 10:00–15:00</span>
                </li>
            </ul>
            <a href="https://yandex.ru/maps/?text=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0+%D0%AE%D0%B6%D0%BD%D0%BE%D0%BF%D0%BE%D1%80%D1%82%D0%BE%D0%B2%D0%B0%D1%8F+7%D0%90"
               target="_blank" rel="noopener" class="btn btn--ghost office-card__map-link">Построить маршрут →</a>
        </div>
        <div class="office-card__map">
            <iframe
                src="https://yandex.ru/map-widget/v1/?ll=37.6728%2C55.7058&z=16&pt=37.6728,55.7058,pm2grl&l=map"
                width="100%" height="100%" frameborder="0" allowfullscreen loading="lazy"
                style="border:none;display:block;min-height:320px;"
                title="Офис в Москве"></iframe>
        </div>
    </div>

    <!-- Санкт-Петербург -->
    <div class="office-card" data-region="spb" hidden>
        <div class="office-card__info">
            <h2 class="office-card__city">Санкт-Петербург</h2>
            <ul class="office-card__details">
                <li class="office-card__row">
                    <span class="office-card__label">Адрес</span>
                    <span class="office-card__value">Московское ш., 161, лит. А</span>
                </li>
                <li class="office-card__row">
                    <span class="office-card__label">Телефон</span>
                    <span class="office-card__value"><a href="tel:+78124265638" class="office-card__phone">+7 (812) 426-56-38</a></span>
                </li>
                <li class="office-card__row">
                    <span class="office-card__label">Email</span>
                    <span class="office-card__value"><a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a></span>
                </li>
                <li class="office-card__row">
                    <span class="office-card__label">Режим работы</span>
                    <span class="office-card__value">Пн–Пт 9:00–18:00, Сб 10:00–15:00</span>
                </li>
            </ul>
            <a href="https://yandex.ru/maps/?text=%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3+%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%BE%D0%B5+161"
               target="_blank" rel="noopener" class="btn btn--ghost office-card__map-link">Построить маршрут →</a>
        </div>
        <div class="office-card__map">
            <iframe
                src="https://yandex.ru/map-widget/v1/?ll=30.3897%2C59.8516&z=16&pt=30.3897,59.8516,pm2grl&l=map"
                width="100%" height="100%" frameborder="0" allowfullscreen loading="lazy"
                style="border:none;display:block;min-height:320px;"
                title="Офис в Санкт-Петербурге"></iframe>
        </div>
    </div>

    <div class="contacts-page__requisites">
        <h2>Реквизиты</h2>
        <ul>
            <li><strong>ИНН:</strong> 526016545409</li>
            <li><strong>Расчётный счёт:</strong> 40802810920000907140</li>
            <li><strong>Банк:</strong> ООО «Банк Точка»</li>
            <li><strong>БИК:</strong> 044525104</li>
            <li><strong>Корр. счёт:</strong> 30101810745374525104</li>
            <li><em>Работаем с НДС</em></li>
        </ul>
    </div>

</div>
        ',
    ],
    
    'delivery' => [
        'h1' => 'Доставка',
        'title' => 'Доставка нержавеющих лент | Каталог AISI',
        'description' => 'Условия и способы доставки нержавеющих лент по России. Доставка в Москву, Санкт-Петербург и другие города.',
        'content' => '
            <div style="max-width: 800px; margin: 0 auto;">
                <h2>Условия доставки</h2>
                <p>Мы осуществляем доставку нержавеющих лент по всей территории России. Сроки и стоимость доставки зависят от региона и выбранного способа.</p>
                
                <h3>Способы доставки</h3>
                <ul>
                    <li><strong>Самовывоз</strong> — бесплатно, со склада в Москве</li>
                    <li><strong>Курьерская доставка по Москве</strong> — от 500 ₽, в течение 1-2 рабочих дней</li>
                    <li><strong>Доставка транспортной компанией</strong> — расчет по тарифам ТК</li>
                    <li><strong>Почта России</strong> — для небольших партий</li>
                </ul>
                
                <h3>Доставка авто</h3>
                <p>Отдельно можно заказать доставку авто. Стоимость от грузоподъёмности:</p>
                <ul>
                    <li><strong>До 5 тн</strong> — от 10 000 ₽</li>
                    <li><strong>До 10 тн</strong> — от 18 000 ₽</li>
                    <li><strong>До 20 тн</strong> — от 25 000 ₽</li>
                </ul>
                
                <h3>Доставка в основные города России</h3>
                <table style="width: 100%; border-collapse: collapse; margin: 2rem 0;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Город</th>
                            <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Срок доставки</th>
                            <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Примечание</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Москва</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">1-2 дня</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Курьерская доставка</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Санкт-Петербург</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">3-5 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Новосибирск</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">5-7 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Екатеринбург</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">4-6 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Казань</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">3-5 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Нижний Новгород</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">3-5 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Челябинск</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">5-7 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Самара</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">4-6 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Омск</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">5-7 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Ростов-на-Дону</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">4-6 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Уфа</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">4-6 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Красноярск</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">7-10 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Воронеж</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">3-5 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Пермь</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">5-7 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>Волгоград</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">4-6 дней</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Транспортная компания</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>Важные условия</h3>
                <ul>
                    <li>Минимальная сумма заказа для доставки: 10 000 ₽</li>
                    <li>Доставка оплачивается отдельно (кроме самовывоза)</li>
                    <li>Точную стоимость доставки уточняйте у менеджера</li>
                    <li>Возможна доставка в другие города — уточняйте по телефону</li>
                </ul>
            </div>
        ',
    ],
    
    'payment' => [
        'h1' => 'Способы оплаты',
        'title' => 'Способы оплаты | Каталог AISI',
        'description' => 'Удобные способы оплаты заказа: наличные, банковский перевод, карта, онлайн платежи.',
        'content' => '
            <div style="max-width: 800px; margin: 0 auto;">
                <h2>Способы оплаты</h2>
                <p>Мы предлагаем различные удобные способы оплаты для наших клиентов.</p>
                
                <h3>Для физических лиц</h3>
                <ul>
                    <li><strong>Наличными</strong> — при самовывозе или курьерской доставке</li>
                    <li><strong>Банковской картой</strong> — Visa, MasterCard, МИР</li>
                    <li><strong>Онлайн платежи</strong> — через платежные системы (Сбербанк Онлайн, Яндекс.Касса)</li>
                    <li><strong>Электронные кошельки</strong> — Яндекс.Деньги, WebMoney, QIWI</li>
                </ul>
                
                <h3>Для юридических лиц</h3>
                <ul>
                    <li><strong>Банковский перевод</strong> — по счету с НДС или без НДС</li>
                    <li><strong>Оплата по факту</strong> — для постоянных клиентов (по договору)</li>
                    <li><strong>Отсрочка платежа</strong> — для крупных заказов (обсуждается индивидуально)</li>
                </ul>
                
                <h3>Реквизиты для оплаты</h3>
                <div style="background: #f9f9f9; padding: 1.5rem; border-radius: 5px; margin: 2rem 0;">
                    <p><strong>ИНН:</strong> 526016545409</p>
                    <p><strong>Расчётный счёт:</strong> 40802810920000907140</p>
                    <p><strong>Название банка:</strong> ООО "Банк Точка"</p>
                    <p><strong>БИК:</strong> 044525104</p>
                    <p><strong>Корр. счёт:</strong> 30101810745374525104</p>
                    <p><em>Работаем с НДС</em></p>
                </div>
                
                <h3>Условия оплаты</h3>
                <ul>
                    <li>Оплата наличными — при получении товара</li>
                    <li>Оплата картой — возможна при самовывозе или курьерской доставке</li>
                    <li>Банковский перевод — предоплата 100% или по договору</li>
                    <li>Срок оплаты по счету: 3-5 банковских дней</li>
                </ul>
            </div>
        ',
    ],
    
    'about' => [
        'h1' => 'О компании',
        'title' => 'О компании | Каталог AISI',
        'description' => 'Информация о нашей компании, опыте работы и преимуществах.',
        'content' => '
            <div style="max-width: 800px; margin: 0 auto;">
                <h2>О нашей компании</h2>
                <p>Мы специализируемся на поставке нержавеющих лент различных марок AISI для промышленных и коммерческих нужд.</p>
                
                <h3>Наши преимущества</h3>
                <ul>
                    <li><strong>Широкий ассортимент</strong> — более 16 марок нержавеющих сталей</li>
                    <li><strong>Качество продукции</strong> — все товары имеют сертификаты соответствия</li>
                    <li><strong>Конкурентные цены</strong> — прямые поставки от производителей</li>
                    <li><strong>Быстрая доставка</strong> — по Москве и всей России</li>
                    <li><strong>Опыт работы</strong> — более 10 лет на рынке</li>
                    <li><strong>Индивидуальный подход</strong> — работаем с каждым клиентом</li>
                </ul>
                
                <h3>Наша продукция</h3>
                <p>Мы предлагаем нержавеющие ленты следующих марок:</p>
                <ul>
                    <li>AISI 201, 301, 304, 304L</li>
                    <li>AISI 310, 310S, 316, 316L, 316Ti</li>
                    <li>AISI 321, 409, 420, 430, 431, 441, 904L</li>
                </ul>
                
                <h3>Сферы применения</h3>
                <p>Наша продукция используется в различных отраслях:</p>
                <ul>
                    <li>Пищевая промышленность</li>
                    <li>Химическая промышленность</li>
                    <li>Медицинское оборудование</li>
                    <li>Строительство и архитектура</li>
                    <li>Автомобильная промышленность</li>
                    <li>Энергетика</li>
                    <li>Нефтегазоперерабатывающая промышленность</li>
                </ul>
                
                <h3>Наши клиенты</h3>
                <p>Мы работаем с предприятиями различных масштабов: от небольших мастерских до крупных промышленных компаний. Наши клиенты ценят нас за надежность, качество и оперативность.</p>
                
                <h3>Контакты</h3>
                <p><strong>Телефон:</strong> <a href="tel:+78002003943">+7 (800) 200-39-43</a></p>
                <p><strong>Email:</strong> <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a></p>

                {{PDF_CATALOG}}
            </div>
        ',
    ],
    
    'price' => [
        'h1' => 'Прайс-лист',
        'title' => 'Прайс-лист на нержавеющие ленты | Каталог AISI',
        'description' => 'Актуальные цены на нержавеющие ленты различных марок AISI. Цены за килограмм с НДС.',
        'content' => '
            <div style="max-width: 1000px; margin: 0 auto;">
                <h2>Прайс-лист</h2>
                <p>Цены указаны за 1 килограмм с НДС. Точную стоимость уточняйте у менеджера, так как цена может зависеть от объема заказа, толщины, ширины и других параметров.</p>
                
                <table style="width: 100%; border-collapse: collapse; margin: 2rem 0;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Марка стали</th>
                            <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Толщина (мм)</th>
                            <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: right;">Цена от (₽/кг с НДС)</th>
                            <th style="padding: 0.75rem; border: 1px solid #ddd; text-align: left;">Примечание</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>AISI 201</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">от 0,05 до 4 мм</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd; text-align: right;">от 210 ₽</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Экономичная марка</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>AISI 304</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">от 0,05 до 4 мм</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd; text-align: right;">от 300 ₽</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Самая популярная</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>AISI 304L</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">от 0,05 до 4 мм</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd; text-align: right;">от 270 ₽</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Низкоуглеродистая</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>AISI 316</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">от 0,05 до 4 мм</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd; text-align: right;">от 450 ₽</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Повышенная стойкость</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>AISI 316L</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">от 0,05 до 4 мм</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd; text-align: right;">от 430 ₽</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Для сварных конструкций</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>AISI 321</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">от 0,05 до 4 мм</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd; text-align: right;">от 340 ₽</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Стабилизированная титаном</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>AISI 430</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">от 0,05 до 4 мм</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd; text-align: right;">от 150 ₽</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Ферритная</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;"><strong>AISI 904L</strong></td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">от 0,05 до 4 мм</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd; text-align: right;">от 1 300 ₽</td>
                            <td style="padding: 0.75rem; border: 1px solid #ddd;">Супер-аустенитная</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>Дополнительные параметры, влияющие на цену</h3>
                <ul>
                    <li><strong>Ширина ленты</strong> — нестандартная ширина может увеличить стоимость</li>
                    <li><strong>Состояние</strong> — мягкая или нагартованная</li>
                    <li><strong>Поверхность</strong> — BA (блестящая) или 2B (матовое)</li>
                    <li><strong>Объем заказа</strong> — крупные партии получают скидку</li>
                </ul>
                
                <h3>Как узнать точную цену?</h3>
                <p>Для получения точной цены на интересующую вас марку и размеры:</p>
                <ul>
                    <li>Позвоните нам: <a href="tel:+74951234567">+7 (495) 123-45-67</a></li>
                    <li>Напишите на email: <a href="mailto:sales@example.com">sales@example.com</a></li>
                    <li>Используйте форму обратной связи на сайте</li>
                </ul>
            </div>
        ',
    ],
    
    'privacy-policy' => [
        'h1' => 'Политика конфиденциальности',
        'title' => 'Политика конфиденциальности | Каталог AISI',
        'description' => 'Политика конфиденциальности и обработки персональных данных.',
        'content' => '
            <div style="max-width: 800px; margin: 0 auto;">
                <h2>Политика конфиденциальности</h2>
                <p><strong>Дата вступления в силу:</strong> ' . date('d.m.Y') . '</p>
                
                <h3>1. Общие положения</h3>
                <p>Настоящая Политика конфиденциальности определяет порядок обработки и защиты персональных данных пользователей веб-сайта (далее — "Сайт").</p>
                <p>Используя Сайт, вы соглашаетесь с условиями настоящей Политики конфиденциальности.</p>
                
                <h3>2. Персональные данные</h3>
                <p>Под персональными данными понимается любая информация, относящаяся к прямо или косвенно определенному или определяемому физическому лицу (субъекту персональных данных).</p>
                <p>Мы можем собирать следующие персональные данные:</p>
                <ul>
                    <li>Имя и фамилия</li>
                    <li>Контактный телефон</li>
                    <li>Адрес электронной почты</li>
                    <li>Почтовый адрес</li>
                    <li>Иные данные, предоставленные пользователем</li>
                </ul>
                
                <h3>3. Цели обработки персональных данных</h3>
                <p>Мы обрабатываем персональные данные в следующих целях:</p>
                <ul>
                    <li>Обработка заказов и предоставление услуг</li>
                    <li>Связь с клиентами по вопросам заказов</li>
                    <li>Информирование о новых товарах и акциях (с согласия пользователя)</li>
                    <li>Улучшение качества обслуживания</li>
                    <li>Соблюдение требований законодательства</li>
                </ul>
                
                <h3>4. Правовые основания обработки</h3>
                <p>Обработка персональных данных осуществляется на основании:</p>
                <ul>
                    <li>Федерального закона "О персональных данных" № 152-ФЗ</li>
                    <li>Согласия субъекта персональных данных</li>
                    <li>Договора, стороной которого является субъект персональных данных</li>
                </ul>
                
                <h3>5. Сроки хранения персональных данных</h3>
                <p>Персональные данные хранятся в течение срока, необходимого для достижения целей обработки, или в течение срока, установленного законодательством.</p>
                
                <h3>6. Меры по защите персональных данных</h3>
                <p>Мы применяем необходимые технические и организационные меры для защиты персональных данных от неправомерного доступа, уничтожения, изменения, блокирования, копирования, предоставления, распространения, а также от иных неправомерных действий.</p>
                
                <h3>7. Права субъектов персональных данных</h3>
                <p>Вы имеете право:</p>
                <ul>
                    <li>Получать информацию, касающуюся обработки ваших персональных данных</li>
                    <li>Требовать уточнения, блокирования или уничтожения персональных данных</li>
                    <li>Отозвать согласие на обработку персональных данных</li>
                    <li>Обжаловать действия или бездействие оператора в уполномоченный орган</li>
                </ul>
                
                <h3>8. Передача персональных данных третьим лицам</h3>
                <p>Мы не передаем персональные данные третьим лицам, за исключением случаев:</p>
                <ul>
                    <li>Когда это необходимо для исполнения договора</li>
                    <li>Когда это требуется по законодательству</li>
                    <li>С вашего явного согласия</li>
                </ul>
                
                <h3>9. Cookies и аналогичные технологии</h3>
                <p>Сайт может использовать файлы cookie и аналогичные технологии для улучшения работы сайта и анализа посещаемости. Вы можете отключить cookies в настройках браузера.</p>
                
                <h3>10. Изменения в Политике конфиденциальности</h3>
                <p>Мы оставляем за собой право вносить изменения в настоящую Политику конфиденциальности. Актуальная версия всегда доступна на данной странице.</p>
                
                <h3>11. Контакты</h3>
                <p>По всем вопросам, связанным с обработкой персональных данных, вы можете обратиться:</p>
                <p><strong>Email:</strong> <a href="mailto:info@example.com">info@example.com</a></p>
                <p><strong>Телефон:</strong> <a href="tel:+74951234567">+7 (495) 123-45-67</a></p>
            </div>
        ',
    ],
];
