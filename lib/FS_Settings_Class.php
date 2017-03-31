<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Класс выводит страницу настроек в админке
 */
class FS_Settings_Class
{
    protected $config;

    public function __construct()
    {
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('admin_menu', array(&$this, 'add_menu'));
        $this->config=new FS_Config();
    }

    public function admin_init()
    {
        if (isset($_POST['fs_save_options'])){
            if( !wp_verify_nonce( $_GET['_wpnonce'], 'fs_nonce' ) ) return;
            $options=$_POST['fs_option'];
            if ($options){
                $upd=update_option('fs_option',$options);
                if ($upd){
                    add_action('admin_notices', function(){
                        echo '<div class="updated is-dismissible"><p>Настройки обновлены</p></div>';
                    });
                }else{
                    add_action('admin_notices', function(){
                        echo '<div class="notice notice-warning is-dismissible"><p>Страница перезагружена, но настройки не обновлялись.</p></div>';
                    });
                }

            }

        }


    }
    public function settings_section_wp_plugin_template()
    {
        echo 'Определите настройки вашего магазина.';
    }

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
                __('Orders','fast-shop'),
                __('Orders','fast-shop'),
                'manage_options',
                'fast-shop-orders',
                array(&$this, 'fast_shop_orders')
                );

        // Add a page to manage this plugin's settings
             add_submenu_page(
                'edit.php?post_type=product',
                __('Store settings','fast-shop'),
                __('Store settings','fast-shop'),
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

        $config=new FS_Config();
        // шаблон страницы настроек магазина
        include($this->config->data['plugin_path'].'/templates/back-end/settings.php');
    }

    public function fast_shop_admin_menu()
    {
        $page=$_GET['page'];
        $template=$this->config->data['plugin_path'].'templates/back-end/'.$page.'.php';
        switch ($page) {
            case 'fs-atributes':
            require_once $template;
            break;

            default:
                # code...
            break;
        }
    }
    /**
     *
     */
    public function fast_shop_orders()
    {
        global $wpdb;
        $config=new FS_Config();
        $orders=new FS_Orders_Class();
        $action=!empty($_GET['action'])? $_GET['action']:'' ;
        $table=$config->data['table_orders'];
        $orders_count = $wpdb->get_var("SELECT COUNT(*) FROM  $table");
        switch ($action) {
            case 'info':
            $order_id=(int)$_GET['id'];
            $order=$orders->get_order($order_id);
            if (!$order) {
                exit('невозможно получить данные заказа');
            }
            include ($this->config->data['plugin_path'].'templates/back-end/order-info.php');
            break;

            default:
            include ($this->config->data['plugin_path'].'templates/back-end/orders.php');
            break;
        }


    }
}