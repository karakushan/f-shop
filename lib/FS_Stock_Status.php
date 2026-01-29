<?php
/**
 * Stock Status Management Class
 * 
 * Manages product stock status with customizable options
 * 
 * @package F-Shop
 * @since 1.5.0
 */

namespace FS;

class FS_Stock_Status {
    
    /**
     * Meta key for stock status
     */
    const META_KEY = 'fs_stock_status';
    
    /**
     * Default stock statuses
     */
    const STATUS_IN_STOCK = '';
    const STATUS_OUT_OF_STOCK = '0';
    const STATUS_ON_ORDER = '1';
    const STATUS_EXPECTED = '2';
    
    /**
     * Get available stock statuses
     * 
     * @return array
     */
    public static function get_statuses() {
        $statuses = [
            self::STATUS_IN_STOCK => __('In Stock', 'f-shop'),
            self::STATUS_OUT_OF_STOCK => __('Out of Stock', 'f-shop'),
            self::STATUS_ON_ORDER => __('On Order', 'f-shop'),
            self::STATUS_EXPECTED => __('Expected', 'f-shop'),
        ];
        
        // Allow developers to add custom statuses
        return apply_filters('fs_stock_statuses', $statuses);
    }
    
    /**
     * Get stock status for product
     * 
     * @param int $product_id
     * @return string
     */
    public static function get_status($product_id = 0) {
        $product_id = fs_get_product_id($product_id);
        $status = get_post_meta($product_id, self::META_KEY, true);
        
        // Default to in stock if not set
        if ($status === '') {
            $status = self::STATUS_IN_STOCK;
        }
        
        return apply_filters('fs_get_stock_status', $status, $product_id);
    }
    
    /**
     * Set stock status for product
     * 
     * @param int $product_id
     * @param string $status
     * @return bool
     */
    public static function set_status($product_id, $status) {
        $product_id = fs_get_product_id($product_id);
        $statuses = self::get_statuses();
        
        // Validate status
        if (!array_key_exists($status, $statuses)) {
            return false;
        }
        
        $result = update_post_meta($product_id, self::META_KEY, $status);
        return apply_filters('fs_set_stock_status', $result, $product_id, $status);
    }
    
    /**
     * Check if product is in stock
     * 
     * @param int $product_id
     * @return bool
     */
    public static function is_in_stock($product_id = 0) {
        $status = self::get_status($product_id);
        $in_stock_statuses = [
            self::STATUS_IN_STOCK,
            self::STATUS_ON_ORDER,
            self::STATUS_EXPECTED
        ];
        
        return in_array($status, $in_stock_statuses);
    }
    
    /**
     * Check if product is out of stock
     * 
     * @param int $product_id
     * @return bool
     */
    public static function is_out_of_stock($product_id = 0) {
        return self::get_status($product_id) === self::STATUS_OUT_OF_STOCK;
    }
    
    /**
     * Check if product is on order
     * 
     * @param int $product_id
     * @return bool
     */
    public static function is_on_order($product_id = 0) {
        return self::get_status($product_id) === self::STATUS_ON_ORDER;
    }
    
    /**
     * Check if product is expected
     * 
     * @param int $product_id
     * @return bool
     */
    public static function is_expected($product_id = 0) {
        return self::get_status($product_id) === self::STATUS_EXPECTED;
    }
    
    /**
     * Get status label
     * 
     * @param string $status
     * @return string
     */
    public static function get_status_label($status) {
        $statuses = self::get_statuses();
        return isset($statuses[$status]) ? $statuses[$status] : __('Unknown', 'f-shop');
    }
    
    /**
     * Get status CSS class
     * 
     * @param string $status
     * @return string
     */
    public static function get_status_class($status) {
        $classes = [
            self::STATUS_IN_STOCK => 'fs-in-stock',
            self::STATUS_OUT_OF_STOCK => 'fs-out-of-stock',
            self::STATUS_ON_ORDER => 'fs-on-order',
            self::STATUS_EXPECTED => 'fs-expected',
        ];
        
        $class = isset($classes[$status]) ? $classes[$status] : 'fs-unknown';
        return apply_filters('fs_stock_status_class', $class, $status);
    }
}