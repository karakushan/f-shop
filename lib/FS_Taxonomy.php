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
	public $taxonomy_pagination_structure = 'page/%d';
	public $taxonomy_name;

	function __construct() {
		$this->taxonomy_name = FS_Config::get_data( 'product_taxonomy' );

		add_action( 'init', array( $this, 'create_taxonomy' ),10 );

		add_filter( 'manage_fs-currencies_custom_column', array( $this, 'currencies_column_content' ), 10, 3 );
		add_filter( 'manage_fs-currencies_custom_column', array( $this, 'currencies_column_content' ), 10, 3 );
		add_filter( 'manage_edit-fs-currencies_columns', array( $this, 'add_fs_currencies_columns' ) );

		add_action( 'template_redirect', array( $this, 'redirect_to_localize_url' ) );

		// Remove taxonomy slug from links
		add_filter( 'term_link', array( $this, 'replace_taxonomy_slug_filter' ), 10, 3 );

		// Generate rewrite rules
		add_action( 'generate_rewrite_rules', array( $this, 'taxonomy_rewrite_rules' ) );

		// Filtering products on the category page and in the product archives
		add_action( 'pre_get_posts', array( $this, 'taxonomy_filter_products' ), 12, 1 );

		add_action( 'fs_product_category_filter', [ $this, 'product_category_filter' ] );
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

			echo '<li class="level-' . esc_attr( $level ) . '">';
			echo '<a href="' . esc_url( get_term_link( $product_category ) ) . '" class="level-' . esc_attr( $level ) . '-link ' . esc_attr( $link_class ) . '">' . $category_icon . esc_html( $product_category->name ) . '</a>';
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
	 * Filtering products on the category page and in the product archives
	 *
	 * @param $query
	 */
	public function taxonomy_filter_products( $query ) {
		global $wpdb;
		$fs_config = new FS_Config();

		// Если это админка или не главный запрос
		if ( $query->is_admin || ! $query->is_main_query() ) {
			return;
		}

		// If we are on the search page
		if ( $query->is_search ) {
			// Search by sku
			$search_query = str_replace( ' ', '%', get_search_query() );
			$sku_products = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='%s' AND meta_value LIKE '%s'", $fs_config->meta['sku'], '%' . $search_query . '%' ) );

			if ( $sku_products ) {
				$query->set( 's', '' );
				$query->set( 'post__in', $sku_products );
			}

			$query->set( 'post_type', 'product' );
		} elseif ( $query->is_tax( FS_Config::get_data( 'product_taxonomy' ) ) || $query->is_post_type_archive( FS_Config::get_data( 'post_type' ) ) ) {

			// отфильтровываем выключенные для показа товары в админке
			$meta_query [] = array(
				'relation' => 'AND',
				'exclude'  => array(
					'relation' => 'OR',
					array(
						'key'     => $fs_config->meta['exclude_archive'],
						'compare' => 'NOT EXISTS'
					),
					array(
						'key'     => $fs_config->meta['exclude_archive'],
						'compare' => '!=',
						'value'   => "1"
					)
				)
			);

			// Скрывать товары которых нет в наличии
			if ( fs_option( 'fs_not_aviable_hidden' ) ) {
				$meta_query [] = array(
					'key'     => $fs_config->meta['remaining_amount'],
					'compare' => '!=',
					'value'   => "0"
				);
			}
			$orderby   = array();
			$order     = '';
			$tax_query = array();
			$per_page  = get_option( "posts_per_page" );
			$arr_url   = urldecode( $_SERVER['QUERY_STRING'] );
			parse_str( $arr_url, $url );


			//Фильтрируем по значениям диапазона цен
			if ( isset( $url['price_start'] ) && isset( $url['price_end'] ) ) {

				$price_start                  = ! empty( $url['price_start'] ) ? (int) $url['price_start'] : 0;
				$price_end                    = ! empty( $url['price_end'] ) ? (int) $url['price_end'] : 99999999999999999;
				$meta_query['price_interval'] =
					array(
						'key'     => $fs_config->meta['price'],
						'value'   => array( $price_start, $price_end ),
						'compare' => 'BETWEEN',
						'type'    => 'NUMERIC',
					);
			}

			//Фильтрируем по к-во выводимых постов на странице
			if ( isset( $url['per_page'] ) ) {
				$per_page                                 = $url['per_page'];
				$_SESSION['fs_user_settings']['per_page'] = $per_page;
			}

			//Устанавливаем страницу пагинации
			if ( isset( $url['paged'] ) ) {
				$query->set( 'paged', $url['paged'] );
			}

			// фильтр товаров по признакам
			if ( ! empty( $url['filter_by'] ) ) {

				switch ( $url['filter_by'] ) {
					case 'action_price' :
						$meta_query['action_price'] = array(
							'key'     => $fs_config->meta['action_price'],
							'compare' => '>',
							'value'   => 0
						);
						$orderby['action_price']    = 'DESC';
						break;
				}

			}


			// Set the sort order in the default directory.
			if ( empty( $url['order_type'] ) && fs_option( 'fs_product_sort_by' ) ) {
				$url['order_type'] = fs_option( 'fs_product_sort_by' );
			}

			// Specify sorting rules
			if ( ! empty( $url['order_type'] ) ) {

				switch ( $url['order_type'] ) {
					case 'price_asc': //sort by price in ascending order
						$meta_query['price'] = array(
							'key'  => FS_Config::get_meta( 'price' ),
							'type' => 'DECIMAL'
						);
						$orderby['price']    = 'ASC';
						break;
					case 'price_desc': //sort by price in a falling order
						$meta_query['price'] = array(
							'key'  => FS_Config::get_meta( 'price' ),
							'type' => 'DECIMAL'
						);
						$orderby['price']    = 'DESC';
						break;
					case 'views_desc': //сортируем по просмотрам в спадающем порядке
						$meta_query['views'] = array( 'key' => 'views', 'type' => 'NUMERIC' );
						$orderby['views']    = 'DESC';
						break;
					case 'views_asc': //сортируем по просмотрам в спадающем порядке
						$meta_query['views'] = array( 'key' => 'views', 'type' => 'NUMERIC' );
						$orderby['views']    = 'ASC';
						break;
					case 'name_asc': //сортируем по названию по алфавиту
						$orderby['title'] = 'ASC';
						break;
					case 'name_desc': //сортируем по названию по алфавиту в обратном порядке
						$orderby['title'] = 'DESC';
						break;
					case 'date_desc':
						$orderby['date'] = 'DESC';
						break;
					case 'date_asc':
						$orderby['date'] = 'ASC';
						break;
					case 'action_price' :
						$orderby['action_price'] = 'DESC';
						break;

				}
			} else {
				if ( fs_option( 'fs_product_sort_by' ) == 'menu_order' ) {
					$orderby['menu_order'] = 'ASC';
				}
			}

			if ( ! empty( $_REQUEST['aviable'] ) ) {
				switch ( $_REQUEST['aviable'] ) {
					case 'aviable':
						$meta_query['aviable'] = array(
							'key'     => $fs_config->meta['remaining_amount'],
							'compare' => ' != ',
							'value'   => '0'
						);
						break;
					case 'not_available':
						$meta_query['aviable'] = array(
							'key'     => $fs_config->meta['remaining_amount'],
							'compare' => ' == ',
							'value'   => '0'
						);
						break;
				}

			}

			//Фильтруем по свойствам (атрибутам)
			if ( ! empty( $_REQUEST['attributes'] ) ) {
				$tax_query[] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'product-attributes',
						'field'    => 'id',
						'terms'    => array_values( $_REQUEST['attributes'] ),
						'operator' => 'IN'
					)
				);
			}

			// Фильтрируем по категориям
			if ( ! empty( $_REQUEST['categories'] ) ) {
				$tax_query[] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => FS_Config::get_data( 'product_taxonomy' ),
						'field'    => 'id',
						'terms'    => explode( ';', $_REQUEST['categories'] ),
						'operator' => 'IN'
					)
				);
			}


			$query->set( 'posts_per_page', $per_page );
			if ( ! empty( $meta_query ) ) {
				$query->set( 'meta_query', $meta_query );
			}
			if ( ! empty( $tax_query ) ) {
				$tax_query = array_merge( $query->tax_query->queries, $tax_query );
				$query->set( 'tax_query', $tax_query );
			}
			if ( ! empty( $orderby ) ) {
				$query->set( 'orderby', $orderby );
			}
			if ( ! empty( $order ) ) {
				$query->set( 'order', $order );
			}
		}

	}//end filter_curr_product()




	/**
	 * Add rewrite rules for terms
	 *
	 * @param $wp_rewrite
	 *
	 * @return array
	 */
	function taxonomy_rewrite_rules( $wp_rewrite ) {
		if ( ! fs_option( 'fs_disable_taxonomy_slug' ) ) {
			return;
		}

		$rules = array();
		$terms = get_terms( [ 'taxonomy' => $this->taxonomy_name, 'hide_empty' => false ] );

		if ( fs_option( 'fs_disable_taxonomy_slug' ) ) {
			foreach ( FS_Config::get_languages() as $key => $language ) {
				$meta_key = $language['locale'] != FS_Config::default_locale() ? '_seo_slug__' . $language['locale'] : '_seo_slug';
				foreach ( $terms as $term ) {
					$localize_slug = get_term_meta( $term->term_id, $meta_key, 1 );
					if ( $language['locale'] == FS_Config::default_locale() ) {
						$rules[ $term->slug . '/?$' ]            = 'index.php?' . $term->taxonomy . '=' . $term->slug;
						$rules[ $term->slug . '/page/(\d+)/?$' ] = 'index.php?' . $term->taxonomy . '=' . $term->slug . '&paged=$matches[1]';
						$rules[ $term->slug . '/page-(\d+)/?$' ] = 'index.php?' . $term->taxonomy . '=' . $term->slug . '&paged=$matches[1]';
					} elseif ( $localize_slug ) {
						$rules[ $localize_slug . '/?$' ]            = 'index.php?' . $term->taxonomy . '=' . $term->slug;
						$rules[ $localize_slug . '/page/(\d+)/?$' ] = 'index.php?' . $term->taxonomy . '=' . $term->slug . '&paged=$matches[1]';
						$rules[ $localize_slug . '/page-(\d+)/?$' ] = 'index.php?' . $term->taxonomy . '=' . $term->slug . '&paged=$matches[1]';
					}
				}
			}

		}

		$wp_rewrite->rules = $rules + $wp_rewrite->rules;

		return $wp_rewrite->rules;
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

		$meta_key = get_locale() != FS_Config::default_locale() ? '_seo_slug__' . get_locale() : '_seo_slug';

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
	 * Redirect to a localized url
	 */
	function redirect_to_localize_url() {
		// Leave if the request came not from the product category
		if ( ! is_tax( $this->taxonomy_name ) ) {
			return;
		}

		// Exit if slug localization is disabled in the admin panel.
		if ( fs_option( 'fs_localize_slug' ) !== '1' ) {
			return;
		}

		$current_link = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$term_id      = get_queried_object_id();
		$term_link    = get_term_link( $term_id );
		if ( get_query_var( 'paged' ) && get_query_var( 'paged' ) > 1 ) {
			$taxonomy_pagination_structure = apply_filters( 'fs_taxonomy_pagination_structure', $this->taxonomy_pagination_structure );
			$term_link                     = $term_link . sprintf( $taxonomy_pagination_structure, get_query_var( 'paged' ) ) . '/';


		}
		if ( $_SERVER['QUERY_STRING'] && ! isset( $_GET['q'] ) ) {
			$term_link .= '?' . $_SERVER['QUERY_STRING'];
		}
		if ( $current_link != $term_link ) {
			wp_safe_redirect( $term_link );
			exit;
		}

		return;
	}

	/**
	 * Micro-marking of product category
	 */
	function product_category_microdata() {
		if ( is_admin() || ! is_tax( FS_Config::get_data( 'product_taxonomy' ) ) ) {
			return;
		}

		global $wp_query, $wpdb;

		// Get the current product category term object
		$term = get_queried_object();

		$price_field = FS_Config::get_meta( 'price' );

		# Get ALL related products prices related to a specific product category
		$results = $wpdb->get_col( " SELECT pm.meta_value FROM {$wpdb->prefix}term_relationships as tr INNER JOIN {$wpdb->prefix}term_taxonomy as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN {$wpdb->prefix}terms as t ON tr.term_taxonomy_id = t.term_id INNER JOIN {$wpdb->prefix}postmeta as pm ON tr.object_id = pm.post_id WHERE tt.taxonomy LIKE 'catalog' AND t.term_id = {$term->term_id} AND pm.meta_key = '$price_field' " );

		// Sorting prices numerically
		sort( $results, SORT_NUMERIC );

		// Get the min and max prices
		$min = current( $results );
		$max = end( $results );

		$schema = array(
			"@context" => "https://schema.org",
			"@type"    => "Product",
			"name"     => single_term_title( '', 0 ),
			"offers"   => [
				"@type"         => "AggregateOffer",
				"lowPrice"      => floatval( $min ),
				"highPrice"     => floatval( $max ),
				"offerCount"    => intval( $wp_query->found_posts ),
				"priceCurrency" => "UAH"

			],
			"url"      => get_term_link( get_queried_object_id() )
		);

		if ( ! empty( $schema ) ) {
			echo ' <script type="application/ld+json">';
			echo json_encode( $schema );
			echo ' </script>';
		}
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
						'type' => 'editor',
						'args' => array()
					),
					'_seo_slug'        => array(
						'name' => __( 'SEO slug', 'f-shop' ),
						'type' => 'text',
						'args' => array()
					),
					'_seo_title'       => array(
						'name' => __( 'SEO title', 'f-shop' ),
						'type' => 'text',
						'args' => array()
					),
					'_seo_description' => array(
						'name' => __( 'SEO description', 'f-shop' ),
						'type' => 'textarea',
						'args' => array()
					),
					'_thumbnail_id'    => array(
						'name' => __( 'Thumbnail', 'f-shop' ),
						'type' => 'image',
						'args' => array()
					),
					'_icon_id'         => array(
						'name' => __( 'Icon', 'f-shop' ),
						'type' => 'image',
						'args' => array()
					)
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
					'_thumbnail_id'       => array(
						'name' => __( 'Thumbnail', 'f-shop' ),
						'type' => 'image',
						'args' => array()
					),
					'_fs_delivery_cost'   => array(
						'name' => __( 'Shipping Cost in Base Currency', 'f-shop' ),
						'type' => 'number',
						'args' => array( 'style' => 'width:72px;', 'step' => 0.01 )
					),
					'_fs_disable_fields'  => array(
						'name' => __( 'Fields to disable when choosing this delivery method', 'f-shop' ),
						'type' => 'select',
						'args' => array(
							'values'   => $checkout_fields,
							'multiple' => true
						)
					),
					'_fs_required_fields' => array(
						'name' => __( 'Required fields when choosing this delivery method', 'f-shop' ),
						'type' => 'select',
						'args' => array(
							'values'   => $checkout_fields,
							'multiple' => true
						)
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
					'discount_type' => array(
						'name' => __( 'DiscountType', 'f-shop' ),
						'type' => 'select',
						'args' => array(
							'values' => array(
								'product' => __( 'Скидка применяется только к товарам', 'f-shop' )


							)
						)
					),

					'discount_categories' => array(
						'name' => __( 'Категории на которые распространяется скидка', 'f-shop' ),
						'type' => 'dropdown_categories',
						'rule' => [ 'discount_where_is', '=', 'category' ],
						'args' => array(
							'taxonomy' => FS_Config::get_data( 'product_taxonomy' ),
							'multiple' => true,

						)
					),
					'discount_brands'     => array(
						'name' => __( 'Бренды на которые распространяется скидка', 'f-shop' ),
						'type' => 'dropdown_categories',
						'rule' => [ 'discount_where_is', '=', 'category' ],
						'args' => array(
							'taxonomy' => FS_Config::get_data( 'brand_taxonomy' ),
							'multiple' => true,

						)
					),
					'discount_features'   => array(
						'name' => __( 'Свойства товаров на которые распространяется скидка', 'f-shop' ),
						'type' => 'dropdown_categories',
						'args' => array(
							'taxonomy' => FS_Config::get_data( 'features_taxonomy' ),
							'multiple' => true,

						)
					),
					'discount_amount'     => array(
						'name'     => __( 'Discount amount', 'f-shop' ),
						'type'     => 'text',
						'args'     => array(),
						'required' => true,
						'help'     => 'Мужно указать фиксированную сумму, например "20" или в процентах "10%"'
					)
				)

		);

		return apply_filters( 'fs_taxonomy_fields', $fields );
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
			FS_Config::get_data('product_taxonomy')      => array(
				'object_type'        => FS_Config::get_data('post_type'),
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
			FS_Config::get_data('product_pay_taxonomy')   => array(
				'object_type'        => FS_Config::get_data('post_type'),
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
			FS_Config::get_data('product_del_taxonomy')   => array(
				'object_type'        => FS_Config::get_data('post_type'),
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
			FS_Config::get_data('features_taxonomy')     => array(
				'object_type'        => FS_Config::get_data('post_type'),
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
				'show_admin_column'  => true,
				'hierarchical'       => true,
				'show_in_quick_edit' => true
			),
			FS_Config::get_data('brand_taxonomy')         => array(
				'object_type'        => FS_Config::get_data('post_type'),
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
				'show_admin_column'  => true,
				'hierarchical'       => true,
				'show_in_quick_edit' => true
			),
			FS_Config::get_data('product_taxes_taxonomy') => array(
				'object_type'        => FS_Config::get_data('post_type'),
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
			$taxonomies['fs-currencies'] = array(
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

		// создание дополнительных полей на странице добавления и редактирования таксономии
		if ( $this->shop_taxonomies() ) {
			foreach ( $this->shop_taxonomies() as $key => $taxonomy ) {
				if ( in_array( $key, array( 'product-attributes' ) ) ) {
					continue;
				}
				// поля таксономии категорий товара
				add_action( "{$key}_edit_form_fields", array( $this, 'edit_taxonomy_fields' ), 10, 2 );
				add_action( "{$key}_add_form_fields", array( $this, 'add_taxonomy_fields' ), 10, 1 );
				add_action( "create_{$key}", array( $this, 'save_taxonomy_fields' ), 10, 2 );
				add_action( "edited_{$key}", array( $this, 'save_taxonomy_fields' ), 10, 2 );
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
				$form->render_field( $name, $field['type'], $field['args'] );
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
	 */
	function save_taxonomy_fields( $term_id ) {
		$term     = get_term( $term_id );
		$taxonomy = $term->taxonomy;
		$fields   = self::get_taxonomy_fields();

		$multi_lang = false;
		$screen     = get_current_screen();
		$lang       = $_POST['wpglobus_language'] ? $_POST['wpglobus_language'] : $_COOKIE['wpglobus_language'];

		if ( fs_option( 'fs_multi_language_support' )
		     && ( is_array( FS_Config::get_languages() ) && count( FS_Config::get_languages() ) )
		     && $screen->id == 'edit-catalog'
		) {
			$multi_lang = true;
		}


		if ( count( $fields[ $taxonomy ] ) ) {
			foreach ( $fields[ $taxonomy ] as $name => $field ) {

				if ( $multi_lang ) {
					foreach ( FS_Config::get_languages() as $key => $language ) {
						if ( $language['locale'] == FS_Config::default_locale() ) {
							$meta_key = $name;
						} else {
							$meta_key = $name . '__' . $language['locale'];
						}

						// Если включена локализация ссылок и пустое значение для локализированого слага, делаем это автоматически
						if ( isset( $_POST['wpglobus_language'] ) ) {
							$post_name = $_POST['name'];
						} else {
							$post_name = $_POST[ 'name_' . $key ];
						}


						if ( fs_option( 'fs_localize_slug' )
						     && $name == '_seo_slug' && $post_name != ''
						     && $_POST[ $meta_key ] == ''
						     && $lang == $key ) {
							$_POST[ $meta_key ] = fs_convert_cyr_name( $post_name );
						}

						if ( isset( $_POST[ $meta_key ] ) && $_POST[ $meta_key ] != '' ) {
							update_term_meta( $term_id, $meta_key, $_POST[ $meta_key ] );
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

		// Обновляем правил ЧПУ
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
                <label for="fs_att_compare"><?php esc_html_e( 'Use the number of purchased goods to compare with this attribute.', 'f-shop' ); ?></label>
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
	 * Returns the attribute table for the admin
	 *
	 * @param int $post_id ID поста
	 *
	 * @return false|string
	 */
	public static function fs_get_admin_product_attributes_table( $post_id = 0 ) {
		$post_id  = isset($_POST['post_id']) ?  (int) $_POST['post_id'] : $post_id;
		$is_ajax  = isset($_POST['is_ajax']) && (int) $_POST['is_ajax'] === 1 ?: false;
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
                        <td data-attribute-name="<?php echo esc_attr($parent->term_id); ?>"><?php echo esc_html( apply_filters( 'the_title', $parent->name ) ) ?></td>
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

}