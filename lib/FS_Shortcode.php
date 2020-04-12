<?php

namespace FS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Класс шорткодов магазина
 */
class FS_Shortcode {


	function __construct() {


		// Шорткод формы входа
		add_shortcode( 'fs_login', array( 'FS\FS_Users', 'login_form' ) );

		// Шорткод формы регистрации
		add_shortcode( 'fs_register', array( 'FS\FS_Users', 'register_form' ) );

		// Шорткод формы сброса пароля
		add_shortcode( 'fs_lostpassword', array( 'FS\FS_Users', 'lostpassword_form' ) );

		// Шорткод личного кабинета
		add_shortcode( 'fs_user_cabinet', array( 'FS\FS_Users', 'user_cabinet' ) );

		// Шорткод страницы корзины
		add_shortcode( 'fs_cart', array( $this, 'cart_shortcode' ) );

		// Шорткод виджета корзины
		add_shortcode( 'fs_cart_widget', array( $this, 'cart_widget' ) );

		add_shortcode( 'fs_order_info', array( $this, 'single_order_info' ) );
		add_shortcode( 'fs_last_order_info', array( $this, 'last_order_info' ) );
		add_shortcode( 'fs_last_order_id', array( 'FS\FS_Orders', 'get_last_order_id' ) );
		add_shortcode( 'fs_last_order_amount', array( 'FS\FS_Orders', 'get_last_order_amount' ) );
		add_shortcode( 'fs_have_cart_items', array( $this, 'have_cart_items' ) );

		add_shortcode( 'fs_checkout_success', array( $this, 'fs_checkout_success' ) );
		add_shortcode( 'fs_checkout', array( $this, 'order_send' ) );

		add_shortcode( 'fs_single_order', array( $this, 'single_order' ) );

		add_shortcode( 'fs_user_info', array( 'FS\FS_Users', 'user_info' ) );
		add_shortcode( 'fs_user_orders', array( $this, 'user_orders' ) );
		add_shortcode( 'fs_profile_edit', array( $this, 'profile_edit' ) );
		add_shortcode( 'fs_pay_methods', array( $this, 'pay_methods' ) );
		add_shortcode( 'fs_wishlist', array( $this, 'wishlist_shortcode' ) );
		add_shortcode( 'fs_range_slider', array( $this, 'range_slider' ) );
		add_shortcode( 'fs_order_detail', array( 'FS\FS_Orders', 'order_detail' ) );
		add_shortcode( 'fs_list_orders', array( 'FS\FS_Orders', 'list_orders' ) );


	}

	/**
	 * Шорткод списка желаний
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	function wishlist_shortcode( $atts ) {
		$atts  = shortcode_atts( array(
			'wrapper_class' => 'fs-wislist-poducts row',
			'empty_text'    => __( 'Wish list is empty', 'f-shop' ),
			'template'      => 'wishlist/wishlist-product'
		), $atts );
		$query = fs_get_wishlist();
		$html  = '<div class="' . esc_attr( $atts['wrapper_class'] ) . '">';

		if ( $query->have_posts() ) {
			$html .= $atts['before_loops'];
			while ( $query->have_posts() ) {
				$query->the_post();
				$html .= fs_frontend_template( $atts['template'] );

			}
			$html .= $atts['after_loops'];
		} else {
			$html .= '<p>' . esc_html( $atts['empty_text'] ) . '</p >';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Шорткод проверяет наличие товаров в корзине и выводит их список
	 * или информацию об пустой корзине
	 *
	 * @param $atts -массив настроек шорткода
	 *       'empty_text'-текст пустой корзины
	 *       'empty_wrapper'-wrapper (по умолчанию p)
	 *       'empty_class'-класс враппера (по умолчанию fs-empty-cart)
	 *
	 * @param $content
	 *
	 * @return mixed|string
	 */
	function have_cart_items( $atts, $content ) {
		$atts = shortcode_atts( array(
			'empty_text'  => '',
			'empty_class' => 'fs-info-block fs-empty-cart ',
		), $atts );

		$cart = fs_get_cart();

		if ( $cart ) {
			$content = apply_filters( 'the_content', $content );
		} else {
			if ( empty( $atts['empty_text'] ) ) {
				$content = '<p class="' . esc_attr( $atts['empty_class'] ) . '">';
				$content .= esc_html__( 'Your basket is empty', 'f-shop' );
				$content .= '</p>';
			} else {
				$content = $atts['empty_text'];
			}
		}

		return $content;

	}

	/**
	 * Метод-колбек шорткода [fs_last_order_info]
	 * этот шорткод выводит инфу о последнем заказе текущего посетителя
	 *
	 *
	 * @param $atts
	 *
	 * @return mixed
	 */
	function last_order_info( $atts ) {
		$orders_cl = new FS_Orders;
		$order     = $orders_cl->get_order( $orders_cl->last_order_id );


		return fs_frontend_template( 'order/last-order-info', array(
			'vars' => [
				'order' => $order,
				'args'  => shortcode_atts(
					array(
						'class' => 'fs-order-info'
					),
					$atts, 'fs_last_order_info'
				)
			]
		) );
	}

	/**
	 * Содержимое шорткода [fs_order_thanks]
	 *
	 * @return mixed
	 */
	function fs_checkout_success() {
		return fs_frontend_template( 'checkout/checkout-success' );
	}

	/**
	 * виджет корзины товаров
	 *
	 * @return string
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
		return fs_frontend_template( 'cart/list-products', array( 'vars' => array( 'cart' => FS_Cart_Class::get_cart() ) ) );
	}

	/**
	 * Шорткод показывает информацию о заказе
	 *
	 * @param $atts
	 *
	 * @return mixed
	 */
	public function single_order_info( $atts ) {
		$curent_user = wp_get_current_user();
		$orders_cl   = new FS_Orders;
		$order_id    = ! empty( $_REQUEST['order_detail'] ) ? intval( $_REQUEST['order_detail'] ) : $orders_cl->last_order_id;
// белый список параметров и значения по умолчанию
		$atts = shortcode_atts( array(
			'class'    => 'fs-order-info',
			'order_id' => $order_id,
			'order'    => $orders_cl->get_order( $order_id ),
			'payment'  => new FS_Payment_Class()

		), $atts );

		$html   = '';
		$errors = new \WP_Error();

		if ( ! is_user_logged_in() ) {
			$errors->add( 'fs-no-user', __( 'Register to view this page', 'f-shop' ) );
		}

		if ( ! $atts['order']->exists || empty( $order_id ) ) {
			$errors->add( 'fs-no-order', __( 'Order not found', 'f-shop' ) );
		}

		if ( $curent_user->user_login != $atts['order']->user_name ) {
			$errors->add( 'fs-no-access-order', __( 'Details of this order are not available for you', 'f-shop' ) );
		}

		if ( $errors->get_error_code() ) {
			foreach ( $errors->get_error_messages() as $error ) {
				$html .= '<p class="fs - order - detail">' . $error . '</p>';
			}
		} else {
			$html = fs_frontend_template( 'shortcode/fs-order-info', $atts );
		}

		return $html;


	}


	/**
	 * шорткод для отображения формы оформления заказа
	 *
	 * @param array $atts атрибуты тега form
	 *
	 * @return string
	 */
	public function order_send( $atts = array() ) {
		$atts = shortcode_atts( array(
			'class' => 'fs-checkout-form'
		), $atts );
		$cart = FS_Cart_Class::get_cart();
		if ( empty( $cart ) ) {
			return fs_frontend_template( 'checkout/checkout-no-items' );
		}
		$template = fs_form_header( array( 'name' => 'fs-order-send', 'class' => $atts['class'] ), 'order_send' );
		$template .= fs_frontend_template( 'checkout/checkout', array( 'vars' => array( 'cart' => FS_Cart_Class::get_cart() ) ) );
		$template .= fs_form_bottom( '' );

		return $template;
	}


	public function single_order( $args ) {
		$args     = shortcode_atts( array(
			'product_id' => 0,
			'class'      => ''
		), $args );
		$template = '
<form action="#" name="fs-order-send" class="' . $args['class'] . '" method="POST">
             < div class="products_wrapper" ></div >
  <input type = "hidden" id = "_wpnonce" name = "_wpnonce" value = "' . wp_create_nonce( 'f-shop' ) . '" >
  <input type = "hidden" name = "action" value = "order_send" >
  <input type = "hidden" name = "order_type" value = "single" > ';
		$template .= fs_frontend_template( 'order / single - order', $args );
		$template .= '
                                                                  </form > ';

		return $template;
	}

	/**
	 * Отображает кнопку для оплаты выбранным способом
	 * @return string
	 */
	function pay_methods() {
		if ( empty( $_REQUEST['order_id'] ) || empty( $_REQUEST['pay_method'] ) ) {
			return ' <p>' . __( 'The order number or method of payment is not specified . ', 'fast_shop' ) . ' </p > ';
		}
		$order_id     = intval( $_REQUEST['order_id'] );
		$orders_class = new FS_Orders();
		$order        = $orders_class->get_order( $order_id );
		$html         = sprintf( '<h3 class="text-center">Paying for order #%d using %s</h3 >', esc_attr( $order_id ), esc_attr( $order->payment ) );
		$html         .= '<div class="fs-pay-methods">';
		$html         .= apply_filters( 'fs_pay_methods', $order_id );
		$html         .= '</div> ';

		return $html;
	}


}