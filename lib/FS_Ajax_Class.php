<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Клас для обработки ajax запросов
 */
class FS_Ajax_Class {

	function __construct() {
		//  обработка формы заказа
		add_action( 'wp_ajax_order_send', array( &$this, 'order_send_ajax' ) );
		add_action( 'wp_ajax_nopriv_order_send', array( &$this, 'order_send_ajax' ) );
		//  добавление в список желаний
		add_action( 'wp_ajax_fs_addto_wishlist', array( &$this, 'fs_addto_wishlist' ) );
		add_action( 'wp_ajax_nopriv_fs_addto_wishlist', array( &$this, 'fs_addto_wishlist' ) );
		// удаление из списка желаний
		add_action( 'wp_ajax_fs_del_wishlist_pos', array( &$this, 'fs_del_wishlist_pos' ) );
		add_action( 'wp_ajax_nopriv_fs_del_wishlist_pos', array( &$this, 'fs_del_wishlist_pos' ) );
		//   живой поиск по сайту
		add_action( 'wp_ajax_fs_livesearch', array( &$this, 'fs_livesearch' ) );
		add_action( 'wp_ajax_nopriv_fs_livesearch', array( &$this, 'fs_livesearch' ) );

		//  получение связанных постов категории
		add_action( 'wp_ajax_fs_get_taxonomy_posts', array( &$this, 'get_taxonomy_posts' ) );
		add_action( 'wp_ajax_nopriv_fs_get_taxonomy_posts', array( &$this, 'get_taxonomy_posts' ) );


	}


	/**
	 *Отправка заказа в базу, на почту админа и заказчика
	 */
	function order_send_ajax() {
		global $fs_config;
		if ( ! $fs_config::verify_nonce() ) {
			die ( 'не пройдена верификация формы nonce' );
		}
		$fs_products = $_SESSION['cart'];
		global $wpdb;
		$wpdb->show_errors(); // включаем показывать ошибки при работе с базой

		//Производим очистку полученных данных с формы заказа
		$form_fields    = FS_Config::$form_fields;
		$sanitize_field = array();
		if ( $form_fields ) {
			foreach ( $form_fields as $field_name => $form_field ) {
				if ( empty( $_POST[ $field_name ] ) ) {
					$sanitize_field[ $field_name ] = '-';
				} else {
					if ( $form_field['type'] == 'email' ) {
						$sanitize_field[ $field_name ] = sanitize_email( $_POST[ $field_name ] );
					} else {
						$sanitize_field[ $field_name ] = sanitize_text_field( $_POST[ $field_name ] );
					}
				}
			}
		}

		// устанавливаем заголовки
		$headers[] = 'Content-type: text/html; charset=utf-8';
		$headers[] = 'From: ' . fs_option( 'name_sender', get_bloginfo( 'name' ) ) . ' <' . fs_option( 'email_sender', get_bloginfo( 'admin_email' ) ) . '>';

		// проверяем существование пользователя
		$user_id = 0;
		if ( is_user_logged_in() ) {
			$user    = wp_get_current_user();
			$user_id = $user->ID;
		}

		// Вставляем заказ в базу данных
		$defaults                              = array(
			'post_title'   => $sanitize_field['fs_first_name'] . ' ' . $sanitize_field['fs_last_name'] . ' / ' . date( 'd.m.Y H:i' ),
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'orders',
			'post_author'  => 1,
			'ping_status'  => get_option( 'default_ping_status' ),
			'post_parent'  => 0,
			'menu_order'   => 0,
			'import_id'    => 0,
			'meta_input'   => array(
				'_user_id'  => $user_id,
				'_user'     => array(
					'id'         => $user_id,
					'first_name' => $sanitize_field['fs_first_name'],
					'last_name'  => $sanitize_field['fs_last_name'],
					'email'      => $sanitize_field['fs_email'],
					'phone'      => $sanitize_field['fs_phone'],
					'city'       => $sanitize_field['fs_city']
				),
				'_products' => $fs_products,
				'_delivery' => array(
					'method'    => $sanitize_field['fs_delivery_methods'],
					'secession' => $sanitize_field['fs_delivery_number'],
					'adress'    => $sanitize_field['fs_adress']
				),
				'_payment'  => $sanitize_field['fs_payment_methods'],
				'_amount'   => fs_get_total_amount( $fs_products ),
				'_comment'  => $sanitize_field['fs_comment']
			),
		);
		$order_id                              = wp_insert_post( $defaults );
		$sanitize_field['order_id']            = $order_id;
		$sanitize_field['fs_delivery_methods'] = fs_get_delivery( $sanitize_field['fs_delivery_methods'] );
		$sanitize_field['fs_payment_methods']  = fs_get_payment( $sanitize_field['fs_payment_methods'] );
		$sanitize_field['fs_admin_message']    = fs_option( '' );
		$_SESSION['last_order_id']             = $order_id;
		$search                                = fs_mail_keys( $sanitize_field );
		$replace                               = array_values( $sanitize_field );

		// текст письма заказчику
		$user_message = apply_filters( 'fs_order_user_message', '' );
		$user_message = str_replace( $search, $replace, $user_message );

		// текст письма админу
		$admin_message = apply_filters( 'fs_order_admin_message', '' );
		$admin_message = str_replace( $search, $replace, $admin_message );

		//Отсылаем письмо с данными заказа заказчику
		$customer_mail_header = fs_option( 'customer_mail_header', 'Заказ товара на сайте «' . get_bloginfo( 'name' ) . '»' );
		wp_mail( $sanitize_field['fs_email'], $customer_mail_header, $user_message, $headers );

		//Отсылаем письмо с данными заказа админу
		$admin_email       = fs_option( 'manager_email', get_option( 'admin_email' ) );
		$admin_mail_header = fs_option( 'admin_mail_header', 'Заказ товара на сайте «' . get_bloginfo( 'name' ) . '»' );
		wp_mail( $admin_email, $admin_mail_header, $admin_message, $headers );

		/* Если есть ошибки выводим их*/
		if ( is_wp_error( $order_id ) ) {

			$result = array(
				'success' => false,
				'text'    => $order_id->get_error_messages()
			);
		} else {
			/* обновляем название заказа для админки */
			wp_update_post( array( 'ID' => $order_id, 'post_title' => __( 'Order', 'fast-shop' ) . ' №' . $order_id ) );
			/* обновляем статус поста на новый */
			if ( $term = term_exists( 'new', 'order-statuses' ) ) {
				wp_set_post_terms( $order_id, array( $term ['term_id'] ), 'order-statuses', false );
			}

			$result = array(
				'success'  => true,
				'text'     => 'Заказ №' . $order_id . ' успешно добавлен',
				'products' => $fs_products,
				'order_id' => $order_id,
				'redirect' => get_permalink( fs_option( 'page_success' ) )
			);
			unset( $_SESSION['cart'] );
		}

		echo json_encode( $result );
		exit();
	}

	/**
	 * Метод ajax добавления товара в список желаний
	 */
	public function fs_addto_wishlist() {
		$product_id                             = (int) $_REQUEST['product_id'];
		$_SESSION['fs_wishlist'][ $product_id ] = $product_id;
		$json                                   = json_encode( array(
			'status' => true
		) );
		exit( $json );
	}

	public function fs_del_wishlist_pos() {
		$product_id = (int) $_REQUEST['position'];
		$res        = '';
		unset( $_SESSION['fs_user_settings']['fs_wishlist'][ $product_id ] );
		$wishlist = ! empty( $_SESSION['fs_user_settings']['fs_wishlist'] ) ? $_SESSION['fs_user_settings']['fs_wishlist'] : array();
		$count    = count( $wishlist );
		$class    = $count == 0 ? '' : 'wishlist-show';
		$res      .= '<a href="#" class="hvr-grow"><i class="icon icon-heart"></i><span>' . $count . '</span></a><ul class="fs-wishlist-listing ' . $class . '">
        <li class="wishlist-header">' . __( 'Wishlist', 'cube44' ) . ': <i class="fa fa-times-circle" aria-hidden="true"></i></li>';
		foreach ( $_SESSION['fs_user_settings']['fs_wishlist'] as $key => $value ) {
			$res .= "<li><i class=\"fa fa-trash\" aria-hidden=\"true\" data-fs-action=\"wishlist-delete-position\" data-product-id=\"$key\" data-product-name=\"" . get_the_title( $key ) . "\" ></i> <a href=\"" . get_permalink( $key ) . "\">" . get_the_title( $key ) . "</a></li>";
		}
		$res .= '</ul>';

		if ( ! empty( $res ) ) {
			echo json_encode( array(
				'body' => $res,
				'type' => 'success'
			) );
		}
		exit;
	}

// живой поиск по сайту
	public function fs_livesearch() {
		$config = new FS_Config();
		$search = sanitize_text_field( $_POST['s'] );
		$args   = array(
			's'              => $search,
			'post_type'      => 'product',
			'posts_per_page' => 40
		);
		$query  = query_posts( $args );
		if ( $query ) {
			get_template_part( 'fast-shop/livesearch/livesearch' );
			wp_reset_query();
		} else {
			$args2 = array(
				'post_type'      => 'product',
				'posts_per_page' => 40,
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => $config->meta['product_article'],
						'value'   => $search,
						'compare' => 'LIKE'
					)
				)
			);
			query_posts( $args2 );
			get_template_part( 'fast-shop/livesearch/livesearch' );
			wp_reset_query();
		}
		exit;
	}

//  возвражает список постов определёного термина
	public function get_taxonomy_posts() {
		$term_id = (int) $_POST['term_id'];
		$post_id = (int) $_POST['post'];
		$body    = '';
		$posts   = get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => - 1,
			'post__not_in'   => array( $post_id ),
			'tax_query'      =>
				array(
					array(
						'taxonomy' => 'catalog',
						'field'    => 'term_id',
						'terms'    => $term_id
					)
				)
		) );

		$body .= '<select data-fs-action="select_related">';
		$body .= '<option value="">Выберите товар</option>';
		if ( $posts ) {
			foreach ( $posts as $key => $post ) {
				$body .= '<option value="' . $post->ID . '">' . $post->post_title . '</option>';
			}
		}
		$body .= '</select>';

		echo json_encode( array( 'body' => $body ) );
		exit;
	}
} 