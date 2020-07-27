<?php

namespace FS;


/**
 * Инициализирует функции и классы плагина
 */
class FS_Init
{
    public $fs_config;
    public $fs_payment;
    public $fs_api;
    public $fs_users;
    public $fs_action;
    public $fs_taxonomies;
    public $fs_images;
    public $fs_orders;
    public $fs_cart;
    public $fs_filters;
    public $fs_post_types;
    public $fs_rating;
    public $fs_shortcode;
    public $fs_ajax;
    public $fs_settings;
    public $fs_option;
    public $fs_widget;
    public $fs_product;
    public $fs_migrate;
    protected static $instance = null;


    /**
     * FS_Init constructor.
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts_and_styles'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts_and_styles'));

        // Инициализация классов Fast Shop
        $this->fs_config = $GLOBALS['fs_config'] = new FS_Config();
        $this->fs_option = get_option('fs_option');
        $this->fs_settings = new FS_Settings_Class;
        $this->fs_ajax = new FS_Ajax_Class;
        $this->fs_shortcode = new FS_Shortcode;
        $this->fs_rating = new FS_Rating_Class;
        $this->fs_post_types = new FS_Post_Types;
        $this->fs_filters = new FS_Filters;
        $this->fs_cart = new FS_Cart;
        $this->fs_orders = new FS_Orders;
        $this->fs_images = new FS_Images_Class;
        $this->fs_taxonomies = new FS_Taxonomy;
        $this->fs_action = new FS_Action_Class;
        $this->fs_users = new FS_Users;
        $this->fs_api = new FS_Api_Class();
        $this->fs_payment = new FS_Payment_Class();
        $this->fs_widget = new FS_Widget_CLass();
        $this->fs_product = new FS_Product();
        $this->fs_migrate = new FS_Migrate_Class();
        $this->fs_export = new FS_Export_Class();

        add_filter("plugin_action_links_" . plugin_basename(FS_PLUGIN_FILE), array(
            $this,
            'plugin_settings_link'
        ));
        add_action('plugins_loaded', array($this, 'true_load_plugin_textdomain'));

        add_action('init', array($this, 'session_init'));

        // Подключает свои шаблоны вместо стандартных темы
        add_filter('template_include', array($this, 'custom_plugin_templates'));

        add_action('wp_footer', array($this, 'footer_plugin_code'));

        // Micro-marking of product card
        add_action('wp_head', array($this->fs_product, 'product_microdata'));

        // Micro-marking of product category
        add_action('wp_head', array($this->fs_taxonomies, 'product_category_microdata'));
    } // END public function __construct


    /**
     * The single instance of the class.
     *
     * @return FS_Init|null
     * @since 1.2
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * инициализируем сессии
     */
    function session_init()
    {
        if (session_id() == '') {
            session_start();
        }
    }

    /**
     * Устанавливаем путь к файлам локализации
     */
    function true_load_plugin_textdomain()
    {
        load_plugin_textdomain('f-shop', false, dirname(plugin_basename(FS_PLUGIN_FILE)) . '/languages');
    }


    /**
     * На странице плагинов добавляет ссылку "настроить" напротив нашего плагина
     *
     * @param $links
     *
     * @return mixed
     */
    function plugin_settings_link($links)
    {
        $settings_link = '<a href="edit.php?post_type=product&page=f-shop-settings">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Подключаем скрипты и стили во фронтэнде
     */
    public static function frontend_scripts_and_styles()
    {
        $theme_info = wp_get_theme();
        $textdomain = $theme_info->display('TextDomain');

        wp_enqueue_style(FS_PLUGIN_PREFIX . 'lightslider', FS_PLUGIN_URL . 'assets/lightslider/dist/css/lightslider.min.css', array(), FS_Config::get_data('plugin_ver'), 'all');
        wp_enqueue_style(FS_PLUGIN_PREFIX . 'izi-toast', FS_PLUGIN_URL . 'assets/css/iziToast.min.css', array(), FS_Config::get_data('plugin_ver'), 'all');
        wp_enqueue_style(FS_PLUGIN_PREFIX . 'style', FS_PLUGIN_URL . 'assets/css/f-shop.css', array(), FS_Config::get_data('plugin_ver'), 'all');
        wp_enqueue_style(FS_PLUGIN_PREFIX . 'lightgallery', FS_PLUGIN_URL . 'assets/plugins/lightGallery/dist/css/lightgallery.min.css');
        wp_enqueue_style(FS_PLUGIN_PREFIX . 'jquery-ui',  'https://code.jquery.com/ui/1.12.0/jquery-ui.min.js');

        // Подключаем стили для основных тем Вордпресса
        // TODO: если нет файла стилей для данной темы то необходимо создать уведомление в админке о том что можно купить или заказать адаптацию
        if (in_array($textdomain, ['twentynineteen'])) {
            if (file_exists(get_template_directory() . DIRECTORY_SEPARATOR . FS_PLUGIN_NAME . '/assets/' . $textdomain . '/style.css')) {
                wp_enqueue_style(FS_PLUGIN_PREFIX . $textdomain, get_template_directory_uri() . '/' . FS_PLUGIN_NAME . '/assets/' . $textdomain . '/style.css');
            } elseif (file_exists(FS_PLUGIN_PATH . 'templates/front-end/assets/' . $textdomain . '/style.css')) {
                wp_enqueue_style(FS_PLUGIN_PREFIX . $textdomain, FS_PLUGIN_URL . 'templates/front-end/assets/' . $textdomain . '/style.css');
            }
        }

        wp_enqueue_script(FS_PLUGIN_PREFIX . 'lightgallery', FS_PLUGIN_URL . "assets/plugins/lightGallery/dist/js/lightgallery-all.js", array("jquery"), null, true);
        wp_enqueue_script('jquery-ui-core', array('jquery'));
        wp_enqueue_script('jquery-ui-slider', array('jquery'));
        wp_enqueue_script(FS_PLUGIN_PREFIX . 'jqueryui-touch-punch', '//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js', array(
            'jquery',
            'jquery-ui-core'
        ), false, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX . 'jquery-validate', FS_PLUGIN_URL . 'assets/js/jquery.validate.min.js', array('jquery'), null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX . 'domurl', FS_PLUGIN_URL . 'assets/js/url.min.js', array('jquery'), null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX . 'izi-toast', FS_PLUGIN_URL . 'assets/js/iziToast.min.js', array('jquery'), null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX . 'lightslider', FS_PLUGIN_URL . 'assets/lightslider/dist/js/lightslider.min.js', array('jquery'), null, true);

        wp_enqueue_script(FS_PLUGIN_PREFIX . 'main', FS_PLUGIN_URL . 'assets/js/f-shop.js', array(
            'jquery'
        ), FS_Config::get_data('plugin_ver'), true);

        $l10n = array(
            'ajaxurl' => admin_url("admin-ajax.php"),
            'nonce' => wp_create_nonce('f-shop'),
            'fs_currency' => fs_currency(),
            'cartUrl' => fs_cart_url(false),
            'checkoutUrl' => fs_checkout_url(false),
            'catalogUrl' => fs_get_catalog_link(),
            'wishlistUrl' => fs_wishlist_url(),
            'preorderWindow' => fs_option('fs_preorder_services', 0),
            'langs' => array(
                'success' => __('Success!', 'f-shop'),
                'error' => __('Error!', 'f-shop'),
                'order_send_success' => __('Your order has been successfully created. We will contact you shortly.', 'f-shop'),
                'limit_product' => __('You have selected all available items from stock.', 'f-shop'),
                'addToCart' => __('Item &laquo;%product%&raquo; successfully added to cart.', 'f-shop'),
                'addToCartButtons' => sprintf('<div class="fs-atc-message">%s</div>%s<div class="fs-atc-buttons"><a href="%s" class="btn btn-danger">%s</a> <a href="%s" class="btn btn-primary">%s</a></div>',
                    __('Item &laquo;%product%&raquo; successfully added to cart.', 'f-shop'),
                    '<div class="fs-atc-price">%price% <span>%currency%</span></div>',
                    fs_get_catalog_link(),
                    __('To catalog', 'f-shop'),
                    fs_checkout_url(false), __('Checkout', 'f-shop')),
                'addToWishlist' => __('Item &laquo;%product%&raquo; successfully added to wishlist. <a href="%wishlist_url%">Go to wishlist</a>', 'f-shop'),
            ),
            'catalogMinPrice' => fs_price_min(),
            'catalogMaxPrice' => fs_price_max(),
            'fs_cart_type' => fs_option('fs_cart_type', 'modal')
        );
        wp_localize_script(FS_PLUGIN_PREFIX . 'main', 'fShop', $l10n);
    }


    /**
     *  Подключаем скрипты и стили во бэкэнде
     */
    public function admin_scripts_and_styles()
    {
        // необходимо для работы загрузчика изображений
        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }


        wp_enqueue_style(FS_PLUGIN_PREFIX . 'spectrum', FS_PLUGIN_URL . 'assets/css/spectrum.css');
        wp_enqueue_style(FS_PLUGIN_PREFIX . 'fs-tooltipster', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/css/tooltipster.main.min.css');
        wp_enqueue_style(FS_PLUGIN_PREFIX . 'fs-tooltipster-bundle', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/css/tooltipster.bundle.min.css');
        wp_enqueue_style(FS_PLUGIN_PREFIX . 'fs-tooltipster-theme', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.min.css');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style(FS_PLUGIN_PREFIX . 'select2', FS_PLUGIN_URL . 'assets/plugins/bower_components/select2/dist/css/select2.min.css');
        wp_enqueue_style(FS_PLUGIN_PREFIX . 'fs-admin', FS_PLUGIN_URL . 'assets/css/fs-admin.css');

        wp_enqueue_script(FS_PLUGIN_PREFIX . 'spectrum', FS_PLUGIN_URL . 'assets/js/spectrum.js', array('jquery'), null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX . 'js-cookie', FS_PLUGIN_URL . 'assets/js/js.cookie.js', array('jquery'), null, true);
        $screen = get_current_screen();
        if ($screen->id == 'edit-product') {
            wp_enqueue_script(FS_PLUGIN_PREFIX . 'quick-edit', FS_PLUGIN_URL . 'assets/js/quick-edit.js', array('jquery'), null, true);
        }

        wp_enqueue_script(FS_PLUGIN_PREFIX . 'tooltipster', FS_PLUGIN_URL . 'assets/plugins/tooltipster-master/dist/js/tooltipster.bundle.min.js', array('jquery'), null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX . 'tooltipster', FS_PLUGIN_URL . 'wp-content/plugins/f-shop/assets/plugins/tooltipster-master/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-shadow.min.css', array('jquery'), null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX . 'select2', FS_PLUGIN_URL . 'assets/plugins/bower_components/select2/dist/js/select2.min.js', array('jquery'), null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX . 'admin', FS_PLUGIN_URL . 'assets/js/fs-admin.js', array(

            'jquery',
            'jquery-ui-dialog',
            FS_PLUGIN_PREFIX . 'js-cookie',
            FS_PLUGIN_PREFIX . 'library'
        ), null, true);

        $l10n = array(
            'allowedImagesType' => fs_allowed_images_type('json'),
            'mediaNonce' => wp_create_nonce('media-form')

        );
        wp_localize_script(FS_PLUGIN_PREFIX . 'admin', 'fShop', $l10n);

    }

    /**
     * Заменяем стандартные шаблоны в теме на свои
     *
     * @param $template
     *
     * @return string
     */
    public function custom_plugin_templates($template)
    {
        // Если стоит галочка не переопределять шаблоны
        if (fs_option('fs_overdrive_templates', false)) {
            return $template;
        }
        // Переопределение шаблона на странице архива типа "product"
        if (is_archive() && (get_query_var('post_type') || get_query_var('catalog'))) {
            $template = locate_template(array(FS_PLUGIN_NAME . '/archive-product/archive.php'));
            if (empty($template) && file_exists(FS_PLUGIN_PATH . 'templates/front-end/archive-product/archive.php')) {
                $template = FS_PLUGIN_PATH . 'templates/front-end/archive-product/archive.php';
            }
        }

        return $template;
    }

    /**
     * Footer plugin code
     */
    function footer_plugin_code()
    {
        echo PHP_EOL . '<div class="fs-side-cart-wrap" data-fs-action="showCart">';
        echo '<div data-fs-element="cart-widget" data-template="cart-widget/side-cart"></div>';
        echo '</div>' . PHP_EOL;

        return;
    }

}
