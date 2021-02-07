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
	public function set_order( int $order_id ) {

		$this->ID = $order_id;

		if ( ! $order_id && $this->get_last_order_id() ) {
			$this->ID = $this->get_last_order_id();
		}

		$this->post = get_post( $this->ID );


		$order_id = $order_id ? $order_id : $this->get_last_order_id();

		if ( ! is_numeric( $order_id ) || $order_id == 0 ) {
			return null;
		}

		$products = get_post_meta( $order_id, '_products', 0 );
		if ( is_array( $products ) ) {
			$products = array_shift( $products );
		}
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