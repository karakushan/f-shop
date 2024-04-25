<?php

namespace FS;

use FS\Admin\TermEdit;
/**
 * Инициализирует функции и классы плагина
 */
class FS_Init {
	public $fs_config;
	public $fs_orders;
	public $fs_option;
	public $fs_product;
	protected $init_classes = [
		FS_Config::class,
		FS_Settings::class,
		FS_Ajax::class,
		FS_Shortcode::class,
		FS_Rating_Class::class,
		FS_Post_Types::class,
		FS_Filters::class,
		FS_Cart::class,
		FS_Orders::class,
		FS_Images_Class::class,
		FS_Taxonomy::class,
        FS_Products::class,
		FS_Action::class,
		FS_Users::class,
		FS_Api_Class::class,
		FS_Payment::class,
		FS_Widget_Class::class,
		FS_Product::class,
		FS_Migrate_Class::class,
		FS_Export_Class::class,
		FS_SEO::class,
		FS_Customers::class,
		FS_Form::class,
		Admin\ProductEdit::class,
		TermEdit::class

	];
	protected static $instance = null;


	/**
	 * FS_Init constructor.
	 */
	public function __construct() {
		global $f_shop;
		$f_shop = $this;
		// Получаем опции плагина
		$this->fs_option = get_option( 'fs_option' );

		// Инициализация классов
		foreach ( $this->init_classes as $init_class ) {
			$this->init_classes[ $init_class ] = new $init_class;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts_and_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts_and_styles' ] );
		add_filter( "plugin_action_links_" . plugin_basename( FS_PLUGIN_FILE ), array(
			$this,
			'plugin_settings_link'
		) );
		add_action( 'plugins_loaded', [ $this, 'true_load_plugin_textdomain' ] );

		add_action( 'init', [ $this, 'session_init' ] );

		// Подключает свои шаблоны вместо стандартных темы
		add_filter( 'template_include', [ $this, 'custom_plugin_templates' ] );

		add_action( 'wp_footer', [ $this, 'footer_plugin_code' ] );

		// Displays js analytics codes in the site header
		add_action( 'wp_head', [ $this, 'marketing_code_header' ] );

		add_action( 'after_setup_theme', [ $this, 'crb_load' ] );
	}

	/**
	 * The single instance of the class.
	 *
	 * @return FS_Init|null
	 * @since 1.2
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

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
		load_plugin_textdomain( 'f-shop', false, dirname( plugin_basename( FS_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * На странице плагинов добавляет ссылку "настроить" напротив нашего плагина
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	function plugin_settings_link( $links ) {
		$settings_link = '<a href="edit.php?post_type=product&page=f-shop-settings">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Подключаем скрипты и стили во фронтэнде
	 */
	public static function frontend_scripts_and_styles() {
		$theme_info      = wp_get_theme();
		$text_domain     = $theme_info->display( 'TextDomain' );
		$post_type       = FS_Config::get_data( 'post_type' );
		$taxonomy        = FS_Config::get_data( 'product_taxonomy' );
		$jquery_ui_theme = 'base';

		wp_enqueue_style( FS_PLUGIN_PREFIX . 'izi-toast', FS_PLUGIN_URL . 'assets/css/iziToast.min.css', array(), FS_Config::get_data( 'plugin_ver' ), 'all' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'style', FS_PLUGIN_URL . 'assets/css/f-shop.css', array(), FS_Config::get_data( 'plugin_ver' ), 'all' );

		// Скрипты на страницах архивов и таксономий
		if ( is_archive( $post_type ) || is_tax( $taxonomy )  || is_search()) {
			wp_enqueue_style( FS_PLUGIN_PREFIX . 'jquery-ui', FS_PLUGIN_URL . 'assets/plugins/jquery-ui-themes-1.11.4/themes/' . $jquery_ui_theme . '/jquery-ui.min.css' );
			wp_enqueue_style( FS_PLUGIN_PREFIX . 'jquery-ui-theme', FS_PLUGIN_URL . 'assets/plugins/jquery-ui-themes-1.11.4/themes/' . $jquery_ui_theme . '/theme.css' );
			wp_enqueue_script( 'jquery-touch-punch' );
			wp_enqueue_script( 'jquery-ui-slider', [ 'jquery', 'jquery-ui-core', 'jquery-touch-punch' ] );
		}

		// Скрипты на странице товара
		if ( is_singular( $post_type ) ) {
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'lightslider', FS_PLUGIN_URL . 'assets/lightslider/dist/js/lightslider.min.js', array( 'jquery' ), null, true );
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'lightgallery', FS_PLUGIN_URL . "assets/plugins/lightGallery/dist/js/lightgallery-all.js", array( "jquery" ), null, true );
			wp_enqueue_style( FS_PLUGIN_PREFIX . 'lightgallery', FS_PLUGIN_URL . 'assets/plugins/lightGallery/dist/css/lightgallery.min.css' );
			wp_enqueue_style( FS_PLUGIN_PREFIX . 'lightslider', FS_PLUGIN_URL . 'assets/lightslider/dist/css/lightslider.min.css', array(), FS_Config::get_data( 'plugin_ver' ), 'all' );
		}

		// Подключаем стили для основных тем Вордпресса
		// TODO: если нет файла стилей для данной темы то необходимо создать уведомление в админке о том что можно купить или заказать адаптацию
		if ( in_array( $text_domain, [ 'twentynineteen' ] ) ) {
			if ( file_exists( get_template_directory() . DIRECTORY_SEPARATOR . FS_PLUGIN_NAME . '/assets/' . $text_domain . '/style.css' ) ) {
				wp_enqueue_style( FS_PLUGIN_PREFIX . $text_domain, get_template_directory_uri() . '/' . FS_PLUGIN_NAME . '/assets/' . $text_domain . '/style.css' );
			} elseif ( file_exists( FS_PLUGIN_PATH . 'templates/front-end/assets/' . $text_domain . '/style.css' ) ) {
				wp_enqueue_style( FS_PLUGIN_PREFIX . $text_domain, FS_PLUGIN_URL . 'templates/front-end/assets/' . $text_domain . '/style.css' );
			}
		}


		wp_enqueue_script( FS_PLUGIN_PREFIX . 'jquery-validate', FS_PLUGIN_URL . 'assets/js/jquery.validate.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'domurl', FS_PLUGIN_URL . 'assets/js/url.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'izi-toast', FS_PLUGIN_URL . 'assets/js/iziToast.min.js', array( 'jquery' ), null, true );

        wp_enqueue_script( FS_PLUGIN_PREFIX . 'main', FS_PLUGIN_URL . 'assets/js/f-shop.js', array(
			'jquery'
		), null, true );

		$l10n = array(
			'ajaxurl'         => admin_url( "admin-ajax.php" ),
			'nonce'           => wp_create_nonce( 'f-shop' ),
			'fs_currency'     => fs_currency(),
			'cartUrl'         => fs_cart_url( false ),
			'checkoutUrl'     => fs_checkout_url( false ),
			'catalogUrl'      => fs_get_catalog_link(),
			'wishlistUrl'     => fs_wishlist_url(),
			'preorderWindow'  => fs_option( 'fs_preorder_services', 0 ),
			'langs'           => array(
				'success'            => __( 'Success!', 'f-shop' ),
				'added'              => __( 'Added!', 'f-shop' ),
				'error'              => __( 'Error!', 'f-shop' ),
				'order_send_success' => __( 'Your order has been successfully created. We will contact you shortly.', 'f-shop' ),
				'limit_product'      => __( 'You have selected all available items from stock.', 'f-shop' ),
				'addToCart'          => __( 'Item &laquo;%product%&raquo; successfully added to cart.', 'f-shop' ),
				'ratingError'        => __( 'Your vote is not counted because you have already voted for this product!', 'f-shop' ),
				'addToCartButtons'   => sprintf( '<div class="fs-atc-message">%s</div>%s<div class="fs-atc-buttons"><button class="btn btn-danger fs-toast-close">%s</button> <a href="%s" class="btn btn-primary">%s</a></div>',
					__( 'Item &laquo;%product%&raquo; successfully added to cart.', 'f-shop' ),
					'<div class="fs-atc-price">%price% <span>%currency%</span></div>',
					__( 'Continue shopping', 'f-shop' ),
					fs_checkout_url( false ), __( 'Checkout', 'f-shop' ) ),
				'addToWishlist'      => __( 'Item &laquo;%product%&raquo; successfully added to wishlist. <a href="%wishlist_url%">Go to wishlist</a>', 'f-shop' ),
			),
			'catalogMinPrice' => fs_price_min(),
			'catalogMaxPrice' => fs_price_max(),
			'fs_cart_type'    => fs_option( 'fs_cart_type', 'modal' )
		);
		wp_localize_script( FS_PLUGIN_PREFIX . 'main', 'fShop', $l10n );
        wp_enqueue_script( FS_PLUGIN_PREFIX . 'frontend', FS_PLUGIN_URL . 'assets/js/fs-frontend.js', [], null, true );
        wp_localize_script( FS_PLUGIN_PREFIX . 'frontend', 'FS_DATA', $l10n );
    }


	/**
	 *  Подключаем скрипты и стили во бэкэнде
	 */
	public function admin_scripts_and_styles() {
		// необходимо для работы загрузчика изображений
		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		wp_enqueue_style( FS_PLUGIN_PREFIX . 'spectrum', FS_PLUGIN_URL . 'assets/css/spectrum.css' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'fs-tooltipster', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/css/tooltipster.main.min.css' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'fs-tooltipster-bundle', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/css/tooltipster.bundle.min.css' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'fs-tooltipster-theme', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.min.css' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'select2', FS_PLUGIN_URL . 'assets/plugins/bower_components/select2/dist/css/select2.min.css' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'fs-material-fonts', '//fonts.googleapis.com/css?family=Roboto:400,500,700,400italic|Material+Icons' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'fs-admin', FS_PLUGIN_URL . 'assets/css/fs-admin.css' );

		wp_enqueue_script( FS_PLUGIN_PREFIX . 'spectrum', FS_PLUGIN_URL . 'assets/js/spectrum.js', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'js-cookie', FS_PLUGIN_URL . 'assets/js/js.cookie.js', array( 'jquery' ), null, true );

		$screen = get_current_screen();
		if ( $screen->id == 'edit-product' ) {
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'quick-edit', FS_PLUGIN_URL . 'assets/js/quick-edit.js', array( 'jquery' ), null, true );
		} elseif ( $screen->id == 'product' ) {
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'jquery-validate', FS_PLUGIN_URL . 'assets/js/jquery.validate.min.js', array( 'jquery' ), null, true );
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'product-edit', FS_PLUGIN_URL . 'assets/js/product-edit.js', array(
				'jquery',
				FS_PLUGIN_PREFIX . 'jquery-validate'
			), null, true );

		}elseif ( $screen->id == 'fs-mail-template' ) {
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'codemirror', FS_PLUGIN_URL . 'assets/plugins/codemirror-5.61.0/lib/codemirror.js', array( 'jquery' ), null, true );
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'codemirror-xml', FS_PLUGIN_URL . 'assets/plugins/codemirror-5.61.0/mode/xml/xml.js', array( 'jquery' ), null, true );
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'codemirror-xml-fold', FS_PLUGIN_URL . 'assets/plugins/codemirror-5.61.0/addon/fold/xml-fold.js', array( 'jquery' ), null, true );
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'codemirror-matchtags', FS_PLUGIN_URL . 'assets/plugins/codemirror-5.61.0/addon/edit/matchtags.js', array(
				'jquery',
				FS_PLUGIN_PREFIX . 'codemirror-xml-fold'
			), null, true );
			wp_enqueue_style( FS_PLUGIN_PREFIX . 'codemirror', FS_PLUGIN_URL . 'assets/plugins/codemirror-5.61.0/lib/codemirror.css' );
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'codemirror-init', FS_PLUGIN_URL . 'assets/js/codemirror.js', array(
				'jquery',
				FS_PLUGIN_PREFIX . 'codemirror',
				FS_PLUGIN_PREFIX . 'codemirror-xml',
				FS_PLUGIN_PREFIX . 'codemirror-matchtags'
			), null, true );
		}
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'tooltipster', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/js/tooltipster.bundle.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'tooltipster', FS_PLUGIN_URL . 'wp-content/plugins/f-shop/assets/plugins/tooltipster-master/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-shadow.min.css', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'select2', FS_PLUGIN_URL . 'assets/plugins/bower_components/select2/dist/js/select2.min.js', array( 'jquery' ), null, true );



		wp_enqueue_script( FS_PLUGIN_PREFIX . 'admin', FS_PLUGIN_URL . 'assets/js/fs-admin.js', array(
			'jquery',
			'jquery-ui-dialog',
			FS_PLUGIN_PREFIX . 'js-cookie'
		), time(), true );

		$l10n = array(
			'allowedImagesType' => fs_allowed_images_type( 'json' ),
			'mediaNonce'        => wp_create_nonce( 'media-form' )

		);
		wp_localize_script( FS_PLUGIN_PREFIX . 'admin', 'fShop', $l10n );

		wp_enqueue_script( FS_PLUGIN_PREFIX . 'backend', FS_PLUGIN_URL . 'assets/js/fs-backend.js', array(), null, true );
		wp_localize_script( FS_PLUGIN_PREFIX . 'backend', 'FS_BACKEND', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'f-shop' ),
			'currency'=>fs_currency(),
			'lang'    => [
				'purchased_items' => __( 'Purchased items', 'f-shop' ),
				'add_product' => __( 'Add product', 'f-shop' ),
				'name' => __( 'Name', 'f-shop' ),
				'price' => __( 'Price', 'f-shop' ),
				'quantity' => __( 'Quantity', 'f-shop' ),
				'delete' => __( 'Delete', 'f-shop' ),
				'order_price' => __( 'Order price', 'f-shop' ),
				'cost_goods' => __( 'Cost of goods', 'f-shop' ),
				'packaging' => __( 'Packaging', 'f-shop' ),
				'delivery' => __( 'Delivery', 'f-shop' ),
				'discount' => __( 'Discount', 'f-shop' ),
				'search_input_label' => __( 'Product name, ID or SKU', 'f-shop' ),
				'found_products' => __( 'Found products', 'f-shop' ),
				'add' => __( 'Add', 'f-shop' ),
				'photo' => __( 'Photo', 'f-shop' ),
				'close' => __( 'Close', 'f-shop' ),
				'action' => __( 'Action', 'f-shop' ),
				'cost' => __( 'Cost', 'f-shop' ),
				'product_selection' => __( 'Product selection', 'f-shop' ),
				'product' => __( 'product', 'f-shop' ),
				'selected' => __( 'selected', 'f-shop' ),
			]
		] );
	}

	/**
	 * Заменяем стандартные шаблоны в теме на свои
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function custom_plugin_templates( $template ) {
		// Если стоит галочка не переопределять шаблоны
		if ( fs_option( 'fs_overdrive_templates', false ) ) {
			return $template;
		}
		// Переопределение шаблона на странице архива типа "product"
		if ( is_archive() && ( get_query_var( 'post_type' ) || get_query_var( 'catalog' ) ) ) {
			$template = locate_template( array( FS_PLUGIN_NAME . '/archive-product/archive.php' ) );
			if ( empty( $template ) && file_exists( FS_PLUGIN_PATH . 'templates/front-end/archive-product/archive.php' ) ) {
				$template = FS_PLUGIN_PATH . 'templates/front-end/archive-product/archive.php';
			}
		}

		return $template;
	}

	/**
	 * Footer plugin code
	 */
	function footer_plugin_code() {
		echo PHP_EOL . '<div class="fs-side-cart-wrap" data-fs-action="showCart">';
		echo '<div data-fs-element="cart-widget" data-template="cart-widget/side-cart"></div>';
		echo '</div>' . PHP_EOL;

		echo fs_option( 'fs_marketing_code_footer' );
	}

	/**
	 * Displays js analytics codes in the site header
	 */
	public function marketing_code_header() {
		echo fs_option( 'fs_marketing_code_header' );
	}

	function crb_load() {
		\Carbon_Fields\Carbon_Fields::boot();
	}

	function get( $classname ) {
		return $this->init_classes[ $classname ] ?? null;
	}
}
