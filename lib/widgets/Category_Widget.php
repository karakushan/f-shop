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

		$current_category   = get_queried_object();
		$title              = apply_filters( 'widget_title', $instance[ $title_name ] );
		$only_subcategories = isset( $instance['only_subcategories'] ) && $instance['only_subcategories'] == 1 ? 1 : 0;
		$item_view_mode     = ! empty( $instance['item_view_mode'] ) ? $instance['item_view_mode'] : 'checkboxes';
		$categories         = get_terms( [
			'taxonomy'   => FS_Config::get_data( 'product_taxonomy' ),
			'hide_empty' => false,
			'parent'     => fs_is_product_category() && $only_subcategories
				? $current_category->term_id : 0
		] );


		if ( empty( $categories ) ) {
			return;
		}

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$query = $_REQUEST['categories'] ?? [];
		if ( ! empty( $categories ) ): ?>
			<ul class="fs-category-filter">
				<?php foreach ( $categories as $category ): ?>
					<?php $clear_category = $query;
					$key                  = array_search( $category->term_id, $query );
					if ( $key !== false ) {
						unset( $clear_category[ $key ] );
					} ?>
					<li class="fs-checkbox-wrapper level-1">
						<?php if ( $item_view_mode == 'links' ): ?>
							<a <?php if ( $current_category->term_id != $category->term_id ): ?>href="<?php echo esc_attr( get_term_link( $category ) ) ?>"<?php endif; ?>
							   class="level-1-link <?php echo $current_category->term_id == $category->term_id || $current_category->parent == $category->term_id ? 'active' : '' ?>">
								<?php echo fs_get_category_icon( $category->term_id ) ?: '' ?>
								<?php echo apply_filters( 'the_title', $category->name ) ?>
							</a>
							<?php
							$sub_categories = get_terms( [
								'taxonomy'   => FS_Config::get_data( 'product_taxonomy' ),
								'hide_empty' => false,
								'parent'     => $category->term_id
							] );
							if ( ! empty( $sub_categories ) ):
								?>
								<ul class="fs-category-filter">
									<?php foreach ( $sub_categories as $sub_category ): ?>
										<li class="level-2">
											<a <?php if ( $current_category->term_id != $sub_category->term_id ): ?>href="<?php echo esc_attr( get_term_link( $sub_category ) ) ?>"<?php endif ?>
											   class="level-2-link <?php echo $current_category->term_id == $sub_category->term_id ? 'active' : '' ?>">
												<?php echo fs_get_category_icon( $sub_category->term_id ) ?: '' ?>
												<?php echo apply_filters( 'the_title', $sub_category->name ) ?>
											</a>
										</li>
									<?php endforeach ?>
								</ul>
							<?php endif; ?>
						<?php else: ?>
							<input type="checkbox" class="checkStyle"
							       data-fs-action="filter"
							       data-fs-redirect="<?php echo esc_url( add_query_arg( [ 'categories' => $clear_category ] ) ); ?>"
							       name="categories[<?php echo $category->slug; ?>]"
							       value="<?php echo esc_url( add_query_arg( [ 'categories' => array_merge( $query, [ $category->term_id ] ) ] ) ); ?>"
							       id="fs-category-<?php echo $category->term_id ?>" <?php echo in_array( $category->term_id, $query ) ? 'checked' : '' ?>>
							<label
								for="fs-category-<?php echo $category->term_id ?>"><?php echo $category->name; ?></label>
						<?php endif; ?>
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
		$instance['item_view_mode']     = $new_instance['item_view_mode'] ?? $old_instance['item_view_mode'];

		return $instance;
	}
}