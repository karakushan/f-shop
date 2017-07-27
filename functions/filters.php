<?php
//фильтр преобразует необработанную цену в формат денег
add_filter( 'fs_price_format', 'fs_price_format', 10, 2 );
function fs_price_format( $price, $delimiter = '' ) {
	$cents     = fs_option( 'price_cents' ) == 1 ? 2 : 0;
	$delimiter = ! empty( $delimiter ) ? $delimiter : fs_option( 'currency_delimiter', '.' );
	$price     = number_format( $price, $cents, $delimiter, ' ' );

	return $price;
}

// создаем новую колонку
add_filter( 'manage_edit-product_columns', 'add_views_column', 4 );
function add_views_column( $columns ) {
	$num         = 2; // после какой по счету колонки вставлять новые
	$new_columns = array(
		'fs_price' => __( 'Price', 'fast-shop' ),
		'fs_photo' => __( 'Photo', 'fast-shop' ),
	);

	return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
}

// заполняем колонку данными
add_filter( 'manage_product_posts_custom_column', 'fill_views_column', 5, 2 ); // wp-admin/includes/class-wp-posts-list-table.php
function fill_views_column( $colname, $post_id ) {
	if ( $colname === 'fs_price' ) {
		$config = new \FS\FS_Config();
		$price  = apply_filters( 'fs_price_format', (float) get_post_meta( $post_id, $config->meta['price'], 1 ) );
		echo $price . ' ' . fs_currency();
	}
	if ( $colname === 'fs_photo' ) {
		$sizes = fs_get_image_sizes();
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'thumbnail' );
		} else {
			echo '<div class="fs_admin_col_photo " style="width:' . $sizes['thumbnail']['width'] . 'px;height:' . $sizes['thumbnail']['height'] . 'px;">' . __( 'no photo', 'fast-shop' ) . '</div>';
		}
	}
}

// изменяем запрос при сортировке колонки
add_filter( 'pre_get_posts', 'fs_sort_admin_by', 30 );
function fs_sort_admin_by( $object ) {
//	эсли это не админка или нет параметра orderby то выходим из функции
	if ( ! is_admin() || empty( $_GET['orderby'] ) && isset( $_GET['post_type'] ) && $_GET['post_type'] != 'product' ) {
		return;
	}
//	сортируем по цене
	if ( ! empty( $_GET['orderby'] ) && $_GET['orderby'] == 'fs_price' ) {
		$config = new \FS\FS_Config();
		$object->set( 'orderby', 'meta_value_num' );
		$object->set( 'meta_key', $config->meta['price'] );
		$object->set( 'order', (string) $_GET['order'] );

	} //	сортируем по дате
	elseif (! empty( $_GET['orderby'] ) && $_GET['orderby'] == 'date' ) {
		$object->set( 'orderby', 'date' );
		$object->set( 'order', (string) $_GET['order'] );
	}//	сортируем по умолчанию по  дате
	else {
		$object->set( 'orderby', 'date' );
		$object->set( 'order', 'DESC' );
	}
}

// добавляем возможность сортировать колонку
add_filter( 'manage_edit-product_sortable_columns', 'add_views_sortable_column' );
function add_views_sortable_column( $sortable_columns ) {
	$sortable_columns['fs_price'] = 'fs_price';

	return $sortable_columns;
}

/**
 * Фильтр возвращает тег формы и скрытые поля необходимые для безопасности
 *
 * @param array $attr массив атрибутов тега form
 *
 * @return mixed|string|void
 */
add_filter( 'fs_form_header', 'fs_form_header', 10, 2 );
function fs_form_header( $attr, $ajax_action ) {
	$attr        = fs_parse_attr( $attr );
	$form_header = '<form ' . $attr . '>';
	$form_header .= wp_nonce_field( 'fast-shop', '_wpnonce', true, false );
	$form_header .= '<input type="hidden" name="action" value="' . $ajax_action . '">';
	$form_header .= '<p class="fs-form-info"></p>';

	return $form_header;
}

/**
 * фильтр возвращает низ формы
 * @return string
 */
add_filter( 'fs_form_bottom', 'fs_form_bottom', 10, 1 );
function fs_form_bottom( $form_bottom ) {
	$form_bottom = $form_bottom . '</form>';

	return $form_bottom;

}

/**
 *  фильтр для создания шаблона письма пользователю
 */
add_filter( 'fs_order_user_message', 'fs_order_user_message', 10, 1 );
function fs_order_user_message( $template = '' ) {
	$default_template = 'oxygen';
	if ( empty( $template ) ) {
		$template = fs_frontend_template( 'mail/themes/' . $default_template );
	}

	return $template;
}

/**
 *  фильтр для создания шаблона письма администратору
 */
add_filter( 'fs_order_admin_message', 'fs_order_admin_message', 10, 1 );
function fs_order_admin_message( $template = '' ) {
	$default_template = 'oxygen';
	if ( empty( $template ) ) {
		$template = fs_frontend_template( 'mail/themes/' . $default_template );
	}

	return $template;
}






