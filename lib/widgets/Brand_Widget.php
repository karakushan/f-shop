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