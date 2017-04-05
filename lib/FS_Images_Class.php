<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Класс работы с изображениями магазина
 */
class FS_Images_Class {
	protected $config;
	public $image_big_width = 9999;// ширина большого изображения слайдера
	public $image_big_height = 330;// высота большого изображения слайдера
	public $image_small_width = 9999; // ширина маленького изображения слайдера
	public $image_small_height = 108;// высота маленького изображения слайдера
	public $image_big_name = 'fs_gallery_big';// название большого изображения слайдера
	public $image_small_name = 'fs_gallery_small';// название маленького изображения слайдера
	public $image_crop = false;// использовать мягкое кадрирование

	function __construct() {
		$this->config = new FS_Config();
		// хук для регистрации размеров изображений для галереи
		add_action( 'init', array( $this, 'register_product_g_thumbnail' ) );
		//  получаем зарегистрированные размеры изобраений
		$this->image_big_width    = fs_option( 'gallery_big_width', $this->image_big_width );
		$this->image_big_height   = fs_option( 'gallery_big_height', $this->image_big_height );
		$this->image_small_width  = fs_option( 'gallery_small_width', $this->image_small_width );
		$this->image_small_height = fs_option( 'gallery_small_height', $this->image_small_height );
	}

	public function register_product_g_thumbnail() {
		if ( function_exists( 'add_image_size' ) ) {
			//  регистрируем два типа изображений: большое - и маленькое слайдера
			add_image_size( $this->image_big_name, $this->image_big_width, $this->image_big_height, $this->image_crop );
			add_image_size( $this->image_small_name, $this->image_small_width, $this->image_small_height, $this->image_crop );
		}
	}

	/**
	 * @param int $post_id
	 * @param array $size
	 *
	 * @return bool|string
	 */
	public function fs_galery_list( $post_id = 0 ) {
		$images_n      = '';
		$gallery_image = '';
		global $post;
		$post_id           = empty( $post_id ) ? $post->ID : $post_id;
		$width             = fs_option( 'gallery_img_width', 300 );
		$height            = fs_option( 'gallery_img_height', 400 );
		$image_placeholder = fs_option( 'image_placeholder', 'holder.js/' . $width . 'x' . $height );
		$galerys           = $this->fs_galery_images( $post_id );
		$images_n          = '';
		$alt               = get_the_title( $post_id );
		// получаем первое изображение галереи из установленной миниатюры поста
		if ( has_post_thumbnail( $post_id ) ) {
			$atach_id    = get_post_thumbnail_id( $post_id );
			$image_big   = wp_get_attachment_image_src( $atach_id, $this->image_big_name );
			$image_small = wp_get_attachment_image_src( $atach_id, $this->image_small_name );
			$image_full  = wp_get_attachment_image_src( $atach_id, 'full' );
			$images_n    .= "<li data-thumb=\"$image_small[0]\" data-src=\"$image_full[0]\" style=\"text-align:center\"><a href=\"$image_full[0]\" data-lightbox=\"roadtrip\" data-title=\"" . get_the_title( $post_id ) . "\"><img src=\"$image_big[0]\" alt=\"$alt\"></a></li>";
		}
		if ( $galerys ) {
			foreach ( $galerys as $atach_id ) {
				$image_big   = wp_get_attachment_image_src( $atach_id, $this->image_big_name );
				$image_small = wp_get_attachment_image_src( $atach_id, $this->image_small_name );
				$image_full  = wp_get_attachment_image_src( $atach_id, 'full' );

				$images_n .= "<li data-thumb=\"$image_small[0]\" data-src=\"$image_full[0]\" style=\"text-align:center\"><a href=\"$image_full[0]\" data-lightbox=\"roadtrip\" data-title=\"" . get_the_title( $post_id ) . "\"><img src=\"$image_big[0]\" alt=\"$alt\"></a></li>";
			}
		}

		if ( empty( $images_n ) ) {
			$images_n .= "<li data-thumb=\"$image_placeholder\" data-src=\"$image_placeholder\"><img src=\"$image_placeholder\" width=\"100%\"></li>";
		}

		return apply_filters( 'fs_galery_list', $images_n, $post_id );
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
			"thumbItem" => 3
		);
		$args    = wp_parse_args( $args, $default );
		$galery  = $this->fs_galery_list( $post_id );
		echo "<script>";
		echo "var fs_lightslider_options=" . json_encode( $args );
		echo "</script>";
		echo "<ul id=\"product_slider\">";
		echo $galery;
		echo "</ul>";
	}

	/**
	 * получаем url изображений галереи в массиве
	 *
	 * @param  integer $post_id - id записи
	 * @param  bool $thumbnail - включать ли в галерею установленную миниатюру поста
	 *
	 * @return array          список id вложений в массиве
	 */
	public function fs_galery_images( $post_id = 0, $thumbnail = true ) {
		global $post;
		$images      = array();
		$gallery_img = array();
		$post_id     = ! empty( $post_id ) ? (int) $post_id : $post->ID;
		$gallery     = get_post_meta( $post_id, 'fs_galery', false );
		$thumb_id    = get_post_thumbnail_id( $post_id );
		// добавляем в галерею изображение миниатюры поста, конечно если $thumbnail верно
		if ( $thumb_id && $thumbnail == true ) {
			$gallery_img[] = $thumb_id;
		}
		$images = ! empty( $gallery[0] ) ? $gallery[0] : array();
		if ( $images ) {
			foreach ( $images as $key => $image ) {
				$gallery_img[] = $image;
			}
		}
		$gallery_img = array_unique( $gallery_img );

		return apply_filters( 'fs_galery_images', $gallery_img, $post_id );
	}

}