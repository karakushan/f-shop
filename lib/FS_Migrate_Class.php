<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 05.04.2017
 * Time: 12:24
 */

namespace FS;



class FS_Migrate_Class {

	function __construct() {

		add_action( 'wp_loaded', array( $this, 'fs_api' ) );
	}

	/**
	 * Запускает миграцию при наличии спец. гет параметров
	 */
	function fs_api() {
		if ( ! empty( $_GET['fs-api'] ) && $_GET['fs-api'] == 'migrate_orders' ) {
			$this->migrate_orders();
		}
	}

	/**
	 * Конвертирует заказы из более раних версий плагина
	 *
	 * @param string $db_name название базы из которой будем импортировать
	 * @param string $table таблица заказов
	 */
	function migrate_orders( $db_name = '', $table = 'wp_fs_orders' ) {
		set_time_limit(0);
		if ( ! empty( $_GET['db'] ) ) {
			$db_name = esc_sql( $_GET['db'] );
		}

		if ( ! empty( $_GET['table'] ) ) {
			$table = esc_sql( $_GET['table'] );
		}

		if ( empty( $db_name ) ) {
			$db_name = DB_NAME;
		}

		global $wpdb2, $fs_config;

		$wpdb2  = new \wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
		$orders = $wpdb2->get_results( "SELECT * FROM $table" );
		if ( $orders ) {
			foreach ( $orders as $order ) {

				$products = unserialize( $order->products );

				$order_id = wp_insert_post(
					array(
						'post_title'     => $order->first_name . ' ' . $order->last_name . ' / ' . date( 'd.m.Y H:i', strtotime( $order->date ) ),
						'post_content'   => '',
						'post_status'    => 'processed',
						'post_type'      => $fs_config->data['post_type_orders'],
						'post_author'    => 1,
						'post_date'      => $order->date,
						'ping_status'    => get_option( 'default_ping_status' ),
						'post_parent'    => 0,
						'comment_status' => 'closed',
						'menu_order'     => 0,
						'import_id'      => 0,
						'meta_input'     => array(
							'_user_id'         => $order->user_id,
							'_user'            => array(
								'id'         => $order->user_id,
								'first_name' => $order->first_name,
								'last_name'  => $order->last_name,
								'email'      => $order->email,
								'phone'      => $order->phone,
								'city'       => $order->city
							),
							'_products'        => $products,
							'_custom_products' => $products,
							'_delivery'        => array(
								'method'    => $order->delivery,
								'secession' => $order->delivery_number,
								'adress'    => $order->address
							),
							'_payment'         => $order->payment,
							'_amount'          => $order->summa,
							'_comment'         => $order->comments
						),
					) );
				if ( $order_id ) {
					/* обновляем название заказа для админки */
					wp_update_post( array(
						'ID'         => $order_id,
						'post_title' => sprintf( 'Заказ №%d от %s %s (%s)', $order_id, $order->first_name, $order->last_name, date( 'd.m.y H:i', strtotime( $order->date ) ) )
					) );
				}
			}
		}

	}

	/**
	 * Импортирует атрибуты товаров из опций
	 */
	static function import_option_attr() {
		$fs_atributes = get_option( 'fs-attr-group' );
		global $post;

		if ( ! empty( $fs_atributes ) ) {
			foreach ( $fs_atributes as $k => $att ) {
//  ищем родительский термин среди уже существующих
				$term_parent = term_exists( $k, 'product-attributes' );
//				если $term_parent возвратило 0 значит термина не существует, добавляем его
				if ( ! $term_parent ) {
					$args        = array(
						'alias_of'    => $k,
						'description' => '',
						'parent'      => 0,
						'slug'        => $att['slug'],
					);
					$term_parent = wp_insert_term( $att['title'], 'product-attributes', $args );
					if ( is_wp_error( $term_parent ) ) {
						update_option( 'fs_last_error', $term_parent->get_error_message() );
					}
				}
// добавляем детей родительского термина
				if ( ! empty( $att['attributes'] ) ) {
					foreach ( $att['attributes'] as $att_key => $attribute ) {
						$args_child     = array(
							'alias_of'    => $att_key,
							'description' => '',
							'parent'      => $term_parent['term_id'],
							'slug'        => $att_key,
						);
						$ins_term_child = wp_insert_term( $attribute, 'product-attributes', $args_child );
						if ( is_wp_error( $ins_term_child ) ) {
							update_option( 'fs_last_error', $ins_term_child->get_error_message() );
						}
					}
				}


			}
		}

		$query = new \WP_Query( array( 'post_type' => 'product', 'posts_per_page' => - 1 ) );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_attr = get_post_meta( $post->ID, 'fs_attributes', false );
				$post_attr = isset( $post_attr[0] ) ? $post_attr[0] : array();
				if ( $post_attr ) {
					$post_terms = array();
					foreach ( $post_attr as $ps ) {
						if ( $ps ) {
							foreach ( $ps as $child_key => $pa ) {
								if ( $pa != 0 ) {
									$post_term    = term_exists( $child_key, 'product-attributes' );
									$post_terms[] = ! empty( $post_term['term_id'] ) ? $post_term['term_id'] : 0;
								}
							}
						}
					}
					$post_terms = array_unique( $post_terms );
					if ( ! empty( $post_terms ) ) {
						wp_set_post_terms( $post->ID, $post_terms, 'product-attributes', true );
					}

				}
			}
		}
	}
}