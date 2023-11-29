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
        }

        add_filter('pre_get_posts', [$this, 'pre_get_posts_product'], 10, 1);
        add_action( 'init', [$this, 'init'], 12 );
        /* We set the real price with the discount and currency */
        add_action( 'fs_after_save_meta_fields', array( $this, 'set_real_product_price' ), 10, 1 );
        add_filter( 'use_block_editor_for_post_type', [ $this, 'prefix_disable_gutenberg' ], 10, 2 );
    }

    function prefix_disable_gutenberg( $current_status, $post_type ) {
        // Use your post type key instead of 'product'
        if ( $post_type === FS_Config::get_data( 'post_type' ) ) {
            return false;
        }

        return $current_status;
    }

    public function set_real_product_price( $product_id = 0 ) {
        update_post_meta( $product_id, '_fs_real_price', fs_get_price( $product_id ) );
    }

    /**
     * hook into WP's init action hook
     */
    public function init() {
        // Initialize Post Type
        $this->create_post_type();


    }

    /**
     * Create the post type
     */
    public function create_post_type() {
        /* регистрируем тип постов - товары */
        register_post_type( FS_Config::get_data( 'post_type' ),
            array(
                'labels'             => array(
                    'name'               => __( 'Products', 'f-shop' ),
                    'singular_name'      => __( 'Product', 'f-shop' ),
                    'add_new'            => __( 'Add product', 'f-shop' ),
                    'add_new_item'       => '',
                    'edit_item'          => __( 'Edit product', 'f-shop' ),
                    'new_item'           => '',
                    'view_item'          => '',
                    'search_items'       => '',
                    'not_found'          => '',
                    'not_found_in_trash' => '',
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Products', 'f-shop' ),
                ),
                'public'             => true,
                'show_in_menu'       => true,
                'yarpp_support'      => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'capability_type'    => 'post',
                'menu_icon'          => 'dashicons-cart',
                'map_meta_cap'       => true,
                'show_in_nav_menus'  => true,
                'show_in_rest'       => true,
                'menu_position'      => 5,
                'can_export'         => true,
                'has_archive'        => true,
                'rewrite'            => apply_filters( 'fs_product_slug', true ),
                'query_var'          => true,
                'taxonomies'         => array( 'catalog', 'product-attributes' ),
                'description'        => __( "Here are the products of your site.", 'f-shop' ),

                'supports' => array(
                    'title',
                    'editor',
                    'excerpt',
                    'thumbnail',
                    'comments',
                    'page-attributes',
                    'revisions'
                )
            )
        );
    }


    /**
     * Локализируем ссылки товаров
     *
     * @param $post_link
     * @param $post
     * @param $leavename
     * @param $sample
     *
     * todo: перенести в соответсвующий клас интеграции
     *
     * @return string
     */
    function product_link_localize($post_link, $post, $leavename, $sample)
    {
        if (!class_exists('WPGlobus_Utils') && !class_exists('WPGlobus')) {
            return $post_link;
        }

        if ($post->post_type != FS_Config::get_data('post_type') || FS_Config::is_default_locale()) {
            return $post_link;
        }

        if ($custom_slug = get_post_meta($post->ID, 'fs_seo_slug__' . mb_strtolower(get_locale()), 1)) {
            return site_url(sprintf('/%s/%s/%s/', \WPGlobus::Config()->language, $post->post_type, $custom_slug));
        }

        return $post_link;
    }

    /**
     * Redirect to a localized url
     */
    function redirect_to_localize_url()
    {
        global $post;
        // Leave if the request came not from the product category
        if (!is_singular(FS_Config::get_data('post_type')) || get_locale() == FS_Config::default_locale()) {
            return;
        }

        $meta_key = 'fs_seo_slug__' . get_locale();
        $slug = get_post_meta($post->ID, $meta_key, 1);

        if (!$slug) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'];
        $uri_components = explode('/', $uri);
        $lang = $uri_components[1];

        $localized_url = sprintf('/%s/%s/%s/', $lang, FS_Config::get_data('post_type'), $slug);


        if ($uri !== $localized_url) {
            wp_redirect(home_url($localized_url));
            exit;
        }

    }

    /**
     * Получаем пост по мета полю - оно же слаг для любого языка кроме установленого по умолчанию
     *
     * @param $query
     */
    function pre_get_posts_product($query)
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

        // Получаем ID поста по метаполю
        global $wpdb;
        $meta_key = 'fs_seo_slug__' . mb_strtolower(get_locale());
        $post_id = $wpdb->get_var("SELECT post_id  FROM $wpdb->postmeta WHERE meta_key='$meta_key' AND meta_value='$slug'");
        if (!$post_id) {
            return $query;
        }

        // Получаем слаг по ID
        $post_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE ID=$post_id");
        if ($post_name) {
            $query->set('name', $post_name);
            $query->set('product', $post_name);
            $query->set('post_type', $post_type);
            $query->set('do_not_redirect', 1);
        }

        return $query;

    }

}