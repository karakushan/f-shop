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

    }


    /**
     *Отправка заказа в базу, на почту админа и заказчика
     */
    function order_send_ajax()
    {
     /*   ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);*/

        if ( !wp_verify_nonce( $_REQUEST['_wpnonce']) || !isset($_SESSION['cart']))
            die ( 'не пройдена верификация формы или не существует сессия корзины');

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

        //Производим очиску полученных данных с формы заказа
        $first_name=filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING);
        $last_name=filter_input(INPUT_POST,'last_name',FILTER_SANITIZE_STRING);
        $mail_client=filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL);
        $customer_phone=filter_input(INPUT_POST,'telefon',FILTER_SANITIZE_NUMBER_INT);
        $delivery=filter_input(INPUT_POST,'delivery',FILTER_SANITIZE_STRING);
        $delivery_address=filter_input(INPUT_POST,'delivery_address',FILTER_SANITIZE_STRING);
        $comments=filter_input(INPUT_POST,'comments',FILTER_SANITIZE_STRING);

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
                'products' =>serialize($_SESSION['cart']),
                'summa' =>fs_total_amount(false)
            ),
            array( '%s','%s','%d','%s','%s','%s','%s','%d')
        );

        $products='';
        foreach ($_SESSION['cart'] as $key => $count) {
            $products.='<li>'.get_the_title($key).' - '.fs_get_price($key).' '.fs_currency().'</li>';
        }

        $order_id=$wpdb->insert_id;
        $_SESSION['last_order_id']=$order_id;
        if (!$order_id) $wpdb->print_error();

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
        $search_replace=$search_replace+$fields;
            print_r($search_replace);
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

        unset($_SESSION['cart']);
        exit;

    }


} ?>