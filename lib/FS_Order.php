<?php

namespace FS;

class FS_Order
{
    /**
     * Products in the order.
     *
     * @var array
     */
    public $items = [];

    /**
     * Delivery method term object.
     *
     * @var \WP_Term
     */
    public $delivery_method;

    /**
     * Payment method term object.
     *
     * @var \WP_Term
     */
    public $payment_method;

    /**
     * Total order amount.
     *
     * @var float
     */
    public $total_amount = 0;

    /**
     * Order discount amount.
     *
     * @var float
     */
    public $discount = 0;

    /**
     * Order comment.
     *
     * @var string
     */
    public $comment = '';

    /**
     * Order status.
     *
     * @var string
     */
    public $status = 'new';

    /**
     * Total quantity of products in order.
     *
     * @var int
     */
    public $count = 0;

    /**
     * Order date and time.
     *
     * @var string
     */
    public $date;

    /**
     * Order ID.
     *
     * @var int
     */
    public $ID = 0;

    /**
     * Order post object.
     *
     * @var \WP_Post|null
     */
    public $post;

    /**
     * Order user data.
     *
     * @var array
     */
    public $user;

    /**
     * Customer database table name.
     *
     * @var string
     */
    protected $customer_table = 'fs_customers';

    /**
     * Current user ID.
     *
     * @var int
     */
    public $user_id = 0;

    /**
     * Order meta data.
     *
     * @var array
     */
    public $meta;

    /**
     * Customer delivery address.
     *
     * @var string|null
     */
    public $customer_address;

    /**
     * Order title format in admin panel.
     *
     * @var string
     */
    public $title = 'Order #%d from %s %s (%s)';

    /**
     * Customer IP address.
     *
     * @var string|null
     */
    protected $customer_ip;

    /**
     * Customer city.
     *
     * @var string|null
     */
    public $customer_city;

    /**
     * Customer email address.
     *
     * @var string|null
     */
    public $customer_email;

    /**
     * Customer first name.
     *
     * @var string|null
     */
    public $customer_first_name;

    /**
     * Customer last name.
     *
     * @var string|null
     */
    public $customer_last_name;

    /**
     * Customer phone number.
     *
     * @var string|null
     */
    public $customer_phone;

    /**
     * Customer newsletter subscription status.
     *
     * @var int
     */
    public $customer_subscribe_news = 1;

    /**
     * Customer group ID.
     *
     * @var int
     */
    public $customer_group = 1;

    /**
     * Selected delivery method ID.
     *
     * @var int|null
     */
    public $delivery_method_id;

    /**
     * Selected payment method ID.
     *
     * @var int|null
     */
    public $payment_method_id;

    /**
     * Order packing cost.
     *
     * @var float
     */
    public $packing_cost = 0.0;

    /**
     * Shipping cost text description.
     *
     * @var string
     */
    public $shipping_cost_text = '';

    /**
     * Shipping cost amount.
     *
     * @var float
     */
    public $shipping_cost = 0.0;

    /**
     * Cart subtotal cost.
     *
     * @var float
     */
    public $cart_cost = 0.0;

    /**
     * Order type (standard, etc).
     *
     * @var string
     */
    public $order_type = 'standard';

    /**
     * Customer object instance.
     *
     * @var FS_Customer|null
     */
    public $customer;

    /**
     * Customer ID in database.
     *
     * @var int
     */
    public $customer_ID = 0;

    /**
     * Customer data array.
     *
     * @var array
     */
    protected $customer_data = [];

    /**
     * Order data object.
     *
     * @var FS_Order
     */
    protected $order_data;

    /**
     * FS_Order constructor.
     *
     * @param int $order_id
     */
    public function __construct($order_id = 0)
    {
        global $wpdb;

        $this->customer_ip = fs_get_user_ip();
        $this->customer_table = $wpdb->prefix.$this->customer_table;

        if ($order_id) {
            $this->set_order($order_id);
        }
    }

    /**
     * Проверяет входные данные пользователя.
     */
    private function check_customer_data()
    {
        $allowed_customer_fields = apply_filters('fs_allowed_customer_fields', [
            'email',
            'first_name',
            'last_name',
            'subscribe_news',
            'group',
            'address',
            'user_id',
            'city',
            'phone',
            'ip',
        ]);

        foreach ($this->customer_data as $key => $user_datum) {
            if (!in_array($key, $allowed_customer_fields)) {
                unset($this->customer_data[$key]);
            }
        }
    }

    /**
     * Обновляет или создает заказ.
     */
    public function save()
    {
        global $wpdb;

        $this->check_customer_data();
        if (empty($this->customer_data) || !is_array($this->customer_data)) {
            return new \WP_Error('fs_not_valid_customer_data', __('User data is not filled in or not valid!', 'f-shop'));
        }

        // Обновляем данные покупателя
        if ($this->customer_ID) {
            $wpdb->update($this->customer_table, $this->customer_data, ['id' => $this->customer_ID]);
        } else {
            $wpdb->insert($this->customer_table, array_merge(
                [
                    'subscribe_news' => $this->customer_subscribe_news,
                    'group' => $this->customer_group,
                    'user_id' => get_current_user_id(),
                    'ip' => $this->customer_ip,
                ],
                $this->customer_data
            ));

            $this->customer_ID = $wpdb->insert_id;
        }

        if (empty($this->order_data) || !is_array($this->order_data)) {
            return new \WP_Error('fs_not_valid_order_data', __('Order data is not complete or not valid!', 'f-shop'));
        }

        $this->order_data['_customer_id'] = $this->customer_ID;

        if ($this->ID) {
            foreach ($this->order_data as $meta_key => $order_datum) {
                update_post_meta($this->ID, $meta_key, $order_datum);
            }
        } else {
            $this->ID = wp_insert_post([
                'post_title' => '',
                'post_status' => fs_option('fs_default_order_status')
                    ? get_term_field('slug', fs_option('fs_default_order_status'), FS_Config::get_data('order_statuses_taxonomy')) : $this->status,
                'post_author' => $this->user_id ? $this->user_id : 1,
                'post_type' => FS_Config::get_data('post_type_orders'),
                'meta_input' => $this->order_data,
            ]);
        }

        if ($this->ID) {
            $order_data = get_post($this->ID);
            if (!$order_data->post_title) {
                $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->customer_table} WHERE id=%d", $this->customer_ID));
                wp_update_post([
                    'ID' => $this->ID,
                    'post_title' => sprintf(
                        $this->title ? __($this->title, 'f-shop') : __('Order #%d from %s %s (%s)', 'f-shop'),
                        $this->ID,
                        $customer->first_name,
                        $customer->last_name,
                        date('d.m.y H:i', strtotime($order_data->post_date))
                    ),
                ]);
            }
        }
    }

    /**
     * Устанавливает данные заказа.
     *
     * @param int $order_id
     *
     * @return null
     */
    public function set_order($order_id = 0)
    {
        global $wpdb;

        $this->ID = $order_id;

        if (!$order_id && $this->get_last_order_id()) {
            $this->ID = $this->get_last_order_id();
        }

        $this->post = get_post($this->ID);
        $order_id = $order_id ? $order_id : $this->get_last_order_id();

        if (!is_numeric($order_id) || $order_id == 0) {
            return null;
        }

        $products = get_post_meta($order_id, '_products', 1) ? get_post_meta($order_id, '_products', 1) : [];

        $this->items = array_values(array_map(function ($item) {
            return fs_set_product($item);
        }, $products));

        foreach ($this->items as $item) {
            /* @var $item FS_Product */
            $this->count += max((int) $item->count, 1);
        }

        $this->total_amount = round((float) get_post_meta($order_id, '_amount', 1), 2);
        $this->packing_cost = (float) get_post_meta($order_id, '_packing_cost', 1);
        $this->cart_cost = (float) get_post_meta($order_id, '_cart_cost', 1);
        $this->discount = (float) get_post_meta($order_id, '_order_discount', 1);

        $this->comment = get_post_meta($order_id, '_comment', 1);
        $this->customer_city = get_post_meta($order_id, 'city', 1);
        $this->user = (array) get_post_meta($order_id, '_user', 1);
        $this->user['ip'] = get_post_meta($order_id, '_customer_ip', 1);
        $this->order_type = get_post_meta($order_id, '_order_type', 1) ? get_post_meta($order_id, '_order_type', 1) : 'standard';

        $this->customer_ID = absint(get_post_meta($order_id, '_customer_id', 1));
        $this->customer = new FS_Customer();
        $this->customer->load_by_order_id($order_id);

        if (!$this->cart_cost) {
            foreach ($this->items as $item) {
                $this->cart_cost += $item->cost;
            }
        }

        $payment_method_id = (int) get_post_meta($order_id, '_payment', 1);
        if ($payment_method_id) {
            $this->payment_method = get_term($payment_method_id, FS_Config::get_data('product_pay_taxonomy'));
        }

        $this->delivery_method = new FS_Delivery($order_id);
        $this->status = $this->get_order_status($order_id);
        $this->date = $this->post->post_date;
    }

    /**
     * Добавляет событие в историю  заказа.
     *
     * @param int   $order_id ID заказа
     * @param array $event    должно содержать поля :
     *
     *  'name'           => Название события ,
     *  'description'    => Описание события,
     *  'initiator_id'   => ID пользотателя инициировашего событие,
     *  'initiator_name' => Имя пользотателя инициировашего событие,
     *  'time'           => время события,
     *  'changed'        => измененные поля
     *
     * @return bool|int|\WP_Error
     */
    public function add_history_event($order_id = 0, $event = [])
    {
        $history = $this->get_order_history($order_id, false, ['sort' => 'asc']);

        if (!isset($event['id'])) {
            return new \WP_Error('fs_no_event_field_isset', __('Не заполненны все объязательные поля события!'));
        }

        array_push($history, $event);

        return update_post_meta($order_id, 'fs_order_history', $history);
    }

    /**
     * Возвращает историю заказа в виде массива.
     *
     * @param int   $order_id      идентификатор заказа
     * @param bool  $creation_date включать ли дату создания в историю
     * @param array $args          дополнительные параметры
     *
     * @return mixed|\WP_Error
     */
    public function get_order_history($order_id = 0, $creation_date = true, $args = [])
    {
        $order_id = $order_id ? $order_id : ($this->ID ? $this->ID : 0);
        $args = wp_parse_args($args, [
            'order' => 'desc',
        ]);

        if (!$order_id) {
            return new \WP_Error('no-order-id', __('Order number not specified', 'f-shop'));
        }

        $history = get_post_meta($order_id, 'fs_order_history', 1) ? get_post_meta($order_id, 'fs_order_history', 1) : [];

        // Сортируем историю в обратном порядке
        if (is_array($history) && count($history) && $args['order'] == 'desc') {
            krsort($history);
        }

        // Добавляем дату создания заказа в историю
        if ($creation_date) {
            array_push($history, [
                'id' => 'create_order',
                'initiator_id' => 0,
                'initiator_name' => implode(' ', [$this->customer->first_name, $this->customer->last_name]),
                'time' => strtotime($this->post->post_date),
                'data' => [
                    'order_id' => $order_id,
                ],
            ]);
        }

        $history = array_map(function ($item) {
            $item['name'] = $this->get_history_event_name($item);
            $item['description'] = $this->get_history_event_detail($item);

            return $item;
        }, $history);

        return apply_filters('fs_order_history', $history);
    }

    /**
     * Возвращает название события в истории заказа.
     *
     * @return mixed|string
     */
    public function get_history_event_name($item)
    {
        $names = [
            'change_order_status' => __('Change order status', 'f-shop'),
            'create_order' => __('Create order', 'f-shop'),
            'adding_a_comment' => __('Adding a comment', 'f-shop'),
        ];

        $names = apply_filters('fs_order_history_names', $names);

        return isset($names[$item['id']]) ? $names[$item['id']] : '';
    }

    /**
     * Возвращает описание события в истории заказа.
     *
     * @return mixed|string
     */
    public function get_history_event_detail($item)
    {
        $detail = '';

        if ($item['id'] == 'change_order_status') {
            $user = get_user_by('ID', $item['initiator_id']);

            // Використовуємо нову функцію для отримання найкращої назви статусу
            $order_status = FS_Orders::get_status_display_name($item['data']['status']);

            $detail = sprintf(__('User "%s" changed order status to "%s"', 'f-shop'), $user->display_name, $order_status);
        } elseif ($item['id'] == 'adding_a_comment') {
            $user = get_user_by('ID', $item['initiator_id']);
            $detail = sprintf(
                __('User "%s" added a comment. <a href="%s" target="_blank"> Go to comment <a>', 'f-shop'),
                $user->display_name,
                esc_url(add_query_arg([
                    'action' => 'editcomment',
                    'c' => $item['data']['comment_id'],
                ], admin_url('/comment.php')))
            );
        } elseif ($item['id'] == 'create_order') {
            $detail = sprintf(__('User "%s" created an order', 'f-shop'), $item['initiator_name']);
        }

        return apply_filters('fs_order_history_detail', $detail);
    }

    /**
     * Получение товаров заказа.
     *
     * @return array
     */
    public function getItems()
    {
        return (array) $this->items;
    }

    /**
     * Получение общей суммы покупки.
     *
     * @return int
     */
    public function getTotalAmount()
    {
        return apply_filters('fs_price_format', $this->total_amount);
    }

    /**
     *  total amount display.
     *
     * @return void
     */
    public function totalAmount($format = '%s <span>%s</span>')
    {
        printf($format, $this->getTotalAmount(), fs_currency());
    }

    /**
     * @return \WP_Term
     */
    public function getDeliveryMethod()
    {
        return $this->delivery_method;
    }

    /**
     * @return \WP_Term
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    public function setComment(string $comment)
    {
        $this->comment = $comment;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    public function setCount(int $count)
    {
        $this->count = $count;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * Возваращает локализированную дату и время заказа.
     *
     * @param string $format
     */
    public function getDate($format = '')
    {
        if (!$format) {
            $format = get_option('time_format');
        }

        return date_i18n($format, strtotime($this->date));
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->ID;
    }

    public function setID(int $ID)
    {
        $this->ID = $ID;
    }

    /**
     * @return int
     */
    public static function get_last_order_id()
    {
        return isset($_SESSION['fs_last_order_id']) && is_numeric($_SESSION['fs_last_order_id'])
            ? intval($_SESSION['fs_last_order_id']) : 0;
    }

    /**
     * @return string
     */
    public function get_customer_table()
    {
        return $this->customer_table;
    }

    /**
     * @return array
     */
    public function get_customer_data()
    {
        return $this->customer_data;
    }

    public function set_customer_data(array $customer_data)
    {
        $this->customer_data = $customer_data;
    }

    /**
     * @return FS_Order
     */
    public function get_order_data()
    {
        return $this->order_data;
    }

    /**
     * @param FS_Order $order_data
     */
    public function set_order_data($order_data)
    {
        $this->order_data = $order_data;
    }

    /**
     * Возвращает статус заказа в текстовой, читабельной форме.
     *
     * @param $order_id - ID заказа
     *
     * @return string
     */
    public function get_order_status(
        $order_id
    ) {
        $post_status_id = get_post_status($order_id);
        $statuses = FS_Orders::default_order_statuses();

        if (!empty($statuses[$post_status_id]['name'])) {
            $status = $statuses[$post_status_id]['name'];
        } else {
            $status = __('The order status is not defined', 'f-shop');
        }

        return $status;
    }
}
