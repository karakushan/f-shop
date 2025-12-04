<?php
/**
 * Test file for order status synchronization
 * This file can be used to test the order status sync functionality.
 *
 * Usage: Include this file in WordPress admin or run via WP-CLI
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test function to create a new order status term and verify synchronization.
 */
function fs_test_order_status_sync()
{
    // Check if we're in admin
    if (!is_admin()) {
        return;
    }

    echo '<div class="notice notice-info"><p><strong>F-Shop Order Status Sync Test</strong></p></div>';

    // Get current order statuses
    $current_statuses = FS\FS_Orders::default_order_statuses();
    echo '<h3>Current Order Statuses:</h3>';
    echo '<ul>';
    foreach ($current_statuses as $slug => $status) {
        echo '<li><strong>'.esc_html($slug).'</strong>: '.esc_html($status['name']).'</li>';
    }
    echo '</ul>';

    // Get taxonomy terms
    $taxonomy_terms = get_terms([
        'taxonomy' => FS\FS_Config::get_data('order_statuses_taxonomy'),
        'hide_empty' => false,
    ]);

    echo '<h3>Taxonomy Terms:</h3>';
    if ($taxonomy_terms && !is_wp_error($taxonomy_terms)) {
        echo '<ul>';
        foreach ($taxonomy_terms as $term) {
            echo '<li><strong>'.esc_html($term->slug).'</strong>: '.esc_html($term->name).' (ID: '.$term->term_id.')</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No taxonomy terms found or error occurred.</p>';
    }

    // Get registered post statuses
    global $wp_post_statuses;
    echo '<h3>Registered Post Statuses (Order Related):</h3>';
    echo '<ul>';
    foreach ($wp_post_statuses as $status_name => $status_obj) {
        // Only show statuses that might be order-related
        if (in_array($status_name, array_keys($current_statuses))
            || strpos($status_name, 'fs_') === 0
            || in_array($status_name, ['new', 'processed', 'pay', 'paid', 'for-delivery', 'delivered', 'refused', 'canceled', 'return', 'black_list'])) {
            echo '<li><strong>'.esc_html($status_name).'</strong>: '.esc_html($status_obj->label).'</li>';
        }
    }
    echo '</ul>';
}

// Hook to display test results in admin
add_action('admin_notices', 'fs_test_order_status_sync');

/*
 * Test creating a new order status term
 * Uncomment the lines below to test creating a new term
 */
/*
add_action('admin_init', function() {
    if (isset($_GET['fs_test_create_status']) && $_GET['fs_test_create_status'] === '1') {
        $test_term = wp_insert_term(
            'Test Status', // Term name
            \FS\FS_Config::get_data('order_statuses_taxonomy'), // Taxonomy
            [
                'slug' => 'test-status',
                'description' => 'This is a test order status created for testing synchronization.'
            ]
        );

        if (!is_wp_error($test_term)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Test order status created successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function() use ($test_term) {
                echo '<div class="notice notice-error"><p>Error creating test status: ' . $test_term->get_error_message() . '</p></div>';
            });
        }
    }
});
*/
