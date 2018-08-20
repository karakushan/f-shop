<?php

namespace FS;
/**
 * Class FS_Filters
 * @package FS
 */
class FS_Filters {
	private $exclude = array(
		'fs_filter',
		'price_start',
		'price_end',
		'sort_custom'
	);

	function __construct() {
		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'exclude_posts' ), 10, 1 );
			add_action( 'pre_get_posts', array( $this, 'filter_curr_product' ), 10, 1 );
		}
		if ( is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'filter_products_admin' ), 12, 1 );
		}

		add_shortcode( 'fs_range_slider', array( $this, 'range_slider' ) );

		// фильтр по категориям товаров в админке
		add_action( 'restrict_manage_posts', array( $this, 'category_filter_admin' ) );

		add_action( 'template_redirect', array( $this, 'redirect_per_page' ) );
	}

	function filter_products_admin( $query ) {
		global $pagenow, $fs_config;
		if ( $query->get( 'post_type' ) == $fs_config->data['post_type'] && $pagenow == 'edit.php' ) {
			if ( ! empty( $_GET['s'] ) ) {
				$query->set( 'meta_query', [
					[
						'key'     => $fs_config->meta['sku'],
						'value'   => $_GET['s'],
						'compare' => '='
					]
				] );
				add_filter( 'get_meta_sql', function ( $sql ) {
					static $nr = 0;
					if ( 0 != $nr ++ ) {
						return $sql;
					}
					$sql['where'] = mb_eregi_replace( '^ AND', ' OR', $sql['where'] );

					return $sql;
				} );
			}
			if ( ! empty( $_GET['orderby'] ) ) {
				switch ( $_GET['orderby'] ) {
					//	сортируем по цене
					case "fs_price":
						$query->set( 'orderby', 'meta_value_num' );
						$query->set( 'meta_key', $fs_config->meta['price'] );
						$query->set( 'order', (string) $_GET['order'] );
						break;
				}
			} else {
				$query->set( 'orderby', array( 'menu_order' => 'ASC' ) );
			}
		} elseif ( $query->get( 'post_type' ) == $fs_config->data['post_type_orders'] && $pagenow == 'edit.php' ) {
			$query->set( 'orderby', array( 'date' => 'DESC' ) );
		}

	}

	/**
	 * Исключает некоторые товары из общего архива и категорий
	 *
	 * @param $query
	 */
	function exclude_posts( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( $query->is_archive || $query->is_tax ) {
			global $fs_config;
			$meta_query                    = [];
			$meta_query['exclude_archive'] =
				array(
					'key'     => $fs_config->meta['exclude_archive'],
					'compare' => 'NOT EXISTS',

				);
			if ( ! empty( $meta_query ) ) {
				$query->set( 'meta_query', $meta_query );
			}
		}
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
			'show_option_all' => __( 'Product category', 'fast-shop' ),
			'taxonomy'        => 'catalog',
			'id'              => 'fs-category-filter',
			'hide_empty'      => false,
			'value_field'     => 'slug',
			'hierarchical'    => true,
			'selected'        => $get_parr,
			'name'            => 'catalog'
		) );
	}


	public function filter_curr_product( $query ) {
		global $fs_config, $wpdb;

		// Если это админка или не главный запрос
		if ( $query->is_admin || ! $query->is_main_query() ) {
			return $query;
		}


		// If we are on the search page
		if ( $query->is_search ) {
			if ( ! empty( $_GET['s'] ) ) {
				// Search by sku
				$query->set( 'meta_query', [
					[
						'key'     => $fs_config->meta['sku'],
						'value'   => $_GET['s'],
						'compare' => '='
					]
				] );
				add_filter( 'get_meta_sql', function ( $sql ) {
					static $nr = 0;
					if ( 0 != $nr ++ ) {
						return $sql;
					}
					$sql['where'] = mb_eregi_replace( '^ AND', ' OR', $sql['where'] );

					return $sql;
				} );

			}
			$query->set( 'post_type', 'product' );

		} elseif ( $query->is_tax ) {

			$meta_query = array();
			$orderby    = array();
			$order      = '';
			$tax_query  = array();
			$per_page   = get_option( "posts_per_page" );
			$arr_url    = urldecode( $_SERVER['QUERY_STRING'] );
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
							'compare' => '> ',
							'value'   => 0
						);
						$orderby['action_price']    = 'DESC';
						break;
				}

			}

			// выполняем сортировку
			if ( isset( $url['order_type'] ) ) {

				switch ( $url['order_type'] ) {
					case 'price_asc': //сортируем по цене в возрастающем порядке
						$meta_query['price']       = array( 'key' => $fs_config->meta['price'] );
						$orderby['meta_value_num'] = 'ASC';
						break;
					case 'price_desc': //сортируем по цене в спадающем порядке
						$meta_query['price']       = array( 'key' => $fs_config->meta['price'] );
						$orderby['meta_value_num'] = 'DESC';
						break;
					case 'views_desc': //сортируем по просмотрам в спадающем порядке
						$meta_query['views'] = array( 'key' => 'views' );
						$orderby['views']    = 'DESC';
						break;
					case 'views_asc': //сортируем по просмотрам в спадающем порядке
						$meta_query['views'] = array( 'key' => 'views' );
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

				$orderby['menu_order'] = 'ASC';
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
			if ( ! empty( $_REQUEST['filter_by'] ) ) {
				switch ( $_REQUEST['filter_by'] ) {
					case 'action_price';
						$meta_query['action_price'] = array(
							'key'     => $fs_config->meta['action_price'],
							'compare' => '> ',
							'value'   => 0
						);
						break;
				}
			}

			//Фильтруем по свойствам (атрибутам)
			if ( ! empty( $_REQUEST['attributes'] ) ) {
				$tax_query[] = array(
					'taxonomy' => 'product-attributes',
					'field'    => 'id',
					'terms'    => array_values( $_REQUEST['attributes'] ),
					'operator' => 'IN'
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
			echo ' <select name="attributes" data-fs-action="filter"><option value="' . remove_query_arg( array(
					'attributes'
				) ) . '"> ' . $option_default . '</option> ';
			foreach ( $fs_atributes as $key => $att ) {
				$redirect_url = add_query_arg( array(
					'fs_filter'  => wp_create_nonce( 'fast-shop' ),
					'attributes' => array( $att->slug => $att->term_id )
				) );
				echo ' <option  value="' . esc_url( $redirect_url ) . '" ' . selected( $url['attributes'][ $att->slug ], $att->term_id, 0 ) . '> ' . $att->name . '</option> ';
			}
			echo '</select> ';
		}
		if ( $type == 'list' ) {
			echo ' <ul>';
			foreach ( $fs_atributes as $key => $att ) {
				$redirect_url = add_query_arg( array(
					'fs_filter'  => wp_create_nonce( 'fast-shop' ),
					'attributes' => array( $att->slug => $att->term_id )
				) );

				echo ' <li><a href="' . esc_url( $redirect_url ) . '" data-fs-action="filter"> ' . $att->name . '</a></li> ';
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
		$nonce = wp_create_nonce( 'fast-shop' );

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
					'name' => __( 'recently added', 'fast-shop' )// недавно добавленные
				),
				'date_asc'   => array(
					'name' => __( 'later added', 'fast-shop' ) // давно добавленные
				),
				'price_asc'  => array(
					'name' => __( 'from cheap to expensive', 'fast-shop' ) // от дешевых к дорогим
				),
				'price_desc' => array(
					'name' => __( 'from expensive to cheap', 'fast-shop' ) // от дорогих к дешевым
				),
				'name_asc'   => array(
					'name' => __( 'by title A to Z', 'fast-shop' ) // по названию от А до Я
				),
				'name_desc'  => array(
					'name' => __( 'by title Z to A', 'fast-shop' ) // по названию от Я до А
				)
			)
		) );

		$order_type_get = ! empty( $_GET['order_type'] ) ? $_GET['order_type'] : '';

		if ( count( $attr['filters'] ) ) {
			echo ' <select name="order_type"  class="' . esc_attr( $attr['class'] ) . '" data-fs-action="filter"> ';
			foreach ( $attr['filters'] as $key => $order_type ) {
				$redirect_url = add_query_arg( array(
					'fs_filter'  => wp_create_nonce( 'fast-shop' ),
					'order_type' => $key
				) );
				echo ' <option value="' . esc_url( $redirect_url ) . '" ' . selected( $key, $order_type_get, 0 ) . '> ' . esc_html( $order_type['name'] ) . ' </option> ';
			}
			echo '</select> ';
		}

		return;
	}

}