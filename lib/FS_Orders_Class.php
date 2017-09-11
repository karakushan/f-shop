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
		add_filter( 'pre_get_posts', array( $this, 'filter_orders_by_search' ));
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


	/**
	 * возвращает один заказ
	 *
	 * @param int $order_id - ID заказа
	 *
	 * @return \stdClass
	 */
	public function get_order( $order_id = 0 ) {
		global $fs_config;
		$order    = new \stdClass();
		$user     = get_post_meta( $order_id, '_user', 0 );
		$items    = get_post_meta( $order_id, '_products', 0 );
		$delivery = get_post_meta( $order_id, '_delivery', 0 );
		$pay_id   = get_post_meta( $order_id, '_payment', 1 );
		if ( ! empty( $pay_id ) && is_numeric( $pay_id ) ) {
			$order->payment = get_term_field( 'name', $pay_id, $fs_config->data['product_pay_taxonomy'] );
		} else {
			$order->payment = $pay_id;
		}
		$order->comment  = get_post_meta( $order_id, '_comment', 1 );
		$order->user     = ! empty( $user[0] ) ? $user[0] : array();
		$order->items    = ! empty( $items[0] ) ? $items[0] : array();
		$order->delivery = ! empty( $delivery[0] ) ? $delivery[0] : array();
		if ( ! empty( $order->delivery['method'] ) && is_numeric( $order->delivery['method'] ) ) {
			$order->delivery['method'] = get_term_field( 'name', $order->delivery['method'], $fs_config->data['product_del_taxonomy'] );
		}
		$order->sum       = fs_get_total_amount( $order->items );
		$order->status    = self::get_order_status( $order_id );
		$order->user_name = get_user_meta( $order->user['id'], 'nickname', true );

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

	public function delete_orders() {
		global $fs_config;
		$posts = new \WP_Query( array(
			'post_type'      => array( $fs_config->data['post_type_orders'] ),
			'posts_per_page' => - 1
		) );
		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) {
				$posts->the_post();
				global $post;
				wp_delete_post( $post->ID, true );
			}
		}
	}

	/**
	 * Создаёт возможность поиска по метаполям на странце заказов
	 */
	function filter_orders_by_search( $query ) {
		if ( ! is_admin() || empty( $_GET['s'] ) ) {
			return;
		}
		global $wpdb;
		$s       = $_GET['s'];
		$prepare = $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_value LIKE '%%%s%%'", $s );
		$results = $wpdb->get_results( $prepare );
		if ( $results ) {
			$user_ids = [];
			foreach ( $results as $result ) {
				$user_ids[] = $result->user_id;
			}
			$user_ids = array_unique( $user_ids );
			$query->set( 's', false );
			$meta_query [] = array(
				'key'     => '_user_id',
				'value'   => $user_ids,
				'compare' => 'IN',
				'type'    => 'CHAR'
			);
			$query->set( 'meta_query', $meta_query );
			$query->set( 'post_type', 'orders' );

		}

		return $query;

	}
}