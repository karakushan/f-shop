<?php
defined('ABSPATH') or die('No script kiddies please!');

/*
Plugin Name: F-Shop
Plugin URI: https://f-shop.top/
Description:  Simple and functional online store plugin.
Version: 1.4.1
Author: Vitaliy Karakushan
Author URI: https://f-shop.top/
License: GPL2
Domain: f-shop
Domain Path: /languages
Requires at least: 5.0
Requires PHP: 7.2
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

// Запрет прямого доступа к файлу
if (!defined('ABSPATH')) {
	exit('Direct access forbidden.');
}

// Проверка минимальной версии PHP
if (version_compare(PHP_VERSION, '7.2', '<')) {
	add_action('admin_notices', function () {
		echo '<div class="error"><p>' .
			sprintf(__('F-Shop требует PHP версии 7.2 или выше. У вас установлена версия %s', 'f-shop'), PHP_VERSION) .
			'</p></div>';
	});
	return;
}

// Проверка минимальной версии WordPress
global $wp_version;
if (version_compare($wp_version, '5.0', '<')) {
	add_action('admin_notices', function () {
		echo '<div class="error"><p>' .
			__('F-Shop требует WordPress версии 5.0 или выше', 'f-shop') .
			'</p></div>';
	});
	return;
}

use FS\FS_Config;
use FS\FS_Orders;

/*
*  The main constants to simplify the development mode,
*  reduce the writing of paths, etc.
*/

define('FS_PLUGIN_FILE', __FILE__);
define('FS_DEBUG', false); // Debug mode
define('FS_PLUGIN_VER', '1.4.1'); // plugin version
define('FS_PLUGIN_PREFIX', 'fs_'); // file prefix
define('FS_PLUGIN_NAME', 'f-shop'); // plugin Name
// todo: убрать констаны ниже, так как они используют __FILE__ который определен в константе FS_PLUGIN_FILE
define('FS_PLUGIN_PATH', plugin_dir_path(__FILE__)); // absolute system path
define('FS_PLUGIN_URL', plugin_dir_url(__FILE__)); // absolute path with http (s)

// Подключаем файл для исправления локализации в AJAX-запросах
require_once __DIR__ . '/locale-fix.php';
/**
 * Including localization files
 */
function fs_load_plugin_textdomain()
{
	$path = dirname(plugin_basename(__FILE__));

	load_plugin_textdomain('f-shop', false, $path . '/languages');
}

add_action('init', 'fs_load_plugin_textdomain');
add_action('plugins_loaded', 'fs_load_plugin_textdomain');

// Sometimes you need complete debugging, just do not forget to turn it off in combat mode
if (defined('FS_DEBUG') && FS_DEBUG == true) {
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
}

require_once 'vendor/autoload.php';

// Initialize the plugin
if (class_exists('\FS\FS_Init')) {
	$GLOBALS['f_shop'] = \FS\FS_Init::instance();
}

// Adding WP CLI support
add_action('init', 'fs_wp_cli_init');
function fs_wp_cli_init()
{
	// Проверяем наличие WP CLI
	if (!defined('WP_CLI') || !WP_CLI) {
		return;
	}

	/**
	 * Команда миграции базы данных
	 *
	 * @param array $args Аргументы команды
	 * @param array $assoc_args Ассоциативные аргументы команды
	 */
	$migrate = function ($args = array(), $assoc_args = array()) {
		\FS\FS_Migrate_Class::migrate_orders();
		\WP_CLI::success('Base migration ended with success.');
	};

	/**
	 * Команда API
	 *
	 * @param array $args Аргументы команды
	 * @param array $assoc_args Ассоциативные аргументы команды
	 */
	$fs_api = function ($args = array(), $assoc_args = array()) {
		do_action('fs_api', $args[0], $assoc_args);
		\WP_CLI::success('command fs_api->' . $args[0] . ' successfully completed!');
	};

	// Регистрируем команды
	\WP_CLI::add_command('fs_migrate_orders', $migrate);
	\WP_CLI::add_command('fs_api', $fs_api);
}

// hooks are triggered when the plugin is activated
register_activation_hook(__FILE__, 'fs_activate');
/**
 * The function is triggered when the plugin is activated.
 */
function fs_activate()
{
	global $wpdb;
	require_once dirname(FS_PLUGIN_FILE) . '/lib/FS_Config.php';
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Версионирование базы данных
	$current_db_version = get_option('fs_db_version', '0');
	$plugin_db_version = '1.0';

	// Создаем таблицу покупателей с улучшенной структурой
	$table_customers = $wpdb->prefix . "fs_customers";
	$charset_collate = $wpdb->get_charset_collate();

	$customers_ddl = "CREATE TABLE IF NOT EXISTS {$table_customers} (
		`id` bigint(20) unsigned NOT NULL auto_increment,
		`email` varchar(100) NOT NULL,
		`first_name` varchar(50) DEFAULT NULL,
		`last_name` varchar(50) DEFAULT NULL,
		`subscribe_news` tinyint(1) DEFAULT 0,
		`group` int(11) DEFAULT NULL,
		`address` text DEFAULT NULL,
		`ip` varchar(45) DEFAULT NULL,
		`user_id` bigint(20) unsigned DEFAULT NULL,
		`city` varchar(100) DEFAULT NULL,
		`phone` varchar(50) DEFAULT NULL,
		`creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`modified_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY email (email),
		KEY user_id (user_id)
	) $charset_collate;";

	// Используем dbDelta для безопасного создания/обновления таблицы
	dbDelta($customers_ddl);

	// Обновляем версию базы данных
	if (version_compare($current_db_version, $plugin_db_version, '<')) {
		update_option('fs_db_version', $plugin_db_version);
	}

	// Регистрируем роли пользователей
	add_role(
		\FS\FS_Config::$users['new_user_role'],
		\FS\FS_Config::$users['new_user_name'],
		array(
			'read'    => true,
			'level_0' => true
		)
	);
	if (! get_option('fs_has_activated', 0)) {
		// Добавляем страницы
		if (\FS\FS_Config::$pages) {
			foreach (\FS\FS_Config::get_pages() as $key => $page) {
				$post_id = wp_insert_post(array(
					'post_title'   => wp_strip_all_tags($page['title']),
					'post_content' => $page['content'],
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_name'    => $key
				));
				if ($post_id) {
					update_option($page['option'], intval($post_id));
					update_option('fs_has_activated', 1);
				}
			}
		}
	}

	// Регистрируем статусы заказов по умолчанию
	$order_statuses = FS_Orders::default_order_statuses();
	if ($order_statuses) {
		register_taxonomy(
			FS_Config::get_data('order_statuses_taxonomy'),
			array(
				'object_type'        => FS_Config::get_data('post_type_orders'),
				'label'              => __('Order statuses', 'f-shop'),
				'labels'             => array(
					'name'          => __('Order statuses', 'f-shop'),
					'singular_name' => __('Order status', 'f-shop'),
					'add_new_item'  => __('Add Order Status', 'f-shop'),
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

		foreach ($order_statuses as $slug => $order_status) {
			if (! term_exists($slug, FS_Config::get_data('order_statuses_taxonomy'))) {
				wp_insert_term($order_status['name'], FS_Config::get_data('order_statuses_taxonomy'), [
					'slug'        => $slug,
					'description' => $order_status['description']
				]);
			}
		}
	}


	// копируем шаблоны плагина в директорию текущей темы
	if (! get_option('fs_copy_templates')) {
		fs_copy_all(FS_PLUGIN_PATH . 'templates/front-end/', TEMPLATEPATH . DIRECTORY_SEPARATOR . FS_PLUGIN_NAME, true);
		update_option('fs_copy_templates', 1);
	}


	flush_rewrite_rules();
}



// Хук деактивации плагина
register_deactivation_hook(__FILE__, 'fs_deactivate');

/**
 * Функция деактивации плагина
 */
function fs_deactivate()
{
	// Очищаем временные опции
	delete_option('fs_copy_templates');

	// Сохраняем настройки и данные пользователей
	// Они будут удалены только при удалении плагина

	// Очищаем расписания cron
	wp_clear_scheduled_hook('fs_daily_cleanup');
	wp_clear_scheduled_hook('fs_check_expired_orders');

	// Сбрасываем правила перезаписи
	flush_rewrite_rules();
}

// Хук удаления плагина
register_uninstall_hook(__FILE__, 'fs_uninstall');

/**
 * Функция полного удаления плагина
 */
function fs_uninstall()
{
	global $wpdb;

	// Удаляем все опции плагина
	$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'fs_%'");

	// Удаляем таблицы плагина
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fs_customers");

	// Удаляем роли
	remove_role('client');

	// Очищаем кэш
	wp_cache_flush();
}
