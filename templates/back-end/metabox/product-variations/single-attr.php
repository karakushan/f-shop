<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 26.11.2018
 * Time: 18:03
 */
global $fs_config;
$args = array(
	'show_option_all'  => 'Свойство товара',
	'show_option_none' => '',
	'orderby'          => 'ID',
	'order'            => 'ASC',
	'show_last_update' => 0,
	'show_count'       => 0,
	'hide_empty'       => 0,
	'child_of'         => 0,
	'exclude'          => '',
	'echo'             => 0,
	'selected'         => ! empty( $att ) ? $att : 0,
	'hierarchical'     => 1,
	'name'             => 'fs_variant[' . esc_attr( $index ) . '][attr][]',
	'id'               => '',
	'class'            => 'fs_select_variant',
	'depth'            => 0,
	'tab_index'        => 0,
	'taxonomy'         => $fs_config->data['product_att_taxonomy'],
	'hide_if_empty'    => false,

);
echo "<div class=\"fs-prop-row\">" . wp_dropdown_categories( $args ) . "<span class=\"dashicons dashicons-trash\" data-fs-element='remove-var-prop' title='Remove property'></span></div>";
