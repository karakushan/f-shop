<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 05.04.2017
 * Time: 12:24
 */

namespace FS;


class FS_Migrate_Class {

	function __construct() {

	}

	/**
	 * Импортирует атрибуты товаров из опций
	 */
	static function import_option_attr() {
		$fs_atributes = get_option( 'fs-attr-group' );
		global $post;


		if ( ! empty( $fs_atributes ) ) {
			foreach ( $fs_atributes as $k => $att ) {

				$term = term_exists( $att['slug'], 'product-attributes' );
				if ( ! $term ) {
					$args     = array(
						'alias_of'    => '',
						'description' => '',
						'parent'      => 0,
						'slug'        => $att['slug'],
					);
					$ins_term = wp_insert_term( $att['title'], 'product-attributes', $args );
					if ( is_wp_error( $ins_term ) ) {
						update_option( 'fs_last_error', $ins_term->get_error_message() );
					}
				}

				if ( ! empty( $att['attributes'] ) ) {
					foreach ( $att['attributes'] as $att_key => $attribute ) {
						$args_child     = array(
							'alias_of'    => '',
							'description' => '',
							'parent'      => $term['term_id'],
							'slug'        => $att_key,
						);
						$ins_term_child = wp_insert_term( $attribute, 'product-attributes', $args_child );
						if ( is_wp_error( $ins_term_child ) ) {
							update_option( 'fs_last_error', $ins_term_child->get_error_message() );
						}
					}
				}


			}
		}

		$query = new \WP_Query( array( 'post_type' => 'product', 'posts_per_page' => - 1 ) );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_attr = get_post_meta( $post->ID, 'fs_attributes', false );
				$post_attr = isset( $post_attr[0] ) ? $post_attr[0] : array();
				if ( $post_attr ) {
					$post_terms = array();
					foreach ( $post_attr as $ps ) {
						if ( $ps ) {
							foreach ( $ps as $child_key => $pa ) {
								if ( $pa != 0 ) {
									$post_term    = term_exists( $child_key, 'product-attributes' );
									$post_terms[] = ! empty( $post_term['term_id'] ) ? $post_term['term_id'] : 0;
								}
							}
						}
					}
					$post_terms = array_unique( $post_terms );
					if ( ! empty( $post_terms ) ) {
						wp_set_post_terms( $post->ID, $post_terms, 'product-attributes', true );
					}

				}
			}
		}
	}
}