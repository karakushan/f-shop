<?php

namespace FS;
/**
 * Инициализирует функции и классы плагина
 */
class FS_Init {
	public $fs_config;
	public $fs_payment;
	public $fs_api;
	public $fs_users;
	public $fs_action;
	public $fs_taxonomies;
	public $fs_images;
	public $fs_orders;
	public $fs_cart;
	public $fs_filters;
	public $fs_post_types;
	public $fs_post_type;
	public $fs_rating;
	public $fs_shortcode;
	public $fs_ajax;
	public $fs_settings;
	public $fs_option;
	public $fs_widget;
	public $fs_product;


	/**
	 * FS_Init constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( &$this, 'fast_shop_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'fast_shop_admin_scripts' ) );

		// Инициализация классов Fast Shop
		$this->fs_option     = get_option( 'fs_option' );
		$this->fs_config     = new FS_Config();
		$this->fs_settings   = new FS_Settings_Class;
		$this->fs_ajax       = new FS_Ajax_Class;
		$this->fs_shortcode  = new FS_Shortcode;
		$this->fs_rating     = new FS_Rating_Class;
		$this->fs_post_type  = new FS_Post_Type;
		$this->fs_post_types = new FS_Post_Types;
		$this->fs_filters    = new FS_Filters;
		$this->fs_cart       = new FS_Cart_Class;
		$this->fs_orders     = new FS_Orders_Class;
		$this->fs_images     = new FS_Images_Class;
		$this->fs_taxonomies = new FS_Taxonomies_Class;
		$this->fs_action     = new FS_Action_Class;
		$this->fs_users      = new FS_Users_Class;
		$this->fs_api        = new FS_Api_Class();
		$this->fs_payment    = new FS_Payment_Class();
		$this->fs_widget     = new FS_Widget_CLass();
		$this->fs_product    = new FS_Product_Class();

		add_filter( "plugin_action_links_" . FS_BASENAME, array( $this, 'plugin_settings_link' ) );
		add_action( 'plugins_loaded', array( $this, 'true_load_plugin_textdomain' ) );

		add_action( 'init', array( $this, 'session_init' ) );
	} // END public function __construct


	/**
	 * инициализируем сессии
	 */
	function session_init() {
		if ( session_id() == '' ) {
			session_start();
		}
	}

	/**
	 * Устанавливаем путь к файлам локализации
	 */
	function true_load_plugin_textdomain() {
		load_plugin_textdomain( 'fast-shop', false, FS_LANG_PATH );
	}


	/**
	 * На странице плагинов добавляет ссылку "настроить" напротив нашего плагина
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	function plugin_settings_link( $links ) {
		$settings_link = '<a href="edit.php?post_type=product&page=fast-shop-settings">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Подключаем скрипты и стили во фронтэнде
	 */
	function fast_shop_scripts() {
		wp_enqueue_style( 'lightslider', FS_PLUGIN_URL . 'assets/lightslider/dist/css/lightslider.min.css', array(), $this->fs_config->data['plugin_ver'], 'all' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'font_awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css', array(), $this->fs_config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'izi-toast', FS_PLUGIN_URL . 'assets/css/iziToast.min.css', array(), $this->fs_config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'fs-style', FS_PLUGIN_URL . 'assets/css/f-shop.css', array(), $this->fs_config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'es-lightgallery', FS_PLUGIN_URL . 'assets/plugins/lightGallery/dist/css/lightgallery.min.css' );
		wp_enqueue_style( FS_PLUGIN_PREFIX.'jquery-ui', FS_PLUGIN_URL . 'assets/css/jquery-ui.min.css' );

		wp_enqueue_script( 'es-lightgallery', FS_PLUGIN_URL . "assets/plugins/lightGallery/dist/js/lightgallery-all.js", array( "jquery" ), null, true );
		wp_enqueue_script( "jquery-ui-core", array( 'jquery' ) );
		wp_enqueue_script( "jquery-ui-slider", array( 'jquery' ) );
		wp_enqueue_script( 'jquery-validate', FS_PLUGIN_URL . 'assets/js/jquery.validate.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'domurl', FS_PLUGIN_URL . 'assets/js/url.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'izi-toast', FS_PLUGIN_URL . 'assets/js/iziToast.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'lightslider', FS_PLUGIN_URL . 'assets/lightslider/dist/js/lightslider.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'f-shop-dev', FS_PLUGIN_URL . 'assets/js/fshop-dev.js', array( 'jquery' ), $this->fs_config->data['plugin_ver'], true );
		wp_enqueue_script( 'f-shop', FS_PLUGIN_URL . 'assets/js/f-shop.js', array( 'jquery' ), $this->fs_config->data['plugin_ver'], true );

		$price_max = (int) fs_price_max( false );
		$l10n      = array(
			'ajaxurl'           => admin_url( "admin-ajax.php" ),
			'fs_slider_max'     => $price_max,
			'fs_nonce'          => wp_create_nonce( 'fast-shop' ),
			'fs_currency'       => fs_currency(),
			'fs_slider_val_min' => ! empty( $_REQUEST['price_start'] ) ? (int) $_REQUEST['price_start'] : 0,
			'fs_slider_val_max' => ! empty( $_REQUEST['price_end'] ) ? (int) $_REQUEST['price_end'] : $price_max
		);
		wp_localize_script( 'f-shop', 'FastShopData', $l10n );
	}


	/**
	 *  Подключаем скрипты и стили во бэкэнде
	 */
	public function fast_shop_admin_scripts() {
		// необходимо для работы загрузчика изображений
		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		wp_enqueue_style( 'spectrum', FS_PLUGIN_URL . 'assets/css/spectrum.css' );
		wp_enqueue_style( 'fs-tooltipster', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/css/tooltipster.bundle.min.css' );
		wp_enqueue_style( 'fs-admin', FS_PLUGIN_URL . 'assets/css/fs-admin.css' );

		wp_enqueue_script( 'spectrum', FS_PLUGIN_URL . 'assets/js/spectrum.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'js-cookie', FS_PLUGIN_URL . 'assets/js/js.cookie.js', array( 'jquery' ), null, true );
		$screen = get_current_screen();
		if ( $screen->id == 'edit-product' ) {
			wp_enqueue_script( 'fs-quick-edit', FS_PLUGIN_URL . 'assets/js/quick-edit.js', array( 'jquery' ), null, true );
		}

		wp_enqueue_script( 'fs-tooltipster', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/js/tooltipster.bundle.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'fs-library', FS_PLUGIN_URL . 'assets/js/fs-library.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'fs-admin', FS_PLUGIN_URL . 'assets/js/f-shop-admin.js', array(
			'jquery',
			'js-cookie',
			'fs-library'
		), null, true );

		$l10n = array(
			'allowedImagesType' => fs_allowed_images_type( 'json' ),
			'mediaNonce'        => wp_create_nonce( 'media-form' )

		);
		wp_localize_script( 'fs-admin', 'fShop', $l10n );
	}

}
