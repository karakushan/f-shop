<?php

namespace FS;

use ES_LIB\ES_config;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Класс шорткодов магазина
 */
class FS_Shortcode {

	protected $config;

	function __construct() {
		$this->config = new FS_Config();

		add_shortcode( 'fs_cart', array( &$this, 'cart_shortcode' ) );
		add_shortcode( 'fs_cart_widget', array( &$this, 'cart_widget' ) );
		add_shortcode( 'fs_order_info', array( &$this, 'single_order_info' ) );
		add_shortcode( 'fs_last_order_id', array( &$this, 'last_order_id' ) );
		add_shortcode( 'fs_last_order_amount', array( &$this, 'last_order_amount' ) );
		add_shortcode( 'fs_review_form', array( &$this, 'review_form' ) );
		add_shortcode( 'fs_checkout', array( &$this, 'checkout_form' ) );
		add_shortcode( 'fs_order_send', array( &$this, 'order_send' ) );
		add_shortcode( 'fs_user_cabinet', array( &$this, 'user_cabinet' ) );
		add_shortcode( 'fs_single_order', array( &$this, 'single_order' ) );
		add_shortcode( 'fs_register_form', 'fs_register_form' );
		add_shortcode( 'fs_user_info', array( 'FS\Users_Class', 'user_info' ) );
		add_shortcode( 'fs_user_orders', array( $this, 'user_orders' ) );
		add_shortcode( 'fs_profile_edit', array( $this, 'profile_edit' ) );
		add_shortcode( 'fs_pay_methods', array( $this, 'pay_methods' ) );


	}

	/**
	 * виджет корзины товаров
	 * @return [type] [description]
	 */
	public function cart_widget() {
		ob_start();
		fs_cart_widget();
		$widget = ob_get_clean();

		return $widget;
	}

	//Шорткод для отображения купленных товаров и оформления покупки

	/**
	 *
	 */
	public function cart_shortcode() {


		$template_row_before = TEMPLATEPATH . '/fast-shop/cart/product-row-before.php';
		$plugin_row_before   = FS_PLUGIN_PATH . 'templates/front-end/cart/product-row-before.php';

		$template_row = TEMPLATEPATH . '/fast-shop/cart/product-row.php';
		$plugin_row   = FS_PLUGIN_PATH . 'templates/front-end/cart/product-row.php';

		$template_row_after = TEMPLATEPATH . '/fast-shop/cart/product-row-after.php';
		$plugin_row_after   = FS_PLUGIN_PATH . 'templates/front-end/cart/product-row-after.php';

		$template_none_plugin = FS_PLUGIN_PATH . 'templates/front-end/cart/cart-empty.php';
		$template_none_theme  = TEMPLATEPATH . '/fast-shop/cart/cart-empty.php';
		//получаем содержимое корзины (сессии)
		$carts = fs_get_cart();

		if ( $carts ) {
			if ( file_exists( $template_row_before ) ) {
				include( $template_row_before );
			} else {
				include( $plugin_row_before );
			}

			foreach ( $carts as $id => $product ) {
				$GLOBALS['product'] = $product;
				if ( file_exists( $template_row ) ) {
					include( $template_row );
				} else {
					include( $plugin_row );
				}
			}
			if ( file_exists( $template_row_after ) ) {
				include( $template_row_after );
			} else {
				include( $plugin_row_after );
			}
		} else {
			if ( file_exists( $template_none_theme ) ) {
				include( $template_none_theme );
			} else {
				include( $template_none_plugin );
			}
		}
	}

	/**
	 * Шорткод показывает информацию о заказе
	 *
	 * @param $atts
	 *
	 * @return mixed|void
	 */
	public function single_order_info( $atts ) {
		if ( ! isset( $_REQUEST['order_detail'] ) ) {
			return;
		}
		$order_id = intval( $_REQUEST['order_detail'] );
		// белый список параметров и значения по умолчанию
		$atts = shortcode_atts( array(
			'class'    => 'fs-order-info',
			'order_id' => $order_id,
			'order'    => FS_Orders_Class::get_order( $order_id ),
			'payment'  => new FS_Payment_Class()

		), $atts );

		if ( empty( $order_id ) ) {
			return '<p class="fs-order-detail">' . __( 'Details of this order are not available for you', 'fast-shop' ) . '</p>';
		}

		return fs_frontend_template( 'shortcode/fs-order-info', $atts );

	}

//Возвращает id последнего заказа
	public function last_order_id() {
		$order_id = empty( $_SESSION['last_order_id'] ) ? 0 : (int) $_SESSION['last_order_id'];

		return $order_id;
	}

	public function last_order_amount() {
		$order_id   = empty( $_SESSION['last_order_id'] ) ? 0 : (int) $_SESSION['last_order_id'];
		$order      = new \FS\FS_Orders_Class;
		$order_info = $order->get_order_data( $order_id );
		$summa      = (float) $order_info->summa;
		$summa      = apply_filters( 'fs_price_format', $summa );

		return $summa;
	}


	public function review_form() {
		global $fs_config;
		require $fs_config['plugin_path'] . 'templates/back-end/review-form.php';
	}

	function checkout_form() {
		global $fs_config;
		$checkout_form_theme  = TEMPLATEPATH . '/fast-shop/checkout/checkout.php';
		$checkout_form_plugin = $fs_config['plugin_path'] . 'templates/front-end/checkout/checkout.php';
		if ( file_exists( $checkout_form_theme ) ) {
			include( $checkout_form_theme );
		} else {
			include( $checkout_form_plugin );
		}
	}

	/**
	 * шорткод для отображения формы оформления заказа
	 *
	 * @param array $atts атрибуты тега form
	 *
	 * @return string
	 */
	public function order_send( $atts = array() ) {
		$atts     = shortcode_atts( array(
			'class' => 'order-send'
		), $atts );
		$template = fs_form_header( array( 'name' => 'fs-order-send', 'class' => $atts['class'] ), 'order_send' );
		$template .= fs_frontend_template( 'order/order-form' );
		$template .= fs_form_bottom( '' );

		return $template;
	}

	function user_cabinet() {
		$user = wp_get_current_user();
		if ( is_user_logged_in() && in_array( 'wholesale_buyer', $user->roles ) ) {
			$temp = fs_user_cabinet();
		} else {

			if ( isset( $_GET['fs-page'] ) && $_GET['fs-page'] == 'register' ) {
				if ( is_user_logged_in() ) {
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

	public function single_order( $args ) {
		$args     = shortcode_atts( array(
			'product_id' => 0,
			'class'      => ''
		), $args );
		$template = '
        <form action="#" name="fs-order-send" class="' . $args['class'] . '" method="POST">
            <div class="products_wrapper"></div>
            <input type="hidden" id="_wpnonce" name="_wpnonce" value="' . wp_create_nonce( 'fast-shop' ) . '">
            <input type="hidden" name="action" value="order_send">
            <input type="hidden" name="order_type" value="single">';
		$template .= fs_frontend_template( 'order/single-order', $args );
		$template .= '</form>';

		return $template;
	}

	/**
	 * Отображает кнопку для оплаты выбранным способом
	 * @return string
	 */
	function pay_methods() {
		if ( empty( $_REQUEST['order_id'] ) || empty( $_REQUEST['pay_method'] ) ) {
			return '<p>' . __( 'The order number or method of payment is not specified.', 'fast_shop' ) . '</p>';
		}
		$order_id = intval( $_REQUEST['order_id'] );

		$order = FS_Orders_Class::get_order( $order_id );
		$html  = '<h3 class="text-center">Оплата заказа №' . esc_attr( $order_id ) . ' с помошью ' . esc_attr( $order->payment ) . '</h3>';
		$html  .= '<div class="fs-pay-methods">';
		$html  .= apply_filters( 'fs_pay_methods', $order_id );
		$html  .= '</div>';

		return $html;
	}


}