<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 18.02.2018
 * Time: 16:13
 */
if ( $args['values'] ) {
	foreach ( $args['values'] as $key => $value ) {

		echo '<div class="radio">';
		echo '<label>';
		echo '<input type="radio" name="' . esc_attr( $name ) . '" ' . checked( $args['value'], $key, 0 ) . ' value="' . esc_attr( $key ) . '">';

		if ( $args['icon'] ) {
			echo '<picture>'.fs_get_category_image( $key, 'full' ).'</picture>';
		}
		echo '<span>'.esc_html( $value ).'</span>';
		echo '</label>';
		echo '</div>';
	}
}