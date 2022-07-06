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
class Category_Widget extends \WP_Widget {
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
		$title              = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$only_subcategories = isset( $instance['only_subcategories'] ) && $instance['only_subcategories'] == 1 ? 1 : 0
		?>
        <div class="fs-widget-wrapper">
            <p class="form-row">
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
            </p>
            <p class="form-row">
                <span class="fs-custom-checkbox">
                  <input type="checkbox"
                         name="<?php echo esc_attr( $this->get_field_name( 'only_subcategories' ) ); ?>"
                         id="<?php echo esc_attr( $this->get_field_id( 'only_subcategories' ) ); ?>"
                         value="1" <?php checked( 1, $only_subcategories ) ?>/>
                    <label for="<?php echo esc_attr( $this->get_field_id( 'only_subcategories' ) ); ?>"><?php esc_html_e( 'Выводить только подкатегории', 'adtools' ); ?></label>
                </span>
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
		$only_subcategories = isset( $instance['only_subcategories'] ) && $instance['only_subcategories'] == 1 ? 1 : 0;

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		$categories = get_terms( [
			'taxonomy'   => FS_Config::get_data( 'product_taxonomy' ),
			'hide_empty' => false,
			'parent'     =>!is_post_type_archive(FS_Config::get_data('post_type')) && is_tax(FS_Config::get_data('product_taxonomy')) && $only_subcategories ? get_queried_object_id() : 0
		] );
		$query  = $_REQUEST['categories'] ?? [];
		?>
		<?php if ( ! empty( $categories ) ): ?>
            <ul class="fs-category-filter">
				<?php foreach ( $categories as $category ): ?>
                <?php  	$clear_category = $query;
					$key         = array_search( $category->term_id, $query );
					if ( $key !== false ) {
						unset( $clear_category[ $key ] );
					}  ?>
                    <li class="fs-checkbox-wrapper">
                        <input type="checkbox" class="checkStyle"
                               data-fs-action="filter"
                               data-fs-redirect="<?php echo esc_url( add_query_arg( [ 'categories' => $clear_category] ) ); ?>"
                               name="categories[<?php echo $category->slug; ?>]"
                               value="<?php echo esc_url( add_query_arg( [ 'categories' => array_merge( $query, [ $category->term_id ] ) ] ) ); ?>"
                               id="fs-category-<?php echo $category->term_id ?>" <?php  echo in_array($category->term_id,$query) ?  'checked' : ''  ?>>
                        <label for="fs-category-<?php echo $category->term_id ?>"><?php echo $category->name; ?></label>
                    </li>
				<?php endforeach; ?>
            </ul>
		<?php endif ?>
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
		$instance                       = [];
		$instance['title']              = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['only_subcategories'] = intval( $new_instance['only_subcategories'] );

		return $instance;
	}
}