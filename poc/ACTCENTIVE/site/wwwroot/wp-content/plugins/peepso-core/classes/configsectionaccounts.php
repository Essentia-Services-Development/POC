<?php

class PeepSoConfigSectionAccounts extends PeepSoConfigSectionAbstract {
    const SITE_ALERTS_SECTION = 'site_alerts_';

    public function register_config_groups() {
        $this->set_context( 'left' );
        $this->registration();

        $this->set_context( 'right' );
        $this->usernames();
        $this->security();
        $this->profiles();

    }


    private function profiles() {

        // Force profile completion
        $this->args( 'descript',
            __( 'ON: users have to fill in all required fields before being able to participate in the community.', 'peepso-core' )
            . "<br/>"
            . __( 'Applies to all users.', 'peepso-core' )
        );
        $this->set_field(
            'force_required_profile_fields',
            __( 'Force profile completion', 'peepso-core' ),
            'yesno_switch'
        );

        $this->set_group(
            'profiles',
            __( 'Profiles', 'peepso-core' )
        );
    }

    private function usernames() {

        // Disable username field
        $this->args( 'descript',
            __( 'Username field will not be available during registration.','peepso-core')
            .' '
            . __('PeepSo will automatically generate a safe username for new users.', 'peepso-core' )
        );
        $this->set_field(
            'no_username_on_register',
            __( 'Generate usernames automatically', 'peepso-core' ),
            'yesno_switch'
        );

        // Clean up third party registrations
        $this->args( 'descript',
            __( 'Some plugins (like WooCommerce, EDD and Learndash) create user accounts where the username is an email address. PeepSo uses the usernames to build profile URLs, which can lead to accidental email exposure through site URLs. Enabling this feature will cause PeepSo to step in during third party user registration.','peepso-core')
            . ' '
            . __('PeepSo will automatically generate a safe username for new users.', 'peepso-core' )
        );

        $this->set_field(
            'thirdparty_username_cleanup',
            __( 'Clean up third party registrations', 'peepso-core' ),
            'yesno_switch'
        );

        // Allow Username changes
        $this->args('descript', __('PeepSo uses usernames in user profile URLs (called "vanity URLs") - users usually want to have control over that. Especially if usernames are generated automatically via the options above.','peepso-core'));
        $this->set_field(
            'system_allow_username_changes',
            __( 'Allow username changes', 'peepso-core' ),
            'yesno_switch'
        );

        $this->set_group(
            'usernames',
            __( 'Usernames', 'peepso-core' )
        );
    }
    private function security() {


        $this->set_field('recaptcha_separator', __( 'ReCaptcha', 'peepso-core' ). ' (v2)', 'separator');
        // # Enable ReCaptcha / Register / Reset / Recover
        $this->set_field(
            'site_registration_recaptcha_enable',
            __( 'ReCaptcha during registration', 'peepso-core' ),
            'yesno_switch'
        );

        // # Enable ReCaptcha / Login
        $this->set_field(
            'recaptcha_login_enable',
            __( 'ReCaptcha during login', 'peepso-core' ),
            'yesno_switch'
        );

        // # ReCaptcha Site Key
        $this->set_field(
            'site_registration_recaptcha_sitekey',
            __( 'Site Key', 'peepso-core' ),
            'text'
        );

        // # ReCaptcha Secret Key
        $this->args( 'descript',
            sprintf(__( 'Get Google ReCaptcha keys %s','peepso-core'), sprintf('<a href="https://www.google.com/recaptcha/admin" target="_blank">%s</a>', __('here','peepso-core')))
            . '<br/>' . sprintf(__('Please make sure to use %s','peepso-core'),'<strong>ReCaptcha v2: invisible ReCaptcha badge</strong>')
            . '<br/>'
            . sprintf(__('Having issues or questions? Please refer to the %s or contact %s', 'peepso-core' ), sprintf('<a href="http://peep.so/recaptcha" target="_blank">%s</a>',__('documentation','peepso-core')), sprintf('<a href="https://peep.so/help" target="_blank">%s</a>', __('PeepSo Support','peepso-core')))
        );
        $this->set_field(
            'site_registration_recaptcha_secretkey',
            __( 'Secret Key', 'peepso-core' ),
            'text'
        );

        // # Use ReCaptcha Globally
        $this->args( 'descript',
            __( 'ON: will use "www.recaptcha.net" in circumstances when "www.google.com" is not accessible.', 'peepso-core' ) );
        $this->set_field(
            'site_registration_recaptcha_use_globally',
            __( 'Use ReCaptcha Globally', 'peepso-core' ),
            'yesno_switch'
        );

        /** PASSWORDS **/
        $this->set_field(
            'separator_password_reset',
            __('Passwords', 'peepso-core'),
            'separator'
        );

        // Password length
        $options=array();

        for($i=5;$i<=20;$i+=1) {
            $options[$i]=$i . ' ' . __('characters','peepso-core');
        }

        $this->args('default', 10);
        $this->args('options', $options);
        $this->args('descript', __('Applies only to new passwords.','peepso-core'));
        $this->set_field(
            'minimum_password_length',
            __('Minimum password length', 'peepso-core'),
            'select'
        );

        // Reset delay
        $options=array();
        $options[0] = __('Disabled', 'peepso-core');

        for($i=1;$i<=4;$i++) {
            $options[$i]=gmdate("H:i", $i*60);
        }

        for($i=5;$i<=30;$i+=5) {
            $options[$i]=gmdate("H:i", $i*60);
        }

        $this->args('validation', array('numeric'));
        $this->args('int', TRUE);
        $this->args('default', 0);
        $this->args('options', $options);
        $this->args('descript', __('hours:minutes - time required between password reset attempts','peepso-core'));
        $this->set_field(
            'brute_force_password_reset_delay',
            __('Password reset delay', 'peepso-core'),
            'select'
        );

        /** LOGIN SECURITY **/
        // # Separator Brute Force
        $this->set_field(
            'separator_brute_force',
            __('Login security', 'peepso-core'),
            'separator'
        );

        # Always remember me
        $this->args( 'default', 0 );

        $this->set_field(
            'site_frontpage_rememberme_default',
            __( 'Check "remember me" by default', 'peepso-core' ),
            'yesno_switch'
        );

        # Disable login with username
        $this->args('options', array(
            0 => __('No', 'peepso-core'),
            1 => __('Administrators', 'peepso-core'),
            2 => __('Everyone', 'peepso-core')
        ));

        $this->args('descript',
            __('Improves security by preventing username sign-in; email address is required to log in.','peepso-core')
            .'<br>'.
            __('Intended to apply to all login attempts: PeepSo, WordPress and third party (if proper filter are implemented).','peepso-core')
            .'<br>'.
            __('"Administrators" are any users who have a manage_options cap and/or PeepSo Administrator role.','peepso-core')
        );

        $this->args('default', 0);
        $this->set_field(
            'login_with_email',
            __('Require email to login', 'peepso-core'),
            'select'
        );

        $this->args('descript', __('Recommended.','peepso-core'));
        $this->set_field(
            'brute_force_enable',
            __('Login brute force protection', 'peepso-core'),
            'yesno_switch'
        );


        // Max failed attempts
        $options=array();

        for($i=3;$i<=15;$i+=1) {
            $options[$i]=$i . ' ' . __('failed attempts','peepso-core');
        }

        $this->args('default', 3);
        $this->args('options', $options);
        $this->args('descript', __('Maximum failed attempts allowed.','peepso-core'));
        $this->set_field(
            'brute_force_max_retries',
            __('Block login after', 'peepso-core'),
            'select'
        );

        // Block time
        $options=array();

        for($i=15;$i<=120;$i+=15) {
            $options[$i]=gmdate("H:i", $i*60);
        }

        $this->args('validation', array('numeric'));
        $this->args('int', TRUE);
        $this->args('default', 15);
        $this->args('options', $options);
        $this->args('descript', __('hours:minutes - how long to block login attempts after the above limit is reached.','peepso-core'));
        $this->set_field(
            'brute_force_lockout_time',
            __('Block for', 'peepso-core'),
            'select'
        );

        // Email
        $keys = array(0,1,2,3,4,5,6,7,8,9,10);
        $options=array();
        foreach($keys as $i) {

            if(0==$i) {
                $options[$i] = __('Disabled','peepso-core');
            } else {
                $options[$i]=sprintf(_n('After %s block','After %s blocks', $i,'peepso-core'), $i);
            }
        }
        $this->args('options', $options);
        $this->args('descript', __('Send an email notification to the user, warning them about failed login attempts.','peepso-core'));
        $this->args('default', 0);
        $this->set_field(
            'brute_force_email_notification',
            __('Email Notification', 'peepso-core'),
            'select'
        );

        // Max blocks
        $options=array();

        for($i=1;$i<=10;$i+=1) {
            $options[$i]=$i . ' ' . __('login blocks','peepso-core');
        }

        $this->args('options', $options);
        $this->args('default', 5);
        $this->args('descript', __('Additional security when users block themselves repeatedly.','peepso-core'));
        $this->set_field(
            'brute_force_max_lockout',
            __('Enable additional block after', 'peepso-core'),
            'select'
        );

        // Extend block
        $keys = array(6,12,24,48,72);
        $options=array();
        foreach($keys as $i) {
            $options[$i]=$i . ' ' . __('hours', 'peepso-core');
        }
        $this->args('options', $options);

        $this->args('descript', __('How long to block login attempts when additional security is triggered.','peepso-core'));
        $this->args('default', 24);


        $this->set_field(
            'brute_force_extend_lockout',
            __('Additional block length', 'peepso-core'),
            'select'
        );

        // Reset retries
        $keys = array(24,48,72);
        $options=array();
        foreach($keys as $i) {
            $options[$i]=$i . ' ' . __('hours', 'peepso-core');
        }
        $this->args('options', $options);

        $this->args('descript', __('How long it takes for the system to "forget" about a failed login attempt.','peepso-core'));
        $this->args('default', 24);
        $this->set_field(
            'brute_force_reset_retries',
            __('Reset retries after', 'peepso-core'),
            'select'
        );

        // IP whitelist
        $this->args('raw', TRUE);
        $this->args('descript', __('One per line. ','peepso-core').__('Example IP:','peepso-core').'<br/>8.8.8.8<br/>4.4.4.4');

        $this->set_field(
            'brute_force_whitelist_ip',
            __('IP whitelist', 'peepso-core'),
            'textarea'
        );

        /** CACHING & SECURITY **/
        // # Separator Brute Force

        $this->set_field(
            'separator_security_caching',
            __('Security & caching', 'peepso-core'),
            'separator'
        );

        $this->set_field(
            'separator_security_caching_msg',
            __('These security measures should only be disabled if you are experiencing caching issues and/or unusual errors during login, registration or password reset.','peepso-core'),
            'message'
        );

        # Login nonce
        $this->args('default', 1);
        $this->set_field(
            'login_nonce_enable',
            __('Login nonce check', 'peepso-core'),
            'yesno_switch'
        );

        # registration nonce
        $this->args('default', 1);
        $this->set_field(
            'registration_nonce_enable',
            __('Registration nonce check', 'peepso-core'),
            'yesno_switch'
        );

        # password reset nonce
        $this->args('default', 1);
        $this->set_field(
            'password_reset_nonce_enable',
            __('Password reset nonce check', 'peepso-core'),
            'yesno_switch'
        );

        # allow password preview
        $this->args('default', 0);
        $this->set_field(
            'password_preview_enable',
            __('Allow password preview', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_group(
            'recaptcha',
            __( 'Security', 'peepso-core')
        );
    }


    private function registration() {
        /** GENERAL **/

        // disabled registration
        $this->args( 'descript', __( 'ON: registration through PeepSo becomes impossible and is not shown anywhere in the front-end. Use only if your site is a closed community or registrations are coming in through another plugin.', 'peepso-core' ) );
        $this->args( 'default', 0 );
        $this->set_field(
            'site_registration_disabled',
            __( 'Disable PeepSo registration', 'peepso-core' ),
            'yesno_switch'
        );

        // Redirect WP registration
        $this->args( 'descript', __( 'ON: WordPress registration page will be redirected to the PeepSo registration page.', 'peepso-core' ) );
        $this->set_field(
            'registration_redirect_wp_to_peepso',
            __( 'Redirect WordPress registration', 'peepso-core' ),
            'yesno_switch'
        );

        // Re-enter email
        $this->args( 'default', 1 );
        $this->args( 'descript', __( 'ON: users need to type their email twice, which improves the chance of it being valid and the verification email reaching them.', 'peepso-core' ) );
        $this->set_field(
            'registration_confirm_email_field',
            __( 'Repeat email field', 'peepso-core' ),
            'yesno_switch'
        );

        // Enable Account Verification
        $this->args( 'descript',
            __( 'ON: users register, confirm their email (optional) and must be accepted by an Admin. Users are notified by email when they\'re approved.<br/>OFF: users register, confirm their email (optional) and can immediately participate in your community.', 'peepso-core' )
            . "<br/>"
            . __( 'PeepSo will not apply this step to third party registrations (WooCommerce, EDD, Social Login etc.)', 'peepso-core' )
        );

        $this->set_field(
            'site_registration_enableverification',
            __( 'Admin account verification', 'peepso-core' ),
            'yesno_switch'
        );

        // Enable Secure Mode For Registration
        $this->args( 'descript', __( 'Requires a valid SSL certificate.<br/>Enabling this option without a valid certificate might break your site.', 'peepso-core' ) );
        $this->set_field(
            'site_registration_enable_ssl',
            __( 'Force SSL on registration page', 'peepso-core' ),
            'yesno_switch'
        );


        /** AVATAR ON REGISTRATION **/
        if(FALSE) {
            $this->set_field(
                'separator_avatar',
                __('Avatar in the registration form', 'peepso-core'),
                'separator'
            );

            if (PeepSo::get_option('avatars_wordpress_only', 0)) {
                $this->set_field(
                    'registration_avatars_disabled_message',
                    '<a href="' . admin_url('admin.php?page=peepso_config&tab=appearance#field_avatars_wordpress_only') . '">' . __('The users are unable to upload avatars via PeepSo interface. PeepSo will inherit the avatars from your WordPress site.', 'peepso-core') . '</a>',
                    'message'
                );
                PeepSoConfigSettings::get_instance()->set_option('registration_avatars_enable', 0);
            } else {
                $this->args('options', [0 => __('No', 'peepso-core'), 1 => __('Yes - optional', 'peepso-core'), 2 => __('Yes - required', 'peepso-core'),]);
                $this->set_field(
                    'registration_avatars_enable',
                    __('Enabled', 'msgso'),
                    'select'
                );
            }
        }
        /** ACTIVATION & REDIRECT **/
        // # Separator Email Confirmation
        $this->set_field(
            'separator_emil_confirmation',
            __( 'Activation & Redirect', 'peepso-core' ),
            'separator'
        );


        // Disable email confirmation
        $this->args( 'descript',
            __( 'ON: users don\'t need to confirm their email.', 'peepso-core' )
            . "<br/>"
            . __( 'PeepSo will not apply this step to third party registrations (WooCommerce, EDD, Social Login etc.)', 'peepso-core' )
        );
        $this->set_field(
            'registration_disable_email_verification',
            __( 'Skip email verification', 'peepso-core' ),
            'yesno_switch'
        );

        $options = array(
            0 => __( 'No', 'peepso-core' ),
        );

        for ( $i = 1; $i <= 5; $i ++ ) {
            $options[ $i ] = sprintf( _n( '%d time', '%d times', $i, 'peepso-core' ), $i );
        }

//        $this->args('options', $options);
//        $this->args('descript', __('If enabled, unconfirmed users will receive a repeated activation link', 'msgso'));
//        $this->set_field(
//            'registration_email_verification_resend',
//            __('Resend activation email', 'msgso'),
//            'select'
//        );


//        $options = array();
//
//        for($i=1; $i<=14; $i++) {
//            $options[$i] = sprintf(_n('%d day', '%d days', $i, 'peepso-core'), $i);
//        }
//
//        $this->args('options', $options);
//
//        $this->set_field(
//            'registration_email_verification_resend_period',
//            __('Resend every', 'msgso'),
//            'select'
//        );

        // # Redirect After activations

        $args    = array(
            'sort_order'   => 'asc',
            'sort_column'  => 'post_title',
            'hierarchical' => 1,
            'exclude'      => '',
            'include'      => '',
            'meta_key'     => '',
            'meta_value'   => '',
            'authors'      => '',
            'child_of'     => 0,
            'parent'       => - 1,
            'exclude_tree' => '',
            'number'       => '',
            'offset'       => 0,
            'post_type'    => 'page',
            'post_status'  => 'publish'
        );
        $pages   = get_pages( $args );
        $options = array(
            - 1 => __( 'First known visit', 'peepso-core' ),
            0   => __( 'Home page', 'peepso-core' ) . ': ' . home_url( '/' ),
        );

        $pageredirect = PeepSo::get_option( 'site_activation_redirect' );
        $settings     = PeepSoConfigSettings::get_instance();
        foreach ( $pages as $page ) {
            // handling selected old value (activity/profile)
            if ( $page->post_name == $pageredirect ) {
                //$this->args('default', $page->ID);
                // update option to selected ID
                $settings->set_option( 'site_activation_redirect', $page->ID );
            }

            $options[ $page->ID ] = __( 'Page:', 'peepso-core' ) . ' ' . ( $page->post_parent > 0 ? '&nbsp;&nbsp;' : '' ) . $page->post_title;
        }

        $this->args( 'options', $options );

        $this->set_field(
            'site_activation_redirect',
            __( 'Activation redirect', 'peepso-core' ),
            'select'
        );

        /** T&C **/

        // # Separator Terms & Conditions
        $this->set_field(
            'separator_terms',
            __( 'Terms & Conditions', 'peepso-core' ),
            'separator'
        );

        // # Enable Terms & Conditions
        $this->set_field(
            'site_registration_enableterms',
            __( 'Enabled', 'peepso-core' ),
            'yesno_switch'
        );

        // # T&C Page
        $options = [
            0 => __('None - add text below','peepso-core'),
        ];

        $args = array(
            'depth'                 => 2,
            'child_of'              => 0,
            'selected'              => 0,
            'echo'                  => 1,
            'name'                  => 'page_id',
            'id'                    => '',
            'class'                 => '',
            'show_option_none'      => '',
            'show_option_no_change' => '',
            'option_none_value'     => '',
            'value_field'           => 'ID',
        );

        $pages = get_pages($args);

        $depth = 0;
        $previous_parent = 0;

        foreach($pages as $page) {

            if(!$page->post_parent) {
                $depth = 0;
                $previous_parent = 0;
            } elseif($page->post_parent != $previous_parent) {
                $depth++;
                $previous_parent = $page->post_parent;
            }

            $title = str_repeat( '&nbsp;', $depth * 3 ) . $page->post_title;
            $options[$page->ID] = $title;
        }

        $this->args( 'options', $options );
        $this->args('descript', __('If you select a page, it will open in a new tab. If you write your own text, it will open in an overlay.','peepso-core'));
        $this->set_field(
            'site_registration_terms_page',
            __( 'Page', 'peepso-core' ),
            'select'
        );

        // # Terms & Conditions Text
        $this->args( 'raw', true );

        $this->set_field(
            'site_registration_terms',
            __( 'Terms &amp; Conditions', 'peepso-core' ),
            'textarea'
        );

        /** Privacy Policy **/

        // # Separator Terms & Conditions
        $this->set_field(
            'separator_privacy',
            __( 'Privacy Policy', 'peepso-core' ),
            'separator'
        );

        // # Enable Privacy
        $this->set_field(
            'site_registration_enableprivacy',
            __( 'Enabled', 'peepso-core' ),
            'yesno_switch'
        );

        // PP Page
        $this->args( 'options', $options );
        $this->args('descript', __('If you select a page, it will open in a new tab. If you write your own text, it will open in an overlay.','peepso-core'));
        $this->set_field(
            'site_registration_privacy_page',
            __( 'Page', 'peepso-core' ),
            'select'
        );

        // # Privacy Text
        $this->args( 'raw', true );

        $this->set_field(
            'site_registration_privacy',
            __( 'Privacy Policy', 'peepso-core' ),
            'textarea'
        );

        /** RESEND **/
        // # Separator Advanced
        $this->set_field(
            'separator_resend_confirmation',
            __( 'Advanced', 'peepso-core' ),
            'separator'
        );

        // Resend ON/OFF
        $this->args( 'descript', __( 'PeepSo will resend the activation email a defined amount of times to any users who did not activate their account.', 'peepso-core' ) );
        $this->set_field(
            'resend_activation',
            __( 'Automatically resend activation', 'peepso-core' ),
            'yesno_switch'
        );


        // Resend DELAY
        $options = array();

        if(isset($_GET['resend_activation_debug'])) {
            $options[60] = "DEBUG: ONE MINUTE";
        }

        for ( $i = 1; $i <= 72; $i ++ ) {
            $options[ $i * 3600 ] = sprintf( _n( '%d hour', '%d hours', $i ), $i );
        }



        $this->args( 'options', $options );
        $this->set_field(
            'resend_activation_interval',
            __( 'Every', 'peepso-core' ),
            'select'
        );

        // Resend MAX TRY
        $options = array();

        if(isset($_GET['resend_activation_debug'])) {
            $options[50] = "DEBUG: 50 TIMES";
            $options[100] = "DEBUG: 100 TIMES";
        }

        for ( $i = 1; $i <= 10; $i ++ ) {
            $options[ $i ] = sprintf( _n( '%d attempt', '%d attempts', $i ), $i );
        }

        $this->args( 'options', $options );
        $this->set_field(
            'resend_activation_max_attempts',
            __( 'Maximum', 'peepso-core' ),
            'select'
        );


        /** BLOCK LIST / ALLOW LIST **/

        // # Separator Advanced
        $this->set_field(
            'separator_block_allow_list',
            __( 'Email domains', 'peepso-core' ),
            'separator'
        );

        // # Blacklist Domain
        $this->args('descript', __('When enabled, emails belonging to these domains on the block list will be considered invalid.', 'peepso-core'));
        $this->set_field(
            'limitusers_blacklist_domain_enable',
            __('Block selected domains', 'peepso-core'),
            'yesno_switch'
        );

        // # Predefined  Text
        $this->args('raw', TRUE);
        $this->args('multiple', TRUE);
        $this->args('descript', __('One per line.','peepso-core'));
        $this->set_field(
            'limitusers_blacklist_domain',
            __('Blocked domains', 'peepso-core'),
            'textarea'
        );

        // # Whitelist Domain
        $this->args('descript', __('When enabled, domains on the allow list will NOT be blocked. Emails from other domains will be rejected.', 'peepso-core'));
        $this->set_field(
            'limitusers_whitelist_domain_enable',
            __('Allow only selected domains', 'peepso-core'),
            'yesno_switch'
        );

        // # Predefined  Text
        $this->args('raw', TRUE);
        $this->args('multiple', TRUE);
        $this->args('descript', __('One per line.','peepso-core'));
        $this->set_field(
            'limitusers_whitelist_domain',
            __('Allowed domains', 'peepso-core'),
            'textarea'
        );

        // Build Group
        $this->set_group(
            'registration',
            __( 'Registration', 'peepso-core' )
        );
    }
}

// EOF
