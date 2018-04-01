<?php
/**
 * Возвращает массив атрибутов конкретного товара
 *
 * @param int $product_id
 *
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
 *
 * @param $attr_id
 * @param int $product_id
 * @param array $args
 *
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
 * @return array массив объектов поста
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
 * @param array $attr дополниетльные атрибуты html тега
 *
 * @return string              выводит html элемент типа select
 */
function fs_types_sort_filter( $attr = array() ) {
	$filter      = '';
	$order_types = array(
		'date_desc'    => array(
			'name' => __( 'First new', 'fast-shop' )
		),
		'date_asc'     => array(
			'name' => __( 'First old ones', 'fast-shop' )
		),
		'price_asc'    => array(
			'name' => __( 'Price low to high', 'fast-shop' )
		),
		'price_desc'   => array(
			'name' => __( 'Price high to low', 'fast-shop' )
		),
		'name_asc'     => array(
			'name' => __( 'Name A to Z', 'fast-shop' )
		),
		'name_desc'    => array(
			'name' => __( 'Name Z to A', 'fast-shop' )
		),
		'action_price' => array(
			'name' => __( 'First promotion', 'fast-shop' )
		)
	);
	$order_types = apply_filters( 'fs_types_sort_name', $order_types );

	$attr = fs_parse_attr( $attr, array(
		'class'          => 'fs-types-sort-filter',
		'id'             => 'fs-types-sort-filter',
		'name'           => 'order_type',
		'data-fs-action' => 'filter'
	) );

	if ( $order_types ) {
		$filter .= '<select  ' . $attr . '>';

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

	return;
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
 * @param array $attr
 *
 * @return string выводит переключатель к-ва товаров
 */
function fs_per_page_filter( $interval = array(), $attr = array() ) {
	$filters = new FS\FS_Filters;
	if ( empty( $interval ) ) {
		$interval = array( 12, 24, 36, 48, 60, 100 );
	}
	$page_filter = $filters->posts_per_page_filter( $interval, $attr );
	echo $page_filter;

	return;
}

/**
 * Функция выводит фильтр по атрибутам товара
 *
 * @param $group_id
 * @param array $args
 */
function fs_attr_filter( $group_id, $args = array() ) {
	global $fs_config;
	$default = array(
		'redirect'            => true,
		'container'           => 'ul',
		'childs'              => false, // выводить также подгруппы (по умолчанию нет)
		'childs_class'        => 'child', // css класс подгруппы
		'container_class'     => 'listCheck',
		'container_id'        => 'listCheck-' . $group_id,
		'input_wrapper_class' => 'fs-checkbox-wrapper',
		'input_class'         => 'checkStyle',
		'after_input'         => '<span></span>',
		'label_class'         => 'checkLabel',
		'taxonomy'            => $fs_config->data['product_att_taxonomy'],
	);
	$args    = wp_parse_args( $args, $default );

	$terms_args = array(
		'taxonomy'   => $args['taxonomy'],
		'hide_empty' => false,
		'parent'     => $group_id,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);


	if ( ! $args['childs'] ) {
		$terms = get_terms( $terms_args );
	} else {
		$terms = fs_get_taxonomy_hierarchy( $terms_args );
	}

	$arr_url = urldecode( $_SERVER['QUERY_STRING'] );
	parse_str( $arr_url, $url );

	if ( $terms ) {
		if ( ! empty( $args['container'] ) ) {
			echo '<' . $args['container'] . ' class="' . sanitize_html_class( $args['container_class'] ) . '"  id="' . sanitize_html_class( $args['container_id'] ) . '">';
		}
		foreach ( $terms as $key => $term ) {
			$product_attributes = isset( $_GET['attributes'][ $term->slug ] ) ? $_GET['attributes'][ $term->slug ] : '';
			if ( $args['container'] == 'ul' ) {
				echo '<li class="' . esc_attr( $args['input_wrapper_class'] ) . '">';
			} else {
				echo '<div class="' . esc_attr( $args['input_wrapper_class'] ) . '">';
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
			if ( ! empty( $args['after_input'] ) ) {
				echo $args['after_input'];
			}
			echo '<label for="check-' . $term->slug . '"  ' . $label_class . '>' . $term->name . '</label >';
			if ( $args['childs'] ) {
				fs_attr_filter( $term->term_id, $args );
			}

			if ( $args['container'] == 'ul' ) {
				echo '</li>';
			} else {
				echo '</div>';
			}


		}

		if ( ! empty( $args['container'] ) ) {
			echo '</' . $args['container'] . '>';
		}
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
						echo '<input type="radio"  name="group-' . esc_attr( $required_att ) . '" data-product-id="' . esc_attr( $product_id ) . '" data-target="#group-' . esc_attr( $required_att ) . '" data-action="change-attr" id="attr-' . esc_attr( $id ) . '" value="' . esc_attr( $id ) . '">';
						echo '<label for="attr-' . esc_attr( $id ) . '" style="background-color:' . esc_attr( $att['value'] ) . '"><span class="checkbox"></span></label>';
						echo '</span>';
						break;
					default:
						echo '<div class="fs-attr-group-text">';
						echo '<input type="radio"  name="group-' . $required_att . '" data-product-id="' . esc_attr( $product_id ) . '"  data-target="#group-' . esc_attr( $required_att ) . '" id="attr-' . esc_attr( $id ) . '" data-action="change-attr" value="' . esc_attr( $id ) . '">';
						echo '<label for="attr-' . esc_attr( $id ) . '"><span class="checkbox"></span>' . esc_html( $att['value'] ) . '</label>';
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

			echo '<div class="fs-attr-group-name">' . esc_html( $group ) . '</div>';
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

/**
 * возвращает термины поста (товара) отсортированные по родителю
 *
 * @param int $post_id
 * @param $taxonomy
 *
 * @param array $args
 *
 * @return array
 */
function fs_get_the_terms_group( $post_id = 0, $taxonomy, $args = array( 'orderby' => 'none' ) ) {
	$terms = wp_get_object_terms( $post_id, $taxonomy, $args );
	if ( empty( $terms ) ) {
		return array();
	}
	$group = array();
	foreach ( $terms as $term ) {
		$group[ $term->parent ][ $term->term_id ] = $term;
	}

	return $group;
}

/**
 * Выводит поля для покупки товара с возможностью выбора атрибутов типа цвет, размер и т.д.
 *
 * @param int $product_id
 * @param int $parent
 * @param array $args
 */
function fs_product_att_select( $product_id = 0, $parent = 0, $args = array() ) {

	global $post, $fs_config;
	if ( empty( $product_id ) ) {
		$product_id = $post->ID;
	}
	$args  = wp_parse_args( $args, array(
		'type'          => 'radio',
		'wpapper'       => 'ul',
		'wpapper_class' => 'fs-att-select-w',
		'class'         => 'fs-att-select'
	) );
	$terms = fs_get_the_terms_group( $product_id, $fs_config->data['product_att_taxonomy'] );

	$tag_att = fs_parse_attr( array(
		'class'           => $args['class'],
		'name'            => 'fs-group-' . $parent,
		'data-action'     => 'change-attr',
		'data-parent'     => $parent,
		'data-product-id' => $product_id
	) );

	if ( empty( $terms[ $parent ] ) ) {
		return;
	}
	printf( '<%s class="%s">', $args['wpapper'], sanitize_html_class( $args['wpapper_class'] ) );
	switch ( $args['type'] ) {
		case 'radio':
			$i = 0;
			foreach ( $terms[ $parent ] as $term ) {
				if ( $args['wpapper'] == 'ul' ) {
					echo ' <li>';
				} elseif ( 'div' ) {
					echo ' <div>';
				}
				echo '<input type="radio" ' . $tag_att . ' ' . checked( 0, $i, 0 ) . '    value="' . esc_attr( $term->term_id ) . '" id="fs-att-' . esc_attr( $term->term_id ) . '">
                  <label for="fs-att-' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</label>';
				if ( $args['wpapper'] == 'ul' ) {
					echo ' </li>';
				} elseif ( 'div' ) {
					echo ' </div>';
				}
				$i ++;
			}

			break;
		case'select':
			echo '<select ' . $tag_att . '>';
			$i = 0;
			foreach ( $terms[ $parent ] as $k => $term ) {
				echo '<option value="' . $term->term_id . '"  ' . selected( 0, $i, 0 ) . ' >' . $term->name . '</option>';
				$i ++;
			}
			echo '</select>';
			break;
	}
	printf( '</%s>', $args['wpapper'] );

}

/**
 * Выводит поля для покупки товара с возможностью выбора атрибутов типа цвет, размер и т.д.
 * по предназначению похожа на функцию fs_product_att_select, с отличием того что
 * можно задавать только собственные атрибуты, не из таксономии свойств товара
 *
 * @param int $product_id
 * @param array $custom_attributes собственные атрибуты
 * @param array $args
 *
 * @internal param int $parent
 */
function fs_custom_att_select( $product_id = 0, $custom_attributes = array(), $args = array() ) {

	global $post;
	if ( empty( $product_id ) ) {
		$product_id = $post->ID;
	}
	$args    = wp_parse_args( $args, array(
		'type'          => 'radio',
		'wpapper'       => 'ul',
		'wpapper_class' => 'fs-att-select-w',
		'class'         => 'fs-att-select'
	) );
	$tag_att = fs_parse_attr( array(
		'class'           => $args['class'],
		'name'            => 'fs-group',
		'data-action'     => 'change-attr',
		'data-product-id' => $product_id
	) );

	if ( empty( $custom_attributes ) ) {
		return;
	}
	printf( '<%s class="%s">', $args['wpapper'], sanitize_html_class( $args['wpapper_class'] ) );
	switch ( $args['type'] ) {
		case 'radio':
			foreach ( $custom_attributes as $key => $term ) {
				if ( $args['wpapper'] == 'ul' ) {
					echo ' <li>';
				} elseif ( 'div' ) {
					echo ' <div>';
				}
				echo '<input type="radio" ' . $tag_att . '    value="' . esc_attr( $term ) . '" id="fs-att-' . esc_attr( $key ) . '">
                  <label for="fs-att-' . esc_attr( $key ) . '">' . esc_html( $term ) . '</label>';
				if ( $args['wpapper'] == 'ul' ) {
					echo ' </li>';
				} elseif ( 'div' ) {
					echo ' </div>';
				}
			}
			break;
		case'select':
			echo '<select ' . $tag_att . '>';
			foreach ( $custom_attributes as $key => $term ) {
				echo '<option value="' . esc_html( $term ) . '">' . esc_html( $term ) . '</option>';
			}
			echo '</select>';
			break;
	}
	printf( '</%s>', $args['wpapper'] );

	return;
}