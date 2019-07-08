<?php
/*
Plugin Name: F-Shop
Plugin URI: https://f-shop.top/
Description:  Simple and functional online store plugin.
Version: 1.3
Author: Vitaliy Karakushan
Author URI: https://f-shop.top/
License: GPL2
Domain: f-shop
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

defined( 'ABSPATH' ) || exit;


/*
*  The main constants to simplify the development mode,
*  reduce the writing of paths, etc.
*/
define( 'FS_PLUGIN_FILE', __FILE__ );
define( 'FS_DEBUG', false ); // Debug mode
define( 'FS_PLUGIN_VER', '1.2' ); // plugin version
define( 'FS_PLUGIN_PREFIX', 'fs_' ); // file prefix
define( 'FS_PLUGIN_NAME', 'f-shop' ); // plugin Name
// todo: убрать констаны ниже, так как они используют __FILE__ который определен в константе FS_PLUGIN_FILE
define( 'FS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // absolute system path
define( 'FS_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // absolute path with http (s)

// Sometimes you need complete debugging, just do not forget to turn it off in combat mode
if ( defined( 'FS_DEBUG' ) && FS_DEBUG == true ) {
	ini_set( "display_errors", 1 );
	error_reporting( E_ALL );
}

require_once 'vendor/autoload.php';

// Initialize the plugin
if ( class_exists( '\FS\FS_Init' ) ) {
	$GLOBALS['f_shop'] = \FS\FS_Init::instance();
}

// Adding WP CLI support
add_action( 'init', 'fs_wp_cli_init' );
function fs_wp_cli_init() {
	if ( ! class_exists( 'WP_CLI' ) ) {
		return;
	}

	$migrate = function ( $args = array(), $assoc_args = array() ) {
		\FS\FS_Migrate_Class::migrate_orders();
		WP_CLI::success( 'Base migration ended with success.' );
	};

	$fs_api = function ( $args = array(), $assoc_args = array() ) {
		do_action( 'fs_api', $args[0], $assoc_args );
		WP_CLI::success( 'command fs_api->' . $args[0] . ' successfully completed!' );
	};

	WP_CLI::add_command( 'fs_migrate_orders', $migrate );
	WP_CLI::add_command( 'fs_api', $fs_api );

}

// hooks are triggered when the plugin is activated or deactivated
register_activation_hook( __FILE__, 'fs_activate' );
register_deactivation_hook( __FILE__, 'fs_deactivate' );