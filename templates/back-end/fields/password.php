<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 */ ?>
<input type="password" name="<?php echo esc_attr( $name ) ?>" id="<?php echo esc_attr( $args['id'] ) ?>"
       class="<?php echo esc_attr( $args['class'] ) ?>"
       value="<?php echo esc_html( $args['value'] ) ?>"
       placeholder="<?php echo esc_attr( $args['placeholder'] ) ?>"
       <?php if ( ! empty( $args['size'] ) ): ?>size="<?php echo esc_attr( $args['size'] ) ?>"<?php endif; ?>
       <?php if ( ! empty( $args['style'] ) ): ?>style="<?php echo esc_attr( $args['style'] ) ?>"<?php endif; ?>
       title="<?php echo esc_attr( $args['title'] ) ?>" <?php if ( $args['required'] )
	echo 'required' ?> autocomplete="off">
