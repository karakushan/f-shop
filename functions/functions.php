<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

use FS\FS_Cart;
use FS\FS_Config;
use FS\FS_Order;
use FS\FS_Product;
use FS\FS_Users;

/**
 * Recursively get taxonomy and its children.
 *
 * @return array
 */
function fs_get_taxonomy_hierarchy($args)
{
    // get all direct decendants of the $parent
    $terms = get_terms($args);
    // prepare a new array.  these are the children of $parent
    // we'll ultimately copy all the $terms into this new array, but only after they
    // find their own children
    $children = [];
    // go through all the direct decendants of $parent, and gather their children
    foreach ($terms as $term) {
        // recurse to get the direct decendants of "this" term
        $args['parent'] = $term->term_id;
        $term->children = fs_get_taxonomy_hierarchy($args);
        // add the term to our new array
        $children[$term->term_id] = $term;
    }

    // send the results back to the caller
    return $children;
}

/**
 * Outputs the product gallery for a specified product.
 *
 * @param int   $post_id The ID of the product. Defaults to 0.
 * @param array $args    optional arguments to customize the gallery display
 */
function fs_product_gallery($post_id = 0, $args = [])
{
    $post_id = fs_get_product_id($post_id);

    return (new FS\FS_Images_Class())->product_gallery_list($post_id, $args);
}

/**
 * Retrieves slider images for a given post.
 *
 * @param int   $post_id The ID of the post. Defaults to 0.
 * @param array $args    additional arguments for retrieving images
 *
 * @return array an array of URLs for the slider images
 */
function fs_get_slider_images($post_id = 0, $args = [])
{
    $post_id = fs_get_product_id($post_id);
    $gallery = new FS\FS_Images_Class();
    $images = $gallery->gallery_images_url($post_id, $args);

    return $images;
}

/**
 * Возвращает массив ID изображений галереи товара.
 *
 * @param int  $product_id
 * @param bool $thumbnail
 *
 * @return array $images
 */
function fs_get_gallery($product_id = 0, $thumbnail = true, $attachments = false)
{
    $gallery = new FS\FS_Images_Class();
    $images = $gallery->get_gallery($product_id, $thumbnail, $attachments);

    return $images;
}

/**
 * Проверяет есть ли акционная цена.
 *
 * @return bool
 */
function fs_has_sale_price($product_id = 0)
{
    $product_id = fs_get_product_id($product_id);

    return (bool) get_post_meta($product_id, FS_Config::get_meta('action_price'), 1);
}

/**
 * Получает текущую цену с учётом скидки.
 *
 * @param int $product_id   - id поста, в данном случае товара (по умолчанию берётся из глобальной переменной $post)
 * @param int $variation_id - id вариации
 *
 * @return float $price - значение цены
 */
function fs_get_price($product_id = 0, $variation_id = 0)
{
    $product_id = fs_get_product_id($product_id);
    $product_class = new FS_Product();

    // Если товар вариативный, то возвращаем цену вариации
    if ($product_class->is_variable_product($product_id)) {
        $variation = $product_class->get_variation($product_id, $variation_id);
        $variation_price = floatval($variation['price']);
        $variation_sale_price = floatval($variation['sale_price']);
        if ($variation_sale_price > 0 && $variation_sale_price < $variation_price) {
            $price = $variation_sale_price;
        } elseif ($variation_price > 0) {
            $price = $variation_price;
        }
    } else {
        $price = get_post_meta($product_id, FS_Config::get_meta('price'), true);
        $action_price = get_post_meta($product_id, FS_Config::get_meta('action_price'), true);
        $price = floatval(str_replace(',', '.', $price));
        $action_price = floatval(str_replace(',', '.', $action_price));

        if ($action_price && $action_price < $price) {
            $price = $action_price;
        }
    }

    $price = apply_filters('fs_price_discount_filter', $price, $product_id);
    $price = apply_filters('fs_price_filter', $price, $product_id);
    $use_pennies = fs_option('price_cents') ? 2 : 0;

    return round(floatval($price), $use_pennies);
}

/**
 * Displays the current price with discount.
 *
 * @param int|string $product_id product id
 * @param string     $wrap       html wrapper for price
 * @param array      $args       additional arguments
 */
function fs_the_price($product_id = 0, $wrap = '%s %s', $args = [])
{
    $args = wp_parse_args($args, [
        'class' => 'fs-price',
    ]);
    $cur_symb = fs_currency($product_id);
    $product_id = fs_get_product_id($product_id);
    $price = fs_get_price($product_id);
    $price_formatted = apply_filters('fs_price_format', $price);

    printf('<span class="'.esc_attr($args['class']).'">'.$wrap.'</span>', '<span x-text="typeof price===\'number\' && price_formatted ? price_formatted : \''.$price_formatted.'\' ">'.esc_attr($price_formatted).'</span>', '<span x-text="typeof currency!==\'undefined\' ? currency : \''.esc_attr($cur_symb).'\'">'.esc_attr($cur_symb).'</span>');
}

/**
 * Выводит текущую оптовую цену с учётом скидки вместе с валютой сайта.
 *
 * @param int $product_id id товара
 */
function fs_the_wholesale_price($product_id = 0)
{
    $price = fs_get_wholesale_price($product_id);
    $price = apply_filters('fs_price_format', $price);
    printf('<span>%s <span>%s</span></span>', esc_html($price), esc_html(fs_currency()));
}

/**
 * Получает текущую оптовую цену с учётом скидки.
 *
 * @param int $product_id - id товара
 *
 * @return float price - значение цены
 */
function fs_get_wholesale_price($product_id = 0)
{
    $config = new FS_Config();
    global $post;
    $product_id = empty($product_id) ? $post->ID : (int) $product_id;

    $old_price = get_post_meta($product_id, $config->meta['wholesale_price'], 1);
    $new_price = get_post_meta($product_id, $config->meta['wholesale_price_action'], 1);
    $price = !empty($new_price) ? (float) $new_price : (float) $old_price;
    if (empty($price)) {
        $price = 0;
    }

    return $price;
}

/**
 * Displays the total amount of all products in the cart.
 *
 * @param string $wrap          формат отображения цены с валютой
 * @param bool   $delivery_cost если false стоимость доставки будет расчитана автоматически,
 *                              если указать числовое значение, то стоимость доставки равна этому числу
 */
function fs_total_amount($wrap = '%s <span>%s</span>', $delivery_cost = false)
{
    $total = fs_get_total_amount($delivery_cost);
    $total = apply_filters('fs_price_format', $total);
    printf('<span data-fs-element="total-amount">'.$wrap.'</span>', esc_attr($total), esc_attr(fs_currency()));
}

/**
 * Returns the value of the goods in the cart excluding all discounts, taxes and other things.
 *
 * @return float|int
 */
function fs_get_cart_cost()
{
    $products = FS_Cart::get_cart();
    $cost = 0;
    if (is_array($products) && count($products)) {
        foreach ($products as $key => $product) {
            $product = fs_set_product($product, $key);
            $cost += $product->cost;
        }
    }

    return floatval($cost);
}

/**
 * Выводит стоимость товаров в корзине без учета всех скидок, налогов и прочего.
 *
 * @param array $args
 */
function fs_cart_cost($args = [])
{
    $args = wp_parse_args($args, [
        'format' => '<span data-fs-element="cart-cost">%price% <span>%currency%</span></span>',
    ]);

    $cart_cost = fs_get_cart_cost();

    $cart_cost = apply_filters('fs_price_format', $cart_cost);
    $replace = [
        '%price%' => esc_attr($cart_cost),
        '%currency%' => esc_attr(fs_currency()),
    ];
    echo str_replace(array_keys($replace), array_values($replace), $args['format']);
}

/**
 * Returns the total amount of all products in the cart.
 *
 * @param bool  $delivery_cost
 * @param array $args          дополнительные аргументы
 *
 * @return float|int
 *
 * @internal param int $delivery_term_id
 * @internal param int $shipping_cost
 */
function fs_get_total_amount($delivery_cost = false, $args = [])
{
    $args = wp_parse_args($args, [
        'customer_phone' => !empty($_POST['fs_phone']) ? $_POST['fs_phone'] : '',
        'shipping_method' => !empty(!empty($_POST['fs_delivery_methods'])) ? absint($_POST['fs_delivery_methods']) : 0,
    ]);
    // Получаем чистую стоимость товаров (c учетом акционной цены)
    $amount = fs_get_cart_cost();

    // Если сумма товаров в корзине превышает указанную в настройке "fs_free_delivery_cost" то стоимость доставки равна 0
    if (fs_option('fs_free_delivery_cost') && $amount > fs_option('fs_free_delivery_cost')) {
        $delivery_cost = 0;
    }

    // Добавляем стоимость доставки
    if (!is_numeric($delivery_cost)) {
        $delivery_cost = fs_get_delivery_cost();
    }
    $amount = $amount + $delivery_cost;

    // Добавляем стоимость упаковки если нужно
    if (fs_option('fs_include_packing_cost') && $args['shipping_method']) {
        $amount += fs_get_packing_cost($args['shipping_method']);
    }

    // Вычисляем налоги
    $taxes_amount = fs_get_taxes_amount($amount);
    $amount = $amount + $taxes_amount;

    return floatval($amount);
}

/**
 * Displays the total value of goods in the cart excluding discounts.
 *
 * @param string $wrap формат вывода
 */
function fs_total_amount_without_discount($wrap = '%s <span>%s</span>')
{
    $cart_items = FS_Cart::get_cart();

    $total = 0;

    foreach ($cart_items as $item_id => $cart_item) {
        $item = fs_set_product($cart_item, $item_id);
        $total += $item->base_price;
    }

    $total = apply_filters('fs_price_format', $total);
    printf('<span data-fs-element="total-amount">'.$wrap.'</span>', esc_attr($total), esc_attr(fs_currency()));
}

/**
 * Расчитывает и возвращает сумму налогов.
 *
 * @return float
 */
function fs_get_taxes_amount($amount)
{
    $args = [
        'taxonomy' => 'fs-taxes',
        'hide_empty' => false,
    ];

    $terms = get_terms($args);
    $taxes_amount = [];

    if ($terms) {
        foreach ($terms as $term) {
            $tax = get_term_meta($term->term_id, '_fs_tax_value', 1);

            if (strpos($tax, '%') !== false) {
                $tax_num = floatval(str_replace('%', '', $tax));
                $taxes_amount[] = $amount * $tax_num / 100;
            } elseif (is_numeric($tax)) {
                $taxes_amount[] = floatval($tax);
            } else {
                $taxes_amount[] = 0;
            }
        }
    }
    $amount = floatval(array_sum($taxes_amount));

    return $amount;
}

/**
 * Returns the total discount amount of all items in the cart.
 *
 * @param string $phone_number
 *
 * @return float|int
 */
function fs_get_total_discount($phone_number = '')
{
    global $wpdb;
    $discount = 0;
    $amount = fs_get_cart_cost();

    $discounts = get_terms([
        'taxonomy' => FS_Config::get_data('discount_taxonomy'),
        'hide_empty' => 0,
    ]);

    if (is_wp_error($discounts)) {
        return $discount;
    }

    foreach ($discounts as $d) {
        $discount_type = get_term_meta($d->term_id, 'fs_discount_type', 1);
        $discount_value = (float) get_term_meta($d->term_id, 'fs_discount_value', 1);
        $discount_value_type = get_term_meta($d->term_id, 'fs_discount_value_type', 1);

        if ($discount_value <= 0) {
            continue;
        }

        // Скидка на повторный заказ
        if ($discount_type == 'repeat_order' && $phone_number) {
            $search_customer = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}fs_customers WHERE phone = '%s'", preg_replace('/[^0-9]/', '', $phone_number)));

            if ($search_customer && $discount_value_type == 'discount_percent') {
                $discount += $amount * $discount_value / 100;
            } elseif ($search_customer && $discount_value_type == 'discount_fixed') {
                $discount += $discount_value;
            }
        }
    }

    if (!FS_Cart::has_empty()) {
        foreach (fs_get_cart() as $item) {
            if (!fs_has_sale_price($item['ID'])) {
                continue;
            }
            $discount += (fs_get_base_price($item['ID']) - fs_get_price($item['ID'])) * $item['qty'];
        }
    }

    return floatval($discount);
}

/**
 * Выводит сумму скидки.
 *
 * @param string $format
 */
function fs_cart_discount($format = '%s <span>%s</span>')
{
    printf($format, apply_filters('fs_price_format', fs_get_total_discount()), fs_currency());
}

/**
 * Скидка на общую сумму товаров в корзине.
 *
 * @return float
 */
function fs_get_full_cart_discount()
{
    $discount = 0;
    if (!taxonomy_exists('fs-discounts')) {
        return $discount;
    }
    // Добавляем скидку на общую сумму товаров в корзине
    $cart_cost = fs_get_cart_cost();
    $discount_terms = get_terms([
        'taxonomy' => 'fs-discounts',
        'hide_empty' => false,
    ]);

    if ($discount_terms) {
        foreach ($discount_terms as $discount_term) {
            $where = get_term_meta($discount_term->term_id, 'discount_where', 1);
            $value = get_term_meta($discount_term->term_id, 'discount_value', 1);
            $amount = get_term_meta($discount_term->term_id, 'discount_amount', 1);

            if (empty($where) || empty($value) || empty($amount) || !is_numeric($value)) {
                continue;
            }

            if ($where == '>=' && $cart_cost >= $value) {
                if (strpos($amount, '%')) {
                    $amount = floatval(str_replace('%', '', $amount));
                    $discount += $cart_cost * ($amount / 100);
                } elseif (is_numeric($amount)) {
                    $discount += $cart_cost - $amount;
                }
            } elseif ($where == '>' && $cart_cost > $value) {
                if (strpos($amount, '%')) {
                    $amount = floatval(str_replace('%', '', $amount));
                    $discount += $cart_cost * ($amount / 100);
                } elseif (is_numeric($amount)) {
                    $discount += $cart_cost - $amount;
                }
            } elseif ($where == '<' && $cart_cost < $value) {
                if (strpos($amount, '%')) {
                    $amount = floatval(str_replace('%', '', $amount));
                    $discount += $cart_cost * ($amount / 100);
                } elseif (is_numeric($amount)) {
                    $discount += $cart_cost - $amount;
                }
            } elseif ($where == '<=' && $cart_cost <= $value) {
                if (strpos($amount, '%')) {
                    $amount = floatval(str_replace('%', '', $amount));
                    $discount += $cart_cost * ($amount / 100);
                } elseif (is_numeric($amount)) {
                    $discount += $cart_cost - $amount;
                }
            }
        }
    }

    return floatval($discount);
}

/**
 * Возвращает стоимость упаковки.
 *
 * @param int $shipping_method id способа доставки
 *
 * @return float|int|string|string[]|null
 */
function fs_get_packing_cost($shipping_method = 0)
{
    $cost = 0;
    $parse_value = preg_replace('/[^0-9]/', '', fs_option('fs_packing_cost_value'));
    $products_cost = fs_get_cart_cost();
    $include_packing_cost = get_term_meta($shipping_method, '_fs_add_packing_cost', 1);

    if (!$include_packing_cost) {
        return 0;
    }

    if (!fs_option('fs_include_packing_cost') || !$parse_value) {
        return $cost;
    }

    if (strpos(fs_option('fs_packing_cost_value'), '%') !== false) {
        $cost = $products_cost * $parse_value / 100;
    } else {
        $cost = $parse_value;
    }

    return $cost;
}

/**
 * Выводит стоимость упаковки.
 *
 * @param string $format
 * @param array  $args
 */
function fs_packing_cost($format = '%s <span>%s</span>', $args = [])
{
    $args = wp_parse_args($args, [
        'class' => 'fs-packing-cost',
    ]);

    if (fs_option('fs_include_packing_cost')) {
        printf(
            '<div class="%s" data-fs-element="packing-cost">'.$format.'</div>',
            esc_attr($args['class']),
            apply_filters('fs_price_format', fs_get_packing_cost()),
            fs_currency()
        );
    }
}

/**
 * Returns information about the first nearest discount.
 */
function fs_get_first_discount()
{
    $fs_config = new FS_Config();
    $total_amount = fs_get_total_amount();
    $discounts = get_terms(['taxonomy' => $fs_config->data['discount_taxonomy'], 'hide_empty' => 0]);
    $discounts_cart = [];
    $total_discount = 0;
    $discount_difference = 0;
    $discount_diff = [];
    if ($discounts) {
        foreach ($discounts as $k => $discount) {
            $discount_type = get_term_meta($discount->term_id, 'discount_where_is', 1);
            $discount_where = get_term_meta($discount->term_id, 'discount_where', 1);
            $discount_value = get_term_meta($discount->term_id, 'discount_value', 1);
            $discount_amount = get_term_meta($discount->term_id, 'discount_amount', 1);
            // если скидка указана в процентах
            if (strpos($discount_amount, '%') !== false) {
                $discount_amount = floatval(str_replace('%', '', $discount_amount));
                $discount_amount = $discount_value * $discount_amount / 100;
            }

            if ($discount_type == 'sum' && ($discount_where == '>=' || $discount_where == '>') && $total_amount < $discount_value) {
                $discounts_cart[$k] = $discount_amount;
                $discount_diff[$k] = $discount_value - $total_amount;
            }
        }
    }
    if (!empty($discounts_cart)) {
        $total_discount = min($discounts_cart);
        $discount_difference = min($discount_diff);
    }

    return [
        'discount' => $total_discount,
        'discount_difference' => $discount_difference,
    ];
}

/**
 * Displays a discount amount.
 *
 * @param string $wrap
 */
function fs_total_discount($wrap = '%s <span>%s</span>')
{
    $discount = fs_get_total_discount('');
    $discount = apply_filters('fs_price_format', $discount);
    printf('<span data-fs-element="total-discount">'.$wrap.'</span>', esc_attr($discount), esc_html(fs_currency()));
}

/**
 * Возвращает количество товаров в корзине.
 *
 * @param array $products
 *
 * @return array|float|int
 */
function fs_total_count($products = [])
{
    if (empty($products)) {
        $products = !empty($_SESSION['cart']) ? $_SESSION['cart'] : 0;
    }
    $all_count = [];
    if ($products) {
        foreach ($products as $key => $count) {
            $all_count[$key] = $count['count'];
        }
    }
    $all_count = array_sum($all_count);

    return $all_count;
}

/**
 * Получаем содержимое корзины в виде массива.
 *
 * @param array $args
 *
 * @return array|bool элементов корзины в виде:
 *                    'id'-id товара,
 *                    'name'-название товара,
 *                    'count'-количество единиц одного продукта,
 *                    'price'-цена за единицу,
 *                    'all_price'-общая цена
 */
function fs_get_cart($args = [])
{
    $cart_items = FS_Cart::get_cart();
    $args = wp_parse_args($args, [
        'price_format' => '%s <span>%s</span>',
        'thumbnail_size' => 'thumbnail',
    ]);
    $products = [];
    if (is_array($cart_items) && count($cart_items)) {
        foreach ($cart_items as $key => $item) {
            $offer = fs_set_product($item);
            if (!$offer->id) {
                continue;
            }

            $product_image_url = fs_get_product_thumbnail_url($offer->id, $args['thumbnail_size']);

            $products[$key] = [
                'ID' => $offer->id,
                'id' => $offer->id,
                'name' => $offer->title,
                'count' => $offer->count,
                'qty' => $offer->count,
                /* @deprecated */
                'thumb' => $product_image_url,
                'thumbnail_url' => $product_image_url,
                'thumbnail' => '<img src="'.esc_attr($product_image_url).'" alt="'.esc_attr($offer->title).'" title="'.esc_attr($offer->title).'">',
                'attr' => $offer->attributes,
                'link' => $offer->permalink,
                'price' => $offer->price_display,
                'base_price' => $offer->base_price_display,
                'all_price' => $offer->cost_display,
                'sku' => $offer->sku,
                'currency' => $offer->currency,
            ];
        }
    }

    return $products;
}

/**
 * Displays the button for removing goods from the cart.
 *
 * @param int   $cart_item cart item id
 * @param array $args      array of arguments for the button or link
 *                         'text' - содержимое кнопки, по умолчанию '&#10005;',
 *                         'type' - тип тега ссылка 'link' или 'button',
 *                         'class'- класс для кнопки, ссылки (по умолчанию класс 'fs-delete-position')
 */
function fs_delete_position($cart_item = 0, $args = [])
{
    $cart = FS_Cart::get_cart();
    $name = !empty($cart[$cart_item]) ? get_the_title($cart[$cart_item]['ID']) : '';
    $args = wp_parse_args($args, [
        'content' => '&#10006;',
        'type' => 'link',
        'class' => 'fs-delete-position',
        'refresh' => true,
        'confirm' => sprintf(__('Are you sure you want to delete the item &laquo;%s&raquo; from the basket?', 'f-shop'), $name),
    ]);

    $html_atts = [
        'data-confirm' => $args['confirm'],
        'title' => sprintf(__('Remove item &laquo;%s&raquo;', 'f-shop'), $name),
        'data-cart-item' => $cart_item,
        'data-fs-type' => 'product-delete',
        'class' => $args['class'],
        'data-refresh' => $args['refresh'],
    ];

    $atts = fs_parse_attr([], $html_atts);

    switch ($args['type']) {
        case 'link':
            echo '<a href="javascript:void()" '.$atts.'>'.$args['content'].'</a>';
            break;
        case 'button':
            echo '<button type="button" '.$atts.'>'.$args['content'].'</button>';
            break;
    }
}

/**
 * Возвращает ссылку на каталог товаров.
 *
 * @return false|string
 */
function fs_get_catalog_link()
{
    $fs_config = new FS_Config();

    return get_post_type_archive_link($fs_config->data['post_type']);
}

/**
 * Удаляет товар из списка желаний.
 *
 * @param int    $product_id -id товара (если указать 0 будет взято ID  товара из цикла)
 * @param string $content    -текст кнопки
 * @param array  $args       -дополнительные атрибуты
 */
function fs_delete_wishlist_position($product_id = 0, $content = '🞫', $args = [])
{
    $product_id = fs_get_product_id($product_id);
    $args = wp_parse_args($args, [
        'class' => 'fs-delete-wishlist-position',
        'title' => sprintf(__('Remove from wishlist', 'f-shop'), get_the_title($product_id)),
    ]);

    echo '<button type="button" x-on:click.prevent="Alpine.store(\'FS\').removeWishlistItem('.$product_id.')" '.fs_parse_attr($args).'>'.$content.'</button>';
}

/**
 * Выводит к-во всех товаров в корзине.
 *
 * @param bool $echo выводить результат или возвращать, по умолчанию выводить
 *
 * @return int
 */
function fs_product_count($echo = true)
{
    $cart_items = FS_Cart::get_cart() && is_array(FS_Cart::get_cart())
        ? FS_Cart::get_cart() : [];
    $count = 0;

    foreach ($cart_items as $cart_item) {
        if (isset($cart_item['count'])) {
            $count += (int) $cart_item['count'];
        }
    }

    if ($echo) {
        echo esc_html($count);
    }

    return (int) $count;
}

/**
 * Получает базовую цену (перечёркнутую) без учёта скидки.
 *
 * @param int $product_id -id товара
 *
 * @return float $price
 */
function fs_get_base_price($product_id = 0)
{
    $product_id = fs_get_product_id($product_id);

    $price = floatval(get_post_meta($product_id, FS_Config::get_meta('price'), 1));
    $sale_price = get_post_meta($product_id, FS_Config::get_meta('action_price'), true)
        ? floatval(get_post_meta($product_id, FS_Config::get_meta('action_price'), true)) : 0;

    $first_variation = fs_get_first_variation($product_id);
    if (isset($first_variation['price']) && is_numeric($first_variation['price'])) {
        $price = floatval($first_variation['price']);
    }

    $price = apply_filters('fs_price_filter', $price, $product_id);
    $sale_price = apply_filters('fs_price_filter', $sale_price, $product_id);

    if ($sale_price && $sale_price < $price) {
        return $price;
    }

    return null;
}

/**
 * Выводит текущую цену с символом валюты без учёта скидки.
 *
 * @param int    $product_id id товара
 * @param string $wrap       html обёртка для цены
 * @param array  $args
 */
function fs_base_price($product_id = 0, $wrap = '%s <span>%s</span>', $args = [])
{
    $args = wp_parse_args($args, [
        'class' => 'fs-base-price',
    ]);
    $product_id = fs_get_product_id($product_id);
    $price = fs_get_base_price($product_id);
    if (empty($wrap)) {
        $wrap = '%s <span>%s</span>';
    }

    if (!$price) {
        return null;
    }
    $price = apply_filters('fs_price_format', $price);
    $show_price = sprintf($wrap, esc_html($price), esc_html(fs_currency()));
    printf(
        '<span data-fs-element="base-price" data-product-id="%d" data-fs-value="%f" class="%s">%s</span>',
        esc_attr($product_id),
        esc_attr($price),
        esc_attr($args['class']),
        $show_price
    );
}

/**
 * Возвращает первый вариант покупки.
 *
 * @param        $product_id - ID товара
 * @param string $return     - что возвращать: 'all' - весь массив, 'key' - только ключ
 *
 * @return int|string|null
 */
function fs_get_first_variation($product_id, $return = 'all')
{
    $product_class = new FS_Product();
    $variations = $product_class->get_product_variations($product_id);

    return count($variations) ? $variations[0] : null;
}

/**
 * Returns the name of the first category of product.
 *
 * @param int $product_id
 *
 * @return string
 */
function fs_get_product_category_name($product_id = 0)
{
    $category = get_the_terms($product_id, FS_Config::get_data('product_taxonomy'));
    if (!empty($category) && !is_wp_error($category)) {
        return array_pop($category)->name;
    }
}

/**
 * Displays the button "to cart" with all the necessary attributes.
 *
 * @param int    $product_id [id поста (оставьте пустым в цикле wordpress)]
 * @param string $label      [надпись на кнопке]
 * @param array  $args       дополнительные атрибуты
 *
 * @return mixed|void
 */
function fs_add_to_cart($product_id = 0, $label = 'Add to cart', $args = [])
{
    $product_id = fs_get_product_id($product_id);
    $label = $label ? $label : __('Add to cart', 'f-shop');

    // Параметры доступные для переопределения
    $args = wp_parse_args($args, [
        'type' => 'button',
        'title' => __('Add to cart', 'f-shop'),
        'id' => 'fs-atc-'.$product_id,
        'data-count' => fs_get_product_min_qty($product_id),
        'class' => 'fs-add-to-cart',
        'inline_attributes' => '',
    ]);

    // Смешиваем параметры для вывода текстов в качестве атрибутов кнопки
    $args = array_merge($args, [
        'x-data' => json_encode([
            'inCart' => FS_Cart::contains($product_id),
            'productId' => $product_id,
        ]),
        'x-on:click' => '(() => { $event.preventDefault(); const safeCount = typeof count !== "undefined" ? Number(count) : 1; const safeVariationId = typeof variationId !== "undefined" ? variationId : null; const safeAttributes = typeof attributes !== "undefined" ? attributes : {}; Alpine.store("FS").addToCart(productId, safeCount, safeVariationId, safeAttributes); inCart = true; })()',
    ]);

    $atc_after = '<span class="fs-atc-preloader" style="display:none" x-show="$store?.FS?.loading"></span>';

    if ($args['type'] == 'link') {
        $args['href'] = add_query_arg(['fs-api' => 'add_to_cart', 'product_id' => $product_id]);
    }

    $html_attributes = fs_parse_attr([], $args);

    // Проверяем, является ли inline_attributes массивом
    if (isset($args['inline_attributes']) && !empty($args['inline_attributes'])) {
        if (is_array($args['inline_attributes'])) {
            $args['inline_attributes'] = implode(' ', $args['inline_attributes']);
        }
        $html_attributes .= ' '.$args['inline_attributes'];
    }

    /* allow you to set different html elements as a button */
    switch ($args['type']) {
        case 'link':
            $atc_button = sprintf('<a %s>%s %s</a>', $html_attributes, $label, $atc_after);
            break;
        default:
            $atc_button = sprintf('<button type="button" %s>%s %s</button>', $html_attributes, $label, $atc_after);
            break;
    }

    echo apply_filters('fs_add_to_cart_filter', $atc_button);
}

/**
 * Выводит кнопку "добавить к сравнению".
 *
 * @param int    $post_id
 * @param string $label
 * @param array  $attr
 */
function fs_add_to_comparison($post_id = 0, $label = '', $attr = [])
{
    global $post;
    $post_id = empty($post_id) ? $post->ID : $post_id;
    $attr = wp_parse_args(
        $attr,
        [
            'json' => ['count' => 1, 'attr' => new stdClass()],
            'class' => 'fs-add-to-comparison',
            'type' => 'button',
            'success' => sprintf(__('Item «%s» added to comparison', 'f-shop'), get_the_title($post_id)),
            'error' => __('Error adding product to comparison', 'f-shop'),
        ]
    );

    // устанавливаем html атрибуты кнопки
    $attr_set = [
        'data-action' => 'add-to-comparison',
        'data-product-id' => $post_id,
        'data-product-name' => get_the_title($post_id),
        'id' => 'fs-atc-'.$post_id,
        'data-success' => $attr['success'],
        'data-error' => $attr['error'],
        'class' => $attr['class'],
    ];
    $html_atts = fs_parse_attr([], $attr_set);

    // дополнительные скрытые инфо-блоки внутри кнопки (прелоадер, сообщение успешного добавления в корзину)
    $atc_after = '<span class="fs-atc-preloader" style="display:none"></span>';

    /* позволяем устанавливать разные html элементы в качестве кнопки */
    switch ($attr['type']) {
        case 'link':
            printf('<a href="#add_to_comparison" %s>%s %s</a>', $html_atts, $label, $atc_after);
            break;
        default:
            printf('<button type="button" %s>%s %s</button>', $html_atts, $label, $atc_after);
            break;
    }
}

/**
 * Displays the submission button of the order form.
 *
 * @param string $label the inscription on the button
 * @param array  $attr  html attributes of the button element
 */
function fs_order_send($label = '', $attr = [])
{
    if (empty($label)) {
        $label = __('Send order', 'f-shop');
    }
    $args = wp_parse_args($attr, [
        'class' => 'fs-order-send btn btn-success btn-lg',
        'preloader_src' => FS_PLUGIN_URL.'assets/img/form-preloader.svg',
        'preloader_width' => 32,
    ]);
    $preloader = '<img class="fs-atc-preloader" style="display:none" x-show="loading" src="'.esc_attr($args['preloader_src']).'" width="'.esc_attr($args['preloader_width']).'" alt="preloader">';
    $inline_attributes = fs_parse_attr($attr, $args, ['preloader_src', 'preloader_width']);

    printf('<button type="submit" x-on:fs-checkout-start-submit.window="loading = true" x-on:fs-checkout-finish-submit.window="loading = false" x-data="{loading: false }" %s><span>%s</span> '.$preloader.'</button>', $inline_attributes, $label);
}

/**
 * Returns a link to the checkout page.
 *
 * @return false|string
 */
function fs_get_checkout_page_link()
{
    $page_id = fs_option('page_checkout', 0);
    if ($page_id) {
        return get_the_permalink($page_id);
    }
}

function fs_order_send_form()
{
    $form = new FS\FS_Shortcode();
    echo $form->order_send();
}

// Получает количество просмотров статьи
function fs_post_views($post_id = '')
{
    global $post;
    $post_id = empty($post_id) ? $post->ID : $post_id;

    $views = get_post_meta($post_id, 'views', true);

    if (!$views) {
        $views = 0;
    }

    return $views;
}

/**
 * показывает вижет корзины в шаблоне.
 *
 * @param array $args -массив атрибутов html элемента обёртки
 */
function fs_cart_widget($args = [])
{
    $args = wp_parse_args($args, [
        'class' => 'fs-cart-widget',
        'empty' => false,
        'tag' => 'a',
    ]);

    $template = '<'.$args['tag'].' href="'.fs_cart_url(false).'" data-fs-element="cart-widget" class="'.esc_attr($args['class']).'">';

    // если параметр  $args['empty']  == true это значит использовать отдельный шаблон для пустой корзины
    if ($args['empty']) {
        if (fs_total_count()) {
            $template .= fs_frontend_template('cart-widget/widget');
        } else {
            $template .= fs_frontend_template('cart-widget/cart-empty');
        }
    } else {
        $template .= fs_frontend_template('cart-widget/widget');
    }

    $template .= '</'.$args['tag'].'>';

    echo apply_filters('fs_cart_widget_template', $template);
}

/**
 * Shows or returns a link to the cart page.
 *
 * @param bool $show
 *
 * @return false|string
 */
function fs_cart_url($show = true)
{
    $cart_page = get_permalink(fs_option('page_cart', 0));
    if ($show == true) {
        echo esc_url($cart_page);
    } else {
        return $cart_page;
    }
}

/**
 * Displays a link to the checkout or payment page.
 *
 * @param bool $echo
 *
 * @return false|string
 */
function fs_checkout_url($echo = true, $query_args = [])
{
    $checkout_page_id = fs_option('page_checkout', 0);
    $base_url = get_permalink($checkout_page_id);
    if ($echo) {
        echo esc_url(add_query_arg($query_args, $base_url));
    } else {
        return add_query_arg($query_args, $base_url);
    }
}

/**
 * The function checks the availability of goods in stock.
 *
 * @param int $product_id id записи
 *
 * @return bool true - the product is in stock, false - not
 *
 * @deprecated  recommend using fs_in_stock()
 */
function fs_aviable_product($product_id = 0)
{
    return fs_in_stock();
}

if (!function_exists('fs_in_stock')) {
    /**
     * The function checks the availability of goods in stock.
     *
     * @param int $product_id id записи
     *
     * @return bool true - the product is in stock, false - not
     */
    function fs_in_stock($product_id = 0)
    {
        $product_id = fs_get_product_id($product_id);

        $stock = get_post_meta($product_id, FS_Config::get_meta('remaining_amount'), true);

        return (is_numeric($stock) && intval($stock) > 0) || $stock == '';
    }
}

/**
 * Returns the minimum quantity that is available to order.
 *
 * @param $product_id int
 *
 * @return int
 */
function fs_get_product_min_qty($product_id)
{
    $product_categories = get_the_terms($product_id, FS_Config::get_data('product_taxonomy'));
    if ($product_categories) {
        foreach ($product_categories as $product_category) {
            $min_qty = get_term_meta($product_category->term_id, '_min_qty', 1);
            if ($min_qty && is_numeric($min_qty)) {
                return (int) $min_qty;
            }
        }
    }

    return 1;
}

/**
 * Отображает или возвращает поле для изменения количества добавляемых товаров в корзину.
 *
 * @param int   $product_id - ID товара
 * @param array $args       - массив аргументов
 */
function fs_quantity_product($product_id = 0, $args = [])
{
    $product_id = fs_get_product_id($product_id);
    $min_qty = fs_get_product_min_qty($product_id);
    $args = wp_parse_args($args, [
        'position' => '%pluss% %input% %minus%',
        'wrapper' => 'div',
        'wrapper_class' => 'fs-qty-wrap',
        'pluss_class' => 'fs-pluss',
        'pluss_content' => '&plus;',
        'minus_class' => 'fs-minus',
        'minus_content' => '&ndash;',
        'input_class' => 'fs-quantity',
        'step' => 1,
        'min' => $min_qty,
    ]);

    $first_variation = fs_get_first_variation($product_id);
    if (!is_null($first_variation)) {
        $total_count = $first_variation['count'];
    } else {
        $total_count = get_post_meta($product_id, FS_Config::get_meta('remaining_amount'), true);
    }

    // Set attributes for a tag of type input text
    $data_atts = fs_parse_attr([
        'value' => $min_qty,
        'name' => 'count',
        'class' => $args['input_class'],
        'type' => 'text',
        'min' => $args['min'],
        'step' => $args['step'],
        'x-model' => 'count',
        'max' => fs_option('fs_in_stock_manage', 0) && $total_count ? intval($total_count) : '',
    ]);

    $pluss = sprintf('<button x-on:click.prevent="count=count+1" type="button" class="%s">%s</button> ', $args['pluss_class'], $args['pluss_content']);
    $minus = sprintf('<button x-on:click.prevent="count=count>parseInt('.$args['min'].') ? count-1 : 1" type="button" class="%s">%s</button>', $args['minus_class'], $args['minus_content']);
    $input = '<input  '.$data_atts.'>';

    $quantity = str_replace(['%pluss%', '%input%', '%minus%'], [
        $minus,
        $input,
        $pluss,
    ], $args['position']);

    printf('<%s class="%s"> %s </%s>', esc_attr($args['wrapper']), esc_attr($args['wrapper_class']), $quantity, esc_attr($args['wrapper']));
}

/**
 * Displays a field for changing the number of products in the basket.
 */
function fs_cart_quantity(int $item_id, float $value, array $args = [])
{
    $cart = fs_get_cart();
    $args = wp_parse_args($args, [
        'wrapper' => 'div',
        'refresh' => true,
        'wrapper_class' => 'fs-qty-wrap',
        'position' => '%minus% %input% %pluss%  ',
        'pluss' => ['class' => sanitize_html_class('fs-pluss'), 'content' => '+'],
        'minus' => ['class' => sanitize_html_class('fs-minus'), 'content' => '-'],
        'input' => ['class' => 'fs-cart-quantity'],
        'step' => 1,
        'min' => fs_get_product_min_qty($cart[$item_id]['ID']),
    ]);

    $input_atts = fs_parse_attr(
        [],
        [
            'type' => 'text',
            'name' => 'fs-cart-quantity',
            'data-refresh' => $args['refresh'],
            'value' => $value,
            'class' => $args['input']['class'],
            'data-fs-type' => 'cart-quantity',
            'data-item-id' => $item_id,
            'step' => $args['step'],
            'min' => $args['min'],
        ]
    );

    $pluss = '<button type="button" class="'.$args['pluss']['class'].'" data-fs-count="pluss" data-target="#product-quantify-'.$item_id.'">'.$args['pluss']['content'].'</button> ';
    $minus = '<button type="button" class="'.$args['minus']['class'].'" data-fs-count="minus" data-target="#product-quantify-'.$item_id.'">'.$args['minus']['content'].'</button>';
    $input = '<input   '.$input_atts.'     >';
    $quantity = str_replace(['%pluss%', '%minus%', '%input%'], [
        $pluss,
        $minus,
        $input,
    ], $args['position']);
    printf(
        '<%s class="%s"  data-fs-element="fs-quantity">%s</%s>',
        $args['wrapper'],
        esc_attr($args['wrapper_class']),
        $quantity,
        $args['wrapper']
    );
}

/**
 * Парсит урл и возвращает всё что находится до знака ?
 *
 * @param string $url строка url которую нужно спарсить
 *
 * @return string возвращает строку урл
 */
function fs_parse_url($url = '')
{
    $url = (filter_var($url, FILTER_VALIDATE_URL)) ? $url : $_SERVER['REQUEST_URI'];
    $parse = explode('?', $url);

    return $parse[0];
}

/**
 * Checks whether the stock price is set on a product.
 *
 * @param int $product_id
 *
 * @return bool|mixed
 */
function fs_is_action($product_id = 0)
{
    $product_id = fs_get_product_id($product_id);

    if (get_post_meta($product_id, FS_Config::get_product_field('label_promotion')['key'], 1)) {
        return true;
    }

    return false;
}

/**
 * Returns the object of the viewed goods or records.
 *
 * @param array $args
 *
 * @return array
 */
function fs_user_viewed($args = [])
{
    if (!isset($_SESSION['fs_user_settings']['viewed_product']) || !is_array($_SESSION['fs_user_settings']['viewed_product'])) {
        return [];
    }

    $posts = get_posts(wp_parse_args($args, [
        'post_type' => FS_Config::get_data('post_type'),
        'include' => array_values($_SESSION['fs_user_settings']['viewed_product']),
        'numberposts' => -1,
    ]));

    return $posts;
}

/**
 * Returns the currency ID of the item.
 *
 * важно знать что "ID валюты товара" должно быть равно
 * ID одной из добавленных терминов таксономии валюты
 * то-есть возвращается целое число а не код или симовл валюты
 *
 * @param int $product_id
 *
 * @return array;
 */
function fs_get_product_currency($product_id = 0)
{
    $fs_config = new FS_Config();
    $product_id = fs_get_product_id($product_id);
    $product_currency_id = intval(get_post_meta($product_id, $fs_config->meta['currency'], 1));
    $product_currency_code = get_term_meta($product_currency_id, '__fs_currency_code', 1);
    $product_currency_symbol = get_term_meta($product_currency_id, '__fs_currency_display', 1);
    $product_currency_locales = carbon_get_term_meta($product_currency_id, '_fs_currency_locale');
    $site_currency_id = intval(fs_option('default_currency', 0));

    // если у товара не найден ID валюта возвращаем  ID валюты по умолчанию
    if (!$product_currency_id) {
        $product_currency_id = $site_currency_id;
    }

    $currency = [
        'id' => $product_currency_id,
        'symbol' => $product_currency_symbol ?: fs_option('currency_symbol', '$'),
        'code' => $product_currency_code ?: 'USD',
        'locales' => $product_currency_locales ?: ['uk'],
    ];

    return $currency;
}

/**
 * Return the currency symbol.
 *
 * @return string
 */
function fs_currency()
{
    $currency_symbol = fs_option('currency_symbol', '$');

    if (fs_option('multi_currency_on')) { 
        global $wpdb;
        $default_country = FS_Config::get_data('default_country');
        $current_country = apply_filters('fs_current_country', $default_country);

        $locale_currency_id = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM $wpdb->termmeta WHERE meta_key LIKE %s AND meta_value = %s", $wpdb->esc_like('__fs_currency_locale').'%', $current_country));

        if ($locale_currency_id) {
            $currency_symbol = get_term_meta($locale_currency_id, '__fs_currency_display', 1);
        }
    }

    return apply_filters('fs_currency', $currency_symbol);
}

/**
 * Gets the currency symbol for a specific locale.
 * Caches the database query and the result.
 *
 * @param string $locale
 *
 * @return string|null
 */
function _fs_get_currency_symbol_for_locale($locale)
{
    global $wpdb;

    $id_cache_key = 'fs_currency_id_for_locale_'.$locale;
    $locale_currency_id = wp_cache_get($id_cache_key, 'f-shop');

    if (false === $locale_currency_id) {
        $locale_currency_id = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM $wpdb->termmeta WHERE meta_key LIKE %s AND meta_value = %s", $wpdb->esc_like(FS_Config::META_CURRENCY_LOCALE).'%', $locale));
        wp_cache_set($id_cache_key, $locale_currency_id, 'f-shop');
    }

    if (!$locale_currency_id) {
        return null;
    }

    $symbol_cache_key = 'fs_currency_symbol_'.$locale_currency_id;
    $currency_symbol = wp_cache_get($symbol_cache_key, 'f-shop');

    if (false === $currency_symbol) {
        $currency_symbol = get_term_meta($locale_currency_id, FS_Config::META_CURRENCY_DISPLAY, true);
        wp_cache_set($symbol_cache_key, $currency_symbol, 'f-shop');
    }

    return $currency_symbol;
}

/**
 * Returns option data.
 *
 * @param string $option_name option name
 * @param string $default     default value
 *
 * @return string
 */
function fs_option($option_name, $default = '')
{
    $option = get_option($option_name);

    if (empty($option) && !empty($default)) {
        $option = $default;
    }

    return $option;
}

/**
 * Возвращает настройку темы.
 *
 * @param string $default
 *
 * @return mixed|string
 */
function fs_get_theme_option($option_name, $default = '')
{
    $option = get_theme_mod($option_name);

    if (empty($option) && !empty($default)) {
        $option = $default;
    }

    return $option;
}

/**
 * Выводит настройку темы.
 *
 * @param string $default
 * @param string $filter
 *
 * @return mixed|string
 */
function fs_theme_option($option_name, $default = '', $filter = 'text')
{
    $option = fs_get_theme_option($option_name, $default);

    if ($filter == 'number') {
        $option = preg_replace('/[^0-9]/', '', $option);
    }

    echo esc_html($option);
}

/**
 * This function displays a delete button for all items in the cart.
 *
 * @param array $args
 */
function fs_delete_cart($args = [])
{
    $args = wp_parse_args($args, [
        'text' => __('Remove all items', 'f-shop'),
        'class' => 'fs-delete-cart',
        'type' => 'button',
    ]);
    $html_att = fs_parse_attr([], [
        'class' => $args['class'],
        'data-fs-element' => 'delete-cart',
        'data-confirm' => __('Are you sure you want to empty the trash?', 'f-shop'),
        'data-url' => wp_nonce_url(add_query_arg(['fs_action' => 'delete-cart']), 'fs_action'),
    ]);
    switch ($args['type']) {
        case 'button':
            echo '<button '.$html_att.'>'.$args['text'].'</button> ';
            break;
        case 'link':
            echo '<a href="#" '.$html_att.'>'.$args['text'].'</a> ';
            break;
    }
}

/**
 * Выводит процент или сумму скидки(в зависимости от настрорек).
 *
 * @param int|string $product_id -id товара(записи)
 * @param bool       $echo
 * @param string     $wrap       -html обёртка для скидки
 *
 * @return float|int
 */
function fs_amount_discount($product_id = 0, $echo = true, $wrap = '<span>%s</span>')
{
    global $post;
    $config = new FS_Config();
    $product_id = empty($product_id) ? $post->ID : $product_id;
    $action_symbol = isset($config->options['action_count']) && $config->options['action_count'] == 1 ? '<span>%</span>' : '<span>'.fs_currency().'</span>';
    $discount_meta = (float) get_post_meta($product_id, $config->meta['discount'], 1);
    $discount = empty($discount_meta) ? '' : sprintf($wrap, $discount_meta.' '.$action_symbol);
    $discount_return = empty($discount_meta) ? 0 : $discount_meta;
    if ($echo) {
        echo $discount;
    } else {
        return $discount_return;
    }
}

/**
 * Добавляет возможность фильтрации по определёному атрибуту.
 *
 * @param string $group          название группы (slug)
 * @param string $type           тип фильтра 'option' (список опций в теге "select",по умолчанию) или обычный список "ul"
 * @param string $option_default первая опция (текст) если выбран 2 параметр "option"
 */
function fs_attr_group_filter($group, $type = 'option', $option_default = '')
{
    if (empty($option_default)) {
        $option_default = __('Select value', 'f-shop');
    }
    $fs_filter = new FS\FS_Filters();
    echo $fs_filter->attr_group_filter($group, $type, $option_default);
}

/**
 * Displays a price slider.
 *
 * - it is recommended to call only on the page of the archive of goods
 *   and taxonomy of the category of goods
 */
function fs_range_slider()
{
    $term_id   = get_queried_object_id() ? get_queried_object_id() : 0;
    // Generate unique ID for each slider instance to avoid duplicate HTML IDs on the page.
    $unique_id = str_replace('.', '_', uniqid('fs_slider_'));

    echo fs_frontend_template(
        'widget/jquery-ui-slider/ui-slider',
        [
            'vars' => [
                'unique_id'   => $unique_id,
                'price_min'   => fs_price_min($term_id),
                'price_max'   => fs_price_max($term_id),
                'price_start' => ! empty($_GET['price_start']) ? intval($_GET['price_start']) : fs_price_min($term_id),
                'price_end'   => ! empty($_GET['price_end']) ? intval($_GET['price_end']) : fs_price_max($term_id),
                'currency'    => fs_currency(),
            ],
        ]
    );
}

/**
 * Функция получает значение максимальной цены установленной на сайте.
 *
 * @return float|int|string|null
 */
function fs_price_max($term_id)
{
    global $wpdb;
    $max = 0;

    if ($term_id) {
        $term = get_term($term_id);
        $taxonomy_name = FS_Config::get_data('product_taxonomy');
        $max = wp_cache_get('fs_max_price_term_'.$term->term_id);
        if (!$max) {
            // get max price form meta value price in product category
            $max = $wpdb->get_var($wpdb->prepare("
				SELECT MAX( CAST( pm.meta_value AS DECIMAL(10,2) ) )
				FROM {$wpdb->posts} AS p
				INNER JOIN {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id
				INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
				WHERE p.post_type = %s
				AND p.post_status = 'publish'
				AND tt.taxonomy = %s
				AND tt.term_id = %d
				AND pm.meta_key = %s
			", FS_Config::get_data('post_type'), $taxonomy_name, $term->term_id, FS_Config::get_meta('price')));
            wp_cache_set('fs_max_price_term_'.$term->term_id, $max);
        }
    } elseif (fs_is_catalog() || is_search()) {
        $max = wp_cache_get('fs_max_price_archive');
        if (!$max) {
            $sql = "SELECT max(cast(meta_value as unsigned)) FROM $wpdb->postmeta WHERE meta_key='%s'";
            $max = $wpdb->get_var($wpdb->prepare($sql, FS_Config::get_meta('price')));
            wp_cache_set('fs_max_price_archive', $max);
        }
    }

    return (float) $max;
}

/**
 * Функция получает значение минимальной цены установленной на сайте.
 *
 * @return float|int|string|null
 */
function fs_price_min()
{
    global $wpdb;
    $min = 0;

    if (fs_is_product_category()) {
        $term = get_queried_object();
        $min = wp_cache_get('fs_min_price_term_'.$term->term_id);
        $taxonomy_name = FS_Config::get_data('product_taxonomy');
        if (!$min) {
            $products = get_posts([
                'post_type' => FS_Config::get_data('post_type'),
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy_name,
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                    ],
                ],
            ]);
            foreach ($products as $product) {
            }

            wp_cache_set('fs_min_price_term_'.$term->term_id, $min);
        }
    } elseif (fs_is_catalog()) {
        $min = wp_cache_get('fs_min_price_archive');
        if (!$min) {
            $sql = "SELECT min(cast(meta_value as unsigned)) FROM $wpdb->postmeta WHERE meta_key='%s'";
            $min = (float) $wpdb->get_var($wpdb->prepare($sql, FS_Config::get_meta('price')));
            wp_cache_set('fs_min_price_archive', $min);
        }
    }

    return $min;
}

/**
 * Displays a button or link to add a product to the wishlist.
 *
 * @param int    $product_id  Product ID. If not specified, the current product is used.
 * @param string $button_text button or link text
 * @param array  $args        Additional parameters for button/link customization:
 *                            - 'attr'      (string) Custom attributes.
 *                            - 'type'      (string) Element type ('button' or 'link'). Default is 'button'.
 *                            - 'preloader' (string) HTML code for preloader, shown during loading.
 *                            - 'class'     (string) CSS classes for the element.
 *                            - 'id'        (string) HTML ID of the element.
 *                            - 'atts'      (string) Additional attributes for the element.
 *
 * @return void
 */
function fs_add_to_wishlist($product_id = 0, $button_text = '', $args = [])
{
    if (empty($button_text)) {
        $button_text = __('Add to wishlist', 'f-shop');
    }
    $product_id = fs_get_product_id($product_id);
    $defaults = [
        'attr' => '',
        'type' => 'button',
        'preloader' => '<img src="'.FS_PLUGIN_URL.'/assets/img/ajax-loader.gif" alt="preloader">',
        'class' => 'fs-whishlist-btn',
        'id' => 'fs-whishlist-btn-'.$product_id,
        'atts' => '',
    ];
    $args = wp_parse_args($args, $defaults);
    $html_atts = fs_parse_attr([], [
        'class' => $args['class'],
        'id' => $args['id'],
        'data-product-id' => $product_id,
        'x-data' => json_encode(['inWishlist' => FS\FS_Wishlist::contains($product_id)]),
        'x-on:click' => 'Alpine.store("FS").addToWishlist('.$product_id.'); inWishlist=!inWishlist',
        'x-bind:class' => '{\'fs-in-wishlist\':inWishlist}',
    ]);

    switch ($args['type']) {
        case 'link':
            echo '<a href="#fs-whishlist-btn"  '.$html_atts.' '.$args['atts'].'>'.$button_text.'<span class="fs-atc-preloader" style="display:none">'.$args['preloader'].'</span></a>';
            break;

        case 'button':
            echo '<button '.$html_atts.' '.$args['atts'].'>'.$button_text.'<span class="fs-atc-preloader" style="display:none">'.$args['preloader'].'</span></button>';
            break;
    }
}

/**
 * Displays a button or link to add a product to the comparison list.
 *
 * @param int    $product_id  Product ID. If not specified, the current product is used.
 * @param string $button_text button or link text
 * @param array  $args        Additional parameters for button/link customization:
 *                            - 'attr'      (string) Custom attributes.
 *                            - 'type'      (string) Element type ('button' or 'link'). Default is 'button'.
 *                            - 'preloader' (string) HTML code for preloader, shown during loading.
 *                            - 'class'     (string) CSS classes for the element.
 *                            - 'id'        (string) HTML ID of the element.
 *                            - 'atts'      (string) Additional attributes for the element.
 *
 * @return void
 */
function fs_add_to_compare($product_id = 0, $button_text = '', $args = [])
{
    if (empty($button_text)) {
        $button_text = __('Add to comparison', 'f-shop');
    }
    $product_id = fs_get_product_id($product_id);
    $defaults = [
        'attr' => '',
        'type' => 'button',
        'preloader' => '<img src="'.FS_PLUGIN_URL.'/assets/img/ajax-loader.gif" alt="preloader">',
        'class' => 'fs-compare-btn',
        'id' => 'fs-compare-btn-'.$product_id,
        'atts' => '',
    ];
    $args = wp_parse_args($args, $defaults);
    $html_atts = fs_parse_attr([], [
        'class' => $args['class'],
        'id' => $args['id'],
        'data-product-id' => $product_id,
        'x-data' => json_encode(['inCompare' => FS\FS_Compare::contains($product_id)]),
        'x-on:click' => 'Alpine.store("FS").addToCompare('.$product_id.'); inCompare=!inCompare',
        'x-bind:class' => '{\'fs-in-compare\':inCompare}',
    ]);

    switch ($args['type']) {
        case 'link':
            echo '<a href="#fs-compare-btn"  '.$html_atts.' '.$args['atts'].'>'.$button_text.'<span class="fs-atc-preloader" style="display:none">'.$args['preloader'].'</span></a>';
            break;

        case 'button':
            echo '<button '.$html_atts.' '.$args['atts'].'>'.$button_text.'<span class="fs-atc-preloader" style="display:none">'.$args['preloader'].'</span></button>';
            break;
    }
}

/**
 * Функция транслитерации русских букв.
 *
 * @return mixed|string
 */
function fs_transliteration($s)
{
    $s = (string) $s; // преобразуем в строковое значение
    $s = strip_tags($s); // убираем HTML-теги
    $s = str_replace(["\n", "\r"], ' ', $s); // убираем перевод каретки
    $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
    $s = trim($s); // убираем пробелы в начале и конце строки
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
    $s = strtr($s, [
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'є' => 'ye',
        'ё' => 'e',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'y',
        'і' => 'i',
        'ї' => 'yi',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'kh',
        'ц' => 'c',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'shch',
        'ы' => 'y',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',
        'ъ' => '',
        'ь' => '',
    ]);
    $s = preg_replace('/[^0-9a-z-_ ]/i', '', $s); // очищаем строку от недопустимых символов
    $s = str_replace(' ', '-', $s); // заменяем пробелы знаком минус

    return $s; // возвращаем результат
}

/**
 * Подключает шаблон $template из директории темы, если шаблон остсуствует ищет в папке "/templates/front-end/" плагина.
 *
 * @param        $template  -название папки и шаблона без расширения
 * @param array  $args      -дополнительные аргументы
 * @param string $extension
 *
 * @return mixed|void
 */
function fs_frontend_template($template, $args = [], $extension = '.php')
{
    $args = wp_parse_args($args, [
        'theme_base_path' => TEMPLATEPATH.DIRECTORY_SEPARATOR.'f-shop'.DIRECTORY_SEPARATOR,
        'plugin_base_path' => FS_PLUGIN_PATH.'templates'.DIRECTORY_SEPARATOR.'front-end'.DIRECTORY_SEPARATOR,
        'vars' => [],
    ]);
    $template_plugin = $args['plugin_base_path'].$template.$extension;
    $template_theme = $args['theme_base_path'].$template.$extension;
    extract($args['vars']);

    ob_start();
    if (file_exists($template_theme)) {
        include $template_theme;
    } elseif (file_exists($template_plugin)) {
        include $template_plugin;
    } else {
        printf(__('Template file %s not found in function %s', 'f-shop'), $template, __FUNCTION__);
    }
    $template = ob_get_clean();

    return apply_filters('fs_frontend_template', $template);
}

/**
 * Gets current user data with additional meta fields.
 *
 * Retrieves WordPress user object and enhances it with custom meta fields
 * from the F-Shop plugin.
 *
 * @since 1.0.0
 *
 * @return WP_User|null Enhanced user object or null if user doesn't exist
 */
function fs_get_current_user()
{
    $user = wp_get_current_user();
    if ($user->exists()) {
        $profile_update = empty($user->profile_update) ? strtotime($user->user_registered) : $user->profile_update;

        // Get all user fields
        $user_fields = FS_Users::get_user_fields();

        // Set each field removing fs_ prefix
        foreach ($user_fields as $field => $value) {
            $field_name = str_replace('fs_', '', $field);
            $user->$field_name = FS_Users::get_user_field($field);
        }

        // Set basic email from WP user
        $user->email = $user->user_email;

        // Set profile update time
        $user->profile_update = $profile_update;
    }

    return $user;
}

function fs_page_content()
{
    $page = filter_input(INPUT_GET, 'fs-page', FILTER_SANITIZE_URL);
    $pages = ['profile', 'conditions'];
    if (in_array($page, $pages)) {
        echo fs_frontend_template('auth/'.$page);
    } else {
        echo fs_frontend_template('auth/profile');
    }
}

/**
 * Gets the item number by the post id.
 *
 * @param int|int $product_id
 *
 * @return string $articul артикул товара
 */
function fs_get_product_code($product_id = 0)
{
    $product_id = fs_get_product_id($product_id);

    // Проверяем, является ли товар вариативным
    if (fs_is_variated($product_id)) {
        // Получаем первую вариацию
        $first_variation = fs_get_first_variation($product_id);

        // Если первая вариация существует и у нее есть ID
        if (!empty($first_variation) && isset($first_variation['sku'])) {
            // Проверяем, есть ли у вариации артикул

            if (!empty($first_variation['sku'])) {
                return $first_variation['sku'];
            }
        }
    }

    // Если товар не вариативный или у вариации нет артикула, используем артикул основного товара
    $sku = get_post_meta($product_id, FS_Config::get_meta('sku'), true);

    return $sku ? $sku : '';
}

/**
 * получает артикул товара по переданному id поста.
 *
 * @param int|int $product_id -id поста
 * @param string  $wrap       -html обёртка для артикула (по умолчанию нет)
 *
 * @return string артикул товара
 */
function fs_product_code($product_id = 0, $wrap = '%s')
{
    $articul = fs_get_product_code($product_id);
    if ($articul) {
        printf('<span class="fs-sku" data-fs-element="sku">'.$wrap.'</span>', esc_html($articul));
    }
}

/**
 * Returns the quantity or stock of goods in stock (if the value is empty 1 is displayed).
 *
 * @param int|int $product_id product id
 *
 * @return int|int stock of goods in stock
 */
function fs_remaining_amount($product_id = 0)
{
    global $post;
    $product_id = !empty($product_id) ? $product_id : $post->ID;
    $meta_field = FS_Config::get_meta('remaining_amount');
    $amount = get_post_meta($product_id, $meta_field, true);
    $amount = ($amount === '') ? '' : (int) $amount;

    return $amount;
}

/**
 * Returns all registered price types.
 *
 * @return array array of all registered prices
 */
function fs_get_all_prices()
{
    $config_prices = FS_Config::$prices;

    return apply_filters('fs_prices', $config_prices);
}

/**
 * @param int    $product_id
 * @param string $price_type
 *
 * @return float
 */
function fs_get_type_price($product_id = 0, $price_type = 'price')
{
    global $post;
    $product_id = empty($product_id) ? $post->ID : $product_id;
    $prices = fs_get_all_prices();
    $price = get_post_meta($product_id, $prices[$price_type]['meta_key'], 1);

    return floatval($price);
}

/**
 * Get the url of product gallery images.
 *
 * @param int $product_id
 *
 * @return array
 */
function fs_gallery_images_url($product_id = 0, $args = [])
{
    $product_id = fs_get_product_id($product_id);
    $gallery = new FS\FS_Images_Class();

    return $gallery->gallery_images_url($product_id, $args);
}

/**
 * Checks if the sales hit label is displayed.
 *
 * @param int $product_id
 *
 * @return bool
 */
function fs_is_bestseller($product_id = 0)
{
    $product_id = fs_get_product_id($product_id);

    return get_post_meta($product_id, FS_Config::get_product_field('label_bestseller')['key'], 1) ? true : false;
}

/**
 * Проверяет установлена ли у товара настройка "новинка".
 *
 * @param int $product_id
 *
 * @return bool
 */
function fs_is_novelty($product_id = 0)
{
    $product_id = fs_get_product_id($product_id);

    return get_post_meta($product_id, FS_Config::get_product_field('label_novelty')['key'], 1) ? true : false;
}

/**
 * возвращает объект  с похожими или связанными товарами.
 *
 * @param int|int $product_id идентификатор товара(поста)
 * @param array   $args       передаваемые дополнительные аргументы
 *
 * @return object объект с товарами
 */
function fs_get_related_products($product_id = 0, $args = [])
{
    global $post;
    $fs_config = new FS_Config();
    $product_id = empty($product_id) ? $post->ID : $product_id;
    $products = get_post_meta($product_id, $fs_config->meta['related_products'], false);
    $args = wp_parse_args($args, [
        'limit' => 4,
        'post__in' => [],
    ]);

    // ищем товары привязанные вручную
    if (!empty($products[0]) && is_array($products[0])) {
        $products = array_unique($products[0]);
        $args = [
            'post_type' => 'product',
            'post__in' => array_merge($products, $args['post__in']),
            'post__not_in' => [$product_id],
            'posts_per_page' => $args['limit'],
        ];
    } else {
        $term_ids = wp_get_post_terms($product_id, FS_Config::get_data('product_taxonomy'), ['fields' => 'ids']);
        $args = [
            'post_type' => 'product',
            'posts_per_page' => $args['limit'],
            'post__in' => array_merge($products, $args['post__in']),
            'post__not_in' => [$product_id],
            'tax_query' => [
                [
                    'taxonomy' => 'catalog',
                    'field' => 'term_id',
                    'terms' => $term_ids,
                ],
            ],
        ];
    }
    $posts = new WP_Query($args);

    return $posts;
}

/**
 * Returns the discount percentage.
 *
 * @param int $product_id
 * @param int $round      number of decimal places when rounding
 *
 * @return float|int|string
 */
function fs_change_price_percent($product_id = 0, $round = 1)
{
    $product_id = fs_get_product_id($product_id);
    $change_price = 0;

    if (
        get_post_meta($product_id, FS_Config::get_meta('action_price'), 1) == ''
        || get_post_meta($product_id, FS_Config::get_meta('price'), 1) == ''
    ) {
        return $change_price;
    }

    // получаем возможные типы цен
    $base_price = (float) get_post_meta($product_id, FS_Config::get_meta('price'), true); // базовая и главная цена
    $action_price = (float) get_post_meta($product_id, FS_Config::get_meta('action_price'), true); // акционная цена

    if ($base_price > 0 && $action_price < $base_price) {
        $change_price = ($base_price - $action_price) / $base_price * 100;
        $change_price = round($change_price, $round);
    }

    return $change_price;
}

/**
 * Displays a product discount in percent.
 *
 * @param int    $product_id -ID товара(записи)
 * @param string $format     -html теги, обёртка для скидки
 * @param array  $args
 */
function fs_discount_percent($product_id = 0, $format = '-%s%s', $args = [])
{
    $args = wp_parse_args(
        $args,
        [
            'class' => 'fs-discount',
        ]
    );
    $discount = fs_change_price_percent($product_id, 0);

    if ($discount > 0) {
        printf('<span data-fs-element="discount" class="%s">', esc_attr($args['class']));
        printf($format, $discount, '%');
        printf('</span>');
    }
}

/**
 * Преобразует массив аргументов в строку для использования в атрибутах тегов
 * принцип работы похож на wp_parse_args().
 *
 * @param array $attr    атрибуты которые доступны для изменения динамически
 * @param array $default атрибуты функции по умолчанию
 * @param array $exclude атрибуты которые не нужно выводить в html теге
 *
 * @return string $att          строка атрибутов
 */
function fs_parse_attr($attr = [], $default = [], $exclude = [])
{
    $attr = wp_parse_args($attr, $default);
    $attributes = [];
    foreach ($attr as $key => $attribute) {
        if (in_array($key, $exclude)) {
            continue;
        }

        // Проверяем, является ли $attribute массивом, и если да, преобразуем его в JSON-строку
        if (is_array($attribute)) {
            $attribute = json_encode($attribute);
        }

        $attributes[] = esc_attr($key).'="'.esc_attr($attribute).'"';
    }

    if (count($attributes)) {
        return implode(' ', $attributes);
    }

    return null;
}

/**
 * Returns a wishlist.
 *
 * @param array  массив аргументов, идентичные WP_Query
 *
 * @return array
 */
function fs_get_wishlist($args = [])
{
    return FS\FS_Wishlist::get_wishlist_products();
}

/**
 * Displays the number of products in the wishlist.
 */
function fs_wishlist_count()
{
    echo esc_html(FS\FS_Wishlist::get_wishlist_count());
}

/**
 * Displays the number of products in the compare list.
 */
function fs_compare_count()
{
    echo esc_html(FS\FS_Compare::get_compare_count());
}

/**
 * Returns the total number of products in the wishlist.
 *
 * Gets the count from the FS_Wishlist class which combines items from:
 * - Session storage for non-logged in users
 * - User meta for logged in users
 *
 * @return int Number of products in wishlist
 */
function fs_get_wishlist_count()
{
    return FS\FS_Wishlist::get_wishlist_count();
}

/**
 * Return a link to the wish list.
 *
 * the page is set automatically during installation or can be set manually in the settings in the tab "Pages"
 */
function fs_wishlist_url()
{
    return get_the_permalink(intval(fs_option('page_whishlist')));
}

/**
 * Returns a link to the compare page.
 *
 * @return false|string
 */
function fs_compare_url()
{
    return get_the_permalink(intval(fs_option('page_compare')));
}

/**
 * Return a link to account page.
 *
 * the page is set automatically during installation or can be set manually in the settings in the tab "Pages"
 */
function fs_account_url()
{
    return is_user_logged_in()
        ? get_the_permalink(intval(fs_option('page_cabinet')))
        : get_the_permalink(intval(fs_option('page_auth')));
}

/**
 * отображает список желаний.
 *
 * @param array $html_attr массив html атрибутов для дива обёртки
 */
function fs_wishlist_widget($html_attr = [])
{
    echo fs_frontend_template(
        'wishlist/wishlist',
        [
            'vars' => [
                'wishlist_url' => fs_wishlist_url(),
                'wishlist_count' => fs_get_wishlist_count(),
            ],
        ]
    );
}

/**
 * @param int $order_id -id заказа
 *
 * @return bool|object возвращает объект с данными заказа или false
 */
function fs_get_order($order_id = 0)
{
    $order = false;
    if ($order_id) {
        $orders = new FS\FS_Orders();
        $order = $orders->get_order($order_id);
    }

    return $order;
}

function fs_get_delivery($delivery_id)
{
    $name = get_term_field('name', $delivery_id, 'fs-delivery-methods');

    return $name;
}

function fs_get_payment($payment_id)
{
    $name = get_term_field('name', $payment_id, 'fs-payment-methods');

    return $name;
}

/**
 * Функция выводе одно поле формы заказа.
 *
 * @param string $field_name название поля, атрибут name
 * @param array  $args       массив аргументов типа класс, тип, обязательность заполнения, title
 */
function fs_form_field($field_name, $args = [])
{
    $form_class = new FS\FS_Form();
    $form_class->render_field($field_name, '', $args);
}

/**
 * создаёт переменные в письмах из массива ключей.
 *
 * @param array $keys -ключи массива
 *
 * @return array массив из значений типа %variable%
 */
function fs_mail_keys($keys = [])
{
    $email_variable = [];
    if ($keys) {
        foreach ($keys as $key => $value) {
            $email_variable[] = '%'.$key.'%';
        }
    }

    return $email_variable;
}

function fs_attr_list($attr_group = 0)
{
    $terms = get_terms([
        'taxonomy' => 'product-attributes',
        'hide_empty' => false,
        'parent' => $attr_group,
    ]);
    $atts = [];
    foreach ($terms as $term) {
        switch (get_term_meta($term->term_id, 'fs_att_type', 1)) {
            case 'color':
                $atts[] = get_term_meta($term->term_id, 'fs_att_color_value', 1);
                break;
            case 'image':
                $atts[] = get_term_meta($term->term_id, 'fs_att_image_value', 1);
                break;
            case 'text':
                $atts[] = $term->name;
                break;
        }
    }

    return $atts;
}

/**
 * Выводит список всех атрибутов товара в виде:
 *   Название группы свойств : свойство (свойства)
 *
 * @param int   $post_id -ID товара
 * @param array $args    -дополнительные аргументы вывода
 */
function fs_the_atts_list($post_id = 0, $args = [])
{
    global $post;
    $fs_config = new FS_Config();
    $list = '';
    $post_id = !empty($post_id) ? $post_id : $post->ID;
    $args = wp_parse_args($args, [
        'wrapper' => 'ul',
        'group_wrapper' => 'span',
        'wrapper_class' => 'fs-atts-list',
        'exclude' => [],
        'parent' => 0,
        'limit' => 0, // 0 means no limit
        'grouped' => false, // If true, display attributes grouped by parent categories with headers
    ]);
    $term_args = [
        'hide_empty' => false,
        'exclude_tree' => $args['exclude'],
    ];
    $post_terms = wp_get_object_terms($post_id, $fs_config->data['features_taxonomy'], $term_args);
    $parents = [];
    if ($post_terms) {
        foreach ($post_terms as $post_term) {
            if ($post_term->parent == 0 || ($args['parent'] != 0 && $args['parent'] != $post_term->parent)) {
                continue;
            }
            $parents[$post_term->parent][$post_term->term_id] = $post_term->term_id;
        }
    }
    if ($parents) {
        // Get parent terms with positions for sorting
        $parent_terms = [];
        foreach ($parents as $k => $parent) {
            $primary_term = get_term($k, $fs_config->data['features_taxonomy']);
            if (is_wp_error($primary_term) || !$primary_term) {
                continue;
            }
            $position = get_term_meta($k, 'fs_att_position', true);
            $position = $position !== '' ? intval($position) : 0;
            $parent_terms[] = [
                'term' => $primary_term,
                'position' => $position,
                'children' => $parent
            ];
        }
        
        // Sort by position
        usort($parent_terms, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }
            return ($a['position'] < $b['position']) ? -1 : 1;
        });
        
        // Apply limit if specified
        if ($args['limit'] > 0) {
            $parent_terms = array_slice($parent_terms, 0, $args['limit']);
        }
        
        // If grouped format is requested, display with headers and two-column grid
        if ($args['grouped']) {
            foreach ($parent_terms as $parent_data) {
                $primary_term = $parent_data['term'];
                $parent = $parent_data['children'];
                $attributes = [];
                foreach ($parent as $p) {
                    $s = get_term($p, $fs_config->data['features_taxonomy']);
                    if (!is_wp_error($s) && $s) {
                        $attributes[] = [
                            'name' => apply_filters('the_title', $s->name),
                            'value' => apply_filters('the_title', $s->name)
                        ];
                    }
                }
                
                if (empty($attributes)) {
                    continue;
                }
                
                // Group header
                $list .= '<div class="fs-atts-group">';
                $list .= '<h3 class="fs-atts-group__title">' . esc_html(apply_filters('the_title', $primary_term->name)) . '</h3>';
                $list .= '<div class="fs-atts-group__grid">';
                
                // Attributes in two columns - each child term is a separate attribute
                // Each child term name is both label and value (as shown in screenshot)
                foreach ($attributes as $attr) {
                    $list .= '<div class="fs-atts-group__item">';
                    $list .= '<span class="fs-atts-group__label">' . esc_html($attr['name']) . ':</span>';
                    $list .= '<span class="fs-atts-group__value">' . esc_html($attr['name']) . '</span>';
                    $list .= '</div>';
                }
                
                $list .= '</div>'; // .fs-atts-group__grid
                $list .= '</div>'; // .fs-atts-group
            }
        } else {
            // Original format for backward compatibility
            foreach ($parent_terms as $parent_data) {
                $primary_term = $parent_data['term'];
                $parent = $parent_data['children'];
                $second_term = [];
                foreach ($parent as $p) {
                    $s = get_term($p, $fs_config->data['features_taxonomy']);
                    if (!is_wp_error($s) && $s) {
                        $second_term[] = apply_filters('the_title', $s->name);
                    }
                }

                if (!empty($second_term)) {
                    $list .= '<li><span class="first">'.apply_filters('the_title', $primary_term->name).': </span><span class="last">'.implode(', ', $second_term).' </span></li > ';
                }
            }
        }
    }

    if ($args['grouped']) {
        // For grouped format, output div wrapper
        $html_atts = fs_parse_attr([], [
            'class' => $args['wrapper_class'] . ' fs-atts-list--grouped',
        ]);
        printf('<div %s>%s</div>', $html_atts, $list);
    } else {
        // Original format
        $html_atts = fs_parse_attr([], [
            'class' => $args['wrapper_class'],
        ]);
        printf(' <%s % s >%s </%s > ', $args['wrapper'], $html_atts, $list, $args['wrapper']);
    }
}

/**
 * Gets product attributes as array data structure
 *
 * @param int $post_id Product post ID. Default 0 (current post)
 * @param array $args {
 *     Optional. Array of arguments.
 *     @type array $exclude Array of term IDs to exclude
 *     @type int $parent Parent term ID to filter by. Default 0 (all parents)
 *     @type int $limit Maximum number of attribute groups to return. Default 0 (no limit)
 *     @type bool $grouped If true, returns grouped structure with parent names. Default false
 * }
 * @return array Array of attribute groups with their children. Empty array if no attributes found.
 */
function fs_get_atts_list($post_id = 0, $args = [])
{
    global $post;
    $fs_config = new FS_Config();
    $post_id = !empty($post_id) ? $post_id : $post->ID;
    $args = wp_parse_args($args, [
        'exclude' => [],
        'parent' => 0,
        'limit' => 0, // 0 means no limit
        'grouped' => false, // If true, returns grouped structure with parent names
    ]);
    
    $term_args = [
        'hide_empty' => false,
        'exclude_tree' => $args['exclude'],
    ];
    
    $post_terms = wp_get_object_terms($post_id, $fs_config->data['features_taxonomy'], $term_args);
    $parents = [];
    
    if ($post_terms) {
        foreach ($post_terms as $post_term) {
            if ($post_term->parent == 0 || ($args['parent'] != 0 && $args['parent'] != $post_term->parent)) {
                continue;
            }
            $parents[$post_term->parent][$post_term->term_id] = $post_term->term_id;
        }
    }
    
    if (empty($parents)) {
        return [];
    }
    
    // Get parent terms with positions for sorting
    $parent_terms = [];
    foreach ($parents as $k => $parent) {
        $primary_term = get_term($k, $fs_config->data['features_taxonomy']);
        if (is_wp_error($primary_term) || !$primary_term) {
            continue;
        }
        $position = get_term_meta($k, 'fs_att_position', true);
        $position = $position !== '' ? intval($position) : 0;
        $parent_terms[] = [
            'term' => $primary_term,
            'position' => $position,
            'children' => $parent
        ];
    }
    
    // Sort by position
    usort($parent_terms, function ($a, $b) {
        if ($a['position'] == $b['position']) {
            return 0;
        }
        return ($a['position'] < $b['position']) ? -1 : 1;
    });
    
    // Apply limit if specified
    if ($args['limit'] > 0) {
        $parent_terms = array_slice($parent_terms, 0, $args['limit']);
    }
    
    $result = [];
    
    foreach ($parent_terms as $parent_data) {
        $primary_term = $parent_data['term'];
        $parent = $parent_data['children'];
        $attributes = [];
        
        foreach ($parent as $p) {
            $s = get_term($p, $fs_config->data['features_taxonomy']);
            if (!is_wp_error($s) && $s) {
                $attr_type = get_term_meta($s->term_id, 'fs_att_type', true);
                $attr_value = $s->name;
                
                // Get attribute value based on type
                if ($attr_type == 'color') {
                    $attr_value = get_term_meta($s->term_id, 'fs_att_color_value', true);
                } elseif ($attr_type == 'image') {
                    $image_id = get_term_meta($s->term_id, 'fs_att_image_value', true);
                    $attr_value = $image_id ? wp_get_attachment_url($image_id) : $s->name;
                }
                
                $attributes[] = [
                    'id' => $s->term_id,
                    'name' => apply_filters('the_title', $s->name),
                    'slug' => $s->slug,
                    'value' => apply_filters('the_title', $attr_value),
                    'type' => $attr_type ? $attr_type : 'text',
                ];
            }
        }
        
        if (empty($attributes)) {
            continue;
        }
        
        if ($args['grouped']) {
            // Grouped structure with parent name
            $result[] = [
                'parent_id' => $primary_term->term_id,
                'parent_name' => apply_filters('the_title', $primary_term->name),
                'parent_slug' => $primary_term->slug,
                'position' => $parent_data['position'],
                'attributes' => $attributes,
            ];
        } else {
            // Flat structure: parent name as key, attributes as value
            $result[] = [
                'parent_id' => $primary_term->term_id,
                'parent_name' => apply_filters('the_title', $primary_term->name),
                'parent_slug' => $primary_term->slug,
                'position' => $parent_data['position'],
                'values' => array_map(function($attr) {
                    return $attr['name'];
                }, $attributes),
                'attributes' => $attributes,
            ];
        }
    }
    
    return $result;
}

/**
 * Получает информацию обо всех зарегистрированных размерах картинок.
 *
 * @param bool [$unset_disabled=true] Удалить из списка размеры с 0 высотой и шириной?
 *
 * @return array данные всех размеров
 *
 * @global $_wp_additional_image_sizes
 *
 * @uses   get_intermediate_image_sizes()
 */
function fs_get_image_sizes($unset_disabled = true)
{
    $wais = &$GLOBALS['_wp_additional_image_sizes'];

    $sizes = [];

    foreach (get_intermediate_image_sizes() as $_size) {
        if (in_array($_size, ['thumbnail', 'medium', 'medium_large', 'large'])) {
            $sizes[$_size] = [
                'width' => get_option("{$_size}_size_w"),
                'height' => get_option("{$_size}_size_h"),
                'crop' => (bool) get_option("{$_size}_crop"),
            ];
        } elseif (isset($wais[$_size])) {
            $sizes[$_size] = [
                'width' => $wais[$_size]['width'],
                'height' => $wais[$_size]['height'],
                'crop' => $wais[$_size]['crop'],
            ];
        }

        // size registered, but has 0 width and height
        if ($unset_disabled && ($sizes[$_size]['width'] == 0) && ($sizes[$_size]['height'] == 0)) {
            unset($sizes[$_size]);
        }
    }

    return $sizes;
}

/**
 * Возвращает массив состоящий id прикреплённых к посту вложений.
 *
 * @param int  $post_id   -ID поста
 * @param bool $thumbnail -включать ли миниатюру в галерею,
 *                        если да, то миниатюра будет выведена первым изображением
 *
 * @return array
 */
function fs_gallery_images_ids($post_id = 0, $thumbnail = true)
{
    global $post;
    $fs_config = new FS_Config();
    $post_id = !empty($post_id) ? $post_id : $post->ID;
    $fs_gallery = get_post_meta($post_id, $fs_config->meta['gallery'], false);
    $gallery = [];
    $post_thumbnail_id = get_post_thumbnail_id($post_id);
    if ($post_thumbnail_id && $thumbnail) {
        $gallery[] = $post_thumbnail_id;
    }

    if (!empty($fs_gallery['0'])) {
        foreach ($fs_gallery['0'] as $item) {
            if (wp_get_attachment_image($item)) {
                $gallery[] = $item;
            }
        }
    }

    return $gallery;
}

/**
 * Выводит миниатюру товара, если миниатюра не установлена-заглушку.
 *
 * @param int    $product_id ID товара (поста)
 * @param string $size       размер миниатюры
 * @param array  $args       html атрибуты, типа класс, id
 *
 * @return false|string
 */
function fs_product_thumbnail($product_id = 0, $size = 'thumbnail', $args = [])
{
    $product_id = fs_get_product_id($product_id);
    $args = wp_parse_args($args, [
        'ignore_thumbnail' => false,
        'class' => '',
    ]);

    $img_class = new FS\FS_Images_Class();
    $gallery = $img_class->get_gallery($product_id, !$args['ignore_thumbnail']);
    if (has_post_thumbnail($product_id) && !$args['ignore_thumbnail'] && get_the_post_thumbnail($product_id, $size, $args)) {
        echo get_the_post_thumbnail($product_id, $size, $args);
    } elseif (count($gallery)) {
        $attach_id = array_shift($gallery);
        echo wp_get_attachment_image($attach_id, $size, false, $args);
    } else {
        echo '<img src="'.esc_url(FS_PLUGIN_URL.'assets/img/no-image.jpg').'" alt="'.esc_attr__('No image', 'f-shop').'">';
    }
}

/**
 * Возвращает ссылку на миниатюру товара.
 *
 * @param int    $product_id
 * @param string $size
 *
 * @return false|string|null
 */
function fs_get_product_thumbnail_url($product_id = 0, $size = 'thumbnail')
{
    $img_class = new FS\FS_Images_Class();
    $gallery = $img_class->get_gallery($product_id);
    $url = null;
    if (has_post_thumbnail($product_id)) {
        $url = get_the_post_thumbnail_url($product_id, $size);
    } elseif (count($gallery)) {
        $attach_id = array_shift($gallery);
        $url = wp_get_attachment_image_url($attach_id, $size, false);
    } else {
        $url = FS_PLUGIN_URL.'assets/img/image.svg';
    }

    return $url;
}

/**
 * Создаёт ссылку для отфильтровки товаров по параметрам в каталоге.
 *
 * @param array       $query        строка запроса
 * @param string|null $catalog_link ссылка на страницу на которой отобразить результаты
 */
function fs_filter_link($query = [], $catalog_link = null)
{
    if (!$catalog_link && is_tax(FS_Config::get_data('product_taxonomy'))) {
        $catalog_link = get_term_link(get_queried_object_id());
    }

    $query = wp_parse_args($query, [
        'fs_filter' => wp_create_nonce('f-shop'),
        'echo' => true,
    ]);

    // устанавливаем базовый путь без query_string
    $catalog_link = $catalog_link ?: get_post_type_archive_link(FS_Config::get_data('post_type'));

    $url = add_query_arg($query, $catalog_link);

    if (!$query['echo']) {
        return $url;
    }

    echo $url;
}

/**
 * Выводит список ссылок для сортировки по параметрам в каталоге.
 *
 * @param array $args массив дополнительных параметров
 */
function fs_order_by_links($args = [])
{
    $fs_config = new FS_Config();

    /** @var array $args список аргументов функции */
    $args = wp_parse_args($args, [
        'current_page' => get_post_type_archive_link($fs_config->data['post_type']), // ссылка на текущую страницу
        'before' => '', // код перед ссылкой
        'after' => '', // код после ссылки
        'exclude' => [], // исключенные ключи
    ]);

    /** @var array $order_by_keys содержит список ключей для GET запроса */
    $order_by_keys = $fs_config->get_orderby_keys();
    $html = '';
    $order_by = !empty($_GET['order_type']) ? $_GET['order_type'] : 'date_desc';

    if ($order_by_keys) {
        foreach ($order_by_keys as $key => $order_by_arr) {
            // исключаем GET параметр
            if ($args['exclude']) {
                if (in_array($key, $args['exclude'])) {
                    continue;
                }
            }
            // выводим код перед ссылкой
            if ($args['before']) {
                $html .= $args['before'];
            }
            // собственно одна из ссылок
            $html .= '<a href="';
            $html .= esc_url(
                add_query_arg(
                    [
                        'order_type' => $key,
                        'fs_filter' => wp_create_nonce('f-shop'),
                    ],
                    $args['current_page']
                )
            );
            $html .= '"';
            if ($order_by == $key) {
                $html .= ' class="active"';
            }
            $html .= '>';
            $html .= esc_html($order_by_arr['name']).'</a>';
            // выводим код после ссылки
            if ($args['after']) {
                $html .= $args['after'];
            }
        }
    }

    echo $html;
}

/**
 * Ищет в массиве $haystack значения массива $needles.
 *
 * @return bool если найдены все совпадения будет возвращено true иначе false
 */
function fs_in_array_multi($needles, $haystack)
{
    return !array_diff($needles, $haystack);
}

/**
 * Проверяет является ли товар вариативным
 *
 * @param int $product_id
 *
 * @return int
 */
function fs_is_variated($product_id = 0)
{
    $product_class = new FS_Product();

    return $product_class->is_variable_product($product_id);
}

/**
 * Возвращает цену вариации товара на основании её идентификатора.
 *
 * @param int $product_id
 * @param int $variation_id
 *
 * @return float
 */
function fs_get_variated_price($product_id = 0, $variation_id)
{
    $product_class = new FS_Product();

    return $product_class->get_variation_price($product_id, $variation_id);
}

/**
 * Find product variation by specified attributes.
 *
 * @param int   $product_id Product ID
 * @param array $attributes Array of attribute term IDs
 *
 * @return array|null Returns variation data or null if not found
 */
function fs_get_variation_by_attributes($product_id = 0, $attributes = [])
{
    if (empty($product_id) || empty($attributes) || !is_array($attributes)) {
        return null;
    }

    $product_id = fs_get_product_id($product_id);
    $attributes = array_map('intval', $attributes);

    $product = new FS_Product();
    $variations = $product->get_product_variations($product_id);

    if (empty($variations)) {
        return null;
    }

    // Поиск вариации, соответствующей выбранным атрибутам
    foreach ($variations as $variation_id => $variation) {
        if (empty($variation['attributes'])) {
            continue;
        }

        $variation_attributes = array_map('intval', $variation['attributes']);
        $intersect = array_intersect($attributes, $variation_attributes);

        // Проверяем, содержит ли вариация все выбранные атрибуты
        if (count($intersect) == count($attributes)) {
            return [
                'variation_id' => $variation_id,
                'variation' => $variation,
            ];
        }
    }

    return null;
}

/**
 * Выводит вариативную цену.
 *
 * @param int   $post_id
 * @param array $atts
 * @param array $args
 *
 * @return float
 */
function fs_variated_price($post_id = 0, $atts = [], $args = [])
{
    $post_id = fs_get_product_id($post_id);
    $args = wp_parse_args($args, [
        'count' => true,
        'format' => '%s <span>%s</span>',
        'echo' => true,
    ]);
    $price = fs_get_variated_price($post_id, $atts);
    $price = apply_filters('fs_price_format', $price);
    printf($args['format'], $price, fs_currency());

    return true;
}

/**
 * Получает минимальное количество вариативных покупаемых товаров.
 *
 * @param int   $post_id
 * @param array $atts
 *
 * @return float
 */
function fs_get_variated_count($post_id = 0, $atts = [])
{
    $post_id = fs_get_product_id($post_id);
    $atts = array_map('intval', $atts);
    $variants = get_post_meta($post_id, 'fs_variant', 0);
    $variants_count = get_post_meta($post_id, 'fs_variant_count', 0);
    $variant_count = 1;
    // если не включен чекбок "вариативный товар" , то возвращаем 1
    if (!fs_is_variated($post_id)) {
        return $variant_count;
    }

    if (!empty($variants[0])) {
        foreach ($variants[0] as $k => $variant) {
            // ищем совпадения варианов в присланными значениями
            if (!empty($variants_count) && count($variant) == count($atts) && fs_in_array_multi($atts, $variant)) {
                $variant_count = max($variants_count[0][$k], 1);
            }
        }
    }

    return intval($variant_count);
}

/**
 * Retrieves the product ID.
 *
 * @param int|object $product Product ID or product object. Defaults to 0.
 *
 * @return int Returns the product ID. Returns 0 if the product ID cannot be determined.
 */
function fs_get_product_id($product = 0)
{
    if (empty($product)) {
        global $post;
        if (isset($post) && isset($post->ID)) {
            $product = $post->ID;
        } else {
            return 0; // Або треба повернути значення за замовчуванням.
        }
    } elseif (is_object($product)) {
        $product = $product->ID;
    }

    return intval($product);
}

/**
 * Выводит метку об акции, популярном товаре, или недавно добавленом
 *
 * @param int   $product_id -уникальный ID товара (записи ВП)
 * @param array $labels     текст метки
 *
 * @return null
 */
function fs_product_label($product_id = 0, $labels = [])
{
    $product_id = fs_get_product_id($product_id);
    $product_fields = FS_Config::get_product_field();

    if (!$product_id || empty($product_fields) || !is_array($product_fields)) {
        return;
    }

    $labels = wp_parse_args($labels, [
        $product_fields['label_bestseller']['key'] => $product_fields['label_bestseller']['text'],
        $product_fields['label_promotion']['key'] => $product_fields['label_promotion']['text'],
        $product_fields['label_novelty']['key'] => $product_fields['label_novelty']['text'],
    ]);

    $format = '<span class="fs-label %s">%s</span>';

    if (fs_is_action($product_id)) {
        $class = str_replace('_', '-', $product_fields['label_promotion']['key']);
        printf($format, esc_attr($class), esc_html($labels[$product_fields['label_promotion']['key']]));
    } elseif (fs_is_bestseller($product_id)) {
        $class = str_replace('_', '-', $product_fields['label_bestseller']['key']);
        printf($format, esc_attr($class), esc_html($labels[$product_fields['label_bestseller']['key']]));
    } elseif (fs_is_novelty($product_id)) {
        $class = str_replace('_', '-', $product_fields['label_novelty']['key']);
        printf($format, esc_attr($class), esc_html($labels[$product_fields['label_novelty']['key']]));
    }
}

/**
 * Функция создаёт возможность изменения сообщения пользователю,
 * которое отсылается или показывается после осуществления покупкиэ
 * сообщение может содержать две переменные:
 *
 * @param $pay_method_id -ID выбраного метода оплаты
 *
 * @return mixed|void
 */
function fs_pay_user_message($pay_method_id)
{
    $message = get_term_meta(intval($pay_method_id), 'pay-message', 1);

    return apply_filters('fs_pay_user_message', $message, $pay_method_id);
}

/**
 * Возвращает массив разрешённых типов изображений для загрузки.
 *
 * @param string $return
 *
 * @return array|string
 */
function fs_allowed_images_type($return = 'array')
{
    $mime_types = get_allowed_mime_types();
    $mime = [];
    if ($mime_types) {
        foreach ($mime_types as $mime_type) {
            if (strpos($mime_type, 'image') === 0) {
                if ($return == 'json') {
                    $mime[$mime_type] = true;
                } else {
                    $mime[] = $mime_type;
                }
            }
        }
    }
    if ($return == 'json') {
        return json_encode($mime);
    } else {
        return $mime;
    }
}

/**
 * Выводит текст типа "Показано товаров 12 из 36".
 *
 * @param string $format
 */
function fs_items_on_page($format = '')
{
    global $wp_query;
    $found_posts = intval($wp_query->found_posts);
    $posts_per_page = intval($wp_query->query_vars['posts_per_page']);
    if ($posts_per_page > $found_posts) {
        $posts_per_page = $found_posts;
    }
    $format = empty($format) ? esc_html_e('Showing %1$d products from %2$d', 'f-shop') : $format;
    printf($format, $posts_per_page, $found_posts);
}

/**
 * Копирует папки и файлы и @param string $from из какой папки копировать файлы.
 *
 * @param string $to      куда копировать
 * @param bool   $rewrite
 *
 * @var $from в @var $to учитывая структуру
 */
function fs_copy_all($from, $to, $rewrite = true)
{
    if (is_dir($from) && file_exists($from)) {
        if (!file_exists($to)) {
            mkdir($to);
        }
        $d = dir($from);
        while (false !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            fs_copy_all("$from/$entry", "$to/$entry", $rewrite);
        }
        $d->close();
    } else {
        if (!file_exists($to) || $rewrite) {
            copy($from, $to);
        }
    }
}

/**
 * Возвращает мета данные о категории товара в виде объекта
 * price_max-максимальная цена
 * price_min-минимальная цена
 * sum-общая стоимость товаров
 * count-количество товаров в категории.
 *
 * @param int $category_id
 *
 * @return stdClass
 */
function fs_get_category_meta($category_id = 0)
{
    global $wpdb;
    $fs_config = new FS_Config();
    if (!$category_id) {
        $category_id = get_queried_object_id();
    }

    $meta = $wpdb->get_row($wpdb->prepare("SELECT MAX(CAST( meta_value AS UNSIGNED)) as price_max, MIN(CAST( meta_value AS UNSIGNED)) as price_min, SUM(CAST( meta_value AS UNSIGNED)) as sum, COUNT(post_id) as count FROM wp_postmeta WHERE post_id IN (SELECT object_id FROM  $wpdb->term_relationships WHERE term_taxonomy_id=%d AND object_id IN (SELECT ID from $wpdb->posts WHERE post_status='publish')) AND meta_key='%s'", $category_id, $fs_config->meta['price']));
    if (!$meta) {
        $meta = new stdClass();
        $meta->price_max = 0;
        $meta->price_min = 0;
        $meta->sum = 0;
        $meta->count = 0;
    }

    return $meta;
}

/**
 * Возвращает главное изображение категории.
 *
 * @param int    $term_id идентификатор термина категории
 * @param string $size    размер изображения
 * @param array  $args    дополнительные аргументы
 *
 * @return false|int|string
 */
function fs_get_category_image($term_id = 0, $size = 'thumbnail', $args = [])
{
    if (!$term_id) {
        $term_id = get_queried_object_id();
    }
    $args = wp_parse_args($args, [
        'return' => 'image',
        'attr' => [],
        'default' => FS_PLUGIN_URL.'assets/img/no-image.jpg',
    ]);
    $image_id = get_term_meta($term_id, '_thumbnail_id', 1);
    $image_id = intval($image_id);
    if ($args['return'] == 'image') {
        if (!$image_id) {
            $image = '<img src="'.esc_attr($args['default']).'" alt="no image">';
        } else {
            $image = wp_get_attachment_image($image_id, $size, false, $args['attr']);
        }
    } elseif ($args['return'] == 'url') {
        if (!$image_id) {
            $image = $args['default'];
        } else {
            $image = wp_get_attachment_image_url($image_id, $size, false);
        }
    } else {
        $image = $image_id;
    }

    return $image;
}

/**
 * Возвращает иконку категории.
 *
 * @param int    $term_id идентификатор термина категории
 * @param string $size    размер изображения
 * @param array  $args    дополнительные аргументы
 *
 * @return false|int|string
 */
function fs_get_category_icon($term_id = 0, $size = 'thumbnail', $args = [])
{
    if (!$term_id) {
        $term_id = get_queried_object_id();
    }
    $image = null;

    $args = wp_parse_args($args, [
        'return' => 'image',
        'attr' => [],
        'default' => FS_PLUGIN_URL.'assets/img/add-img.svg',
    ]);

    $image_id = (int) get_term_meta($term_id, '_icon_id', 1);

    if ($args['return'] == 'image') {
        if ($image_id) {
            $image = wp_get_attachment_image($image_id, $size, false, $args['attr']);
        } elseif (!$image_id && !empty($args['default'])) {
            $image = '<img '.fs_parse_attr(array_merge([
                'alt' => 'No image',
                'src' => $args['default'],
            ], $args['attr'])).'>';
        }
    } elseif ($args['return'] == 'url') {
        if ($image_id) {
            $image = wp_get_attachment_image_url($image_id, $size, false);
        } elseif (!$image_id && $args['default']) {
            $image = $args['default'];
        }
    }

    return $image;
}

/**
 * Отображает налоги в виде списка.
 *
 * @param array $args  список аргументов
 * @param float $total -сумма от которой считаются налоги
 *
 * @return mixed|void
 */
function fs_taxes_list($args = [], $total = 0.0)
{
    $args = wp_parse_args($args, [
        'taxonomy' => 'fs-taxes',
        'hide_empty' => false,
        'wrapper' => 'div',
        'wrapper_class' => 'fs-taxes-list',
        'format' => '<span>%name%(%value%)</span> <span>%cost% <span>%currency%</span></span>',
    ]);

    $terms = get_terms($args);
    if ($total == 0.0) {
        $total = fs_get_cart_cost();
    }

    if ($terms) {
        foreach ($terms as $term) {
            $tax = get_term_meta($term->term_id, '_fs_tax_value', 1);

            if (strpos($tax, '%')) {
                $tax_num = floatval(str_replace('%', '', $tax));
                $tax_amount = $total * $tax_num / 100;
                $tax_amount = apply_filters('fs_price_format', $tax_amount);
            } else {
                $tax_amount = apply_filters('fs_price_format', floatval($tax));
            }

            $taxes_html = '';
            if ($args['wrapper']) {
                $taxes_html = '<'.esc_attr($args['wrapper']).' data-fs-element="taxes-list" class="'.esc_attr($args['wrapper_class']).'">';
            }

            $replace = [
                '%name%' => esc_attr($term->name),
                '%value%' => esc_attr($tax),
                '%cost%' => esc_attr($tax_amount),
                '%currency%' => esc_attr(fs_currency()),
            ];
            $taxes_html .= str_replace(array_keys($replace), array_values($replace), $args['format']);

            if ($args['wrapper']) {
                $taxes_html .= '</'.esc_html($args['wrapper']).'>';
            }

            echo apply_filters('fs_taxex_list', $taxes_html);
        }
    }
}

/**
 * Debugging feature for easy debugging.
 *
 * @param mixed  $data       передаваемые данные
 * @param string $before
 * @param string $debug_type какую функцию для отладки использовать,
 * @param bool   $exit       прекратить выполнение кода далее
 *                           по умолчанию var_dump
 */
function fs_debug_data($data, $before = '', $debug_type = 'var_dump', $exit = false)
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $backtrace = debug_backtrace();
    echo '<pre>';
    printf('=== DEBUG: %s ===<br>', $before);
    if (!empty($backtrace[0]['file']) && !empty($backtrace[0]['line'])) {
        printf('=== FILE: "%s:%d" ===<br>', $backtrace[0]['file'], $backtrace[0]['line']);
    }
    if ($debug_type == 'var_dump') {
        var_dump($data);
    } elseif ($debug_type == 'print_r') {
        print_r($data);
    }
    echo "=== END DEBUG $before ===<br>";
    echo '</pre>';
    if ($exit) {
    }
}

/**
 * Displays a list of variations on the product page, output in the form of stylized input radio.
 *
 * @param int   $product_id
 * @param array $args
 */
function fs_list_variations($product_id = 0, $args = [])
{
    $fs_config = new FS_Config();
    $args = wp_parse_args($args, [
        'class' => 'fs-select-variation',
        'show_price' => true,
        'show_name' => true,
        'show_sku' => false,
    ]);
    $product_id = fs_get_product_id($product_id);
    $product = new FS_Product();
    $variations = $product->get_product_variations($product_id);
    if (!empty($variations)) {
        echo '<ul class="'.esc_attr($args['class']).'">';
        $count = 0;
        foreach ($variations as $var_id => $variation) {
            echo '<li class="radiobtn">';
            echo '<input type="radio" name="fs_variation" data-max="'.esc_attr($variation['count']).'" data-fs-element="select-variation" data-product-id="'.esc_attr($product_id).'" value="'.esc_attr($var_id).'" '.checked(0, $count, 0).' id="fs-var-'.esc_attr($var_id).'">';
            echo '<label for="fs-var-'.esc_attr($var_id).'">';
            // Показываем название в зависимости от настроек
            if ($args['show_name'] && !empty($variation['name'])) {
                echo '<span class="fs-variant-name">'.esc_html($variation['name']).'</span>';
            }
            // Показываем артикул в зависимости от настроек
            if ($args['show_sku'] && !empty($variation['sku'])) {
                echo '<span class="fs-variant-sku  fs-var-container">('.esc_attr($variation['sku']).')</span>';
            }
            if (!empty($variation['attr'])) {
                foreach ($variation['attr'] as $attr) {
                    if (empty($attr)) {
                        continue;
                    }
                    $term = get_term($attr, $fs_config->data['features_taxonomy']);
                    $term_parent_name = get_term_field('name', $term->parent, $fs_config->data['features_taxonomy']);
                    $att_type = get_term_meta($term->term_id, 'fs_att_type', 1);
                    $att_show = $term->name;
                    if ($att_type == 'image') {
                        $image_id = get_term_meta($term->term_id, 'fs_att_image_value', 1);
                        if ($image_id) {
                            $image_url = wp_get_attachment_image_url($image_id, 'full');
                            $att_show = '<span class="fs-attr-image" style="background-image:url('.esc_url($image_url).');"></span>';
                        }
                    } elseif ($att_type == 'color') {
                        $color = get_term_meta($term->term_id, 'fs_att_color_value', 1);
                        if ($color) {
                            $att_show = '<span class="fs-attr-color" style="background-color:'.esc_attr($color).';"></span>';
                        }
                    }
                    echo '<span class="fs-inline-flex align-items-center fs-var-container">'.esc_html($term_parent_name).': '.$att_show.'</span> ';
                }
            }
            // Если включено показывать цену
            if ($args['show_price']) {
                if (!empty($variation['action_price']) && $variation['price'] > $variation['action_price']) {
                    $price = apply_filters('fs_price_filter', $variation['price'], $product_id);
                    $price = apply_filters('fs_price_format', $price);
                    $action_price = apply_filters('fs_price_filter', $variation['action_price'], $product_id);
                    $action_price = apply_filters('fs_price_format', $action_price);
                    echo '<span class="fs-inline-flex align-items-center fs-variation-price fs-var-container">'.sprintf('%s <span>%s</span>', esc_attr($action_price), esc_attr(fs_currency())).'</span>';
                    echo '<del class="fs-inline-flex align-items-center fs-variation-price fs-var-container">'.sprintf('%s <span>%s</span>', esc_attr($price), esc_attr(fs_currency())).'</del>';
                } else {
                    $price = apply_filters('fs_price_filter', $variation['price'], $product_id);
                    $price = apply_filters('fs_price_format', $price);
                    echo '<span class="fs-inline-flex align-items-center fs-variation-price fs-var-container">'.sprintf('%s <span>%s</span>', esc_attr($price), esc_attr(fs_currency())).'</span>';
                }
            }
            echo '</label></li>';
            ++$count;
        }
        echo '</ul>';
    }
}

/**
 * Sets the product data passed in the $ product parameter.
 *
 *  массив должен состоять как миннимум из двух значений:
 *      $product['ID'] -  ID товара
 *      $product['variation'] -  ID вариации товара
 *  остальные тоже передаются по возможности
 *
 * @param array $product
 * @param int   $item_id
 *
 * @return FS_Product
 */
function fs_set_product($product = [], $item_id = 0)
{
    $product_class = new FS_Product();
    $product_class->set_product($product, $item_id);

    return $product_class;
}

/**
 * Устанавливает данные заказа.
 *
 * @return FS_Order
 */
function fs_set_order(WP_Post $post)
{
    return new FS_Order($post->ID);
}

/**
 * Enables plugin template.
 *
 * сначала идет поиск в директории f-shop текущей темы
 * далее ищем дефолтный шаблон в папке плагина "templates/front-end"
 * !!! подключаемый шаблон должен иметь расширение *.php
 *
 * @param string $template_path путь к шаблону (без расширения)
 */
function fs_load_template($template_path)
{
    $base_template = FS_PLUGIN_NAME.DIRECTORY_SEPARATOR.$template_path;
    if (file_exists(get_template_directory().DIRECTORY_SEPARATOR.$base_template.'.php')) {
        get_template_part($base_template);
    } elseif (file_exists(FS_PLUGIN_PATH.'templates/front-end/'.$template_path.'.php')) {
        load_template(FS_PLUGIN_PATH.'templates/front-end/'.$template_path.'.php', false);
    } else {
        esc_html_e('File "%s" not found', 'f-shop');
    }
}

/**
 * Возвращает все методы доставки
 * обертка функции get_terms().
 *
 * @return array|int|WP_Error
 */
function fs_get_shipping_methods($args = [])
{
    $args = wp_parse_args($args, [
        'taxonomy' => FS_Config::get_data('product_del_taxonomy'),
        'hide_empty' => false,
    ]);
    return get_terms($args);
}

function fs_get_payment_methods()
{
    return get_terms([
        'taxonomy' => FS_Config::get_data('product_pay_taxonomy'),
        'hide_empty' => false,
    ]);
}

/**
 * Returns true if the item has a label specified in the $label parameter.
 *
 * вы можете указать следующие значения
 * 'pop' - если  в админке установлен чекбокс на "Включить метку "Хит продаж""
 * 'new' - если  в админке установлен чекбокс на "Включить метку "Включить метку "Новинка""
 * 'promo' - если  в админке установлен чекбокс на "Включить метку "Акция""
 *
 * @return bool
 */
function fs_is_label($label)
{
    global $post;
    $product_id = $post->ID;
    $label_show = false;
    switch ($label) {
        case 'pop':
            $label_show = get_post_meta($product_id, 'fs_on_bestseller', true) ? true : false;
            break;
        case 'new':
            $label_show = get_post_meta($product_id, 'fs_on_novelty', true) ? true : false;
            break;
        case 'promo':
            $label_show = get_post_meta($product_id, 'fs_on_promotion', true) ? true : false;
            break;
    }

    return $label_show;
}

/**
 * Возвращает стоимость доставки в корзине.
 *
 * @return float $cost
 */
function fs_get_delivery_cost($delivery_method = 0)
{
    $fs_config = new FS_Config();

    $delivery_methods = get_terms([
        'taxonomy' => $fs_config->data['product_del_taxonomy'],
        'hide_empty' => false,
    ]);

    $cost = 0.0;

    if ($delivery_method) {
        $cost = get_term_meta($delivery_method, '_fs_delivery_cost', 1);
    } elseif (!is_wp_error($delivery_methods) && count($delivery_methods)) {
        $term_id = intval($delivery_methods[0]->term_id);
        $cost = get_term_meta($term_id, '_fs_delivery_cost', 1);
    }

    // Получаем чистую стоимость товаров (c учетом акционной цены)
    $amount = fs_get_cart_cost();

    // Если сумма товаров в корзине превышает указанную в настройке "fs_free_delivery_cost" то стоимость доставки равна 0
    if (fs_option('fs_free_delivery_cost') && $amount > fs_option('fs_free_delivery_cost')) {
        $cost = 0;
    }

    return floatval($cost);
}

/**
 * Выводит стоимость доставки в корзине.
 *
 * @param string $format
 */
function fs_delivery_cost($format = '%s <span>%s</span>', $delivery_method = 0)
{
    $cost = fs_get_delivery_cost($delivery_method);
    printf('<span data-fs-element="delivery-cost">'.$format.'</span>', esc_attr($cost), esc_html(fs_currency()));
}

/**
 * Displays the item rating block in the form of icons.
 *
 * @param int   $product_id
 * @param array $args
 */
function fs_product_rating($product_id = 0, $args = [])
{
    $product_id = fs_get_product_id($product_id);
    $product = new FS_Product();
    $product->product_rating($product_id, $args);
}

/**
 * Выводит усредненный рейтинг голосований по товару.
 *
 * @param int $product_id
 *
 * @return void
 */
function fs_product_average_rating($product_id = 0)
{
    $product_id = fs_get_product_id($product_id);
    $rating = FS_Product::get_average_rating($product_id);
    echo $rating > 0 ? $rating : 0;
}

/**
 * Displays the number of comments for a product.
 *
 * @param int  $product_id Product ID
 * @param bool $return     Whether to return or echo the count
 *
 * @return int|void Returns count if $return is true, otherwise echoes the count
 */
function fs_comments_count_number($product_id = 0, $return = false)
{
    $product_id = fs_get_product_id($product_id);
    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1'", $product_id));

    $count = apply_filters('fs_product_comments_count_number', $count, $product_id);

    if ($return) {
        return (int) $count;
    }

    echo (int) $count;
}

/**
 * Выводит к-во комментариев к товару.
 *
 * @param int $product_id
 */
function fs_comments_count($product_id = 0, $return = false)
{
    $product_id = fs_get_product_id($product_id);
    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1'", $product_id));

    $count = apply_filters('fs_product_comments_count', $count, $product_id);

    if ($count == 0) {
        if ($return) {
            return esc_html__('No reviews', 'f-shop');
        }
        esc_html_e('No reviews', 'f-shop');

        return;
    }

    $count_text = sprintf(
        /* translators: %s: number of reviews */
        _nx('%s review', '%s reviews', $count, 'reviews count', 'f-shop'),
        number_format_i18n($count)
    );

    if ($return) {
        return $count_text;
    }

    echo $count_text;
}

/**
 * Returns the average rating of the product.
 *
 * @param int $product_id Product ID
 *
 * @return float Product rating (0-5)
 */
function fs_get_product_rating($product_id = 0)
{
    $product_id = fs_get_product_id($product_id);
    $rating = FS_Product::get_average_rating($product_id);

    return $rating > 0 ? (float) $rating : 0;
}

if (!function_exists('fs_get_category_text')) {
    /**
     * Returns the product category text (supports WP Multilang format).
     *
     * @param int $category_id
     */
    function fs_get_category_text($category_id = 0)
    {
        if (!$category_id && is_tax()) {
            $category_id = get_queried_object_id();
        }

        // Try WP Multilang format first (field without suffix, WP Multilang handles translation)
        $content = get_term_meta($category_id, '_content', 1);
        
        // Fallback to old suffix format if empty
        if (empty($content)) {
            $content = fs_get_term_meta('_content', $category_id, 1);
        }

        return apply_filters('the_content', $content);
    }
}

/**
 * Displays a link to reset the filters.
 *
 * @param string $base_url url to return when pressed
 */
function fs_reset_filter_link($base_url = '')
{
    $fs_config = new FS_Config();
    if (empty($base_url) && is_tax()) {
        $base_url = get_term_link(get_queried_object_id());
    } else {
        $base_url = get_post_type_archive_link($fs_config->data['post_type']);
    }
    echo esc_url($base_url);
}

if (!function_exists('fs_phpinfo_to_array')) {
    /**
     * Converts phpinfo data to an array.
     *
     * @return array
     */
    function fs_phpinfo_to_array()
    {
        $entitiesToUtf8 = function ($input) {
            // http://php.net/manual/en/function.html-entity-decode.php#104617
            return preg_replace_callback('/(&#[0-9]+;)/', function ($m) {
                return mb_convert_encoding($m[1], 'UTF-8', 'HTML-ENTITIES');
            }, $input);
        };
        $plainText = function ($input) use ($entitiesToUtf8) {
            return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
        };
        $titlePlainText = function ($input) use ($plainText) {
            return '# '.$plainText($input);
        };

        ob_start();
        phpinfo(-1);

        $phpinfo = ['phpinfo' => []];

        // Strip everything after the <h1>Configuration</h1> tag (other h1's)
        if (!preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches)) {
            return [];
        }

        $input = $matches[1];
        $matches = [];

        if (preg_match_all(
            '#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|'.
                '(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
            $input,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                $fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
                if (strlen($match[1])) {
                    $phpinfo[$match[1]] = [];
                } elseif (isset($match[3])) {
                    $keys1 = array_keys($phpinfo);
                    $phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? [
                        $fn($match[3]),
                        $fn($match[4]),
                    ] : $fn($match[3]);
                } else {
                    $keys1 = array_keys($phpinfo);
                    $phpinfo[end($keys1)][] = $fn($match[2]);
                }
            }
        }

        return $phpinfo;
    }
}

function fs_buy_one_click($product_id = 0, $text = 'Купить в 1 клик', $args = [])
{
    $product_id = fs_get_product_id($product_id);
    $atts = fs_parse_attr($args, [
        'type' => 'button',
        'class' => 'fs-buy-one-click',
        'data-fs-element' => 'buy-one-click',
        'data-id' => $product_id,
        'data-name' => get_the_title($product_id),
        'data-url' => get_the_permalink($product_id),
        'data-price' => apply_filters('fs_price_format', fs_get_price($product_id)),
        'data-currency' => fs_currency($product_id),
        'data-thumbnail' => fs_get_product_thumbnail_url($product_id, 'medium'),
    ]);
    echo '<button '.$atts.'>'.esc_html($text).'</button>';
}

/**
 * Getting a taxonomy field.
 *
 * @param int  $term_id
 * @param int  $type
 * @param bool $multilang
 */
function fs_get_term_meta(string $meta_key, $term_id = 0, $type = 1, $multilang = true)
{
    $term_id = $term_id ?: get_queried_object_id();
    $meta_key = $multilang ? $meta_key.'__'.mb_strtolower(get_locale()) : $meta_key;

    return get_term_meta($term_id, $meta_key, $type);
}

/**
 * Выводит или возвращает системное сообщение.
 *
 * @param        $title  заголовок сообщения
 * @param        $text   текст сообщения
 * @param string $status статус сообщения: info, success, error, warning
 * @param array  $args   дополнительные аргументы
 *
 * @return string
 */
function fs_action_message($title, $text, $status = 'info', $args = [])
{
    $args = wp_parse_args($args, [
        'icon' => '<img src="'.esc_url(FS_PLUGIN_URL.'assets/img/icon/info-'.$status.'.svg').'" alt="icon">',
        'class' => 'fs-action-message fs-action-'.$status,
        'echo' => true,
        'button' => null,
    ]);

    $html = '<div class="'.esc_attr($args['class']).'">';
    $html .= '<div class="fs-action-message__left">';
    $html .= $args['icon'];
    $html .= '</div>';
    $html .= '<div class="fs-action-message__right">';
    $html .= '<h4>'.esc_html($title).'</h4>';
    $html .= '<p>'.esc_html($text).'</p>';

    if ($args['button']) {
        $html .= $args['button'];
    }

    $html .= '</div>';
    $html .= '</div>';

    if (!$args['echo']) {
        return apply_filters('fs_action_message', $html);
    }

    echo apply_filters('fs_action_message', $html);
}

/**
 * Возвращает cross sell товары в виде объекта.
 *
 * @param int $product_id
 * @param int $limit
 *
 * @return WP_Query
 */
function fs_get_cross_sells($product_id = 0, $limit = -1)
{
    $product_id = fs_get_product_id($product_id);
    $cross_sells = get_post_meta($product_id, FS_Config::get_meta('cross_sell', 0));

    if (!is_array($cross_sells) || empty($cross_sells)) {
        return null;
    }

    $cross_sells = array_shift($cross_sells);

    if (in_array($product_id, $cross_sells)) {
        $cross_sells = array_diff($cross_sells, [$product_id]);
    }

    return new WP_Query([
        'post_type' => FS_Config::get_data('post_type'),
        'posts_per_page' => $limit,
        'post__in' => $cross_sells,
    ]);
}

/**
 * Возвращает up sell товары в виде объекта.
 *
 * @param int $product_id
 * @param int $limit
 *
 * @return WP_Query
 */
function fs_get_up_sells($product_id = 0, $limit = -1)
{
    $product_id = fs_get_product_id($product_id);
    $up_sells = get_post_meta($product_id, FS_Config::get_meta('up_sell', 0));

    if (!is_array($up_sells) || empty($up_sells)) {
        return new WP_Query();
    }

    $up_sells = array_shift($up_sells);

    if (in_array($product_id, $up_sells)) {
        $up_sells = array_diff($up_sells, [$product_id]);
    }

    return new WP_Query([
        'post_type' => FS_Config::get_data('post_type'),
        'posts_per_page' => $limit,
        'post__in' => $up_sells,
    ]);
}

if (!function_exists('fs_convert_cyr_name')) {
    /**
     * Converts a string from Cyrillic to Latin.
     *
     * @return string
     */
    function fs_convert_cyr_name($str)
    {
        $tr = [
            'А' => 'a',
            'Б' => 'b',
            'В' => 'v',
            'Г' => 'g',
            'Д' => 'd',
            'Е' => 'e',
            'Ё' => 'yo',
            'Ж' => 'zh',
            'З' => 'z',
            'И' => 'i',
            'Й' => 'j',
            'К' => 'k',
            'Л' => 'l',
            'М' => 'm',
            'Н' => 'n',
            'О' => 'o',
            'П' => 'p',
            'Р' => 'r',
            'С' => 's',
            'Т' => 't',
            'У' => 'u',
            'Ф' => 'f',
            'Х' => 'kh',
            'Ц' => 'c',
            'Ч' => 'ch',
            'Ш' => 'sh',
            'Щ' => 'shch',
            'Ъ' => '',
            'Ы' => 'y',
            'Ь' => '',
            'Э' => 'e',
            'Ю' => 'yu',
            'Я' => 'ya',
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'yo',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'kh',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shch',
            'ъ' => '',
            'ы' => 'y',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            ' ' => '-',
            '.' => '',
            ',' => '',
            '/' => '-',
            ':' => '',
            ';' => '',
            '—' => '',
            '–' => '-',
        ];

        return strtr($str, $tr);
    }
}

if (!function_exists('fs_is_product')) {
    /**
     * Is_product - Returns true when viewing a single product.
     *
     * @return bool
     */
    function fs_is_product()
    {
        return is_singular(['product']);
    }
}

if (!function_exists('fs_form_submit')) {
    /**
     * Displays a submit button of any form.
     *
     * @param string $text
     * @param array  $args
     *
     * @return string
     */
    function fs_form_submit($text = 'Send', $args = [])
    {
        $args = wp_parse_args($args, [
            'class' => 'btn btn-success btn-lg',
        ]);

        echo '<button type="submit" '.fs_parse_attr($args).'>'.$text.'</button>';
    }
}

if (!function_exists('fs_localize_meta_key')) {
    /**
     * Localizes the meta field key.
     *
     * @param string $meta_key
     *
     * @return string
     */
    function fs_localize_meta_key($meta_key = '')
    {
        if (fs_option('fs_multi_language_support')) {
            $meta_key = $meta_key.'__'.mb_strtolower(get_locale());
        }

        return $meta_key;
    }
}

/**
 * Колбек функция для вывода единчного комментария.
 */
function fs_comment_single($comment, $args, $depth)
{
    $user = get_user_by('id', $comment->user_id);
    echo fs_frontend_template('product/tabs/comment-list-item', [
        'vars' => compact('comment', 'args', 'depth', 'user'),
    ]);
}

function fs_list_product_reviews($product_id = null, $args = [])
{
    $product_id = fs_get_product_id($product_id);
    $args = wp_parse_args($args, [
        'post_id' => $product_id,
        'status' => 'approve',
    ]);
    $comments = get_comments($args);
    echo fs_frontend_template('product/comments', [
        'vars' => compact('comments'),
    ]);
}

if (!function_exists('fs_get_user_ip')) {
    /**
     * Returns the IP address of the current visitor.
     *
     * @return mixed|string
     */
    function fs_get_user_ip()
    {
        $value = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $value = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $value = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $value = $_SERVER['REMOTE_ADDR'];
        }

        return $value;
    }
}

if (!function_exists('fs_localize_category_url')) {
    /**
     * Локализирует урл категории товара с учетом настроек
     * если не указан параметр $locale то используется текущая локаль сайта.
     *
     * @param string $locale
     * @param array  $args
     *
     * @return string|void
     */
    function fs_localize_category_url($term_id, $locale = '', $args = [])
    {
        if (!$locale) {
            $locale = get_locale();
        }
        $taxonomy = FS_Config::get_data('product_taxonomy');
        $term = get_term($term_id, $taxonomy);
        $disable_slug = fs_option('fs_disable_taxonomy_slug');
        $args = wp_parse_args($args, [
            'prefixes' => [
                'uk' => 'ua',
                'ru_RU' => 'ru',
                'en_US' => 'en',
            ],
        ]);

        $prefix = FS_Config::default_locale() != $locale ? $args['prefixes'][$locale] : '';
        $slug = FS_Config::default_locale() != $locale && get_term_meta($term_id, '_seo_slug__'.mb_strtolower($locale), 1)
            ? get_term_meta($term_id, '_seo_slug__'.mb_strtolower($locale), 1) : $term->slug;

        $url_components = [$prefix];

        if (!$disable_slug) {
            array_push($url_components, $taxonomy);
        }
        array_push($url_components, $slug);

        return site_url(implode('/', $url_components).'/');
    }
}

/**
 * Удаляет параметр запроса или группу из урл.
 *
 * @param        $param - тут необходимо указать значение характеристики, категории и т.д которые будут удалены из фильтра
 * @param string $group - ключ массива удаляемого параметра(значения)
 * @param string $url   - урл над которым производится удаление, если не указан то $_SERVER['REQUEST_URI']
 *
 * @return string
 */
function fs_remove_url_param($param, $group = '', $url = '')
{
    $url = $url ?: $_SERVER['REQUEST_URI'];
    $query = parse_url($url);

    if (!isset($query['query'])) {
        return $url;
    }

    parse_str($query['query'], $output);

    if (!empty($group) && is_array($output[$group]) && $array_key = array_search($param, $output[$group])) {
        unset($output[$group][$array_key]);
    } elseif (empty($group) && isset($output[$param])) {
        unset($output[$param]);
    }

    return $query['path'].'?'.http_build_query($output);
}

/**
 * Checks if we are on the product category page.
 *
 * @return bool
 */
function fs_is_product_category()
{
    return !is_post_type_archive(FS_Config::get_data('post_type'))
        && is_tax(FS_Config::get_data('product_taxonomy'));
}

/**
 * Checks if we are currently on the page of the archive (catalog) of products.
 *
 * @return bool
 */
function fs_is_catalog()
{
    return is_post_type_archive(FS_Config::get_data('post_type')) && !is_tax(FS_Config::get_data('product_taxonomy'));
}

/**
 * Checks the presence of an order in the database by ID.
 *
 * @return bool
 */
function fs_order_exist($order_id)
{
    return get_post($order_id) && get_post_type($order_id) == FS_Config::get_data('post_type_orders');
}

function fs_get_currencies()
{
    $args = ['taxonomy' => 'fs-currencies', 'hide_empty' => false];
    $terms = get_terms($args);
    $options = [];
    foreach ($terms as $term) {
        if (!is_object($term)) {
            continue;
        }
        $options[$term->term_id] = $term->name;
    }

    return $options;
}

/**
 * Добавяет атрибуты к тегу <body>.
 *
 * @param array $data
 *
 * @return void
 */
function fs_body_open($data = [])
{
    $json = json_encode(array_merge([
        'cart' => [
            'items' => [],
            'total' => 0,
            'count' => 0,
        ],
    ], $data));
    $json = str_replace('"', "'", $json);
    ?>
    x-data="<?php echo esc_attr($json); ?>"
    x-on:fs-cart-updated.window="Alpine.store('FS')?.getCart().then(r=>cart=r.data)"
    x-init="Alpine.store('FS')?.getCart().then(r=>cart=r.data)"
<?php
}

/**
 * Выводит атрибуты для компонента Alpine.js
 * необходимо вставлять перед началом цикла единичного товара.
 *
 * @return void
 */
function fs_before_product_atts()
{
    $product_id = get_the_ID();
    $product = new FS_Product();
    $variation_attributes = $product->get_all_variation_attributes($product_id);
    $attributes = [];
    array_walk($variation_attributes, function ($value, $key) use (&$attributes) {
        $newKey = str_replace('-', '_', $value['slug']); // Пример преобразования ключа
        $attributes[$newKey] = $value['children'][0]['term_id'];
    });

    // Получаем ID первой вариации для инициализации
    $first_variation_id = fs_get_first_variation($product_id, 'key');

    echo ' x-data=\''.json_encode([
        'attributes' => $attributes,
        'count' => 1,
        'variationId' => 0, // Добавляем ID первой вариации
        'price_formatted' => apply_filters('fs_price_format', fs_get_price($product_id)),
        'old_price_formatted' => apply_filters('fs_price_format', fs_get_base_price($product_id)),
        'price' => fs_get_price($product_id),
        'old_price' => fs_get_base_price($product_id),
        'currency' => fs_currency($product_id),
    ]).' \'';

    echo ' x-init="async()=>{
      const store = Alpine.store(\'FS\');
      await store.loadProductVariations('.$product_id.');
    
      $watch(\'attributes\',async(val)=>{
        const result = await store.findVariation('.$product_id.', val);
        console.log(result);
        if (result) {
            variationId = result.variation_id;
        }

        store.calculatePrice('.$product_id.',val).then(r=>{
            if(r.success){
                price=r.data.price;
                sale_price=r.data.sale_price;
                price_formatted=r.data.price_formatted;
                old_price_formatted=r.data.old_price_formatted;
                price=r.data.price;
                old_price=r.data.old_price;
            }
        })
      })
    }
    " ';
}

/**
 * Checks whether a product is in a certain category.
 *
 * @return bool
 */
function fs_in_category($category_id, $post_id = null)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $categories = get_the_terms($post_id, FS_Config::get_data('product_taxonomy'));
    if (!$categories) {
        return false;
    }
    foreach ($categories as $category) {
        if ($category->term_id == $category_id) {
            return true;
        }
    }

    return false;
}
