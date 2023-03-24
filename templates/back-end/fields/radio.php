<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 18.02.2018
 * Time: 16:13
 * @var $args array
 * @var $name string
 */



if (  !empty( $args['values'] ) ) {
    $value =  $args['value'] ?? key($args['values'][0]);
	foreach ( $args['values'] as $key => $value ) {

		echo '<div class="radio">';
		echo '<label>';
		echo '<input '.fs_parse_attr($args['attributes'] ?? []).' type="radio" name="' . esc_attr( $name ) . '" ' . checked( $args['value'], $key, 0 ) . ' value="' . esc_attr( $key ) . '">';

		if ( $args['icon'] ) {
			echo '<picture>'.fs_get_category_image( $key, 'full' ).'</picture>';
		}
		echo '<span>'.esc_html( $value ).'</span>';
		echo '</label>';
		echo '</div>';
	}
}
