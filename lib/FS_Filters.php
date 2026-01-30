<?php

namespace FS;
/**
 * Class FS_Filters
 *
 * Handles various filtering, hooks, and actions for managing products.
 */
class FS_Filters {
	/**
	 * Defines the separator used for parameter delimitation.
	 */
	private static $param_separator = ',';

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
	 * Retrieves the parameter separator used in filters and applies the 'fs_filters_param_separator' filter.
	 *
	 * @return string The parameter separator after applying any filters.
	 */
	public static function get_param_separator() {
		$separator = apply_filters( 'fs_filters_param_separator', self::$param_separator );
		if ( ! is_string( $separator ) ) {
			$separator = ',';
		}

		return $separator;
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
						<span
                                class="title"><?php esc_html_e( 'Price', 'f-shop' ) ?> (<?php echo fs_currency(); ?>)</span>
                        <span class="input-text-wrap">
                        <input type="number" name="<?php echo esc_attr( FS_Config::get_meta( 'price' ) ) ?>"
                               class="fs_price"
                               value=""
                               required step="0.01" min="0">
                    </span>
                    </label>
                    <label>
                        <span class="title"><?php esc_html_e( 'Vendor code', 'f-shop' ) ?></span>
                        <span class="input-text-wrap">
                        <input type="text" name="<?php echo esc_attr( FS_Config::get_meta( 'sku' ) ) ?>"
                               class="fs_vendor_code"
                               value="">
                    </span>
                    </label>
                    <label>
            <span
                    class="title"><?php esc_html_e( 'Stock in stock', 'f-shop' ) ?> (<?php esc_html_e( 'units', 'f-shop' ) ?>
                )</span>
                        <span class="input-text-wrap">
                        <input type="number" name="<?php echo esc_attr( FS_Config::get_meta( 'remaining_amount' ) ) ?>"
                               class="fs_stock" min="0" step="1"
                               value=""></span>
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
		global $pagenow, $wpdb;

		// Если это не админка
		if ( ! is_admin() ) {
			return;
		}

		if ( $query->get( 'post_type' ) != FS_Config::get_data( 'post_type' ) ) {
			return;
		}

		if ( $pagenow != 'edit.php' ) {
			return;
		}

		// Поиск товаров по артикулу
		if ( ! empty( $_GET['s'] ) ) {
			// Search by sku
			$sku_products = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='%s' AND meta_value='%s'", FS_Config::get_meta( 'sku' ), esc_sql( $_GET['s'] ) ) );
			if ( ! empty( $sku_products ) ) {
				$query->set( 's', '' );
				$query->set( 'post__in', $sku_products );
			}
		}

		// Сортировка найденных результатов
		if ( ! empty( $_GET['orderby'] ) ) {
			switch ( $_GET['orderby'] ) {
				//	сортируем по цене
				case "fs_price":
					$query->set( 'orderby', 'meta_value_num' );
					$query->set( 'meta_key', FS_Config::get_meta( 'price' ) );
					$query->set( 'order', (string) $_GET['order'] );
					break;
			}

		}
		$query->set( 'post_type', 'product' );
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
		$req = isset( $_GET['per_page'] ) ? intval( $_GET['per_page'] ) : get_option( "posts_per_page" );

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
	 * @return void              выводит html элемент типа select
	 */
	public static function fs_types_sort_filter( $attr = array() ) {
		$attr = wp_parse_args( $attr, array(
			'class' => 'fs-types-sort-filter'
		) );

		$sorting_types = apply_filters( 'fs_catalog_sorting_criteria', [
			'none'           => [
				'name' => __( 'By default', 'f-shop' ) // по умолчанию
			],
			'menu_order'     => [
				'name' => __( 'By sorting field', 'f-shop' ) // по полю сортировки
			],
			'date_desc'      => [
				'name' => __( 'Recently added', 'f-shop' ) // недавно добавленные
			],
			'date_asc'       => [
				'name' => __( 'Later added', 'f-shop' ) // давно добавленные
			],
			'price_asc'      => [
				'name' => __( 'From cheap to expensive', 'f-shop' ) // от дешевых к дорогим
			],
			'price_desc'     => [
				'name' => __( 'From expensive to cheap', 'f-shop' ) // от дорогих к дешевым
			],
			'name_asc'       => [
				'name' => __( 'By title A to Z', 'f-shop' ) // по названию от А до Я
			],
			'name_desc'      => [
				'name' => __( 'By title Z to A', 'f-shop' ) // по названию от Я до А
			],
			'views_desc'     => [
				'name' => __( 'By popularity', 'f-shop' ) // по популярности
			],
			'action_price'   => [
				'name' => __( 'First promotional', 'f-shop' ) // акционные
			],
			'rating_desc'    => [
				'name' => __( 'By rating', 'f-shop' ) // по рейтингу
			],
			'stock_desc'     => [
				'name' => __( 'In stock', 'f-shop' ) // в наличии
			],
			'stock_priority' => [
				'name' => __( 'By availability priority', 'f-shop' ) // по приоритету наличия
			]
		] );

		if ( empty( $sorting_types ) ) {
			return;
		}

		$order_type_get = ! empty( $_GET['order_type'] ) ? $_GET['order_type'] : fs_option( 'fs_product_sort_by' );
		if ( is_array( $order_type_get ) ) {
			$order_type_get = 'date_desc';
		}

		echo ' <select name="order_type"  class="' . esc_attr( $attr['class'] ) . '" data-fs-action="filter"> ';
		foreach ( $sorting_types as $key => $order_type ) {
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

	/**
	 * Retrieves the active filters applied to a product catalog or listing.
	 * The filters include categories, price range, and attributes, if specified in the request parameters.
	 *
	 * @return array An array of active filters, where each filter is represented as an associative array containing:
	 *               - 'name': The name of the filter.
	 *               - 'value': The value associated with the filter item.
	 *               - 'type': The type of the filter ('categories', 'price', 'attributes').
	 *               - 'reset_link': A URL to remove the specific filter from the current context.
	 */
	public static function get_used_filters() {
		$filters    = [];
		$url_params = filter_var( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ), FILTER_SANITIZE_STRING );
		if ( ! is_string( $url_params ) ) {
			return [];
		}
		parse_str( $url_params, $url_params_array );
		$current_url = site_url( $_SERVER['REQUEST_URI'] );

		$attributes_ids = ! empty( $url_params_array['filter'] ) ? array_map( 'intval', explode( ',', $url_params_array['filter'] ) ) : [];
		$attributes     = ! empty( $attributes_ids ) ? get_terms( array(
			'taxonomy'   => FS_Config::get_data( 'features_taxonomy' ),
			'hide_empty' => false,
			'include'    => $attributes_ids
		) ) : [];


		// фильтры по категориям
		if ( ! empty( $url_params_array['categories'] ) ) {
			$categories_ids = array_map( 'intval', explode( self::$param_separator, $url_params_array['categories'] ) );

			$categories = get_terms( array(
				'taxonomy'   => FS_Config::get_data( 'product_taxonomy' ),
				'hide_empty' => false,
				'include'    => $categories_ids
			) );

			if ( ! empty( $categories ) ) {
				foreach ( $categories as $category ) {
					$reset_link = add_query_arg( [ 'categories' => implode( self::$param_separator, array_diff( $categories_ids, [ $category->term_id ] ) ) ], $current_url );
					$filters[]  = [
						'name'       => $category->name,
						'value'      => $category->term_id,
						'type'       => 'categories',
						'reset_link' => $reset_link
					];
				}
			}
		}

		// фильтры по цене
		if ( ! empty( $url_params_array['price_start'] ) && ! empty( $url_params_array['price_end'] ) ) {
			$filters[] = [
				'name'       => sprintf( __( 'Price: %s-%s %s', 'f-shop' ), $url_params_array['price_start'], $url_params_array['price_end'], fs_currency() ),
				'value'      => $url_params_array['price_start'] . '-' . $url_params_array['price_end'],
				'type'       => 'price',
				'reset_link' => remove_query_arg( 'price_start', remove_query_arg( 'price_end', $current_url ) )
			];
		}

		// фильтры по атрибутам
		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $key => $attribute ) {
				$reset_link = add_query_arg( [ 'filter' => implode( array_diff( $attributes_ids, [ $attribute->term_id ] ) ) ], $current_url );
				$filters[]  = [
					'name'       => $attribute->name,
					'value'      => $attribute->term_id,
					'type'       => 'attributes',
					'reset_link' => $reset_link
				];

			}
		}

		// фильтр по брендам
		if ( ! empty( $url_params_array['brands'] ) ) {
			$brand_ids = array_map( 'intval', explode( self::$param_separator, $url_params_array['brands'] ) );

			$brands = get_terms( array(
				'taxonomy'   => FS_Config::get_data( 'brand_taxonomy' ),
				// Replace 'brand_taxonomy' with the actual taxonomy for brands
				'hide_empty' => false,
				'include'    => $brand_ids
			) );

			if ( ! empty( $brands ) ) {
				foreach ( $brands as $brand ) {
					$reset_link = add_query_arg( [ 'brands' => implode( self::$param_separator, array_diff( $brand_ids, [ $brand->term_id ] ) ) ], $current_url );
					$filters[]  = [
						'name'       => $brand->name,
						'value'      => $brand->term_id,
						'type'       => 'brands',
						'reset_link' => $reset_link
					];
				}
			}
		}

		return $filters;
	}
}