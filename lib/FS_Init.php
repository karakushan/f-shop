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
	public $fs_migrate;


	/**
	 * FS_Init constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'fs_frontend_scripts_and_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'fs_admin_scripts_and_styles' ) );
		add_action( 'wp_head', array( $this, 'head_microdata' ) );

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
		$this->fs_migrate    = new FS_Migrate_Class();

		add_filter( "plugin_action_links_" . FS_BASENAME, array( $this, 'plugin_settings_link' ) );
		add_action( 'plugins_loaded', array( $this, 'true_load_plugin_textdomain' ) );

		add_action( 'init', array( $this, 'session_init' ) );

		// Подключает свои шаблоны вместо стандартных темы
		add_filter( 'template_include', array( $this, 'custom_plugin_templates' ) );
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
		load_plugin_textdomain( 'f-shop', false, FS_LANG_PATH );
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
	function fs_frontend_scripts_and_styles() {
		$theme_info = wp_get_theme();
		$textdomain = $theme_info->display( 'TextDomain' );

		wp_enqueue_style( FS_PLUGIN_PREFIX . 'lightslider', FS_PLUGIN_URL . 'assets/lightslider/dist/css/lightslider.min.css', array(), $this->fs_config->data['plugin_ver'], 'all' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'font_awesome', 'https://use.fontawesome.com/releases/v5.1.0/css/all.css', array(), $this->fs_config->data['plugin_ver'], 'all' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'izi-toast', FS_PLUGIN_URL . 'assets/css/iziToast.min.css', array(), $this->fs_config->data['plugin_ver'], 'all' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'style', FS_PLUGIN_URL . 'assets/css/f-shop.css', array(), $this->fs_config->data['plugin_ver'], 'all' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'lightgallery', FS_PLUGIN_URL . 'assets/plugins/lightGallery/dist/css/lightgallery.min.css' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'jquery-ui', FS_PLUGIN_URL . 'assets/css/jquery-ui.min.css' );

		// Подключаем стили для основных тем Вордпресса
		// TODO: если нет файла стилей для данной темы то необходимо создать уведомление в админке о том что можно купить или заказать адаптацию
		if ( in_array( $textdomain, [ 'twentynineteen' ] ) ) {
			if ( file_exists( get_template_directory() . DIRECTORY_SEPARATOR . FS_PLUGIN_NAME . '/assets/' . $textdomain . '/style.css' ) ) {
				wp_enqueue_style( FS_PLUGIN_PREFIX . $textdomain, get_template_directory_uri() . DIRECTORY_SEPARATOR . FS_PLUGIN_NAME . '/assets/' . $textdomain . '/style.css' );
			} elseif ( file_exists( FS_PLUGIN_PATH . 'templates/front-end/assets/' . $textdomain . '/style.css' ) ) {
				wp_enqueue_style( FS_PLUGIN_PREFIX . $textdomain, FS_PLUGIN_URL . 'templates/front-end/assets/' . $textdomain . '/style.css' );
			}
		}

		wp_enqueue_script( FS_PLUGIN_PREFIX . 'lightgallery', FS_PLUGIN_URL . "assets/plugins/lightGallery/dist/js/lightgallery-all.js", array( "jquery" ), null, true );
		wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );
		wp_enqueue_script( 'jquery-ui-slider', array( 'jquery' ) );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'jqueryui-touch-punch', '//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js', array(
			'jquery',
			'jquery-ui-core'
		), false, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'jquery-validate', FS_PLUGIN_URL . 'assets/js/jquery.validate.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'domurl', FS_PLUGIN_URL . 'assets/js/url.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'izi-toast', FS_PLUGIN_URL . 'assets/js/iziToast.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'lightslider', FS_PLUGIN_URL . 'assets/lightslider/dist/js/lightslider.min.js', array( 'jquery' ), null, true );

		wp_enqueue_script( 'f-shop-library', FS_PLUGIN_URL . 'assets/js/fs-library.js', array( 'jquery' ), $this->fs_config->data['plugin_ver'], true );
		wp_enqueue_script( 'f-shop', FS_PLUGIN_URL . 'assets/js/f-shop.js', array(
			'jquery',
			'f-shop-library'
		), $this->fs_config->data['plugin_ver'], true );

		// Здесь подгружается обработчик событий плагина, вы можете отключить его в опциях
		if ( ! fs_option( 'fs_disable_messages', 0 ) ) {
			wp_enqueue_script( 'fs-events', FS_PLUGIN_URL . 'assets/js/fs-events.js', array(
				'jquery',
				'f-shop-library',
				'f-shop'
			), $this->fs_config->data['plugin_ver'], true );
		}

		$price_max = fs_price_max( false );
		$l10n      = array(
			'ajaxurl'           => admin_url( "admin-ajax.php" ),
			'fs_slider_max'     => intval( $price_max ),
			'fs_nonce'          => wp_create_nonce( 'f-shop' ),
			'fs_currency'       => fs_currency(),
			'cartUrl'           => fs_cart_url( false ),
			'checkoutUrl'       => fs_checkout_url( false ),
			'catalogUrl'        => fs_get_catalog_link(),
			'wishlistUrl'       => fs_wishlist_url(),
			'preorderWindow'    => fs_option( 'fs_preorder_services', 0 ),
			'lang'              => array(
				'success'            => __( 'Success!', 'f-shop' ),
				'error'              => __( 'Error!', 'f-shop' ),
				'order_send_success' => __( 'Your order has been successfully created. We will contact you shortly.', 'f-shop' ),
				'limit_product'      => __( 'You have selected all available items from stock.', 'f-shop' ),
				'addToCart'          => __( 'Item &laquo;%product%&raquo; successfully added to cart.', 'f-shop' ),
				'addToCartButtons'   => sprintf( '<div class="fs-atc-message">%s</div>%s<div class="fs-atc-buttons"><a href="%s" class="btn btn-danger">%s</a> <a href="%s" class="btn btn-primary">%s</a></div>',
					__( 'Item &laquo;%product%&raquo; successfully added to cart.', 'f-shop' ),
					'<div class="fs-atc-price">%price% <span>%currency%</span></div>',
					fs_get_catalog_link(),
					__( 'To catalog', 'f-shop' ),
					fs_checkout_url( false ), __( 'Checkout', 'f-shop' ) ),
				'addToWishlist'      => __( 'Item &laquo;%product%&raquo; successfully added to wishlist. <a href="%wishlist_url%">Go to wishlist</a>', 'f-shop' ),
			),
			'fs_slider_val_min' => ! empty( $_REQUEST['price_start'] ) ? (int) $_REQUEST['price_start'] : 0,
			'fs_slider_val_max' => ! empty( $_REQUEST['price_end'] ) ? (int) $_REQUEST['price_end'] : intval( $price_max )
		);
		wp_localize_script( 'f-shop', 'FastShopData', $l10n );
	}


	/**
	 *  Подключаем скрипты и стили во бэкэнде
	 */
	public function fs_admin_scripts_and_styles() {
		// необходимо для работы загрузчика изображений
		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		wp_enqueue_style( FS_PLUGIN_PREFIX . 'spectrum', FS_PLUGIN_URL . 'assets/css/spectrum.css' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'fs-tooltipster', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/css/tooltipster.main.min.css' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'fs-tooltipster-bundle', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/css/tooltipster.bundle.min.css' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'fs-tooltipster-theme', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.min.css' );
		wp_enqueue_style( FS_PLUGIN_PREFIX . 'fs-admin', FS_PLUGIN_URL . 'assets/css/fs-admin.css' );

		wp_enqueue_script( FS_PLUGIN_PREFIX . 'spectrum', FS_PLUGIN_URL . 'assets/js/spectrum.js', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'js-cookie', FS_PLUGIN_URL . 'assets/js/js.cookie.js', array( 'jquery' ), null, true );
		$screen = get_current_screen();
		if ( $screen->id == 'edit-product' ) {
			wp_enqueue_script( FS_PLUGIN_PREFIX . 'quick-edit', FS_PLUGIN_URL . 'assets/js/quick-edit.js', array( 'jquery' ), null, true );
		}

		wp_enqueue_script( FS_PLUGIN_PREFIX . 'tooltipster', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/js/tooltipster.bundle.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'tooltipster', FS_PLUGIN_URL . 'wp-content/plugins/f-shop/assets/plugins/tooltipster-master/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-shadow.min.css', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'library', FS_PLUGIN_URL . 'assets/js/fs-library.js', array( 'jquery' ), null, true );
		wp_enqueue_script( FS_PLUGIN_PREFIX . 'admin', FS_PLUGIN_URL . 'assets/js/fs-admin.js', array(
			'jquery',
			FS_PLUGIN_PREFIX . 'js-cookie',
			FS_PLUGIN_PREFIX . 'library'
		), null, true );

		$l10n = array(
			'allowedImagesType' => fs_allowed_images_type( 'json' ),
			'mediaNonce'        => wp_create_nonce( 'media-form' )

		);
		wp_localize_script( FS_PLUGIN_PREFIX . 'admin', 'fShop', $l10n );

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

	function head_microdata() {
		global $fs_config;
		if ( is_singular( $fs_config->data['post_type'] ) ) {
			$categories   = get_the_terms( get_the_ID(), 'catalog' );
			$manufacturer = get_the_terms( get_the_ID(), 'brands' );

			$total_vote  = get_post_meta( get_the_ID(), 'fs_product_rating', 0 );
			$sum_votes   = array_sum( $total_vote );
			$count_votes = count( $total_vote );
			$rate        = $count_votes ? round( $sum_votes / $count_votes, 2 ) : 0;
			if ( $rate > 0 ) {
				$aggregateRating = [
					"@type"       => "AggregateRating",
					"ratingCount" => $count_votes,
					"ratingValue" => $rate
				];
			} else {
				$aggregateRating = [];
			}

			$schema = array(
				"@context"        => "https://schema.org",
				"@type"           => "Product",
				"url"             => get_the_permalink(),
				"aggregateRating" => $aggregateRating,
				"category"        => $categories ? $categories[0]->name : '',
				"image"           => esc_url( fs_get_product_thumbnail_url( 0, 'full' ) ),
				"brand"           => $manufacturer ? $manufacturer[0]->name : get_bloginfo( 'name' ),
				"manufacturer"    => $manufacturer ? $manufacturer[0]->name : get_bloginfo( 'name' ),
				"model"           => get_the_title(),
				"sku"             => fs_get_product_code(),
				"productID"       => get_the_ID(),
				"description"     => strip_tags( get_the_excerpt() ),
				"name"            => get_the_title(),
				"offers"          => [
					"@type"         => "Offer",
					"availability"  => fs_aviable_product() ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
					"price"         => fs_get_price(),
					"priceCurrency" => fs_option( 'fs_currency_code', 'UAH' ),
					"url"           => get_the_permalink()
				]
			);

			echo ' <script type="application/ld+json">';
			echo json_encode( $schema );
			echo ' </script>';
		}
	}

}
