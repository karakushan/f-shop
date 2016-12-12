<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 04.11.2016
 * Time: 16:58
 */

namespace FS;


class FS_Users_Class
{
    function __construct()
    {
        // Аякс вход пользователя
        add_action('wp_ajax_fs_login', array($this,'login_user'));
        add_action('wp_ajax_nopriv_fs_login', array($this,'login_user'));
        //  создание профиля пользователя
        add_action('wp_ajax_fs_profile_create',array(&$this,'fs_profile_create') );
        add_action('wp_ajax_nopriv_fs_profile_create',array(&$this,'fs_profile_create') );
        //  редактирование профиля пользователя
        add_action('wp_ajax_fs_profile_edit',array(&$this,'fs_profile_edit') );
        add_action('wp_ajax_nopriv_fs_profile_edit',array(&$this,'fs_profile_edit') );
    }

// Аякс вход пользователя
    function login_user()
    {
        $username =filter_input(INPUT_POST,'username',FILTER_SANITIZE_EMAIL) ;
        $password =filter_input(INPUT_POST,'password',FILTER_SANITIZE_STRING);

        if (is_email($username) ) {
            if (email_exists($username)) {
                $user=get_user_by('email',$username);
                $username=$user->user_login;

            }else{
                echo json_encode(array('status'=>0,'redirect'=>false,'error'=>'К сожалению пользователя с таким email не существует на сайте'));
                exit;
            }
        }else{
            if (!username_exists($username)) {
                echo json_encode(array('status'=>0,'redirect'=>false,'error'=>'К сожалению такого пользователя не существует на сайте'));
                exit;
            }
        }

// Авторизуем
        $user=get_user_by('login',$username);
        if(!in_array('wholesale_buyer', $user->roles)){
            echo json_encode(array(
                'status'=>0,
                'redirect'=>false,
                'error'=>'К сожалению вы не входите  в категорию "оптовый покупатель".'));
            exit;
        }

        $auth = wp_authenticate( $username, $password );
// Проверка ошибок
        if ( is_wp_error( $auth ) ) {
            echo json_encode(array('status'=>0,'redirect'=>false,'error'=>$auth->get_error_message()));
            exit;
        }
        else {
            nocache_headers();
            wp_clear_auth_cookie();
            wp_set_auth_cookie( $auth->ID );
            echo json_encode(array('status'=>1,'redirect'=>false));
            exit;
        }
    }

    // создание профиля пользователя
    public function fs_profile_create()
    {
        if (!wp_verify_nonce($_POST['_wpnonce'],'fast-shop')) exit('неправильный проверочный код nonce');
        if (empty($_POST['fs-form'])) exit('форма не передала никаких данных');

        $login=filter_input(INPUT_POST,'fs-login',FILTER_SANITIZE_STRING);
        $email=filter_input(INPUT_POST,'fs-email',FILTER_SANITIZE_EMAIL);

        $user_id = wp_insert_user( array( 
            'user_pass' => filter_input(INPUT_POST,'fs-password',FILTER_SANITIZE_STRING),
            'user_email' => $email,
            'user_login' =>  $login,
            'show_admin_bar_front'=>false
            ) );
        if (is_wp_error($user_id)) {
            $errors=$user_id->get_error_message();
            echo json_encode(array(
                'status'=>0,
                'message'=>$errors
                ));
            exit;
        }else{
           $post_data=array_map('trim',$_POST['fs-form']);
           foreach ($post_data as $meta_key => $meta_value) {
            update_user_meta($user_id,$meta_key,$meta_value) ;
        }
        echo json_encode(array(
            'status'=>1,
            'redirect'=>'/opt/',
            'message'=>'Поздравляем! Вы успешно прошли регистрацию. Теперь ваш профиль должен быть подтверждённым администратором сайта. Если проверка будет пройдена вы будете  переведены в категорию "оптовые покупатели".'
            )); 

// отсылаем письма
        $headers[] = 'Content-type: text/html; charset=utf-8';
    // пользователю
        $user_mail_header='Регистрация на сайте «'.get_bloginfo('name').'»';
        $user_message='<h3>Поздравляем!</h3><p>Вы успешно прошли регистрацию на сайте. Теперь ваш профиль должен быть подтверждённым администратором сайта. Если проверка будет пройдена вы будете  переведены в категорию "оптовые покупатели".</p>';
        $mail_user_send=wp_mail($email,$user_mail_header,$user_message, $headers ); 

// админу
        $admin_mail_header='Регистрация на сайте «'.get_bloginfo('name').'»';
        $admin_message='<h3>На вашем сайте новый пользователь '. $login.'!</h3><p>Вам необходимо проверить данные и перевести в категорию пользователей "Оптовые покуптели". Также не забудьте уведомить пользователя об этом.</p>';
        $mail_user_send=wp_mail(get_bloginfo('admin_email'),$admin_mail_header,$admin_message, $headers );
    }
    exit;
}

// редактирование профиля пользователя
public function fs_profile_edit()
{
    if (!wp_verify_nonce($_POST['_wpnonce'],'fast-shop')) exit('неправильный проверочный код nonce');
    if (empty($_POST['fs-form'])) exit('форма не передала никаких данных');
    $user=wp_get_current_user();
    $post_data=array_map('trim',$_POST['fs-form']);
    foreach ($post_data as $meta_key => $meta_value) {
        update_user_meta($user->ID,$meta_key,$meta_value) ;
    }
    $user_id = wp_update_user( array( 
        'ID' =>$user->ID,
        'user_pass' => filter_input(INPUT_POST,'fs-password',FILTER_SANITIZE_STRING),
        'user_email' => filter_input(INPUT_POST,'fs-email',FILTER_SANITIZE_EMAIL)
        ) );
    if (is_wp_error($user_id)) {
        $errors=$user_id->get_error_message();
        echo json_encode(array(
            'status'=>0,
            'message'=>$errors
            ));
        exit;
    }
    echo json_encode(array(
        'status'=>1,
        'message'=>'Ваши данные успешно обновились!'
        ));
    exit;
}

}