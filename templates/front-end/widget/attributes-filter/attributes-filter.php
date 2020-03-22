<div class="fs-widget-wrapper">
	<p>
		<label
			for="<?php use FS\FS_Config;

			echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'f-shop' ) ?></label>
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