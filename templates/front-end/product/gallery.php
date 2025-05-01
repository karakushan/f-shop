<?php

/**
 * @var array $gallery_images_ids An array of image IDs for the gallery.
 * @var array $args Additional arguments for the gallery configuration.
 */
?>
<div class="fs-product-gallery">
    <div class="fs-product-gallery__thumbs">
        <div class="swiper" id="<?php echo $args['thumbs_gallery_id']; ?>">
            <div class="swiper-wrapper">
                <?php foreach ($gallery_images_ids as $image_id) : ?>
                    <div class="swiper-slide">
                        <div class="fs-product-gallery__thumb ">
                            <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="fs-product-gallery__main">
        <div class="swiper" id="<?php echo $args['big_gallery_id']; ?>">
            <div class="swiper-wrapper">
                <?php foreach ($gallery_images_ids as $image_id) : ?>
                    <div class="swiper-slide">
                        <div class="fs-product-gallery__main-slide">
                            <a data-fslightbox="product-gallery"
                                href="<?php echo esc_url(wp_get_attachment_image_url($image_id, 'full')) ?>">
                                <?php echo wp_get_attachment_image($image_id, 'large'); ?>
                            </a>
                        </div>
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