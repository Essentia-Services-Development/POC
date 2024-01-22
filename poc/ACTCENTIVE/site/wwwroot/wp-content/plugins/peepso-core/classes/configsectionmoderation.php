<?php

class PeepSoConfigSectionModeration extends PeepSoConfigSectionAbstract {
    const SITE_ALERTS_SECTION = 'site_alerts_';

    public function register_config_groups() {
        $this->set_context( 'left' );
        $this->reporting();
        $this->set_context( 'right' );
        $this->sensitive();


        $this->wordfilter();
    }


    private function reporting() {

        // # Enable Reporting
        $this->args( 'children', array( 'site_reporting_types', 'reporting_notify' ) );
        $this->set_field(
            'site_reporting_enable',
            __( 'Enabled', 'peepso-core' ),
            'yesno_switch'
        );

        // # Automatically unpublish reported posts after X reports

        $options = [];

        for ( $i = 0; $i <= 50; $i ++ ) {
            $options[ $i ] = $i;
        }

        $this->args( 'options', $options );
        $this->args( 'default', 0 );
        $this->args( 'validation', array( 'numeric' ) );
        $this->args( 'descript', __( 'If a post is reported enough times, it will be automatically unpublished. Set to 0 to disable.', 'peepso-core' ) );

        $this->set_field(
            'site_reporting_num_unpublish_post',
            __( "Automatically unpublish posts after: [n] reports", 'peepso-core' ),
            'select'
        );

        // # Predefined  Text
        $this->args( 'raw', true );
        $this->args( 'multiple', true );
        $this->args( 'descript', __( 'One per line.', 'peepso-core' ) );
        $this->set_field(
            'site_reporting_types',
            __( 'Predefined report reasons', 'peepso-core' ),
            'textarea'
        );

        // # Email alerts
        $this->args( 'descript', __( 'ON: Administrators and Community Administrators will receive emails about new reports' ) );
        $this->set_field(
            'reporting_notify_email',
            __( 'Email alerts', 'peepso-core' ),
            'yesno_switch'
        );

        // # Email alerts
        $this->args( 'descript', __( 'One per line.', 'peepso-core' ) . ' ' . __( 'Additional emails to receive notifications about new reports.' ) );
        $this->set_field(
            'reporting_notify_email_list',
            __( 'Additional recipients', 'peepso-core' ),
            'textarea'
        );


        // Build Group
        $this->set_group(
            'reporting',
            __( 'Reporting', 'peepso-core' )
        );
    }

    private function sensitive()
    {
        $this->set_field(
            'nsfw',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        // Build Group
        $this->set_group(
            'nsfw',
            __('Sensitive posts', 'peepso-core'),
            __('If enabled, users will be able to mark "sensitive" posts (NSFW/nudity, spoilers etc). The entire post content will be hidden until clicked.', 'peepso-core')
        );
    }


    private function wordfilter() {
        if(!function_exists('mb_substr') || !function_exists('mb_strlen')) {
            $this->set_field(
                'wordfilter_mb_warning',
                __('PHP functions mb_substr and mb_strlen are recommended for accurate text processing, especially for languages with accents (French, Spanish, Polish, Vietnamese etc) or using non-latin script (Russian, Chinese, Japanese etc).', 'peepso-core'),
                'message'
            );
        }
		# Enable WordFilter
		$this->args('default', 1);
		$this->set_field(
			'wordfilter_enable',
			__('Enabled', 'peepso-core'),
			'yesno_switch'
		);

		// Keywords to remove
		$this->args('validation', array('required', 'custom'));
		$this->args('validation_options',
            [
                [
                    'error_message' => __('Keywords cannot be empty and separated by comma.', 'peepso-core'),
                    'function' => array($this, 'check_keywords')
                ],
            ]
		);
		$this->args('descript', __('Separate words or phrases with a comma.', 'peepso-core'));
		$this->set_field(
			'wordfilter_keywords',
			__('Keywords to remove', 'peepso-core'),
			'textarea'
		);


		// what to filter
		$this->args('default', 1);
		$this->set_field(
			'wordfilter_type_' . PeepSoActivityStream::CPT_POST,
			__('Filter posts', 'peepso-core'),
			'yesno_switch'
		);

		$this->args('default', 1);
		$this->set_field(
			'wordfilter_type_' . PeepSoActivityStream::CPT_COMMENT,
			__('Filter comments', 'peepso-core'),
			'yesno_switch'
		);

		if ( class_exists('PeepSoMessagesPlugin') ) {
			$this->args('default', 1);
			$this->set_field(
				'wordfilter_type_' . PeepSoMessagesPlugin::CPT_MESSAGE,
				__('Filter chat messages', 'peepso-core'),
				'yesno_switch'
			);
		}

		// How to render
		$options = array(
			PeepSoWordFilter::WORDFILTER_FULL => '••••',
			PeepSoWordFilter::WORDFILTER_MIDDLE => 'W••d',
		);
		$this->args('options', $options);
		$this->args('default', 'on');
		$this->set_field(
			'wordfilter_how_to_render',
			__('How to render', 'peepso-core'),
			'select'
		);

		// Filter character
		$options = array(
            '•' => '••••',
			'*' => '****',
			'#' => '####',
		);
		$this->args('options', $options);
		$this->set_field(
			'wordfilter_character',
			__('Filter character', 'peepso-core'),
			'select'
		);

		$general_config = apply_filters('peepso_wordfilter_general_config', array());

		if(count($general_config) > 0 ) {

			foreach ($general_config as $option) {
				if(isset($option['descript'])) {
					$this->args('descript', $option['descript']);
				}
				if(isset($option['int'])) {
					$this->args('int', $option['int']);
				}
				if(isset($option['default'])) {
					$this->args('default', $option['default']);
				}

				$this->set_field($option['name'], $option['label'], $option['type']);
			}
		}


		// Build Group
		$this->set_group(
			'wordfilter',
			__('Word Filter', 'peepso-core')
		);
    }

    /**
	 * Checks if the keywords value is valid.
	 * @param  string $value keywords to filter
	 * @return boolean
	 */
	public function check_keywords($value)
	{
		$keywords = explode(',', $value);
		$ret = TRUE;
		foreach ($keywords as $word) {
			$word = trim($word);
			if(empty($word)) {
				$ret = FALSE;
			}
		}

		return $ret;
	}
}

// EOF
