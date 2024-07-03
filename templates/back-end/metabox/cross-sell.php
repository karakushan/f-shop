<h3><?php esc_html_e( 'Cross sell products', 'f-shop' ) ?></h3>
<p>
    <button type="button" class="button fs-add-upsell" data-field="fs_cross_sell"><?php esc_html_e( 'Add cross-sell products', 'f-shop' ) ?>
    </button>
</p>

<ul id="fs-upsell-wrapper" class="fs-upsell-wrapper">
	<?php
	$upsells = get_post_meta( get_the_ID(), 'fs_cross_sell', 0 );
	if ( is_array( $upsells ) ) {
		$upsells = array_shift( $upsells );
	}
	if ( ! empty( $upsells ) ) {
		foreach ( $upsells as $upsell ) {
			echo '<li><span>' . esc_html( get_the_title( $upsell ) ) . '</span>';
			echo '<button class="button button-cancel remove-product">&times;</button><input type="hidden" name="fs_cross_sell[]" value="' . esc_attr( $upsell ) . '">';
			echo '</li>';
		}
	}
	?>
</ul>

