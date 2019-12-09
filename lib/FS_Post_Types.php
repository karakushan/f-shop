<?php

namespace FS;
/**
 *
 */
class FS_Post_Types {

	function __construct() {
		add_action( 'init', array( &$this, 'init' ) );

	}

	public function init() {
		$this->create_types();
	}

	public function create_types() {
		foreach ( $this->register_custom_post_types() as $name => $type ) {
			$this->create_post_type( $name, $type );
		}
	}

	public function create_post_type( $name, $type ) {
		$default = array(
			'labels'              => array(
				'name'          => $name,
				'singular_name' => $name,
				'add_new'       => 'Добавить ' . $name,
				'add_new_item'  => 'Добавить ' . $name,
				'edit_item'     => 'Изменить ' . $name,
			),
			'public'              => true,
			'show_in_menu'        => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'show_in_nav_menus'   => true,
			'menu_position'       => null,
			'can_export'          => true,
			'has_archive'         => true,
			'rewrite'             => true,
			'query_var'           => true,
			'show_in_rest'        => true,
		);

		$new_cpt_settings = array_merge( $default, $type );

		register_post_type( $name, $new_cpt_settings );
	}

	/**
	 * This method returns the additional registered record types
	 *
	 * @return mixed|void
	 */
	public function register_custom_post_types() {
		$types = array(
			'reviews' => array(
				'name'                => 'Отзывы',
				'singular_name'       => 'отзыв',
				'menu_icon'           => 'dashicons-thumbs-up',
				'exclude_from_search' => true,
				'taxonomies'          => array(),
				'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'gutenburg' ),

			),
			'reviews' => array(
				'labels'             => array(
					'name'               => __( 'Orders', 'f-shop' ),
					'singular_name'      => __( 'Order', 'f-shop' ),
					'add_new'            => __( 'Add Order', 'f-shop' ),
					'add_new_item'       => '',
					'edit_item'          => __( 'Edit order', 'f-shop' ),
					'new_item'           => '',
					'view_item'          => '',
					'search_items'       => '',
					'not_found'          => '',
					'not_found_in_trash' => '',
					'parent_item_colon'  => '',
					'menu_name'          => __( 'Orders', 'f-shop' ),
				),
				'public'             => true,
				'show_in_menu'       => true,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'capability_type'    => 'post',
				'menu_icon'          => 'dashicons-list-view',
				'map_meta_cap'       => true,
				'show_in_nav_menus'  => false,
				'menu_position'      => 6,
				'can_export'         => true,
				'has_archive'        => true,
				'rewrite'            => true,
				'query_var'          => true,
				'description'        => __( "Orders from your site are placed here.", 'f-shop' ),
				'supports'           => array(
					'title'
				)
			)
		);

		return apply_filters( 'fs_register_custom_post_types', $types );
	}


}