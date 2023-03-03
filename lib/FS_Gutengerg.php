<?php

namespace FS;

class FS_Gutengerg {

	public function __construct() {
		require_once FS_PLUGIN_PATH."gutenberg/fs-range-slider/fs-range-slider.php";
		require_once FS_PLUGIN_PATH."gutenberg/fs-shop-categories/fs-shop-categories.php";

		add_filter( 'block_categories_all', [ $this, 'register_layout_category' ] );
	}

	function register_layout_category( $categories ) {

		$categories[] = array(
			'slug'  => 'f-shop',
			'title' => 'F-Shop'
		);

		return $categories;
	}
}