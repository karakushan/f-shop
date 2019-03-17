<?php

namespace FS;

/**
 * The class is responsible for the plugin's settings panel in the admin panel.
 */
class FS_Settings_Class {

	private $settings_page = 'f-shop-settings';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'init_settings' ) );
	}

	/**
	 * The method contains an array of basic settings of the plugin
	 *
	 * @return array
	 */
	function register_settings() {
		global $fs_config;
		$export_class = new FS_Export_Class();

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

		// php settings
		ob_start();
		echo '<code class="fs-code"><pre>';
		print_r( phpinfo2array() );
		echo '</pre></code>';
		$phpinfo = ob_get_clean();

		$feed_link = add_query_arg( array(
			'feed' => $export_class->feed_name
		), home_url( '/' ) );

		$feed_link_permalink = sprintf( home_url( '/feed/%s/' ), $export_class->feed_name );


		$settings = array(
			'general'    => array(
				'name'   => __( 'Basic settings', 'f-shop' ),
				'fields' => array(
					array(
						'type'  => 'checkbox',
						'name'  => 'discounts_on',
						'label' => __( 'Activate discount system', 'f-shop' ),
						'help'  => __( 'Allows you to create conditions under which the cost of goods in the basket will decrease', 'f-shop' ),
						'value' => fs_option( 'discounts_on' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'multi_currency_on',
						'label' => __( 'Activate multicurrency', 'f-shop' ),
						'help'  => __( 'It is necessary for the cost of the goods to be converted automatically at the established rate.', 'f-shop' ),
						'value' => fs_option( 'multi_currency_on' )
					),


				)


			),
			'products'   => array(
				'name'   => __( 'Products', 'f-shop' ),
				'fields' => array(
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_not_aviable_hidden',
						'label' => __( 'Hide items out of stock', 'f-shop' ),
						'help'  => __( 'Goods that are not available Budus hidden in the archives and catalog. These products are available by direct link..', 'f-shop' ),
						'value' => fs_option( 'fs_not_aviable_hidden' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_preorder_services',
						'label' => __( 'Enable the service of notification of receipt of goods to the warehouse', 'f-shop' ),
						'help'  => __( 'When adding a product to the cart which is not available, the buyer will be shown a window asking for contact information', 'f-shop' ),
						'value' => fs_option( 'fs_preorder_services' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_in_stock_manage',
						'label' => __( 'Enable inventory management', 'f-shop' ),
						'help'  => __( 'If this option is enabled, the stock of goods will decrease automatically with each purchase.', 'f-shop' ),
						'value' => fs_option( 'fs_in_stock_manage' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_product_sort_on',
						'label' => __( 'Enable item sorting by dragging', 'f-shop' ),
						'help'  => 'Allows you to quickly change the position of goods on the site by dragging them into the admin panel',
						'value' => fs_option( 'fs_product_sort_on' )
					),
					array(
						'type'   => 'select',
						'name'   => 'fs_product_sort_by',
						'values' => array(
							'none'       => __( 'By default', 'f-shop' ),
							'menu_order' => __( 'By sorting field', 'f-shop' )
						),
						'label'  => __( 'Sort items in the catalog by', 'f-shop' ),
						'help'   => __( 'This determines the order in which products are displayed on the site. By default, Wordpress sorts by ID.', 'f-shop' ),
						'value'  => fs_option( 'fs_product_sort_by' )
					),
					array(
						'type'   => 'radio',
						'name'   => 'fs_product_filter_type',
						'values' => array(
							'IN'  => __( 'The product must have at least one of the selected characteristics in the filter', 'f-shop' ),
							'AND' => __( 'The product must have all of the selected characteristics in the filter', 'f-shop' )
						),
						'label'  => __( 'Method of applying filters in the catalog', 'f-shop' ),
						'help'   => null,
						'value'  => fs_option( 'fs_product_filter_type', 'IN' )
					),
					array(
						'type'  => 'text',
						'name'  => 'fs_total_discount_percent',
						'label' => __( 'Total discount on products as a percentage', 'f-shop' ),
						'help'  => null,
						'value' => fs_option( 'fs_total_discount_percent' )
					)

				)


			),
			'shoppers'   => array(
				'name'   => __( 'Buyers', 'f-shop' ),
				'fields' => array(
					array(
						'type'  => 'checkbox',
						'name'  => 'autofill',
						'label' => __( 'Fill in the data of the authorized user automatically', 'f-shop' ),
						'help'  => __( 'Used when placing the order, if the user is authorized', 'f-shop' ),
						'value' => fs_option( 'autofill' )
					)
				)


			),
			'currencies' => array(
				'name'   => __( 'Currencies', 'f-shop' ),
				'fields' => array(
					array(
						'type'  => 'text',
						'name'  => 'currency_symbol',
						'label' => __( 'Currency sign ($ displayed by default)', 'f-shop' ),
						'value' => fs_option( 'currency_symbol', '$' )
					),
					array(
						'type'  => 'text',
						'name'  => 'fs_currency_code',
						'label' => __( 'Three-digit currency code', 'f-shop' ),
						'value' => fs_option( 'fs_currency_code', 'UAH' )
					),
					array(
						'type'  => 'text',
						'name'  => 'currency_delimiter',
						'label' => __( 'Price separator (default ".")', 'f-shop' ),
						'value' => fs_option( 'currency_delimiter', '.' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'price_cents',
						'label' => __( 'Use a penny?', 'f-shop' ),
						'value' => fs_option( 'price_cents', '0' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'price_conversion',
						'label' => __( 'Conversion of the cost of goods depending on the language', 'f-shop' ),
						'help'  => __( 'If selected, the price will be automatically converted into the required currency. Important! In order for this to work, you must specify the locale in the currency settings.', 'f-shop' ),
						'value' => fs_option( 'price_conversion', '0' )
					)

				)
			),
			'templates'  => array(
				'name'   => __( 'Templates', 'f-shop' ),
				'fields' => array(
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_overdrive_templates',
						'label' => __( 'Do not override standard templates.', 'f-shop' ),
						'help'  => __( 'This checkbox is needed if you will not store templates in the "f-shop" directory of your theme.', 'f-shop' ),
						'value' => fs_option( 'fs_overdrive_templates', '0' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_disable_messages',
						'label' => __( 'Disable js event handling by the plugin', 'f-shop' ),
						'help'  => __( 'Этот чекбокс нужно отметить если вы сами намерены обрабатывать события инициируемые плагином', 'f-shop' ),
						'value' => fs_option( 'fs_disable_messages', 0 )
					)
				)
			),
			'letters'    => array(
				'name'   => __( 'Letters', 'f-shop' ),
				'fields' => array(
					array(
						'type'  => 'text',
						'name'  => 'manager_email',
						'label' => __( 'Recipients of letters', 'f-shop' ),
						'help'  => __( 'You can specify multiple recipients separated by commas.', 'f-shop' ),
						'value' => fs_option( 'manager_email', get_option( 'admin_email' ) )
					),
					array(
						'type'  => 'image',
						'name'  => 'site_logo',
						'label' => __( 'Letter Logo', 'f-shop' ),
						'value' => fs_option( 'site_logo' )
					),
					array(
						'type'  => 'email',
						'name'  => 'email_sender',
						'label' => __( 'Email sender', 'f-shop' ),
						'value' => fs_option( 'email_sender' )
					),
					array(
						'type'  => 'text',
						'name'  => 'name_sender',
						'label' => __( 'Name of the sender of letters', 'f-shop' ),
						'value' => fs_option( 'name_sender', get_bloginfo( 'name' ) )
					),
					array(
						'type'  => 'text',
						'name'  => 'customer_mail_header',
						'label' => __( 'The title of the letter to the customer', 'f-shop' ),
						'value' => fs_option( 'customer_mail_header', __( sprintf( 'Order goods on the site "%s"', get_bloginfo( 'name' ) ), 'f-shop' ) )
					),
					array(
						'type'  => 'editor',
						'name'  => 'customer_mail',
						'label' => __( 'The text of the letter to the customer after sending the order', 'f-shop' ),
						'value' => fs_option( 'customer_mail', __( '<h3 style="text-align: center;">Thank you for your purchase.</h3>
<p style="text-align: center;">Order #%order_id% created successfully. The order is considered confirmed after feedback from our operator on the specified
Your phone number.</p>', 'f-shop' ) )
					),
					array(
						'type'  => 'editor',
						'name'  => 'admin_mail',
						'label' => __( 'The text of the letter to the administrator after sending the order', 'f-shop' ),
						'value' => fs_option( 'admin_mail', __( '<h3 style="text-align: center;">On the site a new order #%order_id%</h3>', 'f-shop' ) )
					),
					array(
						'type'  => 'editor',
						'name'  => 'fs_mail_footer_message',
						'label' => __( 'The text at the bottom of the letter', 'f-shop' ),
						'value' => fs_option( 'fs_mail_footer_message', sprintf( __( '<p style="text-align: center;">Online store "%s" is functioning thanks to the plugin <a href="https://f-shop.top/" target="_blank" rel="noopener">F-Shop.</a></p>', 'f-shop' ), get_bloginfo( 'name' ) ) )
					)
				)


			),
			'pages'      => array(
				'name'        => __( 'Service pages', 'f-shop' ),
				'description' => __( 'Service pages are created and installed automatically when the plugin is activated. Can you also override them here', 'f-shop' ),
				'fields'      => array(
					array(
						'type'  => 'pages',
						'name'  => 'page_cart',
						'label' => __( 'Cart page', 'f-shop' ),
						'value' => fs_option( 'page_cart', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_checkout',
						'label' => __( 'Checkout Page', 'f-shop' ),
						'value' => fs_option( 'page_checkout', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_payment',
						'label' => __( 'Payment page', 'f-shop' ),
						'value' => fs_option( 'page_payment', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_success',
						'label' => __( 'Successful ordering page', 'f-shop' ),
						'value' => fs_option( 'page_success', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_whishlist',
						'label' => __( 'Wish List Page', 'f-shop' ),
						'value' => fs_option( 'page_whishlist', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_cabinet',
						'label' => __( 'Personal account page', 'f-shop' ),
						'value' => fs_option( 'page_cabinet', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_auth',
						'label' => __( 'Login page', 'f-shop' ),
						'value' => fs_option( 'page_auth', 0 )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_order_detail',
						'label' => __( 'Order Information Page', 'f-shop' ),
						'value' => fs_option( 'page_order_detail', 0 )
					),
				)


			),
			'export'     => array(
				'name'        => __( 'Экспорт товаров', 'f-shop' ),
				'description' => sprintf( __( 'Link to product feed in YML format: <a href="%s" target="_blank">%s</a> or <a href="%s" target="_blank">%s</a>', 'f-shop' ), $feed_link, $feed_link, $feed_link_permalink, $feed_link_permalink ),
				'fields'      => array(
					array(
						'type'   => 'radio',
						'name'   => '_fs_export_prom',
						'label'  => __( 'Export to the site', 'f-shop' ),
						'values' => array(
							'rozetka' => __( 'Розетка', 'f-shop' ),
							'prom'    => __( 'Prom.ua', 'f-shop' ),
						),
//						'help'   => __( 'Your export file will contain additional settings', 'f-shop' ),
						'value'  => fs_option( '_fs_export_prom', 'rozetka' )
					)


				)


			),
			'debug'      => array(
				'name'        => __( 'Debugging', 'f-shop' ),
				'description' => __( 'The debug data is displayed here as text.', 'f-shop' ),
				'fields'      => array(
					array(
						'type'  => 'html',
						'name'  => FS_PLUGIN_PREFIX . 'debug_session',
						'label' => __( 'Sessions', 'f-shop' ),
						'value' => $session
					),
					array(
						'type'  => 'html',
						'name'  => FS_PLUGIN_PREFIX . 'debug_cookie',
						'label' => 'Cookie',
						'value' => $cookie
					),
					array(
						'type'  => 'html',
						'name'  => FS_PLUGIN_PREFIX . 'debug_php',
						'label' => 'PHP',
						'value' => $phpinfo
					)
				)


			)
		);

		if ( taxonomy_exists( $fs_config->data['currencies_taxonomy'] ) ) {
			$settings['currencies']['fields'] [] = array(
				'type'     => 'dropdown_categories',
				'taxonomy' => 'fs-currencies',
				'name'     => 'default_currency',
				'label'    => __( 'Default currency', 'f-shop' ),
				'value'    => fs_option( 'default_currency' )
			);
		}
		$settings = apply_filters( 'fs_plugin_settings', $settings );


		return $settings;
	}


	public function settings_section_description() {
		esc_html_e( 'Determine your store settings.', 'f-shop' );
	}

	/**
	 * add a menu
	 */
	public function add_menu() {

		// Регистрация страницы настроек
		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Store settings', 'f-shop' ),
			__( 'Store settings', 'f-shop' ),
			'manage_options',
			$this->settings_page,
			array( &$this, 'settings_page' )
		);
	} // END public function add_menu()

	/**
	 * Displays fields, tabs of the plugin settings in the submenu items
	 */
	public function settings_page() {
		echo ' <div class="wrap f-shop-settings"> ';
		echo ' <h2> ' . esc_html__( 'Store settings', 'f-shop' ) . ' </h2> ';
		settings_errors();
		$settings      = $this->register_settings();
		$settings_keys = array_keys( $settings );
		$tab           = $settings_keys[0];
		if ( ! empty( $_GET['tab'] ) ) {
			$tab = esc_attr( $_GET['tab'] );
		}
		echo ' <form method="post" action="' . esc_url( add_query_arg( array( 'tab' => $tab ), 'options.php' ) ) . '"> ';
		echo ' <h2 class="nav-tab-wrapper"> ';

		foreach ( $settings as $key => $setting ) {
			$class = $tab == $key ? 'nav-tab-active' : '';
			echo ' <a href="' . esc_url( add_query_arg( array( "tab" => $key ) ) ) . '" class="nav-tab ' . esc_attr( $class ) . '"> ' . esc_html( $setting['name'] ) . ' </a> ';
		}
		echo "</h2>";
		settings_fields( "fs_{$tab}_section" );
		do_settings_sections( $this->settings_page );
		submit_button( null, 'button button-primary button-large' );
		echo ' </form></div> ';
	}


	/**
	 * Gets active tab settings
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
	 * Displays a description of the section, tab in the settings
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
	 * Initializes the plugin settings defined in the register_settings () method
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
	 * Callback function that displays the settings fields from the class FS_Form_Class
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