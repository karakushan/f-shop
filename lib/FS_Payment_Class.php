<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 25.11.2017
 * Time: 22:14
 */

namespace FS;


class FS_Payment_Class {

	function __construct() {
		add_shortcode( 'fs_order_pay', array( $this, 'order_pay' ) );
	}

	/**
	 * Возвращает все зарегистрированные способы оплаты в виде масссива
	 * @return mixed|void
	 */
	function payment_methods( $order_id = 0 ) {
		$methods = array();

		return apply_filters( "fs_payment_methods", $methods, $order_id );
	}

	/**
	 * Шорткод для помещения на страницу оплаты заказа
	 *
	 * Выводит необходимые формы, кнопки, ссылки для оплаты выбранным методом
	 * Также после оплаты идёт переадресация на эту страницу
	 *
	 * @param int $order_id
	 *
	 * @return string
	 */
	function order_pay( $order_id = 0 ) {
		$html = '';
		if ( empty( $order_id ) && ! empty( $_GET['order_id'] ) ) {
			$order_id = intval( $_GET['order_id'] );
		}
		$order = FS_Orders_Class::get_order( $order_id );
		switch ( (string) $_GET['fs_action'] ) {
			case 'order_status':
				$order_status = FS_Orders_Class::get_order_status( $order_id );
				$html         .= '<div class="fs-order-status">Статус заказа №' . $order_id . ': <b>' . $order_status . '</b></div>';
				break;
			case 'payment_method':
				$method_name    = sanitize_text_field( $_GET['payment_method'] );
				$payment_method = $this->payment_methods( $order_id );
				$html           .= '<h3>Оплата заказа №' . $order_id . ' с помощью ' . $payment_method[ $method_name ]['name'] . '</h3>';
				$html           .= '<div class="fs-pay-wrapper">';
				$html           .= '<div class="amount">Сумма покупки: ' . $order->sum . ' ' . fs_currency() . '</div>';
				$html           .= $payment_method[ $method_name ]['image'];
				$html           .= $payment_method[ $method_name ]['html'];
				$html           .= '</div>';
				break;

			default:
				$html .= '<p>Похоже вы попали на эту страницу случайно</p>';
				break;

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
					'action'         => 'payment_method',
					'order_id'       => $order_id,
					'payment_method' => $k
				), get_permalink( fs_option( 'page_payment' ) ) ) ), $payment_method['name'] );
			}
		}
	}


}