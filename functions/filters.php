<?php
//фильтр преобразует необработанную цену в формат денег
add_filter('fs_price_format','fs_price_format',10,3);
function  fs_price_format($price,$delimiter='.',$thousands_separator=' '){
	$price=number_format($price,0,$delimiter,$thousands_separator);
	return $price;
}

add_filter('fs_first_gallery_image','fs_first_image',10,2);
function fs_first_image($post_id,$size){
	$image_first='';
	if (has_post_thumbnail( $post_id)) {
		$atach_id = get_post_thumbnail_id($post_id);
		$image= wp_get_attachment_image_src($atach_id, $size);
		$image_full= wp_get_attachment_image_src( $atach_id,'full');

		$image_first= "<li data-thumb=\"$image[0]\" data-src=\"$image_full[0]\"><a href=\"$image_full[0]\"  data-lightbox=\"roadtrip\" data-title=\"".get_the_title($post_id)."\"><img src=\"$image_full[0]\" width=\"100%\"></a></li>";
	}
	return $image_first;	

}