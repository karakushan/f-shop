<?php
/**
 * Gallery field template.
 *
 * @var $args array
 * @var $name string
 */

$gallery = (array) $args['value'];
?>
<div class="fs-field-row clearfix">
	<button type="button" class="button button-secondary"
	        id="fs-add-gallery"><?php esc_html_e( 'Choose from the library', 'f-shop' ); ?></button>
</div>

<div class="fs-field-row fs-gallery clearfix">
	<?php if ( ! empty( $gallery ) ): ?>
		<p><?php esc_html_e( 'You can drag images to change positions in the gallery.', 'f-shop' ); ?>.</p>
	<?php endif ?>
	<div class="fs-grid fs-sortable-items" id="fs-gallery-wrapper">
		<?php if ( $gallery ) ?>
		<?php foreach ( $gallery as $key => $img ):
			if ( ! file_exists( get_attached_file( $img ) ) ) {
				continue;
			}
			?>
			<div class="fs-col-4" draggable="true">
				<div class="fs-remove-img" title="<?php esc_attr_e( 'Remove from gallery', 'f-shop' ) ?>"></div>
				<input type="hidden" name="<?php echo esc_attr( $name ) ?>[]" value="<?php echo esc_attr( $img ) ?>">
				<?php echo wp_get_attachment_image( $img, 'medium' ) ?>
			</div>
		<?php endforeach ?>
	</div>
</div>