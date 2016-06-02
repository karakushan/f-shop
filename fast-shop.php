<?php
/*
Plugin Name: Fast Shop
Plugin URI: http://profglobal.ru/fast-shop/
Description:  The plugin will help in the creation of any online store without changing your template.
Version: 1.0.1
Author: Vitaliy Karakushan
Author URI: http://profglobal.ru/
License: GPL2
Domain: fast-shop
*/
/*
Copyright 2016 Vitaliy Karakushan  (email : karakushan@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

//отсюда всё пляшет, основные настройки плагина
$GLOBALS['fs_config']=array(
	'plugin_path'=>plugin_dir_path( __FILE__ ),
	'plugin_ver'=>'1.0',
	'table_name'=>$wpdb->prefix."fs_orders",
	'plugin_meta'=>array(
		),
	);

require_once(sprintf("%s/settings.php", dirname(__FILE__)));
require_once(sprintf("%s/post-types/post_type_template.php", dirname(__FILE__)));
require_once(sprintf("%s/lib/fs-rating-class.php", dirname(__FILE__)));
require_once(sprintf("%s/lib/fs-cart_class.php", dirname(__FILE__)));
require_once(sprintf("%s/lib/fs-order-class.php", dirname(__FILE__)));
require_once(sprintf("%s/lib/fs-delivery-class.php", dirname(__FILE__)));
require_once(sprintf("%s/lib/fs-ajax-class.php", dirname(__FILE__)));
require_once(sprintf("%s/lib/fs-post-types.php", dirname(__FILE__)));
require_once(sprintf("%s/lib/fs-images-class.php", dirname(__FILE__)));
require_once(sprintf("%s/lib/fs-shortcode-class.php", dirname(__FILE__)));
require_once(sprintf("%s/taxonomy/taxonomy.php", dirname(__FILE__)));
require_once(sprintf("%s/lib/fs-action-class.php", dirname(__FILE__)));
require_once("functions.php");

if(!class_exists('WP_Fast_Shop'))
{
	class WP_Fast_Shop
	{
		public function __construct()
		{

			add_action( 'wp_enqueue_scripts',array(&$this,'fast_shop_scripts' ) );
			add_action( 'admin_enqueue_scripts',array(&$this,'fast_shop_admin_scripts' ) );

			// Инициализация классов Fast Shop
			$fs_settings=new FS_Settings_Class();
			$fs_ajax=new FS_Ajax_Class();
			$shortcode=					  new FS_Shortcode();
			$post_view =                               new FS_Rating_Class();	
			$fs_post_type=           			  new FS_Post_Type();
			$fs_post_types=           			  new FS_Post_Types();
			$GLOBALS['fs_cart'] =               new FS_Cart_Class();
			$GLOBALS['fs_orders'] =          new FS_Orders_Class();
			$GLOBALS['fs_image']=    	  new FS_Images_Class();
			$GLOBALS['fs_delivery']=         new FS_Delivery_Class();
			$GLOBALS['fs_taxonomies'] = new FS_Taxonomies_Class();
			$fs_action=new FS_Action_Class();



			$plugin = plugin_basename(__FILE__);
			add_filter("plugin_action_links_$plugin", array( $this, 'plugin_settings_link' ));
			add_action( 'plugins_loaded', array($this,'true_load_plugin_textdomain' ));
			

		} // END public function __construct

		function true_load_plugin_textdomain() {
			load_plugin_textdomain( 'fast-shop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
		}

		/**
		 * Activate the plugin
		 */
		public static function activate()

		{
			global $wpdb, $fs_config;
			$table_name=$fs_config['table_name'];
			
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
				$sql = "CREATE TABLE ".$table_name." (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
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
			global $fs_config;
			wp_enqueue_style( 'fs-style', plugins_url( 'assets/css/fast-shop.css',__FILE__ ),array(),$fs_config['plugin_ver'],'all');	
			wp_enqueue_style( 'lightslider', plugins_url( 'assets/lightslider/dist/css/lightslider.min.css',__FILE__ ),array(),$fs_config['plugin_ver'],'all');
			wp_enqueue_style( 'lightbox', plugins_url( 'assets/lightbox2/dist/css/lightbox.min.css',__FILE__ ),array(),$fs_config['plugin_ver'],'all');			
			wp_enqueue_style( 'font-awesome', plugins_url( 'assets/fontawesome/css/font-awesome.min.css',__FILE__ ),array(),$fs_config['plugin_ver'],'all');			
			
			wp_enqueue_script( 'jquery-validate',plugins_url( 'assets/js/jquery.validate.min.js',__FILE__ ), array( 'jquery' ), null, true);
			wp_enqueue_script( 'lightbox',plugins_url( 'assets/lightbox2/dist/js/lightbox.min.js',__FILE__ ), array( 'jquery' ), null, true);
			wp_enqueue_script( 'lightslider',plugins_url( 'assets/lightslider/dist/js/lightslider.min.js',__FILE__ ), array( 'jquery' ), null, true);
			wp_enqueue_script( 'fast-shop',plugins_url( 'assets/js/fast-shop.js',__FILE__ ), array( 'jquery', 'jquery-validate'), $fs_config['plugin_ver'], true);
		}

		public function fast_shop_admin_scripts()
		{
			global $fs_config;
			wp_enqueue_style( 'fs-style', plugins_url( 'assets/css/fast-shop.css',__FILE__ ),array(),$fs_config['plugin_ver'],'all');	
			wp_enqueue_script( 'fs-galery',plugins_url( 'assets/js/fs-galery.js',__FILE__ ), array( 'jquery' ), null, true);

		}

	} // END class WP_Fast_Shop
} // END if(!class_exists('WP_Fast_Shop))

if(class_exists('WP_Fast_Shop'))
{
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('WP_Fast_Shop', 'activate'));
	register_deactivation_hook(__FILE__, array('WP_Fast_Shop', 'deactivate'));

	// instantiate the plugin class
	$wp_plugin_template = new WP_Fast_Shop();

}
