# Hooks & Filters

Extend and customize the stock status system using WordPress hooks and filters.

## Available Hooks

### Filters

#### fs_stock_statuses

Modify the list of available stock statuses.

**Parameters:**
- `$statuses` (array) - Associative array of status values and labels

**Usage:**
```php
add_filter('fs_stock_statuses', function($statuses) {
    // Add new statuses
    $statuses['3'] = __('Coming Soon', 'f-shop');
    $statuses['4'] = __('Discontinued', 'f-shop');
    
    // Remove existing statuses
    unset($statuses['2']); // Remove "Expected"
    
    // Modify existing labels
    $statuses['0'] = __('Temporarily Unavailable', 'f-shop');
    
    return $statuses;
});
```

#### fs_get_stock_status

Modify stock status retrieval for a product.

**Parameters:**
- `$status` (string) - Current status value
- `$product_id` (int) - Product ID

**Usage:**
```php
add_filter('fs_get_stock_status', function($status, $product_id) {
    // Custom logic based on product category
    $categories = get_the_terms($product_id, 'product_cat');
    if ($categories && in_array('special-items', wp_list_pluck($categories, 'slug'))) {
        // Special handling for special items
        if ($status === '0') {
            return '1'; // Show as "On Order" instead of "Out of Stock"
        }
    }
    
    return $status;
}, 10, 2);
```

#### fs_set_stock_status

Modify stock status setting behavior.

**Parameters:**
- `$result` (bool) - Current result of setting operation
- `$product_id` (int) - Product ID
- `$status` (string) - Status value being set

**Usage:**
```php
add_filter('fs_set_stock_status', function($result, $product_id, $status) {
    // Log status changes
    if ($result) {
        error_log("Stock status changed for product #$product_id to '$status'");
    }
    
    // Add custom validation
    if ($status === '1' && !current_user_can('manage_woocommerce')) {
        return false; // Only admins can set "On Order" status
    }
    
    return $result;
}, 10, 3);
```

#### fs_stock_status_class

Modify CSS classes applied to status elements.

**Parameters:**
- `$class` (string) - Current CSS class
- `$status` (string) - Status value

**Usage:**
```php
add_filter('fs_stock_status_class', function($class, $status) {
    // Add custom classes based on status
    switch ($status) {
        case '0':
            return $class . ' fs-status-unavailable fs-highlight';
        case '1':
            return $class . ' fs-status-preorder fs-animated';
        case '2':
            return $class . ' fs-status-coming-soon fs-pulse';
        default:
            return $class . ' fs-status-available';
    }
}, 10, 2);
```

### Actions

Currently no actions are available, but you can create custom ones:

```php
// Example: Create custom action when status changes
add_filter('fs_set_stock_status', function($result, $product_id, $status) {
    if ($result) {
        do_action('fs_stock_status_changed', $product_id, $status);
    }
    return $result;
}, 10, 3);

// Hook into the custom action
add_action('fs_stock_status_changed', function($product_id, $new_status) {
    // Send notification email
    wp_mail(
        get_option('admin_email'),
        'Stock Status Changed',
        "Product #$product_id status changed to: " . fs_get_stock_status_label($new_status)
    );
}, 10, 2);
```

## Practical Examples

### 1. Adding Seasonal Statuses

```php
// Add seasonal stock statuses
add_filter('fs_stock_statuses', function($statuses) {
    $statuses['seasonal'] = __('Seasonal Item', 'f-shop');
    $statuses['holiday'] = __('Holiday Only', 'f-shop');
    return $statuses;
});

// Handle seasonal logic
add_filter('fs_get_stock_status', function($status, $product_id) {
    // Check if product is seasonal
    if (get_post_meta($product_id, '_is_seasonal', true)) {
        $current_month = date('n');
        $seasonal_months = get_post_meta($product_id, '_seasonal_months', true);
        
        if ($seasonal_months && !in_array($current_month, $seasonal_months)) {
            return '0'; // Out of season = Out of Stock
        }
    }
    
    return $status;
}, 10, 2);
```

### 2. Inventory-Based Status Automation

```php
// Automatically update status based on inventory levels
add_action('updated_post_meta', function($meta_id, $post_id, $meta_key, $meta_value) {
    // Only act on product posts
    if (get_post_type($post_id) !== 'product') {
        return;
    }
    
    // Only act on quantity changes
    if ($meta_key === 'fs_remaining_amount') {
        $quantity = intval($meta_value);
        $current_status = fs_get_stock_status($post_id);
        
        // Define thresholds
        $low_stock_threshold = 5;
        $out_of_stock_threshold = 0;
        
        if ($quantity <= $out_of_stock_threshold && $current_status !== '0') {
            fs_set_stock_status($post_id, '0'); // Set to Out of Stock
        } elseif ($quantity <= $low_stock_threshold && $current_status === '') {
            fs_set_stock_status($post_id, '2'); // Set to Expected
        } elseif ($quantity > $low_stock_threshold && $current_status !== '') {
            fs_set_stock_status($post_id, ''); // Set to In Stock
        }
    }
}, 10, 4);
```

### 3. Custom Frontend Display

```php
// Modify frontend status display
add_filter('fs_stock_status_class', function($class, $status) {
    // Add priority indicators
    if ($status === '') {
        $class .= ' fs-priority-high';
    } elseif ($status === '2') {
        $class .= ' fs-priority-medium';
    } else {
        $class .= ' fs-priority-low';
    }
    
    return $class;
}, 10, 2);

// Add custom status badges
function display_custom_stock_badge($product_id = 0) {
    $status = fs_get_stock_status($product_id);
    $label = fs_get_stock_status_label($status);
    
    $badge_class = apply_filters('fs_stock_status_class', 'fs-status-badge', $status);
    
    echo '<span class="' . esc_attr($badge_class) . '">' . esc_html($label) . '</span>';
}
```

### 4. Integration with External Systems

```php
// Sync with external inventory system
add_filter('fs_set_stock_status', function($result, $product_id, $status) {
    if ($result) {
        // Send status update to external system
        $external_id = get_post_meta($product_id, '_external_system_id', true);
        if ($external_id) {
            wp_remote_post('https://external-system.com/api/stock-status', [
                'body' => json_encode([
                    'product_id' => $external_id,
                    'status' => $status,
                    'timestamp' => current_time('mysql')
                ]),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . get_option('external_api_key')
                ]
            ]);
        }
    }
    return $result;
}, 10, 3);
```

### 5. Advanced Reporting

```php
// Track status changes for reporting
add_filter('fs_set_stock_status', function($result, $product_id, $status) {
    if ($result) {
        // Log the change
        $log_entry = [
            'product_id' => $product_id,
            'old_status' => fs_get_stock_status($product_id), // Get old status before update
            'new_status' => $status,
            'changed_by' => get_current_user_id(),
            'timestamp' => current_time('mysql')
        ];
        
        // Store in custom table or option
        $logs = get_option('fs_stock_status_logs', []);
        $logs[] = $log_entry;
        update_option('fs_stock_status_logs', $logs);
    }
    return $result;
}, 10, 3);

// Function to retrieve status change history
function get_product_status_history($product_id) {
    $logs = get_option('fs_stock_status_logs', []);
    return array_filter($logs, function($log) use ($product_id) {
        return $log['product_id'] == $product_id;
    });
}
```

## Best Practices

### 1. Priority Management

When using multiple filters, be mindful of execution priority:

```php
// Lower numbers execute first
add_filter('fs_stock_statuses', 'add_basic_statuses', 10);
add_filter('fs_stock_statuses', 'add_premium_statuses', 20);
add_filter('fs_stock_statuses', 'finalize_statuses', 100);
```

### 2. Performance Optimization

Avoid heavy operations in frequently called filters:

```php
// ❌ Bad - Database query on every status check
add_filter('fs_get_stock_status', function($status, $product_id) {
    $expensive_data = get_expensive_database_query(); // Slow!
    return $status;
});

// ✅ Good - Cache expensive operations
add_filter('fs_get_stock_status', function($status, $product_id) {
    $cached_data = wp_cache_get("expensive_data_$product_id");
    if ($cached_data === false) {
        $cached_data = get_expensive_database_query();
        wp_cache_set("expensive_data_$product_id", $cached_data, 'fs_stock', 3600);
    }
    return $status;
});
```

### 3. Error Handling

Always include proper error handling:

```php
add_filter('fs_stock_statuses', function($statuses) {
    try {
        // Your custom logic here
        return $statuses;
    } catch (Exception $e) {
        error_log('Stock status filter error: ' . $e->getMessage());
        return $statuses; // Return original array on error
    }
});
```

### 4. Documentation

Document your custom hooks for other developers:

```php
/**
 * Custom stock status handler for seasonal products
 * 
 * @hook fs_get_stock_status
 * @param string $status Current status
 * @param int $product_id Product ID
 * @return string Modified status
 * @since 1.5.0
 */
add_filter('fs_get_stock_status', 'handle_seasonal_products', 15, 2);
```

## Debugging Hooks

Enable WordPress debugging to troubleshoot hook issues:

```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Debug specific hooks
add_filter('fs_stock_statuses', function($statuses) {
    error_log('fs_stock_statuses called with: ' . print_r($statuses, true));
    return $statuses;
});
```

Use the Debug Bar plugin to monitor hook execution in real-time.