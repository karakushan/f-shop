<?php

namespace FS;

//error_reporting( E_ALL );
//ini_set( 'display_errors', true );
//ini_set( 'display_startup_errors', true );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * PHP CLass to handle ajax requests
 */
class FS_Ajax {


	function __construct() {

		if ( wp_doing_ajax() ) {
			//  Add to wishlist
			add_action( 'wp_ajax_fs_addto_wishlist', array( $this, 'fs_addto_wishlist' ) );
			add_action( 'wp_ajax_nopriv_fs_addto_wishlist', array( $this, 'fs_addto_wishlist' ) );

			// Remove from wish list
			add_action( 'wp_ajax_fs_del_wishlist_pos', array( $this, 'fs_del_wishlist_pos' ) );
			add_action( 'wp_ajax_nopriv_fs_del_wishlist_pos', array( $this, 'fs_del_wishlist_pos' ) );

			//  Getting related category posts
			add_action( 'wp_ajax_fs_get_taxonomy_posts', array( $this, 'get_taxonomy_posts' ) );
			add_action( 'wp_ajax_nopriv_fs_get_taxonomy_posts', array( $this, 'get_taxonomy_posts' ) );

			// Add product to compare
			add_action( 'wp_ajax_fs_add_to_comparison', array( $this, 'fs_add_to_comparison_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_add_to_comparison', array( $this, 'fs_add_to_comparison_callback' ) );

			// Deletes one term (property) of a product
			add_action( 'wp_ajax_fs_remove_product_term', array( $this, 'fs_remove_product_term_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_remove_product_term', array( $this, 'fs_remove_product_term_callback' ) );

			// Adds a purchase option
			add_action( 'wp_ajax_fs_add_variant', array( $this, 'fs_add_variant_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_add_variant', array( $this, 'fs_add_variant_callback' ) );

			// Getting options for the price of goods
			add_action( 'wp_ajax_fs_get_variated', array( $this, 'fs_get_variated_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_get_variated', array( $this, 'fs_get_variated_callback' ) );

			// Attribute attribute to product
			add_action( 'wp_ajax_fs_add_att', array( $this, 'fs_add_att_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_add_att', array( $this, 'fs_add_att_callback' ) );

			// Setting a product rating
			add_action( 'wp_ajax_fs_set_rating', array( $this, 'fs_set_rating_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_set_rating', array( $this, 'fs_set_rating_callback' ) );

			// Product Item Update
			add_action( 'wp_ajax_fs_update_position', array( $this, 'fs_update_position_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_update_position', array( $this, 'fs_update_position_callback' ) );

			// Returns the HTML code of the template located at /templates/front-end/checkout/shipping-fields.php
			add_action( 'wp_ajax_fs_show_shipping', array( $this, 'fs_show_shipping_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_show_shipping', array( $this, 'fs_show_shipping_callback' ) );

			// Returns a template, works based on get_template_part ()
			add_action( 'wp_ajax_fs_get_template_part', array( $this, 'fs_get_template_part' ) );
			add_action( 'wp_ajax_nopriv_fs_get_template_part', array( $this, 'fs_get_template_part' ) );

			// Live product search
			add_action( 'wp_ajax_fs_livesearch', array( $this, 'livesearch_callback' ) );
			add_action( 'wp_ajax_fs_livesearch', array( $this, 'livesearch_callback' ) );

			// Live product search in admin
			add_action( 'wp_ajax_fs_search_product_admin', array( $this, 'search_product_admin' ) );
			add_action( 'wp_ajax_nopriv_fs_search_product_admin', array( $this, 'search_product_admin' ) );

			// Add new order and send e-mail
			add_action( 'wp_ajax_order_send', array( $this, 'order_send_ajax' ) );
			add_action( 'wp_ajax_nopriv_order_send', array( $this, 'order_send_ajax' ) );

			// Notifies of the appearance of goods in stock
			add_action( 'wp_ajax_fs_report_availability', array( $this, 'report_availability' ) );
			add_action( 'wp_ajax_nopriv_fs_report_availability', array( $this, 'report_availability' ) );

			// Returns the product gallery
			add_action( 'wp_ajax_fs_get_product_gallery_ids', array( $this, 'fs_get_product_gallery_ids' ) );
			add_action( 'wp_ajax_nopriv_fs_get_product_gallery_ids', array( $this, 'fs_get_product_gallery_ids' ) );

			// Получаем API ключ для сайта
			add_action( 'wp_ajax_fs_get_api_key', array( $this, 'fs_get_api_key' ) );
			add_action( 'wp_ajax_fs_get_api_key', array( $this, 'fs_get_api_key' ) );

			add_action( 'wp_ajax_fs_add_custom_attribute', array( $this, 'fs_add_custom_attribute_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_add_custom_attribute', array( $this, 'fs_add_custom_attribute_callback' ) );

			add_action( 'wp_ajax_fs_get_admin_attributes_table', array(
				'FS\FS_Taxonomy',
				'fs_get_admin_product_attributes_table'
			) );
			add_action( 'wp_ajax_nopriv_fs_get_admin_attributes_table', array(
				'FS\FS_Taxonomy',
				'fs_get_admin_product_attributes_table'
			) );

			add_action( 'wp_ajax_fs_like_comment', array( $this, 'fs_like_comment_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_like_comment', array( $this, 'fs_like_comment_callback' ) );

			// Заполняет поля данными в режиме quick edit
			add_action( 'wp_ajax_fs_quick_edit_values', array( $this, 'fs_quick_edit_values_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_quick_edit_values', array( $this, 'fs_quick_edit_values_callback' ) );
		}
	}

	/**
	 * Заполняет поля данными в режиме quick edit
	 */
	public function fs_quick_edit_values_callback() {
		if ( empty( $_POST['fields'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Fields not specified!', 'f-shop' ) ] );
		}

		$fields = [];
		foreach ( $_POST['fields'] as $field ) {
			$fields[ $field ] = get_post_meta( intval( $_POST['post_id'] ), $field, 1 );
		}

		wp_send_json_success( [ 'fields' => $fields ] );
	}

	public function fs_like_comment_callback() {
		$ip            = $_SERVER['HTTP_CLIENT_IP'] ? $_SERVER['HTTP_CLIENT_IP']
			: ( $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'] );
		$comment_id    = (int) $_POST['comment_id'];
		$like_user_ips = get_comment_meta( $comment_id, 'fs_like_user' );

		if ( in_array( $ip, $like_user_ips ) ) {
			wp_send_json_error( [
				'msg' => __( 'You have already voted for this review!', 'f-shop' )
			] );
		}

		$comment_like_count = (int) get_comment_meta( $comment_id, 'fs_like_count', 1 );
		$comment_like_count ++;
		update_comment_meta( $comment_id, 'fs_like_count', $comment_like_count );

		add_comment_meta( $comment_id, 'fs_like_user', $ip );

		wp_send_json_success( [
			'count' => $comment_like_count,
			'msg'   => __( 'Your like has been added to the review!', 'f-shop' )
		] );
	}

	/**
	 * Добавляет атрибуты к товару
	 */
	function fs_add_custom_attribute_callback() {
		$post_id         = (int) $_POST['post_id'];
		$attribute_name  = trim( $_POST['name'] );
		$attribute_value = trim( $_POST['value'] );
		$attribute_tax   = FS_Config::get_data( 'features_taxonomy' );

		if ( empty( $attribute_name ) || empty( $attribute_value ) ) {
			wp_send_json_error( [ 'message' => __( 'Название атрибута или значение не может быть пустым!' ) ] );
		}

		$term_parent = term_exists( $attribute_name, $attribute_tax, 0 );

		if ( ! $term_parent ) {
			$term_parent = wp_insert_term( $attribute_name, $attribute_tax, [
				'parent' => 0
			] );
		}

		if ( is_wp_error( $term_parent ) || ! $term_parent ) {
			wp_send_json_error( [ 'message' => $term_parent->get_error_message() ] );
		}

		$term_child = term_exists( $attribute_value, $attribute_tax, $term_parent['term_id'] );

		if ( ! $term_child ) {
			$term_child = wp_insert_term( $attribute_value, $attribute_tax, [
				'parent' => $term_parent['term_id']
			] );
		}

		if ( is_wp_error( $term_child ) ) {
			wp_send_json_error( [
				'message' => $attribute_value . ': ' . $term_child->get_error_message()
			] );
		}

		$value_term_id = (int) $term_child['term_id'];
		$set_terms     = wp_set_object_terms( $post_id, [
			$term_parent['term_id'],
			$value_term_id
		], $attribute_tax, true );
		if ( ! is_wp_error( $set_terms ) ) {
			wp_send_json_success( [ 'message' => __( 'Атрибуты успешно добавленны!', 'f-shop' ) ] );
		}

		wp_send_json_error( [
			'message' => __( 'Возникла ошибка при добавлении атрибута к товару', 'f-shop' )
		] );
	}


	/**
	 * Получаем API ключ для сайта
	 */
	public static function fs_get_api_key() {
		$response = wp_remote_post( 'https://api.f-shop.top/site/create', array(
			'body'      => array(
				'domain'      => $_SERVER['HTTP_HOST'],
				'admin_email' => get_option( 'admin_email' )
			),
			'sslverify' => true
		) );

		// проверка ошибки
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Что-то пошло не так: $error_message";
			wp_die();
		} else {
			$body = wp_remote_retrieve_body( $response );

			echo $body;
			wp_die();
		}

	}


	/**
	 * Live product search callback function
	 */
	function livesearch_callback() {

		$find_posts = get_posts( array(
			's'              => sanitize_text_field( $_POST['search'] ),
			'posts_per_page' => - 1,
			'post_type'      => FS_Config::get_data( 'post_type' )
		) );
		$out        = '';
		if ( $find_posts ) {
			$out .= '<ul class="fs-livesearch-data">';
			foreach ( $find_posts as $find_post ) {
				$out .= '<li><a href="' . get_permalink( $find_post->ID ) . '">' . apply_filters( 'the_title', $find_post->post_title ) . ' <span>(' . fs_get_price( $find_post->ID ) . ' ' . fs_currency( $find_post->ID ) . ')</span></a>';
			}
			$out .= '</ul>';
			wp_send_json_success( array( 'html' => $out ) );
		}
		wp_send_json_error();
	}

	/**
	 * Live product search callback function
	 */
	function search_product_admin() {
		$find_posts = get_posts( array(
			's'              => sanitize_text_field( $_POST['search'] ),
			'posts_per_page' => 12,
			'post_type'      => FS_Config::get_data( 'post_type' )
		) );

		if ( $find_posts ) {
			wp_send_json_success( array_map( function ( $item ) {
				return fs_set_product( [ 'ID' => $item->ID, 'count' => 1, 'attr' => [] ] );
			}, $find_posts ) );
		}

		wp_send_json_error();
	}


	/**
	 * Sends a message to the admin to notify the user about the availability of goods
	 */
	function report_availability() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}
		$email = sanitize_email( $_POST['email'] );
		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( array( 'msg' => __( 'Please enter a valid email address', 'f-shop' ) ) );
		}
		$subject = __( 'Просьба уведомить о наличии товара', 'f-shop' );
		$msg     = sprintf( __( 'User %s requests to be notified of the availability of the product "%s". Product Link: %s', 'f-shop' ), $email, $_POST['product_name'], $_POST['product_url'] );
		$headers = array(
			sprintf(
				'From: %s <%s>',
				fs_option( 'name_sender', get_bloginfo( 'name' ) ),
				fs_option( 'email_sender', 'shop@' . $_SERVER['SERVER_NAME'] )
			)
		);
		if ( wp_mail( fs_option( 'manager_email', get_option( 'admin_email' ) ), $subject, $msg, $headers ) ) {
			wp_send_json_success( array(
				'msg'     => __( 'Your request has been sent successfully!', 'f-shop' ),
				'post'    => $_POST,
				'headers' => $headers
			) );
		} else {
			wp_send_json_error( array(
				'msg'     => __( 'There was an error sending a letter to the site administrator!', 'f-shop' ),
				'post'    => $_POST,
				'headers' => $headers
			) );
		}


	}



	// Возвращает HTML код галереи товара или конкретной вариации
	// TODO : добавить nonce проверку
	function fs_get_product_gallery_ids() {

		$product_id   = intval( $_POST['product_id'] );
		$variation_id = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : null;

		$gallery = '';
		// Получаем галерею вариативного товара
		if ( $product_id && $variation_id ) {
			$product_class = new FS_Product();
			$variations    = $product_class->get_product_variations( $product_id );

			if ( ! empty( $variations[ $variation_id ]['gallery'] ) ) {
				foreach ( $variations[ $variation_id ]['gallery'] as $image ) {
					$image   = wp_get_attachment_image_url( $image, 'full' );
					$title   = get_the_title( $product_id );
					$gallery .= '<li data-thumb="' . esc_url( $image ) . '"  data-src="' . esc_url( $image ) . '"><a href="' . esc_url( $image ) . '" data-lightbox="roadtrip" data-title="' . esc_attr( $title ) . '"><img src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '" itemprop="' . esc_url( $image ) . '" data-zoom-image="' . esc_url( $image ) . '"></a></li>';
				}
			}
		} else {
			// иначе возвращаем основную галерею товара
			$images_class = new FS_Images_Class();
			$gallery      .= $images_class->list_gallery( $product_id );
		}

		if ( ! empty( $gallery ) ) {
			wp_send_json_success( array(
				'gallery' => $gallery
			) );
		}

		wp_send_json_error();
	}

	// возвращает шаблон, работает на основе get_template_part()
	function fs_get_template_part() {
		ob_start();
		$index = intval( $_POST['index'] );
		require_once( FS_PLUGIN_PATH . 'templates/back-end/metabox/product-variations/single-attr.php' );
		$template = ob_get_clean();
		wp_send_json_success( array( 'template' => $template ) );
	}

	/**
	 * Обновление позиции товаров
	 */
	function fs_update_position_callback() {
		global $wpdb;
		$ids = array_map( 'intval', $_POST['ids'] );

		// ставим позицию 99999, то есть в самом конце для постов с позицией 0 или меньше
		$posts = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE menu_order<=0 AND post_type='product'" );
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$wpdb->update( $wpdb->posts, array( 'menu_order' => 99999 ), array( 'ID' => $post->ID ) );
			}
		}

		// для выбранных записей устанавливаем позиции согласно сортировке
		if ( count( $ids ) ) {
			foreach ( $ids as $position => $id ) {
				$data = array(
					'ID'         => $id,
					'menu_order' => $position + 1
				);
				wp_update_post( $data );
			}
		}
		echo json_encode( array( "status" => 1 ) );
		exit();
	}

	/**
	 * setting a product rating callback function
	 */
	function fs_set_rating_callback() {
		if ( ! empty( $_POST ) ) {
			$product_id     = intval( $_POST['product'] );
			$product_rating = intval( $_POST['value'] );
			add_post_meta( $product_id, 'fs_product_rating', $product_rating );

		}
		exit();
	}


	/**
	 * Linking an attribute to a product
	 */
	function fs_add_att_callback() {
		$features_taxonomy = FS_Config::get_data( 'features_taxonomy' );
		$term_id           = intval( $_POST['term'] );
		$post_id           = intval( $_POST['post'] );

		$post_terms = wp_set_post_terms( $post_id, $term_id, $features_taxonomy, true );

		if ( is_wp_error( $post_terms ) ) {
			wp_send_json_error( [ 'message' => $post_terms->get_error_message() ] );
		} elseif ( $post_terms === false ) {
			wp_send_json_error( [ 'message' => __( 'An unexpected error occurred while attaching the attribute to the product.', 'f-shop' ) ] );
		} else {
			wp_send_json_success( [
				'term_name' => get_term_field( 'name', $term_id, $features_taxonomy ),
				'message'   => __( 'Attribute successfully attached to product', 'f-shop' )
			] );
		}

		wp_send_json_error( [ 'message' => __( 'An unexpected error occurred while attaching the attribute to the product.', 'f-shop' ) ] );
	}

	/**
	 * Коллбек функция для поиска варианта покупки
	 */
	function fs_get_variated_callback() {
		$product      = new FS_Product();
		$product_id   = intval( $_POST['product_id'] );
		$current_attr = intval( $_POST['current'] );
		$atts         = array_map( 'intval', $_POST['atts'] );
		$variations   = $product->get_product_variations( $product_id );

		$matched_options = []; // Совпавшие варианты

		// сначала ищем совпадение по всем атрибутам, т.е. массив присланных атрибутов и и атрибутов вариации должны совпадать
		if ( ! count( $atts ) || ! count( $variations ) ) {
			wp_send_json_error( [ 'msg' => __( 'Goods with such a set of characteristics are not in stock. Try changing parameters.', 'f-shop' ) ] );
		}

		foreach ( $variations as $k => $variant ) {
			$variant_atts = array_map( 'intval', $variant['attr'] );
			// ищем совпадения варианов в присланными значениями
			if ( fs_in_array_multi( $variant_atts, $atts ) ) {
				$matched_options[ $k ] = array(
					'variation'    => $k,
					'price'        => floatval( str_replace( ',', '.', $variant['price'] ) ),
					'action_price' => floatval( str_replace( ',', '.', $variant['action_price'] ) )
				);

			}

		}

		// Если есть хоть один совпавший вариант
		// TODO: В дальнейшем если есть несколько совпавших вариантов выводить доп. окно с уточнением
		if ( count( $matched_options ) && is_array( $matched_options ) ) {
			$matched_options = array_shift( $matched_options );
			$price           = apply_filters( 'fs_price_filter', $product_id, $matched_options['price'] );
			$action_price    = apply_filters( 'fs_price_filter', $product_id, $matched_options['action_price'] );
			$base_price      = null;

			if ( $action_price > 0 && $action_price < $price ) {
				$base_price = $price;
				$price      = $action_price;

			}
			wp_send_json_success( array(
				'options'   => $matched_options,
				'price'     => $price ? sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', $price ), fs_currency() ) : 0,
				'basePrice' => $base_price ? sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', $base_price ), fs_currency() ) : ''
			) );
		}

		wp_send_json_error( [ 'msg' => __( 'Goods with such a set of characteristics are not in stock. Try changing parameters.', 'f-shop' ) ] );

	}


	/**
	 * Добавление варианта цены. колбек функция
	 */
	function fs_add_variant_callback() {
		$template_path = FS_PLUGIN_PATH . 'templates/back-end/metabox/product-variations/add-variation.php';
		if ( file_exists( $template_path ) ) {
			ob_start();
			$index = intval( $_POST['index'] );
			include( $template_path );
			$template = ob_get_contents();
			ob_clean();
			wp_send_json_success( array( 'template' => $template ) );
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * удаляет один термин (свойство) товара
	 */
	function fs_remove_product_term_callback() {
		$fs_config = new FS_Config();
		$output    = array_map( 'sanitize_text_field', $_POST );
		$remove    = wp_remove_object_terms( (int) $output['product_id'], (int) $output['term_id'], $fs_config->data['features_taxonomy'] );
		if ( $remove ) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * добавление товара к сравнению
	 */
	function fs_add_to_comparison_callback() {
		session_start();
		unset( $_SESSION['fs_comparison_list'] );
		if ( ! empty( $_SESSION['fs_comparison_list'] ) && is_array( $_SESSION['fs_comparison_list'] ) && ! in_array( (int) $_POST['product_id'], $_SESSION['fs_comparison_list'] ) ) {
			$_SESSION['fs_comparison_list'] = array_unshift( $_SESSION['fs_comparison_list'], (int) $_POST['product_id'] );
		} else {
			$_SESSION['fs_comparison_list'][] = (int) $_POST['product_id'];

		}

// Устанавливаем Cookie до конца сессии:
		setcookie( "fs_comparison_list", serialize( $_SESSION['fs_comparison_list'] ), 30 * DAYS_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
		echo json_encode( array(
			'status' => true
		) );
		exit();
	}


	/**
	 *Отправка заказа в базу, на почту админа и заказчика
	 */
	function order_send_ajax() {
		global $wpdb;
		$fs_config                 = new FS_Config();
		$fs_template               = new FS_Template();
		$order_create_time         = time();
		$order_create_date_display = date_i18n( 'd F Y H:i', $order_create_time );
		$form_data                 = array_map( 'trim', $_POST );
		$update_user_meta          = isset( $_POST['fs_update_user_meta'] ) ? boolval( $_POST['fs_update_user_meta'] ) : true;
		$order_status_id           = intval( fs_option( 'fs_default_order_status' ) );


		// Set new order status
		$order_status = 'new';
		if ( $order_status_id ) {
			$order_status_term = get_term_field( 'slug', $order_status_id, FS_Config::get_data( 'order_statuses_taxonomy' ) );

			if ( ! is_wp_error( $order_status_term ) ) {
				$order_status = $order_status_term;
			}
		}

		// Валидация данных запроса
		// Проверяем происходит ли запрос от нашего сайта
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}

		// Проверяем минимальную сумму заказа, если указано
		if ( fs_option( 'fs_minimum_order_amount', 0 )
		     && fs_get_total_amount( false ) < fs_option( 'fs_minimum_order_amount', 0 ) ) {
			wp_send_json_error( array(
				'msg' => sprintf( __( 'Минимальная сумма заказа %s %s', 'f-shop' ),
					fs_option( 'fs_minimum_order_amount', 0 ),
					fs_currency()
				)
			) );
		}

		// IP адрес покупателя
		$customer_ip = fs_get_user_ip();

		// Ищем покупателя в черном списке
		$search_blacklist = $wpdb->get_var( "SELECT COUNT({$wpdb->posts}.ID) FROM $wpdb->postmeta LEFT JOIN $wpdb->posts ON {$wpdb->postmeta}.post_id={$wpdb->posts}.ID  WHERE post_status='black_list' AND {$wpdb->postmeta}.meta_key='_customer_ip' AND {$wpdb->postmeta}.meta_value='$customer_ip'" );

		$product_class = new FS_Product();
		$fs_products   = FS_Cart::get_cart();

		$fs_custom_products = ! empty( $_POST['fs_custom_product'] ) ? serialize( $_POST['fs_custom_product'] ) : '';
		$user_id            = 0;
		$delivery_cost      = floatval( get_term_meta( intval( $_POST['fs_delivery_methods'] ), '_fs_delivery_cost', 1 ) );
		$sum                = fs_get_total_amount( $delivery_cost );
		$wpdb->show_errors(); // включаем показывать ошибки при работе с базой

		//Производим очистку полученных данных с формы заказа
		$form_fields    = FS_Users::get_user_fields();
		$sanitize_field = array();

		foreach ( $form_fields as $field_name => $form_field ) {
			// Пропускаем поля которые не должны быть в оформлении заказа
			if ( ! isset( $form_field['checkout'] ) || ( isset( $form_field['checkout'] ) && $form_field['checkout'] == false ) ) {
				continue;
			}

			// Очищаем номер телефона от ненужных символов
			if ( $field_name == 'fs_phone' ) {
				$sanitize_field[ $field_name ] = preg_replace( "/[^0-9]/", '', $form_data[ $field_name ] );
				continue;
			}

			// Если  тип поля email, то очищаем его от ненужных символов
			if ( $form_field['type'] == 'email' ) {
				$sanitize_field[ $field_name ] = sanitize_email( $form_data[ $field_name ] );
				continue;
			}

			$sanitize_field[ $field_name ] = isset( $form_data[ $field_name ] ) ? $form_data[ $field_name ] : '';
		}

		// проверяем существование пользователя
		if ( is_user_logged_in() ) {
			$user    = wp_get_current_user();
			$user_id = $user->ID;
		} else {
			if ( ! empty( $sanitize_field['fs_customer_register'] ) && $sanitize_field['fs_customer_register'] == 1 ) {
				// Если пользователь не залогинен пробуем его зарегистрировать
				$new_user = register_new_user( $sanitize_field['fs_email'], $sanitize_field['fs_email'] );
				if ( ! is_wp_error( $new_user ) ) {
					$user_id = $new_user;
				} else {
					if ( $new_user->get_error_code() == 'email_exists' || $new_user->get_error_code() == 'username_exists' ) {
						$error_text = sprintf( __( 'User with such E-mail or Login is registered on the site. <a href="#fs-modal-login" data-fs-action="modal">Login to site</a>. <a href="%s">Forgot your password?</a>', 'f-shop' ), wp_lostpassword_url( get_permalink() ) );
					} else {
						$error_text = $new_user->get_error_message();
					}

					wp_send_json_error( [ 'msg' => $error_text ] );
				}
			}

		}

		// обновляем мета поля пользователя
		if ( $user_id && $update_user_meta ) {
			$user_data = [
				'ID' => $user_id,
			];
			if ( ! empty( $sanitize_field['fs_first_name'] ) ) {
				$user_data['first_name'] = $sanitize_field['fs_first_name'];
			}
			if ( ! empty( $sanitize_field['fs_first_name'] ) ) {
				$user_data['last_name'] = $sanitize_field['fs_last_name'];
			}
			wp_update_user( $user_data );

			// Сохраняем мета поля пользователя
			foreach ( $sanitize_field as $key => $user_meta ) {
				if ( ! empty( $sanitize_field[ $key ] ) && ! empty( $user_meta['save_meta'] ) ) {
					update_user_meta( $user_id, $key, $sanitize_field[ $key ] );
				}
			}
		}

		// Добавляем покупателя в базу
		$wpdb->insert( $wpdb->prefix . 'fs_customers', [
			'user_id'    => $user_id,
			'first_name' => $sanitize_field['fs_first_name'],
			'last_name'  => $sanitize_field['fs_last_name'],
			'email'      => $sanitize_field['fs_email'],
			'phone'      => $sanitize_field['fs_phone'],
			'address'    => $sanitize_field['fs_address'],
			'ip'         => $customer_ip,
			'group'      => 1,
		] );
		$customer_id = $wpdb->insert_id;

		// Вставляем заказ в базу данных
		$pay_method     = $sanitize_field['fs_payment_methods'] ? get_term( intval( $sanitize_field['fs_payment_methods'] ), $fs_config->data['product_pay_taxonomy'] ) : null;
		$new_order_data = array(
			'post_title'   => $sanitize_field['fs_first_name'] . ' ' . $sanitize_field['fs_last_name'] . ' / ' . date( 'd.m.Y H:i' ),
			'post_content' => '',
			'post_status'  => $search_blacklist ? 'black_list' : $order_status,
			'post_type'    => FS_Config::get_data( 'post_type_orders' ),
			'post_author'  => 1,
			'ping_status'  => get_option( 'default_ping_status' ),
			'post_parent'  => 0,
			'menu_order'   => 0,
			'import_id'    => 0,
			'meta_input'   => array(
				'_user_id'         => $user_id,
				'_customer_ip'     => $customer_ip,
				'_customer_email'  => $sanitize_field['fs_email'],
				'_customer_phone'  => $sanitize_field['fs_phone'],
				'_order_discount'  => fs_get_total_discount(),
				'_packing_cost'    => fs_get_packing_cost(),
				'_customer_id'     => $customer_id,
				'_user'            => array(
					'id'         => $user_id,
					'first_name' => $sanitize_field['fs_first_name'],
					'last_name'  => $sanitize_field['fs_last_name'],
					'email'      => $sanitize_field['fs_email'],
					'phone'      => $sanitize_field['fs_phone']
				),
				'city'             => $sanitize_field['fs_city'],
				'_products'        => $fs_products,
				'_custom_products' => $fs_custom_products,
				'_delivery'        => array(
					'method'    => $sanitize_field['fs_delivery_methods'] ? $sanitize_field['fs_delivery_methods'] : 0,
					'secession' => $sanitize_field['fs_delivery_number'],
					'address'   => $sanitize_field['fs_address']
				),
				'_payment'         => $pay_method && isset( $pay_method->term_id ) ? $pay_method->term_id : 0,
				'_amount'          => $sum,
				'_comment'         => $sanitize_field['fs_comment']
			),

		);
		$order_id       = wp_insert_post( $new_order_data );


		/* Если есть ошибки выводим их*/
		if ( is_wp_error( $order_id ) ) {
			wp_send_json_error( [ 'msg' => $order_id->get_error_message() ] );
		} else {
			// устанавливаем новый запас товаров на складе
			if ( fs_option( 'fs_in_stock_manage' ) ) {
				foreach ( $fs_products as $fs_product ) {
					$variation = isset( $fs_product['variation'] ) && is_numeric( $fs_product['variation'] ) ? $fs_product['variation'] : null;
					$product_class->fs_change_stock_count( $fs_product['ID'], $fs_product['count'], $variation );
				}
			}
			// Здесь уже можно навешивать сторонние обработчики
			do_action( 'fs_create_order', $order_id );

			$_SESSION['fs_last_order_id']  = $order_id;
			$_SESSION['fs_last_order_pay'] = $pay_method ? $pay_method->slug : 0;

			$customer_mail_subject = fs_option( 'customer_mail_header', sprintf( __( 'Order goods on the site "%s"', 'f-shop' ), get_bloginfo( 'name' ) ) );

			// Здесь мы определяем переменные для шаблона письма
			$mail_data = [
				// Cart data
				'order_date'        => $order_create_date_display,
				'order_id'          => $order_id,
				'cart_discount'     => sprintf( '%s %s', apply_filters( 'fs_price_format', fs_get_total_discount() ), fs_currency() ),
				'cart_amount'       => sprintf( '%s %s', apply_filters( 'fs_price_format', $sum ), fs_currency() ),
				'delivery_cost'     => sprintf( '%s %s', apply_filters( 'fs_price_format', $delivery_cost ), fs_currency() ),
				'products_cost'     => sprintf( '%s %s', apply_filters( 'fs_price_format', fs_get_cart_cost() ), fs_currency() ),
				'packing_cost'      => sprintf( '%s %s', apply_filters( 'fs_price_format', fs_get_packing_cost() ), fs_currency() ),
				'delivery_method'   => $sanitize_field['fs_delivery_methods'] ? fs_get_delivery( $sanitize_field['fs_delivery_methods'] ) : '',
				'delivery_number'   => $sanitize_field['fs_delivery_number'],
				'payment_method'    => $pay_method && isset( $pay_method->name ) ? $pay_method->name : '',
				'cart_items'        => fs_get_cart(),
				'order_title'       => $customer_mail_subject,
				'order_edit_url'    => admin_url( 'post.php?post=' . $order_id . '&action=edit' ),

				// Site data
				'site_name'         => get_bloginfo( 'name' ),
				'home_url'          => home_url( '/' ),
				'dashboard_url'     => fs_account_url(),
				'admin_email'       => get_option( 'admin_email' ),
				'contact_email'     => fs_option( 'manager_email', get_option( 'admin_email' ) ),
				'contact_phone'     => fs_option( 'contact_phone' ),
				'mail_logo'         => fs_option( 'site_logo' ) ? wp_get_attachment_image_url( fs_option( 'site_logo' ), 'full' ) : '',
				'social_links'      => [],

				// Client data
				'client_city'       => $sanitize_field['fs_city'],
				'client_address'    => $sanitize_field['fs_address'],
				'client_phone'      => $sanitize_field['fs_phone'],
				'client_email'      => $sanitize_field['fs_email'],
				'client_first_name' => $sanitize_field['fs_first_name'],
				'client_last_name'  => $sanitize_field['fs_last_name'],
				'client_id'         => $user_id,
			];

			$mail_data = apply_filters( 'fs_create_order_mail_data', $mail_data );


			// Проверяем включен ли тестовый режим
			if ( fs_option( 'fs_test_mode' ) ) {
				//Отсылаем письмо с данными заказа заказчику
				$admin_email        = get_bloginfo( 'admin_email' );
				$user_email_message = $fs_template->get( 'mail/user-create-order', $mail_data );

				FS_Form::send_email( $admin_email, $customer_mail_subject, $user_email_message );

				//Отсылаем письмо с данными заказа на почту указанную в настроках для оповещения о заказах
				$admin_mail_subject = fs_option( 'admin_mail_header', sprintf( __( 'Order goods on the site "%s"', 'f-shop' ), get_bloginfo( 'name' ) ) );
				$admin_message      = $fs_template->get( 'mail/admin-create-order', $mail_data );
				FS_Form::send_email( $admin_email, $admin_mail_subject, $admin_message );
			} else {
				if ( $sanitize_field['fs_email'] ) {
					//Отсылаем письмо с данными заказа заказчику
					$user_email_message = $fs_template->get( 'mail/user-create-order', $mail_data );
					FS_Form::send_email( $sanitize_field['fs_email'], $customer_mail_subject, $user_email_message );
				}

				//Отсылаем письмо с данными заказа на почту указанную в настроках для оповещения о заказах
				$admin_mail_subject = fs_option( 'admin_mail_header', sprintf( __( 'Order goods on the site "%s"', 'f-shop' ), get_bloginfo( 'name' ) ) );
				$admin_message      = $fs_template->get( 'mail/admin-create-order', $mail_data );
				FS_Form::send_email( fs_option( 'manager_email', get_option( 'admin_email' ) ), $admin_mail_subject, $admin_message );
			}

			/* обновляем название заказа для админки */
			wp_update_post( array(
					'ID'         => $order_id,
					'post_title' => sprintf(
						__( 'Order #%d from %s %s (%s)', 'f-shop' ),
						$order_id, $sanitize_field['fs_first_name'], $sanitize_field['fs_last_name'], date( 'd.m.y H:i', time() ) )
				)
			);

			$redirect_to   = $pay_method && get_term_meta( $pay_method->term_id, '_fs_checkout_redirect', 1 ) ? 'page_payment' : 'page_success';
			$redirect_link = get_permalink( fs_option( $redirect_to ) );
			$result        = array(
				'msg'      => sprintf( __( 'Order #%d successfully added', 'f-shop' ), $order_id ),
				'products' => $fs_products,
				'order_id' => $order_id,
				'sum'      => $sum,
				'redirect' => $redirect_link
			);
			unset( $_SESSION['cart'] );
			wp_send_json_success( $result );
		}

		wp_send_json_error( [ 'msg' => __( 'Errors occurred while creating an order', 'f-shop' ) ] );
	}

	/**
	 * Метод ajax добавления товара в список желаний
	 */
	public function fs_addto_wishlist() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}
		$product_id                             = (int) $_REQUEST['product_id'];
		$_SESSION['fs_wishlist'][ $product_id ] = $product_id;

		wp_send_json_success( array(
			'body'   => fs_frontend_template( 'wishlist/wishlist' ),
			'status' => true
		) );
	}

	public function fs_del_wishlist_pos() {
		$product_id = (int) $_REQUEST['position'];
		$res        = '';
		unset( $_SESSION['fs_user_settings']['fs_wishlist'][ $product_id ] );
		$wishlist = ! empty( $_SESSION['fs_user_settings']['fs_wishlist'] ) ? $_SESSION['fs_user_settings']['fs_wishlist'] : array();
		$count    = count( $wishlist );
		$class    = $count == 0 ? '' : 'wishlist-show';
		$res      .= '<a href="#" class="hvr-grow"><i class="icon icon-heart"></i><span>' . $count . '</span></a>
<ul class="fs-wishlist-listing ' . $class . '">
  <li class="wishlist-header">' . __( 'Wishlist', 'cube44' ) . ': <i class="fa fa-times-circle" aria-hidden="true"></i>
  </li>
  ';
		foreach ( $_SESSION['fs_user_settings']['fs_wishlist'] as $key => $value ) {
			$res .= "
  <li><i class=\"fa fa-trash\" aria-hidden=\"true\" data-fs-action=\"wishlist-delete-position\" data-product-id=\"$key\"
    data-product-name=\"" . get_the_title( $key ) . "\" ></i> <a href=\"" . get_permalink( $key ) . "\">" .
			        get_the_title( $key ) . "</a></li>
  ";
		}
		$res .= '
</ul>';

		if ( ! empty( $res ) ) {
			echo json_encode( array(
				'body' => $res,
				'type' => 'success'
			) );
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
		$body .= '
  <option value="">Выберите товар</option>
  ';
		if ( $posts ) {
			foreach ( $posts as $key => $post ) {
				$body .= '
  <option value="' . $post->ID . '">' . $post->post_title . '</option>
  ';
			}
		}
		$body .= '</select>';

		echo json_encode( array( 'body' => $body ) );
		exit;
	}


	/**
	 * Подгружает стоимость доставки, поля которые нужно скрыть в оформлении покупки
	 */
	function fs_show_shipping_callback() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}
		$term_id             = intval( $_POST['delivery'] );
		$delivery_cost_clean = floatval( get_term_meta( $term_id, '_fs_delivery_cost', 1 ) );
		$delivery_cost       = sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', $delivery_cost_clean ), fs_currency() );
		$total_amount        = sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', fs_get_total_amount( $delivery_cost_clean ) ), fs_currency() );
		$total               = $delivery_cost_clean + fs_get_cart_cost();

		ob_start();
		fs_taxes_list( array( 'wrapper' => false ), $total );
		$taxes_out = ob_get_clean();

		$disable_fields = get_term_meta( $term_id, '_fs_disable_fields', 0 );
		$disable_fields = ! empty( $disable_fields ) ? array_shift( $disable_fields ) : [];

		$required_fields = get_term_meta( $term_id, '_fs_required_fields', 0 );
		$required_fields = ! empty( $required_fields ) ? array_shift( $required_fields ) : [];

		wp_send_json_success( array(
			'disableFields'  => $disable_fields,
			'requiredFields' => $required_fields,
			'taxes'          => $taxes_out,
			'price'          => $delivery_cost,
			'total'          => $total_amount,
		) );
	}
} 