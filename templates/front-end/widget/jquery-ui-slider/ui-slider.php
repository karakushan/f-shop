<?php
/**
 * Price Range Slider
 */
?>
<?php do_action( 'fs_before_range_slider', [ 'wrapper_class' => 'noUiSlider-wrapper' ] ); ?>
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