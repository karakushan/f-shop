<?php

// добавляем шорткоды полей в Contact Form 7
add_action( 'wpcf7_init', 'fs_cf7_add_shortcode' );
function fs_cf7_add_shortcode() {
	wpcf7_add_shortcode( 'post_url', 'fs_post_url_shortcode_handler', true );
	wpcf7_add_shortcode( 'post_id', 'fs_post_id_shortcode_handler', true );
	wpcf7_add_shortcode( 'post_title', 'fs_post_title_shortcode_handler', true );
	wpcf7_add_shortcode( array( 'delivery_methods', 'delivery_methods*' ), 'fs_delivery_shortcode_handler', true );
	wpcf7_add_shortcode( array( 'payment_methods', 'payment_methods*' ), 'fs_payment_shortcode_handler', true );

}

function fs_post_url_shortcode_handler( $tag ) {
	$field = '<input type="hidden" name="' . $tag['name'] . '" value="' . get_the_permalink() . '">';

	return $field;
}

function fs_post_id_shortcode_handler( $tag ) {
	$field = '<input type="hidden" name="' . $tag['name'] . '" value="' . get_the_id() . '">';

	return $field;
}

function fs_post_title_shortcode_handler( $tag ) {
	$field = '<input type="hidden" name="' . $tag['name'] . '" value="' . get_the_title() . '">';

	return $field;
}

function fs_delivery_shortcode_handler( $tag ) {
	$methods = get_terms( array( 'taxonomy' => 'fs-delivery-methods', 'hide_empty' => false ) );
	$options = '';
	if ( $tag['options'] ) {
		foreach ( $tag['options'] as $key => $option ) {
			$opt     = explode( ':', $option );
			$options .= $opt[0] . '=' . '"' . $opt[1] . '" ';
		}
	}
	$field = '';
	if ( $methods ) {
		$field .= '<select name="' . $tag['name'] . '" ' . $options . ' required>';
		$field .= '<option value="">' . __( 'Choose a shipping method', 'fast-shop' ) . '</option>';
		foreach ( $methods as $key => $method ) {
			$field .= '<option value="' . $method->name . '">' . $method->name . '</option>';
		}
		$field .= '</select>';
	}

	return $field;
}

function fs_payment_shortcode_handler( $tag ) {
	$methods = get_terms( array( 'taxonomy' => 'fs-payment-methods', 'hide_empty' => false ) );
	$options = '';
	if ( $tag['options'] ) {
		foreach ( $tag['options'] as $key => $option ) {
			$opt     = explode( ':', $option );
			$options .= $opt[0] . '=' . '"' . $opt[1] . '" ';
		}
	}
	$field = '';
	if ( $methods ) {
		$field .= '<select name="' . $tag['name'] . '" ' . $options . ' required>';
		$field .= '<option value="">' . __( 'Choose a shipping method', 'fast-shop' ) . '</option>';
		foreach ( $methods as $key => $method ) {
			$field .= '<option value="' . $method->name . '">' . $method->name . '</option>';
		}
		$field .= '</select>';
	}

	return $field;
}






