<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class FS_Post_Type {
	protected $config;
	public $custom_tab_title;
	public $custom_tab_body;
	public $tabs;
	public $product_id;

	/**
	 * The Constructor
	 */
	public function __construct() {
		global $fs_config;
		// register actions
		add_action( 'init', array( $this, 'init' ), 12 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'save_post', array( $this, 'save_fs_fields' ) );
		add_action( 'fs_before_product_meta', array( $this, 'before_product_meta' ) );
		$this->product_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
		$this->config     = new FS_Config();
	} // END public function __construct()


	/**
	 * hook into WP's init action hook
	 */
	public function init() {
		// Initialize Post Type
		$this->create_post_type();


	} // END public function init()

	// Выводит скрытый прелоадер перед полями метабокса
	function before_product_meta() {
		echo '<div class="fs-mb-preloader"></div>';
	}


	/**
	 * Create the post type
	 */
	public function create_post_type() {
		global $fs_config;
		/* регистрируем тип постов  - товары */
		register_post_type( $fs_config->data['post_type'],
			array(
				'labels'             => array(
					'name'               => __( 'Products', 'f-shop' ),
					'singular_name'      => __( 'Product', 'f-shop' ),
					'add_new'            => __( 'Add product', 'f-shop' ),
					'add_new_item'       => '',
					'edit_item'          => __( 'Edit product', 'f-shop' ),
					'new_item'           => '',
					'view_item'          => '',
					'search_items'       => '',
					'not_found'          => '',
					'not_found_in_trash' => '',
					'parent_item_colon'  => '',
					'menu_name'          => __( 'Products', 'f-shop' ),
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
				'show_in_rest'       => true,
				'menu_position'      => 5,
				'can_export'         => true,
				'has_archive'        => true,
				'rewrite'            => apply_filters( 'fs_product_slug', true ),
				'query_var'          => true,
				'taxonomies'         => array( 'catalog', 'product-attributes' ),
				'description'        => __( "Здесь размещены товары вашего сайта." ),

				'supports' => array(
					'title',
					'editor',
					'excerpt',
					'thumbnail',
					'comments',
					'page-attributes',
					'revisions',
					'gutenburg'
				)
			)
		);

		/* Регистрируем тип постов - заказы */
		register_post_type( 'orders',
			array(
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
				'description'        => __( "Здесь размещены заказы с вашего сайта." ),
				'supports'           => array(
					'title'

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
		global $fs_config, $wpdb;
		$save_meta = $this->meta_save_fields();

		if ( isset( $_POST['post_type'] ) && $_POST['post_type'] == $fs_config->data['post_type'] && current_user_can( 'edit_post', $post_id ) ) {

			// ставим позицию 99999, то есть в самом конце для постов с позицией 0 или меньше
			$posts = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE menu_order<=0 AND post_type='product'" );
			if ( $posts ) {
				foreach ( $posts as $post ) {
					$wpdb->update( $wpdb->posts, array( 'menu_order' => 99999 ), array( 'ID' => $post->ID ) );
				}
			}

			if ( is_array( $save_meta ) && count( $save_meta ) ) {
				foreach ( $save_meta as $key => $field_name ) {
					if ( ! is_array( $_POST[ $field_name ] ) && ! isset( $_POST[ $field_name ] ) || (string) $_POST[ $field_name ] == '' ) {
						delete_post_meta( $post_id, $field_name );
						continue;
					}
					if ( is_array( $_POST[ $field_name ] ) && empty( $_POST[ $field_name ] ) ) {
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
								update_post_meta( $post_id, $field_name, $_POST[ $field_name ] );
							} else {
								update_post_meta( $post_id, $field_name, $_POST[ $field_name ] );
							}
							break;
					}


				}
			}
		}
	} // END public function save_post($post_id)

	/**
	 * hook into WP's admin_init action hook
	 */
	public function admin_init() {
		// Add metaboxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	} // END public function admin_init()

	/**
	 * hook into WP's add_meta_boxes action hook
	 */
	public function add_meta_boxes() {
		global $fs_config;
		remove_meta_box( 'order-statusesdiv', 'orders', 'side' );
		// Add this metabox to every selected post
		add_meta_box(
			sprintf( 'fast_shop_%s_metabox', $fs_config->data['post_type'] ),
			__( 'Product settings', 'f-shop' ),
			array( &$this, 'add_inner_meta_boxes' ),
			$fs_config->data['post_type'],
			'normal',
			'high'
		);

		// добавляем метабокс списка товаров в заказе
		add_meta_box(
			sprintf( 'fast_shop_%s_metabox', 'orders' ),
			__( 'List of products', 'f-shop' ),
			array( &$this, 'add_order_products_meta_boxes' ),
			'orders',
			'normal',
			'high'
		);

		// добавляем метабокс списка товаров в заказе
		add_meta_box(
			sprintf( 'fast_shop_%s_user_metabox', 'orders' ),
			__( 'Customer data', 'f-shop' ),
			array( &$this, 'add_order_user_meta_boxes' ),
			'orders',
			'normal',
			'default'
		);
		// Add this metabox to every selected post

	} // END public function add_meta_boxes()


	/**
	 *Registers new metafields dynamically from tabs
	 *
	 * @return array
	 */
	function meta_save_fields() {
		global $fs_config;
		$product_tabs = $fs_config->get_product_tabs();
		$meta_fields  = $fs_config->meta;
		if ( ! empty( $product_tabs ) ) {
			foreach ( $product_tabs as $product_tab ) {
				if ( ! empty( $product_tab['fields'] ) ) {
					foreach ( $product_tab['fields'] as $key => $field ) {
						$meta_fields[ $key ] = $key;
					}
				}

			}
		}

		return $meta_fields;
	}

	/**
	 * called off of the add meta box
	 *
	 * @param $post
	 */
	public function add_inner_meta_boxes( $post ) {
		global $fs_config;
		$form_class       = new FS_Form_Class();
		$product_tabs     = $fs_config->get_product_tabs();
		$this->product_id = $post->ID;
		$cookie           = isset( $_COOKIE['fs_active_tab'] ) ? $_COOKIE['fs_active_tab'] : 'prices';
		echo '<div class="fs-metabox" id="fs-metabox">';
		do_action( 'fs_before_product_meta' );
		if ( count( $product_tabs ) ) {
			echo '<ul class="tab-header">';
			foreach ( $product_tabs as $key => $tab ) {
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
					$class = $key == 0 ? 'fs-link-active' : '';
				}
				echo '<li class="' . esc_attr( $class ) . '"><a href="#tab-' . esc_attr( $key ) . '" data-tab="' . esc_attr( $key ) . '">' . esc_html__( $tab['title'], 'f-shop' ) . '</a></li>';
			}
			echo '</ul>';
			echo "<div class=\"fs-tabs\">";
			foreach ( $product_tabs as $key_body => $tab_body ) {
				if ( ! $tab_body['on'] ) {
					continue;
				}

				if ( $key_body == $cookie ) {
					$class_tab = 'fs-tab-active';
				} else {
					$class_tab = '';
				}


				echo '<div class="fs-tab ' . esc_attr( $class_tab ) . '" id="tab-' . esc_attr( $key_body ) . '">';
				if ( ! empty( $tab_body['fields'] ) ) {
					if ( ! empty( $tab_body['title'] ) ) {
						echo '<h3>' . esc_html( $tab_body['title'] ) . '</h3>';
					}
					if ( ! empty( $tab_body['description'] ) ) {
						echo '<p class="description">' . esc_html( $tab_body['description'] ) . '</p>';
					}
					foreach ( $tab_body['fields'] as $key => $field ) {
						$filter_meta[ $key ] = $key;
					}

					foreach ( $tab_body['fields'] as $key => $field ) {
						// если у поля есть атрибут "on" и он выключён то выходим из цикла
						if ( isset( $field['on'] ) && $field['on'] != true ) {
							continue;
						}
						// если не указан атрибут type
						if ( empty( $field['type'] ) ) {
							$field['type'] = 'text';
						}
						echo '<div class="fs-field-row clearfix">';
						$field['value'] = get_post_meta( $post->ID, $key, true );
						$form_class->render_field( $key, $field['type'], $field );
						echo '</div>';
					}
				} elseif ( ! empty( $tab_body['template'] ) ) {
					$template_file = sprintf( FS_PLUGIN_PATH . 'templates/back-end/metabox/%s.php', $tab_body['template'] );
					if ( file_exists( $template_file ) ) {
						include( $template_file );
					} else {
						esc_html_e( 'Template file not found', 'f-shop' );
					}
				} elseif ( ! empty( $tab_body['body'] ) ) {
					echo $tab_body['body'];
				}
				echo '</div>';

			}
			echo "</div>";
			echo '<div class="clearfix"></div>';

		}
		echo '</div>';
	}

	/* метабокс списка товаров в редактировании заказа */
	public function add_order_products_meta_boxes( $post ) {
		$products = get_post_meta( $post->ID, '_products', 0 );
		$products = ! empty( $products[0] ) ? $products[0] : [];
		$amount   = get_post_meta( $post->ID, '_amount', 1 );
		$amount   = apply_filters( 'fs_price_format', $amount ) . ' ' . fs_currency();
		require FS_PLUGIN_PATH . 'templates/back-end/metabox/order/meta-box-0.php';
	}

	/* метабокс данных пользователя в редактировании заказа */
	public function add_order_user_meta_boxes( $post ) {
		$user     = get_post_meta( $post->ID, '_user', 0 );
		$user     = $user[0];
		$payment  = get_post_meta( $post->ID, '_payment', 1 );
		$delivery = get_post_meta( $post->ID, '_delivery', 0 );
		$delivery = $delivery[0];

		require FS_PLUGIN_PATH . 'templates/back-end/metabox/order/meta-box-1.php';
	}
} // END class Post_Type_Template