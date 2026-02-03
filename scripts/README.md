# Скрипты

## Импорт товаров (без Composer — из CSV)

1. В Excel: **Файл → Сохранить как** → тип **«CSV (разделители — запятые)»** или **«CSV UTF-8»**. Сохраните как:
   `storage/imports/products.csv`

2. Запуск:
   ```bash
   php scripts/import_products_from_csv.php
   ```

Composer не нужен. Первая строка CSV — заголовки (название, aisi, цена, толщина, ширина, состояние, поверхность). Картинки подтягиваются из `img/product_images_named/` по совпадению имени файла и slug. Импорт идемпотентный.

---

## Импорт из Excel (XLSX) — нужен Composer

Если установлен Composer:

```bash
composer install
php scripts/import_products_from_xlsx.php
```

Файл: `storage/imports/products.xlsx`. Логика та же: заголовки в первой строке, картинки из `img/product_images_named/`.
