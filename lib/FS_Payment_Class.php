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
	function payment_methods() {
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
	function order_pay( $atts ) {

		$atts = shortcode_atts( array(
			'item-wrapper-class' => 'col-lg-2 col-sm-6',
			'item-class'         => 'fs-pay-item'
		), $atts );

		$order_id = ! empty( $_GET['order_id'] ) && is_numeric( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : FS_Orders::get_last_order_id();

		// Если не указан номер заказа
		if ( ! $order_id ) {
			return '<div class="fs-order-pay"><p>' . __( 'No order number specified.', 'f-shop' ) . '</p></div>';
		}

		$order = FS_Orders::get_order( $order_id );
		$pay_method = intval( $order->data->_payment );

		// Если не указан метод оплаты
		if ( ! $pay_method ) {
			return '<div class="fs-order-pay"><p>' . __( 'No payment method specified.', 'f-shop' ) . '</p></div>';
		}

		$html            = '';
		$order_amount    = floatval( $order->data->_amount );
		$payment_methods = $this->payment_methods();

		do_action( 'fs_order_pay_before' );

		if ( $order_id && in_array( get_post_status( $order_id ), array( 'paid' ) ) ) {

			// Если указан номер заказа то выводим сообщение об успешной оплате
			if ( get_term_meta( $order->payment_id, '_fs_after_pay_message', 1 ) ) {
				$html .= get_term_meta( $order->payment_id, '_fs_after_pay_message', 1 );
			} else {
				$html .= fs_action_message(
					sprintf( __( 'Order #%d paid successfully', 'f-shop' ), $order_id ),
					__( 'You or someone else paid for this order.', 'f-shop' ),
					'success',
					[ 'echo' => false ]
				);

			}
		} else {
			$term = get_term_by( 'id', $pay_method, FS_Config::get_data('product_pay_taxonomy') );

			if ( ! empty( $payment_methods[ $term->slug ] ) && ! is_wp_error( $term ) ) {

				$html .= fs_action_message(
					sprintf( __( 'Оплата заказа #%d с помощью &laquo;%s&raquo;', 'f-shop' ), $order_id, $term->name ),
					sprintf( __( 'В случае успешной оплаты с вас будет снято %s %s. ', 'f-shop' ), apply_filters( 'fs_price_format', $order_amount ), fs_currency() ),
					'info',
					[
						'echo'   => false,
						'icon'   => '<img src="' . esc_url( FS_PLUGIN_URL . 'assets/img/icon/pay.svg' ) . '" alt="icon">',
						'button' => $payment_methods[ $term->slug ]['html']
					]
				);
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