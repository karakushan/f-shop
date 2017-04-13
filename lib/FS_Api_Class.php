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
		add_action( 'init', array( $this, 'plugin_api_actions' ) );
	}

	/**
	 * Исполняет API запросы по http, работает только в том случае если пользователь авторизован как админ
	 *
	 * fs-api=migrate - запрос для получения свойст товара из метаполей, которые работали в первых версиях плагина
	 * fs-api=drop_orders_table - удаляет таблицу с заказами
	 */
	function plugin_api_actions() {
		if ( ! is_admin() && ! isset( $_REQUEST['fs-api'] ) ) {
			return;
		}
		global $wpdb;
		$config = new FS_Config();
//		импортирует свойства товаров из опций
		if ( $_REQUEST['fs-api'] == 'migrate' ) {
			FS_Migrate_Class::import_option_attr();
//			удаляет таблицу заказов
		} elseif ( $_REQUEST['fs-api'] == 'drop_orders_table' ) {
			$orders_table=$config->data['table_orders'];
			$wpdb->query( "DROP TABLE IF EXISTS $orders_table") ;

		}
	}

}