# Developer Guide

Comprehensive guide for developers working with F-Shop plugin.

## Getting Started

### Development Environment Setup

1. **Local Development**
   ```bash
   # Clone the repository
   git clone https://github.com/karakushan/f-shop.git
   
   # Install dependencies
   cd f-shop
   composer install
   npm install  # if frontend build tools are used
   ```

2. **Development Plugins**
   - Query Monitor
   - Debug Bar
   - Log Deprecated Notices
   - WP Crontrol

### Coding Standards

F-Shop follows WordPress coding standards:

```php
// ✅ Good - Proper formatting and documentation
/**
 * Calculate product discount based on quantity.
 *
 * @param int $product_id Product ID
 * @param int $quantity Quantity purchased
 * @return float Discount percentage
 */
function calculate_product_discount($product_id, $quantity) {
    $base_discount = 0.1; // 10%
    
    if ($quantity >= 10) {
        $base_discount = 0.15; // 15% for bulk orders
    }
    
    return apply_filters('fs_product_discount', $base_discount, $product_id, $quantity);
}

// ❌ Bad - Poor formatting and no documentation
function calcDisc($pid,$qty){
if($qty>=10)$disc=0.15;else $disc=0.1;
return $disc;}
```

## Plugin Architecture

### Core Components

```
f-shop/
├── lib/                    # Core classes and libraries
│   ├── FS_Config.php      # Configuration management
│   ├── FS_Stock_Status.php # Stock status system
│   └── ...                # Other core classes
├── functions/             # Helper functions
├── templates/             # Template files
├── assets/               # CSS, JS, images
└── languages/            # Translation files
```

### Main Classes

#### FS_Config
Handles plugin configuration and settings.

#### FS_Stock_Status
Manages product stock status functionality (new in v1.5.0).

#### FS_Product
Core product management class.

#### FS_Cart
Shopping cart functionality.

#### FS_Orders
Order processing and management.

## Extending F-Shop

### Creating Extensions

1. **Plugin Structure**
   ```php
   <?php
   /*
   Plugin Name: F-Shop Extension Example
   Description: Example extension for F-Shop
   Version: 1.0.0
   Author: Your Name
   */
   
   // Check if F-Shop is active
   if (!class_exists('FS\\FS_Config')) {
       add_action('admin_notices', function() {
           echo '<div class="error"><p>F-Shop plugin is required for this extension.</p></div>';
       });
       return;
   }
   
   // Your extension code here
   ```

2. **Hooking into F-Shop**
   ```php
   // Add custom functionality
   add_action('fs_after_save_meta_fields', function($product_id) {
       // Custom logic after product save
   });
   
   // Modify existing functionality
   add_filter('fs_price', function($price, $product_id) {
       // Custom price modification
       return $price;
   }, 10, 2);
   ```

### Custom Post Types and Taxonomies

```php
// Register custom product type
function register_custom_product_type() {
    register_post_type('custom_product', [
        'labels' => [
            'name' => 'Custom Products',
            'singular_name' => 'Custom Product'
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail'],
        'menu_icon' => 'dashicons-products'
    ]);
}
add_action('init', 'register_custom_product_type');
```

## API Development

### REST API Endpoints

F-Shop provides several REST API endpoints:

```javascript
// Example: Get product information
fetch('/wp-json/f-shop/v1/products/123')
    .then(response => response.json())
    .then(data => {
        console.log('Product data:', data);
    });

// Example: Add to cart
fetch('/wp-json/f-shop/v1/cart/add', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        product_id: 123,
        quantity: 2
    })
});
```

### Creating Custom API Endpoints

```php
// Register custom REST endpoint
add_action('rest_api_init', function() {
    register_rest_route('f-shop/v1', '/custom-endpoint', [
        'methods' => 'GET',
        'callback' => 'my_custom_endpoint_callback',
        'permission_callback' => '__return_true'
    ]);
});

function my_custom_endpoint_callback($request) {
    // Your custom logic here
    return new WP_REST_Response([
        'success' => true,
        'data' => 'Custom response'
    ]);
}
```

## Database Operations

### Custom Database Tables

```php
// Create custom table
function create_custom_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'fs_custom_data';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id bigint(20) NOT NULL,
        custom_field varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_custom_table');
```

### Database Queries

```php
// Safe database queries
global $wpdb;

// ✅ Good - Using prepare() for security
$product_id = 123;
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}fs_custom_data WHERE product_id = %d",
        $product_id
    )
);

// ❌ Bad - Direct query without sanitization
$results = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}fs_custom_data WHERE product_id = $product_id"
);
```

## JavaScript Integration

### Frontend JavaScript

```javascript
// Enqueue custom scripts
function enqueue_custom_scripts() {
    wp_enqueue_script(
        'f-shop-custom',
        plugin_dir_url(__FILE__) . 'assets/js/custom.js',
        ['jquery'],
        '1.0.0',
        true
    );
    
    // Localize script with data
    wp_localize_script('f-shop-custom', 'fShopCustom', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fshop_custom_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');
```

### AJAX Handlers

```php
// AJAX handler
add_action('wp_ajax_fshop_custom_action', 'handle_custom_action');
add_action('wp_ajax_nopriv_fshop_custom_action', 'handle_custom_action');

function handle_custom_action() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'fshop_custom_nonce')) {
        wp_die('Security check failed');
    }
    
    // Process request
    $response = [
        'success' => true,
        'message' => 'Action completed successfully'
    ];
    
    wp_send_json($response);
}
```

## Testing

### Unit Testing

```php
// Example unit test
class FS_Stock_Status_Test extends WP_UnitTestCase {
    
    public function test_get_statuses() {
        $statuses = FS_Stock_Status::get_statuses();
        
        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('', $statuses);
        $this->assertArrayHasKey('0', $statuses);
    }
    
    public function test_set_and_get_status() {
        $product_id = $this->factory->post->create(['post_type' => 'product']);
        
        $result = FS_Stock_Status::set_status($product_id, '1');
        $this->assertTrue($result);
        
        $status = FS_Stock_Status::get_status($product_id);
        $this->assertEquals('1', $status);
    }
}
```

### Integration Testing

```php
// Integration test example
function test_complete_purchase_flow() {
    // Create test product
    $product_id = create_test_product();
    
    // Add to cart
    FS_Cart::add_to_cart($product_id, 1);
    
    // Process checkout
    $order_id = process_test_checkout();
    
    // Verify order
    $order = FS_Orders::get_order($order_id);
    $this->assertEquals($product_id, $order->items[0]['product_id']);
}
```

## Performance Optimization

### Caching Strategies

```php
// Implement caching
function get_expensive_product_data($product_id) {
    $cache_key = "expensive_data_{$product_id}";
    $cached_data = wp_cache_get($cache_key, 'fshop');
    
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // Expensive database query
    $data = perform_expensive_query($product_id);
    
    // Cache for 1 hour
    wp_cache_set($cache_key, $data, 'fshop', HOUR_IN_SECONDS);
    
    return $data;
}
```

### Database Optimization

```php
// Optimized queries
function get_products_with_meta($category_id) {
    global $wpdb;
    
    return $wpdb->get_results(
        $wpdb->prepare("
            SELECT p.*, pm.meta_value as custom_field
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND tr.term_taxonomy_id = %d
            AND pm.meta_key = 'custom_field'
            ORDER BY p.post_date DESC
            LIMIT 20
        ", $category_id)
    );
}
```

## Security Best Practices

### Input Validation

```php
// Validate and sanitize input
function process_product_data($data) {
    $validated_data = [
        'product_id' => absint($data['product_id']),
        'quantity' => absint($data['quantity']),
        'name' => sanitize_text_field($data['name']),
        'description' => wp_kses_post($data['description'])
    ];
    
    return $validated_data;
}
```

### Capability Checks

```php
// Check user capabilities
function can_manage_products($user_id = null) {
    $user_id = $user_id ?: get_current_user_id();
    return user_can($user_id, 'manage_woocommerce') || user_can($user_id, 'administrator');
}

// Use in admin functions
if (!can_manage_products()) {
    wp_die('Insufficient permissions');
}
```

## Debugging Tools

### Custom Debug Functions

```php
// Debug helper function
function fshop_debug($data, $label = '') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log(($label ? $label . ': ' : '') . print_r($data, true));
        
        if (defined('DOING_AJAX') && DOING_AJAX) {
            // Also send to browser console for AJAX requests
            echo "<script>console.log('Debug: " . esc_js($label) . "', " . json_encode($data) . ");</script>";
        }
    }
}

// Usage
fshop_debug($product_data, 'Product Data');
```

### Error Logging

```php
// Structured error logging
function log_fshop_error($message, $context = []) {
    $log_entry = [
        'timestamp' => current_time('mysql'),
        'message' => $message,
        'context' => $context,
        'user_id' => get_current_user_id(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    error_log('F-Shop Error: ' . json_encode($log_entry));
}
```

## Deployment Checklist

### Pre-Deployment

- [ ] Code review completed
- [ ] Unit tests passing
- [ ] Integration tests passing
- [ ] Security audit performed
- [ ] Performance testing completed
- [ ] Documentation updated
- [ ] Changelog updated

### Post-Deployment

- [ ] Monitor error logs
- [ ] Check user feedback
- [ ] Verify functionality
- [ ] Performance monitoring
- [ ] Update documentation if needed

## Contributing

### Pull Request Guidelines

1. Fork the repository
2. Create feature branch
3. Write tests
4. Update documentation
5. Submit pull request

### Code Review Process

All contributions must:
- Follow coding standards
- Include appropriate tests
- Have proper documentation
- Pass all automated checks

---

**Need help with development?** Check the [API Reference](stock-status/api-reference.md) or visit our [GitHub repository](https://github.com/karakushan/f-shop).