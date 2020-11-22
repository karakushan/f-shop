<?php

namespace FS;

/**
 * Class FS_Attribute_Widget
 *
 * Creates an attribute filter widget
 *
 * @package FS
 */
class FS_Category_Widget extends \WP_Widget {
	function __construct() {
		parent::__construct(
			'fs_category_widget',
			__( 'Product category filter', 'f-shop' ),
			array( 'description' => __( 'Allows you to filter products by category', 'f-shop' ) )
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
		$title          = ! empty( $instance['title'] ) ? $instance['title'] : '';
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

		$title        = apply_filters( 'widget_title', $instance[ $title_name ] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo '<div class="widget-content">';
		do_action( 'fs_product_category_filter');
		echo '</div>';
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

		$instance['title']          = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}