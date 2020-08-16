<?php


namespace FS;


class FS_SEO
{
    function __construct()
    {
        /* Добавляет микроразметку типа Огранизация */
        add_action('fs_organization_microdata', [$this, 'schema_organization_microdata']);

        /* Выводит микроразмету типа LocalBusiness */
        add_action('fs_local_business_microdata', [$this, 'schema_local_business_microdata']);

        /* Позволяет регистрировать события для ремаркетинга Google Adwords */
        add_action('fs_adwords_remarketing', [$this, 'adwords_remarketing']);

        add_action('wp_footer', [$this, 'scripts_in_footer']);
        add_action('wp_head', [$this, 'scripts_in_head']);
    }

    /**
     * Выводит скрипты в шапке
     */
    public function scripts_in_head()
    {
        do_action('fs_local_business_microdata');
        do_action('fs_organization_microdata');
    }


    /**
     * Выводит скрипты в футере
     */
    public function scripts_in_footer()
    {

        if (fs_option('_fs_adwords_remarketing')) do_action('fs_adwords_remarketing');
    }

    /**
     * Sends the custom dimension to Google Analytics
     */
    public function adwords_remarketing()
    {
        $page_cart = fs_option('page_cart');
        $page_checkout = fs_option('page_checkout');
        $page_checkout_success = fs_option('page_success');
        ?>
        <script>
            <?php if (is_front_page()): ?>
            gtag('event', 'page_view', {
                'ecomm_prodid': '',
                'ecomm_pagetype': 'home',
                'ecomm_totalvalue': ''
            });

            <?php  elseif ($page_cart && is_page([$page_cart])): ?>
            gtag('event', 'page_view', {
                'ecomm_prodid': '',
                'ecomm_pagetype': 'cart',
                'ecomm_totalvalue': <?php  echo esc_attr(fs_get_total_amount()) ?>
            });
            <?php  elseif ($page_checkout && is_page([$page_checkout])): ?>
            gtag('event', 'page_view', {
                'ecomm_prodid': '',
                'ecomm_pagetype': 'cart',
                'ecomm_totalvalue': <?php  echo esc_attr(fs_get_total_amount()) ?>
            });
            <?php  elseif ($page_checkout_success && is_page($page_checkout_success)):
            $order = new FS_Orders();
            ?>
            gtag('event', 'page_view', {
                'ecomm_prodid': <?php echo esc_attr($order->get_last_order_id()) ?>,
                'ecomm_pagetype': 'purchase',
                'ecomm_totalvalue': <?php echo esc_attr($order->get_last_order_amount()); ?>
            });
            <?php  elseif (is_singular(FS_Config::get_data('post_type'))): ?>
            gtag('event', 'page_view', {
                'ecomm_prodid': <?php the_ID(); ?>,
                'ecomm_pagetype': 'product',
                'ecomm_totalvalue':  <?php echo esc_attr(fs_get_price(get_the_ID())); ?>
            });
            <?php endif; ?>
        </script>
        <?php
    }

    /**
     * Добавляет микроразметку типа Огранизация
     *
     * @see https://schema.org/Organization
     */
    public function schema_organization_microdata()
    {
        if (!(is_front_page() || is_home())) return;

        $custom_logo_id = get_theme_mod('custom_logo');
        $custom_logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : ' ';
        $micro_data = [
            "@context" => "http://www.schema.org",
            "@type" => 'Organization',
            "name" => fs_option('contact_name', get_bloginfo('name')),
            "url" => home_url('/'),
            "logo" => $custom_logo_url,
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => fs_option('contact_phone'),
                "contactType" => "customer service",
                "contactOption" => "TollFree",
                "areaServed" => "UA",
                "availableLanguage" => "Ukrainian"
            ],
        ];

        $micro_data = apply_filters('fs_schema_organization_microdata', $micro_data);

        echo '<script type=\'application/ld+json\'>';
        echo json_encode($micro_data);
        echo '</script>';
    }

    /**
     * Displays the micro-layout of the store on the main
     */
    public function schema_local_business_microdata()
    {
        if (!(is_front_page() || is_home())) return;

        $custom_logo_id = get_theme_mod('custom_logo');
        $custom_logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : ' ';
        $micro_data = [
            "@context" => "http://www.schema.org",
            "@type" => fs_option('contact_type', 'LocalBusiness'),
            "priceRange" => "$$",
            "name" => fs_option('contact_name', get_bloginfo('name')),
            "url" => home_url('/'),
            "logo" => $custom_logo_url,
            "image" => $custom_logo_url,
            "description" => get_bloginfo('description'),
            "address" => [
                "@type" => "PostalAddress",
                "streetAddress" => fs_option('contact_address'),
                "addressLocality" => fs_option('contact_city'),
                "postalCode" => fs_option('contact_zip'),
                "addressCountry" => fs_option('contact_country')
            ],
            "openingHours" => fs_option('opening_hours'),
            "telephone" => fs_option('contact_phone')
        ];


        $micro_data = apply_filters('fs_schema_local_business_microdata', $micro_data);

        echo '<script type=\'application/ld+json\'>';
        echo json_encode($micro_data);
        echo '</script>';
    }

}