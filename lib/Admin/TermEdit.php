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
                    // Add to multilingual fields array
                    $multilingual_fields[] = mb_strtolower($name);

                    // Get current language for display
                    $current_lang = 'ua'; // Default
                    if (is_admin() && isset($_GET['edit_lang'])) {
                        $current_lang = sanitize_text_field($_GET['edit_lang']);
                    } elseif (function_exists('wpm_get_language')) {
                        $current_lang = wpm_get_language();
                    }

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
