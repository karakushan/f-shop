<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 11.07.2018
 * Time: 20:56
 */
$args = array(
	'depth'            => 0,
	'child_of'         => 0,
	'selected'         => $args['value'],
	'echo'             => 1,
	'name'             => $name,
	'show_option_none' => '',
	'exclude'          => '',
	'exclude_tree'     => '',
	'value_field'      => 'ID', // поле для значения value e тега option
);
wp_dropdown_pages( $args );