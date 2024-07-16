<?php
/**
 * Price Range Slider
 *
 * @var float|int $price_min Minimum price
 * @var float|int $price_max Maximum price
 * @var float|int $price_start Start price
 * @var float|int $price_end End price
 */
$data = [
	'fsRangeSlider' => null,
	'price_min'     => $price_min,
	'price_max'     => $price_max,
	'price_start'   => $price_start,
	'price_end'     => $price_end
]
?>
<?php do_action( 'fs_before_range_slider', [ 'data' => $data, 'wrapper_class' => 'noUiSlider-wrapper' ] ); ?>
    <div class="flex justify-between gap-2 mb-3">
        <label class="relative flex items-center">
            <span class="text-[12px] text-theme3 absolute left-[10px]"><?php _e( 'From', 'f-shop' ) ?>:</span>
            <input type="text" x-model="price_start"
                   class="w-full h-10 text-center p-3 rounded-[24px] text-theme1 border border-solid border-theme7 font-light focus:border-theme6 outline-none"/></label>
        <label class="relative flex items-center">
            <span class="text-[12px] text-theme3 absolute left-[10px]"><?php _e( 'To', 'f-shop' ) ?>:</span>
            <input type="text" x-model="price_end"
                   class="w-full h-10 text-center p-3 rounded-[24px] text-theme1 border border-solid border-theme7 font-light focus:border-theme6 outline-none"/>
        </label>
    </div>
    <div x-ref="fsRangeSlider"></div>
<?php do_action( 'fs_after_range_slider' ); ?>