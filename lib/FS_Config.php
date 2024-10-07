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
	public static $nonce = 'f-shop';
	public static $text_domain = 'f-shop';
	public static $pages = array();

	protected static $nonce_field = 'fs_secret';
	private static $telegram_bot = 'FShopOfficialBot';
	private static $telegram_bot_token = '7657903599:AAEJ_-9Tpcuot9fpLdS5yf2Fsbso5sSFhDo';


	/**
	 * FS_Config constructor.
	 */
	function __construct() {
		$this->data = self::get_data();

		// Gets an array of service texts
		$this->texts = self::get_texts();


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


		self::$currencies = array(
			'USD' => __( 'US dollar', 'f-shop' ),
			'UAH' => __( 'Ukrainian hryvnia', 'f-shop' ),
			'RUB' => __( 'Russian ruble', 'f-shop' ),
		);

		self::$users = array(
			'new_user_role' => 'client',
			'new_user_name' => __( 'Client', 'f-shop' )
		);

		self::$pages = self::get_pages();
	}


	/**
	 * Service pages that are created when the plugin is first activated
	 *
	 * @return array
	 */
	public static function get_pages() {
		return array(
			'cart'         => array(
				'title'   => __( 'Basket', 'f-shop' ),
				'content' => '[fs_cart]',
				'option'  => 'page_cart'
			),
			'ckeckout'     => array(
				'title'   => __( 'Checkout', 'f-shop' ),
				'content' => '[fs_checkout]',
				'option'  => 'page_checkout'
			),
			'pay'          => array(
				'title'   => __( 'Payment order', 'f-shop' ),
				'content' => '[fs_order_pay]',
				'option'  => 'page_payment'
			),
			'thanks'       => array(
				'title'   => __( 'Thank you for your purchase.', 'f-shop' ),
				'content' => '[fs_checkout_success]',
				'option'  => 'page_success'
			),
			'wishlist'     => array(
				'title'   => __( 'Wishlist', 'f-shop' ),
				'content' => '[fs_wishlist]',
				'option'  => 'page_whishlist'
			),
			'account'      => array(
				'title'   => __( 'Personal Area', 'f-shop' ),
				'content' => '[fs_user_cabinet]',
				'option'  => 'page_cabinet'
			),
			'log-in'       => array(
				'title'   => __( 'Sign in', 'f-shop' ),
				'content' => '[fs_login]',
				'option'  => 'page_auth'
			),
			'register'     => array(
				'title'   => __( 'Register', 'f-shop' ),
				'content' => '[fs_register]',
				'option'  => 'page_register'
			),
			'order-info'   => array(
				'title'   => __( 'Information about order', 'f-shop' ),
				'content' => '[fs_order_info]',
				'option'  => 'page_order_detail'
			),
			'lostpassword' => array(
				'title'   => __( 'Password reset', 'f-shop' ),
				'content' => '[fs_lostpassword]',
				'option'  => 'page_lostpassword'
			)

		);
	}


	/**
	 * Returns a list of keys to sort in the directory
	 *
	 * @return array
	 */
	public static function get_orderby_keys() {
		$keys = array(
			'date_desc'    => array(
				'name' => __( 'Recently added', 'f-shop' )
			),
			'date_asc'     => array(
				'name' => __( 'Later added', 'f-shop' )
			),
			'price_asc'    => array(
				'name' => __( 'From cheap to expensive', 'f-shop' )
			),
			'price_desc'   => array(
				'name' => __( 'From expensive to cheap', 'f-shop' )
			),
			'name_asc'     => array(
				'name' => __( 'By title A to Z', 'f-shop' )
			),
			'name_desc'    => array(
				'name' => __( 'By title Z to A', 'f-shop' )
			),
			'views_desc'   => array(
				'name' => __( 'Behind the popularity', 'f-shop' )
			),
			'rating_desc'  => array(
				'name' => __( 'By rating', 'f-shop' )
			),
			'action_price' => array(
				'name' => __( 'First promotional', 'f-shop' )
			),
		);

		return apply_filters( 'fs_orderby_keys', $keys );
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
	 * Returns language locales and names
	 *
	 * @return array
	 */
	public static function get_locales() {
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
			'real_price'        => 'fs_real_price',
			'currency'          => 'fs_currency',
			'sku'               => 'fs_articul',
			'remaining_amount'  => 'fs_remaining_amount',
			'gallery'           => 'fs_galery',
			'related_products'  => 'fs_related_products',
			'vendor'            => 'fs_vendor',
			'variations'        => 'fs_variations',
			'variants'          => 'fs_variant',
			'variants_price'    => 'fs_variant_price',
			'variant_count'     => 'fs_variant_count',
			'variant_count_max' => 'fs_variant_count_max',
			'variated_on'       => 'fs_variated_on',
			'exclude_archive'   => 'fs_exclude_archive',
			'label_bestseller'  => 'fs_on_bestseller',
			'label_promotion'   => 'fs_on_promotion',
			'label_novelty'     => 'fs_on_novelty',
			'up_sell'           => 'fs_up_sell',
			'cross_sell'        => 'fs_cross_sell',
			'product_rating'    => 'fs_product_rating',
			'product_type'      => 'fs_product_type'
		);

		$meta = apply_filters( 'fs_meta', $meta );
		if ( empty( $meta_key ) ) {
			return $meta;
		} else {
			return $meta[ $meta_key ];
		}
	}

	/**
	 * Returns extended meta field data
	 *
	 * @param string $key
	 *
	 * @return array|mixed|void
	 */
	public static function get_product_field( $key = '' ) {
		$fields = array(
			'price'             => [ 'key' => 'fs_price' ],
			'action_price'      => [ 'key' => 'fs_action_price' ],
			'real_price'        => [ 'key' => '_fs_real_price' ],
			'currency'          => [ 'key' => 'fs_currency' ],
			'sku'               => [
				'key'   => 'fs_articul',
				'label' => __( 'SKU', 'f-shop' ),
				'type'  => 'text',
			],
			'quantity'          => [
				'key'   => 'fs_remaining_amount',
				'type'  => 'text',
				'atts'  => [
					'min'  => 0,
					'step' => 1,
					'type' => 'number',
				],
				'label' => __( 'Stock in stock', 'f-shop' ),
				'help'  => __( 'Enter "0" if stock is exhausted. An empty field means inventory control for the item. disabled, and the goods are always in the presence!', 'f-shop' )
			],
			'gallery'           => [ 'key' => 'fs_galery' ],
			'related_products'  => [ 'key' => 'fs_related_products' ],
			'vendor'            => [ 'key' => 'fs_vendor' ],
			'variants'          => [ 'key' => 'fs_variant' ],
			'variants_price'    => [ 'key' => 'fs_variant_price' ],
			'variant_count'     => [ 'key' => 'fs_variant_count' ],
			'variant_count_max' => [ 'key' => 'fs_variant_count_max' ],
			'variated_on'       => [ 'key' => 'fs_variated_on' ],
			'exclude_archive'   => [
				'key'   => 'fs_exclude_archive',
				'type'  => 'checkbox',
				'label' => __( 'Exclude from the archive of goods', 'f-shop' )
			],
			'label_bestseller'  => [
				'key'   => 'fs_on_bestseller',
				'type'  => 'checkbox',
				'text'  => __( 'Best-seller', 'f-shop' ),
				'label' => __( 'Include the tag "Hit sales"', 'f-shop' )
			],
			'label_promotion'   => [
				'key'   => 'fs_on_promotion',
				'type'  => 'checkbox',
				'text'  => __( 'Sale', 'f-shop' ),
				'label' => __( 'Include tag "Promotion"', 'f-shop' )
			],
			'label_novelty'     => [
				'key'   => 'fs_on_novelty',
				'type'  => 'checkbox',
				'text'  => __( 'Novelty', 'f-shop' ),
				'label' => __( 'Include tag "New"', 'f-shop' )
			],
			'up_sell'           => [ 'key' => 'fs_up_sell' ],
			'cross_sell'        => [ 'key' => 'fs_cross_sell' ],
			'is_virtual'        => [ 'key' => 'fs_is_virtual' ]
		);

		$fields = apply_filters( 'fs_product_field', $fields );
		if ( empty( $key ) ) {
			return $fields;
		} else {
			return $fields[ $key ];
		}
	}

	/**
	 * Начальный список языков для мультиязычности полей
	 *
	 * @return mixed|void
	 */
	public
	static function get_languages() {
		$languages = array(
			'en' => array(
				'name'   => __( 'English', 'f-shop' ),
				'locale' => 'en_US'
			),
			'ru' => array(
				'name'   => __( 'Russian', 'f-shop' ),
				'locale' => 'ru_RU'
			),
			'ua' => array(
				'name'   => __( 'Ukrainian', 'f-shop' ),
				'locale' => 'uk'
			)
		);

		return apply_filters( 'fs_languages', $languages );
	}

	/**
	 * Язык (локаль) по умолчанию
	 *
	 * @return mixed|void
	 */
	public
	static function default_locale() {
		return apply_filters( 'fs_default_language', 'ru_RU' );
	}

	/**
	 * Язык (локаль) по умолчанию
	 *
	 * @return mixed|void
	 */
	public
	static function default_language_name() {
		return apply_filters( 'fs_default_language_name', 'ru' );
	}

	/**
	 * Возвращает true если используется локаль по умолчанию
	 *
	 * @return bool
	 */
	public static function is_default_locale() {
		return self::default_locale() == get_locale();
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
	public
	static function get_data(
		$key = ''
	) {
		$data = array(
			'plugin_path'             => FS_PLUGIN_PATH,
			'plugin_url'              => FS_PLUGIN_URL,
			'plugin_ver'              => '1.3',
			'plugin_name'             => 'f-shop',
			'plugin_user_template'    => get_template_directory() . '/f-shop/',
			'plugin_template'         => FS_PLUGIN_PATH . 'templates/front-end/',
			'plugin_settings'         => 'f-shop-settings',
			'post_type'               => 'product',
			'post_type_orders'        => 'orders',
			'order_statuses_taxonomy' => 'fs-order-status',
			'product_taxonomy'        => 'catalog',
			'features_taxonomy'       => 'product-attributes',
			'product_pay_taxonomy'    => 'fs-payment-methods',
			'brand_taxonomy'          => 'fs-brands',
			'product_del_taxonomy'    => 'fs-delivery-methods',
			'product_taxes_taxonomy'  => 'fs-taxes',
			'discount_taxonomy'       => 'fs-discounts',
			'currencies_taxonomy'     => 'fs-currencies',
			'preloader'               => FS_PLUGIN_URL . '/assets/img/ajax-loader.gif',
			'default_order_status'    => 'new'

		);
		$data = apply_filters( 'fs_data', $data );

		if ( ! empty( $key ) ) {
			return $data[ $key ];
		} else {
			return $data;

		}

	}

	public static function get_telegram_bot_token(): string {
		return self::$telegram_bot_token;
	}
}