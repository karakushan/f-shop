<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 27.02.2017
 * Time: 15:57
 */

namespace FS;
class FS_Form_Class {

	public function __construct() {


	}


	/**
	 * Returns all registered field types
	 *
	 * @return mixed|void
	 */
	function registered_field_types() {
		$types = array(
			'text',
			'hidden',
			'email',
			'tel',
			'textarea',
			'editor',
			'checkbox',
			'radio',
			'select',
			'gallery',
			'image',
			'media',
			'number',
			'dropdown_categories',
			'radio_categories',
			'pages',
			'html'
		);

		return apply_filters( 'fs_registered_field_types', $types );

	}

	/**
	 * Displays a field of a certain type
	 *
	 * @param $name
	 * @param string $type
	 * @param array $args
	 */
	function render_field( $name, $type = 'text', $args = [] ) {
		$args = wp_parse_args( $args, array(
			'value'          => '',
			'required'       => false,
			'title'          => '',
			'label'          => '',
			'placeholder'    => '',
			'label_position' => 'before',
			'taxonomy'       => 'category',
			'help'           => '',
			'size'           => '',
			'style'          => '',
			'step'           => 1,
			'first_option'   => __( 'Select'),
			'class'          => str_replace( '_', '-', sanitize_title( 'fs-' . $type . '-field' ) ),
			'id'             => str_replace( '_', '-', sanitize_title( 'fs-' . $name . '-' . $type ) ),
			'default'        => '',
			'textarea_rows'  => 8,
			'editor_args'    => array(
				'textarea_rows' => 8,
				'textarea_name' => $name
			)
		) );
		if ( in_array( $type, $this->registered_field_types() ) && file_exists( FS_PLUGIN_PATH . 'templates/back-end/fields/' . $type . '.php' ) ) {
			if ( ( $args['label'] || $args['help'] ) && $args['label_position'] == 'before' ) {
				echo '<label for="' . esc_attr( $args['id'] ) . '">' . esc_html( $args['label'] );
				if ( $args['help'] ) {
					echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_html( $args['help'] ) . '"></span>';
				}
				echo '</label>';
			}
			include FS_PLUGIN_PATH . 'templates/back-end/fields/' . $type . '.php';

			if ( ( ! empty( $args['label'] ) || ! empty( $args['help'] ) ) && $args['label_position'] == 'after' ) {
				echo '<label for="' . esc_attr( $args['id'] ) . '">' . esc_html( $args['label'] );
				if ( $args['help'] ) {
					echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_html( $args['help'] ) . '"></span>';
				}
				echo '</label>';
			}
		}
	}

	/**
	 * @param string $field_name ключ поля в FS_Config::$form_fields
	 * @param array $args атрибуты input (class,id,value,checked)
	 *
	 * @return string html код поля
	 */
	function fs_form_field( $field_name, $args = array() ) {
		$default = array(
			'type'           => ! empty( FS_Config::$form_fields[ $field_name ]['type'] ) ? FS_Config::$form_fields[ $field_name ]['type'] : 'text',
			'class'          => 'fs-input',
			'wrapper'        => false,
			'autofill'       => true,
			'wrapper_class'  => 'fs-field-wrapper',
			'label_class'    => 'fs-form-label',
			'taxonomy'       => ! empty( FS_Config::$form_fields[ $field_name ]['taxonomy'] ) ? FS_Config::$form_fields[ $field_name ]['taxonomy'] : 'category',
			'id'             => str_replace( array(
				'[',
				']'
			), array( '_' ), $field_name ),
			'required'       => ! empty( FS_Config::$form_fields[ $field_name ]['required'] ) ? FS_Config::$form_fields[ $field_name ]['required'] : false,
			'title'          => ! empty( FS_Config::$form_fields[ $field_name ]['title'] ) ? FS_Config::$form_fields[ $field_name ]['title'] : __( 'this field is required', 'fast-shop' ),
			'placeholder'    => ! empty( FS_Config::$form_fields[ $field_name ]['placeholder'] ) ? FS_Config::$form_fields[ $field_name ]['placeholder'] : null,
			'value'          => ! empty( FS_Config::$form_fields[ $field_name ]['value'] ) ? FS_Config::$form_fields[ $field_name ]['value'] : '',
			'label'          => ! empty( FS_Config::$form_fields[ $field_name ]['label'] ) ? FS_Config::$form_fields[ $field_name ]['label'] : '',
			'label_position' => ! empty( FS_Config::$form_fields[ $field_name ]['label_position'] ) ? FS_Config::$form_fields[ $field_name ]['label_position'] : 'after',
			'html'           => '',
			'selected'       => '',
			'options'        => array(),
			'format'         => '%input% %label%',
			'el'             => 'radio',
			'first_option'   => ! empty( FS_Config::$form_fields[ $field_name ]['first_option'] ) ? FS_Config::$form_fields[ $field_name ]['first_option'] : __('Select'),
			'before'         => '',
			'after'          => '',
			'editor_args'    => array(
				'textarea_rows' => 8,
				'textarea_name' => $field_name,
				'tinymce'       => false,
				'media_buttons' => false
			)

		);
		if ( $default['autofill'] ) {
			$curent_user = wp_get_current_user();
			//подставляем начальное значение в атрибут value интпута формы
			if ( $curent_user->exists() ) {
				switch ( $field_name ) {
					case 'fs_email':
						$default['value'] = $curent_user->user_email;
						break;
					case 'fs_first_name':
						$default['value'] = $curent_user->first_name;
						break;
					case 'fs_last_name':
						$default['value'] = $curent_user->last_name;
						break;
					case 'fs_phone':
						$default['value'] = get_user_meta( $curent_user->ID, 'fs_phone', 1 );
						break;
					case 'fs_city':
						$default['value'] = get_user_meta( $curent_user->ID, 'fs_city', 1 );
						break;
					case 'fs_adress':
						$default['value'] = get_user_meta( $curent_user->ID, 'fs_adress', 1 );
						break;
					case 'fs_delivery_methods':
						$default['selected'] = get_user_meta( $curent_user->ID, 'fs_delivery_methods', 1 );
						break;
					case 'fs_payment_methods':
						$default['selected'] = get_user_meta( $curent_user->ID, 'fs_payment_methods', 1 );
						break;

				}
			}

		}
		$args = wp_parse_args( $args, $default );

		echo $args['before'];
		if ( $args['wrapper'] ) {
			echo '<div class="' . esc_attr( $args['wrapper_class'] ) . '">';
		}
		$this->render_field( $field_name, $args['type'], $args );

		if ( ! empty( $args['help'] ) && ! in_array( $args['type'], array( 'checkbox' ) ) ) {
			echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_attr( $args['help'] ) . '"></span>';
		}

		if ( $args['wrapper'] ) {
			echo '</div>';
		}
	}

}
