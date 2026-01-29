# Getting Started with Stock Status

Learn how to configure and use the stock status system in your F-Shop installation.

## Prerequisites

- F-Shop plugin v1.5.0 or higher
- WordPress 5.0 or higher
- Basic understanding of WordPress administration

## Setting Up Stock Status

### In the Admin Panel

1. Navigate to **Products** → **All Products**
2. Edit an existing product or create a new one
3. In the product editor, find the **Stock Status** dropdown
4. Select the appropriate status:
   - **In Stock** (default) - Product is available
   - **Out of Stock** - Product is unavailable
   - **On Order** - Product available for pre-order
   - **Expected** - Product coming soon

### Bulk Editing

You can also set stock statuses for multiple products:

1. Go to **Products** → **All Products**
2. Select multiple products using checkboxes
3. Choose **Edit** from the bulk actions dropdown
4. Select the desired stock status
5. Click **Update**

## Understanding Status Behaviors

### In Stock (Default)

```php
// Status value: ''
// Behavior:
// - Shows "Add to Cart" button
// - Shows quantity selector
// - Shows quick order form
// - Displays "In Stock" message
```

### Out of Stock

```php
// Status value: '0'
// Behavior:
// - Hides "Add to Cart" button
// - Hides quantity selector
// - Hides quick order form
// - Displays "Out of Stock" message
```

### On Order

```php
// Status value: '1'
// Behavior:
// - May hide "Add to Cart" button (configurable)
// - May show pre-order form
// - Displays "On Order" message
```

### Expected

```php
// Status value: '2'
// Behavior:
// - Hides "Add to Cart" button
// - May show notification signup form
// - Displays "Expected" message
```

## Code Examples

### Checking Stock Status

```php
// Check if product is in stock
if (fs_in_stock($product_id)) {
    echo "Product is available!";
}

// Check specific status
if (fs_is_out_of_stock($product_id)) {
    echo "Product is out of stock";
}

if (fs_is_on_order($product_id)) {
    echo "Product is available for pre-order";
}

if (fs_is_expected($product_id)) {
    echo "Product is expected soon";
}
```

### Setting Stock Status

```php
// Set product status programmatically
$product_id = 123;

// Set to out of stock
fs_set_stock_status($product_id, '0');

// Set to on order
fs_set_stock_status($product_id, '1');

// Set to expected
fs_set_stock_status($product_id, '2');
```

### Getting Status Information

```php
// Get current status
$status = fs_get_stock_status($product_id);

// Get status label
$label = fs_get_stock_status_label($status);
echo "Current status: " . $label;

// Get all available statuses
$statuses = fs_get_stock_statuses();
foreach ($statuses as $value => $label) {
    echo "$value: $label<br>";
}
```

## Best Practices

### 1. Consistent Status Usage

Establish clear guidelines for when to use each status:

- **In Stock**: Items ready to ship immediately
- **Out of Stock**: Items temporarily unavailable
- **On Order**: Items customers can reserve/purchase in advance
- **Expected**: Items coming soon with known arrival dates

### 2. Customer Communication

Provide clear messaging for each status:

```php
// Example: Custom status messages
switch (fs_get_stock_status($product_id)) {
    case '':
        echo "✓ In Stock - Ready to ship today!";
        break;
    case '0':
        echo "✗ Currently Unavailable";
        break;
    case '1':
        echo "📅 Available for Pre-order";
        break;
    case '2':
        echo "🔜 Coming Soon - Sign up for notifications";
        break;
}
```

### 3. Automated Updates

Consider automating status updates based on inventory levels:

```php
// Example: Auto-update based on quantity
function auto_update_stock_status($product_id) {
    $quantity = fs_remaining_amount($product_id);
    
    if ($quantity > 0) {
        fs_set_stock_status($product_id, ''); // In Stock
    } elseif ($quantity === 0) {
        fs_set_stock_status($product_id, '0'); // Out of Stock
    }
}
```

## Troubleshooting

### Common Issues

**Issue**: Status not saving
- **Solution**: Check user permissions and plugin version

**Issue**: Wrong status displaying
- **Solution**: Clear cache and check for conflicting plugins

**Issue**: Functions not found
- **Solution**: Ensure F-Shop v1.5.0+ is active

### Debug Information

Enable debug mode to troubleshoot issues:

```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('FS_DEBUG', true);
```

Check the debug log for stock status related messages.

## Next Steps

- Learn about [custom statuses](custom-statuses.md)
- Explore [API reference](api-reference.md)
- Understand [hooks and filters](hooks-filters.md)