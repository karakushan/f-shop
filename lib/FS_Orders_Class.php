<?php
namespace FS;
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Класс заказов
 */
class FS_Orders_Class
{
    public $order_status;
    private $config;

    function __construct()
    {
        add_action('init', array(&$this, 'order_status_change'));
        $this->config = new FS_Config();
        $this->order_status = $this->config->data['order_statuses'];

        //  ajax очистка базы заказов
        add_action('wp_ajax_admin_truncate_orders', array(&$this, 'admin_truncate_orders'));
        add_action('wp_ajax_nopriv_admin_truncate_orders', array(&$this, 'admin_truncate_orders'));
    }

    //Получаем все заказы в виде объекта
    public function get_orders()
    {
        global $wpdb;
        $table_name = $this->config->data['table_name'];
        $per_page = 15;
        if (isset($_SESSION['pagination'])) {
            $per_page = $_SESSION['pagination'];
        }
        $offset = 1;
        if (isset($_GET['tab'])) {
            $offset = $_GET['tab'];
        }
        $offset = $offset * $per_page - $per_page;
        $results = $wpdb->get_results("SELECT * FROM  $table_name ORDER BY id DESC LIMIT $offset,$per_page");
        return $results;
    }


    public function order_pagination($class = '')
    {
        global $wpdb;
        $table_name = $this->config->data['table_name'];
        $per_page = 15;
        if (isset($_SESSION['pagination'])) {
            $per_page = $_SESSION['pagination'];
        }
        $all_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $pages = $all_orders / $per_page + 1;

        if ($all_orders > $per_page) {
            echo "<ul class=\"$class\">";
            for ($i = 1; $i <= $pages; $i++) {
                if (!isset($_GET['tab'])) $_GET['tab'] = 1;
                if ($_GET['tab'] == $i) {
                    $active = 'class="active"';
                } else {
                    $active = '';
                }
                echo "<li><a href=\"" . add_query_arg(array('tab' => $i)) . "\" $active>$i</a></li>";
            }
            echo "</ul>";
        }
    }


    //Отображает статус заказа в удобочитаемом виде
    public function order_status($status)
    {
        return $this->order_status[$status];
    }

    /**
     * возвращает общую информацию о заказе
     * @param  int $order_id [description]
     * @return object           все данные заказа  $order_id
     */
    public function get_order_data(int $order_id)
    {
        global $wpdb;
        $table_name = $this->config->data['table_orders'];
        $res = $wpdb->get_row("SELECT * FROM $table_name WHERE id ='$order_id'");
        return $res;
    }

    /**
     * @param int $user_id id пользователя заказы которого нужно получить
     * @param int|string $status id статуса заказа    ('0'=>'ожидает подтверждения', '1'=>'в ожидании оплаты', '2'=>'оплачен','3'=>'отменён')
     *
     * @return array|null|object объект с заказами
     */
    function get_user_orders($user_id = 0, $status = 'all')
    {
        global $wpdb;
        $config = new FS_Config;
        if (empty($user_id)) {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
        }
        $user_id = (int)$user_id;
        $table = $config->data['table_orders'];
        if ($status == 'all') {
            $query = "SELECT * FROM $table WHERE user_id='$user_id' ORDER by id DESC";

        } else {
            $query = "SELECT * FROM $table WHERE user_id='$user_id' AND status='$status' ORDER by id DESC";

        }
        $orders = $wpdb->get_results($query);
        return $orders;

    }

//Получаем объект одного заказа
    public function get_order(int $id)
    {
        global $wpdb;
        $table_name = $this->config->data['table_orders'];
        $res = $wpdb->get_row("SELECT * FROM $table_name WHERE id ='$id'");
        if (!is_null($res)) {
            $user = get_user_by('id', $res->user_id);
            $res->user_name = $user->display_name;
            $res->email = $user->user_email;
            $res->city = get_user_meta($res->user_id, 'city', 1);
            $res->telephone = get_user_meta($res->user_id, 'phone', 1);
            $res->payment_id = $res->payment;
            $res->delivery_id = $res->delivery;
            $res->delivery_name = 'не определено';
            $res->payment_name = 'не определено';
            $res->products = unserialize($res->products);
            $res->status = $this->order_status($res->status);
            $res->date = strtotime($res->date);
            if (!is_wp_error(get_term_field('name', $res->delivery))) {
                $res->delivery_name = get_term_field('name', $res->delivery);
            }
            if (!is_wp_error(get_term_field('name', $res->payment))) {
                $res->payment_name = get_term_field('name', $res->payment);
            }
        }
        return $res;
    }

    /**
     * подсчитывает общую сумму товаров в одном заказе
     * @param $products - список товаров в объекте
     * @return float $items_sum - стоимость всех товаров
     */
    public function fs_order_total(int $order_id)
    {
        $item = array();
        $currency = fs_currency();
        $products = $this->get_order($order_id);
        if ($products) {
            foreach ($products as $product) {
                $item[$product->post_id] = $product->count * fs_get_price($product->post_id);
            }
            $items_sum = array_sum($item);
        }
        $items_sum = apply_filters('fs_price_format', $items_sum);
        $items_sum = $items_sum . ' ' . $currency;
        return $items_sum;
    }

    public function order_status_change()
    {
        global $wpdb;
        $upd = false;
        $table_name = $this->config->data['table_name'];
        if (isset($_GET['action']) && $_GET['action'] == 'edit') {
            if (isset($_GET['id']) || isset($_GET['status'])) {
                $upd = $wpdb->update(
                    $table_name,
                    array('status' => $_GET['status']),
                    array('id' => $_GET['id']),
                    array('%d'),
                    array('%d')
                );
            }

            if ($upd) {
                $status = $this->order_status($_GET['status']);
                $order = $this->get_order($_GET['id']);
                $user_mesage = 'Статус заказа #' . $_GET['id'] . ' изменён на "' . $status . '". ';
                if ($_GET['status'] == 1) {
                    $user_mesage .= "Вы можете оплатить заказ сейчас если выбрали способ оплаты \"предоплата\" или в момент получения заказа";
                }
                $subject = 'Уведомление о изменении статуса заказа  #' . $_GET['id'];
                $headers[] = 'Content-type: text/html; charset=utf-8'; // в виде массива
                wp_mail($order->email, $subject, $user_mesage, $headers);
                wp_redirect(remove_query_arg(array('action', 'id', 'status')));
            }
        }
    }

    //  делает очистку страницы заказов
    public function admin_truncate_orders()
    {
        global $wpdb;
        if ($wpdb->query("TRUNCATE TABLE wp_fs_orders") && $wpdb->query("TRUNCATE TABLE wp_fs_order_info")) {
            echo json_encode(array(
                'status' => true,
                'action' => 'refresh',
                'message' => 'Операция прошла успешно!',
            ));
        } else {
            echo json_encode(array(
                'status' => false,
                'action' => '',
                'message' => 'Возникла ошибка с удалением базы заказов.',
            ));
        }
        exit;
    }
}