<?php

namespace FS\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use FS\FS_Config;
use FS\FS_Taxonomy;

class TermEdit
{
    private $allowed_types = [
        'media_gallery',
        'text',
        'textarea',
        'checkbox',
        'radio',
        'association',
        'select',
        'html',
        'multiselect',
        'image',
        'rich_text',
        'color',
    ];

    public function __construct()
    {
        add_action('carbon_fields_register_fields', [$this, 'carbon_register_term_meta']);
        add_action('saved_' . FS_Config::get_data('product_taxonomy'), [
            $this,
            'saved_product_category_callback',
        ]);
    }

    /**
     * Registers term meta fields based on taxonomy settings and configurations.
     *
     * This function iterates through the taxonomy fields, creates a container
     * for term metadata, and adds appropriate fields based on the allowed types
     * as well as multilanguage configuration, if applicable.
     *
     * @return void
     */
    public function carbon_register_term_meta()
    {
        $fields = FS_Taxonomy::get_taxonomy_fields();
        $current_lang = $this->get_current_admin_language();
        $default_lang = $this->get_default_admin_language();

        foreach ($fields as $key => $term_fields) {
            $container = Container::make('term_meta', __('Додаткові налаштування'));

            // Create datastore instance for multilingual fields
            $multilingual_datastore = new TermMetaDatastore();
            $multilingual_fields = [];

            $fs = [];
            foreach ($term_fields as $name => $field) {
                if (!isset($field['type']) || !in_array($field['type'], $this->allowed_types)) {
                    continue;
                }

                if (isset($field['args']['multilang']) && $field['args']['multilang'] == true) {
                    if (!empty($field['args']['disable_default_locale']) && $current_lang === $default_lang) {
                        $fs[] = $this->make_default_locale_readonly_field($name, $field['name'], $current_lang);
                        continue;
                    }

                    // Add to multilingual fields array
                    $multilingual_fields[] = mb_strtolower($name);

                    // Create single field with language indicator in label
                    $label = $field['name'] . ' (' . strtoupper($current_lang) . ')';
                    $f = $this->make_field($field, $name, $label);
                    $f->set_datastore($multilingual_datastore);
                    $fs[] = $f;
                } else {
                    $fs[] = $this->make_field($field, $name, $field['name']);
                }
            }

            // Set multilingual fields if any exist
            if (!empty($multilingual_fields)) {
                $multilingual_datastore->set_multilingual_fields($multilingual_fields);
            }

            $container->where('term_taxonomy', '=', $key);
            $container->add_fields($fs);
        }
    }

    /**
     * Returns the current admin language code.
     *
     * @return string
     */
    private function get_current_admin_language()
    {
        if (is_admin() && isset($_GET['edit_lang'])) {
            return sanitize_text_field($_GET['edit_lang']);
        }

        if (function_exists('wpm_get_language')) {
            return (string) wpm_get_language();
        }

        return 'ua';
    }

    /**
     * Returns the default admin language code.
     *
     * @return string
     */
    private function get_default_admin_language()
    {
        if (function_exists('wpm_get_default_language')) {
            return (string) wpm_get_default_language();
        }

        return (string) FS_Config::default_locale();
    }

    /**
     * Creates a read-only HTML field for default locale slug display.
     *
     * @param string $name
     * @param string $label
     * @param string $current_lang
     *
     * @return Field
     */
    private function make_default_locale_readonly_field($name, $label, $current_lang)
    {
        $field = Field::make('html', mb_strtolower($name) . '__default_locale_display', $label . ' (' . strtoupper($current_lang) . ')');
        $term_slug = $this->get_current_term_slug();
        $description = $term_slug !== ''
            ? __('For the default language, this value is taken from the main term slug and cannot be edited here.', 'f-shop')
            : __('For the default language, this value will be taken from the main term slug after the term is saved.', 'f-shop');

        $html = sprintf(
            '<input type="text" class="regular-text" value="%s" readonly="readonly" disabled="disabled" /><p>%s</p>',
            esc_attr($term_slug),
            esc_html($description)
        );

        $field->set_html($html);

        return $field;
    }

    /**
     * Returns the slug of the currently edited term.
     *
     * @return string
     */
    private function get_current_term_slug()
    {
        if (empty($_GET['tag_ID'])) {
            return '';
        }

        $term = get_term((int) $_GET['tag_ID']);

        if (!$term || is_wp_error($term)) {
            return '';
        }

        return (string) $term->slug;
    }

    /**
     * Creates and configures a field object based on the provided parameters and field configuration.
     *
     * @param array  $field an associative array defining the field's attributes, including type, width, required status, subtype, options, types, template, and help text
     * @param string $name  the name of the field
     * @param string $label the label for the field
     *
     * @return Field the configured field object
     */
    public function make_field($field, $name, $label)
    {
        $f = Field::make($field['type'], mb_strtolower($name), $label);
        if (isset($field['width'])) {
            $f->set_width($field['width']);
        }

        if (isset($field['required'])) {
            $f->set_required($field['required']);
        }

        if (isset($field['subtype'])) {
            $f->set_attribute('type', $field['subtype']);
        }

        if (in_array($field['type'], ['select', 'radio', 'multiselect']) && isset($field['options'])) {
            /* @var \Carbon_Fields\Field\Predefined_Options_Field $f */
            $f->set_options($field['options']);
        }

        if ($field['type'] == 'association' && isset($field['types'])) {
            /* @var \Carbon_Fields\Field\Association_Field $f */
            $f->set_types([
                [
                    'type' => 'post',
                    'post_type' => $field['types'],
                ],
            ]);
        }

        if ($field['type'] == 'html' && isset($field['template'])) {
            /* @var \Carbon_Fields\Field\Html_Field $f */
            ob_start();
            include $field['template'];
            $f->set_html(ob_get_clean());
        }

        // Color field specific configurations
        if ($field['type'] == 'color') {
            /* @var \Carbon_Fields\Field\Color_Field $f */
            // Set palette if provided
            if (isset($field['palette']) && is_array($field['palette'])) {
                $f->set_palette($field['palette']);
            }

            // Enable alpha if specified
            if (isset($field['alpha']) && $field['alpha'] === true) {
                $f->set_alpha_enabled(true);
            }
        }

        if (isset($field['help'])) {
            $f->set_help_text($field['help']);
        }

        return $f;
    }

    /**
     * Fires after the term is saved to the database.
     *
     * @return void
     */
    public function saved_product_category_callback()
    {
        if (fs_option('fs_localize_slug')) {
            flush_rewrite_rules();
        }
    }
}
