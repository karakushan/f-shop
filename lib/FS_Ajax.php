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

			// Clean Wishlist
			add_action( 'wp_ajax_fs_clean_wishlist', array( $this, 'fs_clean_wishlist' ) );
			add_action( 'wp_ajax_nopriv_fs_clean_wishlist', array( $this, 'fs_clean_wishlist' ) );

			add_action( 'wp_ajax_fs_add_wishlist_to_cart', array( $this, 'fs_add_wishlist_to_cart' ) );
			add_action( 'wp_ajax_nopriv_fs_add_wishlist_to_cart', array( $this, 'fs_add_wishlist_to_cart' ) );

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
			add_action( 'wp_ajax_nopriv_fs_livesearch', array( $this, 'livesearch_callback' ) );

			// Live product search in admin
			add_action( 'wp_ajax_fs_search_product_admin', array( $this, 'search_product_admin' ) );
			add_action( 'wp_ajax_nopriv_fs_search_product_admin', array( $this, 'search_product_admin' ) );

			// Add new order and send e-mail
			add_action( 'wp_ajax_order_send', array( $this, 'fs_order_create' ) );
			add_action( 'wp_ajax_nopriv_order_send', array( $this, 'fs_order_create' ) );

			// fs_clone_order
			add_action( 'wp_ajax_fs_clone_order', array( $this, 'fs_clone_order' ) );
			add_action( 'wp_ajax_nopriv_fs_clone_order', array( $this, 'fs_clone_order' ) );

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

			// fs_add_child_attribute action
			add_action( 'wp_ajax_fs_add_child_attribute', array( $this, 'fs_add_child_attribute_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_add_child_attribute', array( $this, 'fs_add_child_attribute_callback' ) );

			// action fs_get_post_attributes
			add_action( 'wp_ajax_fs_get_post_attributes', array( $this, 'fs_get_post_attributes_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_get_post_attributes', array( $this, 'fs_get_post_attributes_callback' ) );

			// action fs_detach_attribute
			add_action( 'wp_ajax_fs_detach_attribute', array( $this, 'fs_detach_attribute_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_detach_attribute', array( $this, 'fs_detach_attribute_callback' ) );

			// fs_attach_attribute
			add_action( 'wp_ajax_fs_attach_attribute', array( $this, 'fs_attach_attribute_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_attach_attribute', array( $this, 'fs_attach_attribute_callback' ) );

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

			// fs_get_terms
			add_action( 'wp_ajax_fs_get_terms', array( $this, 'fs_get_terms_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_get_terms', array( $this, 'fs_get_terms_callback' ) );

			// get attribute filters for product category
			add_action( 'wp_ajax_fs_get_category_attributes', array( $this, 'fs_get_category_attributes' ) );
			add_action( 'wp_ajax_nopriv_fs_get_category_attributes', array( $this, 'fs_get_category_attributes' ) );

			// fs_calculate_price
			add_action( 'wp_ajax_fs_calculate_price', array( $this, 'fs_calculate_price_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_calculate_price', array( $this, 'fs_calculate_price_callback' ) );

			// fs_get_max_min_price
			add_action( 'wp_ajax_fs_get_max_min_price', array( $this, 'fs_get_max_min_price_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_get_max_min_price', array( $this, 'fs_get_max_min_price_callback' ) );

			// fs_get_category_brands
			add_action( 'wp_ajax_fs_get_category_brands', array( $this, 'fs_get_category_brands_callback' ) );
			add_action( 'wp_ajax_nopriv_fs_get_category_brands', array( $this, 'fs_get_category_brands_callback' ) );

			// fs_get_product_comments
			add_action( 'wp_ajax_fs_get_product_comments', array( $this, 'fs_get_product_comments' ) );
			add_action( 'wp_ajax_nopriv_fs_get_product_comments', array( $this, 'fs_get_product_comments' ) );

			// fs_send_product_comment
			add_action( 'wp_ajax_fs_send_product_comment', array( $this, 'fs_send_product_comment' ) );
			add_action( 'wp_ajax_nopriv_fs_send_product_comment', array( $this, 'fs_send_product_comment' ) );

			// fs_comment_like_dislike
			add_action( 'wp_ajax_fs_comment_like_dislike', array( $this, 'fs_comment_like_dislike' ) );
			add_action( 'wp_ajax_nopriv_fs_comment_like_dislike', array( $this, 'fs_comment_like_dislike' ) );
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
			wp_send_json_success( [
				'term'    => [
					'id'           => $value_term_id,
					'name'         => $attribute_name,
					'parent'       => $term_parent['term_id'],
					'children'     => [
						[
							'id'   => $value_term_id,
							'name' => $attribute_value
						]
					],
					'children_all' => array_map( function ( $child ) {
						return [
							'id'     => $child->term_id,
							'name'   => $child->name,
							'parent' => $child->parent,
						];
					}, get_terms( [
						'parent'     => $term_parent['term_id'],
						'hide_empty' => false,
						'taxonomy'   => $attribute_tax
					] ) )
				],
				'message' => __( 'Атрибуты успешно добавленны!', 'f-shop' )
			] );
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
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}

		if ( empty( $_POST['search'] ) ) {
			wp_send_json_error( [ 'items' => [] ] );
		}

		$find_posts = get_posts( array(
			's'              => sanitize_text_field( $_POST['search'] ),
			'posts_per_page' => - 1,
			'post_type'      => FS_Config::get_data( 'post_type' )
		) );

		if ( ! empty( $find_posts ) ) {
			$find_posts = array_map( function ( $item ) {
				return [
					'title'      => apply_filters( 'the_title', $item->post_title ),
					'link'       => get_the_permalink( $item->ID ),
					'thumbnail'  => fs_get_product_thumbnail_url( $item->ID ),
					'price'      => fs_get_price( $item->ID ),
					'base_price' => fs_get_base_price( $item->ID ),
					'currency'   => fs_currency( $item->ID ),
					'excerpt'    => strip_tags( get_the_excerpt( $item->ID ) )
				];
			}, $find_posts );
			wp_send_json_success( [ 'items' => $find_posts ] );
		}

		wp_send_json_error( [ 'items' => [] ] );
	}

	/**
	 * Live product search callback function
	 */
	function search_product_admin() {
		$s = trim( $_POST['search'] );

		// Поиск по названию
		$find_posts = get_posts( array(
			's'              => sanitize_text_field( $s ),
			'posts_per_page' => 12,
			'post_type'      => FS_Config::get_data( 'post_type' ),
		) );

		// Поиск по ID
		if ( ! $find_posts && is_numeric( $s ) ) {
			$find_posts = get_posts( array(
				'p'              => absint( $s ),
				'posts_per_page' => 1,
				'post_type'      => FS_Config::get_data( 'post_type' ),
			) );
		}

		// Поиск по артикулу
		if ( ! $find_posts ) {
			$find_posts = get_posts( array(
				'posts_per_page' => 12,
				'post_type'      => FS_Config::get_data( 'post_type' ),
				'meta_query'     => [
					[
						'key'     => 'fs_articul',
						'value'   => $s,
						'compare' => '='
					]
				]
			) );
		}

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
			$gallery      .= $images_class->product_gallery_list( $product_id );
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
		if ( ! empty( $_POST['product'] ) && ! empty( $_POST['value'] ) ) {
			$product_id     = intval( $_POST['product'] );
			$product_rating = intval( $_POST['value'] );
			add_post_meta( $product_id, 'fs_product_rating', $product_rating );
			wp_send_json_success( array(
				'msg'   => __( 'Rating successfully set!', 'f-shop' ),
				'title' => __( 'Success', 'f-shop' )
			) );
		}

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
			$price           = apply_filters( 'fs_price_filter', $matched_options['price'], $product_id );
			$action_price    = apply_filters( 'fs_price_filter', $matched_options['action_price'], $product_id );
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
	 * Performs validation of the fields sent from the order form
	 *
	 * @return array
	 */
	public static function validate_checkout_fields() {
		if ( FS_Cart::has_empty() ) {
			wp_send_json_error( [ 'msg' => __( 'Корзина пуста!', 'f-shop' ) ] );
		}

		$errors          = [];
		$checkout_fields = apply_filters( 'fs_checkout_validate_fields', FS_Users::get_user_fields() );
		$form_fields     = array_filter( $checkout_fields, function ( $item ) {
			return isset( $item['checkout'] ) && $item['checkout'] === true;
		} );

		foreach ( $form_fields as $key => $form_field ) {
			if ( isset( $form_field['required'] ) && $form_field['required'] && trim( $_POST[ $key ] ) == '' ) {
				$errors[ $key ] = sprintf( __( 'The "%s" field is required!', 'f-shop' ), $form_field['name'] );
			}
		}

		array_walk( $form_fields, function ( &$fields, $key ) use ( $form_fields ) {
			$item = $form_fields[ $key ];
			if ( $item['type'] == 'tel' || $item['type'] == 'number' ) {
				$fields = preg_replace( "/[^0-9]/", '', $_POST[ $key ] );
			} elseif ( $item['type'] == 'email' ) {
				$fields = sanitize_email( $_POST[ $key ] );
			} else {
				$fields = trim( $_POST[ $key ] );
			}
		} );

		if ( ! empty( $errors ) ) {
			wp_send_json_error( [ 'errors' => $errors ] );
		}

		return $form_fields;
	}

	function replace_mail_variables( $message, $mail_data ) {
		return str_replace( array_map( function ( $item ) {
			return '%' . $item . '%';
		}, array_keys( $mail_data ) ), array_values( $mail_data ), $message );
	}

	function extract_text_by_locale( $text, $locale = '' ) {
		$locale = $locale ? $locale : get_locale();
		preg_match( '/<' . $locale . '>(.*?)<\/' . $locale . '>/', $text, $matches );

		return isset( $matches[1] ) ? $matches[1] : $text;
	}

	/**
	 * Ajax order creation
	 * @throws \Exception
	 */
	function fs_order_create() {
		// Checking if the request comes from our site
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}

		// Validation of order submission form data
		$sanitize_field = self::validate_checkout_fields();

		global $wpdb;
		$wpdb->show_errors( false );
		$fs_config         = new FS_Config();
		$current_date_i18n = date_i18n( 'd.m.Y H:i' );
		$order_type        = ! empty( $_POST['order_type'] ) ? $_POST['order_type'] : 'standard'; // Тип заказа обычный или быстрый
		$order_status_id   = intval( fs_option( 'fs_default_order_status' ) );
		$customer_id       = 0;

		// Set new order status
		$order_status = 'new';
		if ( $order_status_id ) {
			$order_status_term = get_term_field( 'slug', $order_status_id, FS_Config::get_data( 'order_statuses_taxonomy' ) );
			if ( ! is_wp_error( $order_status_term ) ) {
				$order_status = $order_status_term;
			}
		}

		// Проверяем минимальную сумму заказа, если указано
		if ( fs_option( 'fs_minimum_order_amount', 0 )
		     && fs_get_cart_cost() < fs_option( 'fs_minimum_order_amount', 0 ) ) {
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
		$delivery_cost      = isset( $_POST['fs_delivery_methods'] ) ? floatval( get_term_meta( intval( $_POST['fs_delivery_methods'] ), '_fs_delivery_cost', 1 ) ) : 0;
		$sum                = fs_get_total_amount( $delivery_cost );
		$discount           = fs_get_total_discount( ! empty( $_POST['fs_phone'] ) ? $_POST['fs_phone'] : '' );
		$packing_cost       = fs_get_packing_cost( absint( $_POST['fs_delivery_methods'] ) );
		$cart_cost          = fs_get_cart_cost();

		// проверяем авторизован ли пользователь
		if ( is_user_logged_in() ) {
			$user    = wp_get_current_user();
			$user_id = $user->ID;
		}

		// получаем пользователя по email
		if ( empty( $user_id ) && ! empty( $sanitize_field['fs_email'] ) ) {
			$user_id = email_exists( $sanitize_field['fs_email'] );
		}

		//  Если стоит галочка "Зарегистрироваться" и пользователь не найден
		if ( ! $user_id && ! empty( $sanitize_field['fs_customer_register'] ) && $sanitize_field['fs_customer_register'] == 1 ) {
			$user_id = FS_Users::register_user( $sanitize_field['fs_email'], '', $sanitize_field );
		}

		// Обновляем данные пользователя
		if ( $user_id ) {
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
		try {
			$wpdb->insert( $wpdb->prefix . 'fs_customers', [
				'user_id'    => $user_id,
				'first_name' => $sanitize_field['fs_first_name'],
				'last_name'  => $sanitize_field['fs_last_name'],
				'email'      => $sanitize_field['fs_email'],
				'phone'      => $sanitize_field['fs_phone'],
				'address'    => $sanitize_field['fs_address'],
				'city'       => $sanitize_field['fs_city'],
				'ip'         => $customer_ip,
				'group'      => 1,
			] );
			$customer_id = $wpdb->insert_id;

		} catch ( \Exception $e ) {
			error_log( $e->getMessage() );
		}

		// Вставляем заказ в базу данных
		$pay_method     = $sanitize_field['fs_payment_methods'] ? get_term( intval( $sanitize_field['fs_payment_methods'] ), $fs_config->data['product_pay_taxonomy'] ) : null;
		$post_title     = sprintf( '%s  %s (%s)', $sanitize_field['fs_first_name'], $sanitize_field['fs_last_name'], $current_date_i18n );
		$new_order_data = array(
			'post_title'   => $post_title,
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
				'_order_discount'  => $discount,
				'_packing_cost'    => $packing_cost,
				'_customer_id'     => $customer_id,
				'_order_type'      => $order_type,
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
				'_cart_cost'       => $cart_cost,
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
			$admin_mail_subject    = fs_option( 'admin_mail_header', sprintf( __( 'Order goods on the site "%s"', 'f-shop' ), get_bloginfo( 'name' ) ) );

			// Здесь мы определяем переменные для шаблона письма
			$mail_data = [
				// Cart data
				'order_date'               => $current_date_i18n,
				'order_id'                 => $order_id,
				'cart_discount'            => sprintf( '%s %s', apply_filters( 'fs_price_format', $discount ), fs_currency() ),
				'cart_amount'              => sprintf( '%s %s', apply_filters( 'fs_price_format', $sum ), fs_currency() ),
				'delivery_cost'            => sprintf( '%s %s', apply_filters( 'fs_price_format', $delivery_cost ), fs_currency() ),
				'products_cost'            => sprintf( '%s %s', apply_filters( 'fs_price_format', $cart_cost ), fs_currency() ),
				'packing_cost'             => sprintf( '%s %s', apply_filters( 'fs_price_format', $packing_cost ), fs_currency() ),
				'delivery_method'          => $sanitize_field['fs_delivery_methods'] ? fs_get_delivery( $sanitize_field['fs_delivery_methods'] ) : '',
				'delivery_number'          => $sanitize_field['fs_delivery_number'],
				'payment_method'           => $pay_method && isset( $pay_method->name ) ? $pay_method->name : '',
				'cart_items'               => fs_get_cart(),
				'order_title'              => $customer_mail_subject,
				'order_edit_url'           => admin_url( 'post.php?post=' . $order_id . '&action=edit' ),

				// Site data
				'site_name'                => get_bloginfo( 'name' ),
				'home_url'                 => home_url( '/' ),
				'dashboard_url'            => fs_account_url(),
				'admin_email'              => get_option( 'admin_email' ),
				'contact_email'            => fs_option( 'manager_email', get_option( 'admin_email' ) ),
				'contact_phone'            => fs_option( 'contact_phone' ),
				'contact_address'          => fs_option( 'contact_address' ),
				'mail_logo'                => fs_option( 'fs_email_logo' ) ? wp_get_attachment_image_url( fs_option( 'fs_email_logo' ), 'full' ) : '',
				'social_links'             => [],

				// Client data
				'client_city'              => $sanitize_field['fs_city'],
				'client_address'           => $sanitize_field['fs_address'],
				'address_street'           => $sanitize_field['fs_street'],
				'address_house_number'     => $sanitize_field['fs_home_num'],
				'address_entrance_number'  => $sanitize_field['fs_entrance_num'],
				'address_apartment_number' => $sanitize_field['fs_apartment_num'],
				'client_phone'             => $sanitize_field['fs_phone'],
				'client_email'             => $sanitize_field['fs_email'],
				'client_first_name'        => $sanitize_field['fs_first_name'],
				'client_last_name'         => $sanitize_field['fs_last_name'],
				'client_id'                => $user_id,

				// mail data
				'admin_mail_title'         => __( 'Congratulations! A new order has appeared on your site.', 'f-shop' ),
				'admin_mail_message'       => sprintf( __( 'On your site "%s" user %s created a new order #%d. Please contact the customer for this data:', 'f-shop' ), get_bloginfo( 'name' ), $sanitize_field['fs_first_name'] . ' ' . $sanitize_field['fs_last_name'], $order_id ),
				'customer_mail_title'      => __( 'Thank you for your order', 'f-shop' ),
				'customer_mail_message'    => sprintf( __( 'Your order #%d has been placed. We will contact you shortly.', 'f-shop' ), $order_id ),
			];

			$mail_data = apply_filters( 'fs_create_order_mail_data', $mail_data );

			// NOTIFICATION
			$notification = new FS_Notification();

			// We send a letter with order details to the customer
			$notification->set_recipients( [ $sanitize_field['fs_email'] ] );
			$notification->set_subject( $this->replace_mail_variables( $this->extract_text_by_locale( $customer_mail_subject ), $mail_data ) );
			$notification->set_template( 'mail/' . get_locale() . '/user-create-order', $mail_data );
			$notification->send();

			// Send a letter to the admin
			if ( fs_option( 'fs_notify_telegram' ) ) {
				$notification->push_channel( 'telegram' );
			}

			$admin_users = explode( ',', fs_option( 'manager_email', get_option( 'admin_email' ) ) );
			$admin_users = array_filter( array_map( 'trim', $admin_users ), 'is_email' );
			if ( count( $admin_users ) > 0 ) {
				$notification->set_recipients( $admin_users );
				$notification->set_subject( $this->replace_mail_variables( $this->extract_text_by_locale( $admin_mail_subject ), $mail_data ) );
				$notification->set_template( 'mail/' . get_locale() . '/admin-create-order', $mail_data );
				$notification->send();
			}

			/* updating the order name for the admin panel */
			wp_update_post( array(
					'ID'         => $order_id,
					'post_title' => sprintf(
						__( 'Order #%d from %s %s (%s)', 'f-shop' ),
						$order_id, $sanitize_field['fs_first_name'], $sanitize_field['fs_last_name'], $current_date_i18n )
				)
			);

			// Create a payment link
			$redirect_to   = $pay_method && get_term_meta( $pay_method->term_id, '_fs_checkout_redirect', 1 ) ? 'page_payment' : 'page_success';
			$redirect_link = fs_option( $redirect_to ) ? add_query_arg( [
				'pay_method' => $pay_method->term_id,
				'order_id'   => $order_id
			], get_permalink( fs_option( $redirect_to ) ) ) : '';

			$result = array(
				'msg'      => sprintf( __( 'Order #%d successfully added', 'f-shop' ), $order_id ),
				'products' => $fs_products,
				'order_id' => $order_id,
				'sum'      => $sum,
				'redirect' => apply_filters( 'fs_after_checkout_redirect', $redirect_link, $order_id )
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

//  возвращает список постов определённого термина
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

		// Setting the shipping method
		$shipping_method_id = isset( $_POST['fs_delivery_methods'] ) ? absint( $_POST['fs_delivery_methods'] ) : 0;
		if ( ! $shipping_method_id ) {
			$shipping_methods   = get_terms( [
				'taxonomy'   => FS_Config::get_data( 'product_del_taxonomy' ),
				'hide_empty' => false
			] );
			$shipping_method_id = $shipping_methods[0]->term_id ?? 0;
		}

		if ( ! $shipping_method_id ) {
			wp_send_json_error( array( 'msg' => __( 'Shipping method not found', 'f-shop' ) ) );
		}

		$delivery_cost_clean = floatval( get_term_meta( $shipping_method_id, '_fs_delivery_cost', 1 ) );
		$delivery_cost       = sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', $delivery_cost_clean ), fs_currency() );
		$total_amount        = sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', fs_get_total_amount( $delivery_cost_clean ) ), fs_currency() );
		$total               = $delivery_cost_clean + fs_get_cart_cost();

		ob_start();
		fs_taxes_list( array( 'wrapper' => false ), $total );
		$taxes_out = ob_get_clean();

		$disable_fields = get_term_meta( $shipping_method_id, '_fs_disable_fields', 1 );
		$disable_fields = $disable_fields ?: [];

		$required_fields = get_term_meta( $shipping_method_id, '_fs_required_fields', 1 );
		$required_fields = $required_fields ?: [];

		$packing_cost = fs_option( 'fs_include_packing_cost' ) && $shipping_method_id ? fs_get_packing_cost( $shipping_method_id ) : 0;
		$packing_cost = sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', $packing_cost ), fs_currency() );

		ob_start();
		fs_load_template( 'checkout/shipping-fields' );
		$html = ob_get_clean();

		wp_send_json_success( [
			'method_id'      => $shipping_method_id,
			'disableFields'  => $disable_fields,
			'html'           => $html,
			'requiredFields' => $required_fields,
			'taxes'          => $taxes_out,
			'price'          => $delivery_cost,
			'packing_cost'   => $packing_cost,
			'total'          => $total_amount,
		] );
	}

	/**
	 * @return void
	 */
	function fs_get_terms_callback() {
		$terms = get_terms( FS_Config::get_data( 'features_taxonomy' ), array(
			'hide_empty' => false,
			'parent'     => intval( $_POST['parent'] )
		) );

		wp_send_json_success( $terms );
	}

	/**
	 * Clears the wishlist
	 *
	 * @return void
	 */
	function fs_clean_wishlist() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}

		unset( $_SESSION['fs_wishlist'] );
		wp_send_json_success();
	}

	/**
	 * Adds all products from the wish list to the cart
	 *
	 * @return void
	 */
	function fs_add_wishlist_to_cart() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}

		$wishlist = $_SESSION['fs_wishlist'] ?? [];
		if ( ! empty( $wishlist ) ) {
			foreach ( $wishlist as $product_id ) {
				FS_Cart::push_item( [ 'ID' => $product_id ] );
			}
		}

		wp_send_json_success( [
			'message' => __( 'Products added to cart', 'f-shop' ),
			'title'   => __( 'Success!', 'f-shop' )
		] );
	}

	/**
	 * Возвращает атрибуты товара сгруппированные по родителям
	 *
	 * @return void
	 */
	function fs_get_post_attributes_callback() {
		$post_id = intval( $_POST['post_id'] );
		wp_send_json_success( FS_Product::get_attributes_hierarchy( $post_id ) );
	}

	/**
	 * Открепляет атрибут от товара
	 *
	 * @return void
	 */
	function fs_detach_attribute_callback() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}
		$attribute_id = intval( $_POST['attribute_id'] );
		$post_id      = intval( $_POST['post_id'] );
		$taxonomy     = FS_Config::get_data( 'features_taxonomy' );

		wp_remove_object_terms( $post_id, [ $attribute_id ], $taxonomy );

		wp_send_json_success( [
			'message' => __( 'Attribute removed', 'f-shop' ),
			'title'   => __( 'Success!', 'f-shop' )
		] );
	}

	/**
	 * Attaches an existing attribute to a product
	 *
	 * @return void
	 */
	function fs_attach_attribute_callback() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}
		$attribute_id = intval( $_POST['attribute_id'] );
		$post_id      = intval( $_POST['post_id'] );
		$taxonomy     = FS_Config::get_data( 'features_taxonomy' );

		wp_set_object_terms( $post_id, [ $attribute_id ], $taxonomy, true );

		wp_send_json_success( [
			'message' => __( 'Attribute added', 'f-shop' ),
			'title'   => __( 'Success!', 'f-shop' )
		] );
	}

	/**
	 * Creates and attaches a feature to a product
	 *
	 * @return void
	 */
	function fs_add_child_attribute_callback() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}
		$attribute_name = trim( $_POST['value'] );
		$post_id        = intval( $_POST['post_id'] );
		$parent_id      = intval( $_POST['parent_id'] );
		$taxonomy       = FS_Config::get_data( 'features_taxonomy' );

		$term = wp_insert_term( $attribute_name, $taxonomy, [ 'parent' => $parent_id ] );

		if ( is_wp_error( $term ) ) {
			wp_send_json_error( [
				'message' => $term->get_error_message(),
				'title'   => __( 'Error!', 'f-shop' )
			] );
		}

		$term_ids = wp_set_object_terms( $post_id, [ $term['term_id'] ], $taxonomy, true );

		if ( is_wp_error( $term_ids ) ) {
			wp_send_json_error( [
				'message' => $term_ids->get_error_message(),
				'title'   => __( 'Error!', 'f-shop' )
			] );
		}

		wp_send_json_success( [
			'term'    => get_term( $term['term_id'], $taxonomy ),
			'message' => __( 'Attribute added', 'f-shop' ),
			'title'   => __( 'Success!', 'f-shop' )
		] );
	}

	public function fs_get_category_attributes() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}

		if ( empty( $_POST['attribute_id'] ) ) {
			wp_send_json_error( array( 'msg' => __( 'Attribute ID not found', 'f-shop' ) ) );
		}

		$attributes = fs_product_category_screen_attributes( (int) $_POST['attribute_id'], (int) $_POST['category_id'] );

		wp_send_json_success( [
			'attributes' => (array) $attributes,
		] );
	}

	public function fs_calculate_price_callback() {
		if ( ! is_numeric( $_POST['product_id'] ) ) {
			wp_send_json_error( [ 'msg' => __( 'Product ID not found', 'f-shop' ) ] );
		}

		$product_id      = intval( $_POST['product_id'] );
		$post_attributes = array_map( 'intval', explode( ',', $_POST['attributes'] ) );
		$variations      = ( new FS_Product() )->get_product_variations( $product_id );
		$price           = fs_get_price( $product_id );
		$old_price       = fs_get_base_price( $product_id );
		$use_pennies     = fs_option( 'price_cents' ) ? 2 : 0;

		if ( ! $variations ) {
			wp_send_json_success( [
				'price'     => apply_filters( 'fs_price_format', $price ),
				'old_price' => apply_filters( 'fs_price_format', $old_price ),
			] );
		}

		foreach ( $variations as $variation ) {
			$variation_attributes = array_map( 'intval', $variation['attributes'] );
			$intersect            = array_intersect( $post_attributes, $variation_attributes );

			if ( count( $intersect ) == count( $post_attributes ) ) {
				$price      = apply_filters( 'fs_price_filter', $variation['price'], $product_id );
				$price      = round( floatval( $price ), $use_pennies );
				$sale_price = apply_filters( 'fs_price_filter', (float) $variation['sale_price'], $product_id );
				if ( $sale_price > 0 && $sale_price < $price ) {
					$old_price = $price;
					$price     = $sale_price;
				}
				break;
			}
		}

		wp_send_json_success( [
			'price'     => apply_filters( 'fs_price_format', $price ),
			'old_price' => $old_price > 0 ? apply_filters( 'fs_price_format', $old_price ) : '',
		] );

	}

	public function fs_get_max_min_price_callback() {
		$term_id = (int) $_POST['term_id'];
		wp_send_json_success( [
			'max' => FS_Products::get_max_price_in_category( $term_id ),
			'min' => FS_Products::get_min_price_in_category( $term_id ),
		] );
	}

	public function fs_get_category_brands_callback() {
		$term_id = (int) $_POST['term_id'];
		$brands  = FS_Products::get_category_brands( $term_id );

		wp_send_json_success( $brands );
	}

	public function fs_get_product_comments() {
		$product_id = (int) $_POST['post_id'];
		$per_page   = (int) $_POST['per_page'] ?: 10; // per_page

		if ( ! $product_id ) {
			wp_send_json_error( [ 'msg' => __( 'Product ID not found', 'f-shop' ) ] );
		}

		$comments = get_comments( [
			'post_id' => $product_id,
			'status'  => 'approve',
			'number'  => $per_page,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
		] );


		$comments = array_map( function ( $comment ) {
			$images                 = get_comment_meta( $comment->comment_ID, 'fs_images', true ) ?? [];
			$likes                  = (int) get_comment_meta( $comment->comment_ID, 'fs_likes', true );
			$dislikes               = (int) get_comment_meta( $comment->comment_ID, 'fs_dislikes', true );
			$comment->author_avatar = get_avatar( $comment->comment_author_email, 50 );
			$comment->date          = date_i18n( 'd F Y H:i', strtotime( $comment->comment_date ) );
			$comment->content       = apply_filters( 'comment_text', $comment->comment_content );
			$comment->images        = array_map( function ( $id ) {
				return wp_get_attachment_image_url( $id, 'full' );
			}, $images );
			$comment->likes         = $likes ?: 0;
			$comment->dislikes      = $dislikes ?: 0;

			return $comment;
		}, $comments );

		wp_send_json_success( $comments );
	}

	/**
	 * Handles sending a product comment including validation, file uploads, and user authentication.
	 *
	 * @param void
	 *
	 * @return void
	 * @throws void
	 */
	public function fs_send_product_comment() {
		$product_id    = (int) $_POST['post_id'];
		$wp_upload_dir = wp_upload_dir();
		$errors        = [];

		if ( ! $product_id ) {
			$errors ['product_id'] = __( 'Product ID not found', 'f-shop' );
		}

		// validate email
		if ( ! is_email( $_POST['email'] ) ) {
			$errors ['email'] = __( 'Email is not valid', 'f-shop' );
		}

		// validate name
		if ( empty( $_POST['name'] ) ) {
			$errors ['name'] = __( 'Name is required', 'f-shop' );
		}

		// validate body
		if ( empty( $_POST['body'] ) ) {
			$errors ['body'] = __( 'Comment is required', 'f-shop' );
		}

		if ( count( $errors ) ) {
			wp_send_json_error( $errors );
		}

		// Check if files are uploaded
		$files = [];
		if ( ! empty( $_FILES['files'] ) ) {
			$uploaded_files   = $_FILES['files'];
			$upload_overrides = array( 'test_form' => false );

			// Loop through each uploaded file
			foreach ( $uploaded_files['tmp_name'] as $key => $tmp_name ) {
				$file = array(
					'name'     => uniqid() . '-' . basename( $uploaded_files['name'][ $key ] ),
					'type'     => $uploaded_files['type'][ $key ],
					'tmp_name' => $tmp_name,
					'error'    => $uploaded_files['error'][ $key ],
					'size'     => $uploaded_files['size'][ $key ]
				);

				// Handle the file upload
				$movefile = wp_handle_upload( $file, $upload_overrides );

				if ( $movefile && ! isset( $movefile['error'] ) ) {
					// add to database
					$attachment = array(
						'guid'           => $wp_upload_dir['url'] . '/' . basename( $movefile['file'] ),
						'post_mime_type' => $movefile['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $movefile['file'] ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					);

					// Insert the attachment
					$attach_id = wp_insert_attachment( $attachment, $movefile['file'] );
					if ( is_wp_error( $attach_id ) ) {
						wp_send_json_error( [ 'files' => $attach_id->get_error_message() ] );
					} else {
						$files[] = $attach_id;
					}
					// Do something with the file path, like save it to the database
				} else {
					// Error handling for file upload
					wp_send_json_error( [ 'files' => $movefile['error'] ] );
				}
			}
		}


		if ( ! is_user_logged_in() ) {
			$comment_author = $_POST['name'];
			$comment_email  = $_POST['email'];
		} else {
			$user           = wp_get_current_user();
			$comment_author = $user->display_name;
			$comment_email  = $user->user_email;
		}

		$comment = [
			'comment_post_ID'      => $product_id,
			'comment_author'       => $comment_author,
			'comment_author_email' => $comment_email,
			'comment_content'      => $_POST['body'],
			'user_id'              => get_current_user_id(),
			'comment_date'         => current_time( 'mysql' ),
			'comment_approved'     => 0,
			'comment_karma'        => (int) $_POST['rating'],
		];

		$comment_id = wp_insert_comment( $comment );

		if ( $comment_id ) {
			update_comment_meta( $comment_id, 'fs_images', $files );
			update_comment_meta( $comment_id, 'fs_likes', 0 );
			update_comment_meta( $comment_id, 'fs_dislikes', 0 );
		}

		wp_send_json_success( [
			'message'    => __( 'Your review has been successfully added. It will be published after verification.', 'f-shop' ),
			'comment_id' => $comment_id,
			'images'     => $files
		] );
	}

	public function fs_comment_like_dislike() {
		$comment_id = (int) $_POST['comment_id'];

		if ( ! $comment_id ) {
			wp_send_json_error( [ 'msg' => __( 'Comment ID not found', 'f-shop' ) ] );
		}

		$likes    = (int) get_comment_meta( $comment_id, 'fs_likes', true );
		$dislikes = (int) get_comment_meta( $comment_id, 'fs_dislikes', true );


		if ( $_POST['type'] == 'like' ) {
			$likes ++;
			update_comment_meta( $comment_id, 'fs_likes', $likes );
		}

		if ( $_POST['type'] == 'dislike' ) {
			$dislikes ++;
			update_comment_meta( $comment_id, 'fs_dislikes', $dislikes );
		}

		wp_send_json_success( [
			'likes'    => $likes,
			'dislikes' => $dislikes
		] );
	}

	/**
	 *  Клонирование заказа
	 *
	 * @return void
	 */
	function fs_clone_order(): void {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( [ 'msg' => __( 'Security check failed', 'f-shop' ) ] );
		}

		if ( ! isset( $_POST['order_id'] ) ) {
			wp_send_json_error( [ 'msg' => __( 'Order ID not found', 'f-shop' ) ] );
		}

		$new_order_id                 = FS_Orders::clone_order( $_POST['order_id'] );
		$_SESSION['fs_last_order_id'] = $new_order_id;

		if ( is_wp_error( $new_order_id ) ) {
			wp_send_json_error( [ 'msg' => $new_order_id->get_error_message() ] );
		}

		$redirect_link = fs_option( 'page_success' ) ? add_query_arg( [
			'order_id' => $new_order_id
		], get_permalink( fs_option( 'page_success' ) ) ) : '';

		wp_send_json_success( [ 'order_id' => $new_order_id, 'redirect' => $redirect_link ] );
	}
} 