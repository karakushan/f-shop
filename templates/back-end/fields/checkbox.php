<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 * @var $args array
 * @var $name string
 *
 */
?>
<input
        <?php echo fs_parse_attr($args['attributes']) ?>
        type="checkbox" name="<?php echo esc_attr( $name ) ?>" id="<?php echo esc_attr( $args['id'] ) ?>"
       class="<?php echo esc_attr( $args['class'] ) ?>" value="1" <?php checked(1,$args['value']) ?>>
