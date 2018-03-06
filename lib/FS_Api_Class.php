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
		add_action( 'template_redirect', array( $this, 'plugin_admin_api_actions' ) );
		add_action( 'template_redirect', array( $this, 'plugin_user_api_actions' ) );
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
			// удаляет свойства товаров
		} elseif ( $api_command == 'drop_att' ) {
			$tax_class = new FS_Taxonomies_Class();
			$tax_class->delete_product_attributes();
			// удаляет все товары а вместе с ними категории и свойства
		} elseif ( $api_command == 'drop_all' ) {
			$product_class = new FS_Product_Class();
			$product_class->delete_products();

			$tax_class = new FS_Taxonomies_Class();
			$tax_class->delete_product_categories();

			$tax_class = new FS_Taxonomies_Class();
			$tax_class->delete_product_attributes();

			$orders_class = new FS_Orders_Class();
			$orders_class->delete_orders();
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
		}

	}

}