<?php
/**
 * Price Range Slider
 * @package F-Shop
 *
 * @var array $args
 */
?>
<div class="fs-price-range" data-fs-element="jquery-ui-slider">
    <div data-fs-element="range-slider" id="range-slider"></div>

    <div class="fs-price-range__inputs">
        <div class="fs-price-range__col">
            <label for="fs-price-range-input-from"><?php esc_html_e( 'from', 'f-shop' ) ?></label>
            <input type="number"
                   value="0"
                   data-fs-element="range-start-input"
                   class="fs-price-range__input"
                   id="fs-price-range-input-from"
                   data-url="<?php fs_filter_link( [], null, array( 'price_start' ) ) ?>" min="0">
        </div>

        <div class="fs-price-range__col">
            <label for="fs-price-range-input-to"><?php esc_html_e( 'to', 'f-shop' ) ?></label>
            <input type="number"
                   value="<?php echo esc_attr( $args['price_max'] ) ?>"
                   data-fs-element="range-end-input"
                   data-url="<?php fs_filter_link( [], null, array( 'price_end' ) ) ?>"
                   id="fs-price-range-input-to"
                   class="fs-price-range__input" min="0">
        </div>
    </div>
</div>
