<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 16:13
 */

$default = array(
	'class'             => 'fs-select form-control',
	'echo'              => 1,
	'option_none_value' => "",
	'selected'          => $args['value'],
	'name'              => $name,
	'id'                => $args['id'],
	'value_field'       => 'term_id',
	'orderby'           => 'name',
	'order'             => 'ASC',
	'hide_empty'        => 0,
	'hierarchical'       => 1,
);
$args    = array_merge( $default, $args );
wp_dropdown_categories( $args );