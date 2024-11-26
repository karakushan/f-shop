<?php
/**
 * Class FS_Taxonomies_Class
 *
 * This class is responsible for registering its own taxonomies.
 * category of goods
 * payment methods
 * delivery methods,
 * characteristics of the product,
 * brands,
 * taxes
 * discounts
 * currencies
 *
 * @package FS
 */

namespace FS;

class FS_Taxonomy {
	public $taxonomy_name;


	function __construct() {
		$this->taxonomy_name = FS_Config::get_data( 'product_taxonomy' );

		add_action( 'carbon_fields_loaded', array( $this, 'create_taxonomy' ) );
		add_filter( 'manage_fs-currencies_custom_column', array( $this, 'currencies_column_content' ), 10, 3 );
		add_filter( 'manage_fs-currencies_custom_column', array( $this, 'currencies_column_content' ), 10, 3 );
		add_filter( 'manage_edit-fs-currencies_columns', array( $this, 'add_fs_currencies_columns' ) );
		add_filter( 'manage_edit-' . FS_Config::get_data( 'product_pay_taxonomy' ) . '_columns', [
			$this,
			'pay_taxonomy_columns'
		] );
		add_filter( 'manage_' . FS_Config::get_data( 'product_pay_taxonomy' ) . '_custom_column', array(
			$this,
			'pay_taxonomy_column_content'
		), 10, 3 );

		// Remove taxonomy slug from links
		add_filter( 'term_link', array( $this, 'replace_taxonomy_slug_filter' ), 10, 3 );

		add_action( 'init', function () {
			// Generate rewrite rules
			add_action( 'generate_rewrite_rules', array( $this, 'taxonomy_rewrite_rules' ) );
		} );

		//  redirect to localized url
		add_action( 'template_redirect', array( $this, 'redirect_to_localized_url' ) );

		// Filtering products on the category page and in the product archives
		add_action( 'pre_get_posts', array( $this, 'taxonomy_filter_products' ), 12, 1 );

		// Adding the ability to sort by availability
		if ( fs_option( 'fs_product_sort_by' ) == 'stock_desc' || ( isset( $_GET['order_type'] ) && $_GET['order_type'] == 'stock_desc' ) ) {
			add_filter( 'posts_clauses', array( $this, 'order_by_stock_status' ), 10, 2 );
		}

		add_action( 'fs_product_category_filter', [ $this, 'product_category_filter' ] );

		add_filter( 'posts_clauses', [ $this, 'fs_filter_by_price_clause' ], 10, 2 );
	}

	/**
	 * Фильтр по категориям
	 * Позволяет отфильтровать товары на странице архива или категории товара по принадлежности к категории
	 * На странице категории фильтрует по дочерним категориям
	 *
	 * @param int $parent id родительской категории
	 * @param array $args массив аргументов
	 */
	public function product_category_filter( $parent = 0, $args = [], $level = 0 ) {
		$args = wp_parse_args( $args, [
			'wrapper_class' => 'fs-category-filter'
		] );

		if ( ! $level ) {
			$parent = 0;
		}

		$level ++;

		$product_categories = get_terms( [
			'taxonomy'     => $this->taxonomy_name,
			'hide_empty'   => true,
			'parent'       => $parent,
			'hierarchical' => true,
		] );

		$current_tax = get_queried_object_id();

		echo '<ul class="' . esc_attr( $args['wrapper_class'] ) . '">';

		foreach ( $product_categories as $product_category ) {
			if ( ! is_object( $product_category ) ) {
				continue;
			}

			$category_icon = fs_get_category_icon( $product_category->term_id, 'full', [ 'default' => false ] );

			$parent_term_id = get_term_field( 'parent', $current_tax );

			$link_class = is_tax( 'catalog' ) && ( get_queried_object_id() == $product_category->term_id || $parent_term_id == $product_category->term_id ) ? 'active' : '';
			$href       = ! is_tax( $product_category->taxonomy, $product_category->term_id ) ? 'href="' . esc_url( get_term_link( $product_category ) ) . '"' : '';

			echo '<li class="level-' . esc_attr( $level ) . '">';
			echo '<a ' . $href . ' class="level-' . esc_attr( $level ) . '-link ' . esc_attr( $link_class ) . '">' . $category_icon . esc_html( $product_category->name ) . '</a>';
			$product_categories_child = get_terms( [
				'taxonomy'     => $this->taxonomy_name,
				'hide_empty'   => false,
				'parent'       => $product_category->term_id,
				'hierarchical' => true
			] );
			if ( $product_categories_child ) {

				$this->product_category_filter( $product_category->term_id, [], $level );
			}
			echo '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Добавляет сортировку по наличию
	 *
	 * @param $posts_clauses
	 *
	 * @return mixed
	 */
	public function order_by_stock_status( $posts_clauses, $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return $posts_clauses;
		}

		if ( ( $query->is_archive && isset( $query->query['post_type'] ) && $query->query['post_type'] == FS_Config::get_data( 'post_type' ) )
		     || ( $query->is_tax && isset( $query->query['catalog'] ) ) ) {

			global $wpdb;
			$posts_clauses['join']    .= " INNER JOIN $wpdb->postmeta postmeta ON ($wpdb->posts.ID = postmeta.post_id) ";
			$order_by                 = "if(postmeta.meta_value='0','0','1') DESC";
			$posts_clauses['orderby'] = $posts_clauses['orderby'] ? $posts_clauses['orderby'] . ", $order_by" : " $order_by";
			$posts_clauses['where']   = $posts_clauses['where'] . " AND postmeta.meta_key = 'fs_remaining_amount'";
		}

		return $posts_clauses;
	}

	/**
	 * Filtering products on the category page, in the product archives and in the search
	 *
	 * @param $query
	 */
	public static function taxonomy_filter_products( $query ) {
		global $wpdb;

		// Если это админка или не главный запрос
		if ( $query->is_admin || ! $query->is_main_query() ) {
			return;
		}

		// If we are on the search page
		if ( $query->is_search() ) {

			// Search by sku
			$search_query = str_replace( ' ', '%', get_search_query() );
			$sku_products = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='%s' AND meta_value LIKE '%s'", FS_Config::get_meta( 'sku' ), '%' . $search_query . '%' ) );

			if ( $sku_products ) {
				$query->set( 's', '' );
				$query->set( 'post__in', $sku_products );
			}
			$query->set( 's', $search_query );
			$query->set( 'post_type', FS_Config::get_data( 'post_type' ) );
			$query->set( 'post_name', '' );
			$query->set( 'post_status', 'publish' );
			// отфильтровываем выключенные для показа товары в админке
			/*$meta_query [] = [
				'relation' => 'AND',
				'exclude'  => [
					'relation' => 'OR',
					[
						'key'     => FS_Config::get_meta( 'exclude_archive' ),
						'compare' => 'NOT EXISTS'
					],
					[
						'key'     => FS_Config::get_meta( 'exclude_archive' ),
						'compare' => '!=',
						'value'   => "1"
					]
				]
			];*/
		}

		if ( $query->is_tax( FS_Config::get_data( 'product_taxonomy' ) )
		     || $query->is_post_type_archive( FS_Config::get_data( 'post_type' ) ) || is_search() ) {

			// Скрывать товары которых нет в наличии
			if ( fs_option( 'fs_not_aviable_hidden' ) ) {
				$meta_query [] = array(
					'key'     => FS_Config::get_meta( 'remaining_amount' ),
					'compare' => '!=',
					'value'   => "0"
				);
			}

			$order_by  = [];
			$tax_query = [];
			$arr_url   = urldecode( $_SERVER['QUERY_STRING'] );
			parse_str( $arr_url, $url );

			//Фильтруем по к-во выводимых постов на странице
			if ( isset( $url['per_page'] ) ) {
				$per_page                                 = $url['per_page'];
				$_SESSION['fs_user_settings']['per_page'] = $per_page;
			} else {
				$per_page = fs_option( 'fs_catalog_show_items', 30 );
			}

			//Устанавливаем страницу пагинации
			if ( isset( $url['paged'] ) ) {
				$query->set( 'paged', $url['paged'] );
			}

			// фильтр товаров по признакам
			if ( ! empty( $url['filter_by'] ) && $url['filter_by'] == 'action_price' ) {
				$meta_query['action_price'] = array(
					'key'     => FS_Config::get_meta( 'action_price' ),
					'compare' => '>',
					'value'   => 0
				);
				$order_by['action_price']   = 'DESC';
			}

			$url['order_type'] = ! empty( $url['order_type'] )
				? $url['order_type']
				: fs_option( 'fs_product_sort_by', [ 'ID' => 'DESC' ] );

			switch ( $url['order_type'] ) {

				//sort by price in ascending order
				case 'price_asc':
					$meta_query['price'] = array(
						'key'  => FS_Config::get_meta( 'price' ),
						'type' => 'DECIMAL'
					);
					$order_by['price']   = 'ASC';
					break;

				// Сортировка по наличию, товары которых нет в наличии будут в конце
				case 'stock_desc':
					$meta_query['in_stock'] = [
						'relation' => 'OR',
						[
							'key'     => FS_Config::get_meta( 'remaining_amount' ),
							'type'    => 'NUMERIC',
							'compare' => 'EXISTS',
						]
					];
					$order_by               = [ 'in_stock' => 'ASC' ];
					break;

				case 'price_desc': //sort by price in a falling order
					$meta_query['price'] = array(
						'key'  => FS_Config::get_meta( 'price' ),
						'type' => 'DECIMAL'
					);
					$order_by['price']   = 'DESC';
					break;
				case 'views_desc': //сортируем по просмотрам в спадающем порядке
					$meta_query['views'] = array( 'key' => 'views', 'type' => 'NUMERIC' );
					$order_by['views']   = 'DESC';
					break;
				case 'views_asc': //сортируем по просмотрам в спадающем порядке
					$meta_query['views'] = array( 'key' => 'views', 'type' => 'NUMERIC' );
					$order_by['views']   = 'ASC';
					break;
				case 'name_asc': //сортируем по названию по алфавиту
					$order_by['title'] = 'ASC';
					break;
				case 'name_desc': //сортируем по названию по алфавиту в обратном порядке
					$order_by['title'] = 'DESC';
					break;
				case 'date_desc':
					$order_by['date'] = 'DESC';
					break;
				case 'date_asc':
					$order_by['date'] = 'ASC';
					break;
				case 'action_price' :
					$meta_query['action_price'] = [
						'key'     => FS_Config::get_meta( 'action_price' ),
						'compare' => 'EXISTS'
					];
					$order_by['action_price']   = 'DESC';
					break;
				case 'menu_order' :
					$order_by['menu_order'] = 'ASC';
					break;

			}

			// Фильтрация по наличию
			if ( isset( $url['availability'] ) && $url['availability'] != '' ) {
				switch ( $url['availability'] ) {
					case '1':
						$meta_query['availability'] = array(
							'key'     => FS_Config::get_meta( 'remaining_amount' ),
							'compare' => '!=',
							'value'   => '0'
						);
						break;
					case '0':
						$meta_query['availability'] = array(
							'key'     => FS_Config::get_meta( 'remaining_amount' ),
							'compare' => '==',
							'value'   => '0'
						);
						break;
				}

			}

			// Фильтрация по производителю
			if ( isset( $url['brands'] ) && ! empty( $url['brands'] ) ) {
				$brands      = explode( ',', $url['brands'] );
				$tax_query[] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => FS_Config::get_data( 'brand_taxonomy' ),
						'field'    => 'id',
						'terms'    => array_filter( $brands, 'intval' ),
						'operator' => 'IN'
					)
				);
			}

			//Фильтруем по свойствам (атрибутам)
			if ( ! empty( $_REQUEST['filter'] ) ) {
				$tax_query[] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'product-attributes',
						'field'    => 'id',
						'terms'    => explode( ',', $_REQUEST['filter'] ),
						'operator' => fs_option( 'fs_product_filter_type', 'AND' )
					)
				);
			}

			// Фильтруем по категориям
			if ( isset( $url['categories'] ) && ! empty( $url['categories'] ) && is_array( $url['categories'] ) ) {
				$tax_query[] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => FS_Config::get_data( 'product_taxonomy' ),
						'field'    => 'id',
						'terms'    => array_map( 'intval', $url['categories'] ),
						'operator' => 'IN'
					)
				);
			}

			if ( ! empty( $meta_query ) ) {
				$query->set( 'meta_query', $meta_query );
			}

			if ( ! empty( $tax_query ) ) {
				$tax_query = array_merge( $query->tax_query->queries, $tax_query );
				$query->set( 'tax_query', $tax_query );
			}

			$query->set( 'posts_per_page', $per_page );
			$query->set( 'orderby', $order_by ?: [ 'post_date' => 'DESC' ] );
		}

	}

	/**
	 * Forms a request for filtering by price for products with different currencies
	 *
	 * @param $price_start
	 * @param $price_end
	 *
	 * @return array
	 */
	function fs_filter_by_price_clause( $clauses, $query ) {
		if ( ( ! isset( $_GET['price_start'] ) || ! isset( $_GET['price_end'] ) ) || is_admin() ) {
			return $clauses;
		}

		global $wpdb;

		$price_start = isset( $_GET['price_start'] ) ? (int) $_GET['price_start'] : 0;
		$price_end   = isset( $_GET['price_end'] ) ? (int) $_GET['price_end'] : PHP_INT_MAX;

		$clauses['join'] .= " LEFT JOIN $wpdb->postmeta AS meta ON ({$wpdb->posts}.ID = meta.post_id)";

		$count_currencies = get_terms( [
			'taxonomy'   => FS_Config::get_data( 'currencies_taxonomy' ),
			'hide_empty' => false
		] );

		if ( ! fs_option( 'multi_currency_on' ) || count( $count_currencies ) == 0 ) {
			$clauses['where'] .= $wpdb->prepare( " AND (meta.meta_key = %s AND CAST(meta.meta_value AS SIGNED) BETWEEN %d AND %d)", FS_Config::get_meta( 'price' ), $price_start, $price_end );

			return $clauses;
		}

		$currencies = wp_cache_get( 'fs_currencies' );
		if ( empty( $currencies ) ) {
			$currencies = get_terms( [
				'hide_empty' => false,
				'taxonomy'   => FS_Config::get_data( 'currencies_taxonomy' )
			] );
			wp_cache_set( 'fs_currencies', $currencies );
		}

		if ( empty( $currencies ) ) {
			return $clauses;
		}

		$sub_query = [];
		foreach ( $currencies as $key => $currency ) {
			$price_in_base = get_term_meta( $currency->term_id, '_fs_currency_cost', true );
			if ( $price_in_base == 0 ) {
				continue;
			}
			$sub_query [] = "(meta.meta_key = '" . FS_Config::get_meta( 'price' ) . "' AND CAST(meta.meta_value AS SIGNED) BETWEEN " . $price_start / $price_in_base . " AND " . $price_end / $price_in_base . ")";
		}

		$clauses['where'] .= " AND (" . implode( ' OR ', $sub_query ) . ")";

		return $clauses;
	}


	/**
	 * Add rewrite rules for terms
	 *
	 * @param $wp_rewrite
	 *
	 * @return array
	 */
	function taxonomy_rewrite_rules( $wp_rewrite ) {
		$rules = [];
		$terms = get_terms( [ 'taxonomy' => $this->taxonomy_name, 'hide_empty' => false ] );
		if ( fs_option( 'fs_disable_taxonomy_slug' ) ) {
			foreach ( FS_Config::get_languages() as $language_name => $language ) {
				$meta_key = '_seo_slug__' . mb_strtolower( $language['locale'] );
				foreach ( $terms as $term ) {
					$localize_slug = get_term_meta( $term->term_id, $meta_key, 1 );
					if ( $language['locale'] == FS_Config::default_locale() ) {
						$rules[ $term->slug . '/?$' ]            = 'index.php?' . $term->taxonomy . '=' . $term->slug;
						$rules[ $term->slug . '/page/(\d+)/?$' ] = 'index.php?' . $term->taxonomy . '=' . $term->slug . '&paged=$matches[1]';
						$rules[ $term->slug . '/page-(\d+)/?$' ] = 'index.php?' . $term->taxonomy . '=' . $term->slug . '&paged=$matches[1]';
					} elseif ( $language['locale'] != FS_Config::default_locale() && $localize_slug ) {
						$rules[ $localize_slug . '/?$' ]            = 'index.php?' . $term->taxonomy . '=' . $term->slug;
						$rules[ $localize_slug . '/page/(\d+)/?$' ] = 'index.php?' . $term->taxonomy . '=' . $term->slug . '&paged=$matches[1]';
						$rules[ $localize_slug . '/page-(\d+)/?$' ] = 'index.php?' . $term->taxonomy . '=' . $term->slug . '&paged=$matches[1]';
					}
				}
			}

		} else {
			foreach ( FS_Config::get_languages() as $language ) {
				if ( $language['locale'] == FS_Config::default_locale() ) {
					continue;
				}
				foreach ( $terms as $term ) {
					$meta_key      = $language['locale'] != FS_Config::default_locale() ? '_seo_slug__' . $language['locale'] : '_seo_slug';
					$localize_slug = get_term_meta( $term->term_id, $meta_key, 1 );
					if ( $localize_slug ) {
						$rules[ $term->taxonomy . '/' . $localize_slug . '/?$' ]            = 'index.php?' . $term->taxonomy . '=' . $term->slug;
						$rules[ $term->taxonomy . '/' . $localize_slug . '/page/(\d+)/?$' ] = 'index.php?' . $term->taxonomy . '=' . $term->slug . '&paged=$matches[1]';
						$rules[ $term->taxonomy . '/' . $localize_slug . '/page-(\d+)/?$' ] = 'index.php?' . $term->taxonomy . '=' . $term->slug . '&paged=$matches[1]';
					}
				}
			}
		}

		// Rewriting rules for products in other languages
		foreach ( FS_Config::get_languages() as $language_name => $language ) {
			if ( $language['locale'] == FS_Config::default_locale() ) {
				continue;
			}
			$rules[ $language_name . '/product/([^/]+)(?:/([0-9]+))?/?$' ] = 'index.php?product=$matches[1]&page=$matches[2]';
		}

		$wp_rewrite->rules = $rules + $wp_rewrite->rules;

		return $wp_rewrite->rules;
	}

	/**
	 * Redirects to a localized url if a localized slug is specified for the product category
	 *
	 * @return void
	 */
	function redirect_to_localized_url() {
		global $wp_query;
		if ( ! fs_is_product_category() || count( $wp_query->query ) > 1 || get_locale() == FS_Config::default_locale() ) {
			return;
		}

		$term           = get_queried_object();
		$default_locale = FS_Config::default_locale();
		$locale         = get_locale();

		$localized_slug = get_term_meta( $term->term_id, '_seo_slug__' . mb_strtolower( $locale ), 1 );

		$url_slug = explode( '/', $_SERVER['REQUEST_URI'] )[2];

		if ( $locale != $default_locale && $localized_slug != '' && $url_slug != $localized_slug ) {
			$redirect_url = fs_localize_category_url( $term->term_id );
			wp_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * Removes the taxonomy prefix in the product category link
	 *
	 * @param $term_link
	 * @param $term
	 * @param $taxonomy
	 *
	 * @return string|string[]
	 */
	function replace_taxonomy_slug_filter( $term_link, $term, $taxonomy ) {


		if ( $taxonomy != $this->taxonomy_name ) {
			return $term_link;
		}

		$meta_key = get_locale() != FS_Config::default_locale() ? '_seo_slug__' . mb_strtolower( get_locale() ) : '_seo_slug';

		// Remove the taxonomy prefix in links
		if ( fs_option( 'fs_disable_taxonomy_slug' ) ) {
			$term_link = str_replace( '/' . $taxonomy . '/', '/', $term_link );
		}

		// Convert the link in accordance with the Cyrillic name
		if ( get_locale() != FS_Config::default_locale() && fs_option( 'fs_localize_slug' ) && get_term_meta( $term->term_id, $meta_key, 1 ) ) {
			$localize_slug = get_term_meta( $term->term_id, $meta_key, 1 );
			$term_link     = str_replace( $term->slug, $localize_slug, $term_link );
		}

		return $term_link;
	}


	/**
	 * Registration of additional taxonomy fields
	 *
	 * @param null $term объект текущего термина таксономии
	 *
	 * @return array
	 */
	public static function get_taxonomy_fields( $term = null ) {
		$checkout_fields = [];
		foreach ( FS_Users::get_user_fields() as $key => $user_field ) {
			if ( isset( $user_field['checkout'] ) && $user_field['checkout'] == true ) {
				$checkout_fields[ $key ] = $user_field['name'];
			}
		}
		$fields = array(
			FS_Config::get_data( 'product_taxonomy' )       =>
				array(
					'_content'         => array(
						'name' => __( 'Category text', 'f-shop' ),
						'type' => 'rich_text',
						'args' => [
							'multilang' => true,
						]
					),
					'_seo_slug'        => array(
						'name' => __( 'SEO slug', 'f-shop' ),
						'type' => 'text',
						'args' => [
							'multilang'              => true,
							'disable_default_locale' => true
						]
					),
					'_seo_title'       => array(
						'name' => __( 'SEO title', 'f-shop' ),
						'type' => 'text',
						'args' => [
							'multilang' => true,
						]
					),
					'_seo_description' => array(
						'name' => __( 'SEO description', 'f-shop' ),
						'type' => 'textarea',
						'args' => [
							'multilang' => true,
						]
					),
//                    '_seo_canonical' => array(
//                        'name' => __('SEO canonical', 'f-shop'),
//                        'type' => 'text',
//                        'args' => [
//                            'multilang' => true,
//                        ]
//                    ),
					'_thumbnail_id'    => [
						'name' => __( 'Thumbnail', 'f-shop' ),
						'type' => 'image',
						'args' => [
							'multilang' => false,
						]
					],
					'_icon_id'         => [
						'name' => __( 'Icon', 'f-shop' ),
						'type' => 'image',
						'args' => [
							'multilang' => false,
						]
					],
					'_min_qty'         => array(
						'name'    => __( 'Minimum quantity of goods for order', 'f-shop' ),
						'type'    => 'text',
						'subtype' => 'number',
						'args'    => [
							'multilang' => false,
						],
						'help'    => __( 'Distributed to goods belonging to this category', 'f-shop' )
					),
				),
			'fs-payment-methods'                            =>
				array(
					'_thumbnail_id'         => array(
						'name' => __( 'Thumbnail', 'f-shop' ),
						'type' => 'image',
						'args' => array()
					),
					'_fs_pay_message'       => array(
						'name' => __( 'E-mail message to the buyer if the order is confirmed by the manager', 'f-shop' ),
						'help' => __( 'This message is sent to the buyer at the time the manager confirms the order. You can use meta data of type: <code>%order_id%</code> - order number, <code>%pay_name%</code> - name of the payment method, <code>%pay_url%</code> - payment reference .', 'f-shop' ),
						'type' => 'textarea',
						'args' => array()
					),
					'_fs_after_pay_message' => array(
						'name' => __( 'Message to the buyer after payment on the site', 'f-shop' ),
						'help' => __( 'This message will be shown if the buyer has successfully paid the order. You can use these variables: <code>%order_id%</code> - order number, <code>%pay_name%</code> - name of the payment method', 'f-shop' ),
						'type' => 'textarea',
						'args' => array()
					),
					'_fs_pay_inactive'      => array(
						'name' => __( 'Unavailable for payment', 'f-shop' ),
						'help' => __( 'If you turn off, then the payment method will not be visible to users, only in the admin panel.', 'f-shop' ),
						'type' => 'checkbox',
						'args' => array()
					),
					'_fs_checkout_redirect' => array(
						'name' => __( 'When choosing this method, send the buyer immediately to the payment page', 'f-shop' ),
						'help' => __( 'This is convenient in some cases, but it is better to leave this option off', 'f-shop' ),
						'type' => 'checkbox',
						'args' => array()
					)
				),
			'fs-delivery-methods'                           =>
				array(
					'_thumbnail_id'        => array(
						'name' => __( 'Thumbnail', 'f-shop' ),
						'type' => 'image',
						'args' => array()
					),
					'_fs_delivery_cost'    => array(
						'name'    => __( 'Shipping Cost in Base Currency', 'f-shop' ),
						'type'    => 'text',
						'subtype' => 'number',
						'args'    => array( 'style' => 'width:72px;', 'step' => 0.01 )
					),
					'_fs_disable_fields'   => array(
						'name'    => __( 'Fields to disable when choosing this delivery method', 'f-shop' ),
						'type'    => 'multiselect',
						'options' => $checkout_fields,
					),
					'_fs_required_fields'  => array(
						'name'    => __( 'Required fields when choosing this delivery method', 'f-shop' ),
						'type'    => 'multiselect',
						'options' => $checkout_fields,
					),
					'_fs_add_packing_cost' => array(
						'name' => __( 'Consider the cost of packaging', 'f-shop' ),
						'type' => 'checkbox'
					)
				),
			'fs-currencies'                                 =>
				array(
					'_fs_currency_code'    => array(
						'name' => __( 'International currency code', 'f-shop' ),
						'type' => 'text',
						'args' => array()
					),
					'_fs_currency_cost'    => array(
						'name' => __( 'Cost in base currency', 'f-shop' ),
						'type' => 'text',
						'args' => array()
					),
					'_fs_currency_display' => array(
						'name' => __( 'Display on the site', 'f-shop' ),
						'type' => 'text',
						'args' => array()
					),
					'_fs_currency_locale'  => array(
						'name' => __( 'Currency Language (locale)', 'f-shop' ),
						'type' => 'select',
						'args' => array( 'values' => FS_Config::get_locales(), )
					)
				),
			// Дополнительные поля налога
			FS_Config::get_data( 'product_taxes_taxonomy' ) =>
				array(
					'_fs_tax_value' => array(
						'name' => __( 'The amount or value of tax as a percentage', 'f-shop' ),
						'type' => 'text',
						'args' => array()
					)
				),
			// Дополнительные поля налога
			FS_Config::get_data( 'brand_taxonomy' )         =>
				array(
					'_thumbnail_id' => array(
						'name' => __( 'Thumbnail', 'f-shop' ),
						'type' => 'image',
						'args' => array()
					),
				),
			// Дополнительные поля скидок
			FS_Config::get_data( 'discount_taxonomy' )      =>
				array(
					'fs_discount_type'       => array(
						'name' => __( 'Discount type', 'f-shop' ),
						'type' => 'select',
						'args' => array(
							'values' => array(
								'product'      => __( 'The discount applies only to products of the product categories, brands or characteristics selected below', 'f-shop' ),
								'repeat_order' => __( 'Discount applies to reorders', 'f-shop' ),
								'cart_amount'  => __( 'Discount on the amount of items in the cart', 'f-shop' )
							)
						)
					),
					'fs_min_order_amount'    => array(
						'name' => __( 'Minimum order amount', 'f-shop' ),
						'type' => 'text',
						'help' => __( 'It is applied if the order amount has exceeded the value specified in this field' ),
						'args' => array(
							'min' => 1
						)
					),
					'fs_discount_categories' => array(
						'name' => __( 'Categories for which the discount applies', 'f-shop' ),
						'type' => 'dropdown_categories',
						'rule' => [ 'discount_where_is', '=', 'category' ],
						'args' => array(
							'taxonomy' => FS_Config::get_data( 'product_taxonomy' ),
							'multiple' => true,
						)
					),
					'fs_discount_brands'     => array(
						'name' => __( 'Discount brands', 'f-shop' ),
						'type' => 'dropdown_categories',
						'rule' => [ 'discount_where_is', '=', 'category' ],
						'args' => array(
							'taxonomy' => FS_Config::get_data( 'brand_taxonomy' ),
							'multiple' => true,
						)
					),
					'fs_discount_features'   => array(
						'name' => __( 'Properties of products to which the discount applies', 'f-shop' ),
						'type' => 'dropdown_categories',
						'args' => array(
							'taxonomy' => FS_Config::get_data( 'features_taxonomy' ),
							'multiple' => true,
						)
					),
					'fs_discount_value'      => array(
						'name'     => __( 'Discount value', 'f-shop' ),
						'type'     => 'number',
						'args'     => [ 'min' => 0 ],
						'required' => true,
						'help'     => ''
					),
					'fs_discount_value_type' => array(
						'name'     => __( 'This is a fixed amount or a percentage discount?', 'f-shop' ),
						'type'     => 'select',
						'value'    => 'discount_fixed',
						'args'     => array(
							'values' => [
								'discount_fixed'   => __( 'Fixed discount', 'f-shop' ),
								'discount_percent' => __( 'Percentage discount', 'f-shop' ),
							]
						),
						'required' => true,
						'help'     => ''
					)
				)

		);

		return (array) apply_filters( 'fs_taxonomy_fields', $fields );
	}


	/**
	 * Sets a slug for a product category
	 *
	 * @return mixed|void
	 */
	public function product_category_rewrite_slug() {
		$rewrite = false;

		if ( fs_option( 'fs_product_category_slug' ) ) {
			$rewrite = [
				'slug' => fs_option( 'fs_product_category_slug', 'catalog' )
			];
		}

		return apply_filters( 'fs_product_category_rewrite_slug', $rewrite );
	}

	/**
	 * Register custom taxonomies
	 *
	 * @return array
	 */
	function shop_taxonomies() {
		$taxonomies = array(
			FS_Config::get_data( 'product_taxonomy' )        => array(
				'object_type'        => FS_Config::get_data( 'post_type' ),
				'label'              => __( 'Product categories', 'f-shop' ),
				'labels'             => array(
					'name'              => __( 'Product categories', 'f-shop' ),
					'singular_name'     => __( 'Product category', 'f-shop' ),
					'search_items'      => __( 'Product categories', 'f-shop' ),
					'all_items'         => __( 'Product categories', 'f-shop' ),
					'parent_item'       => __( 'Product categories', 'f-shop' ),
					'parent_item_colon' => __( 'Product categories', 'f-shop' ),
					'edit_item'         => __( 'Category editing', 'f-shop' ),
					'update_item'       => __( 'Product categories', 'f-shop' ),
					'add_new_item'      => __( 'Add category', 'f-shop' ),
					'new_item_name'     => __( 'Product categories', 'f-shop' ),
					'menu_name'         => __( 'Product categories', 'f-shop' ),
				),
				'metabox'            => true,
				'hierarchical'       => true,
				"public"             => true,
				"show_ui"            => true,
				"publicly_queryable" => true,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
				'rewrite'            => $this->product_category_rewrite_slug()
			),
			FS_Config::get_data( 'product_pay_taxonomy' )    => array(
				'object_type'        => FS_Config::get_data( 'post_type' ),
				'label'              => __( 'Payment methods', 'f-shop' ),
				'labels'             => array(
					'name'          => __( 'Payment methods', 'f-shop' ),
					'singular_name' => __( 'Payment method', 'f-shop' ),
					'add_new_item'  => __( 'Add a payment method', 'f-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => true,
				"show_ui"            => true,
				"publicly_queryable" => false,
				'show_in_nav_menus'  => false,
				'meta_box_cb'        => false,
				'metabox'            => false,
				'show_admin_column'  => false,
			),
			FS_Config::get_data( 'product_del_taxonomy' )    => array(
				'object_type'        => FS_Config::get_data( 'post_type' ),
				'label'              => __( 'Delivery methods', 'f-shop' ),
				'labels'             => array(
					'name'          => __( 'Delivery methods', 'f-shop' ),
					'singular_name' => __( 'Delivery method', 'f-shop' ),
					'add_new_item'  => __( 'Add a delivery method', 'f-shop' ),
				),
				"public"             => true,
				"show_ui"            => true,
				"publicly_queryable" => false,
				'show_in_nav_menus'  => false,
				'metabox'            => false,
				'meta_box_cb'        => false,
				'show_admin_column'  => false,
				'show_in_quick_edit' => false
			),
			FS_Config::get_data( 'features_taxonomy' )       => array(
				'object_type'        => FS_Config::get_data( 'post_type' ),
				'label'              => __( 'Product attributes', 'f-shop' ),
				'labels'             => array(
					'name'          => __( 'Product attributes', 'f-shop' ),
					'singular_name' => __( 'Product attributes', 'f-shop' ),
					'add_new_item'  => __( 'Add property / group of properties', 'f-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => true,
				"show_ui"            => true,
				"publicly_queryable" => true,
				'show_in_rest'       => true,
				'meta_box_cb'        => false,
				'metabox'            => false,
				'show_admin_column'  => false,
				'hierarchical'       => true,
				'show_in_quick_edit' => true
			),
			FS_Config::get_data( 'brand_taxonomy' )          => array(
				'object_type'        => FS_Config::get_data( 'post_type' ),
				'label'              => __( 'Manufacturers', 'f-shop' ),
				'labels'             => array(
					'name'          => __( 'Manufacturers', 'f-shop' ),
					'singular_name' => __( 'Manufacturer', 'f-shop' ),
					'add_new_item'  => __( 'Add Manufacturer', 'f-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => true,
				"show_ui"            => true,
				"publicly_queryable" => true,
				'show_in_rest'       => true,
				'metabox'            => true,
				'show_admin_column'  => false,
				'hierarchical'       => true,
				'show_in_quick_edit' => true
			),
			FS_Config::get_data( 'product_taxes_taxonomy' )  => array(
				'object_type'        => FS_Config::get_data( 'post_type' ),
				'label'              => __( 'Taxes', 'f-shop' ),
				'labels'             => array(
					'name'          => __( 'Taxes', 'f-shop' ),
					'singular_name' => __( 'Taxes', 'f-shop' ),
					'add_new_item'  => __( 'Add tax', 'f-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => true,
				"show_ui"            => true,
				'show_in_nav_menus'  => false,
				"publicly_queryable" => false,
				'meta_box_cb'        => false,
				'show_admin_column'  => false,
				'hierarchical'       => false,
				'show_in_quick_edit' => false
			),
			FS_Config::get_data( 'order_statuses_taxonomy' ) => array(
				'object_type'        => FS_Config::get_data( 'post_type_orders' ),
				'label'              => __( 'Order statuses', 'f-shop' ),
				'labels'             => array(
					'name'          => __( 'Order statuses', 'f-shop' ),
					'singular_name' => __( 'Order status', 'f-shop' ),
					'add_new_item'  => __( 'Add Order Status', 'f-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => false,
				"show_ui"            => true,
				'show_in_nav_menus'  => false,
				"publicly_queryable" => false,
				'meta_box_cb'        => false,
				'show_admin_column'  => false,
				'hierarchical'       => false,
				'show_in_quick_edit' => true
			)
		);
		if ( fs_option( 'discounts_on' ) == 1 ) {
			$taxonomies['fs-discounts'] = array(
				'object_type'        => 'product',
				'label'              => __( 'Discounts', 'f-shop' ),
				'labels'             => array(
					'name'          => __( 'Discounts', 'f-shop' ),
					'singular_name' => __( 'Discount', 'f-shop' ),
					'add_new_item'  => __( 'Add Discount', 'f-shop' ),
					'edit_item'     => 'Edit Discount',
					'update_item'   => 'Update Discount',
				),
				//					исключаем категории из лицевой части
				"public"             => false,
				"show_ui"            => true,
				"publicly_queryable" => false,
				'metabox'            => false,
				'show_admin_column'  => false,
				'hierarchical'       => false,
				'meta_box_cb'        => false,
				'show_in_quick_edit' => false
			);
		}
		if ( fs_option( 'multi_currency_on' ) == 1 ) {
			$taxonomies[ FS_Config::get_data( 'currencies_taxonomy' ) ] = array(
				'object_type'        => 'product',
				'label'              => __( 'Currencies', 'f-shop' ),
				'labels'             => array(
					'name'          => __( 'Currencies', 'f-shop' ),
					'singular_name' => __( 'Currency', 'f-shop' ),
					'add_new_item'  => __( 'Add Currency', 'f-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => false,
				"show_ui"            => true,
				"publicly_queryable" => false,
				'metabox'            => false,
				'show_admin_column'  => false,
				'hierarchical'       => false,
				'meta_box_cb'        => false,
				'show_in_quick_edit' => false
			);
		}

		$taxonomies = apply_filters( 'fs_taxonomies', $taxonomies );

		return $taxonomies;
	}

	/**
	 * Creates taxonomy
	 */
	public function create_taxonomy() {
		// сам процесс регистрации таксономий
		if ( $this->shop_taxonomies() ) {
			foreach ( $this->shop_taxonomies() as $key => $taxonomy ) {
				$object_type = $taxonomy['object_type'];
				unset( $taxonomy['object_type'] );
				register_taxonomy( $key, $object_type, $taxonomy );
			}
		}

		// поля таксономии харакеристик товара
		add_action( "product-attributes_edit_form_fields", array( $this, 'edit_product_attr_fields' ) );
		add_action( "product-attributes_add_form_fields", array( $this, 'add_product_attr_fields' ) );
		add_action( "create_product-attributes", array( $this, 'save_custom_taxonomy_meta' ) );
		add_action( "edited_product-attributes", array( $this, 'save_custom_taxonomy_meta' ) );
	}

	/**
	 * Возвращает значение мета поля для термина таксономии
	 * обертка для функции get_term_meta()
	 *
	 * @param int $term_id
	 * @param string $meta_key
	 * @param int $single
	 * @param null $default
	 *
	 * @return mixed|void
	 */
	public static function fs_get_term_meta( $term_id = 0, $meta_key = '', $single = 1, $default = null ) {
		if ( ! $term_id || ! $meta_key ) {
			return;
		}

		$meta_value = get_term_meta( $term_id, $meta_key, $single );

		if ( $single == 1 && $meta_value == '' && $default != '' ) {
			$meta_value = $default;
		}

		return apply_filters( 'fs_term_field_value', $meta_value, $term_id, $meta_key );
	}

	/**
	 * Метод выводит мета поля таксономии
	 * массив полей берётся из класа FS_Config
	 * из метода get_taxonomy_fields()
	 * который позволяет устанавливать свои поля серез фильтр fs_taxonomy_fields
	 * TODO: в дальнейшем метаполя всех таксономий выводить с помощью этого метода
	 *
	 * @param $term
	 * @param $taxonomy
	 */
	function edit_taxonomy_fields( $term, $taxonomy ) {
		$form   = new FS_Form();
		$fields = self::get_taxonomy_fields( $term );

		if ( count( $fields[ $taxonomy ] ) ) {

			foreach ( $fields[ $taxonomy ] as $name => $field ) {

				$field['args']['value'] = self::fs_get_term_meta( $term->term_id, $name );
				echo '<tr class="form-field taxonomy-thumbnail-wrap">';
				echo '<th scope="row"><label for="taxonomy-thumbnail">' . esc_attr( $field['name'] ) . '</label></th>';

				echo '<td>';

				$form->render_field( $name,
					$field['type'], array_merge( $field['args'], [
						'source'  => 'term_meta',
						'term_id' => $term->term_id,
						'default' => get_term_meta( $term->term_id, $name, 1 )
					] ) );
				if ( ! empty( $field['help'] ) ) {
					printf( '<p class="description">%s</p>', $field['help'] );
				}
				echo '</td>';
				echo '</tr>';
			}
		}
	}

	/**
	 * Сохраняет значение мета - полей при добавлении нового термина
	 *
	 * @param $taxonomy
	 */
	function add_taxonomy_fields( $taxonomy ) {

		$form   = new FS_Form();
		$fields = self::get_taxonomy_fields();
		if ( isset( $fields[ $taxonomy ] ) && is_array( $fields[ $taxonomy ] ) && count( $fields[ $taxonomy ] ) ) {
			foreach ( $fields[ $taxonomy ] as $name => $field ) {
				$id = str_replace( '_', '-', sanitize_title( 'fs-' . $name . '-' . $field['type'] ) );
				echo '<div class="form-field ' . esc_attr( $name ) . '-wrap">';
				echo '<label for="' . esc_attr( $id ) . '">' . esc_attr( $field['name'] ) . '</label>';
				$form->render_field( $name, $field['type'], $field['args'] );
				if ( ! empty( $field['help'] ) ) {
					printf( '<p class="description">%s</p>', esc_html( $field['help'] ) );
				}
				echo '</div>';
			}
		}
	}

	/**
	 * Preserves all metafields of taxonomy
	 * if the value is empty, the field is removed from the database
	 * TODO: удалить дубликаты этой функции
	 *
	 * @param $term_id
	 *
	 * @return void|null
	 */
	function save_taxonomy_fields( $term_id ) {
		$fields            = self::get_taxonomy_fields();
		$languages         = FS_Config::get_languages();
		$term              = get_term( $term_id );
		$multilang_support = fs_option( 'fs_multi_language_support' ) == '1' && ! empty( $languages );

		if ( ! empty( $fields[ $term->taxonomy ] ) ) {
			foreach ( $fields[ $term->taxonomy ] as $name => $field ) {
				if ( $multilang_support && ! empty( $field['args']['multilang'] ) ) {
					foreach ( $languages as $code => $language ) {
						$meta_key = $name . '__' . $language['locale'];
						if ( ( isset( $_POST[ $meta_key ] ) && $_POST[ $meta_key ] != '' ) || $name == '_seo_slug' ) {
							$value = apply_filters( 'fs_transform_meta_value', $_POST[ $meta_key ], $name, $code, $term_id );
							update_term_meta( $term_id, $meta_key, $value );
						} else {
							delete_term_meta( $term_id, $meta_key );
						}
					}
				} else {
					if ( isset( $_POST[ $name ] ) && $_POST[ $name ] != '' ) {
						update_term_meta( $term_id, $name, $_POST[ $name ] );
					} else {
						delete_term_meta( $term_id, $name );
					}
				}
			}
		}

		// Обновляем правила ЧПУ
		flush_rewrite_rules();
	}

	/**
	 * Displays fields for setting product characteristics.
	 *
	 * @param $term
	 */
	function edit_product_attr_fields( $term ) {

		$att_type   = get_term_meta( $term->term_id, 'fs_att_type', 1 );
		$attr_types = array(
			'text'  => array( 'name' => __( 'text', 'f-shop' ) ),
			'color' => array( 'name' => __( 'color', 'f-shop' ) ),
			'image' => array( 'name' => __( 'image', 'f-shop' ) ),
			'range' => array( 'name' => __( 'range', 'f-shop' ) )
		);
		?>
        <tr class="form-field term-parent-wrap">
            <th scope="row"><label for="fs_att_type"><?php esc_html_e( 'Attribute type', 'f-shop' ); ?></label></th>
            <td>
                <select name="f-shop[fs_att_type]" id="fs_att_type" class="postform">
					<?php if ( ! empty( $attr_types ) ) {
						foreach ( $attr_types as $att_key => $attr_type ) {
							echo ' <option value="' . esc_attr( $att_key ) . '" ' . selected( $att_key, $att_type, 0 ) . ' > ' . esc_html( $attr_type['name'] ) . '</option >';
						}
					} ?>
                </select>
                <p class="description"><?php esc_html_e( 'Products may have different properties. Here you can choose which type of property you need.', 'f-shop' ); ?></p>
            </td>
        </tr>
        <tr class="form-field term-parent-wrap  fs-att-values fs-att-color"
            style="display: <?php if ( $att_type == 'color' ) {
			    echo 'table-row';
		    } else echo 'none' ?>">
            <th scope="row">
                <label for="fs_att_color_value"><?php esc_html_e( 'Color value', 'f-shop' ); ?></label>
            </th>
            <td>
                <input type="text" name="f-shop[fs_att_color_value]"
                       value="<?php echo esc_attr( get_term_meta( $term->term_id, 'fs_att_color_value', 1 ) ) ?>"
                       class="fs-color-select" id="fs_att_color_value">
            </td>
        </tr>
        <tr class="form-field term-parent-wrap fs-att-values fs-att-range"
            style="display: <?php if ( $att_type == 'range' ) {
			    echo 'table-row';
		    } else echo 'none' ?>">
            <th scope="row"><label><?php esc_html_e( 'Beginning of range', 'f-shop' ); ?></label></th>
            <td>
                <input type="number" step="0.01" name="f-shop[fs_att_range_start_value]" placeholder="0"
                       value="<?php echo esc_attr( get_term_meta( $term->term_id, 'fs_att_range_start_value', 1 ) ) ?>">
            </td>
        </tr>
        <tr class="form-field term-parent-wrap fs-att-values fs-att-range"
            style="display: <?php if ( $att_type == 'range' ) {
			    echo 'table-row';
		    } else echo 'none' ?>">
            <th scope="row"><label><?php esc_html_e( 'End of range', 'f-shop' ); ?></label></th>
            <td>
                <input type="number" step="0.01" name="f-shop[fs_att_range_end_value]" placeholder="∞"
                       value="<?php echo esc_attr( get_term_meta( $term->term_id, 'fs_att_range_end_value', 1 ) ) ?>">
            </td>
        </tr>
        <tr class="form-field term-parent-wrap fs-att-values fs-att-range"
            style="display: <?php if ( $att_type == 'range' ) {
			    echo 'table-row';
		    } else echo 'none' ?>">
            <th scope="row">
                <label
                        for="fs_att_compare"><?php esc_html_e( 'Use the number of purchased goods to compare with this attribute.', 'f-shop' ); ?></label>
            </th>
            <td>
                <input type="checkbox"
                       name="f-shop[fs_att_compare]" <?php checked( 1, get_term_meta( $term->term_id, 'fs_att_compare', 1 ) ) ?>
                       value="1" id="fs_att_compare">
            </td>
        </tr>
		<?php
		$atach_image_id = get_term_meta( $term->term_id, 'fs_att_image_value', 1 );
		$att_image      = $atach_image_id ? wp_get_attachment_image_url( $atach_image_id, 'medium' ) : '';
		$display_button = ! empty( $att_image ) ? 'block' : 'none';
		$display_text   = ! empty( $att_image ) ? __( 'change image', 'f-shop' ) : __( 'select image', 'f-shop' );
		if ( ! empty( $att_image ) ) {
			$class = "show";
		} else {
			$class = "hidden";
		}
		?>
        <tr class="form-field term-parent-wrap fs-att-values fs-att-image"
            style="display: <?php if ( $att_type == 'image' ) {
			    echo 'table-row';
		    } else echo 'none' ?>" id="fs-att-image">
            <th scope="row"><label><?php esc_html_e( 'Image', 'f-shop' ); ?></label></th>
            <td>
                <div class="fs-fields-container">';
                    <div class="fs-selected-image <?php echo esc_attr( $class ) ?>"
                         style=" background-image: url(<?php echo esc_attr( $att_image ) ?>);"></div>
                    <button type="button" class="select_file"><?php echo esc_html( $display_text ) ?></button>
                    <input type="hidden" name="f-shop[fs_att_image_value]"
                           value="<?php echo esc_attr( get_term_meta( $term->term_id, 'fs_att_image_value', 1 ) ) ?>"
                           class="fs-image-select">
                    <button type="button" class="delete_file"
                            style="display:<?php echo esc_attr( $display_button ) ?>"> <?php esc_html_e( 'delete image', 'f-shop' ); ?>
                    </button>
                </div>
            </td>
        </tr>
		<?php
	}


	function add_product_attr_fields() {
		?>
        <div class="form-field term-parent-wrap">
            <label for="fs_att_type"> <?php esc_html_e( 'Attribute type', 'f-shop' ); ?> </label>
            <select name="f-shop[fs_att_type]" id="fs_att_type" class="postform">
                <option value="text"> <?php esc_html_e( 'text', 'f-shop' ); ?></option>
                <option value="color"> <?php esc_html_e( 'color', 'f-shop' ); ?></option>
                <option value="image"> <?php esc_html_e( 'image', 'f-shop' ); ?></option>
            </select>
            <p class="description"> <?php esc_html_e( 'Products may have different properties. Here you can choose which type of property you need.', 'f-shop' ); ?>
                .</p>

        </div>
        <div class="form-field term-parent-wrap fs-att-values" style="display: none;" id="fs-att-color">
            <label for="fs_att_color_value"> <?php esc_html_e( 'Color value', 'f-shop' ); ?> </label>
            <input type="text" name="f-shop[fs_att_color_value]" value="" class="fs-color-select"
                   id="fs_att_color_value">
        </div>
        <div class="form-field term-parent-wrap  fs-att-values" style="display: none;" id="fs-att-image">
            <label> <?php esc_html_e( 'Image', 'f-shop' ); ?></label>
            <div class="fs-fields-container">
                <div class="fs-selected-image" style=" background-image: url();"></div>
                <button type="button" class="select_file"> <?php esc_html_e( 'select image', 'f-shop' ); ?></button>
                <input type="hidden" name="f-shop[fs_att_image_value]" value="" class="fs-image-select">
                <button type="button" class="delete_file"
                        style="display:none"> <?php esc_html_e( 'delete image', 'f-shop' ); ?></button>
            </div>
        </div>
		<?php
	}


	/**
	 * The method is triggered at the time of saving. taxonomy fields
	 *
	 * @param $term_id
	 *
	 * @return bool
	 */
	function save_custom_taxonomy_meta( $term_id ) {
		if ( ! isset( $_POST['f-shop'] ) ) {
			return false;
		}
		if ( ! isset( $_POST['f-shop']['fs_att_compare'] ) ) {
			$_POST['f-shop']['fs_att_compare'] = "-";
		}
		$extra = array_map( 'trim', $_POST['f-shop'] );
		foreach ( $extra as $key => $value ) {
			if ( empty( $value ) || $value == "-" ) {
				delete_term_meta( $term_id, $key );
				continue;
			}
			update_term_meta( $term_id, $key, $value );
		}

		return $term_id;
	}

	// List all product categories
	public function get_product_terms( $terms = 'catalog' ) {
		$args    = array(
			'orderby'                => 'id',
			'order'                  => 'ASC',
			'hide_empty'             => false,
			'exclude'                => array(),
			'exclude_tree'           => array(),
			'include '               => array(),
			'number'                 => '',
			'fields'                 => 'all',
			'slug'                   => '',
			'parent'                 => 0,
			'hierarchical'           => true,
			'child_of'               => 0,
			'get'                    => '',
			'name__like'             => '',
			'pad_counts'             => false,
			'offset'                 => '',
			'search'                 => '',
			'cache_domain'           => 'core',
			'name'                   => '',
			'childless'              => false,
			'update_term_meta_cache' => true,
			'meta_query'             => '',
		);
		$myterms = get_terms( array( $terms ), $args );
		if ( $myterms ) {
			echo "<ul>";
			foreach ( $myterms as $term ) {
				$link  = get_term_link( $term->term_id );
				$class = "no-active";
				if ( strripos( $link, $_SERVER['REQUEST_URI'] ) ) {
					$class = 'active';
				}
				echo '<li class="' . esc_attr( $class ) . '"><a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a></li> ';
			}
			echo "</ul>";
		}

	}

	/**
	 * удаляет все термины из таксономии $taxonomy
	 * не удаляя при этом самой таксономии
	 *
	 * @param null $taxonomy - название таксономии
	 *
	 * @return bool
	 */

	public static function delete_taxonomy_terms( $taxonomy = null ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}
		$terms = get_terms( array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false
			)
		);
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$delete = wp_delete_term( intval( $term->term_id ), $taxonomy );
				if ( is_wp_error( $delete ) ) {
					echo esc_html( $delete->get_error_message() );
					continue;
				}
			}
		}

		return true;

	}

	/**
	 * Регистриует колонку код валюты в таксономии валют
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	function add_fs_currencies_columns( $columns ) {
		$columns['сurrency-code'] = __( 'Currency code', 'f-shop' );
		$columns['cost-basic']    = __( 'Cost', 'f-shop' );
		unset( $columns['description'], $columns['posts'] );

		return $columns;
	}

	/**
	 * Регистрирует колонки в таблице списка способов оплаты в админке
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function pay_taxonomy_columns( $columns ) {
		unset( $columns['posts'], $columns['slug'], $columns['description'] );
		$columns['icon']        = __( 'Иконка', 'f-shop' );
		$columns['status']      = __( 'Status', 'f-shop' );
		$columns['description'] = __( 'Описание', 'f-shop' );

		return $columns;
	}

	function currencies_column_content( $content, $column_name, $term_id ) {
		switch ( $column_name ) {
			case 'сurrency-code':
				//do your stuff here with $term or $term_id
				$content = get_term_meta( $term_id, '_fs_currency_code', 1 );
				break;
			case 'cost-basic':
				//do your stuff here with $term or $term_id
				$content = get_term_meta( $term_id, '_fs_currency_cost', 1 );
				break;
			default:
				break;
		}

		return $content;
	}


	/**
	 * Заполняет в админке колонку способа оплаты данными
	 *
	 * @param $content
	 * @param $column_name
	 * @param $term_id
	 *
	 * @return mixed
	 */
	public function pay_taxonomy_column_content( $content, $column_name, $term_id ) {
		switch ( $column_name ) {
			case 'icon':
				$attachment_id = get_term_meta( $term_id, '_thumbnail_id', 1 );
				if ( $attachment_id ) {
					$content = wp_get_attachment_image( $attachment_id, 'thumbnail', true );
				}

				break;

			case 'status':
				//do your stuff here with $term or $term_id
				$content = get_term_meta( $term_id, '_fs_pay_inactive', 1 ) == 1 ? __( 'Disabled', 'f-shop' ) : __( 'Enabled', 'f-shop' );
				break;
			default:
				break;
		}

		return $content;
	}


	/**
	 * Returns the attribute table for the admin
	 *
	 * @param int $post_id ID поста
	 *
	 * @return false|string
	 */
	public static function fs_get_admin_product_attributes_table( $post_id = 0 ) {
		$post_id  = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : $post_id;
		$is_ajax  = isset( $_POST['is_ajax'] ) && (int) $_POST['is_ajax'] === 1 ?: false;
		$taxonomy = FS_Config::get_data( 'features_taxonomy' );

		$fs_config  = new FS_Config();
		$attributes = get_the_terms( $post_id, $taxonomy );

		$attributes_hierarchy = [];
		if ( $attributes ) {
			foreach ( $attributes as $att ) {
				if ( $att->parent == 0 ) {
					continue;
				}

				$attributes_hierarchy[ $att->parent ][] = $att;
			}
		}
		ob_start();
		?>
        <table class="wp-list-table widefat fixed striped" data-fs-element="product-feature-table">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Attribute', 'f-shop' ); ?></th>
                <th><?php esc_html_e( 'Value', 'f-shop' ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php if ( $attributes_hierarchy ): ?>
				<?php foreach ( $attributes_hierarchy as $k => $att_h ): ?>
					<?php $parent = get_term( $k, $fs_config->data['features_taxonomy'] ) ?>
                    <tr>
                        <td><?php echo esc_html( apply_filters( 'the_title', $parent->name ) ) ?></td>
                        <td>
                            <ul class="fs-childs-list">   <?php foreach ( $att_h as $child ): ?>
                                    <li><?php echo esc_html( apply_filters( 'the_title', $child->name ) ) ?> <a
                                                class="remove-att"
                                                title="<?php esc_attr_e( 'do I delete a property?', 'f-shop' ) ?>"
                                                data-action="remove-att"
                                                data-category-id="<?php echo esc_attr( $child->term_id ) ?>"
                                                data-product-id="<?php echo esc_attr( $post_id ) ?>"><span
                                                    class="dashicons dashicons-no-alt"></span></a>
                                    </li>

								<?php endforeach; ?>

                            </ul>
							<?php $args = array(
								'show_option_all'  => '',
								'show_option_none' => '',
								'orderby'          => 'ID',
								'order'            => 'ASC',
								'show_last_update' => 0,
								'show_count'       => 0,
								'hide_empty'       => 0,
								'child_of'         => $parent->term_id,
								'exclude'          => '',
								'echo'             => 1,
								'selected'         => 0,
								'hierarchical'     => 0,
								'name'             => 'cat',
								'id'               => 'name',
								'class'            => 'fs-select-att',
								'depth'            => 0,
								'tab_index'        => 0,
								'taxonomy'         => $fs_config->data['features_taxonomy'],
								'hide_if_empty'    => false,
								'value_field'      => 'term_id', // значение value e option
								'required'         => false,
							);

							wp_dropdown_categories( $args ); ?>
                            <button type="button" class="button button-secondary" data-fs-action="add-atts-from"
                                    data-post="<?php echo esc_attr( $post_id ) ?>"><?php esc_html_e( 'add attribute', 'f-shop' ); ?>
                            </button>

                        </td>
                    </tr>
				<?php endforeach; ?>
			<?php endif; ?>
            </tbody>
        </table>
		<?php
		$output = ob_get_clean();

		if ( $is_ajax ) {
			wp_send_json_success( $output );
		}

		return $output;
	}

	// count all posts in category and subcategories
	public static function fs_count_posts_in_term( $cat_id, $taxonomy = null ) {
		$count = get_transient( 'fs_count_posts_in_term_' . $cat_id );
		if ( false === $count ) {
			$count    = 0;
			$taxonomy = $taxonomy ?: FS_Config::get_data( 'product_taxonomy' );
			$args     = array(
				'tax_query' => array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'id',
						'terms'    => $cat_id,
					),
				),
			);
			$query    = new \WP_Query( $args );
			if ( $query->have_posts() ) {
				$count = $query->found_posts;
			}
			wp_reset_postdata();
			set_transient( 'fs_count_posts_in_term_' . $cat_id, $count, HOUR_IN_SECONDS );
		}

		return $count;
	}
}