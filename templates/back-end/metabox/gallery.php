<h3><?php esc_html_e( 'Product gallery', 'f-shop' ); ?></h3>
<p><?php esc_html_e( 'To select several images simultaneously from the media library, press "Ctrl" on the keyboard and click on
    selectable images', 'f-shop' ); ?>.</p>
<div class="fs-field-row clearfix">
    <button type="button" class="button button-secondary"
            id="fs-add-gallery"><?php esc_html_e( 'Choose from the library', 'f-shop' ); ?></button>
</div>

<div class="fs-field-row fs-gallery clearfix">
	<?php if ( $gallery ): ?>
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
                <input type="hidden" name="fs_galery[]" value="<?php echo esc_attr( $img ) ?>">
				<?php echo wp_get_attachment_image( $img, 'medium' ) ?>
            </div>
		<?php endforeach ?>
    </div>
</div>