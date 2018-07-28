<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 16:13
 */
$terms = get_terms( array( 'taxonomy' => $args['taxonomy'], 'hide_empty' => false ) );
if ( $terms ) {
	foreach ( $terms as $key => $term ) {
		echo '<div class="radio">';
		echo '<label><input type="radio" name="' . esc_attr( $name ) . '" ' . checked( 0, $key, 0 ) . ' value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</label>';
		echo '</div>';
	}
}