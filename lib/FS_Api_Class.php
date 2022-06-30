<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 05.04.2017
 * Time: 13:44
 */

namespace FS;


class FS_Api_Class {

	function __construct() {
		// $_GET['fs-api']
		add_action( 'admin_init', array( $this, 'http_action' ) );
		add_action( 'fs_api', array( $this, 'plugin_admin_api_actions' ), 10, 2 );

		// Возможность создавать и выполнять хуки для неавторизованого пользователя
		add_action( 'template_redirect', array( $this, 'do_user_api' ) );
	}

	/**
	 * Возможность создавать и выполнять хуки для неавторизованого пользователя
	 *
	 * Запрос должен содержать GET || POST переменную 'fs-api'
	 */
	function do_user_api() {
		if ( ! isset( $_REQUEST['fs-api'] ) || is_admin() ) {
			return;
		}
		$api_command = sanitize_key( $_REQUEST['fs-api'] );
		if ( $api_command ) {
			do_action( $api_command );
		}

	}

	function http_action() {
		if ( ! isset( $_REQUEST['fs-api'] ) ) {
			return;
		}

		if ( empty( $_GET['fs-api'] ) ) {
			wp_die( 'Не указано значение запроса' );
		}
		if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
			wp_die( 'У вас нет прав выполнять это дейстиве' );
		}

		do_action( 'fs_api', $_GET['fs-api'] );
		wp_die();
	}


	/**
	 * Исполняет API запросы по http, работает только в том случае если пользователь авторизован как админ
	 *
	 * fs-api=migrate - запрос для получения свойст товара из метаполей, которые работали в первых версиях плагина
	 * fs-api=drop_orders_table - удаляет таблицу с заказами
	 *
	 * @param $api_command
	 * @param $assoc_args
	 */
	function plugin_admin_api_actions( $api_command, $assoc_args ) {
		if ( empty( $api_command ) ) {
			wp_die( 'Не задана API команда' );
		}
		$fs_config=new FS_Config();
		// импортирует свойства товаров из опций
		if ( $api_command == 'migrate' ) {
			FS_Migrate_Class::import_option_attr();
			// удаляет все заказы
		} elseif ( $api_command == 'drop_orders' ) {
			do_action( 'fs_delete_orders' );
			// удаляет все товары
		} elseif ( $api_command == 'drop_products' ) {
			do_action( 'fs_delete_products' );
			// удаляет категории товаров
		} elseif ( $api_command == 'drop_cat' ) {
			do_action( 'fs_delete_taxonomy_terms', FS_Config::get_data( 'product_taxonomy' ) );
			// удаляет свойства товаров
		} elseif ( $api_command == 'drop_att' ) {
			do_action( 'fs_delete_taxonomy_terms', $fs_config->data['features_taxonomy'] );
			// удаляет все товары а вместе с ними категории и свойства
		} elseif ( $api_command == 'drop_curr' ) {
			do_action( 'fs_delete_taxonomy_terms', $fs_config->data['currencies_taxonomy'] );
			// удаляет все товары а вместе с ними категории и свойства
		} elseif ( $api_command == 'drop_all' ) {
			do_action( 'fs_delete_taxonomy_terms', FS_Config::get_data( 'product_taxonomy' ) );
			do_action( 'fs_delete_taxonomy_terms', $fs_config->data['features_taxonomy'] );
			do_action( 'fs_delete_taxonomy_terms', $fs_config->data['currencies_taxonomy'] );
			do_action( 'fs_delete_products' );
			do_action( 'fs_delete_orders' );
		} else {
			do_action( 'fs_' . $api_command, $api_command );
		}
	}

}