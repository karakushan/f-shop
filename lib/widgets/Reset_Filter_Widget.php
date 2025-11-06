<?php

namespace FS\Widget;

use FS\FS_Config;

class Reset_Filter_Widget extends \WP_Widget {
	public function __construct() {
		$class = 'widget_fs_reset_filter_widget';

		if ( isset( $_GET['fs_filter'] ) ) {
			$class .= ' active';
		}

		parent::__construct(
			'fs_reset_filter_widget',
			__( 'Filter reset (F-SHOP)', 'f-shop' ),
			array(
				'description' => __( 'Allows you to clear selected filters on the product catalog page', 'f-shop' ),
				'classname'   => $class
			)
		);
	}

	/*
	 * Widget settings form
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
            <div class="form-row">
                <label
                        for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'f-shop' ) ?></label>
				<?php if ( fs_option( 'fs_multi_language_support' ) ) : ?>
                <div class="form-group">
					<?php 
					// Show field for default locale (uses 'title' without locale suffix)
					// Display as text input if current locale matches default locale, otherwise as hidden
					?>
					<input class="widefat title form-group__sub"
						type="<?php echo esc_attr($current_locale == $default_locale ? 'text' : 'hidden') ?>"
						id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
						value="<?php echo esc_attr( $title ); ?>"/>
					<?php if ($current_locale == $default_locale): ?>
					<span class="form-group__sub"><?php echo esc_html( FS_Config::default_language_name() ) ?></span>
					<?php endif; ?>
					
					<?php
					foreach ( FS_Config::get_languages() as $key => $language ) {
						if ( $language['locale'] == FS_Config::default_locale() ) {
							continue;
						}
						$name  = 'title_' . $language['locale'];
						$title = ! empty( $instance[ $name ] ) ? $instance[ $name ] : '';
						?>
                        <div class="form-group">
                            <input class="widefat title form-group__sub"
                                   type="<?php echo esc_attr($language['locale'] == $current_locale ? 'text' : 'hidden') ?>"
                                   id="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>"
                                   name="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>"
                                   value="<?php echo esc_attr( $title ); ?>"/>
							<?php if ($language['locale'] == $current_locale): ?>
                            <span class="form-group__sub"><?php echo $key ?></span>
							<?php endif; ?>
                        </div>
					<?php }
					?>
                </div>
				<?php else: ?>
					<input class="widefat title"
						   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
						   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
						   value="<?php echo esc_attr( $title ); ?>"/>
				<?php endif; ?>
            </div>
        </div>

		<?php
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$title_name = fs_option( 'fs_multi_language_support' )
		              && FS_Config::default_locale() != get_locale() ? 'title_' . get_locale() : 'title';
		if ( empty( $instance[ $title_name ] ) ) {
			$title_name = 'title';
		}

		$title = !empty($instance[ $title_name ]) ? apply_filters( 'widget_title', $instance[ $title_name ] ) : '';

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		if ( isset( $_GET['fs_filter'] ) ) {
			$base_filter = remove_query_arg( 'attributes', $_SERVER['REQUEST_URI'] );
			$links       = [];
			if ( ! empty( $_GET['price_start'] ) && ! empty( $_GET['price_end'] ) ) {
				$links[] = [
					'url'  => remove_query_arg( [ 'price_start', 'price_end' ] ),
					'name' => $_GET['price_start'] . '-' . $_GET['price_end'] . ' ' . fs_currency()
				];
			}
			if ( ! empty( $_GET['attributes'] ) ) {
				foreach ( $_GET['attributes'] as $key => $attribute ) {
					parse_str( remove_query_arg( $key, http_build_query( $_GET['attributes'] ) ), $attributes );
					$links[] = [
						'url'  => add_query_arg( [ 'attributes' => $attributes ], $base_filter ),
						'name' => get_term_field( 'name', $attribute )
					];
				}
			}
			global $wp_query;
			$count = intval( $wp_query->found_posts );
			echo fs_frontend_template( 'widget/reset-filter/reset-filter', [ 'vars' => compact( 'links', 'count' ) ] );
		}
		echo $args['after_widget'];
	}

	/*
	 * сохранение настроек виджета
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