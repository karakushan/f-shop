<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class FS_Post_Type {
	const POST_TYPE = "product";
	protected $config;
	public $custom_tab_title;
	public $custom_tab_body;
	public $tabs;
	public $product_id;

	/**
	 * The Constructor
	 */
	public function __construct() {

		// register actions
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'save_post', array( $this, 'save_fs_fields' ) );
		$this->product_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;

		$this->config = new FS_Config();
	} // END public function __construct()

	/**
	 * hook into WP's init action hook
	 */
	public function init() {
		// Initialize Post Type
		$this->create_post_type();

	} // END public function init()

	/**
	 * Create the post type
	 */
	public function create_post_type() {
		/* регистрируем тип постов  - товары */
		register_post_type( self::POST_TYPE,
			array(
				'labels'             => array(
					'name'               => __( 'Products', 'fast-shop' ),
					'singular_name'      => __( 'product', 'fast-shop' ),
					'add_new'            => __( 'Add product', 'fast-shop' ),
					'add_new_item'       => '',
					'edit_item'          => __( 'Edit product', 'fast-shop' ),
					'new_item'           => '',
					'view_item'          => '',
					'search_items'       => '',
					'not_found'          => '',
					'not_found_in_trash' => '',
					'parent_item_colon'  => '',
					'menu_name'          => __( 'Products', 'fast-shop' ),
				),
				'public'             => true,
				'show_in_menu'       => true,
				'yarpp_support'      => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'capability_type'    => 'post',
				'menu_icon'          => 'dashicons-cart',
				'map_meta_cap'       => true,
				'show_in_nav_menus'  => true,
				'menu_position'      => 5,
				'can_export'         => true,
				'has_archive'        => true,
				'rewrite'            => true,
				'query_var'          => true,
				'taxonomies'         => array( 'catalog', 'manufacturer', 'countries' ),
				'description'        => __( "Здесь размещены товары вашего сайта." ),

				'supports' => array(
					'title',
					'editor',
					'excerpt',
					'thumbnail',
					'comments'
				)
			)
		);

		/* Регистрируем тип постов - заказы */
		register_post_type( 'order',
			array(
				'labels'             => array(
					'name'               => __( 'Orders', 'fast-shop' ),
					'singular_name'      => __( 'Order', 'fast-shop' ),
					'add_new'            => __( 'Add Order', 'fast-shop' ),
					'add_new_item'       => '',
					'edit_item'          => __( 'Edit order', 'fast-shop' ),
					'new_item'           => '',
					'view_item'          => '',
					'search_items'       => '',
					'not_found'          => '',
					'not_found_in_trash' => '',
					'parent_item_colon'  => '',
					'menu_name'          => __( 'Orders', 'fast-shop' ),
				),
				'public'             => true,
				'show_in_menu'       => true,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'capability_type'    => 'post',
				'menu_icon'          => 'dashicons-list-view',
				'map_meta_cap'       => true,
				'show_in_nav_menus'  => true,
				'menu_position'      => 6,
				'can_export'         => true,
				'has_archive'        => true,
				'rewrite'            => true,
				'query_var'          => true,
				'taxonomies'         => array( 'order-statuses' ),
				'description'        => __( "Здесь размещены заказы с вашего сайта." ),

				'supports' => array(
					'title',
					'comments'
				)
			)
		);
	}

	/**
	 * Save the metaboxes for this custom post type
	 *
	 * @param $post_id
	 */
	public function save_fs_fields( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['post_type'] ) && $_POST['post_type'] == self::POST_TYPE && current_user_can( 'edit_post', $post_id ) ) {
			foreach ( @$this->config->meta as $field_name ) {
				if (!isset($_POST[ $field_name ])) {
					delete_post_meta( $post_id, $field_name );
					continue;
				}
				switch ( $field_name ) {
					case 'fs_price':
						$price = (float) str_replace( array( ',' ), array( '.' ), sanitize_text_field( $_POST[ $field_name ] ) );
						update_post_meta( $post_id, $field_name, $price );
						break;
					case 'fs_related_products':
						if ( empty( $field_value ) ) {
							delete_post_meta( $post_id, $field_name );
						} else {
							update_post_meta( $post_id, $field_name, sanitize_text_field( $_POST[ $field_name ] ) );
						}
						break;
					default:
						if ( is_array( $_POST[ $field_name ] ) ) {

							$field_sanitize = array_map( 'sanitize_text_field', $_POST[ $field_name ] );
							$field_sanitize = array_unique( $field_sanitize );
							update_post_meta( $post_id, $field_name, $field_sanitize );
						} else {
							update_post_meta( $post_id, $field_name, sanitize_text_field( $_POST[ $field_name ] ) );
						}
						break;
				}


			}
		}
		// if($_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
	} // END public function save_post($post_id)

	/**
	 * hook into WP's admin_init action hook
	 */
	public function admin_init() {
		// Add metaboxes
		add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
	} // END public function admin_init()

	/**
	 * hook into WP's add_meta_boxes action hook
	 */
	public function add_meta_boxes() {
		// Add this metabox to every selected post
		add_meta_box(
			sprintf( 'fast_shop_%s_metabox', self::POST_TYPE ),
			__( 'Product settings', 'fast-shop' ),
			array( &$this, 'add_inner_meta_boxes' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		// Add this metabox to every selected post

	} // END public function add_meta_boxes()

	/**
	 * called off of the add meta box
	 *
	 * @param $post
	 */
	public function add_inner_meta_boxes( $post ) {
		$this->product_id = $post->ID;
		$cookie           = isset( $_COOKIE['fs_active_tab'] ) ? $_COOKIE['fs_active_tab'] : null;
		echo '<div class="fs-metabox" id="fs-metabox">';

		if ( ! empty( $this->config->tabs ) && is_array( $this->config->tabs ) ) {
			echo '<ul>';
			foreach ( $this->config->tabs as $key => $tab ) {
				if ( ! $tab['on'] ) {
					continue;
				}
				if ( $cookie ) {
					if ( $cookie == $key ) {
						$class = 'class="fs-link-active"';
					} else {
						$class = '';
					}
				} else {
					if ( $key == 0 ) {
						$class = 'class="fs-link-active"';
					} else {
						$class = '';
					}
				}
				echo '<li ' . $class . '><a href="#tab-' . $key . '" data-tab="' . $key . '">' . __( $tab['title'], 'fast-shop' ) . '</a></li>';
			}
			echo '</ul>';
			echo "<div class=\"fs-tabs\">";
			foreach ( $this->config->tabs as $key_body => $tab_body ) {
				if ( ! $tab_body['on'] ) {
					continue;
				}
				if ( $cookie ) {
					if ( $key_body == $cookie ) {
						$class_tab = 'fs-tab-active';
					} else {
						$class_tab = '';
					}
				} else {
					if ( $key_body == 0 ) {
						$class_tab = 'fs-tab-active';
					} else {
						$class_tab = '';
					}
				}

				$template_default = FS_PLUGIN_PATH . 'templates/back-end/metabox/tab-' . $key_body . '.php';
				$template_file    = empty( $tab_body['template'] ) ? $template_default : $tab_body['template'];

				echo '<div class="fs-tab ' . $class_tab . '" id="tab-' . $key_body . '">';
				if ( empty( $tab_body['body'] ) ) {
					if ( file_exists( $template_file ) ) {
						include( $template_file );
					}
				} else {
					echo $tab_body['body'];
				}
				echo '</div>';

			}
			echo "</div>";
			echo '<div class="clearfix"></div>';

		}
		echo '</div>';
	}


} // END class Post_Type_Template