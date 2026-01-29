<?php
// Test stock status functionality
define('WP_USE_THEMES', false);
require_once '../../../wp-load.php';

echo "Testing stock status functions...\n";

// Test if functions exist
if (function_exists('fs_get_stock_statuses')) {
    echo "✓ fs_get_stock_statuses() function exists\n";
    $statuses = fs_get_stock_statuses();
    echo "Available statuses:\n";
    print_r($statuses);
} else {
    echo "✗ fs_get_stock_statuses() function NOT found\n";
}

if (function_exists('fs_get_stock_status')) {
    echo "✓ fs_get_stock_status() function exists\n";
} else {
    echo "✗ fs_get_stock_status() function NOT found\n";
}

echo "\nTest completed.\n";