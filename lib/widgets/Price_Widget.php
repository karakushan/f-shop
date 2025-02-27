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
		$current_locale = isset($_GET['edit_lang']) ? $languages[$_GET['edit_lang']]['locale'] : $default_locale;
?>

		<div class="fs-widget-wrapper">
			<?php if (fs_option('fs_multi_language_support')): ?>
				<div class="form-row">
					<label for="<?php echo esc_attr($this->get_field_id('title_' . $current_locale)); ?>">
						<?php esc_html_e('Title', 'f-shop') ?></label>

					<div class="form-group">
						<?php foreach ($languages as $lang_name => $lang) : ?>
							<?php $field_name = 'title_' . $lang['locale']; ?>
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
		echo fs_frontend_template('widget/jquery-ui-slider/ui-slider');
		echo $args['after_widget'];
	}

	/*
	 * Saving widget settings
	 */
	public function update($new_instance, $old_instance)
	{
		//		$new_instance['title'] = isset( $new_instance['title'] ) ? trim( strip_tags( $new_instance['title'] ) ) : '';

		return $new_instance;
	}
}
