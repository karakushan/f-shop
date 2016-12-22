<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class FS_Export_Class
{
    public function __construct()
    {
        //  запуск экспорта базы по крону
        add_action( 'my_task_hook', array($this,products_to_yml()) );
        if ( ! wp_next_scheduled( 'my_task_hook' ) ) {
           $export_shedule=fs_option('export_shedule');
           if (!empty($export_shedule)) {
               wp_schedule_event( time(), $export_shedule, 'my_task_hook' );
           }

       }
   }

   static function products_to_yml($admin_notices=false){
    $xml=new \DomDocument('1.0',get_bloginfo('charset'));
    $gallery=new \FS\FS_Images_Class;
    $format=fs_option('export_format','xml');

    $upload_dir=wp_upload_dir('shop');
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
    $shop_company=$xml->createElement('company',fs_option('company_name',get_bloginfo('name')));
    $shop->appendChild($shop_company);
    /*yml_catalog->shop->url*/
    $shop_url=$xml->createElement('url',get_bloginfo('url'));
    $shop->appendChild($shop_url);
    /*yml_catalog->shop->currencies*/
    $currencies=$xml->createElement('currencies');
    $shop->appendChild($currencies);
    /*yml_catalog->shop->currencies->currency*/
    $currency=$xml->createElement('currency');
    $currency->setAttribute("id",'UAH');
    $currency->setAttribute('rate','1');
    $currencies->appendChild($currency);

        //  КАТЕГОРИИ
    /*yml_catalog->shop->currencies*/
    $categories=$xml->createElement('categories');
    $shop->appendChild($categories);   
    /*yml_catalog->shop->category*/
    $terms=get_terms(array('taxonomy'=>'catalog','hide_empty'=>false));
    if ($terms) {
        foreach ($terms as $key => $term) {
            $category=$xml->createElement('category',$term->name);
            $category->setAttribute("id",$term->term_id);
            if ($term->parent) {
             $category->setAttribute("parentId",$term->parent);
         }
         $categories->appendChild($category);
     }
 }

        //  ТОВАРЫ
 /*yml_catalog->shop->offers*/
 $offers=$xml->createElement('offers');
 $shop->appendChild($offers); 

 $posts=get_posts(array('post_type'=>'product','posts_per_page'=>-1));
 if ($posts) {
  foreach ($posts as $key => $post) { setup_postdata($post);
    $offer_id=apply_filters('fs_product_id',$post->ID);
    /*yml_catalog->shop->offers->offer*/
    $offer=$xml->createElement('offer');

    $offer->setAttribute("id",$offer_id);
    $offer->setAttribute("available",'true');
    $offers->appendChild($offer);
    /*yml_catalog->shop->offers->offer->url*/
    $url=$xml->createElement('url',get_permalink($post->ID));
    $offer->appendChild($url);  
    /*yml_catalog->shop->offers->offer->price*/
    $price_format=fs_get_wholesale_price($post->ID);
    $price=$xml->createElement('price', number_format($price_format, 2, '.', ''));
    $offer->appendChild($price);  
    /*yml_catalog->shop->offers->offer->currencyId*/
    $currencyId=$xml->createElement('currencyId','UAH');
    $offer->appendChild($currencyId); 
    /*yml_catalog->shop->offers->offer->name*/
    $name=$xml->createElement('name',get_the_title($post->ID));
    $offer->appendChild($name);   
    /*yml_catalog->shop->offers->offer->vendorCode*/
    $vendorCode=$xml->createElement('vendorCode',fs_product_code($post->ID));
    $offer->appendChild($vendorCode);  
    /*yml_catalog->shop->offers->offer->description*/
    $description=$xml->createElement('description',sanitize_text_field($post->post_content));
    $offer->appendChild($description); 
    /*yml_catalog->shop->offers->offer->oldprice*/
    $old_price=fs_base_price($post->ID,false);
    if (!empty($old_price)) {
        $oldprice=$xml->createElement('oldprice',round($old_price));
        $offer->appendChild($oldprice); 
    }

    /*yml_catalog->shop->offers->offer->categoryId*/
    $product_terms=get_the_terms($post->ID,'catalog');
    if ( $product_terms) {
        foreach ($product_terms as $key => $product_term) {
            $categoryId=$xml->createElement('categoryId',$product_term->term_id);
            $offer->appendChild($categoryId);
        }
    }
    /*yml_catalog->shop->offers->offer->param*/
    $product_attributes=get_the_terms($post->ID,'product-attributes');
    if ( $product_attributes) {
        foreach ($product_attributes as $key => $product_attribut) {
            $parent_name = get_term_field( 'name', $product_attribut->parent,'product-attributes');
            if (!is_wp_error( $parent_name)) {
                $param=$xml->createElement('param',$product_attribut->name);
                $parent_name = get_term_field( 'name', $product_attribut->parent,'product-attributes');
                $param->setAttribute("name",$parent_name);
                $offer->appendChild( $param);
            }

        }
    }
    /*yml_catalog->shop->offers->offer->picture*/
    $gallery_images=$gallery->fs_galery_images($post->ID);
    if (!empty($gallery_images)) {
        foreach ($gallery_images as $key => $gallery_image) {
            if (is_numeric( $gallery_image)) {
                $picture=$xml->createElement('picture',wp_get_attachment_url($gallery_image));
            }else{
                $picture=$xml->createElement('picture',esc_url(get_bloginfo('url').$gallery_image));
            }
            $offer->appendChild($picture); 
        }
    }

}
}
        //  сохраняем результат
$save_file=$xml->save($upload_dir['path'].'/products.'.$format);
if ($admin_notices) {
    if ($save_file) {
        add_action('admin_notices', function(){
            echo '<div class="updated is-dismissible"><p>'.__('Update/create database was successful!','fast-shop').'</p></div>';
        });
    }else{
        add_action('admin_notices', function(){
            echo '<div class="notice notice-warning is-dismissible"><p>'.__('An error occurred during the process of database updates!','fast-shop').'</p></div>';
        });
    }
}
return $save_file;
}

}
