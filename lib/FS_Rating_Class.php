<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 *Класс рефтинга и статистики
 */
class FS_Rating_Class
{

    function __construct()
    {
        add_action('wp_head', array(&$this,'kama_postviews'));
        add_action('wp_head', array(&$this,'viewed_posts'));
    }


    function kama_postviews() {

        /* ------------ Настройки -------------- */
        $meta_key       = 'views';  // Ключ мета поля, куда будет записываться количество просмотров.
        $who_count      = 0;            // Чьи посещения считать? 0 - Всех. 1 - Только гостей. 2 - Только зарегистрированных пользователей.
        $exclude_bots   =0;            // Исключить ботов, роботов, пауков и прочую нечесть :)? 0 - нет, пусть тоже считаются. 1 - да, исключить из подсчета.

        global $user_ID, $post;
        if(is_singular()) {
            $id = (int)$post->ID;
            static $post_views = false;
            if($post_views) return true; // чтобы 1 раз за поток
            $post_views = (int)get_post_meta($id,$meta_key, true);
            $should_count = false;
            switch( (int)$who_count ) {
                case 0: $should_count = true;
                    break;
                case 1:
                    if( (int)$user_ID == 0 )
                        $should_count = true;
                    break;
                case 2:
                    if( (int)$user_ID > 0 )
                        $should_count = true;
                    break;
            }
            if( (int)$exclude_bots==1 && $should_count ){
                $useragent = $_SERVER['HTTP_USER_AGENT'];
                $notbot = "Mozilla|Opera"; //Chrome|Safari|Firefox|Netscape - все равны Mozilla
                $bot = "Bot/|robot|Slurp/|yahoo"; //Яндекс иногда как Mozilla представляется
                if ( !preg_match("/$notbot/i", $useragent) || preg_match("!$bot!i", $useragent) )
                    $should_count = false;
            }

            if($should_count)
                if( !update_post_meta($id, $meta_key, ($post_views+1)) ) add_post_meta($id, $meta_key, 1, true);
        }
        return true;
    }


    /**
     * Метод позволяет зафиксировать в сессию $_SESSION['fs_user_settings']['viewed_product'] массив айдишников просмотренных товаровы
     * @return bool
     */
    function viewed_posts(){
        if (is_singular()){
            global $post;
            $id = (int)$post->ID;
            $_SESSION['fs_user_settings']['viewed_product'][]=$id;
            $_SESSION['fs_user_settings']['viewed_product']=array_unique($_SESSION['fs_user_settings']['viewed_product']);
        }
        return true;
    }
}
