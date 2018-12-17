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
	public static $order_statuses = [];
	public $last_order_id = null;


	function __construct() {

		$this->order_statuses = FS_Config::default_order_statuses();


		add_filter( 'pre_get_posts', array( $this, 'filter_orders_by_search' ) );

		//===== ORDER STATUSES ====
		add_action( 'init', array( $this, 'order_status_custom' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'true_append_post_status_list' ) );
		add_action( 'admin_footer-post.php', array( $this, 'true_append_post_status_list' ) );
		add_filter( 'display_post_states', array( $this, 'true_status_display' ) );

		// Срабатывает в момент изменения статуса заказа
		add_action( 'transition_post_status', array( $this, 'fs_change_order_status' ) );


		// операции с метабоксами - удаление стандартных, добавление новых
		add_action( 'admin_menu', array( $this, 'remove_submit_order_metabox' ) );


		$this->last_order_id = $this->get_last_order_id();

	}

	/**
	 * Возвращает ID последнего заказа
	 */
	public static function get_last_order_id() {
		$id = 0;
		if ( ! empty( $_SESSION['fs_last_order_id'] ) ) {
			$id = intval( $_SESSION['fs_last_order_id'] );
		}

		return $id;
	}

	/**
	 * Возвращает общую сумму последнего заказа
	 */
	public function get_last_order_amount() {
		$order_id   = $this->get_last_order_id();
		$order      = new FS_Orders_Class;
		$order_info = $order->get_order( $order_id );
		$amount     = floatval( $order_info->summa );
		$amount     = apply_filters( 'fs_price_format', $amount );

		return $amount;
	}

	/**
	 * удаляем стандартный метабокс сохранения, публикации поста
	 * добавляет свой метабокс для изменения статуса заказа
	 */
	function remove_submit_order_metabox() {
		remove_meta_box( 'submitdiv', $this->post_type, 'side' );
		add_meta_box( 'fs_order_status_box', __( 'Order status', 'f-shop' ), array(
			$this,
			'order_status_box'
		), $this->post_type, 'side', 'high' );

		$this->orders_bubble();
	}

	/**
	 * Показывает количество новых заказов справа около пунктом меню "Заказы"
	 * не заходя в сам пункт "Заказы"
	 * создано для удобства и информирования админов
	 */
	function orders_bubble() {
		global $menu, $fs_config;
		$custom_post_count         = wp_count_posts( $this->post_type );
		$custom_post_pending_count = $custom_post_count->{$fs_config->data['default_order_status']};
		if ( ! $custom_post_pending_count ) {
			return;
		}
		foreach ( $menu as $key => $value ) {
			if ( $menu[ $key ][2] == 'edit.php?post_type=' . esc_attr( $fs_config->data['post_type_orders'] ) ) {
				$menu[ $key ][0] .= ' <span class="update-plugins count-' . esc_attr( $custom_post_pending_count ) . '"><span class="plugin-count" aria-hidden="true"> ' . esc_html( $custom_post_pending_count ) . '</span><span class="screen-reader-text"> ' . esc_html( $custom_post_pending_count ) . '</span></span>';

				return;
			}
		}

	}

	/**
	 * Метабокс отображает селект с статусами заказа и кнопку сохранения
	 */
	function order_status_box() {
		global $post;
		echo '<p><span class="dashicons dashicons-calendar-alt"></span> ' . esc_html__( 'Date of purchase', 'f-shop' ) . ': <b> ' . esc_html( get_the_date( "j.m.Y H:i" ) ) . '</b></p>';
		echo '<p><span class="dashicons dashicons-calendar-alt"></span> ' . esc_html__( 'Last modified', 'f-shop' ) . ':  <b>' . esc_html( get_the_modified_date( "j.m.Y H:i" ) ) . '</b></p>';
		if ( $this->order_statuses ) {
			echo '<p><label for="fs-post_status"><span class="dashicons dashicons-post-status"></span> ' . esc_html__( 'Status' ) . '</label>';
			echo '<p><select id="fs-post_status" name="post_status">';
			foreach ( $this->order_statuses as $key => $order_status ) {
				echo '<option value="' . esc_attr( $key ) . '" ' . selected( get_post_status( $post->ID ), $key, 0 ) . '>' . esc_attr( $order_status['name'] ) . '</option>';
			}
			echo '</select></p>';
		}
		echo '<p><input type="submit" name="save" id="save-post" value="' . esc_attr__( 'Save' ) . '" class="button button-primary button-large"></p>';
		echo '<div class="clear"></div>';
		echo '<p><a class="submitdelete deletion" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">' . esc_html__( 'Delete' ) . '</a></p>';
		echo '<div class="clear"></div>';
	}

	/**
	 * Returns a single order information template
	 *
	 * @return mixed|string|void
	 */
	function order_detail() {
		$order_id = intval( $_GET['order_detail'] );
		if ( empty( $order_id ) ) {
			return '<p class="fs-order-detail">' . esc_html__( 'Order number is not specified', 'f-shop' ) . '</p>';
		}

		return fs_frontend_template( 'shortcode/order-detail', array(
			'vars' => array(
				'order'   => FS_Orders_Class::get_order( $order_id ),
				'payment' => new FS_Payment_Class()
			)
		) );
	}

	/**Отображает заказы текущего пользователя в виде таблицы
	 *
	 * @return string
	 */
	function list_orders() {
		if ( ! empty( $_GET['order_detail'] ) && is_numeric( $_GET['order_detail'] ) ) {
			return self::order_detail();

		}

		return fs_frontend_template( 'shortcode/list-orders', array( 'vars' => array( 'orders' => self::get_user_orders() ) ) );
	}

	/**
	 *  Это событие отправляет покупателю сведения об оплате выбранным способом
	 *  срабатывает в момент изменения статуса заказа с "новый" на "обработан"
	 *
	 * @param $new_status
	 */
	function fs_change_order_status( $new_status ) {
		global $post, $fs_config;

		// Если новый статус заказа "обработан" (processed)
		if ( $post && $post->post_type == $this->post_type && $new_status == 'processed' ) {
			// Получаем ID выбраного способа оплаты
			$pay_method_id = get_post_meta( $post->ID, '_payment', 1 );
			// Получаем кастомное сообшение пользователю, извещение об возможности оплаты
			$message_no_filter = get_term_meta( $pay_method_id, '_fs_pay_message', 1 );
			$pay_term          = get_term( $pay_method_id, $fs_config->data['product_pay_taxonomy'] );
			if ( empty( $message_no_filter ) ) {
				$message_no_filter = __( 'Your order #%order_id% has been successfully approved. The next stage is payment of the order. You can pay for the purchase by <a href="%pay_url%">link</a>. You have chosen the payment method: %pay_name%.
Good luck!', 'f-shop' );
			}
			// Создаём ссылку для оплаты покупки
			$pay_link = add_query_arg( array(
				'pay_method' => $pay_term->slug,
				'order_id'   => $post->ID
			), get_permalink( fs_option( 'page_payment' ) ) );
			$message  = apply_filters( 'fs_pay_user_message', $message_no_filter );
			// Производим замену мета данных типа %var%
			$message = str_replace( array(
				'%order_id%',
				'%pay_url%',
				'%pay_name%'
			), array(
				$post->ID,
				esc_url( $pay_link ),
				$pay_term->name
			), $message );

			$message_decode = wp_specialchars_decode( $message, ENT_QUOTES );
			$user_data      = get_post_meta( $post->ID, '_user', 1 );

			if ( is_email( $user_data['email'] ) && ! empty( $message ) ) {
				wp_mail( $user_data['email'], __( 'Your order is approved', 'f-shop' ), $message_decode, $fs_config->email_headers() );
			}
		}
	}

	function true_status_display( $statuses ) {
		global $post;
		if ( $this->order_statuses ) {
			foreach ( $this->order_statuses as $key => $status ) {
				if ( get_query_var( 'post_status' ) != $key ) {
					if ( $post->post_status == $key ) {
						$statuses[] = $status['name'];
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

		if ( $this->order_statuses ) {
			foreach ( $this->order_statuses as $key => $status ) {
				register_post_status( $key, array(
					'label'                     => $status['name'],
					'label_count'               => _n_noop( $status['name'] . ' <span class="count">(%s)</span>', $status['name'] . ' <span
        class="count">(%s)</span>' ),
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
			if ( $this->order_statuses ) : ?>
                <script> jQuery(function ($) {
						<?php foreach ( $this->order_statuses as $key => $status ): ?>
                        $('select#post_status').append("<option value=\"<?php echo esc_attr( $key )?>\" <?php selected( $post->post_status, $key ) ?>><?php echo esc_attr( $status['name'] )?></option>");
						<?php if ( $post->post_status == $key ): ?>
                        $('#post-status-display').text('<?php echo esc_attr( $status['name'] )?>');
						<?php endif; endforeach;  ?>
                    });</script>";
			<?php
			endif;
		}
	}

	/**
	 * Добавляет к обычному посту свойства или метаданные заказа
	 *
	 * @param $order_id
	 *
	 * @return \stdClass
	 */
	function set_order_data( $order_id ) {
		$order_meta = get_post_meta( $order_id );
		$meta       = new \stdClass();
		if ( $order_meta ) {
			foreach ( $order_meta as $key => $item ) {
				if ( count( $item ) == 1 && unserialize( array_shift( $item ) ) === false ) {
					$meta->{$key} = get_post_meta( $order_id, $key, 1 );
				} else {
					$meta->{$key} = get_post_meta( $order_id, $key, 0 )[0];
				}
			}
		}

		return $meta;
	}

	/**
	 * Displays the object with the orders of an individual user, by default the current user
	 *
	 * @param null|int $user_id - User ID
	 * @param string $status - Order status id (same as post status)
	 * @param array $args - array of arguments similar to WP_Query ()
	 *
	 * @return array|null|object object with orders
	 */
	public function get_user_orders( $user_id = null, $status = 'any', $args = array() ) {
		global $fs_config;
		$user_id = ! is_null( $user_id ) && is_numeric( $user_id ) ? $user_id : get_current_user_id();
		$args    = wp_parse_args( $args, array(
			'post_type'   => $fs_config->data['post_type_orders'],
			'post_status' => $status,
			'meta_key'    => '_user_id',
			'meta_value'  => $user_id,
		) );

		$orders = get_posts( $args );
		$data   = [];
		if ( $orders ) {
			foreach ( $orders as $order ) {
				$order->data = self::set_order_data( $order->ID );
				$data[]      = $order;
			}
		}

		return $orders;
	}

	/**
	 * Возвращает статус заказа в текстовой, читабельной форме
	 *
	 * @param $order_id - ID заказа
	 *
	 * @return string
	 */
	public function get_order_status( $order_id ) {
		$post_status_id = get_post_status( $order_id );

		if ( ! empty( self::$order_statuses[ $post_status_id ]['name'] ) ) {
			$status = self::$order_statuses[ $post_status_id ]['name'];
		} else {
			$status = __( 'The order status is not defined', 'f-shop' );
		}

		return $status;
	}

	public function get_order_items( $order_id ) {
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
					'sum'   => get_post_meta( $id, '_amount', 1 ),
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

		$order = get_post( $order_id );
		if ( $order ) {
			$order->data = self::set_order_data( $order_id );
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
	public
	function fs_order_total(
		int $order_id
	) {
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

	public static function delete_orders() {
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
	 *
	 * @param $query
	 */
	function filter_orders_by_search( $query ) {
		if ( ! is_admin() || empty( $_GET['s'] ) ) {
			return;
		}
		global $wpdb;
		$s       = $_GET['s'];
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT user_id FROM %s WHERE meta_value LIKE '%%%s%%'", $wpdb->usermeta, $s ) );
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