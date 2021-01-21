<?php

namespace FS;

/**
 * Class FS_Users
 *
 * @package FS
 */
class FS_Users {

	private $form;

	/**
	 * Password Verification Ruleset
	 *
	 * @var $rules array
	 */
	protected $rules = array(
		'lengthStatus'           => true, //password length true for applicable
		'minLength'              => '6',
		'maxLength'              => '12',
		'numberStatus'           => false, //password number true for contain at last one number
		'uppercaseStatus'        => false, //password uppercase true for contain at last one uppercase
		'lowercaseStatus'        => false, //password lowercase true for contain at last one lowercase
		'specialCharacterStatus' => true, //password special character true for contain at last one special character
		'whiteSpaceStatus'       => false, //password allow space
	);

	function __construct() {

		$this->form = new FS_Form();

		// User Authorization
		add_action( 'wp_ajax_fs_login', array( $this, 'login_user' ) );
		add_action( 'wp_ajax_nopriv_fs_login', array( $this, 'login_user' ) );

		//  Create user profile
		add_action( 'wp_ajax_fs_profile_create', array( $this, 'create_user_ajax' ) );
		add_action( 'wp_ajax_nopriv_fs_profile_create', array( $this, 'create_user_ajax' ) );

		//  Editing user profile
		add_action( 'wp_ajax_fs_profile_edit', array( $this, 'fs_profile_edit' ) );
		add_action( 'wp_ajax_nopriv_fs_profile_edit', array( $this, 'fs_profile_edit' ) );

		// Saves profile settings
		add_action( 'wp_ajax_fs_save_user_data', array( $this, 'save_user_data' ) );
		add_action( 'wp_ajax_nopriv_fs_save_user_data', array( $this, 'save_user_data' ) );

		// Password reset
		add_action( 'wp_ajax_fs_lostpassword', array( $this, 'lost_password_ajax' ) );
		add_action( 'wp_ajax_nopriv_fs_lostpassword', array( $this, 'lost_password_ajax' ) );

		// Login and password change
		add_action( 'wp_ajax_fs_change_login', array( $this, 'change_login' ) );
		add_action( 'wp_ajax_nopriv_fs_change_login', array( $this, 'change_login' ) );

		// Protection of personal account from unauthorized users
		add_action( 'template_redirect', array( $this, 'cabinet_protect' ) );

		// Add the field to user profile editing screen
		add_action( 'show_user_profile', [ $this, 'admin_profile_edit_fields' ] );

		// Add the save action to user's own profile editing screen update.
		add_action( 'edit_user_profile', [ $this, 'admin_profile_edit_fields' ] );

		// Add the save action to user's own profile editing screen update.
		add_action( 'personal_options_update', [ $this, 'admin_profile_save_fields' ] );

		// Add the save action to user profile editing screen update.
		add_action( 'edit_user_profile_update', [ $this, 'admin_profile_save_fields' ] );

	}

	/**
	 * Displays fields in user profile editing
	 *
	 * @param $user
	 */
	public function admin_profile_edit_fields( $user ) {
		?>
        <h2><?php esc_html_e( 'Delivery Settings', 'f-shop' ); ?></h2>
        <table class="form-table">
			<?php foreach ( self::get_user_fields( $user->ID ) as $name => $user_field ) {
				if ( isset( $user_field['save_meta'] ) && $user_field['save_meta'] == false ) {
					continue;
				}
				?>
                <tr>
                    <th>
                        <label for="<?php echo esc_attr( str_replace( '_', '-', $name ) ); ?>"><?php echo esc_html( $user_field['name'] ) ?></label>
                    </th>
                    <td>
						<?php
						$args = wp_parse_args( $user_field, [
							'value' => get_user_meta( $user->ID, $name, 1 ),
							'id'    => str_replace( '_', '-', $name ),
							'class' => 'regular-text',
						] );
						unset( $args['name'] );
						$this->form->render_field( $name, $user_field['type'], $args );
						if ( ! empty( $user_field['description'] ) ): ?>
                            <p class="description">
								<?php echo $user_field['description']; ?>
                            </p>
						<?php endif ?>
                    </td>
                </tr>
			<?php } ?>
        </table>
		<?php
	}

	/**
	 * The save action.
	 *
	 * @param $user_id int the ID of the current user.
	 *
	 * @return bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	function admin_profile_save_fields( $user_id ) {
		// check that the current user have the capability to edit the $user_id
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		foreach ( self::get_user_fields( $user_id ) as $meta_key => $user_field ) {
			if ( isset( $user_field['save_meta'] ) && $user_field['save_meta'] == false ) {
				continue;
			}

			$meta_value = sanitize_text_field( $_POST[ $meta_key ] );
			update_user_meta( $user_id, $meta_key, $meta_value );
		}

		return true;
	}

	/**
	 * Password complexity check
	 *
	 * @param $str
	 *
	 * @return mixed
	 */
	public function password_validation( $str ) {
		$result['status'] = false;

		if ( empty( $str ) ) {
			$result['msg']    = 'Password can not be empty';
			$result['status'] = false;

		} elseif ( ( $this->rules['lengthStatus'] == true ) & ( $this->lengthValidation( $str, $this->rules['minLength'], $this->rules['maxLength'] ) == false ) ) {
			$result['msg']    = 'Your password must be ' . $this->rules['minLength'] . ' to  ' . $this->rules['maxLength'] . ' characters';
			$result['status'] = false;

		} elseif ( ( $this->rules['numberStatus'] == true ) & ( $this->isContainNumber( $str ) == false ) ) {
			$result['msg']    = 'Your password must contain at least one number.';
			$result['status'] = false;

		} elseif ( ( $this->rules['uppercaseStatus'] == true ) & ( $this->isContainUppercase( $str ) == false ) ) {
			$result['msg']    = 'Your password must contain at least one uppercase letter.';
			$result['status'] = false;

		} elseif ( ( $this->rules['lowercaseStatus'] == true ) & ( $this->isContainLowercase( $str ) == false ) ) {
			$result['msg']    = 'Your password must contain at least one lowercase letter.';
			$result['status'] = false;

		} elseif ( ( $this->rules['specialCharacterStatus'] == true ) & ( $this->isContainSpecialCharacter( $str ) == false ) ) {
			$result['msg']    = 'Your password must contain at least one special character.';
			$result['status'] = false;

		} elseif ( ( $this->rules['whiteSpaceStatus'] == true ) & ( $this->isWhiteSpaceContain( $str ) == false ) ) {
			$result['msg']    = 'Space is not allow in password';
			$result['status'] = false;

		} else {
			$result = true;
		}

		return $result;
	}

	/**
	 * Check is string contain space or not
	 *
	 * @param $str
	 *
	 * @return bool; true: if there is space
	 */
	public function isWhiteSpaceContain( $str ) {
		$str = preg_replace( '/\s\s+/', ' ', $str );
		if ( strpos( $str, ' ' ) | preg_match( ' ', $str ) ) {
			return false;

		} else {
			return true;
		}
	}

	/**
	 * Check string is contain minimum single uppercase
	 *
	 * @param $str
	 *
	 * @return bool; true for having uppercase
	 */
	public function isContainUppercase( $str ) {
		$pattern = '/[A-Z]/';
		if ( preg_match( $pattern, $str, $matches ) ) {
			return true;

		} else {
			return false;
		}
	}

	/**
	 * Check is string contain minimum one lowercase
	 *
	 * @param $str
	 *
	 * @return bool; true for having lowercase
	 */
	public function isContainLowercase( $str ) {
		$pattern = '/[a-z]/';
		if ( preg_match( $pattern, $str, $matches ) ) {
			return true;

		} else {
			return false;
		}
	}

	/**
	 * Check is string contain minimum one number
	 *
	 * @param $str
	 *
	 * @return bool; true for having number
	 */
	public function isContainNumber( $str ) {
		$pattern = '/[0-9]/';
		if ( preg_match( $pattern, $str, $matches ) ) {
			return true;

		} else {
			return false;
		}
	}

	/**
	 * Check is string contain minimum single special character
	 *
	 * @param $str contain ~!@#$%^&*()_+`{}[]|\<>,.?
	 *
	 * @return bool; true for having special character
	 */
	public function isContainSpecialCharacter( $str ) {
		$pattern = '/[!@#$%^&*()\\-_=+{};\:,<\.>~|"\']/';
		if ( preg_match( $pattern, $str, $matches ) ) {
			return true;

		} else {
			return false;
		}
	}

	/**
	 * Checking is string length in required length
	 *
	 * @param $str
	 * @param int $minL ; minimum required length
	 * @param int $maxL ; maximum required length
	 *
	 * @return bool
	 */
	public function lengthValidation( $str, $minL = 5, $maxL = 8 ) {
		$length = strlen( $str );
		if ( ( $length >= $minL ) & ( $length <= $maxL ) ) {
			return true;

		} else {
			return false;
		}
	}

	function password_strength_check( $password, $min_len = 8, $max_len = 70, $req_digit = 1, $req_lower = 1, $req_upper = 1, $req_symbol = 1 ) {
		// Build regex string depending on requirements for the password
		$regex = '/^';
		if ( $req_digit == 1 ) {
			$regex .= '(?=.*\d)';
		}              // Match at least 1 digit
		if ( $req_lower == 1 ) {
			$regex .= '(?=.*[a-z])';
		}           // Match at least 1 lowercase letter
		if ( $req_upper == 1 ) {
			$regex .= '(?=.*[A-Z])';
		}           // Match at least 1 uppercase letter
		if ( $req_symbol == 1 ) {
			$regex .= '(?=.*[^a-zA-Z\d])';
		}    // Match at least 1 character that is none of the above
		$regex .= '.{' . $min_len . ',' . $max_len . '}$/';

		if ( preg_match( $regex, $password ) ) {
			return true;
		} else {
			return false;
		}
	}

	function change_login() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}

		$password = sanitize_text_field( $_POST['fs_password'] );
		$login    = sanitize_text_field( $_POST['fs_login'] );

		$current_user = wp_get_current_user();


		if ( $login && $password ) {
			$user_id = wp_update_user( array(
				'ID'         => $current_user->ID,
				'user_pass'  => $password,
				'user_login' => $login,
			) );
			if ( ! is_wp_error( $user_id ) ) {
				wp_send_json_success( array( 'msg' => __( 'Your data has been successfully changed', 'f-shop' ) ) );
			} else {
				wp_send_json_success( array( 'msg' => $user_id->get_error_message() ) );
			}

		}

		wp_send_json_error( array( 'msg' => __( 'Your data has not been changed, or you did not specify it.', 'f-shop' ) ) );
	}


	/**
	 * Returns all user fields
	 *
	 * Data from these fields can be used:
	 *  1. In the order form
	 *  2. In the registration form
	 *  3. In the login form
	 *
	 *  If you add 'save_meta' => false then the field will not be saved in the user's meta field
	 *
	 *
	 * @param int $user_id user identifier whose data to receive
	 *
	 * @return mixed|void
	 */
	public static function get_user_fields( $user_id = 0 ) {

		$user = $user_id == 0 ? wp_get_current_user() : get_user_by( 'ID', $user_id );

		if ( isset( $user->ID ) ) {
			$user_id = intval( $user->ID );
		}

		$fields = array(
			'fs_email'      => array(
				'name'        => __( 'E-mail', 'f-shop' ),
				'type'        => 'email',
				'label'       => '',
				'value'       => ! empty( $user->user_email ) ? $user->user_email : '',
				'placeholder' => __( 'Your email', 'f-shop' ),
				'title'       => __( 'Keep the correct email', 'f-shop' ),
				'required'    => true,
				'checkout'    => true,
				'save_meta'   => false
			),
			'fs_first_name' => array(
				'name'        => __( 'First name', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'value'       => ! empty( $user->first_name ) ? $user->first_name : '',
				'placeholder' => __( 'First name', 'f-shop' ),
				'title'       => __( 'This field is required.', 'f-shop' ),
				'required'    => true,
				'checkout'    => true,
				'save_meta'   => false
			),
			'fs_last_name'  => array(
				'name'        => __( 'Last name', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'value'       => ! empty( $user->last_name ) ? $user->last_name : '',
				'placeholder' => __( 'Last name', 'f-shop' ),
				'title'       => __( 'This field is required.', 'f-shop' ),
				'required'    => true,
				'checkout'    => true,
				'save_meta'   => false
			),

			'fs_user_avatar'       => array(
				'name'        => __( 'Avatar', 'f-shop' ),
				'type'        => 'file',
				'label'       => '',
				'placeholder' => __( 'Gender', 'f-shop' ),
				'title'       => '',
				'required'    => false,
				'save_meta'   => false
			),
			'fs_phone'             => array(
				'name'        => __( 'Phone number', 'f-shop' ),
				'type'        => 'tel',
				'label'       => '',
				'value'       => $user_id ? get_user_meta( $user_id, 'fs_phone', 1 ) : '',
				'placeholder' => __( 'Phone number', 'f-shop' ),
				'title'       => __( 'Keep the correct phone number', 'f-shop' ),
				'required'    => true,
				'checkout'    => true,

			),
			'fs_gender'            => array(
				'name'        => __( 'Gender', 'f-shop' ),
				'type'        => 'select',
				'label'       => '',
				'values'      => array(
					'Male'   => __( 'Male', 'f-shop' ),
					'Female' => __( 'Female', 'f-shop' )
				),
				'value'       => $user_id ? get_user_meta( $user_id, 'fs_gender', 1 ) : '',
				'placeholder' => __( 'Gender', 'f-shop' ),
				'title'       => '',
				'required'    => false
			),
			'fs_city'              => array(
				'name'        => __( 'City', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'City', 'f-shop' ),
				'title'       => __( 'This field is required.', 'f-shop' ),
				'required'    => true,
				'value'       => $user_id ? get_user_meta( $user_id, 'fs_city', 1 ) : '',
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_country'           => array(
				'name'        => __( 'Country', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Country', 'f-shop' ),
				'title'       => '',
				'value'       => $user_id ? get_user_meta( $user_id, 'fs_country', 1 ) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_zip_code'          => array(
				'name'        => __( 'Zip Code', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Zip Code', 'f-shop' ),
				'value'       => $user_id ? get_user_meta( $user_id, 'fs_zip_code', 1 ) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_region'            => array(
				'name'        => __( 'State / province', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'title'       => __( 'This field is required.', 'f-shop' ),
				'placeholder' => __( 'State / province', 'f-shop' ),
				'value'       => $user_id ? get_user_meta( $user_id, 'fs_region', 1 ) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_address'           => array(
				'name'        => __( 'Address', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Address', 'f-shop' ),
				'value'       => $user_id ? get_user_meta( $user_id, 'fs_address', 1 ) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_home_num'          => array(
				'name'        => __( 'House number', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'House number', 'f-shop' ),
				'value'       => $user_id ? get_user_meta( $user_id, 'fs_home_num', 1 ) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_apartment_num'     => array(
				'name'        => __( 'Apartment number', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Apartment number', 'f-shop' ),
				'value'       => $user_id ? get_user_meta( $user_id, 'fs_apartment_num', 1 ) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_delivery_number'   => array(
				'name'        => __( 'Branch number', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Branch number', 'f-shop' ),
				'value'       => $user_id ? get_user_meta( $user_id, 'fs_delivery_number', 1 ) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_delivery_methods'  => array(
				'name'         => __( 'Delivery methods', 'f-shop' ),
				'type'         => 'dropdown_categories',
				'first_option' => __( "Choose delivery method", 'f-shop' ),
				'taxonomy'     => FS_Config::get_data( 'product_del_taxonomy' ),
				'icon'         => true,
				'title'        => __( 'Choose shipping method', 'f-shop' ),
				'value'        => $user_id ? get_user_meta( $user_id, 'fs_delivery_methods', 1 ) : '',
				'values'       => get_terms( array(
					'taxonomy'   => FS_Config::get_data( 'product_del_taxonomy' ),
					'fields'     => 'id=>name',
					'hide_empty' => 0,
					'parent'     => 0
				) ),
				'required'     => true,
				'checkout'     => true,
				'save_meta'    => true


			),
			'fs_payment_methods'   => array(
				'name'         => __( 'Payment methods', 'f-shop' ),
				'type'         => 'dropdown_categories',
				'first_option' => __( "Choose a payment method", 'f-shop' ),
				'taxonomy'     => FS_Config::get_data( 'product_pay_taxonomy' ),
				'icon'         => true,
				'title'        => __( 'Select a Payment Method', 'f-shop' ),
				'value'        => $user_id ? get_user_meta( $user_id, 'fs_payment_methods', 1 ) : '',
				'query_params' => [
					'taxonomy'   => FS_Config::get_data( 'product_pay_taxonomy' ),
					'meta_query' => [
						'relation' => 'OR',
						[
							'key'     => '_fs_pay_inactive',
							'value'   => 1,
							'compare' => '!=',
							'type'    => 'NUMERIC'
						],
						[
							'key'     => '_fs_pay_inactive',
							'compare' => 'NOT EXISTS'
						]
					],
					'hide_empty' => 0,
					'parent'     => 0
				],
				'required'     => true,
				'checkout'     => true,
				'save_meta'    => true


			),
			'fs_comment'           => array(
				'name'        => __( 'Comment on the order', 'f-shop' ),
				'type'        => 'textarea',
				'label'       => '',
				'placeholder' => __( 'Comment on the order', 'f-shop' ),
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => false

			),
			'fs_customer_register' => array(
				'name'           => __( 'Register on the site', 'f-shop' ),
				'type'           => 'checkbox',
				'label'          => __( 'Register on the site', 'f-shop' ),
				'label_position' => 'after',
				'value'          => 1,
				'required'       => false,
				'checkout'       => true,
				'save_meta'      => false

			),
			'fs_subscribe_news'    => array(
				'name'           => __( 'Subscribe', 'f-shop' ),
				'type'           => 'checkbox',
				'label'          => __( 'Receive site news', 'f-shop' ),
				'label_position' => 'after',
				'required'       => false,
				'checkout'       => true,
				'value'          => get_user_meta( $user->ID, 'fs_subscribe_news', 1 )

			),
			'fs_subscribe_cart'    => array(
				'name'           => __( 'Receive a message about goods left in the basket', 'f-shop' ),
				'type'           => 'checkbox',
				'label'          => __( 'Receive a message about goods left in the basket', 'f-shop' ),
				'label_position' => 'after',
				'required'       => false,
				'checkout'       => true,
				'value'          => get_user_meta( $user->ID, 'fs_subscribe_cart', 1 )

			),
			'fs_login'             => array(
				'name'        => __( 'Login', 'f-shop' ),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __( 'Login', 'f-shop' ),
				'value'       => $user->user_login,
				'required'    => true,
				'save_meta'   => false
			),
			'fs_password'          => array(
				'name'        => __( 'Password', 'f-shop' ),
				'placeholder' => __( 'Password', 'f-shop' ),
				'type'        => 'password',
				'label'       => '',
				'value'       => '',
				'required'    => true,
				'save_meta'   => false
			),
			'fs_repeat_password'   => array(
				'name'        => __( 'Confirm password', 'f-shop' ),
				'placeholder' => __( 'Confirm password', 'f-shop' ),
				'type'        => 'password',
				'label'       => '',
				'value'       => '',
				'required'    => true,
				'save_meta'   => false
			),
		);

		return apply_filters( 'fs_user_fields', $fields );
	}


	/**
	 * Password reset
	 */
	static public function lost_password_ajax() {
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

		$replace_keys = [
			'site_url'    => get_bloginfo( 'url' ),
			'site_name'   => get_bloginfo( 'name' ),
			'admin_email' => get_bloginfo( 'admin_email' ),
			'password'    => $new_password,
			'first_name'  => $user->first_name,
		];

		$template = new FS_Template();

		FS_Form::send_email( $user_email, __( 'Password reset on the site', 'f-shop' ), $template->get( 'mail/templates/' . get_locale() . '/user-lost-password', $replace_keys ) );

		wp_send_json_success( [ 'msg' => __( 'Your password has been successfully reset. Password sent to your e-mail.', 'f-shop' ) ] );
	}


	/**
	 * Saves profile settings
	 */
	public function save_user_data() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}

		$user_fields = self::get_user_fields();
		$user_id     = get_current_user_id();

		require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/media.php' );


		// If for some reason the user’s fields have disappeared, although this is unlikely
		if ( ! is_array( $user_fields ) || ! count( $user_fields ) ) {
			wp_send_json_error( array( 'msg' => __( 'No fields found to save user data', 'f-shop' ) ) );
		}

		// If the user is not logged in
		if ( ! $user_id ) {
			wp_send_json_error( array( 'msg' => __( 'User is not found', 'f-shop' ) ) );
		}

		// Сохраняем данные пользователя
		foreach ( $user_fields as $meta_key => $user_field ) {
			if ( $user_field['type'] == 'file' && empty( $_FILES[ $meta_key ] ) ) {
				continue;
			}

			// Выходим из цикла если поля не существует
			if ( ! isset( $_POST[ $meta_key ] ) ) {
				continue;
			}

			$meta_value = trim( $_POST[ $meta_key ] );

			if ( $user_field['type'] == 'checkbox' && $meta_value != 1 ) {
				$meta_value = 0;
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

			// Сохраняем поля в данных ВП
			if ( $meta_key == 'fs_first_name' ) {
				wp_update_user( array(
					'ID'         => $user_id,
					'first_name' => $meta_value
				) );
				continue;
			} elseif ( $meta_key == 'fs_last_name' ) {
				wp_update_user( array(
					'ID'        => $user_id,
					'last_name' => $meta_value
				) );
				continue;
			}

			update_user_meta( $user_id, $meta_key, $meta_value );


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
	public function create_user_ajax() {

		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Failed verification of nonce form', 'f-shop' ) ) );
		}


		// POST data cleaning
		$allowed_fields = self::get_user_fields();
		$save_fields    = [];

		foreach ( $allowed_fields as $key => $field ) {
			$value = $_POST[ $key ];
			if ( $field['type'] == 'email' ) {
				$value = sanitize_email( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
			$save_fields[ $key ] = $value;
		}

		// Check if the transmitted address is an email address
		if ( ! is_email( $save_fields['fs_email'] ) ) {
			wp_send_json_error( [ 'msg' => __( 'Email does not match format', 'f-shop' ) ] );
		}

		// Check if the name field is filled
		if ( empty( $save_fields['fs_first_name'] ) ) {
			wp_send_json_error( [ 'msg' => __( 'Name field cannot be empty', 'f-shop' ) ] );
		}

		// Check password for reliability
		$check_password = $this->password_validation( $save_fields['fs_password'] );
		if ( $check_password !== true && is_array( $check_password ) ) {
			wp_send_json_error( $check_password );
		}

		// Adding a user to the database
		$user_id = wp_insert_user( array(
			'user_pass'            => $save_fields['fs_password'],
			'user_email'           => $save_fields['fs_email'],
			'user_login'           => $save_fields['fs_email'],
			'display_name'         => $save_fields['fs_first_name'],
			'role'                 => 'client',
			'show_admin_bar_front' => false
		) );

		// If an error occurred while adding a user
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( [ 'msg' => $user_id->get_error_message() ] );
		}

		// Keys for replacement in the letter
		$replace_keys = [
			'site_name'   => get_bloginfo( 'name' ),
			'first_name'  => $save_fields['fs_first_name'],
			'full_name'   => $save_fields['fs_first_name'],
			'password'    => $save_fields['fs_password'],
			'email'       => $save_fields['fs_email'],
			'admin_email' => get_bloginfo( 'admin_email' ),
			'site_url'    => get_bloginfo( 'url' ),
			'login'       => $save_fields['fs_email'],
		];


		$post_template = get_post( fs_option( 'register_mail_template', 0 ) );

		if ( $post_template ) {
			// A letter from the admin panel template
			$post_template_title   = apply_filters( 'the_title', $post_template->post_title );
			$post_template_content = apply_filters( 'the_content', $post_template->post_content );
		} else {
			// A letter if the template is not found
			$post_template_title   = __( 'Registration on the website «{{ site_name }}»', 'f-shop' );
			$post_template_content = fs_frontend_template( 'mail/templates/' . get_locale() . '/user-registration', [], '.twig' );
		}

		// Connect a template engine
		$template     = new FS_Template();
		$user_subject = $template->get_from_string( $post_template_title, $replace_keys );
		$user_message = $template->get_from_string( $post_template_content, $replace_keys );

		// Send a letter to the registered user
		FS_Form::send_email( $save_fields['fs_email'], $user_subject, $user_message );

		// Send a letter to the admin
		$admin_mail_header = $template->get_from_string( __( 'Registration on the website «{{ site_name }}»', 'f-shop' ), $replace_keys );
		$admin_message     = $template->get( 'mail/templates/' . get_locale() . '/user-registration-admin', $replace_keys );

		FS_Form::send_email( get_bloginfo( 'admin_email' ), $admin_mail_header, $admin_message );

		// Отправляем сообщение успешной регистрации на экран
		wp_send_json_success( array(
			'msg' => sprintf( __( 'Congratulations! You have successfully registered! <a href="%s">Log in</a>', 'f-shop' ), esc_url( get_permalink( fs_option( 'page_auth' ) ) ) )
		) );


	}

	/**
	 * Editing user profile
	 */
	public function fs_profile_edit() {
		if ( ! FS_Config::verify_nonce() || empty( $_POST['fs'] ) || ! is_user_logged_in() ) {
			wp_send_json_error( array(
				'status'  => 0,
				'message' => __( 'The form did not pass the security check!', 'f-shop' )
			) );
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
			'message' => __( 'Your data has been updated successfully!', 'f-shop' )
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
			'logged_in_text' => __( 'You are already logged in.', 'f-shop' )

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
			'logged_in_text' => __( 'You are already logged in.', 'f-shop' )

		) );

		$template = '';
		if ( is_user_logged_in() ) {
			$template .= '<p class="text-center">' . $args['logged_in_text'] . '</p>';
			$template .= '<p class="text-center"><a href="' . esc_url( get_the_permalink( fs_option( 'page_cabinet', 0 ) ) ) . '">' . __( 'To personal account', 'f-shop' ) . '</a></p>';
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
			'logged_in_text' => __( 'You are already logged in.', 'f-shop' )

		) );

		$template = '';
		if ( is_user_logged_in() ) {
			$template .= '<p class="text-center">' . $args['logged_in_text'] . '</p>';
			$template .= '<p class="text-center"><a href="' . esc_url( get_the_permalink( fs_option( 'page_cabinet', 0 ) ) ) . '">' . __( 'To personal account', 'f-shop' ) . '</a></p>';
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
			return FS_Users::login_form();

		}
	}

	/**
	 * Отвечает за создание вкладок личного кабинета и их содержимого
	 */
	static function user_cabinet_tabs() {

		$tabs = array(
			'personal_info'  => array(
				'title'     => __( 'Personal information', 'f-shop' ),
				'content'   => fs_frontend_template( 'dashboard/personal_info', array(
					'vars' => array(
						'user' => fs_get_current_user()
					)
				) ),
				'link'      => false,
				'nav_class' => 'nav-item nav-link active',
				'tab_class' => 'tab-pane fade active show'
			),
			'orders'         => array(
				'title'     => __( 'Current orders', 'f-shop' ),
				'content'   => fs_frontend_template( 'dashboard/orders', [
					'vars' => array(
						'orders' => FS_Orders::get_user_orders( 0, 'new' )
					)
				] ),
				'link'      => false,
				'nav_class' => 'nav-item nav-link',
				'tab_class' => 'tab-pane fade'
			),
			'orders_history' => array(
				'title'     => __( 'Purchase history', 'f-shop' ),
				'content'   => fs_frontend_template( 'dashboard/orders', [
					'vars' => array(
						'orders' => FS_Orders::get_user_orders()
					)
				] ),
				'link'      => false,
				'nav_class' => 'nav-item nav-link',
				'tab_class' => 'tab-pane fade'
			),
			'wishlist'       => array(
				'title'     => __( 'WishList', 'f-shop' ),
				'content'   => fs_frontend_template( 'dashboard/wishlist' ),
				'link'      => false,
				'nav_class' => 'nav-item nav-link',
				'tab_class' => 'tab-pane fade'
			),
			'reviews'        => array(
				'title'     => __( 'Reviews', 'f-shop' ),
				'content'   => fs_frontend_template( 'dashboard/reviews' ),
				'link'      => false,
				'nav_class' => 'nav-item nav-link',
				'tab_class' => 'fade'
			),
			'logout'         => array(
				'title'     => __( 'Logout', 'f-shop' ),
				'content'   => null,
				'link'      => true,
				'link_href' => wp_logout_url( $_SERVER['REQUEST_URI'] ),
				'nav_class' => 'nav-item nav-link',
				'tab_class' => 'tab-pane fade'
			)
		);
		$tabs = apply_filters( 'fs_user_cabinet_tabs', $tabs );


		if ( empty( $tabs ) || ! is_array( $tabs ) ) {
			return false;
		}


		echo '<div class="fs-dashboard">';
		do_action( 'fs_dashboard_tabs_before' );
		echo '<div class="nav nav-tabs" id="fs-dashboard-nav" role="tablist">';
		do_action( 'fs_dashboard_nav_before' );

		foreach ( $tabs as $tab_id => $tab ) {
			$href   = '#fs-dashboard-' . $tab_id;
			$toggle = 'tab';
			if ( $tab['link'] ) {
				$href   = $tab['link_href'];
				$toggle = 'no-tab';
			}
			echo '<a class="' . esc_attr( $tab['nav_class'] ) . '" data-toggle="' . esc_attr( $toggle ) . '" href="' . esc_attr( $href ) . '" role="tab" aria-controls="' . esc_attr( $tab_id ) . '">' . $tab['title'] . '</a>';
		}

		do_action( 'fs_dashboard_nav_after' );
		echo '</div><!-- end #fs-dashboard-nav -->';
		do_action( 'fs_dashboard_tabs_content_before' );
		echo '<div class="tab-content" id="fs-dashboard-content">';
		$index = 0;
		foreach ( $tabs as $tab_id => $tab ) {
			if ( $tab['link'] ) {
				continue;
			}
			$tab_class = $tab['tab_class'];
			if ( $index === 0 ) {
				$tab_class = $tab['tab_class'] . ' fade show in';
			}
			echo '<div class="' . esc_attr( $tab_class ) . '" id="fs-dashboard-' . esc_attr( $tab_id ) . '" role="tabpanel" aria-labelledby="fs-dashboard-' . esc_attr( $tab_id ) . '">' . $tab['content'] . '</div>';
			$index ++;
		}

		echo '</div><!-- end #fs-dashboard-content -->';
		do_action( 'fs_dashboard_tabs_after' );
		echo '</div><!-- end #fs-dashboard -->';


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