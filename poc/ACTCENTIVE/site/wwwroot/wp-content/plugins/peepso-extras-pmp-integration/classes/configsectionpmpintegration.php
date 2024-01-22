<?php

class PeepSoConfigSectionPMPIntegration extends PeepSoConfigSectionAbstract
{
// Builds the groups array
	public function register_config_groups()
	{
		$this->context='left';
		$this->_pmp_integration_general();
	}

	/**
	 * Add this addon's configuration options to the admin section
	 * @param  array $config_groups
	 * @return array
	 */
	private function _pmp_integration_general()
	{

		$section = 'pmp_integration_';

		// # Message Logging Description
		$this->set_field(
				$section,
				__('Configure the use of PeepSo PMP Integration.', 'peepso-pmp'),
				'message'
		);

		$args = array(
			'descript' => __('Turn on the use of PMP Integration', 'peepso-pmp'),
			'int' => TRUE,
			'field_wrapper_class' => 'controls col-sm-8',
			'field_label_class' => 'control-label col-sm-4',
		);

		$this->args = $args;
		$this->set_field(
			$section . 'enabled',
			__('Enable PMP Integration', 'peepso-pmp'),
			'yesno_switch'
		);

		$this->args('default', 1);
		$this->set_field(
			$section . 'enable_upon_registration',
			__('Enable integration upon PeepSo registration', 'peepso-pmp'),
			'yesno_switch'
		);

		$this->args('default', 1);
		$this->set_field(
			$section . 'force_complete_membership',
			__('Force complete membership for users that have no membership plans', 'peepso-pmp'),
			'yesno_switch'
		);

		$this->set_field(
			$section . 'email_notification_enabled',
			__('Enable email notifications for users that have no membership plans', 'peepso-pmp'),
			'yesno_switch'
		);

		$this->set_field(
			$section . 'hide_membership_tab',
			__('Hide "Membership" tab in user profiles', 'peepso-pmp'),
			'yesno_switch'
		);

		// User State
        $options = array(
            'cookie' => __('Cookie','peepso-core'),
            'session' => __('Session','peepso-core'),
        );
        $args = array(
        	'options' => $options,
        	'decript' => __('How would you like to handle the user login state while choosing a membership level? This setting is related to an issue introduced in PMP 2.4.1','peepso-pmp')
        );
        $this->args = $args;
        $this->set_field(
            $section . 'user_login_state',
            __('User Login State Handler', 'peepso-pmp'),
            'select'
        );

		if (class_exists('PeepSoGroupsPlugin')) {
			$this->args('default', 0);
			$this->args('descript',__('When enabled, users who lose access to a membership level will be removed from all groups assigned to this membership level', 'peepso-pmp'));

			$this->set_field(
				$section . 'remove_from_group',
				__('Strict group memberships (BETA)', 'peepso-pmp'),
				'yesno_switch'
			);
		}

		// Build Group
		$this->set_group(
			'general',
			__('General', 'peepso-pmp')
		);
	}

}
