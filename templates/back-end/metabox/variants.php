

<div x-data='{variants:[]}' x-init="()=>{
this.$store?.FS?.post('fs_get_variations', {product_id: document.getElementById('post_ID').value})
            .then((response) => response.json())
	            .then((response) => {
	            console.log(response.data);
	            }
	            );
        }">
	<button type="button" class="button" id="fs-add-variant"><?php esc_html_e( 'add variant', 'f-shop' ) ?>
		<img src="<?php echo esc_url( FS_PLUGIN_URL . 'assets/img/ajax-loader.gif' ) ?>" alt="preloader"
		     class="fs-preloader">
	</button>
	<a href="javascript:void(0)" class="fs-collapse-all">
		<?php esc_html_e( 'Expand / hide all', 'f-shop' ) ?>
	</a>
	<div id="fs-variants-wrapper">
		<?php
		//	if ( ! empty( $variants ) ) {
		//		foreach ( $variants as $index => $variant ) {
		//			if ( file_exists( FS_PLUGIN_PATH . 'templates/back-end/metabox/product-variations/add-variation.php' ) ) {
		//				include( FS_PLUGIN_PATH . 'templates/back-end/metabox/product-variations/add-variation.php' );
		//			}
		//		}
		//	} ?>
	</div>

</div>
