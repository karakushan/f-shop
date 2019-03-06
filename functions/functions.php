<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Recursively get taxonomy and its children
 *
 * @param $args
 *
 * @return array
 */
function fs_get_taxonomy_hierarchy( $args ) {
	// get all direct decendants of the $parent
	$terms = get_terms( $args );
	// prepare a new array.  these are the children of $parent
	// we'll ultimately copy all the $terms into this new array, but only after they
	// find their own children
	$children = array();
	// go through all the direct decendants of $parent, and gather their children
	foreach ( $terms as $term ) {
		// recurse to get the direct decendants of "this" term
		$args['parent'] = $term->term_id;
		$term->children = fs_get_taxonomy_hierarchy( $args );
		// add the term to our new array
		$children[ $term->term_id ] = $term;
	}

	// send the results back to the caller
	return $children;
}


/**
 * Выводит select для выбора свойст товара добавляемого в корзину
 * используется на странице товара
 *
 * @param int $group_id - term_id родителя группы свойств
 * @param int $product_id - id товара
 * @param array $args - дополнительные атрибуты
 */
function fs_dropdown_attr_group( $group_id = 0, $product_id = 0, $args = array() ) {
	global $fs_config;
	$product    = new FS\FS_Product_Class();
	$variations = $product->get_product_variations( $product_id );
	$product_id = fs_get_product_id( $product_id );
	if ( ! $group_id ) {
		printf( __( 'Не указан параметр $group_id в функции %s', 'f-shop' ), __FUNCTION__ );
	}
	$args = wp_parse_args( $args, array(
		'class'    => 'fs-dropdown-attr fs-dropdown-attr-' . $group_id,
		'first'    => __( 'Select' ),// название первой, пустой опции селекта
		'variated' => false // если false свойства берутся из вкладки "свойства товара" иначе из добавленных вариаций
	) );

	echo '<select name="' . esc_attr( $group_id ) . '" class="' . esc_attr( $args['class'] ) . '" data-fs-element="attr" data-product-id="' . esc_attr( $product_id ) . '">';
	if ( $args['first'] ) {
		echo '<option value="">' . esc_attr( $args['first'] ) . '</option>';
	}

	// Если установлен источник терминов из вариаций
	if ( $args['variated'] && count( $variations ) ) {
		$all_atts = [];
		foreach ( $variations as $variation ) {
			if ( ! empty( $variation['attr'] ) ) {
				foreach ( $variation['attr'] as $attr ) {
					$term = get_term( $attr, $fs_config->data['product_att_taxonomy'] );
					if ( $term->parent == $group_id && ! in_array( $term->term_id, $all_atts ) ) {
						$all_atts[] = $term->term_id;
						echo '<option value="' . esc_attr( $term->term_id ) . '">' . esc_attr( $term->name ) . '</option>';
					}
				}
			}
		}

	} else {
		$terms = get_the_terms( $product_id, $fs_config->data['product_att_taxonomy'] );
		foreach ( $terms as $term ) {
			if ( $term->parent == $group_id ) {
				echo '<option value="' . esc_attr( $term->term_id ) . '">' . esc_attr( $term->name ) . '</option>';
			}
		}
	}

	echo '<select>';

}

/**
 * @param integer $post_id -id записи
 * @param array $args -массив аргументов: http://sachinchoolur.github.io/lightslider/settings.html
 */
function fs_lightslider( $post_id = 0, $args = array() ) {
	$post_id = fs_get_product_id( $post_id );
	$galery  = new FS\FS_Images_Class();
	$galery->lightslider( $post_id, $args );
}

/**
 * Возвращает массив изображений галереи товара
 *
 * @param int $post_id -id поста
 * @param bool $thumbnail -включать ли миниатюру поста в список
 *
 * @return array
 */
function fs_get_slider_images( $post_id = 0, $thumbnail = true ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : (int) $post_id;
	$galery  = new FS\FS_Images_Class();
	$images  = $galery->gallery_images_url( $post_id, $thumbnail );

	return $images;
}


//Получает текущую цену с учётом скидки
/**
 * @param int $product_id -id поста, в данном случае товара (по умолчанию берётся из глобальной переменной $post)
 *
 * @return float $price-значение цены
 */
function fs_get_price( $product_id = 0 ) {
	global $fs_config;
	$product_id = fs_get_product_id( $product_id );

	// получаем возможные типы цен
	$base_price   = get_post_meta( $product_id, $fs_config->meta['price'], true );//базовая и главная цена
	$action_price = get_post_meta( $product_id, $fs_config->meta['action_price'], true );//акионная цена
	$price        = floatval( $base_price );
	$action_price = floatval( $action_price );

	if ( $price == 0 ) {
		$first_variation = fs_get_first_variation( $product_id );
		if ( $first_variation ) {
			$price        = ! empty( $first_variation['price'] ) ? floatval( $first_variation['price'] ) : 0;
			$action_price = ! empty( $first_variation['action_price'] ) ? floatval( $first_variation['action_price'] ) : 0;


		}

	}

	//если поле акционной цены заполнено возвращаем его
	if ( $action_price > 0 ) {
		$price = $action_price;
	}

	$price = apply_filters( 'fs_price_filter', $product_id, $price );

	return floatval( $price );
}

/**
 * Displays the current price with discount
 *
 * @param int|string $product_id product id
 * @param string $wrap html wrapper for price
 * @param array $args additional arguments
 */
function fs_the_price( $product_id = 0, $wrap = "%s <span>%s</span>", $args = array() ) {
	$args       = wp_parse_args( $args, array(
		'class' => 'fs-price'
	) );
	$cur_symb   = fs_currency( $product_id );
	$product_id = fs_get_product_id( $product_id );
	$price      = fs_get_price( $product_id );
	$price      = apply_filters( 'fs_price_format', $price );
	printf( '<span data-fs-element="price" data-product-id="' . esc_attr( $product_id ) . '" data-fs-value="' . esc_attr( $price ) . '" class="' . esc_attr( $args['class'] ) . '">' . $wrap . '</span>', esc_attr( $price ), esc_attr( $cur_symb ) );
}

/**
 * Выводит текущую оптовую цену с учётом скидки вместе с валютой сайта
 *
 * @param int $product_id id товара
 */
function fs_the_wholesale_price( $product_id = 0 ) {
	$price = fs_get_wholesale_price( $product_id );
	$price = apply_filters( 'fs_price_format', $price );
	printf( '<span>%s <span>%s</span></span>', esc_html( $price ), esc_html( fs_currency() ) );
}

/**
 * Получает текущую оптовую цену с учётом скидки
 *
 * @param int $product_id - id товара
 *
 * @return float price - значение цены
 */
function fs_get_wholesale_price( $product_id = 0 ) {
	$config = new \FS\FS_Config();
	global $post;
	$product_id = empty( $product_id ) ? $post->ID : (int) $product_id;

	$old_price = get_post_meta( $product_id, $config->meta['wholesale_price'], 1 );
	$new_price = get_post_meta( $product_id, $config->meta['wholesale_price_action'], 1 );
	$price     = ! empty( $new_price ) ? (float) $new_price : (float) $old_price;
	if ( empty( $price ) ) {
		$price = 0;
	}

	return $price;
}

/**
 * Displays the total amount of all products in the cart
 *
 * @param string $wrap -формат отображения цены с валютой
 */
function fs_total_amount( $wrap = '%s <span>%s</span>' ) {
	$total = fs_get_total_amount();
	$total = apply_filters( 'fs_price_format', $total );
	printf( '<span data-fs-element="total-amount">' . $wrap . '</span>', esc_attr( $total ), esc_attr( fs_currency() ) );
}

/**
 * Returns the value of the goods in the cart excluding all discounts, taxes and other things.
 *
 * @return float|int
 */
function fs_get_cart_cost() {
	$products = \FS\FS_Cart_Class::get_cart();
	$cost     = 0;
	if ( count( $products ) ) {
		foreach ( $products as $key => $product ) {
			$product = fs_set_product( $product, $key );
			$cost    += $product->cost;
		}
	}

	return floatval( $cost );
}

/**
 * Выводит стоимость товаров в корзине без учета всех скидок, налогов и прочего
 *
 * @param array $args
 */
function fs_cart_cost( $args = [] ) {
	$args = wp_parse_args( $args, array(
		'format' => '<span data-fs-element="cart-cost">%price% <span>%currency%</span></span>'
	) );

	$cart_cost = fs_get_cart_cost();

	$cart_cost = apply_filters( 'fs_price_format', $cart_cost );
	$replace   = array(
		'%price%'    => esc_attr( $cart_cost ),
		'%currency%' => esc_attr( fs_currency() )
	);
	echo str_replace( array_keys( $replace ), array_values( $replace ), $args['format'] );

}

/**
 * Возвращает общую сумму всех продуктов в корзине
 *
 * @param float $delivery_cost
 *
 * @return float|int
 * @internal param int $delivery_term_id
 *
 * @internal param int $shipping_cost
 */
function fs_get_total_amount( $delivery_cost = 0.0 ) {
	/*
	 *  Сумма покупки расчитывается следующим образом:
	 *  (Стоимость всех товаров в корзине + Стоимость доставки)-Скидка + Налоги
	 */

	if ( ! $delivery_cost ) {
		$delivery_cost = fs_get_delivery_cost();
	}
	$amount = fs_get_cart_cost(); // Стоимость товаров в корзине
	$amount = floatval( $amount );

	// fs_debug_data($amount,'$amount','var_dump');

	$amount = $amount + $delivery_cost;// Стоимость товара вместе с доставкой без учета налогов

	// $amount=apply_filters( 'fs_discount_filter', $amount );// Отнимаем скидку
	$taxes_amount = fs_get_taxes_amount( $amount );// Вычисляем налоги

	$amount = $amount + $taxes_amount; // Расчёт общей суммы

	return floatval( $amount );
}

/**
 * Расчитывает и возвращает сумму налогов
 *
 * @param $amount
 *
 * @return float
 */
function fs_get_taxes_amount( $amount ) {
	$args = array(
		'taxonomy'   => 'fs-taxes',
		'hide_empty' => false
	);

	$terms        = get_terms( $args );
	$taxes_amount = [];

	if ( $terms ) {
		foreach ( $terms as $term ) {
			$tax = get_term_meta( $term->term_id, '_fs_tax_value', 1 );

			if ( strpos( $tax, '%' ) !== false ) {
				$tax_num        = floatval( str_replace( '%', '', $tax ) );
				$taxes_amount[] = $amount * $tax_num / 100;
			} elseif ( is_numeric( $tax ) ) {
				$taxes_amount[] = floatval( $tax );
			} else {
				$taxes_amount[] = 0;
			}
		}
	}
	$amount = floatval( array_sum( $taxes_amount ) );

	return $amount;
}

/**
 * Returns the total discount amount of all items in the cart.
 *
 * @return float|int
 */
function fs_get_total_discount() {
	$discount = 0;

	return $discount;
}

/**
 * Returns information about the first nearest discount.
 *
 * @return mixed
 */
function fs_get_first_discount() {

	global $fs_config;
	$total_amount        = fs_get_total_amount();
	$discounts           = get_terms( array( 'taxonomy' => $fs_config->data['discount_taxonomy'], 'hide_empty' => 0 ) );
	$discounts_cart      = [];
	$total_discount      = 0;
	$discount_difference = 0;
	$discount_diff       = [];
	if ( $discounts ) {
		foreach ( $discounts as $k => $discount ) {
			$discount_type   = get_term_meta( $discount->term_id, 'discount_where_is', 1 );
			$discount_where  = get_term_meta( $discount->term_id, 'discount_where', 1 );
			$discount_value  = get_term_meta( $discount->term_id, 'discount_value', 1 );
			$discount_amount = get_term_meta( $discount->term_id, 'discount_amount', 1 );
			// если скидка указана в процентах
			if ( strpos( $discount_amount, '%' ) !== false ) {
				$discount_amount = floatval( str_replace( '%', '', $discount_amount ) );
				$discount_amount = $discount_value * $discount_amount / 100;
			}

			if ( $discount_type == 'sum' && ( $discount_where == '>=' || $discount_where == '>' ) && $total_amount < $discount_value ) {
				$discounts_cart[ $k ] = $discount_amount;
				$discount_diff[ $k ]  = $discount_value - $total_amount;
			}
		}
	}
	if ( ! empty( $discounts_cart ) ) {
		$total_discount      = min( $discounts_cart );
		$discount_difference = min( $discount_diff );
	}

	return array(
		'discount'            => $total_discount,
		'discount_difference' => $discount_difference
	);

}

/**
 * Displays a discount amount
 *
 * @param string $wrap
 *
 */
function fs_total_discount( $wrap = '%s <span>%s</span>' ) {
	$discount = 0;
	$cart     = FS\FS_Cart_Class::get_cart();
	if ( $cart ) {
		foreach ( $cart as $key => $product ) {
			$item = fs_set_product( $product, $key );
			if ( $item->price > $item->base_price ) {
				continue;
			}
			$discount += ( $item->base_price - $item->price ) * $item->count;
		}
	}
	$discount = apply_filters( 'fs_price_format', $discount );
	printf( '<span data-fs-element="total-discount">' . $wrap . '</span>', esc_attr( $discount ), esc_html( fs_currency() ) );
}


/**
 * Выводит количество товаров в корзине
 *
 * @param array $products
 *
 * @return array|float|int
 */
function fs_total_count( $products = array() ) {
	if ( empty( $products ) ) {
		$products = ! empty( $_SESSION['cart'] ) ? $_SESSION['cart'] : 0;
	}
	$all_count = array();
	if ( $products ) {
		foreach ( $products as $key => $count ) {
			$all_count[ $key ] = $count['count'];
		}
	}
	$all_count = array_sum( $all_count );

	return $all_count;
}


/**
 * Получаем содержимое корзины в виде массива
 *
 * @param array $args
 *
 * @return array|bool элементов корзины в виде:
 *         'id'-id товара,
 *         'name'-название товара,
 *         'count'-количество единиц одного продукта,
 *         'price'-цена за единицу,
 *         'all_price'-общая цена
 */
function fs_get_cart( $args = array() ) {
	$cart_items = FS\FS_Cart_Class::get_cart();
	$args       = wp_parse_args( $args, array(
		'price_format'   => '%s <span>%s</span>',
		'thumbnail_size' => 'thumbnail'
	) );
	$products   = array();
	if ( is_array( $cart_items ) && count( $cart_items ) ) {
		foreach ( $cart_items as $key => $item ) {
			$offer = fs_set_product( $item );
			if ( ! $offer->id ) {
				continue;
			}

			$products[ $key ] = array(
				'id'         => $offer->id,
				'name'       => $offer->title,
				'count'      => $offer->count,
				'thumb'      => get_the_post_thumbnail_url( $offer->id, $args['thumbnail_size'] ),
				'thumbnail'  => get_the_post_thumbnail( $offer->id, $args['thumbnail_size'] ),
				'attr'       => $offer->attributes,
				'link'       => $offer->permalink,
				'price'      => $offer->price_display,
				'base_price' => $offer->base_price_display,
				'all_price'  => $offer->cost_display,
				'sku'        => $offer->sku,
				'currency'   => $offer->currency
			);
		}
	}

	return $products;
}


/**
 * Displays the button for removing goods from the cart
 *
 * @param int $cart_item cart item id
 * @param  array $args array of arguments for the button or link
 *        'text' - содержимое кнопки, по умолчанию '&#10005;',
 *        'type' - тип тега ссылка 'link' или 'button',
 *        'class'- класс для кнопки, ссылки (по умолчанию класс 'fs-delete-position')
 */
function fs_delete_position( $cart_item = 0, $args = array() ) {
	$cart = FS\FS_Cart_Class::get_cart();
	$name = ! empty( $cart[ $cart_item ] ) ? get_the_title( $cart[ $cart_item ]['ID'] ) : '';
	$args = wp_parse_args( $args, array(
		'content' => '&#10006;',
		'type'    => 'link',
		'class'   => 'fs-delete-position',
		'refresh' => true
	) );

	$html_atts = array(
		'data-confirm'   => sprintf( __( 'Are you sure you want to delete the item &laquo;%s&raquo; from the basket?', 'f-shop' ), $name ),
		'title'          => sprintf( __( 'Remove item &laquo;%s&raquo;', 'f-shop' ), $name ),
		'data-cart-item' => $cart_item,
		'data-fs-type'   => "product-delete",
		'class'          => $args['class'],
		'data-refresh'   => $args['refresh']
	);

	$atts = fs_parse_attr( array(), $html_atts );

	switch ( $args['type'] ) {
		case 'link':
			echo '<a href="javascript:void()" ' . $atts . '>' . $args['content'] . '</a>';
			break;
		case 'button':
			echo '<button type="button" ' . $atts . '>' . $args['content'] . '</button>';
			break;

	}
}

/**
 * Возвращает ссылку на каталог товаров
 *
 * @return false|string
 */
function fs_get_catalog_link() {
	global $fs_config;

	return get_post_type_archive_link( $fs_config->data['post_type'] );
}

/**
 * Удаляет товар из списка желаний
 *
 * @param int $product_id -id товара (если указать 0 будет взято ID  товара из цикла)
 * @param string $content -текст кнопки
 * @param array $args -дополнительные атрибуты
 */
function fs_delete_wishlist_position( $product_id = 0, $content = '🞫', $args = array() ) {
	$product_id = fs_get_product_id( $product_id );
	$args       = wp_parse_args( $args, array(
		'type'  => 'link',
		'class' => 'fs-delete-wishlist-position',
		'data'  => array(),
		'title' => sprintf( __( 'Remove from wishlist', 'f-shop' ), get_the_title( $product_id ) )
	) );
	$html_atts  = fs_parse_attr( $args['data'], array(
		'class'          => $args['class'],
		'title'          => sprintf( $args['title'], get_the_title( $product_id ) ),
		'data-fs-action' => 'delete_wishlist_position',
		'data-fs-id'     => $product_id
	) );

	switch ( $args['type'] ) {
		case 'link':
			echo '<a  href="' . esc_attr( add_query_arg( array(
					'fs-user-api' => 'delete_wishlist_position',
					'product_id'  => $product_id
				) ) ) . '" ' . $html_atts . '>' . $content . '</a>';
			break;
		case 'button':
			echo '<button type="button" ' . $html_atts . '>' . $content . '</button>';
			break;
		default:
			echo '<a href="' . esc_attr( add_query_arg( array(
					'fs-user-api' => 'delete_wishlist_position',
					'product_id'  => $product_id
				) ) ) . '" ' . $html_atts . '>' . $content . '</a>';
			break;
	}

}


/**
 * Выводит к-во всех товаров в корзине
 *
 * @param  array $products список товаров, по умолчанию $_SESSION['cart']
 * @param  boolean $echo выводить результат или возвращать, по умолчанию выводить
 *
 * @return int
 */
function fs_product_count( $products = array(), $echo = true ) {
	$all_count = array();
	if ( ! empty( $_SESSION['cart'] ) || ! is_array( $products ) ) {
		$products = isset( $_SESSION['cart'] ) ? $_SESSION['cart'] : array();
	}
	if ( count( $products ) ) {
		foreach ( $products as $key => $count ) {
			$all_count[ $key ] = $count['count'];
		}
	}
	$count = intval( array_sum( $all_count ) );
	if ( $echo ) {
		echo esc_attr( $count );
	} else {
		return $count;
	}
}

/**
 * получает базовую цену (перечёркнутую) без учёта скидки
 *
 * @param int $post_id -id товара
 *
 * @return float $price
 */
function fs_get_base_price( $post_id = 0 ) {
	global $post;
	$config       = new \FS\FS_Config();
	$post_id      = empty( $post_id ) ? $post->ID : $post_id;
	$price        = get_post_meta( $post_id, $config->meta['price'], 1 );
	$action_price = get_post_meta( $post_id, $config->meta['action_price'], 1 );
	if ( $price == fs_get_price( $post_id ) || empty( $action_price ) ) {
		return;
	}
	$price = empty( $price ) ? 0 : (float) $price;
	$price = apply_filters( 'fs_price_filter', $post_id, $price );

	return $price;
}

/**
 * Выводит текущую цену с символом валюты без учёта скидки
 *
 * @param int $product_id -id товара
 * @param string $wrap -html обёртка для цены
 * @param array $args
 */
function fs_base_price( $product_id = 0, $wrap = '%s <span>%s</span>', $args = array() ) {
	$args       = wp_parse_args( $args, array(
		'class' => 'fs-base-price'
	) );
	$product_id = fs_get_product_id( $product_id );
	$price      = fs_get_base_price( $product_id );

	if ( ! $price ) {
		return;
	}
	$price      = apply_filters( 'fs_price_format', $price );
	$show_price = sprintf( $wrap, esc_html( $price ), esc_html( fs_currency() ) );
	printf( '<span data-fs-element="base-price" data-product-id="%d" data-fs-value="%f" class="%s">%s</span>',
		esc_attr( $product_id ),
		esc_attr( $price ),
		esc_attr( $args['class'] ),
		$show_price );
}

/**
 * Возвращает первый вариант покупки
 *
 * @param $product_id - ID товара
 * @param string $return - что возвращать: 'all' - весь массив, 'key' - только ключ
 *
 * @return int|string|null
 */
function fs_get_first_variation( $product_id, $return = 'all' ) {
	$product_class   = new FS\FS_Product_Class();
	$variations      = $product_class->get_product_variations( $product_id );
	$first_variation = null;
	if ( count( $variations ) ) {
		foreach ( $variations as $key => $variation ) {
			if ( $return == 'all' ) {
				$first_variation = $variation;
			} elseif ( $return == 'key' ) {
				$first_variation = $key;
			}
			break;
		}
	}

	return $first_variation;
}

/**
 * [Отображает кнопку "в корзину" со всеми необходимыми атрибутамии]
 *
 * @param  int $product_id [id поста (оставьте пустым в цикле wordpress)]
 * @param  string $label [надпись на кнопке]
 * @param  array $args дополнительные атрибуты
 *
 * @return mixed|void
 */
function fs_add_to_cart( $product_id = 0, $label = null, $args = array() ) {
	global $fs_config;
	$product_id = fs_get_product_id( $product_id );
	$label      = is_null( $label ) ? __( 'Add to cart', 'f-shop' ) : $label;
	// Параметры по умолчанию
	$args_default = array(
		'preloader' => '<img src="' . FS_PLUGIN_URL . '/assets/img/ajax-loader.gif" alt="preloader" width="16">',
		'class'     => 'fs-add-to-cart',
		'type'      => 'button',
		'data'      => array(
			'title'           => __( 'Add to cart', 'f-shop' ),
			'data-action'     => 'add-to-cart',
			'data-product-id' => $product_id,
			'data-available'  => fs_aviable_product( $product_id ) ? 'true' : 'false',
			'data-name'       => get_the_title( $product_id ),
			'data-price'      => apply_filters( 'fs_price_format', fs_get_price( $product_id ) ),
			'data-currency'   => fs_currency(),
			'data-url'        => get_the_permalink( $product_id ),
			'data-sku'        => fs_get_product_code( $product_id ),
			'id'              => 'fs-atc-' . $product_id,
			'data-count'      => 1,
			'data-attr'       => json_encode( new stdClass() ),
			'data-image'      => esc_url( get_the_post_thumbnail_url( $product_id ) )
		)
	);

	$first_variation = fs_get_first_variation( $product_id, 'key' );
	if ( ! is_null( $first_variation ) ) {
		$args_default['data']['data-variation'] = esc_attr( $first_variation );
	}

	$args                  = wp_parse_args( $args, $args_default );
	$args['data']['class'] = $args['class'] . ' fs-atc-' . $product_id;

	// помещаем название категории в дата атрибут category
	$category = get_the_terms( $product_id, $fs_config->data['product_taxonomy'] );
	if ( ! empty( $category ) ) {
		$args['data']['data-category'] = array_pop( $category )->name;
	}
	// Парсим атрибуты html тега
	$html_atts = fs_parse_attr( array(), $args['data'] );

	// дополнительные скрытые инфо-блоки внутри кнопки (прелоадер, сообщение успешного добавления в корзину)
	$atc_after = '<span class="fs-atc-preloader" style="display:none"></span>';

	/* позволяем устанавливать разные html элементы в качестве кнопки */
	switch ( $args['type'] ) {
		case 'link':
			$atc_button = sprintf( '<a href="javacript:void(0)" %s>%s %s</a>', $html_atts, $label, $atc_after );
			break;
		default:
			$atc_button = sprintf( '<button type="button" %s>%s %s</button>', $html_atts, $label, $atc_after );
			break;
	}

	echo apply_filters( 'fs_add_to_cart_filter', $atc_button );
}

/**
 * Выводит кнопку "добавить к сравнению"
 *
 * @param int $post_id
 * @param string $label
 * @param array $attr
 */
function fs_add_to_comparison( $post_id = 0, $label = '', $attr = array() ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : $post_id;
	$attr    = wp_parse_args( $attr,
		array(
			'json'    => array( 'count' => 1, 'attr' => new stdClass() ),
			'class'   => 'fs-add-to-comparison',
			'type'    => 'button',
			'success' => sprintf( __( 'Item «%s» added to comparison', 'f-shop' ), get_the_title( $post_id ) ),
			'error'   => __( 'Error adding product to comparison', 'f-shop' ),
		)
	);

	// устанавливаем html атрибуты кнопки
	$attr_set  = array(
		'data-action'       => 'add-to-comparison',
		'data-product-id'   => $post_id,
		'data-product-name' => get_the_title( $post_id ),
		'id'                => 'fs-atc-' . $post_id,
		'data-success'      => $attr['success'],
		'data-error'        => $attr['error'],
		'class'             => $attr['class']
	);
	$html_atts = fs_parse_attr( array(), $attr_set );

	// дополнительные скрытые инфо-блоки внутри кнопки (прелоадер, сообщение успешного добавления в корзину)
	$atc_after = '<span class="fs-atc-preloader" style="display:none"></span>';

	/* позволяем устанавливать разные html элементы в качестве кнопки */
	switch ( $attr['type'] ) {
		case 'link':
			printf( '<a href="#add_to_comparison" %s>%s %s</a>', $html_atts, $label, $atc_after );
			break;
		default:
			printf( '<button type="button" %s>%s %s</button>', $html_atts, $label, $atc_after );
			break;
	}
}


/**
 * Displays the submission button of the order form
 *
 * @param string $label the inscription on the button
 * @param array $attr html attributes of the button element
 */
function fs_order_send( $label = 'Отправить заказ', $attr = array() ) {
	$attr = fs_parse_attr( $attr, array(
		'data-fs-action'  => "order-send",
		'data-after-send' => __( 'Sent', 'f-shop' ),
		'data-content'    => $label,
		'data-redirect'   => 'page_success',
		'class'           => 'fs-order-send btn btn-success btn-lg'
	) );

	printf( '<button type="submit" %s>%s</button>', $attr, $label );
}

/**
 * Returns a link to the checkout page.
 *
 * @return false|string
 */
function fs_get_checkout_page_link() {
	$page_id = fs_option( 'page_checkout', 0 );
	if ( $page_id ) {
		return get_the_permalink( $page_id );
	}
}

function fs_order_send_form() {
	$form = new \FS\FS_Shortcode;
	echo $form->order_send();
}

//Получает количество просмотров статьи
function fs_post_views( $post_id = '' ) {
	global $post;
	$post_id = empty( $post_id ) ? $post->ID : $post_id;

	$views = get_post_meta( $post_id, 'views', true );

	if ( ! $views ) {
		$views = 0;
	}

	return $views;
}

/**
 * показывает вижет корзины в шаблоне
 *
 * @param array $args -массив атрибутов html элемента обёртки
 *
 */
function fs_cart_widget( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'class' => 'fs-cart-widget',
		'empty' => false
	) );

	$template = '<div data-fs-element="cart-widget" class="' . esc_attr( $args['class'] ) . '">';

	// если параметр  $args['empty']  == true это значит использовать отдельный шаблон для пустой корзины
	if ( $args['empty'] ) {
		if ( fs_total_count() ) {
			$template .= fs_frontend_template( 'cart-widget/widget' );
		} else {
			$template .= fs_frontend_template( 'cart-widget/cart-empty' );
		}
	} else {
		$template .= fs_frontend_template( 'cart-widget/widget' );
	}

	$template .= "</div>";

	echo apply_filters( 'fs_cart_widget_template', $template );
}


/**
 * Shows or returns a link to the cart page.
 *
 * @param bool $show
 *
 * @return false|string
 */
function fs_cart_url( $show = true ) {
	$cart_page = get_permalink( fs_option( 'page_cart', 0 ) );
	if ( $show == true ) {
		echo esc_url( $cart_page );
	} else {
		return $cart_page;
	}
}

/**
 * Displays a link to the checkout or payment page.
 *
 * @param bool $echo
 *
 * @return false|string
 */
function fs_checkout_url( $echo = true ) {
	$checkout_page_id = fs_option( 'page_checkout', 0 );
	if ( $echo ) {
		echo esc_url( get_permalink( $checkout_page_id ) );
	} else {
		return get_permalink( $checkout_page_id );
	}


}


/**
 * The function checks the availability of goods in stock
 *
 * @param int $product_id id записи
 *
 * @return bool  true - товар есть на складе, false - нет
 */
function fs_aviable_product( $product_id = 0 ) {
	global $fs_config;
	$product_id    = fs_get_product_id( $product_id );
	$product_class = new FS\FS_Product_Class();
	$variations    = $product_class->get_product_variations( $product_id );
	$aviable       = false;

	if ( count( $variations ) ) {
		$aviable = true;
	} else {
		$availability = get_post_meta( $product_id, $fs_config->meta['remaining_amount'], true );
		if ( $availability == '' || $availability > 0 ) {
			$aviable = true;
		}
	}

	return $aviable;
}


/**
 * Отображает или возвращает поле для изменения количества добавляемых товаров в корзину
 *
 * @param int $product_id - ID товара
 * @param array $args - массив аргументов
 */
function fs_quantity_product( $product_id = 0, $args = array() ) {
	global $fs_config;
	$product_id = fs_get_product_id( $product_id );
	$args       = wp_parse_args( $args, array(
		'position'      => '%pluss% %input% %minus%',
		'wrapper'       => 'div',
		'wrapper_class' => 'fs-qty-wrap',
		'pluss_class'   => 'fs-pluss',
		'pluss_content' => '+',
		'minus_class'   => 'fs-minus',
		'minus_content' => '-',
		'input_class'   => 'fs-quantity'
	) );

	$first_variation = fs_get_first_variation( $product_id );
	if ( ! is_null( $first_variation ) ) {
		$total_count = $first_variation['count'];
	} else {
		$total_count = get_post_meta( $product_id, $fs_config->meta['remaining_amount'], true );
	}

	if ( $total_count == '' ) {
		$max = '';
	} else {
		$max = 'max="' . intval( $total_count ) . '"';
	}

	$pluss    = sprintf( '<button type="button" class="%s" data-fs-count="pluss">%s</button> ', $args['pluss_class'], $args['pluss_content'] );
	$minus    = sprintf( '<button type="button" class="%s" data-fs-count="minus">%s</button>', $args['minus_class'], $args['minus_content'] );
	$input    = sprintf( '<input type="text" class="%s" name="count" value="1"  data-fs-action="change_count" data-fs-product-id="%s" ' . $max . ' data-limit="%d">', $args['input_class'], $product_id, fs_option( 'fs_in_stock_manage', 0 ) );
	$quantity = str_replace( array( '%pluss%', '%input%', '%minus%' ), array(
		$pluss,
		$input,
		$minus
	), $args['position'] );

	printf( '<%s class="%s" data-fs-element="fs-quantity"> %s </%s>', esc_attr( $args['wrapper'] ), esc_attr( $args['wrapper_class'] ), $quantity, esc_attr( $args['wrapper'] ) );
}

/**
 * Выводит поле для изменения к-ва товаров в корзине
 *
 * @param $item_id
 * @param $value
 * @param array $args
 */
function fs_cart_quantity( $item_id, $value, $args = array() ) {
	$value = intval( $value );
	$args  = wp_parse_args( $args, array(
		'wrapper'       => 'div',
		'refresh'       => true,
		'wrapper_class' => 'fs-qty-wrapper',
		'position'      => '%minus% %input% %pluss%  ',
		'pluss'         => array( 'class' => sanitize_html_class( 'fs-pluss' ), 'content' => '+' ),
		'minus'         => array( 'class' => sanitize_html_class( 'fs-minus' ), 'content' => '-' ),
		'input'         => array( 'class' => 'fs-cart-quantity' )
	) );

	$input_atts = fs_parse_attr( array(),
		array(
			'type'         => "text",
			'name'         => "fs-cart-quantity",
			'data-refresh' => $args['refresh'],
			'value'        => $value,
			'class'        => $args['input']['class'],
			'data-fs-type' => "cart-quantity",
			'data-item-id' => $item_id
		)
	);

	$pluss    = '<button type="button" class="' . $args['pluss']['class'] . '" data-fs-count="pluss" data-target="#product-quantify-' . $item_id . '">' . $args['pluss']['content'] . '</button> ';
	$minus    = '<button type="button" class="' . $args['minus']['class'] . '" data-fs-count="minus" data-target="#product-quantify-' . $item_id . '">' . $args['minus']['content'] . '</button>';
	$input    = '<input   ' . $input_atts . '     >';
	$quantity = str_replace( array( '%pluss%', '%minus%', '%input%' ), array(
		$pluss,
		$minus,
		$input
	), $args['position'] );
	printf( '<%s class="%s"  data-fs-element="fs-quantity">%s</%s>',
		$args['wrapper'],
		esc_attr( $args['wrapper_class'] ),
		$quantity,
		$args['wrapper']
	);
}

/**
 * Парсит урл и возвращает всё что находится до знака ?
 *
 * @param  string $url строка url которую нужно спарсить
 *
 * @return string      возвращает строку урл
 */
function fs_parse_url( $url = '' ) {
	$url   = ( filter_var( $url, FILTER_VALIDATE_URL ) ) ? $url : $_SERVER['REQUEST_URI'];
	$parse = explode( '?', $url );

	return $parse[0];
}

/**
 * Checks whether the stock price is set on a product
 *
 * @param int $product_id
 *
 * @return bool|mixed
 */
function fs_is_action( $product_id = 0 ) {
	global $post, $fs_config;
	$product_id   = empty( $product_id ) ? $post->ID : (int) $product_id;
	$base_price   = get_post_meta( $product_id, $fs_config->meta['price'], 1 );
	$action_price = get_post_meta( $product_id, $fs_config->meta['action_price'], 1 );
	if ( empty( $action_price ) ) {
		return false;
	}
	if ( floatval( $action_price ) > 0 && floatval( $action_price ) < floatval( $base_price ) ) {
		return true;
	} else {
		return false;
	}
}


/**
 * Returns the object of the viewed goods or records
 *
 * @param array $args
 *
 * @return stdClass|WP_Query
 */
function fs_user_viewed( $args = [] ) {
	$viewed = isset( $_SESSION['fs_user_settings']['viewed_product'] ) ? $_SESSION['fs_user_settings']['viewed_product'] : array();
	$posts  = new stdClass();
	if ( ! empty( $viewed ) ) {
		$args  = wp_parse_args( $args, array(
			'post_type'      => 'product',
			'post__in'       => $viewed,
			'posts_per_page' => 4
		) );
		$posts = new WP_Query( $args );
	}

	return $posts;
}


/**
 * Returns the currency ID of the item
 *
 * важно знать что "ID валюты товара" должно быть равно
 * ID одной из добавленных терминов таксономии валюты
 * то-есть возвращается целое число а не код или симовл валюты
 *
 * @param int $product_id
 *
 * @return array;
 */
function fs_get_product_currency( $product_id = 0 ) {
	global $fs_config;
	$product_id            = fs_get_product_id( $product_id );
	$product_currency_id   = intval( get_post_meta( $product_id, $fs_config->meta['currency'], 1 ) );
	$product_currency_code = get_term_meta( $product_currency_id, 'currency-code', 1 );
	$site_currency_id      = intval( fs_option( 'default_currency', 0 ) );

	//если у товара не найден ID валюта возвращаем  ID валюты по умолчанию
	if ( ! $product_currency_id ) {
		$product_currency_id = $site_currency_id;
	}

	return array(
		'id'     => $product_currency_id,
		'symbol' => fs_option( 'currency_symbol', '$' ),
		'code'   => $product_currency_code ? $product_currency_code : 'USD'
	);
}

/**
 * Return the currency symbol
 *
 * @param int $product_id Item ID (default ID is taken from global $post)
 *
 * @return string
 */
function fs_currency( $product_id = 0 ) {
	if ( $product_id ) {
		$product_currency = fs_get_product_currency( $product_id );
		$currency         = $product_currency['symbol'];
	} else {
		$currency = fs_option( 'currency_symbol', '$' );
	}

	return apply_filters( 'fs_currency', $currency );
}


/**
 * Returns option data
 *
 * @param string $option_name option name
 * @param string $default default value
 *
 * @return string
 */
function fs_option( $option_name, $default = '' ) {
	$option = get_option( $option_name, $default );

	return $option;
}

/**
 * This function displays a delete button for all items in the cart.
 *
 * @param array $args
 */
function fs_delete_cart( $args = array() ) {
	$args     = wp_parse_args( $args, array(
		'text'  => __( 'Remove all items', 'f-shop' ),
		'class' => 'fs-delete-cart',
		'type'  => 'button'
	) );
	$html_att = fs_parse_attr( array(), array(
		'class'           => $args['class'],
		'data-fs-element' => "delete-cart",
		'data-confirm'    => __( 'Are you sure you want to empty the trash?', 'f-shop' ),
		'data-url'        => wp_nonce_url( add_query_arg( array( "fs_action" => "delete-cart" ) ), "fs_action" )

	) );
	switch ( $args['type'] ) {
		case 'button':
			echo '<button ' . $html_att . '>' . $args['text'] . '</button> ';
			break;
		case 'link':
			echo '<a href="#" ' . $html_att . '>' . $args['text'] . '</a> ';
			break;
	}
}

/**
 * Выводит процент или сумму скидки(в зависимости от настрорек)
 *
 * @param int|string $product_id -id товара(записи)
 * @param bool $echo
 * @param  string $wrap -html обёртка для скидки
 *
 * @return float|int
 */
function fs_amount_discount( $product_id = 0, $echo = true, $wrap = '<span>%s</span>' ) {
	global $post;
	$config          = new FS\FS_Config;
	$product_id      = empty( $product_id ) ? $post->ID : $product_id;
	$action_symbol   = isset( $config->options['action_count'] ) && $config->options['action_count'] == 1 ? '<span>%</span>' : '<span>' . fs_currency() . '</span>';
	$discount_meta   = (float) get_post_meta( $product_id, $config->meta['discount'], 1 );
	$discount        = empty( $discount_meta ) ? '' : sprintf( $wrap, $discount_meta . ' ' . $action_symbol );
	$discount_return = empty( $discount_meta ) ? 0 : $discount_meta;
	if ( $echo ) {
		echo $discount;
	} else {
		return $discount_return;
	}

}


/**
 * Добавляет возможность фильтрации по определёному атрибуту
 *
 * @param string $group название группы (slug)
 * @param string $type тип фильтра 'option' (список опций в теге "select",по умолчанию) или обычный список "ul"
 * @param string $option_default первая опция (текст) если выбран 2 параметр "option"
 */
function fs_attr_group_filter( $group, $type = 'option', $option_default = 'Выберите значение' ) {
	$fs_filter = new FS\FS_Filters;
	echo $fs_filter->attr_group_filter( $group, $type, $option_default );
}

function fs_range_slider() {
	echo fs_frontend_template( 'widget/jquery-ui-slider/ui-slider', array(
		'price_max' => fs_price_max(),
		'currency'  => fs_currency()

	) );

}

/**
 * Функция получает значение максимальной цены установленной на сайте
 *
 * @param bool $filter
 *
 * @return float|int|null|string
 */
function fs_price_max( $filter = true ) {
	global $wpdb, $fs_config;
	$meta_value_max = $wpdb->get_var( $wpdb->prepare( "SELECT max(cast(meta_value as unsigned)) FROM $wpdb->postmeta WHERE meta_key='%s'", $fs_config->meta['price'] ) );
	$meta_value_max = ! is_null( $meta_value_max ) ? (float) $meta_value_max : 20000;
	if ( $filter ) {
		$max = apply_filters( 'fs_price_format', $meta_value_max );
	} else {
		$max = $meta_value_max;
	}

	return $max;
}

/**
 * функция отображает кнопку "добавить в список желаний"
 *
 * @param  integer $product_id -id записи
 * @param  string $label -текст кнопки
 * @param  array $args -дополнительные аргументы массивом
 *
 */
function fs_add_to_wishlist( $product_id = 0, $label = 'В список желаний', $args = array() ) {
	$product_id = fs_get_product_id( $product_id );
	// определим параметры по умолчанию
	$defaults  = array(
		'attr'      => '',
		'type'      => 'button',
		'preloader' => '<img src="' . FS_PLUGIN_URL . '/assets/img/ajax-loader.gif" alt="preloader">',
		'class'     => 'fs-whishlist-btn',
		'id'        => 'fs-whishlist-btn-' . $product_id,
		'atts'      => ''
	);
	$args      = wp_parse_args( $args, $defaults );
	$html_atts = fs_parse_attr( array(), array(
		'data-fs-action'  => "wishlist",
		'class'           => $args['class'],
		'id'              => $args['id'],
		'data-name'       => get_the_title( $product_id ),
		'title'           => __( 'Add to wishlist', 'f-shop' ),
		'data-image'      => get_the_post_thumbnail_url( $product_id ),
		'data-product-id' => $product_id,
	) );

	switch ( $args['type'] ) {
		case 'link':
			echo '<a href="#fs-whishlist-btn"  ' . $html_atts . ' ' . $args["atts"] . '>' . $label . '<span class="fs-atc-preloader" style="display:none">' . $args['preloader'] . '</span></a>';
			break;

		case 'button':
			echo '<button ' . $html_atts . ' ' . $args["atts"] . '>' . $label . '<span class="fs-atc-preloader" style="display:none">' . $args['preloader'] . '</span></button>';
			break;
	}

}

/**
 * Синоним функции fs_add_to_wishlist()
 * устаревшая функция
 *
 * @param int $post_id
 * @param string $label
 * @param array $args
 */
function fs_wishlist_button( $post_id = 0, $label = 'В список желаний', $args = array() ) {
	fs_add_to_wishlist( $post_id, $label, $args );
}

/**
 * Функция транслитерации русских букв
 *
 * @param $s
 *
 * @return mixed|string
 */
function fs_transliteration( $s ) {
	$s = (string) $s; // преобразуем в строковое значение
	$s = strip_tags( $s ); // убираем HTML-теги
	$s = str_replace( array( "\n", "\r" ), " ", $s ); // убираем перевод каретки
	$s = preg_replace( "/\s+/", ' ', $s ); // удаляем повторяющие пробелы
	$s = trim( $s ); // убираем пробелы в начале и конце строки
	$s = function_exists( 'mb_strtolower' ) ? mb_strtolower( $s ) : strtolower( $s ); // переводим строку в нижний регистр (иногда надо задать локаль)
	$s = strtr( $s, array(
		'а' => 'a',
		'б' => 'b',
		'в' => 'v',
		'г' => 'g',
		'д' => 'd',
		'е' => 'e',
		'ё' => 'e',
		'ж' => 'j',
		'з' => 'z',
		'и' => 'i',
		'й' => 'y',
		'к' => 'k',
		'л' => 'l',
		'м' => 'm',
		'н' => 'n',
		'о' => 'o',
		'п' => 'p',
		'р' => 'r',
		'с' => 's',
		'т' => 't',
		'у' => 'u',
		'ф' => 'f',
		'х' => 'h',
		'ц' => 'c',
		'ч' => 'ch',
		'ш' => 'sh',
		'щ' => 'shch',
		'ы' => 'y',
		'э' => 'e',
		'ю' => 'yu',
		'я' => 'ya',
		'ъ' => '',
		'ь' => ''
	) );
	$s = preg_replace( "/[^0-9a-z-_ ]/i", "", $s ); // очищаем строку от недопустимых символов
	$s = str_replace( " ", "-", $s ); // заменяем пробелы знаком минус

	return $s; // возвращаем результат
}

/**
 * Подключает шаблон $template из директории темы, если шаблон остсуствует ищет в папке "/templates/front-end/" плагина
 *
 * @param $template -название папки и шаблона без расширения
 * @param array $args -дополнительные аргументы
 *
 * @return mixed|void
 */
function fs_frontend_template( $template, $args = array() ) {
	$args            = wp_parse_args( $args, array(
		'theme_base_path'  => TEMPLATEPATH . DIRECTORY_SEPARATOR . 'f-shop' . DIRECTORY_SEPARATOR,
		'plugin_base_path' => FS_PLUGIN_PATH . 'templates' . DIRECTORY_SEPARATOR . 'front-end' . DIRECTORY_SEPARATOR,
		'vars'             => array()
	) );
	$template_plugin = $args['plugin_base_path'] . $template . '.php';
	$template_theme  = $args['theme_base_path'] . $template . '.php';
	extract( $args['vars'] );

	ob_start();
	if ( file_exists( $template_theme ) ) {
		include( $template_theme );
	} elseif ( file_exists( $template_plugin ) ) {
		include( $template_plugin );
	} else {
		printf( __( 'Template file %s not found in function %s', 'f-shop' ), $template, __FUNCTION__ );
	}
	$template = ob_get_clean();

	return apply_filters( 'fs_frontend_template', $template );
}


function fs_get_current_user() {
	$user = wp_get_current_user();
	if ( $user->exists() ) {
		$profile_update  = empty( $user->profile_update ) ? strtotime( $user->user_registered ) : $user->profile_update;
		$user->email     = $user->user_email;
		$user->phone     = get_user_meta( $user->ID, 'phone', 1 );
		$user->city      = get_user_meta( $user->ID, 'city', 1 );
		$user->adress    = get_user_meta( $user->ID, 'adress', 1 );
		$user->birth_day = get_user_meta( $user->ID, 'birth_day', 1 );
		if ( ! empty( $user->birth_day ) ) {
			$user->birth_day = $user->birth_day;
		}
		$user->profile_update = $profile_update;
		$user->gender         = get_user_meta( $user->ID, 'gender', 1 );
	}

	return $user;
}

/**
 * Получает шаблон формы входа
 *
 * @param bool $echo -выводить(по умолчанию) или возвращать
 *
 * @param array $args
 *
 * @return mixed|void
 */
function fs_login_form( $echo = true, $args = array() ) {
	$args     = wp_parse_args( $args, array(
		'name'  => "fs-login",
		'id'    => "fs-login",
		'title' => __( 'Login', 'f-shop' )
	) );
	$template = fs_form_header( $args, 'fs_login' );
	$template .= fs_frontend_template( 'auth/login' );
	$template .= fs_form_bottom();
	if ( $echo ) {
		echo $template;
	} else {
		return $template;
	}

}

/**
 * Получает шаблон формы регистрации
 * @return mixed|void
 */
function fs_register_form() {
	$template = fs_frontend_template( 'auth/register' );

	return $template;
}

/**
 * Получает шаблон формы входа
 * @return mixed|void
 */
function fs_user_cabinet() {
	$template = fs_frontend_template( 'auth/cabinet' );;

	return apply_filters( 'fs_user_cabinet', $template );
}

function fs_page_content() {
	$page  = filter_input( INPUT_GET, 'fs-page', FILTER_SANITIZE_URL );
	$pages = array( 'profile', 'conditions' );
	if ( in_array( $page, $pages ) ) {
		echo fs_frontend_template( 'auth/' . $page );
	} else {
		echo fs_frontend_template( 'auth/profile' );
	}
}

/**
 * Gets the item number by the post id
 *
 * @param  int|integer $product_id
 *
 * @return string $articul артикул товара
 */
function fs_get_product_code( $product_id = 0 ) {
	$config     = new \FS\FS_Config();
	$product_id = fs_get_product_id( $product_id );

	return get_post_meta( $product_id, $config->meta['sku'], 1 );
}

/**
 * получает артикул товара по переданному id поста
 *
 * @param  int|integer $product_id -id поста
 * @param  string $wrap -html обёртка для артикула (по умолчанию нет)
 *
 * @return string артикул товара
 */
function fs_product_code( $product_id = 0, $wrap = '%s' ) {
	$articul = fs_get_product_code( $product_id );
	if ( $articul ) {
		printf( '<span class="fs-sku" data-fs-element="sku">' . $wrap . '</span>', esc_html( $articul ) );
	}
}

/**
 * Returns the quantity or stock of goods in stock (if the value is empty 1 is displayed)
 *
 * @param  int|integer $product_id product id
 *
 * @return int|integer stock of goods in stock
 */
function fs_remaining_amount( $product_id = 0 ) {
	global $post;
	$product_id = ! empty( $product_id ) ? $product_id : $post->ID;
	$config     = new FS\FS_Config();
	$meta_field = $config->meta['remaining_amount'];
	$amount     = get_post_meta( $product_id, $meta_field, true );
	$amount     = ( $amount === '' ) ? '' : (int) $amount;

	return $amount;
}

/**
 * Returns all registered price types
 *
 * @return array  array of all registered prices
 */
function fs_get_all_prices() {
	$config_prices = \FS\FS_Config::$prices;

	return apply_filters( 'fs_prices', $config_prices );
}


/**
 * @param int $product_id
 * @param string $price_type
 *
 * @return float
 */
function fs_get_type_price( $product_id = 0, $price_type = 'price' ) {
	global $post;
	$product_id = empty( $product_id ) ? $post->ID : $product_id;
	$prices     = fs_get_all_prices();
	$price      = get_post_meta( $product_id, $prices[ $price_type ]['meta_key'], 1 );

	return floatval( $price );
}

/**
 * Get the url of product gallery images
 *
 * @param  int $product_id
 * @param string $size
 *
 * @return array
 */
function fs_gallery_images_url( $product_id = 0, $size = 'full' ) {
	global $post;
	$product_id     = empty( $product_id ) ? $post->ID : $product_id;
	$gallery        = new \FS\FS_Images_Class;
	$gallery_images = $gallery->gallery_images_url( $product_id );
	$images         = array();
	if ( is_array( $gallery_images ) ) {
		foreach ( $gallery_images as $key => $gallery_image ) {
			$images[] = wp_get_attachment_url( $gallery_image, $size );
		}
	}

	return $images;
}


/**
 * We receive full images of the gallery of goods
 *
 * @param  int|integer $product_id
 * @param string $size thumbnail size
 *
 * @return array list of images in an array
 */
function fs_gallery_images( $product_id = 0, $size = 'full' ) {
	global $post;
	$product_id     = empty( $product_id ) ? $post->ID : $product_id;
	$gallery        = new \FS\FS_Images_Class;
	$gallery_images = $gallery->gallery_images_url( $product_id );
	$images         = array();
	if ( is_array( $gallery_images ) ) {
		foreach ( $gallery_images as $key => $gallery_image ) {
			$images[] = wp_get_attachment_image( $gallery_image, $size );
		}
	}

	return $images;
}

/**
 * Checks if the sales hit label is displayed.
 *
 * @param int $product_id
 *
 * @return bool
 */
function fs_is_bestseller( $product_id = 0 ) {
	global $fs_config;
	$product_id = fs_get_product_id( $product_id );

	return get_post_meta( $product_id, $fs_config->meta['label_bestseller'], 1 ) ? true : false;

}

/**
 * возвращает объект  с похожими или связанными товарами
 *
 * @param  int|integer $product_id идентификатор товара(поста)
 * @param  array $args передаваемые дополнительные аргументы
 *
 * @return object                  объект с товарами
 */
function fs_get_related_products( $product_id = 0, $args = array() ) {
	global $post, $fs_config;
	$product_id = empty( $product_id ) ? $post->ID : $product_id;
	$products   = get_post_meta( $product_id, $fs_config->meta['related_products'], false );
	$args       = wp_parse_args( $args, array(
		'limit' => 4
	) );

	// ищем товары привязанные вручную
	if ( ! empty( $products[0] ) && is_array( $products[0] ) ) {
		$products = array_unique( $products[0] );
		$args     = array(
			'post_type'      => 'product',
			'post__in'       => $products,
			'post__not_in'   => array( $product_id ),
			'posts_per_page' => $args['limit']
		);
	} else {
		$term_ids = wp_get_post_terms( $product_id, $fs_config->data['product_taxonomy'], array( 'fields' => 'ids' ) );
		$args     = array(
			'post_type'      => 'product',
			'posts_per_page' => $args['limit'],
			'post__not_in'   => array( $product_id ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'catalog',
					'field'    => 'term_id',
					'terms'    => $term_ids
				)
			)
		);
	}
	$posts = new WP_Query( $args );

	return $posts;
}

/**
 * @param int $product_id
 *
 * @return float|int|string
 */
function fs_change_price_percent( $product_id = 0 ) {
	global $post;
	$product_id   = empty( $product_id ) ? $post->ID : $product_id;
	$change_price = 0;
	$config       = new FS\FS_Config;
	// получаем возможные типы цен
	$base_price   = get_post_meta( $product_id, $config->meta['price'], true );//базовая и главная цена
	$base_price   = (float) $base_price;
	$action_price = get_post_meta( $product_id, $config->meta['action_price'], true );//акионная цена
	$action_price = (float) $action_price;
	if ( ! empty( $action_price ) && ! empty( $base_price ) && $action_price < $base_price ) {

		$change_price = ( $base_price - $action_price ) / $base_price * 100;
		$change_price = round( $change_price );
	}

	return $change_price;
}

/**
 * Выводит скидку на товар в процентах
 *
 * @param int $product_id -ID товара(записи)
 * @param string $format -html теги, обёртка для скидки
 * @param array $args
 */
function fs_discount_percent( $product_id = 0, $format = '-%s%s', $args = array() ) {
	$args     = wp_parse_args( $args,
		array(
			'class' => 'fs-discount'
		)
	);
	$discount = fs_change_price_percent( $product_id );
	if ( $discount > 0 ) {
		printf( '<span data-fs-element="discount" class="%s">', esc_attr( $args['class'] ) );
		printf( $format, $discount, '%' );
		print( '</span>' );
	}

}

/**
 * Преобразует массив аргументов в строку для использования в атрибутах тегов
 * принцип работы похож на wp_parse_args()
 *
 * @param  array $attr атрибуты которые доступны для изменения динамически
 * @param  array $default атрибуты функции по умолчанию
 *
 * @return string $att          строка атрибутов
 */
function fs_parse_attr( $attr = array(), $default = array() ) {
	$attr      = wp_parse_args( $attr, $default );
	$atributes = array();
	foreach ( $attr as $key => $att ) {
		$atributes[] = esc_attr( $key ) . '="' . esc_attr( $att ) . '"';
	}

	if ( count( $atributes ) ) {
		$att = implode( ' ', $atributes );
	}

	return $att;
}


/**
 * возвращает список желаний
 *
 * @param array $args массив аргументов, идентичные WP_Query
 *
 * @return array список желаний
 */
function fs_get_wishlist( $args = array() ) {
	if ( empty( $_SESSION['fs_wishlist'] ) ) {
		$wishlist[0] = 0;
	} else {
		$wishlist = $_SESSION['fs_wishlist'];
	}
	$args     = wp_parse_args( $args, array(
		'post_type' => 'product',
		'post__in'  => array_unique( $wishlist )

	) );
	$wh_posts = new WP_Query( $args );

	return $wh_posts;
}

/**
 * Выводит количество товаров в списке желаний
 */
function fs_wishlist_count() {
	$wl = fs_get_wishlist();
	if ( $wl ) {
		echo esc_html( $wl->found_posts );
	} else {
		echo 0;
	}
}


/**
 * Return a link to the wish list
 *
 * the page is set automatically during installation or can be set manually in the settings in the tab "Pages"
 */
function fs_wishlist_url() {
	return get_the_permalink( intval( fs_option( 'page_whishlist' ) ) );
}

/**
 * отображает список желаний
 *
 * @param  array $html_attr массив html атрибутов для дива обёртки
 */
function fs_wishlist_widget( $html_attr = array() ) {
	$template = fs_frontend_template( 'wishlist/wishlist' );

	$attr_set  = array(
		'data-fs-element' => 'whishlist-widget'
	);
	$html_attr = fs_parse_attr( $html_attr, $attr_set );
	printf( '<div %s>%s</div>', $html_attr, $template );
}

/**
 * @param int $order_id -id заказа
 *
 * @return bool|object возвращает объект с данными заказа или false
 */
function fs_get_order( $order_id = 0 ) {
	$order = false;
	if ( $order_id ) {
		$orders = new \FS\FS_Orders_Class();
		$order  = $orders->get_order( $order_id );
	}

	return $order;
}

function fs_get_delivery( $delivery_id ) {
	$name = get_term_field( 'name', $delivery_id, 'fs-delivery-methods' );

	return $name;
}

function fs_get_payment( $payment_id ) {
	$name = get_term_field( 'name', $payment_id, 'fs-payment-methods' );

	return $name;
}

/**
 * Функция выводе одно поле формы заказа
 *
 * @param string $field_name название поля, атрибут name
 * @param array $args массив аргументов типа класс, тип, обязательность заполнения, title
 */
function fs_form_field( $field_name, $args = array() ) {
	$form_class = new \FS\FS_Form_Class();
	$form_class->fs_form_field( $field_name, $args );
}

/**
 * создаёт переменные в письмах из массива ключей
 *
 * @param array $keys -ключи массива
 *
 * @return array массив из значений типа %variable%
 */
function fs_mail_keys( $keys = array() ) {
	$email_variable = array();
	if ( $keys ) {
		foreach ( $keys as $key => $value ) {
			$email_variable[] = '%' . $key . '%';
		}
	}

	return $email_variable;
}

function fs_attr_list( $attr_group = 0 ) {
	$terms = get_terms( array(
		'taxonomy'   => 'product-attributes',
		'hide_empty' => false,
		'parent'     => $attr_group,
	) );
	$atts  = array();
	foreach ( $terms as $term ) {
		switch ( get_term_meta( $term->term_id, 'fs_att_type', 1 ) ) {
			case 'color':
				$atts[] = get_term_meta( $term->term_id, 'fs_att_color_value', 1 );
				break;
			case 'image':
				$atts[] = get_term_meta( $term->term_id, 'fs_att_image_value', 1 );
				break;
			case 'text':
				$atts[] = $term->name;
				break;
		}

	}

	return $atts;
}


/**
 * Выводит список всех атрибутов товара в виде:
 *   Название группы свойств : свойство (свойства)
 *
 * @param int $post_id -ID товара
 * @param array $args -дополнительные аргументы вывода
 */
function fs_the_atts_list( $post_id = 0, $args = array() ) {
	global $post, $fs_config;
	$list       = '';
	$post_id    = ! empty( $post_id ) ? $post_id : $post->ID;
	$args       = wp_parse_args( $args, array(
		'wrapper'       => 'ul',
		'group_wrapper' => 'span',
		'wrapper_class' => 'fs-atts-list',
		'exclude'       => array(),
		'parent'        => 0
	) );
	$term_args  = array(
		'hide_empty'   => false,
		'exclude_tree' => $args['exclude'],
	);
	$post_terms = wp_get_object_terms( $post_id, $fs_config->data['product_att_taxonomy'], $term_args );
	$parents    = array();
	if ( $post_terms ) {
		foreach ( $post_terms as $post_term ) {
			if ( $post_term->parent == 0 || ( $args['parent'] != 0 && $args['parent'] != $post_term->parent ) ) {
				continue;
			}
			$parents[ $post_term->parent ][ $post_term->term_id ] = $post_term->term_id;

		}
	}
	if ( $parents ) {
		foreach ( $parents as $k => $parent ) {
			$primary_term = get_term( $k, $fs_config->data['product_att_taxonomy'] );
			$second_term  = [];
			foreach ( $parent as $p ) {
				$s             = get_term( $p, $fs_config->data['product_att_taxonomy'] );
				$second_term[] = $s->name;
			}

			$list .= '<li><span class="first">' . $primary_term->name . ': </span><span class="last">' . implode( ', ', $second_term ) . ' </span></li > ';


		}
	}


	$html_atts = fs_parse_attr( array(), array(
		'class' => $args['wrapper_class']
	) );
	printf( ' <%s % s >%s </%s > ', $args['wrapper'], $html_atts, $list, $args['wrapper'] );

}

/**
 * Получает информацию обо всех зарегистрированных размерах картинок.
 *
 * @global $_wp_additional_image_sizes
 * @uses   get_intermediate_image_sizes()
 *
 * @param  boolean [$unset_disabled=true] Удалить из списка размеры с 0 высотой и шириной?
 *
 * @return array Данные всех размеров.
 */
function fs_get_image_sizes( $unset_disabled = true ) {
	$wais =& $GLOBALS['_wp_additional_image_sizes'];

	$sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
			$sizes[ $_size ] = array(
				'width'  => get_option( "{$_size}_size_w" ),
				'height' => get_option( "{$_size}_size_h" ),
				'crop'   => (bool) get_option( "{$_size}_crop" ),
			);
		} elseif ( isset( $wais[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $wais[ $_size ]['width'],
				'height' => $wais[ $_size ]['height'],
				'crop'   => $wais[ $_size ]['crop'],
			);
		}

		// size registered, but has 0 width and height
		if ( $unset_disabled && ( $sizes[ $_size ]['width'] == 0 ) && ( $sizes[ $_size ]['height'] == 0 ) ) {
			unset( $sizes[ $_size ] );
		}
	}

	return $sizes;
}

/**
 * Возвращает массив состоящий id прикреплённых к посту вложений
 *
 * @param int $post_id -ID поста
 *
 * @param bool $thumbnail -включать ли миниатюру в галерею,
 * если да, то миниатюра будет выведена первым изображением
 *
 * @return array
 */
function fs_gallery_images_ids( $post_id = 0, $thumbnail = true ) {
	global $post, $fs_config;
	$post_id           = ! empty( $post_id ) ? $post_id : $post->ID;
	$fs_gallery        = get_post_meta( $post_id, $fs_config->meta['gallery'], false );
	$gallery           = array();
	$post_thumbnail_id = get_post_thumbnail_id( $post_id );
	if ( $post_thumbnail_id && $thumbnail ) {
		$gallery       [] = $post_thumbnail_id;
	}

	if ( ! empty( $fs_gallery['0'] ) ) {
		foreach ( $fs_gallery['0'] as $item ) {
			if ( wp_get_attachment_image( $item ) ) {
				$gallery       [] = $item;
			}
		}
	}

	return $gallery;
}

/**
 * Выводит миниатюру товара, если миниатюра не установлена-заглушку
 *
 * @param int $product_id ID товара (поста)
 * @param string $size размер миниатюры
 * @param array $args html атрибуты, типа класс, id
 *
 * @return false|string
 */
function fs_product_thumbnail( $product_id = 0, $size = 'thumbnail', $args = array() ) {
	$img_class = new FS\FS_Images_Class();
	$gallery   = $img_class->get_gallery( $product_id );
	if ( has_post_thumbnail( $product_id ) ) {
		echo get_the_post_thumbnail( $product_id, $size, $args );
	} elseif ( count( $gallery ) ) {
		$attach_id = array_shift( $gallery );
		echo wp_get_attachment_image( $attach_id, $size, false, $args );
	} else {
		echo '<img src="' . esc_url( FS_PLUGIN_URL . 'assets/img/image.svg' ) . '" alt="' . esc_attr__( 'No image', 'f-shop' ) . '">';
	}
}

/**
 * Возвращает ссылку на миниатюру товара
 *
 * @param int $product_id
 * @param string $size
 *
 * @return false|string|null
 */
function fs_get_product_thumbnail_url( $product_id = 0, $size = 'thumbnail' ) {
	$img_class = new FS\FS_Images_Class();
	$gallery   = $img_class->get_gallery( $product_id );
	$url       = null;
	if ( has_post_thumbnail( $product_id ) ) {
		$url = get_the_post_thumbnail_url( $product_id, $size );
	} elseif ( count( $gallery ) ) {
		$attach_id = array_shift( $gallery );
		$url       = wp_get_attachment_image_url( $attach_id, $size, false );
	} else {
		$url = FS_PLUGIN_URL . 'assets/img/image.svg';
	}

	return $url;
}

/**
 * Создаёт ссылку для отфильтровки товаров по параметрам в каталоге
 *
 * @param array $query строка запроса
 * @param string $catalog_link ссылка на страницу на которой отобразить результаты
 * @param array $unset параметры, которые нужно удалить из строки запроса
 */
function fs_filter_link( $query = [], $catalog_link = null ) {
	global $fs_config;

	$query = wp_parse_args( $query, array(
		'fs_filter' => wp_create_nonce( 'f-shop' )
	) );

	// устанавливаем базовый путь без query_string
	$catalog_link = $catalog_link ? $catalog_link : get_post_type_archive_link( $fs_config->data['post_type'] );

	echo esc_url( add_query_arg( $query, $catalog_link ) );
}


/**
 * Выводит список ссылок для сортировки по параметрам в каталоге
 *
 * @param array $args массив дополнительных параметров
 */
function fs_order_by_links( $args = array() ) {
	global $fs_config;

	/** @var array $args список аргументов функции */
	$args = wp_parse_args( $args, array(
		'current_page' => get_post_type_archive_link( $fs_config->data['post_type'] ),// ссылка на текущую страницу
		'before'       => '',// код перед ссылкой
		'after'        => '',// код после ссылки
		'exclude'      => array()// исключенные ключи
	) );

	/** @var array $order_by_keys содержит список ключей для GET запроса */
	$order_by_keys = $fs_config->get_orderby_keys();
	$html          = '';
	$order_by      = ! empty( $_GET['order_type'] ) ? $_GET['order_type'] : 'date_desc';

	if ( $order_by_keys ) {
		foreach ( $order_by_keys as $key => $order_by_arr ) {
			// исключаем GET параметр
			if ( $args['exclude'] ) {
				if ( in_array( $key, $args['exclude'] ) ) {
					continue;
				}
			}
			// выводим код перед ссылкой
			if ( $args['before'] ) {
				$html .= $args['before'];
			}
			// собственно одна из ссылок
			$html .= '<a href="';
			$html .= esc_url( add_query_arg(
					array(
						'order_type' => $key,
						'fs_filter'  => wp_create_nonce( 'f-shop' )
					), $args['current_page'] )
			);
			$html .= '"';
			if ( $order_by == $key ) {
				$html .= ' class="active"';
			}
			$html .= '>';
			$html .= esc_html( $order_by_arr['name'] ) . '</a>';
			// выводим код после ссылки
			if ( $args['after'] ) {
				$html .= $args['after'];
			}
		}
	}

	echo $html;
}


/**
 * Ищет в массиве $haystack значения массива $needles
 *
 * @param $needles
 * @param $haystack
 *
 * @return bool если найдены все совпадения будет возвращено true иначе false
 */
function fs_in_array_multi( $needles, $haystack ) {
	return ! array_diff( $needles, $haystack );
}

/**
 * Проверяет является ли товар вариативным
 *
 * @param int $post_id
 *
 * @return int
 */
function fs_is_variated( $post_id = 0 ) {
	global $fs_config;

	return intval( get_post_meta( $post_id, $fs_config->meta['variated_on'], 1 ) );
}

/**
 * Получает вариативную цену
 *
 * @param int $product_id
 * @param int $variation_id
 *
 * @return float
 */
function fs_get_variated_price( $product_id = 0, $variation_id ) {
	$product_class = new FS\FS_Product_Class();

	return $product_class->get_variation_price( $product_id, $variation_id );
}

/**
 * Выводит вариативную цену
 *
 * @param int $post_id
 * @param array $atts
 *
 * @param array $args
 *
 * @return float
 */
function fs_variated_price( $post_id = 0, $atts = array(), $args = array() ) {
	$post_id = fs_get_product_id( $post_id );
	$args    = wp_parse_args( $args, array(
		'count'  => true,
		'format' => '%s <span>%s</span>',
		'echo'   => true
	) );
	$price   = fs_get_variated_price( $post_id, $atts );
	$price   = apply_filters( 'fs_price_format', $price );
	printf( $args['format'], $price, fs_currency() );

	return true;
}

/**
 * Получает минимальное количество вариативных покупаемых товаров
 *
 * @param int $post_id
 * @param array $atts
 *
 * @param bool $count
 *
 * @return float
 */
function fs_get_variated_count( $post_id = 0, $atts = array() ) {
	$post_id        = fs_get_product_id( $post_id );
	$atts           = array_map( 'intval', $atts );
	$variants       = get_post_meta( $post_id, 'fs_variant', 0 );
	$variants_count = get_post_meta( $post_id, 'fs_variant_count', 0 );
	$variant_count  = 1;
	// если не включен чекбок "вариативный товар" , то возвращаем 1
	if ( ! fs_is_variated( $post_id ) ) {
		return $variant_count;
	}

	if ( ! empty( $variants[0] ) ) {
		foreach ( $variants[0] as $k => $variant ) {
			// ищем совпадения варианов в присланными значениями
			if ( ! empty( $variants_count ) && count( $variant ) == count( $atts ) && fs_in_array_multi( $atts, $variant ) ) {
				$variant_count = max( $variants_count[0][ $k ], 1 );
			}
		}

	}

	return intval( $variant_count );
}

/**
 * Возвращает ID товара
 *
 * @param integer $product ID поста
 *
 * @return int
 */
function fs_get_product_id( $product = 0 ) {
	if ( empty( $product ) ) {
		global $post;
		$product = $post->ID;
	} elseif ( is_object( $product ) ) {
		$product = $product->ID;
	}

	return intval( $product );
}

/**
 * Выводит метку об акции, популярном товаре, или недавно добавленом
 *
 * @param int $product_id -уникальный ID товара (записи ВП)
 * @param array $labels HTML код метки
 *              могут быть метки типа: 'action','popular','new'
 */
function fs_product_label( $product_id = 0, $labels = array() ) {
	$product_id = fs_get_product_id( $product_id );
	$args       = wp_parse_args( $labels, array(
		'action'  => '',
		'popular' => '',
		'new'     => ''
	) );
	if ( ! empty( $_GET['order_type'] ) ) {
		if ( $_GET['order_type'] == 'field_action' ) {
			echo $args['action'];
		} elseif ( $_GET['order_type'] == 'views_desc' ) {
			echo $args['popular'];
		} elseif ( $_GET['order_type'] == 'date_desc' ) {
			echo $args['new'];
		}
	} else {
		if ( fs_is_action( $product_id ) ) {
			echo $args['action'];
		}
	}


}

/**
 * Функция создаёт возможность изменения сообщения пользователю,
 * которое отсылается или показывается после осуществления покупкиэ
 * сообщение может содержать две переменные:
 *
 *
 * @param $pay_method_id -ID выбраного метода оплаты
 *
 * @return mixed|void
 */
function fs_pay_user_message( $pay_method_id ) {
	$message = get_term_meta( intval( $pay_method_id ), 'pay-message', 1 );

	return apply_filters( 'fs_pay_user_message', $message, $pay_method_id );
}

/**
 * Возвращает массив разрешённых типов изображений для загрузки
 *
 * @param string $return
 *
 * @return array|string
 */
function fs_allowed_images_type( $return = 'array' ) {
	$mime_types = get_allowed_mime_types();
	$mime       = [];
	if ( $mime_types ) {
		foreach ( $mime_types as $mime_type ) {
			if ( strpos( $mime_type, 'image' ) === 0 ) {
				if ( $return == 'json' ) {
					$mime[ $mime_type ] = true;
				} else {
					$mime[] = $mime_type;
				}
			}
		}
	}
	if ( $return == 'json' ) {
		return json_encode( $mime );
	} else {
		return $mime;
	}
}

/**
 * Выводит текст типа "Показано товаров 12 из 36"
 *
 * @param string $format
 */
function fs_items_on_page( $format = '' ) {
	global $wp_query;
	$found_posts    = intval( $wp_query->found_posts );
	$posts_per_page = intval( $wp_query->query_vars['posts_per_page'] );
	if ( $posts_per_page > $found_posts ) {
		$posts_per_page = $found_posts;
	}
	$format = empty( $format ) ? esc_html_e( 'Showing %1$d products from %2$d', 'f-shop' ) : $format;
	printf( $format, $posts_per_page, $found_posts );
}

/**
 * Копирует папки и файлы и @var $from в @var $to учитывая структуру
 *
 * @param string $from из какой папки копировать файлы
 * @param string $to куда копировать
 * @param bool $rewrite
 */
function fs_copy_all( $from, $to, $rewrite = true ) {
	if ( is_dir( $from ) && file_exists( $from ) ) {
		if ( ! file_exists( $to ) ) {
			mkdir( $to );
		}
		$d = dir( $from );
		while ( false !== ( $entry = $d->read() ) ) {
			if ( $entry == "." || $entry == ".." ) {
				continue;
			}
			fs_copy_all( "$from/$entry", "$to/$entry", $rewrite );
		}
		$d->close();
	} else {
		if ( ! file_exists( $to ) || $rewrite ) {
			copy( $from, $to );
		}
	}
}

/**
 * Возвращает мета данные о категории товара в виде объекта
 * price_max-максимальная цена
 * price_min-минимальная цена
 * sum-общая стоимость товаров
 * count-количество товаров в категории
 *
 * @param int $category_id
 *
 * @return stdClass
 */
function fs_get_category_meta( $category_id = 0 ) {
	global $wpdb, $fs_config;
	if ( ! $category_id ) {
		$category_id = get_queried_object_id();
	}

	$meta = $wpdb->get_row( $wpdb->prepare( "SELECT MAX(CAST( meta_value AS UNSIGNED)) as price_max, MIN(CAST( meta_value AS UNSIGNED)) as price_min, SUM(CAST( meta_value AS UNSIGNED)) as sum, COUNT(post_id) as count FROM wp_postmeta WHERE post_id IN (SELECT object_id FROM  $wpdb->term_relationships WHERE term_taxonomy_id=%d AND object_id IN (SELECT ID from $wpdb->posts WHERE post_status='publish')) AND meta_key='%s'", $category_id, $fs_config->meta['price'] ) );
	if ( ! $meta ) {
		$meta            = new stdClass();
		$meta->price_max = 0;
		$meta->price_min = 0;
		$meta->sum       = 0;
		$meta->count     = 0;
	}

	return $meta;
}

/**
 * Возвращает главное изображение категории
 *
 * @param int $term_id идентификатор термина категории
 * @param string $size размер изображения
 * @param array $args дополнительные аргументы
 *
 * @return false|int|string
 */
function fs_get_category_image( $term_id = 0, $size = 'thumbnail', $args = array() ) {
	if ( ! $term_id ) {
		$term_id = get_queried_object_id();
	}
	$args     = wp_parse_args( $args, array(
		'return'  => 'image',
		'attr'    => array(),
		'default' => FS_PLUGIN_URL . 'assets/img/no-image.png'
	) );
	$image_id = get_term_meta( $term_id, '_thumbnail_id', 1 );
	$image_id = intval( $image_id );
	if ( $args['return'] == 'image' ) {
		if ( ! $image_id ) {
			$image = '<img src="' . esc_attr( $args['default'] ) . '" alt="no image">';
		} else {
			$image = wp_get_attachment_image( $image_id, $size, false, $args['attr'] );
		}

	} elseif ( $args['return'] == 'url' ) {
		if ( ! $image_id ) {
			$image = $args['default'];
		} else {
			$image = wp_get_attachment_image_url( $image_id, $size, false );
		}
	} else {
		$image = $image_id;
	}

	return $image;

}

/**
 * Отображает налоги в виде списка
 *
 * @param array $args список аргументов
 *
 * @param float $total -сумма от которой считаются налоги
 *
 * @return mixed|void
 */
function fs_taxes_list( $args = [], $total = 0.0 ) {

	$args = wp_parse_args( $args, array(
		'taxonomy'      => 'fs-taxes',
		'hide_empty'    => false,
		'wrapper'       => 'div',
		'wrapper_class' => 'fs-taxes-list',
		'format'        => '<span>%name%(%value%)</span> <span>%cost% <span>%currency%</span></span>'
	) );

	$terms = get_terms( $args );
	if ( $total == 0.0 ) {
		$total = fs_get_cart_cost();
	}

	if ( $terms ) {
		foreach ( $terms as $term ) {
			$tax = get_term_meta( $term->term_id, '_fs_tax_value', 1 );

			if ( strpos( $tax, '%' ) ) {
				$tax_num    = floatval( str_replace( '%', '', $tax ) );
				$tax_amount = $total * $tax_num / 100;
				$tax_amount = apply_filters( 'fs_price_format', $tax_amount );
			} else {
				$tax_amount = apply_filters( 'fs_price_format', floatval( $tax ) );
			}

			$taxes_html = '';
			if ( $args['wrapper'] ) {
				$taxes_html = '<' . esc_attr( $args['wrapper'] ) . ' data-fs-element="taxes-list" class="' . esc_attr( $args['wrapper_class'] ) . '">';
			}

			$replace    = array(
				'%name%'     => esc_attr( $term->name ),
				'%value%'    => esc_attr( $tax ),
				'%cost%'     => esc_attr( $tax_amount ),
				'%currency%' => esc_attr( fs_currency() )
			);
			$taxes_html .= str_replace( array_keys( $replace ), array_values( $replace ), $args['format'] );

			if ( $args['wrapper'] ) {
				$taxes_html .= '</' . esc_html( $args['wrapper'] ) . '>';
			}

			echo apply_filters( 'fs_taxex_list', $taxes_html );
		}
	}
}

/**
 * Debugging feature for easy debugging
 *
 * @param mixed $data передаваемые данные
 * @param string $before
 * @param string $debug_type какую функцию для отладки использовать,
 * по умолчанию var_dump
 */
function fs_debug_data( $data, $before = '', $debug_type = 'var_dump' ) {
	$backtrace = debug_backtrace();
	echo '<pre>';
	printf( "=== DEBUG: %s ===<br>", $before );
	if ( ! empty( $backtrace[0]['file'] ) && ! empty( $backtrace[0]['line'] ) ) {
		printf( "=== FILE: \"%s:%d\" ===<br>", $backtrace[0]['file'], $backtrace[0]['line'] );
	}
	if ( $debug_type == 'var_dump' ) {
		var_dump( $data );
	} elseif ( $debug_type == 'print_r' ) {
		print_r( $data );
	}
	echo "=== END DEBUG $before ===<br>";
	echo '</pre>';
}

/**
 * Displays a list of variations on the product page, output in the form of stylized input radio
 *
 * @param int $product_id
 * @param array $args
 */
function fs_list_variations( $product_id = 0, $args = array() ) {
	global $fs_config;
	$args       = wp_parse_args( $args, array(
		'class'      => 'fs-select-variation',
		'show_price' => true,
		'show_name'  => true,
		'show_sku'   => false
	) );
	$product_id = fs_get_product_id( $product_id );
	$product    = new FS\FS_Product_Class();
	$variations = $product->get_product_variations( $product_id );
	if ( ! empty( $variations ) ) {
		echo '<ul class="' . esc_attr( $args['class'] ) . '">';
		$count = 0;
		foreach ( $variations as $var_id => $variation ) {
			echo '<li class="radiobtn">';
			echo '<input type="radio" name="fs_variation" data-max="' . esc_attr( $variation['count'] ) . '" data-fs-element="select-variation" data-product-id="' . esc_attr( $product_id ) . '" value="' . esc_attr( $var_id ) . '" ' . checked( 0, $count, 0 ) . ' id="fs-var-' . esc_attr( $var_id ) . '">';
			echo '<label for="fs-var-' . esc_attr( $var_id ) . '">';
			// Показываем название в зависимости от настроек
			if ( $args['show_name'] && ! empty( $variation['name'] ) ) {
				echo '<span class="fs-variant-name">' . esc_html( $variation['name'] ) . '</span>';
			}
			// Показываем артикул в зависимости от настроек
			if ( $args['show_sku'] && ! empty( $variation['sku'] ) ) {
				echo '<span class="fs-variant-sku  fs-var-container">(' . esc_attr( $variation['sku'] ) . ')</span>';
			}
			if ( ! empty( $variation['attr'] ) ) {
				foreach ( $variation['attr'] as $attr ) {
					if ( empty( $attr ) ) {
						continue;
					}
					$term             = get_term( $attr, $fs_config->data['product_att_taxonomy'] );
					$term_parent_name = get_term_field( 'name', $term->parent, $fs_config->data['product_att_taxonomy'] );
					$att_type         = get_term_meta( $term->term_id, 'fs_att_type', 1 );
					$att_show         = $term->name;
					if ( $att_type == 'image' ) {
						$image_id = get_term_meta( $term->term_id, 'fs_att_image_value', 1 );
						if ( $image_id ) {
							$image_url = wp_get_attachment_image_url( $image_id, 'full' );
							$att_show  = '<span class="fs-attr-image" style="background-image:url(' . esc_url( $image_url ) . ');"></span>';
						}
					} elseif ( $att_type == 'color' ) {
						$color = get_term_meta( $term->term_id, 'fs_att_color_value', 1 );
						if ( $color ) {
							$att_show = '<span class="fs-attr-color" style="background-color:' . esc_attr( $color ) . ';"></span>';
						}
					}
					echo '<span class="fs-inline-flex align-items-center fs-var-container">' . esc_html( $term_parent_name ) . ': ' . $att_show . '</span> ';

				}
			}
			// Если включено показывать цену
			if ( $args['show_price'] ) {
				if ( ! empty( $variation['action_price'] ) && $variation['price'] > $variation['action_price'] ) {
					$price        = apply_filters( 'fs_price_format', $variation['price'] );
					$price        = apply_filters( 'fs_price_filter', $product_id, $price );
					$action_price = apply_filters( 'fs_price_format', $variation['action_price'] );
					$action_price = apply_filters( 'fs_price_filter', $product_id, $action_price );
					echo '<span class="fs-inline-flex align-items-center fs-variation-price fs-var-container">' . sprintf( '%s <span>%s</span>', esc_attr( $action_price ), esc_attr( fs_currency() ) ) . '</span>';
					echo '<del class="fs-inline-flex align-items-center fs-variation-price fs-var-container">' . sprintf( '%s <span>%s</span>', esc_attr( $price ), esc_attr( fs_currency() ) ) . '</del>';
				} else {
					$price = apply_filters( 'fs_price_format', $variation['price'] );
					$price = apply_filters( 'fs_price_filter', $product_id, $price );
					echo '<span class="fs-inline-flex align-items-center fs-variation-price fs-var-container">' . sprintf( '%s <span>%s</span>', esc_attr( $price ), esc_attr( fs_currency() ) ) . '</span>';
				}
			}
			echo '</label></li>';
			$count ++;
		}
		echo '</ul>';
	}

}

/**
 * Sets the product data passed in the $ product parameter
 *
 *  массив должен состоять как миннимум из двух значений:
 *      $product['ID'] -  ID товара
 *      $product['variation'] -  ID вариации товара
 *  остальные тоже передаются по возможности
 *
 * @param array $product
 *
 * @param int $item_id
 *
 * @return \FS\FS_Product_Class
 */
function fs_set_product( $product, $item_id = 0 ) {
	$product_class = new FS\FS_Product_Class();
	$product_class->set_product( $product, $item_id );

	return $product_class;
}

/**
 * Enables plugin template
 *
 * сначала идет поиск в директории f-shop текущей темы
 * далее ищем дефолтный шаблон в папке плагина "templates/front-end"
 * !!! подключаемый шаблон должен иметь расширение *.php
 *
 * @param string $template_path путь к шаблону (без расширения)
 */
function fs_load_template( $template_path ) {
	$base_template = FS_PLUGIN_NAME . DIRECTORY_SEPARATOR . $template_path;
	if ( file_exists( get_template_directory() . DIRECTORY_SEPARATOR . $base_template . '.php' ) ) {
		get_template_part( $base_template );
	} elseif ( file_exists( FS_PLUGIN_PATH . 'templates/front-end/' . $template_path . '.php' ) ) {
		load_template( FS_PLUGIN_PATH . 'templates/front-end/' . $template_path . '.php', false );
	} else {
		esc_html_e( 'File "%s" not found', 'f-shop' );
	}
}


/**
 * Возвращает все методы доставки
 * обертка функции get_terms()
 *
 * @return array|int|WP_Error
 */
function fs_get_shipping_methods() {
	global $fs_config;
	$shipping_methods = get_terms( array(
		'taxonomy'   => $fs_config->data['product_del_taxonomy'],
		'hide_empty' => false
	) );

	return $shipping_methods;
}

/**
 * Returns true if the item has a label specified in the $label parameter.
 *
 * вы можете указать следующие значения
 * 'pop' - если  в админке установлен чекбокс на "Включить метку "Хит продаж""
 * 'new' - если  в админке установлен чекбокс на "Включить метку "Включить метку "Новинка""
 * 'promo' - если  в админке установлен чекбокс на "Включить метку "Акция""
 *
 * @param $label
 *
 * @return bool
 */
function fs_is_label( $label ) {
	global $post;
	$product_id = intval( $post->ID );
	$label_show = false;
	switch ( $label ) {
		case 'pop':
			$label_show = get_post_meta( $product_id, 'fs_on_bestseller', true ) ? true : false;
			break;
		case 'new':
			$label_show = get_post_meta( $product_id, 'fs_on_novelty', true ) ? true : false;
			break;
		case 'promo':
			$label_show = get_post_meta( $product_id, 'fs_on_promotion', true ) ? true : false;
			break;
	}

	return $label_show;

}

/**
 * Возвращает стоимость доставки в корзине
 *
 * @return float $cost
 */
function fs_get_delivery_cost() {
	global $fs_config;
	$delivery_methods = get_terms( array(
		'taxonomy'   => $fs_config->data['product_del_taxonomy'],
		'hide_empty' => false
	) );
	$cost             = 0.0;
	if ( ! is_wp_error( $delivery_methods ) && count( $delivery_methods ) ) {
		$term_id = intval( $delivery_methods[0]->term_id );
		$cost    = get_term_meta( $term_id, '_fs_delivery_cost', 1 );
	}

	return floatval( $cost );
}

/**
 * Выводит стоимость доставки в корзине
 *
 * @param string $format
 */
function fs_delivery_cost( $format = '%s <span>%s</span>' ) {
	$cost = fs_get_delivery_cost();
	printf( '<span data-fs-element="delivery-cost">' . $format . '</span>', esc_attr( $cost ), esc_html( fs_currency() ) );
}

/**
 * Displays the item rating block in the form of icons
 *
 * @param int $product_id
 * @param array $args
 */
function fs_product_rating( $product_id = 0, $args = array() ) {
	$product = new FS\FS_Product_Class();
	$product->product_rating( $product_id, $args );
}

/**
 * Returns the product category text
 *
 * @param int $category_id
 *
 * @return mixed
 */
function fs_get_category_text( $category_id = 0 ) {
	if ( ! $category_id && is_tax() ) {
		$category_id = get_queried_object_id();
	}

	return get_term_meta( $category_id, '_content', 1 );

}

/**
 * Displays a link to reset the filters.
 *
 * @param string $base_url url to return when pressed
 */
function fs_reset_filter_link( $base_url = '' ) {
	global $fs_config;
	if ( empty( $base_url ) && is_tax() ) {
		$base_url = get_term_link( get_queried_object_id() );
	} else {
		$base_url = get_post_type_archive_link( $fs_config->data['post_type'] );
	}
	echo esc_url( $base_url );
}

function phpinfo2array() {
	$entitiesToUtf8 = function ( $input ) {
		// http://php.net/manual/en/function.html-entity-decode.php#104617
		return preg_replace_callback( "/(&#[0-9]+;)/", function ( $m ) {
			return mb_convert_encoding( $m[1], "UTF-8", "HTML-ENTITIES" );
		}, $input );
	};
	$plainText      = function ( $input ) use ( $entitiesToUtf8 ) {
		return trim( html_entity_decode( $entitiesToUtf8( strip_tags( $input ) ) ) );
	};
	$titlePlainText = function ( $input ) use ( $plainText ) {
		return '# ' . $plainText( $input );
	};

	ob_start();
	phpinfo( - 1 );

	$phpinfo = array( 'phpinfo' => array() );

	// Strip everything after the <h1>Configuration</h1> tag (other h1's)
	if ( ! preg_match( '#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches ) ) {
		return array();
	}

	$input   = $matches[1];
	$matches = array();

	if ( preg_match_all(
		'#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|' .
		'(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
		$input,
		$matches,
		PREG_SET_ORDER
	) ) {
		foreach ( $matches as $match ) {
			$fn = strpos( $match[0], '<th' ) === false ? $plainText : $titlePlainText;
			if ( strlen( $match[1] ) ) {
				$phpinfo[ $match[1] ] = array();
			} elseif ( isset( $match[3] ) ) {
				$keys1                                        = array_keys( $phpinfo );
				$phpinfo[ end( $keys1 ) ][ $fn( $match[2] ) ] = isset( $match[4] ) ? array(
					$fn( $match[3] ),
					$fn( $match[4] )
				) : $fn( $match[3] );
			} else {
				$keys1                      = array_keys( $phpinfo );
				$phpinfo[ end( $keys1 ) ][] = $fn( $match[2] );
			}

		}
	}

	return $phpinfo;
}

<<<<<<< HEAD
/**
 * Возвращает список прикрепленных к посту изобажений
 *
 * @param int $post_id
 *
 * @return array
 */
function fs_get_attached_media( $post_id = 0 ) {
	$attached_media = get_attached_media( 'image', $post_id );
	$attached_ids   = array();
	if ( $attached_media ) {
		$attached_ids = array_keys( $attached_media );
	}

	return array_unique( $attached_ids );
=======
function fs_buy_one_click( $product_id = 0, $text = 'Купить в 1 клик', $args = array() ) {
	$product_id = fs_get_product_id( $product_id );
	$atts       = fs_parse_attr( $args, array(
		'type'            => 'button',
		'class'           => 'fs-buy-one-click',
		'data-fs-element' => 'buy-one-click',
		'data-id'         => $product_id,
		'data-name'       => get_the_title( $product_id ),
		'data-url'        => get_the_permalink( $product_id ),
		'data-price'      => apply_filters( 'fs_price_format', fs_get_price( $product_id ) ),
		'data-currency'   => fs_currency( $product_id ),
		'data-thumbnail'  => fs_get_product_thumbnail_url( $product_id, 'medium' ),
	) );
	echo '<button ' . $atts . '>' . esc_html( $text ) . '</button>';
>>>>>>> b7e37d2448172de20c0dc735a1abd19ba231c12c
}