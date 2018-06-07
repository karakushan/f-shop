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
		add_action( 'wp_ajax_order_send', array( $this, 'order_send_ajax' ) );
		add_action( 'wp_ajax_nopriv_order_send', array( $this, 'order_send_ajax' ) );
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

	function fs_get_variated_callback() {

		$post_id        = sanitize_text_field( $_POST['product_id'] );
		$atts           = array_map( 'intval', $_POST['atts'] );
		$variants       = get_post_meta( $post_id, 'fs_variant', 0 );
		$variants_price = get_post_meta( $post_id, 'fs_variant_price', 0 );
		$variants_count = get_post_meta( $post_id, 'fs_variant_count', 0 );
		if ( ! empty( $variants[0] ) ) {
			foreach ( $variants[0] as $k => $variant ) {
				// ищем совпадения варианов в присланными значениями
				if ( count( $variant ) == count( $atts ) && fs_in_array_multi( $atts, $variant ) ) {
					$price     = (float) $variants_price[0][ $k ];
					$min_count = 1;
					foreach ( $atts as $att ) {
						if ( get_term_meta( $att, 'fs_att_type', 1 ) == 'range' ) {
							$min_count = intval( get_term_meta( $att, 'fs_att_range_start_value', 1 ) );
						}
					}

					// если вариант найден то выходим возвращая цену
					$base_price = apply_filters( 'fs_price_filter', $post_id, (float) $price );
					echo json_encode( array(
						'result'     => 1,
						'base_price' => apply_filters( 'fs_price_format', $base_price ),
						'currency'   => fs_currency(),
						'count'      => max( $min_count, 1 )
					) );
					exit();
				}
			}

		}
		echo json_encode( array(
			'result'     => 0,
			'base_price' => apply_filters( 'fs_price_format', fs_get_price( $post_id ) ),
			'old_price'  => apply_filters( 'fs_price_format', fs_get_base_price( $post_id ) )
		) );
		exit();
	}


	/**
	 * Добавление варианта цены. колбек функция
	 */
	function fs_add_variant_callback() {
		$index = (int) $_POST['index'];
		?>
      <div class="fs-rule fs-field-row" data-index="<?php echo $index ?>">
        <a href="#" class="fs-remove-variant">удалить вариант</a>
        <p>
          <label for="">Вариант <span class="index"><?php echo $index + 1 ?></span></label>
			<?php

			global $fs_config;
			$args = array(
				'show_option_all'  => 'Свойство товара',
				'show_option_none' => '',
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'show_last_update' => 0,
				'show_count'       => 0,
				'hide_empty'       => 0,
				'child_of'         => 0,
				'exclude'          => '',
				'echo'             => 1,
				'selected'         => 0,
				'hierarchical'     => 1,
				'name'             => 'fs_variant[' . $index . '][]',
				'id'               => '',
				'class'            => 'fs_select_variant',
				'depth'            => 0,
				'tab_index'        => 0,
				'taxonomy'         => $fs_config->data['product_att_taxonomy'],
				'hide_if_empty'    => false,

			);

			wp_dropdown_categories( $args ); ?>
          <button type="button" class="button-small" data-fs-element="clone-att">ещё свойство</button>
        </p>
        <p>
          <label for="">Цена</label>
          <input type="text" name="fs_variant_price[<?php echo $index ?>]" class="fs_variant_price">
        </p>
      </div>
		<?php
		exit();
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
		global $fs_config;
		if ( ! $fs_config::verify_nonce() ) {
			die ( 'не пройдена верификация формы nonce' );
		}
		$fs_products        = $_SESSION['cart'];
		$fs_custom_products = serialize( $_POST['fs_custom_product'] );
		$user_id            = 0;
		$sum                = fs_get_total_amount( $fs_products );
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
						$error_text = 'Пользователь с таким E-mail или Логином зарегистрирован на сайте. <a href="#fs-modal-login"
                                                                                    data-fs-action="modal">Войти на
  сайт</a>. <a href="' . wp_lostpassword_url( get_permalink() ) . '">Забыли пароль?</a>';
					} else {
						$error_text = $new_user->get_error_message();
					}
					echo json_encode( array(
						'success'    => false,
						'text'       => $error_text,
						'error_code' => $new_user->get_error_code()
					) );
					exit();
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
		$defaults                              = array(
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
				'_payment'         => $sanitize_field['fs_payment_methods'],
				'_amount'          => $sum,
				'_comment'         => $sanitize_field['fs_comment']
			),
		);
		$order_id                              = wp_insert_post( $defaults );
		$sanitize_field['order_id']            = $order_id;
		$sanitize_field['total_amount']        = $sum . ' ' . fs_currency();
		$sanitize_field['site_name']           = get_bloginfo( 'name' );
		$sanitize_field['fs_delivery_methods'] = fs_get_delivery( $sanitize_field['fs_delivery_methods'] );
		$sanitize_field['fs_payment_methods']  = fs_get_payment( $sanitize_field['fs_payment_methods'] );
		$_SESSION['last_order_id']             = $order_id;

// текст письма заказчику
		$user_message = apply_filters( 'fs_email_template', $sanitize_field, fs_option( 'customer_mail' ) );

// текст письма админу
		$admin_message = apply_filters( 'fs_email_template', $sanitize_field, fs_option( 'admin_mail', get_bloginfo( 'admin_email' ) ) );

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
				'sum'      => $sum,
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

		$json = json_encode( array(
			'body'   => fs_frontend_template( 'wishlist/wishlist' ),
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
} 