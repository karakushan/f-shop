<?php

namespace FS;

if (!defined('ABSPATH')) {
    exit;
}

class FS_Blacklist
{
    const DB_VERSION = '1.0';

    /**
     * @var FS_Blacklist_List
     */
    private $blacklist;

    public function __construct()
    {
        add_action('init', [$this, 'maybe_upgrade_table']);
        add_action('admin_menu', [$this, 'blacklist_list_menu_item']);
        add_action('admin_notices', [$this, 'render_notice']);
        add_action('admin_post_fs_blacklist_save', [$this, 'handle_save_request']);
        add_action('admin_post_fs_blacklist_add_order', [$this, 'handle_add_order_request']);
        add_action('post_submitbox_misc_actions', [$this, 'render_order_submitbox_action']);
    }

    public static function get_table_name(): string
    {
        global $wpdb;

        return $wpdb->prefix.'fs_blacklist';
    }

    public function maybe_upgrade_table(): void
    {
        $current_db_version = get_option('fs_blacklist_db_version', '0');

        if (version_compare($current_db_version, self::DB_VERSION, '>=')) {
            return;
        }

        global $wpdb;

        require_once ABSPATH.'wp-admin/includes/upgrade.php';

        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL auto_increment,
            email varchar(100) DEFAULT NULL,
            phone varchar(50) DEFAULT NULL,
            ip varchar(45) DEFAULT NULL,
            source_order_id bigint(20) unsigned DEFAULT NULL,
            created_by bigint(20) unsigned DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY email (email),
            KEY phone (phone),
            KEY ip (ip),
            KEY source_order_id (source_order_id)
        ) {$charset_collate};";

        dbDelta($sql);

        update_option('fs_blacklist_db_version', self::DB_VERSION);
    }

    public function blacklist_list_menu_item(): void
    {
        $hook = add_submenu_page(
            'edit.php?post_type=orders',
            __('Blacklist', 'f-shop'),
            __('Blacklist', 'f-shop'),
            'manage_options',
            'fs-blacklist',
            [$this, 'blacklist_settings_page']
        );

        add_action("load-$hook", [$this, 'screen_option']);
    }

    public function screen_option(): void
    {
        add_screen_option('per_page', [
            'label' => __('Blacklist', 'f-shop'),
            'default' => 30,
            'option' => 'blacklist_per_page',
        ]);

        $this->blacklist = new FS_Blacklist_List();
    }

    public function blacklist_settings_page(): void
    {
        if (!$this->blacklist) {
            $this->blacklist = new FS_Blacklist_List();
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Blacklist', 'f-shop'); ?></h1>

            <h2><?php esc_html_e('Add manually', 'f-shop'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 16px 0 24px;">
                <?php wp_nonce_field('fs_blacklist_save'); ?>
                <input type="hidden" name="action" value="fs_blacklist_save">
                <input type="hidden" name="redirect_to" value="<?php echo esc_url($this->get_blacklist_page_url()); ?>">
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row"><label for="fs_blacklist_email"><?php esc_html_e('E-mail', 'f-shop'); ?></label></th>
                        <td><input type="email" class="regular-text" id="fs_blacklist_email" name="email"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fs_blacklist_phone"><?php esc_html_e('Phone number', 'f-shop'); ?></label></th>
                        <td><input type="text" class="regular-text" id="fs_blacklist_phone" name="phone"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fs_blacklist_ip"><?php esc_html_e('IP address', 'f-shop'); ?></label></th>
                        <td><input type="text" class="regular-text" id="fs_blacklist_ip" name="ip"></td>
                    </tr>
                    </tbody>
                </table>
                <?php submit_button(__('Add to blacklist', 'f-shop')); ?>
            </form>

            <hr>

            <form method="get" action="">
                <input type="hidden" name="page" value="fs-blacklist">
                <input type="hidden" name="post_type" value="orders">
                <?php
                $this->blacklist->search_box(__('Find', 'f-shop'), 'search_blacklist');
                $this->blacklist->prepare_items();
                $this->blacklist->display();
                ?>
            </form>
        </div>
        <?php
    }

    public function handle_save_request(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage the blacklist.', 'f-shop'));
        }

        check_admin_referer('fs_blacklist_save');

        $redirect_to = !empty($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : $this->get_blacklist_page_url();
        $result = self::upsert_entry([
            'email' => isset($_POST['email']) ? wp_unslash($_POST['email']) : '',
            'phone' => isset($_POST['phone']) ? wp_unslash($_POST['phone']) : '',
            'ip' => isset($_POST['ip']) ? wp_unslash($_POST['ip']) : '',
        ]);

        if (is_wp_error($result)) {
            wp_safe_redirect(add_query_arg([
                'fs_blacklist_notice' => $result->get_error_message(),
                'fs_blacklist_notice_type' => 'error',
            ], $redirect_to));
            exit;
        }

        wp_safe_redirect(add_query_arg([
            'fs_blacklist_notice' => __('Entry added to blacklist.', 'f-shop'),
            'fs_blacklist_notice_type' => 'success',
        ], $redirect_to));
        exit;
    }

    public function handle_add_order_request(): void
    {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have permission to edit orders.', 'f-shop'));
        }

        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        check_admin_referer('fs_blacklist_add_order_'.$order_id);

        $result = self::add_order_to_blacklist($order_id);
        $redirect_to = $order_id ? admin_url('post.php?post='.$order_id.'&action=edit') : $this->get_blacklist_page_url();

        if (is_wp_error($result)) {
            wp_safe_redirect(add_query_arg([
                'fs_blacklist_notice' => $result->get_error_message(),
                'fs_blacklist_notice_type' => 'error',
            ], $redirect_to));
            exit;
        }

        wp_safe_redirect(add_query_arg([
            'fs_blacklist_notice' => __('Client added to blacklist.', 'f-shop'),
            'fs_blacklist_notice_type' => 'success',
        ], $redirect_to));
        exit;
    }

    public function render_order_submitbox_action(): void
    {
        global $post;

        if (!$post instanceof \WP_Post || $post->post_type !== FS_Config::get_data('post_type_orders')) {
            return;
        }

        $order = new FS_Order($post->ID);
        $identity = self::normalize_identity([
            'email' => $order->user['email'] ?? '',
            'phone' => $order->user['phone'] ?? '',
            'ip' => $order->user['ip'] ?? '',
        ]);
        $matches = self::find_matches($identity);

        echo '<div class="misc-pub-section">';
        echo '<span class="dashicons dashicons-dismiss" style="margin-top:2px;"></span> ';

        if (!self::has_identity($identity)) {
            echo esc_html__('Save order with client data first to add it to blacklist.', 'f-shop');
        } elseif ($matches) {
            echo '<strong>'.esc_html__('Client is in blacklist', 'f-shop').'</strong><br>';
            echo '<a href="'.esc_url($this->get_blacklist_page_url()).'">'.esc_html__('Open blacklist', 'f-shop').'</a>';
        } else {
            $url = wp_nonce_url(
                admin_url('admin-post.php?action=fs_blacklist_add_order&order_id='.$post->ID),
                'fs_blacklist_add_order_'.$post->ID
            );

            echo '<a class="button" href="'.esc_url($url).'">'.esc_html__('Add client to blacklist', 'f-shop').'</a>';
        }

        echo '</div>';
    }

    public static function prepare_email($email): string
    {
        return sanitize_email(strtolower(trim((string) $email)));
    }

    public static function prepare_phone($phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone);
    }

    public static function prepare_ip($ip): string
    {
        return trim((string) $ip);
    }

    public static function normalize_identity(array $identity): array
    {
        return [
            'email' => self::prepare_email($identity['email'] ?? ''),
            'phone' => self::prepare_phone($identity['phone'] ?? ''),
            'ip' => self::prepare_ip($identity['ip'] ?? ''),
        ];
    }

    public static function has_identity(array $identity): bool
    {
        return !empty($identity['email']) || !empty($identity['phone']) || !empty($identity['ip']);
    }

    public static function find_matches(array $identity): array
    {
        global $wpdb;

        $identity = self::normalize_identity($identity);
        if (!self::has_identity($identity)) {
            return [];
        }

        $table_name = self::get_table_name();
        $where = [];
        $values = [];

        foreach (['email', 'phone', 'ip'] as $field) {
            if (!empty($identity[$field])) {
                $where[] = "{$field} = %s";
                $values[] = $identity[$field];
            }
        }

        $sql = "SELECT * FROM {$table_name} WHERE ".implode(' OR ', $where).' ORDER BY id DESC';

        return $wpdb->get_results($wpdb->prepare($sql, $values), ARRAY_A);
    }

    public static function get_matching_entry(array $identity): ?array
    {
        $matches = self::find_matches($identity);

        return !empty($matches) ? $matches[0] : null;
    }

    public static function upsert_entry(array $identity, int $source_order_id = 0)
    {
        global $wpdb;

        $identity = self::normalize_identity($identity);
        if (!self::has_identity($identity)) {
            return new \WP_Error('fs_blacklist_empty_identity', __('Specify at least one value: e-mail, phone, or IP.', 'f-shop'));
        }

        $table_name = self::get_table_name();
        $existing = self::get_matching_entry($identity);

        $data = [
            'email' => $identity['email'] ?: null,
            'phone' => $identity['phone'] ?: null,
            'ip' => $identity['ip'] ?: null,
            'source_order_id' => $source_order_id ?: null,
            'created_by' => get_current_user_id() ?: null,
        ];

        if ($existing) {
            $update_data = [
                'email' => $data['email'] ?: $existing['email'],
                'phone' => $data['phone'] ?: $existing['phone'],
                'ip' => $data['ip'] ?: $existing['ip'],
                'source_order_id' => $data['source_order_id'] ?: $existing['source_order_id'],
            ];

            $wpdb->update(
                $table_name,
                $update_data,
                ['id' => absint($existing['id'])],
                ['%s', '%s', '%s', '%d'],
                ['%d']
            );

            return absint($existing['id']);
        }

        $inserted = $wpdb->insert(
            $table_name,
            $data,
            ['%s', '%s', '%s', '%d', '%d']
        );

        if (!$inserted) {
            return new \WP_Error('fs_blacklist_insert_failed', __('Failed to save blacklist entry.', 'f-shop'));
        }

        return (int) $wpdb->insert_id;
    }

    public static function add_order_to_blacklist(int $order_id)
    {
        if (!$order_id) {
            return new \WP_Error('fs_blacklist_order_missing', __('Order not found.', 'f-shop'));
        }

        $order = new FS_Order($order_id);
        $identity = self::normalize_identity([
            'email' => $order->user['email'] ?? '',
            'phone' => $order->user['phone'] ?? '',
            'ip' => $order->user['ip'] ?? '',
        ]);

        return self::upsert_entry($identity, $order_id);
    }

    public function get_blacklist_page_url(): string
    {
        return admin_url('edit.php?post_type=orders&page=fs-blacklist');
    }

    public function render_notice(): void
    {
        if (empty($_GET['fs_blacklist_notice'])) {
            return;
        }

        $type = !empty($_GET['fs_blacklist_notice_type']) ? sanitize_key(wp_unslash($_GET['fs_blacklist_notice_type'])) : 'success';
        $type_class = $type === 'error' ? 'notice notice-error' : 'notice notice-success';
        $message = sanitize_text_field(wp_unslash($_GET['fs_blacklist_notice']));

        echo '<div class="'.esc_attr($type_class).' is-dismissible"><p>'.esc_html($message).'</p></div>';
    }
}
