
<h3>Цены</h3>
<p>В этой вкладке вы можете настроить цены ваших товаров на сайте.</p>
<?php if ($prices=fs_get_all_prices()): ?>
	<?php foreach ($prices as $key => $price): ?>
		<?php if (!$price['on']) continue; ?>
		<div class="fs-field-row clearfix">
			<label ><?php echo $price['name']  ?> <span><?php echo fs_currency() ?></span></label>
			<input type="text" name="<?php echo $price['meta_key'] ?>" id="price" size="10"  value="<?php echo @get_post_meta($post->ID, $price['meta_key'], true); ?>"> <span class="fs-help"><?php echo $price['description'] ?></span>
		</div>
	<?php endforeach ?>
<?php endif ?>