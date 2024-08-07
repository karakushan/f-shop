<?php
/**
 * Price Range Slider
 */
?>
<?php do_action( 'fs_before_range_slider', [ 'wrapper_class' => 'noUiSlider-wrapper' ] ); ?>
<div class="flex justify-between gap-2 mb-3">
    <label class="relative flex items-center">
        <span class="text-[12px] text-theme3 absolute left-[10px]">від:</span>
        <input type="text" x-model="price_start"
               class="w-full h-10 text-center p-3 rounded-[24px] text-theme1 border border-solid border-theme7 font-light focus:border-theme6 outline-none"/></label>
    <label class="relative flex items-center">
        <span class="text-[12px] text-theme3 absolute left-[10px]">до:</span>
        <input type="text" x-model="price_end"
               class="w-full h-10 text-center p-3 rounded-[24px] text-theme1 border border-solid border-theme7 font-light focus:border-theme6 outline-none"/>
    </label>
</div>
<div x-ref="fsRangeSlider"></div>
<?php do_action( 'fs_after_range_slider' ); ?>

