# Stock Status API Reference

Complete documentation of all functions, classes, and methods available in the Stock Status system.

## Core Class

### FS_Stock_Status

The main class that handles all stock status functionality.

#### Constants

```php
FS_Stock_Status::META_KEY          // 'fs_stock_status'
FS_Stock_Status::STATUS_IN_STOCK   // ''
FS_Stock_Status::STATUS_OUT_OF_STOCK // '0'
FS_Stock_Status::STATUS_ON_ORDER   // '1'
FS_Stock_Status::STATUS_EXPECTED   // '2'
```

#### Methods

##### get_statuses()

Get all available stock statuses.

```php
/**
 * @return array Associative array of status values and labels
 */
public static function get_statuses()
```

**Example:**
```php
$statuses = FS_Stock_Status::get_statuses();
// Returns: ['' => 'In Stock', '0' => 'Out of Stock', ...]
```

##### get_status($product_id)

Get the current stock status for a product.

```php
/**
 * @param int $product_id Product ID
 * @return string Status value
 */
public static function get_status($product_id = 0)
```

**Example:**
```php
$status = FS_Stock_Status::get_status(123);
// Returns: '' or '0' or '1' or '2'
```

##### set_status($product_id, $status)

Set the stock status for a product.

```php
/**
 * @param int $product_id Product ID
 * @param string $status Status value
 * @return bool Success status
 */
public static function set_status($product_id, $status)
```

**Example:**
```php
$success = FS_Stock_Status::set_status(123, '0');
// Returns: true if successful
```

##### is_in_stock($product_id)

Check if product is considered in stock.

```php
/**
 * @param int $product_id Product ID
 * @return bool
 */
public static function is_in_stock($product_id = 0)
```

**Example:**
```php
if (FS_Stock_Status::is_in_stock(123)) {
    // Product is available
}
```

##### is_out_of_stock($product_id)

Check if product is out of stock.

```php
/**
 * @param int $product_id Product ID
 * @return bool
 */
public static function is_out_of_stock($product_id = 0)
```

##### is_on_order($product_id)

Check if product is on order.

```php
/**
 * @param int $product_id Product ID
 * @return bool
 */
public static function is_on_order($product_id = 0)
```

##### is_expected($product_id)

Check if product is expected.

```php
/**
 * @param int $product_id Product ID
 * @return bool
 */
public static function is_expected($product_id = 0)
```

##### get_status_label($status)

Get the human-readable label for a status.

```php
/**
 * @param string $status Status value
 * @return string Status label
 */
public static function get_status_label($status)
```

**Example:**
```php
$label = FS_Stock_Status::get_status_label('0');
// Returns: 'Out of Stock'
```

##### get_status_class($status)

Get CSS class for a status.

```php
/**
 * @param string $status Status value
 * @return string CSS class name
 */
public static function get_status_class($status)
```

## Helper Functions

These functions provide convenient access to stock status functionality.

### fs_get_stock_status($product_id)

Get product stock status.

```php
/**
 * @param int $product_id Product ID (optional, uses current post if empty)
 * @return string Status value
 */
function fs_get_stock_status($product_id = 0)
```

### fs_set_stock_status($product_id, $status)

Set product stock status.

```php
/**
 * @param int $product_id Product ID
 * @param string $status Status value
 * @return bool Success status
 */
function fs_set_stock_status($product_id, $status)
```

### fs_get_stock_statuses()

Get all available stock statuses.

```php
/**
 * @return array Associative array of status values and labels
 */
function fs_get_stock_statuses()
```

### fs_is_out_of_stock($product_id)

Check if product is out of stock.

```php
/**
 * @param int $product_id Product ID (optional)
 * @return bool
 */
function fs_is_out_of_stock($product_id = 0)
```

### fs_is_on_order($product_id)

Check if product is on order.

```php
/**
 * @param int $product_id Product ID (optional)
 * @return bool
 */
function fs_is_on_order($product_id = 0)
```

### fs_is_expected($product_id)

Check if product is expected.

```php
/**
 * @param int $product_id Product ID (optional)
 * @return bool
 */
function fs_is_expected($product_id = 0)
```

### fs_get_stock_status_label($status)

Get status label.

```php
/**
 * @param string $status Status value
 * @return string Status label
 */
function fs_get_stock_status_label($status)
```

## Hooks and Filters

### Actions

None currently available.

### Filters

#### fs_stock_statuses

Modify the list of available stock statuses.

```php
/**
 * @param array $statuses Associative array of status values and labels
 * @return array Modified statuses array
 */
apply_filters('fs_stock_statuses', $statuses)
```

**Example:**
```php
add_filter('fs_stock_statuses', function($statuses) {
    $statuses['3'] = __('Coming Soon', 'f-shop');
    $statuses['4'] = __('Discontinued', 'f-shop');
    return $statuses;
});
```

#### fs_get_stock_status

Modify stock status retrieval.

```php
/**
 * @param string $status Current status value
 * @param int $product_id Product ID
 * @return string Modified status value
 */
apply_filters('fs_get_stock_status', $status, $product_id)
```

#### fs_set_stock_status

Modify stock status setting.

```php
/**
 * @param bool $result Current result
 * @param int $product_id Product ID
 * @param string $status Status value
 * @return bool Modified result
 */
apply_filters('fs_set_stock_status', $result, $product_id, $status)
```

#### fs_stock_status_class

Modify CSS class for status.

```php
/**
 * @param string $class Current CSS class
 * @param string $status Status value
 * @return string Modified CSS class
 */
apply_filters('fs_stock_status_class', $class, $status)
```

## Database Structure

### Meta Fields

Stock status is stored in post meta:

- **Meta Key**: `fs_stock_status`
- **Data Type**: String
- **Possible Values**: `''`, `'0'`, `'1'`, `'2'`, or custom values

### Default Values

- Empty string (`''`) = In Stock (default)
- `'0'` = Out of Stock
- `'1'` = On Order
- `'2'` = Expected

## Error Handling

The system includes built-in validation:

- Invalid status values are rejected
- Non-existent products return default values
- Missing meta fields default to "In Stock"
- All functions gracefully handle edge cases

## Performance Considerations

- Status data is cached by WordPress
- Functions use efficient database queries
- Minimal overhead compared to traditional stock checking
- Bulk operations are optimized

## Backward Compatibility

The system maintains full compatibility with:

- Existing `fs_in_stock()` function
- Traditional quantity-based stock management
- Legacy theme templates
- Older plugin versions (fallback behavior)