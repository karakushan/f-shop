<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 */
if ( $args['required'] ){
    $args['placeholder']=$args['placeholder'].'*';
}
?>
<textarea name="<?php echo esc_attr( $name ) ?>" id="<?php echo esc_attr( $args['id'] ) ?>"
          class="<?php echo esc_attr( $args['class'] ) ?>"
          rows="<?php echo esc_attr( $args['textarea_rows'] ) ?>"
          title="<?php echo esc_attr( $args['title'] ) ?>"
          placeholder="<?php echo esc_html( $args['placeholder'] ) ?>" <?php if ( $args['required'] )
	echo 'required' ?> <?php if ( !empty($args['readonly']) ) echo 'readonly' ?>><?php echo esc_html( $args['value'] ) ?></textarea>
