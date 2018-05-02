<?php
/*
Plugin Name: F-Shop
Plugin URI: https://f-shop.top/
Description:  Плагин интернет магазина для Wordpress.
Version: 1.2
Author: Vitaliy Karakushan
Author URI: https://f-shop.top/
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

/* Выходим если кто-то пытается получить прямой доступ к файлам плагина */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

ini_set( 'max_execution_time', 0 ); //0=NOLIMIT
set_time_limit( 0 );

require_once __DIR__ . '/functions/functions.php';
require_once __DIR__ . '/functions/actions.php';
require_once __DIR__ . '/functions/attributes.php';
require_once __DIR__ . '/functions/filters.php';

/* Основные константы для упрощения режим разработки, сокращения написания путей и пр. */
define( 'FS_PLUGIN_VER', '1.2' ); // версия плагина
define( 'FS_PLUGIN_PREFIX', 'fs_' ); // префикс файлов
define( 'FS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // абсолютный системный путь
define( 'FS_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // абсолютный путь с http(s)
define( 'FS_BASENAME', plugin_basename( __FILE__ ) ); // относительный путь типа my-plugin/my-plugin.php
define( 'FS_LANG_PATH', dirname( plugin_basename( __FILE__ ) ) . '/languages' ); // путь к папке с переводами

$GLOBALS['fs_error'] = new WP_Error();

/* Инициализируем плагин */
if ( ! class_exists( '\FS\FS_Init', false ) ) {
	$GLOBALS['f_shop']    = new \FS\FS_Init;
	$GLOBALS['fs_config'] = new FS\FS_Config();
}


// хуки срабатывают в момент активации и деактивации плагина
register_activation_hook( __FILE__, 'fs_activate' );
register_deactivation_hook( __FILE__, 'fs_deactivate' );
function fs_activate() {
	require_once 'lib/FS_Config.php';
	// Регистрируем роли пользователей
	add_role(
		\FS\FS_Config::$users['new_user_role'],
		\FS\FS_Config::$users['new_user_name'],
		array(
			'read'    => true,
			'level_0' => true
		) );
	if ( ! get_option( 'fs_has_activated', 0 ) ) {
// Добавляем страницы
		if ( \FS\FS_Config::$pages ) {
			foreach ( \FS\FS_Config::$pages as $key => $page ) {
				$post_id = wp_insert_post( array(
					'post_title'   => wp_strip_all_tags( $page['title'] ),
					'post_content' => $page['content'],
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_name'    => 'fs-' . $key
				) );
				if ( $post_id ) {
					update_option( $page['option'], intval( $post_id ) );
					update_option( 'fs_has_activated', 1 );
				}
			}
		}
	}


}

function fs_deactivate() {
}