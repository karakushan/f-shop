<?php
/**
 * Price Range Slider
 *
 * @var array $vars Template variables including price_data with cached min/max values
 */
$price_data = $vars['price_data'] ?? [];
$has_cache = !empty($price_data['has_cache']);
$price_min = $price_data['min'] ?? 0;
$price_max = $price_data['max'] ?? 0;

do_action( 'fs_before_range_slider', [
    'wrapper_class' => 'noUiSlider-wrapper',
    'price_data'    => $price_data,
] ); ?>
    <div class="flex justify-between gap-2 mb-3">
        <label class="relative flex items-center">
            <span class="text-[12px] text-theme3 absolute top-[-22px] left-[14px]"><?php _e( 'від', 'roov' ) ?>:</span>
            <input type="number" id="fsPriceStartInput"
                   class="w-full h-10 text-center p-3 rounded-[24px] text-theme1 border border-solid border-theme7 font-light focus:border-theme6 outline-none"/></label>
        <label class="relative flex items-center">
            <span class="text-[12px] text-theme3 absolute top-[-22px] left-[14px]">до:</span>
            <input type="number" id="fsPriceEndInput"
                   class="w-full h-10 text-center p-3 rounded-[24px] text-theme1 border border-solid border-theme7 font-light focus:border-theme6 outline-none"/>
        </label>
    </div>
    <div id="fsRangeSlider"></div>
<?php do_action( 'fs_after_range_slider' ); ?>