<?php
/**
 * Price Range Slider
 *
 * @var float|int $price_min Minimum price
 * @var float|int $price_max Maximum price
 * @var float|int $price_start Start price
 * @var float|int $price_end End price
 */
?>
<div class="slider" data-fs-element="jquery-ui-slider">
	<div class="price--inputs">
		<input type="text"
		       value="<?php echo esc_attr( $price_start ) ?>"
		       data-fs-element="range-start-input"
		       placeholder=" <?php esc_html_e( 'from', 'f-shop-2' ) ?>"
		       data-url="<?php fs_filter_link( [], null, array( 'price_start' ) ) ?>">
		<input type="text" value="<?php echo esc_attr( $price_end ) ?>"
		       data-fs-element="range-end-input"
		       placeholder="<?php esc_html_e( 'to', 'f-shop-2' ) ?>"
		       data-url="<?php fs_filter_link( [], null, array( 'price_end' ) ) ?>">
		<button class="btn btn-primary">Ok</button>
	</div>
	<div data-min="<?php echo esc_attr( $price_min ) ?>"
	     data-max="<?php echo esc_attr( $price_max ) ?>"
	     data-fs-element="range-slider" id="range-slider"></div>
</div>
