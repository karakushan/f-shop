<?php

namespace FS\Admin;

use Carbon_Fields\Field\Field;

class PostMetaDatastore extends \Carbon_Fields\Datastore\Datastore
{
    /**
     * An array of field names that are multilingual.
     *
     * @var array
     */
    protected $multilingual_fields = [];

    /**
     * Set the multilingual fields.
     *
     * @param array $fields
     *
     * @return self
     */
    public function set_multilingual_fields($fields)
    {
        $this->multilingual_fields = $fields;

        return $this;
    }

    public function init()
    {
        // TODO: Implement init() method.
    }

    protected function get_key_for_field(Field $field)
    {
        return $field->get_base_name();
    }

    public function load(Field $field)
    {
        $key = $this->get_key_for_field($field);
        $raw_value = get_post_meta($this->get_object_id(), $key, true);

        if (!in_array($field->get_base_name(), $this->multilingual_fields, true)) {
            return $raw_value !== '' ? $raw_value : $field->get_default_value();
        }

        if (!empty($raw_value) && function_exists('wpm_is_ml_string') && wpm_is_ml_string($raw_value)) {
            $current_lang = 'ua'; // Default
            if (is_admin() && isset($_GET['edit_lang'])) {
                $current_lang = sanitize_text_field($_GET['edit_lang']);
            } elseif (function_exists('wpm_get_language')) {
                $current_lang = wpm_get_language();
            }

            if (function_exists('wpm_string_to_ml_array')) {
                $ml_array = wpm_string_to_ml_array($raw_value);

                return isset($ml_array[$current_lang]) ? $ml_array[$current_lang] : '';
            }

            return function_exists('wpm_translate_string') ? wpm_translate_string($raw_value) : $raw_value;
        }

        return $raw_value !== '' ? $raw_value : $field->get_default_value();
    }

    public function get_raw_meta($key)
    {
        global $wpdb;
        $meta_value = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $this->get_object_id(), $key));

        return $meta_value;
    }

    public function save(Field $field)
    {
        $key = $this->get_key_for_field($field);
        $value = $field->get_value();

        if (!in_array($field->get_base_name(), $this->multilingual_fields, true)) {
            update_post_meta($this->get_object_id(), $key, $value);

            return;
        }

        $current_lang = isset($_POST['edit_lang']) ? sanitize_text_field($_POST['edit_lang']) :
            (isset($_GET['edit_lang']) ? sanitize_text_field($_GET['edit_lang']) : 'ua');

        $existing_value = $this->get_raw_meta($key);

        $ml_array = [];

        if (!empty($existing_value) && function_exists('wpm_is_ml_string') && wpm_is_ml_string($existing_value)) {
            $ml_array = wpm_string_to_ml_array($existing_value);
        } elseif (!empty($existing_value)) {
            if (function_exists('wpm_get_default_language')) {
                $default_lang = wpm_get_default_language();
                $ml_array[$default_lang] = $existing_value;
            } else {
                $ml_array['ua'] = $existing_value;
            }
        }

        $ml_array[$current_lang] = $value;

        if (function_exists('wpm_ml_array_to_string')) {
            $new_value = wpm_ml_array_to_string($ml_array);
            update_post_meta($this->get_object_id(), $key, $new_value);
        } else {
            update_post_meta($this->get_object_id(), $key, $value);
        }
    }

    public function delete(Field $field)
    {
        //		 $key = $this->get_key_for_field( $field );
        //		 delete_post_meta( $this->get_object_id(), $key );
    }
}
