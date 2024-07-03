<?php

namespace FS\Integrations;

use FS\FS_Config;

class FS_Yoast_SEO {

	public function __construct() {
		// yoast seo meta description
		add_filter( 'wpseo_title', [ $this, 'yoast_seo_title' ],11, 1 );
		add_action( 'wpseo_metadesc', [ $this, 'yoast_seo_description' ] );
		add_filter( 'wpseo_robots', [ $this, 'add_noindex_meta_tag' ] );
		add_filter( 'wpseo_canonical', [ $this, 'replace_products_canonical' ], 10, 1 );
	}

	/**
	 * Blocks pages with product filters from indexing by search engines
	 *
	 * @param $robots
	 *
	 * @return mixed|string
	 */
	function add_noindex_meta_tag( $robots ) {
		if ( isset( $_GET['fs_filter'] ) ) {
			$robots = 'noindex, nofollow';
		}

		return $robots;
	}

	/**
	 * Change wordpress seo meta description
	 *
	 * @param string $meta_description
	 *
	 * @return string
	 */
	public function yoast_seo_description( $meta_description ) {
		// Если это страница каталога
		if ( fs_is_catalog() && fs_option( '_fs_catalog_meta_description' ) != '' ) {
			$meta_description = fs_option( '_fs_catalog_meta_description' );
		} elseif ( fs_is_product_category() ) { // Если посетитель находится на странице таксономии товаров
			$meta_description = fs_get_term_meta( '_seo_description' );
		}

		return apply_filters( 'fs_meta_description', $meta_description );
	}

	/**
	 * Изменяет meta title
	 *
	 * @param $title
	 *
	 * @return mixed
	 */
	public function yoast_seo_title( $title ) {
		if ( fs_is_catalog() && fs_option( '_fs_catalog_meta_title' ) != '' ) {
			$title = esc_attr( apply_filters( 'the_title', fs_option( '_fs_catalog_meta_title' ) ) );
		} elseif ( fs_is_product_category() ) {
			$term_id        = get_queried_object_id();
			$meta_title     = get_term_meta( $term_id, fs_localize_meta_key( '_seo_title' ), 1 ) ?: get_term_meta( $term_id, '_seo_title', 1 );
			$title=esc_attr( apply_filters( 'the_title', $meta_title ) );
		}

		return apply_filters( 'fs_meta_title', $title );
	}

	/**
	 * Change WordPress seo canonical
	 *
	 * @param $canonical
	 *
	 * @return string|string[]
	 */
	function replace_products_canonical( $canonical ) {
		$taxonomy_name = FS_Config::get_data( 'product_taxonomy' );
		if ( is_tax( $taxonomy_name ) && fs_option( 'fs_disable_taxonomy_slug' ) ) {
			$canonical = get_term_link( get_queried_object_id(), $taxonomy_name );
		}

		return $canonical;
	}
}