<h3><?php _e('Purchase options','fast-shop') ?></h3>
<p><?php _e('This tab is relevant if you have several varieties of a single product.','fast-shop') ?></p>
<?php
global $fs_config;
$variants             = get_post_meta( $post->ID, 'fs_variant', 0 );
$template_path        = FS_PLUGIN_PATH . 'templates/back-end/metabox/product-variations/add-variation.php';
?>
<button type="button" class="button" id="fs-add-variant"><?php _e('add variant','fast-shop') ?> <img
            src="<?php echo FS_PLUGIN_URL . 'assets/img/ajax-loader.gif' ?>" alt="preloader" class="fs-preloader">
</button>
<div id="fs-variants-wrapper">
	<?php
	// fs_debug_data( $variants[0], 'variant', 'print_r' );
	if ( ! empty( $variants[0] ) ) {
		foreach ( $variants[0] as $index => $variant ) {
			include( $template_path );
		}
	} ?>
</div>
<div class="clearfix"></div>
