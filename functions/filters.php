<?php

use FS\FS_Config;

// фильтр преобразует необработанную цену в формат денег
add_filter('fs_price_format', 'fs_price_format', 10, 2);
function fs_price_format($price, $delimiter = '')
{
    $cents = fs_option('price_cents') == 1 ? 2 : 0;
    $delimiter = !empty($delimiter) ? $delimiter : fs_option('currency_delimiter', '.');
    $price = number_format(floatval($price), $cents, $delimiter, ' ');

    return $price;
}

/**
 * Вычитывает скидку из общей суммы заказа.
 *
 * @param $price - цена без скидки
 *
 * @return float
 */
function fs_discount_filter__callback($price)
{
    $price = $price - fs_get_total_discount();

    return (float) $price;
}

add_filter('fs_discount_filter', 'fs_discount_filter__callback', 10, 1);

// создаем новую колонку
add_filter('manage_edit-product_columns', 'add_views_column', 4);
function add_views_column($columns)
{
    $num = 1; // после какой по счету колонки вставлять новые
    $new_columns = [
        'fs_menu_order' => __('Sort', 'f-shop'),
        'title' => __('Title', 'f-shop'),
        'fs_id' => __('ID', 'f-shop'),
        'fs_price' => __('Price', 'f-shop'),
        'fs_vendor_code' => __('Vendor code', 'f-shop'),
        'fs_stock' => __('Stock in stock', 'f-shop'),
        'fs_photo' => __('Photo', 'f-shop'),
        //        'fs-product-cat' => __('Product categories', 'f-shop')
    ];

    if (!fs_option('fs_product_sort_on')) {
        unset($new_columns['fs_menu_order']);
    }

    return array_slice($columns, 0, $num) + $new_columns + array_slice($columns, $num);
}

// заполняем колонку данными
add_filter('manage_product_posts_custom_column', 'fill_views_column', 5, 2);
function fill_views_column($colname, $post_id)
{
    $config = new FS_Config();
    switch ($colname) {
        case 'fs-product-cat':
            echo '<div class="fs-product-cat-wrap">';
            echo get_the_term_list($post_id, FS_Config::get_data('product_taxonomy'), '', ', ', '');
            echo '</div>';
            break;
        case 'fs_menu_order':
            echo '<img src="'.esc_url(FS_PLUGIN_URL.'assets/img/sort. svg').'" width="40" title="Потяните вверх или вниз, чтобы изменить позицию">';
            break;
        case 'fs_id':
            echo esc_html($post_id);
            break;
        case 'fs_price':
            echo '<span class="fs-price-blank">';
            fs_the_price($post_id);
            fs_base_price($post_id, '<br><del>%s %s,</del>');
            break;
        case 'fs_vendor_code':
            fs_product_code($post_id);
            break;
        case 'fs_stock':
            $stock_real = get_post_meta($post_id, $config->meta['remaining_amount'], 1);
            if (fs_aviable_product($post_id)) {
                $stock = __('in stock', 'f-shop');
            } else {
                $stock = __('not available', 'f-shop');
            }
            echo esc_html($stock);
            echo '<span class="fs_stock_real" style="display:none">'.esc_html($stock_real).'</span>';
            break;
        case 'fs_photo':
            fs_product_thumbnail();
            break;
    }
}

// создаем новую колонку
add_filter('manage_edit-orders_columns', 'fs_edit_orders_columns', 4);
function fs_edit_orders_columns($columns)
{
    $num = 2; // после какой по счету колонки вставлять новые
    $new_columns = [
        //		'fs_order_id'     => __( 'ID', 'f-shop'),
        'fs_order_amount' => __('Total cost', 'f-shop'),
        'fs_order_status' => __('Status', 'f-shop'),
        'fs_user' => __('Customer', 'f-shop'),
    ];

    return array_slice($columns, 0, $num) + $new_columns + array_slice($columns, $num);
}

// добавляем возможность сортировать колонку
add_filter('manage_edit-product_sortable_columns', 'add_views_sortable_column');
function add_views_sortable_column($sortable_columns)
{
    $sortable_columns['fs_price'] = 'fs_price';
    $sortable_columns['fs_id'] = 'ID';

    return $sortable_columns;
}

// добавляем возможность сортировать колонки заказов
add_filter('manage_edit-orders_sortable_columns', 'add_orders_sortable_columns');
function add_orders_sortable_columns($sortable_columns)
{
    $sortable_columns['fs_order_amount'] = 'fs_order_amount';
    $sortable_columns['fs_order_status'] = 'post_status';

    return $sortable_columns;
}

/**
 *  возвращает тег формы и скрытые поля необходимые для безопасности.
 *
 * @param array $args
 *
 * @return mixed|string|void
 *
 * @internal param array $attr массив атрибутов тега form
 */
function fs_form_header($args = [], $ajax_action)
{
    $attr = fs_parse_attr($args, [
        'method' => 'POST',
        'autocomplete' => 'off',
        'class' => 'fs-form',
    ], ['echo']);
    $form_header = '<form '.$attr.'>';
    $form_header .= FS_Config::nonce_field();
    $form_header .= '<input type="hidden" name="action" value="'.$ajax_action.'">';
    if (!empty($args['title'])) {
        $form_header .= '<div class="fs-form-title">'.$args['title'].'</div>';
    }
    $form_header .= '<p class="fs-form-info"></p>';

    return $form_header;
}

/*
 * фильтр возвращает низ формы
 * @return string
 */
add_filter('fs_form_bottom', 'fs_form_bottom', 10, 1);
function fs_form_bottom($form_bottom = '')
{
    $form_bottom = $form_bottom.'</form>';

    return $form_bottom;
}

/**
 *  фильтр для создания шаблона письма пользователю.
 *
 * @param $vars переменные письма
 *
 * @return mixed|string|void
 */
function fs_email_template($vars)
{
    $template = 'oxygen';

    $search = fs_mail_keys($vars);
    $replace = array_values($vars);
    $replace = array_map('esc_attr', $replace);

    $html = fs_frontend_template('mail/themes/'.$template, ['vars' => $vars]);
    $html = str_replace($search, $replace, $html);

    return $html;
}

add_filter('fs_email_template', 'fs_email_template', 10, 2);

add_filter('wp_dropdown_cats', 'fs_dropdown_cats_multiple', 10, 2);
function fs_dropdown_cats_multiple($output, $r)
{
    if (isset($r['multiple']) && $r['multiple']) {
        $output = preg_replace('/^<select/i', '<select multiple size="30"', $output);

        $output = str_replace("name='{$r['name']}'", "name='{$r['name']}[]'", $output);

        $selected = is_array($r['selected'])
            ? $r['selected']
            : explode(',', $r['selected']);

        foreach (array_map('trim', $selected) as $value) {
            $output = str_replace("value=\"{$value}\"", "value=\"{$value}\" selected", $output);
        }
    }

    return $output;
}

// вносит коррективы в цену с учётом настроек валюты
add_filter('fs_price_filter', 'fs_price_filter_callback', 10, 2);
function fs_price_filter_callback($price, $post_id)
{
    $price = floatval(str_replace(',', '.', $price));

    if (fs_option('multi_currency_on') != 1) {
        return $price;
    }

    global $wpdb;
    $default_currency_id = fs_option('default_currency'); // id валюты установленной в настройках
    $product_currency = fs_get_product_currency($post_id);
    $product_currency_id = $product_currency['id']; // id валюты товара
    if (function_exists('wpm_get_language') && function_exists('wpm_get_lang_option')) {
        $current_lang_code = wpm_get_language();
        $languages = wpm_get_lang_option();
        $locale = isset($languages[$current_lang_code]['locale']) ? $languages[$current_lang_code]['locale'] : get_locale();
    } else {
        $locale = get_locale();
    }

    // Если установлена галочка "конвертация стоимости в зависимости от языка"
    if (fs_option('price_conversion')) {
        // если в валюте товара не указаны локали или текущая локаль не входит в список разрешённых
        if (empty($product_currency['locales']) || !in_array($locale, $product_currency['locales'])) {
            // получаем валюту текущей локали
            $locale_currency_id = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM $wpdb->termmeta WHERE meta_key LIKE %s AND meta_value = %s", $wpdb->esc_like('__fs_currency_locale').'%', $locale));

            if (!$locale_currency_id) {
                $locale_currency_id = $default_currency_id;
            }
            $locale_currency_cost = get_term_meta($locale_currency_id, '__fs_currency_cost', true);

            if ($product_currency_id != $locale_currency_id) {
                $product_currency_cost = floatval(get_term_meta($product_currency_id, '__fs_currency_cost', true));
                // конвертируем в базовую валюту
                if ($product_currency_cost) {
                    $price = $price * $product_currency_cost;
                }
                // Конвертируем в валюту локали
                if ($locale_currency_cost && $locale_currency_id != $default_currency_id) {
                    $price = $price * $locale_currency_cost;
                }
            }
        }

        // округляем цену к ближайшему целому если установлена галочка "округлять цену"
        $cents = fs_option('price_cents') == 1 ? 2 : 0;
        $price = round($price, $cents);

        return $price;
    }

    //  Если установлена валюта у товара отличная от валюты сайта, то конвертируем её
    // if ($product_currency_id && $product_currency_id != $default_currency_id) {
    //     $product_currency_cost = floatval(get_term_meta($product_currency_id, '_fs_currency_cost', true));
    //     if ($product_currency_cost) {
    //         $price = $price * $product_currency_cost;

    //         // округляем цену к ближайшему целому если установлена галочка "округлять цену"
    //         $cents = fs_option('price_cents') == 1 ? 2 : 0;
    //         $price = round($price, $cents);
    //     }
    // }

    return $price;
}

// Adds a suffix to the meta field of the taxonomy term
add_filter('fs_term_meta_name', 'fs_term_meta_name_filter');
function fs_term_meta_name_filter($meta_key)
{
    if (fs_option('fs_multi_language_support')) {
        $meta_key = get_locale() == FS_Config::default_locale() ? $meta_key : $meta_key.'__'.get_locale();
    }

    return $meta_key;
}

add_filter('fs_price_discount_filter', 'fs_price_discount_filter', 10, 2);
/**
 * Функция устанавливает скидку на одну позицию товара.
 */
function fs_price_discount_filter($price, $product_id)
{
    $discount_terms_conf = [
        'taxonomy' => FS_Config::get_data('discount_taxonomy'),
        'hide_empty' => false,
        'meta_query' => [
            [
                'key' => 'discount_type',
                'value' => 'product',
                'compare' => '=',
            ],
        ],
    ];
    $discount_terms = get_terms($discount_terms_conf);
    $product_terms = wp_get_object_terms($product_id,
        [
            FS_Config::get_data('product_taxonomy'),
            FS_Config::get_data('brand_taxonomy'),
            FS_Config::get_data('features_taxonomy'),
        ]);

    // Если товар не привязан ни к одному термину или нет скидок возвращаем исходную цену
    if (!$discount_terms || !$product_terms) {
        return $price;
    }

    $discounts = [];

    // Проходимся по всем скидкам, которые предназначены для товара (есть еще другие скидки, не путать)
    foreach ($discount_terms as $discount_term) {
        if (!is_object($discount_term)) {
            continue;
        }

        if (!isset($discount_term->term_id)) {
            continue;
        }

        // Получаем скидку по категориям
        $product_discount_categories = get_term_meta($discount_term->term_id, 'discount_categories', 0);
        $product_discount_categories = !empty($product_discount_categories) && is_array($product_discount_categories)
            ? array_shift($product_discount_categories) : [];

        // Получаем скидку по производителям
        $product_discount_brands = get_term_meta($discount_term->term_id, 'discount_brands', 0);
        $product_discount_brands = !empty($product_discount_brands) && is_array($product_discount_brands)
            ? array_shift($product_discount_brands) : [];

        // Получаем скидку по характеристикам
        $product_discount_features = get_term_meta($discount_term->term_id, 'discount_features', 0);
        $product_discount_features = !empty($product_discount_features) && is_array($product_discount_features)
            ? array_shift($product_discount_features) : [];

        $product_in_categories = is_object_in_term($product_id, FS_Config::get_data('product_taxonomy'), $product_discount_categories);

        $product_in_brands = is_object_in_term($product_id, FS_Config::get_data('brand_taxonomy'), $product_discount_brands);
        $product_in_features = is_object_in_term($product_id, FS_Config::get_data('features_taxonomy'), $product_discount_features);

        $discount = get_term_meta($discount_term->term_id, 'discount_amount', 1);

        // Ищем знак процента в строке
        if (strpos($discount, '%') !== false) {
            $discount = preg_replace('/[^0-9]/', '', $discount);
            $discount = $price * $discount / 100;
        }

        // Если значение скидки 0 или пустое значение переходим на след. итерацию цикла
        if (empty($discount)) {
            continue;
        }

        // Проверям привязан ли товар к указанным выше категориям
        if (!empty($product_discount_categories) && $product_in_categories) {
            // Добавляем все скидки в массив
            array_push($discounts, floatval($discount));
        }

        // Проверям привязан ли товар к указанным выше брендам
        if (!empty($product_discount_brands) && $product_in_brands) {
            // Добавляем все скидки в массив
            array_push($discounts, floatval($discount));
        }

        // Проверям привязан ли товар к указанным выше свойствам
        if (!empty($product_discount_features) && $product_in_features) {
            // Добавляем все скидки в массив
            array_push($discounts, floatval($discount));
        }
    }

    if ($discounts) {
        // Применяем максимальную скидку
        $total_discount = max($discounts);
        $price = $price - $total_discount;
    }

    return floatval($price);
}

// Localization of product meta fields
add_filter('fs_product_tab_admin_meta_key', 'fs_product_tab_admin_meta_key', 10, 2);
function fs_product_tab_admin_meta_key($meta_key, $field)
{
    if (!isset($field['multilang']) || (isset($field['multilang']) && $field['multilang'] == false)) {
        return $meta_key;
    }

    if (isset($_REQUEST['language'])) {
        $query_lang = $_REQUEST['language'];
    } elseif (isset($_REQUEST['wpglobus_language'])) {
        $query_lang = $_REQUEST['wpglobus_language'];
    } else {
        return $meta_key;
    }

    $all_languages = FS_Config::get_languages();
    $default_language = FS_Config::default_locale();

    $current_language = isset($all_languages[$query_lang]['locale'])
        ? $all_languages[$query_lang]['locale']
        : $default_language;

    if ($current_language == $default_language) {
        return $meta_key;
    }

    $meta_key = implode('__', [$meta_key, $current_language]);

    return $meta_key;
}
