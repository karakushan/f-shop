<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 27.02.2017
 * Time: 15:57
 */

namespace FS;
class FS_Form_Class {

	/**
	 * @param string $field_name ключ поля в FS_Config::$form_fields
	 * @param array $args атрибуты input (class,id,value,checked)
	 *
	 * @return string html код поля
	 */
	function fs_form_field( $field_name, $args = array() ) {
		global $fs_config;
		$default     = array(
			'type'        => 'text',
			'class'       => '',
			'label_class' => 'fs-form-label',
			'id'          => str_replace( array(
				'[',
				']'
			), array( '_' ), $field_name ),
			'required'    => false,
			'title'       => __( 'this field is required', 'fast-shop' ),
			'placeholder' => '',
			'value'       => '',
			'format'      => '%input% %label%',
			'el'          => 'select',
			'editor_args' => array(
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
		$field    = '';
		switch ( $args['type'] ) {
			case 'text':
				$field = ' <input type="text" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . ' ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'email':
				$field = ' <input type="email" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'tel':
				$field = ' <input type="tel" name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'radio':
				$field = ' <input type="radio" name="' . $field_name . '"  ' . checked( 'on', $value, false ) . ' ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $value . ' ' . $id . '> ';
				break;
			case 'checkbox':
				$field = ' <input type="checkbox" name="' . $field_name . '"  ' . checked( '1', $args['value'], false ) . ' ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . '  value="1"  ' . $id . '> ';
				break;
			case 'textarea':
				$field = '<textarea name="' . $field_name . '"  ' . $class . ' ' . $title . ' ' . $required . '  ' . $placeholder . ' ' . $id . '></textarea>';
				break;
			case 'button':
				$field ='<button type="button" ' . $class . ' ' . $id . '>'.$args['value'].'</button>';
				break;
			case 'pages':
				$field = wp_dropdown_pages( array(
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
					$field = wp_dropdown_categories( array(
						'show_option_all' => 'Способ оплаты',
						'hide_empty'      => 0,
						'name'            => $field_name,
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
						foreach ( $pay_methods as $pay_method ) {
							$field .= str_replace( array( '%input%', '%label%' ), array(
								'<input type="radio" name="' . $field_name . '" value="' . $pay_method->term_id . '" class="' . $args['class'] . '" id="fs-del-' . $pay_method->term_id . '">',
								'<label for="fs-del-' . $pay_method->term_id . '" class="' . $args['label_class'] . '">' . $pay_method->name . '</label>'
							), $args['format'] );
						}
					}

				}
				break;
			case 'del_methods':
				if ( $args['el'] == 'select' ) {
					$field = wp_dropdown_categories( array(
						'show_option_all' => 'Способ доставки',
						'hide_empty'      => 0,
						'name'            => $field_name,
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
						foreach ( $del_methods as $del_method ) {
							$field .= str_replace( array( '%input%', '%label%' ), array(
								'<input type="radio" name="' . $field_name . '" value="' . $del_method->term_id . '" class="' . $args['class'] . '" id="fs-del-' . $del_method->term_id . '">',
								'<label for="fs-del-' . $del_method->term_id . '" class="' . $args['label_class'] . '">' . $del_method->name . '</label>'
							), $args['format'] );
						}
					}

				}
				break;

			case 'editor':
				wp_editor( esc_html( $args['value'] ), $args['id'], $args['editor_args'] );
				$field = ob_get_clean();
				break;
		}
		echo apply_filters( 'fs_form_field', $field, $field_name, $args );
	}
}
