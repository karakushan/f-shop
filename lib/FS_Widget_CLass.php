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
		register_widget( '\FS\FS_Attribute_Widget' );
		register_widget( '\FS\FS_Price_Widget' );
	}
}