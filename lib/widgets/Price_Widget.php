<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 30.04.2018
 * Time: 19:07
 */

namespace FS\Widget;

/*
 * Слайдер цены
 */

class Price_Widget extends \WP_Widget {
	function __construct() {
		parent::__construct(
			'fs_price_widget',
			__('Filter by price range (F-SHOP)','f-shop'),
			array( 'description' => __('Filtering products by price range','f-shop') )
		);
	}

	/*
	 * Widget settings form
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
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
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		fs_range_slider();
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