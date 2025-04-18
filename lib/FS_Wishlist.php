<?php

namespace FS;

/**
 * Class for managing the Wishlist functionality in F-Shop.
 *
 * This class provides wishlist functionality, allowing users to:
 * - Add products to wishlist
 * - Remove products from wishlist
 * - View wishlist items
 * - Clear the entire wishlist
 *
 * The wishlist is stored in two ways:
 * - In session for non-logged-in users
 * - In user metadata for logged-in users
 */
class FS_Wishlist
{
    private const SESSION_WISHLIST_KEY = 'fs_wishlist';

    public function __construct()
    {
        //  Add to wishlist
        add_action('wp_ajax_fs_addto_wishlist', [$this, 'fs_add_to_wishlist']);
        add_action('wp_ajax_nopriv_fs_addto_wishlist', [$this, 'fs_add_to_wishlist']);

        // Remove from wish list
        add_action('wp_ajax_fs_del_wishlist_pos', [$this, 'remove_from_wishlist']);
        add_action('wp_ajax_nopriv_fs_del_wishlist_pos', [$this, 'remove_from_wishlist']);

        // Clean Wishlist
        add_action('wp_ajax_fs_clean_wishlist', [$this, 'fs_clean_wishlist']);
        add_action('wp_ajax_nopriv_fs_clean_wishlist', [$this, 'fs_clean_wishlist']);
    }

    /**
     * Retrieves the items in the wishlist.
     *
     * Merges items from session and user metadata if the user is logged in
     *
     * @return array Array of product IDs in the wishlist
     */
    public static function get_wishlist_items(): array
    {
        $wishlist = $_SESSION[self::SESSION_WISHLIST_KEY] ?? [];

        if (is_user_logged_in()) {
            $user_id = get_current_user_id(); // Get the current user's ID
            $user_wishlist = get_user_meta($user_id, 'fs_wishlist', true); // Get items from the user's meta field

            // Ensure the meta field data is an array
            if (is_array($user_wishlist)) {
                $wishlist = array_unique(array_merge($wishlist, $user_wishlist));
            }
        }

        return is_array($wishlist) ? $wishlist : [];
    }

    /**
     * Retrieves the products from the wishlist.
     *
     * Returns complete product post objects that are in the wishlist
     *
     * @return array Array of product post objects
     */
    public static function get_wishlist_products(): array
    {
        $wishlist = self::get_wishlist_items();

        if (empty($wishlist)) {
            return [];
        }

        // Retrieve wishlist products
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'post__in' => $wishlist, // Use IDs from the wishlist
        ];

        return get_posts($args);
    }

    /**
     * Checks if a product is in the wishlist.
     *
     * @param int $product_id Product ID to check
     *
     * @return bool true if product is in wishlist, false otherwise
     */
    public static function contains(int $product_id): bool
    {
        return in_array($product_id, self::get_wishlist_items(), true);
    }

    /**
     * Returns the number of products in the wishlist.
     *
     * @return int Number of products
     */
    public static function get_wishlist_count(): int
    {
        return count(self::get_wishlist_items());
    }

    /**
     * Adds a product to the wishlist.
     *
     * Handles AJAX request to add product. If product already exists - removes it
     *
     * @return void Sends JSON response
     */
    public function fs_add_to_wishlist()
    {
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Security check failed', 'f-shop')]);
        }

        $productId = (int) $_REQUEST['product_id'];

        // Check if product exists
        if (!get_post_status($productId) || get_post_type($productId) !== FS_Config::get_data('post_type')) {
            wp_send_json_error(['msg' => __('The product does not exist.', 'f-shop')]);
        }

        // Получаем полную информацию о товаре
        $product = [
            'id' => $productId,
            'title' => get_the_title($productId),
            'permalink' => get_permalink($productId),
            'thumbnail_url' => get_the_post_thumbnail_url($productId, 'thumbnail'),
            'cost' => fs_get_price($productId),
            'cost_display' => apply_filters('fs_price_format', fs_get_price($productId)),
            'sku' => fs_get_product_code($productId),
        ];

        // Check if the product is already in the wishlist
        if (self::contains($productId)) {
            self::delete_from_wishlist($productId);

            wp_send_json_success([
                'action' => 'remove',
                'msg' => __('Product removed from wishlist', 'f-shop'),
                'product' => $product,
            ]);

            return;
        }

        $this->update_user_wishlist(get_current_user_id(), $productId);

        wp_send_json_success([
            'action' => 'add',
            'msg' => str_replace(['%product%', '%wishlist_url%'], [
                get_the_title($productId),
                fs_wishlist_url(),
            ], __('Item &laquo;%product%&raquo; successfully added to wishlist. <a href="%wishlist_url%">Go to wishlist</a>', 'f-shop')),
            'product' => $product,
        ]);
    }

    /**
     * Updates the user's wishlist.
     *
     * @param int $userId    User ID
     * @param int $productId Product ID
     *
     * @return bool Operation result
     */
    private function update_user_wishlist(int $userId, int $productId): bool
    {
        if (!$userId) {
            $_SESSION[self::SESSION_WISHLIST_KEY][$productId] = $productId;

            return true; // Product successfully added to session
        }

        $userWishlist = get_user_meta($userId, 'fs_wishlist', true);
        $userWishlist = is_array($userWishlist) ? $userWishlist : [];

        if (in_array($productId, $userWishlist, true)) {
            return false; // Product already in wishlist
        }

        $userWishlist[] = $productId;
        update_user_meta($userId, self::SESSION_WISHLIST_KEY, $userWishlist);

        return true; // Product successfully added
    }

    /**
     * Removes a product from the wishlist.
     *
     * Handles AJAX request to remove specific product
     *
     * @return void Sends JSON response
     */
    public function remove_from_wishlist()
    {
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Security check failed', 'f-shop')]);
        }

        $product_id = (int) $_REQUEST['item_id'];

        self::delete_from_wishlist($product_id);

        wp_send_json_success([
            'msg' => __('Product removed from your wishlist.', 'f-shop'),
            'status' => true,
        ]);
    }

    /**
     * Cleans the entire wishlist.
     *
     * Removes all products from session and user metadata
     *
     * @return void Sends JSON response
     */
    public function fs_clean_wishlist()
    {
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Security check failed', 'f-shop')]);
        }

        unset($_SESSION['fs_wishlist']);

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            delete_user_meta($user_id, 'fs_wishlist');
        }

        wp_send_json_success();
    }

    /**
     * Deletes specific product from wishlist.
     *
     * @param int $product_id Product ID to delete
     *
     * @return bool true if product was successfully removed, false otherwise
     */
    public static function delete_from_wishlist(int $product_id): bool
    {
        $wishlist = self::get_wishlist_items();

        // Check if the product exists in the wishlist
        if (!in_array($product_id, $wishlist, true)) {
            return false; // Product is not in the wishlist
        }

        // Remove the product from the wishlist
        $key = array_search($product_id, $wishlist);
        unset($wishlist[$key]);

        // If the user is logged in, update their wishlist in user meta
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, self::SESSION_WISHLIST_KEY, $wishlist);
        }

        // Update session-based wishlist
        $_SESSION[self::SESSION_WISHLIST_KEY] = $wishlist;

        return true; // Product successfully removed
    }
}
