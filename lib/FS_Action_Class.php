<?php

namespace FS;
/**
 *  Класс для регистрации событий плагина
 */
class FS_Action_Class {

	function __construct() {
		add_action( 'init', array( &$this, 'fs_catch_action' ), 2 );
		add_action( 'init', array( &$this, 'register_plugin_action' ), 10 );
		add_action( 'init', array( &$this, 'register_plugin_filters' ), 10 );
		add_action( 'admin_menu', array( $this, 'remove_admin_submenus' ), 999 );

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
	 * метод регистрирует хуки-события плагина
	 */
	function register_plugin_action() {
		//===== PRODUCT =====
		/* отображение скидки в процентах */
		add_action( 'fs_discount_percent', 'fs_discount_percent', 10, 2 );
		/* отображение артикула товара */
		add_action( 'fs_product_code', 'fs_product_code', 10, 3 );
		/* отображение кнопки добавления в корзину */
		add_action( 'fs_add_to_cart', 'fs_add_to_cart', 10, 3 );

		//===== FILTERS =====
		/* выводит select для сортировки по параметрам (обычно применяется в каталоге или на страницах категорий) */
		add_action( 'fs_types_sort_filter', 'fs_types_sort_filter', 10, 1 );
		/* выводит select для указания к-ва выводимых постов на странице (обычно применяется в каталоге или на страницах категорий) */
		add_action( 'fs_per_page_filter', 'fs_per_page_filter', 10, 2 );
		/*выводит список группы свойств и сортирует при выборе свойства*/
		add_action('fs_attr_filter','fs_attr_filter',10,2);

		//===== ATTRIBUTES =====
		/* выводит фильтр для сортировки по атрибутам */
		add_action( 'fs_attr_filter', 'fs_attr_filter', 10, 2 );
		/* Выводит список всех атрибутов товара в виде: группа : свойство (свойства) */
		add_action( 'fs_the_atts_list', 'fs_the_atts_list', 10, 2 );

		//===== PRICE =====
		/* отображение фактической цены */
		add_action( 'fs_the_price', 'fs_the_price', 10, 2 );
		/* Выводит общую сумму всех товаров в корзине */
		add_action( 'fs_total_amount', 'fs_total_amount', 10, 2 );
		/* отображение базовой цены без учёта скидки */
		add_action( 'fs_base_price', 'fs_base_price', 10, 3 );

		//===== WISHLIST =====
		/* отображает кнопку добавления в список желаний */
		add_action( 'fs_wishlist_button', 'fs_wishlist_button', 10, 2 );
		/* отображает виджет (блок) со списком желаний */
		add_action( 'fs_wishlist_widget', 'fs_wishlist_widget', 10, 1 );

		//===== CART =====
		/* выводит корзину в определёном месте */
		add_action( 'fs_cart_widget', 'fs_cart_widget', 10, 1 );
		/* Выводит поле для изменения к-ва товаров в корзине */
		add_action( 'fs_cart_quantity', 'fs_cart_quantity', 10, 3 );
		/* Выводит поле для изменения к-ва товаров добавляемых в корзину */
		add_action( 'fs_quantity_product', 'fs_quantity_product', 10, 3 );
		/* Выводит кнопку для удаления всех товаров в корзине */
		add_action( 'fs_delete_cart', 'fs_delete_cart', 10, 1 );
		/* Выводит кнопку для удаления определёного товара из корзины */
		add_action( 'fs_delete_position', 'fs_delete_position', 10, 2 );

		//===== ORDER =====
		/* Выводит форму заполнения личных данных при отправке заказа */
		add_action( 'fs_order_form', 'fs_order_send_form', 10, 1 );

		//===== USERS =====
		/* Выводит форму авторизации на сайте */
		add_action( 'fs_login_form', 'fs_login_form', 10, 1 );
		/* Выводит всю информацию о текущем пользователе в виде списка */
		add_action( 'fs_user_info', array( 'FS\FS_Users_Class', 'user_info_show' ), 10 );
		/* Выводит форму редактирования профиля */
		add_action( 'fs_profile_edit', array( 'FS\FS_Users_Class', 'profile_edit' ), 10, 1 );

		//===== COMPARISON LIST ====
		add_action( 'fs_add_to_comparison', 'fs_add_to_comparison', 10, 3 );

	}

	/**
	 *Функция регистрирует хуки-фильтры плагина
	 */
	function register_plugin_filters() {
		/* Фильтр возвращает начальный тег формы и скрытые поля необходимые для безопасности */
		add_filter( 'fs_form_header', 'fs_form_header', 10, 2 );
	}

	/**
	 * Удаляет ненужные подпункты навигации в админке
	 */
	function remove_admin_submenus() {
		// удаляем подпункт "добавить заказ"
		remove_submenu_page( 'edit.php?post_type=orders', 'post-new.php?post_type=orders' );
	}
}