<?php if ($prices=fs_get_all_prices()): ?>
	<?php foreach ($prices as $key => $price): ?>
		<p>
			<label for="fs_<?php echo $key ?>"><?php echo $price['name']  ?></label>
			<br>
			<input type="text" id="fs_<?php echo $key ?>" name="<?php echo $price['meta_key'] ?>" value="<?php echo @get_post_meta($post->ID, $price['meta_key'], true); ?>" /><span class="tooltip"><?php echo $price['description'] ?></span>
		</p>
	<?php endforeach ?>
<?php endif ?>