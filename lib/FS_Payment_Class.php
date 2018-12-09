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
		global $fs_config;

		$atts = shortcode_atts( array(
			'item-wrapper-class' => 'col-lg-2 col-sm-6',
			'item-class'         => 'fs-pay-item'
		), $atts );

		$order_class     = new FS_Orders_Class();
		$order_id        = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
		$order           = $order_class->get_order( $order_id );
		$payment_methods = $this->payment_methods();

		do_action( 'fs_order_pay_before' );
		$html = '<div class="fs-order-pay">';
		$html .= '<h3>' . __( 'Available payment methods', 'f-shop') . '</h3>';
		$html .= '<p>' . __( 'If the previously chosen payment method does not suit you, you can pay by one of the ways below', 'f-shop') . ':</p>';
		$html .= '<div class="row">';

		if ( $payment_methods ) {
			foreach ( $payment_methods as $id => $payment_method ) {
				$term     = get_term_by( 'slug', $id, $fs_config->data['product_pay_taxonomy'] );
				$pay_name = $term ? $term->name : $payment_method['name'];

				$html .= '<div class="' . esc_attr( $atts['item-wrapper-class'] ) . '">';
				$html .= '<a href="' . esc_url( add_query_arg( array(
						'pay_method' => $id,
						'order_id'   => $order_id
					), get_the_permalink() ) ) . '" class="' . esc_attr( $atts['item-class'] ) . '" id="' . esc_attr( $id ) . '">';
				$html .= '<figure><img src="' . esc_url( $payment_method['logo'] ) . '" alt="' . esc_attr( $pay_name ) . '"></figure>';
				$html .= '<h4>' . esc_html( $pay_name ) . '</h4>';
				$html .= '</a>';
				$html .= '</div>';

			}
		}
		$html .= '</div><!--END .row-->';
		if ( $order_id && in_array( get_post_status( $order_id ), array( 'paid' ) ) ) {
			// Если указан номер заказа то выводим сообщение об успешной оплате
			$after_pay_message = sprintf( '<h2>' . __( 'Order #%d paid successfully', 'f-shop') . '</h2>', $order_id );
			// TODO здесь еще нужно будет сделать проверку по сессиям, если это не тот пользователь то выдаём сообщение "вы не имеете права просматривать эту страницу"
			if ( isset( $order->payment_id ) ) {
				$message           = get_term_meta( $order->payment_id, '_fs_after_pay_message', 1 );
				$after_pay_message = ! empty( $message ) ? $message : $after_pay_message;

			}

			return $after_pay_message;
		} else {
			$term = get_term_by( 'slug', $_GET['pay_method'], $fs_config->data['product_pay_taxonomy'] );
			if ( ! empty( $_GET['pay_method'] ) && ! empty( $payment_methods[ $_GET['pay_method'] ] ) && $term ) {

				$html .= sprintf( '<h2>' . __( 'Payment  <span>%s <span>%s</span></span> with  <span>%s</span>', 'f-shop') . '</h2>', apply_filters( 'fs_price_format', $order->sum ), fs_currency(), $term->name );
				if ( ! empty( $payment_methods[ $_GET['pay_method'] ]['description'] ) ) {
					$html .= sprintf( '<p>%s</p>', $payment_methods[ $_GET['pay_method'] ]['description'] );
				}
				$html .= $payment_methods[ $_GET['pay_method'] ]['html'];
			}
		}

		$html .= '</div><!--END .fs-order-pay-->';

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