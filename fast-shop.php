<?php
/*
Plugin Name: Fast Shop
Plugin URI: https://f-shop.top/
Description:  The plugin will help in the creation of any online store without changing your template.
Version: 1.1
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

/* Подключаем composer (автозагрузка классов, подробнее: https://getcomposer.org/)*/
require_once __DIR__ . '/vendor/autoload.php';

/* Основные константы для упрощения режим разработки, сокращения написания путей и пр. */
define( 'FS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // абсолютный системный путь
define( 'FS_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // абсолютный путь относительно сайта
define( 'FS_BASENAME', plugin_basename( __FILE__ ) ); // относительный путь типа my-plugin/my-plugin.php
define( 'FS_DEBUG', false ); // режим разработки, по умолчанию выключен

/* Включаем режим разработки если константа FS_DEBUG === true */
if ( FS_DEBUG === true ) {
	ini_set( 'error_reporting', E_ALL );
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
}

/* Активируем класс инициализации плагина */
if ( ! class_exists( '\FS\FS_Init', false ) ) {
	$fs_init              = new \FS\FS_Init;
	$GLOBALS['fs_config'] = new FS\FS_Config();
}