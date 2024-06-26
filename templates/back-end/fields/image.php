<?php
/**
 * Image field template.
 *
 * @var array $args
 * @var string $name
 */
?>

<figure class="<?php echo esc_attr( $args['class'] ) ?>"
        id="<?php echo esc_attr( $args['id'] ) ?>" <?php if ( ! empty( $args['value'] ) ) {
	echo 'style="background-image: url(' . wp_get_attachment_image_url( intval( $args['value'] ) ) . ');"';
} ?>>
    <div class="controls">
        <button type="button" class="button dashicons dashicons-camera"
                title="<?php echo esc_attr_e( 'Add / Replace', 'f-shop' ) ?>" data-fs-action="select-image"></button>
        <button type="button" <?php if ( empty( $args['value'] ) ) {
			echo 'style="display: none;"';
		} ?> class="button dashicons dashicons-trash"
                title="<?php echo esc_attr_e( 'Delete', 'f-shop' ) ?>" data-fs-action="delete-image"
                data-text="<?php echo esc_attr_e( 'Are you sure you want to delete the image?', 'f-shop' ) ?>"
                data-noimage="/wp-content/plugins/f-shop/assets/img/no-image.png"></button>
    </div>

    <input type="hidden" name="<?php echo esc_attr( $name ) ?>"
           value="<?php echo esc_html( $args['value'] ) ?>">
</figure>

