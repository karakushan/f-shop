<?php

namespace FS;
/**
 *  Класс для регистрации событий плагина
 */
class FS_Action_Class {

	function __construct() {
		add_action( 'init', array( &$this, 'fs_catch_action' ), 2 );
		$this->register_plugin_action();
	}

	public function fs_catch_action() {
		if ( isset( $_REQUEST['fs_action'] ) ) {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'fs_action' ) ) {
				exit( 'неправильный код проверки' );
			}
			$action = $_REQUEST['fs_action'];
			switch ( $action ) {
				case "delete-cart":
					unset( $_SESSION['cart'] );
					wp_redirect( remove_query_arg( array( 'fs_action', '_wpnonce' ) ) );
					exit();
					break;
				case "export_yml":
					FS_Export_Class::products_to_yml( true );
					break;

				default:
					exit;
					break;
			}
		}


	}


	/**
	 *Функция регистрирует хуки-события плагина
	 */
	function register_plugin_action() {
		/* отображение кнопки добавления в корзину */
		add_action( 'fs_add_to_cart', 'fs_add_to_cart', 10, 3 );
		/* отображение фактической цены */
		add_action( 'fs_the_price', 'fs_the_price', 10, 2 );
		/* отображение артикула товара */
		add_action( 'fs_product_code', 'fs_product_code', 10, 3 );
		/* отображение базовой цены без учёта скидки */
		add_action( 'fs_base_price','fs_base_price', 10, 3 );
	}
}