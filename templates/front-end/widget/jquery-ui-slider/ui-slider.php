<?php
/**
 * Price Range Slider
 */ ?>
<div class="slider" data-fs-element="jquery-ui-slider">
    <div data-fs-element="range-slider" id="range-slider"></div>
    <div class="clearfix"></div>
    <div class="price--inputs">
        <span>
            <?php esc_html_e('from', 'f-shop') ?>
            <input type="text" value="0" data-fs-element="range-start-input"
                   data-url="<?php fs_filter_link([], null, array('price_start')) ?>">
        </span>
        <span>
            <?php esc_html_e('to', 'f-shop') ?>
            <input type="text" value="<?php echo esc_attr($args['price_max']) ?>" data-fs-element="range-end-input"
                   data-url="<?php fs_filter_link([], null, array('price_end')) ?>">
            <?php echo esc_html($args['currency']) ?>.</span>
    </div>
</div>
