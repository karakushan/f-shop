<?php
//фильтр преобразует необработанную цену в формат денег
add_filter( 'fs_price_format', 'fs_price_format', 10, 2 );
function fs_price_format( $price, $delimiter = '' ) {
	$cents     = fs_option( 'price_cents' ) == 1 ? 2 : 0;
	$delimiter = ! empty( $delimiter ) ? $delimiter : fs_option( 'currency_delimiter', '.' );
	$price     = number_format( $price, $cents, $delimiter, ' ' );

	return $price;
}

add_filter( 'fs_discount_filter', 'fs_discount_filter__callback', 10, 1 );

/**
 * Вычитывает скидку из общей суммы заказа
 *
 * @param $price - цена без скидки
 *
 * @return mixed
 */
function fs_discount_filter__callback( $price ) {

	global $fs_config;
	$discounts      = get_terms( array( 'taxonomy' => $fs_config->data['discount_taxonomy'], 'hide_empty' => 0 ) );
	$discounts_cart = [];
	if ( $discounts ) {
		foreach ( $discounts as $discount ) {
			$discount_type   = get_term_meta( $discount->term_id, 'discount_where_is', 1 );
			$discount_where  = get_term_meta( $discount->term_id, 'discount_where', 1 );
			$discount_value  = get_term_meta( $discount->term_id, 'discount_value', 1 );
			$discount_amount = get_term_meta( $discount->term_id, 'discount_amount', 1 );
			// если скидка указана в процентах
			if ( strpos( $discount_amount, '%' ) !== false ) {
				$discount_amount = floatval( str_replace( '%', '', $discount_amount ) );
				$discount_amount = $price * $discount_amount / 100;
			}

			if ( $discount_type == 'sum' && $discount_where == '>=' && $price >= $discount_value ) {
				$discounts_cart[] = $discount_amount;
			} elseif ( $discount_type == 'sum' && $discount_where == '>' && $price > $discount_value ) {
				$discounts_cart[] = $discount_amount;
			} elseif ( $discount_type == 'sum' && $discount_where == '<=' && $price <= $discount_value ) {
				$discounts_cart[] = $discount_amount;
			} elseif ( $discount_type == 'sum' && $discount_where == '<' && $price < $discount_value ) {
				$discounts_cart[] = $discount_amount;
			}
		}
	}
	if ( ! empty( $discounts_cart ) && $price > max( $discounts_cart ) ) {
		$price = $price - max( $discounts_cart );
	}

	return $price;

}

// создаем новую колонку
add_filter( 'manage_edit-product_columns', 'add_views_column', 4 );
function add_views_column( $columns ) {
	$num         = 1; // после какой по счету колонки вставлять новые
	$new_columns = array(
		/*		'fs_order'       => __( 'Position', 'fast-shop' ),
			'title'          => __( 'Title', 'fast-shop' ),*/

		'fs_menu_order'  => __( 'Sort', 'fast-shop' ),
		'title'          => __( 'Title', 'fast-shop' ),
		'fs_id'          => __( 'ID', 'fast-shop' ),
		'fs_price'       => __( 'Price', 'fast-shop' ),
		'fs_vendor_code' => __( 'Vendor code', 'fast-shop' ),
		'fs_stock'       => __( 'Stock in stock', 'fast-shop' ),
		'fs_photo'       => __( 'Photo', 'fast-shop' ),
		'fs-product-cat' => __( 'Product categories', 'fast-shop' )

	);

	if ( ! fs_option( 'fs_product_sort_on' ) ) {
		unset( $new_columns['fs_menu_order'] );
	}

	return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
}


// заполняем колонку данными
add_filter( 'manage_product_posts_custom_column', 'fill_views_column', 5, 2 );
function fill_views_column( $colname, $post_id ) {
	$config = new \FS\FS_Config();
	$post   = get_post( $post_id );
	switch ( $colname ) {
		case "fs-product-cat":
			echo '<div class="fs-product-cat-wrap">';
			echo get_the_term_list( $post_id, $config->data['product_taxonomy'], '', ', ', '' );
			echo '</div>';
			break;
		case "fs_menu_order":
			echo '<img src="' . FS_PLUGIN_URL . 'assets/img/sort.svg" width="40" title="Потяните вверх или вниз, чтобы изменить позицию">';
			break;
		case "fs_id":
			echo $post_id;
			break;
		case "fs_price":
			$price = apply_filters( 'fs_price_format', (float) get_post_meta( $post_id, $config->meta['price'], 1 ) );
			echo '<span class="fs-price-blank">' . $price . '</span> ' . fs_currency();

			break;
		case "fs_vendor_code":
			echo get_post_meta( $post_id, $config->meta['sku'], 1 );
			break;
		case "fs_stock":
			$stock      = get_post_meta( $post_id, $config->meta['remaining_amount'], 1 );
			$stock_real = get_post_meta( $post_id, $config->meta['remaining_amount'], 1 );
			if ( $stock == '' || $stock > 0 ) {
				$stock = __( 'in stock', 'fast-shop' );
			} else {
				$stock = __( 'not available', 'fast-shop' );
			}
			echo $stock . '<span class="fs_stock_real" style="display:none">' . $stock_real . '</span>';
			break;
		case "fs_photo":
			$sizes = fs_get_image_sizes();
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'thumbnail' );
			} else {
				echo '<div class="fs_admin_col_photo " style="width:' . $sizes['thumbnail']['width'] . 'px;height:' . $sizes['thumbnail']['height'] . 'px;">' . __( 'no photo', 'fast-shop' ) . '</div>';
			}
			break;
	}
}

// создаем новую колонку
add_filter( 'manage_edit-orders_columns', 'fs_edit_orders_columns', 4 );
function fs_edit_orders_columns( $columns ) {
	$num         = 2; // после какой по счету колонки вставлять новые
	$new_columns = array(
//		'fs_order_id'     => __( 'ID', 'fast-shop' ),
		'fs_order_amount' => __( 'Total cost', 'fast-shop' ),
		'fs_user'         => __( 'Customer', 'fast-shop' )

	);

	return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
}

add_filter( 'manage_orders_posts_custom_column', 'fs_orders_posts_custom_column', 5, 2 );
function fs_orders_posts_custom_column( $colname, $post_id ) {
	switch ( $colname ) {
		/*	case 'fs_order_id':
				echo $post_id;
				break;*/
		case 'fs_order_amount':
			$amount = get_post_meta( $post_id, '_amount', 1 );
			$amount = apply_filters( 'fs_price_format', $amount );
			echo $amount . ' ' . fs_currency();
			break;
		case 'fs_user':
			$user = get_post_meta( $post_id, '_user', 0 );
			$user = $user[0];
			echo '<ul>';
			echo '<li><b>';
			echo $user['first_name'] . ' ' . $user['last_name'];
			echo '</b></li>';
			printf( '<li><span>%s:</span> %s</li>', __( 'phone', 'fast-shop' ), $user['phone'] );
			printf( '<li><span>%s:</span> %s</li>', __( 'email', 'fast-shop' ), $user['email'] );
			printf( '<li><span>%s:</span> %s</li>', __( 'city', 'fast-shop' ), $user['city'] );
			echo '</ul>';


			break;
	}

}

// Выводим поле в быстром редактировании записи
add_action( 'quick_edit_custom_box', 'shiba_add_quick_edit', 10, 2 );
function shiba_add_quick_edit( $column_name, $post_type ) {
	if ( $column_name == 'fs_price' && $post_type == 'product' ) {
		$config = new \FS\FS_Config();
		?>

        <fieldset class="inline-edit-col-left inline-edit-fast-shop">
            <legend class="inline-edit-legend">Настройки товара</legend>
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php _e( 'Price', 'fast-shop' ) ?> (<?php echo fs_currency(); ?>)</span>
                    <span class="input-text-wrap"><input type="text" name="<?php echo $config->meta['price'] ?>"
                                                         class="fs_price"
                                                         value="" required></span>
                </label>
                <label>
                    <span class="title"><?php _e( 'Vendor code', 'fast-shop' ) ?></span>
                    <span class="input-text-wrap"><input type="text" name="<?php echo $config->meta['sku'] ?>"
                                                         class="fs_vendor_code" value=""></span>
                </label>
                <label>
            <span class="title"><?php _e( 'Stock in stock', 'fast-shop' ) ?> (<?php _e( 'units', 'fast-shop' ) ?>
                )</span>
                    <span class="input-text-wrap"><input type="text"
                                                         name="<?php echo $config->meta['remaining_amount'] ?>"
                                                         class="fs_stock" value=""></span>
                </label>
            </div>
        </fieldset>
		<?php
	}
}


// добавляем возможность сортировать колонку
add_filter( 'manage_edit-product_sortable_columns', 'add_views_sortable_column' );
function add_views_sortable_column( $sortable_columns ) {
	$sortable_columns['fs_price'] = 'fs_price';
	$sortable_columns['fs_id']    = 'ID';

	return $sortable_columns;
}

/**
 *  возвращает тег формы и скрытые поля необходимые для безопасности
 *
 * @param array $args
 * @param $ajax_action
 *
 * @return mixed|string|void
 * @internal param array $attr массив атрибутов тега form
 *
 */

function fs_form_header( $args = array(), $ajax_action ) {
	$attr        = fs_parse_attr( $args, array(
		'method'       => 'POST',
		'autocomplete' => 'off',
		'class'        => 'fs-form'
	) );
	$form_header = '<form ' . $attr . '>';
	$form_header .= \FS\FS_Config::nonce_field();
	$form_header .= '<input type="hidden" name="action" value="' . $ajax_action . '">';
	if ( ! empty( $args['title'] ) ) {
		$form_header .= '<div class="fs-form-title">' . $args['title'] . '</div>';
	}
	$form_header .= '<p class="fs-form-info"></p>';

	return $form_header;
}

/**
 * фильтр возвращает низ формы
 * @return string
 */
add_filter( 'fs_form_bottom', 'fs_form_bottom', 10, 1 );
function fs_form_bottom( $form_bottom = '' ) {
	$form_bottom = $form_bottom . '</form>';

	return $form_bottom;

}

/**
 *  фильтр для создания шаблона письма пользователю
 *
 * @param $vars переменные письма
 *
 * @param $message
 *
 * @return mixed|string|void
 */
function fs_email_template( $vars ) {
	$template = 'oxygen';

	$search  = fs_mail_keys( $vars );
	$replace = array_values( $vars );
	$html    = fs_frontend_template( 'mail/themes/' . $template, array( 'vars' => $vars ) );
	$html    = str_replace( $search, $replace, $html );

	return $html;
}

add_filter( 'fs_email_template', 'fs_email_template', 10, 2 );

//  выводит html код плагина в футере
function fs_footer_html() {
	// выводим форму входа
	if ( ! is_user_logged_in() ) {
		echo '<div class="fs-fade fs-modal" id="fs-modal-login">';
		fs_login_form( 1, array( 'title' => 'Вход на сайт' ) );
		echo '</div>';
	}

}

add_action( 'wp_footer', 'fs_footer_html' );

add_filter( 'wp_dropdown_cats', 'fs_dropdown_cats_multiple', 10, 2 );
function fs_dropdown_cats_multiple( $output, $r ) {

	if ( isset( $r['multiple'] ) && $r['multiple'] ) {

		$output = preg_replace( '/^<select/i', '<select multiple size="30"', $output );

		$output = str_replace( "name='{$r['name']}'", "name='{$r['name']}[]'", $output );

		foreach ( array_map( 'trim', explode( ",", $r['selected'] ) ) as $value ) {
			$output = str_replace( "value=\"{$value}\"", "value=\"{$value}\" selected", $output );
		}

	}

	return $output;
}

// вносит коррективы в цену с учётом настроек валюты
add_filter( 'fs_price_filter', 'fs_price_filter_callback', 10, 2 );
function fs_price_filter_callback( $post_id, $price ) {
	if ( fs_option( 'multi_currency_on' ) != 1 ) {
		return $price;
	}
	global $fs_config, $wpdb;
	$default_currency     = fs_option( 'default_currency' );
	$locale               = get_locale();
	$locale_currency_id   = $wpdb->get_var( "SELECT term_id FROM $wpdb->termmeta WHERE meta_key='_fs_currency_locale' AND meta_value='$locale'" );
	$locale_currency_cost = get_term_meta( $locale_currency_id, '_fs_currency_cost', true );
	$product_currency_id  = get_post_meta( $post_id, $fs_config->meta['currency'], 1 );

	if ( ! $locale_currency_id && $default_currency ) {
		$locale_currency_id = $default_currency;
	}

	if(empty($product_currency_id)){
		$product_currency_id=$default_currency;
    }

	// если валюта не указана, то выходим
	if ( empty( $product_currency_id ) || empty( $locale_currency_id ) || $product_currency_id == $locale_currency_id ) {
		return $price;
	}
	// это сработает если валюта товара отличается от валюты по умолчанию
	if ( $product_currency_id != $locale_currency_id && $locale_currency_cost > 0 ) {
		$price = $price / $locale_currency_cost;
		$price = floatval( $price );
	}

	return $price;
}