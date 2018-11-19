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
    <a href="#" class="fs-remove-variant"><?php _e( 'remove variant', 'fast-shop' ) ?></a>
    <h3><?php _e( 'Variant', 'fast-shop' ) ?> <span class="index"><?php echo $index + 1 ?></span>
        <label class="fs-deactive-variant">
			<?php $deactive = ! empty( $variant['deactive'] ) ? 1 : 0; ?>
            <input type="checkbox" name="fs_variant[<?php echo esc_attr( $index ) ?>][deactive]"
                   value="1" <?php checked( 1,$deactive) ?>> <?php _e( 'Off', 'fast-shop' ) ?>
        </label>
    </h3>
    <div class="fs-flex form-row">
        <div class="col">
            <label for=""><?php _e( 'Variant name', 'fast-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][name]"
                   value="<?php if ( ! empty( $variant['name'] ) )
				       echo esc_attr( $variant['name'] ) ?>">
        </div>
        <div class="col">
            <label><?php _e( 'SKU', 'fast-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][sku]"
                   value="<?php if ( ! empty( $variant['sku'] ) )
				       echo esc_attr( $variant['sku'] ) ?>">
        </div>
    </div>
    <div class="fs-flex"><label class="col-12"><?php _e( 'Properties', 'fast-shop' ) ?></label> <a
                class="hide-if-no-js taxonomy-add-new"
                data-fs-element="clone-att">+ <?php _e( 'add property', 'fast-shop' ) ?></a></div>
    <div class="fs-flex fs-prop-group">
		<?php
		$args = array(
			'show_option_all'  => 'Свойство товара',
			'show_option_none' => '',
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'show_last_update' => 0,
			'show_count'       => 0,
			'hide_empty'       => 0,
			'child_of'         => 0,
			'exclude'          => '',
			'echo'             => 1,
			'selected'         => 0,
			'hierarchical'     => 1,
			'name'             => 'fs_variant[' . esc_attr( $index ) . '][attr][]',
			'id'               => '',
			'class'            => 'fs_select_variant',
			'depth'            => 0,
			'tab_index'        => 0,
			'taxonomy'         => $fs_config->data['product_att_taxonomy'],
			'hide_if_empty'    => false,

		);
		if ( ! empty( $variant['attr'] ) ) {
			foreach ( $variant['attr'] as $att ) {
				$args['selected'] = $att;
				wp_dropdown_categories( $args );
			}
		} else {
			wp_dropdown_categories( $args );
		} ?>

    </div>
    <div class="fs-flex form-row">
        <div class="col">
            <label for=""><?php _e( 'Price', 'fast-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][price]"
                   value="<?php if ( ! empty( $variant['price'] ) )
				       echo esc_attr( $variant['price'] ) ?>">
        </div>
        <div class="col">
            <label for=""><?php _e( 'Promotional price', 'fast-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][action_price]"
                   value="<?php if ( ! empty( $variant['action_price'] ) )
				       echo esc_attr( $variant['action_price'] ) ?>">
        </div>
        <div class="col">
            <label for=""><?php _e( 'Stock', 'fast-shop' ) ?></label>
            <input type="text" name="fs_variant[<?php echo esc_attr( $index ) ?>][count]"
                   value="<?php if ( ! empty( $variant['count'] ) )
				       echo esc_attr( $variant['count'] ) ?>">
        </div>
    </div>

</div>
