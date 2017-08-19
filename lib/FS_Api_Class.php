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
		if ( ! is_admin() && ! isset( $_GET['fs-api'] ) ) {
			return;
		}
		$api_command = $_GET['fs-api'];
		// импортирует свойства товаров из опций
		if ( $api_command == 'migrate' ) {
			FS_Migrate_Class::import_option_attr();
			// удаляет все заказы
		} elseif ( $api_command == 'drop_orders' ) {
			$orders_class = new FS_Orders_Class();
			$orders_class->delete_orders();
			// удаляет все товары
		} elseif ( $api_command == 'drop_products' ) {
			$product_class = new FS_Product_Class();
			$product_class->delete_products();
			// удаляет категории товаров
		} elseif ( $api_command == 'drop_cat' ) {
			$tax_class = new FS_Taxonomies_Class();
			$tax_class->delete_product_categories();
		} elseif ( $api_command == 'drop_att' ) {
			$tax_class = new FS_Taxonomies_Class();
			$tax_class->delete_product_attributes();
		}
	}

}