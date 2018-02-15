<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Класс выводит страницу настроек в админке
 */
class FS_Settings_Class {

	private $settings_page = 'fast-shop-settings';

	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'add_menu' ) );
		add_action( 'admin_init', array( &$this, 'init_settings' ) );
	}


	public function settings_section_description() {
		echo 'Определите настройки вашего магазина.';
	}

	/**
	 * add a menu
	 */
	public function add_menu() {

		// Регистрация страницы настроек
		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Store settings', 'fast-shop' ),
			__( 'Store settings', 'fast-shop' ),
			'manage_options',
			$this->settings_page,
			array( &$this, 'settings_page' )
		);
	} // END public function add_menu()

	/**
	 * Выводит поля, табы настройки плагина в подменю товары
	 */
	public function settings_page() {
		echo '<div class="wrap fast-shop-settings">';
		echo ' <h2>' . esc_html__( 'Store settings', 'fast-shop' ) . '</h2>';
		settings_errors();
		$settings=$this->register_settings();
		$settings_keys=array_keys($settings);
		$tab = $settings_keys[0];
		if ( ! empty( $_GET['tab'] ) ) {
			$tab = esc_attr( $_GET['tab'] );
		}
		echo '<form method="post" action="' . esc_url( add_query_arg( array( 'tab' => $tab ), 'options.php' ) ) . '">';
		echo ' <h2 class="nav-tab-wrapper">';

		foreach ( $settings as $key => $setting ) {
			$class = $tab == $key ? 'nav-tab-active' : '';
			echo '<a href="' . esc_url( add_query_arg( array( "tab" => $key ) ) ) . '" class="nav-tab ' . esc_attr( $class ) . '">' . esc_html( $setting['name'] ) . '</a>';
		}
		echo "</h2>";
		settings_fields( "fs_{$tab}_section" );
		do_settings_sections( $this->settings_page );
		submit_button( null, 'button button-primary button-large' );
		echo '  </form></div>';
	}

	/**
	 * метод содержит массив базовых настроек плагина
	 * @return array|mixed|void
	 */
	function register_settings() {
		$settings = array(
			'shoppers' => array(
				'name'   => __( 'Покупатели', 'fast-shop' ),
				'fields' => array(
					array(
						'type'  => 'checkbox',
						'name'  => 'autofill',
						'label' => 'Заполнять данные пользователя автоматически',
						'help' => 'используется при оформлении заказа, если пользователь авторизован',
						'value' => fs_option( 'autofill' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'auto_registration',
						'label' => 'Регистрировать пользователя при покупке',
						'help' => 'каждый зарегистрированный пользователь получит доступ к личному кабинету, сможет увидеть купленные товары и прочие привилегии',
						'value' => fs_option( 'auto_registration' )
					)

				)


			),
			'currencies' => array(
				'name'   => __( 'Currencies', 'fast-shop' ),
				'fields' => array(
					array(
						'type'  => 'custom',
						'name'  => 'default_currency',
						'label' => 'Валюта по умолчанию',
						'html'  => wp_dropdown_categories( array(
							'taxonomy'         => 'fs-currencies',
							'echo'             => 0,
							'hide_empty'       => 0,
							'selected'         => fs_option( 'default_currency' ),
							'name'             => 'fs_option[default_currency]',
							'show_option_none' => __( 'Select currency', 'fast-shop' ),
						) ),
						'value' => fs_option( 'default_currency' )
					),
					array(
						'type'  => 'text',
						'name'  => 'currency_symbol',
						'label' => 'Символ основной валюты <span>(по умолчанию отображается международный код типа USD):</span>',
						'value' => fs_option( 'currency_symbol' )
					),
					array(
						'type'  => 'text',
						'name'  => 'currency_delimiter',
						'label' => 'Разделитель цены <span>(по умолчанию .)</span>',
						'value' => fs_option( 'currency_delimiter', '.' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'price_cents',
						'label' => 'Использовать копейки?',
						'value' => fs_option( 'price_cents', '0' )
					),
				)


			),
			'letters'    => array(
				'name'   => __( 'Letters', 'fast-shop' ),
				'fields' => array(
					0 => array(
						'type'  => 'email',
						'name'  => 'manager_email',
						'label' => 'Куда отправлять письма',
						'help' => 'можно указать несколько адресатов через запятую',
						'value' => fs_option( 'manager_email', get_option( 'admin_email' ) )
					),
					1 => array(
						'type'  => 'text',
						'name'  => 'site_logo',
						'label' => 'Ссылка на изображение логотипа',
						'value' => fs_option( 'site_logo' )
					),
					2 => array(
						'type'  => 'email',
						'name'  => 'email_sender',
						'label' => 'Email отправителя писем',
						'value' => fs_option( 'email_sender' )
					),
					3 => array(
						'type'  => 'text',
						'name'  => 'name_sender',
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
			'pages'      => array(
				'name'   => __( 'Page', 'fast-shop' ),
				'fields' => array(
					0 => array(
						'type'  => 'pages',
						'name'  => 'page_cart',
						'label' => 'Страница корзины',
						'value' => fs_option( 'page_cart', 0 )
					),
					1 => array(
						'type'  => 'pages',
						'name'  => 'page_payment',
						'label' => 'Страница оплаты',
						'value' => fs_option( 'page_payment', 0 )
					),
					2 => array(
						'type'  => 'pages',
						'name'  => 'page_success',
						'label' => 'Страница успешной отправки заказа',
						'value' => fs_option( 'page_success', 0 )
					),
					3 => array(
						'type'  => 'pages',
						'name'  => 'page_whishlist',
						'label' => 'Страница списка желаний',
						'value' => fs_option( 'page_whishlist', 0 )
					),
					4 => array(
						'type'  => 'pages',
						'name'  => 'page_cabinet',
						'label' => 'Страница личного кабинета',
						'value' => fs_option( 'page_cabinet', 0 )
					),
					5 => array(
						'type'  => 'pages',
						'name'  => 'page_auth',
						'label' => 'Страница авторизации',
						'value' => fs_option( 'page_auth', 0 )
					),
					6 => array(
						'type'  => 'pages',
						'name'  => 'page_order_detail',
						'label' => 'Страница информации о заказе',
						'value' => fs_option( 'page_order_detail', 0 )
					),
				)


			)
		);

		$settings = apply_filters( 'fs_plugin_settings', $settings );

		return $settings;
	}

	/**
	 * Получает активнй таб настроек
	 *
	 * @param $key
	 *
	 * @return string
	 */
	function get_tab( $key ) {
		$settings=$this->register_settings();
		$settings_keys=array_keys($settings);
		return ( isset( $_GET[ $key ] ) ? $_GET[ $key ] : $settings_keys[0]);
	}

	/**
	 * Инициализирует настройки плагина определенные в методе  register_settings()
	 */
	function init_settings() {
		$settings = $this->register_settings();
		// Регистрируем секции и опции в движке
		$setting_key = $this->get_tab( 'tab' );
		$setting     = $settings[ $setting_key ];
		$section     = "fs_{$setting_key}_section";
		add_settings_section( $section, $setting['name'], array(
			$this,
			'settings_section_description'
		), $this->settings_page );
		if ( ! empty( $setting['fields'] ) ) {
			foreach ( $setting['fields'] as $field ) {
				$settings_id = $field['name'];
				add_settings_field(
					$settings_id,
					$field['label'],
					array( $this, 'setting_field_callback' ),
					$this->settings_page,
					$section,
					array( $settings_id, $field )
				);
				register_setting( $section, $settings_id, null );
			}
		}

	}

	/**
	 * Колбек функция отображающая поля настроек из класса  FS_Form_Class
	 *
	 * @param $args
	 */
	function setting_field_callback( $args ) {
		$form_class = new FS_Form_Class();
		if ( in_array($args[1]['type'],array('text','email','number')) ) {
			$args[1]['class'] = 'regular-text';
		}
		$form_class->fs_form_field( $args[0], $args[1] );
	}
}