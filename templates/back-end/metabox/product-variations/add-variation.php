<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 10.11.2018
 * Time: 18:18
 */
global $fs_config;
?>
<div class="fs-rule fs-field-row" data-index="<?php echo $index ?>">
    <a href="#" class="fs-remove-variant"><?php _e( 'remove variant', 'f-shop' ) ?></a>
    <h3><a href="javascript:void(0)" data-fs-element="toggle-accordeon"> <span class="dashicons dashicons-arrow-down-alt2"></span> <?php _e( 'Variant', 'f-shop' ) ?> <span class="index"><?php echo $index + 1 ?></span></a>
        <label class="fs-deactive-variant">
			<?php $deactive = ! empty( $variant['deactive'] ) ? 1 : 0; ?>
            <input type="checkbox" name="fs_variant[<?php echo esc_attr( $index ) ?>][deactive]"
                   value="1" <?php checked( 1,$deactive) ?>> <?php _e( 'Off', 'f-shop' ) ?>
        </label>
    </h3>
    <div class="fs-flex form-row">
        <div class="col">
            <label for=""><?php _e( 'Variant name', 'f-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][name]"
                   value="<?php if ( ! empty( $variant['name'] ) )
				       echo esc_attr( $variant['name'] ) ?>">
        </div>
        <div class="col">
            <label><?php _e( 'SKU', 'f-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][sku]"
                   value="<?php if ( ! empty( $variant['sku'] ) )
				       echo esc_attr( $variant['sku'] ) ?>">
        </div>
    </div>
    <div class="fs-flex"><label class="col-12"><?php _e( 'Properties', 'f-shop' ) ?></label> <a
                class="hide-if-no-js taxonomy-add-new"
                data-fs-element="clone-att">+ <?php _e( 'add property', 'f-shop' ) ?></a></div>
    <div class="fs-flex fs-prop-group">
		<?php
		if ( ! empty( $variant['attr'] ) ) {
			foreach ( $variant['attr'] as $att ) {
				require( FS_PLUGIN_PATH . 'templates/back-end/metabox/product-variations/single-attr.php' );
			}
		}  ?>
    </div>
    <div class="fs-flex form-row">
        <div class="col">
            <label for=""><?php _e( 'Price', 'f-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][price]"
                   value="<?php if ( ! empty( $variant['price'] ) )
				       echo esc_attr( $variant['price'] ) ?>">
        </div>
        <div class="col">
            <label for=""><?php _e( 'Promotional price', 'f-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][action_price]"
                   value="<?php if ( ! empty( $variant['action_price'] ) )
				       echo esc_attr( $variant['action_price'] ) ?>">
        </div>
        <div class="col">
            <label for=""><?php _e( 'Stock', 'f-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][count]"
                   value="<?php if ( ! empty( $variant['count'] ) )
				       echo esc_attr( $variant['count'] ) ?>">
        </div>
    </div>

</div>
