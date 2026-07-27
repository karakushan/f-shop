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
        if ($raw_value === false || $raw_value === '') {
            $raw_value = '';
        }

        if (!in_array($field->get_base_name(), $this->multilingual_fields, true)) {
            return $raw_value !== '' ? $raw_value : $field->get_default_value();
        }

        $current_lang = $this->get_current_language();

        if (!empty($raw_value) && function_exists('wpm_is_ml_string') && wpm_is_ml_string($raw_value)) {
            if (function_exists('wpm_string_to_ml_array')) {
                $ml_array = wpm_string_to_ml_array($raw_value);

                if (isset($ml_array[$current_lang]) && $ml_array[$current_lang] !== '') {
                    return $ml_array[$current_lang];
                }
            }

            if ($key !== 'fs_seo_slug') {
                return function_exists('wpm_translate_string') ? wpm_translate_string($raw_value) : $raw_value;
            }
        } elseif ($raw_value !== '') {
            return $raw_value;
        }

        if ($key === 'fs_seo_slug') {
            return $this->get_generated_product_slug($current_lang);
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

        if ($key === 'fs_seo_slug' && trim((string) $value) === '') {
            $value = $this->get_generated_product_slug($current_lang);
        }

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

    /**
     * Get the language currently selected in the product editor.
     *
     * @return string
     */
    protected function get_current_language()
    {
        if (is_admin() && isset($_GET['edit_lang'])) {
            return sanitize_text_field($_GET['edit_lang']);
        }

        if (isset($_POST['edit_lang'])) {
            return sanitize_text_field($_POST['edit_lang']);
        }

        return function_exists('wpm_get_language') ? wpm_get_language() : 'ua';
    }

    /**
     * Generate a product SEO slug from the title for the selected language.
     *
     * @param string $language Language code.
     * @return string
     */
    protected function get_generated_product_slug($language)
    {
        $post = get_post($this->get_object_id());

        if (!$post || $post->post_type !== 'product') {
            return '';
        }

        $title = $post->post_title;
        if (function_exists('wpm_translate_string')) {
            $translated_title = wpm_translate_string($title, $language);
            if ($translated_title !== '') {
                $title = $translated_title;
            }
        }

        if (function_exists('fs_transliteration')) {
            $slug = fs_transliteration($title);
        } else {
            $slug = sanitize_title($title);
        }

        if ($slug === '') {
            return '';
        }

        return $this->make_product_slug_unique($slug, $language);
    }

    /**
     * Make a generated product slug unique for the selected language.
     *
     * @param string $slug Product slug.
     * @param string $language Language code.
     * @return string
     */
    protected function make_product_slug_unique($slug, $language)
    {
        $post_id = (int) $this->get_object_id();
        $candidate = $slug;
        $suffix = 0;

        while ($this->product_slug_exists($candidate, $language, $post_id)) {
            $suffix++;
            $candidate = $slug . '-' . ($suffix === 1 ? $post_id : $post_id . '-' . $suffix);
        }

        return $candidate;
    }

    /**
     * Check both current and legacy multilingual SEO slug storage.
     *
     * @param string $slug Product slug.
     * @param string $language Language code.
     * @param int    $exclude_post_id Current product ID.
     * @return bool
     */
    protected function product_slug_exists($slug, $language, $exclude_post_id)
    {
        global $wpdb;

        $locale = $language;
        if (function_exists('wpm_get_languages')) {
            $languages = wpm_get_languages();
            if (isset($languages[$language]['locale'])) {
                $locale = $languages[$language]['locale'];
            }
        }

        $legacy_keys = array_unique([
            'fs_seo_slug__' . strtolower($locale),
            'fs_seo_slug__' . $locale,
        ]);
        $legacy_placeholders = implode(',', array_fill(0, count($legacy_keys), '%s'));
        $like = '%[:' . $wpdb->esc_like($language) . ']' . $wpdb->esc_like($slug) . '[:]%';

        $query = $wpdb->prepare(
            "SELECT pm.post_id
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.post_id != %d
               AND p.post_type = 'product'
               AND p.post_status NOT IN ('trash', 'auto-draft')
               AND (
                   (pm.meta_key IN ($legacy_placeholders) AND pm.meta_value = %s)
                   OR (pm.meta_key = 'fs_seo_slug' AND pm.meta_value LIKE %s)
               )
             LIMIT 1",
            array_merge([$exclude_post_id], $legacy_keys, [$slug, $like])
        );

        return (bool) $wpdb->get_var($query);
    }

    public function delete(Field $field)
    {
        //		 $key = $this->get_key_for_field( $field );
        //		 delete_post_meta( $this->get_object_id(), $key );
    }
}
