<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 * @var array $args
 * @var string $name
 */
?>
<?php
$args = wp_parse_args( $args, [
	'multiple' => false
] );

if ( $args['multiple'] && ! is_array( $args['value'] ) ) {
	$args['value'] = [];
	$name          = $name . '[]';
}
do_action( 'qm/debug', $args );

?>
<select name="<?php echo esc_attr( $name ) ?>"
        title="<?php echo esc_attr( $args['title'] ) ?>"
        id="<?php echo esc_attr( $args['id'] ) ?>"
        class="<?php echo esc_attr( $args['class'] ) ?> fs-select2" <?php if ( $args['required'] )
	echo 'required="required"' ?> <?php echo $args['multiple'] ? 'multiple="multiple"' : '' ?>>
    <option value=""><?php echo esc_html( $args['first_option'] ) ?></option>
	<?php if ( $args['values'] ) {
		foreach ( $args['values'] as $key => $value ):
			$selected = ! empty( $args['value'] )
			            && (
				            ( is_array( $args['value'] ) && in_array( $key, $args['value'] ) )
				            ||
				            ( is_string( $args['value'] ) ) && trim( $args['value'] ) == $key
			            ) ? 'selected="selected"' : ''
			?>
            <option value="<?php echo esc_attr( $key ) ?>" <?php echo esc_attr( $selected ) ?>>
				<?php echo esc_html( $value ) ?>
            </option>
		<?php endforeach;
	} ?>
</select>