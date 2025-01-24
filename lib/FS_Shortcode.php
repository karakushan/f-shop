<?php

namespace FS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * The class registers shortcodes for display on store pages
 */
class FS_Shortcode {
	function __construct() {
		# USER
		add_shortcode( 'fs_user_info', array( 'FS\FS_Users', 'user_info' ) );
		add_shortcode( 'fs_profile_edit', array( $this, 'profile_edit' ) );
		add_shortcode( 'fs_user_cabinet', array( 'FS\FS_Users', 'user_cabinet' ) ); // Шорткод личного кабинета
		add_shortcode( 'fs_login', array( 'FS\FS_Users', 'login_form' ) ); // Шорткод формы входа
		add_shortcode( 'fs_register', array( 'FS\FS_Users', 'register_form' ) ); // Шорткод формы регистрации
		add_shortcode( 'fs_lostpassword', array( 'FS\FS_Users', 'lostpassword_form' ) ); // Шорткод формы сброса пароля

		# WIDGETS
		add_shortcode( 'fs_range_slider', array( $this, 'range_slider' ) );

		# WISHLIST
		add_shortcode( 'fs_wishlist', array( $this, 'wishlist_shortcode' ) );

		# CART
		add_shortcode( 'fs_cart_widget', array( $this, 'cart_widget' ) ); // Шорткод виджета корзины
		add_shortcode( 'fs_have_cart_items', array( $this, 'have_cart_items' ) );
		add_shortcode( 'fs_cart', array( $this, 'cart_shortcode' ) ); // Шорткод страницы корзины

		# CHECKOUT
		add_shortcode( 'fs_checkout_success', array( $this, 'fs_checkout_success' ) );
		add_shortcode( 'fs_checkout', array( $this, 'order_send' ) );

		# === ORDERS === #
		// Quick order form shortcode
		add_shortcode( 'fs_quick_order_form', array( $this, 'quick_order_form_shortcode' ) );

		// Quick order button shortcode
		add_shortcode( 'fs_quick_order_btn', array( $this, 'quick_order_btn_shortcode' ) );

		add_shortcode( 'fs_user_orders', array( $this, 'user_orders' ) );
		add_shortcode( 'fs_pay_methods', array( $this, 'pay_methods' ) );
		add_shortcode( 'fs_list_orders', array( 'FS\FS_Orders', 'list_orders' ) );
		add_shortcode( 'fs_order_detail', array( 'FS\FS_Orders', 'order_detail' ) );
		add_shortcode( 'fs_order_pay', array( 'FS\FS_Payment', 'order_pay' ) );
		add_shortcode( 'fs_order_info', array( $this, 'single_order_info' ) );
		add_shortcode( 'fs_last_order_info', array( $this, 'last_order_info' ) );
		add_shortcode( 'fs_last_order_id', array( 'FS\FS_Orders', 'get_last_order_id' ) );
		add_shortcode( 'fs_last_order_amount', array( 'FS\FS_Orders', 'get_last_order_amount' ) );
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
		$items = fs_get_wishlist();
		$html  = '<div class="' . esc_attr( $atts['wrapper_class'] ) . '">';

		if ( $items ) {
			$html .= $atts['before_loops'];
			global $post;
			foreach ( $items as $post ) {
				setup_postdata( $post );
				$html .= fs_frontend_template( $atts['template'] );

			}
			$html .= $atts['after_loops'];
			wp_reset_postdata();
		} else {
			$html .= '<p>' . esc_html( $atts['empty_text'] ) . '</p >';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * The shortcode checks the presence of products in the cart and displays a list of them
	 * or information about an empty cart
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
		$order = new FS_Order();

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
		$template = FS_Orders::get_last_order_id() ? 'checkout/checkout-success' : 'order/order-fail';

		return fs_frontend_template( $template, array(
			'vars' => array(
				'order' => new FS_Order( FS_Orders::get_last_order_id() )
			)
		) );
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
		return fs_frontend_template( 'cart/list-products', array( 'vars' => array( 'cart' => FS_Cart::get_cart() ) ) );
	}

	/**
	 * Шорткод показывает информацию о заказе
	 *
	 * @param $atts
	 *
	 * @return mixed
	 */
	public function single_order_info( $atts ) {
		$order_id = isset( $_GET['order_detail'] ) && is_numeric( $_GET['order_detail'] ) ? intval( $_REQUEST['order_detail'] ) : 0;

		if ( ! fs_order_exist( $order_id ) ) {
			return '<p class="fs-info-block fs-has-warning">' . __( 'Details of this order are unknown or unavailable.', 'f-shop' ) . '</p>';
		}

		$current_user = wp_get_current_user();
		$orders_cl    = new FS_Orders;

		// белый список параметров и значения по умолчанию
		$atts = shortcode_atts( array(
			'class'    => 'fs-order-info',
			'order_id' => $order_id,
			'order'    => $orders_cl->get_order( $order_id ),
			'payment'  => new FS_Payment()

		), $atts );

		$html   = '';
		$errors = new \WP_Error();

		if ( ! is_user_logged_in() ) {
			$errors->add( 'fs-no-user', __( 'Register to view this page', 'f-shop' ) );
		}

		if ( ! $atts['order']->exists || empty( $order_id ) ) {
			$errors->add( 'fs-no-order', __( 'Order not found', 'f-shop' ) );
		}

		if ( $current_user->user_login != $atts['order']->user_name ) {
			$errors->add( 'fs-no-access-order', __( 'Details of this order are not available for you', 'f-shop' ) );
		}

		if ( $errors->get_error_code() ) {
			foreach ( $errors->get_error_messages() as $error ) {
				$html .= '<p class="fs-order-detail">' . $error . '</p>';
			}
		} else {
			$html = fs_frontend_template( 'shortcode/fs-order-info', $atts );
		}

		return $html;


	}


	/**
	 * шорткод для отображения формы оформления заказа
	 *
	 * @param array $args атрибуты тега form
	 *
	 * @return string
	 */
	public function order_send( $args = array() ) {
		$args = shortcode_atts( array(
			'class' => 'fs-checkout-form'
		), $args );

		if ( FS_Cart::has_empty() ) {
			return fs_frontend_template( 'checkout/checkout-no-items' );
		}

		$template = FS_Form::form_open( array(
			'name'              => 'fs-order-send',
			'class'             => $args['class'],
			'ajax_action'       => 'order_send',
			'inline_attributes' => 'x-init="
				$data.loading = false;
				$data.errors = {};
				$data.success = false;
				$el.onsubmit = async function(e) { 
					e.preventDefault();
					$data.loading = true;
					try {
						const response = await Alpine.store(\'FS\').sendOrder(e);
						$data.loading = false;
						if (response.success) {
							$data.success = true;
							iziToast[response.data.type || \'success\']({
								title: response.data.title || \''.__('Success', 'f-shop').'\',
								message: response.data.msg || \''.__('Order successfully created', 'f-shop').'\',
								position: \'topCenter\'
							});
						} else {
							if (response.data && response.data.errors) {
								$data.errors = response.data.errors;
							}
							iziToast[response.data.type || \'error\']({
								title: response.data.title || \''.__('Error', 'f-shop').'\',
								message: response.data.msg || \''.__('Please check all form fields for validation errors', 'f-shop').'\',
								position: \'topCenter\',
								timeout: response.data.type===\'warning\' ? 6000 : 4000,
								overlay:response.data.type===\'warning\' ? true : false, 
								maxWidth: response.data.type===\'warning\' ? 400 : null,
								icon: \'\'
							});
						}
					} catch(error) {
						$data.loading = false;
						console.error(\'Error:\', error);
						iziToast.error({
							title: \''.__('Error', 'f-shop').'\',
							message: error.message,
							position: \'topCenter\'
						});
					}
				}"'		) );
		$template .= fs_frontend_template( 'checkout/checkout', array( 'vars' => array( 'cart' => FS_Cart::get_cart() ) ) );
		$template .= FS_Form::form_close();

		return $template;
	}


	/**
	 * Displays the quick order form code
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function quick_order_form_shortcode( $args ) {
		$args = shortcode_atts( array(
			'product_id' => get_the_ID(),
			'class'      => 'fs-quick-order'
		), $args );
		$cart = [];
		if ( is_singular( FS_Config::get_data( 'post_type' ) ) && ! empty( $args['product_id'] ) ) {
			$cart = [ [ 'ID' => $args['product_id'], 'count' => 1 ] ];
		}
		ob_start(); ?>
    <form action=""
          name="fs-order-send"
          class="<?php echo esc_attr( $args['class'] ) ?>"
          x-data="{ errors: [],msg: '' }"
          x-on:submit.prevent='$store.FS.sendOrder( $event,{ cart: cart.items.length ? cart.items : <?php echo htmlentities( json_encode( $cart ) ) ?> }).then((r)=>{
             if (!r.success) {
                errors = typeof r.data.errors !== "undefined" ? r.data.errors : [];
                msg = typeof r.data.msg !== "undefined" ?  r.data.msg : "";
             }
        })'
          method="POST">
            <input type="hidden" name="order_type" value="quick">
		<?php
		$template = ob_get_clean();
		$template .= fs_frontend_template( 'order/quick-order', $args );
		$template .= '</form>';

		return $template;
	}

	/**
	 * Generates a shortcode for a quick order button.
	 *
	 * @param array $attributes {
	 *     An array of attributes for the shortcode.
	 *
	 * @type string $text The text displayed on the button. Default is 'Quick order'.
	 * @type string $tag The HTML tag to use for the button. Default is 'a'.
	 * @type string $class The CSS class for styling the button. Default is 'fs-quick-order-btn'.
	 * @type string $href The URL to link to when the button is clicked. Default is '#'.
	 * @type int $bs -modal  Whether to enable Bootstrap modal. Default is 0.
	 * @type string $bs -target The Bootstrap modal target if enabled.
	 * }
	 * @return string The generated HTML for the quick order button.
	 */
	public function quick_order_btn_shortcode( $attributes = [] ) {
		$attributes = shortcode_atts( [
			'product_id' => get_the_ID(),
			'text'       => __( 'Quick order', 'f-shop' ),
			'tag'        => 'a',
			'class'      => 'fs-quick-order-btn',
			'href'       => '#',
			'bs-modal'   => '',
		], $attributes );

		$out = '<' . $attributes['tag'] . ' ' . fs_parse_attr( [
				'class' => $attributes['class'],
				'href'  => $attributes['href']
			] );
		$out .= ' onclick="Alpine.store(\'FS\').addToCart(' . $attributes['product_id'] . ')"';
		if ( ! empty( $attributes['bs-modal'] ) ) {
			$out .= ' data-toggle="modal" data-target="' . esc_attr( $attributes['bs-modal'] ) . '"';
		}
		$out .= '>';
		$out .= esc_html( $attributes['text'] );
		$out .= '</' . $attributes['tag'] . '>';

		return $out;
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