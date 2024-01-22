<?php

class PeepSoGroupCategory
{
	protected static $_instance = NULL;

	// post data
	public $id;
	public $author_id;

	public $published;

	public $name;
	public $description;
	public $slug;

	public $order;

	// post meta
	public $groups_count;

	// property
	private $post_data_map;
	private $meta_data_map;

	const POST_TYPE 		= 'peepso-group-cat';

	private $_table;

	/**
	 * PeepSoGroupCategory constructor
	 * @param $id
	 */
	public function __construct($id = NULL, $data = NULL)
	{
		// post data mapping
		$this->post_data_map = array(
			'id' 			=> 'ID',
			'author_id'		=> 'post_author',
			'name'			=> 'post_title',
			'description'	=> 'post_content',
			'slug'          => 'post_name',
			'published'		=> 'post_status',
			'order'			=> 'menu_order',
		);

		// post meta mapping
		$this->meta_data_map = array(
			'groups_count' 				=> 0,
		);

		// constructor is also able to handle group creation
		if( NULL == $id && is_array($data) ) {

			$this->id = $id = $this->create();

			if(isset($data['meta'])) {
				$meta = $data['meta'];

				if(is_array($meta)) {
					foreach($meta as $key=>$val) {
						add_post_meta($this->id, 'peepso_group_cat_' . $key, $val, TRUE);
					}
				}

				unset($data['meta']);
			}
			// Default the "order" to group_cat_id, ensures the new group category will be on the bottom
			$data['order'] = $this->id;
			// force open new category
			update_user_meta(get_current_user_id(), 'peepso_admin_group_category_open_' . $this->id, 1);
			$this->update($data);

			do_action('peepso_action_group_category_create', $this);
		}

		if(-1 == $id) {
			$this->id = -1;
			$this->name = __('Uncategorized', 'groupso');
			return;
		}

		// grabbing group by numeric id
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

		$posts = get_posts($args);

		// category not found
		if(!count($posts)) {
			return FALSE;
		}

		// category found
		$post = $posts[0];

		// map wp_posts data to class properties
		foreach($this->post_data_map as $property => $post_key) {
			$this->$property = $post->$post_key;
		}

		// map postmeta data to class properties
		foreach($this->meta_data_map as $property=>$default) {
			$this->$property = get_post_meta($this->id, 'peepso_group_cat_'.$property, TRUE);
			// get_post_meta WILL RETURN AN EMPTY STRING if the key is not found
			if('' === $this->$property) {
				add_post_meta($this->id, 'peepso_group_cat_' . $property, $default, TRUE);
				$this->$property = $default;
			}
		}

		// Fallback for empty slug
		if(!strlen($this->slug) && $this->id != -1) {
            $name = $this->name;

            if(!$name) {
                $name = 'group-category-'.$this->id;
            }

            $new_slug = wp_unique_post_slug(sanitize_title_with_dashes($name), $this->id, 'any', 'peepso-group-cat', 0);
            $this->update(array('slug' => $new_slug));
        }

		// Fallback for numeric slug
        if(is_numeric($this->slug) && $this->id != -1) {

            $name = $this->name;

            if(!$name) {
                $name = 'group-category-'.$this->id;
            }

            $new_slug = wp_unique_post_slug(sanitize_title_with_dashes($name), $this->id, 'any', 'peepso-group-cat', 0);
            $this->update(array('slug' => $new_slug));
        }

        // Fallback for pre-2.8.0 "Untitled Category" default description. Since 2.8.0 the default is empty
        if('Untitled Category' == $this->description) {
            $this->update(array('description'=>''));
        }


		// Published flag 0/1
		$this->published = ('publish' == $this->published) ? TRUE : FALSE;
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
	 * Called only from __construct()
	 * @return int|WP_Error
	 */
	private function create()
	{
		// insert the post, grab the ID
		$id = wp_insert_post( array( 'post_status'=>'publish', 'post_type' => self::POST_TYPE ) );
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
				if(array_key_exists($key, $this->meta_data_map)) {
					update_post_meta($this->id, 'peepso_group_cat_' . $key, $value);
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


	public function delete($category) {

		wp_delete_post($category);

		return TRUE;
	}

	/** ** ** ** ** GETTERS & FORMATTING ** ** ** ** **/

	/**
	 * Utility - returns category URL
	 * @return string
	 */
	public function get_url($segment = '')
	{
        // Uncategorized
        if(empty($this->slug)) {
            return PeepSo::get_page('groups') . '?category=' . $this->id;
        }

        $segment=trim($segment, '/');
        if(strlen($segment)) {
            $segment.='/';
        }

        return PeepSo::get_page('groups') . '?category/' . $this->slug.'/'.$segment;
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
        $avatar_hash = get_post_meta($this->id,'peepso_group_category_avatar_hash', TRUE);
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
     * Return default avatar for group category.
     *
     * @param boolean $full
     * @return string
     */
    public function get_default_avatar_url($full = TRUE) {
        return PeepSo::get_asset('images/avatar/group-category' . ($full ? '' : '-thumb') . '.png');
    }

    function set_avatar_custom_flag( $flag = TRUE )
    {
        $current_val = get_post_meta($this->id, 'group_category_avatar_custom', TRUE);

        $flag = ( TRUE === $flag) ? TRUE : FALSE;

        if( (TRUE===$flag && 1==$current_val) || (FALSE===$flag && 0==$current_val)) {
            return FALSE;
        }

        update_post_meta($this->id, 'group_category_avatar_custom', $flag);

    }

    /**
     * Returns temporary avatar for the newly uploaded image.
     *
     * @param boolean $full If true, returns the large avatar version.
     * @return string Temporary avatar URL.
     */
    public function get_tmp_avatar($full = FALSE)
    {
        $path = 'avatar' . ($full ? '-full' : '') . '-tmp.jpg';
        $file = $this->get_image_dir() . $path;

        if (file_exists($file)) {
            $file = $this->get_image_url() . $path;
        } else {
            $file = $this->get_default_avatar_url($full);
        }

        return $file;
    }

    /**
     * Checks whether a group has an avatar
     * @return boolean
     */
    public function has_avatar()
    {
        $avatar = $this->get_avatar_url();
        if (strpos($avatar, '/group-categories/') !== FALSE) {
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
        $avatar_hash = get_post_meta($this->id,'peepso_group_category_avatar_hash', TRUE);

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
        update_post_meta($this->id,'peepso_group_category_avatar_hash', $avatar_hash);

        $dest_thumb = $dir . $avatar_hash . '-avatar.jpg';
        $dest_full = $dir . $avatar_hash . '-avatar-full.jpg';
        $dest_orig = $dir . $avatar_hash . '-avatar-orig.jpg';

        // end of #1740 randomize filename

        if (file_exists($src_thumb) && file_exists($src_full) && file_exists($src_orig)) {
            rename($src_thumb, $dest_thumb);
            rename($src_full, $dest_full);
            rename($src_orig, $dest_orig);
        }
    }

    /** ** ** ** ** COVER ** ** ** ** **/

    /*
     * Return the group category's cover photo
     * @return string href value for the group's cover photo
     */
    public function get_cover_url()
    {
        $cover = $this->get_cover_default();

        $group_category_cover_photo = get_post_meta($this->id, 'group_category_cover_photo', TRUE);
        if (!empty($group_category_cover_photo)) {
            $cover = $group_category_cover_photo;
        }

        return ($cover);
    }

    /**
     * Get current group category's default cover image.
     *
     * @return string
     */
    public function get_cover_default()
    {
        return PeepSo::get_asset('images/cover/group-category-default.png');
    }

    /*
     * Check if group category has uploaded a custom cover photo
     * @return Boolean TRUE if custom cover photo, otherwise FALSE
     */
    public function has_cover()
    {
        $cover = $this->get_cover_url();

        if (FALSE !== stripos($cover, '/group-categories/')) {
            return (TRUE);
        }
        return (FALSE);
    }

    /**
     * Display the current group category's cover photo position percentage.
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
    public function move_cover_file($src_file)
    {
        $dir = $this->get_image_dir();

        // remove old cover
        $this->delete_cover_photo();

        $hash = substr(md5(time()), 0, 10);
        $dest_file = $dir . $hash . '-cover.jpg';
        update_post_meta($this->id,'peepso_group_category_cover_hash', $hash);

        $si = new PeepSoSimpleImage();
        $si->convert_image($src_file);
        $si->load($src_file);
        $si->resizeToWidth(PeepSo::get_option('cover_width', 3000));
        $si->save($dest_file, IMAGETYPE_JPEG);

        $image = wp_get_image_editor($dest_file);
        $image->set_quality(PeepSo::get_option('cover_quality', 85));
        $image->save($dest_file, IMAGETYPE_JPEG);

        update_post_meta($this->id, 'group_category_cover_photo', $this->get_image_url() . $hash . '-cover.jpg');

        delete_post_meta($this->id, 'peepso_cover_position_x');
        delete_post_meta($this->id, 'peepso_cover_position_y');
    }

    /*
     * Deletes the group's cover photo and removes the database entry
     */
    public function delete_cover_photo($cover_hash = '')
    {
        $ret = FALSE;

        if (!$cover_hash) {
            $cover_hash = get_post_meta($this->id,'peepso_group_category_cover_hash', TRUE);
            $cover_hash = $cover_hash ? $cover_hash : '';
        }

        $cover_file = $this->get_image_dir() . $cover_hash . '-cover.jpg';
        if (file_exists($cover_file)) {
            unlink($cover_file);
            $ret = TRUE;
        }

        delete_post_meta($this->id, 'group_category_cover_photo');
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
        $dir = PeepSo::get_peepso_dir() . 'group-categories' . DIRECTORY_SEPARATOR . $this->id . DIRECTORY_SEPARATOR;

        // Make sure the dir exists
        $this->_make_user_dir($dir);

        return ($dir);
    }
    public function get_image_url()
    {
//		$dir = WP_CONTENT_URL . '/peepso/groups/' . $this->id . '/';
        $dir = PeepSo::get_peepso_uri() . 'group-categories/' . $this->id . '/';
        return ($dir);
    }

}
