<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 25.08.2016
 * Time: 20:19
 */

namespace FS;


class FS_Config
{
    public $data;
    public $meta;
    public $options;

    function __construct()
    {
        global $wpdb;

        //Массив общих настроек плагина. При изменении настройки все настройки меняются глобально.
        $this->data=array(
            'plugin_path'=>PLUGIN_PATH,
            'plugin_url'=>PLUGIN_URL,
            'plugin_ver'=>'1.0',
            'plugin_name'=>'fast-shop',
            'plugin_settings'=>'fast-shop-settings',
            'table_name'=>$wpdb->prefix."fs_orders",
            'order_statuses'=>array(
                '0'=>'ожидает подтверждения',
                '1'=>'в ожидании оплаты',
                '2'=>'оплачен',
                '3'=>'отменён'
            )
        );

        //Массив настроек сайта
        $this->options=get_option('fs_option',array());


        //Массив настроек мета полей продукта (записи). При изменении настройки все настройки меняются глобально.
        $this->meta=array(
            'price'=>'fs_price',//базовая цена
            'wholesale_price'=>'fs_wholesale_price',//цена для оптовиков
            'discount'=>'fs_discount',//размер скидки
            'availability'=>'fs_availability',//наличие на складе
            'remaining_amount'=>'remaining_amount',//к-во товаров доступных для покупки
            'action'=>'fs_actions',//включить  или выключить акцию
            'action_page'=>'fs_page_action',//сылка на страницу описывающую акцию на товар
            'action_price'=>'fs_action_price',//акционная цена, перебивает цену поставленнуюю полем 'discount'
            'displayed_price'=>'fs_displayed_price',//тображаемая цена
            'attributes'=>'fs_attributes_post',//атрибуты товара
            'gallery'=>'fs_galery',//галерея
            'fs_gallery_path'=>'fs_gallery_path'//ссылка на папку с изображениями

        );
    }

}