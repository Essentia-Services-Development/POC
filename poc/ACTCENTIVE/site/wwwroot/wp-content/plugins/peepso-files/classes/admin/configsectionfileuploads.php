<?php

class PeepSoConfigSectionFileUploads extends PeepSoConfigSectionAbstract
{

    public function register_config_groups()
	{
		$this->context='left';
        $this->general_configuration();

        $this->context='right';
        $this->user_related_configuration();
	}

	private function general_configuration()
	{
        $this->args('default', 0);
        $this->args('descript',
            __('When enabled users are able to upload files.', 'peepsofileuploads') .'<br/><strong>'.
            'Please note: enabling file uploads might in some cases expose your website to vulnerabilities and hacking attempts.<br/>PeepSo, Inc. takes no responsibility for potential security risks related to using this feature. ' .'</strong>'
        );
        $this->set_field(
            'fileuploads_enable',
            __('Enabled', 'peepsofileuploads'),
            'yesno_switch'
        );

        // $this->args('default', 0);
        // $this->args('descript', __('Users will be able to assign categories to files.', 'peepsofileuploads'));
        // $this->set_field(
        //     'fileuploads_categories_enable',
        //     __('Enable File Categories', 'peepsofileuploads'),
        //     'yesno_switch'
        // );

		$this->args('descript',
            __('One file type per line. Enable additional file types at your own risk.', 'peepsofileuploads').'<br>'.
            __('For added security, and especially in the case of risky/executable files (PHP, JS, HTML etc.), it is always better to instruct users to use zipped archives.', 'peepsofileuploads'));
		$this->set_field(
			'fileuploads_allowed_filetype',
			__('Accepted file types', 'peepsofileuploads'),
			'textarea'
		);

		// WP max upload size
		$wp_max_size = max(wp_max_upload_size(), 0);
		$wp_max_size /= pow(1024, 2);

		$this->args('int', TRUE);
		$this->args('validation', array('numeric'));
		$this->args('descript', sprintf(__('WordPress maximum upload size allowed is %1$sMB. WordPress setting takes priority over PeepSo. If you want to allow bigger file uploads please look into WrodPress and / or your server configuration.', 'peepsofileuploads'), $wp_max_size));
		$this->set_field(
			'fileuploads_max_upload_size',
			__('Maximum upload size in megabytes', 'peepsofileuploads'),
			'text'
		);

		// Build Group
		$this->set_group(
			'general',
			__('General', 'peepsofileuploads')
		);
	}

	private function user_related_configuration()
	{
		$this->args('int', TRUE);
        $this->args('default', 100);
		$this->args('validation', array('numeric'));
		$this->args('descript', __('Set 0 for Unlimited.', 'peepsofileuploads'));
		$this->set_field(
			'fileuploads_allowed_user_space',
			__('Allowed space per user in megabytes', 'peepsofileuploads'),
			'text'
		);

        $options = array(
			0    => 0,
			100  => 100,
			250  => 250,
			500  => 500,
			1000 => 1000
		);

        $this->args('options', $options);
        $this->args('validation', array('numeric'));
		$this->args('descript', __('Set 0 for Unlimited.', 'peepsofileuploads'));
        $this->set_field(
            'fileuploads_max_limit',
            __('Maximum number of files per user', 'peepsofileuploads'),
            'select'
        );

        $options = array(
			0  => 0,
			5  => 5,
			10 => 10,
			20 => 20,
			50 => 50
		);

        $this->args('options', $options);
        $this->args('validation', array('numeric'));
		$this->args('descript', __('Set 0 for Unlimited.', 'peepsofileuploads'));
        $this->set_field(
            'fileuploads_max_daily_limit',
            __('Daily files upload limit', 'peepsofileuploads'),
            'select'
        );

		// Build Group
		$this->set_group(
			'general',
			__('User limits', 'peepsofileuploads')
		);
	}
}
