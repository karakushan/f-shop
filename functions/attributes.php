<?php
/**
 * Возвращает массив атрибутов конкретного товара
 *
 * @param int $product_id
 *
 * @return array массив атрибутов
 */
function fs_get_attributes_group($product_id = 0)
{
    global $post;
    $product_id = $product_id == 0 ? $post->ID : $product_id;
    $terms = wp_get_object_terms($product_id, 'product-attributes');
    $parents = array();
    foreach ($terms as $key => $term) {
        $attr_type = get_term_meta($term->term_id, 'fs_att_type', 1);
        $attr_type = empty($attr_type) ? 'text' : $attr_type;
        if ($attr_type == 'text') {
            $attr_value = $term->name;
        } else {
            $attr_value = get_term_meta($term->term_id, 'fs_att_' . $attr_type . '_value', 1);
        }
        $parents[$term->parent][$term->term_id] = array(
            'name' => $term->name,
            'type' => $attr_type,
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
function fs_get_attribute($attr_id, $product_id = 0, $args = array())
{
    $args = wp_parse_args($args, array('return' => 1));
    $attributes = fs_get_attributes_group($product_id);

    if (isset($attributes[$attr_id])) {
        if ($args['return']) {
            $first_attr = array_shift($attributes[$attr_id]);
            $atts = $first_attr['value'];
        } else {
            $atts = array(
                'name' => get_term_field('name', $attr_id),
                'children' => $attributes[$attr_id]
            );
        }
    } else {
        if ($args['return']) {
            $atts = '-';
        } else {
            $atts = array(
                'name' => get_term_field('name', $attr_id),
                'children' => array()
            );
        }

    }

    return $atts;
}

/**
 * Получает термины постов выводимых на текущей странице, нужно указать айди родительского термина
 *
 * @param int $group_id
 *
 * @param array $args
 *
 * @return array массив объектов поста
 */
<<<<<<< HEAD
function fs_current_screen_attributes( $group_id = 0, $args = array() ) {
	global $fs_config;
	$atts = [];
	$args = wp_parse_args( $args, array(
		'taxonomy'   => $fs_config->data['product_att_taxonomy'],
		'parent'     => $group_id,
		'hide_empty' => false
	) );
	if ( is_tax() ) {
		global $wp_query;
		$post_args                    = $wp_query->query;
		$post_args ['posts_per_page'] = - 1;
		$post_args ['post_type']      = 'product';
		$posts                        = get_posts( $post_args );
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$post_terms = get_the_terms( $post, $fs_config->data['product_att_taxonomy'] );
				if ( $post_terms ) {
					foreach ( $post_terms as $post_term ) {
						if ( $post_term->parent != $group_id ) {
							continue;
						}
						$atts[] = $post_term->term_id;
					}
				}
//				fs_debug_data( $post_terms, '$post_terms', 'print_r' );
			}
			if ( count( $atts ) ) {
				$atts            = array_unique( $atts );
				$args['include'] = $atts;
				$atts            = get_terms( $args );
			}
		}
	} else {
		$atts = get_terms( $args );
	}

	return $atts;
=======
function fs_current_screen_attributes($parent_term_id = 0)
{
    global $fs_config;
    if (!$parent_term_id) {
        $parent_term_id = get_queried_object_id();
    }

    $posts = get_posts(array(
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(
            'taxonomy' => $fs_config->data['product_taxonomy'],
            'field' => 'term_id',
            'terms' => $parent_term_id
        )

    ));
    wp_reset_postdata();

    $terms = [];
    foreach ($posts as $post_data) {
        $terms_post = get_the_terms($post_data->ID, $fs_config->data['product_att_taxonomy']);
        if ($terms_post) {
            foreach ($terms_post as $term_post) {
                $terms[] = $term_post->parent;
            }
        }


    }

    $terms = array_unique($terms);

    return $terms;
>>>>>>> 98ecf70be87e5d705381c236e4520f6a67723897
}

// select фильтр сортировки по таксономии
function fs_taxonomy_select_filter($taxonomy = 'catalog', $first_option = 'сделайте выбор')
{
    $manufacturers = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));
    $filter = '';
    if ($manufacturers) {
        $filter .= '<select name="tax-' . $taxonomy . '" data-fs-action="filter">';
        $filter .= '<option value="' . remove_query_arg(array('tax-' . $taxonomy)) . '">' . $first_option . '</option>';
        foreach ($manufacturers as $key => $manufacturer) {
            if (isset($_GET['tax-' . $taxonomy])) {
                $selected = selected($manufacturer->term_id, $_GET['tax-' . $taxonomy], 0);
            } else {
                $selected = '';
            }
            $filter .= '<option value="' . add_query_arg(array(
                    'fs_filter' => wp_create_nonce('f-shop'),
                    'tax-' . $taxonomy => $manufacturer->term_id
                )) . '" ' . $selected . '>' . $manufacturer->name . '</option>';
        }
        $filter .= '</select>';
    }

    return $filter;
}


// селект фильтр для фильтрования товаров по наличию
function fs_aviable_select_filter($first_option = 'сделайте выбор')
{
    $filter = '';
    $aviable_types = array(
        'aviable' => array('name' => __('in stock', 'f-shop')),
        'not_available' => array('name' => __('not available', 'f-shop')),
    );
    if ($aviable_types) {
        $filter .= '<select name="order_type" data-fs-action="filter">';
        $filter .= '<option value="' . remove_query_arg(array('aviable')) . '">' . $first_option . '</option>';
        foreach ($aviable_types as $key => $order_type) {
            if (isset($_GET['aviable'])) {
                $selected = selected($key, $_GET['aviable'], 0);
            } else {
                $selected = '';
            }
            $filter .= '<option value="' . add_query_arg(array(
                    'fs_filter' => wp_create_nonce('f-shop'),
                    'aviable' => $key
                )) . '" ' . $selected . '>' . $order_type['name'] . '</option>';
        }
        $filter .= '</select>';
    }

    return $filter;
}


/**
 * Функция выводит фильтр по атрибутам товара
 *
 * @param $group_id
 * @param array $args
 */
<<<<<<< HEAD
function fs_attr_filter( $group_id, $args = array() ) {
	global $fs_config;
	$default = array(
		'redirect'            => true,
		'container'           => 'ul',
		'childs'              => false,
		// выводить также подгруппы (по умолчанию нет)
		'childs_class'        => 'child',
		// css класс подгруппы
		'container_class'     => 'fs-attr-filter',
		'container_id'        => 'fs-attr-filter-' . $group_id,
		'input_wrapper_class' => 'fs-checkbox-wrapper',
		'input_class'         => 'checkStyle',
		'after_input'         => '',
		'label_class'         => 'checkLabel',
		'taxonomy'            => $fs_config->data['product_att_taxonomy'],
		'type'                => 'normal',
		'current_screen'      => false
		// тип отображения, по умолчанию normal - обычные чекбоксы (color - квадратики с цветом, image - изображения)
	);
	$args    = wp_parse_args( $args, $default );

	$terms_args = array(
		'taxonomy'   => $args['taxonomy'],
		'hide_empty' => false,
		'parent'     => $group_id,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);

	$container_class = $args['container_class'] . ' fs-type-' . $args['type'];

	// Если указано выводить свойства только для текущей категории
	if ( $args['current_screen'] ) {
		$terms = fs_current_screen_attributes( $group_id );
	} else {
		if ( ! $args['childs'] ) {
			$terms = get_terms( $terms_args );
		} else {
			$terms = fs_get_taxonomy_hierarchy( $terms_args );
		}
	}


	$arr_url = urldecode( $_SERVER['QUERY_STRING'] );
	parse_str( $arr_url, $url );

	if ( $terms ) {
		if ( ! empty( $args['container'] ) ) {
			echo '<' . esc_html( $args['container'] ) . ' class="' . esc_attr( $container_class ) . '"  id="' . sanitize_html_class( $args['container_id'] ) . '">';
		}
		foreach ( $terms as $key => $term ) {
			$product_attributes = isset( $_GET['attributes'][ $term->slug ] ) ? $_GET['attributes'][ $term->slug ] : '';
			if ( $term->term_id == $product_attributes ) {
				$input_wrapper_class = ' active';
			} else {
				$input_wrapper_class = '';
			}

			$color_box_style = '';
			if ( $args['type'] == 'color' ) {
				$label_color     = get_term_meta( $term->term_id, 'fs_att_color_value', 1 );
				$color_box_style = ! empty( $label_color ) ? 'background-color:' . $label_color : '';
			} elseif ( $args['type'] == 'image' ) {
				$label_image_id  = get_term_meta( $term->term_id, 'fs_att_image_value', 1 );
				$label_image_url = wp_get_attachment_image_url( $label_image_id, 'full' );
				$color_box_style = 'background-image:' . $label_image_url;
			}

			if ( $args['container'] == 'ul' ) {
				echo '<li class="' . esc_attr( $args['input_wrapper_class'] . $input_wrapper_class ) . '">';
			} else {
				echo '<div class="' . esc_attr( $args['input_wrapper_class'] . $input_wrapper_class ) . '">';
			}

			if ( ! empty( $url['attributes'] ) ) {
				$attributes = array_merge( $url['attributes'], array( $term->slug => $term->term_id ) );
			} else {
				$attributes = array( $term->slug => $term->term_id );
			}

			$value = add_query_arg( array(
				'fs_filter'  => wp_create_nonce( 'f-shop' ),
				'attributes' => $attributes
			), $_SERVER['REQUEST_URI'] );

			unset( $attributes[ $term->slug ] );
			$remove_attr = add_query_arg( array(
				'fs_filter'  => wp_create_nonce( 'f-shop' ),
				'attributes' => $attributes
			), $_SERVER['REQUEST_URI'] );;

			if ( ! $args['redirect'] ) {
				$value = $term->term_id;
			}

			echo '<input type="checkbox" class="' . esc_attr( $args['input_class'] ) . '" data-fs-action="filter" data-fs-redirect="' . esc_url( $remove_attr ) . '" name="attributes[' . esc_attr( $term->slug ) . ']" value="' . esc_attr( $value ) . '"  ' . checked( $term->term_id, $product_attributes, 0 ) . ' id="check-' . esc_attr( $term->slug ) . '">';
			if ( ! empty( $args['after_input'] ) ) {
				echo $args['after_input'];
			}

			$label_before_text = '';
			if ( $args['type'] == 'color' ) {
				$label_before_text = '<span class="fs-color-box" style="' . esc_attr( $color_box_style ) . '"></span>';
			}

			echo '<label for="check-' . esc_attr( $term->slug ) . '"  class="' . esc_attr( $args['label_class'] ) . '">' . $label_before_text . ' ' . $term->name . '</label >';
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
=======
function fs_attr_filter($group_id, $args = array())
{
    global $fs_config;
    $default = array(
        'redirect' => true,
        'container' => 'ul',
        'childs' => false,
        // выводить также подгруппы (по умолчанию нет)
        'childs_class' => 'child',
        // css класс подгруппы
        'container_class' => 'fs-attr-filter',
        'container_id' => 'fs-attr-filter-' . $group_id,
        'input_wrapper_class' => 'fs-checkbox-wrapper',
        'input_class' => 'checkStyle',
        'after_input' => '',
        'label_class' => 'checkLabel',
        'taxonomy' => $fs_config->data['product_att_taxonomy'],
        'type' => 'normal'
        // тип отображения, по умолчанию normal - обычные чекбоксы (color - квадратики с цветом, image - изображения)
    );
    $args = wp_parse_args($args, $default);

    $terms_args = array(
        'taxonomy' => $args['taxonomy'],
        'hide_empty' => false,
        'parent' => $group_id,
        'orderby' => 'name',
        'order' => 'ASC',
    );

    $container_class = $args['container_class'] . ' fs-type-' . $args['type'];

    if (!$args['childs']) {
        $terms = get_terms($terms_args);
    } else {
        $terms = fs_get_taxonomy_hierarchy($terms_args);
    }

    $arr_url = urldecode($_SERVER['QUERY_STRING']);
    parse_str($arr_url, $url);

    if ($terms) {
        if (!empty($args['container'])) {
            echo '<' . esc_html($args['container']) . ' class="' . esc_attr($container_class) . '"  id="' . sanitize_html_class($args['container_id']) . '">';
        }
        foreach ($terms as $key => $term) {
            $product_attributes = isset($_GET['attributes'][$term->slug]) ? $_GET['attributes'][$term->slug] : '';
            if ($term->term_id == $product_attributes) {
                $input_wrapper_class = ' active';
            } else {
                $input_wrapper_class = '';
            }

            $color_box_style = '';
            if ($args['type'] == 'color') {
                $label_color = get_term_meta($term->term_id, 'fs_att_color_value', 1);
                $color_box_style = !empty($label_color) ? 'background-color:' . $label_color : '';
            } elseif ($args['type'] == 'image') {
                $label_image_id = get_term_meta($term->term_id, 'fs_att_image_value', 1);
                $label_image_url = wp_get_attachment_image_url($label_image_id, 'full');
                $color_box_style = 'background-image:' . esc_url($label_image_url);
            }

            if ($args['container'] == 'ul') {
                echo '<li class="' . esc_attr($args['input_wrapper_class'] . $input_wrapper_class) . '">';
            } else {
                echo '<div class="' . esc_attr($args['input_wrapper_class'] . $input_wrapper_class) . '">';
            }

            if (!empty($url['attributes'])) {
                $attributes = array_merge($url['attributes'], array($term->slug => $term->term_id));
            } else {
                $attributes = array($term->slug => $term->term_id);
            }

            $value = add_query_arg(array(
                'fs_filter' => wp_create_nonce('f-shop'),
                'attributes' => $attributes
            ), $_SERVER['REQUEST_URI']);

            unset($attributes[$term->slug]);
            $remove_attr = add_query_arg(array(
                'fs_filter' => wp_create_nonce('f-shop'),
                'attributes' => $attributes
            ), $_SERVER['REQUEST_URI']);;

            if (!$args['redirect']) {
                $value = $term->term_id;
            }

            echo '<input type="checkbox" class="' . esc_attr($args['input_class']) . '" data-fs-action="filter" data-fs-redirect="' . esc_url($remove_attr) . '" name="attributes[' . esc_attr($term->slug) . ']" value="' . esc_attr($value) . '"  ' . checked($term->term_id, $product_attributes, 0) . ' id="check-' . esc_attr($term->slug) . '">';
            if (!empty($args['after_input'])) {
                echo esc_html($args['after_input']);
            }

            $label_before_text = '';
            if ($args['type'] == 'color') {
                $label_before_text = '<span class="fs-color-box" style="' . esc_attr($color_box_style) . '"></span>';
            }

            echo '<label for="check-' . esc_attr($term->slug) . '"  class="' . esc_attr($args['label_class']) . '">' . $label_before_text . ' ' . $term->name . '</label >';
            if ($args['childs']) {
                fs_attr_filter($term->term_id, $args);
            }

            if ($args['container'] == 'ul') {
                echo '</li>';
            } else {
                echo '</div>';
            }


        }

        if (!empty($args['container'])) {
            echo '</' . esc_attr($args['container']) . '>';
        }
    }
>>>>>>> 98ecf70be87e5d705381c236e4520f6a67723897
}

function fs_attr_change($required_atts = array())
{
    global $post;
    $product_id = $post->ID;
    echo '<div class="fs-attr-group-wrapper" id="fs-attr-change"><div class="fs-attr-group-desc">Выберите необходимые вам свойства</div>';
    $atts = fs_get_attributes_group();
    foreach ($required_atts as $required_att) {
        if (!empty($atts[$required_att])) {
            $group_name = get_term_field('name', $required_att);
            echo '<div class="fs-attr-group">';
            echo '<div class="fs-attr-group-name">' . esc_attr($group_name) . '</div>';
            foreach ($atts[$required_att] as $id => $att) {
                switch ($att['type']) {
                    case "color":
                        echo '<span class="fs-attr-group-color">';
                        echo '<input type="radio"  name="group-' . esc_attr($required_att) . '" data-product-id="' . esc_attr($product_id) . '" data-target="#group-' . esc_attr($required_att) . '" data-action="change-attr" id="attr-' . esc_attr($id) . '" value="' . esc_attr($id) . '">';
                        echo '<label for="attr-' . esc_attr($id) . '" style="background-color:' . esc_attr($att['value']) . '"><span class="checkbox"></span></label>';
                        echo '</span>';
                        break;
                    default:
                        echo '<div class="fs-attr-group-text">';
                        echo '<input type="radio"  name="group-' . esc_attr($required_att) . '" data-product-id="' . esc_attr($product_id) . '"  data-target="#group-' . esc_attr($required_att) . '" id="attr-' . esc_attr($id) . '" data-action="change-attr" value="' . esc_attr($id) . '">';
                        echo '<label for="attr-' . esc_attr($id) . '"><span class="checkbox"></span>' . esc_html($att['value']) . '</label>';
                        echo '</div> ';
                        break;
                }


            }
            echo '</div>';
            echo '<input type="hidden" name="fs-attr" value="" id=group-' . esc_attr($required_att) . '>';
        }
    }

    echo '<div class="fs-group-info"></div>';
    echo '</div>';

}

function fs_list_product_att_group($product_id, $group_id)
{
    global $fs_config;
    $terms = get_the_terms($product_id, $fs_config->data['product_att_taxonomy']);
    if ($terms) {
        foreach ($terms as $term) {
            if ($term->parent == $group_id) {
                $name = apply_filters('the_title', $term->name);
                echo esc_html($name);
            }
        }
    }

}

function fs_list_post_atts($post_id = 0)
{
    global $fs_config, $post;
    $post_id = !empty($post_id) ? $post_id : $post->ID;
    $characteristics = get_the_terms($post_id, $fs_config->data['product_att_taxonomy']);
    $characteristic_sort = array();
    if (!empty($characteristics)) {
        foreach ($characteristics as $characteristic) {
            $characteristic_sort[$characteristic->parent][$characteristic->term_id] = $characteristic;

        }
    }
    if (!empty($characteristic_sort)) {
        foreach ($characteristic_sort as $key => $parent) {

            $group = get_term_field('name', $key, $fs_config->data['product_att_taxonomy']);
            $group_slug = get_term_field('slug', $key, $fs_config->data['product_att_taxonomy']);

            echo '<div class="fs-attr-group-name">' . esc_html($group) . '</div>';
            echo '<ul class="fs-attr-groups-list">';
            $count = 0;
            foreach ($parent as $chilld_id => $child) {
                $attr_type = get_term_meta($child->term_id, 'fs_att_type', 1);
                if ($attr_type == 'image') {
                    $image_id = get_term_meta($child->term_id, 'fs_att_image_value', 1);
                    echo '<li><label>';
                    echo wp_get_attachment_image($image_id, 'full');
                    echo '<input type="radio"  name="' . esc_attr($group_slug) . '" value="' . esc_attr($child->term_id) . '" data-fs-element="attr" data-product-id="' . esc_attr($post_id) . '" ' . checked(0, $count, false) . "></label></li>";
                } else {
                    echo '<li><label>' . esc_html($child->name) . '</label><input type="radio" name="' . esc_attr($group_slug) . '" value="' . esc_attr($child->term_id) . '"></li>';
                }
                $count++;
            }
            echo '</ul>';

        }
    }

}

/**
 * Returns post (product) terms sorted by parent
 *
 * @param int $product_id
 * @param $taxonomy
 *
 * @param array $args
 *
 * @return array
 */
function fs_get_the_terms_group($product_id = 0, $taxonomy = 'product-attributes', $args = array('orderby' => 'none'))
{
    $product_id = fs_get_product_id($product_id);
    $terms = wp_get_object_terms($product_id, $taxonomy, $args);
    if (empty($terms)) {
        return array();
    }
    $group = array();
    foreach ($terms as $term) {
        $group[$term->parent][$term->term_id] = $term;
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
function fs_product_att_select($product_id = 0, $parent = 0, $args = array())
{

    global $fs_config;

    $product_id = fs_get_product_id($product_id);

    $group_name = get_term_field('name', $parent, $fs_config->data['product_att_taxonomy']);

    $args = wp_parse_args($args, array(
        'type' => 'radio',
        'wpapper' => 'ul',
        'wpapper_class' => 'fs-att-select-w',
        'class' => 'fs-att-select',
        'first' => sprintf(__('Select %s', 'f-shop'), esc_attr($group_name))

    ));
    $terms = fs_get_the_terms_group($product_id, $fs_config->data['product_att_taxonomy']);

    $tag_att = fs_parse_attr(array(
        'class' => $args['class'],
        'name' => $parent,
        'data-fs-element' => 'attr',
        'data-parent' => $parent,
        'data-product-id' => $product_id
    ));

    if (empty($terms[$parent])) {
        return;
    }

    switch ($args['type']) {
        case 'radio':
            echo '<' . esc_attr($args['wpapper']) . ' class="' . esc_attr($args['wpapper_class']) . '">';
            $i = 0;
            foreach ($terms[$parent] as $term) {
                $list_class = get_term_meta($term->term_id, 'fs_att_type', 1) ? 'type-' . get_term_meta($term->term_id, 'fs_att_type', 1) : 'type-none';
                if ($args['wpapper'] == 'ul') {
                    echo ' <li class="' . esc_attr($list_class) . '">';
                } elseif ('div') {
                    echo ' <div class="' . esc_attr($list_class) . '">';
                }
                echo '<input type="radio" ' . $tag_att . ' ' . checked(0, $i, 0) . '    value="' . esc_attr($term->term_id) . '" id="fs-att-' . esc_attr($term->term_id) . '">
                  <label for="fs-att-' . esc_attr($term->term_id) . '">';
                if (get_term_meta($term->term_id, 'fs_att_type', 1) == 'color') {
                    echo '<span class="color" style="background-color:' . esc_attr(get_term_meta($term->term_id, 'fs_att_color_value', 1)) . '"></span>';
                } else {
                    echo esc_html($term->name);
                }
                echo '</label>';
                if ($args['wpapper'] == 'ul') {
                    echo ' </li>';
                } elseif ('div') {
                    echo ' </div>';
                }
                $i++;
            }
            echo '<' . esc_attr($args['wpapper']) . '>';
            break;
        case'select':
            echo '<select ' . $tag_att . '>';
            if ($args['first']) {
                echo '<option value="">' . esc_html($args['first']) . '</option>';
            }
            foreach ($terms[$parent] as $k => $term) {
                echo '<option value="' . esc_attr($term->term_id) . '"  data-count="">' . esc_html($term->name) . '</option>';
            }
            echo '</select>';
            break;
    }


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
function fs_custom_att_select($product_id = 0, $custom_attributes = array(), $args = array())
{

    global $post;
    if (empty($product_id)) {
        $product_id = $post->ID;
    }
    $args = wp_parse_args($args, array(
        'type' => 'radio',
        'wpapper' => 'ul',
        'wpapper_class' => 'fs-att-select-w',
        'class' => 'fs-att-select'
    ));
    $tag_att = fs_parse_attr(array(
        'class' => $args['class'],
        'name' => 'fs-group',
        'data-action' => 'change-attr',
        'data-product-id' => $product_id
    ));

    if (empty($custom_attributes)) {
        return;
    }
    printf('<%s class="%s">', esc_attr($args['wpapper']), esc_attr($args['wpapper_class']));
    switch ($args['type']) {
        case 'radio':
            foreach ($custom_attributes as $key => $term) {
                if ($args['wpapper'] == 'ul') {
                    echo ' <li>';
                } elseif ('div') {
                    echo ' <div>';
                }
                echo '<input type="radio" ' . $tag_att . '    value="' . esc_attr($term) . '" id="fs-att-' . esc_attr($key) . '">
                  <label for="fs-att-' . esc_attr($key) . '">' . esc_html($term) . '</label>';
                if ($args['wpapper'] == 'ul') {
                    echo ' </li>';
                } elseif ('div') {
                    echo ' </div>';
                }
            }
            break;
        case'select':
            echo '<select ' . $tag_att . '>';
            foreach ($custom_attributes as $key => $term) {
                echo '<option value="' . esc_html($term) . '">' . esc_html($term) . '</option>';
            }
            echo '</select>';
            break;
    }
    printf('</%s>', $args['wpapper']);

    return;
}

/**
 * Returns product characteristics groups
 *
 * @param int $product_id
 *
 * @return array
 */
function fs_get_product_attr_groups($product_id = 0)
{
    $product_id = fs_get_product_id($product_id);
    $taxes = get_the_terms($product_id, 'product-attributes');
    $parent_tax = [];
    if ($taxes) {
        foreach ($taxes as $tax) {
            if ($tax->parent) {
                $parent_tax[] = $tax->parent;
            } else {
                $parent_tax [] = $tax->term_id;
            }
        }
    }

    return array_unique($parent_tax);

}