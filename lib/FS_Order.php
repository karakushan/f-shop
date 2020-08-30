<?php


namespace FS;


class FS_Order {

	/**
	 * Товары в заказе
	 *
	 * @var array
	 */
	private $items = [];

	/**
	 * @var \WP_Term
	 */
	private $delivery_method = null;

	/**
	 * @var \WP_Term
	 */
	private $payment_method = null;

	/**
	 * Общая сумма заказа
	 *
	 * @var int
	 */
	private $total_amount = 0;

	/**
	 * Комментарий к заказу
	 *
	 * @var string
	 */
	private $comment = '';

	/**
	 * Статус заказа
	 *
	 * @var string
	 */
	private $status = 'new';

	/**
	 * Количество товаров в заказе
	 *
	 * @var int
	 */
	private $count = 0;

	/**
	 * Дата и время заказа
	 *
	 * @var
	 */

	private $date;

	/**
	 * Номер заказа
	 *
	 * @var int
	 */
	private $ID = 0;

	public function __construct( $order ) {

		if ( $order ) {
			$this->set_order( $order );
		}

	}

	/**
	 * Устанавливает данные заказа
	 *
	 * @param $order \WP_Post
	 */
	public function set_order( $order ) {

		$this->ID = (int) $order->ID;

		$this->items        = (array) get_post_meta( $order->ID, '_products', 1 );
		$this->total_amount = (float) get_post_meta( $order->ID, '_amount', 1 );
		$this->comment      = (float) get_post_meta( $order->ID, '_comment', 1 );

		$payment_method_id = (int) get_post_meta( $order->ID, '_payment', 1 );
		if ( $payment_method_id ) {
			$this->payment_method = get_term( $payment_method_id, FS_Config::get_data( 'product_pay_taxonomy' ) );
		}

		$delivery_method = get_post_meta( $order->ID, '_delivery', 1 );
		if ( isset( $delivery_method['method'] ) ) {
			$this->delivery_method = get_term( $delivery_method['method'], FS_Config::get_data( 'product_del_taxonomy' ) );

			if ( is_object( $this->delivery_method ) ) {
				$this->delivery_method->cost = apply_filters( 'fs_price_format', (float)get_term_meta( $this->delivery_method->term_id, '_fs_delivery_cost', 1 ) );
				$this->delivery_method->delivery_address = $delivery_method['secession'];
			}
		}

		$this->count = count( $this->items );

		$this->status = get_post_status_object( $order->post_status )->label;

		$this->date = $order->post_date;
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
}