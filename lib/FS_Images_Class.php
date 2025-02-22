<?php

namespace FS;

/**
 * Store Image Class
 */
class FS_Images_Class
{
	/**
	 * @param int $product_id
	 *
	 * @param array $args
	 *
	 * @return bool|string
	 */
	public function product_gallery_list($product_id = 0, $args = array())
	{
		$product_id = fs_get_product_id($product_id);
		$gallery    = $this->gallery_images_url($product_id, $args);
		$images_n   = '';
		$alt        = get_the_title($product_id);


		if ($gallery) {

			foreach ($gallery as $image) {
				$images_n .= "<li data-thumb=\"$image\"  data-src=\"$image\" style=\"background_image( $image )\">";
				$images_n .= "<a href=\"$image\" data-lightbox=\"roadtrip\" data-title=\"" . get_the_title($product_id) . "\">";
				$images_n .= "<img src=\"$image\" alt=\"$alt\" itemprop=\"image\" data-zoom-image=\"$image\"></a></li>";
			}
		}

		return apply_filters('fs_galery_list', $images_n, $product_id);
	}

	/**
	 * Displays the product gallery using specified arguments and settings.
	 *
	 * @param int $product_id The ID of the product. Defaults to 0.
	 * @param array $args Array of arguments to customize the gallery display. Includes options like gallery layout, navigation elements, thumbnail settings, and additional configurations.
	 *
	 * @return void
	 */
	public static function product_gallery_display($product_id = 0, $args = array())
	{
		$product_id = fs_get_product_id($product_id);

		$default = array(
			"gallery"            => true,
			"item"               => 1,
			"vertical"           => false,
			"thumbItem"          => 5,
			"nextHtml"           => '<div class="swiper-button-next fs-swiper-next"></div>',
			"prevHtml"           => '<div class="swiper-button-prev fs-swiper-prev"></div>',
			"paginationHtml"     => '<div class="swiper-pagination"></div>',
			"attachments"        => false,
			"use_post_thumbnail" => true,
			"verticalHeight"     => 500,
			"image_alt"          => get_the_title($product_id),
			"image_title"        => get_the_title($product_id),
		);
		$args    = wp_parse_args($args, $default);

		$gallery_images_ids = self::get_gallery($product_id, $args['use_post_thumbnail'], $args['attachments']);

		echo fs_frontend_template('product/gallery', [
			'vars' => [
				'gallery_images_ids' => $gallery_images_ids,
				'args'               => $args
			]
		]);
	}

	/**
	 * Returns an array of product gallery images
	 *
	 * @param int $product_id
	 *
	 * @param bool $thumbnail
	 *
	 * @return array $gallery
	 */
	public static function get_gallery($product_id = 0, $thumbnail = true, $attachments = false)
	{
		$product_id = fs_get_product_id($product_id);
		$gallery    = array();

		// Добавляем миниатюру первым фото в галерее при условии что $thumbnail == TRUE и прикреплена сама миниатюра
		if ($thumbnail && has_post_thumbnail($product_id)) {
			$thumbnail_id = has_post_thumbnail($product_id) ? get_post_thumbnail_id($product_id) : null;
			array_push($gallery, $thumbnail_id);
		}

		// Добавляем изображения из мета поля
		$meta_gallery = get_post_meta($product_id, FS_Config::get_meta('gallery'), false);
		if (! empty($meta_gallery[0]) && is_array($meta_gallery[0])) {
			$gallery = array_merge($gallery, $meta_gallery[0]);
		}

		// Получаем изображения из вложений
		if ($attachments) {
			$attachments_ids = get_posts(array(
				'post_type'      => 'attachment',
				'posts_per_page' => -1,
				'post_parent'    => $product_id,
				'fields'         => 'ids'
			));

			if (is_array($attachments_ids) && count($attachments_ids)) {
				$gallery = array_merge($gallery, $attachments_ids);
			}
		}

		// Получаем изображения первой вариации товара
		$product_variations = FS_Product::get_product_variations($product_id);
		if (! empty($product_variations) && is_array($product_variations)) {
			$product_variations_first = array_shift($product_variations);

			if (! empty($product_variations_first['gallery'])) {
				$gallery = array_merge($gallery, $product_variations_first['gallery']);
			}
		}

		$gallery = array_filter($gallery, function ($item) {
			return (is_numeric($item) && $item > 0) && get_post($item);
		});

		return apply_filters('fs_custom_gallery', array_unique($gallery), $product_id);
	}

	/**
	 * получаем url изображений галереи в массиве
	 *
	 * @param integer $product_id - id записи
	 * @param bool $thumbnail - включать ли в галерею установленную миниатюру поста
	 * @param string $size
	 *
	 * @return array          список id вложений в массиве
	 */
	public function gallery_images_url($product_id = 0, $args = array())
	{
		$product_id = fs_get_product_id($product_id);

		$args = wp_parse_args(
			$args,
			array(
				'thumbnail'   => true,
				'size'        => 'full',
				'attachments' => false
			)
		);

		$gallery = $this->get_gallery($product_id, $args['thumbnail'], $args['attachments']);

		$gallery_images = [];
		if ($gallery) {
			foreach ($gallery as $key => $image) {
				$gallery_images[$image] = wp_get_attachment_image_url($image, $args['size']);
			}
		}

		return apply_filters('fs_gallery_images_url', $gallery_images, $product_id);
	}


	/**
	 * Updates the gallery by adding $attach_id as a new image
	 *
	 * @param int $product_id
	 * @param int $attach_id
	 *
	 * @return bool
	 */
	function update_gallery($product_id = 0, $attach_id = 0)
	{

		$product_id = fs_get_product_id($product_id);
		if (empty($attach_id) || ! is_numeric($attach_id)) {
			return false;
		} else {
			$gallery = $this->get_gallery($product_id, false);
			if (! in_array($attach_id, $gallery)) {
				array_push($gallery, $attach_id);
				$config = new FS_Config();
				update_post_meta($product_id, $config->get_meta('gallery'), $gallery);
			}

			return true;
		}
	}
}
