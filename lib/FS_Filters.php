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


		add_action( 'pre_get_posts', array( $this, 'filter_curr_product' ) );
		add_action( 'pre_get_posts', array( $this, 'search_query' ) );
		add_action( 'pre_get_posts', array( $this, 'search_page' ) );


		add_shortcode( 'fs_range_slider', array( $this, 'range_slider' ) );

		// фильтр по категориям товаров в админке
		add_action( 'restrict_manage_posts', array( $this, 'category_filter_admin' ) );

		add_action( 'template_redirect', array( $this, 'redirect_per_page' ) );

	}


	/**
	 * Добавляет возможность поиска по дополнительным полям
	 * отфильтровывает не товары
	 *
	 * @param $query
	 */
	function search_query( $query ) {

		if ( ! is_admin() && $query->is_search && $query->is_main_query() ) {
			global $fs_config;
			$search_term = filter_input( INPUT_GET, 's', FILTER_SANITIZE_NUMBER_INT ) ?: 0;

			if ( empty( $search_term ) ) {
				return $query;
			}

			$query->set( 'post_type', 'product' );
			// включаем поиск по артикулу
			$query->set( 'meta_query', [
				[
					'key'     => $fs_config->meta['product_article'],
					'value'   => trim( $search_term ),
					'compare' => 'LIKE'
				]
			] );
			// включаем возможность искать по нескольким параметрам
			add_filter( 'get_meta_sql', function ( $sql ) {
				global $wpdb;

				static $nr = 0;
				if ( 0 != $nr ++ ) {
					return $sql;
				}

				$sql['where'] = mb_eregi_replace( '^ AND', ' OR', $sql['where'] );

				return $sql;

			} );
		}

		return $query;
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
		if ( is_admin() ) {
			return;
		}

		if ( ! isset( $_REQUEST['fs_filter'] ) || ! $query->is_main_query() ) {
			return $query;
		}

		if ( $query->is_search ) {
			$query->set( 'post_type', 'product' );
		}


		if ( ! wp_verify_nonce( $_REQUEST['fs_filter'], 'fast-shop' ) ) {
			exit( 'ошибка безопасности' );
		}

		$config     = new FS_Config;
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
					'key'     => $config->meta['price'],
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

		// выполняем сортировку
		if ( isset( $url['order_type'] ) ) {

			switch ( $url['order_type'] ) {
				case 'price_asc': //сортируем по цене в возрастающем порядке
					$meta_query['price'] = array( 'key' => $config->meta['price'] );
					$orderby['price']    = 'ASC';
					break;
				case 'price_desc': //сортируем по цене в спадающем порядке
					$meta_query['price'] = array( 'key' => $config->meta['price'] );
					$orderby['price']    = 'DESC';
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
					$meta_query['action_price'] = array( 'key' => $config->meta['action_price'] );
					$orderby['action_price']    = 'DESC';
					break;


			}
		}

		if ( ! empty( $_REQUEST['aviable'] ) ) {
			switch ( $_REQUEST['aviable'] ) {
				case 'aviable':
					$meta_query['aviable'] = array(
						'key'     => $config->meta['remaining_amount'],
						'compare' => '!=',
						'value'   => '0'
					);
					break;
				case 'not_available':
					$meta_query['aviable'] = array(
						'key'     => $config->meta['remaining_amount'],
						'compare' => '==',
						'value'   => '0'
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

		//Фильтруем по производителям
		if ( ! empty( $_REQUEST['tax-manufacturers'] ) ) {
			$manufacturers = array();
			if ( is_array( $_REQUEST['tax-manufacturers'] ) ) {
				$manufacturers = array_values( $_REQUEST['tax-manufacturers'] );
			} else {
				$manufacturers[] = (int) $_REQUEST['tax-manufacturers'];

			}
			$tax_query[] = array(
				'taxonomy' => 'manufacturers',
				'field'    => 'id',
				'terms'    => $manufacturers,
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

		return $query;
	}//end filter_curr_product()


	/**
	 * Оставляет в результатах поиска только товары
	 *
	 * @param $query
	 */
	public function search_page( $query ) {
		if ( is_admin() ) {
			return;
		}
		global $fs_config;
		if ( $query->is_search ) {
			$query->set( 'post_type', $fs_config->data['post_type'] );
		}

		return $query;
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
			echo '<select name="attributes" data-fs-action="filter"><option value="' . remove_query_arg( array(
					'attributes'
				) ) . '">' . $option_default . '</option>';
			foreach ( $fs_atributes as $key => $att ) {
				$redirect_url = add_query_arg( array(
					'fs_filter'  => wp_create_nonce( 'fast-shop' ),
					'attributes' => array( $att->slug => $att->term_id )
				) );
				echo '<option  value="' . esc_url( $redirect_url ) . '" ' . selected( $url['attributes'][ $att->slug ], $att->term_id, 0 ) . '>' . $att->name . '</option>';
			}
			echo '</select>';
		}
		if ( $type == 'list' ) {
			echo '<ul>';
			foreach ( $fs_atributes as $key => $att ) {
				$redirect_url = add_query_arg( array(
					'fs_filter'  => wp_create_nonce( 'fast-shop' ),
					'attributes' => array( $att->slug => $att->term_id )
				) );

				echo '<li><a href="' . esc_url( $redirect_url ) . '" data-fs-action="filter" >' . $att->name . '</a></li>';
			}
			echo '</ul>';
		}
	}//end attr_group_filter()

	/**
	 * метод позволяет вывести поле типа select  для изменения к-ва выводимых постов на странице
	 *
	 * @param  [array] $post_count массив к-ва выводимых записей например array(10,20,30,40)
	 *
	 * @return [type]             html код селекта с опциями
	 */
	public function posts_per_page_filter( $post_count = array(), $attr ) {
		$req    = isset( $_REQUEST['per_page'] ) ? $_REQUEST['per_page'] : get_option( "posts_per_page" );
		$nonce  = wp_create_nonce( 'fast-shop' );
		$attr   = fs_parse_attr( $attr );
		$filter = '<select name="post_count" ' . $attr . ' onchange="document.location=this.options[this.selectedIndex].value">';
		if ( $post_count ) {
			foreach ( $post_count as $key => $count ) {
				$filter .= '<option value="' . add_query_arg( array(
						"fs_filter" => $nonce,
						"per_page"  => $count,
						'paged'     => 1
					) ) . '" ' . selected( $count, $req, false ) . '>' . $count . '</option>';
			}
		}

		$filter .= '</select>';

		return $filter;
	}

}