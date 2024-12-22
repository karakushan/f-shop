<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Delivery class
 */
class FS_Delivery {

	public $name = '';
	public $city = '';
	public $cost = 0;
	public $delivery_address = '';
	public $delivery_service_number = 0;
	public $term_id = 0;

	/**
	 * FS_Delivery constructor.
	 *
	 * @param null $order_id
	 */
	public function __construct( $order_id = null ) {
		if ( ! $order_id ) {
			return;
		}
		$delivery_method = get_post_meta( $order_id, '_delivery', 1 );
		if ( ! isset( $delivery_method['method'] )
		     || ! is_numeric( $delivery_method['method'] )
		     || is_wp_error( get_term( absint( $delivery_method['method'] ), FS_Config::get_data( 'product_del_taxonomy' ) ) ) ) {
			return;
		}


		$term = get_term( $delivery_method['method'], FS_Config::get_data( 'product_del_taxonomy' ) );

		if ( ! $term || is_wp_error( $term ) ) {
			return;
		}

		$this->term_id                 = absint( $term->term_id );
		$this->name                    = $term->name;
		$this->cost                    = get_term_meta( $term->term_id, '_fs_delivery_cost', 1 ) ? apply_filters( 'fs_price_format', (float) get_term_meta( $term->term_id, '_fs_delivery_cost', 1 ) ) : 0;
		$this->city                    = get_post_meta( $order_id, 'city', 1 );
		$this->delivery_address        = ! empty( $delivery_method['address'] ) ? $delivery_method['address'] : '';
		$this->delivery_service_number = ! empty( $delivery_method['secession'] ) ? $delivery_method['secession'] : '';

	}

	/**
	 * Retrieves the available shipping methods based on the specified arguments.
	 *
	 * @param array $args Optional. Arguments to filter the shipping methods. Default values include:
	 *                    'taxonomy'   => The taxonomy to query (default: FS_Config::get_data('product_del_taxonomy')).
	 *                    'hide_empty' => Whether to hide terms not assigned to any posts (default: false).
	 *                    'meta_key'   => Meta key for sorting (default: '_fs_term_order').
	 *                    'orderby'    => Field to order terms by (default: 'meta_value_num').
	 *                    'order'      => Order to retrieve terms in (default: 'ASC').
	 *
	 * @return array An array of shipping method objects with added price details from the term meta.
	 */
	public static function get_shipping_methods( $args = [] ) {

		$args             = wp_parse_args( $args,
			[
				'taxonomy'   => FS_Config::get_data( 'product_del_taxonomy' ),
				'hide_empty' => false,
				'meta_key'   => '_fs_term_order',
				'orderby'    => 'meta_value_num',
				'order'      => 'ASC',
			]
		);
		$methods          = [];
		$shipping_methods = get_terms( $args );

		foreach ( $shipping_methods as $shipping_method ) {
			$method        = $shipping_method;
			$method->price = floatval( get_term_meta( $shipping_method->term_id, '_fs_delivery_cost', 1 ) );
			$methods[]     = $method;
		}

		return apply_filters( 'fs_shipping_methods', $methods );
	}

} 