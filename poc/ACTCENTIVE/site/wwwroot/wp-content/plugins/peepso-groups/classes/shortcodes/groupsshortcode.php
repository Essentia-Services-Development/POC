<?php

class PeepSoGroupsShortcode
{
	const SHORTCODE	= 'peepso_groups';

	private static $_instance 	= NULL;
	public $url 				= NULL;

    public $group				= NULL;
    public $group_id 			= NULL;
    public $group_user 			= NULL;
    public $group_segment_id 	= NULL;

    public $group_category				= NULL;
    public $group_category_id 			= NULL;
    public $group_category_segment_id 	= NULL;




    private function __construct()
    {
		add_filter('peepso_page_title', array(&$this,'peepso_page_title'));
		add_action('peepso_group_segment_settings', array(&$this, 'filter_group_segment_settings'));
		add_action('peepso_group_segment_members', array(&$this, 'filter_group_segment_members'));
		add_filter('peepso_page_title_check', array(&$this, 'peepso_page_title_check'));

		add_action('peepso_group_category_segment_groups', array(&$this, 'filter_group_category_segment_groups'));
	}

	public static function get_instance()
	{
		if (NULL === self::$_instance) {
			self::$_instance = new self();
		}
		return (self::$_instance);
	}


	public function set_page( $url )
	{
		if(!$url instanceof PeepSoUrlSegments) {
            return;
        }

        add_action('wp_enqueue_scripts', array(PeepSoGroupsPlugin::get_instance(), 'enqueue_scripts'));
        $this->url = $url;

		$group_id = $url->get(1);
		// Attempting a single group view
		// A group ID can be numeric or a string (unique URL identifier)
		if( strlen($group_id) ) {

		    // Group Category view
		    if('category' == $group_id) {

                // Assume error: not found / unpublished / otherwise not accessible
                $this->group_category_id = FALSE;

                $this->group_category = new PeepSoGroupCategory($url->get(2));

                // Group found
                if ($this->group_category->id) {
                    $this->group_category_id = $this->group_category->id;
                    $this->group_category_segment_id = $url->get(3);

                    // unregister "groups" listing
                    remove_shortcode(self::SHORTCODE);

                    // replace with "category" view
                    add_shortcode(self::SHORTCODE, array(self::get_instance(), 'shortcode_group_category'));
                } else {
                    // 404
                    $this->group_id = FALSE;
                }
            } else {
                // Group view

                // Assume error: not found / unpublished / otherwise not accessible
                $this->group_id = FALSE;

                $this->group = new PeepSoGroup($group_id);

                // Group found
                if ($this->group->id) {
					status_header(200);

                    $this->group_id = $this->group->id;
                    $this->group_segment_id = $url->get(2);

                    $this->group_user = new PeepSoGroupUser($this->group_id);

                    // Can current user access this group?
                    if ($this->group_user->can('access')) {

                        // unregister "groups" listing
                        remove_shortcode(self::SHORTCODE);
                        // replace with "group" view
                        add_shortcode(self::SHORTCODE, array(self::get_instance(), 'shortcode_group'));
                    } else {
                        // if user doesn't have an access just display groups not found
                        $this->group_id = FALSE;
                    }
                }
            }
		}
	}

    public function peepso_page_title( $title )
    {
        if(self::SHORTCODE == $title['title'] || $title['title'] == 'peepso_activity') {

			$title['newtitle'] = __('Groups', 'groupso');

			if( $this->group_id ) {
				$title['newtitle'] = $this->group->name;
			}
		}

        return $title;
    }


    public static function description() {
	    return __('Displays the Groups listing and single Group view.','groupso');
    }

    public static function post_state() {
        return _x('PeepSo', 'Page listing', 'groupso') . ' - ' . __('Groups', 'groupso');
    }

	/**
	 * Registers the callback function for the peepso_messages shortcode.
	 */
	public static function register_shortcodes()
	{
		add_shortcode(self::SHORTCODE, array(self::get_instance(), 'shortcode_groups'));
	}


    public function shortcode_group_category() {

        PeepSo::reset_query();

        $ret = PeepSoTemplate::get_before_markup();

        $args = array(
            'group_category' => $this->group_category,
            'group_category_segment' => $this->group_category_segment_id,
        );

        add_action('peepso_activity_dialogs', array(&$this, 'upload_dialogs_category'));

        if(strlen($this->group_category_segment_id)) {
            ob_start();
            do_action('peepso_group_category_segment_'.$this->group_category_segment_id, $args, $this->url);
            $ret .= ob_get_clean();
        } else {

            // activity filters & hooks
            add_filter('peepso_activity_meta_query_args', array(&$this, 'activity_meta_query_args'), 10, 2);

            $ret .= PeepSoTemplate::exec_template('groups', 'group-category', $args,TRUE);
        }

		// Group category page script.
		wp_enqueue_script('peepso-groups-page-category',
			PeepSo::get_asset('js/page-category.min.js', dirname(dirname(__FILE__))),
			array('jquery-ui-draggable', 'peepso', 'peepso-fileupload'), PeepSoGroupsPlugin::PLUGIN_VERSION, TRUE);

		add_filter('peepso_data', function( $data ) {
			$category_data = array(
				'id'                  => $this->group_category->get('id'),
				'slug'                => $this->group_category->get('slug'),
				'name'                => $this->group_category->get('name'),
				'has_avatar'          => $this->group_category->has_avatar() ? TRUE : FALSE,
				'img_avatar'          => $this->group_category->get_avatar_url(),
				'img_avatar_default'  => $this->group_category->get_default_avatar_url(),
				'img_avatar_original' => $this->group_category->get_avatar_url_orig(),
				'avatar_nonce'        => wp_create_nonce('group-category-avatar'),
				'has_cover'           => $this->group_category->has_cover() ? TRUE : FALSE,
				'img_cover'           => $this->group_category->get_cover_url(),
				'img_cover_default'   => $this->group_category->get_cover_default(),
				'cover_nonce'         => wp_create_nonce('group-category-cover'),
				'text_error_filetype' => __('The file type you uploaded is not allowed. Only JPEG, PNG, and WEBP allowed.', 'groupso'),
				'text_error_filesize' => sprintf(
					__('The file size you uploaded is too big. The maximum file size is %s.', 'groupso'),
					'<strong>' . PeepSoGeneral::get_instance()->upload_size() . '</strong>'
				)
			);

			$data['group_category'] = array_merge(
				$category_data,
				array(
					'template_avatar'       => PeepSoTemplate::exec_template('groups', 'dialog-category-avatar', array( 'data' => $category_data ), TRUE),
					'template_cover_remove' => PeepSoTemplate::exec_template('groups', 'dialog-category-cover-remove', array(), TRUE),
				)
			);

			return $data;
		}, 10, 1);

		$has_avatar = $this->group_category->has_avatar() ? TRUE : FALSE;
		$avatar_url = $this->group_category->get_avatar_url();
		$avatar_url_default = $this->group_category->get_default_avatar_url();

		$has_cover = FALSE;
		$cover_url = $this->group_category->get_cover_url();
		if ( FALSE !== stripos($cover_url, 'peepso/group-categories/') ) {
			$has_cover = TRUE;
		}

		// Group category page data.
		wp_localize_script('peepso-groups-page-category', 'peepsoGroupCategory', array(
			'id' => $this->group_category->get('id'),
			'name' => $this->group_category->get('name'),
			'avatar' => $avatar_url,
			'avatarUploadedImage' => $has_avatar ? $this->group_category->get_avatar_url_orig() : NULL,
			'avatarDefault' => $avatar_url_default,
			'cover' => $cover_url,
			'coverUploadedImage' => $has_cover ? $cover_url : NULL
		));

        $ret .= PeepSoTemplate::get_after_markup();

        return ($ret);
    }

	public function shortcode_group()
	{
		// Can current user access a particular group segment?
		if(!$this->group_user->can('access_segment', $this->group_segment_id)) {

			// Show content unavailable when guest visit any type of group
			if(!get_current_user_id()) {
				$ret = PeepSoTemplate::get_before_markup();
				$ret .= PeepSoTemplate::do_404();
				$ret .= PeepSoTemplate::get_after_markup();

				return ($ret);
			}

			// Force activity screen redirect if the segment is inaccessible
			PeepSo::redirect($this->group->get('url'));
			exit();
		}

		PeepSo::reset_query();

		$ret = PeepSoTemplate::get_before_markup();

		$args = array(
			'group' => $this->group,
			'group_segment' => $this->group_segment_id
		);

		if(!isset($this->url) || !($this->url instanceof PeepSoUrlSegments)) {
            $this->url = PeepSoUrlSegments::get_instance();
        }

        add_action('peepso_activity_dialogs', array(&$this, 'upload_dialogs'));

		if(strlen($this->group_segment_id)) {
			ob_start();
			do_action('peepso_group_segment_'.$this->group_segment_id, $args, $this->url);
			$ret .= ob_get_clean();
		} else {

			// activity filters & hooks
			add_filter('peepso_activity_meta_query_args', array(&$this, 'activity_meta_query_args'), 10, 2);

			$ret .= PeepSoTemplate::exec_template('groups', 'group', $args,TRUE);
		}


		$ret .= PeepSoTemplate::get_after_markup();

		return ($ret);
	}

	public function activity_meta_query_args($args, $module_id) {
		if($module_id === PeepSoGroupsPlugin::MODULE_ID) {
			array_push($args['meta_query'],
				array(
					'compare' => '=',
					'key' => 'peepso_group_id',
					'value' => $this->group->id,
					)
				);
		}

		return $args;
	}

    public function filter_group_segment_settings($args)
    {
        $PeepSoUrlSegments = PeepSoUrlSegments::get_instance();
        if($tab = $PeepSoUrlSegments->get(3)) {
            PeepSoTemplate::exec_template('groups', 'group-settings-'.$tab, $args);
            return;
        }

        echo PeepSoTemplate::exec_template('groups', 'group-settings', $args);
    }

    public function filter_group_category_segment_groups($args)
    {
        echo PeepSoTemplate::exec_template('groups', 'group-category-groups', $args);
    }

	public function filter_group_segment_members($args)
	{

		if (!$this->group_user->can('view_users')) {
            return;
		}

		wp_enqueue_script('peepso-page-group-members',
			PeepSo::get_asset('js/page-group-members.js', dirname(dirname(__FILE__))),
			array('peepso', 'peepso-page-autoload'), PeepSo::PLUGIN_VERSION, TRUE);

		$PeepSoUrlSegments = PeepSoUrlSegments::get_instance();

		$tab = $PeepSoUrlSegments->get(3);

		if('pending' == $tab) {
			echo PeepSoTemplate::exec_template('groups', 'group-members-pending', $args);
			return;
		}

		if('invited' == $tab) {
			echo PeepSoTemplate::exec_template('groups', 'group-members-invited', $args);
			return;
		}

		if('banned' == $tab) {
			echo PeepSoTemplate::exec_template('groups', 'group-members-banned', $args);
			return;
		}

		if('management' == $tab) {
			echo PeepSoTemplate::exec_template('groups', 'group-members-management', $args);
			return;
		}

		echo PeepSoTemplate::exec_template('groups', 'group-members', $args);
	}

	public function shortcode_groups()
	{
		$allow_guest_access = PeepSo::get_option('groups_allow_guest_access_to_groups_listing', 0);

		PeepSo::reset_query();

		$ret = PeepSoTemplate::get_before_markup();

		// list / search groups
		$input = new PeepSoInput();
		$search = $input->value('filter', NULL, FALSE); // SQL Safe
		$category = $input->int('category', 0);

		$num_results = 0;

		// special case - 404, group hidden, you've been banned etc
		if( FALSE === $this->group_id ) {
			$ret .= PeepSoTemplate::do_404();
		} else {
			$ret .= PeepSoTemplate::exec_template('groups', 'groups', array('search' => $search, 'num_results' => $num_results, 'category' => $category, 'allow_guest_access' => $allow_guest_access), TRUE);
		}

		$ret .= PeepSoTemplate::get_after_markup();

		return ($ret);
	}

    /**
     * callback - peepso_activity_dialogs
     * Renders the dialog boxes for uploading profile and cover photo.
     */
    public function upload_dialogs()
    {
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_style('peepso-fileupload');
    }

    /**
     * callback - peepso_activity_dialogs
     * Renders the dialog boxes for uploading profile and cover photo.
     */
    public function upload_dialogs_category()
    {
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_style('peepso-fileupload');
    }

	/**
	 * todo:docblock
	 */
	public function peepso_page_title_check($post) {
		$url = PeepSoUrlSegments::get_instance();
		$post_slug = $url->get(1);
		$group_id = '';

		if (!empty($post_slug)) {
			$args = array(
				'name'        => $post_slug,
				'post_type'   => PeepSoGroup::POST_TYPE,
				'post_status' => 'publish',
				'numberposts' => 1
			);

			$groups = get_posts($args);

			if (count($groups) == 1) {
				$this->group_id = $group_id = $groups[0]->ID;
				$this->group = new PeepSoGroup($group_id);
			}
		}

		if (((isset($post->post_content) && strpos($post->post_content, '[peepso_groups]') !== FALSE) && !empty($group_id)) && !is_front_page()) {
			return TRUE;
		}

		return $post;
	}

}

// EOF
