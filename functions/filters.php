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
		'fs_price' => 'Цена',
	);

	return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
}

// заполняем колонку данными
add_filter( 'manage_product_posts_custom_column', 'fill_views_column', 5, 2 ); // wp-admin/includes/class-wp-posts-list-table.php
function fill_views_column( $colname, $post_id ) {
	if ( $colname === 'fs_price' ) {
		fs_the_price( $post_id );
	}
}

// добавляем возможность сортировать колонку
add_filter( 'manage_edit-product_sortable_columns', 'add_views_sortable_column' );
function add_views_sortable_column( $sortable_columns ) {
	$sortable_columns['fs_price'] = 'fs_price_fs_price';

	return $sortable_columns;
}

/**
 * подготовка письма для отсылки пользователю
 * берёт шаблон из папки /wp-content/plugins/fast-shop/templates/front-end/mail/mail-user.php
 * заменяет в нём переменные %products_listing% и  %mail_body%
 * %products_listing% - листинг купленных товаров
 * %mail_body% - письмо составленное администратором в админке для пользователя
 */
add_filter( 'fs_order_user_message', 'fs_order_user_message' );
function fs_order_user_message( $fs_products ) {
	$products = '';
	if ( $fs_products ) {
		foreach ( $fs_products as $id => $product ) {
			$products .= fs_frontend_template( 'mail/products-listing', array( 'id' => $id, 'product' => $product ) );
		}
	}
	$message_body  = fs_option( 'customer_mail' );
	$full_template = fs_frontend_template( 'mail/mail-user' );
	$template      = str_replace( array( '%products_listing%', '%mail_body%' ), array(
		$products,
		$message_body
	), $full_template );

	return $template;
}

/**
 * подготовка письма для отсылки администратору
 * берёт шаблон из папки /wp-content/plugins/fast-shop/templates/front-end/mail/mail-admin.php
 * заменяет в нём переменные %products_listing% и  %mail_body%
 * %products_listing% - листинг купленных товаров
 * %mail_body% - письмо составленное администратором в админке для менеджера или себя
 */
add_filter( 'fs_order_admin_message', 'fs_order_admin_message' );
function fs_order_admin_message( $fs_products ) {
	$products = '';
	if ( $fs_products ) {
		foreach ( $fs_products as $id => $product ) {
			$products .= fs_frontend_template( 'mail/products-listing', array( 'id' => $id, 'product' => $product ) );
		}
	}
	$message_body  = fs_option( 'admin_mail' );
	$full_template = fs_frontend_template( 'mail/mail-admin' );
	$template      = str_replace( array( '%products_listing%', '%mail_body%' ), array(
		$products,
		$message_body
	), $full_template );

	return $template;
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






