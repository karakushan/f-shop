<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 22.04.2018
 * Time: 14:36
 */

namespace FS;


class FS_Widget_CLass {

	public function __construct() {
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	function register_widgets() {
		register_widget( '\FS\FS_Cart_Widget' );
	}
}

/*
 * Виджет корзины
 */

class FS_Cart_Widget extends \WP_Widget {
	function __construct() {
		parent::__construct(
			'fs_cart_widget',
			'Корзина',
			array( 'description' => 'Позволяет вывести корзину в любом удобном месте' )
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		do_action( 'fs_cart_widget' );
		echo $args['after_widget'];
	}
}