<h3><?php _e( 'Purchase options', 'fast-shop' ) ?></h3>
<p><?php _e( 'This tab is relevant if you have several varieties of a single product.', 'fast-shop' ) ?></p>
<?php
global $fs_config;
$product       = new FS\FS_Product_Class();
$variants      = $product->get_product_variations( 0, false );
$template_path = FS_PLUGIN_PATH . 'templates/back-end/metabox/product-variations/add-variation.php';
?>
<button type="button" class="button" id="fs-add-variant"><?php _e( 'add variant', 'fast-shop' ) ?> <img
            src="<?php echo FS_PLUGIN_URL . 'assets/img/ajax-loader.gif' ?>" alt="preloader" class="fs-preloader">
</button>
<a href="javascript:void(0)" class="fs-collapse-all"><?php _e( 'Expand / hide all', 'fast-shop' ) ?></a>
<div id="fs-variants-wrapper">
	<?php
	// fs_debug_data( $variants[0], 'variant', 'print_r' );
	if ( ! empty( $variants ) ) {
		foreach ( $variants as $index => $variant ) {
			include( $template_path );
		}
	} ?>
</div>
<div class="clearfix"></div>
