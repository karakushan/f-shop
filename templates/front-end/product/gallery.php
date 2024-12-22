<?php
/**
 * @var array $gallery_images_ids An array of image IDs for the gallery.
 * @var array $args Additional arguments for the gallery configuration.
 */
?>
<div class="fs-product-gallery">
    <div class="fs-product-gallery__thumbs">
        <div class="swiper swiper-container" id="productGalleryThumbs">
            <div class="swiper-wrapper">
				<?php foreach ( $gallery_images_ids as $image_id ) : ?>
                    <div class="fs-product-gallery__thumb swiper-slide">
						<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
                    </div>
				<?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="fs-product-gallery__main">
        <div class="swiper swiper-container" id="productGallery">
            <div class="swiper-wrapper">
				<?php foreach ( $gallery_images_ids as $image_id ) : ?>
                    <div class="fs-product-gallery__main-slide swiper-slide">
                        <a data-fslightbox="product-gallery"
                           href="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'full' ) ) ?>">
							<?php echo wp_get_attachment_image( $image_id, 'large' ); ?>
                        </a>
                    </div>
				<?php endforeach; ?>
            </div>
            <!-- navigation buttons -->
			<?php echo $args['nextHtml'] ?: '' ?>
			<?php echo $args['prevHtml'] ?: '' ?>
			<?php echo $args['paginationHtml'] ?: '' ?>
        </div>
    </div>
</div>
