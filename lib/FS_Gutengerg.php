<?php

namespace FS;

class FS_Gutengerg {

	private $blocks = [
		'latest_products' => [
			'is_active' => true
		]
	];

	private array $dependencies = [
		'wp-api-fetch',
		'wp-block-editor',
		'wp-blocks',
		'wp-components',
		'wp-element',
		'wp-i18n',
		'wp-url',
	];

	public function __construct() {
		require_once FS_PLUGIN_PATH."gutenberg/fs-range-slider/fs-range-slider.php";

		add_action( 'admin_init', [ $this, 'register_blocks' ] );
		add_filter( 'block_categories_all', [ $this, 'register_layout_category' ] );

		$this->blocks = array_filter( $this->blocks, function ( $item ) {
			return $item['is_active'] === true;
		} );
	}

	function register_blocks() {
		if ( ! is_array( $this->blocks ) || empty( $this->blocks ) ) {
			return;
		}

		foreach ( $this->blocks as $key => $block ) {
			$handle = 'fs_' . $key;
			$src    = FS_PLUGIN_PATH . 'gutenberg/' . $key . '/build/index.js';

			wp_register_script( $handle, $src, $this->dependencies, filemtime( $src ) );
			register_block_type(
				FS_PLUGIN_PATH . 'gutenberg/' . $key . '/build',
				array(
					'attributes'      => $block['attributes'] ?? [],
					'editor_script'   => $handle,
					'render_callback' => [ $this, $key.'_render_callback' ],
					'skip_inner_blocks' => true,
				)
			);
		}
	}

	function latest_products_render_callback() {
		return 'test block';
	}

	function register_layout_category( $categories ) {

		$categories[] = array(
			'slug'  => 'f-shop',
			'title' => 'F-Shop'
		);

		return $categories;
	}
}