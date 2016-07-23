<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Класс выводит страницу настроек в админке
 */
class FS_Settings_Class
{
  protected $conf;

		public function __construct()
		{
			// register actions
      add_action('admin_init', array(&$this, 'admin_init'));
      add_action('admin_menu', array(&$this, 'add_menu'));

      global $fs_config;
      $this->conf=$fs_config;
		} // END public function __construct
		
        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
        	// register your plugin's settings
        	register_setting('wp_plugin_template-group', 'currency_icon');
          register_setting('wp_plugin_template-group', 'cart_url');
          register_setting('wp_plugin_template-group', 'pay_url');
          register_setting('wp_plugin_template-group', 'fs_success');

        	// add your settings section
          add_settings_section(
           'wp_plugin_template-section', 
           'Общие настройки', 
           array(&$this, 'settings_section_wp_plugin_template'), 
           'wp_plugin_template'
           );

        	// add your setting's fields
          add_settings_field(
            'currency_icon', 
            'Символ валюты', 
            array(&$this, 'settings_field_input_text'), 
            'wp_plugin_template', 
            'wp_plugin_template-section',
            array(
              'field' => 'currency_icon'
              )
            );
          add_settings_field(
            'cart_url', 
            'Ссылка на страницу корзины', 
            array(&$this, 'settings_field_select'), 
            'wp_plugin_template', 
            'wp_plugin_template-section',
            array(
              'field' => 'cart_url'
              )
            );         
          add_settings_field(
            'pay_url', 
            'Ссылка на страницу оплаты/оформления заказа', 
            array(&$this, 'settings_field_select'), 
            'wp_plugin_template', 
            'wp_plugin_template-section',
            array(
              'field' => 'pay_url'
              )
            );         
          add_settings_field(
            'fs_success', 
            'Ссылка на страницу после отправки заказа (если пусто, страница перзагрузится)', 
            array(&$this, 'settings_field_input_text'), 
            'wp_plugin_template', 
            'wp_plugin_template-section',
            array(
              'field' => 'fs_success'
              )
            );
            // Possibly do additional admin_init tasks
        } // END public static function activate
        
        public function settings_section_wp_plugin_template()
        {
            // Think of this as help text for the section.
          echo 'Определите настройки вашего магазина.';
        }
        
        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // Get the field name from the $args array
          $field = $args['field'];
            // Get the value of this setting
          $value = get_option($field);
            // echo a proper input type="text"
          echo sprintf('<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value);
        }
        public function settings_field_select($args)
        {
            // Get the field name from the $args array
          $field = $args['field'];
            // Get the value of this setting
          $value = get_option($field);
            // echo a proper input type="text"
          $list_option="";
          $posts=query_posts(array('post_type'=>'page'));
          if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
          
          <?php $list_option.="<option value=\"".get_the_ID()."\" ".selected(get_the_ID(),  $value, false ).">".get_the_title()."</option>"; ?>
        <?php endwhile;wp_reset_query(); ?>
      <?php endif; ?>
      
      <?php echo sprintf('<select name="%s" id="%s" />'. $list_option.'</select>', $field, $field, $value);
        } // END public function settings_field_input_text($args)
        
        /**
         * add a menu
         */		
        public function add_menu()
        {
       /*   add_menu_page( 
            'Магазин', 'Магазин', 'manage_options', 'fast-shop','' , 'dashicons-products', 9 
            );*/
            // Add a page to manage this plugin's settings
            add_submenu_page(
             'edit.php?post_type=product', 
             'Заказы', 
             'Заказы', 
             'manage_options', 
             'fast-shop-orders', 
             array(&$this, 'fast_shop_orders')
             );              


               // Add a page to manage this plugin's settings
            add_submenu_page(
             'edit.php?post_type=product', 
             'Способы доставки', 
             'Способы доставки', 
             'manage_options', 
             'fs-delivery', 
             array(&$this, 'fast_shop_delivery')
             );    

             // Add a page to manage this plugin's settings
            add_submenu_page(
             'edit.php?post_type=product', 
             'Атрибуты товаров', 
             'Атрибуты товаров', 
             'manage_options', 
             'fs-atributes', 
             array(&$this, 'fast_shop_admin_menu')
             );                   

                   // Add a page to manage this plugin's settings
            add_submenu_page(
             'edit.php?post_type=product', 
             'Настройки магазина', 
             'Настройки магазина', 
             'manage_options', 
             'fast-shop-settings', 
             array(&$this, 'plugin_settings_page')
             );     
        } // END public function add_menu()

        /**
         * Подключение шаблонов подменю
         */		
        public function plugin_settings_page()
        {
          global $fs_config;
          // шаблон страницы настроек магазина
          include($fs_config['plugin_path'].'/templates/back-end/settings.php');
        } 

        public function fast_shop_admin_menu()
        {
          $page=$_GET['page'];
          $template=$this->conf['plugin_path'].'templates/back-end/'.$page.'.php';
          switch ($page) {
            case 'fs-atributes':
            require_once $template;
            break;
            
            default:
              # code...
            break;
          }
        }

      //Визуальное отображение контента на странице настройки доставки
        public function fast_shop_delivery()
        {
          global $fs_config;
          $delivery=new FS_Delivery_Class();
          $delivery->add_delivery();
          $fs_delivery=$delivery->delivery;

          // шаблон  страницы настроек доставки
          include($this->conf['plugin_path'].'/templates/back-end/delivery.php');
        }

        public function fast_shop_orders()
        {

          $orders=new FS_Orders_Class();
          $delivery=new FS_Delivery_Class();
          $action=esc_sql($_GET['action']);
          switch ($action) {
            case 'info':
            $order_info=$orders->get_order(esc_sql($_GET['id'] ));
            $products=unserialize($order_info->products);
            include $this->conf['plugin_path'].'templates/back-end/order-info.php';
            break;
            
            default:
            include $this->conf['plugin_path'].'templates/back-end/orders.php';
            break;
          }


        }
      } 