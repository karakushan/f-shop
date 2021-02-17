<?php


namespace FS;

class FS_Order {

	/**
	 * Товары в заказе
	 *
	 * @var array
	 */
	public $items = [];

	/**
	 * @var \WP_Term
	 */
	public $delivery_method = null;

	/**
	 * @var \WP_Term
	 */
	public $payment_method = null;

	/**
	 * Общая сумма заказа
	 *
	 * @var int
	 */
	public $total_amount = 0;


	/**
	 * Скидка
	 *
	 * @var int
	 */
	public $discount = 0;

	/**
	 * Комментарий к заказу
	 *
	 * @var string
	 */
	public $comment = '';

	/**
	 * Статус заказа
	 *
	 * @var string
	 */
	public $status = 'new';

	/**
	 * Количество товаров в заказе
	 *
	 * @var int
	 */
	public $count = 0;

	/**
	 * Дата и время заказа
	 *
	 * @var
	 */

	public $date;

	/**
	 * Номер заказа
	 *
	 * @var int
	 */
	public $ID = 0;

	public $post = null;

	public $user;

	/**
	 * ID текущего пользователя
	 *
	 * @var
	 */
	public $user_id = 0;

	public $meta;

	public $customer_address = null;

	/**
	 * Заголовок заказа в админке
	 *
	 * @var
	 */
	public $title = 'Order #%d from %s %s (%s)';

	protected $customer_ip = null;
	public $customer_city = null;
	public $customer_email = null;
	public $customer_first_name = null;
	public $customer_last_name = null;
	public $customer_phone = null;
	public $customer_subscribe_news = 1;
	public $customer_group = 1;
	public $delivery_method_id;
	public $payment_method_id;
	public $packing_cost = 0.0;
	public $shipping_cost = 0.0;


	/**
	 * FS_Order constructor.
	 *
	 * @param int $order_id
	 */
	public function __construct( $order_id = 0 ) {
		if ( $order_id ) {
			$this->set_order( $order_id );
		}

		$this->customer_ip = fs_get_user_ip();
	}


	/**
	 * Создает заказ исходя из контекста и переданных данных
	 *
	 */
	public function create() {
		global $wpdb;
		$customer_id     = 0;
		$customers_table = $wpdb->prefix . 'fs_customers';
		$products        = FS_Cart::get_cart();

		if ( $this->user_id ) {
			$logged_customer = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$customers_table} WHERE user_id=%d", $this->user_id ) );
			if ( $logged_customer && isset( $logged_customer->user_id ) ) {
				$customer_id = $logged_customer->id;
				$wpdb->update( $customers_table, [
					'email'          => $this->customer_email ? $this->customer_email : $logged_customer->email,
					'first_name'     => $this->customer_first_name ? $this->customer_first_name : $logged_customer->first_name,
					'last_name'      => $this->customer_last_name ? $this->customer_last_name : $logged_customer->last_name,
					'subscribe_news' => $this->customer_subscribe_news,
					'group'          => $this->customer_group,
					'address'        => $this->customer_address ? $this->customer_address : $logged_customer->address,
					'city'           => $this->customer_city ? $this->customer_city : $logged_customer->city,
					'phone'          => $this->customer_phone ? intval( preg_replace( "/[^0-9]/", '', $this->customer_phone ) ) : $logged_customer->phone,
					'ip'             => $this->customer_ip
				], [ 'user_id' => $this->user_id ] );
			}
		}

		if ( ! $customer_id ) {
			$wpdb->insert( $customers_table, [
				'email'          => $this->customer_email,
				'first_name'     => $this->customer_first_name,
				'last_name'      => $this->customer_last_name,
				'subscribe_news' => $this->customer_subscribe_news,
				'group'          => $this->customer_group,
				'address'        => $this->customer_address,
				'user_id'        => $this->user_id,
				'city'           => $this->customer_city,
				'phone'          => preg_replace( "/[^0-9]/", '', $this->customer_phone ),
				'ip'             => $this->customer_ip
			] );
			$customer_id = $wpdb->insert_id;
		}

		if ( ! $customer_id ) {
			return new \WP_Error( 'fs_no_customer_id', __( 'Failed to get or create customer data', 'f-shop' ) );
		}

		$data = [
			'_order_discount' => fs_get_total_discount(),
			'_packing_cost'   => fs_get_packing_cost(),
			'_customer_id'    => $customer_id,
			'_products'       => $products,
			'_delivery'       => array(
				'method'    => $this->delivery_method_id,
				'secession' => 0,
				'address'   => ''
			),
			'_payment'        => $this->payment_method_id,
			'_amount'         => fs_get_total_amount(),
			'_comment'        => $this->comment
		];

		$order_id = wp_insert_post( [
			'post_title'  => $this->title,
			'post_status' => $this->status,
			'post_author' => $this->user_id ? $this->user_id : 1,
			'post_type'   => FS_Config::get_data( 'post_type_orders' ),
			'meta_input'  => $data,
		] );

		if ( $order_id ) {
			$order_data = get_post( $order_id );
			$customer   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$customers_table} WHERE id=%d", $customer_id ) );
			wp_update_post( [
				'ID'         => $order_id,
				'post_title' => sprintf(
					$this->title ? __( $this->title, 'f-shop' ) : __( 'Order #%d from %s %s (%s)', 'f-shop' ),
					$order_id,
					$customer->first_name,
					$customer->last_name,
					date( 'd.m.y H:i', strtotime( $order_data->post_date ) )
				)
			] );

			FS_Cart::remove_cart();
		}

		return $order_id;
	}


	/**
	 * Устанавливает данные заказа
	 *
	 * @param int $order_id
	 *
	 * @return null
	 */
	public function set_order( $order_id = 0 ) {

		$this->ID = $order_id;

		if ( ! $order_id && $this->get_last_order_id() ) {
			$this->ID = $this->get_last_order_id();
		}

		$this->post = get_post( $this->ID );


		$order_id = $order_id ? $order_id : $this->get_last_order_id();

		if ( ! is_numeric( $order_id ) || $order_id == 0 ) {
			return null;
		}

		$products   = get_post_meta( $order_id, '_products', 1 ) ? get_post_meta( $order_id, '_products', 1 ) : [];
		$this->meta = get_post_meta( $order_id );


		$this->items = array_values( array_map( function ( $item ) {
			return fs_set_product( $item );
		}, $products ) );

		$this->total_amount = (float) get_post_meta( $order_id, '_amount', 1 );
		$this->packing_cost = (float) get_post_meta( $order_id, '_packing_cost', 1 );
		$this->discount     = (float) get_post_meta( $order_id, '_order_discount', 1 );
		$this->comment      = get_post_meta( $order_id, '_comment', 1 );
		$this->user         = (array) get_post_meta( $order_id, '_user', 1 );
		$this->user['ip']   = get_post_meta( $order_id, '_customer_ip', 1 );

		if ( get_post_meta( $order_id, '_customer_id', 1 ) ) {
			global $wpdb;
			$customers_table = $wpdb->prefix . 'fs_customers';
			$this->user      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$customers_table} WHERE id=%d", intval( get_post_meta( $order_id, '_customer_id', 1 ) ) ), ARRAY_A );
		}


		$payment_method_id = (int) get_post_meta( $order_id, '_payment', 1 );
		if ( $payment_method_id ) {
			$this->payment_method = get_term( $payment_method_id, FS_Config::get_data( 'product_pay_taxonomy' ) );
		}

		$delivery_method = get_post_meta( $order_id, '_delivery', 1 );
		if ( isset( $delivery_method['method'] ) ) {
			$this->delivery_method = get_term( $delivery_method['method'], FS_Config::get_data( 'product_del_taxonomy' ) );

			if ( ! is_wp_error( $this->delivery_method ) ) {
				$this->delivery_method->cost                    = apply_filters( 'fs_price_format', (float) get_term_meta( $this->delivery_method->term_id, '_fs_delivery_cost', 1 ) );
				$this->delivery_method->city                    = get_post_meta( $order_id, 'city', 1 );
				$this->delivery_method->delivery_address        = $delivery_method['address'];
				$this->delivery_method->delivery_service_number = $delivery_method['secession'];
			}
		}

		$this->count = is_array( $this->items ) ? count( $this->items ) : 0;

		$this->status = get_post_status_object( $this->post->post_status )->label;

		$this->date = $this->post->post_date;
	}

	/**
	 * Добавляет событие в историю  заказа
	 *
	 * @param int $order_id ID заказа
	 * @param array $event должно содержать поля :
	 *
	 *  'name'           => Название события ,
	 *  'description'    => Описание события,
	 *  'initiator_id'   => ID пользотателя инициировашего событие,
	 *  'initiator_name' => Имя пользотателя инициировашего событие,
	 *  'time'           => время события,
	 *  'changed'        => измененные поля
	 *
	 * @return bool|int|\WP_Error
	 */
	public function add_history_event( $order_id = 0, $event = [] ) {
		$history = $this->get_order_history( $order_id, false, [ 'sort' => 'asc' ] );

		if ( ! isset( $event['id'] ) ) {
			return new \WP_Error( 'fs_no_event_field_isset', __( 'Не заполненны все объязательные поля события!' ) );
		}

		array_push( $history, $event );

		return update_post_meta( $order_id, 'fs_order_history', $history );
	}

	/**
	 * Возвращает историю заказа в виде массива
	 *
	 * @param int $order_id идентификатор заказа
	 * @param bool $creation_date включать ли дату создания в историю
	 * @param array $args дополнительные параметры
	 *
	 * @return mixed|\WP_Error
	 */
	public function get_order_history( int $order_id = 0, $creation_date = true, $args = [] ) {
		$order_id = $order_id ? $order_id : $this->ID;
		$args     = wp_parse_args( $args, [
			'order' => 'desc'
		] );

		if ( ! $order_id ) {
			return new \WP_Error( 'no-order-id', __( 'Order number not specified', 'f-shop' ) );
		}

		$history = get_post_meta( $order_id, 'fs_order_history', 1 ) ? get_post_meta( $order_id, 'fs_order_history', 1 ) : [];

		// Сортируем историю в обратном порядке
		if ( is_array( $history ) && count( $history ) && $args['order'] == 'desc' ) {
			krsort( $history );
		}

		// Добавляем дату создания заказа в историю
		if ( $creation_date ) {
			array_push( $history, [
				'id'             => 'create_order',
				'initiator_id'   => 0,
				'initiator_name' => implode( ' ', [ $this->user['first_name'], $this->user['last_name'] ] ),
				'time'           => strtotime( $this->post->post_date ),
				'data'           => [
					'order_id' => $order_id
				]
			] );
		}

		$history = array_map( function ( $item ) {
			$item['name']        = $this->get_history_event_name( $item );
			$item['description'] = $this->get_history_event_detail( $item );

			return $item;
		}, $history );

		return apply_filters( 'fs_order_history', $history );
	}

	/**
	 * Возвращает название события в истории заказа
	 *
	 * @param $item
	 *
	 * @return mixed|string
	 */
	public function get_history_event_name( $item ) {
		$names = [
			'change_order_status' => __( 'Change order status', 'f-shop' ),
			'create_order'        => __( 'Create order', 'f-shop' ),
			'adding_a_comment'    => __( 'Adding a comment', 'f-shop' ),
		];

		$names = apply_filters( 'fs_order_history_names', $names );

		return isset( $names[ $item['id'] ] ) ? $names[ $item['id'] ] : '';
	}


	/**
	 * Возвращает описание события в истории заказа
	 *
	 * @param $item
	 *
	 * @return mixed|string
	 */
	public function get_history_event_detail( $item ) {
		$detail = '';

		if ( $item['id'] == 'change_order_status' ) {
			$user           = get_user_by( 'ID', $item['initiator_id'] );
			$order_statuses = FS_Orders::default_order_statuses();
			$order_status   = isset( $order_statuses[ $item['data']['status'] ]['name'] ) ? $order_statuses[ $item['data']['status'] ]['name'] : $item['data']['status'];
			$detail         = sprintf( __( 'User "%s" changed order status to "%s"', 'f-shop' ), $user->display_name, $order_status );
		} elseif ( $item['id'] == 'adding_a_comment' ) {
			$user   = get_user_by( 'ID', $item['initiator_id'] );
			$detail = sprintf( __( 'User "%s" added a comment. <a href="%s" target="_blank"> Go to comment <a>', 'f-shop' ),
				$user->display_name,
				esc_url( add_query_arg( [
					'action' => 'editcomment',
					'c'      => $item['data']['comment_id']
				], admin_url( '/comment.php' ) ) ) );
		} elseif ( $item['id'] == 'create_order' ) {
			$detail = sprintf( __( 'User "%s" created an order', 'f-shop' ), $item['initiator_name'] );
		}

		return apply_filters( 'fs_order_history_detail', $detail );
	}

	/**
	 * Получение товаров заказа
	 *
	 * @return array
	 */
	public function getItems(): array {
		return $this->items;
	}

	/**
	 * Получение общей суммы покупки
	 *
	 * @return int
	 */
	public function getTotalAmount(): int {
		return apply_filters( 'fs_price_format', $this->total_amount );
	}

	/**
	 * @return \WP_Term
	 */
	public function getDeliveryMethod(): \WP_Term {
		return $this->delivery_method;
	}

	/**
	 * @param \WP_Term $delivery_method
	 */
	public function setDeliveryMethod( \WP_Term $delivery_method ): void {
		$this->delivery_method = $delivery_method;
	}

	/**
	 * @return \WP_Term
	 */
	public function getPaymentMethod(): \WP_Term {
		return $this->payment_method;
	}

	/**
	 * @param \WP_Term $payment_method
	 */
	public function setPaymentMethod( \WP_Term $payment_method ): void {
		$this->payment_method = $payment_method;
	}

	/**
	 * @return string
	 */
	public function getComment(): string {
		return $this->comment;
	}

	/**
	 * @param string $comment
	 */
	public function setComment( string $comment ): void {
		$this->comment = $comment;
	}

	/**
	 *
	 * @param array $items
	 */
	public function setItems( array $items ): void {
		$this->items = $items;
	}

	/**
	 * @return int
	 */
	public function getCount(): int {
		return $this->count;
	}

	/**
	 * @param int $count
	 */
	public function setCount( int $count ): void {
		$this->count = $count;
	}

	/**
	 * @return string
	 */
	public function getStatus(): string {
		return $this->status;
	}

	/**
	 * @param string $status
	 */
	public function setStatus( string $status ): void {
		$this->status = $status;
	}

	/**
	 * Возваращает локализированную дату и время заказа
	 *
	 * @param string $format
	 *
	 * @return mixed
	 */
	public function getDate( $format = '' ) {
		if ( ! $format ) {
			$format = get_option( 'time_format' );
		}

		return date_i18n( $format, strtotime( $this->date ) );
	}

	/**
	 * @param mixed $date
	 */
	public function setDate( $date ): void {
		$this->date = $date;
	}

	/**
	 * @return int
	 */
	public function getID(): int {
		return $this->ID;
	}

	/**
	 * @param int $ID
	 */
	public function setID( int $ID ): void {
		$this->ID = $ID;
	}

	/**
	 * @return int
	 */
	public function get_last_order_id() {

		return isset( $_SESSION['fs_last_order_id'] ) && is_numeric( $_SESSION['fs_last_order_id'] )
			? intval( $_SESSION['fs_last_order_id'] ) : 0;
	}
}