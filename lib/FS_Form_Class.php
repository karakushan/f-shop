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
			'textarea',
			'editor',
			'checkbox',
			'radio',
			'gallery',
			'image',
			'media',
			'number',
			'dropdown_categories'
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
			'label'          => '',
			'label_position' => 'before',
			'taxonomy'       => 'category',
			'help'           => '',
			'class'          => sanitize_title( 'fs-' . $type . 'field' ),
			'id'             => sanitize_title( 'fs-' . $name . '-' . $type ),
			'default'        => '',
			'textarea_rows'  => 8,
			'editor_args'    => array(
				'textarea_rows' => 8,
				'textarea_name' => $name
			)
		) );
		if ( in_array( $type, $this->registered_field_types() ) && file_exists( FS_PLUGIN_PATH . 'templates/back-end/fields/' . $type . '.php' ) ) {
			if ( $args['label'] && $args['label_position'] == 'before' ) {
				echo '<label for="' . esc_attr( $args['id'] ) . '">' . esc_html( $args['label'] );
				if ( $args['help'] ) {
					echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_html( $args['help'] ) . '"></span>';
				}
				echo '</label>';
			}
			include FS_PLUGIN_PATH . 'templates/back-end/fields/' . $type . '.php';

			if ( $args['label'] && $args['label_position'] == 'after' ) {
				echo '<label for="' . esc_attr( $args['id'] ) . '">' . esc_html( $args['label'] ) . '</label>';
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
		global $fs_config;
		$curent_user = wp_get_current_user();
		//подставляем начальное значение в атрибут value интпута формы
		$default_value = '';
		$selected      = '';
		if ( $curent_user->exists() && fs_option( 'autofill' ) == '1' ) {
			switch ( $field_name ) {
				case 'fs_email':
					$default_value = $curent_user->user_email;
					break;
				case 'fs_first_name':
					$default_value = $curent_user->first_name;
					break;
				case 'fs_last_name':
					$default_value = $curent_user->last_name;
					break;
				case 'fs_phone':
					$default_value = get_user_meta( $curent_user->ID, 'fs_phone', 1 );
					break;
				case 'fs_city':
					$default_value = get_user_meta( $curent_user->ID, 'fs_city', 1 );
					break;
				case 'fs_adress':
					$default_value = get_user_meta( $curent_user->ID, 'fs_adress', 1 );
					break;
				case 'fs_delivery_methods':
					$selected = get_user_meta( $curent_user->ID, 'fs_delivery_methods', 1 );
					break;
				case 'fs_payment_methods':
					$selected = get_user_meta( $curent_user->ID, 'fs_payment_methods', 1 );
					break;
				case 'fs_customer_register':
					return;
					break;

			}
		}
		$default     = array(
			'type'          => ! empty( FS_Config::$form_fields[ $field_name ]['type'] ) ? FS_Config::$form_fields[ $field_name ]['type'] : 'text',
			'class'         => 'form-control',
			'wrapper'       => false,
			'wrapper_class' => 'fs-field-wrapper',
			'label_class'   => 'fs-form-label',
			'id'            => str_replace( array(
				'[',
				']'
			), array( '_' ), $field_name ),
			'required'      => ! empty( FS_Config::$form_fields[ $field_name ]['required'] ) ? FS_Config::$form_fields[ $field_name ]['required'] : false,
			'title'         => ! empty( FS_Config::$form_fields[ $field_name ]['title'] ) ? FS_Config::$form_fields[ $field_name ]['title'] : __( 'this field is required', 'fast-shop' ),
			'placeholder'   => ! empty( FS_Config::$form_fields[ $field_name ]['placeholder'] ) ? FS_Config::$form_fields[ $field_name ]['placeholder'] : null,
			'value'         => ! empty( FS_Config::$form_fields[ $field_name ]['value'] ) ? FS_Config::$form_fields[ $field_name ]['value'] : '',
			'html'          => '',
			'selected'      => $selected,
			'options'       => array(),
			'format'        => '%input% %label%',
			'el'            => 'radio',
			'first_option'  => __( 'Select' ),
			'before'        => '',
			'after'         => '',
			'editor_args'   => array(
				'textarea_rows' => 8,
				'textarea_name' => $field_name,
				'tinymce'       => false,
				'media_buttons' => false
			)

		);
		$args        = wp_parse_args( $args, $default );
		$class       = ! empty( $args['class'] ) ? 'class="' . sanitize_html_class( $args['class'] ) . '"' : '';
		$id          = ! empty( $args['id'] ) ? 'id="' . sanitize_html_class( $args['id'] ) . '"' : 'id=""';
		$title       = ( ! empty( $args['title'] ) && $args['required'] ) ? 'title="' . esc_html( $args['title'] ) . '"' : '';
		$placeholder = ! empty( $args['placeholder'] ) ? 'placeholder="' . esc_html( $args['placeholder'] ) . '"' : '';
		$value       = ! empty( $args['value'] ) ? 'value="' . esc_html( $args['value'] ) . '"' : '';

		$required = ! empty( $args['required'] ) ? 'required' : '';
		$field    = $args['before'];
		if ( $args['wrapper'] ) {
			$field .= '<div class="' . esc_attr( $args['wrapper_class'] ) . '">';
		}

		switch ( $args['type'] ) {
			case 'text':
				$field .= ' <input type="text" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . ' ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'email':
				$field .= ' <input type="email" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'tel':
				$field .= ' <input type="tel" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'radio':
				$field .= ' <input type="radio" name="' . $field_name . '"  ' . checked( 'on', $value, false ) . ' ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'checkbox':
				$field .= '<div class="fs-checkbox"><input type="checkbox" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_name ) . '"  value="1" ' . checked( '1', $args['value'], false ) . '><label for="' . esc_attr( $field_name ) . '">' . esc_html( $args['label'] );
				if ( ! empty( $args['help'] ) ) {
					$field .= '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_attr( $args['help'] ) . '"></span>';
				}
				$field .= '</label> </div>';
				break;
			case 'textarea':
				$field .= '<textarea name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $id . '></textarea>';
				break;
			case 'custom':
				$field .= $args['html'];
				break;
			case 'button':
				$field .= '<button type="button" ' . $class . ' ' . $id . '>' . $args['value'] . '</button>';
				break;
			case 'select':
				$field .= '<select name="' . $field_name . '">';
				$field .= '<option value="">' . $args['first_option'] . '</option>';
				foreach ( $args['options'] as $k => $val ) {
					$field .= '<option value="' . $k . '"  ' . selected( $args['value'], $k, 0 ) . '>' . $val . '</option>';
				}
				$field .= '</select>';

				break;
			case 'pages':
				$field .= wp_dropdown_pages( array(
					'show_option_none'  => __( 'Select page', 'fast-shop' ),
					'option_none_value' => 0,
					'name'              => $field_name,
					'echo'              => 0,
					'id'                => $args['id'],
					'selected'          => $args['value']
				) );
				break;
			case 'pay_methods':
				if ( $args['el'] == 'select' ) {
					$field .= wp_dropdown_categories( array(
						'show_option_all' => $args['first_option'],
						'hide_empty'      => 0,
						'name'            => $field_name,
						'selected'        => $args['selected'],
						'class'           => $args['class'],
						'echo'            => 0,
						'taxonomy'        => $fs_config->data['product_pay_taxonomy']
					) );
				} elseif ( $args['el'] == 'radio' ) {
					$pay_methods = get_terms( array(
						'hide_empty' => false,
						'taxonomy'   => $fs_config->data['product_pay_taxonomy']
					) );
					if ( $pay_methods ) {
						foreach ( $pay_methods as $key => $pay_method ) {
							$field .= '<div class="fs-radio">';
							$field .= '<input type="radio" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( 'fs-term-' . $pay_method->term_id ) . '" value="' . esc_attr( $pay_method->term_id ) . '" ' . checked( 0, $key, 0 ) . '>';
							$field .= '<label for="' . esc_attr( 'fs-term-' . $pay_method->term_id ) . '">' . esc_html( $pay_method->name ) . '</label>';
							$field .= '</div>';
						}
					}

				}
				break;
			case 'del_methods':
				if ( $args['el'] == 'select' ) {
					$field .= wp_dropdown_categories( array(
						'show_option_all' => $args['first_option'],
						'hide_empty'      => 0,
						'name'            => $field_name,
						'selected'        => $args['selected'],
						'class'           => $args['class'],
						'echo'            => 0,
						'taxonomy'        => $fs_config->data['product_del_taxonomy']
					) );
				} elseif ( $args['el'] == 'radio' ) {
					$del_methods = get_terms( array(
						'hide_empty' => false,
						'taxonomy'   => $fs_config->data['product_del_taxonomy']
					) );
					if ( $del_methods ) {
						foreach ( $del_methods as $key => $del_method ) {
							$field .= '<div class="fs-radio">';
							$field .= '<input type="radio" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( 'fs-term-' . $del_method->term_id ) . '" value="' . esc_attr( $del_method->term_id ) . '" ' . checked( 0, $key, 0 ) . '>';
							$field .= '<label for="' . esc_attr( 'fs-term-' . $del_method->term_id ) . '">' . esc_html( $del_method->name ) . '</label>';
							$field .= '</div>';
						}
					}

				}
				break;

			case 'editor':
				wp_editor( esc_html( $args['value'] ), $args['id'], $args['editor_args'] );
				$field = ob_get_clean();
				break;
		}
		if ( ! empty( $args['help'] ) && ! in_array( $args['type'], array( 'checkbox' ) ) ) {
			$field .= '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_attr( $args['help'] ) . '"></span>';
		}
		if ( $args['wrapper'] ) {
			$field .= '</div>';
		}
		echo apply_filters( 'fs_form_field', $field, $field_name, $args );
	}

}
