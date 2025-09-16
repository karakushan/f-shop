<?php

namespace FS;

class FS_Products
{
    public function __construct()
    {
        // Redirect to a localized url
        if (fs_option('fs_localize_product_url')) {
            add_action('template_redirect', [$this, 'redirect_to_localize_url']);
            add_filter('post_type_link', [$this, 'product_link_localize'], 99, 4);
            add_action('pre_get_posts', [$this, 'pre_get_posts_product'], 10, 1);
        }

        add_action('init', [$this, 'init'], 12);
       
        /* We set the real price with the discount and currency */
        add_action('fs_after_save_meta_fields', [$this, 'set_real_product_price'], 10, 1);
        add_filter('use_block_editor_for_post_type', [$this, 'prefix_disable_gutenberg'], 10, 2);

        add_action('fs_product_variations', [$this, 'fs_product_variations_list'], 10, 2);
    }

    public function prefix_disable_gutenberg($current_status, $post_type)
    {
        // Use your post type key instead of 'product'
        if ($post_type === FS_Config::get_data('post_type')) {
            return false;
        }

        return $current_status;
    }

    public function set_real_product_price($product_id = 0)
    {
        update_post_meta($product_id, '_fs_real_price', fs_get_price($product_id));
    }

    /**
     * hook into WP's init action hook.
     */
    public function init()
    {
        // Initialize Post Type
        $this->create_post_type();
    }

    /**
     * Create the post type.
     */
    public function create_post_type()
    {
        /* регистрируем тип постов - товары */
        register_post_type(
            FS_Config::get_data('post_type'),
            [
                'labels' => [
                    'name' => __('Products', 'f-shop'),
                    'singular_name' => __('Product', 'f-shop'),
                    'add_new' => __('Add product', 'f-shop'),
                    'add_new_item' => __('Add new product', 'f-shop'),
                    'edit_item' => __('Edit product', 'f-shop'),
                    'new_item' => __('New product', 'f-shop'),
                    'view_item' => __('View product', 'f-shop'),
                    'search_items' => __('Search products', 'f-shop'),
                    'not_found' => __('No products found', 'f-shop'),
                    'not_found_in_trash' => __('No products found in trash', 'f-shop'),
                    'parent_item_colon' => __('Parent product:', 'f-shop'),
                    'menu_name' => __('Products', 'f-shop'),
                ],
                'public' => true,
                'show_in_menu' => true,
                'yarpp_support' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'capability_type' => 'post',
                'menu_icon' => 'dashicons-cart',
                'map_meta_cap' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'menu_position' => 5,
                'can_export' => true,
                'has_archive' => true,
                'rewrite' => apply_filters('fs_product_slug', true),
                'query_var' => true,
                'taxonomies' => ['catalog', 'product-attributes'],
                'description' => __('Here are the products of your site.', 'f-shop'),

                'supports' => [
                    'title',
                    'editor',
                    'excerpt',
                    'thumbnail',
                    'comments',
                    'page-attributes',
                    'revisions',
                ],
            ]
        );
    }

    /**
     * Локализируем ссылки товаров.
     *
     * @param $sample
     *
     * todo: перенести в соответсвующий клас интеграции
     *
     * @return string
     */
    public function product_link_localize($post_link, $post, $leavename, $sample)
    {
        if (function_exists('wpm_get_language') && !FS_Config::is_default_locale()) {
            $slug = get_post_meta($post->ID, 'fs_seo_slug', true);
            if (function_exists('wpm_translate_string') && !empty($slug)) {
                $translated_slug = wpm_translate_string($slug);
                if ($translated_slug) {
                    return str_replace($post->post_name, $translated_slug, $post_link);
                }
            }
        }

        return $post_link;
    }

    /**
     * Redirect to a localized url.
     */
    public function redirect_to_localize_url()
    {
        global $post;
        // Leave if the request came not from the product category
        if (!is_singular(FS_Config::get_data('post_type')) || !function_exists('wpm_get_language') || FS_Config::is_default_locale()) {
            return;
        }

        $slug = get_post_meta($post->ID, 'fs_seo_slug', true);

        if (!$slug) {
            return;
        }
        $translated_slug = wpm_translate_string($slug);

        if (!$translated_slug) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'];
        $uri_components = explode('/', $uri);
        $lang = $uri_components[1];

        $localized_url = sprintf('/%s/%s/%s/', $lang, FS_Config::get_data('post_type'), $translated_slug);

        if ($uri !== $localized_url) {
            wp_redirect(home_url($localized_url));
            exit;
        }
    }

    /**
     * Получаем пост по мета полю - оно же слаг для любого языка кроме установленого по умолчанию.
     */
    public function pre_get_posts_product($query)
    {
        // Если это админка или не главный запрос
        if ($query->is_admin || !$query->is_main_query() || !$query->is_singular) {
            return $query;
        }

        // Разбиваем текущий урл на компоненты
        $url_components = explode('/', $_SERVER['REQUEST_URI']);

        // нам нужно чтобы было как миннимум 4 компонента
        if (count($url_components) < 4) {
            return $query;
        }

        $lang = $url_components[1];
        $post_type = $url_components[2];
        $slug = $url_components[3];

        if ($post_type != FS_Config::get_data('post_type') || empty($slug)) {
            return $query;
        }

        if (function_exists('wpm_get_language') && $lang != wpm_get_default_language()) {
            global $wpdb;
            $like = '%[:'.$lang.']'.$wpdb->esc_like($slug).'[:%';
            $post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'fs_seo_slug' AND meta_value LIKE %s", $like));
            if (!$post_id) {
                return $query;
            }
            // Получаем слаг по ID
            $post_name = $wpdb->get_var($wpdb->prepare("SELECT post_name FROM $wpdb->posts WHERE ID=%d", $post_id));
            if ($post_name) {
                $query->set('name', $post_name);
                $query->set('product', $post_name);
                $query->set('product_name', $post_name);
                $query->set('post_type', $post_type);
                $query->set('do_not_redirect', 1);
            }
        }

        return $query;
    }

    public function fs_product_variations_list($product_id = null, $args = [])
    {
        $product_id = fs_get_product_id($product_id);
        $product = new FS_Product();
        $variations = $product->get_all_variation_attributes($product_id);
        $args = wp_parse_args($args, [
            'view' => 'select',
            'wrapper_class' => '',
            'label_class' => '',
            'select_class' => '',
        ]);

        $attributes = [];
        array_walk($variations, function ($value, $key) use (&$attributes) {
            $newKey = str_replace('-', '_', $value['slug']); // Пример преобразования ключа
            $attributes[$newKey] = $value['children'][0]['term_id'];
        });

        foreach ($variations as $variation) { ?>

			<div class="fs-product-variations">
				<label class="<?php echo esc_attr($args['label_class']); ?>"><?php echo esc_html($variation['name']); ?></label>
				<select x-model="attributes.<?php echo esc_attr(str_replace('-', '_', $variation['slug'])); ?>"
					class="<?php echo $args['select_class']; ?>">
					<?php foreach ($variation['children'] as $child) { ?>
						<option value="<?php echo $child['term_id']; ?>"><?php echo esc_html($child['name']); ?></option>
					<?php } ?>
				</select>
			</div>

<?php }
        }

    /**
     * Получить максимальную цену в категории вне зависимости от валюты (в базовой валюте).
     *
     * @return float|int
     */
    public static function get_max_price_in_category($term_id)
    {
        // проверяем есть ли данные в кеше
        $cache_key = 'fs_max_price_in_category_'.$term_id;
        $cache = wp_cache_get($cache_key);
        if (false !== $cache) {
            return $cache;
        }

        $args = [
            'post_type' => FS_Config::get_data('post_type'),
            'posts_per_page' => -1, // Получить все товары
            'tax_query' => [
                [
                    'taxonomy' => FS_Config::get_data('product_taxonomy'),
                    'field' => 'term_id',
                    'terms' => $term_id,
                ],
            ],
        ];

        $query = new \WP_Query($args);
        $max_price = 0;

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $price = fs_get_price(get_the_ID());
                if ($price > $max_price) {
                    $max_price = $price;
                }
            }
        }

        wp_reset_postdata(); // Сброс после запроса

        // кешируем результат
        wp_cache_set($cache_key, $max_price);

        return $max_price;
    }

    /**
     * Получить минимальную цену в категории вне зависимости от валюты (в базовой валюте).
     *
     * @return float|int|mixed
     */
    public static function get_min_price_in_category($term_id)
    {
        // проверяем есть ли данные в кеше
        $cache_key = 'fs_min_price_in_category_'.$term_id;
        $cache = wp_cache_get($cache_key);
        if (false !== $cache) {
            return $cache;
        }

        $args = [
            'post_type' => FS_Config::get_data('post_type'),
            'posts_per_page' => -1, // Получить все товары
            'tax_query' => [
                [
                    'taxonomy' => FS_Config::get_data('product_taxonomy'),
                    'field' => 'term_id',
                    'terms' => $term_id,
                ],
            ],
        ];

        $query = new \WP_Query($args);
        $min_price = 0;

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $price = fs_get_price(get_the_ID());
                if ($price < $min_price || $min_price == 0) {
                    $min_price = $price;
                }
            }
        }

        wp_reset_postdata(); // Сброс после запроса

        // кешируем результат
        wp_cache_set($cache_key, $min_price);

        return $min_price;
    }

    /**
     * Возвращает массив брендов товаров в категории.
     *
     * @return array
     */
    public static function get_category_brands($term_id)
    {
        $args = [
            'post_type' => FS_Config::get_data('post_type'),
            'posts_per_page' => -1, // Получить все товары
            'tax_query' => [
                [
                    'taxonomy' => FS_Config::get_data('product_taxonomy'),
                    'field' => 'term_id',
                    'terms' => $term_id,
                ],
            ],
        ];

        $query = new \WP_Query($args);

        $cache_key = 'fs_brand_widget_terms_term_'.$term_id;
        $terms = wp_cache_get($cache_key);

        if (false === $terms) {
            $terms = [];

            while ($query->have_posts()) {
                $query->the_post();
                $post_terms = wp_get_post_terms(get_the_ID(), FS_Config::get_data('brand_taxonomy'));
                foreach ($post_terms as $term) {
                    if (in_array($term->term_id, $terms)) {
                        continue;
                    }
                    $terms[] = $term->term_id;
                }
            }
            $query->reset_postdata();

            wp_cache_set($cache_key, $terms);
        }

        if (empty($terms)) {
            return [];
        }

        $brands = get_terms([
            'taxonomy' => FS_Config::get_data('brand_taxonomy'),
            'hide_empty' => false,
            'include' => $terms,
        ]);

        return !is_wp_error($brands) && is_array($brands) ? $brands : [];
    }
}
