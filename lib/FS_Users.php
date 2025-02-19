<?php

namespace FS;

/**
 * Class FS_Users
 *
 * @package FS
 */
class FS_Users
{

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

	function __construct()
	{

		$this->form = new FS_Form();

		// User Authorization
		add_action('wp_ajax_fs_login', array($this, 'login_user'));
		add_action('wp_ajax_nopriv_fs_login', array($this, 'login_user'));

		//  Create user profile
		add_action('wp_ajax_fs_profile_create', array($this, 'create_profile_callback'));
		add_action('wp_ajax_nopriv_fs_profile_create', array($this, 'create_profile_callback'));

		//  Editing user profile
		add_action('wp_ajax_fs_profile_edit', array($this, 'fs_profile_edit'));
		add_action('wp_ajax_nopriv_fs_profile_edit', array($this, 'fs_profile_edit'));

		// Saves profile settings
		add_action('wp_ajax_fs_save_user_data', array($this, 'save_user_data'));
		add_action('wp_ajax_nopriv_fs_save_user_data', array($this, 'save_user_data'));

		// Password reset
		add_action('wp_ajax_fs_lostpassword', array($this, 'lost_password_ajax'));
		add_action('wp_ajax_nopriv_fs_lostpassword', array($this, 'lost_password_ajax'));

		// Login and password change
		add_action('wp_ajax_fs_change_login', array($this, 'change_login'));
		add_action('wp_ajax_nopriv_fs_change_login', array($this, 'change_login'));

		// Change password
		add_action('wp_ajax_fs_change_password', array($this, 'change_password'));
		add_action('wp_ajax_nopriv_fs_change_password', array($this, 'change_password'));

		// Protection of personal account from unauthorized users
		add_action('template_redirect', array($this, 'cabinet_protect'));

		// Add the field to user profile editing screen
		add_action('show_user_profile', [$this, 'admin_profile_edit_fields']);

		// Add the save action to user's own profile editing screen update.
		add_action('edit_user_profile', [$this, 'admin_profile_edit_fields']);

		// Add the save action to user profile editing screen update.
		add_action('personal_options_update', [$this, 'admin_profile_save_fields']);

		// Add the save action to user profile editing screen update.
		add_action('edit_user_profile_update', [$this, 'admin_profile_save_fields']);

		// User registration form
		add_action('fs_register_form', [$this, 'register_form']);
	}

	/**
	 * Displays fields in user profile editing
	 *
	 * @param $user
	 */
	public function admin_profile_edit_fields($user)
	{
?>
		<h2><?php esc_html_e('Delivery Settings', 'f-shop'); ?></h2>
		<table class="form-table">
			<?php foreach (self::get_user_fields($user->ID) as $name => $user_field) {
				if (isset($user_field['save_meta']) && $user_field['save_meta'] == false) {
					continue;
				}
			?>
				<tr>
					<th>
						<label
							for="<?php echo esc_attr(str_replace('_', '-', $name)); ?>"><?php echo esc_html($user_field['name']) ?></label>
					</th>
					<td>
						<?php
						$args = wp_parse_args($user_field, [
							'value' => get_user_meta($user->ID, $name, 1),
							'id'    => str_replace('_', '-', $name),
							'class' => 'regular-text',
						]);
						unset($args['name']);
						$this->form->render_field($name, $user_field['type'], $args);
						if (! empty($user_field['description'])): ?>
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
	function admin_profile_save_fields($user_id)
	{
		// check that the current user have the capability to edit the $user_id
		if (! current_user_can('edit_user', $user_id)) {
			return false;
		}

		foreach (self::get_user_fields($user_id) as $meta_key => $user_field) {
			if (isset($user_field['save_meta']) && $user_field['save_meta'] == false) {
				continue;
			}

			$meta_value = sanitize_text_field($_POST[$meta_key]);
			update_user_meta($user_id, $meta_key, $meta_value);
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
	public function password_validation($str)
	{
		$result['status'] = false;

		if (empty($str)) {
			$result['msg']    = __('Password can not be empty', 'f-shop');
			$result['status'] = false;
		} elseif (($this->rules['lengthStatus'] == true) & ($this->lengthValidation($str, $this->rules['minLength'], $this->rules['maxLength']) == false)) {
			$result['msg']    = sprintf(__('Your password must be %s to %s characters', 'f-shop'), $this->rules['minLength'], $this->rules['maxLength']);
			$result['status'] = false;
		} elseif (($this->rules['numberStatus'] == true) & ($this->isContainNumber($str) == false)) {
			$result['msg']    = __('Your password must contain at least one number.', 'f-shop');
			$result['status'] = false;
		} elseif (($this->rules['uppercaseStatus'] == true) & ($this->isContainUppercase($str) == false)) {
			$result['msg']    = __('Your password must contain at least one uppercase letter.', 'f-shop');
			$result['status'] = false;
		} elseif (($this->rules['lowercaseStatus'] == true) & ($this->isContainLowercase($str) == false)) {
			$result['msg']    = __('Your password must contain at least one lowercase letter.', 'f-shop');
			$result['status'] = false;
		} elseif (($this->rules['specialCharacterStatus'] == true) & ($this->isContainSpecialCharacter($str) == false)) {
			$result['msg']    = __('Your password must contain at least one special character.', 'f-shop');
			$result['status'] = false;
		} elseif (($this->rules['whiteSpaceStatus'] == true) & ($this->isWhiteSpaceContain($str) == false)) {
			$result['msg']    = __('Space is not allow in password', 'f-shop');
			$result['status'] = false;
		} else {
			$result['status'] = true;
			$result['msg']    = __('Password is valid', 'f-shop');
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
	public function isWhiteSpaceContain($str)
	{
		$str = preg_replace('/\s\s+/', ' ', $str);
		if (strpos($str, ' ') | preg_match(' ', $str)) {
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
	public function isContainUppercase($str)
	{
		$pattern = '/[A-Z]/';
		if (preg_match($pattern, $str, $matches)) {
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
	public function isContainLowercase($str)
	{
		$pattern = '/[a-z]/';
		if (preg_match($pattern, $str, $matches)) {
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
	public function isContainNumber($str)
	{
		$pattern = '/[0-9]/';
		if (preg_match($pattern, $str, $matches)) {
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
	public function isContainSpecialCharacter($str)
	{
		$pattern = '/[!@#$%^&*()\\-_=+{};\:,<\.>~|"\']/';
		if (preg_match($pattern, $str, $matches)) {
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
	public function lengthValidation($str, $minL = 5, $maxL = 8)
	{
		$length = strlen($str);
		if (($length >= $minL) & ($length <= $maxL)) {
			return true;
		} else {
			return false;
		}
	}

	function password_strength_check($password, $min_len = 8, $max_len = 70, $req_digit = 1, $req_lower = 1, $req_upper = 1, $req_symbol = 1)
	{
		// Build regex string depending on requirements for the password
		$regex = '/^';
		if ($req_digit == 1) {
			$regex .= '(?=.*\d)';
		}              // Match at least 1 digit
		if ($req_lower == 1) {
			$regex .= '(?=.*[a-z])';
		}           // Match at least 1 lowercase letter
		if ($req_upper == 1) {
			$regex .= '(?=.*[A-Z])';
		}           // Match at least 1 uppercase letter
		if ($req_symbol == 1) {
			$regex .= '(?=.*[^a-zA-Z\d])';
		}    // Match at least 1 character that is none of the above
		$regex .= '.{' . $min_len . ',' . $max_len . '}$/';

		if (preg_match($regex, $password)) {
			return true;
		} else {
			return false;
		}
	}

	function change_login()
	{
		if (! FS_Config::verify_nonce()) {
			wp_send_json_error(array('msg' => __('Failed verification of nonce form', 'f-shop')));
		}

		$password = sanitize_text_field($_POST['fs_password']);
		$login    = sanitize_text_field($_POST['fs_login']);

		$current_user = wp_get_current_user();


		if ($login && $password) {
			$user_id = wp_update_user(array(
				'ID'         => $current_user->ID,
				'user_pass'  => $password,
				'user_login' => $login,
			));
			if (! is_wp_error($user_id)) {
				wp_send_json_success(array('msg' => __('Your data has been successfully changed', 'f-shop')));
			} else {
				wp_send_json_success(array('msg' => $user_id->get_error_message()));
			}
		}

		wp_send_json_error(array('msg' => __('Your data has not been changed, or you did not specify it.', 'f-shop')));
	}


	/**
	 * Change the user's password after validation and update the user data.
	 *
	 */
	function change_password()
	{
		if (! FS_Config::verify_nonce()) {
			wp_send_json_error(array('msg' => __('Failed verification of nonce form', 'f-shop')));
		}

		$current_user = wp_get_current_user();

		// Check old password
		if (empty($_POST['fs_old_password']) || ! wp_check_password($_POST['fs_old_password'], $current_user->user_pass)) {
			wp_send_json_error(array('msg' => __('Incorrect old password', 'f-shop')));
		}

		// Password and repeat password
		if ($_POST['fs_password'] != $_POST['fs_password_repeat']) {
			wp_send_json_error(array('msg' => __('Passwords do not match', 'f-shop')));
		}

		// Validation
		$password            = sanitize_text_field($_POST['fs_password']);
		$password_validation = $this->password_validation($password);

		if ($password_validation['status'] == false) {
			wp_send_json_error(array('msg' => $password_validation['msg']));
		}

		wp_set_password($password, $current_user->ID);

		$current_user = wp_get_current_user();
		if (wp_check_password($password, $current_user->user_pass, $current_user->ID)) {
			wp_send_json_success(['msg' => __('Your password has been successfully changed', 'f-shop')]);
		}

		wp_send_json_success(['msg' => __('Your data has been successfully changed', 'f-shop')]);
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
	public static function get_user_fields($user_id = 0)
	{

		$user = $user_id == 0 ? wp_get_current_user() : get_user_by('ID', $user_id);

		if (isset($user->ID)) {
			$user_id = intval($user->ID);
		}

		$fields = array(
			'fs_email'                  => array(
				'name'        => __('E-mail', 'f-shop'),
				'type'        => 'email',
				'label'       => '',
				'value'       => fs_option('fs_autofill_form') && ! empty($user->user_email) ? $user->user_email : '',
				'placeholder' => __('Your email', 'f-shop'),
				'title'       => __('Keep the correct email', 'f-shop'),
				'checkout'    => true,
				'save_meta'   => false,
				'required'    => true
			),
			'fs_first_name'             => array(
				'name'        => __('First name', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'value'       => fs_option('fs_autofill_form') && ! empty($user->first_name) ? $user->first_name : '',
				'placeholder' => __('First name', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'checkout'    => true,
				'save_meta'   => false,
				'required'    => true
			),
			'fs_last_name'              => array(
				'name'        => __('Last name', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'value'       => fs_option('fs_autofill_form') && ! empty($user->last_name) ? $user->last_name : '',
				'placeholder' => __('Last name', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'checkout'    => true,
				'save_meta'   => false,
				'required'    => false
			),
			'fs_other_shipping_address' => array(
				'name'        => __('Other shipping address', 'f-shop'),
				'type'        => 'checkbox',
				'label'       => '',
				'value'       => '',
				'placeholder' => __('Other shipping address', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'required'    => false,
				'checkout'    => true,
				'alpine'      => 'fs_other_shipping_address:false',
				'save_meta'   => false
			),
			'fs_shipping_name'          => array(
				'name'        => __('Delivery service name', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'value'       => '',
				'placeholder' => __('Delivery service name', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'checkout'    => true,
				'save_meta'   => false
			),

			'fs_shipping_first_name' => array(
				'name'        => __('First name', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'value'       => '',
				'placeholder' => __('First name', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'attributes'  => [
					'x-bind:required' => 'fs_other_shipping_address'
				],
				'checkout'    => true,
				'save_meta'   => false
			),
			'fs_shipping_last_name'  => array(
				'name'        => __('Last name', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'value'       => '',
				'placeholder' => __('Last name', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => false
			),
			'fs_shipping_email'      => array(
				'name'        => __('E-mail', 'f-shop'),
				'type'        => 'email',
				'label'       => '',
				'value'       => '',
				'placeholder' => __('Your email', 'f-shop'),
				'title'       => __('Keep the correct email', 'f-shop'),
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => false
			),
			'fs_shipping_phone'      => array(
				'name'        => __('Phone', 'f-shop'),
				'type'        => 'tel',
				'label'       => '',
				'value'       => '',
				'placeholder' => __('Phone', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'attributes'  => [
					'x-bind:required' => 'fs_other_shipping_address'
				],
				'checkout'    => true,
				'save_meta'   => false
			),
			'fs_shipping_address'    => array(
				'name'        => __('Address', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'value'       => '',
				'placeholder' => __('Address', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => false
			),
			'fs_shipping_city'       => array(
				'name'        => __('City', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'value'       => '',
				'placeholder' => __('City', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true
			),
			'fs_shipping_state'      => array(
				'name'        => __('State', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'value'       => '',
				'placeholder' => __('State', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => false
			),
			'fs_shipping_zip'        => array(
				'name'        => __('Zip', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'value'       => '',
				'placeholder' => __('Zip', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => false
			),

			'fs_user_avatar' => array(
				'name'        => __('Avatar', 'f-shop'),
				'type'        => 'file',
				'label'       => '',
				'placeholder' => __('Gender', 'f-shop'),
				'title'       => '',
				'required'    => false,
				'save_meta'   => false
			),

			'fs_phone'             => array(
				'name'        => __('Phone number', 'f-shop'),
				'type'        => 'tel',
				'label'       => '',
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_phone', 1) : '',
				'placeholder' => __('Phone number', 'f-shop'),
				'title'       => __('Keep the correct phone number', 'f-shop'),
				'required'    => true,
				'checkout'    => true,
				'mask'        => fs_option('fs_phone_mask', '+380 (99) 999-99-99'),
			),
			'fs_gender'            => array(
				'name'        => __('Gender', 'f-shop'),
				'type'        => 'select',
				'label'       => '',
				'values'      => array(
					'Male'   => __('Male', 'f-shop'),
					'Female' => __('Female', 'f-shop')
				),
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_gender', 1) : '',
				'placeholder' => __('Gender', 'f-shop'),
				'title'       => '',
				'required'    => false
			),
			'fs_city'              => array(
				'name'        => __('City', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __('City', 'f-shop'),
				'title'       => __('This field is required.', 'f-shop'),
				'required'    => false,
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_city', 1) : '',
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_country'           => array(
				'name'        => __('Country', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __('Country', 'f-shop'),
				'title'       => '',
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_country', 1) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_zip_code'          => array(
				'name'        => __('Zip Code', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __('Zip Code', 'f-shop'),
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_zip_code', 1) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_region'            => array(
				'name'        => __('State / province', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'title'       => __('This field is required.', 'f-shop'),
				'placeholder' => __('State / province', 'f-shop'),
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_region', 1) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_address'           => array(
				'name'        => __('Address', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __('Address', 'f-shop'),
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_address', 1) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_street'            => array(
				'name'        => __('Street', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __('Street', 'f-shop'),
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_address', 1) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true
			),
			'fs_home_num'          => array(
				'name'        => __('House number', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __('House number', 'f-shop'),
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_home_num', 1) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_apartment_num'     => array(
				'name'        => __('Apartment number', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __('Apartment number', 'f-shop'),
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_apartment_num', 1) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_entrance_num'      => array(
				'name'        => __('Entrance number', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __('Entrance number', 'f-shop'),
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_apartment_num', 1) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_delivery_number'   => array(
				'name'        => __('Branch number', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __('Branch number', 'f-shop'),
				'value'       => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_delivery_number', 1) : '',
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => true

			),
			'fs_delivery_methods'  => array(
				'name'         => __('Delivery methods', 'f-shop'),
				'type'         => 'dropdown_categories',
				'first_option' => __("Choose delivery method", 'f-shop'),
				'taxonomy'     => FS_Config::get_data('product_del_taxonomy'),
				'icon'         => true,
				'title'        => __('Choose shipping method', 'f-shop'),
				'value'        => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_delivery_methods', 1) : '',
				'values'       => get_terms(array(
					'taxonomy'   => FS_Config::get_data('product_del_taxonomy'),
					'fields'     => 'id=>name',
					'hide_empty' => 0,
					'parent'     => 0
				)),
				'required'     => false,
				'checkout'     => true,
				'save_meta'    => true


			),
			'fs_payment_methods'   => array(
				'name'         => __('Payment methods', 'f-shop'),
				'type'         => 'dropdown_categories',
				'first_option' => __("Choose a payment method", 'f-shop'),
				'taxonomy'     => FS_Config::get_data('product_pay_taxonomy'),
				'icon'         => true,
				'title'        => __('Select a Payment Method', 'f-shop'),
				'value'        => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_payment_methods', 1) : '',
				'values'       => get_terms(array(
					'taxonomy'   => FS_Config::get_data('product_pay_taxonomy'),
					'fields'     => 'id=>name',
					'hide_empty' => 0,
					'parent'     => 0,
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
				)),
				'required'     => false,
				'checkout'     => true,
				'save_meta'    => true


			),
			'fs_comment'           => array(
				'name'        => __('Comment on the order', 'f-shop'),
				'type'        => 'textarea',
				'label'       => '',
				'placeholder' => __('Comment on the order', 'f-shop'),
				'required'    => false,
				'checkout'    => true,
				'save_meta'   => false

			),
			'fs_customer_register' => array(
				'name'           => __('Register on the site', 'f-shop'),
				'type'           => 'checkbox',
				'label'          => __('Register on the site', 'f-shop'),
				'label_position' => 'after',
				'value'          => 0,
				'required'       => false,
				'checkout'       => true,
				'save_meta'      => false
			),
			'fs_subscribe_news'    => array(
				'name'           => __('Subscribe', 'f-shop'),
				'type'           => 'checkbox',
				'label'          => __('Receive site news', 'f-shop'),
				'label_position' => 'after',
				'required'       => false,
				'checkout'       => true,
				'value'          => fs_option('fs_autofill_form') && get_user_meta($user->ID, 'fs_subscribe_news', 1) ? get_user_meta($user->ID, 'fs_subscribe_news', 1) : 0

			),
			'fs_subscribe_cart'    => array(
				'name'           => __('Receive a message about goods left in the basket', 'f-shop'),
				'type'           => 'checkbox',
				'label'          => __('Receive a message about goods left in the basket', 'f-shop'),
				'label_position' => 'after',
				'required'       => false,
				'checkout'       => true,
				'value'          => fs_option('fs_autofill_form') && get_user_meta($user->ID, 'fs_subscribe_cart', 1) ? get_user_meta($user->ID, 'fs_subscribe_cart', 1) : ''

			),
			'fs_login'             => array(
				'name'        => __('Login', 'f-shop'),
				'type'        => 'text',
				'label'       => '',
				'placeholder' => __('Login', 'f-shop'),
				'value'       => fs_option('fs_autofill_form') ? $user->user_login : '',
				'required'    => true,
				'save_meta'   => false,
				'attributes'  => array(
					'autocomplete' => 'off'
				)
			),
			'fs_password'          => array(
				'name'        => __('Password', 'f-shop'),
				'placeholder' => __('Password', 'f-shop'),
				'type'        => 'password',
				'label'       => '',
				'value'       => '',
				'required'    => true,
				'save_meta'   => false,
				'attributes'  => array(
					'autocomplete' => 'off'
				)
			),
			'fs_repeat_password'   => array(
				'name'        => __('Confirm password', 'f-shop'),
				'placeholder' => __('Confirm password', 'f-shop'),
				'type'        => 'password',
				'label'       => '',
				'value'       => '',
				'required'    => true,
				'save_meta'   => false
			),
		);

		return apply_filters('fs_user_fields', $fields);
	}

	/**
	 * Возвращает поле пользователя
	 *
	 * @param $key
	 * @param int $user_id
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	public static function get_user_field($key, $user_id = 0, $default = '')
	{
		if (! $user_id) {
			$user_id = get_current_user_id();
		}

		return get_user_meta($user_id, $key, 1) != ''
			? get_user_meta($user_id, $key, 1)
			: $default;
	}


	/**
	 * Password reset
	 */
	static public function lost_password_ajax()
	{
		if (! FS_Config::verify_nonce()) {
			wp_send_json_error(array('msg' => __('Failed verification of nonce form', 'f-shop')));
		}

		$user_email = sanitize_email($_POST['user_login']);

		if (! email_exists($user_email)) {
			wp_send_json_error(array('msg' => __('This user does not exist on the site', 'f-shop')));
		}

		if (is_user_logged_in()) {
			wp_send_json_error(array('msg' => __('You are already logged in', 'f-shop')));
		}

		$user = get_user_by('email', $user_email);

		$new_password = wp_generate_password();

		wp_set_password($new_password, $user->ID);

		$replace_keys = [
			'site_url'    => get_bloginfo('url'),
			'site_name'   => get_bloginfo('name'),
			'admin_email' => get_bloginfo('admin_email'),
			'password'    => $new_password,
			'first_name'  => $user->first_name,
		];

		$notification = new FS_Notification();
		$notification->set_recipients([$user_email]);
		$notification->set_subject(__('Password reset on the site', 'f-shop'));
		$notification->set_message(__('A password reset request was received on the site "%site_name%". Your new password is: %password%. If this was not you, please ignore this email.', 'f-shop'), $replace_keys);
		$notification->send($user_email, 'user-lost-password', $replace_keys);

		wp_send_json_success(['msg' => __('Your password has been successfully reset. Password sent to your e-mail.', 'f-shop')]);
	}


	/**
	 * Saves user data submitted through the form.
	 *
	 * This method validates the submitted data, checks for user authentication,
	 * and proceeds to save or update various user-related fields, such as text, files,
	 * and special fields like names and phone numbers. It includes nonce verification
	 * for security, handles file uploads, and applies filters for data transformation.
	 *
	 * @return void Outputs a JSON response indicating success or failure with a corresponding message.
	 */
	public function validate_user_data($data, $validate_only = [])
	{
		$validated_data = array();
		$errors = array();
		$user_fields = FS_Users::get_user_fields();

		// Проверяем только те поля, которые пришли в POST запросе
		foreach ($data as $meta_key => $meta_value) {
			// Пропускаем поля указанные в переменной $validate_only
			if (!empty($validate_only) && !in_array($meta_key, $validate_only)) {
				continue;
			}

			$user_field = $user_fields[$meta_key];
			$field_errors = array();

			// Проверка обязательных полей
			if (!empty($user_field['required']) && empty($meta_value)) {
				$field_label = !empty($user_field['name']) ? $user_field['name'] : $meta_key;
				$field_errors[] = sprintf(
					__('The "%s" field is required!', 'f-shop'),
					$field_label
				);
			}

			$meta_value = trim($meta_value);

			// Валидация телефона
			if ($meta_key == 'fs_phone' && !empty($meta_value)) {
				if (!FS_Form::validate_phone($meta_value)) {
					$field_errors[] = __('Invalid phone number', 'f-shop');
				}
			}

			// Валидация email
			if ($user_field['type'] == 'email' && !empty($meta_value)) {
				if (!is_email($meta_value)) {
					$field_errors[] = __('Invalid email address', 'f-shop');
				}
			}

			// Проверка длины поля
			if (isset($user_field['minlength']) && strlen($meta_value) < $user_field['minlength']) {
				$field_errors[] = sprintf(
					__('Minimum length is %d characters', 'f-shop'),
					$user_field['minlength']
				);
			}

			if (isset($user_field['maxlength']) && strlen($meta_value) > $user_field['maxlength']) {
				$field_errors[] = sprintf(
					__('Maximum length is %d characters', 'f-shop'),
					$user_field['maxlength']
				);
			}

			// Если есть ошибки для поля, добавляем их в общий массив ошибок
			if (!empty($field_errors)) {
				$errors[$meta_key] = $field_errors;
				continue;
			}

			// Обработка значений по типу поля
			switch ($user_field['type']) {
				case 'checkbox':
					$meta_value = $meta_value == 1 || $meta_value === 'on' ? 1 : 0;
					break;

				case 'phone':
					$meta_value = preg_replace('/[^0-9]/', '', $meta_value);
					break;

				case 'number':
					$meta_value = filter_var($meta_value, FILTER_SANITIZE_NUMBER_INT);
					break;
			}

			$validated_data[$meta_key] = $meta_value;
		}

		return array(
			'errors' => $errors,
			'data' => $validated_data
		);
	}

	public function save_user_data()
	{
		if (!FS_Config::verify_nonce()) {
			wp_send_json_error(array('msg' => __('Failed verification of nonce form', 'f-shop')));
		}

		$locale = isset($_POST['locale']) ? sanitize_text_field($_POST['locale']) : get_locale();
		switch_to_locale($locale);

		$user_fields = self::get_user_fields();
		$user_id = get_current_user_id();

		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');

		if (!is_array($user_fields) || !count($user_fields)) {
			wp_send_json_error(array('msg' => __('No fields found to save user data', 'f-shop')));
		}

		if (!$user_id) {
			wp_send_json_error(array('msg' => __('User is not found', 'f-shop')));
		}

		// Проверяем, нужно ли валидировать только определенные поля
		if (!empty($_POST['fs_validate_only'])) {
			$validated_keys = explode(',', $_POST['fs_validate_only']);

			// Добавляем отсутствующие поля с дефолтными значениями
			foreach ($validated_keys as $key) {
				if (!isset($_POST[$key]) && isset($user_fields[$key])) {
					$default_value = '';

					// Устанавливаем дефолтные значения в зависимости от типа поля
					switch ($user_fields[$key]['type']) {
						case 'checkbox':
							$default_value = 0;
							break;
						default:
							$default_value = '';
					}

					$_POST[$key] = $default_value;
				}
			}

			// Фильтруем поля для валидации
			$user_fields = array_filter($user_fields, function ($key) use ($validated_keys) {
				return in_array($key, $validated_keys);
			}, ARRAY_FILTER_USE_KEY);
		}

		// Валидируем данные
		$validation_result = $this->validate_user_data($_POST, $user_fields);

		// Если есть ошибки валидации, возвращаем их
		if (!empty($validation_result['errors'])) {
			wp_send_json_error(array(
				'msg' => __('Validation failed', 'f-shop'),
				'errors' => $validation_result['errors']
			));
		}

		// Сохраняем валидированные данные
		foreach ($validation_result['data'] as $meta_key => $meta_value) {
			// Обработка загрузки файлов
			if (isset($user_fields[$meta_key]) && $user_fields[$meta_key]['type'] == 'file' && !empty($_FILES[$meta_key])) {
				$attach_id = media_handle_upload($meta_key, 0);
				if (is_wp_error($attach_id)) {
					wp_send_json_error(array('msg' => $attach_id->get_error_message()));
				}
				update_user_meta($user_id, $meta_key, intval($attach_id));
				continue;
			}

			// Обновление имени и фамилии
			if ($meta_key == 'fs_first_name' || $meta_key == 'fs_last_name') {
				wp_update_user([
					'ID' => $user_id,
					str_replace('fs_', '', $meta_key) => $meta_value
				]);
				continue;
			}

			// Применяем фильтры и сохраняем мета-данные
			$meta_value = apply_filters('fs_user_meta_before_save', $meta_value, $meta_key, $user_id);
			update_user_meta($user_id, $meta_key, $meta_value);
		}

		wp_send_json_success(array(
			'msg' => __('Your data has been successfully updated.', 'f-shop')
		));
	}

	/**
	 * Protecting your personal account from unauthorized users
	 */
	function cabinet_protect()
	{
		$redirect_page = fs_option('page_cabinet');
		$login_page    = fs_option('page_auth');
		if (empty($redirect_page)) {
			return;
		} elseif (is_page($redirect_page) && ! is_user_logged_in()) {
			if (empty($login_page)) {
				wp_redirect(home_url('/'));
			} else {
				wp_redirect(get_permalink((int) $login_page));
			}
		}
	}

	/**
	 * Authenticates a user based on the provided data.
	 *
	 * The 'username' field can contain either the login or the password.
	 * The 'password' field contains the password.
	 *
	 * @return void
	 */
	function login_user()
	{
		// If the user is already logged in, send an error message
		if (is_user_logged_in()) {
			$logout_url = wp_logout_url($_SERVER['REQUEST_URI']);
			$msg        = 'Вы уже авторизованны на сайте. <a href="' . $logout_url . '">Выход</a>. <a href="' . $redirect . '">В кабинет</a>';
			wp_send_json_error(['msg' => $msg]);
		}

		// Check if the nonce is valid
		if (! FS_Config::verify_nonce()) {
			// If the nonce is not valid, send an error message
			wp_send_json_error(['msg' => 'Неправильный проверочный код. Обратитесь к администратору сайта!']);
		}

		// Validate the data
		$validation_result = $this->validate_user_data($_POST, ['fs_login', 'fs_password']);

		// If there are errors, return them
		if (!empty($validation_result['errors'])) {
			wp_send_json_error(array(
				'msg' => __('Validation failed', 'f-shop'),
				'errors' => $validation_result['errors']
			));
		}

		// Get the cabinet page and its URL
		$redirect_page = fs_option('page_cabinet');
		$redirect      = ! empty($redirect_page) ? get_permalink($redirect_page) : false;

		// Get the user based on the username
		if (is_email($validation_result['data']['fs_login'])) {
			$user = get_user_by('email', $validation_result['data']['fs_login']);
		} else {
			$user = get_user_by('login', $validation_result['data']['fs_login']);
		}

		// If the user does not exist, send an error message
		if (! $user) {
			wp_send_json_error([
				'errors' => [
					'fs_email' => __('Unfortunately, a user with such data does not exist on the site', 'f-shop'),
					'fs_login' => __('Unfortunately, a user with such data does not exist on the site', 'f-shop')
				]
			]);
		} else {
			// Authenticate the user
			$auth = wp_authenticate($user->user_login, $validation_result['data']['fs_password']);

			// Check for authentication errors
			if (is_wp_error($auth)) {
				// If there is an error, send the error message
				$reset_password_page_url = fs_option('page_lostpassword')
					? get_permalink(fs_option('page_lostpassword')) : wp_lostpassword_url(home_url());
				wp_send_json_error([
					'errors' => [
						'password' => sprintf(__('The login information you entered is incorrect. <a href="%s">Password recovery</a>', 'f-shop'), $reset_password_page_url)
					]
				]);
			} else {
				// If the authentication is successful, clear the authentication cookie
				nocache_headers();
				wp_clear_auth_cookie();

				// Set the authentication cookie for the authenticated user
				wp_set_auth_cookie($auth->ID);

				// Send a success message with the redirect URL
				wp_send_json_success(['redirect' => $redirect]);
			}
		}
	}


	/**
	 * Создание профиля пользователя при регистрации
	 *
	 * @return void
	 */
	public function create_profile_callback()
	{

		if (! FS_Config::verify_nonce()) {
			wp_send_json_error(array('msg' => __('Failed verification of nonce form', 'f-shop')));
		}


		// POST data cleaning
		$allowed_fields    = self::get_user_fields();
		$save_fields       = [];
		$validation_errors = [];

		foreach ($allowed_fields as $key => $field) {
			$value = $_POST[$key];
			if ($field['type'] == 'email') {
				$value = sanitize_email($value);
			} else {
				$value = sanitize_text_field($value);
			}
			$save_fields[$key] = $value;
		}


		// Check if the transmitted address is an email address
		if (! is_email($save_fields['fs_email'])) {
			$validation_errors['fs_email'] = __('Email does not match format', 'f-shop');
		}

		// Check if the name field is filled
		if (empty($save_fields['fs_first_name'])) {
			$validation_errors['fs_first_name'] = __('Name field cannot be empty', 'f-shop');
		}

		// Check password for reliability
		$check_password = $this->password_validation($save_fields['fs_password']);
		if ($check_password['status'] !== true && is_array($check_password)) {
			$validation_errors['fs_password'] = $check_password['msg'];
		}

		if ($save_fields['fs_password'] !== $save_fields['fs_repeat_password']) {
			$validation_errors['fs_repeat_password'] = __('Passwords do not match', 'f-shop');
		}

		if (! isset($_POST['fs_rules'])) {
			$validation_errors['fs_rules'] = __('You must accept the terms and conditions', 'f-shop');
		}


		// If there are validation errors, send an error message
		if (! empty($validation_errors)) {
			wp_send_json_error(['errors' => $validation_errors]);
		}

		// Adding a user to the database
		$user_id = wp_insert_user(array(
			'user_pass'            => $save_fields['fs_password'],
			'user_email'           => $save_fields['fs_email'],
			'user_login'           => $save_fields['fs_email'],
			'display_name'         => $save_fields['fs_first_name'],
			'role'                 => 'client',
			'show_admin_bar_front' => false
		));

		// If an error occurred while adding a user
		if (is_wp_error($user_id)) {
			wp_send_json_error(['msg' => $user_id->get_error_message()]);
		}

		// Keys for replacement in the letter
		$replace_keys = [
			'site_name'   => get_bloginfo('name'),
			'first_name'  => $save_fields['fs_first_name'],
			'full_name'   => $save_fields['fs_first_name'],
			'password'    => $save_fields['fs_password'],
			'email'       => $save_fields['fs_email'],
			'admin_email' => get_bloginfo('admin_email'),
			'site_url'    => get_bloginfo('url'),
			'login'       => $save_fields['fs_email'],
			'cabinet_url' => fs_account_url(),
		];

		// Send notification to the user
		$notification = new FS_Notification();
		$notification->set_recipients([$save_fields['fs_email']]);
		$notification->set_subject(sprintf(__('Registration on the website «%s»', 'f-shop'), get_bloginfo('name')));
		$notification->set_template('mail/' . get_locale() . '/user-registration', $replace_keys);
		$notification->send();

		// Send a letter to the admin
		$notification->set_recipients([get_bloginfo('admin_email')]);
		$notification->set_template('mail/' . get_locale() . '/user-registration-admin', $replace_keys);
		$notification->send();

		// Отправляем сообщение успешной регистрации на экран
		wp_send_json_success(array(
			'msg' => sprintf(__('Congratulations! You have successfully registered! <a href="%s">Log in</a>', 'f-shop'), esc_url(get_permalink(fs_option('page_auth'))))
		));
	}

	/**
	 * Editing user profile
	 */
	public function fs_profile_edit()
	{
		if (! FS_Config::verify_nonce() || empty($_POST['fs']) || ! is_user_logged_in()) {
			wp_send_json_error(array(
				'status'  => 0,
				'message' => __('The form did not pass the security check!', 'f-shop')
			));
		}

		$user = wp_get_current_user();

		foreach (FS_Config::$user_meta as $meta_key => $meta_field) {
			$name  = $meta_field['name'];
			$value = sanitize_text_field($_POST['fs'][$name]);

			if (empty($value)) {
				delete_user_meta($user->ID, $meta_key);
				continue;
			}

			switch ($meta_key) {
				case 'display_name':
					$update_user = wp_update_user(array(
						'ID'           => $user->ID,
						'display_name' => $value
					));
					if (is_wp_error($update_user)) {
						$errors = $update_user->get_error_message();
						echo json_encode(array(
							'status'  => 0,
							'message' => $errors
						));
						exit;
					}
					break;
				case 'user_email':
					$email = sanitize_email($_POST['fs'][$name]);
					if (! is_email($email)) {
						echo json_encode(array(
							'status'  => 0,
							'message' => 'E-mail не соответствует формату!'
						));
						exit;
					} else {
						$update_user = wp_update_user(array(
							'ID'         => $user->ID,
							'user_email' => $email
						));
						if (is_wp_error($update_user)) {
							$errors = $update_user->get_error_message();
							echo json_encode(array(
								'status'  => 0,
								'message' => $errors
							));
							exit;
						}
					}


					break;
				case 'birth_day':
					update_user_meta($user->ID, $meta_key, strtotime($value));
					break;
				default:
					update_user_meta($user->ID, $meta_key, $value);
					break;
			}
		}

		echo json_encode(array(
			'status'  => 1,
			'message' => __('Your data has been updated successfully!', 'f-shop')
		));
		exit;
	}

	/**
	 * Generates and optionally outputs a login form with specified attributes and functionality.
	 *
	 * @param array $args {
	 *     Optional. An array of arguments to configure the login form.
	 *
	 * @type string $class The CSS class for the form container. Default 'fs-login-form'.
	 * @type string $name The name attribute for the form. Default 'fs-login'.
	 * @type string $method The HTTP method for form submission. Default 'post'.
	 * @type string $data -logged-in-text Text displayed when the user is already logged in. Default 'You are already logged in.'.
	 * @type bool $echo Whether to echo the output or return it. Default false.
	 * @type string $inline_attributes Inline attributes for the form to handle Alpine.js interaction. Default predefined Alpine.js directive.
	 * }
	 * @return string|null The generated login form as a string, or null if `$args['echo']` is set to true.
	 */
	public static function login_form($args = array())
	{
		$args = wp_parse_args($args, array(
			'class'               => 'fs-login-form',
			'name'                => 'fs-login',
			'method'              => 'post',
			'data-logged-in-text' => __('You are already logged in.', 'f-shop'),
			'echo'                => false,
			'inline_attributes'   => 'x-data="{submit:() => console.log(\'submit\')}" x-on:submit.prevent="console.log(\'submit\')"'

		));

		$template = '';
		if (is_user_logged_in()) {

			$template .= '<p class="text-center">' . $args['data-logged-in-text'] . '</p>';
			$template .= '<p class="text-center"><a href="' . esc_url(get_the_permalink(fs_option('page_cabinet'))) . '">В личный кабинет</a></p>';
		} else {
			ob_start();
		?>
			<form method="post" class="fs-login-form" action=""
				x-init="
			$data.loading = false;$data.errors = {};
			$data.success = false;
			$el.onsubmit = async function($event) { 
				$event.preventDefault();
				$data.loading = true;
				try {
					const response = await Alpine.store('FS').login($event);
					$data.loading = false;
					if (response.success) {
						$data.success = true;
						if (typeof response.data.redirect !== 'undefined') {
							window.location.href = response.data.redirect;
						}
						iziToast[response.data.type || 'success']({title: response.data.title || '<?php _e("Success", "f-shop"); ?>',message: response.data.msg || '<?php _e("Successfully logged in", "f-shop"); ?>',position: 'topCenter'});
					} else {
						if (response.data && response.data.errors) {
							$data.errors = response.data.errors;
						}
						iziToast[response.data.type || 'error']({title: response.data.title || '<?php _e("Error", "f-shop"); ?>',message: response.data.msg || '<?php _e("Please check your login credentials", "f-shop"); ?>',position: 'topCenter',timeout: response.data.type==='warning' ? 6000 : 4000,overlay: response.data.type==='warning' ? true : false,maxWidth: response.data.type==='warning' ? 400 : null,icon: ''});}
				} catch(error) {
					$data.loading = false;
					console.error('Error:', error);
					iziToast.error({title: '<?php _e("Error", "f-shop"); ?>',message: error.message,position: 'topCenter'});
				}
			}">


				<div class="alert alert-danger " x-show="errors.any" x-html="errors.any"></div>
			<?php
			$template .= ob_get_clean();
			$template .= fs_frontend_template('auth/login');
			$template .= '</form>';
		}

		if (isset($args['echo']) && $args['echo']) {
			echo $template;

			return null;
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
	public static function register_form($args = array())
	{
		$args = wp_parse_args($args, array(
			'class'               => 'fs-register',
			'name'                => 'fs-register',
			'method'              => 'post',
			'data-logged-in-text' => __('You are already logged in.', 'f-shop'),
			'echo'                => false
		));

		$template = '';
		if (is_user_logged_in()) {
			$template .= '<p class="text-center">' . $args['data-logged-in-text'] . '</p>';
			$template .= '<p class="text-center"><a href="' . esc_url(get_the_permalink(fs_option('page_cabinet'))) . '">' . __('To personal account', 'f-shop') . '</a></p>';
		} else {
			ob_start(); ?>
				<form method="post" class="fs-login-form" action="" x-ref="registerForm" x-data="{ errors: [], msg: '' }"
					x-on:submit.prevent="Alpine.store('FS').register($event).then((r)=>{
                       msg=typeof r.data.msg!=='undefined' ? r.data.msg : '';
                       if(r.success===false) {
                            errors=typeof r.data.errors!=='undefined' ? r.data.errors : [];
                       }else
                        if(r.success===true){
							errors = [];
                            $refs.registerForm.reset();
                            if (typeof r.data.redirect!=='undefined') { window.location.href = r.data.redirect; }
                        }
                    })">
		<?php
			$template .= ob_get_clean();
			$template .= fs_frontend_template('auth/register', array('field' => array()));
			$template .= '</form>';
		}

		if (isset($args['echo']) && $args['echo']) {
			echo $template;

			return null;
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
	public static function lostpassword_form($args = array())
	{
		$args = wp_parse_args($args, array(
			'class'               => 'fs-lostpassword',
			'name'                => 'fs-lostpassword',
			'method'              => 'post',
			'action'              => wp_lostpassword_url(),
			'data-logged-in-text' => __('You are already logged in.', 'f-shop')
		));

		$template = '';
		if (is_user_logged_in()) {
			$template .= '<p class="text-center">' . $args['data-logged-in-text'] . '</p>';
			$template .= '<p class="text-center"><a href="' . esc_url(get_the_permalink(fs_option('page_cabinet'))) . '">' . __('To personal account', 'f-shop') . '</a></p>';
		} else {
			$template = fs_frontend_template('auth/lostpassword', array('field' => array()));
		}

		return $template;
	}

	/**
	 * Выводит html код формы входа в личный кабинет
	 *
	 * @param array $args
	 */
	public static function login_form_show($args = array())
	{
		$args     = wp_parse_args($args, array(
			'class'  => 'fs-login-form',
			'name'   => 'fs-login',
			'method' => 'posts'
		));
		$template = apply_filters('fs_form_header', $args, 'fs_login');
		$template .= fs_frontend_template('auth/login', array('field' => array()));
		$template .= apply_filters('fs_form_bottom', '');

		echo $template;
	}

	public static function user_info()
	{
		$user     = fs_get_current_user();
		$template = fs_frontend_template('cabinet/personal-info', array('user' => $user));

		return $template;
	}

	/**
	 * Выводит информацию о текущем пользователе в виде списка
	 */
	public static function user_info_show()
	{
		echo self::user_info();
	}

	public static function profile_edit($args = array())
	{
		$user           = fs_get_current_user();
		$default        = array(
			'class' => 'fs-profile-edit',
			'echo'  => false
		);
		$args           = wp_parse_args($args, $default);
		$args['name']   = 'fs-profile-edit';
		$args['method'] = 'post';
		$template       = apply_filters('fs_form_header', $args, 'fs_profile_edit');
		$template       .= fs_frontend_template('cabinet/profile-edit', array(
			'user'  => $user,
			'field' => FS_Config::$user_meta
		));
		$template       .= apply_filters('fs_form_bottom', '');
		if (! $args['echo']) {
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
	public static function user_cabinet()
	{

		if (is_user_logged_in()) {
			return self::user_cabinet_tabs();
		} else {
			return FS_Users::login_form();
		}
	}

	/**
	 * Отвечает за создание вкладок личного кабинета и их содержимого
	 */
	static function user_cabinet_tabs()
	{
		$user         = fs_get_current_user();
		$wishlist     = isset($_SESSION['fs_wishlist']) && is_array($_SESSION['fs_wishlist']) ? $_SESSION['fs_wishlist'] : [];
		$wishlist_ids = array_unique(array_values($wishlist));
		$wishlist     = new \WP_Query([
			'post_type'      => FS_Config::get_data('post_type'),
			'post__in'       => count($wishlist_ids) ? $wishlist_ids : [0],
			'posts_per_page' => -1
		]);

		return fs_frontend_template('dashboard/index', array(
			'vars' => compact('user', 'wishlist')
		));
	}

	/**
	 * Возвращает аватарку пользователя
	 *
	 * @param int $user_id
	 * @param string $size
	 *
	 * @return false|string
	 */
	static public function get_user_avatar_url($user_id = 0, $size = 'thumbnail')
	{
		$user_id   = $user_id ? $user_id : get_current_user_id();
		$avatar_id = get_user_meta($user_id, 'fs_user_avatar', 1);
		if ($avatar_id) {
			return wp_get_attachment_image_url($avatar_id, $size);
		}

		return false;
	}

	/**
	 * Выводит виджет с аватаром текущего пользователя
	 */
	public function profile_widget()
	{
		echo fs_frontend_template('widget/profile/widget');
	}
}
