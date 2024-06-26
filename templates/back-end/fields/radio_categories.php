<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 16:13
 * @var array $args
 * @var string $name
 */
$args['query_params'] = isset( $args['query_params'] ) ? (array) $args['query_params'] : [];
$query_params         = wp_parse_args( $args['query_params'],
	[ 'taxonomy' => $args['taxonomy'], 'hide_empty' => false ]
);

$terms = get_terms( $query_params );
if ( $terms ) {
	foreach ( $terms as $key => $term ) {
		echo '<div class="radio fs-input-radio fs-input-radio-' . esc_attr( $term->term_id ) . '">';
		do_action( 'fs_before_radio_category_field', $name, $term, $args );
		echo '<label><input type="radio" name="' . esc_attr( $name ) . '" ' . checked( 0, $key, 0 ) . ' value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</label>';
		do_action( 'fs_after_radio_category_field', $name, $term, $args );
		echo '</div>';
	}
}