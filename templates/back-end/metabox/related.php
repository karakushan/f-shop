<p class="add-related-sect">
	<label for="fs_product_article"><?php _e( 'Related products', 'f-shop' ) ?></label>
	<br>
	<button type="button" class="add-rell" data-fs-action="enabled-select">добавить</button>
	<select name="fs_related_category[]" data-post="<?php echo $post->ID ?>" style="display: none" data-fs-action="get_taxonomy_posts">
		<option value=""><?php _e('Select a category','f-shop'); ?></option>
		<?php $categories=get_terms(array('taxonomy'=>'catalog','hide_empty'=>false));  if ($categories): ?>
		<?php foreach ($categories as $key => $category): ?>
			<option value="<?php echo $category->term_id ?>"><?php echo $category->name ?></option>
		<?php endforeach ?>
	<?php endif ?>
</select>

</p>
<ol class="related-wrap">
	<?php $related_products=get_post_meta($post->ID, $this->config->meta['related_products'], 1); ?>
	<?php if ($related_products){
		$body='';
		foreach ($related_products as $key => $related_product) {
			$body.='<li class="single-rel">';
			$body.='<span>'.get_the_title($related_product).'</span> <button type="button" data-fs-action="delete_parents" class="related-delete" data-target=".single-rel">удалить</button>';
			$body.='<input type="hidden" name="fs_related_products[]" value="'.$related_product.'">';
			$body.='</li>';
		}

		echo $body;
	}  ?>
</ol>