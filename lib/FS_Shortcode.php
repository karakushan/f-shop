<?php
namespace FS;
use ES_LIB\ES_config;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Класс шорткодов магазина
 */
class FS_Shortcode
{

    protected $config;

    function __construct()
    {
        $this->config = new FS_Config();

        add_shortcode('fs_cart', array(&$this, 'cart_shortcode'));
        add_shortcode('fs_cart_widget', array(&$this, 'cart_widget'));
        add_shortcode('fs_order_info', array(&$this, 'single_order_info'));
        add_shortcode('fs_last_order_id', array(&$this, 'last_order_id'));
        add_shortcode('fs_last_order_amount', array(&$this, 'last_order_amount'));
        add_shortcode('fs_review_form', array(&$this, 'review_form'));
        add_shortcode('fs_checkout', array(&$this, 'checkout_form'));
        add_shortcode('fs_order_send', array(&$this, 'order_send'));
        add_shortcode('fs_user_cabinet', array(&$this, 'user_cabinet'));
        add_shortcode('fs_single_order', array(&$this, 'single_order'));
        add_shortcode('fs_register_form', 'fs_register_form');
        add_shortcode('fs_user_info', array($this, 'user_info'));
        add_shortcode('fs_user_orders', array($this, 'user_orders'));
        add_shortcode('fs_profile_edit', array($this, 'profile_edit'));


    }

    /**
     * виджет корзины товаров
     * @return [type] [description]
     */
    public function cart_widget()
    {
        ob_start();
        fs_cart_widget();
        $widget = ob_get_clean();
        return $widget;
    }

    //Шорткод для отображения купленных товаров и оформления покупки
    /**
     *
     */
    public function cart_shortcode()
    {


        $template_row_before = TEMPLATEPATH . '/fast-shop/cart/product-row-before.php';
        $plugin_row_before = FS_PLUGIN_PATH . 'templates/front-end/cart/product-row-before.php';

        $template_row = TEMPLATEPATH . '/fast-shop/cart/product-row.php';
        $plugin_row = FS_PLUGIN_PATH . 'templates/front-end/cart/product-row.php';

        $template_row_after = TEMPLATEPATH . '/fast-shop/cart/product-row-after.php';
        $plugin_row_after = FS_PLUGIN_PATH . 'templates/front-end/cart/product-row-after.php';

        $template_none_plugin = FS_PLUGIN_PATH . 'templates/front-end/cart/cart-empty.php';
        $template_none_theme = TEMPLATEPATH . '/fast-shop/cart/cart-empty.php';
        //получаем содержимое корзины (сессии)
        $carts = fs_get_cart();

        if ($carts) {
            if (file_exists($template_row_before)) {
                include($template_row_before);
            } else {
                include($plugin_row_before);
            }

            foreach ($carts as $id => $product) {
                $GLOBALS['product'] = $product;
                if (file_exists($template_row)) {
                    include($template_row);
                } else {
                    include($plugin_row);
                }
            }
            if (file_exists($template_row_after)) {
                include($template_row_after);
            } else {
                include($plugin_row_after);
            }
        } else {
            if (file_exists($template_none_theme)) {
                include($template_none_theme);
            } else {
                include($template_none_plugin);
            }
        }
    }

    //Шорткод показывает информацию о заказе
    public function single_order_info($order_id = '')
    {
        if ($order_id == '') $order_id = $_SESSION['last_order_id'];

        if (!isset($_SESSION['last_order_id'])) return;

        $order_id = (int)$_SESSION['last_order_id'];

        $order = new FS_Orders_Class();
        $delivery = new FS_Delivery_Class();
        $order_info = $order->get_order($order_id);

        $template_plugin = FS_PLUGIN_PATH . 'templates/front-end/shortcode/fs-order-info.php';
        $template_theme = TEMPLATEPATH . '/fast-shop/shortcode/fs-order-info.php';

        ob_start();
        if (file_exists($template_theme)) {
            include($template_theme);
        } else {
            include($template_plugin);
        }
        $code = ob_get_clean();

        return $code;

    }

//Возвращает id последнего заказа
    public function last_order_id()
    {
        $order_id = empty($_SESSION['last_order_id']) ? 0 : (int)$_SESSION['last_order_id'];
        return $order_id;
    }

    public function last_order_amount()
    {
        $order_id = empty($_SESSION['last_order_id']) ? 0 : (int)$_SESSION['last_order_id'];
        $order = new \FS\FS_Orders_Class;
        $order_info = $order->get_order_data($order_id);
        $summa = (float)$order_info->summa;
        $summa = apply_filters('fs_price_format', $summa);
        return $summa;
    }


    public function review_form()
    {
        global $fs_config;
        require $fs_config['plugin_path'] . 'templates/back-end/review-form.php';
    }

    function checkout_form()
    {
        global $fs_config;
        $checkout_form_theme = TEMPLATEPATH . '/fast-shop/checkout/checkout.php';
        $checkout_form_plugin = $fs_config['plugin_path'] . 'templates/front-end/checkout/checkout.php';
        if (file_exists($checkout_form_theme)) {
            include($checkout_form_theme);
        } else {
            include($checkout_form_plugin);
        }
    }

    /**
     * шорткод для отображения формы оформления заказа
     * @param array $atts атрибуты тега form
     * @return string
     */
    public function order_send($atts = array())
    {
        $prefix = 'order/order-form.php';
        extract(shortcode_atts(array(
            'order_type' => 'normal',
            'class' => 'order-send'
        ), $atts));
        $template = '
        <form action="#" name="fs-order-send" class="' . $class . '" method="POST">
            <div class="products_wrapper"></div>
            <input type="hidden" id="_wpnonce" name="_wpnonce" value="' . wp_create_nonce('fast-shop') . '">
            <input type="hidden" name="action" value="order_send">
            <input type="hidden" name="order_type" value="' . $order_type . '">

            ';
        if (file_exists($this->config->data['plugin_user_template'] . $prefix)) {

            ob_start();
            include($this->config->data['plugin_user_template'] . $prefix);
            $template .= ob_get_contents();
            ob_end_clean();


        } else {
            ob_start();
            include($this->config->data['plugin_template'] . $prefix);
            $template .= ob_get_contents();
            ob_end_clean();
        }
        $template .= '</form>';
        return $template;
    }

    function user_cabinet()
    {
        $user = wp_get_current_user();
        if (is_user_logged_in() && in_array('wholesale_buyer', $user->roles)) {
            $temp = fs_user_cabinet();
        } else {

            if (isset($_GET['fs-page']) && $_GET['fs-page'] == 'register') {
                if (is_user_logged_in()) {
                    $temp = fs_login_form();
                } else {
                    $temp = fs_register_form();
                }
            } else {
                $temp = fs_login_form();
            }


        }
        return $temp;
    }

    public function single_order($args)
    {
        $args = shortcode_atts(array(
            'product_id' => 0,
            'class' => ''
        ), $args);
        $template = '
        <form action="#" name="fs-order-send" class="' . $args['class'] . '" method="POST">
            <div class="products_wrapper"></div>
            <input type="hidden" id="_wpnonce" name="_wpnonce" value="' . wp_create_nonce('fast-shop') . '">
            <input type="hidden" name="action" value="order_send">
            <input type="hidden" name="order_type" value="single">';
        $template .= fs_frontend_template('order/single-order', $args);
        $template .= '</form>';
        return $template;
    }

    function user_info()
    {
        global $wpdb;
        $user = fs_get_current_user();
        $template = fs_frontend_template('cabinet/personal-info', array('user' => $user), true);
        return $template;
    }

    function user_orders()
    {
        global $wpdb;
        $user = fs_get_current_user();
        $orders = $wpdb->get_results("SELECT * FROM wp_fs_orders WHERE user_id='" . $user->ID . "' ORDER by id DESC");

        $template = fs_frontend_template('cabinet/orders', array('user' => $user, 'orders' => $orders), true);
        return $template;
    }

    function profile_edit()
    {
        $user = fs_get_current_user();
        $attr = array(
            'name' => 'fs-profile-edit',
            'method' => 'post'
        );
        $template = apply_filters('fs_form_header', $attr,'fs_profile_edit');
        $template.= fs_frontend_template('cabinet/profile-edit', array('user' => $user,'field'=>FS_Config::$user_meta));
        $template.= apply_filters('fs_form_bottom','');
        return $template;
    }
}