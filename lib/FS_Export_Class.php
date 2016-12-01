<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 30.11.2016
 * Time: 10:34
 */

namespace FS;
use \DOMDocument;


class FS_Export_Class
{

    function products_to_yml(){
        $xml=new DomDocument('1.0',get_bloginfo('charset'));
        $xml->formatOutput = true;
        /*yml_catalog*/
        $yml_catalog=$xml->createElement('yml_catalog');
        $yml_catalog->setAttribute("date", date('Y-m-d H:i'));
        $xml->appendChild($yml_catalog);
        /*yml_catalog->shop*/
        $shop=$xml->createElement('shop');
        $yml_catalog->appendChild($shop);
        /*yml_catalog->shop->name*/
        $shop_name=$xml->createElement('name',get_bloginfo('name'));
        $shop->appendChild($shop_name);
        /*yml_catalog->shop->company*/
        $shop_company=$xml->createElement('company',get_bloginfo('name'));
        $shop->appendChild($shop_company);
        /*yml_catalog->shop->url*/
        $shop_url=$xml->createElement('url',get_bloginfo('url'));
        $shop->appendChild($shop_url);

        $xml->save($_SERVER['DOCUMENT_ROOT'].'/products.xml');
    }
}