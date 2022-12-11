<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 * @var $name string
 * @var $args array
 */

$attributes = wp_parse_args(
	$args['attributes'] ?? [],
	[
		'placeholder' => $args['placeholder'],
		'title'       => $args['title'],
		'class'       => $args['class'],
		'id'          => $args['id'],
		'value'       => $args['value'],
		'type'        => $args['type'],
		'name'        => $name,
	],

);
?>
<input type="number" <?php echo fs_parse_attr( $attributes ) ?>>
