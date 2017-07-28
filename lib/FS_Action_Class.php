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
		add_action( 'fs_base_price', 'fs_base_price', 10, 3 );
		/* отображение скидки в процентах */
		add_action( 'fs_discount_percent', 'fs_discount_percent', 10, 2 );
		/* выводит select для сортировки по параметрам (обычно применяется в каталоге или на страницах категорий) */
		add_action( 'fs_types_sort_filter', 'fs_types_sort_filter', 10, 1 );
		/* выводит select для указания к-ва выводимых постов на странице (обычно применяется в каталоге или на страницах категорий) */
		add_action( 'fs_per_page_filter', 'fs_per_page_filter', 10, 2 );
		/* выводит фильтр для сортировки по атрибутам */
		add_action( 'fs_attr_filter', 'fs_attr_filter', 10, 2 );
		/* выводит корзину в определёном месте */
		add_action( 'fs_cart_widget', 'fs_cart_widget', 10, 1 );
		/* Выводит поле для изменения к-ва товаров в корзине */
		add_action( 'fs_cart_quantity', 'fs_cart_quantity', 10, 3 );
		/* Выводит кнопку для удаления всех товаров в корзине */
		add_action( 'fs_delete_cart', 'fs_delete_cart', 10, 2 );
		/* Выводит общую сумму всех товаров в корзине */
		add_action( 'fs_total_amount', 'fs_total_amount', 10, 3 );
	}
}