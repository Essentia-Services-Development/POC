<?php

class PeepSoProfileShortcode
{
    public $save_success = FALSE;

    private static $_instance = NULL;

    private $err_message = NULL;
    private $url;
    private $can_access = TRUE;
    private $user_blocked = FALSE;

    private $view_user_id = NULL;					// user id of profile to view
    private $form_message = NULL;					// error message used in forms

    private $preference_tabs = array(
        'edit',
        'pref',
        'notifications',
        'blocked',
    );

    const NONCE_NAME = 'profile-edit-form';
    // const DELETE_NONCE_NAME = 'profile-delete-form';

    public function __construct()
    {
        add_shortcode('peepso_profile', array(&$this, 'do_shortcode'));

        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

        add_action('peepso_save_cover_form', array(&$this, 'save_cover_form'));
        add_action('peepso_save_profile_form', array(&$this, 'save_profile_form'));
        // add_action('peepso_delete_profile_form', array(&$this, 'delete_profile_form'));
        add_action('peepso_save_avatar_form', array(&$this, 'save_avatar_form'));
        add_action('peepso_activity_dialogs', array(&$this, 'upload_dialogs'));

        add_filter('peepso_page_title', array(&$this,'peepso_page_title'));
        add_filter('peepso_user_profile_id', array(&$this, 'user_profile_id'));

        add_action('peepso_profile_segment_about', array(&$this, 'peepso_profile_segment_about'));

		add_filter('peepso_page_title_check', array(&$this, 'peepso_page_title_check'));
		add_filter('peepso_permissions_post_create', array(&$this, 'filter_permissions_post_create'));
    }


    public function get_view_user_id()
    {
        return intval($this->view_user_id);
    }

    public static function description() {
        return __('Displays user profiles. Complete with user profile activity stream, about page, notification preferences and other profile configuration options.
            ','peepso-core');
    }

    public static function post_state() {
        return _x('PeepSo', 'Page listing', 'peepso-core') . ' - ' . __('User profile', 'peepso-core');
    }

    /*
     * return singleton instance of teh plugin
     */
    public static function get_instance()
    {
        PeepSoActivityShortcode::get_instance();					// need the Activity Stream
        if (NULL === self::$_instance)
            self::$_instance = new self();
        return (self::$_instance);
    }


    /*
     * Sets up the page for viewing. The combination of page and exta information
     * specifies which profile to view.
     * @param string $page The 'root' of the page, i.e. 'profile'
     * @param string $extra Optional specifier of extra data, i.e. 'username'
     */
    public function set_page($url)
    {
        if(!$url instanceof PeepSoUrlSegments) {
            return;
        }

        $this->url = $url;

        global $wp_query;

        if ($wp_query->is_404) {
            echo "<h1>404</h1>";
            # $virt = new PeepSoVirtualPage($this->url->get(0), $this->url->get(1));
        }

        if ($this->url->get(1)) {
            $user = get_user_by('slug', $this->url->get(1));

            if (FALSE === $user) {
                $this->view_user_id = get_current_user_id();
            } else {
                $this->view_user_id = $user->ID;
            }
        } else {
            $this->view_user_id = get_current_user_id();
        }

        if (0 === $this->view_user_id) {
            PeepSo::redirect(PeepSo::get_page('activity'));
        }

        $blk = new PeepSoBlockUsers();
        $user = PeepSoUser::get_instance($this->view_user_id);

        $this->user_blocked = $blk->is_user_blocking($this->view_user_id, get_current_user_id(), TRUE);
        $this->can_access = PeepSo::check_permissions($this->view_user_id, PeepSo::PERM_PROFILE_VIEW, get_current_user_id(), TRUE);

        $this->init();
    }

    /*
     * Filter for setting the user id of the page being viewed
     * @param int $id The assumed user id
     * @return int The modified user id, based on the profile page being viewed
     */
    public function user_profile_id($id)
    {
        // this uses the value set in the set_page() method
        if (FALSE !== $this->view_user_id)
            $id = $this->view_user_id;
        return ($id);
    }


    /*
     * shortcode callback for the Registration Page
     * @param array $atts Shortcode attributes
     * @param string $content Contents of the shortcode
     * @return string output of the shortcode
     */

    public function peepso_page_title( $title )
    {
        if ('peepso_profile' == $title['title']) {
            $user = PeepSoUser::get_instance($this->get_view_user_id());

            $links = apply_filters('peepso_navigation_profile', array());
            $tab = '';
            
            if ($links) {
                $PeepSoUrlSegments = PeepSoUrlSegments::get_instance();

                foreach ($links as $key => $value) {
                    if ($key != 'stream' && $value['href'] == $PeepSoUrlSegments->get(2)) {
                        $tab = ' - ' . $value['label'];
                    }
                }
            }

			$title['newtitle'] = $title['title'] = $user->get_fullname() . $tab;
        }

        return $title;
    }

    public function do_shortcode($atts, $content)
    {
        PeepSo::set_current_shortcode('peepso_profile');

        if (FALSE == apply_filters('peepso_access_content', TRUE, 'peepso_profile', PeepSo::MODULE_ID)) {
            return PeepSoTemplate::do_404();
        }

        if(!isset($this->url) || !($this->url instanceof PeepSoUrlSegments)) {
            $this->url = PeepSoUrlSegments::get_instance();
        }

        $PeepSoProfile = PeepSoProfile::get_instance();
        $PeepSoProfile->init($this->view_user_id);

        // use get variables to determine exactly which profile template to run
        $ret = PeepSoTemplate::get_before_markup();

        if ($this->user_blocked || FALSE === $this->can_access) {
            $ret .= PeepSoTemplate::do_404();
        }
        else if ($segment = $this->url->get(2)) {
            ob_start();
            // #5989 let admin decide "about" to be first
            $default = array_key_first(apply_filters('peepso_navigation_profile',['stream'=>'stream']));

            if('stream' == $segment && 'stream' != $default) {
                $ret .= PeepSoTemplate::exec_template('profile', 'profile', ['current'=>'stream'], TRUE);
            }
            do_action('peepso_profile_segment_' . $segment, $this->url);
            $ret .= ob_get_clean();
        } else {
            if ($this->view_user_id !== get_current_user_id()) {
                // prevent duplicate counter
                if (!isset($_SERVER['HTTP_REFERER']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], PeepSo::get_option('page_profile')) === FALSE)) {
                    $usr = PeepSoUser::get_instance(0);
                    $usr->add_view_count($this->view_user_id);
                }
            }
            // #5989 let admin decide "about" to be first
            $default = array_key_first(apply_filters('peepso_navigation_profile',['stream'=>'stream']));

            if('stream' != $default) {
                ob_start();
                do_action('peepso_profile_segment_'.$default, $this->url);
                $ret .= ob_get_clean();
            } else {
                $ret .= PeepSoTemplate::exec_template('profile', 'profile', NULL, TRUE);
            }

        }
        $ret .= PeepSoTemplate::get_after_markup();

        if ($PeepSoProfile->can_edit()) {
            wp_enqueue_style('peepso-fileupload');
            wp_enqueue_script('peepso-fileupload');
        }

        PeepSo::reset_query();

        return ($ret);
    }

    /*
     * Init callback. Checks for post operations
     */
    public function init()
    {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            if (isset($_GET['cover']))
                do_action('peepso_save_cover_form', $this->view_user_id);
            else if (isset($_POST['account']))
                do_action('peepso_save_profile_form', $this->view_user_id);
            else if (isset($_GET['avatar']))
                do_action('peepso_save_avatar_form', $this->view_user_id);

            // else if (isset($_POST['delete_account']))
            //     do_action('peepso_delete_profile_form', $this->view_user_id);
        }
    }

    /*
     * Function called when saving cover photo
     */
    public function save_cover_form($id)
    {
        $input = new PeepSoInput();
        $this->view_user_id = $input->int('user_id');

        if (FALSE === PeepSo::check_permissions($this->view_user_id, PeepSo::PERM_PROFILE_EDIT, get_current_user_id())) {
            $this->err_message = __('You do not have enough permissions.', 'peepso-core');
            return (FALSE);
        } else {

            if (isset($_FILES['filedata'])) {
                $allowed_mime_types = apply_filters(
                    'peepso_profiles_cover_mime_types',
                    array(
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    )
                );

                if (!in_array($_FILES['filedata']['type'], $allowed_mime_types)) {
                    $this->err_message = __('The file type you uploaded is not allowed.', 'peepso-core');
                    return (FALSE);
                }

                if (empty($_FILES['filedata']['tmp_name'])) {
                    $this->err_message = __('The file you uploaded is either missing or too large.', 'peepso-core');
                    return (FALSE);
                }

                $user = PeepSoUser::get_instance($this->view_user_id);
                $user->move_cover_file($_FILES['filedata']['tmp_name']);
                return (TRUE);
            } else {
                $this->err_message = __('No file uploaded.', 'peepso-core');
                return (FALSE);
            }

        }
    }

    /*
     * Function called when saving avatar image
     *
     */
    public function save_avatar_form()
    {
        $input = new PeepSoInput();
        $this->view_user_id = $input->int('user_id');

        if (FALSE === PeepSo::check_permissions($this->view_user_id, PeepSo::PERM_PROFILE_EDIT, get_current_user_id())) {
            $this->err_message = __('You do not have enough permissions.', 'peepso-core');
            return (FALSE);
        } else {
            if (isset($_FILES['filedata'])) {
                $allowed_mime_types = apply_filters(
                    'peepso_profiles_avatar_mime_types',
                    array(
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    )
                );

                if (empty($_FILES['filedata']['tmp_name'])) {
                    $this->err_message = __('The file you uploaded is either missing or too large.', 'peepso-core');
                    return (FALSE);
                }

                if (!in_array($_FILES['filedata']['type'], $allowed_mime_types)) {
                    $this->err_message = __('The file type you uploaded is not allowed.', 'peepso-core');
                    return (FALSE);
                }

                $user = PeepSoUser::get_instance($this->view_user_id);
                $user->move_avatar_file($_FILES['filedata']['tmp_name']);

                return (TRUE);
            } else {
                $this->err_message = __('No file uploaded.', 'peepso-core');
                return (FALSE);
            }
        }
    }

    /*
     * Function called when saving an Edit Profile form
     * @param int $id The user id for the form save operation
     */
    public function save_profile_form($id)
    {
        // The permissions engine is here, but admins aren't explicitly exposed to this form
        if (PeepSo::check_permissions($this->view_user_id, PeepSo::PERM_PROFILE_EDIT, get_current_user_id())) {
            $input = new PeepSoInput();

            $nonce = $input->value('-form-id', '', FALSE); // SQL Safe
            if (FALSE === wp_verify_nonce($nonce, self::NONCE_NAME))
                return;

            // verify that authkey field is empty
            $authkey = $input->value('authkey', '', FALSE); // SQL Safe
            if (!empty($authkey))
                return;

            // verify a valid user id
            $user_id = get_current_user_id();
            if (0 == $user_id) {
				return;
			}

            $PeepSoProfile = PeepSoProfile::get_instance();
            $PeepSoProfile->init($user_id);

            $edit_form = apply_filters('peepso_profile_edit_form_fields', array(), $user_id);

            add_filter('peepso_form_validate_after', array(&$PeepSoProfile, 'change_password_validate_after'), 10, 2);

            $form = PeepSoForm::get_instance();
            $form->add_fields($edit_form);
            $form->map_request();

            if (!$form->validate())
                return (FALSE);

            // create a user instance for this user
            $user = PeepSoUser::get_instance($user_id);
            $password_changed = FALSE;

            if (apply_filters('peepso_user_can_change_password', TRUE)) {
                // update password
                $change_password = $input->raw('change_password');

                $password_changed = FALSE;
                if (!empty($change_password)) {
                    wp_set_password($change_password, $user_id);
                    $password_changed = TRUE;
                }
            }

            // update the WordPress user information
            global $wpdb;
            $new_username = $input->value('user_nicename', '', FALSE); // SQL Safe
            $old_username =  $user->get_username();

            $username_changed = FALSE;
            if( $new_username != $old_username ) {
                $check_existing_username = get_user_by('login', $new_username);
                if (FALSE !== $check_existing_username) {
                    return (FALSE);
                }
                $username_changed = TRUE;
            }

            // TODO: need to check if $new_username is already used and not allow the change - `user_login` does not have a UNIQUE index!
            // TODO: the check for and updating of the `user_login` need to be within a transaction to ensure no duplicate names
            $data_user = array('user_login' => $new_username);
            $ret_update_user = $wpdb->update($wpdb->users, $data_user, array('ID' => $user_id));
            // TODO: don't return, allow the rest of the form contents to be updated and return a form validation error message
            // TODO: $wpdb->update() returns the number of rows updated (1 in this case) or FALSE on error -- not a WP_Error instance

            $data = array('ID' => $user_id);
            $props = array('first_name', 'last_name', 'user_url', 'description');
            foreach ($props as $prop) {
                if (isset($_POST[$prop]))
                    $data[$prop] = $input->raw($prop);
            }
            $data['display_name'] = $input->value('first_name', '', FALSE) . ' ' . $input->value('last_name', '', FALSE); // SQL Safe
            $data['nickname']     = $new_username;
            $data['user_nicename']= $new_username;
			$data['user_email']	  = $input->value('user_email', '', FALSE); // SQL Safe
            $ret = wp_update_user($data);

            if (!is_wp_error($ret)) {

                do_action('peepso_profile_after_save', $user_id);

                if( TRUE === $username_changed || TRUE == $password_changed) {
                    PeepSo::redirect(PeepSo::get_page('activity'));
                    die();
                }

                $PeepSoProfile->edit_form_message(__('Changes successfully saved.', 'peepso-core'));
            }

            remove_filter('peepso_form_validate_after', array(&$PeepSoProfile, 'change_password_validate_after'), 10);
        }
    }

    /*
     * Function called when submit a Delete Profile form
     * @param int $id The user id for the form save operation
     */
    // public function delete_profile_form($id)
    // {
    //     // check permissions
    //     if (PeepSo::check_permissions($this->view_user_id, PeepSo::PERM_PROFILE_EDIT, get_current_user_id())) {
    //         $input = new PeepSoInput();

    //         $nonce = $input->value('-form-id', '', FALSE); // SQL Safe
    //         if (FALSE === wp_verify_nonce($nonce, self::DELETE_NONCE_NAME))
    //             return;

    //         // verify that authkey field is empty
    //         $authkey = $input->value('authkey', '', FALSE); // SQL Safe
    //         if (!empty($authkey))
    //             return;

    //         // verify a valid user id
    //         $user_id = $input->int('profile_user_id');
    //         if (0 === $user_id) {
    //             return;
    //         }

    //         $PeepSoProfile = PeepSoProfile::get_instance();
    //         $PeepSoProfile->init($user_id);

    //         echo("delete");
    //         die();

    //         do_action('peepso_profile_after_delete', $user_id);
    //     }
    // }

    /*
     * Enqueues needed css and javascript files for the Profile page
     */
    public function enqueue_scripts()
    {
        $input = new PeepSoInput();

        wp_enqueue_script('peepso-page-profile',
            PeepSo::get_asset('js/page-profile.min.js'),
            is_user_logged_in() ? array('peepso', 'peepso-hammer') : array('peepso'),
            PeepSo::PLUGIN_VERSION, TRUE);

        add_filter('peepso_data', function( $data ) {
            $id = $this->get_view_user_id();
            $PeepSoUser = PeepSoUser::get_instance($id);

            $profile_data = array(
                'id'                  => $id,
                'username'            => $PeepSoUser->get_username(),
                'name'                => $PeepSoUser->get_fullname(),
                'has_avatar'          => $PeepSoUser->has_avatar() ? TRUE : FALSE,
                'img_avatar'          => $PeepSoUser->get_avatar('full'),
                'img_avatar_default'  => $PeepSoUser->get_default_avatar('full'),
                'img_avatar_original' => $PeepSoUser->get_avatar('orig'),
                'avatar_nonce'        => wp_create_nonce('profile-avatar'),
                'has_cover'           => $PeepSoUser->has_cover() ? TRUE : FALSE,
                'img_cover'           => $PeepSoUser->get_cover(),
                'img_cover_default'   => $PeepSoUser->get_cover_default(),
                'cover_nonce'         => wp_create_nonce('profile-cover'),
                'text_error_filetype' => __('The file type you uploaded is not allowed. Only JPEG, PNG, and WEBP allowed.', 'peepso-core'),
                'text_error_filesize' => sprintf(
                    __('The file size you uploaded is too big. The maximum file size is %s.', 'peepso-core'),
                    '<strong>' . PeepSoGeneral::get_instance()->upload_size() . '</strong>'
                )
            );

            $data['profile'] = array_merge(
                $profile_data,
                array(
                    'template_avatar'               => PeepSoTemplate::exec_template('profile', 'dialog-avatar', array( 'data' => $profile_data ), TRUE),
                    'template_cover_remove'         => PeepSoTemplate::exec_template('profile', 'dialog-cover-remove', array(), TRUE),
                    'template_profile_deletion'     => PeepSoTemplate::exec_template('profile', 'dialog-profile-deletion', array(), TRUE),
                    'template_export_data_request'  => PeepSoTemplate::exec_template('profile', 'dialog-profile-request-account-data', array(), TRUE),
                    'template_export_data_download' => PeepSoTemplate::exec_template('profile', 'dialog-profile-download-account-data', array(), TRUE),
                    'template_export_data_delete'   => PeepSoTemplate::exec_template('profile', 'dialog-profile-delete-account-data-archive', array(), TRUE),
                )
            );

            return $data;
        }, 10, 1);

        $load = array(
            'peepso-window' => 'js/window-1.0.js~jquery',
            'peepso-form' => 'js/form.min.js~jquery,peepso-profile',
        );

        if (PeepSo::is_admin() || get_current_user_id() == $this->view_user_id) {
            $load['peepso-crop'] = 'js/crop.js~jquery,peepso-hammer';
            $load['peepso-profileavatar'] = 'js/profile-edit.js~jquery,peepso-crop';
        }

        if ($input->exists('alerts'))
            $load['peepso-profile-alerts'] = 'js/profile-alerts.js~jquery,peepso';

        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-draggable');
        foreach ($load as $handle => $data) {
            $parts = explode('~', $data, 2);
            $deps = explode(',', $parts[1]);

            wp_register_script($handle, PeepSo::get_asset($parts[0]), $deps, PeepSo::PLUGIN_VERSION, TRUE);
            wp_enqueue_script($handle);
        }

        wp_register_script('peepso-resize', PeepSo::get_asset('js/jquery.autosize.min.js'),
            array('jquery'), PeepSoActivityStream::PLUGIN_VERSION, TRUE);

        wp_enqueue_script('peepso-posttabs');
    }


    /**
     * Returns TRUE of FALSE whether an err_message has been set.
     * @return boolean
     */
    public function has_error()
    {
        return (NULL !== $this->err_message);
    }


    /**
     * Returns the error message as a string.
     * @return string The error message.
     */
    public function get_error_message()
    {
        return ($this->err_message);
    }

    /**
     * callback - peepso_activity_dialogs
     * Renders the dialog boxes for uploading profile and cover photo.
     */
    public function upload_dialogs()
    {
        wp_enqueue_style('peepso-fileupload');
    }

    public function peepso_profile_segment_about()
    {
        $PeepSoUrlSegments = PeepSoUrlSegments::get_instance();

        $template = 'profile-about';
        if($PeepSoUrlSegments->get(3)) {
            $template .= '-' . $PeepSoUrlSegments->get(3);
        }

        $PeepSoUser = PeepSoUser::get_instance($this->get_view_user_id());
        $about_tabs = [
            'about' => [
                'link' => $PeepSoUser->get_profileurl() . 'about/',
                'label' => __('About', 'peepso-core'),
                'icon' => 'gcis gci-user-circle',
            ],
            'preferences' => [
                'link' => $PeepSoUser->get_profileurl() . 'about/preferences/',
                'label' => __('Preferences', 'peepso-core'),
                'icon' => 'gcis gci-cog',
            ],
            'notifications' => [
                'link' => $PeepSoUser->get_profileurl() . 'about/notifications/',
                'label' => __('Notifications', 'peepso-core'),
                'icon' => 'gcis gci-bell',
            ],
            'account' => [
                'link' => $PeepSoUser->get_profileurl() . 'about/account/',
                'label' => __('Account', 'peepso-core'),
                'icon' => 'gcis gci-user',
            ]
        ];

        $tabs = apply_filters('peepso_filter_about_tabs', $about_tabs);

        echo PeepSoTemplate::exec_template('profile', $template, array('tabs' => $tabs));
        add_filter('peepso_data', function( $data ) {
            $data['sections']['profile'] = array(
                'textSaveAllErrorNotice' => __('Some fields were not saved. Please make sure all fields are valid.', 'peepso-core')
            );
            return $data;
        }, 10, 1);
    }

	public function peepso_page_title_check($post) {
		if (isset($post->post_content) && strpos($post->post_content, '[peepso_profile]') !== FALSE) {
			return TRUE;
		}
		return $post;
	}

	/**
	 * todo:docblock
	 */
	public function filter_permissions_post_create($acc) {
		$PeepSoProfile = PeepSoProfile::get_instance();

		if (!$PeepSoProfile->is_current_user()) {
			if (PeepSoUser::is_accessible_static($PeepSoProfile->user->get_profile_post_accessibility(), $this->get_view_user_id())) {
				$acc = TRUE;
			} else {
				$acc = FALSE;
			}
		}

		return $acc;
	}
}

// EOF
