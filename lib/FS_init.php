<?php 
namespace FS;
/**
* Инициализирует функции и классы плагина
*/
class FS_Init
{
	public $config;

    /**
     * FS_Init constructor.
     */
    public function __construct()
	{
       $this->config=new FS_Config();

		add_action( 'wp_enqueue_scripts',array(&$this,'fast_shop_scripts' ) );
		add_action( 'admin_enqueue_scripts',array(&$this,'fast_shop_admin_scripts' ) );

		// Инициализация классов Fast Shop
		new FS_Settings_Class();
		new FS_Ajax_Class();
		new FS_Shortcode();
		new FS_Rating_Class();	
		new FS_Post_Type();
		new FS_Post_Types();
		new FS_Filters();
		new FS_Cart_Class();
		new FS_Orders_Class();
		new FS_Images_Class();
		new FS_Delivery_Class();
		new FS_Taxonomies_Class();
		new FS_Action_Class();


		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_$plugin", array( $this, 'plugin_settings_link' ));
		add_action( 'plugins_loaded', array($this,'true_load_plugin_textdomain' ));


		} // END public function __construct

		function true_load_plugin_textdomain() {
			load_plugin_textdomain( 'fast-shop', false, $this->config->data['plugin_name'] . '/languages/' );
		}

		/**
		 * Activate the plugin
		 */
		public function activate()

		{
			global $wpdb;

			$table_name=$this->config->data['table_name'];
			
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
				$sql = "CREATE TABLE $table_name 
( `id` INT(11) NOT NULL AUTO_INCREMENT,
				`products` TEXT NOT NULL,
				`comments` TEXT NOT NULL,
				`delivery` VARCHAR(50) NOT NULL,
				`name` VARCHAR(50) NOT NULL,
				`email` VARCHAR(50) NOT NULL,
				`telephone` VARCHAR(50) NULL DEFAULT NULL,
				`summa` DOUBLE NULL DEFAULT NULL,
				`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`status` INT(11) NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				UNIQUE INDEX `id` (`id`)
				)
				COLLATE='utf8_general_ci'
				ENGINE=InnoDB
				AUTO_INCREMENT=130;";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
			}
		} // END public static function activate

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate()
		{
			// Do nothing
		} // END public static function deactivate

		// Add the settings link to the plugins page
		function plugin_settings_link($links)
		{
			$settings_link = '<a href="admin.php?page=fast-shop-settings">Настройки</a>';
			array_unshift($links, $settings_link);
			return $links;
		}


		function fast_shop_scripts() {
			wp_enqueue_style( 'fs-style', $this->config->data['plugin_url'].'assets/css/fast-shop.css',array(),$this->config->data['plugin_ver'],'all');	
			wp_enqueue_style( 'lightslider',$this->config->data['plugin_url'].'assets/lightslider/dist/css/lightslider.min.css',array(),$this->config->data['plugin_ver'],'all');
			wp_enqueue_style( 'lightbox', $this->config->data['plugin_url'].'assets/lightbox2/dist/css/lightbox.min.css',array(),$this->config->data['plugin_ver'],'all');			
			wp_enqueue_style( 'font-awesome',$this->config->data['plugin_url'].'assets/fontawesome/css/font-awesome.min.css',array(),$this->config->data['plugin_ver'],'all');

            wp_enqueue_style( 'fs-jqueryui', $this->config->data['plugin_url'].'assets/jquery-ui-1.12.0/jquery-ui.min.css',array(),$this->config->data['plugin_ver'],'all');
            wp_enqueue_script('fs-jqueryui',$this->config->data['plugin_url'].'assets/jquery-ui-1.12.0/jquery-ui.min.js',array('jquery'),null,true);
            wp_enqueue_script( 'jquery-validate',$this->config->data['plugin_url'].'assets/js/jquery.validate.min.js', array( 'jquery' ), null, true);
			wp_enqueue_script( 'lightbox',$this->config->data['plugin_url'].'assets/lightbox2/dist/js/lightbox.min.js', array( 'jquery' ), null, true);
			wp_enqueue_script( 'lightslider',$this->config->data['plugin_url'].'assets/lightslider/dist/js/lightslider.min.js', array( 'jquery' ), null, true);
			wp_enqueue_script( 'fast-shop',$this->config->data['plugin_url'].'assets/js/fast-shop.js', array( 'jquery', 'jquery-validate','fs-jqueryui'), $this->config->data['plugin_ver'], true);
			
		}

		public function fast_shop_admin_scripts()
		{
			
			
			wp_enqueue_style( 'fs-jqueryui', $this->config->data['plugin_url'].'assets/jquery-ui-1.12.0/jquery-ui.min.css',array(),$this->config->data['plugin_ver'],'all');
			wp_enqueue_style( 'fs-style', $this->config->data['plugin_url'].'assets/css/fast-shop.css',array(),$this->config->data['plugin_ver'],'all');	

			wp_enqueue_script('fs-jqueryui',$this->config->data['plugin_url'].'assets/jquery-ui-1.12.0/jquery-ui.min.js',array('jquery'),null,true);
			wp_enqueue_script( 'fs-admin',$this->config->data['plugin_url'].'assets/js/fs-admin.js', array( 'jquery' ), null, true);



		}


	}
