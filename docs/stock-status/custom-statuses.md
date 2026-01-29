# Custom Stock Statuses

Learn how to create and implement custom stock statuses that meet your specific business needs.

## Why Custom Statuses?

While the default statuses cover most scenarios, you might need additional statuses for:

- **Seasonal products** - Summer items, Holiday specials
- **Business-specific workflows** - Pre-approved, Quality check pending
- **Geographic variations** - Regional availability, Import status
- **Customer segments** - VIP only, Wholesale availability
- **Supply chain states** - Backordered, Manufacturing delay

## Basic Implementation

### Adding Simple Custom Statuses

```php
// Add custom statuses to the system
add_filter('fs_stock_statuses', function($statuses) {
    // Add your custom statuses
    $statuses['pre_order'] = __('Pre-Order', 'f-shop');
    $statuses['coming_soon'] = __('Coming Soon', 'f-shop');
    $statuses['limited_edition'] = __('Limited Edition', 'f-shop');
    
    return $statuses;
});
```

### Using Custom Statuses

Once added, custom statuses work just like built-in ones:

```php
// Set custom status
fs_set_stock_status($product_id, 'pre_order');

// Check custom status
if (fs_get_stock_status($product_id) === 'pre_order') {
    // Handle pre-order logic
}

// Get status label
echo fs_get_stock_status_label('coming_soon'); // Outputs: "Coming Soon"
```

## Advanced Custom Status Implementation

### 1. Status with Custom Logic

```php
// Add status with special behavior
add_filter('fs_stock_statuses', function($statuses) {
    $statuses['vip_only'] = __('VIP Access Only', 'f-shop');
    $statuses['backordered'] = __('Backordered', 'f-shop');
    return $statuses;
});

// Implement custom behavior
add_filter('fs_get_stock_status', function($status, $product_id) {
    // VIP only logic
    if ($status === 'vip_only') {
        if (!is_user_logged_in() || !current_user_can('vip_access')) {
            return '0'; // Show as out of stock to non-VIP users
        }
    }
    
    // Backordered logic
    if ($status === 'backordered') {
        $backorder_date = get_post_meta($product_id, '_backorder_available_date', true);
        if ($backorder_date && strtotime($backorder_date) > time()) {
            return '2'; // Show as expected until available date
        }
    }
    
    return $status;
}, 20, 2); // Higher priority to run after default logic
```

### 2. Status-Based Pricing

```php
// Custom status that affects pricing
add_filter('fs_stock_statuses', function($statuses) {
    $statuses['clearance'] = __('Clearance Sale', 'f-shop');
    return $statuses;
});

// Apply discount for clearance items
add_filter('fs_price', function($price, $product_id) {
    if (fs_get_stock_status($product_id) === 'clearance') {
        // Apply 30% discount
        $price *= 0.7;
    }
    return $price;
}, 10, 2);
```

### 3. Multi-Language Status Labels

```php
// Add translated status labels
add_filter('fs_stock_statuses', function($statuses) {
    $statuses['local_pickup'] = __('Local Pickup Only', 'f-shop');
    $statuses['international'] = __('International Shipping', 'f-shop');
    return $statuses;
});

// Provide translations
function load_custom_status_translations() {
    load_plugin_textdomain('f-shop-custom', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'load_custom_status_translations');
```

## Real-World Examples

### Example 1: Seasonal Clothing Store

```php
// Seasonal status implementation
class SeasonalStockManager {
    
    public function __construct() {
        add_filter('fs_stock_statuses', [$this, 'add_seasonal_statuses']);
        add_filter('fs_get_stock_status', [$this, 'handle_seasonal_logic'], 20, 2);
        add_action('fs_stock_status_changed', [$this, 'handle_seasonal_notifications'], 10, 2);
    }
    
    public function add_seasonal_statuses($statuses) {
        $statuses['winter'] = __('Winter Collection', 'f-shop');
        $statuses['summer'] = __('Summer Collection', 'f-shop');
        $statuses['transitional'] = __('Transitional Season', 'f-shop');
        return $statuses;
    }
    
    public function handle_seasonal_logic($status, $product_id) {
        // Only apply to clothing products
        if (!$this->is_clothing_product($product_id)) {
            return $status;
        }
        
        $current_season = $this->get_current_season();
        $product_season = get_post_meta($product_id, '_product_season', true);
        
        // If product season doesn't match current season
        if ($product_season && $product_season !== $current_season) {
            return '0'; // Out of season = Out of stock
        }
        
        return $status;
    }
    
    public function handle_seasonal_notifications($product_id, $new_status) {
        if ($new_status === 'winter' || $new_status === 'summer') {
            // Send email notifications to interested customers
            $this->notify_subscribers($product_id, $new_status);
        }
    }
    
    private function is_clothing_product($product_id) {
        $categories = get_the_terms($product_id, 'product_cat');
        return $categories && in_array('clothing', wp_list_pluck($categories, 'slug'));
    }
    
    private function get_current_season() {
        $month = date('n');
        if ($month >= 3 && $month <= 5) return 'spring';
        if ($month >= 6 && $month <= 8) return 'summer';
        if ($month >= 9 && $month <= 11) return 'autumn';
        return 'winter';
    }
    
    private function notify_subscribers($product_id, $season) {
        // Implementation for notifying subscribers
    }
}

// Initialize the seasonal manager
new SeasonalStockManager();
```

### Example 2: Electronics Store with Supply Chain Integration

```php
// Supply chain status integration
class SupplyChainIntegration {
    
    public function __construct() {
        add_filter('fs_stock_statuses', [$this, 'add_supply_chain_statuses']);
        add_filter('fs_get_stock_status', [$this, 'sync_with_supplier'], 15, 2);
        add_action('fs_daily_inventory_sync', [$this, 'sync_all_products']);
    }
    
    public function add_supply_chain_statuses($statuses) {
        $statuses['supplier_delay'] = __('Supplier Delay', 'f-shop');
        $statuses['quality_check'] = __('Quality Check Pending', 'f-shop');
        $statuses['custom_build'] = __('Custom Build Required', 'f-shop');
        return $statuses;
    }
    
    public function sync_with_supplier($status, $product_id) {
        // Check if product has supplier integration
        $supplier_id = get_post_meta($product_id, '_supplier_id', true);
        if (!$supplier_id) {
            return $status;
        }
        
        // Get real-time status from supplier API
        $supplier_status = $this->get_supplier_status($supplier_id, $product_id);
        
        // Map supplier status to our system
        switch ($supplier_status) {
            case 'delayed':
                return 'supplier_delay';
            case 'quality_check':
                return 'quality_check';
            case 'build_required':
                return 'custom_build';
            case 'available':
                return ''; // In stock
            case 'unavailable':
                return '0'; // Out of stock
        }
        
        return $status;
    }
    
    public function sync_all_products() {
        $products = get_posts([
            'post_type' => 'product',
            'meta_query' => [[
                'key' => '_supplier_id',
                'compare' => 'EXISTS'
            ]],
            'posts_per_page' => -1
        ]);
        
        foreach ($products as $product) {
            $current_status = fs_get_stock_status($product->ID);
            $new_status = $this->sync_with_supplier($current_status, $product->ID);
            
            if ($current_status !== $new_status) {
                fs_set_stock_status($product->ID, $new_status);
            }
        }
    }
    
    private function get_supplier_status($supplier_id, $product_id) {
        // API call to supplier system
        $response = wp_remote_get("https://supplier-api.com/status/$supplier_id/$product_id");
        if (is_wp_error($response)) {
            return false;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        return $data['status'] ?? false;
    }
}

// Initialize supply chain integration
new SupplyChainIntegration();

// Schedule daily sync
if (!wp_next_scheduled('fs_daily_inventory_sync')) {
    wp_schedule_event(time(), 'daily', 'fs_daily_inventory_sync');
}
```

### Example 3: Multi-Warehouse System

```php
// Multi-warehouse status management
class WarehouseStatusManager {
    
    public function __construct() {
        add_filter('fs_stock_statuses', [$this, 'add_warehouse_statuses']);
        add_filter('fs_get_stock_status', [$this, 'check_warehouse_availability'], 20, 2);
        add_action('fs_stock_status_changed', [$this, 'update_warehouse_records'], 10, 2);
    }
    
    public function add_warehouse_statuses($statuses) {
        $statuses['warehouse_a'] = __('Available in Warehouse A', 'f-shop');
        $statuses['warehouse_b'] = __('Available in Warehouse B', 'f-shop');
        $statuses['transfer_pending'] = __('Transfer Pending', 'f-shop');
        return $statuses;
    }
    
    public function check_warehouse_availability($status, $product_id) {
        // Check warehouse-specific inventory
        $warehouses = get_post_meta($product_id, '_warehouse_inventory', true);
        if (!$warehouses) {
            return $status;
        }
        
        $customer_location = $this->get_customer_location();
        $nearest_warehouse = $this->find_nearest_warehouse($customer_location);
        
        // Check if product is available in nearest warehouse
        if (isset($warehouses[$nearest_warehouse]) && $warehouses[$nearest_warehouse] > 0) {
            return "warehouse_" . strtolower($nearest_warehouse);
        }
        
        // Check other warehouses
        foreach ($warehouses as $warehouse => $quantity) {
            if ($quantity > 0) {
                return 'transfer_pending';
            }
        }
        
        return '0'; // Out of stock everywhere
    }
    
    public function update_warehouse_records($product_id, $new_status) {
        // Update warehouse records when status changes
        $warehouses = get_post_meta($product_id, '_warehouse_inventory', true);
        if (!$warehouses) return;
        
        // Logic to update warehouse inventory based on status
        // This would integrate with your warehouse management system
    }
    
    private function get_customer_location() {
        // Get customer location from session, geolocation, etc.
        return $_SESSION['customer_location'] ?? 'default';
    }
    
    private function find_nearest_warehouse($location) {
        // Logic to determine nearest warehouse
        $warehouses = ['A', 'B', 'C'];
        return $warehouses[array_rand($warehouses)]; // Simplified example
    }
}

new WarehouseStatusManager();
```

## Best Practices for Custom Statuses

### 1. Naming Conventions

```php
// ✅ Good - Clear, descriptive names
$statuses['pre_order_special'] = __('Special Pre-Order', 'f-shop');
$statuses['limited_time_offer'] = __('Limited Time Offer', 'f-shop');

// ❌ Bad - Vague or confusing names
$statuses['special'] = __('Special', 'f-shop');
$statuses['temp'] = __('Temporary', 'f-shop');
```

### 2. Status Value Management

```php
// Use descriptive keys instead of arbitrary numbers
class CustomStatusKeys {
    const PRE_ORDER_SPECIAL = 'pre_order_special';
    const LIMITED_EDITION = 'limited_edition';
    const COMING_SOON = 'coming_soon';
}

// Usage
add_filter('fs_stock_statuses', function($statuses) {
    $statuses[CustomStatusKeys::PRE_ORDER_SPECIAL] = __('Special Pre-Order', 'f-shop');
    return $statuses;
});
```

### 3. Data Validation

```php
// Validate custom status values
add_filter('fs_set_stock_status', function($result, $product_id, $status) {
    $valid_statuses = array_keys(fs_get_stock_statuses());
    
    if (!in_array($status, $valid_statuses)) {
        error_log("Invalid stock status attempted: $status for product $product_id");
        return false; // Reject invalid statuses
    }
    
    return $result;
}, 5, 3); // Early priority for validation
```

### 4. Performance Considerations

```php
// Cache expensive custom status calculations
add_filter('fs_get_stock_status', function($status, $product_id) {
    // Use transient caching for expensive operations
    $cache_key = "custom_status_{$product_id}";
    $cached_status = get_transient($cache_key);
    
    if ($cached_status !== false) {
        return $cached_status;
    }
    
    // Expensive calculation here
    $calculated_status = $this->complex_status_calculation($product_id);
    
    // Cache for 1 hour
    set_transient($cache_key, $calculated_status, HOUR_IN_SECONDS);
    
    return $calculated_status;
}, 25, 2); // Late priority to allow other filters to run first
```

## Migration and Maintenance

### Version Compatibility

```php
// Handle different plugin versions
function custom_status_compatibility_check() {
    if (version_compare(FS_PLUGIN_VER, '1.5.0', '>=')) {
        // Use new stock status system
        add_filter('fs_stock_statuses', 'add_modern_statuses');
    } else {
        // Fallback for older versions
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>Custom stock statuses require F-Shop 1.5.0+</p></div>';
        });
    }
}
add_action('init', 'custom_status_compatibility_check');
```

### Cleanup and Removal

```php
// Clean up when removing custom statuses
register_deactivation_hook(__FILE__, function() {
    // Remove custom status meta data
    $custom_statuses = ['pre_order_special', 'limited_edition'];
    
    foreach ($custom_statuses as $status) {
        delete_metadata('post', null, 'fs_stock_status', $status, true);
    }
    
    // Clear caches
    wp_cache_flush();
});
```

## Testing Custom Statuses

```php
// Test function for custom statuses
function test_custom_statuses() {
    $test_product_id = 123; // Use a test product
    
    // Test adding custom status
    $result = fs_set_stock_status($test_product_id, 'pre_order_special');
    if (!$result) {
        error_log('Failed to set custom status');
        return false;
    }
    
    // Test retrieving custom status
    $retrieved_status = fs_get_stock_status($test_product_id);
    if ($retrieved_status !== 'pre_order_special') {
        error_log('Custom status not retrieved correctly');
        return false;
    }
    
    // Test status label
    $label = fs_get_stock_status_label('pre_order_special');
    if (empty($label)) {
        error_log('Custom status label not found');
        return false;
    }
    
    return true;
}

// Run tests in development
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('init', function() {
        if (!test_custom_statuses()) {
            error_log('Custom status tests failed!');
        }
    });
}
```

This comprehensive system allows you to create highly customized stock management workflows tailored to your specific business requirements.