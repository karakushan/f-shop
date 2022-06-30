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
class Availability_Widget extends \WP_Widget {
	function __construct() {
		parent::__construct(
			'fs_availability',
			__( 'Фильтр по наличию', 'f-shop' ),
			array( 'description' => __( 'Позволяет отсортировать товары по наличию на складе', 'f-shop' ) )
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

		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
        <p>
            <label
                    for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'f-shop' ) ?></label>
            <input class="widefat title"
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                   value="<?php echo esc_attr( $title ); ?>"/>
        </p>
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

		$title  = apply_filters( 'widget_title', $instance[ $title_name ] );

		echo $args['before_widget'];
		echo ! empty( $title ) ? $args['before_title'] . $title . $args['after_title'] : ''; ?>
        <ul class="fs-brand-filter">

                <li class="fs-checkbox-wrapper">
                    <input type="radio" class="checkStyle"
                           data-fs-action="filter"
                           data-fs-redirect="<?php  echo esc_url(fs_remove_url_param('availability'))  ?>"
                           name="availability"
                           value="<?php  echo esc_url(fs_remove_url_param('availability'))  ?>"
                           id="fs-availability-all" <?php echo empty($_REQUEST['availability']) ? 'checked' : ''; ?>>
                    <label for="fs-availability-all"
                           class="checkLabel"> <?php esc_html_e('All offers','f-shop'); ?></label>
                </li>
            <li class="fs-checkbox-wrapper">
                    <input type="radio" class="checkStyle"
                           data-fs-action="filter"
                           data-fs-redirect="<?php  echo esc_url(fs_remove_url_param('availability'))  ?>"
                           name="availability"
                           value="<?php  echo esc_url(add_query_arg(['availability'=>1]))  ?>"
                           id="fs-availability-available" <?php echo isset($_REQUEST['availability']) && $_REQUEST['availability']=='1' ?'checked':'' ; ?>>
                    <label for="fs-availability-available"
                           class="checkLabel"> <?php esc_html_e('Only in stock','f-shop'); ?></label>
                </li>
            <li class="fs-checkbox-wrapper">
                    <input type="radio" class="checkStyle"
                           data-fs-action="filter"
                           data-fs-redirect="<?php  echo esc_url(fs_remove_url_param('availability'))  ?>"
                           name="availability"
                           value="<?php  echo esc_url(add_query_arg(['availability'=>0]))  ?>"
                           id="fs-availability-not-available" <?php echo isset($_REQUEST['availability']) && $_REQUEST['availability']=='0' ?'checked':'' ; ?>>
                    <label for="fs-availability-not-available"
                           class="checkLabel"> <?php esc_html_e('On order','f-shop'); ?></label>
                </li>

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