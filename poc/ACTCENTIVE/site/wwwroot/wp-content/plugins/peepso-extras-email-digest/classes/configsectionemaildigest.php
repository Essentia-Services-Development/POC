<?php

class PeepSoConfigSectionEmailDigest extends PeepSoConfigSectionAbstract {

// Builds the groups array
	public function register_config_groups() {
		$this->context = 'left';
		$this->general();
		$this->content();
		$this->users();

		$this->context='right';
		$this->log();
	}

	private function content() {

        $this->args('default', get_bloginfo('name') . ' - ' . __('Email Digest', 'peepso-email-digest'));
        $this->set_field(
            'email_digest_title',
            __('Email Digest Title', 'peepso-email-digest'),
            'text'
        );


        $this->args('default', 1);
        $this->set_field(
            'email_digest_use_images',
            __('Use images', 'peepso-email-digest'),
            'yesno_switch'
        );

		if (class_exists('PeepSoFileUploads')) {
			$this->args('default', 0);
			$this->set_field(
				'email_digest_use_files',
				__('Use files', 'peepso-email-digest'),
				'yesno_switch'
			);
		}


        $options = array();
        for ($i = 3; $i <= 10; $i++) {
            $options[$i] = $i;
        }

        $this->args('options', $options);
        $this->set_field(
            'email_digest_activity_count',
            __('How many posts should be included per one email', 'peepso-email-digest'),
            'select'
        );

        $this->args('descript', __("An Email Digest email must have at least 1 new post to be sent out. If there are no new ones it won't be sent.", 'peepso-email-digest'));
        $this->set_field(
            'email_digest_send_less_activities',
            __('Send Email Digest emails even if there are less posts', 'peepso-email-digest'),
            'yesno_switch'
        );

        $this->args('descript', __("With this setting switched on, Email Digest email will also contain the most liked post from a given period and showcase it as such.", 'peepso-email-digest'));
        $this->set_field(
            'email_digest_most_liked',
            __('Include most liked post', 'peepso-email-digest'),
            'yesno_switch'
        );

        $this->args('descript', __("With this setting switched on, Email Digest email will also contain the most commented on post from a given period and showcase it as such.", 'peepso-email-digest'));
        $this->set_field(
            'email_digest_most_commented',
            __('Include most commented post', 'peepso-email-digest'),
            'yesno_switch'
        );

        $this->args('descript', __("With this setting on, most commented on and most liked posts will be showcased as such. However, they might also, and most likely will be shown in the main content of the Email Digest email.", 'peepso-email-digest'));
        $this->set_field(
            'email_digest_allow_duplicate',
            __('Allow duplicate posts', 'peepso-email-digest'),
            'yesno_switch'
        );


        $this->set_field(
            'email_digest_limit_post_enable',
            __('Limit the post length in Email Digest email to', 'peepso-email-digest'),
            'yesno_switch'
        );

        $this->args('descript', __("Show 'read mode' if the posts can't be shown in full and they hit the limit of characters.", 'peepso-email-digest'));
        $this->args('validation', array('numeric', 'minval:200'));
        $this->set_field(
            'email_digest_limit_post_length',
            '',
            'text'
        );

        $this->set_group(
            'content', __('Content', 'peepso-email-digest')
        );
    }
	private function users() {
        $this->args('default', 1);
        $this->set_field(
            'email_digest_default',
            __('Subscribe new users by default', 'peepso-email-digest'),
            'yesno_switch'
        );

        $peepso_roles = PeepSoAdmin::get_instance()->get_translated_roles();
        unset($peepso_roles['ban']);
        unset($peepso_roles['register']);
        unset($peepso_roles['verified']);
        unset($peepso_roles['moderator']);

        $roles = array();
        $i = 0;
        foreach ($peepso_roles as $key => $val) {
            if ($i == 0) {
                $label = __('Which User Roles should receive the emails', 'peepso-email-digest');
            } else {
                $label = '';
            }

            $this->args('descript', $val);
            $this->set_field(
                'email_digest_role_' . $key,
                $label,
                'yesno_switch'
            );

            $i++;
        }

        $this->set_group(
            'users', __('Users', 'peepso-email-digest')
        );

    }
	/**
	 * Add this addon's configuration options to the admin section
	 * @param  array $config_groups
	 * @return array
	 */
	private function general() {
	    

		$this->set_field(
				'email_digest_',
                __('These settings control the Email Digest plugin. The plugin has been designed to interest and bring inactive users back to your community by showing them most interesting and engaging posts from a given period.', 'peepso-email-digest'),
                'message'
		);

		
		$this->args('descript', __("Email Digest emails contains only posts which have 'Public' or 'Site Members' privacy settings. All other posts, meaning with privacy settings of 'Only me' and 'Friends Only' will be ignored and not sent as part of the Email Digest.", 'peepso-email-digest'));
		$this->set_field(
		    'email_digest_enable',
            __('Send Email Digest emails', 'peepso-email-digest'),
            'yesno_switch'
		);


		$options_inactive_day = array();
		for ($i = 1; $i <= 30; $i++) {
			$options_inactive_day[$i] = $i;
		}

		$this->args('descript', __("Users who didn't visit the site in the number of days selected or more will receive the Email Digest emails.", 'peepso-email-digest'));
		$this->args('options', $options_inactive_day);
		$this->set_field(
				'email_digest_send_inactive',
                __('Send Email Digest emails to users inactive for more than', 'peepso-email-digest'),
                'select'
		);

		$options_schedule_type = PeepSoEmailDigest::options_schedule_type();

        $this->args('options', $options_schedule_type);
		$this->set_field(
				'email_digest_schedule_type',
                __('How often should the Email Digest emails be sent', 'peepso-email-digest'),
                'select'
		);


		$options_date = array();
		for ($i = 1; $i <= 31; $i++) {
			$options_date[$i] = $i;
		}

        $this->args('descript', __("If selected date is bigger than the number of days in month, email digest will be sent at the end of the month.", 'peepso-email-digest'));
        $this->args('options', $options_date);
		$this->set_field(
				'email_digest_schedule_monthly_date',
                __('Every', 'peepso-email-digest'),
                'select'
		);

		$options_day = array(
			'sunday' => __('Sun', 'peepso-email-digest'),
			'monday' => __('Mon', 'peepso-email-digest'),
			'tuesday' => __('Tue', 'peepso-email-digest'),
			'wednesday' => __('Wed', 'peepso-email-digest'),
			'thursday' => __('Thu', 'peepso-email-digest'),
			'friday' => __('Fri', 'peepso-email-digest'),
			'saturday' => __('Sat', 'peepso-email-digest')
		);

        $this->args('options', $options_day);
		$this->set_field(
				'email_digest_schedule_weekly_day',
                __('Every', 'peepso-email-digest'),
                'inline-select'
		);

		$options_hour = array();
		for ($i = 0; $i <= 11; $i++) {
			$options_hour[$i] = $i;
		}

		$options_minute = array(
			'00' => '00',
			'15' => '15',
			'30' => '30',
			'45' => '45'
		);

		$options_am_pm = array(
			'am' => 'am',
			'pm' => 'pm'
		);

        $this->args('descript', __("The time when Email Digest emails will start to be sent. Emails are sent in batches by the mailqueue, they're not all sent at once, so your users might get them at different times.", 'peepso-email-digest'));
        $this->args('options', $options_hour);
		$this->set_field(
				'email_digest_hour_daily', __('At', 'peepso-email-digest'), 'select'
		);


        $this->args('options', $options_minute);
		$this->set_field(
				'email_digest_minute_daily',
                __('At', 'peepso-email-digest'),
                'select'
		);



        $this->args('options', $options_am_pm);
		$this->set_field(
				'email_digest_am_pm_daily',
                '',
                'select'
		);

        $this->args('descript', __("The time when Email Digest emails will start to be sent. Emails are sent in batches by the mailqueue, they're not all sent at once, so your users might get them at different times.", 'peepso-email-digest'));
        $this->args('options', $options_hour);
		$this->set_field(
				'email_digest_hour_weekly',
                __('At', 'peepso-email-digest'),
                'select'
		);

        $this->args('options', $options_minute);
		$this->set_field(
				'email_digest_minute_weekly',
                '',
                'select'
		);

		$this->args('options', $options_am_pm);
		$this->set_field(
				'email_digest_am_pm_weekly',
                '',
                'select'
		);


		$this->args('descript', __("The time when Email Digest emails will start to be sent. Emails are sent in batches by the mailqueue, they're not all sent at once, so your users might get them at different times.", 'peepso-email-digest'));
		$this->args('options', $options_hour);
		$this->set_field(
				'email_digest_hour_monthly',
                __('At', 'peepso-email-digest'),
                'select'
		);


		$this->args('options', $options_minute);
		$this->set_field(
				'email_digest_minute_monthly',
                '',
                'select'
		);


		$this->args('options', $options_am_pm);
		$this->set_field(
				'email_digest_am_pm_monthly',
                '',
                'select'
		);



        $this->args('descript', sprintf(__("It's advised to switch this setting on and setup a server-side cron job. You can use this command: wget %s It can easily run every hour.", 'peepso-email-digest'), get_bloginfo('url') . '/?peepso_email_digest_event'));
		$this->set_field(
				'email_digest_external_cron',
                __('Execute Email Digest email creation via cron job', 'peepso-email-digest'),
                'yesno_switch'
		);

		$this->args('descript', __("Depending on the amount of inactive users in your community, not to overload the site there's a batch creation system. This setting controls how many emails will be generated in one batch. This setting does not send the emails. PeepSo mailqueue takes care of the delivery.", 'peepso-email-digest'));
		$this->set_field(
				'email_digest_per_batch',
                __('How many emails should be created in a batch', 'peepso-email-digest'),
                'text'
		);

		$general_config = apply_filters('peepso_email_digest_general_config', array());

		if (count($general_config) > 0) {

			foreach ($general_config as $option) {
				if (isset($option['descript'])) {
					$this->args('descript', $option['descript']);
				}
				if (isset($option['int'])) {
					$this->args('int', $option['int']);
				}
				if (isset($option['default'])) {
					$this->args('default', $option['default']);
				}

				$this->set_field($option['name'], $option['label'], $option['type']);
			}
		}

		// Build Group
		$this->set_group(
				'general',
                __('General', 'peepso-email-digest')
		);
	}

	private function log() {

		$this->set_field(
				'email_digest_log',
                __('The logs keep latest 100 sent Email Digest emails. You can see which users got the emails and what was their last login when the email was generated and sent to them. You can also preview the email that was sent.', 'peepso-email-digest'),
                'message'
		);

		// Build Group
		$this->set_group(
				'log',
                __('Sent Email Digest Logs', 'peepso-email-digest')
		);
	}

}
