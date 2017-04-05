<?php

namespace FS;
/**
 *  Обработка POST или GET запросов
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

//	регистрирует акции и хуки плагина
	function register_plugin_action() {
//		отображение кнопки добавления в корзину
		add_action( 'fs_atc_action', 'fs_add_to_cart', 10, 3 );
	}
}