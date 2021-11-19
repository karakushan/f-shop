<?php

namespace FS;

class FS_Form {
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
	function render_field( $name, $type = 'text', $args = [] ) {

		$field_path = FS_PLUGIN_PATH . 'templates/back-end/fields/' . $type . '.php';

		// If the field is not registered or there is no template file, exit
		if ( ! in_array( $type, $this->get_registered_field_types() ) || ! file_exists( $field_path ) ) {
			return;
		}

		$args = wp_parse_args( $args, array(
			'value'          => '',
			'values'         => array(),
			'required'       => false,
			'title'          => '',
			'label'          => '',
			'label_class'    => '',
			'placeholder'    => '',
			'label_position' => 'before',
			'taxonomy'       => 'category',
			'query_params'   => [],
			'help'           => '',
			'size'           => '',
			'style'          => '',
			'step'           => 1,
			'first_option'   => __( 'Select' ),
			'class'          => str_replace( '_', '-', sanitize_title( 'fs-' . $type . '-field' ) ),
			'id'             => str_replace( '_', '-', sanitize_title( 'fs-' . $name . '-' . $type ) ),
			'default'        => '',
			'textarea_rows'  => 8,
			'post_type'      => 'post',
			'editor_args'    => array(
				'textarea_rows' => 8,
				'textarea_name' => $name
			)
		) );

		$label_after = $args['required'] ? ' <i>*</i>' : '';

		$multi_lang = false;
		$screen     = is_admin() && get_current_screen() ? get_current_screen() : null;

		if ( fs_option( 'fs_multi_language_support' )
		     && ( is_array( FS_Config::get_languages() ) && count( FS_Config::get_languages() ) )
		     && ( ! in_array( $type, [ 'image' ] ) )
		     && ( isset( $screen->id ) && $screen->id == 'edit-catalog' )
		) {
			$multi_lang = true;
		}

		if ( $multi_lang ) {
			echo '<div class="fs-tabs nav-tab-wrapper">';
			echo '<div class="fs-tabs__header">';
			$count = 0;
			foreach ( FS_Config::get_languages() as $key => $language ) {
				$tab_class = ! $count ? 'nav-tab-active' : '';
				echo '<a href="#fs_' . esc_attr( $name ) . '-' . esc_attr( $key ) . '" class="fs-tabs__title nav-tab ' . esc_attr( $tab_class ) . '">' . esc_html( $language['name'] ) . '</a>';
				$count ++;
			}
			echo '</div><!! end .fs-tabs__header !!>';
		}

		if ( $multi_lang ) {
			$count = 0;
			foreach ( FS_Config::get_languages() as $key => $item ) {
				$tab_class  = ! $count ? 'fs-tabs__body fs-tab-active' : 'fs-tabs__body';
				$base_name  = $name;
				$base_id    = $args['id'];
				$args['id'] = $args['id'] . '-' . $key;

				echo '<div class="' . esc_attr( $tab_class ) . '" id="fs_' . esc_attr( $name ) . '-' . esc_attr( $key ) . '">';
				$name          = $item['locale'] != FS_Config::default_locale() ? $name . '__' . $item['locale'] : $name;
				$args['value'] = ! empty( $_GET['tag_ID'] ) ? FS_Taxonomy::fs_get_term_meta( intval( $_GET['tag_ID'] ), $name ) : null;
				if ( ! $args['value'] && $args['default'] ) {
					$args['value'] = $args['default'];
				}

				$args['editor_args']['textarea_name'] = $name;

				if ( in_array( $type, $this->get_registered_field_types() ) && file_exists( FS_PLUGIN_PATH . 'templates/back-end/fields/' . $type . '.php' ) ) {
					if ( ( $args['label'] || $args['help'] ) && $args['label_position'] == 'before' ) {
						echo '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $args['label_class'] ) . '">' . esc_html( $args['label'] ) . $label_after;
						if ( $args['help'] ) {
							echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_html( $args['help'] ) . '"></span>';
						}
						echo '</label>';
					}
					include FS_PLUGIN_PATH . 'templates/back-end/fields/' . $type . '.php';

					if ( ( ! empty( $args['label'] ) || ! empty( $args['help'] ) ) && $args['label_position'] == 'after' ) {

						echo '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $args['label_class'] ) . '">' . esc_html( $args['label'] ) . $label_after;
						if ( $args['help'] ) {
							echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_html( $args['help'] ) . '"></span>';
						}
						echo '</label>';
					}
				}
				echo '</div><!! end .fs-tabs__body !!>';
				$count ++;
				$name       = $base_name;
				$args['id'] = $base_id;
			}

		} else {

			if ( ( $args['label'] || $args['help'] ) && $args['label_position'] == 'before' ) {
				echo '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $args['label_class'] ) . '">' . esc_html( $args['label'] ) . $label_after;
				if ( $args['help'] ) {
					echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_html( $args['help'] ) . '"></span>';
				}
				echo '</label>';
			}
			include( $field_path );

			if ( ( ! empty( $args['label'] ) || ! empty( $args['help'] ) ) && $args['label_position'] == 'after' ) {

				echo '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $args['label_class'] ) . '">' . esc_html( $args['label'] ) . $label_after;
				if ( $args['help'] ) {
					echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_html( $args['help'] ) . '"></span>';
				}
				echo '</label>';
			}

			// Здесь выводим HTML указанный в атрибуте "after" в настройках поля
			if ( ! empty( $args['after'] ) ) {
				echo '<div class="fs-field-after">';
				echo $args['after'];
				echo '</div>';
			}

		}

		if ( $multi_lang ) {
			echo '</div><!! end .fs-tabs !!>';
		}
	}

	/**
	 * @param string $field_name ключ поля в FS_Config::$form_fields
	 * @param array $args атрибуты input (class,id,value,checked)
	 *
	 * @return string html код поля
	 */
	function fs_form_field( $field_name, $args = array() ) {

		$fields = FS_Users::get_user_fields();

		$field = ! empty( $fields[ $field_name ] ) && is_array( $fields[ $field_name ] )
			? $fields[ $field_name ]
			: array();

		$default = array(
			'type'           => ! empty( $field['type'] ) ? $field['type'] : 'text',
			'class'          => 'fs-input form-control',
			'wrapper'        => true,
			'autofill'       => true,
			'wrapper_class'  => 'fs-field-wrap form-group ' . str_replace( '_', '-', $field_name ) . '-wrap',
			'label_class'    => 'fs-form-label',
			'taxonomy'       => ! empty( $field['taxonomy'] ) ? $field['taxonomy'] : 'category',
			'query_params'   => $field['query_params'],
			'id'             => str_replace( array(
				'[',
				']'
			), array( '_' ), $field_name ),
			'required'       => ! empty( $field['required'] ) ? $field['required'] : false,
			'title'          => ! empty( $field['title'] ) ? $field['title'] : __( 'this field is required', 'f-shop' ),
			'placeholder'    => ! empty( $field['placeholder'] ) ? $field['placeholder'] : null,
			'value'          => ! empty( $field['value'] ) ? $field['value'] : null,
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


		if ( $args['wrapper'] ) {
			echo '<div class="' . esc_attr( 'fs-field-wrap ' . $args['wrapper_class'] ) . '">';
		}
		if ( ! empty( $args['before'] ) ) {
			echo $args['before'];
		}

		$this->render_field( $field_name, $args['type'], $args );

		if ( ! empty( $args['help'] ) && ! in_array( $args['type'], array( 'checkbox' ) ) ) {
			echo '<span class="tooltip dashicons dashicons-editor-help" title="' . esc_attr( $args['help'] ) . '"></span>';
		}

		if ( ! empty( $args['after'] ) ) {
			echo $args['after'];
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
			'ajax'         => 'off',
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
		$out .= ' data-ajax="' . esc_attr( $args['ajax'] ) . '"';
		$out .= ' name="' . esc_attr( $args['name'] ) . '"';
		$out .= ' method="' . esc_attr( $args['method'] ) . '"';
		$out .= ' autocomplete="' . esc_attr( $args['autocomplete'] ) . '"';
		$out .= ' data-validation="' . esc_attr( $args['validate'] ) . '"';
		$out .= ' enctype="' . esc_attr( $args['enctype'] ) . '"';
		$out .= ' class="' . esc_attr( $args['class'] ) . '"';
		$out .= ' id="' . esc_attr( $args['id'] ) . '">';
		$out .= FS_Config::nonce_field();
		$out .= '<input type="hidden" name="action" value="' . esc_attr( $args['ajax_action'] ) . '">';
		$out .= '<div class="meter"><span style="width:100%;"><span class="progress"></span></span></div>';

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

}
