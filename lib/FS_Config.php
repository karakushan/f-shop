<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Created by PhpStorm.
 * User: karak
 * Date: 25.08.2016
 * Time: 20:19
 */
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
	public static $nonce = 'fast-shop';
	public static $text_domain = 'fast-shop';
	public static $pages = array();

	protected static $nonce_field = 'fs-secret';

	/**
	 * FS_Config constructor.
	 */
	function __construct() {
		//Массив общих настроек плагина. При изменении настройки все настройки меняются глобально.
		$data       = array(
			'plugin_path'          => FS_PLUGIN_PATH,
			'plugin_url'           => FS_PLUGIN_URL,
			'plugin_ver'           => '1.1',
			'plugin_name'          => 'f-shop',
			'plugin_user_template' => get_template_directory() . '/fast-shop/',
			'plugin_template'      => FS_PLUGIN_PATH . 'templates/front-end/',
			'plugin_settings'      => 'fast-shop-settings',
			'post_type'            => 'product',
			'post_type_orders'     => 'orders',
			'product_taxonomy'     => 'catalog',
			'product_att_taxonomy' => 'product-attributes',
			'product_pay_taxonomy' => 'fs-payment-methods',
			'product_del_taxonomy' => 'fs-delivery-methods',
			'discount_taxonomy'    => 'fs-discounts',
			'currencies_taxonomy'  => 'fs-currencies',
			'preloader'            => FS_PLUGIN_URL . '/assets/img/ajax-loader.gif',
			'default_order_status' => 'new' // статус заказа присваиваемый новому заказу
		);
		$this->data = apply_filters( 'fs_data', $data );

		// Получает массив служебных текстов
		$this->texts = self::get_texts();

		//Табы отображаемые в метабоксе в редактировании товара
		$this->tabs = $this->get_product_tabs();

		//Массив настроек сайта
		$this->options = get_option( 'fs_option', array() );


		//Массив настроек мета полей продукта (записи). При изменении настройки все настройки меняются глобально.
		$meta = array(
			//базовая цена
			'price'             => 'fs_price',
			//акционная цена, перебивает цену
			'action_price'      => 'fs_action_price',
			// валюта товара
			'currency'          => 'fs_currency',
			//артикул
			'product_article'   => 'fs_articul',
			//запас товаров на складе
			'remaining_amount'  => 'fs_remaining_amount',
			//галерея
			'gallery'           => 'fs_galery',
			// похожие товары выбранные вручную
			'related_products'  => 'fs_related_products',
			// поле производителя
			'vendor'            => 'fs_vendor',
			// вариации товара
			'variants'          => 'fs_variant',
			// цены вариации товара
			'variants_price'    => 'fs_variant_price',
			// начальное количество
			'variant_count'     => 'fs_variant_count',
			// максимальное количество
			'variant_count_max' => 'fs_variant_count_max',
			// включает вариативность товара
			'variated_on'       => 'fs_variated_on',
			// исключает из архива товаров
			'exclude_archive'   => 'fs_exclude_archive',
			// включает метку Хит продаж
			'label_bestseller'  => 'fs_on_bestseller',
			// включает метку Акция
			'label_promotion'   => 'fs_on_promotion',
			// включает метку Новинка
			'label_novelty'     => 'fs_on_novelty'
		);

		$this->meta = apply_filters( 'fs_meta', $meta );

		$this->term_meta = array(
			'att_type'      => 'fs_att_type',
			'att_value'     => 'fs_att_value',
			'att_unit'      => 'fs_att_unit',
			'att_unit_type' => 'fs_att_unit_type',
			'att_start'     => 'fs_att_start',
			'att_end'       => 'fs_att_end',
		);

		//  устанавливаем основные типы цен
		self::$prices = array(
			'price'        => array(
				'id'          => 'base-price',
				'name'        => __( 'Базовая цена', 'fast-shop' ),
				'meta_key'    => $this->meta['price'],
				'on'          => true,
				'description' => __( 'Основной тип цены', 'fast-shop' )
			),
			'action_price' => array(
				'id'          => 'action-price',
				'name'        => __( 'Акционная цена', 'fast-shop' ),
				'meta_key'    => $this->meta['action_price'],
				'on'          => true,
				'description' => __( 'Этот тип изменяет базовую цену отображаемую по умолчанию', 'fast-shop' )
			)
		);

		self::$user_meta = array(
			'display_name'   => array( 'label' => 'Отображаемое имя', 'name' => 'display_name' ),
			'user_email'     => array( 'label' => 'E-mail', 'name' => 'user_email' ),
			'phone'          => array( 'label' => 'Телефон', 'name' => 'phone' ),
			'birth_day'      => array( 'label' => 'Дата рождения', 'name' => 'birth_day' ),
			'gender'         => array( 'label' => 'Пол', 'name' => 'gender' ),
			'state'          => array( 'label' => 'Штат/Область', 'name' => 'state' ),
			'country'        => array( 'label' => 'Страна', 'name' => 'country' ),
			'city'           => array( 'label' => 'Город', 'name' => 'city' ),
			'adress'         => array( 'label' => 'Адрес', 'name' => 'adress' ),
			'location'       => array( 'label' => 'Позиция на карте', 'name' => 'location' ),
			'profile_update' => array( 'label' => 'Дата обновления', 'name' => 'profile_update' )
		);

		self::$form_fields = array(
			'fs_email'             => array(
				'type'        => 'email',
				'label'       => '',
				'placeholder' => 'Ваш email',
				'title'       => 'Ведите корректный email',
				'required'    => true
			),
			'fs_first_name'        => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => 'Ваше имя',
				'required'    => true
			),
			'fs_last_name'         => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => 'Ваша фамилия',
				'required'    => true
			),
			'fs_phone'             => array(
				'type'        => 'tel',
				'label'       => '',
				'placeholder' => 'Телефон',
				'title'       => 'Ведите корректный номер телефона',
				'required'    => true,
				'save_meta'   => 1
			),
			'fs_city'              => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => 'Город',
				'required'    => true,
				'save_meta'   => 1
			),
			'fs_adress'            => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => 'Адрес доставки',
				'required'    => false,
				'save_meta'   => 1
			),
			'fs_home_num'          => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => 'Номер дома',
				'required'    => false,
				'save_meta'   => 1
			),
			'fs_apartment_num'     => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => 'Номер квартиры',
				'required'    => false,
				'save_meta'   => 1
			),
			'fs_delivery_number'   => array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => 'Номер отделения',
				'required'    => false,
				'save_meta'   => 1
			),
			'fs_delivery_methods'  => array(
				'type'        => 'del_methods',
				'label'       => __( 'Delivery method', 'fast-shop' ),
				'placeholder' => 'Способ доставки',
				'required'    => true,
				'save_meta'   => 1
			),
			'fs_payment_methods'   => array(
				'type'      => 'pay_methods',
				'label'     => __( 'Payment method', 'fast-shop' ),
				'required'  => true,
				'save_meta' => 1
			),
			'fs_comment'           => array(
				'type'        => 'textarea',
				'label'       => '',
				'placeholder' => 'Комментарий',
				'required'    => false
			),
			'fs_customer_register' => array(
				'type'     => 'checkbox',
				'label'    => __( 'Register on the site', 'fast-shop' ),
				'value'    => 1,
				'required' => false
			),
		);

		self::$currencies = array(
			'USD' => __( 'US dollar', 'fast-shop' ),
			'UAH' => __( 'Ukrainian hryvnia', 'fast-shop' ),
			'RUB' => __( 'Russian ruble', 'fast-shop' ),
		);

		self::$users = array(
			'new_user_role' => 'client',
			'new_user_name' => __( 'Client', 'fast-shop' )
		);

		self::$pages = array(
			'cart'       => array(
				'title'   => 'Корзина',
				'content' => '[fs_cart]',
				'option'  => 'page_cart'
			),
			'ckeckout'   => array(
				'title'   => __( 'Checkout', 'fast-shop' ),
				'content' => '[fs_checkout]',
				'option'  => 'page_checkout'
			),
			'pay'        => array(
				'title'   => 'Оплата покупки',
				'content' => '[fs_pay_methods]',
				'option'  => 'page_payment'
			),
			'thanks'     => array(
				'title'   => 'Благодарим за покупку',
				'content' => '[fs_checkout_success]',
				'option'  => 'page_success'
			),
			'wishlist'   => array(
				'title'   => 'Список желаний',
				'content' => '[fs_wishlist]',
				'option'  => 'page_whishlist'
			),
			'account'    => array(
				'title'   => 'Личный кабинет',
				'content' => '[fs_user_cabinet]',
				'option'  => 'page_cabinet'
			),
			'log-in'     => array(
				'title'   => 'Вход на сайт',
				'content' => '[fs_login]',
				'option'  => 'page_auth'
			),
			'order-info' => array(
				'title'   => 'Информация о заказе',
				'content' => '[fs_order_info]',
				'option'  => 'page_order_detail'
			),

		);

	}

	/**
	 * Получает массив который содержит списко вкладок настроек товара
	 *
	 * @return mixed|void
	 */
	public function get_product_tabs() {
		$tabs = array(
			'prices'     => array(
				'title'       => __( 'Цены', 'fast-shop' ),
				'on'          => true,
				'description' => __( 'В этой вкладке вы можете настроить цены товаров', 'fast-shop' ),
				'fields'      => array(
					$this->meta['price']        => array(
						'label' => __( 'Базовая цена', 'fast-shop' ),
						'type'  => 'text',
						'help'  => __( 'Это основная цена на сайте. Поле обязательное!', 'fast-shop' )
					),
					$this->meta['action_price'] => array(
						'label' => __( 'Акционная цена', 'fast-shop' ),
						'type'  => 'text',
						'help'  => __( 'Если это поле заполнено, то базовая цена теряет свою актуальность. Но вы можете отображать её на сайте.', 'fast-shop' )
					),
					$this->meta['currency']     => array(
						'label'    => __( 'Валюта товара', 'fast-shop' ),
						'type'     => 'dropdown_categories',
						'help'     => __( 'Поле можно использовать если вы включили мультивалютность в настроках.', 'fast-shop' ),
						'taxonomy' => $this->data['currencies_taxonomy']
					)
				)
			),
			'gallery'    => array(
				'title'    => __( 'Gallery', 'fast-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'gallery'
			),
			'attributes' => array(
				'title'    => __( 'Attributes', 'fast-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'attributes'
			),
			'other'      => array(
				'title'    => __( 'Other', 'fast-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'other'
			),
			'related'    => array(
				'title'    => __( 'Associated', 'fast-shop' ),
				'on'       => false, // Сейчас в разработке
				'body'     => '',
				'template' => 'related'
			),
			'variants'   => array(
				'title'    => __( 'Variation', 'fast-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'variants'
			),
		);

		return apply_filters( 'fs_product_tabs', $tabs );
	}

	/**
	 * Возвращает список ключей для сортировки в каталоге
	 *
	 * @return mixed|void
	 */
	public function get_orderby_keys() {
		$keys = array(
			'date_desc'  => array(
				'name' => __( 'recently added', 'fast-shop' )// недавно добавленные
			),
			'date_asc'   => array(
				'name' => __( 'later added', 'fast-shop' ) // давно добавленные
			),
			'price_asc'  => array(
				'name' => __( 'from cheap to expensive', 'fast-shop' ) // от дешевых к дорогим
			),
			'price_desc' => array(
				'name' => __( 'from expensive to cheap', 'fast-shop' ) // от дорогих к дешевым
			),
			'name_asc'   => array(
				'name' => __( 'by title A to Z', 'fast-shop' ) // по названию от А до Я
			),
			'name_desc'  => array(
				'name' => __( 'by title Z to A', 'fast-shop' ) // по названию от Я до А
			)
		);

		return apply_filters( 'fs_orderby_keys', $keys );
	}

	public static function default_order_statuses() {
		$order_statuses = array(
			'new'          => array(
				'name'        => 'Новый',
				'description' => 'Обо всех заказах со статусом “Новый” администратор получает уведомления по почте, что позволяет ему мгновенно связываться с покупателем. Для удобства учета новых заказов, они автоматически попадают во вкладку “Новые” на панели управления заказами и отображаются в виде списка с сортировкой по дате добавления.'
			),
			'processed'    => array(
				'name'        => 'Обработан',
				'description' => 'Заказ принят и может быть оплачен. Статус введен, в основном, для удобства внутреннего ведения заказов, уже не “Новые”, но еще не оплаченные или не отправленные в доставку;',
			),
			'pay'          => array(
				'name'        => 'Оплачивается',
				'description' => 'Статус может быть назначен администратором, после отправки клиенту счета для оплаты.'
			),
			'paid'         => array(
				'name'        => 'Оплачен',
				'description' => 'Статус присваивается заказу автоматически, если расчет произведен через платежную систему Деньги Online. В случае, если товар был доставлен курьером и оплачен наличными, статус может использоваться как отчетный;'
			),
			'for-delivery' => array(
				'name'        => 'В доставку',
				'description' => 'Администратор присваивает заказам этот статус при составлении листа доставки. Лист передается курьеру вместе с товарами.'
			),
			'delivered'    => array(
				'name'        => 'Доставляется',
				'description' => 'Статус присваивается заказам, переданным курьеру. Заказ может сохранять этот статус достаточно долго, в зависимости от того как далеко находится клиент;'
			),
			'refused'      => array(
				'name'        => 'Отказан',
				'description' => 'Статус присваивается заказам, которые не могут быть удовлетворены (например, товара нет на складе). Позже вы в любой момент можете изменить статус заказа (например, если товар появился на складе);'
			),
			'canceled'     => array(
				'name'        => 'Отменен',
				'description' => 'Администратор присваивает заказу такой статус, если клиент по каким-то причинам отказался от заказа;'
			),
			'return'       => array(
				'name'        => 'Возврат',
				'description' => 'Администратор присваивает заказу такой статус, если клиент по каким-то причинам вернул товар.'
			),
		);

		return apply_filters( 'fs_order_statuses', $order_statuses );
	}

	/**
	 * Тексты копирайтов в письмах
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function get_texts( $key = '' ) {
		$texts = array(
			'mail_copywrite' => '<p>Этот интернет-магазин работает благодаря плагину <a href="https://f-shop.top/">F-Shop</a>.  <a href="https://f-shop.top/dokumentacija/">Документация</a>. <a href="https://f-shop.top/novosti/">Новости</a>.</p>'
		);

		$texts = apply_filters( 'fs_service_text', $texts );

		return ! empty( $texts[ $key ] ) ? $texts[ $key ] : $texts;
	}

	/**
	 * Возвращает проверочный код nonce
	 */
	public static function get_nonce() {
		$nonce = wp_create_nonce( self::$nonce );

		return $nonce;
	}

	/**
	 * Выводит скрытое поле с проверочным кодом nonce
	 */
	public static function nonce_field() {
		$field = '<input type="hidden" name="' . self::$nonce_field . '" value="' . self::get_nonce() . '">';

		return $field;
	}

	/**
	 * Проверяет код nonce
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
	 * Возвращает список основных валют
	 * @return array
	 */
	public static function getCurrencies() {
		return apply_filters( 'fs_currencies_filter', self::$currencies );
	}

	/**
	 * Получем валюту по умолчанию
	 * @return string
	 */
	public static function getDefaultCurrency() {
		return self::$default_currency;
	}

	/**
	 * устанавливаем валюту по умолчанию
	 *
	 * @param string $default_currency
	 */
	public static function setDefaultCurrency( string $default_currency ) {
		self::$default_currency = $default_currency;
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
	 * @param array $users
	 */
	public static function setUsers( $users = array() ) {
		self::$users = $users;
	}

	/**
	 * @return array
	 */
	public static function getUserMeta() {
		return self::$user_meta;
	}

	/**
	 * @param array $user_meta
	 */
	public static function setUserMeta( $user_meta = array() ) {
		self::$user_meta = $user_meta;
	}

	/**
	 * @return array
	 */
	public static function getFormFields() {
		return self::$form_fields;
	}

	/**
	 * @param array $form_fields
	 */
	public static function setFormFields( $form_fields = array() ) {
		self::$form_fields = $form_fields;
	}

	/**
	 * @param $key
	 *s
	 *
	 * @return mixed|void
	 */
	public function getMeta( $key ) {
		if ( ! empty( $this->meta[ $key ] ) ) {
			return $this->meta[ $key ];
		}
	}

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

}