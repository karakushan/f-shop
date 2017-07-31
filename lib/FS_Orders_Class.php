<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Класс заказов
 */
class FS_Orders_Class {
	public $order_status;
	private $config;

	function __construct() {
	}


	/**
	 * Выводит объект с заказми отдельного пользователя, по умолчанию текущего, который авторизовался
	 *
	 * @param int $user_id - id пользователя заказы которого нужно получить
	 * @param int|bool $status - id статуса заказа (если указать false будут выведены все)
	 * @param array $args - массив аргументов аналогичных для WP_Query()
	 *
	 * @return array|null|object объект с заказами
	 */
	public static function get_user_orders( $user_id = 0, $status = false, $args = array() ) {

		if ( empty( $user_id ) ) {
			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;
		}
		$user_id = (int) $user_id;
		$args    = wp_parse_args( $args, array(
			'post_type'  => 'orders',
			'meta_key'   => '_user_id',
			'meta_value' => $user_id,

		) );

		if ( $status == false ) {
			$query = new \WP_Query( $args );
		} else {
			$args['order-statuses'] = $status;
			$query                  = new \WP_Query( $args );
		}

		return $query;
	}

	/**
	 * Возвращает статус заказа в текстовой, читабельной форме
	 *
	 * @param $order_id - ID заказа
	 *
	 * @return string
	 */
	public static function get_order_status( $order_id ) {
		$statuses = get_the_terms( $order_id, 'order-statuses' );
		if ( $statuses ) {
			$order_status = $statuses[0]->name;
		} else {
			$order_status = 'статус не задан';
		}

		return $order_status;
	}

	public static function get_order_items( $order_id ) {
		$order_id = (int) $order_id;
		$products = get_post_meta( $order_id, '_products', 0 );
		$products = $products[0];
		$item     = array();
		if ( $products ) {
			foreach ( $products as $id => $product ) {
				$price       = fs_get_price( $id );
				$count       = (int) $product['count'];
				$item[ $id ] = array(
					'id'    => $id,
					'price' => $price,
					'name'  => get_the_title( $id ),
					'count' => $count,
					'code'  => fs_product_code( $id ),
					'sum'   => fs_row_price( $id, $count, false ),
					'image' => get_the_post_thumbnail_url( $id, 'large' ),
					'link'  => get_the_permalink( $id )
				);
			}
		}

		return $item;
	}

//Получаем объект одного заказа
	public function get_order( $id = 0 ) {
		global $wpdb;
		$table_name = $this->config->data['table_orders'];
		$order      = $wpdb->get_row( "SELECT * FROM $table_name WHERE id ='$id'" );
		if ( ! is_null( $order ) ) {
			$user                 = get_user_by( 'id', $order->user_id );
			$order->user          = $user;
			$order->delivery_name = 'не определено';
			$order->payment_name  = 'не определено';
			$order->products      = unserialize( $order->products );
			$order->status        = $this->order_status( $order->status );
			if ( ! is_wp_error( get_term_field( 'name', $order->delivery ) ) ) {
				$order->delivery_name = get_term_field( 'name', $order->delivery );
			}
			if ( ! is_wp_error( get_term_field( 'name', $order->payment ) ) ) {
				$order->payment_name = get_term_field( 'name', $order->payment );
			}
		}

		return $order;
	}

	/**
	 * подсчитывает общую сумму товаров в одном заказе
	 *
	 * @param $products - список товаров в объекте
	 *
	 * @return float $items_sum - стоимость всех товаров
	 */
	public function fs_order_total( int $order_id ) {
		$item     = array();
		$currency = fs_currency();
		$products = $this->get_order( $order_id );
		if ( $products ) {
			foreach ( $products as $product ) {
				$item[ $product->post_id ] = $product->count * fs_get_price( $product->post_id );
			}
			$items_sum = array_sum( $item );
		}
		$items_sum = apply_filters( 'fs_price_format', $items_sum );
		$items_sum = $items_sum . ' ' . $currency;

		return $items_sum;
	}

}