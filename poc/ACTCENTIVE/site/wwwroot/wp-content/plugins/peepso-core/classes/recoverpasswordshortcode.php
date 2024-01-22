<?php

class PeepSoRecoverPasswordShortcode {

    private static $_instance = NULL;

    public function __construct() {}

    public static function get_instance()
    {
        if (NULL === self::$_instance)
            self::$_instance = new self();
        return (self::$_instance);
    }

    public static function description() {
        return __('Displays a password reminder form. Users enter the email they used on registration.','peepso-core');
    }

    public static function post_state() {
        return _x('PeepSo', 'Page listing', 'msgso') . ' - ' . __('Recover password', 'msgso');
    }

    /*
     * Callback function for the Recover Password shortcode
     * @param array $atts Attributes array
     * @param string $content The content within the shortcode
     */

    public function do_shortcode($atts, $content = '')
    {
        PeepSo::do_not_cache();

        PeepSo::set_current_shortcode('peepso_recover');
        $ret = PeepSoTemplate::get_before_markup();
        $input = new PeepSoInput();

        if (is_user_logged_in())
        {
            return PeepSoTemplate::exec_template('profile', 'already-registered', NULL, TRUE);
        }

        if (('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['email'])) || (isset($_GET['email']) && $input->value('nonce', '', false) == md5(session_id())))
        {
            if (1 == PeepSo::get_option('password_reset_nonce_enable', 1) && !isset($_GET['email']) && !wp_verify_nonce($_POST['-form-id'], 'peepso-recover-password-form')) {
                $err = new WP_Error('bad_form', __('Invalid form contents, please resubmit', 'peepso-core'));
            } else {
                $err = $this->retrieve_password($input->value('email','',false));
            }

            if (PeepSo::get_option('site_registration_recaptcha_enable', 0))
            {
                $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
                    'headers' => [
                        'Content-type' => 'application/x-www-form-urlencoded'
                    ],
                    'body' => [
                        'secret' => PeepSo::get_option('site_registration_recaptcha_secretkey', 0),
                        'response' => $input->value('g-recaptcha-response','',FALSE) // SQL safe
                    ]
                ]);
                
                $result = json_decode(wp_remote_retrieve_body($response));

                if ($result->success === FALSE)
                {
                    $err = new WP_Error('bad_form', __('Invalid captcha, please try again', 'peepso-core'));
                }
            }

            if (is_wp_error($err) && 'user_login_blocked' !== $err->get_error_code())
            {
                $ret .= PeepSoTemplate::exec_template('general', 'recover-password', array('error' => $err), TRUE);

                // Enqueue recaptcha script.
                if (PeepSo::get_option('site_registration_recaptcha_enable', 0)) {
                    wp_enqueue_script('peepso-recaptcha');
                }
            } else
            {
                $ret .= PeepSoTemplate::exec_template('general', 'recover-password-sent', NULL, TRUE);
            }
        } else
        {
            $ret .= PeepSoTemplate::exec_template('general', 'recover-password', NULL, TRUE);

            // Enqueue recaptcha script.
            if (PeepSo::get_option('site_registration_recaptcha_enable', 0)) {
                wp_enqueue_script('peepso-recaptcha');
            }
        }
        $ret .= PeepSoTemplate::get_after_markup();

        wp_reset_query();

        // disable WP comments from displaying on page
//        global $wp_query;
//        $wp_query->is_single = FALSE;
//        $wp_query->is_page = FALSE;

        return ($ret);
    }

    /*
     * Creates and sends email based on user information submitted
     * @return multi TRUE if successful, otherwise WP_Error instance
     */

    public function retrieve_password($email)
    {
        $errors = new WP_Error();
        $user_data = NULL;

        if (empty($email)) {
            $errors->add('empty_username', __('<strong>ERROR</strong>: Please enter your email address.', 'peepso-core'));
        } else if (is_email($email)) { 
            $user_data = get_user_by('email', sanitize_email($email));
            if (empty($user_data))
                $errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.', 'peepso-core'));
        }

        if ($errors->get_error_code())
            return ($errors);

        if (empty($user_data)) {
            $errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid email address provided.', 'peepso-core'));
            return ($errors);
        }

        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        // Generate something random for a password reset key.
        $key = get_password_reset_key( $user_data );
        if (is_wp_error($key)) {
            return ($key);
        }

        $peepso_user = PeepSoUser::get_instance($user_data->ID);
        $data = $peepso_user->get_template_fields('user');
        // $data['recover_url'] = site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
        $data['recover_url'] = PeepSo::get_page('reset');
        $data['recover_url'] = add_query_arg( 'key', $key, $data['recover_url'] );
        $data['recover_url'] = add_query_arg( 'login', rawurlencode($user_login), $data['recover_url'] );

        if (is_multisite())
            $blogname = $GLOBALS['current_site']->site_name;
        else
        // The blogname option is escaped with esc_html on the way into the database in sanitize_option
        // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $title = sprintf(__('%s Password Reset', 'peepso-core'), $blogname);
        /**
         * Filter the subject of the password reset email.
         * @since 2.8.0
         * @param string $title Default email title.
         */
        $title = apply_filters('retrieve_password_title', $title);

        PeepSoMailQueue::add_message($user_data->ID, $data, $title, 'password_recover', 'password_recover', PeepSo::MODULE_ID, 1);
        #PeepSoMailQueue::process_mailqueue(1);
        return (TRUE);
    }

}

// EOF
