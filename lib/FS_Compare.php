<?php

namespace FS;

/**
 * Class for managing the Comparison functionality in F-Shop.
 */
class FS_Compare
{
    private const SESSION_COMPARE_KEY = 'fs_compare_list';

    public function __construct()
    {
        //  Add to compare
        add_action('wp_ajax_fs_addto_compare', [$this, 'fs_add_to_compare']);
        add_action('wp_ajax_nopriv_fs_addto_compare', [$this, 'fs_add_to_compare']);

        // Remove from compare
        add_action('wp_ajax_fs_del_compare_pos', [$this, 'remove_from_compare']);
        add_action('wp_ajax_nopriv_fs_del_compare_pos', [$this, 'remove_from_compare']);

        // Clean Compare list
        add_action('wp_ajax_fs_clean_compare', [$this, 'fs_clean_compare']);
        add_action('wp_ajax_nopriv_fs_clean_compare', [$this, 'fs_clean_compare']);

        // Get Compare list
        add_action('wp_ajax_fs_get_compare', [$this, 'ajax_get_compare']);
        add_action('wp_ajax_nopriv_fs_get_compare', [$this, 'ajax_get_compare']);
    }

    /**
     * Retrieves the items in the comparison list.
     */
    public static function get_compare_items(): array
    {
        $compare = $_SESSION[self::SESSION_COMPARE_KEY] ?? [];

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user_compare = get_user_meta($user_id, 'fs_compare_list', true);

            if (is_array($user_compare)) {
                $compare = array_unique(array_merge($compare, $user_compare));
            }
        }

        return is_array($compare) ? array_values($compare) : [];
    }

    /**
     * Checks if a product is in the comparison list.
     */
    public static function contains(int $product_id): bool
    {
        return in_array($product_id, self::get_compare_items());
    }

    /**
     * Returns the number of products in the comparison list.
     */
    public static function get_compare_count(): int
    {
        return count(self::get_compare_items());
    }

    /**
     * AJAX handler to get compare list data.
     */
    public function ajax_get_compare()
    {
        wp_send_json_success([
            'items' => self::get_compare_items(),
            'count' => self::get_compare_count(),
        ]);
    }

    /**
     * Adds a product to the comparison list.
     */
    public function fs_add_to_compare()
    {
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Security check failed', 'f-shop')]);
        }

        $productId = (int) $_REQUEST['product_id'];

        if (!get_post_status($productId) || get_post_type($productId) !== FS_Config::get_data('post_type')) {
            wp_send_json_error(['msg' => __('The product does not exist.', 'f-shop')]);
        }

        $product = [
            'id' => $productId,
            'title' => get_the_title($productId),
            'permalink' => get_permalink($productId),
            'thumbnail_url' => get_the_post_thumbnail_url($productId, 'thumbnail'),
            'cost' => fs_get_price($productId),
            'cost_display' => apply_filters('fs_price_format', fs_get_price($productId)),
            'sku' => fs_get_product_code($productId),
        ];

        if (self::contains($productId)) {
            self::delete_from_compare($productId);

            wp_send_json_success([
                'action' => 'remove',
                'msg' => __('Product removed from comparison list', 'f-shop'),
                'product' => $product,
            ]);

            return;
        }

        // Limit to 4 products
        if (self::get_compare_count() >= 4) {
            wp_send_json_error(['msg' => __('Maximum 4 products allowed in comparison', 'f-shop')]);
        }

        $this->update_user_compare(get_current_user_id(), $productId);

        wp_send_json_success([
            'action' => 'add',
            'msg' => sprintf(__('Item &laquo;%s&raquo; successfully added to comparison list.', 'f-shop'), get_the_title($productId)),
            'product' => $product,
        ]);
    }

    /**
     * Updates the user's compare list.
     */
    private function update_user_compare(int $userId, int $productId): bool
    {
        if (!$userId) {
            if (!isset($_SESSION[self::SESSION_COMPARE_KEY])) {
                $_SESSION[self::SESSION_COMPARE_KEY] = [];
            }
            $_SESSION[self::SESSION_COMPARE_KEY][] = $productId;
            $_SESSION[self::SESSION_COMPARE_KEY] = array_unique($_SESSION[self::SESSION_COMPARE_KEY]);

            return true;
        }

        $userCompare = get_user_meta($userId, 'fs_compare_list', true);
        $userCompare = is_array($userCompare) ? $userCompare : [];

        if (in_array($productId, $userCompare)) {
            return false;
        }

        $userCompare[] = $productId;
        update_user_meta($userId, 'fs_compare_list', $userCompare);

        return true;
    }

    /**
     * Removes a product from the comparison list.
     */
    public function remove_from_compare()
    {
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Security check failed', 'f-shop')]);
        }

        $product_id = (int) $_REQUEST['item_id'];

        self::delete_from_compare($product_id);

        wp_send_json_success([
            'msg' => __('Product removed from your comparison list.', 'f-shop'),
            'status' => true,
        ]);
    }

    /**
     * Cleans the entire comparison list.
     */
    public function fs_clean_compare()
    {
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Security check failed', 'f-shop')]);
        }

        unset($_SESSION[self::SESSION_COMPARE_KEY]);

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            delete_user_meta($user_id, 'fs_compare_list');
        }

        wp_send_json_success();
    }

    /**
     * Deletes specific product from compare list.
     */
    public static function delete_from_compare(int $product_id): bool
    {
        $compare = self::get_compare_items();

        if (!in_array($product_id, $compare)) {
            return false;
        }

        $key = array_search($product_id, $compare);
        unset($compare[$key]);
        $compare = array_values($compare);

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, 'fs_compare_list', $compare);
        }

        $_SESSION[self::SESSION_COMPARE_KEY] = $compare;

        return true;
    }
}
