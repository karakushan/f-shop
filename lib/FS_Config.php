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

    function __construct()
    {
        global $wpdb;

        $this->data=array(
            'plugin_path'=>PLUGIN_PATH,
            'plugin_url'=>PLUGIN_URL,
            'plugin_ver'=>'1.0',
            'plugin_name'=>'fast-shop',
            'table_name'=>$wpdb->prefix."fs_orders"
        );

        $this->meta=array(
            'price'=>'fs_price',
            'discount'=>'fs_discount',
            'availability'=>'fs_availability',
            'action'=>'fs_actions',
            'action_page'=>'fs_page_action',
            'displayed_price'=>'fs_displayed_price',
            'attributes'=>'fs_attributes',
            'gallery'=>'fs_galery'
        );
    }

}