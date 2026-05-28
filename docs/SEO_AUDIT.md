# SEO-аудит: lenta-nerzhavejushhaja-aisi.ru

**Дата:** 2026-05-28
**Метод:** проверка живого домена + ревью исходников.
**Парный документ:** [SEO_PLAN.md](SEO_PLAN.md) — полная стратегия.

Сводный вердикт: **техническая база на 7/10**. JSON-LD, canonical, robots, sitemap, OG, x3 регионы, FAQ — всё уже выкатано. Проблемы — в деталях, которые ломают валидацию схемы или режут индексацию. Ниже — по убыванию приоритета.

---

## P0 — Критические (правлено этой итерацией)

### 1. JSON-LD Organization ссылается на `https://example.com` и имеет `name: "Компания"`

**Где:** `app/config.php`, ключ `company`:
```php
'company' => [
    'name' => 'Компания',
    'url' => 'https://example.com',
    'phone' => '+7 (800) 200-39-43',
],
```

**Что видит Google:** в production-HTML главной страницы:
```json
"name": "Компания",
"url": "https://example.com",
"sameAs": []
```

**Чем плохо:**
- `https://example.com` — невалидный URL, Schema Markup Validator выдаст warning;
- `Компания` как имя организации — это placeholder, который воспринимается как low-quality сигнал E-E-A-T;
- `Organization` JSON-LD теряет связь с брендом → Google не может построить Knowledge Panel.

**Правка:** обновлены поля `company.name` → `Каталог AISI`, `company.url` → `https://lenta-nerzhavejushhaja-aisi.ru`. Также добавлен `company.email`.

---

### 2. Фейковый `aggregateRating` и `review` на каждом товаре

**Где:** `app/views/layout.php`, блок генерации Product JSON-LD (строки 151–161).

**Что видит Google:**
```json
"aggregateRating": {"ratingValue": "5", "reviewCount": "1"},
"review": [{
    "reviewRating": {"ratingValue": "5"},
    "author": {"name": "Покупатель"},
    "reviewBody": "Качественная нержавеющая лента, соответствует характеристикам."
}]
```

**Чем плохо:**
- Один и тот же фейковый отзыв на 800+ карточек товаров — Google Reviews Spam Update 2023 ловит это автоматически.
- Может прилететь ручная санкция за `Misleading structured data`.
- Звёзды в сниппете быстро покажутся, но потом исчезнут с пенальти.

**Правка:** блоки `aggregateRating` и `review` удалены из шаблона. Когда соберём настоящие отзывы (через Яндекс.Бизнес или amoCRM-собиралку отзывов после сделки) — вернём, с реальным `reviewCount`.

---

### 3. `additionalProperty` «Состояние» имеет машинное значение `hard`

**Где:** `app/views/layout.php`, цикл `additionalProps` строки 138–144.

**Что видит Google:**
```json
{"name": "Состояние", "value": "hard"}
```

**Чем плохо:**
- Несоответствие пользовательскому языку (на карточке покупатель видит «нагартованная», в схеме — «hard»).
- Reviewer-маркапа Google Search Console это видит как warning.

**Правка:** в шаблоне теперь применяется маппинг `condition_label` (использует `seo_condition_label()` из `app/seo.php`). Аналогично для `spring` — выводится `Да`/`Нет` (уже было правильно).

---

### 4. `robots.txt` блокирует `?page=N` — пагинация не индексируется

**Где:** `app/views/robots.txt.php`:
```
Disallow: /*?*
```

**Что происходит:** на категории AISI 304 — 184 товара (~8 страниц). Pages 2–8 не индексируются → Google знает только первые 24 товара. Это режет глубину каталога **в 7 раз**.

При этом сам сайт корректно ставит `rel="canonical"` на самостоятельные URL `?page=2`, `rel="next"`, `rel="prev"` — но всё это бесполезно, пока URL заблокирован.

**Правка:** в robots.txt добавлен `Allow: /*?page=` перед `Disallow: /*?*`. Это исключение для пагинации, всё остальное (фильтры через GET) остаётся заблокированным. Также добавил Clean-param на `?th=&cond=&surf=&spring=&sort=` для Яндекса, чтобы фильтры не множили дубли.

**Важно:** Yandex применяет правила сверху вниз, у Yandex `Allow` имеет приоритет если он специфичнее. Для Google порядок не важен — побеждает более длинное (специфичное) правило, то есть `/*?page=` побеждает `/*?*`.

---

## P1 — Высокий приоритет (правлено этой итерацией)

### 5. Нет `og:site_name` и неполный Twitter Card

**Где:** `app/views/layout.php`, блок OG/Twitter (~430).

**Что в HTML:**
```html
<meta name="twitter:description" content="...">
<!-- нет twitter:card, twitter:title, twitter:image, og:site_name -->
```

**Чем плохо:**
- Без `twitter:card` Twitter/X не превращает превью в карточку.
- Без `og:site_name` ссылка в соцсетях выглядит обезличенно.
- VK, Telegram и WhatsApp используют OG-теги для превью — лучше дать.

**Правка:**
- Добавлен `og:site_name` со значением `site_name` из config.
- Добавлены `twitter:card` (summary_large_image, если есть OG-картинка, иначе summary), `twitter:title`, `twitter:image`.

---

### 6. Нет `og:image` на главной, категориях и страницах серий

**Где:** там же.

**Чем плохо:** при шеринге ссылок в Telegram/VK превью без картинки → меньше CTR.

**Правка:** для всех страниц, кроме товара без `image`, теперь подставляется лого `img/logo_aisi_lenta_full.png` как фолбэк.

---

### 7. Series page — кривая хлебная крошка «Серии AISI» → ведёт на главную

**Где:** `app/views/layout.php`, breadcrumbItems для `isSeriesPage`:
```json
{"position": 2, "name": "Серии AISI", "item": "https://lenta-nerzhavejushhaja-aisi.ru"}
```

Item position 2 ссылается на главную (не на отдельный URL «серии») — это технически некорректная крошка.

**Правка:** убрал промежуточный уровень «Серии AISI», оставил только `Главная → Серия 300`. Так чище для пользователя и для Google.

---

### 8. Контакты-страница: Organization JSON-LD дублирует данные с главной, но с тем же placeholder

**Где:** `app/views/layout.php`, блок `isServicePage && contacts`.

Дублирование Organization-схемы между главной и контактами — нормально (это даёт сильный сигнал, что схема описывает одну сущность через `@id`). Но опять же — `name: Компания` и `url: https://example.com`. **Правка через fix config.php.**

---

## P2 — Средний приоритет (рекомендации, не правлено в этой итерации)

### 9. Product `description` в JSON-LD — слабая, часто шаблонная

**Текущее значение для продукта 0.15×10 мм AISI 201:**
> Купить лента нержавеющая 0.15x10 мм aisi 201 нагартованная ba по выгодной цене.

Это похоже на короткий placeholder из БД (поле `products.description`), а не на автогенерацию. Функция `generate_product_description_auto()` собирает богатый текст из 7 блоков (марка, параметры, поверхность, состояние, применения, ГОСТ, цена), но не вызывается потому, что поле в БД заполнено коротышом.

**Что делать (вне scope этой итерации):**
1. Обнулить через админку или SQL поле `description` у товаров, где оно содержит мусор: `UPDATE products SET description = '' WHERE description LIKE 'Купить лента нержавеющая%';`
2. После этого автогенератор автоматически даст хороший текст.

### 10. Title карточки товара заканчивается на `| Компания`

**Сейчас:** `... купить в Москве и РФ — от 315 ₽/кг | Компания`

**Чем плохо:** placeholder в visible title — пользователь видит «Компания» вместо названия бренда.

**Правка:** через fix `config.company.name` → `Каталог AISI` теперь будет: `... | Каталог AISI`. Лучше, но не идеально для бренд-рекалла.

Дополнительная рекомендация: придумать короткое название (например `ЛентаAISI` или просто использовать домен) — это вне scope данной итерации.

### 11. Title категории заканчивается на полное доменное имя

**Сейчас:** `... | lenta-nerzhavejushhaja-aisi.ru`

Длинный домен в Title мало того что некрасиво, ещё и съедает символы. Заменить на короткий брендинг.

**Не правлено в этой итерации** — требует продуктового решения. Рекомендую заменить в `helpers.php::seo_category_title()` строку 564–565 на короткий бренд.

### 12. Sitemap не содержит paginated category pages

Если пагинация теперь индексируется (см. fix #4), желательно добавить `/aisi-304/?page=2`, `?page=3` … в `sitemap.xml.php`.

**Не правлено в этой итерации** — требует обращения к БД для подсчёта количества товаров на категорию, чтобы знать, сколько страниц генерить. Дополнительная итерация.

### 13. Нет image sitemap

Для Яндекс.Картинок и Google Images. У нас есть `<image:image>` namespace для XML sitemap.

**Не правлено в этой итерации.**

### 14. Сайт не показывает русские ГОСТ-аналоги в видимом названии товара / H1

**Сейчас H1 товара:** `Лента нержавеющая 0,15×10 мм AISI 201 нагартованная BA`

**У конкурентов:** `Лента нержавеющая 0,15x10 мм AISI 201 (12Х15Г9НД) нагартованная ГОСТ 4986-79`

**Чем плохо:** запрос `лента 12Х15Г9НД` хорошо ищется, но мы не ранжируемся, потому что русский ГОСТ-аналог не упоминается в H1/Title/тексте товара.

**Что делать (вне scope этой итерации):**
- В `seo_product_h1()` и `seo_product_title()` подмешать `gradeData['gost']` (например `(12Х15Г9НД)`) после `AISI X` — данные уже есть в `app/data/grades_data.php`.
- Аналогично — упомянуть в первом предложении автоописания товара.

Решение требует осторожного дизайна, чтобы не сделать Title >75 символов.

### 15. PageSpeed Insights не замерен в этой итерации

`pagespeed.web.dev` не открылся через WebFetch (TLS). Рекомендую вручную замерить:
```
https://pagespeed.web.dev/analysis?url=https%3A%2F%2Flenta-nerzhavejushhaja-aisi.ru%2F&form_factor=mobile
```

Ориентир по размерам HTML (на наблюдении):
- `/` — 103 KB
- `/aisi-304/` — 247 KB ← тяжеловато для категории
- `/aisi-201/{product}/` — 93 KB

Категория >200 KB HTML — много inline-разметки и JSON-LD. Если LCP проседает, в первую очередь смотреть на изображения товаров (lazy-loading) и CSS критического пути.

---

## P3 — Низкий приоритет / Polish

### 16. Нет `theme-color` meta

Для мобильного Chrome/Edge — цвет адресной строки. Низкий impact.

### 17. Lighthouse Accessibility / Best Practices не проверены

Внешним инструментом нужно прогнать — `axe DevTools` или Lighthouse в Chrome. Низкий приоритет по сравнению с trade-задачами выше.

### 18. ИНН/ОГРН/КПП в footer — проверить

Беглым осмотром в footer не нашёл. Для коммерческих факторов Yandex это +.

### 19. JSON-LD WebSite SearchAction указывает на `/search/?q=...`

В коде есть `SearchAction` на главной, ссылающаяся на `/search/?q={search_term_string}`. Проверить: этот URL реально работает? Если нет — Google пометит как unusable.

```bash
curl -I "https://lenta-nerzhavejushhaja-aisi.ru/search/?q=304"
```

Нужно проверить отдельно. Если 404 — либо реализовать поиск, либо убрать `SearchAction`.

---

## Сводная таблица правок этой итерации

| # | Файл | Что | Приоритет |
|---|---|---|---|
| 1 | `app/config.php` | company.name + company.url из placeholder в реальные | P0 |
| 2 | `app/views/layout.php` | Удалить fake aggregateRating + review из Product JSON-LD | P0 |
| 3 | `app/views/layout.php` | additionalProperty: маппинг condition → русская label | P0 |
| 4 | `app/views/robots.txt.php` | Разрешить `?page=` для индексации пагинации | P0 |
| 5 | `app/views/layout.php` | Добавить og:site_name, twitter:card/title/image | P1 |
| 6 | `app/views/layout.php` | og:image fallback (лого) для главной/категорий/серий | P1 |
| 7 | `app/views/layout.php` | Series breadcrumbs — убрать левую крошку «Серии AISI» | P1 |

---

## Что нужно вручную (вне правок этой итерации)

1. **Очистить placeholder в `products.description`** в БД (см. #9).
2. **Подменить домен на короткий бренд** в Title (см. #10–11).
3. **Добавить ГОСТ-аналог в H1/Title товара** (см. #14) — требует продуктового решения.
4. **Замерить PageSpeed Insights** вручную (см. #15).
5. **Зарегистрировать Яндекс.Бизнес × 3 региона** и собрать первые отзывы (см. SEO_PLAN.md, месяц 1).
6. **Проверить `/search/`** (см. #19).

---

## Что проверить после деплоя

Через 24–48 часов после push в `main` (для SFTP-катапульты):

1. `https://lenta-nerzhavejushhaja-aisi.ru/robots.txt` — содержит `Allow: /*?page=`.
2. `https://lenta-nerzhavejushhaja-aisi.ru/` view-source — JSON-LD Organization имеет `name: "Каталог AISI"` и валидный `url`.
3. Любая карточка товара view-source — нет `aggregateRating`, нет `review`, `additionalProperty Состояние` имеет русское значение.
4. View-source категории — есть `og:site_name`, `twitter:card`, `og:image` с логотипом.
5. View-source страницы серии — в BreadcrumbList только `Главная → Серия 300` (без «Серии AISI»).
6. Schema Markup Validator (`validator.schema.org`) на главной и `/aisi-304/` — без ошибок.
7. Через 1–2 недели — Search Console: рост `Pages with structured data`, отсутствие `Manipulative reviews` warnings.
