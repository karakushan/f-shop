<?php

namespace FS;

use FS\Admin\CommentMetabox;
use FS\Admin\TermEdit;

/**
 * Инициализирует функции и классы плагина.
 */
class FS_Init
{
    public $fs_config;
    public $fs_orders;
    public $fs_option;
    public $fs_product;
    protected $init_classes = [
        FS_Config::class,
        FS_Settings::class,
        FS_Ajax::class,
        FS_Shortcode::class,
        FS_Rating_Class::class,
        FS_Post_Types::class,
        FS_Filters::class,
        FS_Cart::class,
        FS_Orders::class,
        FS_Images_Class::class,
        FS_Taxonomy::class,
        FS_Products::class,
        FS_Action::class,
        FS_Users::class,
        FS_Api_Class::class,
        FS_Payment::class,
        FS_Widget_Class::class,
        FS_Product::class,
        FS_Migrate_Class::class,
        FS_Export_Class::class,
        FS_SEO::class,
        FS_Customers::class,
        FS_Form::class,
        FS_Currency_Price::class,
        Admin\ProductEdit::class,
        TermEdit::class,
        CommentMetabox::class,
        FS_Wishlist::class,
    ];
    protected static $instance;

    /**
     * FS_Init constructor.
     */
    public function __construct()
    {
        global $f_shop;
        $f_shop = $this;
        // Получаем опции плагина
        $this->fs_option = get_option('fs_option');

        // Инициализация классов
        foreach ($this->init_classes as $init_class) {
            $this->init_classes[$init_class] = new $init_class();
        }

        add_action('wp_enqueue_scripts', [$this, 'frontend_scripts_and_styles']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts_and_styles']);
        add_filter('plugin_action_links_'.plugin_basename(FS_PLUGIN_FILE), [
            $this,
            'plugin_settings_link',
        ]);

        add_action('init', [$this, 'session_init']);

        // Подключает свои шаблоны вместо стандартных темы
        add_filter('template_include', [$this, 'custom_plugin_templates']);

        add_action('wp_footer', [$this, 'footer_plugin_code']);

        // Displays js analytics codes in the site header
        add_action('wp_head', [$this, 'marketing_code_header']);

        add_action('after_setup_theme', [$this, 'crb_load']);

        $this->session_init();
    }

    /**
     * The single instance of the class.
     *
     * @return FS_Init|null
     *
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
     * инициализируем сессии.
     */
    public function session_init()
    {
        if (session_id() == '' && !headers_sent()) {
            session_start();
        }
    }

    /**
     * На странице плагинов добавляет ссылку "настроить" напротив нашего плагина.
     */
    public function plugin_settings_link($links)
    {
        $settings_link = '<a href="edit.php?post_type=product&page=f-shop-settings">'.__('Settings').'</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Enqueues frontend scripts and styles for the plugin.
     *
     * The method registers and enqueues CSS and JavaScript files required
     * for the frontend. Additionally, localized script data is provided to
     * make PHP variables accessible in JavaScript.
     *
     * @return void
     */
    public static function frontend_scripts_and_styles()
    {
        wp_enqueue_style(FS_PLUGIN_PREFIX.'izi-toast', FS_PLUGIN_URL.'assets/css/iziToast.min.css', [], FS_Config::get_data('plugin_ver'), 'all');
        wp_enqueue_style(FS_PLUGIN_PREFIX.'style', FS_PLUGIN_URL.'assets/css/f-shop.css', [], FS_Config::get_data('plugin_ver'), 'all');

        wp_enqueue_script(FS_PLUGIN_PREFIX.'domurl', FS_PLUGIN_URL.'assets/js/url.min.js', ['jquery'], null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX.'izi-toast', FS_PLUGIN_URL.'assets/js/iziToast.min.js', ['jquery'], null, true);

        wp_enqueue_script(FS_PLUGIN_PREFIX.'main', FS_PLUGIN_URL.'assets/js/f-shop.js', [
            'jquery',
        ], null, true);

        $l10n = [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('f-shop'),
            'fs_currency' => fs_currency(),
            'cartUrl' => fs_cart_url(false),
            'checkoutUrl' => fs_checkout_url(false),
            'catalogUrl' => fs_get_catalog_link(),
            'wishlistUrl' => fs_wishlist_url(),
            'preorderWindow' => fs_option('fs_preorder_services', 0),
            'langs' => [
                'success' => __('Success!', 'f-shop'),
                'added' => __('Added!', 'f-shop'),
                'error' => __('Error!', 'f-shop'),
                'order_send_success' => __('Your order has been successfully created. We will contact you shortly.', 'f-shop'),
                'limit_product' => __('You have selected all available items from stock.', 'f-shop'),
                'addToCart' => __('Item &laquo;%product%&raquo; successfully added to cart.', 'f-shop'),
                'ratingError' => __('Your vote is not counted because you have already voted for this product!', 'f-shop'),
                'addToCartButtons' => sprintf(
                    '<div class="fs-atc-message">%s</div>%s<div class="fs-atc-buttons"><button class="btn btn-danger fs-toast-close">%s</button> <a href="%s" class="btn btn-primary">%s</a></div>',
                    __('Item &laquo;%product%&raquo; successfully added to cart.', 'f-shop'),
                    '<div class="fs-atc-price">%price% <span>%currency%</span></div>',
                    __('Continue shopping', 'f-shop'),
                    fs_checkout_url(false),
                    __('Checkout', 'f-shop')
                ),
                'addToWishlist' => __('Item &laquo;%product%&raquo; successfully added to wishlist. <a href="%wishlist_url%">Go to wishlist</a>', 'f-shop'),
            ],
            'fs_disable_modals' => fs_option('fs_disable_modals', 0),
        ];
        wp_localize_script(FS_PLUGIN_PREFIX.'main', 'fShop', $l10n);
        wp_enqueue_script(FS_PLUGIN_PREFIX.'frontend', FS_PLUGIN_URL.'assets/js/fs-frontend.js', [], null, false);
        wp_localize_script(FS_PLUGIN_PREFIX.'frontend', 'FS_DATA', $l10n);
    }

    /**
     * Registers and enqueues scripts and styles for the admin interface.
     *
     * This method ensures that admin-specific CSS and JavaScript files are loaded,
     * including dependencies for features like the image uploader, tooltips, code editor,
     * and custom backend functionality. Scripts and styles are conditionally loaded
     * based on the current screen ID.
     *
     * @return void
     */
    public function admin_scripts_and_styles()
    {
        // необходимо для работы загрузчика изображений
        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        wp_enqueue_style(FS_PLUGIN_PREFIX.'spectrum', FS_PLUGIN_URL.'assets/css/spectrum.css');
        wp_enqueue_style(FS_PLUGIN_PREFIX.'fs-tooltipster', FS_PLUGIN_URL.'assets/plugins/tooltipster-master/dist/css/tooltipster.main.min.css');
        wp_enqueue_style(FS_PLUGIN_PREFIX.'fs-tooltipster-bundle', FS_PLUGIN_URL.'assets/plugins/tooltipster-master/dist/css/tooltipster.bundle.min.css');
        wp_enqueue_style(FS_PLUGIN_PREFIX.'fs-tooltipster-theme', FS_PLUGIN_URL.'assets/plugins/tooltipster-master/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.min.css');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style(FS_PLUGIN_PREFIX.'select2', FS_PLUGIN_URL.'assets/plugins/bower_components/select2/dist/css/select2.min.css');
        wp_enqueue_style(FS_PLUGIN_PREFIX.'fs-material-fonts', '//fonts.googleapis.com/css?family=Roboto:400,500,700,400italic|Material+Icons');
        wp_enqueue_style(FS_PLUGIN_PREFIX.'fs-admin', FS_PLUGIN_URL.'assets/css/fs-admin.css');

        wp_enqueue_script(FS_PLUGIN_PREFIX.'spectrum', FS_PLUGIN_URL.'assets/js/spectrum.js', ['jquery'], null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX.'js-cookie', FS_PLUGIN_URL.'assets/js/js.cookie.js', ['jquery'], null, true);

        $screen = get_current_screen();
        if ($screen->id == 'edit-product') {
            wp_enqueue_script(FS_PLUGIN_PREFIX.'quick-edit', FS_PLUGIN_URL.'assets/js/quick-edit.js', ['jquery'], null, true);
        } elseif ($screen->id == 'product') {
            wp_enqueue_script(FS_PLUGIN_PREFIX.'jquery-validate', FS_PLUGIN_URL.'assets/js/jquery.validate.min.js', ['jquery'], null, true);
            wp_enqueue_script(FS_PLUGIN_PREFIX.'product-edit', FS_PLUGIN_URL.'assets/js/product-edit.js', [
                'jquery',
                FS_PLUGIN_PREFIX.'jquery-validate',
            ], null, true);
        }
        wp_enqueue_script(FS_PLUGIN_PREFIX.'tooltipster', FS_PLUGIN_URL.'assets/plugins/tooltipster-master/dist/js/tooltipster.bundle.min.js', ['jquery'], null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX.'tooltipster', FS_PLUGIN_URL.'wp-content/plugins/f-shop/assets/plugins/tooltipster-master/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-shadow.min.css', ['jquery'], null, true);
        wp_enqueue_script(FS_PLUGIN_PREFIX.'select2', FS_PLUGIN_URL.'assets/plugins/bower_components/select2/dist/js/select2.min.js', ['jquery'], null, true);

        wp_enqueue_script(FS_PLUGIN_PREFIX.'admin', FS_PLUGIN_URL.'assets/js/fs-admin.js', [
            'jquery',
            'jquery-ui-dialog',
            FS_PLUGIN_PREFIX.'js-cookie',
        ], time(), true);

        $l10n = [
            'allowedImagesType' => fs_allowed_images_type('json'),
            'mediaNonce' => wp_create_nonce('media-form'),
        ];
        wp_localize_script(FS_PLUGIN_PREFIX.'admin', 'fShop', $l10n);

        wp_enqueue_script(FS_PLUGIN_PREFIX.'backend', FS_PLUGIN_URL.'assets/js/fs-backend.js', [], null, true);
        wp_localize_script(FS_PLUGIN_PREFIX.'backend', 'FS_BACKEND', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('f-shop'),
            'currency' => fs_currency(),
            'lang' => [
                'purchased_items' => __('Purchased items', 'f-shop'),
                'add_product' => __('Add product', 'f-shop'),
                'name' => __('Name', 'f-shop'),
                'price' => __('Price', 'f-shop'),
                'quantity' => __('Quantity', 'f-shop'),
                'delete' => __('Delete', 'f-shop'),
                'order_price' => __('Order price', 'f-shop'),
                'cost_goods' => __('Cost of goods', 'f-shop'),
                'packaging' => __('Packaging', 'f-shop'),
                'delivery' => __('Delivery', 'f-shop'),
                'discount' => __('Discount', 'f-shop'),
                'search_input_label' => __('Product name, ID or SKU', 'f-shop'),
                'found_products' => __('Found products', 'f-shop'),
                'add' => __('Add', 'f-shop'),
                'photo' => __('Photo', 'f-shop'),
                'close' => __('Close', 'f-shop'),
                'action' => __('Action', 'f-shop'),
                'cost' => __('Cost', 'f-shop'),
                'product_selection' => __('Product selection', 'f-shop'),
                'product' => __('product', 'f-shop'),
                'selected' => __('selected', 'f-shop'),
            ],
        ]);
    }

    /**
     * Заменяем стандартные шаблоны в теме на свои.
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
            $template = locate_template([FS_PLUGIN_NAME.'/archive-product/archive.php']);
            if (empty($template) && file_exists(FS_PLUGIN_PATH.'templates/front-end/archive-product/archive.php')) {
                $template = FS_PLUGIN_PATH.'templates/front-end/archive-product/archive.php';
            }
        }

        return $template;
    }

    /**
     * Footer plugin code.
     */
    public function footer_plugin_code()
    {
        echo PHP_EOL.'<div class="fs-side-cart-wrap" data-fs-action="showCart">';
        echo '<div data-fs-element="cart-widget" data-template="cart-widget/side-cart"></div>';
        echo '</div>'.PHP_EOL;

        echo fs_option('fs_marketing_code_footer');
    }

    /**
     * Displays js analytics codes in the site header.
     */
    public function marketing_code_header()
    {
        // Google Fonts with deferred loading for F-Shop
        echo '<!-- F-Shop Google Fonts - Deferred Loading -->'.PHP_EOL;
        echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">'.PHP_EOL;
        echo '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap"></noscript>'.PHP_EOL;
        echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Open+Sans+Condensed:ital,wght@0,300;0,700;1,300&display=swap" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">'.PHP_EOL;
        echo '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans+Condensed:ital,wght@0,300;0,700;1,300&display=swap"></noscript>'.PHP_EOL;

        echo fs_option('fs_marketing_code_header');
    }

    public function crb_load()
    {
        \Carbon_Fields\Carbon_Fields::boot();
    }

    public function get($classname)
    {
        return $this->init_classes[$classname] ?? null;
    }
}
