<?php

namespace FS;

class FS_Config {
	public $data;
	public $meta;
	public $term_meta;
	public $options;
	public $tabs;
	public $texts;
	public $taxonomies;

	public static $currencies = array();
	public static $users = array();
	public static $default_currency = 'USD';
	public static $user_meta = array();
	public static $prices;
	public static $form_fields;
	public static $nonce = 'f-shop';
	public static $text_domain = 'f-shop';
	public static $pages = array();

	protected static $nonce_field = 'fs_secret';

	/**
	 * FS_Config constructor.
	 */
	function __construct() {
		$this->data = self::get_data();

		// Gets an array of service texts
		$this->texts = self::get_texts();

		// Tabs displayed in the metabox in product editing
		$this->tabs = $this->get_product_tabs();

		// Array of site settings
		$this->options = get_option( 'fs_option', array() );


		// An array of product meta (product) settings. When changing settings, all settings are changed globally.
		$this->meta = self::get_meta();

		$this->term_meta = array(
			'att_type'      => 'fs_att_type',
			'att_value'     => 'fs_att_value',
			'att_unit'      => 'fs_att_unit',
			'att_unit_type' => 'fs_att_unit_type',
			'att_start'     => 'fs_att_start',
			'att_end'       => 'fs_att_end',
		);

		//  we set the main types of prices
		self::$prices = array(
			'price'        => array(
				'id'          => 'base-price',
				'name'        => __( 'Base price', 'f-shop' ),
				'meta_key'    => $this->meta['price'],
				'on'          => true,
				'description' => __( 'Basic price type', 'f-shop' )
			),
			'action_price' => array(
				'id'          => 'action-price',
				'name'        => __( 'Promotional price', 'f-shop' ),
				'meta_key'    => $this->meta['action_price'],
				'on'          => true,
				'description' => __( 'This type changes the base price displayed by default.', 'f-shop' )
			)
		);

		self::$user_meta = array(
			'display_name'   => array( 'label' => __( 'Display Name', 'f-shop' ), 'name' => 'display_name' ),
			'user_email'     => array( 'label' => __( 'E-mail', 'f-shop' ), 'name' => 'user_email' ),
			'phone'          => array( 'label' => __( 'Phone number', 'f-shop' ), 'name' => 'phone' ),
			'birth_day'      => array( 'label' => __( 'Date of Birth', 'f-shop' ), 'name' => 'birth_day' ),
			'gender'         => array( 'label' => __( 'Gender', 'f-shop' ), 'name' => 'gender' ),
			'state'          => array( 'label' => __( 'State / Province', 'f-shop' ), 'name' => 'state' ),
			'country'        => array( 'label' => __( 'Country', 'f-shop' ), 'name' => 'country' ),
			'city'           => array( 'label' => __( 'City', 'f-shop' ), 'name' => 'city' ),
			'adress'         => array( 'label' => __( 'Address', 'f-shop' ), 'name' => 'adress' ),
			'location'       => array( 'label' => __( 'Position on the map', 'f-shop' ), 'name' => 'location' ),
			'profile_update' => array( 'label' => __( 'Update Date', 'f-shop' ), 'name' => 'profile_update' )
		);

		self::$form_fields = array(
			'fs_email'             => array(
				'type'        => 'email',
				'label'       => '',
				'placeholder' => __( 'Your email', 'f-shop' ),
				'title'       => __( 'Keep the correct email', 'f-shop' ),
				'required'    => true
			),
			'fs_first_name'        => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Firts name', 'f-shop' ),
				'title'       => __( 'This field is required.', 'f-shop' ),
				'required'    => true
			),
			'fs_last_name'         => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Last name', 'f-shop' ),
				'title'       => __( 'This field is required.', 'f-shop' ),
				'required'    => true
			),
			'fs_phone'             => array(
				'type'        => 'tel',
				'label'       => '',
				'placeholder' => __( 'Phone number', 'f-shop' ),
				'title'       => __( 'Keep the correct phone number', 'f-shop' ),
				'required'    => true,
				'save_meta'   => 1
			),
			'fs_city'              => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'City', 'f-shop' ),
				'title'       => __( 'This field is required.', 'f-shop' ),
				'required'    => true,
				'save_meta'   => 1
			),
			'fs_zip_code'          => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Zip Code', 'f-shop' ),
				'required'    => false,
				'save_meta'   => 1
			),
			'fs_region'            => array(
				'type'        => 'text',
				'label'       => '',
				'title'       => __( 'This field is required.', 'f-shop' ),
				'placeholder' => __( 'State / province', 'f-shop' ),
				'required'    => true,
				'save_meta'   => 1
			),
			'fs_adress'            => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Address', 'f-shop' ),
				'required'    => false,
				'save_meta'   => 1
			),
			'fs_home_num'          => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'House number', 'f-shop' ),
				'required'    => false,
				'save_meta'   => 1
			),
			'fs_apartment_num'     => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Apartment number', 'f-shop' ),
				'required'    => false,
				'save_meta'   => 1
			),
			'fs_delivery_number'   => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Branch number', 'f-shop' ),
				'required'    => false,
				'save_meta'   => 1
			),
			'fs_delivery_methods'  => array(
				'type'         => 'dropdown_categories',
				'first_option' => __( "Choose delivery method", 'f-shop' ),
				'taxonomy'     => $this->data['product_del_taxonomy'],
				'required'     => true,
				'save_meta'    => 1
			),
			'fs_payment_methods'   => array(
				'type'         => 'dropdown_categories',
				'first_option' => __( "Choose a payment method", 'f-shop' ),
				'taxonomy'     => $this->data['product_pay_taxonomy'],
				'required'     => true,
				'save_meta'    => 1
			),
			'fs_comment'           => array(
				'type'        => 'textarea',
				'label'       => '',
				'placeholder' => __( 'Comment', 'f-shop' ),
				'required'    => false
			),
			'fs_customer_register' => array(
				'type'           => 'checkbox',
				'label'          => __( 'Register on the site', 'f-shop' ),
				'label_position' => 'after',
				'value'          => 1,
				'required'       => false
			),
		);

		self::$currencies = array(
			'USD' => __( 'US dollar', 'f-shop' ),
			'UAH' => __( 'Ukrainian hryvnia', 'f-shop' ),
			'RUB' => __( 'Russian ruble', 'f-shop' ),
		);

		self::$users = array(
			'new_user_role' => 'client',
			'new_user_name' => __( 'Client', 'f-shop' )
		);

		self::$pages = array(
			'cart'       => array(
				'title'   => __( 'Basket', 'f-shop' ),
				'content' => '[fs_cart]',
				'option'  => 'page_cart'
			),
			'ckeckout'   => array(
				'title'   => __( 'Checkout', 'f-shop' ),
				'content' => '[fs_checkout]',
				'option'  => 'page_checkout'
			),
			'pay'        => array(
				'title'   => __( 'Payment order', 'f-shop' ),
				'content' => '[fs_order_pay]',
				'option'  => 'page_payment'
			),
			'thanks'     => array(
				'title'   => __( 'Thank you for your purchase.', 'f-shop' ),
				'content' => '[fs_checkout_success]',
				'option'  => 'page_success'
			),
			'wishlist'   => array(
				'title'   => __( 'Wishlist', 'f-shop' ),
				'content' => '[fs_wishlist]',
				'option'  => 'page_whishlist'
			),
			'account'    => array(
				'title'   => __( 'Personal Area', 'f-shop' ),
				'content' => '[fs_user_cabinet]',
				'option'  => 'page_cabinet'
			),
			'log-in'     => array(
				'title'   => __( 'Sign in', 'f-shop' ),
				'content' => '[fs_login]',
				'option'  => 'page_auth'
			),
			'order-info' => array(
				'title'   => __( 'Information about order', 'f-shop' ),
				'content' => '[fs_order_info]',
				'option'  => 'page_order_detail'
			),

		);

	}

	/**
	 * Gets the array that contains the list of product settings tabs.
	 *
	 * @return array
	 */
	public function get_product_tabs() {
		$tabs = array(
			'prices'     => array(
				'title'       => __( 'Prices', 'f-shop' ),
				'on'          => true,
				'description' => __( 'In this tab you can adjust the prices of goods.', 'f-shop' ),
				'fields'      => array(
					$this->meta['price']        => array(
						'label' => __( 'Base price', 'f-shop' ),
						'type'  => 'text',
						'help'  => __( 'This is the main price on the site. Required field!', 'f-shop' )
					),
					$this->meta['action_price'] => array(
						'label' => __( 'Promotional price', 'f-shop' ),
						'type'  => 'text',
						'help'  => __( 'If this field is filled, the base price loses its relevance. But you can display it on the site.', 'f-shop' )
					),
					$this->meta['currency']     => array(
						'label'    => __( 'Item Currency', 'f-shop' ),
						'on'       => fs_option( 'multi_currency_on' ) ? true : false,
						'type'     => 'dropdown_categories',
						'help'     => __( 'The field is active if you have enabled multicurrency in settings.', 'f-shop' ),
						'taxonomy' => $this->data['currencies_taxonomy']
					)
				)
			),
			'gallery'    => array(
				'title'    => __( 'Gallery', 'f-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'gallery'
			),
			'attributes' => array(
				'title'    => __( 'Attributes', 'f-shop' ),
				'on'       => false,
				'body'     => '',
				'template' => 'attributes'
			),
			'other'      => array(
				'title'    => __( 'Other', 'f-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'other'
			),
			'related'    => array(
				'title'    => __( 'Associated', 'f-shop' ),
				'on'       => false, // Сейчас в разработке
				'body'     => '',
				'template' => 'related'
			),
			'variants'   => array(
				'title'    => __( 'Variation', 'f-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'variants'
			),
			'delivery'   => array(
				'title'  => __( 'Shipping and payment', 'f-shop' ),
				'on'     => true,
				'body'   => '',
				'fields' => array(
					'_fs_delivery_description' => array(
						'label' => __( 'Shipping and Payment Details', 'f-shop' ),
						'type'  => 'editor',
						'help'  => ''
					),

				)
			),
		);

		return apply_filters( 'fs_product_tabs_admin', $tabs );
	}

	/**
	 * Returns a list of keys to sort in the directory
	 *
	 * @return array
	 */
	public function get_orderby_keys() {
		$keys = array(
			'date_desc'  => array(
				'name' => __( 'recently added', 'f-shop' )// недавно добавленные
			),
			'date_asc'   => array(
				'name' => __( 'later added', 'f-shop' ) // давно добавленные
			),
			'price_asc'  => array(
				'name' => __( 'from cheap to expensive', 'f-shop' ) // от дешевых к дорогим
			),
			'price_desc' => array(
				'name' => __( 'from expensive to cheap', 'f-shop' ) // от дорогих к дешевым
			),
			'name_asc'   => array(
				'name' => __( 'by title A to Z', 'f-shop' ) // по названию от А до Я
			),
			'name_desc'  => array(
				'name' => __( 'by title Z to A', 'f-shop' ) // по названию от Я до А
			)
		);

		return apply_filters( 'fs_orderby_keys', $keys );
	}

	/**
	 * Returns order status
	 *
	 * @return array
	 */
	public static function default_order_statuses() {
		$order_statuses = array(
			'new'          => array(
				'name'        => __( 'New', 'f-shop' ),
				'description' => __( 'About all orders with the status of “New” administrator receives notification by mail, which allows him to immediately contact the buyer. For the convenience of accounting for new orders, they are automatically placed in the “New” tab on the order management panel and are displayed as a list, sorted by the date added.', 'f-shop' )
			),
			'processed'    => array(
				'name'        => __( 'Processed', 'f-shop' ),
				'description' => __( 'The order is accepted and can be paid. The status is introduced mainly for the convenience of internal order management, not “New”, but not yet paid or not sent for delivery;', 'f-shop' ),
			),
			'pay'          => array(
				'name'        => __( 'In the process of payment', 'f-shop' ),
				'description' => __( 'The status can be assigned by the administrator after sending an invoice to the client for payment.', 'f-shop' )
			),
			'paid'         => array(
				'name'        => __( 'Paid', 'f-shop' ),
				'description' => __( 'The status is assigned to the order automatically if the settlement is made through the Money Online payment system. If the goods were delivered by courier and paid in cash, the status can be used as a reporting;', 'f-shop' )
			),
			'for-delivery' => array(
				'name'        => __( 'In delivery', 'f-shop' ),
				'description' => __( 'The administrator assigns this status to orders when drawing up the delivery list. The sheet is transferred to the courier along with the goods.', 'f-shop' )
			),
			'delivered'    => array(
				'name'        => __( 'Delivered', 'f-shop' ),
				'description' => __( 'The status is assigned to orders transferred to the courier. An order can maintain this status for a long time, depending on how far the customer is located;', 'f-shop' )
			),
			'refused'      => array(
				'name'        => __( 'Denied', 'f-shop' ),
				'description' => __( 'The status is assigned to orders that cannot be satisfied (for example, the product is not in stock). Later, at any time you can change the status of the order (for example, if the product is in stock);', 'f-shop' )
			),
			'canceled'     => array(
				'name'        => __( 'Canceled', 'f-shop' ),
				'description' => __( 'The administrator assigns the status to the order if the client for some reason refused the order;', 'f-shop' )
			),
			'return'       => array(
				'name'        => __( 'Return', 'f-shop' ),
				'description' => __( 'The administrator assigns the status to the order if the client for some reason returned the goods.', 'f-shop' )
			),
		);

		return apply_filters( 'fs_order_statuses', $order_statuses );
	}

	/**
	 * Copywriting texts in letters
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function get_texts( $key = '' ) {
		$texts = array(
			'mail_copywrite' => __( '<p>This online store is powered by <a href="https://f-shop.top/">F-Shop</a>.  <a href="https://f-shop.top/dokumentacija/">Documentation</a>. <a href="https://f-shop.top/novosti/">News</a>.</p>', 'f-shop' )
		);

		$texts = apply_filters( 'fs_service_text', $texts );

		return ! empty( $texts[ $key ] ) ? $texts[ $key ] : $texts;
	}

	/**
	 * Returns nonce verification code
	 */
	public static function get_nonce() {
		$nonce = wp_create_nonce( self::$nonce );

		return $nonce;
	}

	/**
	 * Displays a hidden field with a nonce verification code.
	 */
	public static function nonce_field() {
		$field = '<input type="hidden" name="' . self::$nonce_field . '" value="' . self::get_nonce() . '">';

		return $field;
	}

	/**
	 * Checks nonce code
	 *
	 * @param string $method
	 *
	 * @return false|int
	 */
	public static function verify_nonce( $method = 'post' ) {
		switch ( $method ) {
			case 'post':
				return wp_verify_nonce( $_POST[ self::$nonce_field ], self::$nonce );
				break;
			case 'get':
				return wp_verify_nonce( $_GET[ self::$nonce_field ], self::$nonce );
				break;
			default:
				return wp_verify_nonce( $_POST[ self::$nonce_field ], self::$nonce );
				break;
		}
	}

	/**
	 * Returns a list of major currencies
	 *
	 * @return array
	 */
	public static function getCurrencies() {
		return apply_filters( 'fs_currencies_filter', self::$currencies );
	}


	/**
	 * @param string $user
	 *
	 * @return array
	 */
	public static function getUsers( $user = '' ) {
		return self::$users[ $user ];
	}


	/**
	 * @return array
	 */
	public static function getFormFields() {
		return self::$form_fields;
	}

	/**
	 * Registration of additional taxonomy fields
	 *
	 * @return array
	 */
	function get_taxonomy_fields() {
		$fields = array(
			'catalog'                             => array(
				'_content'           => array(
					'name' => __( 'Category text', 'f-shop' ),
					'type' => 'editor',
					'args' => array()
				),
				'_thumbnail_id'      => array(
					'name' => __( 'Thumbnail', 'f-shop' ),
					'type' => 'image',
					'args' => array()
				),
				'_icon_id'           => array(
					'name' => __( 'Icon', 'f-shop' ),
					'type' => 'image',
					'args' => array()
				),
				'_category_discount' => array(
					'name' => __( 'Total discount for category products (in percent)', 'f-shop' ),
					'type' => 'text',
					'help' => __( 'Enter a number without a percent sign', 'f-shop' ),
					'size' => 5,
					'args' => array()
				)
			),
			'fs-payment-methods'                  => array(
				'_thumbnail_id'         => array(
					'name' => __( 'Thumbnail', 'f-shop' ),
					'type' => 'image',
					'args' => array()
				),
				'_fs_pay_message'       => array(
					'name' => __( 'E-mail message to the buyer if the order is confirmed by the manager', 'f-shop' ),
					'help' => __( 'This message is sent to the buyer at the time the manager confirms the order. You can use meta data of type: <code>%order_id%</code> - order number, <code>%pay_name%</code> - name of the payment method, <code>%pay_url%</code> - payment reference .', 'f-shop' ),
					'type' => 'textarea',
					'args' => array()
				),
				'_fs_after_pay_message' => array(
					'name' => __( 'Message to the buyer after payment on the site', 'f-shop' ),
					'help' => __( 'This message will be shown if the buyer has successfully paid the order. You can use these variables: <code>%order_id%</code> - order number, <code>%pay_name%</code> - name of the payment method', 'f-shop' ),
					'type' => 'textarea',
					'args' => array()
				),
				'_fs_pay_inactive'      => array(
					'name' => __( 'Unavailable for payment', 'f-shop' ),
					'help' => __( 'If you turn off, then the payment method will not be visible to users, only in the admin panel.', 'f-shop' ),
					'type' => 'checkbox',
					'args' => array()
				),
				'_fs_checkout_redirect' => array(
					'name' => __( 'When choosing this method, send the buyer immediately to the payment page', 'f-shop' ),
					'help' => __( 'This is convenient in some cases, but it is better to leave this option off', 'f-shop' ),
					'type' => 'checkbox',
					'args' => array()
				)
			),
			'fs-delivery-methods'                 => array(
				'_thumbnail_id'        => array(
					'name' => __( 'Thumbnail', 'f-shop' ),
					'type' => 'image',
					'args' => array()
				),
				'_fs_delivery_cost'    => array(
					'name' => __( 'Shipping Cost in Base Currency', 'f-shop' ),
					'type' => 'text',
					'args' => array( 'style' => 'width:72px;' )
				),
				'_fs_delivery_address' => array(
					'name' => __( 'Include address fields when choosing this method', 'f-shop' ),
					'type' => 'checkbox',
					'args' => array()
				)
			),
			'fs-currencies'                       => array(
				'_fs_currency_code'    => array(
					'name' => __( 'International currency code', 'f-shop' ),
					'type' => 'text',
					'args' => array()
				),
				'_fs_currency_cost'    => array(
					'name' => __( 'Cost in base currency', 'f-shop' ),
					'type' => 'text',
					'args' => array()
				),
				'_fs_currency_display' => array(
					'name' => __( 'Display on the site', 'f-shop' ),
					'type' => 'text',
					'args' => array()
				),
				'_fs_currency_locale'  => array(
					'name' => __( 'Currency Language (locale)', 'f-shop' ),
					'type' => 'select',
					'args' => array( 'values' => $this->get_locales(), )
				)
			),
			// Дополнительные поля налога
			$this->data['product_taxes_taxonomy'] => array(
				'_fs_tax_value' => array(
					'name' => __( 'The amount or value of tax as a percentage', 'f-shop' ),
					'type' => 'text',
					'args' => array()
				)
			),
			// Дополнительные поля налога
			$this->data['manufacturer_taxonomy']  => array(
				'_thumbnail_id' => array(
					'name' => __( 'Thumbnail', 'f-shop' ),
					'type' => 'image',
					'args' => array()
				),
			),
			// Дополнительные поля скидок
			$this->data['discount_taxonomy']      => array(
				'discount_where_is' => array(
					'name' => __( 'The discount is activated provided', 'f-shop' ),
					'type' => 'select',

					'args' => array(
						'values' => array(
							'sum'   => __( 'The total amount of goods in the cart', 'f-shop' ),
							'count' => __( 'Number of items in the cart', 'f-shop' )
						)
					)
				),
				'discount_where'    => array(
					'name' => __( 'Discount condition', 'f-shop' ),
					'type' => 'select',
					'args' => array(
						'values' => array(
							'>=' => __( 'More or equal', 'f-shop' ),
							'>'  => __( 'More', 'f-shop' ),
							'<'  => __( 'Less', 'f-shop' ),
							'<=' => __( 'Less or equal', 'f-shop' )
						)
					)
				),
				'discount_value'    => array(
					'name' => __( 'Condition value', 'f-shop' ),
					'type' => 'text',
					'args' => array()
				),
				'discount_amount'   => array(
					'name' => __( 'Discount amount', 'f-shop' ),
					'type' => 'text',
					'args' => array()
				)
			)

		);

		return apply_filters( 'fs_taxonomy_fields', $fields );
	}

	/**
	 * Returns language locales and names
	 *
	 * @return array
	 */
	public function get_locales() {
		$all_locales = array(
			'aa_DJ'  => 'Afar (Djibouti)',
			'aa_ER'  => 'Afar (Eritrea)',
			'aa_ET'  => 'Afar (Ethiopia)',
			'af_ZA'  => 'Afrikaans (South Africa)',
			'sq_AL'  => 'Albanian (Albania)',
			'sq_MK'  => 'Albanian (Macedonia)',
			'am_ET'  => 'Amharic (Ethiopia)',
			'ar_DZ'  => 'Arabic (Algeria)',
			'ar_BH'  => 'Arabic (Bahrain)',
			'ar_EG'  => 'Arabic (Egypt)',
			'ar_IN'  => 'Arabic (India)',
			'ar_IQ'  => 'Arabic (Iraq)',
			'ar_JO'  => 'Arabic (Jordan)',
			'ar_KW'  => 'Arabic (Kuwait)',
			'ar_LB'  => 'Arabic (Lebanon)',
			'ar_LY'  => 'Arabic (Libya)',
			'ar_MA'  => 'Arabic (Morocco)',
			'ar_OM'  => 'Arabic (Oman)',
			'ar_QA'  => 'Arabic (Qatar)',
			'ar_SA'  => 'Arabic (Saudi Arabia)',
			'ar_SD'  => 'Arabic (Sudan)',
			'ar_SY'  => 'Arabic (Syria)',
			'ar_TN'  => 'Arabic (Tunisia)',
			'ar_AE'  => 'Arabic (United Arab Emirates)',
			'ar_YE'  => 'Arabic (Yemen)',
			'an_ES'  => 'Aragonese (Spain)',
			'hy_AM'  => 'Armenian (Armenia)',
			'as_IN'  => 'Assamese (India)',
			'ast_ES' => 'Asturian (Spain)',
			'az_AZ'  => 'Azerbaijani (Azerbaijan)',
			'az_TR'  => 'Azerbaijani (Turkey)',
			'eu_FR'  => 'Basque (France)',
			'eu_ES'  => 'Basque (Spain)',
			'be_BY'  => 'Belarusian (Belarus)',
			'bem_ZM' => 'Bemba (Zambia)',
			'bn_BD'  => 'Bengali (Bangladesh)',
			'bn_IN'  => 'Bengali (India)',
			'ber_DZ' => 'Berber (Algeria)',
			'ber_MA' => 'Berber (Morocco)',
			'byn_ER' => 'Blin (Eritrea)',
			'bs_BA'  => 'Bosnian (Bosnia and Herzegovina)',
			'br_FR'  => 'Breton (France)',
			'bg_BG'  => 'Bulgarian (Bulgaria)',
			'my_MM'  => 'Burmese (Myanmar [Burma])',
			'ca_AD'  => 'Catalan (Andorra)',
			'ca_FR'  => 'Catalan (France)',
			'ca_IT'  => 'Catalan (Italy)',
			'ca_ES'  => 'Catalan (Spain)',
			'zh_CN'  => 'Chinese (China)',
			'zh_HK'  => 'Chinese (Hong Kong SAR China)',
			'zh_SG'  => 'Chinese (Singapore)',
			'zh_TW'  => 'Chinese (Taiwan)',
			'cv_RU'  => 'Chuvash (Russia)',
			'kw_GB'  => 'Cornish (United Kingdom)',
			'crh_UA' => 'Crimean Turkish (Ukraine)',
			'hr_HR'  => 'Croatian (Croatia)',
			'cs_CZ'  => 'Czech (Czech Republic)',
			'da_DK'  => 'Danish (Denmark)',
			'dv_MV'  => 'Divehi (Maldives)',
			'nl_AW'  => 'Dutch (Aruba)',
			'nl_BE'  => 'Dutch (Belgium)',
			'nl_NL'  => 'Dutch (Netherlands)',
			'dz_BT'  => 'Dzongkha (Bhutan)',
			'en_AG'  => 'English (Antigua and Barbuda)',
			'en_AU'  => 'English (Australia)',
			'en_BW'  => 'English (Botswana)',
			'en_CA'  => 'English (Canada)',
			'en_DK'  => 'English (Denmark)',
			'en_HK'  => 'English (Hong Kong SAR China)',
			'en_IN'  => 'English (India)',
			'en_IE'  => 'English (Ireland)',
			'en_NZ'  => 'English (New Zealand)',
			'en_NG'  => 'English (Nigeria)',
			'en_PH'  => 'English (Philippines)',
			'en_SG'  => 'English (Singapore)',
			'en_ZA'  => 'English (South Africa)',
			'en_GB'  => 'English (United Kingdom)',
			'en_US'  => 'English (United States)',
			'en_ZM'  => 'English (Zambia)',
			'en_ZW'  => 'English (Zimbabwe)',
			'eo'     => 'Esperanto',
			'et_EE'  => 'Estonian (Estonia)',
			'fo_FO'  => 'Faroese (Faroe Islands)',
			'fil_PH' => 'Filipino (Philippines)',
			'fi_FI'  => 'Finnish (Finland)',
			'fr_BE'  => 'French (Belgium)',
			'fr_CA'  => 'French (Canada)',
			'fr_FR'  => 'French (France)',
			'fr_LU'  => 'French (Luxembourg)',
			'fr_CH'  => 'French (Switzerland)',
			'fur_IT' => 'Friulian (Italy)',
			'ff_SN'  => 'Fulah (Senegal)',
			'gl_ES'  => 'Galician (Spain)',
			'lg_UG'  => 'Ganda (Uganda)',
			'gez_ER' => 'Geez (Eritrea)',
			'gez_ET' => 'Geez (Ethiopia)',
			'ka_GE'  => 'Georgian (Georgia)',
			'de_AT'  => 'German (Austria)',
			'de_BE'  => 'German (Belgium)',
			'de_DE'  => 'German (Germany)',
			'de_LI'  => 'German (Liechtenstein)',
			'de_LU'  => 'German (Luxembourg)',
			'de_CH'  => 'German (Switzerland)',
			'el_CY'  => 'Greek (Cyprus)',
			'el_GR'  => 'Greek (Greece)',
			'gu_IN'  => 'Gujarati (India)',
			'ht_HT'  => 'Haitian (Haiti)',
			'ha_NG'  => 'Hausa (Nigeria)',
			'iw_IL'  => 'Hebrew (Israel)',
			'he_IL'  => 'Hebrew (Israel)',
			'hi_IN'  => 'Hindi (India)',
			'hu_HU'  => 'Hungarian (Hungary)',
			'is_IS'  => 'Icelandic (Iceland)',
			'ig_NG'  => 'Igbo (Nigeria)',
			'id_ID'  => 'Indonesian (Indonesia)',
			'ia'     => 'Interlingua',
			'iu_CA'  => 'Inuktitut (Canada)',
			'ik_CA'  => 'Inupiaq (Canada)',
			'ga_IE'  => 'Irish (Ireland)',
			'it_IT'  => 'Italian (Italy)',
			'it_CH'  => 'Italian (Switzerland)',
			'ja_JP'  => 'Japanese (Japan)',
			'kl_GL'  => 'Kalaallisut (Greenland)',
			'kn_IN'  => 'Kannada (India)',
			'ks_IN'  => 'Kashmiri (India)',
			'csb_PL' => 'Kashubian (Poland)',
			'kk_KZ'  => 'Kazakh (Kazakhstan)',
			'km_KH'  => 'Khmer (Cambodia)',
			'rw_RW'  => 'Kinyarwanda (Rwanda)',
			'ky_KG'  => 'Kirghiz (Kyrgyzstan)',
			'kok_IN' => 'Konkani (India)',
			'ko_KR'  => 'Korean (South Korea)',
			'ku_TR'  => 'Kurdish (Turkey)',
			'lo_LA'  => 'Lao (Laos)',
			'lv_LV'  => 'Latvian (Latvia)',
			'li_BE'  => 'Limburgish (Belgium)',
			'li_NL'  => 'Limburgish (Netherlands)',
			'lt_LT'  => 'Lithuanian (Lithuania)',
			'nds_DE' => 'Low German (Germany)',
			'nds_NL' => 'Low German (Netherlands)',
			'mk_MK'  => 'Macedonian (Macedonia)',
			'mai_IN' => 'Maithili (India)',
			'mg_MG'  => 'Malagasy (Madagascar)',
			'ms_MY'  => 'Malay (Malaysia)',
			'ml_IN'  => 'Malayalam (India)',
			'mt_MT'  => 'Maltese (Malta)',
			'gv_GB'  => 'Manx (United Kingdom)',
			'mi_NZ'  => 'Maori (New Zealand)',
			'mr_IN'  => 'Marathi (India)',
			'mn_MN'  => 'Mongolian (Mongolia)',
			'ne_NP'  => 'Nepali (Nepal)',
			'se_NO'  => 'Northern Sami (Norway)',
			'nso_ZA' => 'Northern Sotho (South Africa)',
			'nb_NO'  => 'Norwegian Bokmål (Norway)',
			'nn_NO'  => 'Norwegian Nynorsk (Norway)',
			'oc_FR'  => 'Occitan (France)',
			'or_IN'  => 'Oriya (India)',
			'om_ET'  => 'Oromo (Ethiopia)',
			'om_KE'  => 'Oromo (Kenya)',
			'os_RU'  => 'Ossetic (Russia)',
			'pap_AN' => 'Papiamento (Netherlands Antilles)',
			'ps_AF'  => 'Pashto (Afghanistan)',
			'fa_IR'  => 'Persian (Iran)',
			'pl_PL'  => 'Polish (Poland)',
			'pt_BR'  => 'Portuguese (Brazil)',
			'pt_PT'  => 'Portuguese (Portugal)',
			'pa_IN'  => 'Punjabi (India)',
			'pa_PK'  => 'Punjabi (Pakistan)',
			'ro_RO'  => 'Romanian (Romania)',
			'ru_RU'  => 'Russian (Russia)',
			'ru_UA'  => 'Russian (Ukraine)',
			'sa_IN'  => 'Sanskrit (India)',
			'sc_IT'  => 'Sardinian (Italy)',
			'gd_GB'  => 'Scottish Gaelic (United Kingdom)',
			'sr_ME'  => 'Serbian (Montenegro)',
			'sr_RS'  => 'Serbian (Serbia)',
			'sid_ET' => 'Sidamo (Ethiopia)',
			'sd_IN'  => 'Sindhi (India)',
			'si_LK'  => 'Sinhala (Sri Lanka)',
			'sk_SK'  => 'Slovak (Slovakia)',
			'sl_SI'  => 'Slovenian (Slovenia)',
			'so_DJ'  => 'Somali (Djibouti)',
			'so_ET'  => 'Somali (Ethiopia)',
			'so_KE'  => 'Somali (Kenya)',
			'so_SO'  => 'Somali (Somalia)',
			'nr_ZA'  => 'South Ndebele (South Africa)',
			'st_ZA'  => 'Southern Sotho (South Africa)',
			'es_AR'  => 'Spanish (Argentina)',
			'es_BO'  => 'Spanish (Bolivia)',
			'es_CL'  => 'Spanish (Chile)',
			'es_CO'  => 'Spanish (Colombia)',
			'es_CR'  => 'Spanish (Costa Rica)',
			'es_DO'  => 'Spanish (Dominican Republic)',
			'es_EC'  => 'Spanish (Ecuador)',
			'es_SV'  => 'Spanish (El Salvador)',
			'es_GT'  => 'Spanish (Guatemala)',
			'es_HN'  => 'Spanish (Honduras)',
			'es_MX'  => 'Spanish (Mexico)',
			'es_NI'  => 'Spanish (Nicaragua)',
			'es_PA'  => 'Spanish (Panama)',
			'es_PY'  => 'Spanish (Paraguay)',
			'es_PE'  => 'Spanish (Peru)',
			'es_ES'  => 'Spanish (Spain)',
			'es_US'  => 'Spanish (United States)',
			'es_UY'  => 'Spanish (Uruguay)',
			'es_VE'  => 'Spanish (Venezuela)',
			'sw_KE'  => 'Swahili (Kenya)',
			'sw_TZ'  => 'Swahili (Tanzania)',
			'ss_ZA'  => 'Swati (South Africa)',
			'sv_FI'  => 'Swedish (Finland)',
			'sv_SE'  => 'Swedish (Sweden)',
			'tl_PH'  => 'Tagalog (Philippines)',
			'tg_TJ'  => 'Tajik (Tajikistan)',
			'ta_IN'  => 'Tamil (India)',
			'tt_RU'  => 'Tatar (Russia)',
			'te_IN'  => 'Telugu (India)',
			'th_TH'  => 'Thai (Thailand)',
			'bo_CN'  => 'Tibetan (China)',
			'bo_IN'  => 'Tibetan (India)',
			'tig_ER' => 'Tigre (Eritrea)',
			'ti_ER'  => 'Tigrinya (Eritrea)',
			'ti_ET'  => 'Tigrinya (Ethiopia)',
			'ts_ZA'  => 'Tsonga (South Africa)',
			'tn_ZA'  => 'Tswana (South Africa)',
			'tr_CY'  => 'Turkish (Cyprus)',
			'tr_TR'  => 'Turkish (Turkey)',
			'tk_TM'  => 'Turkmen (Turkmenistan)',
			'ug_CN'  => 'Uighur (China)',
			'uk'     => 'Ukrainian (Ukraine)',
			'hsb_DE' => 'Upper Sorbian (Germany)',
			'ur_PK'  => 'Urdu (Pakistan)',
			'uz_UZ'  => 'Uzbek (Uzbekistan)',
			've_ZA'  => 'Venda (South Africa)',
			'vi_VN'  => 'Vietnamese (Vietnam)',
			'wa_BE'  => 'Walloon (Belgium)',
			'cy_GB'  => 'Welsh (United Kingdom)',
			'fy_DE'  => 'Western Frisian (Germany)',
			'fy_NL'  => 'Western Frisian (Netherlands)',
			'wo_SN'  => 'Wolof (Senegal)',
			'xh_ZA'  => 'Xhosa (South Africa)',
			'yi_US'  => 'Yiddish (United States)',
			'yo_NG'  => 'Yoruba (Nigeria)',
			'zu_ZA'  => 'Zulu (South Africa)'
		);

		return apply_filters( 'fs_locales', $all_locales );
	}

	/**
	 * Sets the headers to send emails
	 *
	 * @param bool $html
	 *
	 * @return mixed
	 */
	function email_headers( $html = true ) {
		$headers = array();
		if ( $html ) {
			$headers[] = 'content-type: text/html';
		}
		// Имейл отправителя по умолчанию
		$sender_email = 'manager@' . $_SERVER['SERVER_NAME'];

		$headers[] = sprintf( 'From: %s <%s>', fs_option( 'name_sender', get_bloginfo( 'name' ) ), fs_option( 'email_sender', $sender_email ) );

		return apply_filters( 'fs_email_headers', $headers );
	}

	/**
	 * Возвращает название метаполя по ключу
	 *
	 * @param string $meta_key
	 *
	 * @return string|array
	 */
	public static function get_meta( $meta_key = '' ) {
		$meta = array(
			'price'             => 'fs_price',
			'action_price'      => 'fs_action_price',
			'currency'          => 'fs_currency',
			'sku'               => 'fs_articul',
			'remaining_amount'  => 'fs_remaining_amount',
			'gallery'           => 'fs_galery',
			'related_products'  => 'fs_related_products',
			'vendor'            => 'fs_vendor',
			'variants'          => 'fs_variant',
			'variants_price'    => 'fs_variant_price',
			'variant_count'     => 'fs_variant_count',
			'variant_count_max' => 'fs_variant_count_max',
			'variated_on'       => 'fs_variated_on',
			'exclude_archive'   => 'fs_exclude_archive',
			'label_bestseller'  => 'fs_on_bestseller',
			'label_promotion'   => 'fs_on_promotion',
			'label_novelty'     => 'fs_on_novelty'
		);

		$meta = apply_filters( 'fs_meta', $meta );
		if ( empty( $meta_key ) ) {
			return $meta;
		} else {
			return $meta[ $meta_key ];
		}
	}


	/**
	 * Returns a setting or array of basic plugin settings
	 *
	 * @param string $key key of the array of settings
	 *
	 * @return array|mixed|void
	 *
	 * TODO: В будущем перенести все типы настроек в эту. Создать подмасив 'taxonomies' и поместить все таксономии, 'meta' с метаполями и т.д.
	 */
	public static function get_data( $key = '' ) {
		$data = array(
			'plugin_path'            => FS_PLUGIN_PATH,
			'plugin_url'             => FS_PLUGIN_URL,
			'plugin_ver'             => '1.3',
			'plugin_name'            => 'f-shop',
			'plugin_user_template'   => get_template_directory() . '/f-shop/',
			'plugin_template'        => FS_PLUGIN_PATH . 'templates/front-end/',
			'plugin_settings'        => 'f-shop-settings',
			'post_type'              => 'product',
			'post_type_orders'       => 'orders',
			'product_taxonomy'       => 'catalog',
			'product_att_taxonomy'   => 'product-attributes',
			'product_pay_taxonomy'   => 'fs-payment-methods',
			'product_brand_taxonomy' => 'fs-brands',
			'manufacturer_taxonomy'  => 'brands',
			'product_del_taxonomy'   => 'fs-delivery-methods',
			'product_taxes_taxonomy' => 'fs-taxes',
			'discount_taxonomy'      => 'fs-discounts',
			'currencies_taxonomy'    => 'fs-currencies',
			'preloader'              => FS_PLUGIN_URL . '/assets/img/ajax-loader.gif',
			'default_order_status'   => 'new'

		);
		$data = apply_filters( 'fs_data', $data );

		if ( ! empty( $key ) ) {
			return $data[ $key ];
		} else {
			return $data;

		}

	}


}