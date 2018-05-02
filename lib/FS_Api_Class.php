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
		add_action( 'wp_loaded', array( $this, 'plugin_admin_api_actions' ) );
		add_action( 'wp_loaded', array( $this, 'plugin_user_api_actions' ) );
	}

	/**
	 * Исполняет API запросы по http, работает только в том случае если пользователь авторизован как админ
	 *
	 * fs-api=migrate - запрос для получения свойст товара из метаполей, которые работали в первых версиях плагина
	 * fs-api=drop_orders_table - удаляет таблицу с заказами
	 */
	function plugin_admin_api_actions() {
		if ( empty( $_GET['fs-api'] ) ) {
			return;
		}
		if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$api_command = $_GET['fs-api'];
		global $fs_config;
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
			do_action( 'fs_delete_taxonomy_terms', $fs_config->data['product_taxonomy'] );
			// удаляет свойства товаров
		} elseif ( $api_command == 'drop_att' ) {
			do_action( 'fs_delete_taxonomy_terms', $fs_config->data['product_att_taxonomy'] );
			// удаляет все товары а вместе с ними категории и свойства
		} elseif ( $api_command == 'drop_all' ) {
			do_action( 'fs_delete_taxonomy_terms', $fs_config->data['product_taxonomy'] );
			do_action( 'fs_delete_taxonomy_terms', $fs_config->data['product_att_taxonomy'] );
			do_action( 'fs_delete_products' );
			do_action( 'fs_delete_orders' );
		} else {
			do_action( 'fs_admin_custom_api', $api_command );
		}
	}

	/**
	 * Исполняет API запросы по http, работает со всеми типами пользователей
	 *
	 */
	function plugin_user_api_actions() {
		if ( empty( $_REQUEST['fs-user-api'] ) ) {
			return;
		}
		$session     = $_SESSION;
		$api_command = $_REQUEST['fs-user-api'];
		// импортирует свойства товаров из опций
		if ( $api_command == 'delete_wishlist_position' ) {
			if ( ! empty( $session['fs_wishlist'] ) && ! empty( $_REQUEST['product_id'] ) ) {
				$product_id = intval( $_REQUEST['product_id'] );
				unset( $_SESSION['fs_wishlist'][ $product_id ] );
				wp_redirect( remove_query_arg( array( 'fs-user-api', 'product_id' ) ) );
			}
		} else {
			do_action( 'fs_user_custom_api', $api_command );
		}

	}

}