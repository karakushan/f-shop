<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Класс для работы с корзиной
 */
class FS_Cart_Class {

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

		// присваиваем переменной $cart содержимое корзины
		if ( ! empty( $_SESSION['cart'] ) ) {
			$this->cart = $_SESSION['cart'];
		}

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

	// ajax обработка добавления в корзину
	function add_to_cart_ajax() {
		$attr = ! empty( $_POST['attr'] ) ? $_POST['attr'] : array();

		$_SESSION['cart'][] = array(
			'ID'        => intval( $_POST['post_id'] ),
			'count'     => intval( $_POST['count'] ),
			'attr'      => $attr,
			'variation' => intval( $_POST['variation'] )
		);

		fs_cart_widget( array( 'class' => 'cart' ) );

		exit;

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
			return new \WP_Error( __METHOD__, __( 'Cart is empty', 'f-shop') );
		}

		unset( $_SESSION['cart'][ $cart_item ] );

		return true;
	}

	/**
	 * Уничтожает корзину полностью
	 * @return bool
	 */
	function remove_cart() {
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
			wp_send_json_success( array( 'message' => __( 'All items have been successfully removed from the cart.', 'f-shop') ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'An error occurred while removing items from the cart or the cart is empty.', 'f-shop') ) );
		}
	}



	//удаление товара в корзине аяксом
	public function delete_product_ajax() {
		$remove = $this->fs_remove_product( $_POST['item'] );
		if ( $remove ) {
			wp_send_json_success( array( 'message' => sprintf( __( 'Position successful removed from the basket', 'f-shop'), $_POST['item'] ) ) );
		}
		if ( is_wp_error( $remove ) ) {
			wp_send_json_error( array( 'message' => sprintf( $remove->get_error_message(), $_POST['item'] ) ) );
		} else {
			wp_send_json_error( array( 'message' => sprintf( __( 'An error occurred while removing position from the cart', 'f-shop'), $_POST['item'] ) ) );
		}
	}

	/**
	 * Возвращает корзину
	 *
	 * @return array
	 */
	public static function get_cart() {
		if ( ! empty( $_SESSION['cart'] ) ) {
			return $_SESSION['cart'];
		} else {
			return [];
		}
	}


}