<?php

namespace FS;

class FS_Customer
{
    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var string
     */
    public $first_name = '';

    /**
     * @var string
     */
    public $last_name = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $phone = '';

    /**
     * @var string
     */
    public $city = '';

    /**
     * @var string
     */
    public $address = '';

    /**
     * @var int
     */
    public $subscribe_news = 1;

    /**
     * @var int
     */
    public $group = 1;

    /**
     * @var string
     */
    public $ip = '';

    /**
     * @var int
     */
    public $user_id = 0;

    /**
     * FS_Customer constructor.
     * @param int|object $customer ID клиента или объект с данными
     */
    public function __construct($customer = null)
    {
        if (is_numeric($customer)) {
            $this->load_by_id($customer);
        } elseif (is_object($customer)) {
            $this->load_from_object($customer);
        }
    }

    /**
     * Загружает данные клиента по ID заказа
     * @param int $order_id
     * @return bool Успешно ли загружены данные
     */
    public function load_by_order_id($order_id)
    {
        if (!$order_id) {
            return false;
        }

        $customer_id = absint(get_post_meta($order_id, '_customer_id', 1));

        // Загружаем город заказа в любом случае
        $order_city = get_post_meta($order_id, 'city', 1);
        if ($order_city) {
            $this->city = $order_city;
        }

        if (!$customer_id) {
            // Если нет ID покупателя, пробуем загрузить данные из метаполя _user
            $user_data = get_post_meta($order_id, '_user', 1);
            if (!empty($user_data)) {
                $temp_customer = new \stdClass();
                $temp_customer->first_name = isset($user_data['first_name']) ? $user_data['first_name'] : '';
                $temp_customer->last_name = isset($user_data['last_name']) ? $user_data['last_name'] : '';
                $temp_customer->email = isset($user_data['email']) ? $user_data['email'] : '';
                $temp_customer->phone = isset($user_data['phone']) ? $user_data['phone'] : '';

                $this->load_from_object($temp_customer);
                return true;
            }
            return false;
        }

        return (bool)$this->load_by_id($customer_id);
    }

    /**
     * Загружает данные клиента по ID
     * @param int $id
     * @return bool Успешно ли загружены данные
     */
    private function load_by_id($id)
    {
        global $wpdb;
        $customer = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}fs_customers WHERE id = %d",
                $id
            )
        );

        if ($customer) {
            $this->load_from_object($customer);
            return true;
        }
        return false;
    }

    /**
     * Загружает данные из объекта
     * @param object $data
     */
    private function load_from_object($data)
    {
        foreach (get_object_vars($data) as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        $this->id = isset($data->id) ? (int)$data->id : 0;
    }

    /**
     * Получает полное имя клиента
     * @return string
     */
    public function get_full_name()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
