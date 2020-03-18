<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 */ ?>
<?php
if ( ! empty( $args['multiple'] ) && ! is_array( $args['value'] ) ) {
	$args['value'] = [];
}
?>
<select name="<?php echo $args['multiple'] ? esc_attr( $name . '[]' ) : esc_attr( $name ) ?>"
        title="<?php echo esc_attr( $args['title'] ) ?>"
        id="<?php echo esc_attr( $args['id'] ) ?>"
        class="<?php echo esc_attr( $args['class'] ) ?> fs-select2" <?php if ( $args['required'] )
	echo 'required="required"' ?> <?php echo $args['multiple'] ? 'multiple="multiple"' : '' ?>>
    <option value=""><?php echo esc_html( $args['first_option'] ) ?></option>
	<?php if ( $args['values'] ): ?>
		<?php foreach ( $args['values'] as $key => $value ): ?>
            <option
                    value="<?php echo esc_attr( $key ) ?>"
				<?php if ( $args['multiple'] && in_array( $key, $args['value'] ) ) {
					echo 'selected="selected"';
				} else {
					selected( $key, $args['value'], true );
				} ?>>
				<?php echo esc_html( $value ) ?>
            </option>
		<?php endforeach; ?>
	<?php endif; ?>
</select>