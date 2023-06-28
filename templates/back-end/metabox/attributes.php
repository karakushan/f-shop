<?php global $post?>
<div class="form-info">

</div>
<div class="fs-add-attributes">
	<table class="wp-list-table widefat fixed striped">
		<tr data-fs-element="item">
			<td><input type="text" placeholder="<?php echo esc_attr_e('Attribute','f-shop'); ?>" data-fs-element="attribute-name"></td>
			<td><input type="text" placeholder="<?php echo esc_attr_e('Value','f-shop'); ?>" data-fs-element="attribute-value"></td>
			<td>
				<button type="button" class="button button-secondary" data-fs-element="add-custom-attribute"
				        data-post-id="<?php echo esc_attr($_GET['post']); ?>"><?php esc_html_e('add attribute', 'f-shop'); ?></button>
			</td>
		</tr>
	</table>
</div>
<div class="fs-atts-list-table">
	<?php  the_terms($_GET['post'],'product-attributes') ?>
	<?php echo FS\FS_Taxonomy::fs_get_admin_product_attributes_table($_GET['post']); ?>
</div>
