<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Класс заказов
 */
class FS_Orders_Class {
	public $post_type = 'orders';

	function __construct() {
		add_filter( 'pre_get_posts', array( $this, 'filter_orders_by_search' ) );
		//===== ORDER STATUSES ====
		add_action( 'init', array( $this, 'order_status_custom' ), 15 );
		add_action( 'admin_footer-post-new.php', array(
			$this,
			'true_append_post_status_list'
		) ); // страница создания нового поста
		add_action( 'admin_footer-post.php', array( $this, 'true_append_post_status_list' ) );
		add_filter( 'display_post_states', array( $this, 'true_status_display' ) );

		add_action( 'transition_post_status', array( $this, 'fs_publish_order_callback' ) );
	}

	/*
   *  Это событие отправляет покупателю сведения об оплате выбранным способом
   *  срабатывает в момент создания заказа
   */
	function fs_publish_order_callback( $new_status ) {
		global $post;

		if ( $post && $post->post_type == $this->post_type && $new_status == 'fs_pending' ) {
			$pay_method_id     = get_post_meta( $post->ID, '_payment', 1 );
			$message_no_filter = get_term_meta( $pay_method_id, 'pay-message', 1 );
			$message           = apply_filters( 'fs_pay_user_message', $message_no_filter, $pay_method_id, $post->ID );
			$message_decode    = wp_specialchars_decode( $message, ENT_QUOTES );
			$user_data         = get_post_meta( $post->ID, '_user', 1 );
			$headers           = array(
				'content-type: text/html'
			);
			if ( is_email( $user_data['email'] ) && ! empty( $message ) ) {
				wp_mail( $user_data['email'], 'Сведения об оплате', $message_decode, $headers );
			}
		}
	}

	function true_status_display( $statuses ) {
		global $post;
		$all_statuses = $this->get_order_statuses();
		if ( is_array( $all_statuses ) && ! empty( $all_statuses ) ) {
			foreach ( $all_statuses as $key => $status ) {
				if ( get_query_var( 'post_status' ) != $key ) {
					if ( $post->post_status == $key ) {
						$statuses[] = $status;
					}
				}
			}
		}

		return $statuses;
	}

	/**
	 * метод регистрирует статусы заказов в системе
	 */
	function order_status_custom() {
		$all_statuses = $this->get_order_statuses();
		if ( is_array( $all_statuses ) && ! empty( $all_statuses ) ) {
			foreach ( $all_statuses as $key => $status ) {
				register_post_status( $key, array(
					'label'                     => $status,
					'label_count'               => _n_noop( $status . ' <span class="count">(%s)</span>', $status . ' <span class="count">(%s)</span>' ),
					'public'                    => true,
					'show_in_admin_status_list' => true
				) );
			}
		}

	}

	/**
	 * Добавляет зарегистрированные статусы постов  в выпадающий список
	 * на странице редактирования заказа
	 */
	function true_append_post_status_list() {
		global $post;
		if ( $post->post_type == $this->post_type ) {
			$all_statuses = $this->get_order_statuses();
			if ( is_array( $all_statuses ) && ! empty( $all_statuses ) ) : ?>
              <script> jQuery(function ($) {
					  <?php foreach ( $all_statuses as $key => $status ): ?>
                      $('select#post_status').append("<option value=\"<?php echo esc_attr( $key )?>\" <?php selected( $post->post_status, $key ) ?>><?php echo esc_attr( $status )?></option>");
					  <?php if ( $post->post_status == $key ): ?>
                      $('#post-status-display').text('<?php echo esc_attr( $status )?>');
					  <?php endif; endforeach;  ?>
                  });</script>";
				<?php
			endif;
		}
	}

	/**
	 * Возвращает зарегистрированные на сайте статусы заказов
	 *
	 * через фильтр "fs_order_statuses" можно добавлять свой
	 * @return mixed|void
	 */
	public function get_order_statuses() {
		$order_statuses = array(
			'fs_pending'   => __( 'Pending', 'fast-shop' ),
			'fs_completed' => __( 'Completed', 'fast-shop' ),
			'fs_cancelled' => __( 'Cancelled', 'fast-shop' ),
		);

		return apply_filters( 'fs_order_statuses', $order_statuses );
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
		$post_status_id = get_post_status( $order_id );
		if ( $post_status_id ) {
			$all_post_statuses = self::get_order_statuses();
			$status            = $all_post_statuses[ $post_status_id ];
		} else {
			$status = __( 'The order status is not defined', 'fast-shop' );
		}

		return $status;
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
	public static function get_order( $order_id = 0 ) {
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
		$order->status    = get_post_status( $order_id );
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