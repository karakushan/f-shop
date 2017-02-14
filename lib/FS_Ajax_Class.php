<?php
namespace FS;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Клас для обработки ajax запросов
 */
class FS_Ajax_Class
{

    function __construct()
    {
        //  обработка формы заказа
        add_action('wp_ajax_order_send', array(&$this, 'order_send_ajax'));
        add_action('wp_ajax_nopriv_order_send', array(&$this, 'order_send_ajax'));
        //  добавление в список желаний
        add_action('wp_ajax_fs_addto_wishlist', array(&$this, 'fs_addto_wishlist'));
        add_action('wp_ajax_nopriv_fs_addto_wishlist', array(&$this, 'fs_addto_wishlist'));
        // удаление из списка желаний
        add_action('wp_ajax_fs_del_wishlist_pos', array(&$this, 'fs_del_wishlist_pos'));
        add_action('wp_ajax_nopriv_fs_del_wishlist_pos', array(&$this, 'fs_del_wishlist_pos'));
        //   живой поиск по сайту
        add_action('wp_ajax_fs_livesearch', array(&$this, 'fs_livesearch'));
        add_action('wp_ajax_nopriv_fs_livesearch', array(&$this, 'fs_livesearch'));

        //  получение связанных постов категории
        add_action('wp_ajax_fs_get_taxonomy_posts', array(&$this, 'get_taxonomy_posts'));
        add_action('wp_ajax_nopriv_fs_get_taxonomy_posts', array(&$this, 'get_taxonomy_posts'));

    }


    /**
     *Отправка заказа в базу, на почту админа и заказчика
     */
    function order_send_ajax()
    {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'fast-shop')) die ('не пройдена верификация формы nonce');

        $fs_products = $_SESSION['cart'];

        $fs_config = new FS_Config();
        global $wpdb;
        $wpdb->show_errors(); // включаем показывать ошибки при работе с базой

        //Производим очистку полученных данных с формы заказа
        $name = filter_input(INPUT_POST, 'fs_name', FILTER_SANITIZE_STRING);
        $mail_client = filter_input(INPUT_POST, 'fs_email', FILTER_SANITIZE_EMAIL);
        $city = filter_input(INPUT_POST, 'fs_city', FILTER_SANITIZE_STRING);
        $delivery = filter_input(INPUT_POST, 'fs_delivery', FILTER_SANITIZE_STRING);
        $pay = filter_input(INPUT_POST, 'fs_pay', FILTER_SANITIZE_STRING);
        $customer_phone = filter_input(INPUT_POST, 'fs_phone', FILTER_SANITIZE_NUMBER_INT);
        $delivery_info = filter_input(INPUT_POST, 'fs_delivery_info', FILTER_SANITIZE_STRING);
        $fs_message = filter_input(INPUT_POST, 'fs_message', FILTER_SANITIZE_STRING);

        // устанавливаем заголовки
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $headers[] = 'From: ' . fs_option('name_sender', get_bloginfo('name')) . ' <' . fs_option('email_sender', get_bloginfo('admin_email')) . '>';

        // проверяем существование пользователя
        $user_id = 0;
        $register_user=false;
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $user_login = $user->user_login;
        }else {
            if ($user = get_user_by('email', $mail_client)) {
                $user_id = $user->ID;
                $user_login = $user->user_login;
            }
        }

        //Регистрируем нового пользователя
        if (!$user_id) {
            $random_password = wp_generate_password();
            $user_id = wp_create_user($mail_client, $random_password, $mail_client);
            $register_mail_header = 'Регистрация на сайте «' . get_bloginfo('name') . '»';
            $register_message = '<h3>Поздравляем! Вы успешно зарегистрировались на сайте ' . get_bloginfo() . '.</h3> 
            <p>Теперь вам нужно установить пароль для вашей учётной записи. </p>
            <p>Логин: ' . $mail_client . '</p>
            <p><a href="<?php echo esc_url( wp_lostpassword_url( home_url() ) ); ?>" title="Установить пароль.">Установить пароль.</a></p>';
            $mail_user_send = wp_mail($mail_client, $register_mail_header, $register_message, $headers);

            if (!is_wp_error($user_id)) {
                $user = get_user_by('id', $user_id);
                $user_id = $user->ID;
                $user_login = $user->user_login;
                $user_data=array('ID' => $user_id,'display_name'=>$name,'role'=>'client');
                // добавляем роль клиента
                wp_update_user($user_data);
                update_user_meta($user_id, 'city', $city);
                update_user_meta($user_id, 'phone', $customer_phone);
            }else{
                exit('Пользователь существует');
            }
        }

        // включаем возможность пользователям использовать собственные поля в форме заказа
        $fields = array();
        $exclude_fields = array('action', '_wpnonce', '_wp_http_referer');
        if (isset($_POST)) {
            foreach ($_POST as $key => $post_field) {
                if (in_array($key, $exclude_fields)) continue;
                $fields['%' . $key . '%'] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
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
        //Добавляем  данные заказа в базу
        $wpdb->insert(
            $fs_config->data['table_name'],
            array(
                'user_id' => $user_id,
                'status' => 0,
                'comments' => $fs_message,
                'delivery' => $delivery,
                'delivery_info' => $delivery_info,
                'payment' => $pay,
                'products' => serialize($fs_products),
                'summa' => fs_total_amount($fs_products, false),
                'formdata' => serialize($_POST)
            ),
            array(
                '%d',//user_id
                '%d',//status
                '%s',//comments
                '%d',//delivery
                '%s',//delivery_info
                '%d',//payment
                '%s',//products
                '%f',//summa
                '%s'//formdata
            )
        );
        $order_id = $wpdb->insert_id;
        $_SESSION['last_order_id'] = $order_id;

        /*
         Список переменных:
         %fs_name% - Имя заказчика,
         %number_products% - к-во купленных продуктов,
         %total_amount% - общая сумма покупки,
         %fs_wholesale_amount% - общая сумма покупки для оптовых пользователей,
         %total_amount_admin% - общая сумма покупки, по базовой цене, без
         %order_id% - id заказа,
         %products_listing% - список купленных продуктов,
         %products_listing_admin% - список купленных продуктов для администратора,
         %fs_login% - логин если пользователь авторизован.
         %fs_email% - почта заказчика,
         %fs_adress% - адрес доставки,
         %fs_pay% - способ оплаты,
         %fs_city% - город
         %fs_delivery% - тип доставки,
         %fs_delivery_info%- дополнительная информация о доставке
         %fs_phone% - телефон заказчика,
         %fs_message% - комментарий заказчика,
         %site_name% - название сайта
         %site_url% - адрес сайта
         %admin_url% - адрес админки
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

        $search_replace = array(
            '%fs_name%' => $name,
            '%fs_login%' => $user_login,
            '%date%' => date('d.m.Y H:i'),
            '%number_products%' => fs_product_count($fs_products, false),
            '%total_amount%' => apply_filters('fs_price_format', fs_total_amount($fs_products, false)) . ' ' . fs_currency(),
            '%order_id%' => $order_id,
            '%fs_email%' => $mail_client,
            '%fs_city%' => $city,
            '%fs_currency%' => fs_currency(),
            '%fs_pay%' => $pay,
            '%fs_delivery%' => $delivery,
            '%fs_delivery_info%' => $delivery_info,
            '%fs_phone%' => $customer_phone,
            '%fs_message%' => $fs_message,
            '%site_name%' => get_bloginfo('name'),
            '%site_url%' => get_bloginfo('url'),
            '%site_description%' => get_bloginfo('description'),
            '%site_logo%' => fs_option('site_logo'),
            '%admin_url%' => get_admin_url()
        );

        $search_replace = apply_filters('fs_mail_template_var', $search_replace);

        // Производим замену в отсылаемих письмах
        $search_replace = $fields + $search_replace;
        $search = array_keys($search_replace);
        $replace = array_values($search_replace);

        // текст письма заказчику
        $user_message = apply_filters('fs_order_user_message', $fs_products);
        $user_message = str_replace($search, $replace, $user_message);

        // текст письма админу
        $admin_message = apply_filters('fs_order_admin_message', $fs_products);
        $admin_message = str_replace($search, $replace, $admin_message);


        //Отсылаем письмо с данными заказа заказчику
        $customer_mail_header = fs_option('customer_mail_header', 'Заказ товара на сайте «' . get_bloginfo('name') . '»');
        $mail_user_send = wp_mail($mail_client, $customer_mail_header, $user_message, $headers);

        //Отсылаем письмо с данными заказа админу
        $admin_email = fs_option('manager_email', get_option('admin_email'));
        $admin_mail_header = fs_option('admin_mail_header', 'Заказ товара на сайте «' . get_bloginfo('name') . '»');
        $mail_admin_send = wp_mail($admin_email, $admin_mail_header, $admin_message, $headers);


        if (!empty($wpdb->last_error)) {
            $success = false;
            echo $wpdb->last_error;
        } else {
            $success = true;
            $result = array(
                'success' => $success,
                'products' => $fs_products,
                'order_id' => $order_id,
                'redirect' => get_permalink(fs_option('page_success'))
            );
            $json = json_encode($result);
            echo $json;

        }
        if ($success) unset($_SESSION['cart']);
        exit();

    }

    public function fs_addto_wishlist()
    {
        $product_id = (int)$_REQUEST['product_id'];
        $_SESSION['fs_wishlist'][$product_id] = $product_id;
        ob_start();
        fs_wishlist_widget(array(), false);
        $widget = ob_get_clean();
        echo json_encode(array(
            'body' => $widget,
            'type' => 'success'
        ));

        exit;
    }

    public function fs_del_wishlist_pos()
    {
        $product_id = (int)$_REQUEST['position'];
        $res = '';
        unset($_SESSION['fs_user_settings']['fs_wishlist'][$product_id]);
        $wishlist = !empty($_SESSION['fs_user_settings']['fs_wishlist']) ? $_SESSION['fs_user_settings']['fs_wishlist'] : array();
        $count = count($wishlist);
        $class = $count == 0 ? '' : 'wishlist-show';
        $res .= '<a href="#" class="hvr-grow"><i class="icon icon-heart"></i><span>' . $count . '</span></a><ul class="fs-wishlist-listing ' . $class . '">
        <li class="wishlist-header">' . __('Wishlist', 'cube44') . ': <i class="fa fa-times-circle" aria-hidden="true"></i></li>';
        foreach ($_SESSION['fs_user_settings']['fs_wishlist'] as $key => $value) {
            $res .= "<li><i class=\"fa fa-trash\" aria-hidden=\"true\" data-fs-action=\"wishlist-delete-position\" data-product-id=\"$key\" data-product-name=\"" . get_the_title($key) . "\" ></i> <a href=\"" . get_permalink($key) . "\">" . get_the_title($key) . "</a></li>";
        }
        $res .= '</ul>';

        if (!empty($res)) {
            echo json_encode(array(
                'body' => $res,
                'type' => 'success'
            ));
        }
        exit;
    }

// живой поиск по сайту
    public function fs_livesearch()
    {
        $config = new FS_Config();
        $search = sanitize_text_field($_POST['s']);
        $args = array(
            's' => $search,
            'post_type' => 'product',
            'posts_per_page' => 40
        );
        $query = query_posts($args);
        if ($query) {
            get_template_part('fast-shop/livesearch/livesearch');
            wp_reset_query();
        } else {
            $args2 = array(
                'post_type' => 'product',
                'posts_per_page' => 40,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => $config->meta['product_article'],
                        'value' => $search,
                        'compare' => 'LIKE'
                    )
                )
            );
            query_posts($args2);
            get_template_part('fast-shop/livesearch/livesearch');
            wp_reset_query();
        }
        exit;
    }

//  возвражает список постов определёного термина
    public function get_taxonomy_posts()
    {
        $term_id = (int)$_POST['term_id'];
        $post_id = (int)$_POST['post'];
        $body = '';
        $posts = get_posts(array('post_type' => 'product', 'posts_per_page' => -1, 'post__not_in' => array($post_id),
            'tax_query' =>
                array(
                    array(
                        'taxonomy' => 'catalog',
                        'field' => 'term_id',
                        'terms' => $term_id
                    )
                )
        ));

        $body .= '<select data-fs-action="select_related">';
        $body .= '<option value="">Выберите товар</option>';
        if ($posts) {
            foreach ($posts as $key => $post) {
                $body .= '<option value="' . $post->ID . '">' . $post->post_title . '</option>';
            }
        }
        $body .= '</select>';

        echo json_encode(array('body' => $body));
        exit;
    }
} 