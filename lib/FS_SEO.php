<?php


namespace FS;


class FS_SEO
{
    function __construct()
    {
        /* Добавляет микроразметку типа Огранизация */
        add_action('wp_head', [$this, 'schema_organization_microdata']);

        /* Выводит микроразмету типа LocalBusiness */
        add_action('wp_head', [$this, 'schema_local_business_microdata']);
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