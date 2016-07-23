<?php
/*
Plugin Name: Fast Shop
Plugin URI: http://profglobal.ru/fast-shop/
Description:  The plugin will help in the creation of any online store without changing your template.
Version: 1.0.1
Author: Vitaliy Karakushan
Author URI: http://profglobal.ru/
License: GPL2
Domain: fast-shop
*/
/*
Copyright 2016 Vitaliy Karakushan  (email : karakushan@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/vendor/autoload.php';

global $wpdb;

//отсюда всё пляшет, основные настройки плагина
$GLOBALS['fs_config']=array(
	'plugin_path'=>plugin_dir_path( __FILE__ ),
	'plugin_url'=>plugin_dir_url( __FILE__ ),
	'plugin_ver'=>'1.0',
	'plugin_name'=>'fast-shop',
	'table_name'=>$wpdb->prefix."fs_orders",
	'plugin_meta'=>array(
		),
	);


new FS\Fs_init();
	// Installation and uninstallation hooks
register_activation_hook(__FILE__, array('Fs_init', 'activate'));
register_deactivation_hook(__FILE__, array('Fs_init', 'deactivate'));

