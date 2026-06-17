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
            <a href="https://yandex.com/maps/-/CPClyM7a"
               target="_blank" rel="noopener" class="btn btn--ghost office-card__map-link">Построить маршрут →</a>
        </div>
        <div class="office-card__map">
            <iframe
                        src="https://yandex.ru/map-widget/v1/?ll=43.833424%2C56.303605&z=17&pt=43.833424,56.303605,pm2rdl&l=map"
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
                            <span class="office-card__value">ул. Южнопортовая, 7А, стр. 2</span>
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
            <a href="https://yandex.com/maps/-/CPClu84z"
               target="_blank" rel="noopener" class="btn btn--ghost office-card__map-link">Построить маршрут →</a>
        </div>
        <div class="office-card__map">
            <iframe
                        src="https://yandex.ru/map-widget/v1/?ll=37.693417%2C55.708440&z=17&pt=37.693417,55.708440,pm2rdl&l=map"
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
                            <span class="office-card__value">Московское ш., 161, лит. А (Шушары)</span>
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
            <a href="https://yandex.com/maps/-/CPCluLp8"
               target="_blank" rel="noopener" class="btn btn--ghost office-card__map-link">Построить маршрут →</a>
        </div>
        <div class="office-card__map">
            <iframe
                        src="https://yandex.ru/map-widget/v1/?ll=30.425016%2C59.772968&z=17&pt=30.425016,59.772968,pm2rdl&l=map"
                width="100%" height="100%" frameborder="0" allowfullscreen loading="lazy"
                style="border:none;display:block;min-height:320px;"
                title="Офис в Санкт-Петербурге"></iframe>
        </div>
    </div>

    <div class="contacts-page__requisites">
        <h2>Реквизиты</h2>
        <ul>
            <li><strong>Получатель:</strong> Индивидуальный предприниматель Галанов Андрей Олегович (ИП Галанов А. О.)</li>
            <li><strong>ИНН:</strong> 526016545328</li>
            <li><strong>Юридический адрес:</strong> 607665, Нижегородская область, Кстовский р-н, д. Афонино, ул. Академическая, д. 2, кв./оф. 3</li>
            <li><strong>Расчётный счёт:</strong> 40802810616020000353</li>
            <li><strong>Банк:</strong> АО «АЛЬФА-БАНК»</li>
            <li><strong>БИК:</strong> 044525593</li>
            <li><strong>Корр. счёт:</strong> 30101810200000000593</li>
            <li><strong>Валюта счёта:</strong> RUR (российский рубль)</li>
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
                    <p><strong>Получатель:</strong> Индивидуальный предприниматель Галанов Андрей Олегович (ИП Галанов А. О.)</p>
                    <p><strong>ИНН:</strong> 526016545328</p>
                    <p><strong>Юридический адрес:</strong> 607665, Нижегородская область, Кстовский р-н, д. Афонино, ул. Академическая, д. 2, кв./оф. 3</p>
                    <p><strong>Расчётный счёт:</strong> 40802810616020000353</p>
                    <p><strong>Название банка:</strong> АО «АЛЬФА-БАНК»</p>
                    <p><strong>БИК:</strong> 044525593</p>
                    <p><strong>Корр. счёт:</strong> 30101810200000000593</p>
                    <p><strong>Валюта счёта:</strong> RUR (российский рубль)</p>
                    <p><em>Назначение платежа:</em> «Оплата по счёту № __ от __.__.____ г. за нержавеющую ленту AISI». Условия НДС уточняйте у менеджера при выставлении счёта.</p>
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
                    <li>Позвоните нам: <a href="tel:+78002003943">+7 (800) 200-39-43</a></li>
                    <li>Напишите на email: <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a></li>
                    <li>Используйте форму обратной связи на сайте</li>
                </ul>
            </div>
        ',
    ],
    
    'privacy-policy' => [
        'h1' => 'Политика конфиденциальности',
        'title' => 'Политика конфиденциальности | Каталог AISI',
        'description' => 'Политика обработки и защиты персональных данных пользователей сайта lenta-nerzhavejushhaja-aisi.ru.',
        'content' => '
            <div class="legal-page" style="max-width: 860px; margin: 0 auto;">
                <p><strong>Дата вступления в силу:</strong> ' . date('d.m.Y') . '</p>

                <h2>1. Общие положения</h2>
                <p>Настоящая Политика в отношении обработки персональных данных (далее — «Политика») разработана в соответствии с требованиями Федерального закона от 27.07.2006 № 152-ФЗ «О персональных данных» (далее — Закон № 152-ФЗ) и определяет порядок обработки персональных данных и меры по обеспечению безопасности персональных данных, предпринимаемые Оператором.</p>
                <p>Оператор персональных данных:</p>
                <ul>
                    <li><strong>Наименование:</strong> Индивидуальный предприниматель Галанов Андрей Олегович (ИП Галанов А. О.)</li>
                    <li><strong>ИНН:</strong> 526016545328</li>
                    <li><strong>Адрес:</strong> 607665, Нижегородская область, Кстовский р-н, д. Афонино, ул. Академическая, д. 2, кв./оф. 3</li>
                    <li><strong>E-mail:</strong> <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a></li>
                    <li><strong>Сайт:</strong> <a href="https://lenta-nerzhavejushhaja-aisi.ru/">lenta-nerzhavejushhaja-aisi.ru</a></li>
                </ul>
                <p>Оператор ставит своей важнейшей целью и условием осуществления своей деятельности соблюдение прав и свобод человека и гражданина при обработке его персональных данных, в том числе защиты прав на неприкосновенность частной жизни, личную и семейную тайну.</p>
                <p>Настоящая Политика применяется ко всей информации, которую Оператор может получить о посетителях сайта <a href="https://lenta-nerzhavejushhaja-aisi.ru/">lenta-nerzhavejushhaja-aisi.ru</a> (далее — «Сайт»).</p>

                <h2>2. Основные понятия</h2>
                <ul>
                    <li><strong>Персональные данные</strong> — любая информация, относящаяся к прямо или косвенно определённому или определяемому физическому лицу (субъекту персональных данных).</li>
                    <li><strong>Оператор</strong> — ИП Галанов Андрей Олегович (ИНН 526016545328), организующий и (или) осуществляющий обработку персональных данных.</li>
                    <li><strong>Обработка персональных данных</strong> — любое действие (операция) или совокупность действий, совершаемых с использованием средств автоматизации или без таковых с персональными данными, включая сбор, запись, систематизацию, накопление, хранение, уточнение, извлечение, использование, передачу, обезличивание, блокирование, удаление, уничтожение.</li>
                    <li><strong>Cookie</strong> — небольшой фрагмент данных, отправленный веб-сервером и хранимый на устройстве пользователя.</li>
                </ul>

                <h2>3. Какие персональные данные обрабатываются</h2>
                <p>Оператор обрабатывает следующие персональные данные, добровольно предоставленные субъектом при заполнении форм обратной связи и форм заявки на Сайте:</p>
                <ul>
                    <li>фамилия, имя, отчество (при добровольном указании);</li>
                    <li>контактный номер телефона;</li>
                    <li>адрес электронной почты;</li>
                    <li>наименование организации и должность (при оформлении заявки от юридического лица);</li>
                    <li>содержание обращения (текст сообщения, состав запрашиваемого товара).</li>
                </ul>
                <p>Также автоматически собираются обезличенные технические данные: IP-адрес, информация из cookie, сведения о браузере и операционной системе, источник перехода, страницы посещений. Эти данные используются для аналитики и улучшения работы Сайта (Яндекс.Метрика и пр.).</p>

                <h2>4. Цели обработки персональных данных</h2>
                <ul>
                    <li>обработка заявок и обращений пользователей, подготовка и направление коммерческих предложений;</li>
                    <li>заключение и исполнение договоров поставки продукции;</li>
                    <li>информирование о статусе заказа, доставке, оплате;</li>
                    <li>информирование об акциях и новинках (при наличии согласия);</li>
                    <li>повышение качества обслуживания и работы Сайта;</li>
                    <li>соблюдение требований законодательства Российской Федерации.</li>
                </ul>

                <h2>5. Правовые основания обработки</h2>
                <p>Правовыми основаниями обработки персональных данных являются:</p>
                <ul>
                    <li>Конституция Российской Федерации;</li>
                    <li>Гражданский кодекс Российской Федерации;</li>
                    <li>Федеральный закон от 27.07.2006 № 152-ФЗ «О персональных данных»;</li>
                    <li>согласие субъекта персональных данных на обработку его персональных данных, выражаемое отправкой формы или совершением иных конклюдентных действий на Сайте;</li>
                    <li>договор, стороной которого является субъект персональных данных.</li>
                </ul>

                <h2>6. Условия и сроки обработки</h2>
                <p>Обработка персональных данных осуществляется с согласия субъекта персональных данных на обработку его персональных данных (ст. 6 Закона № 152-ФЗ). Хранение персональных данных осуществляется в течение срока, необходимого для достижения целей обработки, либо до отзыва согласия, если иной срок не установлен законодательством РФ.</p>

                <h2>7. Передача персональных данных</h2>
                <p>Оператор не передаёт персональные данные третьим лицам, за исключением случаев, когда это необходимо для исполнения договора (например, передача транспортной компании для доставки), требуется законодательством РФ, или субъект дал явное согласие на такую передачу.</p>
                <p>Трансграничная передача персональных данных не осуществляется.</p>

                <h2>8. Cookies и метрические сервисы</h2>
                <p>Сайт использует файлы cookie и системы веб-аналитики (Яндекс.Метрика и др.) для подсчёта посещаемости, анализа поведения пользователей и улучшения работы Сайта. Подробнее — в <a href="' . '/cookies/' . '">Политике в отношении файлов cookie</a>. Пользователь может управлять cookie в настройках своего браузера.</p>

                <h2>9. Права субъектов персональных данных</h2>
                <p>Субъект персональных данных вправе:</p>
                <ul>
                    <li>получать сведения, касающиеся обработки его персональных данных;</li>
                    <li>требовать уточнения, блокирования или уничтожения персональных данных в случае, если они являются неполными, устаревшими, неточными или незаконно полученными;</li>
                    <li>отозвать своё согласие на обработку персональных данных, направив запрос на e-mail <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a>;</li>
                    <li>обжаловать действия или бездействие Оператора в Роскомнадзоре или в судебном порядке.</li>
                </ul>

                <h2>10. Меры по защите персональных данных</h2>
                <p>Оператор принимает необходимые правовые, организационные и технические меры для защиты персональных данных от неправомерного или случайного доступа, уничтожения, изменения, блокирования, копирования, распространения, а также от иных неправомерных действий третьих лиц, в том числе:</p>
                <ul>
                    <li>ограничение круга лиц, имеющих доступ к персональным данным;</li>
                    <li>использование защищённого соединения (HTTPS);</li>
                    <li>хранение данных в защищённых базах данных.</li>
                </ul>

                <h2>11. Изменения политики</h2>
                <p>Оператор имеет право вносить изменения в настоящую Политику. Новая редакция вступает в силу с момента её размещения на Сайте, если иное не предусмотрено новой редакцией Политики.</p>

                <h2>12. Контакты</h2>
                <p>По всем вопросам, связанным с обработкой персональных данных, обращайтесь:</p>
                <p><strong>E-mail:</strong> <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a><br>
                <strong>Телефон:</strong> <a href="tel:+78002003943">+7 (800) 200-39-43</a><br>
                <strong>Почтовый адрес:</strong> 607665, Нижегородская область, Кстовский р-н, д. Афонино, ул. Академическая, д. 2, кв./оф. 3.</p>
            </div>
        ',
    ],

    'cookies' => [
        'h1' => 'Политика в отношении файлов cookie',
        'title' => 'Политика в отношении cookies | Каталог AISI',
        'description' => 'Какие файлы cookie используются на сайте lenta-nerzhavejushhaja-aisi.ru, для чего и как ими управлять.',
        'content' => '
            <div class="legal-page" style="max-width: 860px; margin: 0 auto;">
                <p><strong>Дата обновления:</strong> ' . date('d.m.Y') . '</p>

                <h2>1. Что такое файлы cookie</h2>
                <p>Файлы cookie — небольшие текстовые файлы, которые сайт сохраняет на устройстве пользователя (компьютер, смартфон, планшет) при посещении страниц. Они позволяют сайту «запоминать» действия и предпочтения пользователя (выбранный регион, состояние согласий и т. п.).</p>

                <h2>2. Какие cookie использует Сайт</h2>
                <p>На сайте <a href="https://lenta-nerzhavejushhaja-aisi.ru/">lenta-nerzhavejushhaja-aisi.ru</a> используются следующие категории файлов cookie:</p>
                <ul>
                    <li><strong>Технические (строго необходимые)</strong> — обеспечивают работу основных функций Сайта: PHP-сессия, выбор региона (Нижний Новгород / Москва / Санкт-Петербург), фиксация согласия на использование cookie. Эти файлы не могут быть отключены через настройки на Сайте.</li>
                    <li><strong>Аналитические</strong> — используются для сбора обезличенной статистики посещаемости и поведения пользователей (Яндекс.Метрика). Это помогает улучшать структуру и контент Сайта.</li>
                    <li><strong>Функциональные</strong> — запоминают пользовательские настройки (например, последний выбранный регион).</li>
                </ul>

                <h2>3. Как мы используем cookie</h2>
                <ul>
                    <li>обеспечение корректной работы Сайта и его функций;</li>
                    <li>сохранение пользовательских настроек;</li>
                    <li>аналитика посещаемости и поведения пользователей;</li>
                    <li>повышение удобства работы с Сайтом.</li>
                </ul>

                <h2>4. Сторонние сервисы</h2>
                <p>На Сайте могут устанавливаться cookie сторонних сервисов:</p>
                <ul>
                    <li><strong>Яндекс.Метрика</strong> — для веб-аналитики (политика: <a href="https://yandex.ru/legal/confidential/" target="_blank" rel="noopener">yandex.ru/legal/confidential</a>);</li>
                    <li><strong>Яндекс.Карты</strong> — для отображения карт на странице «Контакты».</li>
                </ul>

                <h2>5. Управление файлами cookie</h2>
                <p>Пользователь может в любой момент удалить уже сохранённые cookie и запретить их сохранение через настройки браузера:</p>
                <ul>
                    <li>Google Chrome: Настройки → Конфиденциальность и безопасность → Файлы cookie;</li>
                    <li>Mozilla Firefox: Настройки → Приватность и защита;</li>
                    <li>Safari: Настройки → Конфиденциальность;</li>
                    <li>Яндекс.Браузер: Настройки → Сайты → Расширенные настройки.</li>
                </ul>
                <p>Обращаем внимание: отключение cookie может привести к некорректной работе отдельных функций Сайта.</p>

                <h2>6. Согласие на использование cookie</h2>
                <p>При первом посещении Сайта пользователю отображается уведомление об использовании cookie. Продолжая пользоваться Сайтом, пользователь подтверждает своё согласие на обработку cookie в соответствии с настоящей Политикой и <a href="' . '/privacy-policy/' . '">Политикой конфиденциальности</a>.</p>

                <h2>7. Контакты</h2>
                <p>Оператор: ИП Галанов Андрей Олегович, ИНН 526016545328.<br>
                По вопросам, связанным с обработкой cookie: <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a>.</p>
            </div>
        ',
    ],

    'consent' => [
        'h1' => 'Согласие на обработку персональных данных',
        'title' => 'Согласие на обработку персональных данных | Каталог AISI',
        'description' => 'Текст согласия на обработку персональных данных, которое пользователь даёт при отправке форм на сайте.',
        'content' => '
            <div class="legal-page" style="max-width: 860px; margin: 0 auto;">
                <p><strong>Дата обновления:</strong> ' . date('d.m.Y') . '</p>

                <p>Заполняя и отправляя любую форму обратной связи на сайте <a href="https://lenta-nerzhavejushhaja-aisi.ru/">lenta-nerzhavejushhaja-aisi.ru</a> (далее — «Сайт»), а равно проставляя соответствующую отметку или нажимая кнопку отправки, пользователь (далее — «Субъект») в соответствии с Федеральным законом от 27.07.2006 № 152-ФЗ «О персональных данных» свободно, своей волей и в своих интересах даёт согласие на обработку своих персональных данных следующему Оператору:</p>

                <ul>
                    <li><strong>Оператор:</strong> Индивидуальный предприниматель Галанов Андрей Олегович (ИП Галанов А. О.);</li>
                    <li><strong>ИНН:</strong> 526016545328;</li>
                    <li><strong>Адрес:</strong> 607665, Нижегородская область, Кстовский р-н, д. Афонино, ул. Академическая, д. 2, кв./оф. 3;</li>
                    <li><strong>E-mail:</strong> <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a>.</li>
                </ul>

                <h2>1. Состав персональных данных</h2>
                <ul>
                    <li>фамилия, имя, отчество;</li>
                    <li>контактный телефон;</li>
                    <li>адрес электронной почты;</li>
                    <li>наименование организации и должность (при заявках от юридических лиц);</li>
                    <li>содержание обращения, состав запрашиваемых товаров и услуг;</li>
                    <li>обезличенные технические данные (IP-адрес, cookie, информация о браузере и устройстве).</li>
                </ul>

                <h2>2. Перечень действий с персональными данными</h2>
                <p>Сбор, запись, систематизация, накопление, хранение, уточнение (обновление, изменение), извлечение, использование, передача (предоставление), обезличивание, блокирование, удаление и уничтожение персональных данных, осуществляемые с использованием средств автоматизации и без таковых.</p>

                <h2>3. Цели обработки</h2>
                <ul>
                    <li>идентификация Субъекта и обработка его обращения;</li>
                    <li>заключение и исполнение договоров поставки;</li>
                    <li>выставление счетов, согласование условий доставки и оплаты;</li>
                    <li>направление информационных и рекламных материалов о товарах и услугах Оператора (при отдельном согласии);</li>
                    <li>исполнение требований законодательства РФ.</li>
                </ul>

                <h2>4. Срок действия согласия</h2>
                <p>Настоящее согласие действует с момента его предоставления и до момента его отзыва Субъектом. Согласие может быть отозвано в любой момент путём направления письменного заявления на e-mail Оператора <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a> или по почтовому адресу Оператора.</p>

                <h2>5. Передача третьим лицам</h2>
                <p>Субъект уведомлён, что персональные данные могут быть переданы транспортным компаниям, кредитным организациям и иным контрагентам Оператора в объёме, необходимом для исполнения договора поставки.</p>

                <h2>6. Подтверждение</h2>
                <p>Субъект подтверждает, что ознакомлен с <a href="' . '/privacy-policy/' . '">Политикой конфиденциальности</a> и <a href="' . '/cookies/' . '">Политикой в отношении файлов cookie</a> Оператора и согласен с их условиями.</p>
            </div>
        ',
    ],

    'terms' => [
        'h1' => 'Пользовательское соглашение',
        'title' => 'Пользовательское соглашение | Каталог AISI',
        'description' => 'Условия использования сайта lenta-nerzhavejushhaja-aisi.ru: правила, ответственность сторон, интеллектуальная собственность.',
        'content' => '
            <div class="legal-page" style="max-width: 860px; margin: 0 auto;">
                <p><strong>Дата обновления:</strong> ' . date('d.m.Y') . '</p>

                <h2>1. Общие положения</h2>
                <p>Настоящее Пользовательское соглашение (далее — «Соглашение») регулирует отношения между владельцем сайта <a href="https://lenta-nerzhavejushhaja-aisi.ru/">lenta-nerzhavejushhaja-aisi.ru</a> — ИП Галанов Андрей Олегович (ИНН 526016545328), далее — «Администрация», — и любым лицом (далее — «Пользователь»), использующим Сайт.</p>
                <p>Использование Сайта означает безоговорочное принятие Пользователем условий настоящего Соглашения, <a href="' . '/privacy-policy/' . '">Политики конфиденциальности</a> и <a href="' . '/cookies/' . '">Политики в отношении файлов cookie</a>.</p>

                <h2>2. Предмет</h2>
                <p>Сайт представляет собой информационный каталог нержавеющей ленты марок AISI. Информация о товарах, ценах и наличии носит справочный характер и не является публичной офертой. Условия конкретной поставки согласуются индивидуально и оформляются договором или счётом.</p>

                <h2>3. Регистрация и формы обратной связи</h2>
                <p>Заполняя формы обратной связи или формы заявки, Пользователь подтверждает, что предоставленные сведения являются достоверными, и даёт согласие на обработку персональных данных в соответствии с <a href="' . '/consent/' . '">Согласием на обработку персональных данных</a>.</p>

                <h2>4. Интеллектуальная собственность</h2>
                <p>Все материалы Сайта (тексты, изображения, графика, программный код, логотипы) являются объектами интеллектуальной собственности Администрации или используются ею на законных основаниях. Любое использование материалов Сайта без письменного разрешения Администрации запрещено.</p>

                <h2>5. Ответственность сторон</h2>
                <p>Администрация прикладывает разумные усилия для поддержания актуальности информации на Сайте, однако не гарантирует отсутствие технических ошибок и неточностей. Администрация не несёт ответственности за временную недоступность Сайта, действия третьих лиц, а также за решения и убытки Пользователя, принятые на основании информации с Сайта без её предварительного письменного подтверждения.</p>

                <h2>6. Применимое право</h2>
                <p>К отношениям сторон применяется законодательство Российской Федерации. Споры разрешаются в претензионном порядке, а при недостижении согласия — в суде по месту нахождения Администрации.</p>

                <h2>7. Изменения соглашения</h2>
                <p>Администрация вправе в одностороннем порядке вносить изменения в настоящее Соглашение. Новая редакция вступает в силу с момента её публикации на Сайте.</p>

                <h2>8. Контакты</h2>
                <p>ИП Галанов Андрей Олегович, ИНН 526016545328.<br>
                607665, Нижегородская область, Кстовский р-н, д. Афонино, ул. Академическая, д. 2, кв./оф. 3.<br>
                E-mail: <a href="mailto:ev18011@yandex.ru">ev18011@yandex.ru</a>; тел.: <a href="tel:+78002003943">+7 (800) 200-39-43</a>.</p>
            </div>
        ',
    ],
];
