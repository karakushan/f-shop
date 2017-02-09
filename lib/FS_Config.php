<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 25.08.2016
 * Time: 20:19
 */

class FS_Config
{
    public $data;
    public $meta;
    public $options;
    public $tabs;
    public $taxonomies;
    public static $prices;

    /**
     * FS_Config constructor.
     */
    function __construct()
    {
        global $wpdb;

        //Массив общих настроек плагина. При изменении настройки все настройки меняются глобально.
        $this->data=array(
            'plugin_path'=>FS_PLUGIN_PATH,
            'plugin_url'=>FS_PLUGIN_URL,
            'plugin_ver'=>'1.0',
            'plugin_name'=>'fast-shop',
            'plugin_user_template'=>get_template_directory().'/fast-shop/',
            'plugin_template'=>FS_PLUGIN_PATH.'templates/front-end/',
            'plugin_settings'=>'fast-shop-settings',
            'table_orders'=>$wpdb->prefix."fs_orders",
            'table_name'=>$wpdb->prefix."fs_orders",
            'table_order_item'=>$wpdb->prefix."fs_order_info",
            'order_statuses'=>array(
                '0'=>'ожидает подтверждения',
                '1'=>'в ожидании оплаты',
                '2'=>'оплачен',
                '3'=>'отменён'
                )
            );

        // Список зарегистрирвоанных служебных таксономий
        $taxonomies=array(
            'catalog'=>array(
                'label'=>__( 'Product categories', 'fast-shop' ),
                'metabox'=>null,
                'show_admin_column'=>true
                )
            ,
            'fs-payment-methods'=>array(
                'label'=>__( 'Payment methods', 'fast-shop' ),
                'metabox'=>false,
                'show_admin_column'=>false
                ),
            'fs-delivery-methods'=>array(
                'label'=>__( 'Delivery methods', 'fast-shop' ),
                'metabox'=>false,
                'show_admin_column'=>false
                ),
            'product-attributes'=>array(
                'label'=>__( 'Product attributes', 'fast-shop' ),
                'metabox'=>null,
                'show_admin_column'=>false
                ),
            'manufacturers'=>array(
                'label'=>__( 'Manufacturers', 'fast-shop' ),
                'metabox'=>null,
                'show_admin_column'=>false
                )
            );
        $this->taxonomies=apply_filters('fs_taxonomies',$taxonomies);



        //Табы отображаемые в метабоксе в редактировании товара
        $this->tabs=array(
            '0'=>array(
                'title'=>__('Prices','fast-shop'),
                'on'=>true,
                'body'=>'',
                'template'=>''
                ),
            '2'=> array(
                'title'=>__('Gallery','fast-shop'),
                'on'=>true,
                'body'=>'',
                'template'=>''
                ),
            '3'=>array(
                'title'=>__('Other','fast-shop'),
                'on'=>true,
                'body'=>'',
                'template'=>''
                ),
            '4'=>array(
                'title'=>__('Associated','fast-shop'),
                'on'=>false,
                'body'=>'',
                'template'=>''
                )
            );

        //Массив настроек сайта
        $this->options=get_option('fs_option',array());


        //Массив настроек мета полей продукта (записи). При изменении настройки все настройки меняются глобально.
        $meta=array(
            'price'=>'fs_price',//базовая цена
            'action_price'=>'fs_action_price',//акционная цена, перебивает цену 
            'wholesale_price'=>'fs_wholesale_price', //цена для оптовиков
            'wholesale_price_action'=>'fs_wholesale_price_act', //цена для оптовиков акционная
            'discount'=>'fs_discount',//размер скидки
            'product_article'=>'fs_articul',//артикул
            'availability'=>'fs_availability',//наличие на складе
            'remaining_amount'=>'fs_remaining_amount',//запас товаров на складе
            'action'=>'fs_actions',//включить  или выключить акцию
            'action_page'=>'fs_page_action',//ссылка на страницу описывающую акцию на товарпоставленнуюю полем 'discount'
            'displayed_price'=>'fs_displayed_price',//тображаемая цена
            'attributes'=>'fs_attributes_post',//атрибуты товара
            'gallery'=>'fs_galery',//галерея
            'related_products'=>'fs_related_products',//галерея
            );
        $this->meta=apply_filters('fs_post_meta',$meta);

           //  устанавливаем основные типы цен
        self::$prices=array(
            'price'=>array(
                'name'=>__( 'The base price', 'fast-shop' ),
                'meta_key'=>$this->meta['price'],
                'on'=>true,
                'description'=> __( 'This is the main type prices', 'fast-shop' )
                ),
            'action_price'=>array(
                'name'=>__( 'Promotional price', 'fast-shop' ),
                'meta_key'=>$this->meta['action_price'],
                'on'=>true,
                'description'=> __( 'This type of price interrupts the base price', 'fast-shop' )
                ),
            'displayed_price'=>array(
                'name'=>__( 'The displayed price', 'fast-shop' ),
                'meta_key'=>$this->meta['displayed_price'],
                'on'=>false,
                'description'=> __( 'example: "from %d %c for a couple" (%d - replaced with the price of %s on currency)', 'fast-shop' )
                )
            );
    }

}