<?php

namespace FS;

/**
 * Class FS_Currency_Price.
 *
 * Handles product price conversion and storage in different currencies
 * to avoid on-the-fly conversion and improve sorting/filtering performance.
 */
class FS_Currency_Price
{
    /**
     * Constructor - registers hooks.
     */
    public function __construct()
    {
        // Save prices in all supported currencies when product is saved
        add_action('save_post_' . FS_Config::get_data('post_type'), [$this, 'update_product_prices'], 20, 3);

        // Update all product prices when currency rates change
        add_action('edited_' . FS_Config::get_data('currencies_taxonomy'), [$this, 'schedule_update_all_products'], 10, 2);

        add_action('fs_update_all_prices', [$this, 'update_all_products']);

        // fs_update_currencies
        add_action('wp_ajax_fs_update_currencies', [$this, 'update_all_products']);
        add_action('wp_ajax_nopriv_fs_update_currencies', [$this, 'update_all_products']);
    }

    /**
     * Schedule update of all products when currency rates change.
     *
     * @param int $term_id Term ID
     * @param int $tt_id Term taxonomy ID
     */
    public function schedule_update_all_products($term_id, $tt_id)
    {
        // Schedule a single event to update all products
        if (!wp_next_scheduled('fs_update_all_prices')) {
            wp_schedule_single_event(time() + 10, 'fs_update_all_prices');
        }
    }

    /**
     * Update prices for all products.
     */
    public function update_all_products()
    {
        // Get all products
        $args = [
            'post_type' => FS_Config::get_data('post_type'),
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids',
        ];

        $query = new \WP_Query($args);
        $product_ids = $query->posts;

        // Update prices for each product
        foreach ($product_ids as $product_id) {
            $this->update_product_currency_prices($product_id);
        }

        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json_success([
                'msg' => __('Prices updated', 'f-shop'),
            ]);
            exit;
        }
    }

    /**
     * Update product prices when the product is saved.
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     * @param bool $update Whether this is an existing post being updated
     */
    public function update_product_prices($post_id, $post, $update)
    {
        // Skip if doing autosave, ajax, or bulk edit
        if (
            defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
            || defined('DOING_AJAX') && DOING_AJAX
            || isset($_REQUEST['bulk_edit'])
        ) {
            return;
        }

        // Verify permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Skip if this is a revision
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // Skip if product is variated
        if (fs_is_variated($post_id)) {
            return;
        }

        // Update prices for this product
        $this->update_product_currency_prices($post_id);
    }

    /**
     * Update price_sort field for a specific product.
     *
     * @param int $product_id Product ID
     */
    public function update_product_currency_prices($product_id)
    {
        // Skip if product is variated
        if (fs_is_variated($product_id)) {
            return;
        }

        // Получаем цену товара (базовая или акционная)
        $base_price = apply_filters('fs_price_filter', floatval(get_post_meta($product_id, FS_Config::get_meta('price'), true)), $product_id);
        $action_price = apply_filters('fs_price_filter', floatval(get_post_meta($product_id, FS_Config::get_meta('action_price'), true)), $product_id);

        // Если акционная цена заполнена и она ниже базовой, то используем её
        $price_to_use = ($action_price > 0 && $action_price < $base_price) ? $action_price : $base_price;

        // Если цена пустая, выходим
        if (empty($price_to_use)) {
            return;
        }

        // Сохраняем сконвертированную цену в поле price_sort
        update_post_meta($product_id, FS_Config::get_meta('price_sort'), $price_to_use);
    }
}

// Initialize the class
new FS_Currency_Price();
