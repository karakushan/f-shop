<?php

namespace FS;
/**
 * Инициализирует функции и классы плагина
 */
class FS_Init {
	public $config;

	public function __construct() {
		$this->config = new FS_Config();

		add_action( 'wp_enqueue_scripts', array( &$this, 'fast_shop_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'fast_shop_admin_scripts' ) );
		add_action( 'template_redirect', array( $this, 'redirect_users' ) );

		// Инициализация классов Fast Shop
		$GLOBALS['fs_option'] = get_option( 'fs_option' );
		new FS_Settings_Class;
		new FS_Ajax_Class;
		new FS_Shortcode;
		new FS_Rating_Class;
		new FS_Post_Type;
		new FS_Post_Types;
		new FS_Filters;
		new FS_Cart_Class;
		new FS_Orders_Class;
		new FS_Images_Class;
		new FS_Taxonomies_Class;
		new FS_Action_Class;
		new FS_Users_Class;
		new FS_Api_Class();


		add_filter( "plugin_action_links_" . FS_BASENAME, array( $this, 'plugin_settings_link' ) );
		add_action( 'plugins_loaded', array( $this, 'true_load_plugin_textdomain' ) );


	} // END public function __construct

	function true_load_plugin_textdomain() {
		load_plugin_textdomain( 'fast-shop', false, $this->config->data['plugin_name'] . '/languages/' );
	}

	// Add the settings link to the plugins page
	function plugin_settings_link( $links ) {
		$settings_link = '<a href="edit.php?post_type=product&page=fast-shop-settings">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	function fast_shop_scripts() {
		wp_enqueue_style( 'fs-style', $this->config->data['plugin_url'] . 'assets/css/fast-shop.css', array(), $this->config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'lightslider', $this->config->data['plugin_url'] . 'assets/lightslider/dist/css/lightslider.min.css', array(), $this->config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'lightbox', $this->config->data['plugin_url'] . 'assets/lightbox2/dist/css/lightbox.min.css', array(), $this->config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'font-awesome', $this->config->data['plugin_url'] . 'assets/fontawesome/css/font-awesome.min.css', array(), $this->config->data['plugin_ver'], 'all' );

		wp_enqueue_style( 'fs-jqueryui', $this->config->data['plugin_url'] . 'assets/jquery-ui-1.12.0/jquery-ui.min.css', array(), $this->config->data['plugin_ver'], 'all' );

		wp_enqueue_script( "jquery-ui-core", array( 'jquery' ) );
		wp_enqueue_script( "jquery-ui-slider", array( 'jquery' ) );

		wp_enqueue_script( 'jquery-validate', $this->config->data['plugin_url'] . 'assets/js/jquery.validate.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'domurl', $this->config->data['plugin_url'] . 'assets/js/url.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'lightbox', $this->config->data['plugin_url'] . 'assets/lightbox2/dist/js/lightbox.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'lightslider', $this->config->data['plugin_url'] . 'assets/lightslider/dist/js/lightslider.min.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'fast-shop', $this->config->data['plugin_url'] . 'assets/js/fast-shop.js', array( 'jquery' ), $this->config->data['plugin_ver'], true );
		$price_max = (int) fs_price_max( false );
		$l10n      = array(
			'ajaxurl'           => admin_url( "admin-ajax.php" ),
			'fs_slider_max'     => $price_max,
			'fs_nonce'          => wp_create_nonce( 'fast-shop' ),
			'fs_currency'       => fs_currency(),
			'fs_slider_val_min' => ! empty( $_REQUEST['price_start'] ) ? (int) $_REQUEST['price_start'] : 0,
			'fs_slider_val_max' => ! empty( $_REQUEST['price_end'] ) ? (int) $_REQUEST['price_end'] : $price_max
		);
		wp_localize_script( 'fast-shop', 'FastShopData', $l10n );
	}

	public function fast_shop_admin_scripts() {


		wp_enqueue_style( 'fs-jqueryui', $this->config->data['plugin_url'] . 'assets/jquery-ui-1.12.0/jquery-ui.min.css', array(), $this->config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'font-bebas', $this->config->data['plugin_url'] . 'assets/fonts/BebasNeueBold/styles.css' );
		wp_enqueue_style( 'spectrum', $this->config->data['plugin_url'] . 'assets/css/spectrum.css' );
		wp_enqueue_style( 'font-roboto', 'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900&subset=cyrillic' );
		wp_enqueue_style( 'fs-style', $this->config->data['plugin_url'] . 'assets/css/fast-shop.css', array(), $this->config->data['plugin_ver'], 'all' );
		wp_enqueue_style( 'fs-admin', $this->config->data['plugin_url'] . 'assets/css/fs-admin.css', array(), $this->config->data['plugin_ver'], 'all' );
		wp_enqueue_script( 'fs-jqueryui', $this->config->data['plugin_url'] . 'assets/jquery-ui-1.12.0/jquery-ui.min.js', array( 'jquery' ), null, true );

		wp_enqueue_script( 'spectrum', $this->config->data['plugin_url'] . 'assets/js/spectrum.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'js-cookie', $this->config->data['plugin_url'] . 'assets/js/js.cookie.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'fs-library', $this->config->data['plugin_url'] . 'assets/js/fs-library.js', array( 'jquery' ), null, true );

		$screen = get_current_screen();
		if ( $screen->id == 'edit-product' ) {
			wp_enqueue_script( 'fs-quick-edit', $this->config->data['plugin_url'] . 'assets/js/quick-edit.js', array( 'jquery' ), null, true );
		}

		wp_enqueue_script( 'fs-admin', $this->config->data['plugin_url'] . 'assets/js/fs-admin.js', array(
			'jquery',
			'fs-library',
			'js-cookie'
		), null, true );

		// подключаем стили и скрипты текущей темы админки
		$fs_theme = fs_option( 'fs-theme', 'default' );
		wp_enqueue_style( 'fs-theme-' . $fs_theme, $this->config->data['plugin_url'] . 'assets/theme/' . $fs_theme . '/css/style.css', array(), $this->config->data['plugin_ver'], 'all' );
		wp_enqueue_script( 'fs-theme-' . $fs_theme, $this->config->data['plugin_url'] . 'assets/theme/' . $fs_theme . '/js/theme.js', array( 'jquery' ), null, true );
	}


	/**
	 *Переадресовываем неавторизованных пользователей со страницы кабинета
	 */
	function redirect_users() {
		/* if (is_page(fs_option('page_cabinet')) && !is_user_logged_in()){
			 wp_redirect(get_permalink(fs_option('page_auth')));
		 }*/
	}

	/**
	 *Функция срабатывает один раз при активации плагина
	 */
	function fs_activate() {
//		создаём таблицу заказов
		global $wpdb;
		$table_name = $this->config->data['table_orders'];
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			$sql = "CREATE TABLE $table_name
            ( 
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL,
	`first_name` VARCHAR(50) NOT NULL,
	`last_name` VARCHAR(50) NOT NULL,
	`summa` FLOAT NOT NULL DEFAULT '0',
	`status` INT(11) NOT NULL DEFAULT '0',
	`products` TEXT NOT NULL,
	`payment` INT(11) NOT NULL,
	`delivery` INT(11) NOT NULL,
	`address` VARCHAR(255) NOT NULL,
	`city` VARCHAR(255) NOT NULL,
	`email` VARCHAR(50) NOT NULL,
	`comments` TEXT NOT NULL,
	`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`phone` VARCHAR(50) NOT NULL,
	`delivery_number` VARCHAR(50) NOT NULL,
	`formdata` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `id` (`id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            ";

			dbDelta( $sql );
		}
		add_role( 'client', __( 'Client', 'fast-shop' ), array( 'read' => true, 'level_0' => true ) );

	}

	function fs_deactivate() {

	}
}
