<?php

class PeepSoUser
{
	const TABLE = 'peepso_users';

	const THUMB_WIDTH = 64;
	const IMAGE_WIDTH = 100;

	const COVER_WIDTH = 1140;
	const COVER_HEIGHT = 428;

	const DEFAULT_ROLE = 'member';

	const ACCESS_SETTING = 'peepso_user_access';

	const USERNAME_MAXLEN = 50;
	const USERNAME_MINLEN = 2;

	const FIRSTNAME_MAXLEN = 30;
	const FIRSTNAME_MINLEN = 1;
	const LASTNAME_MAXLEN = 30;
	const LASTNAME_MINLEN = 1;
	const EMAIL_MAXLEN = 320;

	private $default_settings = array(
		'stream_view' => PeepSo::ACCESS_PUBLIC,
		'stream_post' => PeepSo::ACCESS_MEMBERS,
		'stream_like_post' => PeepSo::ACCESS_MEMBERS,
		'stream_comment' => PeepSo::ACCESS_MEMBERS,
		'stream_like_comment' => PeepSo::ACCESS_MEMBERS,
	);

	private $id = NULL;
	private $wp_user = FALSE;
	public $peepso_user = NULL;
	public $profile_fields = NULL;
	private $name_parts = NULL;
    public $avatar;

	static $instances;

    /**
     * @param mixed $id - FALSE default to current user, INT load a specific ID, 0 to load a barebones instance with fields
     * @return PeepSoUser
     */
	public static function get_instance($id = FALSE) {

	    // default to current user ID
	    if(FALSE == $id) {
            $id = get_current_user_id();
        }

        // if user ID is 0 we might just want access to some methods and profile fields
	    if(!isset(self::$instances[$id])) {
            self::$instances[$id] = new self($id, FALSE);
        }

        return self::$instances[$id];
    }

	/**
	 * Constructor
	 * @param int $id The user id to create this instance for
	 */
	private function __construct($id = 0)
    {
		if ($id > 0) {
			$this->id = $id;
			$this->wp_user = get_user_by('id', $id);

			if (FALSE === $this->wp_user)
				return (FALSE);

			global $wpdb;
			$sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "` " .
				" WHERE `usr_id`=%d ";
			$res = $wpdb->get_results($wpdb->prepare($sql, $id), ARRAY_A);
			if (count($res) == 0) {
				// if peepso_users record doesn't exist, create it
				$data = array(
					'usr_id' => $id,
					'usr_profile_acc' => PeepSo::ACCESS_PUBLIC,
				);
				$wpdb->insert($wpdb->prefix . self::TABLE, $data);
				$res = $wpdb->get_results($wpdb->prepare($sql, $id), ARRAY_A);
			}
			if (count($res) > 0) {
				$this->peepso_user = $res[0];

				if ($this->id == get_current_user_id()) {
					$this->update_last_activity($this->id);
				}

			}
		}

		$this->profile_fields = new PeepSoProfileFields($this);
	}

    /**
     * Check if user has a custom cover
     * @return bool
     */
    public function has_cover()
    {
        $cover_hash = get_user_meta($this->get_id(), 'peepso_cover_hash', TRUE);
        if($cover_hash) {
            $cover_hash = $cover_hash . '-';
        }

        $filename = $cover_hash . 'cover.jpg';

        if (file_exists($this->get_image_dir() . $filename)) {
            return (TRUE);
        }
        return (FALSE);
    }

	/**
	 * Get current user's cover image.
	 *
	 * @param int $size
	 * @return string
	 */
    public function get_cover($size=0)
    {
        $cover = NULL;

        $cover_hash = get_user_meta($this->get_id(), 'peepso_cover_hash', TRUE);
        if($cover_hash) {
            $cover_hash = $cover_hash . '-';
        }

        $filename = $cover_hash . 'cover.jpg';

        if (file_exists($this->get_image_dir() . $filename)) {
            $cover = $this->get_image_url() . $filename;

            if (is_int($size) && $size > 0) {
                $filename_scaled = $cover_hash . 'cover-' . $size . '.jpg';
                if (!file_exists($this->get_image_dir() . $filename_scaled)) {
                    $si = new PeepSoSimpleImage();
                    $si->convert_image($this->get_image_dir() . $filename);
                    $si->load($this->get_image_dir() . $filename);
                    $si->resizeToWidth($size);
                    $si->save($this->get_image_dir() . $filename_scaled, IMAGETYPE_JPEG);
                }

                $cover = $this->get_image_url() . $filename_scaled;
            }
        }

        if (empty($cover)) {
			$cover = $this->get_cover_default();
        }

        return $cover;
    }

	/**
	 * Get current user's default cover image.
	 *
	 * @return string
	 */
	public function get_cover_default()
	{
		if ($gender = PeepSoField::get_field_by_id('gender', $this->id) ) {
			$gender = $gender->value;
		}

		// get default image based on gender
		switch ($gender)
		{
			case 'm':	$file = 'male';			break;
			case 'f':	$file = 'female';		break;
			default:	$file = 'undefined';	break;
		}

		return PeepSo::get_asset('images/cover/' . $file . '-default.png');
	}

    public function get_cover_position()
    {
        $ret = '';

        $x = get_user_meta($this->id, 'peepso_cover_position_x', TRUE);
        $y = get_user_meta($this->id, 'peepso_cover_position_y', TRUE);


        if ($x) {
            $ret .= 'top: ' . $x . '%;';
        }
        else {
            $ret .= 'top: 0;';
        }

        if ($y) {
            $ret .= 'left: ' . $y . '%;';
        }
        else {
            $ret .= 'left: 0;';
        }

        return $ret;
    }

    public function get_online_status()
    {
        return PeepSo3_Mayfly::get('peepso_cache_'.$this->id.'_online');
    }

	/**
	 * Return user's avatar image
	 * @param string $suffix 'full' or 'orig;
	 * @return string The href value for the avatar image suitible for use in an <img> tag
	 */
	public function get_avatar($suffix ='full')
	{
		if( 1 === PeepSo::get_option('avatars_wordpress_only', 0) ) {
			$avatar = get_avatar($this->get_id(), PeepSo::get_option('avatar_size', 250));
			if (strpos($avatar, '<img') !== FALSE) {
				preg_match("/\<img.+src\=(?:\"|\')(.+?)(?:\"|\')(?:.+?)\>/", $avatar, $matches);
				if (isset($matches[1])) {
					return $matches[1];
				}
			}
		}

		if (get_user_meta($this->id, 'peepso_use_gravatar', TRUE) == 1 && PeepSo::get_option('avatars_gravatar_enable') == 1) {
			return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->get_email()))) . '?s=160&r=' . strtolower(get_option('avatar_rating'));
		}

		$avatar_hash = get_user_meta($this->get_id(),'peepso_avatar_hash', TRUE);
		if($avatar_hash) {
			$avatar_hash = $avatar_hash . '-';
		}

		$filter_suffix = $suffix;
		if(strlen($suffix)) { $suffix ="-$suffix"; }

		$file = $this->get_image_dir() . $avatar_hash . 'avatar' . $suffix . '.jpg';

		// $hash = $this->get_asset_hash();

		if (!file_exists($file)) {
			$this->set_avatar_custom_flag(FALSE);
			$file = $this->get_default_avatar($suffix);

			if(PeepSo::get_option_new('avatars_name_based')) {
			    $file = PeepSo3_Users_Utils::svg_avatar($this->id);
            }
		} else {
			$this->set_avatar_custom_flag(TRUE);
			$file = $this->get_image_url() . $avatar_hash . 'avatar' . $suffix . '.jpg';
            $file = apply_filters('peepso_filter_user_avatar', $file, $this, $filter_suffix);
		}

		// $file.='?'.$hash;
		return ($file);
	}

    /**
     * Checks whether a user has an avatar
     * @return boolean
     */
    public function has_avatar()
    {
        return ($this->peepso_user['usr_avatar_custom']);
	}

    /**
     * Return default avatar for current user.
     *
     * @param boolean $full
     * @return string
     */
    public function get_default_avatar($full = TRUE) {
		if ($gender = PeepSoField::get_field_by_id('gender', $this->id)) {
			$gender = $gender->value;
		}

		switch ($gender)
		{
			case 'm':		$gender = 'male';		break;
			case 'f':		$gender = 'female';		break;
			default:		$gender = 'neutral';	break;
		}

		if(PeepSo::get_option_new('avatars_name_based')) {
			$file = PeepSo3_Users_Utils::svg_avatar($this->id);
		} else {
			$file = PeepSo::get_asset('images/avatar/user-' . $gender . ($full ? '' : '-thumb') . '.png');
		}

		$file = apply_filters('peepso_filter_user_avatar_default', $file, $this, $full);

		return $file;
    }

	function set_avatar_custom_flag( $flag = TRUE )
	{
		global $wpdb;
		$flag = ( TRUE === $flag) ? TRUE : FALSE;

		$data = array('usr_avatar_custom' => $flag);

		$wpdb->update($wpdb->prefix . self::TABLE, $data, array('usr_id' => $this->id));

	}

	/*
	 * Return user's temporary avatar image
	 * @param boolean $full TRUE to return full-size image or FALSE for small image
	 * @return string The href value for the avatar image suitible for use in an <img> tag
	 */
	public function get_tmp_avatar($full = TRUE)
	{
		$file = $this->get_image_dir() . 'avatar' . ($full ? '-full' : '') . '-tmp.jpg';

		if (!file_exists($file)) {
			// fallback to male/female image if no avatar
			if($gender= PeepSoField::get_field_by_id('gender', $this->id)) {
				$gender = $gender->value;        // gender returns FALSE if gender is inaccessible
			}

			switch ($gender)
			{
				case 'm':		$gender = 'male';		break;
				case 'f':		$gender = 'female';		break;
				default:		$gender = 'neutral';	break;
			}
			$file = PeepSo::get_asset('images/user-' . $gender . ($full ? '' : '-thumb') . '.png');
		} else {
			$file = $this->get_image_url() . 'avatar' . ($full ? '-full' : '') . '-tmp.jpg';
		}

		return ($file);
	}


	public function get_profile_accessibility()
	{
		return $this->peepso_user['usr_profile_acc'];
	}

	public function get_profile_post_accessibility()
	{
		$acc = 0;

		// if admin allows users to decide
		if (1 == PeepSo::get_option('site_profile_posts_override', 1)) {
			$acc = intval(get_user_meta($this->get_id(), 'peepso_profile_post_acc', TRUE));
		}

		// fallback to system settings
		if(0 == $acc) {
			$acc = PeepSo::get_option('site_profile_posts', PeepSo::ACCESS_MEMBERS);
		}

		return $acc;
	}

	/*
	 * Determine if data is accessible by the current user
	 * @param string $col_name The name of the column data to check
	 * @return Boolean TRUE for accessible and FALSE for not accessible
	 */
	public function is_accessible($data, $acc = FALSE)
	{
		// if the user is checking their own information -- always return TRUE
		// if user is an admin - always return TRUE
		if (get_current_user_id() === intval($this->id) || PeepSo::is_admin()) {
			return TRUE;
		}

		if( FALSE == $acc ) {
			$data_acc = $data."_acc";

			$acc = 40;

			if(isset($this->peepso_user['usr_'.$data_acc])) {
				$acc = $this->peepso_user['usr_' . $data_acc];
			}
		}

		return self::is_accessible_static($acc, $this->get_id());
	}

	public static function is_accessible_static($acc, $user_id)
	{
		// if the user is checking their own information -- always return TRUE
		// if user is an admin - always return TRUE
		if (get_current_user_id() === intval($user_id) || PeepSo::is_admin()) {
			return TRUE;
		}

		// to start, assume FALSE
		$ret = FALSE;

		$acc = intval($acc);

		// check based on access type
		switch ($acc)
		{
			case 0:
			case PeepSo::ACCESS_PUBLIC:
				$ret = TRUE;
				break;

			case PeepSo::ACCESS_MEMBERS:
				if (is_user_logged_in())
					$ret = TRUE;
				break;

			case PeepSo::ACCESS_PRIVATE:
				// fall through and return FALSE
				break;
		}

		// run the calculated value through filter to allow add-ons a chance to modify it
		return (apply_filters('peepso_user_is_accessible', $ret, $acc, $user_id));
	}


	private function _get_prop($data, $check_acc = TRUE)
	{
		$col_name = 'usr_' . $data;
		$acc_name = $col_name . '_acc';			// name of access column in peepso_users table

		if ($check_acc) {
			// if there's an access column, check it
			if (isset($this->peepso_user[$acc_name])) {
				if (!$this->is_accessible($data))
					return (FALSE);
			}
		}

		// no access restriction requested or no access column, feel free to return the data
		if (isset($this->peepso_user[$col_name]) && ('peepso_user'!=substr($data,0,11))) {
			// the column name exists in peepso_users, so return it
			return ($this->peepso_user[$col_name]);
		} else {
			// the column name doesn't exist in peepso_users so try wp_user
			if (isset($this->wp_user->$data)) {
				return ($this->wp_user->$data);
			}
		}
		return (FALSE);
	}

	/**
	 * Get the name parts according to the site config
	 *
	 * @return array parts of the user name:
	 * 			firstname+lastname 	if enabled or wp-admin
	 * 			username			if disabled or empty
	 *
	 * Last edit: 	Matt Jaworski
	 * Date:		23.03.2016
	 * Reason:		#812 refactoring of the name getters
	 */
    private function get_name_parts($is_admin = FALSE, $override = FALSE)
    {
        // @since 2.8.0.0 an override can be passed to generate previews of each option
        if($override) {
            $display_name_as = $override;
        } else {
            // If the values are already loaded, don't bother the database again
            // Unless admin, always load just in case
            if( !$is_admin && NULL !== $this->name_parts ) {
                return $this->name_parts;
            }

            $display_name_as = 'real_name';

            // If not in wp-admin, load real names or username depending on the config
            if( !$is_admin ) {
                $display_name_as = $this->get_display_name_as_setting();
            }
        }

        if( 'username' != $display_name_as)
        {
            $name_parts= array(
                $this->_get_prop('first_name', FALSE),
                $this->_get_prop('last_name', FALSE),
            );

            if('real_name_first'==$display_name_as) {
                unset($name_parts[1]);
            }

            if('real_name_first_last_initial'==$display_name_as && isset($name_parts[1]) && strlen($name_parts[1])) {
                if(function_exists('mb_substr')) {
                    $name_parts[1] = mb_substr($name_parts[1],0,1).'.';
                } else {
                    $name_parts[1] = $name_parts[1][0] . '.';
                }
            }
        }

        // If the array is empty or the firstname is empty, fallback to username
        if(!isset($name_parts) || !strlen($name_parts[0])) {

            $name_parts = array(
                $this->get_username(),
            );

        } else {
            if($is_admin) {
                $name_parts[]='('.$this->get_username().')';
            }
        }

        $name_parts = apply_filters('peepso_get_name_parts', $name_parts, $this->id);

        // Do not cache if loading an override
        if(!$override) {
            $this->name_parts = $name_parts;
        }

        return $name_parts;
    }

    /**
     * Returns the firstname and lastname glued together
     */
    public function get_fullname($is_admin = FALSE, $override = FALSE)
    {
        $name_parts = $this->get_name_parts($is_admin, $override);
        $name = implode(' ', $name_parts);

        if($this->wp_user instanceof WP_User && $this->wp_user->display_name != $name && 2==PeepSo::get_option_new('use_name_everywhere')) {
            wp_update_user( array( 'ID' => $this->wp_user->ID, 'display_name' => $name ) );
        }

        return $name;
    }

    /**
     * Returns the firstname and lastname glued together
     * @return string before name addon + firstname+lastname or username + after name addon
     *
     */
    public function get_fullname_with_addons()
    {
        ob_start();
        //[peepso]_[action]_[WHICH_PLUGIN]_[WHERE]_[WHAT]_[BEFORE/AFTER]
        do_action('peepso_action_render_user_name_before', $this->get_id());

        echo $this->get_fullname();

        //[peepso]_[action]_[WHICH_PLUGIN]_[WHERE]_[WHAT]_[BEFORE/AFTER]
        do_action('peepso_action_render_user_name_after', $this->get_id());

        $res = ob_get_clean();


        return $res;
    }

    /**
     * Returns the firstname if accessible and set, or username
     * @return string firstname, username or empty string
     *
     * Last edit: 	Matt Jaworski
     * Date:		23.03.2016
     * Reason:		#812 refactoring of the name getters
     */
    public function get_firstname()
    {
        $display_name_as = $this->get_display_name_as_setting();

        if( 'username' != $display_name_as) {
            $display_name_as = 'real_name_first';
        }

        $name_parts = $this->get_name_parts(FALSE, $display_name_as);
        return isset($name_parts[0]) ? $name_parts[0] : '';
    }

    private function get_display_name_as_setting() {
        if (1 === intval(PeepSo::get_option('system_override_name', 0))) {
            // Are users allowed to override name setting?
            $display_name_as = $this->get_display_name_as();
        } else {
            // Otherwise get the global config
            $display_name_as = PeepSo::get_option('system_display_name_style', 'username');
        }

        return $display_name_as;
    }

	/**
	 * Returns the lastname, if accessible
	 * @return string lastname or empty string
	 * @deprecated
	 *
	 * Last edit: 	Matt Jaworski
	 * Date:		23.03.2016
	 * Reason:		#812 refactoring of the name getters
	 */
	private function get_lastname()
	{
		$name_parts = $this->get_name_parts();
		return isset($name_parts[1]) ? $name_parts[1] : '';
	}

	/**
	 * Returns the wordpress username
	 * @return mixed  The user login or FALSE if no user ID
	 *
	 * Last edit: 	Matt Jaworski
	 * Date:		23.03.2016
	 * Reason:		#812 refactoring of the name getters
	 */
	public function get_username()
	{
		if($this->_wp_user()) {
			return ($this->wp_user->user_login);
		}

		return FALSE;
	}

	/**
	 * Returns the wordpress username
	 * @return mixed The user login or FALSE if no user ID
	 *
	 * Last edit: 	Matt Jaworski
	 * Date:		23.03.2016
	 * Reason:		#812 refactoring of the name getters
	 */
	public function get_nicename()
	{
		if($this->_wp_user()) {
			return ($this->wp_user->user_nicename);
		}

		return FALSE;
	}

	/**
	 * Returns the email that was used to register
	 * @return string The user's email or FALSE if no user ID
	 *
	 * Last edit: 	Matt Jaworski
	 * Date:		23.03.2016
	 * Reason:		#812 refactoring of the name getters
	 */
	public function get_email()
	{
		if($this->_wp_user()) {
			return ($this->wp_user->user_email);
		}
		return FALSE;
	}

	private function _wp_user()
	{
		if(!$this->wp_user)
		{
			$this->wp_user = get_user_by('id', $this->id);
		}

		return $this->wp_user;
	}

	/**
	 * Returns the date of last login of the user
	 * @param  boolean $check_acc Whether or not to check if the viewing user has access to this object
	 * @return mixed The date of last login if has access, otherwise FALSE
	 */
	public function get_last_online()
	{
		$last_online = $this->_get_prop('last_activity', FALSE);

		if ('0000-00-00 00:00:00' === $last_online)
			return (__('Never', 'peepso-core'));

		return ($last_online);
	}

	/**
	 * Returns the registration date of the user
	 * @return string The user's registration date
	 */
	public function get_date_registered()
	{
		// TODO: this is okay for now, but we need to determine why wp_user was not created and make sure it gets created
		if (FALSE === $this->wp_user)
			return ('');
		$dt = $this->wp_user->user_registered;
		return ($dt);
	}

	/**
	 * Return the roles assigned to the user
	 * @deprecated since Jan 2015. Use get_user_role() instead
	 * @return string The user's Wordpress roles
	 */
	public function get_role()
	{
		// TODO: this is okay for now, but we need to determine why wp_user was not created and make sure it gets created
		if (FALSE === $this->wp_user)
			return ('');
		return ($this->wp_user->roles);
	}

	/**
	 * Get's the role information from the `peepso_users` table column `usr_role`
	 * @return string The user's role. One of 'user','member','moderator','admin','ban','register'
	 */
	public function get_user_role()
	{
		return ($this->_get_prop('role', FALSE));
	}

	/**
	 * Changes the user's role to the specified value
	 * @param string $role The new role to change the user to
	 */
	public function set_user_role($role)
	{
		global $wpdb;

		if($wpdb->update($wpdb->prefix . self::TABLE, array('usr_role' => $role), array('usr_id' => $this->id))) {
            do_action('peepso_action_user_role_change', $this->id, $role);
        }
	}

	public static function update_last_activity($user_id)
	{
		add_action('wp_footer', function() use($user_id) {
			global $wpdb;
			$mayfly = 'peepso_cache_' . $user_id . '_online';
			if ( ! PeepSo3_Mayfly::get( $mayfly ) ) {
				if ( 1 != get_user_meta( $user_id, 'peepso_hide_online_status', true ) ) {
					PeepSo3_Mayfly::set( $mayfly, 1, 180 );
				}

				$wpdb->update( $wpdb->prefix . self::TABLE, array( 'usr_last_activity' => current_time( 'mysql' ) ), array( 'usr_id' => $user_id ) );
			}
		});
	}


	public function is_online()
	{
		$mayfly = 'peepso_cache_'.$this->id.'_online';
		return (PeepSo3_Mayfly::get($mayfly)) ? TRUE : FALSE;
	}

	/**
	 * Return the user's ID
	 * @return int The user ID field
	 */
	public function get_id()
	{
		return intval($this->id);
	}

	/*
	 * Get the user's profile page URL
	 * @return string The user's profile URL
	 */
	public function get_profileurl()
	{
	    if(1 == PeepSo::get_option('disable_questionmark_urls', 0)) {
            $page = PeepSo::get_page('profile') . urlencode($this->get_nicename()) . '/';
        } else {
            $page = PeepSo::get_page('profile') .'?'. urlencode($this->get_nicename()) . '/';
        }

		return (apply_filters('peepso_username_link', $page, $this->id));
	}

	public static function set_stream_filters($input) {
	    if($input instanceof PeepSo3_Input) {

	        // stream id
            $default = PeepSo::get_option('stream_id_default');
            $stream_id_list = apply_filters('peepso_stream_id_list', array());

            $stream_id = $input->value('stream_id', $default, array_keys($stream_id_list));

            if(!isset($stream_id_list[$stream_id]) && !array_key_exists($stream_id, $stream_id_list)) {
                $stream_id = $default;
            }

            if(!isset($stream_id_list[$stream_id]) && !array_key_exists($stream_id, $stream_id_list)) {
                reset($stream_id_list);
                $stream_id = key($stream_id_list);
            }

            // Set usermeta preference
            if(PeepSo::get_option('stream_id_sticky', 0)) {
                update_user_meta(get_current_user_id(), 'peepso_stream_id', $stream_id);
            } else {
                delete_user_meta(get_current_user_id(), 'peepso_stream_id');
            }

            // show my posts
            $show_my_posts_list =  apply_filters( 'peepso_show_my_posts_list', [] );
            reset($show_my_posts_list);
            $default = key($show_my_posts_list);
            $show_my_posts = $input->value('stream_filter_show_my_posts', $default, array_keys($show_my_posts_list)); // SQL Safe
            if(!array_key_exists($show_my_posts, $show_my_posts_list)) {
                $show_my_posts = $default;
            }
            update_user_meta(get_current_user_id(), 'peepso_stream_filter_show_my_posts', $show_my_posts);

            // sort by latest post or recently commented
            $sort_posts =  apply_filters( 'peepso_stream_sort_list', [] );
            $sort_default = PeepSo::get_option_new('stream_sort_default');

            $sort_by = $input->value('stream_filter_sort_by', $sort_default, array_keys($sort_posts)); // SQL Safe

            update_user_meta(get_current_user_id(), 'peepso_stream_filter_sort_by', $sort_by);
        }
    }

    /**
     * Return the stream filters for the current user
     * @return array|null
     */
	public static function get_stream_filters() {
	    $r = NULL;

	    if($user_id = get_current_user_id()) {

	        $r = array();

	        /** STREAM ID - relates to peepso_stream_id_list **/

	        // Get usermeta preference


            if(PeepSo::get_option('stream_id_sticky', 0)) {
                $r['stream_id'] = get_user_meta($user_id, 'peepso_stream_id', TRUE);
            }

            // Force revalidation
            if(!isset($r['stream_id']) || !strlen($r['stream_id'])) {
                $r['stream_id'] = PeepSo::get_option('stream_id_default', 'core_community');
            }

            // Set usermeta preference
            if(PeepSo::get_option('stream_id_sticky', 0)) {
                update_user_meta(get_current_user_id(), 'peepso_stream_id', $r['stream_id']);
            } else {
                delete_user_meta(get_current_user_id(), 'peepso_stream_id');
            }

            /** STREAM FILTER - SHOW MY POSTS **/
            $r['show_my_posts'] = get_user_meta($user_id, 'peepso_stream_filter_show_my_posts', TRUE);

            // Force revalidation
            $show_my_posts_list =  apply_filters( 'peepso_show_my_posts_list', [] );
            reset($show_my_posts_list);
            $default = key($show_my_posts_list);
            if(!array_key_exists($r['show_my_posts'], $show_my_posts_list)) {
                $r['show_my_posts'] = $default;
            }

            // Set usermeta preference
            update_user_meta(get_current_user_id(), 'peepso_stream_filter_show_my_posts', $r['show_my_posts']);

            /** STREAM FILTER - SORT BY **/
            $r['sort_by'] = get_user_meta($user_id, 'peepso_stream_filter_sort_by', TRUE);

            // Force revalidation
            $sort_posts =  apply_filters( 'peepso_stream_sort_list', [] );
            $sort_default = PeepSo::get_option_new('stream_sort_default');
            if(!array_key_exists($r['sort_by'], $sort_posts)){
                $r['sort_by'] = $sort_default;
            }

            // Set usermeta preference
            update_user_meta(get_current_user_id(), 'peepso_stream_filter_sort_by', $r['sort_by']);
        }

        return $r;
    }


	/*
	 * creates a WordPress user and "marks" it as a PeepSo user
	 * @param string $fname The new user's first name
	 * @param string $lname The new user's last name
	 * @param string $uname The new user's username
	 * @param string $email The new user's email address
	 * @param string $passw The new user's password
	 * @param string $gender The new user's gender (Optional)
	 * @return multi The new user's id on success or FALSE on error
	 */
	public function create_user($fname, $lname, $uname, $email, $passw, $gender = 'u')
	{
		$default_role = apply_filters('peepso_user_default_role', 'register');

		// sanitize user name, removing non-allowed characters
		$uname = sanitize_user($uname, TRUE);
		#$uname = str_replace('@', '', $uname);
		$uname = str_replace('*', '', $uname);
		$uname = str_replace(' ', '', $uname);

		// sanitize first name and last name
		$fname = sanitize_text_field(strip_tags($fname));
		$lname = sanitize_text_field(strip_tags($lname));

		// sanitize email
		$email = sanitize_email($email);

		// create the peepso_users table record
		$data = array(
			'user_login' 		=> $uname,
			'user_pass' 		=> $passw,
			'user_nicename' 	=> $uname,
			'user_email' 		=> $email,
			'first_name' 		=> $fname,
			'last_name' 		=> $lname,
			'user_registered' 	=> current_time('mysql'),
			'role' 				=> get_option('default_role'),
		);

		$id = wp_insert_user($data);

		if (is_wp_error($id)) {
			return (FALSE);
		}

		// create the PeepSo user
		global $wpdb;
		$sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "` " .
			" WHERE `usr_id`=%d ";
		$res = $wpdb->get_results($wpdb->prepare($sql, $id), ARRAY_A);
		if (count($res) == 0) {
			// if peepso_users record doesn't exist, create it
			$data = array(
				'usr_id' => $id,
				'usr_last_activity' => current_time('mysql'),
				'usr_role' => $default_role,
				'usr_profile_acc' => PeepSo::ACCESS_PUBLIC,
			);
			$wpdb->insert($wpdb->prefix . self::TABLE, $data);
			$res = $wpdb->get_results($wpdb->prepare($sql, $id), ARRAY_A);
		}

		$this->id = $id;
//		$this->profile_fields=new PeepSoProfileFields($this);
//		$this->profile_fields->create_user();
//		$this->profile_fields->set('gender', $gender);

		// create the user's directory directory
		$temp_id = $this->id;
		$this->id = $id;

		$user_dir = $this->get_image_dir();
		$parent_dir = dirname($user_dir);
		if (!file_exists($parent_dir)) {
			@mkdir($parent_dir);
        }
		@mkdir($user_dir);

		$this->reset_user_digest();
		$this->reset_user_notification();

		return ($id);
	}

	public function reset_user_digest() {
		if ($this->id > 0) {
		    update_user_meta($this->id, 'peepso_email_digest_receive_enabled', PeepSo::get_option('email_digest_default', 1));
		}
	}

	public function reset_user_notification() {
	    if($this->id > 0) {
            $notifications = array();

            $PeepSoProfile = PeepSoProfile::get_instance();
            $alerts = $PeepSoProfile->get_alerts_definition(TRUE);

            if(count($alerts)) {

                foreach ($alerts as $group) {

                    if (count($group['items'])) {

                        foreach ($group['items'] as $item) {

                            if (!PeepSo::get_option('default_onsite_' . $item['setting'], 1)) {
                                $notifications[] = $item['setting'] . '_notification';
                            }

                            if (!PeepSo::get_option('default_email_' . $item['setting'], 1)) {
                                $notifications[] = $item['setting'] . '_email';
                            }
                        }
                    }
                }
            }

            update_user_meta($this->id, 'peepso_notifications', $notifications);
        }
    }


	/*
	 * Sends activation email with code to new user
	 * @param string $email The email address of the user
	 * @param int $now If set to 1 the email will be sent immediately
	 */
	public function send_activation($email,$now = 0, $trigger = NULL)
	{
		if (empty($this->wp_user->user_activation_key)) {
			$key = md5(wp_generate_password(20, FALSE) . $this->id . time());

			// update the database
			global $wpdb;
			$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('ID' => $this->id));
		} else {
			$key = $this->wp_user->user_activation_key;
		}

		do_action('retrieve_password_key', $this->get_username(), $key);		// let others know

		$data = $this->get_template_fields('user');
		$data['activation'] = $key;
		$data['activatelink'] = PeepSo::get_page('register') . '?community_activate&community_activation_code=' . $key;
		$data['useremail'] = $email;
		$data['userfullname'] = ($data['userfullname'] !== FALSE) ? $data['userfullname'] : $data['userfirstname'] . ' ' . $data['userlastname'];
		$data['userfirstname'] = isset($data['userfirstname']) ? $data['userfirstname'] : $data['userlogin'];
		// Save the key as meta so this user is searchable.
        add_user_meta($this->id, 'peepso_activation_key', $key, TRUE);

//		$content = PeepSoMailQueue::add_message($this->id, $data, sprintf(__('Welcome to %s', 'peepso-core'), get_bloginfo('sitename')), 'new_user', 'new_user');

		if (PeepSo::get_option('site_registration_enableverification', '0')) {
			$template = 'new_user';
		} else {
			$template = 'new_user_no_approval';
		}

		PeepSoMailQueue::add_message($this->id, $data, sprintf(__('Welcome to %s', 'peepso-core'),
			get_bloginfo('sitename')), $template /*'new_user_no_approval'*/, 'new_user',0,$now);

		// Log attempts
		if(NULL == $trigger) {
			$trigger = $this->id; // who triggered it? by default, it's the user himself
		}

		PeepSo3_Mayfly_Int::increment('user_'.$this->id.'_send_activation_count');
		PeepSo3_Mayfly::set('user_'.$this->id.'_send_activation_last_attempt_trigger', $trigger);
		PeepSo3_Mayfly::set('user_'.$this->id.'_send_activation_last_attempt_date', current_time('mysql'));
	}

	/* check if an action is allowed to be performed by an author
	 * @param string $action Name of action to check
	 * @param int $author Id of author attempting the action
	 */
	public function check_access($action, $author)
	{
		$access = get_user_meta($this->id, self::ACCESS_SETTING, TRUE);
		if (empty($access))
			$access = $this->default_settings;
		else
			$access = unserialize($access);

		$val = PeepSo::ACCESS_PRIVATE;
		if (isset($access['stream_' . $action]))
			$val = $access['stream_' . $action];

		switch ($val)
		{
			// TODO: check this - better to do 'case 0:' and 'case ACCESS_PUBLIC:' on separate lines
			case 0 || PeepSo::ACCESS_PUBLIC:
				return (TRUE);
				break;
			case PeepSo::ACCESS_MEMBERS:
				if ($author)				// if author != 0, they're logged in and therefore a member
					return (TRUE);
				break;
			case PeepSo::ACCESS_PRIVATE:
				break;
		}
		return (FALSE);
	}


	/*
	 * Adds to profile view count.
	 * @param int $user_id The user id to add to the view count. If not provided will use
	 * the instance's user id.
	 */
	public function add_view_count($user_id = NULL)
	{
		if (NULL === $user_id && NULL === $this->id)
			return;

		if (NULL === $user_id)
			$user_id = $this->id;

		global $wpdb;
		$sql = "UPDATE `{$wpdb->prefix}" . self::TABLE . "` " .
			" SET `usr_views` = `usr_views`+1 " .
			" WHERE `usr_id`=%d ";
		$wpdb->query($wpdb->prepare($sql, $user_id));

		// Update view count cache on the target user's instance.
		if ( isset(self::$instances[$user_id]) ) {
			self::$instances[$user_id]->peepso_user['usr_views'] += 1;
		}
	}


	/*
	 * Retrieve the user's profile view count
	 * @return int The number of views of the profile
	 */
	public function get_view_count()
	{
		return intval($this->peepso_user['usr_views']);
	}


	/**
	 * Move $src_file to the image directory and update the database entry for its location
	 * @param string $src_file The original file path to get the file from
	 * @param bool $add_to_stream, if true add new hook called peepso_user_after_change_cover
	 */
	// TODO: move to PeepSoProfile
	public function move_cover_file($src_file, $add_to_stream=TRUE)
	{
		$dir = $this->get_image_dir();

		// remove old cover
		$this->delete_cover_photo();

		$hash = substr(md5(time()), 0, 10);
		$dest_file = $dir . $hash . '-cover.jpg';
		update_user_meta($this->get_id(),'peepso_cover_hash', $hash);

		// Ensure white background on transparent PNG uploads
		$si = new PeepSoSimpleImage();
		$si->convert_image($src_file);
        $si->load($src_file);
        $si->resizeToWidth(PeepSo::get_option('cover_width', 3000));
        $si->save($dest_file, IMAGETYPE_JPEG);

		$image = wp_get_image_editor($dest_file);
		$image->set_quality(PeepSo::get_option('cover_quality', 85));
		$image->save($dest_file, IMAGETYPE_JPEG);

		// update database table entry
		$data = array('usr_cover_photo' => $this->get_image_url() . $hash . '-cover.jpg');

		global $wpdb;
		$wpdb->update($wpdb->prefix . self::TABLE, $data, array('usr_id' => $this->id));

		delete_user_meta($this->id, 'peepso_cover_position_x');
		delete_user_meta($this->id, 'peepso_cover_position_y');

		if($add_to_stream) {
	    	// add action after user change cover
	    	do_action('peepso_user_after_change_cover', $this->get_id(), $dest_file);
	    }

	}


	/*
	 * Deletes the user's cover photo and removes the database entry
	 */
	// TODO: move to PeepSoProfile
	public function delete_cover_photo($cover_hash = '')
	{
		$ret = FALSE;

		if (!$cover_hash) {
			$cover_hash = get_user_meta($this->get_id(),'peepso_cover_hash', TRUE);
			$cover_hash = $cover_hash ? $cover_hash : '';
		}

		$cover_file = $this->get_image_dir() . $cover_hash . '-cover.jpg';
		if (file_exists($cover_file)) {
			unlink($cover_file);
			$ret = TRUE;
		}

		$other_sizes = apply_filters('peepso_filter_cover_sizes_to_delete', array(750));

		foreach ($other_sizes as $size) {
            $cover_file = $this->get_image_dir() . $cover_hash . '-cover-' . $size . '.jpg';
            if (file_exists($cover_file)) {
                unlink($cover_file);
            }
        }

		global $wpdb;
		$data = array('usr_cover_photo' => '');
		$wpdb->update($wpdb->prefix . self::TABLE, $data, array('usr_id' => $this->id));
		delete_user_meta($this->id, 'peepso_cover_position_x');
		delete_user_meta($this->id, 'peepso_cover_position_y');
		delete_user_meta($this->id, 'peepso_cover_hash');

		return ($ret);
	}


	/*
	 * Deletes the user's avatar image, including the original and small versions
	 */
	// TODO: move to PeepSoProfile
	public function delete_avatar()
	{
		$dir = $this->get_image_dir();
		$avatar_hash = get_user_meta($this->get_id(),'peepso_avatar_hash', TRUE);

		if($avatar_hash) {
			$avatar_hash = $avatar_hash . '-';
		}

		if (file_exists($dir . $avatar_hash . 'avatar.jpg')) {
			unlink($dir . $avatar_hash . 'avatar.jpg');
		}

		if (file_exists($dir . $avatar_hash . 'avatar-full.jpg')) {
			unlink($dir . $avatar_hash . 'avatar-full.jpg');
		}

		if (file_exists($dir . $avatar_hash . 'avatar-orig.jpg')) {
			unlink($dir . $avatar_hash . 'avatar-orig.jpg');
		}

		$input = new PeepSoInput();
		if ($input->value('use_gravatar', 0, FALSE) == 1 && PeepSo::get_option('avatars_gravatar_enable') == 1) {
			$this->set_avatar_custom_flag(TRUE);
		} else {
			$this->set_avatar_custom_flag(FALSE);
		}
	}

	/**
	 * Fix image orientation
	 * @param object $image WP_Image_Editor
	 * @param array $exif EXIF metadata
	 * @return object $image WP_Image_Editor
	 */
	// TODO: move this. Where is it used? PeepSoProfile?
	public function fix_image_orientation($image, $orientation)
	{
		switch ($orientation)
		{
			case 3:
				$image->rotate(180);
				break;
			case 6:
				$image->rotate(-90);
				break;
			case 8:
				$image->rotate(90);
				break;
		}
		return ($image);
	}

	/*
	 * Moves an uploaded avatar file into the user's directory, renaming and converting
	 * the file to .jpg
	 * @param string $src_file Path to the source / uploaded file
	 * @param Boolean $delete Set to TRUE to delete $src_file
	 */
	// TODO: move to PeepSoProfile
	public function move_avatar_file($src_file, $delete = FALSE)
	{
		$dir = $this->get_image_dir();

        // Ensure white background on transparent PNG uploads
		$si = new PeepSoSimpleImage();
		$si->convert_image($src_file);

		$image = wp_get_image_editor($src_file);

		if (!is_wp_error($image)) {
			$dest_orig = $dir . 'avatar-orig-tmp.jpg';
			$dest_full = $dir . 'avatar-full-tmp.jpg';
			$dest_thumb = $dir . 'avatar-tmp.jpg';

			if (function_exists('exif_read_data') && function_exists('exif_imagetype') && IMAGETYPE_JPEG === exif_imagetype($src_file)) {
				$exif = @exif_read_data($src_file);
				$orientation = isset($exif['Orientation']) ? $exif['Orientation'] : 0;
			} else {
				$exif = new PeepSoExif($src_file);
				$orientation = $exif->get_orientation();
			}
			$image->set_quality(PeepSo::get_option('avatar_quality', 75));
			$image = $this->fix_image_orientation($image, $orientation);
			$image->save($dest_orig, IMAGETYPE_JPEG);

			$image = wp_get_image_editor($src_file);
			$image->resize(PeepSo::get_option('avatar_size', 250), PeepSo::get_option('avatar_size', 250), TRUE);
			$image->set_quality(PeepSo::get_option('avatar_quality', 75));
			$image = $this->fix_image_orientation($image, $orientation);
			$image->save($dest_full, IMAGETYPE_JPEG);

			$image = wp_get_image_editor($src_file);
			$image->resize(self::THUMB_WIDTH, self::THUMB_WIDTH, TRUE);
			$image->set_quality(PeepSo::get_option('avatar_quality', 75));
			$image = $this->fix_image_orientation($image, $orientation);
			$image->save($dest_thumb, IMAGETYPE_JPEG);
		}

		if ($delete)
			unlink($src_file);
	}


	/**
	 * Finalize moves temporary avatar files into designated location.
	 * @param bool $add_to_stream, if true add new hook called peepso_user_after_change_cover
	 */
	public function finalize_move_avatar_file($add_to_stream=TRUE)
	{
		$dir = $this->get_image_dir();

		$src_thumb = $dir . 'avatar-tmp.jpg';
		$src_full = $dir . 'avatar-full-tmp.jpg';
		$src_orig = $dir . 'avatar-orig-tmp.jpg';

		// #1740 randomize filename
		// remove old avatar if hash exists
		$this->delete_avatar();

		// set new hash
		$avatar_hash = substr(md5(time()), 0, 10);
		update_user_meta($this->get_id(),'peepso_avatar_hash', $avatar_hash);

		$dest_thumb = $dir . $avatar_hash . '-avatar.jpg';
		$dest_full = $dir . $avatar_hash . '-avatar-full.jpg';
		$dest_orig = $dir . $avatar_hash . '-avatar-orig.jpg';

		// end of #1740 randomize filename

		if (file_exists($src_thumb) && file_exists($src_full) && file_exists($src_orig)) {
			rename($src_thumb, $dest_thumb);
			rename($src_full, $dest_full);
			rename($src_orig, $dest_orig);

			if($add_to_stream) {
				// add action after user change avatar
	      		do_action('peepso_user_after_change_avatar', $this->get_id(), $dest_thumb, $dest_full, $dest_orig);
			}

			$this->set_avatar_custom_flag(TRUE);
		}
	}


	/*
	 * Checks for and creates the user's image directory
	 * @param string $file The file name that is going to be created
	 */
	private function _make_user_dir($dir_name)
	{
		$dir_name = rtrim($dir_name, '/');
		if (!file_exists($dir_name) ) {
			$ret = @mkdir($dir_name, 0755, TRUE);
			return ($ret);
		}
		return (TRUE);
	}


	/*
	 * return directory where user's images are located
	 * @param int $id User id to retrieve directory
	 * @return string directory where user's images are located
	 */
	public function get_image_dir()
	{
		// wp-content/peepso/users/{user_id}/
//		$dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'peepso' . DIRECTORY_SEPARATOR .
//			'users' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR;
		$dir = PeepSo::get_peepso_dir() . 'users' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR;

		// Make sure the dir exists
		$this->_make_user_dir($dir);

		return ($dir);
	}
	public function get_image_url()
	{
//		$dir = WP_CONTENT_URL . '/peepso/users/' . $this->id . '/';
		$dir = PeepSo::get_peepso_uri() . 'users/' . $this->id . '/';
		return ($dir);
	}

	/*
	 * return directory where user's images are located
	 * @param int $id User id to retrieve directory
	 * @return string directory where user's images are located
	 */
	public function get_personaldata_dir()
	{
		// wp-content/peepso/users/{user_id}/
//		$dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'peepso' . DIRECTORY_SEPARATOR .
//			'users' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR;
		$dir = PeepSo::get_peepso_dir() . 'personaldata' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR;

		// Make sure the dir exists
		$this->_make_user_dir($dir);

		return ($dir);
	}
	public function get_personaldata_url()
	{
//		$dir = WP_CONTENT_URL . '/peepso/users/' . $this->id . '/';
		$dir = PeepSo::get_peepso_uri() . 'personaldata/' . $this->id . '/';
		return ($dir);
	}

	// used for testing...
	public function get_user()
	{
		return ($this->wp_user);
	}

	public function get_peepso_user()
	{


		return ($this->peepso_user);
	}

	/*
	 * updates the peepso_user record with data passed in via array
	 * @param array $data Key=>value array of column names found in the peepso_user table
	 * @return Boolean TRUE on sucess, otherwise 1 - the number of records updated
	 */
	public function update_peepso_user($data)
	{
		// update local store of data - ensure only known columns are passed in
		foreach ($this->peepso_user as $col => $val) {
			if (isset($data[$col])) {
				$this->peepso_user[$col] = $data[$col];
			}
		}

		global $wpdb;
		return ($wpdb->update($wpdb->prefix . PeepSoUser::TABLE, $this->peepso_user, array('usr_id' => $this->id)));
	}


	/*
	 * Returns user-specific template fields. Used by PeepSoEmailTemplate
	 * @param string $prefix The prefix name for the array elements to return. Used to differentiate between the user and from names
	 * @return array Associative array of template fields in key => value form
	 */
	// TODO: move to PeepSoEmailTemplate class
	public function get_template_fields($prefix)
	{
		$ret = array();
		$ret[$prefix . 'email'] = $this->get_email();
		$ret[$prefix . 'fullname'] = $this->get_fullname();
		$ret[$prefix . 'firstname'] = $this->get_firstname();
		$ret[$prefix . 'login'] = $this->get_username();
		return ($ret);
	}

	/**
	 * Returns whether or not this user's profile can be "Like"`d
	 * Defaults to TRUE if not set.
	 * @return boolean
	 */
	public function is_profile_likable()
	{
		$likable = get_user_meta($this->id, 'peepso_is_profile_likable', TRUE);
		$likable = (('' !== $likable) ? $likable : TRUE);
		$likable = $likable && ($this->id != get_current_user_id());

		return $likable;
	}

	/**
	 * Returns whether or not this user's hide themselves from all user listings
	 * Defaults to TRUE if not set.
	 * @return boolean
	 */
	public function is_hide_profile_from_user_listing()
	{
		$is_hide = get_user_meta($this->id, 'peepso_is_hide_profile_from_user_listing', TRUE);

		return (('' !== $is_hide) ? $is_hide : 0);
	}

    public function get_hide_online_status()
    {
        $hide_online_status = get_user_meta($this->id, 'peepso_hide_online_status', TRUE);
        return (('' !== $hide_online_status ) ? $hide_online_status  : 0);
    }

    public function get_hide_birthday_year()
    {
        $hide_birthday_year = get_user_meta($this->id, 'peepso_hide_birthday_year', TRUE);
        return (('' !== $hide_birthday_year ) ? $hide_birthday_year : 0);
    }

    public static function get_gmt_offset($user_id)
    {
        $user_gmt_offset = get_user_meta($user_id, 'peepso_gmt_offset', TRUE);

        return (('' !== $user_gmt_offset) ? $user_gmt_offset : get_option('gmt_offset'));
    }

    /**
     * @deprecated since 1.7.4
     */
    public function get_num_feeds_to_show()
	{
	    trigger_error('PeepSoUser::get_num_feeds_to_show() is depreacated since 1.7.4');
        return 5;
	}

	/**
	 * Returns the peepso_profile_display_name_as setting
	 * @return int
	 */
	public function get_display_name_as()
	{
		$display_name_as = get_user_meta($this->id, 'peepso_profile_display_name_as', TRUE);
		return (('' !== $display_name_as) ? $display_name_as : PeepSo::get_option('system_display_name_style', 'username'));
	}

    /**
     * Fires after admin has approved the user.
     * Sends notice that the user may now login.
     */
    public function approve_user()
    {
        $data = $this->get_template_fields('user');
        $data['useremail'] = $this->get_email();

        PeepSoMailQueue::add_message($this->get_id(), $data, __('Account activated', 'peepso-core'), 'user_approved', 'user_approved');

        do_action('peepso_action_user_approved', $this->get_id());
    }


    /*
     * Fetches from the peepso_users table per gender
     * @param string $gender Gender identifier ('u', 'f', 'm')
     * @param Boolean $check_acc TRUE to do accessibility checks; otherwise FALSE
     * @return object $wpdb result
     */
    public function get_by_gender($gender, $check_acc = TRUE)
    {
        global $wpdb;

        $gender = strtolower(substr($gender, 0, 1));
        if ('m' !== $gender && 'f' !== $gender && 'u' !== $gender)
            $gender = 'u';

        $sql = 'SELECT * ' .
            " FROM `{$wpdb->prefix}" . self::TABLE . "` ";

		if (PeepSo::get_option_new('user_blocking_enable')) {
            $sql .= " LEFT JOIN `{$wpdb->prefix}peepso_blocks` ON `blk_user_id`=`usr_id` OR `blk_blocked_id`=`usr_id` ";
		}

        if ($check_acc) {
            // add exclusion if the Gender is not accessible

            // public
            $where[] = ' (`usr_gender_acc`=' . PeepSo::ACCESS_PUBLIC . " AND `usr_gender`='{$gender}') ";

            // members: logged in search for gender, otherwise check for unknown
            if (is_user_logged_in())
                $where[] = ' (`usr_gender_acc`=' . PeepSo::ACCESS_MEMBERS . " AND `usr_gender`='{$gender}') ";
            else if ('u' === $gender)
                $where[] = ' (`usr_gender_acc`=' . PeepSo::ACCESS_MEMBERS . " AND `usr_gender`='u') ";

            // TODO: handle Friends accessibility

            // private: admin search for gender, otherwise check for unknown
            if (PeepSo::is_admin())
                $where[] = ' (`usr_gender_acc`=' . PeepSo::ACCESS_PRIVATE . " AND `usr_gender`='{$gender}') ";
            else if ('u' === $gender)
                $where[] = ' (`usr_gender_acc`=' . PeepSo::ACCESS_PRIVATE . " AND `usr_gender`='u') ";
        } else {
            $where[] = "`usr_gender`='{$gender}' ";
        }

        // add the WHERE clause to the statement
        $sql .= ' WHERE ' . implode(' OR ', $where);

        return ($wpdb->get_results($wpdb->prepare($sql, $gender), ARRAY_A));
    }

    /*
     * Fetches the count of members from the peepso_users table by gender
     * @param string $gender Gender identifier ('u', 'f', 'm')
     * @return int Number of users found
     */
    public function get_count_by_gender($gender)
    {
        global $wpdb;
        // TODO: add exclusion if the Gender is not accessible
        $sql = 'SELECT COUNT(DISTINCT `user_id`) AS `count` ' .
            " FROM `{$wpdb->usermeta}` " .
            ' WHERE meta_key = \'peepso_user_field_gender\'' .
            ' AND `meta_value`=\'%s\' ';
        $ret = intval($wpdb->get_var($wpdb->prepare($sql, $gender)));
        return ($ret);
    }

    /**
     * Returns counts for the number of users
     * @global type $wpdb
     * @return type
     */
    public function get_counts_by_role()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) `count`, `usr_role` AS `role`
				FROM `{$wpdb->prefix}" . self::TABLE . "`
				LEFT JOIN `{$wpdb->users}` ON `ID`=`usr_id`
				WHERE `ID` IS NOT NULL
				GROUP BY `usr_role`
				ORDER BY `usr_role`";

        $res = $wpdb->get_results($sql, ARRAY_A);
        return ($res);
    }
    public function count_for_roles($roles)
    {
        if (0 === count($roles))
            return (0);

        $inlist = array();
        foreach ((array) $roles as $role)
            $inlist[] = '\'' . esc_sql($role) . '\'';
        $inlist = implode(',', $inlist);

        global $wpdb;
        $sql = "SELECT COUNT(*) `count`
				FROM `{$wpdb->prefix}" . self::TABLE . "`
				LEFT JOIN `{$wpdb->users}` ON `ID`=`usr_id`
				WHERE `ID` IS NOT NULL AND `usr_role` IN ({$inlist})";
        return (intval($wpdb->get_var($sql)));
    }
}

// EOF
