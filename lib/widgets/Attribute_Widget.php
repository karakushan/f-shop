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
class Attribute_Widget extends \WP_Widget {
	function __construct() {
		parent::__construct(
			'fs_attribute_widget',
			__( 'Product attribute filter', 'f-shop' ),
			array( 'description' => __( 'Allows you to display a filter to filter products by attributes', 'f-shop' ) )
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


		$fs_config = new FS_Config();

		$title                 = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$fs_att_group          = ! empty( $instance['fs_att_group'] ) ? $instance['fs_att_group'] : '';
		$fs_att_types          = ! empty( $instance['fs_att_types'] ) ? $instance['fs_att_types'] : '';
		$fs_screen_atts        = ! empty( $instance['fs_screen_atts'] ) ? $instance['fs_screen_atts'] : 0;
		$fs_only_cats          = ! empty( $instance['fs_only_cats'] ) ? $instance['fs_only_cats'] : '';
		$fs_hide_in_catalog    = $instance['fs_hide_in_catalog'] ?? 0;
		$fs_hide_custom_values = $instance['fs_hide_custom_values'] ?? 0;


		$args      = array(
			'show_option_all'  => '',
			'show_option_none' => '',
			'orderby'          => 'name',
			'order'            => 'ASC',
			'show_last_update' => 0,
			'show_count'       => 0,
			'hide_empty'       => 0,
			'child_of'         => 0,
			'exclude'          => '',
			'echo'             => 1,
			'selected'         => $fs_att_group,
			'hierarchical'     => 1,
			'name'             => $this->get_field_name( 'fs_att_group' ),
			'id'               => $this->get_field_id( 'fs_att_group' ),
			'depth'            => 1,
			'tab_index'        => 0,
			'taxonomy'         => $fs_config->data['features_taxonomy'],
			'hide_if_empty'    => false,
			'value_field'      => 'term_id',
			'class'            => 'fs-select-field',
			'required'         => false,
		);
		$languages = FS_Config::get_languages();
		?>
		<div class="fs-widget-wrapper">
			<div class="form-row">
				<label
					for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'f-shop' ) ?></label>
				<?php if ( fs_option( 'fs_multi_language_support' ) ) : ?>
				<div class="form-group">
					<span class="form-group__sub"><?php echo esc_html( FS_Config::default_language_name() ) ?></span>
					<?php endif; ?>
					<input class="widefat title"
					       id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
					       value="<?php echo esc_attr( $title ); ?>"/>
					<?php if ( fs_option( 'fs_multi_language_support' ) ) : ?>
				</div>
			<?php endif; ?>
				<?php

				if ( fs_option( 'fs_multi_language_support' ) ) {
					foreach ( FS_Config::get_languages() as $key => $language ) {
						if ( $language['locale'] == FS_Config::default_locale() ) {
							continue;
						}
						$name  = 'title_' . $language['locale'];
						$title = ! empty( $instance[ $name ] ) ? $instance[ $name ] : '';
						?>
						<div class="form-group">
							<span class="form-group__sub"><?php echo $key ?></span>
							<input class="widefat title form-group__sub"
							       id="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>"
							       name="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>"
							       value="<?php echo esc_attr( $title ); ?>"/>
						</div>
					<?php }
				}
				?>
			</div>
			<p>
				<label
					for="<?php echo esc_attr( $this->get_field_id( 'fs_att_group' ) ); ?>">
					<?php esc_html_e( 'Feature Group', 'f-shop' ) ?>
				</label>
				<?php wp_dropdown_categories( $args ); ?>
			</p>
			<p>
                <span class="fs-custom-checkbox">
                    <input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'fs_screen_atts' ) ); ?>"
                           value="1"
                           id="<?php echo esc_attr( $this->get_field_id( 'fs_screen_atts' ) ); ?>" <?php checked( 1, $fs_screen_atts ) ?>>
                <label
	                for="<?php echo esc_attr( $this->get_field_id( 'fs_screen_atts' ) ); ?>"><?php esc_html_e( 'Attributes for category only', 'f-shop' ) ?></label>
                </span>
			</p>
			<p>
                <span class="fs-custom-checkbox">
                       <input type="checkbox"
                              name="<?php echo esc_attr( $this->get_field_name( 'fs_hide_in_catalog' ) ); ?>"
                              value="1"
                              id="<?php echo esc_attr( $this->get_field_id( 'fs_hide_in_catalog' ) ); ?>" <?php checked( 1, $fs_hide_in_catalog ) ?>>
                <label
	                for="<?php echo esc_attr( $this->get_field_id( 'fs_hide_in_catalog' ) ); ?>"><?php esc_html_e( 'Don\'t show on catalog page', 'f-shop' ) ?></label>
                </span>

			</p>
			<p>
				<label
					for="<?php echo esc_attr( $this->get_field_id( 'fs_att_types' ) ); ?>"><?php esc_html_e( 'Type', 'f-shop' ) ?></label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'fs_att_types' ) ); ?>"
				        id="<?php echo esc_attr( $this->get_field_id( 'fs_att_types' ) ); ?>" class="fs-select-field">
					<option value="normal"><?php esc_html_e( 'Normal', 'f-shop' ) ?></option>
					<option
						value="color" <?php selected( 'color', $fs_att_types ) ?>><?php esc_html_e( 'Color', 'f-shop' ) ?></option>
					<option
						value="image" <?php selected( 'image', $fs_att_types ) ?>><?php esc_html_e( 'Image', 'f-shop' ) ?></option>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'fs_only_cats' ) ) ?>">
					<?php esc_html_e( 'Show only in categories', 'f-shop' ); ?>
				</label>
				<?php $args = array(
					'show_option_all'   => '',
					'show_option_none'  => '',
					'option_none_value' => - 1,
					'orderby'           => 'name',
					'order'             => 'ASC',
					'show_last_update'  => 0,
					'show_count'        => 0,
					'hide_empty'        => 1,
					'child_of'          => 0,
					'exclude'           => '',
					'echo'              => 1,
					'selected'          => $fs_only_cats,
					'hierarchical'      => 1,
					'multiple'          => 1,
					'name'              => $this->get_field_name( 'fs_only_cats' ),
					'id'                => $this->get_field_id( 'fs_only_cats' ),
					'class'             => 'fs-select-field',
					'depth'             => 0,
					'tab_index'         => 0,
					'taxonomy'          => FS_Config::get_data( 'product_taxonomy' ),
					'hide_if_empty'     => false,
					'value_field'       => 'term_id',
					'required'          => false,
				);

				wp_dropdown_categories( $args ); ?>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'fs_hide_custom_values' ) ) ?>">
					<?php esc_html_e( 'Скрыть значения из фильтра', 'f-shop' ); ?>
				</label>
				<?php $args = array(
					'show_option_all'   => '',
					'show_option_none'  => '',
					'option_none_value' => - 1,
					'orderby'           => 'name',
					'order'             => 'ASC',
					'show_last_update'  => 0,
					'show_count'        => 0,
					'hide_empty'        => 1,
					'child_of'          => $fs_att_group,
					'exclude'           => '',
					'echo'              => 1,
					'selected'          => $fs_hide_custom_values ?? '',
					'hierarchical'      => 1,
					'multiple'          => 1,
					'name'              => $this->get_field_name( 'fs_hide_custom_values' ),
					'id'                => $this->get_field_id( 'fs_hide_custom_values' ),
					'class'             => 'fs-select-field',
					'depth'             => 0,
					'tab_index'         => 0,
					'taxonomy'          => FS_Config::get_data( 'features_taxonomy' ),
					'hide_if_empty'     => false,
					'value_field'       => 'term_id',
					'required'          => false,
				);

				do_action( 'qm/debug', $args );

				wp_dropdown_categories( $args ); ?>
			</p>
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

		$title              = apply_filters( 'widget_title', $instance[ $title_name ] );
		$type               = ! empty( $instance['fs_att_types'] ) ? $instance['fs_att_types'] : 'text';
		$fs_only_cats       = ! empty( $instance['fs_only_cats'] ) ? explode( ',', $instance['fs_only_cats'] ) : [];
		$fs_hide_in_catalog = ! empty( $instance['fs_hide_in_catalog'] ) ? $instance['fs_hide_in_catalog'] : 0;

		$widget_attributes = apply_filters( 'fs_widget_attributes',
			[
				'type'           => $type,
				'current_screen' => intval( $instance['fs_screen_atts'] ),
				'exclude'        => isset( $instance['fs_hide_custom_values'] ) ? $instance['fs_hide_custom_values'] : []
			], $instance );

		//We exit if we are on the page of the term taxonomy and the term is not found in the settings
		if ( is_tax() && count( $fs_only_cats ) && ! in_array( get_queried_object_id(), $fs_only_cats ) ) {
			return;
		}

		// Скрываем виджет на странице архива товаров
		if ( $fs_hide_in_catalog && is_archive() && ! is_tax() ) {
			return;
		}

		ob_start();
		do_action( 'fs_attr_filter', $instance['fs_att_group'], $widget_attributes );
		$html = ob_get_clean();

		if ( empty( $html ) ) {
			return;
		}

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo $html;

		echo $args['after_widget'];
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

		$instance['title']                 = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['fs_att_group']          = intval( $new_instance['fs_att_group'] );
		$instance['fs_att_types']          = ! empty( $new_instance['fs_att_types'] ) ? strip_tags( $new_instance['fs_att_types'] ) : '';
		$instance['fs_screen_atts']        = ! empty( $new_instance['fs_screen_atts'] ) ? strip_tags( $new_instance['fs_screen_atts'] ) : 0;
		$instance['fs_only_cats']          = ! empty( $new_instance['fs_only_cats'] ) ? implode( ',', $new_instance['fs_only_cats'] ) : '';
		$instance['fs_hide_in_catalog']    = isset( $new_instance['fs_hide_in_catalog'] ) ? $new_instance['fs_hide_in_catalog'] : 0;
		$instance['fs_hide_custom_values'] = isset( $new_instance['fs_hide_custom_values'] ) ? $new_instance['fs_hide_custom_values'] : [];

		return $instance;
	}
}