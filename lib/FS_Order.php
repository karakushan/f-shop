<?php


namespace FS;

use WP_Post;


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

	public $meta;


	public function __construct( $order ) {
		$this->set_order( $order );

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

		$products = get_post_meta( $order_id, '_products', 1 ) ? get_post_meta( $order_id, '_products', 1 ) :[];
		$this->meta = get_post_meta( $order_id );


		$this->items = array_values( array_map( function ( $item ) {
			return fs_set_product( $item );
		}, $products ) );

		$this->total_amount = (float) get_post_meta( $order_id, '_amount', 1 );
		$this->discount     = (float) get_post_meta( $order_id, '_order_discount', 1 );
		$this->comment      = get_post_meta( $order_id, '_comment', 1 );
		$this->user         = (array) get_post_meta( $order_id, '_user', 1 );
		$this->user['ip']   = get_post_meta( $order_id, '_customer_ip', 1 );

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