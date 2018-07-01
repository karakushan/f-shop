<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 16:13
 */
wp_dropdown_categories( array(
	'taxonomy'   => $args['taxonomy'],
	'echo'       => 1,
	'hide_empty' => 0,
	'selected'   => $args['value'],
	'name'       => $name,
	'id'         => $args['id'],
) );