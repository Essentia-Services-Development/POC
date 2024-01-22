<?php

class PeepSoConfigSectionWPJM extends PeepSoConfigSectionAbstract
{

    public function register_config_groups()
	{
		$this->context='left';
        $this->general_configuration();

        $this->context='right';
        $this->activity_stream();
	}

	private function general_configuration()
	{
        $this->args('default', 1);
        $this->set_field(
            'wpjm_enable',
            __('Enabled', 'peepso-wpjm'),
            'yesno_switch'
        );

        // Advanced

        $this->set_field(
            'wpjm_advanced_header',
            __('Advanced', 'peepsogivewp'),
            'separator'
        );

        $this->args('descript', __('Leave empty for default value', 'peepsogivewp'));
        $this->set_field(
            'wpjm_navigation_profile_label',
            __('Profile label', 'peepsogivewp'),
            'text'
        );

        $this->args('descript', __('Leave empty for default value', 'peepsogivewp') . '. Example: /profile/?' . PeepSoUser::get_instance()->get_username() . '/' . PeepSo::get_option('wpjm_navigation_profile_slug', 'jobs', TRUE));
        $this->set_field(
            'wpjm_navigation_profile_slug',
            __('Profile slug', 'peepsogivewp'),
            'text'
        );

        $this->args('descript', __('FontAwesome (or similar). Leave empty for default value', 'peepsogivewp'));
        $this->set_field(
            'wpjm_navigation_profile_icon',
            __('Custom icon CSS class', 'peepsogivewp'),
            'text'
        );
        
		// Build Group
		$this->set_group(
			'general',
			__('Profile Integration', 'peepso-wpjm')
		);
	}

	private function activity_stream()
	{
		$this->args('default', 1);
        $this->args('descript',
            __('Create activity stream item when someone post a job.', 'peepso-wpjm')
        );
        $this->set_field(
            'wpjm_stream_enable',
            __('Enabled', 'peepso-wpjm'),
            'yesno_switch'
        );

		$this->set_field(
			'wpjm_action_text',
			__('Action text', 'peepso-wpjm'),
			'text'
		);

        $this->args('descript', __('The title of  the Job Listing will be displayed after the action text as a link','peepso-wpjm'));
		$this->set_field(
			'wpjm_append_title',
			__('Append title after action text', 'peepso-wpjm'),
			'yesno_switch'
		);

		$options = [];
		$options[10] = __('Public', 'peepso-wpjm');
		$options[20] = __('Site Members', 'peepso-wpjm');
		if (class_exists('PeepSoFriendsPlugin')) $options[30] = __('Friends Only', 'peepso-wpjm');
		$options[40] = __('Only Me', 'peepso-wpjm');


        $this->args('options', $options);
        $this->set_field(
            'wpjm_default_privacy',
            __('Default privacy', 'peepso-wpjm'),
            'select'
        );
		
		// Build Group
		$this->set_group(
			'general',
			__('Activity Stream', 'peepso-wpjm')
		);
	}
}
