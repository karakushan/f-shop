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

		//  добавление в список желаний
		add_action( 'wp_ajax_fs_addto_wishlist', array( $this, 'fs_addto_wishlist' ) );
		add_action( 'wp_ajax_nopriv_fs_addto_wishlist', array( $this, 'fs_addto_wishlist' ) );

		// удаление из списка желаний
		add_action( 'wp_ajax_fs_del_wishlist_pos', array( $this, 'fs_del_wishlist_pos' ) );
		add_action( 'wp_ajax_nopriv_fs_del_wishlist_pos', array( $this, 'fs_del_wishlist_pos' ) );

		//  получение связанных постов категории
		add_action( 'wp_ajax_fs_get_taxonomy_posts', array( $this, 'get_taxonomy_posts' ) );
		add_action( 'wp_ajax_nopriv_fs_get_taxonomy_posts', array( $this, 'get_taxonomy_posts' ) );

		// добавление товара к сравнению
		add_action( 'wp_ajax_fs_add_to_comparison', array( $this, 'fs_add_to_comparison_callback' ) );
		add_action( 'wp_ajax_nopriv_fs_add_to_comparison', array( $this, 'fs_add_to_comparison_callback' ) );

		// удаляет один термин (свойство) товара
		add_action( 'wp_ajax_fs_remove_product_term', array( $this, 'fs_remove_product_term_callback' ) );
		add_action( 'wp_ajax_nopriv_fs_remove_product_term', array( $this, 'fs_remove_product_term_callback' ) );

		// добавляет вариант покупки товара
		add_action( 'wp_ajax_fs_add_variant', array( $this, 'fs_add_variant_callback' ) );
		add_action( 'wp_ajax_nopriv_fs_add_variant', array( $this, 'fs_add_variant_callback' ) );

		// получение вариантов цены товара
		add_action( 'wp_ajax_fs_get_variated', array( $this, 'fs_get_variated_callback' ) );
		add_action( 'wp_ajax_nopriv_fs_get_variated', array( $this, 'fs_get_variated_callback' ) );

		// привязка атрибута к товару
		add_action( 'wp_ajax_fs_add_att', array( $this, 'fs_add_att_callback' ) );
		add_action( 'wp_ajax_nopriv_fs_add_att', array( $this, 'fs_add_att_callback' ) );

		// setting a product rating
		add_action( 'wp_ajax_fs_set_rating', array( $this, 'fs_set_rating_callback' ) );
		add_action( 'wp_ajax_nopriv_fs_set_rating', array( $this, 'fs_set_rating_callback' ) );

		// Обновление позиции товаров
		add_action( 'wp_ajax_fs_update_position', array( $this, 'fs_update_position_callback' ) );
		add_action( 'wp_ajax_nopriv_fs_update_position', array( $this, 'fs_update_position_callback' ) );

		// Возврщает HTML код шаблона расположеного по адресу /templates/front-end/checkout/shipping-fields.php
		add_action( 'wp_ajax_fs_show_shipping', array( $this, 'fs_show_shipping_callback' ) );
		add_action( 'wp_ajax_nopriv_fs_show_shipping', array( $this, 'fs_show_shipping_callback' ) );

		// возвращает шаблон, работает на основе get_template_part()
		add_action( 'wp_ajax_fs_get_template_part', array( $this, 'fs_get_template_part' ) );
		add_action( 'wp_ajax_nopriv_fs_get_template_part', array( $this, 'fs_get_template_part' ) );

		// Возвращает id изображений галереи товара или его вариации
		add_action( 'wp_ajax_fs_get_product_gallery_ids', array( $this, 'fs_get_product_gallery_ids' ) );
		add_action( 'wp_ajax_nopriv_fs_get_product_gallery_ids', array( $this, 'fs_get_product_gallery_ids' ) );

		// обновление к-ва товара в корзине
		add_action( 'wp_ajax_fs_change_cart_count', array( $this, 'change_cart_item_count' ) );
		add_action( 'wp_ajax_nopriv_fs_change_cart_count', array( $this, 'change_cart_item_count' ) );

		//Регистрируем обработку AJAX событий
		if ( is_array( $this->ajax_actions() ) && count( $this->ajax_actions() ) ) {
			foreach ( $this->ajax_actions() as $key => $action ) {
				add_action( 'wp_ajax_' . $key, $action );
				add_action( 'wp_ajax_nopriv_' . $key, $action );
			}
		}
	}

	function ajax_actions() {
		$actions = array(
			'fs_report_availability' => array( $this, 'report_availability' ),
			'order_send'             => array( $this, 'order_send_ajax' )
		);

		return apply_filters( 'fs_ajax_actions', $actions );
	}

	/**
	 * Отправляет админу сообщение уведомить юзера о наличии товара
	 */
	function report_availability() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}
		$email = sanitize_email( $_POST['email'] );
		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( array( 'msg' => __( 'Пожалуйста введите валидный адрес э-почты', 'f-shop' ) ) );
		}
		$subject = __( 'Просьба уведомить о наличии товара', 'f-shop' );
		$msg     = sprintf( __( 'Пользователь %s просит уведомить его о наличии товара "%s". Ссылка на товар: %s', 'f-shop' ), $email, $_POST['product_name'], $_POST['product_url'] );
		$headers = array(
			sprintf(
				'From: %s <%s>',
				fs_option( 'name_sender', get_bloginfo( 'name' ) ),
				fs_option( 'email_sender', 'shop@' . $_SERVER['SERVER_NAME'] )
			)
		);
		if ( wp_mail( fs_option( 'manager_email', get_option( 'admin_email' ) ), $subject, $msg, $headers ) ) {
			wp_send_json_success( array(
				'msg'     => __( 'Ваш запрос успешно отправлен!', 'f-shop' ),
				'post'    => $_POST,
				'headers' => $headers
			) );
		} else {
			wp_send_json_error( array(
				'msg'     => __( 'Возникла ошибка с отправкой письма администратору сайта!', 'f-shop' ),
				'post'    => $_POST,
				'headers' => $headers
			) );
		}


	}

	//обновление к-ва товара в корзине аяксом
	public function change_cart_item_count() {
		$item_id       = intval( $_REQUEST['item_id'] );
		$product_count = intval( $_REQUEST['count'] );
		if ( ! empty( $_SESSION['cart'] ) ) {
			$_SESSION['cart'][ $item_id ]['count'] = $product_count;
			wp_send_json_success();
		}
		wp_send_json_error();

	}

	// Возвращает HTML код галереи товара или конкретной вариации
	// TODO : добавить nonce проверку
	function fs_get_product_gallery_ids() {
		$product_id   = intval( $_POST['product_id'] );
		$variation_id = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : null;

		$gallery = '';
		// Получаем галерею вариативного товара
		if ( $product_id && $variation_id ) {
			$product_class = new FS_Product_Class();
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

	// привязка атрибута к товару
	function fs_add_att_callback() {
		global $fs_config;
		$post = array_map( 'sanitize_text_field', $_POST );

		$post_terms = wp_set_post_terms( intval( $post['post'] ), intval( $post['term'] ), $fs_config->data['product_att_taxonomy'], true );
		if ( is_wp_error( $post_terms ) ) {
			echo json_encode( array(
				'status'  => 0,
				'message' => $post_terms->get_error_message()
			) );
		} elseif ( $post_terms === false ) {
			echo json_encode( array(
				'status'  => 0,
				'message' => __( 'Возникла непредвиденная ошибка при прикреплении атрибута к товару' )
			) );
		} else {
			echo json_encode( array(
				'status'    => 1,
				'term_name' => get_term_field( 'name', intval( $post['term'] ), $fs_config->data['product_att_taxonomy'] ),
				'message'   => __( 'Атрибут успешно прикреплен к товару' )
			) );
		}
		wp_die();
	}

	/**
	 * Коллбек функция для поиска варианта покупки
	 */
	function fs_get_variated_callback() {
		$product      = new FS_Product_Class();
		$product_id   = intval( $_POST['product_id'] );
		$current_attr = intval( $_POST['current'] );
		$atts         = array_map( 'intval', $_POST['atts'] );
		$variations   = $product->get_product_variations( $product_id );

		// сначала ищем совпадение по всем атрибутам, т.е. массив присланных атрибутов и и атрибутов вариации должны совпадать
		if ( count( $atts ) && count( $variations ) ) {
			foreach ( $variations as $k => $variant ) {
				$variant_atts = array_map( 'intval', $variant['attr'] );
				// ищем совпадения варианов в присланными значениями
				if ( count( $variant_atts ) == count( $atts ) && fs_in_array_multi( $atts, $variant_atts ) ) {
					$price = floatval( $variant['price'] );
					$price = apply_filters( 'fs_price_filter', $product_id, $price );

					$action_price = floatval( $variant['action_price'] );
					$action_price = apply_filters( 'fs_price_filter', $product_id, $action_price );

					$price_base = false;
					if ( ! empty( $action_price ) && $action_price < $price ) {
						$price      = $action_price;
						$price_base = $price;
					}
					wp_send_json_success( array(
						'variation' => $k,
						'atts'      => array_map( 'intval', $variant['attr'] ),
						'price'     => sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', $price ), fs_currency() ),
						'basePrice' => sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', $price_base ), fs_currency() )
					) );
				}
				// если нет совпадения по всем атрибутам ищем первый вариант в котором есть выбранный атрибут
				if ( count( $variant_atts ) && in_array( $current_attr, $variant_atts ) ) {
					$atts_with_parent = [];
					foreach ( $variant_atts as $variant_att ) {
						$parent = get_term_field( 'parent', $variant_att );
						if ( ! is_wp_error( $parent ) ) {
							$atts_with_parent[ $parent ] = $variant_att;
						}
					}
					$price = floatval( $variant['price'] );
					$price = apply_filters( 'fs_price_filter', $product_id, $price );

					$action_price = floatval( $variant['action_price'] );
					$action_price = apply_filters( 'fs_price_filter', $product_id, $action_price );

					$price_base = false;
					if ( ! empty( $action_price ) && $action_price < $price ) {
						$price_base = sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', $price ), fs_currency() );
						$price      = $action_price;

					}
					wp_send_json_success( array(
						'active'    => $atts_with_parent,
						'variation' => $k,
						'price'     => sprintf( '%s <span>%s</span>', apply_filters( 'fs_price_format', $price ), fs_currency() ),
						'basePrice' => $price_base
					) );
				}
			}
			wp_send_json_error( [ 'msg' => __( 'Goods with such a set of characteristics are not in stock. Try changing parameters.', 'f-shop' ) ] );
		} else {
			wp_send_json_error( [ 'msg' => __( 'Goods with such a set of characteristics are not in stock. Try changing parameters.', 'f-shop' ) ] );
		}
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
		global $fs_config;
		$output = array_map( 'sanitize_text_field', $_POST );
		$remove = wp_remove_object_terms( (int) $output['product_id'], (int) $output['term_id'], $fs_config->data['product_att_taxonomy'] );
		if ( $remove ) {
			echo json_encode( array(
				'status' => true
			) );
		} else {
			echo json_encode( array(
				'status' => false
			) );
		}
		exit();
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
		global $fs_config, $wpdb;
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}
		$product_class      = new FS_Product_Class();
		$fs_products        = FS_Cart_Class::get_cart();
		$fs_custom_products = ! empty( $_POST['fs_custom_product'] ) ? serialize( $_POST['fs_custom_product'] ) : '';
		$user_id            = 0;
		$delivery_cost      = floatval( get_term_meta( intval( $_POST['fs_delivery_methods'] ), '_fs_delivery_cost', 1 ) );
		$sum                = fs_get_total_amount( $delivery_cost );
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
					wp_update_user( array(
						'ID'         => $user_id,
						'first_name' => $sanitize_field['fs_first_name'],
						'last_name'  => $sanitize_field['fs_last_name'],
						'role'       => FS_Config::getUsers( 'new_user_role' )
					) );
				} else {
					if ( $new_user->get_error_code() == 'email_exists' || $new_user->get_error_code() == 'username_exists' ) {
						$error_text = __( 'User with such E-mail or Login is registered on the site. <a href="#fs-modal-login" data-fs-action="modal">Login to site</a>. <a href="' . wp_lostpassword_url( get_permalink() ) . '">Forgot your password?</a>', 'f-shop' );
					} else {
						$error_text = $new_user->get_error_message();
					}

					wp_send_json_error( [ 'msg' => $error_text ] );
				}
			}

		}

		// обновляем мета поля пользователя
		if ( $user_id ) {
			wp_update_user( array(
				'ID'         => $user_id,
				'first_name' => $sanitize_field['fs_first_name'],
				'last_name'  => $sanitize_field['fs_last_name']
			) );
			foreach ( FS_Config::getFormFields() as $key => $user_meta ) {
				if ( ! empty( $sanitize_field[ $key ] ) && ! empty( $user_meta['save_meta'] ) ) {
					update_user_meta( $user_id, $key, $sanitize_field[ $key ] );
				}
			}
		}


		// Вставляем заказ в базу данных
		$pay_method     = get_term( intval( $sanitize_field['fs_payment_methods'] ), $fs_config->data['product_pay_taxonomy'] );
		$new_order_data = array(
			'post_title'   => $sanitize_field['fs_first_name'] . ' ' . $sanitize_field['fs_last_name'] . ' / ' . date( 'd.m.Y H:i' ),
			'post_content' => '',
			'post_status'  => $fs_config->data['default_order_status'],
			'post_type'    => $fs_config->data['post_type_orders'],
			'post_author'  => 1,
			'ping_status'  => get_option( 'default_ping_status' ),
			'post_parent'  => 0,
			'menu_order'   => 0,
			'import_id'    => 0,
			'meta_input'   => array(
				'_user_id'         => $user_id,
				'_user'            => array(
					'id'         => $user_id,
					'first_name' => $sanitize_field['fs_first_name'],
					'last_name'  => $sanitize_field['fs_last_name'],
					'email'      => $sanitize_field['fs_email'],
					'phone'      => $sanitize_field['fs_phone'],
					'city'       => $sanitize_field['fs_city']
				),
				'_products'        => $fs_products,
				'_custom_products' => $fs_custom_products,
				'_delivery'        => array(
					'method'    => $sanitize_field['fs_delivery_methods'],
					'secession' => $sanitize_field['fs_delivery_number'],
					'adress'    => $sanitize_field['fs_adress']
				),
				'_payment'         => $pay_method ? $pay_method->term_id : 0,
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

			$sanitize_field['order_id']            = $order_id;
			$sanitize_field['total_amount']        = $sum . ' ' . fs_currency();
			$sanitize_field['site_name']           = get_bloginfo( 'name' );
			$sanitize_field['fs_delivery_methods'] = fs_get_delivery( $sanitize_field['fs_delivery_methods'] );
			$sanitize_field['fs_payment_methods']  = $pay_method->name;
			$_SESSION['fs_last_order_id']          = $order_id;

			// текст письма заказчику
			$sanitize_field['message'] = fs_option( 'customer_mail' );
			$user_message              = apply_filters( 'fs_email_template', $sanitize_field );

			// текст письма админу
			$sanitize_field['message'] = fs_option( 'admin_mail' );
			$admin_message             = apply_filters( 'fs_email_template', $sanitize_field );

			// Заголовки E-mail
			$headers = array(
				sprintf(
					'From: %s <%s>',
					fs_option( 'name_sender', get_bloginfo( 'name' ) ),
					fs_option( 'email_sender', 'shop@' . $_SERVER['SERVER_NAME'] )
				),
				'Content-type: text/html; charset=utf-8'
			);

			//Отсылаем письмо с данными заказа заказчику
			$customer_mail_header = fs_option( 'customer_mail_header', sprintf( __( 'Order goods on the site "%s"', 'f-shop' ), get_bloginfo( 'name' ) ) );
			if ( $sanitize_field['fs_email'] ) {
				wp_mail( $sanitize_field['fs_email'], $customer_mail_header, $user_message, $headers );
			}

			//Отсылаем письмо с данными заказа админу
			$admin_email       = fs_option( 'manager_email', get_option( 'admin_email' ) );
			$admin_mail_header = fs_option( 'admin_mail_header', sprintf( __( 'Order goods on the site "%s"', 'f-shop' ), get_bloginfo( 'name' ) ) );
			wp_mail( $admin_email, $admin_mail_header, $admin_message, $headers );

			/* обновляем название заказа для админки */
			wp_update_post( array(
					'ID'         => $order_id,
					'post_title' => sprintf( 'Order #%d from %s %s (%s)',
						$order_id, $sanitize_field['fs_first_name'], $sanitize_field['fs_last_name'], date( 'd.m.y H:i', time() ) )
				)
			);

			$result = array(
				'msg'      => sprintf( __( 'Order #%d successfully added', 'f-shop' ), $order_id ),
				'products' => $fs_products,
				'order_id' => $order_id,
				'sum'      => $sum,
				'redirect' => get_permalink( fs_option( 'fs_checkout_redirect', fs_option( 'page_success', 0 ) ) )
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
	 * Возврщает HTML код шаблона расположеного по адресу /templates/front-end/checkout/shipping-fields.php
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

		wp_send_json_success( array(
			'show'  => get_term_meta( $term_id, '_fs_delivery_address', 1 ) ? 1 : 0,
			'taxes' => $taxes_out,
			'price' => $delivery_cost,
			'total' => $total_amount,
			'html'  => fs_frontend_template( 'checkout/shipping-fields' )
		) );
	}
} 