<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 19.08.2017
 * Time: 18:16
 */

namespace FS;


class FS_Product
{
    public $variation = null;
    public $id = 0;
    public $item_id = 0;
    public $title;
    public $price;
    public $base_price;
    public $base_price_display;
    public $price_format = '%s <span>%s</span>';
    public $price_display;
    public $currency;
    public $sku;
    public $permalink;
    public $thumbnail_url;
    public $cost;
    public $cost_display;
    public $count = 1;
    public $attributes = [];
    public $post_type = 'product';


    /**
     * FS_Product_Class constructor.
     */
    public function __construct()
    {
        add_action('save_post', array($this, 'save_product_fields'), 10, 3);
        add_action('init', array($this, 'init'), 12);
        add_action('admin_init', array($this, 'admin_init'));
        add_action('fs_before_product_meta', array($this, 'before_product_meta'));

        /* We set the real price with the discount and currency */
        add_action('fs_after_save_meta_fields', array($this, 'set_real_product_price'), 10, 1);

        add_filter('pre_get_posts', array($this, 'pre_get_posts_product'), 10, 1);

        // Redirect to a localized url
        if (fs_option('fs_localize_product_url')) {
            add_action('template_redirect', array($this, 'redirect_to_localize_url'));
            add_filter('post_type_link', [$this, 'product_link_localize'], 99, 4);
        }

        add_filter('use_block_editor_for_post_type', [$this, 'prefix_disable_gutenberg'], 10, 2);

        $this->post_type = FS_Config::get_data('post_type');
    }

    function prefix_disable_gutenberg($current_status, $post_type)
    {
        // Use your post type key instead of 'product'
        if ($post_type === $this->post_type) {
            return false;
        }

        return $current_status;
    }

    public static function product_comment_likes($comment_id = 0)
    {
        $comment_like_count = (int)get_comment_meta($comment_id, 'fs_like_count', 1); ?>
        <div class="comment-like" data-fs-element="comment-like"
             data-comment-id="<?php echo esc_attr($comment_id); ?>">
            <i class="icon icon-like"></i> <span class="comment-like__count"
                                                 data-fs-element="comment-like-count"><?php echo esc_html($comment_like_count); ?></span>
        </div>
    <?php }

    /**
     * Локализируем ссылки товаров
     *
     * @param $post_link
     * @param $post
     * @param $leavename
     * @param $sample
     *
     * @return string
     */
    function product_link_localize($post_link, $post, $leavename, $sample)
    {
        if (!class_exists('WPGlobus_Utils') && !class_exists('WPGlobus')) {
            return $post_link;
        }

        if ($post->post_type != $this->post_type || FS_Config::is_default_locale()) {
            return $post_link;
        }

        $custom_slug = get_post_meta($post->ID, 'fs_seo_slug__' . get_locale(), 1);

        if (!$custom_slug) {
            return $post_link;
        }

        $post_link = str_replace($post->post_name, $custom_slug, $post_link);

        return \WPGlobus_Utils::localize_url($post_link, \WPGlobus::Config()->language);
    }


    /**
     * Redirect to a localized url
     */
    function redirect_to_localize_url()
    {
        global $post;
        // Leave if the request came not from the product category
        if (!is_singular($this->post_type) || get_locale() == FS_Config::default_locale()) {
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

        $localized_url = sprintf('/%s/%s/%s/', $lang, $this->post_type, $slug);


        if ($uri !== $localized_url) {
            wp_redirect(home_url($localized_url));
            exit;
        }

    }


    /**
     * hook into WP's init action hook
     */
    public function init()
    {
        // Initialize Post Type
        $this->create_post_type();


    } // END public function init()


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

        if ($post_type != $this->post_type || empty($slug)) {
            return $query;
        }

        // Получаем ID поста по метаполю
        global $wpdb;
        $meta_key = 'fs_seo_slug__' . get_locale();
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

    // Выводит скрытый прелоадер перед полями метабокса
    function before_product_meta()
    {
        echo '
        <div class="fs-mb-preloader"></div>';
    }

    /**
     * Возвращает массив вариаций товара
     *
     * @param int $product_id идентификатор товара
     *
     * @param bool $hide_disabled не возвращать выключенные
     *
     * @return array
     */
    public static function get_product_variations($product_id = 0, $hide_disabled = true)
    {
        $product_id = fs_get_product_id($product_id);
        $variations = get_post_meta($product_id, 'fs_variant', 0);

        if (!empty($variations[0])) {
            if ($hide_disabled) {
                foreach ($variations[0] as $key => $variation) {
                    if ((!empty($variation['deactive']) && $variation['deactive'] == 1) || (!empty($variation['count']) && $variation['count'] == 0)) {
                        unset($variations[0][$key]);
                    }
                }
            }

            return $variations[0];

        } else {
            return array();
        }
    }

    /**
     * Проверяет является ли вариативным товар
     * логическая функции
     * проверка идет по поличию добавленных вариаций товара
     *
     * @param int $product_id
     *
     * @return bool
     */
    public function is_variable_product($product_id = 0)
    {
        $product_id = fs_get_product_id($product_id);
        $variations = $this->get_product_variations($product_id);

        return count($variations) ? true : false;
    }

    /**
     * Возвращает массив всех атрибутов товара, которые добавляются в вариациях товара
     *
     * @param int $product_id
     *
     * @param bool $parents если true, то будут возвращены только родидители
     *
     * @return array|int
     */
    function get_all_variation_attributes($product_id = 0, $parents = false)
    {
        $product_id = fs_get_product_id($product_id);
        $variations = $this->get_product_variations($product_id);
        $attributes = [];
        $parents_atts = [];
        if (!count($variations)) {
            return $attributes;
        }
        foreach ($variations as $variation) {
            if (!empty($variation['attr']) && is_array($variation['attr'])) {
                foreach ($variation['attr'] as $att) {
                    $att = intval($att);
                    if ($parents) {
                        $parents_atts_get = get_term_field('parent', $att);
                        if (!is_wp_error($parents_atts_get)) {
                            $parents_atts[] = $parents_atts_get;
                        }
                    } else {
                        $attributes [] = $att;
                    }

                }

            }
        }

        if ($parents) {
            return array_unique($parents_atts);
        } else {
            return array_unique($attributes);
        }
    }

    /**
     * Изменяет запас товаров на складе
     *  - если $variant == null, то отминусовка идет от общего поля иначе отнимается у поля запаса для указанного варианта
     *
     * @param int $product_id
     * @param int $count - сколько единиц товара будет отминусовано
     * @param null $variant - если товар вариативный, то здесь нужно указывать номер варианта покупки
     */
    function fs_change_stock_count($product_id = 0, $count = 0, $variant = null)
    {
        $fs_config = new FS_Config();
        $variants = $this->get_product_variations($product_id, false);

        //если указан вариант покупки
        if (count($variants) && !is_null($variant) && is_numeric($variant)) {
            $variants = $this->get_product_variations($product_id, false);
            $variants[$variant]['count'] = max(0, $variants[$variant]['count'] - $count);
            update_post_meta($product_id, $fs_config->meta['variants'], $variants);
        } else {
            // по всей видимости товар не вариативный
            $max_count = get_post_meta($product_id, $fs_config->meta['remaining_amount'], 1);
            if (is_numeric($count) && $count != 0) {
                $max_count = max(0, $max_count - $count);
                update_post_meta($product_id, $fs_config->meta['remaining_amount'], $max_count);
            }

        }

    }


    /**
     * Calculates the overall rating of the product
     *
     * @param int $product_id
     *
     * @return float|int
     */
    public static function get_average_rating($product_id = 0)
    {
        $product_id = fs_get_product_id($product_id);
        $rate = 0;
        $total_vote = get_post_meta($product_id, 'fs_product_rating');
        if ($total_vote) {
            $sum_votes = array_sum($total_vote);
            $count_votes = count($total_vote);
            $rate = round($sum_votes / $count_votes, 2);
        }

        return $rate;
    }

    /**
     * Displays the item rating block in the form of icons
     *
     * @param int $product_id
     * @param array $args
     */
    public function product_rating($product_id = 0, $args = array())
    {
        $product_id = fs_get_product_id($product_id);

        $args = wp_parse_args($args, array(
            'wrapper_class' => 'fs-rating',
            'before' => '',
            'after' => '',
            'stars' => 5,
            'default_value' => self::get_average_rating($product_id),
            'star_class' => 'far fa-star',
            'star_active_class' => 'fas fa-star',
            'echo' => true
        ));
        if (!$args['echo']) {
            ob_start();
        }
        ?>
        <div class="<?php echo esc_attr($args['wrapper_class']) ?>">
            <?php if ($args['before'])
                echo $args['before'] ?>
            <div class="star-rating" data-fs-element="rating">
                <?php if ($args['stars']) {
                    for ($count = 1; $count <= $args['stars']; $count++) {
                        if ($count <= $args['default_value']) {
                            $star_class = $args['star_active_class'] . ' active';
                        } else {
                            $star_class = $args['star_class'];
                        }
                        echo '<span class="' . esc_attr($star_class) . '" data-fs-element="rating-item" data-rating="' . esc_attr($count) . '"></span>';
                    }
                } ?>
                <input type="hidden" name="fs-rating-value" data-product-id="<?php echo esc_attr($product_id) ?>"
                       class="rating-value"
                       value="<?php echo esc_attr($args['default_value']) ?>">
            </div>
            <?php if ($args['after'])
                echo $args['after'] ?>
        </div>
        <?php
        if (!$args['echo']) {
            return ob_get_clean();
        }
    }

    /**
     * удаляет все товары
     *
     */
    public static function delete_products()
    {
        $fs_config = new FS_Config();
        $attachments = true;
        $posts = new \WP_Query(array(
            'post_type' => array($fs_config->data['post_type']),
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));
        if ($posts->have_posts()) {
            while ($posts->have_posts()) {
                $posts->the_post();
                global $post;
                if ($attachments) {
                    $childrens = get_children(array('post_type' => 'attachment', 'post_parent' => $post->ID));
                    if ($childrens) {
                        foreach ($childrens as $children) {
                            wp_delete_post($children->ID, true);
                        }
                    }
                }
                wp_delete_post($post->ID, true);
            }
        }
    }

    /**
     * Возвращает вариативную цену
     * в случае если поле вариативной цены не заполнено, то будет возвращена обычная цена
     *
     * @param int $product_id
     * @param int $variation_id
     *
     * @return float
     */
    public function get_variation_price($product_id = 0, $variation_id = null)
    {
        $product_id = $product_id ? $product_id : $this->id;
        $variation_id = !is_null($variation_id) && is_numeric($variation_id) ? $variation_id : $this->variation;
        $variation = $this->get_variation($product_id, $variation_id);
        $price = floatval($variation['price']);

        if (!empty($variation['action_price']) && $price > floatval($variation['action_price'])) {
            $price = floatval($variation['action_price']);
        }

        return apply_filters('fs_price_filter', $price, $product_id);
    }

    /**
     * Возвращает название товара или его вариации, если указан параметр $variation_id
     *
     * @param int $product_id
     *
     * @param null $variation_id
     *
     * @return string
     */
    function get_title($product_id = 0, $variation_id = null)
    {
        $product_id = $product_id ? $product_id : $this->id;
        $variation_id = !is_null($variation_id) && is_numeric($variation_id) ? $variation_id : $this->variation;
        $variation = $this->get_variation($product_id, $variation_id);
        $title = !empty($variation['name']) ? $variation['name'] : get_the_title($product_id);

        return $title;
    }

    /**
     * Возвращает артикул (код производителя)  товара или его вариации, если указан параметр $variation_id
     *
     * @param int $product_id
     * @param null $variation_id
     *
     * @return string
     */
    function get_sku($product_id = 0, $variation_id = null)
    {
        $product_id = $product_id ? $product_id : $this->id;
        $variation_id = !is_null($variation_id) && is_numeric($variation_id) ? $variation_id : $this->variation;
        $sku = fs_get_product_code($product_id);
        if (!is_null($variation_id) && is_numeric($variation_id)) {
            $variations = $this->get_product_variations($product_id, false);
            if (!empty($variations[$variation_id]['sku'])) {
                $sku = $variations[$variation_id]['sku'];
            }
        }

        return $sku;
    }

    /**
     * Отображает артикул товара в корзине
     *
     * @param string $format
     */
    function the_sku($format = '%s')
    {
        if ($this->get_sku($this->id, $this->variation)) {
            printf('<span data-fs-element="sku" class="fs-sku">' . $format . '</span>', esc_html($this->get_sku()));
        }
    }

    /**
     * Возвращает цену  товара или его вариации, если указан параметр $variation_id
     *
     * @param int $product_id
     * @param null $variation_id
     *
     * @return string
     */
    function get_price($product_id = 0, $variation_id = null)
    {
        $product_id = $product_id ? $product_id : $this->id;
        $variation_id = !is_null($variation_id) && is_numeric($variation_id) ? $variation_id : $this->variation;
        $price = fs_get_price($product_id);

        $variations = $this->get_product_variations($product_id, true);

        if (count($variations) && !is_null($variation_id) && is_numeric($variation_id)) {
            $variation = $this->get_variation($product_id, $variation_id);
            $price = floatval($variation['price']);
            $action_price = floatval($variation['action_price']);

            // если забыли установить главную цену
            if ($price == 0 && $action_price > 0) {
                $price = $action_price;
            }
            if (!empty($variation['action_price']) && $action_price < $price) {
                $price = $action_price;
            }
            $price = apply_filters('fs_price_filter', $price, $product_id);
        }

        return $price;
    }


    /**
     * Displays the current price of the product for the basket
     *
     * @param string $format
     */
    function the_price($format = '%s <span>%s</span>')
    {
        $format = !empty($format) ? $format : $this->price_format;

        printf('<span class="fs-price">' . $format . '</span>', apply_filters('fs_price_format', $this->get_price()), $this->currency);
    }

    /**
     * Displays the old, base price (provided that the promotional price is established)
     *
     * @param string $format
     */
    function the_base_price($format = '%s <span>%s</span>')
    {
        $format = !empty($format) ? $format : $this->price_format;
        if ($this->get_base_price() > $this->get_price()) {
            printf('<del class="fs-base-price">' . $format . '</del>', apply_filters('fs_price_format', $this->get_base_price()), $this->currency);
        }
    }


    /**
     * Displays the cost of one item of the basket of goods (the quantity multiplied by the price of the 1st product)
     *
     * @param string $format
     */
    function the_cost($format = '')
    {
        $format = !empty($format) ? $format : $this->price_format;
        printf($format, esc_html($this->cost_display), esc_html($this->currency));
    }

    /**
     * Displays the item quantifier in the cart
     *
     * @param array $args
     */
    function cart_quantity($args = array())
    {
        fs_cart_quantity($this->item_id, $this->count, $args);
    }

    /**
     * Displays the button for removing goods from the cart
     *
     * @param array $args
     */
    function delete_position($args = array())
    {
        fs_delete_position($this->item_id, $args);
    }


    /**
     * Returns item data as a current class object
     *
     * @param array $product
     *
     * @param int $item_id
     *
     * @return $this
     */
    public function set_product($product = [], $item_id = 0)
    {
        $fs_config = new FS_Config();
        $this->setId(intval($product['ID']));
        $this->set_item_id($item_id);

        if (isset($product['variation']) && is_numeric($product['variation'])) {
            $this->setVariation($product['variation']);
            $variation = $this->get_variation();
            $this->attributes = !empty($variation['attr']) ? $variation['attr'] : [];
        }

        $this->title = !empty($product['name']) ? apply_filters('the_title', $product['name']) : $this->get_title();
        $this->sku = $this->get_sku();
        $this->price = !empty($product['price']) ? floatval($product['price']) : $this->get_price();
        $this->base_price = $this->get_base_price();
        $this->base_price_display = apply_filters('fs_price_format', $this->base_price);
        $this->price_display = apply_filters('fs_price_format', $this->price);
        $this->permalink = $this->get_permalink();
        $this->count = floatval($product['count']);
        $this->cost = floatval($this->count * $this->price);
        $this->cost_display = apply_filters('fs_price_format', $this->cost);
        $this->currency = fs_currency($this->id);
        $this->attributes = [];
        $this->thumbnail_url = has_post_thumbnail($this->id) ? get_the_post_thumbnail_url($this->id) : null;


        // Если указаны свойства товара
        if (!empty($product['attr'])) {
            foreach ($product['attr'] as $key => $att) {
                if (empty($att)) {
                    continue;
                }
                $attribute = get_term(intval($att), $fs_config->data['features_taxonomy']);
                $attribute->parent_name = '';
                if ($attribute->parent) {
                    $attribute->parent_name = get_term_field('name', $attribute->parent, $fs_config->data['features_taxonomy']);
                }
                $this->attributes[] = $attribute;
            }
        }

        return $this;

    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the product variation data as an array
     *
     * @param int $product_id
     * @param null $variation_id
     *
     * @return null
     */
    public function get_variation($product_id = 0, $variation_id = null)
    {
        $product_id = $product_id ? $product_id : $this->id;
        $variation_id = !is_null($variation_id) && is_numeric($variation_id) ? $variation_id : $this->variation;
        $variation = [];
        if (!is_null($variation_id) && is_numeric($variation_id)) {
            $variations = $this->get_product_variations($product_id, false);
            if (!empty($variations[$variation_id])) {
                $variation = $variations[$variation_id];
            }
        }

        return $variation;
    }

    /**
     * @param mixed $variation
     */
    public function setVariation($variation)
    {
        $this->variation = $variation;
    }

    /**
     * Returns a link to the product
     *
     * @param int $product_id
     *
     * @return mixed
     */
    public function get_permalink($product_id = 0)
    {
        $product_id = $product_id ? $product_id : $this->id;

        return get_the_permalink($product_id);
    }

    /**
     * Выводит ссылку на странцу товара
     *
     * @param int $product_id
     */
    public function the_permalink($product_id = 0)
    {
        echo esc_url($this->get_permalink($product_id));
    }

    /**
     * Returns the base price of the item.
     *
     * @param int $product_id
     *
     * @param null $variation_id
     *
     * @return mixed
     */
    public function get_base_price($product_id = 0, $variation_id = null)
    {
        $fs_config = new FS_Config();

        $product_id = $product_id ? $product_id : $this->id;
        $variation_id = !is_null($variation_id) && is_numeric($variation_id) ? $variation_id : $this->variation;

        $variations = $this->get_product_variations($product_id, true);

        if (count($variations) && !is_null($variation_id) && is_numeric($variation_id)) {
            $variation_id = !is_null($variation_id) && is_numeric($variation_id) ? $variation_id : $this->variation;
            $variation = $this->get_variation($product_id, $variation_id);
            $base_price = apply_filters('fs_price_filter', $variation['price'], $product_id);

            return floatval($base_price);
        } else {
            $price = get_post_meta($product_id, $fs_config->meta['price'], 1);

            return apply_filters('fs_price_filter', $price, $product_id);
        }
    }

    /**
     * @param int $item_id
     */
    public function set_item_id($item_id = 0)
    {
        $this->item_id = $item_id;
    }

    /**
     * Выводит вкладки товаров в лицевой части сайта
     *
     * @param int $product_id
     * @param array $args
     */
    public static function product_tabs($product_id = 0, $args = array())
    {
        $product_id = fs_get_product_id($product_id);

        $args = wp_parse_args($args, array(
            'wrapper_class' => 'fs-product-tabs',
            'before' => '',
            'after' => '',
            'attributes_args' => array()

        ));

        // Get the product attributes
        ob_start();
        fs_the_atts_list($product_id, $args['attributes_args']);
        $attributes = ob_get_clean();

        // Вкладки по умолчанию
        $default_tabs = array(
            'attributes' => array(
                'title' => __('Characteristic', 'f-shop'),
                'content' => $attributes
            ),
            'description' => array(
                'title' => __('Description', 'f-shop'),
                'content' => apply_filters('the_content', get_the_content())
            ),
            'delivery' => array(
                'title' => __('Shipping and payment', 'f-shop'),
                'content' => fs_frontend_template('product/tabs/delivery')
            ),
            'reviews' => array(
                'title' => __('Reviews', 'f-shop'),
                'content' => fs_frontend_template('product/tabs/comments')
            )

        );

        $default_tabs = apply_filters('fs_product_tabs_items', $default_tabs, $product_id);

        if (is_array($default_tabs) && !empty($default_tabs)) {

            $html = '<div class="' . esc_attr($args['wrapper_class']) . '">';
            $html .= $args['before'];
            $html .= '<ul class="nav nav-tabs" id="fs-product-tabs-nav" role="tablist">';

            // Display tab switches
            $counter = 0;
            foreach ($default_tabs as $id => $tab) {
                $class = !$counter ? ' active' : '';
                $html .= '<li class="nav-item ' . $class . '">';
                $html .= '<a class="nav-link' . esc_attr($class) . '" id="fs-product-tab-nav-' . esc_attr($id) . '" data-toggle="tab" href="#fs-product-tab-' . esc_attr($id) . '" role="tab" aria-controls="' . esc_attr($id) . '" aria-selected="true">' . esc_html($tab['title']) . '</a>';
                $html .= '</li>';
                $counter++;
            }

            $html .= '</ul><!-- END #fs-product-tabs-nav -->';

            $html .= '<div class="tab-content" id="fs-product-tabs-content">';

            // Display the contents of the tabs
            $counter = 0;
            foreach ($default_tabs as $id => $tab) {
                $class = !$counter ? ' active' : '';
                $html .= '<div class="tab-pane' . esc_attr($class) . '" id="fs-product-tab-' . esc_attr($id) . '" role="tabpanel" aria-labelledby="' . esc_attr($id) . '-tab">';
                $html .= $tab['content'];
                $html .= '</div>';
                $counter++;
            }

            $html .= '</div><!-- END #fs-product-tabs-content -->';
            $html .= $args['after'];
            $html .= ' </div><!-- END .product-meta__row -->';

            echo apply_filters('fs_product_tabs_html', $html);
        }


    }

    /**
     * Create the post type
     */
    public function create_post_type()
    {
        /* регистрируем тип постов  - товары */
        register_post_type(FS_Config::get_data('post_type'),
            array(
                'labels' => array(
                    'name' => __('Products', 'f-shop'),
                    'singular_name' => __('Product', 'f-shop'),
                    'add_new' => __('Add product', 'f-shop'),
                    'add_new_item' => '',
                    'edit_item' => __('Edit product', 'f-shop'),
                    'new_item' => '',
                    'view_item' => '',
                    'search_items' => '',
                    'not_found' => '',
                    'not_found_in_trash' => '',
                    'parent_item_colon' => '',
                    'menu_name' => __('Products', 'f-shop'),
                ),
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
                'taxonomies' => array('catalog', 'product-attributes'),
                'description' => __("Here are the products of your site.", 'f-shop'),

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
     * Save product fields
     *
     * @param $post_id
     */
    function save_product_fields($post_id, $post, $update)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!isset($_POST['post_type']) || (isset($_POST['post_type']) && $_POST['post_type'] != FS_Config::get_data('post_type'))) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        do_action('fs_before_save_meta_fields', $post_id);

        $save_meta_keys = self::get_product_tabs();

        if (!empty($save_meta_keys)) {
            foreach ($save_meta_keys as $fields) {
                if (empty($fields['fields'])) {
                    continue;
                }

                foreach ($fields['fields'] as $key => $field) {
                    $is_multilang = isset($field['multilang']) && $field['multilang'] && fs_option('fs_multi_language_support');
                    if (!in_array($field['type'], ['checkbox']) && $is_multilang) {
                        foreach (FS_Config::get_languages() as $code => $language) {
                            $meta_key = $key . '__' . $language['locale'];
                            if ((isset($_POST[$meta_key]) && $_POST[$meta_key] != '')
                                || ($key == 'fs_seo_slug' && isset($_POST[$meta_key]))) {
                                $value = apply_filters('fs_transform_meta_value', $_POST[$meta_key], $key, $code, $post_id);
                                update_post_meta($post_id, $key . '__' . $language['locale'], $value);
                            } else {
                                delete_post_meta($post_id, $key . '__' . $language['locale']);
                            }
                        }
                    } else {
                        if (isset($_POST[$key]) && $_POST[$key] != '') {
                            $value = apply_filters('fs_transform_meta_value', $_POST[$key], $key, null, $post_id);
                            update_post_meta($post_id, $key, $value);
                        } else {
                            delete_post_meta($post_id, $key);
                        }
                    }
                }
            }
        }

        // Set the number of views for a new product 0
        if (!get_post_meta($post_id, 'views', 1)) {
            update_post_meta($post_id, 'views', 0);
        }

        do_action('fs_after_save_meta_fields', $post_id);
    }

    /**
     * hook into WP's admin_init action hook
     */
    public function admin_init()
    {
        // Add metaboxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
    } // END public function admin_init()

    /**
     * hook into WP's add_meta_boxes action hook
     */
    public function add_meta_boxes()
    {
        // Add this metabox to every selected post
        add_meta_box(
            sprintf('fast_shop_%s_metabox', FS_Config::get_data('post_type')),
            __('Product settings', 'f-shop'),
            array(&$this, 'add_inner_meta_boxes'),
            FS_Config::get_data('post_type'),
            'normal',
            'high'
        );

        // Add this metabox to every selected post

    } // END public function add_meta_boxes()


    /**
     * Gets the array that contains the list of product settings tabs.
     *
     * @return array
     */
    public static function get_product_tabs()
    {
        $product_fields = FS_Config::get_product_field();
        $tabs = array(
            'basic' => array(
                'title' => __('Basic', 'f-shop'),
                'on' => true,
                'description' => __('In this tab you can adjust the prices of goods.', 'f-shop'),
                'fields' => array(
                    FS_Config::get_meta('product_type') => array(
                        'label' => __('Product type', 'f-shop'),
                        'type' => 'radio',
                        'required' => false,
                        'values' => array(
                            'physical' => __('Physical', 'f-shop'),
                            'virtual' => __('Virtual', 'f-shop')
                        ),
                        'value' => 'physical',
                        'attributes' => [
                            'x-model' => 'productType',
                        ],
                        'help' => __('Check this box if you are selling a non-physical item.', 'f-shop')
                    ),
                    FS_Config::get_meta('price') => array(
                        'label' => __('Base price', 'f-shop'),
                        'type' => 'number',
                        'required' => true,
                        'attributes' => [
                            'min' => 0,
                            'step' => 0.01
                        ],
                        'help' => __('This is the main price on the site. Required field!', 'f-shop')
                    ),
                    FS_Config::get_meta('action_price') => array(
                        'label' => __('Promotional price', 'f-shop'),
                        'type' => 'number',
                        'help' => __('If this field is filled, the base price loses its relevance. But you can display it on the site.', 'f-shop')
                    ),

                    $product_fields['sku']['key'] => $product_fields['sku'],
                    $product_fields['quantity']['key'] => $product_fields['quantity'],
                    FS_Config::get_meta('currency') => array(
                        'label' => __('Item Currency', 'f-shop'),
                        'on' => fs_option('multi_currency_on') ? true : false,
                        'type' => 'dropdown_categories',
                        'help' => __('The field is active if you have enabled multicurrency in settings.', 'f-shop'),
                        'taxonomy' => FS_Config::get_data('currencies_taxonomy')
                    ),

                )
            ),
            'gallery' => array(
                'title' => __('Gallery', 'f-shop'),
                'on' => true,
                'body' => '',
//				'template' => 'gallery',
                'fields' => [
                    'fs_galery' => [
                        'label' => __('Gallery', 'f-shop'),
                        'type' => 'gallery',
                        'help' => __('Add images to the gallery.', 'f-shop'),
                        'multilang' => false
                    ]
                ]
            ),
            'attributes' => array(
                'title' => __('Attributes', 'f-shop'),
                'on' => true,
                'body' => '',
                'template' => 'attributes'
            ),
            'related' => array(
                'title' => __('Associated', 'f-shop'),
                'on' => false, // Сейчас в разработке
                'body' => '',
                'template' => 'related'
            ),
            'up_sell' => array(
                'title' => __('Up-sell', 'f-shop'),
                'on' => true,
                'body' => '',
                'template' => 'up-sell'
            ),
            'cross_sell' => array(
                'title' => __('Cross-sell', 'f-shop'),
                'on' => true,
                'body' => '',
                'template' => 'cross-sell'
            ),
            'variants' => array(
                'title' => __('Variation', 'f-shop'),
                'on' => true,
                'body' => '',
                'template' => 'variants'
            ),
            'delivery' => array(
                'title' => __('Shipping and payment', 'f-shop'),
                'on' => true,
                'body' => '',
                'fields' => array(
                    '_fs_delivery_description' => array(
                        'label' => __('Shipping and Payment Details', 'f-shop'),
                        'type' => 'editor',
                        'help' => ''
                    ),

                )
            ),
            'seo' => array(
                'title' => __('SEO', 'f-shop'),
                'on' => true,
                'body' => '',
                'fields' => array(
                    'fs_seo_slug' => array(
                        'label' => __('SEO slug', 'f-shop'),
                        'type' => 'text',
                        'multilang' => true,
                        'help' => __('Allows you to set multilingual url', 'f-shop')
                    ),

                )
            ),
            'additionally' => array(
                'title' => __('Additionally', 'f-shop'),
                'on' => true,
                'body' => '',
                'fields' => array(
                    $product_fields['exclude_archive']['key'] => $product_fields['exclude_archive'],
                    $product_fields['label_bestseller']['key'] => $product_fields['label_bestseller'],
                    $product_fields['label_promotion']['key'] => $product_fields['label_promotion'],
                    $product_fields['label_novelty']['key'] => $product_fields['label_novelty'],

                )
            ),
            'virtual' => array(
                'title' => __('Virtual Product', 'f-shop'),
                'on' => true,
                'nav_attributes' => [
                    'x-show' => 'productType === \'virtual\''
                ],
                'body' => '',
                'fields' => array(
                    '_fs_virtual_product_url' => array(
                        'label' => __('Ссылка на товар', 'f-shop'),
                        'type' => 'text',
                        'help' => ''
                    ),
                )
            ),
        );

        return apply_filters('fs_product_tabs_admin', $tabs);
    }

    /**
     * called off of the add meta box
     *
     * @param $post
     */
    public function add_inner_meta_boxes($post)
    {
        $form_class = new FS_Form();
        $product_tabs = array_filter(self::get_product_tabs(), function ($tab) {
            return $tab['on'];
        });
        $this->product_id = $post->ID;

        $gallery = \FS\FS_Images_Class::get_gallery(0, false, false);
        $product_type = get_post_meta($post->ID, FS_Config::get_meta('product_type'), true);

        echo '<div class="fs-metabox" id="fs-metabox" x-data="{ 
                activeTab: localStorage.getItem(\'fs_active_tab\') || \'basic\',
                productType: \'' .($product_type ?: 'physical')  . '\',     
             }">';
        do_action('fs_before_product_meta');
        if (count($product_tabs)) {
            echo '<ul class="tab-header">';

            foreach ($product_tabs as $key => $tab) : ?>
                <li class="<?php echo esc_attr('fs-tab-nav-' . $key) ?>"
                    <?php echo fs_parse_attr($tab['nav_attributes'] ?? []) ?>
                    :class="activeTab=='<?php echo esc_attr($key) ?>' ? 'fs-nav-link-active' : ''">
                    <a href="#tab-'<?php echo esc_attr($key) ?>"
                       x-on:click.prevent="activeTab='<?php echo esc_attr($key) ?>'; window.FS.setActiveTab('<?php echo esc_attr($key) ?>');"
                       data-tab="<?php echo esc_attr($key) ?> ">
                        <?php _e($tab['title'], 'f-shop') ?>
                    </a>
                </li>
            <?php
            endforeach;

            echo '</ul>'; ?>

            <div class="fs-tabs">
            <?php foreach ($product_tabs as $key_body => $tab_body) : ?>
                <div class="fs-tab fs-tab-<?php echo esc_attr($key_body) ?>"
                x-show="activeTab==='<?php echo esc_attr($key_body) ?>'"
                id="<?php echo esc_attr($key_body) ?>">
                <?php if (!empty($tab_body['fields'])) {
                    if (!empty($tab_body['title'])) {
                        echo '<h3>' . esc_html($tab_body['title']) . '</h3>';
                    }
                    if (!empty($tab_body['description'])) {
                        echo '<p class="description">' . esc_html($tab_body['description']) . '</p>';
                    }
                    foreach ($tab_body['fields'] as $key => $field) {
                        $filter_meta[$key] = $key;
                    }


                    foreach ($tab_body['fields'] as $key => $field) {
                        // если у поля есть атрибут "on" и он выключён то выходим из цикла
                        if (isset($field['on']) && $field['on'] != true) {
                            continue;
                        }
                        // если не указан атрибут type
                        if (empty($field['type'])) {
                            $field['type'] = 'text';
                        }
                        echo '<div class="fs-field-row clearfix">';

                        $key = apply_filters('fs_product_tab_admin_meta_key', $key, $field);
                        $field['source'] = 'post_meta';
                        $field['post_id'] = $post->ID;
                        $form_class->render_field($key, $field['type'], $field);
                        echo '</div>';
                    }
                } elseif (!empty($tab_body['template'])) {
                    $template_file = sprintf(FS_PLUGIN_PATH . 'templates/back-end/metabox/%s.php', $tab_body['template']);
                    if (file_exists($template_file)) {
                        include($template_file);
                    } else {
                        esc_html_e('Template file not found', 'f-shop');
                    }
                } elseif (!empty($tab_body['body'])) {
                    echo $tab_body['body'];
                }
                echo '</div>';

            endforeach;
            echo "</div>";
            echo '<div class="clearfix"></div>';

        }
        ?>
        <!-- The modal / dialog box, hidden somewhere near the footer -->
        <div id="fs-upsell-dialog" class="hidden fs-select-products-dialog" style="max-width:420px">
            <?php
            $args = array(
                'posts_per_page' => -1,
                'post_type' => FS_Config::get_data('post_type')
            );
            $query = new \WP_Query($args);
            if ($query->have_posts()) {
                echo '<ul>';
                while ($query->have_posts()) {
                    $query->the_post();
                    the_title('<li><span>', '</span><button class="button add-product" data-id="' . esc_attr(get_the_ID()) . '" data-field="fs_up_sell" data-name="' . esc_attr(get_the_title()) . '">' . esc_html__('choose', 'f-shop') . '</button></li>');
                }
                echo '</ul>';
            }
            wp_reset_query();
            ?>
        </div>
        <?php
        echo '</div>';
    }


    public function set_real_product_price($product_id = 0)
    {
        update_post_meta($product_id, '_fs_real_price', fs_get_price($product_id));
    }

    public function the_title()
    {
        echo esc_html($this->get_title());
    }

    public function the_thumbnail($size = 'thumbnail', $args = array())
    {
        fs_product_thumbnail($this->getId(), $size, $args);
    }

    public function getCount()
    {
        return (int)$this->count;
    }

    /**
     * Выводит форму отзывов, комментариев на странице товара
     *
     * @return void
     */
    public static function product_comments_form(): void
    {
        echo fs_frontend_template('product/tabs/comments');
    }

    // get minimal price of all products in the category
    public static function get_min_price_in_category()
    {
        $min_price = 0;
        if (fs_is_product_category()) {
            $taxonomy = \FS\FS_Config::get_data('product_taxonomy');
            $term = get_queried_object();
            $args = [
                'post_type' => \FS\FS_Config::get_data('post_type'),
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                    ]
                ]
            ];
            $query = new \WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $price = fs_get_price();
                    if ($price > 0 && $price < $min_price || $min_price == 0) {
                        $min_price = $price;
                    }
                }
            }
            wp_reset_postdata();
        }

        return floatval($min_price);
    }

// get max price of all products in the category
    public static function get_max_price_in_category()
    {
        $max_price = 0;
        if (fs_is_product_category()) {
            $taxonomy = \FS\FS_Config::get_data('product_taxonomy');
            $term = get_queried_object();
            $args = [
                'post_type' => \FS\FS_Config::get_data('post_type'),
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                    ]
                ]
            ];
            $query = new \WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $price = fs_get_price();
                    if ($price && $price > $max_price) {
                        $max_price = $price;
                    }
                }
            }
            wp_reset_postdata();
        }

        return floatval($max_price);
    }

// count all products in the category
    public static function get_count_products_in_category()
    {
        $count = 0;
        if (fs_is_product_category()) {
            $taxonomy = \FS\FS_Config::get_data('product_taxonomy');
            $term = get_queried_object();
            $args = [
                'post_type' => \FS\FS_Config::get_data('post_type'),
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                    ]
                ]
            ];
            $query = new \WP_Query($args);
            if ($query->have_posts()) {
                $count = $query->post_count;
            }
            wp_reset_postdata();
        }

        return intval($count);
    }
}