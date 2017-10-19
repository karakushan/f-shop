<?php
/**
 * Возвращает массив атрибутов конкретного товара
 * @return array массив атрибутов
 */
function fs_get_attributes_group( $product_id = 0 ) {
	global $post;
	$product_id = $product_id == 0 ? $post->ID : $product_id;
	$terms      = wp_get_object_terms( $product_id, 'product-attributes' );
	$parents    = array();
	foreach ( $terms as $key => $term ) {
		$attr_type = get_term_meta( $term->term_id, 'fs_att_type', 1 );
		$attr_type = empty( $attr_type ) ? 'text' : $attr_type;
		if ( $attr_type == 'text' ) {
			$attr_value = $term->name;
		} else {
			$attr_value = get_term_meta( $term->term_id, 'fs_att_' . $attr_type . '_value', 1 );
		}
		$parents[ $term->parent ][ $term->term_id ] = array(
			'name'  => $term->name,
			'type'  => $attr_type,
			'value' => $attr_value
		);
	}

	return $parents;
}


/**
 * получает заданное свойство товара с вложенными свойтвами
 * @return array
 */
function fs_get_attribute( $attr_id, $product_id = 0, $args = array() ) {
	$args       = wp_parse_args( $args, array( 'return' => 1 ) );
	$attributes = fs_get_attributes_group( $product_id );

	if ( isset( $attributes[ $attr_id ] ) ) {
		if ( $args['return'] ) {
			$first_attr = array_shift( $attributes[ $attr_id ] );
			$atts       = $first_attr['value'];
		} else {
			$atts = array(
				'name'     => get_term_field( 'name', $attr_id ),
				'children' => $attributes[ $attr_id ]
			);
		}
	} else {
		if ( $args['return'] ) {
			$atts = '-';
		} else {
			$atts = array(
				'name'     => get_term_field( 'name', $attr_id ),
				'children' => array()
			);
		}

	}

	return $atts;
}

/**
 * Получает термины постов выводимых на текущей странице, нужно указать айди родительского термина
 *
 * @param $parent_term_id
 *
 * @return массив объектов поста
 */
function fs_current_screen_attributes( $parent_term_id ) {
	global $wp_query;
	$posts = new WP_Query( array(
		'posts_per_page' => - 1,
		'fields'         => 'ids',
		'tax_query'      => array(
			array(
				'taxonomy' => 'catalog',
				'field'    => 'id',
				'terms'    => $wp_query->queried_object_id
			)
		)
	) );

	$ids       = $posts->posts;
	$obj_terms = array();
	$terms     = wp_get_object_terms( $ids, 'product-attributes' );
	foreach ( $terms as $key => $term ) {
		if ( $term->parent != $parent_term_id ) {
			continue;
		}
		$obj_terms[] = $term;
	}
	wp_reset_postdata();

	return $obj_terms;
}

// select фильтр сортировки по таксономии
function fs_taxonomy_select_filter( $taxonomy = 'catalog', $first_option = 'сделайте выбор' ) {
	$manufacturers = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
	$filter        = '';
	if ( $manufacturers ) {
		$filter .= '<select name="tax-' . $taxonomy . '" data-fs-action="filter">';
		$filter .= '<option value="' . remove_query_arg( array( 'tax-' . $taxonomy ) ) . '">' . $first_option . '</option>';
		foreach ( $manufacturers as $key => $manufacturer ) {
			if ( isset( $_GET[ 'tax-' . $taxonomy ] ) ) {
				$selected = selected( $manufacturer->term_id, $_GET[ 'tax-' . $taxonomy ], 0 );
			} else {
				$selected = '';
			}
			$filter .= '<option value="' . add_query_arg( array(
					'fs_filter'        => wp_create_nonce( 'fast-shop' ),
					'tax-' . $taxonomy => $manufacturer->term_id
				) ) . '" ' . $selected . '>' . $manufacturer->name . '</option>';
		}
		$filter .= '</select>';
	}

	return $filter;
}


/**
 * выводит фильтр сортировки по разным параметрам
 *
 * @param  [type] $attr          дополниетльные атрибуты html тега
 *
 * @return [type]               выводит html элемент типа select
 */
function fs_types_sort_filter( $attr = array() ) {
	$filter      = '';
	$order_types = array(
		'date_desc'  => array(
			'name' => __( 'First new', 'fast-shop' )
		),
		'date_asc'   => array(
			'name' => __( 'First old ones', 'fast-shop' )
		),
		'price_asc'  => array(
			'name' => __( 'Price low to high', 'fast-shop' )
		),
		'price_desc' => array(
			'name' => __( 'Price high to low', 'fast-shop' )
		),
		'name_asc'   => array(
			'name' => __( 'Name A to Z', 'fast-shop' )
		),
		'name_desc'  => array(
			'name' => __( 'Name Z to A', 'fast-shop' )
		)
	);

	$attr = fs_parse_attr( $attr );

	if ( $order_types ) {
		$filter .= '<select name="order_type" data-fs-action="filter" ' . $attr . '>';

		foreach ( $order_types as $key => $order_type ) {
			if ( isset( $_GET['order_type'] ) ) {
				$selected = selected( $key, $_GET['order_type'], 0 );
			} else {
				$selected = '';
			}
			$filter .= '<option value="' . add_query_arg( array(
					'fs_filter'  => wp_create_nonce( 'fast-shop' ),
					'order_type' => $key
				) ) . '" ' . $selected . '>' . $order_type['name'] . '</option>';
		}
		$filter .= '</select>';
	}

	echo $filter;
}

// селект фильтр для фильтрования товаров по наличию
function fs_aviable_select_filter( $first_option = 'сделайте выбор' ) {
	$filter        = '';
	$aviable_types = array(
		'aviable'       => array( 'name' => __( 'in stock', 'fast-shop' ) ),
		'not_available' => array( 'name' => __( 'not available', 'fast-shop' ) ),
	);
	if ( $aviable_types ) {
		$filter .= '<select name="order_type" data-fs-action="filter">';
		$filter .= '<option value="' . remove_query_arg( array( 'aviable' ) ) . '">' . $first_option . '</option>';
		foreach ( $aviable_types as $key => $order_type ) {
			if ( isset( $_GET['aviable'] ) ) {
				$selected = selected( $key, $_GET['aviable'], 0 );
			} else {
				$selected = '';
			}
			$filter .= '<option value="' . add_query_arg( array(
					'fs_filter' => wp_create_nonce( 'fast-shop' ),
					'aviable'   => $key
				) ) . '" ' . $selected . '>' . $order_type['name'] . '</option>';
		}
		$filter .= '</select>';
	}

	return $filter;
}

/**
 * @param array $interval массив содержащий  интервалы выводимых товаров на странице
 *
 * @return  выводит переключатель к-ва товаров
 */
function fs_per_page_filter( $interval = array(), $attr = array() ) {
	$filters = new FS\FS_Filters;
	if ( empty( $interval ) ) {
		$interval = array( 12, 24, 36, 48, 60, 100 );
	}
	$page_filter = $filters->posts_per_page_filter( $interval, $attr );
	echo $page_filter;
}

/**
 * Функция выводит фильтр по атрибутам товара
 *
 * @param $group_id
 * @param array $args
 */
function fs_attr_filter( $group_id, $args = array() ) {
	$default = array(
		'redirect'        => true,
		'container'       => 'ul',
		'container_class' => 'listCheck',
		'container_id'    => 'listCheck-' . $group_id,
		'input_class'     => 'checkStyle',
		'label_class'     => 'checkLabel'
	);
	$args    = wp_parse_args( $args, $default );
	$terms   = get_terms( array(
		'taxonomy'   => 'product-attributes',
		'hide_empty' => false,
		'parent'     => $group_id
	) );
	$arr_url = urldecode( $_SERVER['QUERY_STRING'] );
	parse_str( $arr_url, $url );

	if ( $terms ) {
		echo '<' . $args['container'] . ' class="' . sanitize_html_class( $args['container_class'] ) . '"  id="' . sanitize_html_class( $args['container_id'] ) . '">';
		foreach ( $terms as $key => $term ) {
			$product_attributes = isset( $_GET['attributes'][ $term->slug ] ) ? $_GET['attributes'][ $term->slug ] : '';
			if ( $args['container'] == 'ul' ) {
				echo '<li>';
			} else {
				echo '<div>';
			}

			if ( ! empty( $url['attributes'] ) ) {
				$attributes = array_merge( $url['attributes'], array( $term->slug => $term->term_id ) );
			} else {
				$attributes = array( $term->slug => $term->term_id );
			}

			$value = add_query_arg( array(
				'fs_filter'  => wp_create_nonce( 'fast-shop' ),
				'attributes' => $attributes
			), $_SERVER['REQUEST_URI'] );

			unset( $attributes[ $term->slug ] );
			$remove_attr = add_query_arg( array(
				'fs_filter'  => wp_create_nonce( 'fast-shop' ),
				'attributes' => $attributes
			), $_SERVER['REQUEST_URI'] );;

			if ( ! $args['redirect'] ) {
				$value = $term->term_id;
			}
			$input_class = 'class="' . sanitize_html_class( $args['input_class'] ) . '"';
			$label_class = 'class="' . sanitize_html_class( $args['label_class'] ) . '"';
			echo '<input type="checkbox" ' . $input_class . ' data-fs-action="filter" data-fs-redirect="' . $remove_attr . '" name="attributes[' . $term->slug . ']" value="' . $value . '"  ' . checked( $term->term_id, $product_attributes, 0 ) . ' id="check-' . $term->slug . '">';
			echo '<label for="check-' . $term->slug . '"  ' . $label_class . '>' . $term->name . '</label >';
			if ( $args['container'] == 'ul' ) {
				echo '</li>';
			} else {
				echo '</div>';
			}
		}
		echo '</' . $args['container'] . '>';
	}
}

function fs_attr_change( $required_atts = array() ) {
	global $post;
	$product_id = $post->ID;
	echo '<div class="fs-attr-group-wrapper" id="fs-attr-change"><div class="fs-attr-group-desc">Выберите необходимые вам свойства</div>';
	$atts = fs_get_attributes_group();
	foreach ( $required_atts as $required_att ) {
		if ( ! empty( $atts[ $required_att ] ) ) {
			$group_name = get_term_field( 'name', $required_att );
			echo '<div class="fs-attr-group">';
			echo '<div class="fs-attr-group-name">' . $group_name . '</div>';
			foreach ( $atts[ $required_att ] as $id => $att ) {
				switch ( $att['type'] ) {
					case "color":
						echo '<span class="fs-attr-group-color">';
						echo '<input type="radio"  name="group-' . $required_att . '" data-product-id="' . $product_id . '" data-target="#group-' . $required_att . '" data-action="change-attr" id="attr-' . $id . '" value="' . $id . '">';
						echo '<label for="attr-' . $id . '" style="background-color:' . $att['value'] . '"><span class="checkbox"></span></label>';
						echo '</span>';
						break;
					default:
						echo '<div class="fs-attr-group-text">';
						echo '<input type="radio"  name="group-' . $required_att . '" data-product-id="' . $product_id . '"  data-target="#group-' . $required_att . '" id="attr-' . $id . '" data-action="change-attr" value="' . $id . '">';
						echo '<label for="attr-' . $id . '"><span class="checkbox"></span>' . $att['value'] . '</label>';
						echo '</div> ';
						break;
				}


			}
			echo '</div>';
			echo '<input type="hidden" name="fs-attr" value="" id=group-' . $required_att . '>';

		}

	}

	echo '<div class="fs-group-info"></div>';
	echo '</div>';

}

function fs_list_product_att_group( $product_id, $group_id ) {
	global $fs_config;
	$terms = get_the_terms( $product_id, $fs_config->data['product_att_taxonomy'] );
	if ( $terms ) {
		foreach ( $terms as $term ) {
			if ( $term->parent == $group_id ) {
				echo apply_filters( 'the_title', $term->name );
			}
		}
	}

}

function fs_list_post_atts( $post_id = 0 ) {
	global $fs_config, $post;
	$post_id             = ! empty( $post_id ) ? $post_id : $post->ID;
	$characteristics     = get_the_terms( $post_id, $fs_config->data['product_att_taxonomy'] );
	$characteristic_sort = array();
	if ( ! empty( $characteristics ) ) {
		foreach ( $characteristics as $characteristic ) {
			$characteristic_sort[ $characteristic->parent ][ $characteristic->term_id ] = $characteristic;

		}
	}
	if ( ! empty( $characteristic_sort ) ) {
		foreach ( $characteristic_sort as $key => $parent ) {

			$group      = get_term_field( 'name', $key, $fs_config->data['product_att_taxonomy'] );
			$group_slug = get_term_field( 'slug', $key, $fs_config->data['product_att_taxonomy'] );

			echo '<div class="fs-attr-group-name">' . $group . '</div>';
			echo '<ul class="fs-attr-groups-list">';
			foreach ( $parent as $child ) {
				$attr_type = get_term_meta( $child->term_id, 'fs_att_type', 1 );
				if ( $attr_type == 'image' ) {
					$image_id = get_term_meta( $child->term_id, 'fs_att_image_value', 1 );
					$img_url  = wp_get_attachment_thumb_url( $image_id );
					echo "<li><label><img src=\"$img_url\" width=\"90\" height=\"90\"><input type=\"radio\"  name=\"$group_slug\" value=\"$child->term_id\" data-fs-element=\"attr\" data-product-id=\"$post_id\"></label></li>";
				} else {
					echo "<li><label>" . $child->name . "</label><input type=\"radio\" name=\"$group_slug\" value=\"$child->term_id\"></li>";
				}
			}
			echo '</ul>';

		}
	}

}