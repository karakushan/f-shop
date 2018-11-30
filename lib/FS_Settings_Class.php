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

	/**
	 * метод содержит массив базовых настроек плагина
	 * @return array|mixed|void
	 */
	function register_settings() {
		global $fs_config;
		// Дебаг сессий
		ob_start();
		echo '<code class="fs-code"><pre>';
		print_r( $_SESSION );
		echo '</pre></code>';
		$session = ob_get_clean();

		// Дебаг COOKIE
		ob_start();
		echo '<code class="fs-code"><pre>';
		print_r( $_COOKIE );
		echo '</pre></code>';
		$cookie = ob_get_clean();


		$settings = array(
			'general'    => array(
				'name'   => __( 'Главное', 'fast-shop' ),
				'fields' => array(
					array(
						'type'  => 'checkbox',
						'name'  => 'discounts_on',
						'label' => 'Включить систему скидок',
						'help'  => 'вы сможете показать лояльное отношение к клиенту и таким способом привлечь покупателей',
						'value' => fs_option( 'discounts_on' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'multi_currency_on',
						'label' => 'Включить мультивалютность',
						'help'  => 'Если вы планируете, чтобы стоимость товара конвертировалась автоматически по установленному курсу',
						'value' => fs_option( 'multi_currency_on' )
					),


				)


			),
			'products'   => array(
				'name'   => __( 'Products', 'fast-shop' ),
				'fields' => array(
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_in_stock_manage',
						'label' => __( 'Включить управление запасами', 'fast-shop' ),
						'help'  => __( 'Если опция включена, то запас товаров будет уменьшаться автоматически при каждой покупке', 'fast-shop' ),
						'value' => fs_option( 'fs_in_stock_manage' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_product_sort_on',
						'label' => 'Включить сортировку товаров перетаскиванием',
						'help'  => 'Позволяет быстро изменять позиции товаров на сайте, перетаскиванием их в админпанели',
						'value' => fs_option( 'fs_product_sort_on' )
					),
					array(
						'type'   => 'select',
						'name'   => 'fs_product_sort_by',
						'values' => array(
							'none'       => 'По умолчанию',
							'menu_order' => 'По полю сортировки'
						),
						'label'  => 'Сортировать товары в каталоге по',
						'help'   => 'Это определяет в каком порядке товары отображаются на сайте. По умолчанию Wordpress сортирует по ID.',
						'value'  => fs_option( 'fs_product_sort_by' )
					)

				)


			),
			'shoppers'   => array(
				'name'   => __( 'Покупатели', 'fast-shop' ),
				'fields' => array(
					array(
						'type'  => 'checkbox',
						'name'  => 'autofill',
						'label' => 'Заполнять данные авторизованого пользователя автоматически',
						'help'  => 'используется при оформлении заказа, если пользователь авторизован',
						'value' => fs_option( 'autofill' )
					)
				)


			),
			'currencies' => array(
				'name'   => __( 'Currencies', 'fast-shop' ),
				'fields' => array(
					array(
						'type'  => 'text',
						'name'  => 'currency_symbol',
						'label' => 'Знак валюты <span>(по умолчанию отображается $):</span>',
						'value' => fs_option( 'currency_symbol', '$' )
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
					array(
						'type'  => 'checkbox',
						'name'  => 'price_conversion',
						'label' => 'Конвертация стоимости товара в зависимости от языка',
						'help'  => 'Если выбрано, то цена будет автоматически конвертироваться в необходимую валюту. Важно! Для того, чтобы это сработало необходимо указать локаль в настройках валюты.',
						'value' => fs_option( 'price_conversion', '0' )
					)

				)
			),
			'letters'    => array(
				'name'   => __( 'Letters', 'fast-shop' ),
				'fields' => array(
					array(
						'type'  => 'text',
						'name'  => 'manager_email',
						'label' => 'Куда отправлять письма',
						'help'  => 'можно указать несколько адресатов через запятую',
						'value' => fs_option( 'manager_email', get_option( 'admin_email' ) )
					),
					array(
						'type'  => 'image',
						'name'  => 'site_logo',
						'label' => 'Логотип',
						'value' => fs_option( 'site_logo' )
					),
					array(
						'type'  => 'email',
						'name'  => 'email_sender',
						'label' => 'Email отправителя писем',
						'value' => fs_option( 'email_sender' )
					),
					array(
						'type'  => 'text',
						'name'  => 'name_sender',
						'label' => 'Название отправителя писем',
						'value' => fs_option( 'name_sender', get_bloginfo( 'name' ) )
					),
					array(
						'type'  => 'text',
						'name'  => 'customer_mail_header',
						'label' => 'Заголовок письма заказчику',
						'value' => fs_option( 'customer_mail_header', 'Заказ товара на сайте «' . get_bloginfo( 'name' ) . '»' )
					),
					array(
						'type'  => 'editor',
						'name'  => 'customer_mail',
						'label' => 'Текст письма заказчику после отправки заказа',
						'value' => fs_option( 'customer_mail', '<h3 style="text-align: center;">Благодарим за покупку.</h3>
<p style="text-align: center;">Заказ #%order_id% успешно создан. Заказ считается подтвержденным после обратной связи с нашим оператором на указанный
Вами номер телефона.</p>' )
					),
					array(
						'type'  => 'editor',
						'name'  => 'admin_mail',
						'label' => 'Текст письма администратору после отправки заказа',
						'value' => fs_option( 'admin_mail', '<h3 style="text-align: center;">На сайте новый заказ #%order_id%</h3>' )
					),
					array(
						'type'  => 'editor',
						'name'  => 'fs_mail_footer_message',
						'label' => 'Текст в самом низу письма',
						'value' => fs_option( 'fs_mail_footer_message', sprintf( '<p style="text-align: center;">Интернет магазин "%s" функционирует благодаря плагину <a href="https://f-shop.top/" target="_blank" rel="noopener">F-Shop.</a></p>', get_bloginfo( 'name' ) ) )
					)
				)


			),
			'pages'      => array(
				'name'   => __( 'Page', 'fast-shop' ),
				'fields' => array(
					array(
						'type'  => 'pages',
						'name'  => 'page_cart',
						'label' => 'Страница корзины',
						'value' => fs_option( 'page_cart', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_checkout',
						'label' => 'Страница оформление покупки',
						'value' => fs_option( 'page_checkout', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_payment',
						'label' => 'Страница оплаты',
						'value' => fs_option( 'page_payment', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_success',
						'label' => 'Страница успешной отправки заказа',
						'value' => fs_option( 'page_success', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_whishlist',
						'label' => 'Страница списка желаний',
						'value' => fs_option( 'page_whishlist', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_cabinet',
						'label' => 'Страница личного кабинета',
						'value' => fs_option( 'page_cabinet', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_auth',
						'label' => 'Страница авторизации',
						'value' => fs_option( 'page_auth', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_order_detail',
						'label' => 'Страница информации о заказе',
						'value' => fs_option( 'page_order_detail', 0 )
					),
				)


			),
			'Orders'     => array(
				'name'   => __( 'Orders', 'fast-shop' ),
				'fields' => array(
					array(
						'type'  => 'pages',
						'name'  => 'fs_checkout_redirect',
						'label' => 'Передресовывать после оформления покупки',
						'value' => fs_option( 'fs_checkout_redirect', fs_option( 'page_success', 0 ) )
					),

				)


			),
			'debug'      => array(
				'name'        => __( 'Отладка', 'fast-shop' ),
				'description' => 'Здесь отображаются данные отладки в виде  текста',
				'fields'      => array(
					array(
						'type'  => 'html',
						'name'  => FS_PLUGIN_PREFIX . 'debug_session',
						'label' => 'Сессии',
						'value' => $session
					),
					array(
						'type'  => 'html',
						'name'  => FS_PLUGIN_PREFIX . 'debug_cookie',
						'label' => 'Cookie',
						'value' => $cookie
					)
				)


			)
		);

		if ( taxonomy_exists( $fs_config->data['currencies_taxonomy'] ) ) {
			$settings['currencies']['fields'] [] = array(
				'type'     => 'dropdown_categories',
				'taxonomy' => 'fs-currencies',
				'name'     => 'default_currency',
				'label'    => 'Валюта по умолчанию',
				'value'    => fs_option( 'default_currency' )
			);
		}
		$settings = apply_filters( 'fs_plugin_settings', $settings );


		return $settings;
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
		$settings      = $this->register_settings();
		$settings_keys = array_keys( $settings );
		$tab           = $settings_keys[0];
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
	 * Получает активнй таб настроек
	 *
	 * @param $key
	 *
	 * @return string
	 */
	function get_tab( $key ) {
		$settings      = $this->register_settings();
		$settings_keys = array_keys( $settings );

		return ( isset( $_GET[ $key ] ) ? $_GET[ $key ] : $settings_keys[0] );
	}

	/**
	 * Выводит описание секции, таба в настройках
	 */
	function get_tab_description() {
		$settings    = $this->register_settings();
		$setting_key = $this->get_tab( 'tab' );
		$setting     = $settings[ $setting_key ];
		if ( ! empty( $setting['description'] ) ) {
			echo $setting['description'];
		}

	}

	/**
	 * Инициализирует настройки плагина определенные в методе  register_settings()
	 */
	function init_settings() {
		$settings = $this->register_settings();
		// Регистрируем секции и опции в движке
		$setting_key = $this->get_tab( 'tab' );

		if ( ! empty( $settings[ $setting_key ] ) ) {
			$setting = $settings[ $setting_key ];
			$section = "fs_{$setting_key}_section";
			add_settings_section(
				$section,
				$setting['name'],
				array( $this, 'get_tab_description' ),
				$this->settings_page
			);
			if ( count( $setting['fields'] ) ) {
				foreach ( $setting['fields'] as $field ) {
					if ( empty( $field['name'] ) ) {
						continue;
					}
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


	}

	/**
	 * Колбек функция отображающая поля настроек из класса  FS_Form_Class
	 *
	 * @param $args
	 */
	function setting_field_callback( $args ) {
		$form_class = new FS_Form_Class();
		if ( in_array( $args[1]['type'], array( 'text', 'email', 'number' ) ) ) {
			$args[1]['class'] = 'regular-text';
		}
		$args[1]['label']          = '';
		$args[1]['label_position'] = 'after';

		$form_class->render_field( $args[0], $args[1]['type'], $args[1] );
	}
}