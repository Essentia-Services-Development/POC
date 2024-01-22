<?php

/**
 * @todo what's missing
 *
 * covers
 * avatars
 * privacy
 * custom URL slug
 */
class PeepSoGroup
{
	protected static $_instance = NULL;

	public static $validation= array(

        'name'          => array(
            'required'	=> TRUE,
            'not_int'	=> TRUE,
            'minlength'	=> 3,
            'maxlength' => 64,
        ),

        'slug'          => array(
            'required'	=> TRUE,
            'not_int'	=> TRUE,
            'minlength'	=> 3,
            'maxlength' => 64,
        ),

		'description'   => array(
			'required'	=> TRUE,
			'not_int'	=> FALSE,
			'minlength' => 3,
			'maxlength'	=> 1500,
		),

        'privacy'       => array(
            'required'	=> TRUE,
            'not_int'	=> FALSE,
        ),
	);

	// post data
	public $id;
	public $author_id;
	public $owner_id;
	public $date_created;
	public $date_modified;
    public $excerpt;
	public $published;

	public $name;
	public $description;
	public $rules;
	public $slug;

	// post meta
	public $members_count;
	public $pending_admin_members_count;
	public $pending_user_members_count;
	public $banned_members_count;
	public $privacy; // PeepSoGroupPrivacy
	public $is_open		= TRUE;
	public $is_closed 	= FALSE;
	public $is_secret	= FALSE;

	public $is_joinable    = TRUE;
	public $is_invitable   = TRUE;
	public $is_readonly    = FALSE;

    public $members_tab = TRUE;
	public $is_interactable = FALSE;
	public $is_join_muted  = FALSE;
	public $is_auto_accept_join_request = FALSE;
	public $is_allowed_non_member_actions = 0;



	// property
	public $post_data_map;
	public $meta_data_map;

	private $_groupuser = FALSE;

	const POST_TYPE 		= 'peepso-group';

	/**
	 * PeepSoGroup constructor.
	 * @param $id
	 */
	public function __construct($id = NULL, $data = NULL)
	{
		// post data mapping
		$this->post_data_map = array(
			'id' 			=> 'ID',
			'owner_id'		=> 'post_author',
			'date_created'	=> 'post_date_gmt',
			'date_modified'	=> 'post_modified_gmt',
			'name'			=> 'post_title',
			'description'	=> 'post_content',
			'excerpt'		=> 'post_excerpt',
			'slug'			=> 'post_name',
			'published'		=> 'post_status',
		);

		// post meta mapping
		$this->meta_data_map = array(
			'members_count' 				=> 1, // the group creator should always be a member, so the minimum member count is "1"
			'pending_admin_members_count'	=> 0,
			'pending_user_members_count'	=> 0,
			'banned_members_count'			=> 0,
			'privacy'						=> PeepSoGroupPrivacy::PRIVACY_OPEN, // groups are public by default,
            'rules'                         => '',
			'is_joinable'					=> TRUE,
			'is_invitable'					=> TRUE,
            'is_readonly'					=> FALSE,
            'members_tab'					=> PeepSo::get_option_new('groups_members_tab'),
			'is_interactable'				=> FALSE,
			'is_join_muted'					=> FALSE,
			'is_auto_accept_join_request' 	=> FALSE,
			'is_allowed_non_member_actions' => 0,
		);

		// #4883 handle a case when an object is passed via filters
        if($id instanceof PeepSoGroup) {
            $id = $id->get('id');
        }

		// constructor is also able to handle group creation
		if( NULL == $id && is_array($data) ) {

			// optional filters to modify data before creation
			// $data = apply_filters('peepso_groups_will_create_group', $data);

			// If no owner_id was passed, default to current user and generate a notice
			if( !isset($data['owner_id']) ) {
				trigger_error("Missing owner_id PeepSoGroup", E_USER_NOTICE);
				$data['owner_id'] = get_current_user_id();
			}

			$this->id = $id = $this->create($data['name']);

			// Assign categories or trigger a notice
			if(PeepSo::get_option('groups_categories_enabled', FALSE)) {
				if (isset($data['category_id'])) {
					PeepSoGroupCategoriesGroups::add_group_to_categories($id, $data['category_id']);
					unset($data['category_id']);
				} else {
					trigger_error("Missing category_id PeepSoGroup", E_USER_NOTICE);
				}
			}

			// Assign meta data
			if(isset($data['meta'])) {
				$meta = $data['meta'];

				if(is_array($meta)) {
					foreach($meta as $key=>$val) {
						add_post_meta($this->id, 'peepso_group_' . $key, $val, TRUE);
					}
				}

				unset($data['meta']);
			}
			$this->update($data);

			// hooks after groups created
			do_action('peepso_action_group_create', $this);
		}

		// grabbing group by numeric id...
		$args = array(
			'include'	=> array($id),
			'post_type' => self::POST_TYPE,
		);

		// ...or by post_name
		if( !is_numeric($id) ) {
			unset($args['include']);
			$args['name'] = $id;
		}

		$args['post_status'] = 'any';

		ob_start();
		$posts = get_posts($args);
        ob_get_clean();

		// group not found
		if(!count($posts)) {
			return FALSE;
		}

		// group found
		$post = $posts[0];

		// map wp_posts data to class properties
		foreach($this->post_data_map as $property => $post_key) {
			$this->$property = $post->$post_key;
		}

		// map postmeta data to class properties
		foreach($this->meta_data_map as $property=>$default) {
			$this->$property = get_post_meta($this->id, 'peepso_group_'.$property, TRUE);
			// get_post_meta WILL RETURN AN EMPTY STRING if the key is not found
			if('' === $this->$property) {
				add_post_meta($this->id, 'peepso_group_' . $property, $default, TRUE);
				$this->$property = $default;
			}
		}

		switch($this->privacy) {
			case PeepSoGroupPrivacy::PRIVACY_CLOSED:
				$this->is_open 	 = FALSE;
				$this->is_closed = TRUE;
				$this->is_secret = FALSE;
				break;
			case PeepSoGroupPrivacy::PRIVACY_SECRET:
				$this->is_open	 = FALSE;
				$this->is_closed = FALSE;
				$this->is_secret = TRUE;
				break;
			default:
				break;
		}

		$this->privacy = PeepSoGroupPrivacy::_($this->privacy);

		// Fallback for empty slug
		if(!strlen($this->slug)) {
            $name = $this->name;

            if(!$name) {
                $name = 'group-'.$this->id;
            }

            $new_slug = wp_unique_post_slug(sanitize_title_with_dashes($name), $this->id, 'any', 'peepso-group', 0);

            $this->update(array('slug' => $new_slug));
		}

		// Fallback for numeric slug
        if(is_numeric($this->slug)) {
            $new_slug = wp_unique_post_slug(sanitize_title_with_dashes('group-'.$this->id), $this->id, 'any', 'peepso-group', 0);

            $this->update(array('slug' => $new_slug));
        }

        // Fallback for blocked slug
        if(in_array($this->slug, PeepSoGroupsPlugin::$group_slug_blocklist)) {
            $new_slug = 'group-'.$this->slug;
            $new_slug = wp_unique_post_slug(sanitize_title_with_dashes($new_slug), $this->_group_id, 'any', 'peepso-group', 0);
            $this->update(array('slug' => $new_slug));
        }

		// Published flag 0/1
		// @todo more granular publish status in separate property
		$this->published = ('publish' == $this->published) ? TRUE : FALSE;
	}

	/**
	 * Singleton - if the PeepSoGroupUser instance is needed, try to load it only once
	 * @return bool|PeepSoGroup
	 */
	private function get_groupuser_instance()
	{
		if(FALSE === $this->_groupuser) {
			$this->_groupuser = new PeepSoGroupUser($this->id, NULL, $this);
		}

		return $this->_groupuser;
	}

	public function get($prop)
	{
		if(property_exists($this, $prop)) {
			return $this->$prop;
		}

		$method = "get_$prop";
		if(method_exists($this, $method)) {
			return $this->$method();
		}

		trigger_error("Unknown property/method $prop/$method");
	}


	/** ** ** ** ** CREATE & UPDATE ** ** ** ** **/

	/**
	 * Injects a new wp_post
	 * @todo and runs other group creation tasks
	 * Called only from __construct()
	 * @return int|WP_Error
	 */
	private function create($name = NULL)
	{
		// insert the post, grab the ID
        $args = array( 'post_status'=>'publish', 'post_type' => self::POST_TYPE );
        if(NULL != $name) {
            $args['post_title'] = $name;
        }

		$id = wp_insert_post( $args );

		// @todo add the creator as a group member
		$group_user = new PeepSoGroupUser($id);
		$group_user->member_join();
		$group_user->member_modify('member_owner');

		return $id;
	}

	/**
	 * Updates post data and post meta
	 * @param array $data - key/value array of group properties
	 */
	public function update( $data )
	{
		$post_data = array(
			'ID' => $this->id,
		);

		foreach( $data as $key=>$value ) {

			if( property_exists($this, $key )) {

				// update self
				$this->$key = $value;

				// if the key belongs to post_data
				if(array_key_exists($key, $this->post_data_map)) {
					$post_key = $this->post_data_map[$key];
					$post_data[$post_key] = $value;
					continue;
				}

				// otherwise save in postmeta
				if(in_array($key, $this->meta_data_map)) {
					update_post_meta($this->id, 'peepso_group_' . $key, $value);
					continue;
				}

			} else {
				trigger_error("Unknown property PeepSoGroup::$key", E_USER_NOTICE);
			}
		}

		if(count($post_data) > 1) {
			wp_update_post($post_data);
		}
	}

	public static function validate($group_data)
	{
		$errors=array();

		// validation for category_id
		if(PeepSo::get_option('groups_categories_enabled', FALSE)) {
			// #5054 Do not validate if there is no published category.
			$PeepSoGroupCategories = new PeepSoGroupCategories(FALSE, TRUE);
			$categories = $PeepSoGroupCategories->categories;
			if(count($categories)) {
				self::$validation['category_id'] = array(
					'required'	=> TRUE
				);
			}
		}

		foreach($group_data as $property=>$value) {

			if(!isset(self::$validation[$property])) {
				continue;
			}

			$rule = self::$validation[$property];

			if(is_array($value)) {
				if((!count($value) && TRUE == $rule['required'])) {
					$errors[$property] = __('This field is required', 'groupso');
				}
			} else {
				if((!strlen($value) && TRUE == $rule['required'])) {
					$errors[$property] = __('This field is required', 'groupso');
				}
			}

			if ( isset($rule['not_int']) && is_numeric($value) && TRUE == $rule['not_int'] ) {
				$errors[$property] = __('This field can\'t be a number', 'groupso');
			}

			if ( isset($rule['minlength']) && is_numeric($rule['minlength'])  && strlen(utf8_decode($value)) < $rule['minlength']) {
				$errors[$property] = sprintf( __('Minimum length is %s characters', 'groupso'), $rule['minlength'] );
			}

			if ( isset($rule['maxlength']) && is_numeric($rule['maxlength'])  && strlen(utf8_decode($value)) > $rule['maxlength'] ) {
				$errors[$property] = sprintf(__('Maximum length is %s', 'groupso'), $rule['maxlength']);
			}

			// #5493 let admin enforce unique group names
			if( 'name' == $property && PeepSo::get_option_new('groups_unique_names') ) {

			    global $wpdb;
                $value=str_replace(' ','',trim(strtoupper($value)));
                $sql = "SELECT COUNT(id) FROM {$wpdb->posts} WHERE REPLACE(UPPER(post_title),' ','') = '$value' AND `post_type`='peepso-group'";

                if( isset($group_data['id']) && is_int($group_data['id'] ) ) {
                    $sql .= " AND ID <> {$group_data['id']}";
                }

                $count = $wpdb->get_var($sql);

                if($count > 0) {
                    $errors[$property] = sprintf(__('This group name cannot be used', 'groupso'), $rule['maxlength']);
                }
            }
		}

		return $errors;
	}

	/** ** ** ** ** GETTERS & FORMATTING ** ** ** ** **/

	/**
	 * Utility - returns group URL
	 * @param bool $slug - TRUE to return URL using the slug (if available), FALSE to use the numeric ID
	 * @return string
	 */
	public function get_url( $permalink = FALSE )
	{
	    $url = array();

	    $url[]=PeepSo::get_page('groups');

        if(0 == PeepSo::get_option('disable_questionmark_urls', 0)) {
            $url[]='?';
        }

		// permalink uses the numeric ID that never changes
		if( TRUE === $permalink || (0 == PeepSo::get_option('groups_urls_slugs_enable', 1))) {
            $url[]= $this->id;
		} else {
            $url[] = $this->slug;
        }

        $url[] = '/';

        return implode('', $url);
	}

	/**
	 * Utility - returns group Creation
	 * @return string
	 */
	public function get_date_created_formatted()
	{
		// format the date
		return date_i18n(get_option('date_format'), strtotime($this->date_created));
	}


	/** ** ** ** ** AVATAR ** ** ** ** **/

	// @TODO DRY this should be PeepSo global
	/**
	 * Returns the max upload size from php.ini and wp.
	 * @return string The max upload size bytes in human readable format.
	 */
	public function upload_size()
	{
		$peepso_general = PeepSoGeneral::get_instance();
		return ($peepso_general->upload_size());
	}

	/*
	 * Obtain href for group's full sized avatar image
	 *
	 */
	public function get_avatar_url_full()
	{
		$avatar = $this->get_avatar_url(TRUE);
		return $avatar;
	}

	/**
	 * Display the group's original avatar
	 */
	public function get_avatar_url_orig()
	{
		$avatar = $this->get_avatar_url(TRUE);
		$avatar = str_replace('-full.jpg', '-orig.jpg', $avatar);
		return $avatar;
	}

	/*
	 * Return group's avatar image
	 * @param boolean $full TRUE to return full-size image or FALSE for small image
	 * @return string The href value for the avatar image suitible for use in an <img> tag
	 */
	public function get_avatar_url($full = TRUE)
	{
		$avatar_hash = get_post_meta($this->id,'peepso_group_avatar_hash', TRUE);
		if($avatar_hash) {
			$avatar_hash = $avatar_hash . '-';
		}
		$file = $this->get_image_dir() . $avatar_hash . 'avatar' . ($full ? '-full' : '') . '.jpg';


		if (!file_exists($file)) {
			$this->set_avatar_custom_flag(FALSE);
			$file = $this->get_default_avatar_url($full);
		} else {
			$this->set_avatar_custom_flag(TRUE);
			$file = $this->get_image_url() . $avatar_hash . 'avatar' . ($full ? '-full' : '') . '.jpg';
		}

		return ($file);
	}

    /**
     * Return default avatar for group.
     *
     * @param boolean $full
     * @return string
     */
    public function get_default_avatar_url($full = TRUE) {
        return PeepSo::get_asset('images/avatar/group' . ($full ? '' : '-thumb') . '.png');
    }

	function set_avatar_custom_flag( $flag = TRUE )
	{
		$current_val = get_post_meta($this->id, 'group_avatar_custom', TRUE);

		$flag = ( TRUE === $flag) ? TRUE : FALSE;

		if( (TRUE===$flag && 1==$current_val) || (FALSE===$flag && 0==$current_val)) {
			return FALSE;
		}

		update_post_meta($this->id, 'group_avatar_custom', $flag);

	}

	/*
	 * Return group's temporary avatar image
	 * @param boolean $full TRUE to return full-size image or FALSE for small image
	 * @return string The href value for the avatar image suitible for use in an <img> tag
	 */
	public function get_tmp_avatar($full = FALSE)
	{
		$file = $this->get_image_dir() . 'avatar' . ($full ? '-full' : '') . '-tmp.jpg';

		if (!file_exists($file)) {
			$file = PeepSo::get_asset('images/user-neutral' . ($full ? '' : '-thumb') . '.png');
		} else {
			$file = $this->get_image_url() . 'avatar' . ($full ? '-full' : '') . '-tmp.jpg';
		}

		return ($file);
	}

	/**
	 * Checks whether a group has an avatar
	 * @return boolean
	 */
	public function has_avatar()
	{
		$avatar = $this->get_avatar_url();
		if (strpos($avatar, '/groups/') !== FALSE) {
			return (TRUE);
		}
		return (FALSE);
	}

	/*
	 * Deletes the group's avatar image, including the original and small versions
	 */
	public function delete_avatar()
	{
		$dir = $this->get_image_dir();
		$avatar_hash = get_post_meta($this->id,'peepso_group_avatar_hash', TRUE);

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

		$this->set_avatar_custom_flag(FALSE);
	}

	/**
	 * Fix image orientation
	 * @param object $image WP_Image_Editor
	 * @param array $exif EXIF metadata
	 * @return object $image WP_Image_Editor
	 */
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
	 * Moves an uploaded avatar file into the group's directory, renaming and converting
	 * the file to .jpg
	 * @param string $src_file Path to the source / uploaded file
	 * @param Boolean $delete Set to TRUE to delete $src_file
	 */
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

			// @Since 1.7.4 the EXIF PHP extension is required
			// http://php.net/manual/en/function.exif-imagetype.php
			if (function_exists('exif_read_data')) {
				$exif = @exif_read_data($src_file);
			}

			$orientation = isset($exif) && isset($exif['Orientation']) ? $exif['Orientation'] : 0;

			$image->set_quality(PeepSo::get_option('avatar_quality', 75));
			$image = $this->fix_image_orientation($image, $orientation);
			$image->save($dest_orig, IMAGETYPE_JPEG);

			$image = wp_get_image_editor($src_file);
			$image->resize(PeepSo::get_option('avatar_size', 100), PeepSo::get_option('avatar_size', 100), TRUE);
			$image->set_quality(PeepSo::get_option('avatar_quality', 75));
			$image = $this->fix_image_orientation($image, $orientation);
			$image->save($dest_full, IMAGETYPE_JPEG);

			$image = wp_get_image_editor($src_file);
			$image->resize(PeepSoUser::THUMB_WIDTH, PeepSoUser::THUMB_WIDTH, TRUE);
			$image->set_quality(PeepSo::get_option('avatar_quality', 75));
			$image = $this->fix_image_orientation($image, $orientation);
			$image->save($dest_thumb, IMAGETYPE_JPEG);
		}

		if ($delete) {
			unlink($src_file);
		}
	}


	/**
	 * Finalize moves temporary avatar files into designated location.
	 */
	public function finalize_move_avatar_file()
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
		update_post_meta($this->id,'peepso_group_avatar_hash', $avatar_hash);

		$dest_thumb = $dir . $avatar_hash . '-avatar.jpg';
		$dest_full = $dir . $avatar_hash . '-avatar-full.jpg';
		$dest_orig = $dir . $avatar_hash . '-avatar-orig.jpg';

		// end of #1740 randomize filename

		if (file_exists($src_thumb) && file_exists($src_full) && file_exists($src_orig)) {
			rename($src_thumb, $dest_thumb);
			rename($src_full, $dest_full);
			rename($src_orig, $dest_orig);

			if(class_exists('PeepSoSharePhotos')) {
				// add action after user change avatar
	      		do_action('peepso_groups_after_change_avatar', $this->get('id'), $dest_thumb, $dest_full, $dest_orig);
			}
		}
	}

	/** ** ** ** ** COVER ** ** ** ** **/

	/*
	 * Return the group's cover photo
	 * @return string href value for the group's cover photo
	 */
	public function get_cover_url()
    {
		$cover = $this->get_cover_default();

		$group_cover_photo = get_post_meta($this->id, 'group_cover_photo', TRUE);
		if (!empty($group_cover_photo)) {
			$cover = $group_cover_photo;
		}

		return ($cover);
	}

	/**
	 * Get current group's default cover image.
	 *
	 * @return string
	 */
	public function get_cover_default()
    {
		return PeepSo::get_asset('images/cover/group-default.png');
	}

	/*
	 * Check if group has uploaded a custom cover photo
	 * @return Boolean TRUE if custom cover photo, otherwise FALSE
	 */
	public function has_cover()
	{
		$cover = $this->get_cover_url();

		if (FALSE !== stripos($cover, '/groups/')) {
			return (TRUE);
		}
		return (FALSE);
	}

	/**
	 * Display the current group's cover photo position percentage.
	 */
	public function cover_photo_position()
	{
		$ret = '';

		$x = get_post_meta($this->id, 'peepso_cover_position_x', TRUE);
		$y = get_post_meta($this->id, 'peepso_cover_position_y', TRUE);

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


	/**
	 * Move $src_file to the image directory and update the database entry for its location
	 * @param string $src_file The original file path to get the file from
	 */
	// TODO: move to PeepSoProfile
	public function move_cover_file($src_file)
	{
		$dir = $this->get_image_dir();

		// remove old cover
		$this->delete_cover_photo();

		$hash = substr(md5(time()), 0, 10);
		$dest_file = $dir . $hash . '-cover.jpg';
		update_post_meta($this->id,'peepso_group_cover_hash', $hash);

        // Ensure white background on transparent PNG uploads
        $si = new PeepSoSimpleImage();
		$si->convert_image($src_file);
        $si->load($src_file);
        $si->resizeToWidth(PeepSo::get_option('cover_width', 3000));
        $si->save($dest_file, IMAGETYPE_JPEG);

		$image = wp_get_image_editor($dest_file);
		$image->set_quality(PeepSo::get_option('cover_quality', 85));
		$image->save($dest_file, IMAGETYPE_JPEG);

		update_post_meta($this->id, 'group_cover_photo', $this->get_image_url() . $hash . '-cover.jpg');

		delete_post_meta($this->id, 'peepso_cover_position_x');
		delete_post_meta($this->id, 'peepso_cover_position_y');

		if(class_exists('PeepSoSharePhotos')) {
	    	// add action after user change cover
	    	do_action('peepso_groups_after_change_cover', $this->get('id'), $dest_file);
	    }

	}

	/*
	 * Deletes the group's cover photo and removes the database entry
	 */
	public function delete_cover_photo($cover_hash = '')
	{
		$ret = FALSE;

		if (!$cover_hash) {
			$cover_hash = get_post_meta($this->id, 'peepso_group_cover_hash', TRUE);
			$cover_hash = $cover_hash ? $cover_hash : '';
		}

		$cover_file = $this->get_image_dir() . $cover_hash . '-cover.jpg';
		if (file_exists($cover_file)) {
			unlink($cover_file);
			$ret = TRUE;
		}

		delete_post_meta($this->id, 'group_cover_photo');
		delete_post_meta($this->id, 'peepso_cover_position_x');
		delete_post_meta($this->id, 'peepso_cover_position_y');

		return $ret;
	}

	/*
	 * Checks for and creates the group's image directory
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
	 * return directory where group's images are located
	 * @param int $id User id to retrieve directory
	 * @return string directory where group's images are located
	 */
	// @todo DRY - repeated code
	public function get_image_dir()
	{
		// wp-content/peepso/groups/{user_id}/
//		$dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'peepso' . DIRECTORY_SEPARATOR .
//			'groups' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR;
		$dir = PeepSo::get_peepso_dir() . 'groups' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR;

		// Make sure the dir exists
		$this->_make_user_dir($dir);

		return ($dir);
	}
	public function get_image_url()
	{
//		$dir = WP_CONTENT_URL . '/peepso/groups/' . $this->id . '/';
		$dir = PeepSo::get_peepso_uri() . 'groups/' . $this->id . '/';
		return ($dir);
	}

}
