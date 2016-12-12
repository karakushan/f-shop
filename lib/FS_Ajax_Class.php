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
        //  обработка формы заказа
        add_action('wp_ajax_order_send',array(&$this,'order_send_ajax') );
        add_action('wp_ajax_nopriv_order_send',array(&$this,'order_send_ajax') );
        //  добавление в список желаний
        add_action('wp_ajax_fs_addto_wishlist',array(&$this,'fs_addto_wishlist') );
        add_action('wp_ajax_nopriv_fs_addto_wishlist',array(&$this,'fs_addto_wishlist') );
        // удаление из списка желаний
        add_action('wp_ajax_fs_del_wishlist_pos',array(&$this,'fs_del_wishlist_pos') );
        add_action('wp_ajax_nopriv_fs_del_wishlist_pos',array(&$this,'fs_del_wishlist_pos') );
       //   живой поиск по сайту
        add_action('wp_ajax_fs_livesearch',array(&$this,'fs_livesearch') );
        add_action('wp_ajax_nopriv_fs_livesearch',array(&$this,'fs_livesearch') );

    }


    /**
     *Отправка заказа в базу, на почту админа и заказчика
     */
    function order_send_ajax()
    {
        if ( !wp_verify_nonce($_POST['_wpnonce'],'fast-shop')) die ( 'не пройдена верификация формы nonce');
        $fs_products=array();
        $user=wp_get_current_user();
        if ($user->ID==0){
            $user_login='пользователь не авторизован';
        }else{
            $user_login=$user->user_login;
        }
        if (!empty($_POST['fs_cart'])){
            if($_POST['order_type']=="single"){
                $product_id=(int)$_POST['fs_cart']['product_id'];
                $product_count=(int)$_POST['fs_cart']['count'];
                $fs_products[$product_id]=array('count'=>$product_count);
            }else{
                $fs_products=$_POST['fs_cart'];
            }
            
        }else{
            if (empty($_SESSION['cart'])){
                die ( 'не найдена сессия корзины');
            }else{
                $fs_products=$_SESSION['cart'];
            }
        }
        // если корзина пуста или возникла ошибка с обработкой выходим с собщением
        if (empty($fs_products)) die ('нет товаров в корзине!');

        $fs_config=new FS_Config();
        global $wpdb;
        $wpdb->show_errors(); // включаем показывать ошибки при работе с базой

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
                'products' =>serialize($fs_products),
                'summa' =>fs_total_amount($fs_products,false)
                ),
            array( '%s','%s','%d','%s','%s','%s','%s','%d')
            ); 
        $order_id=$wpdb->insert_id;
        $_SESSION['last_order_id']=$order_id;

        foreach ($fs_products as $post => $data) {
            $wpdb->insert(
                'wp_fs_order_info',
                array(
                 'order_id'=>$order_id,
                 'post_id'=>$post,
                 'id_model'=>get_post_meta($post,'al_product_id',1),
                 'count'=>$data['count'],
                 ),
                array('%d','%d','%d','%d')
                );
        }

        $products='';
        foreach ($fs_products as $id =>$product) {
            $products.=fs_frontend_template('order/products-list',array('id'=>$id,'product'=>$product));
       }

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
         %site_url% - адрес сайта
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
            '%fs_login%'=>$user_login,
            '%date%'=>date('d.m.Y H:i'),
            '%number_products%'=>fs_product_count($fs_products),
            '%total_amount%'=>fs_total_amount($fs_products,false).' '.fs_currency(),
            '%total_amount_opt%'=>fs_total_amount($fs_products,false).' '.fs_currency(),
            '%order_id%'=>$order_id,
            '%products_listing%'=> $products,
            '%fs_email%'=>$mail_client,
            '%fs_adress%'=>$delivery_address,
            '%fs_city%'=>$city,
            '%fs_pay%'=>$pay,
            '%fs_delivery%'=>$delivery,
            '%fs_phone%'=>$customer_phone,
            '%fs_message%'=>$fs_message,
            '%site_name%'=>get_bloginfo('name'),
            '%site_url%'=>get_bloginfo('url'),
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
        $mail_user_send=wp_mail($mail_client,$customer_mail_header,$user_message, $headers );

        //Отсылаем письмо с данными заказа админу
        $admin_email=fs_option('manager_email',get_option('admin_email'));
        $admin_mail_header=fs_option('admin_mail_header','Заказ товара на сайте «'.get_bloginfo('name').'»');
        $mail_admin_send=wp_mail($admin_email,$admin_mail_header,$admin_message, $headers );

        //Регистрируем нового пользователя
        if (isset($_REQUEST['fs_register_user']) && $_REQUEST['fs_register_user']==1){
            $user_id = username_exists($mail_client);
            if ( !$user_id ) {
                $random_password = wp_generate_password();
                $new_user_id= wp_create_user($mail_client, $random_password, $mail_client);
                $register_mail_header='Регистрация на сайте «'.get_bloginfo('name').'»';
                $register_message='<h3>Поздравляем! Вы успешно зарегистрировались на сайте '.get_bloginfo().'.</h3> 
                <p>Теперь вам нужно установить пароль для вашей учётной записи. </p>
                <p>Логин: '.$mail_client.'</p>
                <p><a href="<?php echo esc_url( wp_lostpassword_url( home_url() ) ); ?>" title="Установить пароль.">Установить пароль.</a></p>';
                $mail_user_send=wp_mail($mail_client, $register_mail_header, $register_message,$headers);

            }
        }

        $result=array(
//            'post_object'=>$_POST,
            'admin_email'=>$admin_email,
            'admin_mail_header'=>$admin_mail_header,
            'admin_message'=>$admin_message,
            'headers'=>$headers,
            'wpdb_error'=>$wpdb->last_error,
            'mail_user_send'=>$mail_user_send,
            'products'=>$fs_products,
            'mail_admin_send'=> $mail_admin_send,
            'redirect'=>get_permalink(fs_option('page_success'))
            );
        echo json_encode($result);

        unset($_SESSION['cart']);
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