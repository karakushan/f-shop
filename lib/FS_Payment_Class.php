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
			'item-class'         => 'fs-pay-item',
			'aviable_methods'    => false
		), $atts );

		$order_id   = ! empty( $_GET['order_id'] ) && is_numeric( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
		$pay_method = ! empty( $_GET['pay_method'] ) ? $_GET['pay_method'] : null;

		// Если не указан номер заказа или метод оплаты
		if ( ! $order_id && $pay_method ) {
			return '<div class="fs-order-pay"><p>' . __( 'No order number or payment method specified.', 'f-shop' ) . '</p></div>';
		}


		$order_class     = new FS_Orders_Class();
		$html            = '';
		$order           = $order_class->get_order( $order_id );
		$order_amount    = floatval( $order->data->_amount );
		$payment_methods = $this->payment_methods();

		do_action( 'fs_order_pay_before' );


		if ( $payment_methods && $atts['aviable_methods'] ) {
			$html .= '<div class="fs-order-pay">';
			$html .= '<h3>' . __( 'Available payment methods', 'f-shop' ) . '</h3>';
			$html .= '<p>' . __( 'If the previously chosen payment method does not suit you, you can pay by one of the ways below', 'f-shop' ) . ':</p>';
			$html .= '<div class="row">';
			foreach ( $payment_methods as $slug => $payment_method ) {
				$term     = get_term_by( 'slug', $slug, $fs_config->data['product_pay_taxonomy'] );
				$pay_name = $term ? $term->name : $payment_method['name'];
				$logo     = fs_get_category_image( $term->term_id );

				$html .= '<div class="' . esc_attr( $atts['item-wrapper-class'] ) . '">';
				$html .= '<a href="' . esc_url( add_query_arg( array(
						'pay_method' => $slug,
						'order_id'   => $order_id
					), get_the_permalink() ) ) . '" class="' . esc_attr( $atts['item-class'] ) . '" id="' . esc_attr( $slug ) . '">';

				if ( $logo ) {
					$html .= '<figure>' . $logo . '</figure>';
				}
				$html .= '<h4>' . esc_html( $pay_name ) . '</h4>';
				$html .= '</a>';
				$html .= '</div>';

			}
			$html .= '</div><!--END .row-->';
			$html .= '</div><!--END .fs-order-pay-->';
		}

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
			$term = get_term_by( 'slug', $pay_method, $fs_config->data['product_pay_taxonomy'] );
			if ( ! empty( $pay_method ) && ! empty( $payment_methods[ $pay_method ] ) && ! is_wp_error( $term ) ) {

				$html .= fs_action_message(
					sprintf( __( 'Оплата заказа #%d с помощью &laquo;%s&raquo;', 'f-shop' ), $order_id, $term->name ),
					sprintf( __( 'В случае успешной оплаты с вас будет снято %s %s. ', 'f-shop' ), apply_filters( 'fs_price_format', $order_amount ), fs_currency() ),
					'info',
					[
						'echo'   => false,
						'icon'   => '<img src="' . esc_url( FS_PLUGIN_URL . 'assets/img/icon/pay.svg' ) . '" alt="icon">',
						'button' => $payment_methods[ $pay_method ]['html']
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