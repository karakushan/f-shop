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

	public $taxonomies;
	public static $currencies = array();
	protected static $users = array();
	public static $default_currency = 'USD';
	public static $user_meta = array();
	public static $prices;
	public static $form_fields;
	protected static $nonce = 'fast-shop';
	protected static $text_domain = 'fast-shop';


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
			'preloader'            => FS_PLUGIN_URL . '/assets/img/ajax-loader.gif'
		);
		$this->data = apply_filters( 'fs_data', $data );


		//Табы отображаемые в метабоксе в редактировании товара
		$this->tabs = array(
			 array(
				'title'    => __( 'Prices', 'fast-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'prices'
			),
			array(
				'title'    => __( 'Gallery', 'fast-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'gallery'
			),
			array(
				'title'    => __( 'Attributes', 'fast-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'attributes'
			),
			array(
				'title'    => __( 'Other', 'fast-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'other'
			),
			array(
				'title'    => __( 'Associated', 'fast-shop' ),
				'on'       => false,
				'body'     => '',
				'template' => 'related'
			),
			array(
				'title'    => __( 'Варианты', 'fast-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'variants'
			),
		);

		//Массив настроек сайта
		$this->options = get_option( 'fs_option', array() );


		//Массив настроек мета полей продукта (записи). При изменении настройки все настройки меняются глобально.
		$meta = array(
			//базовая цена
			'price'            => 'fs_price',
			//акционная цена, перебивает цену
			'action_price'     => 'fs_action_price',
			// валюта товара
			'currency'         => 'fs_currency',
			//артикул
			'product_article'  => 'fs_articul',
			//запас товаров на складе
			'remaining_amount' => 'fs_remaining_amount',
			//галерея
			'gallery'          => 'fs_galery',
			// похожие товары выбранные вручную
			'related_products' => 'fs_related_products',
			// поле производителя
			'vendor' => 'fs_vendor',
			// вариации товара
			'variants'=>'fs_variant',
			// цены вариации товара
			'variants_price'=>'fs_variant_price',
			// начальное количество
			'variant_count'=>'fs_variant_count',
			// максимальное количество
			'variant_count_max'=>'fs_variant_count_max',
			// включает вариативность товара
			'variated_on'=>'fs_variated_on'
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
			'fs_email'            => array( 'type' => 'email', 'label' => 'Ваш email', 'required' => true ),
			'fs_first_name'       => array( 'type' => 'text', 'label' => 'Ваше имя', 'required' => true ),
			'fs_last_name'        => array( 'type' => 'text', 'label' => 'Ваша фамилия', 'required' => true ),
			'fs_phone'            => array(
				'type'      => 'tel',
				'label'     => 'Телефон',
				'required'  => true,
				'save_meta' => 1
			),
			'fs_city'             => array( 'type'      => 'text',
			                                'label'     => 'Город',
			                                'required'  => true,
			                                'save_meta' => 1
			),
			'fs_adress'           => array(
				'type'      => 'text',
				'label'     => 'Адрес доставки',
				'required'  => false,
				'save_meta' => 1
			),
			'fs_home_num'         => array(
				'type'      => 'text',
				'label'     => 'Номер дома',
				'required'  => false,
				'save_meta' => 1
			),
			'fs_apartment_num'    => array(
				'type'      => 'text',
				'label'     => 'Номер квартиры',
				'required'  => false,
				'save_meta' => 1
			),
			'fs_delivery_number'  => array(
				'type'      => 'text',
				'label'     => 'Номер отделения',
				'required'  => false,
				'save_meta' => 1
			),
			'fs_delivery_methods' => array( 'type'      => 'radio',
			                                'label'     => 'Способ доставки',
			                                'required'  => true,
			                                'save_meta' => 1
			),
			'fs_payment_methods'  => array( 'type' => 'radio', 'label' => 'Способ оплаты', 'save_meta' => 1 ),
			'fs_comment'          => array( 'type' => 'text', 'label' => 'Комментарий', 'required' => false ),
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
			'ready'        => array(
				'name'        => 'Готов',
				'description' => 'Cтатус присваивается заказу, если товар доставлен, оплачен, и его можно отправить в архив. Заказы с этим статусом нужны вам только для внутреннего учета.'
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


}