<?php

class PeepSoConfigSectionNotifications extends PeepSoConfigSectionAbstract
{
    // Builds the groups array
    public function register_config_groups()
    {
        // LEFT
        $this->context='left';

        $this->notification_previews();
        $this->post_follow();

        if(PeepSo::is_dev_mode('web_push')) {
            $this->web_push();
        }

        // RIGHT
        $this->context = 'right';
        $this->notification_defaults();

        // BOTTOM
        $this->context='full';
        $this->emails();
    }

    private function notification_previews()
    {

        //$this->args('descript', __('By default the full cover displays only in the header of the "Stream" section'));
        $this->args('default',1);
        $this->set_field(
            'notification_previews',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $options = array();
        for($i=5;$i<=500;$i+=5) {
            $options[$i] = $i;// .' '. __('characters','peepso-core');
        }

        //$options[0] = __('Disabled', 'peepso-core');

        $this->args('default', 50);
        $this->args('options', $options);
        $this->args('descript', __('Notification previews will be trimmed to this length.  To avoid cutting words in the middle, the actual length of a preview might be shorter.','peepso-core'));
        $this->set_field(
            'notification_preview_length',
            __('Preview length', 'peepso-core'),
            'select'
        );

        $this->args('descript', __('If a notification is over the limit, the ellipsis will be attached to the end. The length of the potential ellipsis counts into a total notification preview length.','peepso-core'));
        $this->args('default', '...');
        $this->args('maxlength', 10);
        $this->args('size', 10);
        $this->set_field(
            'notification_preview_ellipsis',
            __('Ellipsis', 'peepso-core'),
            'text'
        );

        // Build Group
        $this->set_group(
            'notification_previews',
            __('On-site notification previews', 'peepso-core')
        );
    }

    private function post_follow()
    {
        $this->args('descript',__('Does not apply to post author'));
        $this->set_field(
            'post_follow_notify_react',
            __('Notify post followers about reactions', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_field(
            'auto_follow_separator',
            __('Automatically follow posts', 'peepso-core'),
            'separator'
        );

        $this->set_field(
            'auto_follow_message',
            __('Decide which actions will trigger a post follow if the user has no prior relationship with a given post','peepso-core'),
            'message'
        );

        $this->set_field(
            'post_auto_follow_comment',
            _x('Comment', 'verb', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_field(
            'post_auto_follow_react',
            _x('React', 'verb', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_field(
            'post_auto_follow_save',
            _x('Save', 'verb', 'peepso-core'),
            'yesno_switch'
        );


        // Build Group
        $this->set_group(
            'autofollow',
            __('Post following & notifications','peepso-core')
        );
    }

    private function web_push() {

        $this->args('descript', __('Enabled: Web Push Notifications option will appear in user preferences', 'peepso-core'));
        $this->set_field(
            'web_push',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->args('descript', __('Defines the default state for all users who never set this notification preference', 'peepso-core'));
        $this->set_field(
            'web_push_user_default',
            __('Enable by default for users', 'peepso-core'),
            'yesno_switch'
        );


        $this->set_field(
            'web_push_private_key',
            __('Private Key', 'peepso-core'),
            'text'
        );



        $this->set_field(
            'web_push_public_key',
            __('Public Key', 'peepso-core'),
            'text'
        );
        $this->args('descript','lorem');
        $this->set_field(
            'web_push_keys_howto_header',
            __('How to generate keys', 'peepso-core'),
            'separator'
        );


        ob_start();
        ?>
        <pre class="ps-js-copy-source">openssl ecparam -genkey -name prime256v1 -out peepso_key.pem && rm -f peepso_pub_key.txt && rm -f peepso_prv_key.txt

openssl ec -in peepso_key.pem -pubout -outform DER|tail -c 65|base64|tr -d '=' |tr '/+' '_-' >> peepso_pub_key.txt

openssl ec -in peepso_key.pem -outform DER|tail -c +8|head -c 32|base64|tr -d '=' |tr '/+' '_-' >> peepso_prv_key.txt

clear && print "\nPrivate key:" && cat peepso_prv_key.txt

print "\n\nPublic key:" && cat peepso_pub_key.txt && print ""

</pre>
        <?php
        $pre = ob_get_clean();

        $this->set_field('web_push_keys_howto',
            "Use a <a href=\"https://www.google.com/search?q=browser+push+key+generator\" target=\"_blank\"> key generator</a> <b>or</b> the following commands (<a href=\"#\" class=\"ps-js-copy-trigger\" data-copy-success=\"copied\">click to copy</a>)"
            . $pre,
            'message'
        );

        // Build Group
        $this->set_group(
            'web_push',
            __('Web Push Notifications', 'peepso-core') . ' (Early Access)'
        );
    }

    private function notification_defaults() {


        $levels = PeepSoNotificationsIntensity::email_notifications_intensity_levels();

        $options = [];
        foreach($levels as $value => $level) {
            $options[$value] = $level['label'] . ' - ' . $level['desc'];
        }

        $this->args('options', $options);
        $this->set_field(
            'default_email_intensity',
            __('Email notification intensity', 'peepso-core'),
            'select'
        );


        $PeepSoProfile = PeepSoProfile::get_instance();
        $alerts = $PeepSoProfile->get_alerts_definition(TRUE);

        foreach($alerts as $group) {

            $this->set_field(
                'default_separator_'.$group['title'],
                $group['title'],
                'separator'
            );

            foreach($group['items'] as $item) {

                $this->set_field(
                    'default_separator_'.$item['setting'],
                    '<h5>'.$item['label'].'</h5>',
                    'message'
                );


                $this->args('default', 1);
                $this->set_field(
                    'default_onsite_'.$item['setting'],
                    __('On-site', 'peepso-core'),
                    'yesno_switch'
                );

                $this->args('default', 1);
                $this->set_field(
                    'default_email_'.$item['setting'],
                    __('Email', 'peepso-core'),
                    'yesno_switch'
                );

                $this->set_field(
                    'default_separator_'.time(),
                    '<br/>',
                    'message'
                );

            }
        }

        // Build Group
        $this->set_group(
            'notification_defaults',
            __('Notification defaults', 'peepso-core'),
            __('These defaults will only be applied to new users','peepso-core')
        );
    }
    private function emails()
    {
        // # Email Sender
        $this->args('validation', array('validate'));
        $this->args('data', array(
            'rule-min-length' => 1,
            'rule-max-length' => 64,
            'rule-message'    => __('Should be between 1 and 64 characters long.', 'peepso-core')
        ));


        $this->set_field(
            'site_emails_sender',
            __('Email sender', 'peepso-core'),
            'text'
        );

        // # Admin Email
        $this->args('validation', array('validate'));
        $this->args('data', array(
            'rule-min-length' => 1,
            'rule-max-length' => 64,
            'rule-message'    => __('Should be between 1 and 64 characters long.', 'peepso-core')
        ));

        $this->args('descript', __('To improve email delivery, do not use a generic address like @gmail.com - instead try using your own domain, like this: no-reply@example.com','peepso-core'));
        $this->set_field(
            'site_emails_admin_email',
            __('Admin Email', 'peepso-core'),
            'text'
        );


        $this->set_field(
            'emails_override_full_separator',
            __('Customize the entire email layout', 'peepso-core'),
            'separator'
        );

        $this->set_field(
            'emails_override_full_msg',
            __('Text, HTML and inline CSS only (no PHP or shortcodes). Leave empty for the default layout.','peepso-core')
            . '<br/><br/>'
            . sprintf(__('<a href="%s" target="_blank">Click here</a> after saving to test your changes.','peepso-core'), admin_url('admin-ajax.php?action=peepso_preview_email'))
            .'<br/><br/>'.
            __('Available variables: <br/>{email_contents} - email contents <font color="red">*</font><br/>{unsubscribeurl} - URL of the user notification preferences <font color="red">*</font><br/>{currentuserfullname} - full name of the recipient<br>{useremail} - email of the recipient<br/>{sitename} - the name of your site<br/>{siteurl} - the URL of your site<br/><br/><font color="red">*</font> required variable', 'peepso-core'),
            'message'
        );

        // # Full HTML
        $this->args('raw', TRUE);
        $this->args('validation', array('custom'));
        $this->args('validation_options',
            [
                [
                    'error_message' => __('Missing variable {emails_contents} or {unsubscribeurl}', 'peepso-core'),
                    'function' => array($this, 'check_emails_layout')
                ],
            ]
        );

        $this->set_field(
            'emails_override_entire_html',
            __('Override entire HTML', 'peepso-core'),
            'textarea'
        );

        // Build Group
        $this->set_group(
            'emails',
            __('Emails', 'peepso-core'),
            __('This section controls the settings and layout of all emails sent by PeepSo: notifications, registration, forgot password etc.','peepso-core')
        );
    }

    public function check_emails_layout($value)
    {
        if (!empty($value)) {
            if (strpos($value, 'email_contents') === false || strpos($value, 'unsubscribeurl') === false) {
                return FALSE;
            }
        }

        return TRUE;
    }

}
