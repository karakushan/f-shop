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






