<h3><?php esc_html_e('Product attributes','f-shop'); ?></h3>
<p><?php esc_html_e('Here you can specify what properties the product has.','f-shop'); ?></p>
<div class="form-info">

</div>
<div class="fs-add-attributes">
	<table class="wp-list-table widefat fixed striped">
		<tr data-fs-element="item">
			<td><input type="text" placeholder="<?php echo esc_attr_e('Attribute','f-shop'); ?>" data-fs-element="attribute-name"></td>
			<td><input type="text" placeholder="<?php echo esc_attr_e('Value','f-shop'); ?>" data-fs-element="attribute-value"></td>
			<td>
				<button type="button" class="button button-secondary" data-fs-element="add-custom-attribute"
				        data-post-id="<?php echo esc_attr($post->ID); ?>"><?php esc_html_e('add attribute', 'f-shop'); ?></button>
			</td>
		</tr>
	</table>
</div>
<div class="fs-atts-list-table">
	<?php echo FS\FS_Taxonomy::fs_get_admin_product_attributes_table($post->ID); ?>
</div>
