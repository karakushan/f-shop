<?php

namespace FS;
/**
 *
 */
class FS_Post_Types
{

    function __construct()
    {
        add_action('init', [$this, 'init']);
        add_action('add_meta_boxes', [$this, 'mail_template_metabox']);
        add_action('save_post_fs-mail-template', [$this, 'save_mail_template_callback'], 10, 3);

        // Hiding the Discussion and Comments metaboxes
        add_action('admin_menu',[$this, 'hide_discussion_metabox']);
    }

    function hide_discussion_metabox() {
        remove_meta_box('commentstatusdiv', FS_Config::get_data('post_type_orders'), 'normal');
        remove_meta_box('commentsdiv', FS_Config::get_data('post_type_orders'), 'normal');
    }

    public function init()
    {
        $this->create_types();
    }

    /**
     * Create all registered post types
     */
    public function create_types()
    {
        $post_types = $this->register_custom_post_types();
        if (!is_array($post_types) && count($post_types) == 0) {
            return;
        }

        foreach ($post_types as $name => $type) {
            $this->create_post_type($name, $type);
        }

    }

    /**
     * Create a new post type
     *
     * @param $name
     * @param $args
     */
    public function create_post_type($name, $args)
    {
        $args = wp_parse_args($args, array(
            'labels' => array(
                'name' => $name,
                'singular_name' => $name,
                'add_new' => 'Добавить ' . $name,
                'add_new_item' => 'Добавить ' . $name,
                'edit_item' => 'Изменить ' . $name,
            ),
            'public' => true,
            'show_in_menu' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'show_in_nav_menus' => true,
            'menu_position' => null,
            'can_export' => true,
            'has_archive' => true,
            'rewrite' => true,
            'query_var' => true,
            'show_in_rest' => true,
        ));

        register_post_type($name, $args);
    }

    /**
     * This method returns the additional registered record types
     *
     * @return mixed|void
     */
    public function register_custom_post_types()
    {
        $types = array(
            'reviews' => array(
                'name' => __('Reviews', 'f-shop'),
                'singular_name' => __('Review', 'f-shop'),
                'menu_icon' => 'dashicons-thumbs-up',
                'exclude_from_search' => true,
                'taxonomies' => array(),
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'comments', 'gutenburg'),
            ),
            'fs-mail-template' => array(
                'labels' => array(
                    'name' => __('Email Templates', 'f-shop'),
                    'singular_name' => __('Email Template', 'f-shop'),
                    'add_new' => __('Add Email Template', 'f-shop'),
                    'edit_item' => __('Edit Email Template', 'f-shop'),
                    'menu_name' => __('Email Templates', 'f-shop'),
                ),
                'menu_icon' => 'dashicons-email-alt',
                'exclude_from_search' => true,
                'supports' => array('title'),
                'public' => true,
                'publicly_queryable' => false,
                'show_ui' => true,
            ),
            FS_Config::get_data('post_type_orders') => array(
                'labels' => array(
                    'name' => __('Orders', 'f-shop'),
                    'singular_name' => __('Order', 'f-shop'),
                    'add_new' => __('Add Order', 'f-shop'),
                    'add_new_item' => '',
                    'edit_item' => __('Edit order', 'f-shop'),
                    'new_item' => '',
                    'view_item' => '',
                    'search_items' => '',
                    'not_found' => '',
                    'not_found_in_trash' => '',
                    'parent_item_colon' => '',
                    'menu_name' => __('Orders', 'f-shop'),
                ),
                'public' => false,
                'show_in_menu' => true,
                'publicly_queryable' => false,
                'show_ui' => true,
                'capability_type' => 'post',
                'menu_icon' => 'dashicons-list-view',
                'map_meta_cap' => true,
                'show_in_nav_menus' => false,
                'menu_position' => 6,
                'can_export' => true,
                'has_archive' => true,
                'rewrite' => true,
                'query_var' => true,
                'description' => __("Orders from your site are placed here.", 'f-shop'),
                'supports' => array(
                    'title'
                ),
                '_builtin' => false
            )
        );

        return apply_filters('fs_register_custom_post_types', $types);
    }

    /**
     * Регистрирует блок мета полей для страницы шаблона писем
     */
    public function mail_template_metabox()
    {
        add_meta_box(
            'fs-mail-template-metabox',       // $id
            __('Letter settings', 'f-shop'),                  // $title
            [$this, 'mail_template_metabox_callback'],  // $callback
            'fs-mail-template',                 // $page
            'normal',                  // $context
            'high'                     // $priority
        );
    }

    /**
     * Отображает поля на страницах шаблона письма
     */
    public function mail_template_metabox_callback()
    {
        global $post;
        ?>
        <div class="form-field form-required">
            <label for="tag-name"><?php esc_html_e('Letter template', 'f-shop') ?></label>
            <textarea name="fs-mail[template]" id="fs-mail-template"
                      class="form-field"><?php echo get_post_meta($post->ID, 'template', 1) ?></textarea>
        </div>
        <?php
    }

    function save_mail_template_callback($post_ID, $post, $update)
    {
        if (!isset($_POST['fs-mail']) || !is_array($_POST['fs-mail'])) return;

        foreach ($_POST['fs-mail'] as $meta_key => $meta_value) {
            if ($meta_value == '') continue;

            update_post_meta($post_ID, $meta_key, trim($meta_value));
        }
    }
}