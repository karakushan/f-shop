<?php

namespace FS;

class FS_Form {

	public function __construct() {
		add_filter( 'fs_render_field_args', array( $this, 'render_field_args' ), 10, 3 );
	}

	/**
	 * Returns all registered field types
	 *
	 * @return mixed|void
	 */
	function get_registered_field_types() {
		$types = array(
			'text',
			'hidden',
			'password',
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
			'dropdown_posts',
			'file',
			'html'
		);

		return apply_filters( 'fs_registered_field_types', $types );

	}

	/**
	 * Displays a label for a field
	 *
	 * @param $args
	 *
	 * @return void
	 */
	function field_label( $args ) {
		echo '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $args['label_class'] ) . '">' . esc_html( $args['label'] );
		echo $args['required'] ? ' <i>*</i>' : '';
		if ( $args['help'] ) {
			echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_html( $args['help'] ) . '"></span>';
		}
		echo '</label>';
	}


	/**
	 * Code that is executed before the field is displayed
	 *
	 * @param $args
	 *
	 * @return void
	 */
	function before_render_field( $args = [] ) {
		if ( ! empty( $args['wrapper'] ) ) {
			echo '<div class="fs-field-wrap ' . esc_attr( $args['wrapper_class'] ?? '' ) . '">';
		}
	}

	/**
	 * Code that is executed after the field is displayed
	 *
	 * @param $args
	 *
	 * @return void
	 */
	function after_render_field( $args = [] ) {
		if ( ! empty( $args['after'] ) ) {
			echo '<div class="fs-field-after">' . $args['after'] . '</div>';
		}

		if ( ! empty( $args['wrapper'] ) ) {
			echo '</div>';
		}
	}


	/**
	 * Sending Email
	 *
	 * @param string $email почта на которую отправляется письмо
	 * @param string $subject тема письма
	 * @param array $message отправляемое сообщение
	 * @param array $headers заголовки письма
	 * @param array $attachments файлы, вложения
	 *
	 * @return bool
	 */
	public static function send_email( $email, $subject, $message, $headers = [], $attachments = array() ) {
		$headers = wp_parse_args( $headers, array(
			sprintf(
				'From: %s <%s>',
				fs_option( 'name_sender', get_bloginfo( 'name' ) ),
				fs_option( 'email_sender', 'shop@' . $_SERVER['SERVER_NAME'] )
			),
			'Content-type: text/html; charset=utf-8'
		) );

		return wp_mail( $email, $subject, $message, $headers, $attachments );
	}

	/**
	 * Displays a field of a certain type
	 *
	 * @param $name
	 * @param string $type
	 * @param array $args
	 */
	public function render_field( $name, $type = '', $args = [] ) {
		$fields = FS_Users::get_user_fields();

		$field = ! empty( $fields[ $name ] ) && is_array( $fields[ $name ] )
			? $fields[ $name ]
			: array();

		$default = array_merge( array(
			'type'           => 'text',
			'class'          => 'fs-input form-control',
			'wrapper'        => true,
			'autofill'       => true,
			'wrapper_class'  => 'form-group ' . str_replace( '_', '-', $name ) . '-wrap',
			'label_class'    => 'fs-form-label',
			'taxonomy'       => 'category',
			'query_params'   => null,
			'id'             => str_replace( array( '[', ']' ), array( '_' ), $name ),
			'required'       => false,
			'title'          => __( 'this field is required', 'f-shop' ),
			'placeholder'    => null,
			'value'          => null,
			'label'          => null,
			'icon'           => null,
			'label_position' => 'before',
			'html'           => '',
			'selected'       => '',
			'source'         => 'post_meta',
			'help'           => '',
			'post_id'        => 0,
			'term_id'        => 0,
			'options'        => array(),
			'values'         => array(),
			'format'         => '%input% %label%',
			'el'             => 'radio',
			'first_option'   => __( 'Select' ),
			'before'         => '',
			'after'          => '<span class="fs-error" x-show="errors[\'' . $name . '\']" x-text="errors[\'' . $name . '\']"></span>',
			'disabled'       => false,
			'editor_args'    => array(
				'textarea_rows' => 8,
				'textarea_name' => $name,
				'tinymce'       => false,
				'media_buttons' => false
			),
		), $field );

		$args = wp_parse_args( $args, $default );

		$type = is_string( $type ) && $type != '' ? $type : $args['type'];

		$field_path = FS_PLUGIN_PATH . 'templates/back-end/fields/' . $type . '.php';

		if ( ! empty( $args['alpine'] ) ) {
			$alpine_args                   = explode( ':', $args['alpine'] );
			$args['attributes']['x-model'] = $alpine_args[0];
		}

		// If the field is not registered or there is no template file, exit
		if ( ! in_array( $type, $this->get_registered_field_types() ) || ! file_exists( $field_path ) ) {
			return;
		}

		$args = apply_filters( "fs_render_field_{$type}_args", $args, $name, $type );
		$args = apply_filters( "fs_render_field_args", $args, $name, $type );

		$label_after = isset( $args['required'] ) && $args['required'] ? ' <i>*</i>' : '';
		$screen      = is_admin() && get_current_screen() ? get_current_screen() : null;

		// === START RENDER FIELD
		$this->before_render_field( $args );

		if ( $args['source'] == 'post_meta' && $args['post_id'] ) {
			$args['value'] = get_post_meta( $args['post_id'], $name, true );
		} elseif ( $args['source'] == 'term_meta' && $args['term_id'] ) {
			$args['value'] = get_term_meta( $args['term_id'], $name, true );
		}

		if ( ( $args['label'] || $args['help'] ) && $args['label_position'] == 'before' ) {
			$this->field_label( $args );
		}

		include( $field_path );

		if ( ! empty( $args['help'] ) && $args['label_position'] == 'after' ) {
			$this->field_label( $args );
		}

		$this->after_render_field( $args );

		// === END RENDER FIELD
	}

	/**
	 * Returns an array of fields to send to alpine.js
	 *
	 * @return string
	 */
	public static function alpine_map_fields_xdata( $fields = array() ) {
		if ( empty( $fields ) ) {
			$fields = FS_Users::get_user_fields();
		}

		$form_fields = array_filter( $fields, function ( $field ) {
			return ! empty( $field['alpine'] );
		} );

		$form_fields = array_map( function ( $field ) {
			return $field['alpine'];
		}, $form_fields );

		return implode( ' ', $form_fields );
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
			'method'            => 'POST',
			'autocomplete'      => 'off',
			'ajax'              => 'off',
			'class'             => 'fs-form',
			'id'                => 'fs-form',
			'name'              => 'fs-ajax',
			'enctype'           => 'multipart/form-data',
			'action'            => '',
			'ajax_action'       => 'fs_save_data',
			'validate'          => true,
			'inline_attributes' => '',
			'alpine_data'       => array(),
		) );

		$out = '<form';
		$out .= ' action="' . esc_attr( $args['action'] ) . '"';
		$out .= ' data-ajax="' . esc_attr( $args['ajax'] ) . '"';
		$out .= ' name="' . esc_attr( $args['name'] ) . '"';
		$out .= ' method="' . esc_attr( $args['method'] ) . '"';
		$out .= ' autocomplete="' . esc_attr( $args['autocomplete'] ) . '"';
		$out .= ' data-validation="' . esc_attr( $args['validate'] ) . '"';
		$out .= ' enctype="' . esc_attr( $args['enctype'] ) . '"';
		$out .= ' class="' . esc_attr( $args['class'] ) . '"';
		$out .= ' x-data="' . esc_attr( json_encode( $args['alpine_data'] ) ) . '"';
		$out .= ' id="' . esc_attr( $args['id'] ) . '"';
		$out .= ' ' . $args['inline_attributes'];
		$out .= ' >';
		$out .= FS_Config::nonce_field();

		$out .= '<input type="hidden" name="action" value="' . esc_attr( $args['ajax_action'] ) . '">';
		$out .= '<input type="hidden" name="locale" value="' . esc_attr( get_locale() ) . '">';

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

	/**
	 * Возвращает кнопку для отправки формы
	 *
	 * @param string $label
	 * @param array $args
	 *
	 * @return string
	 */
	public static function form_submit( $label = '', $args = [] ) {

		if ( $label == '' ) {
			$label = __( 'Save', 'f-shop' );
		}
		$inline_attributes = fs_parse_attr( $args, [ 'class' => 'fs-submit', 'type' => 'submit' ] );

		return '<button ' . $inline_attributes . '>' . esc_html( $label ) . '</button>';
	}

	function render_field_args( $args, $name, $type ) {
		if ( ! empty( $args['alpine'] ) ) {
			$alpine_args                   = explode( ':', $args['alpine'] );
			$args['attributes']['x-model'] = $alpine_args[0];
		}

		return $args;
	}

	/**
	 * Phone number validity check
	 *
	 * @param $phone
	 * @param string $country
	 *
	 * @return bool
	 */
	public static function validate_phone( $phone, $country = 'ua' ) {
		// Clear the number of all characters except numbers
		$cleaned_number = preg_replace( '/[^0-9]/', '', $phone );

		// We check that the number starts with 380 and contains exactly 12 digits
		if ( $country == 'ua' && ! preg_match( '/^380\d{9}$/', $cleaned_number ) ) {
			return false;
		}

		return true; // Номер не валиден
	}
}
