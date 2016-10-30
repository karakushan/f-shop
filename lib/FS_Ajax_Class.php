<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Клас для обработки ajax запросов
 */

class FS_Ajax_Class
{

    function __construct()
    {
        add_action('wp_ajax_order_send',array(&$this,'order_send_ajax') );
        add_action('wp_ajax_nopriv_order_send',array(&$this,'order_send_ajax') );

        add_action('wp_ajax_attr_edit',array(&$this,'attr_edit_ajax') );
        add_action('wp_ajax_nopriv_attr_edit',array(&$this,'attr_edit_ajax') );

        add_action('wp_ajax_attr_group_edit',array(&$this,'attr_group_edit_ajax') );
        add_action('wp_ajax_nopriv_attr_group_edit',array(&$this,'attr_group_edit_ajax') );

        add_action('wp_ajax_attr_group_remove',array(&$this,'attr_group_remove_ajax') );
        add_action('wp_ajax_nopriv_attr_group_remove',array(&$this,'attr_group_remove_ajax') );

        add_action('wp_ajax_attr_single_remove',array(&$this,'attr_single_remove_ajax') );
        add_action('wp_ajax_nopriv_attr_single_remove',array(&$this,'attr_single_remove_ajax') );

        add_action('wp_ajax_fs_addto_wishlist',array(&$this,'fs_addto_wishlist') );
        add_action('wp_ajax_nopriv_fs_addto_wishlist',array(&$this,'fs_addto_wishlist') );

        add_action('wp_ajax_fs_del_wishlist_pos',array(&$this,'fs_del_wishlist_pos') );
        add_action('wp_ajax_nopriv_fs_del_wishlist_pos',array(&$this,'fs_del_wishlist_pos') ); 

       //живой поиск по сайту
        add_action('wp_ajax_fs_livesearch',array(&$this,'fs_livesearch') );
        add_action('wp_ajax_nopriv_fs_livesearch',array(&$this,'fs_livesearch') );

    }


    /**
     *Отправка заказа в базу, на почту админа и заказчика
     */
    function order_send_ajax()
    {
        $fs_config=new FS_Config();
       if ( !wp_verify_nonce( $_REQUEST['_wpnonce'],$fs_config->data['plugin_name'])) die ( 'не пройдена верификация формы nonce');

       if (isset($_REQUEST['fast-order']) && is_numeric($_REQUEST['fast-order'])){
        $product_id=(int)$_REQUEST['fast-order'];
        $product_count=(int)$_REQUEST['count'];
        $order_type='form';
    }else{
        if (empty($_SESSION['cart'])){
            die ( 'не найдена сессия корзины');
        }else{
            $order_type='session';
        }
    }

    global $wpdb;
    $wpdb->show_errors();

    
    $fs_delivery=new FS_Delivery_Class();

    // включаем возможность пользователям использовать собственные поля в форме заказа
    $fields=array();
    $exclude_fields=array('action','_wpnonce','_wp_http_referer');
    if (isset($_POST)){
        foreach ($_POST as $key=>$post_field){
            if (in_array($key,$exclude_fields)) continue;
            $fields['%'.$key.'%']=filter_input(INPUT_POST,$key,FILTER_SANITIZE_STRING);
        }
    }

        /*
        Список основных полей для использования в письмах
        fs_name
        fs_email
        fs_city
        fs_delivery
        fs_pay
        fs_phone
        fs_adress
        fs_message
        */

        //Производим очистку полученных данных с формы заказа
        $name=filter_input(INPUT_POST,'fs_name',FILTER_SANITIZE_STRING);
        $mail_client=filter_input(INPUT_POST,'fs_email',FILTER_SANITIZE_EMAIL);
        $city=filter_input(INPUT_POST,'fs_city',FILTER_SANITIZE_STRING);
        $delivery=filter_input(INPUT_POST,'fs_delivery',FILTER_SANITIZE_STRING);
        $pay=filter_input(INPUT_POST,'fs_pay',FILTER_SANITIZE_STRING);
        $customer_phone=filter_input(INPUT_POST,'fs_phone',FILTER_SANITIZE_NUMBER_INT);
        $delivery_address=filter_input(INPUT_POST,'fs_adress',FILTER_SANITIZE_STRING);
        $fs_message=filter_input(INPUT_POST,'fs_message',FILTER_SANITIZE_STRING);

        $insert_products=isset($_SESSION['cart'])?serialize($_SESSION['cart']):serialize(array($product_id));

        //Добавляем  данные заказа в базу
        $wpdb->insert(
            $fs_config->data['table_name'],
            array(
                'name' =>$name,
                'email' =>$mail_client,
                'status' =>0,
                'telephone' => $customer_phone,
                'comments' =>$fs_message,
                'delivery' =>$delivery,
                'products' =>$insert_products,
                'summa' =>fs_total_amount(false)
                ),
            array( '%s','%s','%d','%s','%s','%s','%s','%d')
            );

        $products='';

        $fs_att_group=get_option('fs-attr-groups')!=false?get_option('fs-attr-groups'):array(); 
        $fs_att=get_option('fs-attributes')!=false?get_option('fs-attributes'):array(); 

        $atts=array();
        if ($order_type=='form'){
            $products.='<li>'.get_the_title($product_id).' - '.fs_get_price($product_id).' '.fs_currency().'('.$product_count.' шт.)</li>';
        }else{
            foreach ($_SESSION['cart'] as $key => $count) {
               if (!empty($count['attr'])) {
                   foreach ($count['attr'] as $att_key => $att) {
                    if (empty($att)) continue;
                    if ($key!='count'){
                     $atts[]=$fs_att_group[$att_key].' - '.$fs_att[$att_key][$att]['name'];
                 }

             }
             $count_single=$count['attr']['count'];
         }

         $attributes=implode(',', $atts);
         $products.='<li>'.get_the_title($key).' - '.fs_get_price($key).' '.fs_currency().'('.$attributes.' | '.$count_single.' шт.)</li>';
     }
 }


 $order_id=$wpdb->insert_id;
 $_SESSION['last_order_id']=$order_id;
        /*
         Список переменных:
         %fs_name% - Имя заказчика,
         %number_products% - к-во купленных продуктов,
         %total_amount% - общая сумма покупки,
         %order_id% - id заказа,
         %products_listing% - список купленных продуктов,
         %fs_email% - почта заказчика,
         %fs_adress% - адрес доставки,
         %fs_pay% - способ оплаты,
         %fs_city% - город
         %fs_delivery% - тип доставки,
         %fs_phone% - телефон заказчика,
         %fs_message% - комментарий заказчика,
         %site_name% - название сайта
        */
         
        /*
        Список основных полей для использования в письмах
        fs_name
        fs_email
        fs_city
        fs_delivery
        fs_pay
        fs_phone
        fs_adress
        fs_message
        */
        $search_replace=array(
            '%fs_name%'=>$name,
            '%number_products%'=>fs_product_count(),
            '%total_amount%'=>fs_total_amount(false).' '.fs_currency(),
            '%order_id%'=>$order_id,
            '%products_listing%'=> $products,
            '%fs_email%'=>$mail_client,
            '%fs_adress%'=>$delivery_address,
            '%fs_city%'=>$city,
            '%fs_pay%'=>$pay,
            '%fs_delivery%'=>$fs_delivery->get_delivery($delivery),
            '%fs_phone%'=>$customer_phone,
            '%fs_message%'=>$fs_message,
            '%site_name%'=>get_bloginfo('name'),
            '%admin_url%'=>get_admin_url()
            );

        //Производим замену в отсылаемих письмах
        $search_replace=$fields+$search_replace;
        $search=array_keys($search_replace);
        $replace=array_values($search_replace);

        $user_message=str_replace($search,$replace,fs_option('customer_mail'));
        $admin_message=str_replace($search,$replace,fs_option('admin_mail'));

        //Отсылаем письмо с данными заказа заказчику
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $customer_mail_header=fs_option('customer_mail_header','Заказ товара на сайте «'.get_bloginfo('name').'»');
        wp_mail($mail_client,$customer_mail_header,$user_message, $headers );

        //Отсылаем письмо с данными заказа админу
        $admin_email=fs_option('manager_email',get_option('admin_email'));
        $admin_mail_header=fs_option('admin_mail_header','Заказ товара на сайте «'.get_bloginfo('name').'»');
        wp_mail($admin_email,$admin_mail_header,$admin_message, $headers );

        //Регистрируем нового пользователя
        if (!is_user_logged_in() && fs_option('register_user')==1){
            require_once(ABSPATH . WPINC . '/registration.php');
            $user_id = username_exists($mail_client);
            if ( !$user_id ) {
                $random_password = wp_generate_password();
                $new_user_id= wp_create_user($mail_client, $random_password, $mail_client);
                wp_new_user_notification( $new_user_id, $random_password);
            }
        }
        $result=array(
            'wpdb_error'=>$wpdb->last_error,
            'redirect'=>get_permalink(fs_option('page_success'))
            );
        echo json_encode($result);

        unset($_SESSION['cart']);
        exit;
    }

    public function attr_edit_ajax()
    {
        // print_r($_REQUEST);
        $fs_atributes=get_option('fs-attributes')!=false?get_option('fs-attributes'):array();
        if (isset($_REQUEST['action']) && $_REQUEST['action']=='attr_edit') {
           $fs_atributes[$_REQUEST['fs_attr_group']][]=array(
            'name'=>$_REQUEST['fs_attr_name'],
            'type'=>$_REQUEST['fs_attr_type'],
            'value'=>$_REQUEST['fs_attr_image_id']
            );

           update_option('fs-attributes',$fs_atributes);
           print_r($fs_atributes);
       }
       exit;
   } 

   public function attr_group_edit_ajax()
   {
        // print_r($_REQUEST);
    $fs_atributes=get_option('fs-attr-groups')!=false?get_option('fs-attr-groups'):array();
    if (isset($_REQUEST['action']) && $_REQUEST['action']=='attr_group_edit') {
        if (!empty($_REQUEST['slug']) || !empty($_REQUEST['name'])){
            $fs_atributes[$_REQUEST['slug']]=$_REQUEST['name'];
        } 
        $fs_atributes=array_diff($fs_atributes, array(''));
        update_option('fs-attr-groups',$fs_atributes);

        if (!empty($fs_atributes)) {
            $count=count($fs_atributes);
            echo "<option value=\"\">выберите группу</option>";
            $count_inc=0;
            foreach ($fs_atributes as $key => $value) {
                $count_inc++;
                if ( $count==$count_inc) {
                    echo "<option value=\"$key\" selected>$value</option>";
                } else {
                    echo "<option value=\"$key\">$value</option>";
                }
                
                
            }
        }
    }
    exit;
}

public function attr_group_remove_ajax()
{
    $fs_atributes=get_option('fs-attr-groups')!=false?get_option('fs-attr-groups'):array();
    if (isset($_REQUEST['action']) && $_REQUEST['action']=='attr_group_remove') {
      unset( $fs_atributes[$_REQUEST['attr']]);
      update_option('fs-attr-groups',$fs_atributes);
      echo "<option value=\"\">выберите группу</option>";
      if (!empty($fs_atributes)) {
         foreach ($fs_atributes as $key => $value) {     
            echo "<option value=\"$key\">$value</option>";
        }
    }

}
exit;
}

public function attr_single_remove_ajax()
{

    if (isset($_REQUEST['action']) && $_REQUEST['action']=='attr_single_remove') {
     $fs_atributes=get_option('fs-attributes')!=false?get_option('fs-attributes'):array();
     unset($fs_atributes[$_REQUEST['attr_group']][$_REQUEST['attr_id']]);
     update_option('fs-attributes',$fs_atributes);
 }
 exit;
}

public function fs_addto_wishlist()
{
    $res='';
    $product_id=(int)$_REQUEST['product_id'];

    $_SESSION['fs_user_settings']['fs_wishlist'][$product_id]= $product_id;
    if(!empty($_SESSION['fs_user_settings']['fs_wishlist'])){
        $wishlist=$_SESSION['fs_user_settings']['fs_wishlist'];
        $count=count($wishlist);
        $res.= '<a href="#" class="hvr-grow"><i class="icon icon-heart"></i><span>'.$count.'</span></a><ul class="fs-wishlist-listing">
        <li class="wishlist-header">'.__('Wishlist','cube44').': <i class="fa fa-times-circle" aria-hidden="true"></i></li>';
        foreach ($_SESSION['fs_user_settings']['fs_wishlist'] as $key => $value) { 
            $res.= "<li><i class=\"fa fa-trash\" aria-hidden=\"true\" data-fs-action=\"wishlist-delete-position\" data-product-id=\"$key\" data-product-name=\"".get_the_title($key)."\" ></i> <a href=\"".get_permalink($key)."\">".get_the_title($key)."</a></li>";
        }
        $res.='</ul>';
    }
    if (!empty($res)) {
        echo json_encode(array(
            'body'=>$res,
            'type'=>'success'
            ));
    }
    exit;    
}

public function fs_del_wishlist_pos()
{
    $product_id=(int)$_REQUEST['position'];
    $res='';
    unset($_SESSION['fs_user_settings']['fs_wishlist'][$product_id]);
    $wishlist=!empty($_SESSION['fs_user_settings']['fs_wishlist'])?$_SESSION['fs_user_settings']['fs_wishlist']:array();
    $count=count($wishlist);
    $class=$count==0?'':'wishlist-show';
    $res.= '<a href="#" class="hvr-grow"><i class="icon icon-heart"></i><span>'.$count.'</span></a><ul class="fs-wishlist-listing '.$class.'">
    <li class="wishlist-header">'.__('Wishlist','cube44').': <i class="fa fa-times-circle" aria-hidden="true"></i></li>';
    foreach ($_SESSION['fs_user_settings']['fs_wishlist'] as $key => $value) { 
        $res.= "<li><i class=\"fa fa-trash\" aria-hidden=\"true\" data-fs-action=\"wishlist-delete-position\" data-product-id=\"$key\" data-product-name=\"".get_the_title($key)."\" ></i> <a href=\"".get_permalink($key)."\">".get_the_title($key)."</a></li>";
    }
    $res.='</ul>';
    
    if (!empty($res)) {
        echo json_encode(array(
            'body'=>$res,
            'type'=>'success'
            ));
    }
    exit;
}

// живой поиск по сайту
public function fs_livesearch()
{
    $search=filter_input(INPUT_POST, 's', FILTER_SANITIZE_STRING); 
    
    $args=array('s'=>$search,'post_type'=>'product','posts_per_page'=>40);
    if (preg_match("/[a-z0-9_-]/",$search)) {
       $args['meta_query'] = array(
        array(
            'key'     => 'al_articul',
            'value'   => $search,
            'compare' => 'LIKE'
            )
        );
       unset($args['s']);
   }
   query_posts($args);
   get_template_part('fast-shop/livesearch/livesearch'); 
   wp_reset_query();
   exit;
}
} 