<?php

class PeepSoRegister
{
	protected static $_instance = NULL;

	private $user_id = NULL;
	private $user = NULL;

	// list of allowed template tags
	public $template_tags = array(
		'register_form',		// return the registration form
		'display_terms_and_conditions',
	);

	private function __construct()
	{
	}

	/*
	 * return singleton instance
	 */
	public static function get_instance()
	{
		if (self::$_instance === NULL)
			self::$_instance = new self();
		return (self::$_instance);
	}

	/* return propeties for the profile page
	 * @param string $prop The name of the property to return
	 * @return mixed The value of the property
	 */
	public function get_prop($prop)
	{
		$ret = '';
		return ($ret);
	}

	//// implementation of template tags

	/*
	 * constructs the profile edit form
	 */
	public function register_form()
	{
		$input = new PeepSoInput();

		$fields = array(

			'username' => array(
				'label' => __('Username', 'peepso-core'),
				'descript' => __('Enter your desired username', 'peepso-core'),
				'value' => $input->value('username', '', FALSE), // SQL Safe
				'required' => 1,
				'row_wrapper_class' => 'ps-form__row--half',
				'validation' => array(
					'username',
					'required',
					'minlen:' . apply_filters('peepso_filter_username_len_min',PeepSoUser::USERNAME_MINLEN),
					'maxlen:' . apply_filters('peepso_filter_username_len_max',PeepSoUser::USERNAME_MAXLEN),
					'custom'
				),
				'validation_options' => [
                    [
                        'error_message' => __('Username must not be the same as your password.', 'peepso-core'),
                        'function' => array(&$this, 'validate_username_not_password'),
                        'name' => 'validate_username_not_password',
                    ],
                    [
                        'error_message' => __('Username cannot be an email.', 'peepso-core'),
                        'function' => array(&$this, 'validate_username_not_email'),
                        'name'=> 'validate_username_not_email',
                    ],
				],

				'type' => 'text',
			),
            'email' => array(
                'label' => __('Email', 'peepso-core'),
                'descript' => __('Enter your email address', 'peepso-core'),
                'value' => $input->value('email', '', FALSE), // SQL Safe
                'required' => 1,
                'row_wrapper_class' => 'ps-form__row--half',
                'type' => 'email',
                'validation' => array(
                    'email',
                    'required',
                    'maxlen:' . PeepSoUser::EMAIL_MAXLEN,
                ),
            ),
            'email_verify' => array(
                'label' => __('Verify Email', 'peepso-core'),
                'descript' => __('Please re-enter your email address', 'peepso-core'),
                'value' => $input->value('email', '', FALSE), // SQL Safe
                'required' => 1,
                'row_wrapper_class' => 'ps-form__row--half',
                'type' => 'email',
                'validation' => array(
                    'email',
                    'required',
                    'maxlen:' . PeepSoUser::EMAIL_MAXLEN,
                ),
                'extra' => ' onpaste="return false"',
            ),
				'password' => array(
						'label' => __('Password', 'peepso-core'),
						'descript' => __('Enter your desired password', 'peepso-core'),
						'value' => '',
						'required' => 1,
						'row_wrapper_class' => 'ps-form__row--half',
						'validation' => array('password', 'required'),
						'type' => 'password',
						'class' => PeepSo::get_option_new('password_preview_enable') ? 'ps-js-password-preview' : null,
				),
				'password2' => array(
						'label' => __('Verify Password', 'peepso-core'),
						'descript' => __('Please re-enter your password', 'peepso-core'),
						'value' => '',
						'required' => 1,
						'row_wrapper_class' => 'ps-form__row--half',
						'validation' => array('password', 'required'),
						'type' => 'password',
						'class' => PeepSo::get_option_new('password_preview_enable') ? 'ps-js-password-preview' : null,
				),
			/*'firstname' => array(
				'label' => __('First Name', 'peepso-core'),
				'descript' => __('Enter your first name', 'peepso-core'),
				'value' => $input->value('firstname', '', FALSE), // SQL Safe
				'required' => 1,
				'validation' => array(
					'name-utf8',
					'minlen:' . PeepSoUser::FIRSTNAME_MINLEN,
					'maxlen:' . PeepSoUser::FIRSTNAME_MAXLEN
				),
				'type' => 'text',
			),
			'lastname' => array(
				'label' => __('Last Name', 'peepso-core'),
				'descript' => __('Enter your last name', 'peepso-core'),
				'value' => $input->value('lastname', '', FALSE), // SQL Safe
				'required' => 1,
				'validation' => array(
					'name-utf8',
					'minlen:' . PeepSoUser::LASTNAME_MINLEN,
					'maxlen:' . PeepSoUser::LASTNAME_MAXLEN
				),
				'type' => 'text',
			),

			'gender' => array(
				'label' => __('Your Gender', 'peepso-core'),
				'descript' => __('Please enter your gender', 'peepso-core'),
				'value' => $input->value('gender', 'm', FALSE), // SQL Safe
				'required' => 1,
				'type' => 'radio',
				'options' => array('m' => __('Male', 'peepso-core'), 'f' => __('Female', 'peepso-core')),
			),*/
			'task' => array(
				'type' => 'hidden',
				'value' => '-register-save',
			),
			'-form-id' => array(
				'type' => 'hidden',
				'value' => wp_create_nonce('register-form'),
			),
			'authkey' => array(
				'type' => 'hidden',
				'value' => '',
			),
            'terms' => array(
                'label' => sprintf(__('I agree to the %s.', 'peepso-core'),
                    '<a href="#" class="ps-js-btn-showterms">' . __('Terms and Conditions', 'peepso-core') . '</a>'
                ),
                'type' => 'checkbox',
                'required' => 1,
                'row_wrapper_class' => 'ps-form__row--checkbox',
                'value' => 1
            ),
            'privacy' => array(
                'label' => sprintf(__('I agree to the %s.', 'peepso-core'),
                    '<a href="#" class="ps-js-btn-showprivacy">' . __('Privacy Policy', 'peepso-core') . '</a>'
                ),
                'type' => 'checkbox',
                'required' => 1,
                'row_wrapper_class' => 'ps-form__row--checkbox',
                'value' => 1
            ),
            'avatar' => array(
                'label' => __('Avatar', 'peepso-core'),
                'required' => 1,
                'type' => 'avatar'
            ),
			'message' => array(
				'label' => __('Fields marked with an asterisk (<span class="ps-form__required">*</span>) are required.', 'peepso-core'),
				'type' => 'message',
			),
			'submit' => array(
				'label' => _x('Register', 'Submit Button on PeepSo registration form', 'peepso-core'),
				'class' => 'ps-btn--action',
				'type' => 'submit',
			)
		);

        // Remove "avatar" if the option is not enabled or WP avatars are enforced
        unset($fields['avatar']);
        if(FALSE) {
            $avatar = 0;
            if (!PeepSo::get_option('avatars_wordpress_only', 0)) {
                $avatar = (int)PeepSo::get_option_new('registration_avatars_enable');
            }

            switch ($avatar) {
                case 0:
                    unset($fields['avatar']);
                    break;              // Disabled, remove field
                case 1:
                    unset($fields['avatar']['required']);
                    break;  // Optional, remove "required"
            }
        }

        // Remove "no email" rule from validators if no username field is shown
        if(PeepSo::get_option_new('no_username_on_register')){
            $validation = $fields['username']['validation_options'];
            $new_validation = [];
            foreach($validation as $rule) {
                if($rule['name'] != 'validate_username_not_email') {
                    $new_validation[] = $rule;
                }
            }

            $fields['username']['validation_options'] = $new_validation;
            $fields['username']['type'] = 'hidden';
            if(!PeepSo::get_option('registration_confirm_email_field',1)) {
                $fields['email']['row_wrapper_class'] = 'ps-form__row--full ps-form__row--clear';
            }
        }

		if(PeepSo::get_option('registration_confirm_email_field',1)) {
		    $fields['username']['row_wrapper_class'] = 'ps-form__row--full ps-form__row--clear';
        } else {
		    unset($fields['email_verify']);
        }

		if(PeepSo::get_option('site_registration_recaptcha_enable', 0)) {
			$fields['submit']['class'] .= ' ps-js-recaptcha';

			// Enqueue recaptcha script.
			wp_enqueue_script('peepso-recaptcha');
		}

	$form = array(
			'name' => 'profile-edit',
			'action' => PeepSo::get_page('register'),
			'method' => 'POST',
			'class' => 'ps-form--register ps-form--register-main cform community-form-validate ps-js-form-register',
			'extra' => 'autocomplete="off"',
		);

        if (0 === PeepSo::get_option('site_registration_enableterms', 0)) {
            unset($fields['terms']);
            unset($fields['terms_text']);
        } elseif($page = PeepSo::get_option_new('site_registration_terms_page')) {
            $url =get_permalink( $page );
            $fields['terms']['label'] = sprintf(__('I agree to the %s.', 'peepso-core'),
                '<a href="'.$url.'" target="_blank" class="ps-js-btn-showterms-new-tab">' . __('Terms and Conditions', 'peepso-core') . '</a>');
        }

        if (0 === PeepSo::get_option('site_registration_enableprivacy', 0)) {
            unset($fields['privacy']);
            unset($fields['privacy_text']);
        }elseif($page = PeepSo::get_option_new('site_registration_privacy_page')) {
            $url =get_permalink( $page );
            $fields['privacy']['label'] = sprintf(__('I agree to the %s.', 'peepso-core'),
                '<a href="'.$url.'" target="_blank" class="ps-js-btn-showterms-new-tab">' . __('Privacy Policy', 'peepso-core') . '</a>');
        }

		$fields = apply_filters('peepso_register_form_fields', $fields);

		$form = array(
			'container' => array(
				'element' => 'div',
				'class' => 'ps-form__grid',
			),
			'fieldcontainer' => array(
				'element' => 'div',
				'class' => 'ps-form__row',
			),
			'form' => $form,
			'fields' => $fields,
		);

		return ($form);
	}

	/**
	 * Custom form validation -
	 * Validates if username is not equal to the password.
	 * @param  string $value The username, supplied from the post value.
	 * @return boolean
	 */
    public function validate_username_not_password($value)
    {
        $input = new PeepSoInput();

        // SQL Safe
        return (!empty($value) && $input->value('password','', FALSE) !== $value);
    }

    public function validate_username_not_email($value)
    {
        if(strstr($value, '@')) return FALSE;
        return TRUE;
    }
}

// EOF
