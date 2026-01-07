<?php
/**
 * Price Range Slider
 *
 * @var array $vars Template variables passed from fs_range_slider().
 */

$unique_id = isset($vars['unique_id']) ? $vars['unique_id'] : '';
?>
<?php do_action( 'fs_before_range_slider', [ 'wrapper_class' => 'noUiSlider-wrapper', 'unique_id' => $unique_id ] ); ?>
    <div class="flex justify-between gap-2 mb-3">
        <label class="relative flex items-center">
            <span class="text-[12px] text-theme3 absolute top-[-22px] left-[14px]"><?php _e( 'від', 'roov' ) ?>:</span>
            <input type="number"
                   id="fsPriceStartInput<?php echo esc_attr( $unique_id ); ?>"
                   class="w-full h-10 text-center p-3 rounded-[24px] text-theme1 border border-solid border-theme7 font-light focus:border-theme6 outline-none"/></label>
        <label class="relative flex items-center">
            <span class="text-[12px] text-theme3 absolute top-[-22px] left-[14px]">до:</span>
            <input type="number"
                   id="fsPriceEndInput<?php echo esc_attr( $unique_id ); ?>"
                   class="w-full h-10 text-center p-3 rounded-[24px] text-theme1 border border-solid border-theme7 font-light focus:border-theme6 outline-none"/>
        </label>
    </div>
    <div id="fsRangeSlider<?php echo esc_attr( $unique_id ); ?>"></div>
<?php do_action( 'fs_after_range_slider' ); ?>