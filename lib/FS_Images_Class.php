<?php

namespace FS;

/**
 * Store Image Class
 */
class FS_Images_Class
{
	public function __construct()
	{
		// Регистрируем хук по умолчанию для отображения галереи
		if (!has_action('fs_product_gallery')) {
			add_action('fs_product_gallery', [$this, 'product_gallery_display'], 10, 2);
		}
	}

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
		$big_gallery_id = uniqid('fs-product-gallery-');
		$thumbs_gallery_id = uniqid('fs-product-gallery-thumbs-');

		$default = array(
			"big_gallery_id"                 => $big_gallery_id,
			"thumbs_gallery_id"            => $thumbs_gallery_id,
			"gallery"            => true,
			"item"               => 1,
			"vertical"           => false,
			"thumbItem"          => 7,
			"height"             => 500,
			"nextHtml"           => '<div class="swiper-button-next fs-swiper-next"></div>',
			"prevHtml"           => '<div class="swiper-button-prev fs-swiper-prev"></div>',
			"paginationHtml"     => '<div class="swiper-pagination"></div>',
			"attachments"        => false,
			"use_post_thumbnail" => true,
			"thumbs_direction"   => null,
			"thumbs_spaceBetween" => null,
			"thumbs_loop"        => null,
			"thumbs_watchSlidesProgress" => null,
			"thumbs_freeMode"    => null,
			"main_spaceBetween"  => null,
			"main_loop"          => null,
			"main_grabCursor"    => null,
			"thumbsSwiper"       => array(),
			"mainSwiper"         => array(),
			"image_alt"          => get_the_title($product_id),
			"image_title"        => get_the_title($product_id),
		);
		$args    = wp_parse_args($args, $default);

		$thumbs_swiper_config = array(
			'direction'           => 'vertical',
			'slidesPerView'       => (int) $args['thumbItem'],
			'spaceBetween'        => 10,
			'loop'                => false,
			'watchSlidesProgress' => true,
			'freeMode'            => true,
		);

		if (is_array($args['thumbsSwiper'])) {
			$thumbs_swiper_config = array_replace_recursive($thumbs_swiper_config, $args['thumbsSwiper']);
		}

		$thumbs_flat_map = array(
			'thumbs_direction'           => 'direction',
			'thumbs_spaceBetween'        => 'spaceBetween',
			'thumbs_loop'                => 'loop',
			'thumbs_watchSlidesProgress' => 'watchSlidesProgress',
			'thumbs_freeMode'            => 'freeMode',
		);

		foreach ($thumbs_flat_map as $arg_key => $config_key) {
			if ($args[$arg_key] !== null) {
				$thumbs_swiper_config[$config_key] = $args[$arg_key];
			}
		}

		$main_swiper_config = array(
			'slidesPerView' => (int) $args['item'],
			'spaceBetween'  => 10,
			'loop'          => false,
		);

		if (is_array($args['mainSwiper'])) {
			$main_swiper_config = array_replace_recursive($main_swiper_config, $args['mainSwiper']);
		}

		$main_flat_map = array(
			'main_spaceBetween' => 'spaceBetween',
			'main_loop'         => 'loop',
			'main_grabCursor'   => 'grabCursor',
		);

		foreach ($main_flat_map as $arg_key => $config_key) {
			if ($args[$arg_key] !== null) {
				$main_swiper_config[$config_key] = $args[$arg_key];
			}
		}

		$gallery_images_ids = self::get_gallery($product_id, $args['use_post_thumbnail'], $args['attachments']);
		$images_count = count($gallery_images_ids);
		$show_navigation = $images_count > 1;
		?>
		<script>
			(function() {
				let retryCount = 0;
				const maxRetries = 50; // Maximum 5 seconds (50 * 100ms)
				const imagesCount = <?php echo $images_count; ?>;
				const showNavigation = <?php echo $show_navigation ? 'true' : 'false'; ?>;
				
				function initProductGallery() {
					retryCount++;
					
					// Check if Swiper is available
					if (typeof Swiper === 'undefined') {
						if (retryCount <= maxRetries) {
							console.warn('Swiper library not loaded yet, retrying... (' + retryCount + '/' + maxRetries + ')');
							setTimeout(initProductGallery, 100);
						} else {
							console.error('Swiper library failed to load after ' + maxRetries + ' attempts');
						}
						return;
					}
					
					if (typeof window.SwiperNavigation === 'undefined' || typeof window.SwiperThumbs === 'undefined') {
						if (retryCount <= maxRetries) {
							console.warn('Swiper modules not loaded yet, retrying... (' + retryCount + '/' + maxRetries + ')');
							setTimeout(initProductGallery, 100);
						} else {
							console.error('Swiper modules failed to load after ' + maxRetries + ' attempts');
						}
						return;
					}

					console.log('Initializing product gallery with IDs:', '<?php echo $thumbs_gallery_id; ?>', '<?php echo $big_gallery_id; ?>');

					// Initialize thumbnail swiper
					const thumbsConfig = <?php echo wp_json_encode($thumbs_swiper_config); ?>;
					const mainConfig = <?php echo wp_json_encode($main_swiper_config); ?>;
					const thumbsSwiper = new Swiper("#<?php echo $thumbs_gallery_id; ?>", thumbsConfig);
					
					console.log('Thumbs swiper initialized:', thumbsSwiper);

					// Initialize main gallery swiper
					mainConfig.modules = [window.SwiperNavigation, window.SwiperThumbs];
					mainConfig.thumbs = Object.assign({}, mainConfig.thumbs || {}, {
						swiper: thumbsSwiper,
					});

					if (typeof mainConfig.grabCursor === "undefined") {
						mainConfig.grabCursor = imagesCount > 1;
					}

					if (typeof mainConfig.navigation === "undefined") {
						mainConfig.navigation = showNavigation ? {
							nextEl: ".fs-swiper-next",
							prevEl: ".fs-swiper-prev",
						} : false;
					} else if (mainConfig.navigation && showNavigation) {
						mainConfig.navigation = Object.assign({
							nextEl: ".fs-swiper-next",
							prevEl: ".fs-swiper-prev",
						}, mainConfig.navigation);
					} else if (!showNavigation) {
						mainConfig.navigation = false;
					}

					mainConfig.on = Object.assign({}, mainConfig.on || {}, {
						init: function() {
							console.log('Main gallery initialized');
							// Workaround for thumbnails sync issue
							setTimeout(() => {
								if (this && this.slides && this.slides.length > 1) {
									this.slideTo(1, 0);
									this.slideTo(0, 0);
									console.log('Thumbnails synced');
								}
							}, 100);
						},
						slideChange: function() {
							console.log('Slide changed to:', this.activeIndex);
						}
					});

					const mainGallerySwiper = new Swiper("#<?php echo $big_gallery_id; ?>", mainConfig);
					
					console.log('Main gallery swiper initialized:', mainGallerySwiper);
				}

				// Start initialization when DOM is ready
				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', initProductGallery);
				} else {
					// DOM already loaded
					initProductGallery();
				}
			})();
		</script>
		<div class="fs-product-gallery">
			<?php
			echo fs_frontend_template('product/gallery', [
				'vars' => [
					'gallery_images_ids' => $gallery_images_ids,
					'args'               => $args
				]
			]);
			?>
		</div>
<?php
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
