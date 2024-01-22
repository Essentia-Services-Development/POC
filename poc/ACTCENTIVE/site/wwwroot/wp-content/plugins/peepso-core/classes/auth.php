<?php

class PeepSoAuth extends PeepSoAjaxCallback
{
	/**
	 * Called from PeepSoAjaxHandler
	 * Declare methods that don't need auth to run
	 * @return array
	 */
	public function ajax_auth_exceptions()
	{
		return array(
			'login',
		);
	}

	/**
	 * Handles AJAX login requests.
	 * @param  PeepSoAjaxResponse $resp
	 */
	public function login(PeepSoAjaxResponse $resp)
	{
        if(1 == PeepSo::get_option('login_nonce_enable', 1)) {
            $check = check_ajax_referer('ajax-login-nonce', 'security', FALSE);
            if ($check === FALSE) {
                $resp->set('securify_check', $check);
                $resp->set('dialog_title', __('Login Error', 'peepso-core'));
                $resp->error(__('Sorry, your login attempt failed. Please refresh the page and try again.<br><br>Contact the webmaster if the problem persists.', 'peepso-core'));
                return (FALSE);
            }
		}
		
		if(PeepSo::get_option('recaptcha_login_enable', 0)) {
			$input = new PeepSoInput();
			$recaptcha_response = $input->value('g-recaptcha-response','', FALSE); //SQL safe

			$args = array(
					'body' => array(
							'response' => $recaptcha_response,
							'secret' => PeepSo::get_option('site_registration_recaptcha_secretkey', 0),
					)
			);

			$host = PeepSo3_ReCaptcha::url();
			$request = wp_remote_post($host . '/recaptcha/api/siteverify', $args);
			$response_json = json_decode(wp_remote_retrieve_body($request), true);
			if (isset($response_json['success']) && $response_json['success'] !== TRUE) {
				$resp->set('dialog_title', __('Login Error', 'peepso-core'));
				$resp->error(__('ReCaptcha security check failed.', 'peepso-core'));
				return (FALSE);
			}
		}

		$info = array();
		$info['user_login'] = trim($this->_input->value('username','',false));// SQL safe, admin only
		$info['user_password'] = $this->_input->raw('password');
		$info['remember'] = $this->_input->int('remember', 0) ? TRUE : FALSE;

		$secure_cookie = NULL;

		// If the user wants ssl but the session is not ssl, force a secure cookie.
		$user = is_email( $info['user_login'] ) ? get_user_by( 'email', $info['user_login'] ) : get_user_by( 'login', sanitize_user( $info['user_login'] ) );
		if ( ! force_ssl_admin() ) {
			if ( $user && get_user_option( 'use_ssl', $user->ID ) ) {
				$secure_cookie = TRUE;
				force_ssl_admin( TRUE );
			}
		}

		if ( force_ssl_admin() ) {
			$secure_cookie = TRUE;
		}

		if ( is_null( $secure_cookie ) && force_ssl_admin() ) {
			$secure_cookie = TRUE;
		}

		$login_with_email = PeepSo::get_option('login_with_email', 0);
		if ($login_with_email == 2 && !is_email($info['user_login'])) {
			$resp->success(FALSE);
			$resp->set('dialog_title', __('Login Error', 'peepso-core'));
			$resp->error(__('Invalid email address.', 'peepso-core'));
			return (FALSE);
		}

		if ($login_with_email == 1 && !is_email($info['user_login']) && isset($user->ID) && PeepSo::is_admin($user->ID)) {
			$resp->success(FALSE);
			$resp->set('dialog_title', __('Login Error', 'peepso-core'));
			$resp->error(__('Invalid email address.', 'peepso-core'));
			return (FALSE);
		}
		
		remove_action( 'wp_login_failed', 'pmpro_login_failed', 10, 2 ); // PMP compatibility
	    $user_signon = wp_signon($info, $secure_cookie);
	    if (is_wp_error($user_signon)){
	    	$resp->success(FALSE);
	    	$resp->set('dialog_title', __('Login Error', 'peepso-core'));

	    	if (empty($info['user_login']) && empty($info['user_password']))
	    		$resp->error(__('Username and password required.', 'peepso-core'));
			else {
				$msg = $user_signon->get_error_message();
				$pattern = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
				$msg = preg_replace($pattern, PeepSo::get_page('recover'), $msg);
				$resp->set('error_code', $user_signon->get_error_codes());
				$resp->error($msg);
				return (FALSE);
			}
	    } else {
			wp_set_auth_cookie($user_signon->ID, $info['remember']);
	        $resp->success(TRUE);
	    }
	}
}

// EOF
