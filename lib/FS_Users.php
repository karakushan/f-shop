<?php

namespace FS;

/**
 * Class FS_Users.
 */
class FS_Users
{
    private $form;

    /**
     * Password Verification Ruleset.
     *
     * @var array
     */
    protected $rules = [
        'lengthStatus' => true, // password length true for applicable
        'minLength' => '6',
        'maxLength' => '12',
        'numberStatus' => false, // password number true for contain at last one number
        'uppercaseStatus' => false, // password uppercase true for contain at last one uppercase
        'lowercaseStatus' => false, // password lowercase true for contain at last one lowercase
        'specialCharacterStatus' => true, // password special character true for contain at last one special character
        'whiteSpaceStatus' => false, // password allow space
    ];

    public function __construct()
    {
        $this->form = new FS_Form();

        // User Authorization
        add_action('wp_ajax_fs_login', [$this, 'login_user']);
        add_action('wp_ajax_nopriv_fs_login', [$this, 'login_user']);

        //  Create user profile
        add_action('wp_ajax_fs_profile_create', [$this, 'create_profile_callback']);
        add_action('wp_ajax_nopriv_fs_profile_create', [$this, 'create_profile_callback']);

        //  Editing user profile
        add_action('wp_ajax_fs_profile_edit', [$this, 'fs_profile_edit']);
        add_action('wp_ajax_nopriv_fs_profile_edit', [$this, 'fs_profile_edit']);

        // Saves profile settings
        add_action('wp_ajax_fs_save_user_data', [$this, 'save_user_data']);
        add_action('wp_ajax_nopriv_fs_save_user_data', [$this, 'save_user_data']);

        // Password reset
        add_action('wp_ajax_fs_lostpassword', [$this, 'lost_password_ajax']);
        add_action('wp_ajax_nopriv_fs_lostpassword', [$this, 'lost_password_ajax']);

        // Login and password change
        add_action('wp_ajax_fs_change_login', [$this, 'change_login']);
        add_action('wp_ajax_nopriv_fs_change_login', [$this, 'change_login']);

        // Change password
        add_action('wp_ajax_fs_change_password', [$this, 'change_password']);
        add_action('wp_ajax_nopriv_fs_change_password', [$this, 'change_password']);

        // Protection of personal account from unauthorized users
        add_action('template_redirect', [$this, 'cabinet_protect']);

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
     * Displays fields in user profile editing.
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
							for="<?php echo esc_attr(str_replace('_', '-', $name)); ?>"><?php echo esc_html($user_field['name']); ?></label>
					</th>
					<td>
						<?php
			                $args = wp_parse_args($user_field, [
			                    'value' => get_user_meta($user->ID, $name, 1),
			                    'id' => str_replace('_', '-', $name),
			                    'class' => 'regular-text',
			                ]);
			    unset($args['name']);
			    $this->form->render_field($name, $user_field['type'], $args);
			    if (!empty($user_field['description'])) { ?>
							<p class="description">
								<?php echo $user_field['description']; ?>
							</p>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php
    }

    /**
     * The save action.
     *
     * @param $user_id int the ID of the current user
     *
     * @return bool meta ID if the key didn't exist, true on successful update, false on failure
     */
    public function admin_profile_save_fields($user_id)
    {
        // check that the current user have the capability to edit the $user_id
        if (!current_user_can('edit_user', $user_id)) {
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
     * Password complexity check.
     */
    public function password_validation($str)
    {
        $result['status'] = false;

        if (empty($str)) {
            $result['msg'] = __('Password can not be empty', 'f-shop');
            $result['status'] = false;
        } elseif (($this->rules['lengthStatus'] == true) & ($this->lengthValidation($str, $this->rules['minLength'], $this->rules['maxLength']) == false)) {
            $result['msg'] = sprintf(__('Your password must be %s to %s characters', 'f-shop'), $this->rules['minLength'], $this->rules['maxLength']);
            $result['status'] = false;
        } elseif (($this->rules['numberStatus'] == true) & ($this->isContainNumber($str) == false)) {
            $result['msg'] = __('Your password must contain at least one number.', 'f-shop');
            $result['status'] = false;
        } elseif (($this->rules['uppercaseStatus'] == true) & ($this->isContainUppercase($str) == false)) {
            $result['msg'] = __('Your password must contain at least one uppercase letter.', 'f-shop');
            $result['status'] = false;
        } elseif (($this->rules['lowercaseStatus'] == true) & ($this->isContainLowercase($str) == false)) {
            $result['msg'] = __('Your password must contain at least one lowercase letter.', 'f-shop');
            $result['status'] = false;
        } elseif (($this->rules['specialCharacterStatus'] == true) & ($this->isContainSpecialCharacter($str) == false)) {
            $result['msg'] = __('Your password must contain at least one special character.', 'f-shop');
            $result['status'] = false;
        } elseif (($this->rules['whiteSpaceStatus'] == true) & ($this->isWhiteSpaceContain($str) == false)) {
            $result['msg'] = __('Space is not allow in password', 'f-shop');
            $result['status'] = false;
        } else {
            $result['status'] = true;
            $result['msg'] = __('Password is valid', 'f-shop');
        }

        return $result;
    }

    /**
     * Check is string contain space or not.
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
     * Check string is contain minimum single uppercase.
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
     * Check is string contain minimum one lowercase.
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
     * Check is string contain minimum one number.
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
     * Check is string contain minimum single special character.
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
     * Checking is string length in required length.
     *
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

    public function password_strength_check($password, $min_len = 8, $max_len = 70, $req_digit = 1, $req_lower = 1, $req_upper = 1, $req_symbol = 1)
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
        $regex .= '.{'.$min_len.','.$max_len.'}$/';

        if (preg_match($regex, $password)) {
            return true;
        } else {
            return false;
        }
    }

    public function change_login()
    {
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Failed verification of nonce form', 'f-shop')]);
        }

        $password = sanitize_text_field($_POST['fs_password']);
        $login = sanitize_text_field($_POST['fs_login']);

        $current_user = wp_get_current_user();

        if ($login && $password) {
            $user_id = wp_update_user([
                'ID' => $current_user->ID,
                'user_pass' => $password,
                'user_login' => $login,
            ]);
            if (!is_wp_error($user_id)) {
                wp_send_json_success(['msg' => __('Your data has been successfully changed', 'f-shop')]);
            } else {
                wp_send_json_success(['msg' => $user_id->get_error_message()]);
            }
        }

        wp_send_json_error(['msg' => __('Your data has not been changed, or you did not specify it.', 'f-shop')]);
    }

    /**
     * Change the user's password after validation and update the user data.
     */
    public function change_password()
    {
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Failed verification of nonce form', 'f-shop')]);
        }

        $current_user = wp_get_current_user();

        // Check old password
        if (empty($_POST['fs_old_password']) || !wp_check_password($_POST['fs_old_password'], $current_user->user_pass)) {
            wp_send_json_error(['msg' => __('Incorrect old password', 'f-shop')]);
        }

        // Password and repeat password
        if ($_POST['fs_password'] != $_POST['fs_password_repeat']) {
            wp_send_json_error(['msg' => __('Passwords do not match', 'f-shop')]);
        }

        // Validation
        $password = sanitize_text_field($_POST['fs_password']);
        $password_validation = $this->password_validation($password);

        if ($password_validation['status'] == false) {
            wp_send_json_error(['msg' => $password_validation['msg']]);
        }

        wp_set_password($password, $current_user->ID);

        $current_user = wp_get_current_user();
        if (wp_check_password($password, $current_user->user_pass, $current_user->ID)) {
            wp_send_json_success(['msg' => __('Your password has been successfully changed', 'f-shop')]);
        }

        wp_send_json_success(['msg' => __('Your data has been successfully changed', 'f-shop')]);
    }

    /**
     * Returns all user fields.
     *
     * Data from these fields can be used:
     *  1. In the order form
     *  2. In the registration form
     *  3. In the login form
     *
     *  If you add 'save_meta' => false then the field will not be saved in the user's meta field
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

        $fields = [
            'fs_email' => [
                'name' => __('E-mail', 'f-shop'),
                'type' => 'email',
                'label' => '',
                'value' => fs_option('fs_autofill_form') && !empty($user->user_email) ? $user->user_email : '',
                'placeholder' => __('Your email', 'f-shop'),
                'title' => __('Keep the correct email', 'f-shop'),
                'description' => 'Primary email address used for account notifications and communications',
                'checkout' => true,
                'save_meta' => false,
                'required' => true,
            ],
            'fs_first_name' => [
                'name' => __('First name', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'value' => fs_option('fs_autofill_form') && !empty($user->first_name) ? $user->first_name : '',
                'placeholder' => __('First name', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'Customer\'s first name used for personalization and shipping',
                'checkout' => true,
                'save_meta' => false,
                'required' => true,
            ],
            'fs_last_name' => [
                'name' => __('Last name', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'value' => fs_option('fs_autofill_form') && !empty($user->last_name) ? $user->last_name : '',
                'placeholder' => __('Last name', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'Customer\'s last name used for shipping and account identification',
                'checkout' => true,
                'save_meta' => false,
                'required' => false,
            ],
            'fs_middle_name' => [
                'name' => __('Middle name', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'value' => fs_option('fs_autofill_form') && !empty($user->middle_name) ? $user->middle_name : '',
                'placeholder' => __('Middle name', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'Customer\'s middle name for complete identification (optional)',
                'checkout' => true,
                'save_meta' => true,
                'required' => false,
            ],

            'fs_other_shipping_address' => [
                'name' => __('Other shipping address', 'f-shop'),
                'type' => 'checkbox',
                'label' => '',
                'value' => '',
                'placeholder' => __('Other shipping address', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'Option to specify an alternative shipping address different from billing address',
                'required' => false,
                'checkout' => true,
                'alpine' => 'fs_other_shipping_address:false',
                'save_meta' => false,
            ],
            'fs_shipping_name' => [
                'name' => __('Delivery service name', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'value' => '',
                'placeholder' => __('Delivery service name', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'Name of the preferred delivery service provider',
                'checkout' => true,
                'save_meta' => false,
            ],

            'fs_shipping_first_name' => [
                'name' => __('First name', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'value' => '',
                'placeholder' => __('First name', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'First name for alternative shipping address',
                'attributes' => [
                    'x-bind:required' => 'fs_other_shipping_address',
                ],
                'checkout' => true,
                'save_meta' => false,
            ],
            'fs_shipping_last_name' => [
                'name' => __('Last name', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'value' => '',
                'placeholder' => __('Last name', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'Last name for alternative shipping address',
                'required' => false,
                'checkout' => true,
                'save_meta' => false,
            ],
            'fs_shipping_email' => [
                'name' => __('E-mail', 'f-shop'),
                'type' => 'email',
                'label' => '',
                'value' => '',
                'placeholder' => __('Your email', 'f-shop'),
                'title' => __('Keep the correct email', 'f-shop'),
                'description' => 'Email address for shipping notifications and tracking updates',
                'required' => false,
                'checkout' => true,
                'save_meta' => false,
            ],
            'fs_shipping_phone' => [
                'name' => __('Phone', 'f-shop'),
                'type' => 'tel',
                'label' => '',
                'value' => '',
                'placeholder' => __('Phone', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'Contact phone number for shipping-related communications',
                'attributes' => [
                    'x-bind:required' => 'fs_other_shipping_address',
                ],
                'checkout' => true,
                'save_meta' => false,
            ],
            'fs_shipping_address' => [
                'name' => __('Address', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'value' => '',
                'placeholder' => __('Address', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'Alternative shipping address street name and number',
                'required' => false,
                'checkout' => true,
                'save_meta' => false,
            ],
            'fs_shipping_city' => [
                'name' => __('City', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'value' => '',
                'placeholder' => __('City', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'City name for alternative shipping address',
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_shipping_state' => [
                'name' => __('State', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'value' => '',
                'placeholder' => __('State', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'State/province for alternative shipping address',
                'required' => false,
                'checkout' => true,
                'save_meta' => false,
            ],
            'fs_shipping_zip' => [
                'name' => __('Zip', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'value' => '',
                'placeholder' => __('Zip', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'ZIP/Postal code for alternative shipping address',
                'required' => false,
                'checkout' => true,
                'save_meta' => false,
            ],

            'fs_user_avatar' => [
                'name' => __('Avatar', 'f-shop'),
                'type' => 'image',
                'label' => '',
                'placeholder' => __('Avatar', 'f-shop'),
                'title' => '',
                'description' => 'Profile picture or avatar image for user account',
                'required' => false,
                'save_meta' => true,
            ],

            'fs_phone' => [
                'name' => __('Phone number', 'f-shop'),
                'type' => 'tel',
                'label' => '',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_phone', 1) : '',
                'placeholder' => __('Phone number', 'f-shop'),
                'title' => __('Keep the correct phone number', 'f-shop'),
                'description' => 'Primary contact phone number for order and account notifications',
                'required' => true,
                'checkout' => true,
                'mask' => fs_option('fs_phone_mask', '+380 (99) 999-99-99'),
            ],
            'fs_gender' => [
                'name' => __('Gender', 'f-shop'),
                'type' => 'select',
                'label' => '',
                'values' => [
                    'Male' => __('Male', 'f-shop'),
                    'Female' => __('Female', 'f-shop'),
                ],
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_gender', 1) : '',
                'placeholder' => __('Gender', 'f-shop'),
                'title' => '',
                'description' => 'Customer\'s gender for personalization purposes',
                'required' => false,
            ],
            'fs_city' => [
                'name' => __('City', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'placeholder' => __('City', 'f-shop'),
                'title' => __('This field is required.', 'f-shop'),
                'description' => 'Primary city of residence for billing and default shipping',
                'required' => false,
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_city', 1) : '',
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_country' => [
                'name' => __('Country', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'placeholder' => __('Country', 'f-shop'),
                'title' => '',
                'description' => 'Country of residence for billing and default shipping',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_country', 1) : '',
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_zip_code' => [
                'name' => __('Zip Code', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'placeholder' => __('Zip Code', 'f-shop'),
                'description' => 'ZIP/Postal code for billing and default shipping address',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_zip_code', 1) : '',
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_region' => [
                'name' => __('State / province', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'title' => __('This field is required.', 'f-shop'),
                'placeholder' => __('State / province', 'f-shop'),
                'description' => 'State or province for billing and default shipping address',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_region', 1) : '',
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_address' => [
                'name' => __('Address', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'placeholder' => __('Address', 'f-shop'),
                'description' => 'Street address for billing and default shipping',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_address', 1) : '',
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_location_lat' => [
                'name' => __('Location latitude', 'f-shop'),
                'type' => 'hidden',
                'label' => '',
                'placeholder' => __('Location latitude', 'f-shop'),
                'description' => 'Geographic latitude coordinate for precise location mapping and delivery calculations',
                'save_meta' => true,
                'checkout' => true,
            ],
            'fs_location_lng' => [
                'name' => __('Location longitude', 'f-shop'),
                'type' => 'hidden',
                'label' => '',
                'placeholder' => __('Location longitude', 'f-shop'),
                'description' => 'Geographic longitude coordinate for precise location mapping and delivery calculations',
                'save_meta' => true,
                'checkout' => true,
            ],
            'fs_street' => [
                'name' => __('Street', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'placeholder' => __('Street', 'f-shop'),
                'description' => 'Street name for detailed address information',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_address', 1) : '',
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_home_num' => [
                'name' => __('House number', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'placeholder' => __('House number', 'f-shop'),
                'description' => 'Building or house number for precise delivery location',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_home_num', 1) : '',
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_apartment_num' => [
                'name' => __('Apartment number', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'placeholder' => __('Apartment number', 'f-shop'),
                'description' => 'Apartment or unit number for precise delivery location',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_apartment_num', 1) : '',
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_entrance_num' => [
                'name' => __('Entrance number', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'placeholder' => __('Entrance number', 'f-shop'),
                'description' => 'Building entrance number for complex delivery locations',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_apartment_num', 1) : '',
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_delivery_number' => [
                'name' => __('Branch number', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'placeholder' => __('Branch number', 'f-shop'),
                'description' => 'Delivery service branch number for pickup points',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_delivery_number', 1) : '',
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_delivery_methods' => [
                'name' => __('Delivery methods', 'f-shop'),
                'type' => 'dropdown_categories',
                'first_option' => __('Choose delivery method', 'f-shop'),
                'taxonomy' => FS_Config::get_data('product_del_taxonomy'),
                'icon' => true,
                'title' => __('Choose shipping method', 'f-shop'),
                'description' => 'Preferred shipping method for order delivery',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_delivery_methods', 1) : '',
                'values' => get_terms([
                    'taxonomy' => FS_Config::get_data('product_del_taxonomy'),
                    'fields' => 'id=>name',
                    'hide_empty' => 0,
                    'parent' => 0,
                ]),
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_payment_methods' => [
                'name' => __('Payment methods', 'f-shop'),
                'type' => 'dropdown_categories',
                'first_option' => __('Choose a payment method', 'f-shop'),
                'taxonomy' => FS_Config::get_data('product_pay_taxonomy'),
                'icon' => true,
                'title' => __('Select a Payment Method', 'f-shop'),
                'description' => 'Preferred payment method for order transactions',
                'value' => fs_option('fs_autofill_form') && $user_id ? get_user_meta($user_id, 'fs_payment_methods', 1) : '',
                'values' => get_terms([
                    'taxonomy' => FS_Config::get_data('product_pay_taxonomy'),
                    'fields' => 'id=>name',
                    'hide_empty' => 0,
                    'parent' => 0,
                    'meta_query' => [
                        'relation' => 'OR',
                        [
                            'key' => '_fs_pay_inactive',
                            'value' => 1,
                            'compare' => '!=',
                            'type' => 'NUMERIC',
                        ],
                        [
                            'key' => '_fs_pay_inactive',
                            'compare' => 'NOT EXISTS',
                        ],
                    ],
                ]),
                'required' => false,
                'checkout' => true,
                'save_meta' => true,
            ],
            'fs_comment' => [
                'name' => __('Comment on the order', 'f-shop'),
                'type' => 'textarea',
                'label' => '',
                'placeholder' => __('Comment on the order', 'f-shop'),
                'description' => 'Additional notes or special instructions for the order',
                'required' => false,
                'checkout' => true,
                'save_meta' => false,
            ],
            'fs_customer_register' => [
                'name' => __('Register on the site', 'f-shop'),
                'type' => 'checkbox',
                'label' => __('Register on the site', 'f-shop'),
                'label_position' => 'after',
                'description' => 'Option to create an account during checkout process',
                'value' => 0,
                'required' => false,
                'checkout' => true,
                'save_meta' => false,
            ],
            'fs_subscribe_news' => [
                'name' => __('Subscribe', 'f-shop'),
                'type' => 'checkbox',
                'label' => __('Receive site news', 'f-shop'),
                'label_position' => 'after',
                'description' => 'Opt-in for receiving newsletters and site updates',
                'required' => false,
                'checkout' => true,
                'value' => fs_option('fs_autofill_form') && get_user_meta($user->ID, 'fs_subscribe_news', 1) ? get_user_meta($user->ID, 'fs_subscribe_news', 1) : 0,
            ],
            'fs_subscribe_cart' => [
                'name' => __('Receive a message about goods left in the basket', 'f-shop'),
                'type' => 'checkbox',
                'label' => __('Receive a message about goods left in the basket', 'f-shop'),
                'label_position' => 'after',
                'description' => 'Opt-in for abandoned cart reminder notifications',
                'required' => false,
                'checkout' => true,
                'value' => fs_option('fs_autofill_form') && get_user_meta($user->ID, 'fs_subscribe_cart', 1) ? get_user_meta($user->ID, 'fs_subscribe_cart', 1) : '',
            ],
            'fs_login' => [
                'name' => __('Login', 'f-shop'),
                'type' => 'text',
                'label' => '',
                'placeholder' => __('Login', 'f-shop'),
                'description' => 'Username for account login',
                'value' => fs_option('fs_autofill_form') ? $user->user_login : '',
                'required' => true,
                'save_meta' => false,
                'attributes' => [
                    'autocomplete' => 'off',
                ],
            ],
            'fs_password' => [
                'name' => __('Password', 'f-shop'),
                'placeholder' => __('Password', 'f-shop'),
                'type' => 'password',
                'label' => '',
                'description' => 'Account password for secure access',
                'value' => '',
                'required' => true,
                'save_meta' => false,
                'attributes' => [
                    'autocomplete' => 'off',
                ],
            ],
            'fs_repeat_password' => [
                'name' => __('Confirm password', 'f-shop'),
                'placeholder' => __('Confirm password', 'f-shop'),
                'type' => 'password',
                'label' => '',
                'description' => 'Repeat password to ensure correct entry',
                'value' => '',
                'required' => true,
                'save_meta' => false,
            ],
        ];

        return apply_filters('fs_user_fields', $fields);
    }

    /**
     * Возвращает поле пользователя.
     *
     * @param int    $user_id
     * @param string $default
     *
     * @return mixed|string
     */
    public static function get_user_field($key, $user_id = 0, $default = '')
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        return get_user_meta($user_id, $key, 1) != ''
            ? get_user_meta($user_id, $key, 1)
            : $default;
    }

    /**
     * Password reset.
     */
    public static function lost_password_ajax()
    {
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Failed verification of nonce form', 'f-shop')]);
        }

        $user_email = sanitize_email($_POST['fs_email']);

        if (!email_exists($user_email)) {
            wp_send_json_error(['msg' => __('This user does not exist on the site', 'f-shop')]);
        }

        if (is_user_logged_in()) {
            wp_send_json_error(['msg' => __('You are already logged in', 'f-shop')]);
        }

        $user = get_user_by('email', $user_email);

        $new_password = wp_generate_password();

        wp_set_password($new_password, $user->ID);

        $replace_keys = [
            'site_url' => get_bloginfo('url'),
            'site_name' => get_bloginfo('name'),
            'admin_email' => get_bloginfo('admin_email'),
            'password' => $new_password,
            'first_name' => $user->first_name,
        ];

        $notification = new FS_Notification();
        $notification->set_recipients([$user_email]);
        $notification->set_subject(__('Password reset on the site', 'f-shop'));
        $notification->set_message(__('A password reset request was received on the site "%site_name%". Your new password is: %password%. If this was not you, please ignore this email.', 'f-shop'), $replace_keys);
        $notification->send($user_email, 'user-lost-password', $replace_keys);

        wp_send_json_success(['msg' => __('Your password has been successfully reset. Password sent to your e-mail.', 'f-shop')]);
    }

    /**
     * Validates user data based on field rules and requirements.
     *
     * @param array $data          Data to validate
     * @param array $validate_only Optional array of field keys to validate
     *
     * @return array Array containing validation errors and validated data
     */
    public function validate_user_data($data, $validate_only = [])
    {
        $validated_data = [];
        $errors = [];
        $user_fields = FS_Users::get_user_fields();

        // Проверяем только те поля, которые пришли в POST запросе
        foreach ($data as $meta_key => $meta_value) {
            // Пропускаем поля указанные в переменной $validate_only
            if (!empty($validate_only) && !in_array($meta_key, $validate_only)) {
                continue;
            }

            $user_field = $user_fields[$meta_key];
            $field_errors = [];

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

        return [
            'errors' => $errors,
            'data' => $validated_data,
        ];
    }

    /**
     * Handles file uploads for user fields.
     *
     * @param array $allowed_field_keys Array of allowed field keys
     * @param array $user_fields        Array of user field definitions
     * @param int   $user_id            User ID
     *
     * @return array Array with status and any errors
     */
    private function handle_file_uploads($allowed_field_keys, $user_fields, $user_id)
    {
        // Подключаем необходимые файлы для работы с медиа
        require_once ABSPATH.'wp-admin/includes/image.php';
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/media.php';

        $file_errors = [];

        if (empty($_FILES)) {
            return ['success' => true, 'errors' => []];
        }

        foreach ($_FILES as $field_key => $file) {
            // Проверяем, является ли поле разрешенным файловым полем
            if (
                !in_array($field_key, $allowed_field_keys)
                || !isset($user_fields[$field_key])
                || !in_array($user_fields[$field_key]['type'], ['file', 'image'])
            ) {
                continue;
            }

            // Проверяем наличие ошибок при загрузке
            if ($file['error'] !== UPLOAD_ERR_OK) {
                if ($file['error'] !== UPLOAD_ERR_NO_FILE) { // Игнорируем ошибку отсутствия файла
                    $file_errors[$field_key] = $this->get_file_error_message($file['error']);
                }
                continue;
            }

            // Проверяем тип файла
            $allowed_types = apply_filters('fs_allowed_file_types', [
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/pdf',
            ], $field_key);

            if (!in_array($file['type'], $allowed_types)) {
                $file_errors[$field_key] = __('Invalid file type. Allowed types: JPG, PNG, GIF, PDF', 'f-shop');
                continue;
            }

            // Проверяем размер файла (по умолчанию максимум 5MB)
            $max_size = apply_filters('fs_max_file_size', 5 * 1024 * 1024, $field_key);
            if ($file['size'] > $max_size) {
                $file_errors[$field_key] = sprintf(
                    __('File is too large. Maximum size is %s MB', 'f-shop'),
                    $max_size / (1024 * 1024)
                );
                continue;
            }

            // Загружаем файл в медиабиблиотеку WordPress
            $attachment_id = media_handle_upload($field_key, 0);

            if (is_wp_error($attachment_id)) {
                $file_errors[$field_key] = $attachment_id->get_error_message();
                continue;
            }

            // Удаляем старый файл, если он существует
            $old_attachment_id = get_user_meta($user_id, $field_key, true);
            if ($old_attachment_id) {
                wp_delete_attachment($old_attachment_id, true);
            }

            // Сохраняем ID нового файла в мета-данных пользователя
            update_user_meta($user_id, $field_key, $attachment_id);
        }

        return [
            'success' => empty($file_errors),
            'errors' => $file_errors,
        ];
    }

    /**
     * Saves user data submitted through the form.
     */
    public function save_user_data()
    {
        // Проверка nonce для безопасности
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error([
                'msg' => __('Failed verification of nonce form', 'f-shop'),
            ]);
        }

        // Получаем поля пользователя и ID текущего пользователя
        $user_fields = self::get_user_fields();
        $user_id = get_current_user_id();

        // Проверяем наличие полей и пользователя
        if (!is_array($user_fields) || empty($user_fields)) {
            wp_send_json_error([
                'msg' => __('No fields found to save user data', 'f-shop'),
            ]);
        }

        if (!$user_id) {
            wp_send_json_error([
                'msg' => __('User is not found', 'f-shop'),
            ]);
        }

        // Фильтруем все поля, оставляя только те, которые зарегистрированы в user_fields
        $allowed_field_keys = array_keys($user_fields);

        // Фильтруем POST данные, оставляя только разрешенные поля
        $post_data = array_intersect_key($_POST, array_flip($allowed_field_keys));

        // Обработка загрузки файлов
        $upload_result = $this->handle_file_uploads($allowed_field_keys, $user_fields, $user_id);
        if (!$upload_result['success']) {
            wp_send_json_error([
                'msg' => __('File upload failed', 'f-shop'),
                'errors' => $upload_result['errors'],
            ]);
        }

        // Обработка частичной валидации полей
        if (!empty($_POST['fs_validate_only'])) {
            $validated_keys = explode(',', $_POST['fs_validate_only']);
            $post_data = array_intersect_key($post_data, array_flip($validated_keys));
        }

        // Валидация данных
        $validation_result = $this->validate_user_data($post_data);

        if (!empty($validation_result['errors'])) {
            wp_send_json_error([
                'msg' => __('Validation failed', 'f-shop'),
                'errors' => $validation_result['errors'],
            ]);
        }

        // Сохранение валидированных данных
        foreach ($validation_result['data'] as $meta_key => $meta_value) {
            // Пропускаем файловые поля, так как они уже обработаны
            if ($this->is_file_field($user_fields, $meta_key)) {
                continue;
            }

            // Обновление имени и фамилии
            if ($this->is_name_field($meta_key)) {
                $this->update_user_name($user_id, $meta_key, $meta_value);
            }

            // Сохранение мета-данных
            $meta_value = apply_filters('fs_user_meta_before_save', $meta_value, $meta_key, $user_id);
            update_user_meta($user_id, $meta_key, $meta_value);
        }

        wp_send_json_success([
            'msg' => __('Your data has been successfully updated.', 'f-shop'),
        ]);
    }

    /**
     * Возвращает сообщение об ошибке загрузки файла.
     *
     * @param int $error_code Код ошибки загрузки файла
     *
     * @return string Сообщение об ошибке
     */
    private function get_file_error_message($error_code)
    {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return __('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'f-shop');
            case UPLOAD_ERR_FORM_SIZE:
                return __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'f-shop');
            case UPLOAD_ERR_PARTIAL:
                return __('The uploaded file was only partially uploaded', 'f-shop');
            case UPLOAD_ERR_NO_TMP_DIR:
                return __('Missing a temporary folder', 'f-shop');
            case UPLOAD_ERR_CANT_WRITE:
                return __('Failed to write file to disk', 'f-shop');
            case UPLOAD_ERR_EXTENSION:
                return __('A PHP extension stopped the file upload', 'f-shop');
            default:
                return __('Unknown upload error', 'f-shop');
        }
    }

    /**
     * Checks if field is a file field.
     *
     * @param array  $user_fields All user fields
     * @param string $meta_key    Field key
     *
     * @return bool
     */
    private function is_file_field($user_fields, $meta_key)
    {
        return isset($user_fields[$meta_key])
            && $user_fields[$meta_key]['type'] == 'file'
            && !empty($_FILES[$meta_key]);
    }

    /**
     * Checks if field is a name or surname field.
     *
     * @param string $meta_key Field key
     *
     * @return bool
     */
    private function is_name_field($meta_key)
    {
        return in_array($meta_key, ['fs_first_name', 'fs_last_name']);
    }

    /**
     * Updates user's first or last name.
     *
     * @param int    $user_id    User ID
     * @param string $meta_key   Field key
     * @param string $meta_value Field value
     */
    private function update_user_name($user_id, $meta_key, $meta_value)
    {
        wp_update_user([
            'ID' => $user_id,
            str_replace('fs_', '', $meta_key) => $meta_value,
        ]);
    }

    /**
     * Protecting your personal account from unauthorized users.
     */
    public function cabinet_protect()
    {
        $redirect_page = fs_option('page_cabinet');
        $login_page = fs_option('page_auth');
        if (empty($redirect_page)) {
            return;
        } elseif (is_page($redirect_page) && !is_user_logged_in()) {
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
     * The 'username' field can contain either the login or email.
     * The 'password' field contains the password.
     *
     * @return void JSON response with authentication result
     */
    public function login_user()
    {
        // Get the cabinet page and its URL first
        $redirect_page = fs_option('page_cabinet');
        $redirect = !empty($redirect_page) ? get_permalink($redirect_page) : false;

        // If the user is already logged in, send an error message
        if (is_user_logged_in()) {
            $logout_url = wp_logout_url($_SERVER['REQUEST_URI']);
            $msg = sprintf(
                /* translators: 1: Logout link 2: Cabinet link */
                __('You are already logged in. <a href="%1$s">Logout</a>. <a href="%2$s">Go to cabinet</a>', 'f-shop'),
                esc_url($logout_url),
                esc_url($redirect)
            );
            wp_send_json_error(['msg' => $msg]);
        }

        // Check if the nonce is valid
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Invalid verification code. Please contact the site administrator!', 'f-shop')]);
        }

        // Validate the data
        $validation_result = $this->validate_user_data($_POST, ['fs_login', 'fs_password']);

        // If there are errors, return them
        if (!empty($validation_result['errors'])) {
            wp_send_json_error([
                'msg' => __('Validation failed', 'f-shop'),
                'errors' => $validation_result['errors'],
            ]);
        }

        // Get the user based on the username
        if (is_email($validation_result['data']['fs_login'])) {
            $user = get_user_by('email', $validation_result['data']['fs_login']);
        } else {
            $user = get_user_by('login', $validation_result['data']['fs_login']);
        }

        // If the user does not exist, send an error message
        if (!$user) {
            wp_send_json_error([
                'errors' => [
                    'fs_email' => __('Unfortunately, a user with such data does not exist on the site', 'f-shop'),
                    'fs_login' => __('Unfortunately, a user with such data does not exist on the site', 'f-shop'),
                ],
            ]);
        } else {
            // Authenticate the user
            $auth = wp_authenticate($user->user_login, $validation_result['data']['fs_password']);

            // Check for authentication errors
            if (is_wp_error($auth)) {
                // If there is an error, send the error message
                $reset_password_page_url = fs_option('page_lostpassword')
                    ? get_permalink(fs_option('page_lostpassword'))
                    : wp_lostpassword_url(home_url());

                wp_send_json_error([
                    'errors' => [
                        'password' => sprintf(
                            /* translators: %s: Password reset URL */
                            __('The login information you entered is incorrect. <a href="%s">Reset password</a>', 'f-shop'),
                            esc_url($reset_password_page_url)
                        ),
                    ],
                ]);
            } else {
                // If the authentication is successful, clear the authentication cookie
                nocache_headers();
                wp_clear_auth_cookie();

                // Set the authentication cookie for the authenticated user
                wp_set_auth_cookie($auth->ID);

                // Send a success message with the redirect URL
                wp_send_json_success([
                    'msg' => sprintf(
                        /* translators: %s: User display name */
                        __('Welcome back, %s! You have successfully logged in.', 'f-shop'),
                        esc_html($auth->display_name)
                    ),
                    'redirect' => $redirect,
                ]);
            }
        }
    }

    /**
     * Creates user profile during registration.
     *
     * @return void JSON response with registration result
     */
    public function create_profile_callback()
    {
        if (!FS_Config::verify_nonce()) {
            wp_send_json_error(['msg' => __('Failed verification of nonce form', 'f-shop')]);
        }

        // POST data cleaning
        $allowed_fields = self::get_user_fields();
        $save_fields = [];
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
        if (!is_email($save_fields['fs_email'])) {
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

        if (!isset($_POST['fs_rules'])) {
            $validation_errors['fs_rules'] = __('You must accept the terms and conditions', 'f-shop');
        }

        // If there are validation errors, send an error message
        if (!empty($validation_errors)) {
            wp_send_json_error(['errors' => $validation_errors]);
        }

        // Adding a user to the database
        $user_id = wp_insert_user([
            'user_pass' => $save_fields['fs_password'],
            'user_email' => $save_fields['fs_email'],
            'user_login' => $save_fields['fs_email'],
            'display_name' => $save_fields['fs_first_name'],
            'role' => 'client',
            'show_admin_bar_front' => false,
        ]);

        // If an error occurred while adding a user
        if (is_wp_error($user_id)) {
            wp_send_json_error(['msg' => $user_id->get_error_message()]);
        }

        // Keys for replacement in the letter
        $replace_keys = [
            'site_name' => get_bloginfo('name'),
            'first_name' => $save_fields['fs_first_name'],
            'full_name' => $save_fields['fs_first_name'],
            'password' => $save_fields['fs_password'],
            'email' => $save_fields['fs_email'],
            'admin_email' => get_bloginfo('admin_email'),
            'site_url' => get_bloginfo('url'),
            'login' => $save_fields['fs_email'],
            'cabinet_url' => fs_account_url(),
        ];

        // Send notification to the user
        $notification = new FS_Notification();
        $notification->set_recipients([$save_fields['fs_email']]);
        $notification->set_subject(sprintf(__('Registration on the website «%s»', 'f-shop'), get_bloginfo('name')));
        $notification->set_template('mail/'.get_locale().'/user-registration', $replace_keys);
        $notification->send();

        // Send a letter to the admin
        $notification->set_recipients([get_bloginfo('admin_email')]);
        $notification->set_template('mail/'.get_locale().'/user-registration-admin', $replace_keys);
        $notification->send();

        // Отправляем сообщение успешной регистрации на экран
        wp_send_json_success([
            'msg' => sprintf(__('Congratulations! You have successfully registered! <a href="%s">Log in</a>', 'f-shop'), esc_url(get_permalink(fs_option('page_auth')))),
        ]);
    }

    /**
     * Editing user profile.
     */
    public function fs_profile_edit()
    {
        if (!FS_Config::verify_nonce() || empty($_POST['fs']) || !is_user_logged_in()) {
            wp_send_json_error([
                'status' => 0,
                'message' => __('The form did not pass the security check!', 'f-shop'),
            ]);
        }

        $user = wp_get_current_user();

        foreach (FS_Config::$user_meta as $meta_key => $meta_field) {
            $name = $meta_field['name'];
            $value = sanitize_text_field($_POST['fs'][$name]);

            if (empty($value)) {
                delete_user_meta($user->ID, $meta_key);
                continue;
            }

            switch ($meta_key) {
                case 'display_name':
                    $update_user = wp_update_user([
                        'ID' => $user->ID,
                        'display_name' => $value,
                    ]);
                    if (is_wp_error($update_user)) {
                        $errors = $update_user->get_error_message();
                        echo json_encode([
                            'status' => 0,
                            'message' => $errors,
                        ]);
                        exit;
                    }
                    break;
                case 'user_email':
                    $email = sanitize_email($_POST['fs'][$name]);
                    if (!is_email($email)) {
                        echo json_encode([
                            'status' => 0,
                            'message' => 'E-mail не соответствует формату!',
                        ]);
                        exit;
                    } else {
                        $update_user = wp_update_user([
                            'ID' => $user->ID,
                            'user_email' => $email,
                        ]);
                        if (is_wp_error($update_user)) {
                            $errors = $update_user->get_error_message();
                            echo json_encode([
                                'status' => 0,
                                'message' => $errors,
                            ]);
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

        echo json_encode([
            'status' => 1,
            'message' => __('Your data has been updated successfully!', 'f-shop'),
        ]);
        exit;
    }

    /**
     * Generates and optionally outputs a registration form with specified attributes.
     *
     * @param array $args {
     *                    Optional. Array of form configuration arguments.
     *
     * @var string $class CSS class for form container
     * @var string $name Form name attribute
     * @var string $method HTTP method for submission
     * @var string $data-logged-in-text Text shown when user is logged in
     * @var bool   $echo Whether to echo or return the form
     *             }
     *
     * @return string|null Generated form HTML or null if echoed
     */
    public static function register_form($args = [])
    {
        $args = wp_parse_args($args, [
            'class' => 'fs-register',
            'name' => 'fs-register',
            'method' => 'post',
            'data-logged-in-text' => __('You are already logged in.', 'f-shop'),
            'echo' => false,
        ]);

        $template = '';
        if (is_user_logged_in()) {
            $template .= '<p class="text-center">'.$args['data-logged-in-text'].'</p>';
            $template .= '<p class="text-center"><a href="'.esc_url(get_the_permalink(fs_option('page_cabinet'))).'">'.__('To personal account', 'f-shop').'</a></p>';
        } else {
            ob_start(); ?>
			<form method="post" class="fs-login-form" action="" x-ref="registerForm" x-data="{ errors: [], msg: '', success: false }"
				x-on:submit.prevent="Alpine.store('FS').register($event).then((r)=>{
                       msg=typeof r.data.msg!=='undefined' ? r.data.msg : '';
                       if(r.success===false) {
                            errors=typeof r.data.errors!=='undefined' ? r.data.errors : [];
                            success=false;
                       }else
                        if(r.success===true){
                            success=true;
							errors = [];
                            $refs.registerForm.reset();
                            if (typeof r.data.redirect!=='undefined') { window.location.href = r.data.redirect; }
                        }
                    })">
			<?php
            $template .= ob_get_clean();
            $template .= fs_frontend_template('auth/register', ['field' => []]);
            $template .= '</form>';
        }

        if (isset($args['echo']) && $args['echo']) {
            echo $template;

            return null;
        }

        return $template;
    }

    /**
     * Generates and outputs password reset form.
     *
     * @param array $args {
     *                    Optional. Array of form configuration arguments.
     *
     * @var string $class CSS class for form container
     * @var string $name Form name attribute
     * @var string $method HTTP method for submission
     * @var string $action Form action URL
     * @var string $data-logged-in-text Text shown when user is logged in
     *             }
     *
     * @return string Generated form HTML
     */
    public static function lostpassword_form($args = [])
    {
        $args = wp_parse_args($args, [
            'class' => 'fs-lostpassword',
            'name' => 'fs-lostpassword',
            'method' => 'post',
            'action' => wp_lostpassword_url(),
            'data-logged-in-text' => __('You are already logged in.', 'f-shop'),
        ]);

        $template = '';
        if (is_user_logged_in()) {
            $template .= '<p class="text-center">'.$args['data-logged-in-text'].'</p>';
            $template .= '<p class="text-center"><a href="'.esc_url(get_the_permalink(fs_option('page_cabinet'))).'">'.__('To personal account', 'f-shop').'</a></p>';
        } else {
            $template = fs_frontend_template('auth/lostpassword', ['field' => []]);
        }

        return $template;
    }

    /**
     * Returns current user information template.
     *
     * @return string Generated user info HTML
     */
    public static function user_info()
    {
        $user = fs_get_current_user();
        $template = fs_frontend_template('cabinet/personal-info', ['user' => $user]);

        return $template;
    }

    /**
     * Returns user avatar URL.
     *
     * @param int    $user_id User ID, defaults to current user
     * @param string $size    Image size name
     *
     * @return false|string Avatar URL or false if not found
     */
    public static function get_user_avatar_url($user_id = 0, $size = 'thumbnail')
    {
        $user_id = $user_id ? $user_id : get_current_user_id();
        $avatar_id = get_user_meta($user_id, 'fs_user_avatar', 1);
        if ($avatar_id) {
            return wp_get_attachment_image_url($avatar_id, $size);
        }

        return false;
    }

    /**
     * Displays user info.
     */
    public static function user_info_show()
    {
        echo self::user_info();
    }

    /**
     * Displays user profile edit form.
     *
     * @param array $args {
     *                    Optional. Array of form configuration arguments.
     *
     * @var string $class CSS class for form container
     * @var bool   $echo Whether to echo or return the form
     *             }
     *
     * @return string|bool Generated form HTML or true if echoed
     */
    public static function profile_edit($args = [])
    {
        $user = fs_get_current_user();
        $default = [
            'class' => 'fs-profile-edit',
            'echo' => false,
        ];
        $args = wp_parse_args($args, $default);
        $args['name'] = 'fs-profile-edit';
        $args['method'] = 'post';

        $template = apply_filters('fs_form_header', $args, 'fs_profile_edit');
        $template .= fs_frontend_template('cabinet/profile-edit', [
            'user' => $user,
            'field' => FS_Config::$user_meta,
        ]);
        $template .= apply_filters('fs_form_bottom', '');

        if (!$args['echo']) {
            return $template;
        }

        echo $template;

        return true;
    }

    /**
     * Returns user cabinet content.
     *
     * Shows login form for non-authenticated users
     * or cabinet tabs for authenticated users
     *
     * @return string Generated HTML content
     */
    public static function user_cabinet()
    {
        if (is_user_logged_in()) {
            return self::user_cabinet_tabs();
        }

        return self::login_form(); // Now we can call it directly as static method
    }

    /**
     * Handles user cabinet tabs and content.
     *
     * @return string Generated HTML for cabinet tabs
     */
    public static function user_cabinet_tabs()
    {
        $user = fs_get_current_user();

        $wishlist = FS_Wishlist::get_wishlist_products();

        return fs_frontend_template('dashboard/index', [
            'vars' => compact('user', 'wishlist'),
        ]);
    }

    /**
     * Displays user profile widget.
     */
    public function profile_widget()
    {
        echo fs_frontend_template('widget/profile/widget');
    }

    /**
     * Generates and optionally outputs a login form with specified attributes.
     *
     * @param array $args {
     *                    Optional. Array of form configuration arguments.
     *
     * @var string $class CSS class for form container
     * @var string $name Form name attribute
     * @var string $method HTTP method for submission
     * @var string $data-logged-in-text Text shown when user is logged in
     * @var bool   $echo Whether to echo or return the form
     * @var string $inline_attributes Additional form attributes
     *             }
     *
     * @return string|null Generated form HTML or null if echoed
     */
    public static function login_form($args = [])
    {
        $args = wp_parse_args($args, [
            'class' => 'fs-login-form',
            'name' => 'fs-login',
            'method' => 'post',
            'data-logged-in-text' => __('You are already logged in.', 'f-shop'),
            'echo' => false,
            'inline_attributes' => 'x-data="{submit:() => console.log(\'submit\')}" x-on:submit.prevent="console.log(\'submit\')"',
        ]);

        $template = '';
        if (is_user_logged_in()) {
            $template .= '<p class="text-center">'.$args['data-logged-in-text'].'</p>';
            $template .= '<p class="text-center"><a href="'.esc_url(get_the_permalink(fs_option('page_cabinet'))).'">'.__('To personal account', 'f-shop').'</a></p>';
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
							iziToast[response.data.type || 'success']({title: response.data.title || '<?php _e('Success', 'f-shop'); ?>',message: response.data.msg || '<?php _e('Successfully logged in', 'f-shop'); ?>',position: 'topCenter'});
						} else {
							if (response.data && response.data.errors) {
								$data.errors = response.data.errors;
							}
							iziToast[response.data.type || 'error']({title: response.data.title || '<?php _e('Error', 'f-shop'); ?>',message: response.data.msg || '<?php _e('Please check your login credentials', 'f-shop'); ?>',position: 'topCenter',timeout: response.data.type==='warning' ? 6000 : 4000,overlay: response.data.type==='warning' ? true : false,maxWidth: response.data.type==='warning' ? 400 : null,icon: ''});}
					} catch(error) {
						$data.loading = false;
						console.error('Error:', error);
						iziToast.error({title: '<?php _e('Error', 'f-shop'); ?>',message: error.message,position: 'topCenter'});
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
}
