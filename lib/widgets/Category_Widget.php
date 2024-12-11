<?php

namespace FS\Widget;

use FS\FS_Config;
use FS\FS_Filters;

/**
 * Class Category_Widget
 *
 * A WordPress widget that allows filtering products by category.
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
	 * Generates the widget form in the admin panel.
	 *
	 * @param array $instance The current settings of the widget instance.
	 *
	 * @return void Outputs the HTML for the widget form.
	 */
	public function form( $instance ) {
		$title              = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$item_view_mode     = ! empty( $instance['item_view_mode'] ) ? $instance['item_view_mode'] : 'checkboxes';
		$only_subcategories = isset( $instance['only_subcategories'] ) && $instance['only_subcategories'] == 1 ? 1 : 0
		?>
        <div class="fs-widget-wrapper">
            <div class="form-row">
                <label
                        for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'f-shop' ); ?></label>
                <input class="widefat title"
                       id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                       name="<?php
				       echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                       value="<?php echo esc_attr( $title ); ?>"/>

            </div>

            <p class="form-row">
                <span class="fs-custom-checkbox">
                  <input type="checkbox"
                         name="<?php echo esc_attr( $this->get_field_name( 'only_subcategories' ) ); ?>"
                         id="<?php echo esc_attr( $this->get_field_id( 'only_subcategories' ) ); ?>"
                         value="1" <?php checked( 1, $only_subcategories ) ?>/>
                    <label
                            for="<?php echo esc_attr( $this->get_field_id( 'only_subcategories' ) ); ?>"><?php esc_html_e( 'Display only subcategories', 'f-shop' ); ?></label>
                </span>
            </p>

            <div class="form-row">
                <label><?php esc_html_e( 'Display method', 'f-shop' ); ?></label>
                <select name="<?php echo esc_attr( $this->get_field_name( 'item_view_mode' ) ); ?>"
                        id="<?php echo esc_attr( $this->get_field_id( 'item_view_mode' ) ); ?>">
                    <option
                            value="checkboxes" <?php selected( $item_view_mode, 'checkboxes' ) ?>><?php esc_html_e( 'Checkbox', 'f-shop' ); ?></option>
                    <option
                            value="links" <?php selected( $item_view_mode, 'links' ) ?>><?php esc_html_e( 'Links', 'f-shop' ); ?></option>
                </select>
            </div>
        </div>
		<?php
	}

	/**
	 * Outputs the content of the widget on the front-end.
	 *
	 * @param array $args Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 *
	 * @return void Outputs the widget's HTML content.
	 */
	public function widget( $args, $instance ) {
		$title_name = fs_option( 'fs_multi_language_support' )
		              && FS_Config::default_locale() != get_locale() ? 'title_' . get_locale() : 'title';

		if ( empty( $instance[ $title_name ] ) ) {
			$title_name = 'title';
		}

		$current_category   = get_queried_object();
		$title              = apply_filters( 'widget_title', $instance[ $title_name ] );
		$only_subcategories = isset( $instance['only_subcategories'] ) && $instance['only_subcategories'] == 1 ? 1 : 0;
		$categories         = get_terms( [
			'taxonomy'   => FS_Config::get_data( 'product_taxonomy' ),
			'hide_empty' => false,
			'parent'     => fs_is_product_category() && $only_subcategories
				? $current_category->term_id : 0
		] );

		if ( is_wp_error( $categories ) || empty( $categories ) ) {
			return;
		}

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		$query = isset( $_REQUEST['categories'] ) ? array_map( 'sanitize_text_field', explode( FS_Filters::get_param_separator(), $_REQUEST['categories'] ) ) : [];
		?>
        <ul class="fs-category-filter"
            x-data='{categories: <?php echo wp_json_encode( $query ?? [] ) ?>, separator : "<?php echo FS_Filters::get_param_separator() ?>", allCategories: <?php echo wp_json_encode( $categories ) ?>}'
            x-init="()=>{
            $watch('categories', (value) => {
              if (value.length > 0) {
                    const params = new URLSearchParams(window.location.search);
                    params.set('categories', value.join(separator));
                    window.location = `${window.location.pathname}?${params.toString()}`;
                } else {
                    const params = new URLSearchParams(window.location.search);
                    params.delete('categories');
                    window.location = `${window.location.pathname}?${params.toString()}`;
                }
            })
            }">
            <template x-for="category in allCategories" :key="category.term_id">
                <li class="fs-checkbox-wrapper level-1">
                    <input type="checkbox" x-model="categories" class="checkStyle"
                           :name="'categories['+category.term_id+']'"
                           :value="category.term_id"
                           :id="'fs-category-'+category.term_id">
                    <label :for="'fs-category-'+category.term_id" x-text="category.name"></label>
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

		$instance                       = [];
		$instance['title']              = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['only_subcategories'] = intval( $new_instance['only_subcategories'] );
		$instance['item_view_mode']     = $new_instance['item_view_mode'] ?? $old_instance['item_view_mode'];

		return $instance;
	}
}