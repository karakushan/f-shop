<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 10.11.2018
 * Time: 18:18
 */
?>
<div class="fs-rule fs-field-row" data-index="<?php echo esc_attr( $index ) ?>">
    <a href="#" class="fs-remove-variant"><?php esc_html_e( 'remove variant', 'f-shop' ) ?></a>
    <h3>
        <a href="javascript:void(0)" data-fs-element="toggle-accordeon">
            <span class="dashicons dashicons-arrow-down-alt2"></span>
			<?php esc_html_e( 'Variant', 'f-shop' ) ?>
            <span class="index"><?php echo esc_html( $index + 1 ) ?>
            </span>
        </a>
        <label class="fs-deactive-variant">
			<?php $deactive = ! empty( $variant['deactive'] ) ? 1 : 0; ?>
            <input type="checkbox" name="fs_variant[<?php echo esc_attr( $index ) ?>][deactive]"
                   value="1" <?php checked( 1, $deactive ) ?>> <?php esc_html_e( 'Off', 'f-shop' ) ?>
        </label>
    </h3>
    <div class="fs-flex form-row">
        <div class="col">
            <label><?php esc_html_e( 'Variant name', 'f-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][name]"
                   value="<?php if ( ! empty( $variant['name'] ) )
				       echo esc_attr( $variant['name'] ) ?>">
        </div>
        <div class="col">
            <label><?php esc_html_e( 'SKU', 'f-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][sku]"
                   value="<?php if ( ! empty( $variant['sku'] ) )
				       echo esc_attr( $variant['sku'] ) ?>">
        </div>
    </div>
    <div class="fs-flex">
        <label class="col-12"><?php esc_html_e( 'Properties', 'f-shop' ) ?></label>
        <a class="hide-if-no-js taxonomy-add-new"
           data-fs-element="clone-att">+ <?php esc_html_e( 'add property', 'f-shop' ) ?></a></div>
    <div class="fs-flex fs-prop-group">
		<?php
		if ( ! empty( $variant['attr'] ) ) {
			foreach ( $variant['attr'] as $att ) {
				require( FS_PLUGIN_PATH . 'templates/back-end/metabox/product-variations/single-attr.php' );
			}
		} ?>
    </div>
    <div class="fs-flex form-row">
        <div class="col">
            <label for=""><?php esc_html_e( 'Price', 'f-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][price]"
                   value="<?php if ( ! empty( $variant['price'] ) )
				       echo esc_attr( $variant['price'] ) ?>">
        </div>
        <div class="col">
            <label><?php esc_html_e( 'Promotional price', 'f-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][action_price]"
                   value="<?php if ( ! empty( $variant['action_price'] ) )
				       echo esc_attr( $variant['action_price'] ) ?>">
        </div>
        <div class="col">
            <label for=""><?php esc_html_e( 'Stock', 'f-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][count]"
                   value="<?php if ( isset( $variant['count'] ) && is_numeric( $variant['count'] ) )
				       echo esc_attr( $variant['count'] ) ?>">
        </div>
    </div>
</div>