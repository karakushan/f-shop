<?php
//фильтр преобразует необработанную цену в формат денег
add_filter('fs_price_format','fs_price_format',10);
function  fs_price_format($price){
    $cents=fs_option('price_cents')==1?2:0;
    $delimiter=fs_option('currency_delimiter','.');
	$price=number_format($price,$cents,$delimiter,' ');
	return $price;
}

add_filter('fs_first_gallery_image','fs_first_image',10,2);
function fs_first_image($post_id,$size){
	$image_first='';
	if (has_post_thumbnail( $post_id)) {
		$atach_id = get_post_thumbnail_id($post_id);
		$image= wp_get_attachment_image_src($atach_id, $size);
		$image_full= wp_get_attachment_image_src( $atach_id,'full');
		$image_first= "<li data-thumb=\"$image[0]\" data-src=\"$image_full[0]\"><a href=\"$image_full[0]\"  data-lightbox=\"roadtrip\" data-title=\"".get_the_title($post_id)."\"><img src=\"$image[0]\" width=\"100%\"></a></li>";
	}
	return $image_first;
}

/*add_action('in_admin_header', 'my_get_current_screen');
function my_get_current_screen(){
    $screen_info = get_current_screen();

    echo '<pre>';
    print_r($screen_info);
    echo '</pre>';
}*/

// создаем новую колонку
add_filter('manage_edit-product_columns', 'add_views_column', 4);
function add_views_column( $columns ){
    $num = 2; // после какой по счету колонки вставлять новые
    $new_columns = array(
    	'fs_price' => 'Цена',
    	);
    return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
}

// заполняем колонку данными
add_filter('manage_product_posts_custom_column', 'fill_views_column', 5, 2); // wp-admin/includes/class-wp-posts-list-table.php
function fill_views_column( $colname, $post_id ){
	if( $colname === 'fs_price' ){
		fs_the_price($post_id);   
	}
}

// добавляем возможность сортировать колонку
add_filter('manage_edit-product_sortable_columns', 'add_views_sortable_column');
function add_views_sortable_column($sortable_columns){
	$sortable_columns['fs_price'] = 'fs_price_fs_price';

	return $sortable_columns;
}