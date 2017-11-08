<h3>Цены</h3>
<p>В этой вкладке вы можете настроить цены ваших товаров на сайте.</p>
<?php if ( $prices = fs_get_all_prices() ): ?>
	<?php foreach ( $prices as $key => $price ): ?>
		<?php if ( ! $price['on'] ) {
			continue;
		} ?>
    <div class="fs-field-row clearfix">
      <label><?php echo $price['name'] ?> <span><?php echo fs_currency() ?></span></label>
      <input type="text" name="<?php echo $price['meta_key'] ?>" id="price" size="10"
             value="<?php echo @get_post_meta( $post->ID, $price['meta_key'], true ); ?>"> <span
        class="fs-help"><?php echo $price['description'] ?></span>
    </div>
	<?php endforeach ?>
<?php endif ?>
<div class="fs-field-row clearfix">
  <label>Валюта товара</label>
	<?php
	wp_dropdown_categories( array(
		'taxonomy'         => 'fs-currencies',
		'echo'             => 1,
		'hide_empty'       => 0,
		'selected'         => get_post_meta( $post->ID, 'fs_currency', true ),
		'name'             => 'fs_currency',
		'show_option_all' => __( 'Select currency', 'fast-shop' ),
	) ) ?>
  <span
    class="fs-help">Каждый товар может котироваться в разной валюте</span>
</div>
