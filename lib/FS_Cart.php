<?php

namespace FS;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Класс для работы с корзиной.
 */
class FS_Cart
{
    public $cart;

    public function __construct()
    {
        // добавление товара в корзину
        add_action('wp_ajax_add_to_cart', [$this, 'add_to_cart_ajax']);
        add_action('wp_ajax_nopriv_add_to_cart', [$this, 'add_to_cart_ajax']);

        // Удаление товара из корзины ajax
        add_action('wp_ajax_fs_delete_cart_item', [$this, 'delete_cart_item']);
        add_action('wp_ajax_nopriv_fs_delete_cart_item', [$this, 'delete_cart_item']);

        // Удаление всех товаров из корзины ajax
        add_action('wp_ajax_fs_delete_cart', [$this, 'remove_cart_ajax']);
        add_action('wp_ajax_nopriv_fs_delete_cart', [$this, 'remove_cart_ajax']);

        // получаем содержимое корзины
        add_action('wp_ajax_fs_get_cart', [$this, 'fs_get_cart_callback']);
        add_action('wp_ajax_nopriv_fs_get_cart', [$this, 'fs_get_cart_callback']);

        // Update of cart items in cart
        add_action('wp_ajax_fs_change_cart_count', [$this, 'change_cart_item_count']);
        add_action('wp_ajax_nopriv_fs_change_cart_count', [$this, 'change_cart_item_count']);

        // полное удаление корзины
        add_action('fs_destroy_cart', [$this, 'destroy_cart']);

        // присваиваем переменной $cart содержимое корзины
        if (!empty($_SESSION['cart'])) {
            $this->cart = $_SESSION['cart'];
        }
    }

    /**
     * Updating the number of goods in the basket by ajax.
     */
    public function change_cart_item_count()
    {
        $item_id = intval($_POST['index']);
        $product_count = floatval($_POST['count']);
        if (!empty($_SESSION['cart'])) {
            $_SESSION['cart'][$item_id]['count'] = $product_count;
            $product_id = (int) $_SESSION['cart'][$item_id]['ID'];
            $sum = fs_get_price($product_id) * $product_count;
            wp_send_json_success([
                'sum' => apply_filters('fs_price_format', $sum) . ' ' . fs_currency(),
                'cost' => apply_filters('fs_price_format', fs_get_cart_cost()) . ' ' . fs_currency(),
                'total' => fs_get_total_amount() . ' ' . fs_currency(),
            ]);
        }
        wp_send_json_error();
    }

    /**
     * Получает шаблон корзины методом ajax
     * позволяет использовать пользователям отображение корзины в нескольких местах одновременно.
     */
    public function fs_get_cart_callback()
    {
        wp_send_json_success((array) self::get_cart_object());
    }

    /**
     * Получает объект корзины.
     *
     * @return \stdClass
     */
    public static function get_cart_object()
    {
        $cart_items = self::get_cart();

        $cart = new \stdClass();
        $cart->items = [];
        $cart->total = 0;
        $cart->count = 0;

        if (empty($cart_items)) {
            return $cart;
        }

        foreach ($cart_items as $key => $item) {
            $product = fs_set_product($item, $key);
            $cart->items[] = [
                'ID' => $item['ID'],
                'count' => (int) $item['count'],
                'attr' => $item['attr'],
                'variation' => $item['variation'],
                'product' => $product,
            ];
            $cart->total += $product->price * $item['count'];
            $cart->count += $item['count'];
        }

        $cart->total_display = apply_filters('fs_price_format', $cart->total) . ' ' . fs_currency();
        $cart->total = floatval($cart->total);

        return $cart;
    }

    /**
     * Подключает шаблон дополнительных полей доставки.
     */
    public static function show_shipping_fields()
    {
        echo '<div id="fs-shipping-fields">';
        fs_load_template('checkout/shipping-fields');
        echo '</div>';
    }

    /**
     * Adds an item to the cart.
     *
     * @param array $data
     *
     * @return bool|\WP_Error
     */
    public static function push_item($data = [])
    {
        if (empty($data['ID'])) {
            return new \WP_Error('fs_not_specified_id', __('Item ID not specified', 'f-shop'));
        }

        $data = wp_parse_args($data, [
            'ID' => $data['ID'],
            'count' => 1,
            'attr' => [],
            'variation' => null,
        ]);

        $cart = self::get_cart();
        $item_exists = false;

        // Проходим по корзине и проверяем наличие такого же товара
        foreach ($cart as $key => $item) {
            // Если товар вариативный, проверяем совпадение ID и variation
            if ($data['variation'] !== null) {
                if ($item['ID'] == $data['ID'] && $item['variation'] == $data['variation']) {
                    $cart[$key]['count'] += $data['count'];
                    $item_exists = true;
                    break;
                }
            }
            // Если товар не вариативный, проверяем только ID
            elseif ($item['ID'] == $data['ID'] && $item['variation'] === null) {
                $cart[$key]['count'] += $data['count'];
                $item_exists = true;
                break;
            }
        }

        // Если товар не найден в корзине, добавляем его
        if (!$item_exists) {
            array_push($cart, $data);
        }

        $_SESSION['cart'] = $cart;

        return true;
    }

    // ajax обработка добавления в корзину
    /**
     * Handles AJAX request for adding a product to the cart.
     * 
     * Processes the request data, validates it, and adds the product to the cart.
     * If a similar product already exists in the cart, it updates the quantity.
     * Supports product variations and attributes.
     * 
     * @return void Sends JSON response with success or error message
     */
    public function add_to_cart_ajax()
    {
        // Проверка nonce
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Security check failed', 'f-shop')]);
        }

        // Проверка на наличие count или установка значения по умолчанию 1
        if (!empty($_POST['count'])) {
            $count = intval($_POST['count']) > 0 ? intval($_POST['count']) : 1;
        }

        // Проверка на наличие product_id
        if (!is_numeric($_POST['post_id']) || empty($_POST['post_id'])) {
            wp_send_json_error(['msg' => __('Product ID not specified', 'f-shop')]);
        }

        $product_id = intval($_POST['post_id']);
        $attr = !empty($_POST['attr']) ? $_POST['attr'] : [];
        $product_class = new FS_Product();

        // Получение вариаций продукта
        $variation = null;
        $is_variated = false;
        $variation_id = 0;
        if ($product_class->is_variable_product($product_id) && is_numeric($_POST['variation_id'])) {
            $variation_id = intval($_POST['variation_id']);
            $variation = $product_class->get_variation($product_id, $variation_id);
            $is_variated = true;
        }

        self::push_item([
            'ID' => $product_id,
            'count' => $count,
            'attr' => $attr,
            'variation' => $variation_id,
        ]);

        // Получаем данные о товаре
        $product = get_post($product_id);

        if ($product) {
            // Базовая информация о товаре
            $product->name = apply_filters('the_title', $product->post_title);

            if ($is_variated && !empty($variation)) {
                $attributes = get_terms([
                    'taxonomy' => FS_Config::get_data('features_taxonomy'),
                    'include' => $variation['attributes'],
                ]);
                $product->name .= ' (' . implode(', ', array_map(function ($attribute) {
                    return $attribute->name;
                }, $attributes)) . ')';
            }

            // SKU или артикул товара
            if ($is_variated && !empty($variation) && !empty($variation['sku'])) {
                $product->sku = $variation['sku'];
            } else {
                $product->sku = fs_get_product_code($product_id);
            }

            // Цена товара
            $price = fs_get_price($product_id, $variation_id);
            $product->price = apply_filters('fs_price_format', $price);
            $product->price_raw = $price;

            // Валюта
            $product->currency = \fs_currency($product_id);

            // Изображение товара
            $thumbnail_id = get_post_thumbnail_id($product_id);
            if ($thumbnail_id) {
                $thumbnail = wp_get_attachment_image_src($thumbnail_id, 'medium');
                $product->thumbnail = $thumbnail ? $thumbnail[0] : '';
            } else {
                $product->thumbnail = '';
            }

            // URL товара
            $product->url = get_permalink($product_id);

            unset($product->post_content, $product->post_excerpt, $product->post_author, $product->post_date, $product->post_date_gmt, $product->post_name, $product->post_parent, $product->post_type, $product->post_mime_type, $product->comment_status, $product->ping_status, $product->post_password, $product->post_status, $product->comment_count);
        }

        wp_send_json_success([
            'product' => $product,
            'data' => $_POST,
        ]);
    }

    /**
     * Метод удаляет конкретный товар или все товары из корзины покупателя.
     *
     * @param int $cart_item
     *
     * @return bool|\WP_Error
     */
    public static function delete_item($cart_item = 0)
    {
        if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            return new \WP_Error(__METHOD__, __('Cart is empty', 'f-shop'));
        }

        unset($_SESSION['cart'][$cart_item]);

        return true;
    }

    /**
     * Destroys the cart completely.
     *
     * @return bool
     */
    public static function destroy_cart($except = [])
    {
        unset($_SESSION['cart']);

        return true;
    }

    /**
     * Destroys the cart completely via ajax.
     *
     * @return bool
     */
    public function remove_cart_ajax()
    {
        $remove = $this->destroy_cart();
        if ($remove) {
            wp_send_json_success(['message' => __('All items have been successfully removed from the cart.', 'f-shop')]);
        } else {
            wp_send_json_error(['message' => __('An error occurred while removing items from the cart or the cart is empty.', 'f-shop')]);
        }
    }

    /**
     * Удаляет одну позицию из корзины по индексу массива.
     *
     * @return void
     */
    public function delete_cart_item()
    {
        if (!is_numeric($_POST['index']) || !isset($_SESSION['cart'][$_POST['index']])) {
            wp_send_json_error(['message' => __('Index not specified', 'f-shop')]);
        }
        unset($_SESSION['cart'][$_POST['index']]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);

        wp_send_json_success(self::get_cart_object());
    }

    /**
     * Returns the cart.
     *
     * @return array
     */
    public static function get_cart()
    {
        return !empty($_POST['cart']) ? (array) $_POST['cart'] : (isset($_SESSION['cart']) ? (array) $_SESSION['cart'] : []);
    }

    /**
     * Checks if the cart is empty.
     *
     * @return bool
     */
    public static function has_empty()
    {
        $cart = self::get_cart();

        return count($cart) == 0;
    }

    /**
     * Устанавливает корзину.
     */
    public static function set_cart($cart)
    {
        $_SESSION['cart'] = $cart;
    }

    /**
     * Checks if an item is in the cart.
     *
     * @return bool
     */
    public static function contains($product_id = 0)
    {
        $ids = array_map(function ($item) {
            return $item['ID'];
        }, self::get_cart());

        return in_array($product_id, $ids);
    }
}
