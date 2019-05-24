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
			'file',
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
		$args        = wp_parse_args( $args, array(
			'value'          => '',
			'values'         => array(),
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
			'first_option'   => __( 'Select' ),
			'class'          => str_replace( '_', '-', sanitize_title( 'fs-' . $type . '-field' ) ),
			'id'             => str_replace( '_', '-', sanitize_title( 'fs-' . $name . '-' . $type ) ),
			'default'        => '',
			'textarea_rows'  => 8,
			'editor_args'    => array(
				'textarea_rows' => 8,
				'textarea_name' => $name
			)
		) );
		$label_after = $args['required'] ? ' <i>*</i>' : '';

		if ( in_array( $type, $this->registered_field_types() ) && file_exists( FS_PLUGIN_PATH . 'templates/back-end/fields/' . $type . '.php' ) ) {
			if ( ( $args['label'] || $args['help'] ) && $args['label_position'] == 'before' ) {
				echo '<label for="' . esc_attr( $args['id'] ) . '">' . esc_html( $args['label'] ) . $label_after;
				if ( $args['help'] ) {
					echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_html( $args['help'] ) . '"></span>';
				}
				echo '</label>';
			}
			include FS_PLUGIN_PATH . 'templates/back-end/fields/' . $type . '.php';

			if ( ( ! empty( $args['label'] ) || ! empty( $args['help'] ) ) && $args['label_position'] == 'after' ) {

				echo '<label for="' . esc_attr( $args['id'] ) . '">' . esc_html( $args['label'] ) . $label_after;
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

		$user_id = get_current_user_id();

		$fields = FS_Config::get_user_fields();
		$field  = ! empty( $fields[ $field_name ] ) && is_array( $fields[ $field_name ] )
			? $fields[ $field_name ]
			: array();

		$value = $field['value']
			? $field['value']
			: get_user_meta( $user_id, $field_name, 1 );

		$default = array(
			'type'           => ! empty( $field['type'] ) ? $field['type'] : 'text',
			'class'          => 'fs-input form-control',
			'wrapper'        => true,
			'autofill'       => true,
			'wrapper_class'  => 'fs-field-wrap form-group ' . str_replace( '_', '-', $field_name ) . '-wrap',
			'label_class'    => 'fs-form-label',
			'taxonomy'       => ! empty( $field['taxonomy'] ) ? $field['taxonomy'] : 'category',
			'id'             => str_replace( array(
				'[',
				']'
			), array( '_' ), $field_name ),
			'required'       => ! empty( $field['required'] ) ? $field['required'] : false,
			'title'          => ! empty( $field['title'] ) ? $field['title'] : __( 'this field is required', 'f-shop' ),
			'placeholder'    => ! empty( $field['placeholder'] ) ? $field['placeholder'] : null,
			'value'          => is_user_logged_in() && $value ? $value : null,
			'label'          => ! empty( $field['label'] ) ? $field['label'] : '',
			'icon'           => ! empty( $field['icon'] ) ? $field['icon'] : '',
			'label_position' => ! empty( $field['label_position'] ) ? $field['label_position'] : 'before',
			'html'           => '',
			'selected'       => '',
			'options'        => array(),
			'values'         => ! empty( $field['values'] ) ? $field['values'] : array(),
			'format'         => '%input% %label%',
			'el'             => 'radio',
			'first_option'   => ! empty( $field['first_option'] ) ? $field['first_option'] : __( 'Select' ),
			'before'         => '',
			'after'          => '',
			'disabled'       => ! empty( $field['disabled'] ) ? 'disabled' : false,
			'editor_args'    => array(
				'textarea_rows' => 8,
				'textarea_name' => $field_name,
				'tinymce'       => false,
				'media_buttons' => false
			)

		);

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

	/**
	 * Возвращает открывающий тег формы со скрытыми полями безопасности
	 *
	 * @param $args array дополнительные аргументы формы
	 *
	 * @return string
	 */
	public static function form_open( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'method'       => 'POST',
			'autocomplete' => 'off',
			'class'        => 'fs-form',
			'id'           => 'fs-form',
			'name'         => 'fs-ajax',
			'enctype'      => 'multipart/form-data',
			'action'       => '',
			'ajax_action'  => 'fs_save_data',
			'validate'     => true
		) );

		$out = '<form';
		$out .= ' action="' . esc_attr( $args['action'] ) . '"';
		$out .= ' name="' . esc_attr( $args['name'] ) . '"';
		$out .= ' method="' . esc_attr( $args['method'] ) . '"';
		$out .= ' autocomplete="' . esc_attr( $args['autocomplete'] ) . '"';
		$out .= ' data-validation="' . esc_attr( $args['validate'] ) . '"';
		$out .= ' enctype="' . esc_attr( $args['enctype'] ) . '"';
		$out .= ' class="' . esc_attr( $args['class'] ) . '"';
		$out .= ' id="' . esc_attr( $args['id'] ) . '">';
		$out .= FS_Config::nonce_field();
		$out .= '<input type="hidden" name="action" value="' . esc_attr( $args['ajax_action'] ) . '">';

		return $out;
	}

	/**
	 * Возвращает закрывающий тег формы
	 *
	 * @return string
	 */
	public static function form_close() {
		return '</form>';
	}

}
