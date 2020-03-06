<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Delivery class
 */
class FS_Delivery {

	/**
	 * Returns available payment methods
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_shipping_methods( $args = [] ) {

		$args             = wp_parse_args( $args,
			[
				'taxonomy'   => FS_Config::get_data( 'product_del_taxonomy' ),
				'hide_empty' => false,
			]
		);
		$methods          = [];
		$shipping_methods = get_terms( $args );

		foreach ( $shipping_methods as $shipping_method ) {
			$method        = $shipping_method;
			$method->price = floatval( get_term_meta( $shipping_method->term_id, '_fs_delivery_cost', 1 ) );
			$methods[]     = $method;
		}

		return $methods;
	}

} 