<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 */ ?>
<input type="number" name="<?php echo esc_attr( $name ) ?>" id="<?php echo esc_attr( $args['id'] ) ?>"
       class="<?php echo esc_attr( $args['class'] ) ?>" value="<?php echo esc_html( $args['value'] ) ?>"
       placeholder="<?php echo esc_html( $args['placeholder'] ) ?>"
       title="<?php echo esc_attr( $args['title'] ) ?>"
       step="<?php echo esc_attr( $args['step'] ) ?>"
    <?php if($args['required']) echo 'required' ?> <?php if ( !empty($args['readonly']) ) echo 'readonly' ?>>
