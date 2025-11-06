<?php

namespace FS\Widget;

use FS\FS_Config;

/**
 * Class FS_Attribute_Widget
 *
 * Creates an attribute filter widget
 *
 * @package FS
 */
class Brand_Widget extends \WP_Widget {
	function __construct() {
		parent::__construct(
			'fs_brand',
			__( 'Фильтр по производителю', 'f-shop' ),
			array( 'description' => __( 'Позволяет отсортировать товары по производителю', 'f-shop' ) )
		);
	}

	/**
	 * Widget settings form
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
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

		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
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
        <p>
            <label
                    for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'f-shop' ) ?></label>
            <input class="widefat title"
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                   value="<?php echo esc_attr( $title ); ?>"/>
        </p>
			<?php endif; ?>
        </div>
		<?php
	}

	/**
	 * Display a widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$title_name = fs_option( 'fs_multi_language_support' )
		              && FS_Config::default_locale() != get_locale() ? 'title_' . get_locale() : 'title';

		if ( empty( $instance[ $title_name ] ) ) {
			$title_name = 'title';
		}

		$title = apply_filters( 'widget_title', $instance[ $title_name ] );
		echo $args['before_widget'];
		echo ! empty( $title ) ? $args['before_title'] . $title . $args['after_title'] : '';
		?>

        <ul class="fs-brand-filter"
            x-data="{brands: [], selectedBrands: () => {
                    const params = new URLSearchParams(window.location.search);
                    if(!params.get('brands')) return [];
                    return params.get('brands').split(',');
                }}"
            x-init="
            Alpine.store('FS').getCategoryBrands(<?php echo get_queried_object_id() ?>).then(r=>{ if(r.success) brands=r.data});
            $watch('selectedBrands', (value) => {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('brands',[...new Set(value)].join(','));
                window.location.href=currentUrl;
            });
">
            <template x-for="brand in brands" :key="brand.term_id">
                <li class="fs-checkbox-wrapper">
                    <input type="checkbox" class="checkStyle"
                           x-model="selectedBrands"
                           :name="'brands['+brand.slug+']'"
                           :value="brand.term_id"
                           :id="'fs-brand-'+brand.term_id">
                    <label :for="'fs-brand-'+brand.term_id"
                           class="checkLabel" x-text="brand.name"></label>
                </li>
            </template>


        </ul>

		<?php echo $args['after_widget'];
	}


	/**
	 *  Saving widget settings
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		// Saving multilingual titles
		if ( fs_option( 'fs_multi_language_support' ) ) {
			foreach ( FS_Config::get_languages() as $key => $language ) {
				if ( $language['locale'] == FS_Config::default_locale() ) {
					continue;
				}
				$name              = 'title_' . $language['locale'];
				$instance[ $name ] = ( ! empty( $new_instance[ $name ] ) ) ? strip_tags( $new_instance[ $name ] ) : '';
			}
		}

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}