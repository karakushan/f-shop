<?php
/*
Plugin Name: F-Shop
Plugin URI: https://f-shop.top/
Description:  Simple and functional online store plugin.
Version: 1.4.1
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

use FS\FS_Config;
use FS\FS_Orders;

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
	ini_set( 'error_reporting', E_ALL );
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
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

// hooks are triggered when the plugin is activated
register_activation_hook( __FILE__, 'fs_activate' );
/**
 * The function is triggered when the plugin is activated.
 */
function fs_activate() {
	global $wpdb;
	require_once dirname( FS_PLUGIN_FILE ) . '/lib/FS_Config.php';
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// Создаем таблицу покупателей
	$table_customers = $wpdb->prefix . "fs_customers";
	$customers_ddl   = "create table {$table_customers}
						(
						    `id`             int auto_increment primary key,  
						    `email`          varchar(64) null,
						    `first_name`     varchar(32) null,
						    `last_name`      varchar(32) null,
						    `subscribe_news` int         null,
						    `group`        	 int         null,
						    `address`        varchar(100) null,    
						    `ip`             varchar(100) null,    
						    `user_id`        int         null,
						    `city`           varchar(50) null,
						    `phone`          varchar(30) null,
						    `creation_date`  timestamp not null default current_timestamp 
						)  
						charset = utf8;";

	maybe_create_table( $table_customers, $customers_ddl );

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
			foreach ( \FS\FS_Config::get_pages() as $key => $page ) {
				$post_id = wp_insert_post( array(
					'post_title'   => wp_strip_all_tags( $page['title'] ),
					'post_content' => $page['content'],
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_name'    => $key
				) );
				if ( $post_id ) {
					update_option( $page['option'], intval( $post_id ) );
					update_option( 'fs_has_activated', 1 );
				}
			}
		}
	}

	// Регистрируем статусы заказов по умолчанию
	$order_statuses = FS_Orders::default_order_statuses();
	if ( $order_statuses ) {
		register_taxonomy( FS_Config::get_data( 'order_statuses_taxonomy' ), array(
				'object_type'        => FS_Config::get_data( 'post_type_orders' ),
				'label'              => __( 'Order statuses', 'f-shop' ),
				'labels'             => array(
					'name'          => __( 'Order statuses', 'f-shop' ),
					'singular_name' => __( 'Order status', 'f-shop' ),
					'add_new_item'  => __( 'Add Order Status', 'f-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => false,
				"show_ui"            => true,
				'show_in_nav_menus'  => false,
				"publicly_queryable" => false,
				'meta_box_cb'        => false,
				'show_admin_column'  => false,
				'hierarchical'       => false,
				'show_in_quick_edit' => true
			)
		);

		foreach ( $order_statuses as $slug => $order_status ) {
			if ( ! term_exists( $slug, FS_Config::get_data( 'order_statuses_taxonomy' ) ) ) {
				wp_insert_term( $order_status['name'], FS_Config::get_data( 'order_statuses_taxonomy' ), [
					'slug'        => $slug,
					'description' => $order_status['description']
				] );
			}
		}
	}


	// копируем шаблоны плагина в директорию текущей темы
	if ( ! get_option( 'fs_copy_templates' ) ) {
		fs_copy_all( FS_PLUGIN_PATH . 'templates/front-end/', TEMPLATEPATH . DIRECTORY_SEPARATOR . FS_PLUGIN_NAME, true );
		update_option( 'fs_copy_templates', 1 );
	}


	flush_rewrite_rules();
}

/**
 * Including localization files
 */
function fs_load_plugin_textdomain() {
	$path = dirname( plugin_basename( __FILE__ ) );
	load_plugin_textdomain( 'f-shop', false, $path . '/languages' );
}

add_action( 'init', 'fs_load_plugin_textdomain' );
add_action( 'plugins_loaded', 'fs_load_plugin_textdomain' );
