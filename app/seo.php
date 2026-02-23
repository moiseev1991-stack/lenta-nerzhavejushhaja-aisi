<?php
/**
 * Генерация уникальных meta title и description для SEO.
 * Поддержка override из БД (meta_title, meta_description).
 */

if (!function_exists('seo_condition_label')) {
    function seo_condition_label($condition) {
        $map = [
            'soft' => 'мягкая',
            'hard' => 'нагартованная',
            'semi_hard' => 'полугартованная',
            'extra_hard' => 'высоконагартованная',
        ];
        return isset($map[$condition]) ? $map[$condition] : trim((string) $condition);
    }
}

/**
 * H1 = product.name (как в product-card__name). Fallback — собираем по параметрам.
 * Title: Купить {product.name} — цена по запросу | Лента AISI
 * Description: {product.name}. Подберём марку и размеры, ответим за 15 минут. Доставка по РФ. Цена по запросу.
 */
function buildMetaForProduct(array $product, array $category): array {
    $overrideTitle = trim((string) ($product['meta_title'] ?? ''));
    $overrideDesc = trim((string) ($product['meta_description'] ?? ''));
    $productName = trim((string) ($product['name'] ?? ''));

    $aisi = seo_grade_part($category['name'] ?? 'AISI');
    $t = isset($product['thickness']) && $product['thickness'] !== null && $product['thickness'] !== '' ? (float) $product['thickness'] : null;
    $w = !empty($product['width']) && is_numeric($product['width']) ? (float) $product['width'] : null;
    $sizeParts = [];
    if ($t !== null) {
        $sizeParts[] = str_replace('.', ',', $t == (int) $t ? (string) (int) $t : (string) $t);
    }
    if ($w !== null) {

        $sizeParts[] = str_replace('.', ',', $w == (int) $w ? (string) (int) $w : (string) $w);
    }
    $sizePart = empty($sizeParts) ? '' : implode('x', $sizeParts) . ' мм';
    // H1: products.name (как на карточке) или собираем по шаблону
    $condition = trim((string) ($product['condition'] ?? ''));
    $condLabel = $condition !== '' ? seo_condition_label($condition) : '';
    $surface = trim((string) ($product['surface'] ?? ''));

    $h1Parts = ['Лента нержавеющая'];
    if ($sizePart !== '') $h1Parts[] = $sizePart;
    $h1Parts[] = $aisi;
    if ($condLabel !== '') $h1Parts[] = $condLabel;
    if ($surface !== '') $h1Parts[] = $surface;
    $nameFallback = trim(implode(' ', array_filter($h1Parts, function ($x) { return $x !== ''; })));
    $name = $productName !== '' ? $productName : $nameFallback;

    $h1 = $name;

    $title = $overrideTitle !== '' ? $overrideTitle : ('Купить ' . $name . ' — цена по запросу | Лента AISI');
    $description = $overrideDesc !== '' ? $overrideDesc : ($name . '. Подберём марку и размеры, ответим за 15 минут. Доставка по РФ. Цена по запросу.');

    return ['title' => $title, 'description' => $description, 'h1' => $h1];
}

/**
 * Категория: Лента нержавеющая {AISI} — каталог, цена по запросу
 * При page>1: Лента нержавеющая {AISI} — каталог (страница {page})
 * Description: Лента нержавеющая {AISI}: каталог товаров ({count} позиций). Подбор толщины и ширины...
 */
function buildMetaForCategory(array $category, int $totalCount, int $page = 1): array {
    $overrideTitle = trim((string) ($category['meta_title'] ?? ''));
    $overrideDesc = trim((string) ($category['meta_description'] ?? ''));
    $aisi = seo_grade_part($category['name'] ?? 'AISI');

    // Title: при page>1 всегда уникальный шаблон с номером страницы
    if ($page > 1) {
        $title = 'Лента нержавеющая ' . $aisi . ' — каталог (страница ' . $page . ')';
    } elseif ($overrideTitle !== '') {
        $title = $overrideTitle;
    } else {
        $title = 'Лента нержавеющая ' . $aisi . ' — каталог, цена по запросу';
    }

    $countWord = $totalCount === 1 ? 'позиция' : ($totalCount >= 2 && $totalCount <= 4 ? 'позиции' : 'позиций');
    $description = 'Лента нержавеющая ' . $aisi . ': каталог товаров (' . $totalCount . ' ' . $countWord . '). Подбор толщины и ширины, консультация, доставка и отгрузка по РФ. Цена по запросу.';
    if ($overrideDesc !== '') {
        $description = $overrideDesc;
    }
    if ($page > 1) {
        $description .= ' (страница ' . $page . ')';
    }

    return ['title' => $title, 'description' => $description];
}

/**
 * Статические страницы: контакты, доставка, оплата, about, price, privacy-policy.
 * Принимает slug/route, возвращает title и description из app/data/pages.php.
 */
function buildMetaForStatic(string $route): array {
    $route = trim($route, '/');
    $pages = require __DIR__ . '/data/pages.php';
    if (isset($pages[$route])) {
        $p = $pages[$route];
        return [
            'title' => $p['title'] ?? 'Каталог AISI',
            'description' => $p['description'] ?? '',
        ];
    }
    return ['title' => 'Каталог AISI', 'description' => ''];
}
