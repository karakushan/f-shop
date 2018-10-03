<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 */ ?>
<select name="<?php echo esc_attr( $name ) ?>"
        id="<?php echo esc_attr( $args['id'] ) ?>"
        class="<?php echo esc_attr( $args['class'] ) ?>" <?php if ( $args['required'] )
	echo 'required="required"' ?>>
  <option value=""><?php echo esc_html(  $args['first_option'] ) ?></option>
	<?php if ( $args['values'] ): ?>
		<?php foreach ( $args['values'] as $key => $value ): ?>
        <option
          value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, $args['value'], true ) ?>><?php echo esc_html( $value ) ?></option>
		<?php endforeach; ?>
	<?php endif; ?>
</select>