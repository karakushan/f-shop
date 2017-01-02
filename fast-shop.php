<?php
/*
Plugin Name: Fast Shop
Plugin URI: https://fast-shop.profglobal.ru
Description:  The plugin will help in the creation of any online store without changing your template.
Version: 1.0.1
Author: Vitaliy Karakushan
Author URI: https://fast-shop.profglobal.ru
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

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__.'/vendor/autoload.php';

define('FS_PLUGIN_PATH',plugin_dir_path( __FILE__ ));
define('FS_PLUGIN_URL',plugin_dir_url( __FILE__ ));

if (class_exists('\FS\FS_Init')) {
    new \FS\FS_Init;
    
    function fs_activate(){
        global $wpdb;
        $config=new FS\FS_Config();
        $table_name=$config->data['table_name'];
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
            $sql = "CREATE TABLE $table_name
            ( 
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `products` TEXT NULL,
            `comments` TEXT NULL,
            `delivery` VARCHAR(50) NULL DEFAULT NULL,
            `name` VARCHAR(50) NULL DEFAULT NULL,
            `email` VARCHAR(50) NULL DEFAULT NULL,
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


            dbDelta($sql);
        }  

        if($wpdb->get_var("SHOW TABLES LIKE 'wp_fs_order_info'") != 'wp_fs_order_info'){
            $sql2 = "CREATE TABLE `wp_fs_order_info` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL DEFAULT '0',
            `post_id` INT(11) NOT NULL DEFAULT '0',
            `id_model` INT(11) NOT NULL DEFAULT '0',
            `count` INT(11) NOT NULL DEFAULT '0',
            UNIQUE INDEX `id` (`id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            ;";
            dbDelta($sql2);
        }
        add_role('wholesale_buyer', 'Оптовый покупатель', array( 'read' => true, 'level_0' => true ) );

    } 
    function fs_deactivate()
    {
    // Do nothing
} // END public static function deactivate

// Installation and uninstallation hooks
register_activation_hook(__FILE__, 'fs_activate');
register_deactivation_hook(__FILE__,'fs_deactivate');
}



