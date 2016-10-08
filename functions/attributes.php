<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 08.10.2016
 * Time: 17:03
 */

/**
 * получает все зарегистрированные группы атрибутов или свойтва товаров
 * @return array|mixed|void
 */
function fs_get_attributes_group(){
    $group=get_option('fs-attr-groups')!=false?get_option('fs-attr-groups'):array();
    return $group;
}

/**
 * получает все зарегистрированные атрибуты или свойтва товаров
 * @return array|mixed|void
 */
function fs_get_attributes(){
    $attributes=get_option('fs-attributes')!=false?get_option('fs-attributes'):array();
    return $attributes;
}