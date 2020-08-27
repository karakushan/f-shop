<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 30.04.2018
 * Time: 19:07
 */

namespace FS;

/*
 * Виджет корзины
 */

class FS_Cart_Widget extends \WP_Widget {
	function __construct() {
		parent::__construct(
			'fs_cart_widget',
			__( 'Cart', 'f-shop' ),
			array( 'description' => __( 'Cart widget', 'f-shop' ) )
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		do_action( 'fs_cart_widget' );
		echo $args['after_widget'];
	}
}