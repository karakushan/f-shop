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

}