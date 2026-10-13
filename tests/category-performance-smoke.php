<?php

namespace FS {
    class FS_Config
    {
        public const META_CURRENCY_LOCALE = '__fs_currency_locale';
        public const META_CURRENCY_DISPLAY = '__fs_currency_display';

        public $meta = ['currency' => 'currency'];

        public static function get_data($key)
        {
            $values = [
                'default_country' => 'UA',
                'post_type' => 'product',
                'product_taxonomy' => 'catalog',
            ];

            return isset($values[$key]) ? $values[$key] : null;
        }

        public static function get_meta($key)
        {
            $values = [
                'variations' => 'variations',
                'price' => 'price',
                'action_price' => 'action_price',
            ];

            return isset($values[$key]) ? $values[$key] : $key;
        }
    }
}

namespace {
    define('ABSPATH', __DIR__ . '/');

    $GLOBALS['test_options'] = [
        'multi_currency_on' => 1,
        'price_conversion' => 1,
        'default_currency' => 1,
        'price_cents' => 0,
        'currency_symbol' => '₴',
        'fs_fshop_category_product_summary_version' => 1,
    ];
    $GLOBALS['test_country'] = 'UA';
    $GLOBALS['test_language'] = 'ua';
    $GLOBALS['test_posts'] = [
        (object) ['ID' => 101],
        (object) ['ID' => 102],
        (object) ['ID' => 103],
    ];
    $GLOBALS['test_prices'] = [101 => 100, 102 => 0, 103 => 50];
    $GLOBALS['test_comments'] = [
        (object) ['comment_post_ID' => 101, 'comment_karma' => 4],
        (object) ['comment_post_ID' => 101, 'comment_karma' => 5],
        (object) ['comment_post_ID' => 102, 'comment_karma' => 2],
    ];
    $GLOBALS['test_comment_queries'] = 0;
    $GLOBALS['test_filters'] = [];
    $GLOBALS['test_meta_reads'] = [];
    $GLOBALS['test_carbon_reads'] = [];
    $GLOBALS['test_wp_cache'] = [];
    $GLOBALS['test_transients'] = [];
    $GLOBALS['test_last_transient_ttl'] = null;
    $GLOBALS['test_price_reads'] = [];
    $GLOBALS['test_reset_count'] = 0;

    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
    {
        $GLOBALS['test_filters'][$hook][$priority][] = [$callback, $accepted_args];
    }

    function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
    {
        return true;
    }

    function apply_filters($hook, $value)
    {
        $args = func_get_args();
        $hook = array_shift($args);
        $value = array_shift($args);

        if ('fs_current_country' === $hook) {
            return $GLOBALS['test_country'];
        }

        if (!empty($GLOBALS['test_filters'][$hook])) {
            ksort($GLOBALS['test_filters'][$hook]);
            foreach ($GLOBALS['test_filters'][$hook] as $callbacks) {
                foreach ($callbacks as $entry) {
                    $callback_args = array_merge([$value], array_slice($args, 0, max(0, $entry[1] - 1)));
                    $value = call_user_func_array($entry[0], $callback_args);
                }
            }
        }

        return $value;
    }

    function get_option($name, $default = false)
    {
        return array_key_exists($name, $GLOBALS['test_options'])
            ? $GLOBALS['test_options'][$name]
            : $default;
    }

    function add_option($name, $value, $deprecated = '', $autoload = 'yes')
    {
        if (array_key_exists($name, $GLOBALS['test_options'])) {
            return false;
        }
        $GLOBALS['test_options'][$name] = $value;
        return true;
    }

    function update_option($name, $value, $autoload = null)
    {
        $GLOBALS['test_options'][$name] = $value;
        return true;
    }

    function get_transient($key)
    {
        return array_key_exists($key, $GLOBALS['test_transients'])
            ? $GLOBALS['test_transients'][$key]
            : false;
    }

    function set_transient($key, $value, $expiration = 0)
    {
        $GLOBALS['test_transients'][$key] = $value;
        $GLOBALS['test_last_transient_ttl'] = $expiration;
        return true;
    }

    function get_post_type($post_id)
    {
        return 'product';
    }

    function get_locale()
    {
        return 'uk_UA';
    }

    function wpm_get_language()
    {
        return $GLOBALS['test_language'];
    }

    function is_post_type_archive($post_type = '')
    {
        return false;
    }

    function is_tax($taxonomy = '', $term = '')
    {
        return true;
    }

    function get_queried_object()
    {
        return (object) ['term_id' => 26];
    }

    function get_queried_object_id()
    {
        return 26;
    }

    function get_term($term_id, $taxonomy = '')
    {
        return (object) ['term_id' => (int) $term_id, 'taxonomy' => $taxonomy];
    }

    function is_wp_error($value)
    {
        return false;
    }

    function get_terms($args = [])
    {
        if (!empty($args['include'])) {
            $parents = [501 => 10, 502 => 10, 503 => 10, 601 => 20, 602 => 20];
            $terms = [];
            foreach ($args['include'] as $term_id) {
                $term_id = (int) $term_id;
                if (isset($parents[$term_id]) && (int) $args['parent'] === $parents[$term_id]) {
                    $terms[] = (object) ['term_id' => $term_id, 'parent' => $parents[$term_id]];
                }
            }
            return $terms;
        }

        return [];
    }

    function get_the_terms($post_id, $taxonomy)
    {
        $terms = [
            101 => [(object) ['term_id' => 501, 'parent' => 10], (object) ['term_id' => 601, 'parent' => 20]],
            102 => [(object) ['term_id' => 502, 'parent' => 10], (object) ['term_id' => 602, 'parent' => 20]],
            103 => [(object) ['term_id' => 503, 'parent' => 10]],
        ];

        return isset($terms[$post_id]) ? $terms[$post_id] : [];
    }

    function get_comments($args = [])
    {
        ++$GLOBALS['test_comment_queries'];
        $post_ids = isset($args['post__in']) ? array_map('intval', $args['post__in']) : [];
        return array_values(array_filter($GLOBALS['test_comments'], function ($comment) use ($post_ids) {
            return in_array((int) $comment->comment_post_ID, $post_ids, true);
        }));
    }

    function wp_get_object_terms($object_ids, $taxonomies, $args = [])
    {
        return [];
    }

    function get_query_var($name, $default = '')
    {
        return 'lang' === $name ? $GLOBALS['test_language'] : $default;
    }

    function wp_parse_args($args, $defaults = [])
    {
        return array_merge($defaults, (array) $args);
    }

    function get_post_meta($post_id, $key = '', $single = false)
    {
        if ('currency' === $key) {
            return 3;
        }
        if ('price' === $key) {
            $GLOBALS['test_price_reads'][] = $post_id;
            return $GLOBALS['test_prices'][$post_id];
        }
        if ('action_price' === $key) {
            return 0;
        }

        return [];
    }

    function get_term_meta($term_id, $key = '', $single = false)
    {
        $read_key = $term_id . ':' . $key;
        if (!isset($GLOBALS['test_meta_reads'][$read_key])) {
            $GLOBALS['test_meta_reads'][$read_key] = 0;
        }
        ++$GLOBALS['test_meta_reads'][$read_key];

        $values = [
            '2:__fs_currency_cost' => 1.5,
            '2:__fs_currency_display' => '€',
            '3:__fs_currency_cost' => 2,
            '3:__fs_currency_code' => 'USD',
            '3:__fs_currency_display' => '$',
        ];

        return isset($values[$read_key]) ? $values[$read_key] : '';
    }

    function carbon_get_term_meta($term_id, $key)
    {
        $read_key = $term_id . ':' . $key;
        if (!isset($GLOBALS['test_carbon_reads'][$read_key])) {
            $GLOBALS['test_carbon_reads'][$read_key] = 0;
        }
        ++$GLOBALS['test_carbon_reads'][$read_key];

        return ['ua'];
    }

    function wp_cache_get($key, $group = '')
    {
        $cache_key = $group . ':' . $key;
        return array_key_exists($cache_key, $GLOBALS['test_wp_cache'])
            ? $GLOBALS['test_wp_cache'][$cache_key]
            : false;
    }

    function wp_cache_set($key, $value, $group = '', $expiration = 0)
    {
        $GLOBALS['test_wp_cache'][$group . ':' . $key] = $value;
        return true;
    }

    function get_the_ID()
    {
        global $post;
        return isset($post->ID) ? $post->ID : 0;
    }

    function wp_reset_postdata()
    {
        ++$GLOBALS['test_reset_count'];
    }

    class TestWpdb
    {
        public $termmeta = 'wp_termmeta';
        public $query_count = 0;

        public function esc_like($value)
        {
            return $value;
        }

        public function prepare($query)
        {
            return $query;
        }

        public function get_var($query)
        {
            ++$this->query_count;
            return 2;
        }
    }

    class WP_Query
    {
        public static $query_count = 0;
        public static $last_args = [];
        public $posts = [];
        public $post_count = 0;
        private $index = 0;

        public function __construct($args)
        {
            ++self::$query_count;
            self::$last_args = $args;
            $this->posts = $GLOBALS['test_posts'];
            $this->post_count = count($this->posts);
        }

        public function have_posts()
        {
            return $this->index < count($this->posts);
        }

        public function the_post()
        {
            global $post;
            $post = $this->posts[$this->index++];
        }
    }

    function assert_same($expected, $actual, $message)
    {
        if ($expected !== $actual) {
            throw new \RuntimeException($message . ': expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    $GLOBALS['wpdb'] = new TestWpdb();

    require dirname(__DIR__) . '/functions/functions.php';
    require dirname(__DIR__) . '/lib/FS_Product.php';
    require dirname(__DIR__) . '/lib/FS_Products.php';
    require dirname(__DIR__) . '/lib/FS_Rating_Class.php';
    require dirname(__DIR__) . '/functions/filters.php';
    require dirname(__DIR__) . '/functions/attributes.php';

    $summary = \FS\FS_Product::get_category_product_summary();
    assert_same([
        'min' => 150.0,
        'max' => 300.0,
        'count' => 3,
        'product_ids' => [101, 102, 103],
    ], $summary, 'Category summary');
    assert_same(1, WP_Query::$query_count, 'The category should be queried once');
    assert_same([101, 102, 103], $GLOBALS['test_price_reads'], 'Each product price should be calculated once');
    assert_same(1, $GLOBALS['wpdb']->query_count, 'Locale currency should be queried once per request');
    assert_same(1, $GLOBALS['test_meta_reads']['2:__fs_currency_cost'], 'Locale rate should be read once');
    assert_same(1, $GLOBALS['test_meta_reads']['3:__fs_currency_cost'], 'Product currency rate should be read once');
    assert_same(1, $GLOBALS['test_meta_reads']['3:__fs_currency_code'], 'Currency code should be read once');
    assert_same(1, $GLOBALS['test_meta_reads']['3:__fs_currency_display'], 'Currency symbol should be read once');
    assert_same(1, $GLOBALS['test_carbon_reads']['3:_fs_currency_locale'], 'Currency locales should be read once');
    assert_same(300, $GLOBALS['test_last_transient_ttl'], 'Category summary transient lifetime');

    assert_same(150.0, \FS\FS_Product::get_min_price_in_category(), 'Minimum price wrapper');
    assert_same(300.0, \FS\FS_Product::get_max_price_in_category(), 'Maximum price wrapper');
    assert_same(3, \FS\FS_Product::get_count_products_in_category(), 'Product count wrapper');
    assert_same(150.0, \FS\FS_Products::get_min_price_in_category(26), 'Price slider minimum wrapper');
    assert_same(300.0, \FS\FS_Products::get_max_price_in_category(26), 'Price slider maximum wrapper');
    assert_same(1, WP_Query::$query_count, 'Summary wrappers should reuse the request cache');

    $summary_cache_property = new \ReflectionProperty(\FS\FS_Product::class, 'category_product_summary_cache');
    $summary_cache_property->setAccessible(true);
    $summary_cache_property->setValue(null, []);
    assert_same($summary, \FS\FS_Product::get_category_product_summary(), 'Persistent category summary cache');
    assert_same(1, WP_Query::$query_count, 'Persistent cache should avoid another category query');
    \FS\FS_Product::invalidate_category_summary_for_post_meta(1, 101, 'views');
    assert_same(1, $GLOBALS['test_options']['fs_fshop_category_product_summary_version'], 'Unrelated product metadata must preserve the cache');
    \FS\FS_Product::invalidate_category_summary_for_option('fs_fshop_category_product_summary_version');
    assert_same(1, $GLOBALS['test_options']['fs_fshop_category_product_summary_version'], 'Cache version option must not invalidate itself');

    \FS\FS_Product::invalidate_category_product_summary_cache();
    assert_same(2, $GLOBALS['test_options']['fs_fshop_category_product_summary_version'], 'Cache version after invalidation');
    \FS\FS_Product::get_category_product_summary();
    assert_same(2, WP_Query::$query_count, 'Invalidated cache should recalculate the category summary');

    assert_same('€', fs_currency(), 'Currency symbol');
    assert_same('€', fs_currency(), 'Cached currency symbol');
    assert_same(2, $GLOBALS['wpdb']->query_count, 'Currency symbol lookup should be cached');

    $rating_queries_before = WP_Query::$query_count;
    $rating_summary = \FS\FS_Rating_Class::get_category_rating_summary();
    assert_same(['count' => 2, 'average' => 3.25], $rating_summary, 'Category rating summary');
    assert_same($rating_queries_before, WP_Query::$query_count, 'Rating summary should reuse product IDs from the category cache');
    assert_same(1, $GLOBALS['test_comment_queries'], 'Ratings should load comments in one bulk query');
    assert_same(2, \FS\FS_Rating_Class::get_count_ratings_in_category(), 'Rating count wrapper');
    assert_same(3.25, \FS\FS_Rating_Class::get_average_rating_in_category(), 'Average rating wrapper');
    assert_same(1, $GLOBALS['test_comment_queries'], 'Rating wrappers should reuse the cached summary');

    $rating_cache_property = new \ReflectionProperty(\FS\FS_Rating_Class::class, 'category_rating_summary_cache');
    $rating_cache_property->setAccessible(true);
    $rating_cache_property->setValue(null, []);
    assert_same($rating_summary, \FS\FS_Rating_Class::get_category_rating_summary(), 'Persistent rating summary cache');
    assert_same(1, $GLOBALS['test_comment_queries'], 'Rating transient should avoid another comments query');

    $GLOBALS['wp_query'] = (object) [
        'tax_query' => (object) [
            'queries' => [
                ['taxonomy' => 'catalog', 'field' => 'term_id', 'terms' => 26],
            ],
        ],
    ];
    $queries_before_attributes = WP_Query::$query_count;
    assert_same([501, 502, 503], array_map(function ($term) { return $term->term_id; }, fs_current_screen_attributes(10)), 'First attribute group');
    assert_same($queries_before_attributes + 1, WP_Query::$query_count, 'Attribute IDs should use one product query for all groups');
    assert_same(86400, $GLOBALS['test_last_transient_ttl'], 'Attribute ID cache lifetime');
    assert_same([601, 602], array_map(function ($term) { return $term->term_id; }, fs_current_screen_attributes(20)), 'Second attribute group');
    assert_same($queries_before_attributes + 1, WP_Query::$query_count, 'Another attribute group should reuse the category cache');

    echo "F-SHOP category, attribute, cache invalidation, and currency-query smoke checks passed.\n";
}
