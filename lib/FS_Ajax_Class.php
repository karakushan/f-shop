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

    }


    /**
     *Отправка заказа в базу, на почту админа и заказчика
     */
    function order_send_ajax()
    {
        /*   ini_set('error_reporting', E_ALL);
           ini_set('display_errors', 1);
           ini_set('display_startup_errors', 1);*/

           if ( !wp_verify_nonce( $_REQUEST['_wpnonce'])) die ( 'не пройдена верификация формы');

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

        $fs_config=new FS_Config();
        $fs_delivery=new FS_Delivery_Class();

        $fields=array();
        $exclude_fields=array('action','_wpnonce','_wp_http_referer');
        if (isset($_POST)){
            foreach ($_POST as $key=>$post_field){
                if (in_array($key,$exclude_fields)) continue;
                $fields['%'.$key.'%']=filter_input(INPUT_POST,$key,FILTER_SANITIZE_STRING);
            }
        }

        //Производим очистку полученных данных с формы заказа
        $first_name=filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING);
        $last_name=filter_input(INPUT_POST,'last_name',FILTER_SANITIZE_STRING);
        $mail_client=filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL);
        $customer_phone=filter_input(INPUT_POST,'telefon',FILTER_SANITIZE_NUMBER_INT);
        $delivery=filter_input(INPUT_POST,'delivery',FILTER_SANITIZE_STRING);
        $delivery_address=filter_input(INPUT_POST,'delivery_address',FILTER_SANITIZE_STRING);
        $comments=filter_input(INPUT_POST,'comments',FILTER_SANITIZE_STRING);

        $insert_products=isset($_SESSION['cart'])?serialize($_SESSION['cart']):serialize(array($product_id));

        //Добавляем  данные заказа в базу
        $wpdb->insert(
            $fs_config->data['table_name'],
            array(
                'name' =>$first_name.' '.$last_name,
                'email' =>$mail_client,
                'status' =>0,
                'telephone' => $customer_phone,
                'comments' =>$comments,
                'delivery' =>$delivery,
                'products' =>$insert_products,
                'summa' =>fs_total_amount(false)
                ),
            array( '%s','%s','%d','%s','%s','%s','%s','%d')
            );

        $products='';
        if ($order_type=='form'){
            $products.='<li>'.get_the_title($product_id).' - '.fs_get_price($product_id).' '.fs_currency().'('.$product_count.' шт.)</li>';
        }else{
            foreach ($_SESSION['cart'] as $key => $count) {
                $products.='<li>'.get_the_title($key).' - '.fs_get_price($key).' '.fs_currency().'('.$count['count'].' шт.)</li>';
            }
        }


        $order_id=$wpdb->insert_id;
        $_SESSION['last_order_id']=$order_id;
        /*
         Список переменных:
         %first_name% - Имя заказчика,
         %last_name% - Фамилия,
         %number_products% - к-во купленных продуктов,
         %total_amount% - общая сумма покупки,
         %order_id% - id заказа,
         %products_listing% - список купленных продуктов,
         %mail_client% - почта заказчика,
         %delivery_address% - адрес доставки,
         %delivery% - тип доставки,
         %customer_phone% - телефон заказчика,
         %comments% - комментарий заказчика,
         %site_name% - название сайта
        */
         $search_replace=array(
            '%first_name%'=>$first_name,
            '%last_name%'=>$last_name,
            '%number_products%'=>fs_product_count(),
            '%total_amount%'=>fs_total_amount(false).' '.fs_currency(),
            '%order_id%'=>$order_id,
            '%products_listing%'=> $products,
            '%mail_client%'=>$mail_client,
            '%delivery_address%'=>$delivery_address,
            '%delivery%'=>$fs_delivery->get_delivery($delivery),
            '%customer_phone%'=>$customer_phone,
            '%site_name%'=>get_bloginfo('name')

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


} 