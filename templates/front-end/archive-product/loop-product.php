<?php
/**
 * Template part for displaying post archives and search results
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <div class="fs-thumbnail-meta">
        <figure>
            <a href="<?php the_permalink() ?>"
               title="<?php echo esc_attr(sprintf(__('Go to &laquo;%s&raquo;', 'f-shop'), get_the_title())) ?>">
                <?php fs_product_thumbnail(0, 'full') ?>
            </a>
        </figure>
        <div class="fs-meta">
            <?php the_title('<h3><a href="' . esc_url(get_the_permalink()) . '" title="' . esc_attr(sprintf(__('Go to &laquo;%s&raquo;', 'f-shop'), get_the_title())) . '">', '</a></h3>') ?>
            <?php the_excerpt(); ?>
            <div class="fs-price-row">
                <?php fs_the_price() ?>
                <?php fs_base_price() ?>
            </div>
            <div class="fs-atc-row">
                <?php fs_quantity_product() ?>
                <?php fs_add_to_cart() ?>
                <?php fs_add_to_wishlist(0, '<i class="fas fa-heart"></i>') ?>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</article><!-- #post-${ID} -->
