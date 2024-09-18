<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 25.11.2017
 * Time: 22:14
 */

namespace FS;


class FS_Payment {
	/**
	 * Возвращает все зарегистрированные способы оплаты в виде масссива
	 * @return mixed|void
	 */
	public static function payment_methods() {
		$methods = array();

		return apply_filters( "fs_payment_methods", $methods );
	}

	/**
	 * Шорткод для помещения на страницу оплаты заказа
	 *
	 * Выводит необходимые формы, кнопки, ссылки для оплаты выбранным методом
	 * Также после оплаты идёт переадресация на эту страницу
	 *
	 * @param array $atts - атрибуты шорткода
	 *
	 * @return string
	 */
	public static function order_pay() {
		$order_id = ! empty( $_GET['order_id'] ) && is_numeric( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : FS_Orders::get_last_order_id();

		// Если не указан номер заказа
		if ( ! $order_id ) {
			return '<div class="fs-order-pay"><p>' . __( 'No order number specified.', 'f-shop' ) . '</p></div>';
		}

		$order      = FS_Orders::get_order( $order_id );
		$pay_method = intval( $order->data->_payment );

		// Если не указан метод оплаты
		if ( ! $pay_method ) {
			return '<div class="fs-order-pay"><p>' . __( 'No payment method specified.', 'f-shop' ) . '</p></div>';
		}

		$html         = '';
		$order_amount = floatval( $order->data->_amount );

		$payment_methods = self::payment_methods();

		do_action( 'fs_order_pay_before' );

		if ( $order_id && in_array( get_post_status( $order_id ), array( 'paid' ) ) ) {
			$html = fs_frontend_template( 'payment/payment-success', array(
				'vars' => array(
					'order'    => $order,
					'order_id' => $order_id
				)
			) );
		} else {
			$term = get_term_by( 'id', $pay_method, FS_Config::get_data( 'product_pay_taxonomy' ) );

			if ( ! empty( $payment_methods[ $term->slug ] ) && ! is_wp_error( $term ) ) {
				$html = fs_frontend_template( 'payment/payment-form', array(
					'vars' => array(
						'term'         => $term,
						'pay_button'   => $payment_methods[ $term->slug ]['html'],
						'order_amount' => $order_amount,
						'order_id'     => $order_id
					)
				) );
			}
		}

		return $html;
	}


	/**
	 * Выводит доступные методы оплаты в виде ссылок
	 *
	 * @param $order_id
	 */
	function show_payment_methods( $order_id ) {
		if ( $this->payment_methods( $order_id ) ) {
			foreach ( $this->payment_methods( $order_id ) as $k => $payment_method ) {
				printf( '<a href="%s">%s</a>', esc_url( add_query_arg( array(
					'fs-action'      => 'payment_method',
					'order_id'       => $order_id,
					'payment_method' => $k
				), get_permalink( fs_option( 'page_payment' ) ) ) ), $payment_method['name'] );
			}
		}
	}


}