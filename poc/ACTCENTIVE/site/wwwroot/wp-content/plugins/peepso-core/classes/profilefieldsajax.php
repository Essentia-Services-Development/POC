<?php

class PeepSoProfilefieldsAjax extends PeepSoAjaxCallback
{
    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        return array(
        	'validate',
            'validate_register',            
        );
    }

	// @todo docblock
	public function validate(PeepSoAjaxResponse $resp)
	{
		$view_uid      = $this->_input->int('view_user_id',0);
		$id            = $this->_input->int('id',0);
		$name          = $this->_input->value('name','', FALSE); // SQL Safe
		$value         = $this->_input->value('value','', FALSE); // SQL Safe
		$validate_only = TRUE;

		$field = PeepSoField::get_field_by_id($id, $view_uid);

		if( !($field instanceof PeepSoField)) {
			$resp->success( FALSE );
			$resp->error('Invalid field ID');
			return;
		}

		// wp field returns INT, peepso field returns BOOL
		$success = $field->save($value, $validate_only);

		if( TRUE === $success || is_int($success) ) {
			$resp->success( TRUE );
		} else {
			$resp->success( FALSE );
			$resp->error($field->validation_errors);
		}
	}

	// @todo docblock
	public function savefield(PeepSoAjaxResponse $resp)
	{
		// @todo this code is repeated
		$view_uid 	= $this->_input->int('view_user_id',0);
		$uid 		= $this->_input->int('user_id',0);
		$cur_uid	= get_current_user_id();

		if( (!$view_uid || !$uid || !$cur_uid) || ($cur_uid != $uid) || ($view_uid !=$uid && !current_user_can('edit_users')) ) {
			$resp->error('Insufficient permissions');
			$resp->success(FALSE);
			return;
		}
		// eof @todo this code is repeated

		$id			= $this->_input->int('id');
		$value		= $this->_input->value('value', '', FALSE); // SQL Safe

		$field = PeepSoField::get_field_by_id($id, $view_uid);

		if( !($field instanceof PeepSoField)) {
			$resp->success( FALSE );
			$resp->error('Invalid field ID');
			return;
		}

		// wp field returns INT, peepso field returns BOOL
        $user = PeepSoUser::get_instance($view_uid);
        $user->profile_fields->load_fields();
        $user->profile_fields->get_fields();
        $stats_old = $user->profile_fields->profile_fields_stats;

		$success = $field->save($value);

		if( TRUE === $success || is_int($success) ) {

            // reload everything
			$user->profile_fields->load_fields();
			$user->profile_fields->get_fields();
			$stats = $user->profile_fields->profile_fields_stats;

			// the action is wrapped in a buffer to avoid breaking the AJAX
			ob_start();
            $resp->set('peepso_action_profile_completeness_change', 0);
			if($stats_old['completeness'] != $stats['completeness']) {
                do_action('peepso_action_profile_completeness_change', array('before' => $stats_old['completeness'], 'after' => $stats['completeness']));
                $resp->set('peepso_action_profile_completeness_change', 1);
            }
			ob_end_clean();

			$resp->set('profile_completeness', $stats['completeness']);
			$resp->set('profile_completeness_message', $stats['completeness_message']);

			$resp->set('missing_required',	$stats['missing_required']);
			$resp->set('missing_required_message',	$stats['missing_required_message']);

			$resp->success( TRUE );
			$resp->set('display_value', $field->render( FALSE ));
		} else {
			$resp->success( FALSE );
			$resp->error($field->validation_errors);
		}
	}

	public function save_acc(PeepSoAjaxResponse $resp)
	{
		// @todo this code is repeated
		$view_uid 	= $this->_input->int('view_user_id',0);
		$uid 		= $this->_input->int('user_id',0);
		$cur_uid	=	get_current_user_id();

		if( (!$view_uid || !$uid || !$cur_uid) || ($cur_uid != $uid) || ($view_uid !=$uid && !current_user_can('edit_users')) ) {
			$resp->error('Insufficient permissions');
			$resp->success(FALSE);
			return;
		}
		// eof @todo this code is repeated


		$id			= $this->_input->int('id');
		$acc		= $this->_input->int('acc');

		$field = PeepSoField::get_field_by_id($id, $view_uid);

		if( !($field instanceof PeepSoField)) {
			$resp->success( FALSE );
			$resp->error('Invalid field ID');
			return;
		}

		if( TRUE === $field->save_acc($acc) ) {
			$resp->success( TRUE );
		} else {
			$resp->success( FALSE );
			$resp->error(__('Couldn\'t save privacy', 'peepso-core'));
		}
	}

	public function validate_register(PeepSoAjaxResponse $resp) {
		$fname = $this->_input->value('name', '', FALSE); // SQL Safe
		$uname = $this->_input->value('username', '', FALSE); // SQL Safe
		$email = $this->_input->value('email', '', FALSE); // SQL Safe
		$passw = $this->_input->value('password', '', FALSE); // SQL Safe
		$pass2 = $this->_input->value('password2', '', FALSE); // SQL Safe

		$register = PeepSoRegister::get_instance();
		$register_form = $register->register_form();
		$form = PeepSoForm::get_instance();
		$form->add_fields($register_form['fields']);
		$form->map_request();

		if (FALSE === $form->validate()) {
			foreach ($form->fields as &$field) {
				if ($field['name'] === $fname && !$field['valid']) {
					foreach ($field['error_messages'] as $error) {
						$resp->error( $error );
					}
				}
			}
		}

		// validate username
		if ('username' === $fname) {
			$id = get_user_by('login', $uname);
			if (FALSE !== $id) {
				$resp->error(__('That user name is already in use.', 'peepso-core'));
			}
		}

		// validate email
		if ('email' === $fname) {
			// validate email
			$valid_email = apply_filters('peepso_register_valid_email', TRUE, $email);
			if (!$valid_email) {
				$resp->error(__('Please enter a valid email address.', 'peepso-core'));
			}
			
			$id = get_user_by('email', $email);
			if (FALSE !== $id) {
				$resp->error(__('That email address is already in use.', 'peepso-core'));
			}
		}

		// validate verify email
		if ('email_verify' === $fname) {
			if (PeepSo::get_option('registration_confirm_email_field', 1)) {
				$email_verify = $this->_input->value('email_verify', '', FALSE); // SQL Safe
				if ($email !== $email_verify) {
					$resp->error(__('The emails you submitted do not match.', 'peepso-core'));
				}
			}
		}

		// validate password
		if ('password' === $fname) {
		}

		// validate verify password
		if ('password2' === $fname) {
			if ($passw != $pass2) {
				$resp->error(__('The passwords you submitted do not match.', 'peepso-core'));
			}
		}
	}
}

// EOF
