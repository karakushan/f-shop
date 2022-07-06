<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 */

$attributes = array_merge(
	[
		'placeholder' => $args['placeholder'],
		'title'       => $args['title'],
		'class'       => $args['class'],
		'id'          => $args['id'],
		'value'       => $args['value'],
		'type'        => $args['type'],
		'name' => $name,
	],
	 $args['attributes'] ?? []
);

?>
<input <?php echo fs_parse_attr( $attributes ) ?> <?php echo isset( $args['required'] ) && $args['required'] ? 'required="true"' : '' ?>>
