<?php

class PeepSoRegisterShortcode
{
	private static $_instance = NULL;

	private $_err_message = NULL;

	private $_form = NULL;

    public $url;

	public function __construct()
	{
		if (PeepSo::get_option('site_registration_enable_ssl')) {
            PeepSo3_Utility_Redirect::https();
        }

        add_action('peepso_register_new_user', array(&$this, 'send_activation'), 10, 1);
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
		add_filter('peepso_register_error', array(&$this, 'error_message'), 10, 1);

		if ('POST' === $_SERVER['REQUEST_METHOD']) {
			if (isset($_POST['submit-activate'])) {
				// submitted the activation code
				$this->activate_account();
			}
			else if (isset($_POST['task']) && $_POST['task'] == '-resend-activation') {
				// submitted resend activation link
				$this->resend_activation();
			} else {
				if (FALSE !== $this->register_user()) {
                    PeepSo3_Utility_Redirect::_(PeepSo::get_page('register') . '?success');
				}
			}
		}
	}

	/*
	 * return singleton instance of teh plugin
	 */
	public static function get_instance()
	{
		if (self::$_instance === NULL)
			self::$_instance = new self();
		return (self::$_instance);
	}

	/*
	 * shortcode callback for the Registration Page
	 * @param array $atts Shortcode attributes
	 * @param string $content Contents of the shortcode
	 * @return string output of the shortcode
	 */
	public function do_shortcode($atts, $content)
	{
        PeepSo::do_not_cache();

		PeepSo::set_current_shortcode('peepso_register');

		$data = array('error' => $this->_err_message);

		if(!isset($this->url) || !($this->url instanceof PeepSoUrlSegments)) {
            $this->url = PeepSoUrlSegments::get_instance();
        }

        // since 1.11.3 - fallback for peepso_activate renamed into community_activate #3180
        if(get_current_user_id() && !isset($_GET['peepso_activate']) && !isset($_GET['community_activate'])) {
		    return PeepSoTemplate::exec_template('profile', 'already-registered', NULL, TRUE);
        }

		$ret = PeepSoTemplate::get_before_markup();

        // since 1.11.3 - fallback for peepso_activate renamed into community_activate #3180
		if (isset($_GET['community_activate']) || isset($_GET['peepso_activate'])) {
			$error = ('POST' === $_SERVER['REQUEST_METHOD']) ? array('error' => new WP_Error('error', $data['error'])) : array();
			$result = $this->activate_account();
			if ($result === FALSE)
				$ret .= PeepSoTemplate::exec_template('register', 'register-activate', $error, TRUE);
		} else if (isset($_GET['success'])) {
			$ret .= PeepSoTemplate::exec_template('register', 'register-complete', NULL, TRUE);
		} else if (isset($_GET['verified'])) {
			$ret .= PeepSoTemplate::exec_template('register', 'register-verified', NULL, TRUE);
		} else if (isset($_GET['resend'])) {
			if ('POST' === $_SERVER['REQUEST_METHOD']) {
				// check for any errors from call to resend_activation() in __construct()
				if (NULL === $this->_err_message){
					$ret .= PeepSoTemplate::exec_template('register', 'register-resent', NULL, TRUE);
				}
				else{
					$ret .= PeepSoTemplate::exec_template('register', 'register-resend', array('error' => new WP_Error('error', $this->_err_message)), TRUE);

					// Enqueue recaptcha script.
		            if (PeepSo::get_option('site_registration_recaptcha_enable', 0)) {
		                wp_enqueue_script('peepso-recaptcha');
		            }
				}
			} else {
				$ret .= PeepSoTemplate::exec_template('register', 'register-resend', NULL, TRUE);

				// Enqueue recaptcha script.
	            if (PeepSo::get_option('site_registration_recaptcha_enable', 0)) {
	                wp_enqueue_script('peepso-recaptcha');
	            }
			}
        } else if ($this->url->get(1) != PeepSo::get_page('register') && $this->url->get(1) != '') {
            // third parties might use our shortcode at a different URL
            ob_start();
            do_action('peepso_register_segment_' . $this->url->get(1), $this->url);

            $override = trim(ob_get_clean());

            if(strlen($override)) {
                $ret .= $override;
            } else {
                // #3657 in case there is no override hooked, present the default form
                $ret .= PeepSoTemplate::exec_template('register', 'register', $data, TRUE);
            }
        } else {

            $ret .= PeepSoTemplate::exec_template('register', 'register', $data, TRUE);
        }
		$ret .= PeepSoTemplate::get_after_markup();

		wp_reset_query();

		// disable WP comments from displaying on page
//		global $wp_query;
//		$wp_query->is_single = FALSE;
//		$wp_query->is_page = FALSE;

		return ($ret);
	}

	/*
	 * Performs registration operation
	 */
	private function register_user()
	{
		$input = new PeepSoInput();
		$sNonce = $input->value('-form-id', '', FALSE); // SQL Safe. isset($_POST['-form-id']) ? $_POST['-form-id'] : '';

        if (1 == PeepSo::get_option('registration_nonce_enable', 1) && !wp_verify_nonce($sNonce, 'register-form')) {
			$this->_err_message = __('Nonce is invalid, try refreshing the page or contact the Administrators.', 'peepso-core');
			return FALSE;
		}

		$u = PeepSoUser::get_instance(0);

		$uname = $input->value('username', '', FALSE); // SQL Safe
		$email = $input->value('email', '', FALSE); // SQL Safe
		$passw = $input->raw('password', '');
		$pass2 = $input->raw('password2', '');

		$task = $input->value('task', '', FALSE); // SQL Safe

		$register = PeepSoRegister::get_instance();
		$register_form = $register->register_form();
		$form = PeepSoForm::get_instance();
		$form->add_fields($register_form['fields']);
		$form->map_request();

		if (FALSE === $form->validate()) {
			$this->_err_message = __('Form contents are invalid.', 'peepso-core');
			return (FALSE);
		}

		// verify form contents
		if ('-register-save' != $task) {
			$this->_err_message = __('Form contents are invalid.', 'peepso-core');
			return (FALSE);
		}

		if (empty($uname) || empty($email) || empty($passw)) {
			$this->_err_message = __('Required form fields are missing.', 'peepso-core');
			return (FALSE);
		}

		$valid_email = apply_filters('peepso_register_valid_email', TRUE, $email);
		if (!is_email($email) || !$valid_email) {
			$this->_err_message = __('Please enter a valid email address.', 'peepso-core');
			return (FALSE);
		}

		$id = get_user_by('email', $email);
		if (FALSE !== $id) {
			$this->_err_message = __('That email address is already in use.', 'peepso-core');
			return (FALSE);
		}

		$id = get_user_by('login', $uname);
		if (FALSE !== $id) {
			$this->_err_message = __('That user name is already in use.', 'peepso-core');
			return (FALSE);
		}

		if (PeepSo::get_option('registration_confirm_email_field', 1)) {
			$email_verify = $input->value('email_verify', '', FALSE); // SQL Safe
			if ($email !== $email_verify) {
				$this->_err_message = __('The emails you submitted do not match.', 'peepso-core');
				return (FALSE);
			}
		}

		if ($passw != $pass2) {
			$this->_err_message = __('The passwords you submitted do not match.', 'peepso-core');
			return (FALSE);
		}

		// checking additional fields is include in registration page?.
		if(isset($register_form['fields']['extended_profile_fields'])) {
			$valid_ext_fields = apply_filters('peepso_register_valid_extended_fields', TRUE, $input);
			if( FALSE === $valid_ext_fields) {
				$this->_err_message = __('Additional fields are invalid.', 'peepso-core');
				return (FALSE);
			}
		}

		// Verify Invisible reCAPTCHA parameter if config is enabled.
		if (PeepSo::get_option('site_registration_recaptcha_enable', 0)) {
			$args = array(
				'body' => array(
					'response' => $input->value('g-recaptcha-response', '', FALSE), // SQL Safe
					'secret' => PeepSo::get_option('site_registration_recaptcha_secretkey', 0),
				)
			);
			$request = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', $args);
			$result = json_decode(wp_remote_retrieve_body($request), true);
			if (!$result) {
				$this->_err_message = __('Failed to verify reCAPTCHA.', 'peepso-core');
				return (FALSE);
			}
			if (!$result['success']) {
				$this->_err_message = __('The reCAPTCHA code is invalid.', 'peepso-core');
				return (FALSE);
			}
		}

		$wpuser = $u->create_user('', '', $uname, $email, $passw, '');
		$user = PeepSoUser::get_instance($wpuser);

		if (PeepSo::get_option('registration_disable_email_verification', '0')) {
			if (PeepSo::get_option('site_registration_enableverification', '0')) {
				$this->admin_approval($user);
			} else {
				$user->set_user_role('member');
			}
		} else {
			$user->set_user_role(apply_filters('peepso_user_default_role', 'register'));
		}
		do_action('peepso_register_new_user', $wpuser);

		return (TRUE);
	}

	/**
	 * Send the email activation link to new users
	 */
	public function send_activation($user_id)
	{
		if (PeepSo::get_option('registration_disable_email_verification', 0)) {
			return;
		}

		$u = PeepSoUser::get_instance($user_id);

		// send user an activation email
		$u->send_activation($u->get_email(),1);
	}

	/*
	 * Resends the email activation link to new users
	 */
	private function resend_activation()
	{
		$input = new PeepSoInput();

		$err = NULL;
		$nonce = $input->value('-form-id', '', FALSE); // SQL Safe
		if (!wp_verify_nonce($nonce, 'resent-activation-form')) {
			$this->_err_message = __('Invalid form contents.', 'peepso-core');
			return (FALSE);
		}

		$email = sanitize_email($input->value('email', '', FALSE)); // SQL Safe
		if (!is_email($email)) {
			$this->_err_message = __('Please enter a valid email address', 'peepso-core');
			return (FALSE);
		}

		// verify form contents
		$task = $input->value('task', '', FALSE); // SQL Safe
		if ('-resend-activation' !== $task) {
			$this->_err_message = __('Invalid form contents.', 'peepso-core');
			return (FALSE);
		}

		// form is valid; look up user by email address
		$user = get_user_by('email', $email);
		if (FALSE !== $user) {
			// if it's a valid user - resend the email
			$u = PeepSoUser::get_instance($user->ID);
			$u->send_activation($email, 1);
		} else {
			$this->_err_message = __('There is no user registered with that email address.', 'peepso-core');
			return (FALSE);
		}
		// if it's not a valid user, we don't want to act like there was a problem
	}

	/**
	 * Returns the error message
	 * @param  string $msg The error message, assigned to $this->_err_message
	 * @return string      The error message
	 */
	public function error_message($msg)
	{
		if (NULL !== $this->_err_message)
			$msg = $this->_err_message;
		return ($msg);
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script('peepso-register', PeepSo::get_asset('js/register.min.js'), array('jquery', 'underscore', 'peepso-form'), PeepSo::PLUGIN_VERSION, TRUE);

		// Frontend data for the registration form.
		add_filter('peepso_data', function ($data) {
			$data_register = array(
				'confirm_email_field' => PeepSo::get_option('registration_confirm_email_field', 1),
				'text_terms' => stripslashes(nl2br(PeepSoSecurity::strip_content(PeepSo::get_option('site_registration_terms', '')))),
				'text_privacy' => stripslashes(nl2br(PeepSoSecurity::strip_content(PeepSo::get_option('site_registration_privacy', ''))))
			);

			$data['register'] = $data_register;
			return $data;
		}, 10, 1);
	}

	/**
	 * Changes the user's role to peepso_verified.
	 */
	public function activate_account()
	{
		global $wpdb;

        $input = new PeepSoInput();
        $key = $input->value('community_activation_code', $input->value('peepso_activation_code', NULL, FALSE), FALSE); // Fallback activation code - see #3142

		// Empty key, error
        if(!strlen($key)) {
            $this->_err_message = __('Please enter an activation code', 'peepso-core');
            return (FALSE);
        }

        // Get user by meta
        $args = array(
            'fields' => 'ID',
            'meta_key' => 'peepso_activation_key',
            'meta_value' => $key,
            'number' => 1 // limit to 1 user
        );
        $user = new WP_User_Query($args);

        if (count($user->results) > 0) {
            $user = get_user_by('id', $user->results[0]);
            $wpuser = PeepSoUser::get_instance($user->ID);

            if('ban' == $wpuser->get_user_role()) {
                echo __('Your account has been suspended indefinitely', 'peepso-core');
                return;
            }

            do_action('peepso_register_verified', $wpuser);

	        PeepSo3_Mayfly_Int::del('user_'.$user->ID.'_send_activation_count');
	        PeepSo3_Mayfly::del('user_'.$user->ID.'_send_activation_last_attempt_trigger');
	        PeepSo3_Mayfly::del('user_'.$user->ID.'_send_activation_last_attempt_date');

			if (PeepSo::get_option('site_registration_enableverification', '0')) {
				// force to activation redirect, if admin update role manually
				if($wpuser->get_user_role() == 'member') {
					$_SESSION['peepso_activate_account'] = 1;
					PeepSo::redirect(PeepSo::get_page('activation_redirect'));
                	die();
				}

                $this->admin_approval($wpuser);
                PeepSo::redirect(PeepSo::get_page('register') . '?verified');
                die();
            } else {
				$_SESSION['peepso_activate_account'] = 1;
                $wpuser->set_user_role('member');
                PeepSo::redirect(PeepSo::get_page('activation_redirect'));
                die();
                // Automatic login is insecure - see #2680
                #wp_clear_auth_cookie();
                #wp_set_current_user($user->ID);
                #wp_set_auth_cookie($user->ID);
            }


            $redirect = PeepSo::get_page('redirectlogin');
            if (empty($redirect)) {
                $redirect = PeepSo::get_page('profile');
            }

            PeepSo::redirect($redirect);
        } else {
            $this->_err_message = __('We are unable to find an account for this activation code', 'peepso-core');
            return (FALSE);
        }
	}

	public static function description() {
	    return __('Displays registration form for your community. You can decide which fields to show on the registration.','peepso-core');
    }

    public static function post_state() {
        return _x('PeepSo', 'Page listing', 'peepso-core') . ' - ' . __('Registration', 'peepso-core');
    }

	/**
	 * Admin approval
	 */
	public function admin_approval($wpuser)
	{
		if ($wpuser->peepso_user['usr_role'] != 'verified') {
			$wpuser->set_user_role('verified');

			// send admin an email
			$args = array(
				'role' => 'administrator',
			);

			$user_query = new WP_User_Query($args);
			$users = $user_query->get_results();

			$adm_email = PeepSo::get_notification_emails();

			$data = array(
				'userlogin' => $wpuser->get_username(),
				'userfullname' => trim(strip_tags($wpuser->get_fullname())),
				'userfirstname' => $wpuser->get_firstname(),
				'permalink' => admin_url('users.php?s=' . $wpuser->get_email()),
			);

			$is_admin_email = FALSE;
			if (count($users) > 0) {
				foreach ($users as $user) {
					$email = $user->data->user_email;
					if ($email == $adm_email) {
						$is_admin_email = TRUE;
					}
					$data['useremail'] = $email;
					$data['thatuseremail'] = $wpuser->get_email();
					$data['currentuserfullname'] = PeepSoUser::get_instance(get_user_by('email', $email)->ID)->get_fullname();
					PeepSoMailQueue::add_message($user->ID, $data, __('{sitename} - New User Registration', 'peepso-core'), 'new_user_registration', 'new_user_registration',0,1);
				}
			}

			if (!$is_admin_email) {
				$data['useremail'] = $adm_email;
				$data['thatuseremail'] = $wpuser->get_email();
				$data['currentuserfullname'] = PeepSoUser::get_instance(get_user_by('email', $email)->ID)->get_fullname();
				PeepSoMailQueue::add_message(PeepSo::get_notification_user(), $data, __('{sitename} - New User Registration', 'peepso-core'), 'new_user_registration', 'new_user_registration',0,1);
			}
		}
	}
}

// EOF
