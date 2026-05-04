<?php

namespace FS;

class FS_Blacklist_List extends \WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => __('Blacklist entry', 'f-shop'),
            'plural' => __('Blacklist', 'f-shop'),
            'ajax' => false,
        ]);
    }

    public static function get_entries($per_page = 30, $page_number = 1)
    {
        global $wpdb;

        $table_name = FS_Blacklist::get_table_name();
        $sql = "SELECT * FROM {$table_name}";
        $allowed_fields = ['id', 'email', 'phone', 'ip'];

        if (!empty($_REQUEST['s']) && !empty($_REQUEST['field']) && in_array($_REQUEST['field'], $allowed_fields, true)) {
            $field = sanitize_key(wp_unslash($_REQUEST['field']));
            $search = wp_unslash($_REQUEST['s']);

            if ($field === 'id') {
                $sql .= $wpdb->prepare(' WHERE id = %d', absint($search));
            } else {
                if ($field === 'phone') {
                    $search = FS_Blacklist::prepare_phone($search);
                } elseif ($field === 'email') {
                    $search = FS_Blacklist::prepare_email($search);
                } elseif ($field === 'ip') {
                    $search = FS_Blacklist::prepare_ip($search);
                }

                $sql .= $wpdb->prepare(" WHERE {$field} LIKE %s", '%'.$wpdb->esc_like($search).'%');
            }
        }

        if (!empty($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], ['id', 'email', 'phone', 'ip', 'created_at'], true)) {
            $order = !empty($_REQUEST['order']) && strtoupper((string) $_REQUEST['order']) === 'ASC' ? 'ASC' : 'DESC';
            $sql .= ' ORDER BY '.sanitize_key(wp_unslash($_REQUEST['orderby'])).' '.$order;
        } else {
            $sql .= ' ORDER BY id DESC';
        }

        $sql .= ' LIMIT %d OFFSET %d';

        return $wpdb->get_results(
            $wpdb->prepare($sql, $per_page, ($page_number - 1) * $per_page),
            ARRAY_A
        );
    }

    public static function delete_entry($id): void
    {
        global $wpdb;

        $wpdb->delete(
            FS_Blacklist::get_table_name(),
            ['id' => absint($id)],
            ['%d']
        );
    }

    public static function record_count()
    {
        global $wpdb;

        return $wpdb->get_var('SELECT COUNT(*) FROM '.FS_Blacklist::get_table_name());
    }

    public function no_items()
    {
        esc_html_e('Blacklist is empty.', 'f-shop');
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'source_order_id':
                if (empty($item['source_order_id'])) {
                    return '&mdash;';
                }

                $edit_link = admin_url('post.php?post='.absint($item['source_order_id']).'&action=edit');

                return '<a href="'.esc_url($edit_link).'">#'.absint($item['source_order_id']).'</a>';
            case 'created_at':
                return esc_html(mysql2date('d.m.Y H:i', $item['created_at']));
            case 'email':
            case 'phone':
            case 'ip':
                return $item[$column_name] ? esc_html($item[$column_name]) : '&mdash;';
            default:
                return esc_html((string) $item[$column_name]);
        }
    }

    public function column_email($item)
    {
        $delete_nonce = wp_create_nonce('fs_delete_blacklist_entry');
        $title = $item['email'] ? esc_html($item['email']) : '&mdash;';
        $actions = [
            'delete' => sprintf(
                '<a href="?page=%s&post_type=orders&action=%s&entry=%d&_wpnonce=%s">%s</a>',
                esc_attr($_REQUEST['page']),
                'delete',
                absint($item['id']),
                $delete_nonce,
                esc_html__('Delete', 'f-shop')
            ),
        ];

        return $title.$this->row_actions($actions);
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="bulk-delete[]" value="%d" />', absint($item['id']));
    }

    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox" />',
            'id' => __('ID', 'f-shop'),
            'email' => __('E-mail', 'f-shop'),
            'phone' => __('Phone number', 'f-shop'),
            'ip' => __('IP address', 'f-shop'),
            'source_order_id' => __('Order', 'f-shop'),
            'created_at' => __('Created', 'f-shop'),
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id' => ['id', true],
            'email' => ['email', false],
            'phone' => ['phone', false],
            'ip' => ['ip', false],
            'created_at' => ['created_at', false],
        ];
    }

    public function get_bulk_actions()
    {
        return [
            'bulk-delete' => __('Delete', 'f-shop'),
        ];
    }

    public function search_box($text, $input_id)
    {
        $input_id = $input_id.'-search-input';
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>"><?php echo esc_html($text); ?>:</label>
            <select name="field">
                <option value="id" <?php selected(isset($_REQUEST['field']) ? $_REQUEST['field'] : '', 'id'); ?>><?php esc_html_e('ID', 'f-shop'); ?></option>
                <option value="email" <?php selected(isset($_REQUEST['field']) ? $_REQUEST['field'] : '', 'email'); ?>><?php esc_html_e('E-mail', 'f-shop'); ?></option>
                <option value="phone" <?php selected(isset($_REQUEST['field']) ? $_REQUEST['field'] : '', 'phone'); ?>><?php esc_html_e('Phone number', 'f-shop'); ?></option>
                <option value="ip" <?php selected(isset($_REQUEST['field']) ? $_REQUEST['field'] : '', 'ip'); ?>><?php esc_html_e('IP address', 'f-shop'); ?></option>
            </select>
            <input type="search" id="<?php echo esc_attr($input_id); ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button($text, '', '', false, ['id' => 'search-submit']); ?>
        </p>
        <?php
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();
        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('blacklist_per_page', 30);
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
        ]);

        $this->items = self::get_entries($per_page, $current_page);
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '';

            if (!wp_verify_nonce($nonce, 'fs_delete_blacklist_entry')) {
                wp_die(esc_html__('Security check failed.', 'f-shop'));
            }

            if (isset($_REQUEST['entry'])) {
                self::delete_entry(absint($_REQUEST['entry']));
            }
        }

        if (
            (isset($_REQUEST['action']) && $_REQUEST['action'] === 'bulk-delete')
            || (isset($_REQUEST['action2']) && $_REQUEST['action2'] === 'bulk-delete')
        ) {
            $delete_ids = isset($_REQUEST['bulk-delete']) ? (array) wp_unslash($_REQUEST['bulk-delete']) : [];

            foreach ($delete_ids as $id) {
                self::delete_entry(absint($id));
            }
        }
    }
}
