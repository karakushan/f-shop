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

		// Сохраняет настройки профиля
		add_action( 'wp_ajax_fs_save_user_data', array( &$this, 'save_user_data' ) );
		add_action( 'wp_ajax_nopriv_fs_save_user_data', array( &$this, 'save_user_data' ) );


		// Сброс пароля
		add_action( 'wp_ajax_fs_lostpassword', array( &$this, 'ajax_lostpassword' ) );
		add_action( 'wp_ajax_nopriv_fs_lostpassword', array( &$this, 'ajax_lostpassword' ) );

		/* Защита личного кабинета от неавторизованных пользователей */
		add_action( 'template_redirect', array( &$this, 'cabinet_protect' ) );
	}


	/**
	 * Сброс пароля
	 */
	static public function ajax_lostpassword() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}

		$user_email = sanitize_email( $_POST['user_login'] );

		if ( ! email_exists( $user_email ) ) {
			wp_send_json_error( array( 'msg' => __( 'This user does not exist on the site', 'f-shop' ) ) );
		}

		if ( is_user_logged_in() ) {
			wp_send_json_error( array( 'msg' => __( 'You are already logged in', 'f-shop' ) ) );
		}

		$user = get_user_by( 'email', $user_email );

		$new_password = wp_generate_password();

		wp_set_password( $new_password, $user->ID );

		$message = sprintf( __( 'You or someone else initiated a password reset on the site %s', 'f-shop' ), get_bloginfo( 'url' ) ) . PHP_EOL;
		$message .= sprintf( __( 'New password: %s', 'f-shop' ), $new_password ) . PHP_EOL. PHP_EOL;
		$message .= sprintf( __( 'If it was not you tell the site administrator на e-mail: %s', 'f-shop' ), get_bloginfo( 'admin_email' ) );

		wp_mail( $user_email, __( 'Password reset on the site', 'f-shop' ), $message );

		wp_send_json_success( [ 'msg' => __( 'Your password has been successfully reset. Password sent to your e-mail.' ) ] );
	}


	/**
	 * Сохраняет настройки профиля
	 */
	public function save_user_data() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}

		$user_fields = FS_Config::get_user_fields();
		$user_id     = get_current_user_id();

		require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/media.php' );


		// Если по какой то причине пропали поля юзера, хотя это маловероятно
		if ( ! is_array( $user_fields ) || ! count( $user_fields ) ) {
			wp_send_json_error( array( 'msg' => __( 'No fields found to save user data', 'f-shop' ) ) );
		}

		// Если пользователь не вошел
		if ( ! $user_id ) {
			wp_send_json_error( array( 'msg' => __( 'User is not found', 'f-shop' ) ) );
		}

		// Сохраняем данные пользователя
		foreach ( $user_fields as $meta_key => $user_field ) {

			if ( ( $user_field['type'] == 'file' && empty( $_FILES[ $meta_key ] ) ) ) {
				continue;
			}

			// Сохраняем аватарку
			if ( $user_field['type'] == 'file' && ! empty( $_FILES[ $meta_key ] ) ) {
				if ( $_FILES[ $meta_key ]['error'] ) {
					wp_send_json_error( array( 'msg' => $_FILES ) );
				}
				$attach_id = media_handle_upload( $meta_key, 0 );
				if ( is_wp_error( $attach_id ) ) {
					wp_send_json_error( array( 'msg' => $attach_id->get_error_message() ) );
				}
				update_user_meta( $user_id, $meta_key, intval( $attach_id ) );
				continue;

			}

			// Сохраняем или удаляем поля
			if ( ( $user_field['type'] != 'file' && ! empty( $_POST[ $meta_key ] ) ) ) {
				update_user_meta( $user_id, $meta_key, $_POST[ $meta_key ] );
			} else {
				delete_user_meta( $user_id, $meta_key );
			}

		}

		wp_send_json_success( array(
			'msg' => __( 'Your data has been successfully updated.', 'f-shop' )
		) );
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

		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}

		// Очистка POST данных
		$name     = sanitize_text_field( $_POST['fs-name'] );
		$email    = sanitize_email( $_POST['fs-email'] );
		$login    = $email;
		$password = sanitize_text_field( $_POST['fs-password'] );

		// Добавление пользователя в базу
		$user_id = wp_insert_user( array(
			'user_pass'            => $password,
			'user_email'           => $email,
			'user_login'           => $login,
			'display_name'         => $name,
			'role'                 => 'client',
			'show_admin_bar_front' => false
		) );

		// Если возникла ошибка
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( [ 'msg' => $user_id->get_error_message() ] );
		}

		// Устанавливаем заголовки писем
		$headers[] = 'Content-type: text/html; charset=utf-8';

		// Отсылаем письмо пользователю
		$user_mail_header = 'Регистрация на сайте «' . get_bloginfo( 'name' ) . '»';
		$user_message     = '<h3>Поздравляем ' . $name . '!</h3><p>Вы успешно прошли регистрацию на сайте. Вы автоматически переведены в категорию "клиенты". Для входа в личный кабинет используйте ваше имя пользователя и пароль с которыми регистрировались на сайте. </p> ';
		wp_mail( $email, $user_mail_header, $user_message, $headers );

		// Отсылаем письмо админу
		$admin_mail_header = 'Регистрация на сайте «' . get_bloginfo( 'name' ) . '»';
		$admin_message     = '<h3>На вашем сайте новый пользователь ' . $login . '!</h3><p> Это просто информационное сообщение, на него не нужно отвечать.</p>';
		wp_mail( get_bloginfo( 'admin_email' ), $admin_mail_header, $admin_message, $headers );

		// Отправляем сообщение успешной регистрации на экран
		wp_send_json_success( array(
			'msg' => sprintf( __( 'Congratulations! You have successfully registered! <a href="' . esc_url( get_permalink( fs_option( 'page_auth' ) ) ) . '">Log in</a>', 'f-shop' ) )
		) );


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
		$args = wp_parse_args( $args, array(
			'class'          => 'fs-login-form',
			'name'           => 'fs-login',
			'method'         => 'post',
			'logged_in_text' => __( 'Вы уже вошли на сайт.', 'f-shop' )

		) );

		$template = '';
		if ( is_user_logged_in() ) {
			$template .= '<p class="text-center">' . $args['logged_in_text'] . '</p>';
			$template .= '<p class="text-center"><a href="' . esc_url( get_the_permalink( fs_option( 'page_cabinet', 0 ) ) ) . '">В личный кабинет</a></p>';
		} else {
			$template = apply_filters( 'fs_form_header', $args, 'fs_login' );
			$template .= fs_frontend_template( 'auth/login', array( 'field' => array() ) );
			$template .= apply_filters( 'fs_form_bottom', '' );
		}

		return $template;
	}


	/**
	 * Шорткод формы регистрации
	 *
	 * @param array $args
	 *
	 * @return mixed|string|void
	 */
	public static function register_form( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'class'          => 'fs-register',
			'name'           => 'fs-register',
			'method'         => 'post',
			'logged_in_text' => __( 'Вы уже вошли на сайт.', 'f-shop' )

		) );

		$template = '';
		if ( is_user_logged_in() ) {
			$template .= '<p class="text-center">' . $args['logged_in_text'] . '</p>';
			$template .= '<p class="text-center"><a href="' . esc_url( get_the_permalink( fs_option( 'page_cabinet', 0 ) ) ) . '">В личный кабинет</a></p>';
		} else {
			$template = apply_filters( 'fs_form_header', $args, 'fs_profile_create' );
			$template .= fs_frontend_template( 'auth/register', array( 'field' => array() ) );
			$template .= apply_filters( 'fs_form_bottom', '' );
		}

		return $template;
	}

	/**
	 * Шорткод формы для сброса пароля
	 *
	 * @param array $args
	 *
	 * @return mixed|string|void
	 */
	public static function lostpassword_form( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'class'          => 'fs-lostpassword',
			'name'           => 'fs-lostpassword',
			'method'         => 'post',
			'action'         => wp_lostpassword_url(),
			'logged_in_text' => __( 'Вы уже вошли на сайт.', 'f-shop' )

		) );

		$template = '';
		if ( is_user_logged_in() ) {
			$template .= '<p class="text-center">' . $args['logged_in_text'] . '</p>';
			$template .= '<p class="text-center"><a href="' . esc_url( get_the_permalink( fs_option( 'page_cabinet', 0 ) ) ) . '">В личный кабинет</a></p>';
		} else {
			$template = apply_filters( 'fs_form_header', $args, 'fs_lostpassword' );
			$template .= fs_frontend_template( 'auth/lostpassword', array( 'field' => array() ) );
			$template .= apply_filters( 'fs_form_bottom', '' );
		}

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
		$template = fs_frontend_template( 'cabinet/personal-info', array( 'user' => $user ) );

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

		return true;
	}

	/**
	 * Шорткод личного кабинета
	 *
	 * @return mixed|void
	 */
	public static function user_cabinet() {

		if ( is_user_logged_in() ) {
			return self::user_cabinet_tabs();
		} else {
			return FS_Users_Class::login_form();

		}
	}

	/**
	 * Отвечает за создание вкладок личного кабинета и их содержимого
	 */
	static function user_cabinet_tabs() {

		$tabs = array(
			'personal_info' => array(
				'title'     => __( 'Personal information', 'f-shop' ),
				'content'   => fs_frontend_template( 'dashboard/personal_info' ),
				'link'      => false,
				'nav_class' => 'active',
				'tab_class' => 'fade active show'
			),
			'orders'        => array(
				'title'     => __( 'Orders', 'f-shop' ),
				'content'   => fs_frontend_template( 'dashboard/orders' ),
				'link'      => false,
				'nav_class' => false,
				'tab_class' => 'fade'
			),
			'wishlist'      => array(
				'title'     => __( 'WishList', 'f-shop' ),
				'content'   => fs_frontend_template( 'dashboard/wishlist' ),
				'link'      => false,
				'nav_class' => '',
				'tab_class' => 'fade'
			),
			'reviews'       => array(
				'title'     => __( 'Reviews', 'f-shop' ),
				'content'   => fs_frontend_template( 'dashboard/reviews' ),
				'link'      => false,
				'nav_class' => '',
				'tab_class' => 'fade'
			)
		);
		$tabs = apply_filters( 'fs_user_cabinet_tabs', $tabs );


		if ( empty( $tabs ) || ! is_array( $tabs ) ) {
			return false;
		}

		$out = FS_Form_Class::form_open( array(
			'class'       => 'fs-dashboard',
			'id'          => 'fs-save-user-data',
			'name'        => 'fs-save-user-data',
			'ajax_action' => 'fs_save_user_data'
		) );
		$out .= '<div class="nav nav-tabs" id="fs-dashboard-nav" role="tablist">';

		foreach ( $tabs as $tab_id => $tab ) {
			$out .= '<a class="nav-item nav-link ' . esc_attr( $tab['nav_class'] ) . '" data-toggle="tab" href="#fs-dashboard-' . esc_attr( $tab_id ) . '" role="tab" aria-controls="' . esc_attr( $tab_id ) . '">' . $tab['title'] . '</a>';
		}

		$out .= '</div><!-- end #fs-dashboard-nav -->';

		$out .= '<div class="tab-content" id="fs-dashboard-content">';

		foreach ( $tabs as $tab_id => $tab ) {
			$out .= '<div class="tab-pane  ' . esc_attr( $tab['nav_class'] ) . '" id="fs-dashboard-' . esc_attr( $tab_id ) . '" role="tabpanel" aria-labelledby="fs-dashboard-' . esc_attr( $tab_id ) . '">' . $tab['content'] . '</div>';
		}

		$out .= '</div><!-- end #fs-dashboard-content -->';

		$out .= FS_Form_Class::form_close();

		return $out;

	}

	/**
	 * Возвращает аватарку пользователя
	 *
	 * @param int $user_id
	 * @param string $size
	 *
	 * @return false|string
	 */
	static public function get_user_avatar_url( $user_id = 0, $size = 'thumbnail' ) {
		$user_id   = $user_id ? $user_id : get_current_user_id();
		$avatar_id = get_user_meta( $user_id, 'fs_user_avatar', 1 );
		if ( $avatar_id ) {
			return wp_get_attachment_image_url( $avatar_id, $size );
		}

		return false;
	}


}