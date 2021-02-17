<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Класс для работы с корзиной
 */
class FS_Cart {

	public $cart = null;

	function __construct() {
		// добавление товара в корзину
		add_action( 'wp_ajax_add_to_cart', array( $this, 'add_to_cart_ajax' ) );
		add_action( 'wp_ajax_nopriv_add_to_cart', array( $this, 'add_to_cart_ajax' ) );

		//Удаление товара из корзины ajax
		add_action( 'wp_ajax_fs_delete_product', array( $this, 'delete_product_ajax' ) );
		add_action( 'wp_ajax_nopriv_fs_delete_product', array( $this, 'delete_product_ajax' ) );

		//Удаление всех товаров из корзины ajax
		add_action( 'wp_ajax_fs_delete_cart', array( $this, 'remove_cart_ajax' ) );
		add_action( 'wp_ajax_nopriv_fs_delete_cart', array( $this, 'remove_cart_ajax' ) );

		// получаем содержимое корзины
		add_action( 'wp_ajax_fs_get_cart', array( $this, 'fs_get_cart_callback' ) );
		add_action( 'wp_ajax_nopriv_fs_get_cart', array( $this, 'fs_get_cart_callback' ) );

		// Update of cart items in cart
		add_action( 'wp_ajax_fs_change_cart_count', array( $this, 'change_cart_item_count' ) );
		add_action( 'wp_ajax_nopriv_fs_change_cart_count', array( $this, 'change_cart_item_count' ) );

		// присваиваем переменной $cart содержимое корзины
		if ( ! empty( $_SESSION['cart'] ) ) {
			$this->cart = $_SESSION['cart'];
		}

	}


	/**
	 * Updating the number of goods in the basket by ajax
	 */
	public function change_cart_item_count() {
		$item_id       = intval( $_POST['item_id'] );
		$product_count = floatval( $_POST['count'] );
		if ( ! empty( $_SESSION['cart'] ) ) {
			$_SESSION['cart'][ $item_id ]['count'] = $product_count;
			$product_id                            = (int) $_SESSION['cart'][ $item_id ]['ID'];
			$cost                                  = fs_get_price( $product_id ) * $product_count;
			wp_send_json_success( [
				'cost'  => apply_filters( 'fs_price_format', $cost ) . ' ' . fs_currency(),
				'total' => fs_get_total_amount() . ' ' . fs_currency()
			] );
		}
		wp_send_json_error();

	}

	/**
	 * Получает шаблон корзины методом ajax
	 * позволяет использовать пользователям отображение корзины в нескольких местах одновременно
	 */
	function fs_get_cart_callback() {
		$template = ! empty( $_POST['template'] ) ? $_POST['template'] : 'cart-widget/widget';
		if ( ! empty( $template ) ) {
			echo fs_frontend_template( $template );
		}
		exit();
	}

	/**
	 * Подключает шаблон дополнительных полей доставки
	 */
	function show_shipping_fields() {
		$shipping_methods = fs_get_shipping_methods();
		echo '<div id="fs-shipping-fields">';
		if ( $shipping_methods ) {
			$show_fields = get_term_meta( $shipping_methods[0]->term_id, '_fs_delivery_address', true );
			if ( $show_fields ) {
				fs_load_template( 'checkout/shipping-fields' );
			}
		}
		echo '</div>';
	}

	/**
	 * Добавдяет позицию в корзину
	 *
	 * @param array $data
	 *
	 * @return bool|\WP_Error
	 */
	public static function push_item( $data = [] ) {
		if ( empty( $data['ID'] ) ) {
			return new \WP_Error( 'fs_not_specified_id', __( 'Item ID not specified', 'f-shop' ) );
		}

		$data = wp_parse_args( $data, array(
			'ID'        => $data['ID'],
			'count'     => 1,
			'attr'      => [],
			'variation' => null
		) );

		$cart = self::get_cart();

		array_push( $cart, $data );
		$_SESSION['cart'] = $cart;

		return true;
	}

	// ajax обработка добавления в корзину
	function add_to_cart_ajax() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}
		$product_class = new FS_Product();
		$attr          = ! empty( $_POST['attr'] ) ? $_POST['attr'] : array();
		$product_id    = intval( $_POST['post_id'] );
		$variation     = ! empty( $_POST['variation'] ) ? intval( $_POST['variation'] ) : null;
		$count         = floatval( $_POST['count'] );
		$variations    = $product_class->get_product_variations( $product_id, false );
		$is_variated   = count( $variations ) ? true : false;
		$search_item   = - 1;

		// Выполняем поиск подобной позиции в корзине
		if ( ! empty( $_SESSION['cart'] ) ) {
			foreach ( $_SESSION['cart'] as $key => $item ) {
				if ( $is_variated && $item['ID'] == $product_id && $item['variation'] == $variation ) {
					$search_item = $key;
				} elseif ( ! $is_variated && $item['ID'] == $product_id ) {
					$search_item = $key;
				}
			}
		}

		if ( $search_item != - 1 && ! empty( $_SESSION['cart'] ) ) {
			$_SESSION['cart'][ $search_item ] = array(
				'ID'        => $product_id,
				'count'     => floatval( $_SESSION['cart'][ $search_item ]['count'] + $count ),
				'attr'      => $attr,
				'variation' => $variation
			);
		} else {
			$_SESSION['cart'][] = array(
				'ID'        => $product_id,
				'count'     => $count,
				'attr'      => $attr,
				'variation' => ! empty( $variations[ $variation ] ) ? $variation : null
			);
		}


		wp_send_json_success( array( 'data' => $_POST ) );

	}


	/**
	 * Метод удаляет конкретный товар или все товары из корзины покупателя
	 *
	 * @param int $cart_item
	 *
	 * @return bool|\WP_Error
	 */
	public function fs_remove_product( $cart_item = 0 ) {
		if ( empty( $_SESSION['cart'] ) || ! is_array( $_SESSION['cart'] ) ) {
			return new \WP_Error( __METHOD__, __( 'Cart is empty', 'f-shop' ) );
		}

		unset( $_SESSION['cart'][ $cart_item ] );

		return true;
	}

	/**
	 * Уничтожает корзину полностью
	 *
	 * @return bool
	 */
	public static function remove_cart() {
		unset( $_SESSION['cart'] );

		if ( empty( $_SESSION['cart'] ) ) {
			return true;
		} else {
			return false;
		}


	}

	/**
	 * Уничтожает корзину полностью через ajax
	 *
	 * @return bool
	 */
	function remove_cart_ajax() {
		$remove = $this->remove_cart();
		if ( $remove ) {
			wp_send_json_success( array( 'message' => __( 'All items have been successfully removed from the cart.', 'f-shop' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'An error occurred while removing items from the cart or the cart is empty.', 'f-shop' ) ) );
		}
	}


	//удаление товара в корзине аяксом
	public function delete_product_ajax() {
		$remove = $this->fs_remove_product( $_POST['item'] );
		if ( $remove ) {
			wp_send_json_success( array( 'message' => sprintf( __( 'Position successful removed from the basket', 'f-shop' ), $_POST['item'] ) ) );
		}
		if ( is_wp_error( $remove ) ) {
			wp_send_json_error( array( 'message' => sprintf( $remove->get_error_message(), $_POST['item'] ) ) );
		} else {
			wp_send_json_error( array( 'message' => sprintf( __( 'An error occurred while removing position from the cart', 'f-shop' ), $_POST['item'] ) ) );
		}
	}

	/**
	 * Возвращает корзину
	 *
	 * @return array
	 */
	public static function get_cart() {
		return isset( $_SESSION['cart'] ) ? $_SESSION['cart'] : [];
	}


}
