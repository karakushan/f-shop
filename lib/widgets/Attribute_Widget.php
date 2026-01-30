<?php

namespace FS\Widget;

use FS\FS_Config;

/**
 * Class FS_Attribute_Widget.
 *
 * Creates an attribute filter widget
 */
class Attribute_Widget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'fs_attribute_widget',
            __('Product attribute filter', 'f-shop'),
            ['description' => __('Allows you to display a filter to filter products by attributes', 'f-shop')]
        );
    }

    /**
     * Widget settings form.
     *
     * @param array $instance
     *
     * @return string|void
     */
    public function form($instance)
    {
        $fs_config = new FS_Config();

        $title = !empty($instance['title']) ? $instance['title'] : '';
        $fs_att_group = !empty($instance['fs_att_group']) ? $instance['fs_att_group'] : '';
        $fs_att_types = !empty($instance['fs_att_types']) ? $instance['fs_att_types'] : '';
        $fs_screen_atts = !empty($instance['fs_screen_atts']) ? $instance['fs_screen_atts'] : 0;
        $fs_only_cats = !empty($instance['fs_only_cats']) ? $instance['fs_only_cats'] : '';
        $fs_hide_in_catalog = $instance['fs_hide_in_catalog'] ?? 0;
        $fs_hide_custom_values = $instance['fs_hide_custom_values'] ?? 0;

        $args = [
            'show_option_all' => '',
            'show_option_none' => '',
            'orderby' => 'name',
            'order' => 'ASC',
            'show_last_update' => 0,
            'show_count' => 0,
            'hide_empty' => 0,
            'child_of' => 0,
            'exclude' => '',
            'echo' => 1,
            'selected' => $fs_att_group,
            'hierarchical' => 1,
            'name' => $this->get_field_name('fs_att_group'),
            'id' => $this->get_field_id('fs_att_group'),
            'depth' => 1,
            'tab_index' => 0,
            'taxonomy' => $fs_config->data['features_taxonomy'],
            'hide_if_empty' => false,
            'value_field' => 'term_id',
            'class' => 'fs-select-field',
            'required' => false,
        ];
        $languages = FS_Config::get_languages();
        $default_locale = FS_Config::default_locale();
        $current_locale = isset($_GET['edit_lang']) ? $languages[$_GET['edit_lang']]['locale'] : $default_locale;
        ?>
		<div class="fs-widget-wrapper">
			<?php if (fs_option('fs_multi_language_support')) { ?>
				<div class="form-row">
					<label for="<?php echo esc_attr($this->get_field_id('title_'.$current_locale)); ?>">
						<?php esc_html_e('Title', 'f-shop'); ?></label>

					<div class="form-group">
						<?php foreach ($languages as $lang_name => $lang) { ?>
							<?php $field_name = 'title_'.$lang['locale']; ?>
							<input class="widefat title form-group__sub"
								type="<?php echo esc_attr($lang['locale'] == $current_locale ? 'text' : 'hidden'); ?>"
								id="<?php echo esc_attr($this->get_field_id($field_name)); ?>"
								name="<?php echo esc_attr($this->get_field_name($field_name)); ?>"
								value="<?php echo esc_attr(isset($instance[$field_name]) ? $instance[$field_name] : ''); ?>" />
						<?php } ?>
					</div>
				</div>
			<?php } else { ?>
				<div class="form-row">
					<label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
						<?php esc_html_e('Title', 'f-shop'); ?></label>

					<div class="form-group">
						<input class="widefat title form-group__sub"
							type="text"
							id="<?php echo esc_attr($this->get_field_id('title')); ?>"
							name="<?php echo esc_attr($this->get_field_name('title')); ?>"
							value="<?php echo esc_attr($instance['title']); ?>" />

					</div>
				</div>
			<?php } ?>
			<p>
				<label
					for="<?php echo esc_attr($this->get_field_id('fs_att_group')); ?>">
					<?php esc_html_e('Feature Group', 'f-shop'); ?>
				</label>
				<?php wp_dropdown_categories($args); ?>
			</p>
			<p>
				<span class="fs-custom-checkbox">
					<input type="checkbox" name="<?php echo esc_attr($this->get_field_name('fs_screen_atts')); ?>"
						value="1"
						id="<?php echo esc_attr($this->get_field_id('fs_screen_atts')); ?>" <?php checked(1, $fs_screen_atts); ?>>
					<label
						for="<?php echo esc_attr($this->get_field_id('fs_screen_atts')); ?>"><?php esc_html_e('Attributes for category only', 'f-shop'); ?></label>
				</span>
			</p>
			<p>
				<span class="fs-custom-checkbox">
					<input type="checkbox"
						name="<?php echo esc_attr($this->get_field_name('fs_hide_in_catalog')); ?>"
						value="1"
						id="<?php echo esc_attr($this->get_field_id('fs_hide_in_catalog')); ?>" <?php checked(1, $fs_hide_in_catalog); ?>>
					<label
						for="<?php echo esc_attr($this->get_field_id('fs_hide_in_catalog')); ?>"><?php esc_html_e('Don\'t show on catalog page', 'f-shop'); ?></label>
				</span>

			</p>
			<p>
				<label
					for="<?php echo esc_attr($this->get_field_id('fs_att_types')); ?>"><?php esc_html_e('Type', 'f-shop'); ?></label>
				<select name="<?php echo esc_attr($this->get_field_name('fs_att_types')); ?>"
					id="<?php echo esc_attr($this->get_field_id('fs_att_types')); ?>" class="fs-select-field">
					<option value="normal"><?php esc_html_e('Normal', 'f-shop'); ?></option>
					<option
						value="color" <?php selected('color', $fs_att_types); ?>><?php esc_html_e('Color', 'f-shop'); ?></option>
					<option
						value="image" <?php selected('image', $fs_att_types); ?>><?php esc_html_e('Image', 'f-shop'); ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id('fs_only_cats')); ?>">
					<?php esc_html_e('Show only in categories', 'f-shop'); ?>
				</label>
				<?php $args = [
				    'show_option_all' => '',
				    'show_option_none' => '',
				    'option_none_value' => -1,
				    'orderby' => 'name',
				    'order' => 'ASC',
				    'show_last_update' => 0,
				    'show_count' => 0,
				    'hide_empty' => 1,
				    'child_of' => 0,
				    'exclude' => '',
				    'echo' => 1,
				    'selected' => $fs_only_cats,
				    'hierarchical' => 1,
				    'multiple' => 1,
				    'name' => $this->get_field_name('fs_only_cats'),
				    'id' => $this->get_field_id('fs_only_cats'),
				    'class' => 'fs-select-field',
				    'depth' => 0,
				    'tab_index' => 0,
				    'taxonomy' => FS_Config::get_data('product_taxonomy'),
				    'hide_if_empty' => false,
				    'value_field' => 'term_id',
				    'required' => false,
				];

        wp_dropdown_categories($args); ?>
			</p>
			<p>
				<label for="<?php echo esc_attr($this->get_field_id('fs_hide_custom_values')); ?>">
					<?php esc_html_e('Скрыть значения из фильтра', 'f-shop'); ?>
				</label>
				<?php $args = [
				    'show_option_all' => '',
				    'show_option_none' => '',
				    'option_none_value' => -1,
				    'orderby' => 'name',
				    'order' => 'ASC',
				    'show_last_update' => 0,
				    'show_count' => 0,
				    'hide_empty' => 1,
				    'child_of' => $fs_att_group,
				    'exclude' => '',
				    'echo' => 1,
				    'selected' => $fs_hide_custom_values ?? '',
				    'hierarchical' => 1,
				    'multiple' => 1,
				    'name' => $this->get_field_name('fs_hide_custom_values'),
				    'id' => $this->get_field_id('fs_hide_custom_values'),
				    'class' => 'fs-select-field',
				    'depth' => 0,
				    'tab_index' => 0,
				    'taxonomy' => FS_Config::get_data('features_taxonomy'),
				    'hide_if_empty' => false,
				    'value_field' => 'term_id',
				    'required' => false,
				];

        wp_dropdown_categories($args); ?>
			</p>
		</div>
	<?php
    }

    /**
     * Display a widget.
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        $title = fs_option('fs_multi_language_support') && isset($instance['title_'.get_locale()])
            ? $instance['title_'.get_locale()] : $instance['title'];

        $title = apply_filters('widget_title', $title);
        $fs_only_cats = !empty($instance['fs_only_cats']) ? $instance['fs_only_cats'] : [];
        $fs_hide_in_catalog = !empty($instance['fs_hide_in_catalog']) ? $instance['fs_hide_in_catalog'] : 0;

        // We exit if we are on the page of the term taxonomy and the term is not found in the settings
        if (is_tax() && count($fs_only_cats) && !in_array(get_queried_object_id(), $fs_only_cats)) {
            return;
        }

        // Скрываем виджет на странице архива товаров
        if ($fs_hide_in_catalog && is_archive() && !is_tax()) {
            return;
        }

        echo $args['before_widget'] ?: '';
        echo $args['before_title'] ?: '';
        echo $title ?: '';
        echo $args['after_title'] ?: '';

        $current_category = fs_is_product_category() ? get_queried_object() : 0;
        $attr_group = get_term($instance['fs_att_group']);
        $attribute_id = $instance['fs_att_group'];
        $category_id = $current_category->term_id ?? 0;

        // Server-side caching: check for cached attributes
        $cache_key = 'fs_archive_attributes_' . $attribute_id;
        $cached_attributes = get_transient($cache_key);

        // Only use cache for archive (no specific category), not for category pages
        $use_cache = ($category_id == 0 && $cached_attributes !== false);
        $attributes_json = '';

        if ($use_cache) {
            // Use cached data - no AJAX needed
            $attributes_json = json_encode($cached_attributes);
            echo '<ul class="fs-attribute-widget" x-data="{attributes: ' . $attributes_json . ', loaded: true }">';
        } else {
            // No cache - will need AJAX to fetch data
            echo '<ul class="fs-attribute-widget" x-data="{attributes: [], loaded: false }"
                x-init="Alpine.store(\'FS\')?.getCategoryAttributes(' . $attribute_id . ', ' . $category_id . ')
                .then( (r) =>{ if(r.success===true) {attributes=r.data.attributes;} } )
                .finally(()=> loaded=true);">';
        } ?>
			<div class="fs-loader-block" :style="{'display':!loaded?'flex':'none'}" style="display:none;">
				<img src="<?php echo esc_url(FS_PLUGIN_URL); ?>/assets/img/loader-circle.svg" alt="loader">
			</div>
			<div x-data="{
            baseUrl: '<?php echo esc_url(site_url($_SERVER['REQUEST_URI'])); ?>',
            selectedAtts: [],
            init() {
                const params = new URLSearchParams(window.location.search);
                if(params.get('filter')) {
                    this.selectedAtts = params.get('filter').split(',').map(id => id.toString());
                }
            },
            updateUrl: function() {
                setTimeout(() => {
                    const currentUrl = new URL(this.baseUrl);
                    if(this.selectedAtts.length > 0) {
                    currentUrl.searchParams.set('filter', [...new Set(this.selectedAtts)].join(','));
                    } else {
                        currentUrl.searchParams.delete('filter');
                    }
                    window.location.href = currentUrl;
                }, 300);
            }
            }" x-init="init()">
				<template x-for="attribute in attributes" :key="attribute.term_id">
					<li :class="{'selected': selectedAtts.includes(attribute.term_id.toString())}">
						<label :for="'filter-<?php echo $attr_group->slug; ?>-'+attribute.term_id">
							<input type="checkbox"
								:id="'filter-<?php echo $attr_group->slug; ?>-'+attribute.term_id"
								:name="'<?php echo $attr_group->slug; ?>['+attribute.slug+']'"
								:value="attribute.term_id.toString()"
								x-model="selectedAtts"
								@change="updateUrl">
							<span x-html="attribute.name"></span>
						</label>
					</li>
				</template>
			</div>

		</ul>

<?php
            echo $args['after_widget'] ?: '';
    }

    /**
     *  Saving widget settings.
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        $instance = [];

        // Saving multilingual titles
        if (fs_option('fs_multi_language_support')) {
            foreach (FS_Config::get_languages() as $key => $language) {
                if ($language['locale'] == FS_Config::default_locale()) {
                    continue;
                }
                $name = 'title_'.$language['locale'];
                $instance[$name] = (!empty($new_instance[$name])) ? strip_tags($new_instance[$name]) : '';
            }
        }

        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['fs_att_group'] = intval($new_instance['fs_att_group']);
        $instance['fs_att_types'] = !empty($new_instance['fs_att_types']) ? strip_tags($new_instance['fs_att_types']) : '';
        $instance['fs_screen_atts'] = !empty($new_instance['fs_screen_atts']) ? strip_tags($new_instance['fs_screen_atts']) : 0;
        $instance['fs_only_cats'] = !empty($new_instance['fs_only_cats']) ? implode(',', $new_instance['fs_only_cats']) : '';
        $instance['fs_hide_in_catalog'] = isset($new_instance['fs_hide_in_catalog']) ? $new_instance['fs_hide_in_catalog'] : 0;
        $instance['fs_hide_custom_values'] = isset($new_instance['fs_hide_custom_values']) ? $new_instance['fs_hide_custom_values'] : [];

        return $instance;
    }
}
