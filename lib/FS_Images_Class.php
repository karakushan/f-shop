<?php

namespace FS;

/**
 * Store Image Class
 */
class FS_Images_Class {
	function __construct() {
	}

	/**
	 * @param int $product_id
	 *
	 * @return bool|string
	 */
	public function list_gallery( $product_id = 0 ) {
		$product_id = fs_get_product_id( $product_id );
		$gallery    = $this->gallery_images_url( $product_id, true, 'full' );
		$images_n   = '';
		$alt        = get_the_title( $product_id );


		if ( $gallery ) {
			foreach ( $gallery as $image ) {
				$images_n .= "<li data-thumb=\"$image\"  data-src=\"$image\" style=\"background_image( $image )\">";
				$images_n .= "<a href=\"$image\" data-lightbox=\"roadtrip\" data-title=\"" . get_the_title( $product_id ) . "\">";
				$images_n .= "<img src=\"$image\" alt=\"$alt\" itemprop=\"image\" data-zoom-image=\"$image\"></a></li>";
			}
		}

		return apply_filters( 'fs_galery_list', $images_n, $product_id );
	}

	/**
	 * @param integer $post_id - id записи
	 * @param array $args - массив аргументов: http://sachinchoolur.github.io/lightslider/settings.html
	 */
	public function lightslider( $post_id = 0, $args = array() ) {
		$default = array(
			"gallery"   => true,
			"item"      => 1,
			"vertical"  => false,
			"thumbItem" => 3,
			"prevHtml"  => '',
			"nextHtml"  => ''
		);
		$args    = wp_parse_args( $args, $default );
		echo "<script>";
		echo "var fs_lightslider_options=" . json_encode( $args );
		echo "</script>";
		echo "<ul id=\"product_slider\">";
		echo $this->list_gallery( $post_id );
		echo "</ul>";
	}

	/**
	 * Returns an array of product gallery images
	 *
	 * @param int $product_id
	 *
	 * @param bool $thumbnail
	 *
	 * @return mixed
	 */
	public function get_gallery( $product_id = 0, $thumbnail = true ) {
		$fs_config  = new FS_Config();
		$product_id = fs_get_product_id( $product_id );
		$gallery    = get_post_meta( $product_id, $fs_config->get_meta( 'gallery' ), false );
		$gallery    = array_shift( $gallery );
		$gallery    = apply_filters( 'fs_custom_gallery', $gallery, $product_id );
		if ( ! $gallery ) {
			$gallery = [];
		}
		if ( has_post_thumbnail( $product_id ) && $thumbnail ) {
			array_unshift( $gallery, get_post_thumbnail_id( $product_id ) );
		}

		return array_unique( $gallery );

	}

	/**
	 * получаем url изображений галереи в массиве
	 *
	 * @param  integer $product_id - id записи
	 * @param  bool $thumbnail - включать ли в галерею установленную миниатюру поста
	 * @param string $size
	 *
	 * @return array          список id вложений в массиве
	 */
	public function gallery_images_url( $product_id = 0, $thumbnail = true, $size = 'full' ) {
		$product_id     = fs_get_product_id( $product_id );
		$gallery        = $this->get_gallery( $product_id, $thumbnail );
		$gallery_images = [];
		if ( $gallery ) {
			foreach ( $gallery as $key => $image ) {
				$gallery_images[ $image ] = wp_get_attachment_image_url( $image, $size );
			}
		}

		return apply_filters( 'fs_galery_images', $gallery_images, $product_id );
	}


	/**
	 * Updates the gallery by adding $attach_id as a new image
	 *
	 * @param int $product_id
	 * @param int $attach_id
	 *
	 * @return bool
	 */
	function update_gallery( $product_id = 0, $attach_id = 0 ) {

		$product_id = fs_get_product_id( $product_id );
		if ( empty( $attach_id ) || ! is_numeric( $attach_id ) ) {
			return false;
		} else {
			$gallery = $this->get_gallery( $product_id, false );
			if ( ! in_array( $attach_id, $gallery ) ) {
				array_push( $gallery, $attach_id );
				$config = new FS_Config();
				update_post_meta( $product_id, $config->get_meta( 'gallery' ), $gallery );
			}

			return true;
		}
	}

}