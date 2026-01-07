<?php

/**
 * Created by PhpStorm.
 * User: karak
 * Date: 30.04.2018
 * Time: 19:07
 */

namespace FS\Widget;

use FS\FS_Config;

/*
 *  Widget for filtering by price
 */

class Price_Widget extends \WP_Widget
{
	function __construct()
	{
		parent::__construct(
			'fs_price_widget',
			__('Filter by price range (F-SHOP)', 'f-shop'),
			array('description' => __('Filtering products by price range', 'f-shop'))
		);
	}

	/*
	 * Widget settings form
	 */
	public function form($instance)
	{
		$languages      = FS_Config::get_languages();
		$default_locale = FS_Config::default_locale();
		
		// Get current locale from edit_lang parameter
		// Handle language combinations like 'ua-ru' by extracting the appropriate language
		$edit_lang = isset($_GET['edit_lang']) ? sanitize_text_field($_GET['edit_lang']) : '';
		$current_locale = $default_locale;
		
		if ($edit_lang) {
			// Handle language combinations first (e.g., 'ua-ru')
			if (strpos($edit_lang, '-') !== false) {
				$lang_parts = explode('-', $edit_lang);
				// Try the second language first (usually the target language in combinations like 'ua-ru' -> 'ru')
				if (count($lang_parts) > 1 && isset($languages[$lang_parts[1]])) {
					$current_locale = $languages[$lang_parts[1]]['locale'];
				} 
				// Fallback to first language
				elseif (count($lang_parts) > 0 && isset($languages[$lang_parts[0]])) {
					$current_locale = $languages[$lang_parts[0]]['locale'];
				}
			}
			// Try direct lookup if edit_lang is a single language code
			elseif (isset($languages[$edit_lang])) {
				$current_locale = $languages[$edit_lang]['locale'];
			}
			// If still not found, try to use wp-multilang
			elseif (function_exists('wpm_get_languages') && function_exists('wpm_get_language')) {
				$wpm_languages = wpm_get_languages();
				$wpm_lang = wpm_get_language();
				
				// If wp-multilang returned a valid language, use its locale
				if (isset($wpm_languages[$wpm_lang]) && isset($wpm_languages[$wpm_lang]['locale'])) {
					$wpm_locale = $wpm_languages[$wpm_lang]['locale'];
					// Find matching locale in FS languages
					foreach ($languages as $lang) {
						if ($lang['locale'] === $wpm_locale) {
							$current_locale = $wpm_locale;
							break;
						}
					}
				}
			}
		}
?>

		<div class="fs-widget-wrapper">
			<?php if (fs_option('fs_multi_language_support')): ?>
				<div class="form-row">
					<label for="<?php echo esc_attr($this->get_field_id('title_' . $current_locale)); ?>">
						<?php esc_html_e('Title', 'f-shop') ?></label>

					<div class="form-group">
						<?php 
						// Show field for default locale (uses 'title' without locale suffix)
						// Display as text input if current locale matches default locale, otherwise as hidden
						?>
						<input class="widefat title form-group__sub"
							type="<?php echo esc_attr($current_locale == $default_locale ? 'text' : 'hidden') ?>"
							id="<?php echo esc_attr($this->get_field_id('title')); ?>"
							name="<?php echo esc_attr($this->get_field_name('title')); ?>"
							value="<?php echo esc_attr(isset($instance['title']) ? $instance['title'] : ''); ?>" />
						
						<?php foreach ($languages as $lang_name => $lang) : ?>
							<?php 
							// Skip default locale as it's handled above
							if ($lang['locale'] == $default_locale) {
								continue;
							}
							$field_name = 'title_' . $lang['locale']; 
							?>
							<input class="widefat title form-group__sub"
								type="<?php echo esc_attr($lang['locale'] == $current_locale ? 'text' : 'hidden') ?>"
								id="<?php echo esc_attr($this->get_field_id($field_name)); ?>"
								name="<?php echo esc_attr($this->get_field_name($field_name)); ?>"
								value="<?php echo esc_attr(isset($instance[$field_name]) ? $instance[$field_name] : ''); ?>" />
						<?php endforeach; ?>
					</div>
				</div>
			<?php else: ?>
				<div class="form-row">
					<label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
						<?php esc_html_e('Title', 'f-shop') ?></label>

					<div class="form-group">
						<input class="widefat title form-group__sub"
							type="text"
							id="<?php echo esc_attr($this->get_field_id('title')); ?>"
							name="<?php echo esc_attr($this->get_field_name('title')); ?>"
							value="<?php echo esc_attr(isset($instance['title']) ? $instance['title'] : ''); ?>" />

					</div>
				</div>
			<?php endif ?>
		</div>

<?php
	}

	/**
	 * Responsible for displaying the widget on the site
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance)
	{
		$title = fs_option('fs_multi_language_support') && isset($instance['title_' . get_locale()])
			? $instance['title_' . get_locale()] : $instance['title'];

		$title = apply_filters('widget_title', $title);

		echo $args['before_widget'];
		if (! empty($title)) {
			echo $args['before_title'] . esc_html($title) . $args['after_title'];
		}

		// Wrap price slider with Alpine loader similar to Attribute_Widget
		?>
		<div x-data="{ loaded: false }"
			 x-on:fs_price_slider_loaded.window="loaded = true">
			<div class="fs-loader-block"
				 :style="{'display': !loaded ? 'flex' : 'none'}"
				 style="display:none;">
				<img src="<?php echo esc_url(FS_PLUGIN_URL); ?>/assets/img/loader-circle.svg" alt="loader">
			</div>

			<div :style="{'display': loaded ? 'block' : 'none'}">
				<?php echo fs_frontend_template('widget/jquery-ui-slider/ui-slider'); ?>
			</div>
		</div>
		<?php

		echo $args['after_widget'];
	}

	/*
	 * Saving widget settings
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = array();
		
		// Saving multilingual titles
		if (fs_option('fs_multi_language_support')) {
			foreach (FS_Config::get_languages() as $key => $language) {
				if ($language['locale'] == FS_Config::default_locale()) {
					continue;
				}
				$name              = 'title_' . $language['locale'];
				$instance[$name] = (! empty($new_instance[$name])) ? strip_tags($new_instance[$name]) : '';
			}
		}
		
		$instance['title'] = (! empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		
		return $instance;
	}
}
