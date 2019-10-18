<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 30.04.2018
 * Time: 19:07
 */

namespace FS;

/*
 * Виджет корзины
 */

class FS_Attribute_Widget extends \WP_Widget {
	function __construct() {
		parent::__construct(
			'fs_attribute_widget',
			'Фильтр по атрибутам товара',
			array( 'description' => 'Позволяет вывести фильтр для фильтрации товара по атрибутам' )
		);
	}

	/*
	 * бэкэнд виджета
	 */
	public function form( $instance ) {
		global $fs_config;

		$title          = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$fs_att_group   = ! empty( $instance['fs_att_group'] ) ? $instance['fs_att_group'] : '';
		$fs_att_types   = ! empty( $instance['fs_att_types'] ) ? $instance['fs_att_types'] : '';
		$fs_screen_atts = ! empty( $instance['fs_screen_atts'] ) ? $instance['fs_screen_atts'] : 0;
		$fs_only_cats   = ! empty( $instance['fs_only_cats'] ) ? $instance['fs_only_cats'] : '';

		$args = array(
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
			'value_field'      => 'term_id', // значение value e option
			'required'         => false,
		);
		?>
        <div class="fs-widget-wrapper">
            <p>
                <label
                        for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'f-shop' ) ?></label>
                <input class="widefat wpglobus-dialog-field" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                       value="<?php echo esc_attr( $title ); ?>"/>
            </p>
            <p>
                <label
                        for="<?php echo esc_attr( $this->get_field_id( 'fs_att_group' ) ); ?>"><?php esc_html_e( 'Feature Group', 'f-shop' ) ?></label>
				<?php wp_dropdown_categories( $args ); ?>
            </p>
            <p>
                <input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'fs_screen_atts' ) ); ?>"
                       value="1"
                       id="<?php echo esc_attr( $this->get_field_id( 'fs_screen_atts' ) ); ?>" <?php checked( 1, $fs_screen_atts ) ?>>
                <label for="<?php echo esc_attr( $this->get_field_id( 'fs_screen_atts' ) ); ?>"><?php esc_html_e( 'Attributes for category only', 'f-shop' ) ?></label>
            </p>
            <p>
                <label
                        for="<?php echo esc_attr( $this->get_field_id( 'fs_att_types' ) ); ?>"><?php esc_html_e( 'Type', 'f-shop' ) ?></label><br>
                <select name="<?php echo esc_attr( $this->get_field_name( 'fs_att_types' ) ); ?>"
                        id="<?php echo esc_attr( $this->get_field_id( 'fs_att_types' ) ); ?>">
                    <option value="normal"><?php esc_html_e( 'Normal', 'f-shop' ) ?></option>
                    <option
                            value="color" <?php selected( 'color', $fs_att_types ) ?>><?php esc_html_e( 'Color', 'f-shop' ) ?></option>
                    <option
                            value="image" <?php selected( 'image', $fs_att_types ) ?>><?php esc_html_e( 'Image', 'f-shop' ) ?></option>
                </select>
            </p>
            <p>
                <label for="">Показывать только в категориях</label>
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
					'class'             => 'postform',
					'depth'             => 0,
					'tab_index'         => 0,
					'taxonomy'          => FS_Config::get_data( 'product_taxonomy' ),
					'hide_if_empty'     => false,
					'value_field'       => 'term_id', // значение value e option
					'required'          => false,
				);

				wp_dropdown_categories( $args ); ?>
            </p>
        </div>
		<?php
	}

	public function widget( $args, $instance ) {
		$title        = apply_filters( 'widget_title', $instance['title'] );
		$type         = ! empty( $instance['fs_att_types'] ) ? $instance['fs_att_types'] : 'text';
		$fs_only_cats = ! empty( $instance['fs_only_cats'] ) ? explode( ',', $instance['fs_only_cats'] ) : [];

		// Выходим если находимся на странице термина таксономии и термин не найден в настройках
		if ( is_tax() && count( $fs_only_cats ) && ! in_array( get_queried_object_id(), $fs_only_cats ) ) {
			return;
		}

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		do_action( 'fs_attr_filter', $instance['fs_att_group'], array(
			'type'           => $type,
			'current_screen' => ! empty( $instance['fs_screen_atts'] ) ? true : false
		) );
		echo $args['after_widget'];
	}

	/*
	 * сохранение настроек виджета
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                   = array();
		$instance['title']          = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['fs_att_group']   = intval( $new_instance['fs_att_group'] );
		$instance['fs_att_types']   = ! empty( $new_instance['fs_att_types'] ) ? strip_tags( $new_instance['fs_att_types'] ) : '';
		$instance['fs_screen_atts'] = ! empty( $new_instance['fs_screen_atts'] ) ? strip_tags( $new_instance['fs_screen_atts'] ) : 0;
		$instance['fs_only_cats']   = ! empty( $new_instance['fs_only_cats'] ) ? implode( ',', $new_instance['fs_only_cats'] ) : '';

		return $instance;
	}
}