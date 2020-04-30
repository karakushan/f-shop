<?php

namespace FS;
/**
 * Class FS_Filters
 * @package FS
 */
class FS_Filters {
	function __construct() {


		// Backend product filtering
		add_action( 'pre_get_posts', array( $this, 'filter_products_admin' ), 10, 1 );

		// Filter by product categories in the admin panel
		add_action( 'restrict_manage_posts', array( $this, 'category_filter_admin' ) );

		// Setting the quantity of goods on the page of the archive of goods
		add_action( 'template_redirect', array( $this, 'redirect_per_page' ) );

		// Admin redirects
		add_action( 'admin_init', array( $this, 'redirects_admin_pages' ) );

		// Display the product edit fields in the quick editing mode
		add_action( 'quick_edit_custom_box', array( $this, 'product_quick_edit_fields' ), 10, 2 );

		// Modify the saved product field
		add_filter( 'fs_filter_meta_field', array( $this, 'fs_filter_meta_field' ), 10, 3 );

		// Here are the hooks for changing the name and mail of the sender
		add_filter( 'wp_mail_from', array( $this, 'sender_email' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'sender_name' ) );

		/**
		 * Transliteration of text for use in the slug
		 */
		add_filter( 'fs_filter_meta_field', array( $this, 'transliteration_product_slug' ), 10, 3 );

	}

	/**
	 * Transliteration of text for use in the slug
	 *
	 * @param $title
	 * @param $field_name
	 * @param $post
	 *
	 * @return mixed|string
	 */
	function transliteration_product_slug( $title, $field_name, $post ) {

		if ( strpos( $field_name, 'fs_seo_slug' ) === false ) {
			return $title;
		}

		global $wpdb;

		if ( empty( $title ) && defined( 'WPGLOBUS_VERSION' ) && ! empty( $_REQUEST['wpglobus_language'] ) ) {
			$title = \WPGlobus_Core::extract_text( $post->post_title, $_REQUEST['wpglobus_language'] );
		} elseif ( empty( $title ) && isset( $post->post_title ) ) {
			$title = apply_filters( 'the_title', $post->post_title );
		}

		$slug = fs_transliteration( $title );

		// Looking for a similar slug in the database, if found, add a suffix
		$query                 = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key='%s' AND meta_value='%s' AND post_id!=%d ", $field_name, $slug, $post->ID );
		$slug_duplicates_count = $wpdb->get_var( $query );
		if ( $slug_duplicates_count > 0 ) {
			$slug = $slug . '-' . $post->ID;
		}

		return $slug;
	}

	function sender_email( $original_email_address ) {
		return fs_option( 'email_sender', $original_email_address );
	}

	function sender_name( $original_email_from ) {
		return fs_option( 'name_sender', $original_email_from );
	}

	/**
	 * Modify the saved product field
	 *
	 * @param $value
	 * @param $field_name
	 * @param $post
	 *
	 * @return float
	 */
	function fs_filter_meta_field( $value, $field_name, $post ) {
		if ( $field_name == FS_Config::get_meta( 'price' ) ) {
			$value = floatval( str_replace( array( ',' ), array( '.' ), sanitize_text_field( $value ) ) );
		}

		return $value;
	}

	/**
	 * Display the product edit fields in the quick editing mode
	 *
	 * @param $column_name
	 * @param $post_type
	 */
	function product_quick_edit_fields( $column_name, $post_type ) {

		if ( $column_name == 'fs_price' && $post_type == 'product' ) {
			?>
            <fieldset class="inline-edit-col-left inline-edit-fast-shop">
                <legend class="inline-edit-legend"><?php esc_html_e( 'Product Settings', 'f-shop' ) ?> </legend>
                <div class="inline-edit-col">
                    <label>
                        <span class="title"><?php esc_html_e( 'Price', 'f-shop' ) ?> (<?php echo fs_currency(); ?>)</span>
                        <span class="input-text-wrap">
                        <input type="number" name="<?php echo esc_attr( FS_Config::get_meta( 'price' ) ) ?>"
                               class="fs_price"
                               value="<?php echo esc_attr( get_post_meta( get_the_ID(), FS_Config::get_meta( 'price' ), 1 ) ) ?>"
                               required step="0.01" min="0">
                    </span>
                    </label>
                    <label>
                        <span class="title"><?php esc_html_e( 'Vendor code', 'f-shop' ) ?></span>
                        <span class="input-text-wrap">
                        <input type="text" name="<?php echo esc_attr( FS_Config::get_meta( 'sku' ) ) ?>"
                               class="fs_vendor_code"
                               value="<?php echo esc_attr( get_post_meta( get_the_ID(), FS_Config::get_meta( 'sku' ), 1 ) ) ?>">
                    </span>
                    </label>
                    <label>
            <span class="title"><?php esc_html_e( 'Stock in stock', 'f-shop' ) ?> (<?php esc_html_e( 'units', 'f-shop' ) ?>
                )</span>
                        <span class="input-text-wrap">
                        <input type="number" name="<?php echo esc_attr( FS_Config::get_meta( 'remaining_amount' ) ) ?>"
                               class="fs_stock" min="0" step="1"
                               value="<?php echo esc_attr( get_post_meta( get_the_ID(), FS_Config::get_meta( 'remaining_amount' ), 1 ) ) ?>"></span>
                    </label>
                </div>
            </fieldset>
			<?php
		}
	}

	// редиректы для админки
	function redirects_admin_pages() {
		global $pagenow;
		// этот хак
		if ( $pagenow == 'edit.php' && ! empty( $_GET['s'] ) && isset( $_GET['action'] ) ) {
			wp_redirect( remove_query_arg( [ 'action' ] ) );
			exit;
		}
	}

	/**
	 *  Фильтрация товаров на бэкенде
	 *
	 * @param $query
	 */
	function filter_products_admin( $query ) {
		if ( ! is_admin() ) {
			return;
		}
		global $pagenow, $wpdb;
		$fs_config = new FS_Config();

		$post_type_product = $query->get( 'post_type' ) == $fs_config->data['post_type'] ? true : false;

		if ( ! empty( $_GET['s'] ) && $post_type_product && $pagenow == 'edit.php' ) {
			// Search by sku
			$sku_products = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='%s' AND meta_value='%s'", $fs_config->meta['sku'], esc_sql( $_GET['s'] ) ) );
			if ( ! empty( $sku_products ) ) {
				$query->set( 's', '' );
				$query->set( 'post__in', $sku_products );
			}


			$query->set( 'post_type', 'product' );
		}
		if ( ! empty( $_GET['orderby'] ) && $post_type_product && $pagenow == 'edit.php' ) {
			switch ( $_GET['orderby'] ) {
				//	сортируем по цене
				case "fs_price":
					$query->set( 'orderby', 'meta_value_num' );
					$query->set( 'meta_key', $fs_config->meta['price'] );
					$query->set( 'order', (string) $_GET['order'] );
					break;
			}

		}
		$query->query['action']      = '';
		$query->query_vars['action'] = '';
	}


	public function redirect_per_page() {
		if ( isset( $_GET['paged'] ) ) {
			wp_redirect( remove_query_arg( 'paged' ) );
			exit();
		}
	}


	/**
	 * фильтр по категориям товаров в админке
	 */
	function category_filter_admin() {
		global $typenow;
		$get_parr = isset( $_GET['catalog'] ) ? $_GET['catalog'] : '';
		if ( $typenow != 'product' ) {
			return;
		}

		wp_dropdown_categories( array(
			'show_option_all' => __( 'Product category', 'f-shop' ),
			'taxonomy'        => 'catalog',
			'id'              => 'fs-category-filter',
			'hide_empty'      => false,
			'value_field'     => 'slug',
			'hierarchical'    => true,
			'selected'        => $get_parr,
			'name'            => 'catalog'
		) );
	}





	/**
	 * @param $group
	 * @param string $type
	 * @param string $option_default
	 */
	public function attr_group_filter( $group, $type = 'option', $option_default = 'Выберите значение' ) {
		//		получаем группу атрибутов
		$fs_atributes = get_terms( array(
			'parent'     => $group,
			'taxonomy'   => 'product-attributes',
			'hide_empty' => false
		) );

		$arr_url = urldecode( $_SERVER['QUERY_STRING'] );
		parse_str( $arr_url, $url );

		if ( $type == 'option' ) {
			echo ' <select name="attributes" data-fs-action="filter"><option value="' . esc_attr( remove_query_arg( array( 'attributes' ) ) ) . '"> ' . esc_html( $option_default ) . '</option> ';
			foreach ( $fs_atributes as $key => $att ) {
				$redirect_url = add_query_arg( array(
					'fs_filter'  => wp_create_nonce( 'f-shop' ),
					'attributes' => array( $att->slug => $att->term_id )
				) );
				echo '<option  value="' . esc_url( $redirect_url ) . '" ' . selected( $url['attributes'][ $att->slug ], $att->term_id, 0 ) . '> ' . esc_html( $att->name ) . '</option> ';
			}
			echo '</select> ';
		}
		if ( $type == 'list' ) {
			echo ' <ul>';
			foreach ( $fs_atributes as $key => $att ) {
				$redirect_url = add_query_arg( array(
					'fs_filter'  => wp_create_nonce( 'f-shop' ),
					'attributes' => array( $att->slug => $att->term_id )
				) );

				echo ' <li><a href="' . esc_url( $redirect_url ) . '" data-fs-action="filter"> ' . esc_html( $att->name ) . '</a></li> ';
			}
			echo '</ul> ';
		}
	}//end attr_group_filter()

	/**
	 * метод позволяет вывести поле типа select  для изменения к-ва выводимых постов на странице
	 *
	 * @param array $args дополнительные настройки
	 *
	 * @echo  string;
	 */
	public static function per_page_filter( $args ) {
		$req   = isset( $_REQUEST['per_page'] ) ? $_REQUEST['per_page'] : get_option( "posts_per_page" );
		$args  = wp_parse_args( $args,
			array(
				'interval' => array( 15, 30, 45, 90 ),
				'class'    => 'fs-count-filter'
			) );
		$nonce = wp_create_nonce( 'f-shop' );

		echo ' <select name="post_count" class="' . esc_attr( $args['class'] ) . '" onchange="document.location=this.options[this.selectedIndex].value"> ';
		foreach ( $args['interval'] as $key => $count ) {
			$redirect_url = add_query_arg( array(
				"fs_filter" => $nonce,
				"per_page"  => $count,
				'paged'     => 1
			) );
			echo ' <option value="' . esc_attr( $redirect_url ) . '" ' . selected( $count, $req, false ) . '> ' . esc_html( $count ) . '</option> ';
		}
		echo '</select> ';
	}

	/**
	 * выводит фильтр сортировки на странице архива по разным параметрам
	 *
	 * @param array $attr дополниетльные атрибуты html тега
	 *
	 * @return string              выводит html элемент типа select
	 */
	public static function fs_types_sort_filter( $attr = array() ) {
		$attr = wp_parse_args( $attr, array(
			'class'   => 'fs-types-sort-filter',
			'filters' => array(
				'date_desc'  => array(
					'name' => __( 'recently added', 'f-shop' )// недавно добавленные
				),
				'date_asc'   => array(
					'name' => __( 'later added', 'f-shop' ) // давно добавленные
				),
				'price_asc'  => array(
					'name' => __( 'from cheap to expensive', 'f-shop' ) // от дешевых к дорогим
				),
				'price_desc' => array(
					'name' => __( 'from expensive to cheap', 'f-shop' ) // от дорогих к дешевым
				),
				'name_asc'   => array(
					'name' => __( 'by title A to Z', 'f-shop' ) // по названию от А до Я
				),
				'name_desc'  => array(
					'name' => __( 'by title Z to A', 'f-shop' ) // по названию от Я до А
				)
			)
		) );

		$order_type_get = ! empty( $_GET['order_type'] ) ? $_GET['order_type'] : fs_option( 'fs_product_sort_by' );
		if ( count( $attr['filters'] ) ) {
			echo ' <select name="order_type"  class="' . esc_attr( $attr['class'] ) . '" data-fs-action="filter"> ';
			foreach ( $attr['filters'] as $key => $order_type ) {
				if ( $key == 'default' ) {
					if ( is_page() ) {
						$redirect_url = get_the_permalink( get_the_ID() );
					} elseif ( is_tax() ) {
						$redirect_url = get_term_link( get_queried_object_id() );
					} elseif ( is_archive( FS_Config::get_data( 'post_type' ) ) ) {
						$redirect_url = get_post_type_archive_link( FS_Config::get_data( 'post_type' ) );
					} else {
						$redirect_url = $_SERVER['REQUEST_URI'];
					}

				} else {
					$redirect_url = add_query_arg( array(
						'fs_filter'  => wp_create_nonce( 'f-shop' ),
						'order_type' => $key
					) );
				}

				echo ' <option value="' . esc_url( $redirect_url ) . '" ' . selected( $key, $order_type_get, 0 ) . '> ' . esc_html( $order_type['name'] ) . ' </option> ';
			}
			echo '</select> ';
		}

		return;
	}

}