<?php if ($this->config->prices): ?>
	<?php foreach ($this->config->prices as $key => $price): ?>
		<p>
			<label for="fs_<?php echo $key ?>"><?php  _e($price['name'],'fast-shop') ?></label>
			<br>
			<input type="text" id="fs_<?php echo $key ?>" name="<?php echo $price['meta_key'] ?>" value="<?php echo @get_post_meta($post->ID, $price['meta_key'], true); ?>" /><span class="tooltip"><?php _e($price['description'],'fast-shop') ?></span>
		</p>
	<?php endforeach ?>
<?php endif ?>