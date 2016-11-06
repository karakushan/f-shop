<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
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
        add_shortcode('fs_order_info', array(&$this, 'single_order_info'));
        add_shortcode('fs_last_order_id', array(&$this, 'last_order_id'));
        add_shortcode('fs_review_form', array(&$this, 'review_form'));
        add_shortcode('fs_checkout', array(&$this, 'checkout_form'));
        add_shortcode('fs_order_send', array(&$this, 'order_send'));
        add_shortcode('fs_user_cabinet', array(&$this, 'user_cabinet'));


    }

    //Шорткод для отображения купленных товаров и оформления покупки
    /**
     *
     */
    public function cart_shortcode()
    {


        $template_row_before = TEMPLATEPATH . '/fast-shop/cart/product-row-before.php';
        $plugin_row_before = PLUGIN_PATH . 'templates/front-end/cart/product-row-before.php';

        $template_row = TEMPLATEPATH . '/fast-shop/cart/product-row.php';
        $plugin_row = PLUGIN_PATH . 'templates/front-end/cart/product-row.php';

        $template_row_after = TEMPLATEPATH . '/fast-shop/cart/product-row-after.php';
        $plugin_row_after = PLUGIN_PATH . 'templates/front-end/cart/product-row-after.php';

        $template_none_plugin = PLUGIN_PATH . 'templates/front-end/cart/cart-empty.php';
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

        if (!is_numeric($order_id)) return;

        $order = new FS_Orders_Class();
        $delivery = new FS_Delivery_Class();
        $order_info = $order->get_order($order_id);

        $info = "<table>
    	<tr>
    		<th>Ваше имя</th><td>" . $order_info->name . "</td>
    	</tr>
    	<tr>
    		<th>Электронная почта </th><td>" . $order_info->email . "</td>
    	</tr>
    	<tr>
    		<th>Номер телефона </th><td>" . $order_info->telephone . "</td>
    	</tr>
    	<tr>
    		<th>Способ доставки</th><td>" . $delivery->get_delivery($order_info->delivery) . "</td>
    	</tr>
    	<tr>
    		<th>Общая сумма</th><td>" . $order_info->summa . " " . get_option('currency_icon', '$') . "</td>
    	</tr>
    	<tr>
    		<th>Статус</th><td>" . $order->order_status[$order_info->status] . "</td>
    	</tr>
    </table>";
        return $info;

    }

//Возвращает id последнего заказа
    public function last_order_id()
    {
        $order_id = $_SESSION['last_order_id'];

        if (!is_numeric($order_id) || empty($order_id)) {
            return 0;
        } else {
            return $order_id;
        }
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
     */
    public function order_send($atts)
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
            $template .= $template_user_file;

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

        if (is_user_logged_in()) {
            $temp=fs_user_cabinet();
        } else {
            if (isset($_GET['fs-page']) && $_GET['fs-page']=='register') {
               $temp=fs_register_form();
            }else{
                $temp=fs_login_form();
            }

            
        }
        return $temp;
    }

}