<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Класс выводит страницу настроек в админке
 */
class FS_Settings_Class {
	protected $config;

	public function __construct() {
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'add_menu' ) );
		$this->config = new FS_Config();
	}

	public function admin_init() {
		if ( isset( $_POST['fs_save_options'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'fs_nonce' ) ) {
				return;
			}
			$options = $_POST['fs_option'];
			if ( $options ) {
				$upd = update_option( 'fs_option', $options );
				if ( $upd ) {
					add_action( 'admin_notices', function () {
						echo '<div class="updated is-dismissible"><p>Настройки обновлены</p></div>';
					} );
				} else {
					add_action( 'admin_notices', function () {
						echo '<div class="notice notice-warning is-dismissible"><p>Страница перезагружена, но настройки не обновлялись.</p></div>';
					} );
				}

			}

		}


	}

	public function settings_section_wp_plugin_template() {
		echo 'Определите настройки вашего магазина.';
	}

	/**
	 * add a menu
	 */
	public function add_menu() {

		// Add a page to manage this plugin's settings
		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Store settings', 'fast-shop' ),
			__( 'Store settings', 'fast-shop' ),
			'manage_options',
			'fast-shop-settings',
			array( &$this, 'settings_page' )
		);
	} // END public function add_menu()

	/**
	 * подключение шаблона настроек плагина
	 */
	public function settings_page() {

		$plugin_settings = $this->register_settings();
		include( FS_PLUGIN_PATH . '/templates/back-end/settings.php' );
	}

	/**
	 * метод содержит массив базовых настроек плагина
	 * @return array|mixed|void
	 */
	public function register_settings() {
		$settings = array(
			'general' => array(
				'name'   => __( 'General', 'fast-shop' ),
				'fields' => array(
					0 => array(
						'type'  => 'text',
						'name'  => 'currency_symbol',
						'label' => 'Символ валюты <span>(по умолчанию $):</span>',
						'value' => fs_option( 'currency_symbol' )
					),
					1 => array(
						'type'  => 'text',
						'name'  => 'currency_delimiter',
						'label' => 'Разделитель цены <span>(по умолчанию .)</span>',
						'value' => fs_option( 'currency_delimiter', '.' )
					),
					2 => array(
						'type'  => 'checkbox',
						'name'  => 'price_cents',
						'label' => 'Использовать копейки?',
						'value' => fs_option( 'price_cents', '0' )
					),
				)


			),
			'letters' => array(
				'name'   => __( 'Letters', 'fast-shop' ),
				'fields' => array(
					0 => array(
						'type'  => 'text',
						'name'  => 'manager_email',
						'label' => 'Куда отправлять письма',
						'value' => fs_option( 'manager_email', get_option( 'admin_email' ) )
					),
					1 => array(
						'type'  => 'text',
						'name'  => 'site_logo',
						'label' => 'Ссылка на логотип сайта в письме',
						'value' => fs_option( 'site_logo' )
					),
					2 => array(
						'type'  => 'text',
						'name'  => 'email_sender',
						'label' => 'Email отправителя писем',
						'value' => fs_option( 'email_sender', get_bloginfo( 'admin_email' ) )
					),
					3 => array(
						'type'  => 'text',
						'name'  => 'email_sender',
						'label' => 'Название отправителя писем',
						'value' => fs_option( 'name_sender', get_bloginfo( 'name' ) )
					),
					4 => array(
						'type'  => 'text',
						'name'  => 'customer_mail_header',
						'label' => 'Заголовок письма заказчику',
						'value' => fs_option( 'customer_mail_header', 'Заказ товара на сайте «' . get_bloginfo( 'name' ) . '»' )
					),
					5 => array(
						'type'  => 'editor',
						'name'  => 'customer_mail',
						'label' => 'Текст письма заказчику после отправки заказа',
						'value' => fs_option( 'customer_mail' )
					),
					6 => array(
						'type'  => 'editor',
						'name'  => 'admin_mail',
						'label' => 'Текст письма администратору после отправки заказа',
						'value' => fs_option( 'admin_mail' )
					),
				)


			),
			'pages'   => array(
				'name'   => __( 'Page', 'fast-shop' ),
				'fields' => array(
					0 => array(
						'type'  => 'pages',
						'name'  => 'page_cart',
						'label' => 'Страница корзины',
						'value' => fs_option( 'page_cart' )
					),
					1 => array(
						'type'  => 'pages',
						'name'  => 'page_payment',
						'label' => 'Страница оплаты',
						'value' => fs_option( 'page_payment' )
					),
					2 => array(
						'type'  => 'pages',
						'name'  => 'page_success',
						'label' => 'Страница успешной отправки заказа',
						'value' => fs_option( 'page_success' )
					),
					3 => array(
						'type'  => 'pages',
						'name'  => 'page_whishlist',
						'label' => 'Страница списка желаний',
						'value' => fs_option( 'page_whishlist' )
					),
					4 => array(
						'type'  => 'pages',
						'name'  => 'page_cabinet',
						'label' => 'Страница личного кабинета',
						'value' => fs_option( 'page_cabinet' )
					),
					5 => array(
						'type'  => 'pages',
						'name'  => 'page_auth',
						'label' => 'Страница авторизации',
						'value' => fs_option( 'page_auth' )
					),
				)


			),
			'users'   => array(
				'name'   => __( 'Users', 'fast-shop' ),
				'fields' => array(
					0 => array(
						'type'  => 'checkbox',
						'name'  => 'register_user',
						'label' => 'Регистрировать пользователя при покупке?',
						'value' => fs_option( 'register_user' )
					)
				)


			)
		);

		$settings = apply_filters( 'fs_plugin_settings', $settings );

		return $settings;
	}
}