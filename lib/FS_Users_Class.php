<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 04.11.2016
 * Time: 16:58
 */

namespace FS;


class FS_Users_Class {
	function __construct() {
		// Аякс вход пользователя
		add_action( 'wp_ajax_fs_login', array( $this, 'login_user' ) );
		add_action( 'wp_ajax_nopriv_fs_login', array( $this, 'login_user' ) );
		//  создание профиля пользователя
		add_action( 'wp_ajax_fs_profile_create', array( &$this, 'fs_profile_create' ) );
		add_action( 'wp_ajax_nopriv_fs_profile_create', array( &$this, 'fs_profile_create' ) );
		//  редактирование профиля пользователя
		add_action( 'wp_ajax_fs_profile_edit', array( &$this, 'fs_profile_edit' ) );
		add_action( 'wp_ajax_nopriv_fs_profile_edit', array( &$this, 'fs_profile_edit' ) );

		/* Защита личного кабинета от неавторизованных пользователей */
		add_action( 'template_redirect', array( &$this, 'cabinet_protect' ) );
	}

	/**
	 * Защита личного кабинета от неавторизованных пользователей
	 */
	function cabinet_protect() {
		$redirect_page = fs_option( 'page_cabinet' );
		$login_page    = fs_option( 'page_auth' );
		if ( empty( $redirect_page ) ) {
			return;
		} elseif ( is_page( $redirect_page ) && ! is_user_logged_in() ) {
			if ( empty( $login_page ) ) {
				wp_redirect( home_url( '/' ) );
			} else {
				wp_redirect( get_permalink( (int) $login_page ) );
			}
			exit();
		}
	}

	/**
	 *Функция авторизует пользователя по полученным данным
	 * поле username - может содержать логин или пароль
	 * поле password - пароль
	 */
	function login_user() {
		$username      = sanitize_text_field( $_POST['username'] );
		$password      = sanitize_text_field( $_POST['password'] );
		$referer       = esc_url( $_POST['_wp_http_referer'] );
		$redirect_page = fs_option( 'page_cabinet' );
		$redirect      = ! empty( $redirect_page ) ? get_permalink( $redirect_page ) : false;

		if ( ! FS_Config::verify_nonce() ) {
			echo json_encode( array(
				'status'   => 0,
				'redirect' => false,
				'error'    => 'Неправильный проверочный код. Обратитесь к администратору сайта!'
			) );
			exit;
		}

//        если отправляющий форму авторизован, то выходим отправив сообщение об ошибке
		if ( is_user_logged_in() ) {
			echo json_encode( array(
				'status'   => 0,
				'redirect' => false,
				'error'    => 'Вы уже авторизованны на сайте. <a href="' . wp_logout_url( $_SERVER['REQUEST_URI'] ) . '">Выход</a>. <a href="' . $redirect . '">В кабинет</a>'
			) );
			exit;
		}

		if ( is_email( $username ) ) {
			$user = get_user_by( 'email', $username );
		} else {
			$user = get_user_by( 'login', $username );
		}

		if ( ! $user ) {
			echo json_encode( array(
				'status'   => 0,
				'redirect' => false,
				'error'    => 'К сожалению пользователя с таким данными не существует на сайте'
			) );
			exit;
		} else {
			// Авторизуем
			$auth = wp_authenticate( $username, $password );
			// Проверка ошибок
			if ( is_wp_error( $auth ) ) {
				echo json_encode( array( 'status' => 0, 'redirect' => false, 'error' => $auth->get_error_message() ) );
				exit;
			} else {
				nocache_headers();
				wp_clear_auth_cookie();
				wp_set_auth_cookie( $auth->ID );

				echo json_encode( array( 'status' => 1, 'redirect' => $redirect ) );
				exit;
			}
		}
	}

	// создание профиля пользователя
	public function fs_profile_create() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'fast-shop' ) ) {
			exit( 'неправильный проверочный код nonce' );
		}

		$name = sanitize_text_field( $_POST['fs-name'] );

		$referer  = esc_url( $_POST['_wp_http_referer'] );
		$email    = sanitize_email( $_POST['fs-email'] );
		$login    = $email;
		$password = sanitize_text_field( $_POST['fs-password'] );
		$city     = sanitize_text_field( $_POST['fs-city'] );
		$phone    = sanitize_text_field( $_POST['fs-phone'] );

		$json = json_encode( array(
			'status'  => 0,
			'message' => 'данные не прошли обработку'
		) );

		$new_user = array(
			'user_pass'            => $password,
			'user_email'           => $email,
			'user_login'           => $login,
			'display_name'         => $name,
			'role'                 => 'client',
			'show_admin_bar_front' => false
		);

		$user_id = wp_insert_user( $new_user );


		if ( ! is_wp_error( $user_id ) ) {
			$user_data = array(
				'city'  => $city,
				'phone' => $phone,
			);
			foreach ( $user_data as $meta_key => $meta_value ) {
				update_user_meta( $user_id, $meta_key, $meta_value );
			}
			$json = json_encode( array(
				'status'   => 1,
				'redirect' => '',
				'message'  => 'Поздравляем! Вы успешно зарегистрированны! <a href="' . esc_url( get_permalink( fs_option( 'page_auth' ) ) ) . '">Выполнить вход</a>'
			) );

			// отсылаем письма
			$headers[] = 'Content-type: text/html; charset=utf-8';
			// пользователю
			$user_mail_header = 'Регистрация на сайте «' . get_bloginfo( 'name' ) . '»';
			$user_message     = '<h3>Поздравляем ' . $name . '!</h3><p>Вы успешно прошли регистрацию на сайте. Вы автоматически переведены в категорию "клиенты". Для входа в личный кабинет используйте ваше имя пользователя и пароль с которыми регистрировались на сайте. </p> ';
			$mail_user_send   = wp_mail( $email, $user_mail_header, $user_message, $headers );

			// админу
			$admin_mail_header = 'Регистрация на сайте «' . get_bloginfo( 'name' ) . '»';
			$admin_message     = '<h3>На вашем сайте новый пользователь ' . $login . '!</h3><p> Это просто информационное сообщение, на него не нужно отвечать.</p>';
			$mail_user_send    = wp_mail( get_bloginfo( 'admin_email' ), $admin_mail_header, $admin_message, $headers );
		} else {
			$errors = $user_id->get_error_message();
			$json   = json_encode( array(
				'status'  => 0,
				'message' => $errors
			) );
		}
		echo $json;
		exit;
	}

// редактирование профиля пользователя
	public function fs_profile_edit() {
		if ( ! FS_Config::verify_nonce() || empty( $_POST['fs'] ) || ! is_user_logged_in()
		) {
			echo json_encode( array(
				'status'  => 0,
				'message' => 'Форма не прошла проверку безопасности!'
			) );
			exit;
		}

		$user = wp_get_current_user();

		foreach ( FS_Config::$user_meta as $meta_key => $meta_field ) {
			$name  = $meta_field['name'];
			$value = sanitize_text_field( $_POST['fs'][ $name ] );

			if ( empty( $value ) ) {
				delete_user_meta( $user->ID, $meta_key );
				continue;
			}

			switch ( $meta_key ) {
				case 'display_name':
					$update_user = wp_update_user( array(
						'ID'           => $user->ID,
						'display_name' => $value
					) );
					if ( is_wp_error( $update_user ) ) {
						$errors = $update_user->get_error_message();
						echo json_encode( array(
							'status'  => 0,
							'message' => $errors
						) );
						exit;
					}
					break;
				case 'user_email':
					$email = sanitize_email( $_POST['fs'][ $name ] );
					if ( ! is_email( $email ) ) {
						echo json_encode( array(
							'status'  => 0,
							'message' => 'E-mail не соответствует формату!'
						) );
						exit;
					} else {
						$update_user = wp_update_user( array(
							'ID'         => $user->ID,
							'user_email' => $email
						) );
						if ( is_wp_error( $update_user ) ) {
							$errors = $update_user->get_error_message();
							echo json_encode( array(
								'status'  => 0,
								'message' => $errors
							) );
							exit;
						}
					}


					break;
				case 'birth_day' :
					update_user_meta( $user->ID, $meta_key, strtotime( $value ) );
					break;
				default:
					update_user_meta( $user->ID, $meta_key, $value );
					break;

			}

		}

		echo json_encode( array(
			'status'  => 1,
			'message' => 'Ваши данные успешно обновились!'
		) );
		exit;
	}

	/**
	 * Возвращает html код формы входа в личный кабинет
	 *
	 * @param array $args
	 *
	 * @return mixed|string|void
	 */
	public static function login_form( $args = array() ) {
		$args     = wp_parse_args( $args, array(
			'class'  => 'fs-login-form',
			'name'   => 'fs-login',
			'method' => 'post'
		) );
		$template = apply_filters( 'fs_form_header', $args, 'fs_login' );
		$template .= fs_frontend_template( 'auth/login', array( 'field' => array() ) );
		$template .= apply_filters( 'fs_form_bottom', '' );

		return $template;
	}

	/**
	 * Выводит html код формы входа в личный кабинет
	 *
	 * @param array $args
	 */
	public static function login_form_show( $args = array() ) {
		$args     = wp_parse_args( $args, array(
			'class'  => 'fs-login-form',
			'name'   => 'fs-login',
			'method' => 'posts'
		) );
		$template = apply_filters( 'fs_form_header', $args, 'fs_login' );
		$template .= fs_frontend_template( 'auth/login', array( 'field' => array() ) );
		$template .= apply_filters( 'fs_form_bottom', '' );

		echo $template;
	}

	public static function user_info() {
		$user     = fs_get_current_user();
		$template = fs_frontend_template( 'cabinet/personal-info', array( 'user' => $user ), true );

		return $template;
	}

	/**
	 * Выводит информацию о текущем пользователе в виде списка
	 */
	public static function user_info_show() {
		echo self::user_info();
	}

	public static function profile_edit( $args = array() ) {
		$user           = fs_get_current_user();
		$default        = array(
			'class' => 'fs-profile-edit',
			'echo'  => false
		);
		$args           = wp_parse_args( $args, $default );
		$args['name']   = 'fs-profile-edit';
		$args['method'] = 'post';
		$template       = apply_filters( 'fs_form_header', $args, 'fs_profile_edit' );
		$template       .= fs_frontend_template( 'cabinet/profile-edit', array(
			'user'  => $user,
			'field' => FS_Config::$user_meta
		) );
		$template       .= apply_filters( 'fs_form_bottom', '' );
		if ( ! $args['echo'] ) {
			return $template;
		} else {
			echo $template;
		}
	}


}