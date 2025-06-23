<?php

namespace FS;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Класс заказов.
 */
class FS_Orders
{
    public $post_type = 'orders';
    public $last_order_id;
    public $order_fields = [];
    public $order_statuses = [];
    public $id;
    public $_products;

    public function __construct()
    {
        // Initialize order statuses with registered WordPress statuses
        $this->order_statuses = self::get_registered_order_statuses();

        add_filter('pre_get_posts', [$this, 'filter_orders_by_search']);

        // ===== ORDER STATUSES ====
        add_action('admin_init', [$this, 'order_status_custom']);
        add_action('admin_footer-post-new.php', [$this, 'true_append_post_status_list']);
        add_action('admin_footer-post.php', [$this, 'true_append_post_status_list']);
        add_filter('display_post_states', [$this, 'true_status_display']);

        // Срабатывает в момент изменения статуса заказа
        add_action('transition_post_status', [$this, 'fs_change_order_status'], 10, 3);

        // операции с метабоксами - удаление стандартных, добавление новых
        add_action('admin_menu', [$this, 'remove_submit_order_metabox']);

        // Сохранение заказа в админке
        add_action('save_post', [$this, 'save_order'], 10, 3);

        $this->last_order_id = $this->get_last_order_id();

        // Срабатывает после добавления комментария к заказу
        add_action('comment_post', [$this, 'after_comment_inserted'], 10, 3);

        add_action('admin_init', function () {
            add_action('add_meta_boxes', [$this, 'register_order_meta_box']);
        });

        add_filter('manage_orders_posts_custom_column', [$this, 'admin_order_custom_column'], 5, 2);

        // Add status filter dropdown and filtering
        add_action('restrict_manage_posts', [$this, 'add_order_status_filter']);
        add_filter('parse_query', [$this, 'filter_orders_by_status']);
    }

    public function admin_order_custom_column($colname, $post_id)
    {
        $order = new FS_Order($post_id);
        switch ($colname) {
            case 'fs_order_amount':
                $amount = get_post_meta($post_id, '_amount', 1);
                $amount = apply_filters('fs_price_format', $amount);
                echo esc_html($amount.' '.fs_currency());
                break;
            case 'fs_order_status':
                $post = get_post($post_id);
                $status = $post->post_status;
                $status_name = $this->get_status_name($status);
                $status_color = $this->get_status_color($status);
                $status_class = 'status-'.str_replace('-', '_', $status);

                $style = '';
                if ($status_color) {
                    $style = ' style="background-color: '.esc_attr($status_color).'; color: #fff;"';
                }

                echo '<span class="fs-order-status-badge '.esc_attr($status_class).'"'.$style.'>'.esc_html($status_name).'</span>';
                break;
            case 'fs_user':
                echo '<ul>';
                if (isset($order->customer->first_name) && !empty($order->customer_ID)) {
                    printf('<li><b><a href="%s" target="_blank">%s %s</a></b></li>', esc_url(admin_url('edit.php?page=fs-customers&field=id&s='.$order->customer_ID.'&post_type=orders')), esc_html($order->customer->first_name), esc_html($order->customer->last_name));
                }
                if (isset($order->customer->phone)) {
                    printf('<li><a href="tel:%1$s">%1$s</a></li>', esc_html($order->customer->phone));
                }
                if (isset($order->customer->email)) {
                    printf('<li><a href="mailto:%1$s">%1$s</a></li>', esc_html($order->customer->email));
                }
                echo '</ul>';
                break;
        }
    }

    /**
     * Get status name for display.
     *
     * @param string $status
     *
     * @return string
     */
    private function get_status_name($status)
    {
        return self::get_status_display_name($status);
    }

    /**
     * Get status color for badge.
     *
     * @param string $status
     *
     * @return string|null
     */
    private function get_status_color($status)
    {
        // Спробуємо отримати колір з мета поля таксономії
        $term = get_term_by('slug', $status, FS_Config::get_data('order_statuses_taxonomy'));
        if ($term && !is_wp_error($term)) {
            $color = get_term_meta($term->term_id, '_fs_status_color', true);
            if ($color) {
                return $color;
            }
        }

        // Якщо колір не знайдено, повертаємо синій за замовчуванням
        return '#3498db';
    }

    /**
     * Коллбек хука "comment_post".
     *
     * @see https://developer.wordpress.org/reference/hooks/comment_post/
     */
    public function after_comment_inserted($comment_ID, $comment_approved, $commentdata)
    {
        if (get_post_type($commentdata['comment_post_ID']) !== FS_Config::get_data('post_type_orders')) {
            return;
        }

        $order = new FS_Order($commentdata['comment_post_ID']);
        $order->add_history_event($commentdata['comment_post_ID'], [
            'id' => 'adding_a_comment',
            'initiator_id' => $commentdata['user_id'],
            'initiator_name' => $commentdata['comment_author'],
            'data' => [
                'comment_id' => $comment_ID,
            ],
        ]);
    }

    /**
     * Сохраняет данные заказа в админке.
     */
    public function save_order($post_id, $post, $update)
    {
        global $pagenow, $typenow;

        if (!is_admin()
             || $pagenow !== 'post.php'
             || $typenow !== FS_Config::get_data('post_type_orders')
             || $post->post_type != FS_Config::get_data('post_type_orders')
             || wp_is_post_revision($post_id)
             || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
             || !isset($_POST['fs_is_admin'])
        ) {
            return;
        }

        $order = new FS_Order($post_id);
        $order->customer_ID = absint($_REQUEST['user']['customer_ID']);
        $order->set_customer_data(array_map('trim', $_POST['user']));
        $order->set_order_data($_POST['order']);
        $order->save();
    }

    /**
     * Возвращает ID последнего заказа.
     */
    public static function get_last_order_id()
    {
        $id = 0;
        if (!empty($_SESSION['fs_last_order_id'])) {
            $id = intval($_SESSION['fs_last_order_id']);
        }

        return $id;
    }

    /**
     * Возвращает общую сумму последнего заказа.
     */
    public function get_last_order_amount()
    {
        $order_id = $this->get_last_order_id();
        $order = new FS_Orders();
        $order_info = $order->get_order($order_id);

        return floatval($order_info->summa);
    }

    /**
     * удаляем стандартный метабокс сохранения, публикации поста
     * добавляет свой метабокс для изменения статуса заказа.
     */
    public function remove_submit_order_metabox()
    {
        remove_meta_box('submitdiv', $this->post_type, 'side');
        add_meta_box('fs_order_status_box', __('Order status', 'f-shop'), [
            $this,
            'order_status_box',
        ], $this->post_type, 'side', 'high');

        $this->orders_bubble();
    }

    /**
     * Показывает количество новых заказов справа около пунктом меню "Заказы"
     * не заходя в сам пункт "Заказы"
     * создано для удобства и информирования админов.
     */
    public function orders_bubble()
    {
        global $menu;
        $custom_post_count = wp_count_posts(FS_Config::get_data('post_type_orders'));
        $order_status_id = fs_option('fs_default_order_status');
        $order_status = get_term_field('slug', $order_status_id, FS_Config::get_data('order_statuses_taxonomy'));

        $custom_post_pending_count = !is_wp_error($order_status) && isset($custom_post_count->{$order_status})
            ? $custom_post_count->{$order_status} : 0;

        if (!$custom_post_pending_count) {
            return;
        }

        if ($menu) {
            foreach ($menu as $key => $value) {
                if ($menu[$key][2] == 'edit.php?post_type='.esc_attr(FS_Config::get_data('post_type_orders'))) {
                    $menu[$key][0] .= ' <span class="update-plugins count-'.esc_attr($custom_post_pending_count).'"><span class="plugin-count" aria-hidden="true"> '.esc_html($custom_post_pending_count).'</span><span class="screen-reader-text"> '.esc_html($custom_post_pending_count).'</span></span>';

                    return;
                }
            }
        }
    }

    /**
     * Returns default F-Shop order statuses only.
     * These statuses are registered when plugin is activated.
     *
     * @return array Array of default statuses with 'name' and 'description' keys
     */
    public static function default_order_statuses()
    {
        $order_statuses = [
            'new' => [
                'name' => __('New', 'f-shop'),
                'description' => __('About all orders with the status of "New" administrator receives notification by mail, which allows him to immediately contact the buyer. For the convenience of accounting for new orders, they are automatically placed in the "New" tab on the order management panel and are displayed as a list, sorted by the date added.', 'f-shop'),
            ],
            'processed' => [
                'name' => __('Processed', 'f-shop'),
                'description' => __('The order is accepted and can be paid. The status is introduced mainly for the convenience of internal order management, not "New", but not yet paid or not sent for delivery;', 'f-shop'),
            ],
            'pay' => [
                'name' => __('In the process of payment', 'f-shop'),
                'description' => __('The status can be assigned by the administrator after sending an invoice to the client for payment.', 'f-shop'),
            ],
            'paid' => [
                'name' => __('Paid', 'f-shop'),
                'description' => __('The status is assigned to the order automatically if the settlement is made through the Money Online payment system. If the goods were delivered by courier and paid in cash, the status can be used as a reporting;', 'f-shop'),
            ],
            'for-delivery' => [
                'name' => __('In delivery', 'f-shop'),
                'description' => __('The administrator assigns this status to orders when drawing up the delivery list. The sheet is transferred to the courier along with the goods.', 'f-shop'),
            ],
            'delivered' => [
                'name' => __('Delivered', 'f-shop'),
                'description' => __('The status is assigned to orders transferred to the courier. An order can maintain this status for a long time, depending on how far the customer is located;', 'f-shop'),
            ],
            'refused' => [
                'name' => __('Denied', 'f-shop'),
                'description' => __('The status is assigned to orders that cannot be satisfied (for example, the product is not in stock). Later, at any time you can change the status of the order (for example, if the product is in stock);', 'f-shop'),
            ],
            'canceled' => [
                'name' => __('Canceled', 'f-shop'),
                'description' => __('The administrator assigns the status to the order if the client for some reason refused the order;', 'f-shop'),
            ],
            'return' => [
                'name' => __('Return', 'f-shop'),
                'description' => __('The administrator assigns the status to the order if the client for some reason returned the goods.', 'f-shop'),
            ],
            'black_list' => [
                'name' => __('Black list', 'f-shop'),
                'description' => __('This status is assigned to an order if violations were detected on the part of the customer. On subsequent orders, all orders from this IP, e-mail, phone automatically receive this status.', 'f-shop'),
            ],
        ];

        return apply_filters('fs_default_order_statuses', $order_statuses);
    }

    /**
     * Returns all registered order statuses from WordPress.
     * Gets statuses that are actually registered in WordPress system.
     *
     * @return array Array of registered statuses with 'name' and 'description' keys
     */
    public static function get_registered_order_statuses()
    {
        $registered_statuses = [];

        // Получаем все зарегистрированные статусы постов WordPress
        $wp_post_statuses = get_post_stati(['internal' => false], 'objects');

        foreach ($wp_post_statuses as $status_name => $status_object) {
            // Исключаем стандартные WordPress статусы, которые не относятся к заказам
            if (in_array($status_name, ['publish', 'draft', 'auto-draft', 'inherit'])) {
                continue;
            }

            $registered_statuses[$status_name] = [
                'name' => $status_object->label,
                'description' => isset($status_object->description) ? $status_object->description : '',
                'public' => $status_object->public,
                'show_in_admin_status_list' => $status_object->show_in_admin_status_list,
            ];
        }

        return apply_filters('fs_registered_order_statuses', $registered_statuses);
    }

    /**
     * Get the best available status name for display.
     * Checks WordPress registered statuses first, then defaults, then taxonomy.
     *
     * @param string $status
     *
     * @return string
     */
    public static function get_status_display_name($status)
    {
        // Спочатку шукаємо в зареєстрованих WordPress статусах
        $status_object = get_post_status_object($status);
        if ($status_object) {
            return $status_object->label;
        }

        // Якщо не знайдено в WordPress, шукаємо в дефолтних статусах F-Shop
        $default_statuses = self::default_order_statuses();
        if (isset($default_statuses[$status])) {
            return $default_statuses[$status]['name'];
        }

        // Якщо не знайдено в дефолтних, спробуємо отримати з таксономії
        $term = get_term_by('slug', $status, FS_Config::get_data('order_statuses_taxonomy'));
        if ($term && !is_wp_error($term)) {
            return $term->name;
        }

        // Якщо нічого не знайдено, повертаємо статус як є
        return ucfirst(str_replace(['-', '_'], ' ', $status));
    }

    /**
     * Метабокс отображает селект с статусами заказа и кнопку сохранения.
     */
    public function order_status_box()
    {
        global $post;
        echo '<p><span class="dashicons dashicons-calendar-alt"></span> '.esc_html__('Date of purchase', 'f-shop').': <b> '.esc_html(get_the_date('j.m.Y H:i')).'</b></p>';
        echo '<p><span class="dashicons dashicons-calendar-alt"></span> '.esc_html__('Last modified', 'f-shop').':  <b>'.esc_html(get_the_modified_date('j.m.Y H:i')).'</b></p>';
        $registered_statuses = self::get_registered_order_statuses();
        if ($registered_statuses) {
            echo '<p><label for="fs-post_status"><span class="dashicons dashicons-post-status"></span> '.esc_html__('Status').'</label>';
            echo '<p><select id="fs-post_status" name="post_status">';
            foreach ($registered_statuses as $key => $order_status) {
                echo '<option value="'.esc_attr($key).'" '.selected(get_post_status($post->ID), $key, 0).'>'.esc_attr($order_status['name']).'</option>';
            }
            echo '</select></p>';
        }

        echo '<p><input type="submit" name="save" id="save-order" value="'.esc_attr__('Save').'" class="button button-primary button-large"></p>';
        echo '<div class="clear"></div>';
        echo '<p><a class="submitdelete deletion" href="'.esc_url(get_delete_post_link($post->ID)).'">'.esc_html__('Delete').'</a></p>';
        echo '<div class="clear"></div>';
    }

    /**
     * Returns a single order information template.
     *
     * @return mixed|string|void
     */
    public function order_detail()
    {
        $order_id = intval($_GET['order_detail']);
        if (empty($order_id)) {
            return '<p class="fs-order-detail">'.esc_html__('Order number is not specified', 'f-shop').'</p>';
        }

        return fs_frontend_template('shortcode/order-detail', [
            'vars' => [
                'order' => FS_Orders::get_order($order_id),
                'payment' => new FS_Payment(),
            ],
        ]);
    }

    /**
     * Отображает заказы текущего пользователя в виде таблицы.
     *
     * @return string
     */
    public function list_orders()
    {
        if (!empty($_GET['order_detail']) && is_numeric($_GET['order_detail'])) {
            return self::order_detail();
        }

        return fs_frontend_template('shortcode/list-orders', ['vars' => ['orders' => self::get_user_orders()]]);
    }

    /**
     *  Это событие отправляет покупателю сведения об оплате выбранным способом
     *  срабатывает в момент изменения статуса заказа с "новый" на "обработан".
     */
    public function fs_change_order_status($new_status, $old_status, $post)
    {
        if ($new_status == $old_status || $post->post_type != FS_Config::get_data('post_type_orders')) {
            return;
        }

        $fs_config = new FS_Config();

        // Если новый статус заказа "обработан" (processed)
        if ($new_status == 'processed') {
            // Получаем ID выбраного способа оплаты
            $pay_method_id = get_post_meta($post->ID, '_payment', 1);
            // Получаем кастомное сообшение пользователю, извещение об возможности оплаты
            $message_no_filter = get_term_meta($pay_method_id, '_fs_pay_message', 1);
            $pay_term = get_term($pay_method_id, FS_Config::get_data('product_pay_taxonomy'));
            if (empty($message_no_filter)) {
                $message_no_filter = __('Your order #%order_id% has been successfully approved. The next stage is payment of the order. You can pay for the purchase by <a href="%pay_url%">link</a>. You have chosen the payment method: %pay_name%.
Good luck!', 'f-shop');
            }
            // Создаём ссылку для оплаты покупки
            $pay_link = add_query_arg([
                'pay_method' => $pay_term->slug,
                'order_id' => $post->ID,
            ], get_permalink(fs_option('page_payment')));
            $message = apply_filters('fs_pay_user_message', $message_no_filter);
            // Производим замену мета данных типа %var%
            $message = str_replace([
                '%order_id%',
                '%pay_url%',
                '%pay_name%',
            ], [
                $post->ID,
                esc_url($pay_link),
                $pay_term->name,
            ], $message);

            $message_decode = wp_specialchars_decode($message, ENT_QUOTES);
            $user_data = get_post_meta($post->ID, '_user', 1);

            if (is_email($user_data['email']) && !empty($message)) {
                wp_mail($user_data['email'], __('Your order is approved', 'f-shop'), $message_decode, $fs_config->email_headers());
            }
        }

        $order = new FS_Order($post->ID);
        $current_user = wp_get_current_user();

        $order->add_history_event($post->ID, [
            'id' => 'change_order_status',
            'initiator_id' => $current_user->ID,
            'initiator_name' => $current_user->display_name,
            'time' => current_time('timestamp'),
            'data' => [
                'status' => $new_status,
            ],
        ]);
    }

    public function true_status_display($statuses)
    {
        // check if screen is order list
        global $current_screen;
        if (!is_object($current_screen) || $current_screen->id != 'edit-shop_order') {
            return $statuses;
        } // end if
        global $post;
        $registered_statuses = self::get_registered_order_statuses();
        if ($registered_statuses) {
            foreach ($registered_statuses as $key => $status) {
                if (get_query_var('post_status') != $key) {
                    if ($post->post_status == $key) {
                        $statuses[] = $status['name'];
                    }
                }
            }
        }

        return $statuses;
    }

    /**
     * Registers default F-Shop order statuses in WordPress.
     * This method is called on admin_init to ensure default statuses are properly registered.
     */
    public function order_status_custom()
    {
        // Register default F-Shop statuses
        $default_statuses = self::default_order_statuses();

        if (count($default_statuses)) {
            foreach ($default_statuses as $key => $status) {
                // Skip if already registered by WordPress
                if (!get_post_status_object($key)) {
                    register_post_status($key, [
                        'label' => $status['name'],
                        'label_count' => _n_noop(
                            $status['name'].' <span class="count">(%s)</span>',
                            $status['name'].' <span class="count">(%s)</span>'
                        ),
                        'public' => true,
                        'show_in_admin_status_list' => true,
                    ]);
                }
            }
        }

        // Register statuses from taxonomy terms
        $this->register_taxonomy_statuses();

        // Also sync any existing statuses that might have been missed
        $this->sync_all_taxonomy_statuses();
    }

    /**
     * Registers post statuses for all terms in the order statuses taxonomy.
     * This ensures all custom statuses from taxonomy are available as WordPress post statuses.
     */
    public function register_taxonomy_statuses()
    {
        $taxonomy_terms = get_terms([
            'taxonomy' => FS_Config::get_data('order_statuses_taxonomy'),
            'hide_empty' => false,
        ]);

        if ($taxonomy_terms && !is_wp_error($taxonomy_terms)) {
            foreach ($taxonomy_terms as $term) {
                // Skip if already registered by WordPress
                if (!get_post_status_object($term->slug)) {
                    register_post_status($term->slug, [
                        'label' => $term->name,
                        'label_count' => _n_noop(
                            $term->name.' <span class="count">(%s)</span>',
                            $term->name.' <span class="count">(%s)</span>'
                        ),
                        'public' => true,
                        'show_in_admin_status_list' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Synchronizes all taxonomy terms with WordPress post statuses.
     * Ensures that all existing taxonomy terms have corresponding post statuses registered.
     */
    public function sync_all_taxonomy_statuses()
    {
        $taxonomy_terms = get_terms([
            'taxonomy' => FS_Config::get_data('order_statuses_taxonomy'),
            'hide_empty' => false,
        ]);

        if ($taxonomy_terms && !is_wp_error($taxonomy_terms)) {
            foreach ($taxonomy_terms as $term) {
                // Always register/update the post status for this term
                register_post_status($term->slug, [
                    'label' => $term->name,
                    'label_count' => _n_noop(
                        $term->name.' <span class="count">(%s)</span>',
                        $term->name.' <span class="count">(%s)</span>'
                    ),
                    'public' => true,
                    'show_in_admin_status_list' => true,
                ]);
            }
        }
    }

    /**
     * Добавляет зарегистрированные статусы постов  в выпадающий список
     * на странице редактирования заказа.
     */
    public function true_append_post_status_list()
    {
        global $post;
        if ($post->post_type == $this->post_type && !empty($this->order_statuses)) { ?>
            <script> jQuery(function ($) {
					<?php foreach ($this->order_statuses as $key => $status) { ?>
                    $('select#post_status').append("<option value=\"<?php echo esc_attr($key); ?>\" <?php selected($post->post_status, $key); ?>><?php echo esc_attr($status['name']); ?></option>");
					<?php if ($post->post_status == $key) { ?>
                    $('#post-status-display').text('<?php echo esc_attr($status['name']); ?>');
					<?php }
					}  ?>
                });</script>";
			<?php
        }
    }

    /**
     * Добавляет к обычному посту свойства или метаданные заказа.
     *
     * @return \stdClass
     */
    private static function set_order_data(
        $order_id
    ) {
        $order_meta = get_post_meta($order_id);
        $data = new self();

        $data->_products = self::get_order_items($order_id);

        if ($order_meta) {
            foreach ($order_meta as $key => $item) {
                $unserialize = @unserialize(array_shift($item));

                if (count($item) == 1 && $unserialize === false) {
                    $data->{$key} = get_post_meta($order_id, $key, 1);
                } else {
                    $data->{$key} = get_post_meta($order_id, $key, 0)[0];
                }
            }
        }

        return $data;
    }

    /**
     * Displays the object with the orders of an individual user, by default the current user.
     *
     * @param int    $user_id - User ID
     * @param string $status  - Order status id (same as post status)
     * @param array  $args    - array of arguments similar to WP_Query ()
     *
     * @return array|object|null object with orders
     */
    public static function get_user_orders(
        $user_id = 0, $status = 'any', $args = []
    ) {
        $user_id = $user_id ? $user_id : get_current_user_id();

        $args = wp_parse_args($args, [
            'post_type' => FS_Config::get_data('post_type_orders'),
            'post_status' => $status,
            'meta_key' => '_user_id',
            'meta_value' => $user_id,
        ]);

        $orders = get_posts($args);

        return array_filter($orders, function ($item) {
            return $item->post_status != 'trash';
        });
    }

    public static function get_order_items($order_id)
    {
        $order_id = (int) $order_id;
        $products = get_post_meta($order_id, '_products', 0);
        $products = $products[0];
        $item = [];

        if ($products) {
            foreach ($products as $id => $product) {
                $price = fs_get_price($id);
                $count = (int) $product['count'];
                $item[$id] = [
                    'id' => $id,
                    'price' => $price,
                    'name' => get_the_title($id),
                    'count' => $count,
                    'code' => fs_get_product_code($id),
                    'sum' => get_post_meta($id, '_amount', 1),
                    'image' => get_the_post_thumbnail_url($id, 'large'),
                    'link' => get_the_permalink($id),
                ];
            }
        }

        return $item;
    }

    /**
     * возвращает один заказ.
     *
     * @param int $order_id - ID заказа
     *
     * @return \stdClass
     */
    public static function get_order($order_id = 0)
    {
        if (!$order_id && self::get_last_order_id()) {
            $order_id = self::get_last_order_id();
        }

        if (!$order_id) {
            return null;
        }
        $order = get_post($order_id);
        if ($order) {
            $order->data = self::set_order_data($order_id);
        }

        return $order;
    }

    /**
     * подсчитывает общую сумму товаров в одном заказе.
     *
     * @return float $items_sum - стоимость всех товаров
     */
    public function fs_order_total(
        int $order_id
    ) {
        $item = [];
        $currency = fs_currency();
        $products = $this->get_order($order_id);
        if ($products) {
            foreach ($products as $product) {
                $item[$product->post_id] = $product->count * fs_get_price($product->post_id);
            }
            $items_sum = array_sum($item);
        }
        $items_sum = apply_filters('fs_price_format', $items_sum);
        $items_sum = $items_sum.' '.$currency;

        return $items_sum;
    }

    public static function delete_orders()
    {
        $fs_config = new FS_Config();
        $posts = new \WP_Query([
            'post_type' => [$fs_config->data['post_type_orders']],
            'posts_per_page' => -1,
        ]);
        if ($posts->have_posts()) {
            while ($posts->have_posts()) {
                $posts->the_post();
                global $post;
                wp_delete_post($post->ID, true);
            }
        }
    }

    /**
     * Creates the ability to search by meta-fields on the order page.
     */
    public function filter_orders_by_search($query)
    {
        global $wpdb, $pagenow, $typenow;
        // Если это не админка
        if (!is_admin()) {
            return;
        }

        if ($query->get('post_type') != FS_Config::get_data('post_type_orders')) {
            return;
        }

        $user_ids = [];

        if (!empty($_GET['s'])) {
            $order = new FS_Order();
            $customer_table = $order->get_customer_table();
            $s = $_GET['s'];
            $q = $wpdb->prepare("SELECT id FROM $customer_table WHERE phone ='%s' OR email = '%s'  OR first_name LIKE '%s' OR last_name LIKE '%s'", $s, $s, '%'.$s.'%', '%'.$s.'%');
            $results = $wpdb->get_results($q);

            if ($results) {
                foreach ($results as $result) {
                    $user_ids[] = $result->id;
                }
                $user_ids = array_unique($user_ids);
                $query->set('s', false);
            }
        }

        if (!empty($_GET['customer_id'])) {
            array_push($user_ids, absint($_GET['customer_id']));
            $query->set('s', false);
        }

        if (!empty($user_ids)) {
            $meta_query[] = [
                'key' => '_customer_id',
                'value' => $user_ids,
                'compare' => 'IN',
                'type' => 'CHAR',
            ];
            $query->set('meta_query', $meta_query);
        }

        $query->set('post_type', 'orders');

        return $query;
    }

    /**
     * @return array
     */
    public function get_order_fields()
    {
        return $this->order_fields;
    }

    public function set_order_fields(array $order_fields)
    {
        $this->order_fields = $order_fields;
    }

    /**
     * Добавляем метабокс к заказам
     */
    public function register_order_meta_box()
    {
        remove_meta_box('order-statusesdiv', 'orders', 'side');

        add_meta_box(
            sprintf('fast_shop_%s_user_metabox', 'orders'),
            __('Order data', 'f-shop'),
            [&$this, 'edit_add_order_meta_box'],
            'orders',
            'normal',
            'default'
        );
    }

    /* метабокс данных пользователя в редактировании заказа */
    public function edit_add_order_meta_box($post)
    {
        $screen = get_current_screen();
        if ($screen->id != 'orders') {
            return;
        }
        global $wpdb;
        $order = new FS_Order($post->ID);
        $action = $screen->action ?: (isset($_GET['action']) ? $_GET['action'] : 'edit');
        $shipping_methods = get_terms([
            'taxonomy' => FS_Config::get_data('product_del_taxonomy'),
            'hide_empty' => false,
        ]);
        $payment_methods = get_terms([
            'taxonomy' => FS_Config::get_data('product_pay_taxonomy'),
            'hide_empty' => false,
        ]);

        require FS_PLUGIN_PATH.'templates/back-end/metabox/order/'.$action.'.php';
    }

    /**
     *  Клонирование заказа.
     *
     * @return \WP_Error|int
     */
    public static function clone_order($order_id)
    {
        $post = get_post($order_id);
        if (!$post) {
            return new \WP_Error('not_found', 'Order not found');
        }

        $new_order_id = wp_insert_post([
            'post_title' => $post->post_title,
            'post_status' => 'new',
            'post_type' => FS_Config::get_data('post_type_orders'),
            'post_author' => $post->post_author,
        ]);

        global $wpdb;

        // clone all meta fields to new order
        $meta_fields = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = $order_id");
        foreach ($meta_fields as $meta_field) {
            $wpdb->insert($wpdb->postmeta, [
                'meta_key' => $meta_field->meta_key,
                'meta_value' => $meta_field->meta_value,
                'meta_id' => null,
                'post_id' => $new_order_id,
            ]);
        }

        $user = get_post_meta($new_order_id, '_user', true);

        /* обновляем название заказа для админки */
        wp_update_post([
            'ID' => $new_order_id,
            'post_title' => sprintf(
                __('Order #%d from %s %s (%s)', 'f-shop'),
                $new_order_id, $user['fs_first_name'], $user['fs_last_name'], date('d.m.y H:i', time())),
        ]
        );

        do_action('fs_create_order', $new_order_id);

        return $new_order_id;
    }

    /**
     * Add dropdown filter for order statuses.
     */
    public function add_order_status_filter()
    {
        global $typenow;

        if ($typenow == 'orders') {
            $current_status = isset($_GET['order_status_filter']) ? $_GET['order_status_filter'] : '';
            $statuses = self::default_order_statuses();

            echo '<select name="order_status_filter">';
            echo '<option value="">'.__('All statuses', 'f-shop').'</option>';

            foreach ($statuses as $status_key => $status_data) {
                $selected = selected($current_status, $status_key, false);
                echo '<option value="'.esc_attr($status_key).'"'.$selected.'>'.esc_html($status_data['name']).'</option>';
            }

            echo '</select>';
        }
    }

    /**
     * Filter orders by status.
     */
    public function filter_orders_by_status($query)
    {
        global $pagenow, $typenow;

        if ($pagenow == 'edit.php' && $typenow == 'orders' && isset($_GET['order_status_filter']) && $_GET['order_status_filter'] != '') {
            $query->query_vars['post_status'] = $_GET['order_status_filter'];
        }
    }
}
