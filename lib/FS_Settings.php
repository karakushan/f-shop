<?php

namespace FS;

/**
 * The class is responsible for the plugin's settings panel in the admin panel.
 */
class FS_Settings {

	private $settings_page = 'f-shop-settings';

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $customers_obj;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ) );
		add_action( 'admin_bar_menu', array( $this, 'modify_admin_bar' ) );
		add_action( 'admin_init', array( $this, 'permalink_settings' ) );
		add_action( 'admin_init', array( $this, 'save_plugin_settings' ) );
		add_filter( 'set-screen-option', [ $this, 'set_screen' ], 10, 3 );
	}

	/**
	 *
	 */
	function permalink_settings() {
		add_settings_section(
			'fs_permalink_settings', // ID
			__( 'Store link settings', 'f-shop' ), // Section title
			[ $this, 'permalink_settings_fields' ], // Callback for your function
			'permalink' // Location (Settings > Permalinks)
		);
	}

	public function permalink_settings_fields() {
		?>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th><label for="category_base"><?php _e( 'Slug for product categories', 'f-shop' ); ?></label></th>
                <td>
                    <input name="fs_settings[fs_product_category_slug]" id="fs_product_category_slug" type="text"
                           value="<?php echo esc_attr( fs_option( 'fs_product_category_slug', 'catalog' ) ); ?>"
                           class="regular-text code">
                    <p><?php esc_html_e( 'Default: catalog', 'f-shop' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="tag_base"><?php _e( 'Product slug', 'f-shop' ); ?></label></th>
                <td>
                    <input name="fs_settings[fs_product_slug]" id="fs_product_slug" type="text"
                           value="<?php echo esc_attr( fs_option( 'fs_product_slug', 'product' ) ); ?>"
                           class="regular-text code">
                    <p><?php esc_html_e( 'Default: product', 'f-shop' ); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
		<?php
	}


	/**
	 * Adds the inscription "Store" in the upper toolbar
	 *
	 * @param $wp_admin_bar
	 */
	function modify_admin_bar( $wp_admin_bar ) {
		// Only admin sees the panel
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		ob_start(); ?>
        <div class="ab-sub-wrapper">
            <ul id="wp-admin-bar-fs-top" class="ab-submenu">
                <li>
                    <a class="ab-item"
                       href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ) ?>"><?php esc_html_e( 'Add product', 'f-shop' ) ?></a>
                </li>
                <li>
                    <a class="ab-item"
                       href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=catalog&post_type=product' ) ) ?>"><?php esc_html_e( 'Product Categories', 'f-shop' ) ?></a>
                </li>
                <li>
                    <a class="ab-item"
                       href="<?php echo esc_url( admin_url( 'edit.php?post_type=orders' ) ) ?>"><?php esc_html_e( 'Orders', 'f-shop' ) ?></a>
                </li>
                <li>
                    <a class="ab-item"
                       href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=f-shop-settings' ) ) ?>"><?php esc_html_e( 'Settings', 'f-shop' ) ?></a>
                </li>
                <li>
                    <a class="ab-item"
                       href="<?php echo esc_url( 'https://f-shop.top/' ) ?>"
                       target="_blank"><?php esc_html_e( 'Documentation F-Shop', 'f-shop' ) ?></a>
                </li>
            </ul>
        </div>
		<?php $sub_menu = ob_get_clean();

		$wp_admin_bar->add_menu( array(
			'id'     => 'fs-top',
			'parent' => null,
			'group'  => null,
			'title'  => __( 'Shop', 'f-shop' ),
			'href'   => admin_url( 'edit.php?post_type=product' ),
			'meta'   => array(
				'target'   => '_self',
				'title'    => __( 'Go to products', 'f-shop' ),
				'html'     => $sub_menu,
				'class'    => 'ab-item menupop',
				'rel'      => 'friend',
				'onclick'  => "",
				'tabindex' => 20,
			),
		) );
	}


	/**
	 * Updating API settings
	 */
	public static function save_settings() {


		if ( ! empty( $_POST['fs_api'] ) ) {
			do_action( 'fs_save_options', $_POST );
			update_option( 'fs_api_data', $_POST['fs_api'] );

			return;
		}

	}

	/**
	 * Saves plugin settings
	 */
	public static function save_plugin_settings() {
		do_action( 'fs_save_options', $_POST );
		if ( ! empty( $_POST['fs_settings'] ) ) {
			foreach ( $_POST['fs_settings'] as $key => $setting ) {
				update_option( $key, sanitize_text_field( $setting ) );
			}

			return;
		}
	}

	/**
	 * The method contains an array of basic settings of the plugin
	 *
	 * @return array
	 */
	function get_register_settings() {
		$fs_config    = new FS_Config();
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
		print_r( fs_phpinfo_to_array() );
		echo '</pre></code>';
		$phpinfo = ob_get_clean();

		$feed_link = add_query_arg( array(
			'feed' => $export_class->feed_name
		), home_url( '/' ) );

		$feed_link_permalink = sprintf( home_url( '/feed/%s/' ), $export_class->feed_name );


		$settings = array(
			'general'          => array(
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
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_multi_language_support',
						'label' => __( 'Multi-language support', 'f-shop' ),
						'help'  => '',
						'value' => fs_option( 'fs_multi_language_support' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_test_mode',
						'label' => __( 'Test mode', 'f-shop' ),
						'help'  => __( 'In test mode, orders come only to the administrator\'s mail', 'f-shop' ),
						'value' => fs_option( 'fs_test_mode' )
					),


				)


			),
			'products'         => array(
				'name'   => __( 'Products', 'f-shop' ),
				'fields' => array(
					array(
						'type'  => 'number',
						'name'  => 'fs_catalog_show_items',
						'label' => __( 'Number of products per page', 'f-shop' ),
						'help'  => __( 'This parameter configures the number of products that will be displayed on the catalog page or product category', 'f-shop' ),
						'value' => fs_option( 'fs_catalog_show_items', 30 )
					),
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
							'menu_order' => __( 'By sorting field', 'f-shop' ),
							'price_asc'  => __( 'By price from lowest to highest', 'f-shop' ),
							'price_desc' => __( 'By price from larger to smaller', 'f-shop' ),
							'views_desc' => __( 'By popularity', 'f-shop' ),
							'name_asc'   => __( 'By title from A to Z', 'f-shop' ),
							'name_desc'  => __( 'By name from Z to A', 'f-shop' ),
							'date_desc'  => __( 'recently added', 'f-shop' ),
							'date_asc'   => __( 'later added', 'f-shop' ),
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
			'product_category' => array(
				'name'   => __( 'Product Categories', 'f-shop' ),
				'fields' => array(
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_disable_taxonomy_slug',
						'label' => __( 'Disable taxonomy slug in permalink', 'f-shop' ),
						'help'  => '',
						'value' => fs_option( 'fs_disable_taxonomy_slug' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_localize_slug',
						'label' => __( 'Localize Cyrillic slug', 'f-shop' ),
						'help'  => '',
						'value' => fs_option( 'fs_localize_slug' )
					),

				)


			),
			'cart'             => array(
				'name'   => __( 'Basket', 'f-shop' ),
				'fields' => array(
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_autofill_form',
						'label' => __( 'Fill in the data of the authorized user automatically', 'f-shop' ),
						'help'  => __( 'Used when placing the order, if the user is authorized', 'f-shop' ),
						'value' => fs_option( 'fs_autofill_form' )
					),
					array(
						'type'  => 'text',
						'name'  => 'fs_free_delivery_cost',
						'label' => __( 'The amount of goods in the basket at which free shipping is activated', 'f-shop' ),
						'help'  => null,
						'value' => fs_option( 'fs_free_delivery_cost' )
					),
					array(
						'type'  => 'checkbox',
						'name'  => 'fs_include_packing_cost',
						'label' => __( 'Add packaging cost to order', 'f-shop' ),
						'help'  => __( 'When choosing this option, the cost of packaging will also be added to the order value.', 'f-shop' ),
						'value' => fs_option( 'fs_include_packing_cost' )
					),
					array(
						'type'  => 'text',
						'name'  => 'fs_packing_cost_value',
						'label' => __( 'Packing cost', 'f-shop' ),
						'help'  => __( 'You can enter a fixed cost or as a percentage, for example "5%"', 'f-shop' ),
						'value' => fs_option( 'fs_packing_cost_value' )
					),
				)


			),
			'currencies'       => array(
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
			'templates'        => array(
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
						'help'  => __( 'This checkbox should be noted if you intend to process events triggered by the plugin yourself', 'f-shop' ),
						'value' => fs_option( 'fs_disable_messages', 0 )
					),
					array(
						'type'   => 'radio',
						'name'   => 'fs_cart_type',
						'label'  => __( 'What type of basket to show', 'f-shop' ),
						'values' => array(
							'modal'   => __( 'Modal window', 'f-shop' ),
							'side'    => __( 'Appears from the side', 'f-shop' ),
							'disable' => __( 'Turn off the appearance of the basket', 'f-shop' )
						),
						'value'  => fs_option( 'fs_cart_type', 'modal' )
					)
				)
			),
			'letters'          => array(
				'name'   => __( 'Orders', 'f-shop' ),
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
						'type'  => 'text',
						'name'  => 'name_sender',
						'label' => __( 'Name of the sender of letters', 'f-shop' ),
						'value' => fs_option( 'name_sender', get_bloginfo( 'name' ) )
					),
					array(
						'type'  => 'email',
						'name'  => 'email_sender',
						'label' => __( 'Email sender', 'f-shop' ),
						'value' => fs_option( 'email_sender', get_option( 'admin_email' ) ),
						'help'  => __( 'By default, this is the site administrator’s e-mail', 'f-shop' ),
					),
					array(
						'type'  => 'text',
						'name'  => 'admin_mail_header',
						'label' => __( 'Title in the order letter to the administrator', 'f-shop' ),
						'value' => fs_option( 'admin_mail_header', sprintf( __( 'Order goods on the site "%s"', 'f-shop' ), get_bloginfo( 'name' ) ) ),
						'help'  => '',
					),
					array(
						'type'  => 'text',
						'name'  => 'customer_mail_header',
						'label' => __( 'Title in the order letter to the buyer', 'f-shop' ),
						'value' => fs_option( 'customer_mail_header', sprintf( __( 'Order goods on the site "%s"', 'f-shop' ), get_bloginfo( 'name' ) ) ),
						'help'  => '',
					),

					array(
						'type'      => 'dropdown_posts',
						'name'      => 'register_mail_template',
						'label'     => __( 'Email template for user when creating an account', 'f-shop' ),
						'value'     => fs_option( 'register_mail_template' ),
						'post_type' => 'fs-mail-template'
					),
					array(
						'type'      => 'dropdown_posts',
						'name'      => 'create_order_mail_template',
						'label'     => __( 'Letter template to the user when creating a new order', 'f-shop' ),
						'value'     => fs_option( 'create_order_mail_template' ),
						'post_type' => 'fs-mail-template'
					),
					array(
						'type'      => 'select',
						'name'      => 'fs_default_order_status',
						'label'     => __( 'The status that is assigned to a new order', 'f-shop' ),
						'value'     => fs_option( 'fs_default_order_status', 'new' ),
						'values'    => get_terms( array(
							'taxonomy'   => FS_Config::get_data( 'order_statuses_taxonomy' ),
							'fields'     => 'id=>name',
							'hide_empty' => 0,
							'parent'     => 0
						) ),
						'post_type' => 'fs-mail-template'
					),
					array(
						'type'       => 'number',
						'name'       => 'fs_minimum_order_amount',
						'label'      => sprintf( __( 'Минимальная сумма заказа (%s)', 'f-shop' ), fs_currency() ),
						'value'      => fs_option( 'fs_minimum_order_amount', 0 ),
						'attributes' => [
							'min' => 0
						]
					),
				)


			),
			'contacts'         => array(
				'name'   => __( 'Контакты', 'f-shop' ),
				'fields' => array(

					array(
						'type'  => 'text',
						'name'  => 'contact_type',
						'label' => __( 'Тип магазина', 'f-shop' ),
						'help'  => __( 'Используется для микроразметки', 'f-shop' ),
						'value' => fs_option( 'contact_type' )
					),
					array(
						'type'  => 'text',
						'name'  => 'contact_name',
						'label' => __( 'Название магазина', 'f-shop' ),
						'help'  => __( 'Используется для микроразметки', 'f-shop' ),
						'value' => fs_option( 'contact_name' )
					),
					array(
						'type'  => 'text',
						'name'  => 'contact_phone',
						'label' => __( 'Телефон для связи', 'f-shop' ),
						'help'  => __( 'Published on the website so that buyers can contact you', 'f-shop' ),
						'value' => fs_option( 'contact_phone' )
					),
					array(
						'type'  => 'text',
						'name'  => 'contact_country',
						'label' => __( 'Страна', 'f-shop' ),
						'help'  => __( 'Используется для микроразметки, и в других целях', 'f-shop' ),
						'value' => fs_option( 'contact_country' )
					),
					array(
						'type'  => 'text',
						'name'  => 'contact_zip',
						'label' => __( 'Почтовый индекс', 'f-shop' ),
						'help'  => __( 'Используется для микроразметки, и в других целях', 'f-shop' ),
						'value' => fs_option( 'contact_zip' )
					),
					array(
						'type'  => 'text',
						'name'  => 'contact_city',
						'label' => __( 'Город', 'f-shop' ),
						'help'  => __( 'Используется для микроразметки, и в других целях', 'f-shop' ),
						'value' => fs_option( 'contact_city' )
					),
					array(
						'type'  => 'text',
						'name'  => 'contact_address',
						'label' => __( 'Физический адрес магазина', 'f-shop' ),
						'help'  => __( 'Используется для микроразметки, и в других целях', 'f-shop' ),
						'value' => fs_option( 'contact_address' )
					),
					array(
						'type'  => 'text',
						'name'  => 'opening_hours',
						'label' => __( 'Время работы', 'f-shop' ),
						'help'  => __( 'Используется для микроразметки, и в других целях', 'f-shop' ),
						'value' => fs_option( 'opening_hours' )
					),

				)


			),
			'pages'            => array(
				'name'        => __( 'Service pages', 'f-shop' ),
				'description' => __( 'Service pages are created and installed automatically when the plugin is activated. Can you also override them here', 'f-shop' ),
				'fields'      => array(
					array(
						'type'  => 'pages',
						'name'  => 'page_cart',
						'label' => __( 'Cart page', 'f-shop' ),
						'value' => fs_option( 'page_cart' )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_checkout',
						'label' => __( 'Checkout Page', 'f-shop' ),
						'value' => fs_option( 'page_checkout' )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_payment',
						'label' => __( 'Payment page', 'f-shop' ),
						'value' => fs_option( 'page_payment' )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_success',
						'label' => __( 'Successful ordering page', 'f-shop' ),
						'value' => fs_option( 'page_success' )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_whishlist',
						'label' => __( 'Wish List Page', 'f-shop' ),
						'value' => fs_option( 'page_whishlist' )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_cabinet',
						'label' => __( 'Personal account page', 'f-shop' ),
						'value' => fs_option( 'page_cabinet' )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_auth',
						'label' => __( 'Login page', 'f-shop' ),
						'value' => fs_option( 'page_auth' )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_register',
						'label' => __( 'Register page', 'f-shop' ),
						'value' => fs_option( 'page_register' )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_lostpassword',
						'label' => __( 'Forgot your password?', 'f-shop' ),
						'value' => fs_option( 'page_lostpassword' )
					),
					array(
						'type'  => 'pages',
						'name'  => 'page_order_detail',
						'label' => __( 'Order Information Page', 'f-shop' ),
						'value' => fs_option( 'page_order_detail' )
					),
				)


			),
			'seo'              => array(
				'name'        => __( 'SEO', 'f-shop' ),
				'description' => __( 'Basic SEO settings for your store', 'f-shop' ),
				'fields'      => array(
					array(
						'type'  => 'checkbox',
						'name'  => '_fs_adwords_remarketing',
						'label' => __( 'Support for Google Adwords Remarketing Events', 'f-shop' ),
//						'help'   => __( 'Your export file will contain additional settings', 'f-shop' ),
						'value' => fs_option( '_fs_adwords_remarketing' )
					),
					array(
						'type'  => 'text',
						'name'  => '_fs_catalog_title',
						'label' => __( 'Product archive title', 'f-shop' ),
						'value' => fs_option( '_fs_catalog_title' )
					),
					array(
						'type'  => 'text',
						'name'  => '_fs_catalog_meta_title',
						'label' => __( 'Meta title for product archive', 'f-shop' ),
						'value' => fs_option( '_fs_catalog_meta_title' )
					),
					array(
						'type'  => 'textarea',
						'name'  => '_fs_catalog_meta_description',
						'label' => __( 'Meta description for product archive', 'f-shop' ),
						'value' => fs_option( '_fs_catalog_meta_description' )
					),


				)


			),
			'export'           => array(
				'name'        => __( 'Export of goods', 'f-shop' ),
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
			'marketing'        => array(
				'name'        => __( 'Marketing', 'f-shop' ),
				'description' => __( 'Allows you to set analytics and marketing codes', 'f-shop' ),
				'fields'      => array(
					array(
						'type'  => 'textarea',
						'name'  => 'fs_marketing_code_header',
						'label' => __( 'Коды аналитики в шапке', 'f-shop' ),
						'value' => fs_option( 'fs_marketing_code_header' )
					),
					array(
						'type'  => 'textarea',
						'name'  => 'fs_marketing_code_footer',
						'label' => __( 'Коды аналитики в футере', 'f-shop' ),
						'value' => fs_option( 'fs_marketing_code_footer' )
					),
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
		// Регистрация страницы API
		$hook = add_submenu_page(
			'edit.php?post_type=orders',
			__( 'Customers', 'f-shop' ),
			__( 'Customers', 'f-shop' ),
			'manage_options',
			'fs-customers',
			array( &$this, 'customers_settings_page' )
		);
		add_action( "load-$hook", [ $this, 'screen_option' ] );
	} // END public function add_menu()

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}


	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => __( 'Customers', 'f-shop' ),
			'default' => 30,
			'option'  => 'customers_per_page'
		];

		add_screen_option( $option, $args );

		$this->customers_obj = new FS_Customers_List();
	}

	/**
	 * Plugin settings page
	 */
	public function customers_settings_page() {
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Customers', 'f-shop' ); ?></h2>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-1">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <p class="search-box">
                                    <label class="screen-reader-text" for="post-search-input">:</label>
                                    <input type="search" id="post-search-input" name="s" value=""
                                           placeholder="Поиск клиента">
                                    <select name="field">
                                        <option value="">Телефон</option>
                                        <option value="">E-mail</option>
                                        <option value="">Фамилия</option>
                                        <option value="">Имя</option>
                                    </select>
                                    <input type="submit" id="search-submit" class="button button-primary" value="Найти">
                                </p>
								<?php
								$this->customers_obj->prepare_items();
								$this->customers_obj->display(); ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
		<?php
	}


	/**
	 * Displays API settings page
	 */
	public static function api_page() {
		$api_settings = get_option( 'fs_api_data' );

		$api_token = ! empty( $api_settings['api_token'] ) ? $api_settings['api_token'] : '';
		$api_email = ! empty( $api_settings['api_email'] )
			? $api_settings['api_email']
			: get_option( 'admin_email' );
		?>

        <div class="wrap fs-api-settings">
            <h2>Настройка F-SHOP API</h2>
            <p>
                На этой странице вы можете настроить подключение к сервису: <a href="http://api.f-shop.top"
                                                                               target="_blank">api.f-shop.top</a>.
            </p>
            <form action="" method="post">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">API токен</th>
                        <td>
                            <input type="text" name="fs_api[api_token]"
                                   value="<?php echo $api_token ?>">
                            <button type="button" class="button" onclick="fShop.getApiKey()">Получить</button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">E-mail для уведомлений</th>
                        <td>
                            <input type="email" name="fs_api[api_email]" id="fs-api-email"
                                   value="<?php echo $api_email ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Получать новости сервиса</th>
                        <td>
                            <input type="checkbox" name="fs_api[subscribe_news]"
                                   value="1" <?php checked( 1, $api_settings['subscribe_news'] ) ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Получать рекомендации по улучшению работы интернет магазина</th>
                        <td>
                            <input type="checkbox" name="fs_api[subscribe_advice]"
                                   value="1" <?php checked( 1, $api_settings['subscribe_advice'] ) ?>>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary button-large"
                           value="Сохранить изменения">
                </p>
            </form>
        </div>
		<?php
	}

	/**
	 * Displays fields, tabs of the plugin settings in the submenu items
	 */
	public function settings_page() {
		echo ' <div class="wrap f-shop-settings"> ';
		echo ' <h2> ' . esc_html__( 'Store settings', 'f-shop' ) . ' </h2> ';
		settings_errors();
		$settings      = $this->get_register_settings();
		$settings_keys = array_keys( $settings );
		$tab           = $settings_keys[0];
		if ( ! empty( $_GET['tab'] ) ) {
			$tab = esc_attr( $_GET['tab'] );
		}
		echo ' <form method="post" action="' . esc_url( add_query_arg( array( 'tab' => $tab ), 'options.php' ) ) . '"> ';
		echo '<div class="fs-mb-preloader"></div>';
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
		$settings      = $this->get_register_settings();
		$settings_keys = array_keys( $settings );

		return ( isset( $_GET[ $key ] ) ? $_GET[ $key ] : $settings_keys[0] );
	}

	/**
	 * Displays a description of the section, tab in the settings
	 */
	function get_tab_description() {
		$settings    = $this->get_register_settings();
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
		$settings = $this->get_register_settings();
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
		$form_class = new FS_Form();
		if ( in_array( $args[1]['type'], array( 'text', 'email', 'number' ) ) ) {
			$args[1]['class'] = 'regular-text';
		}
		$args[1]['label']          = '';
		$args[1]['label_position'] = 'after';

		$form_class->render_field( $args[0], $args[1]['type'], $args[1] );
	}
}