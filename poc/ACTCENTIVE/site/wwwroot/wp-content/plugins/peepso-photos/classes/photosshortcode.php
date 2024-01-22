<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UUU3dDdMMDhxUTUzVUw2WmdXYU9GRG9scnpndGRlUndrZnRwWFoxT2p0c20wbTVqeDZzY1NDemJXUTFuVEhPNHlvRE9TSDlMWVc1MXhxRUpObEY5TFhPZVFWUmJBMEovSk1vN1QyVFpUdWlrMnN3SzdQT1JIeVpsd21XYVhyUzBJOHRaVEJrWWREa3Z6WmZnYW03RHcw*/

class PeepSoPhotosShortcode
{
	const SHORTCODE_LATEST = 'peepsophotos_latest';
	const SHORTCODE_ALBUM  = 'peepsophotos_album';

	const SHORTCODE_GROUP_LATEST_PHOTOS = 'peepsophotos_group_latest_photos';
	const SHORTCODE_GROUP_ALBUM  = 'peepsophotos_group_album';

	public $url;

	private static $_instance = NULL;

	private $view_user_id = 0;
	private $view_group_id = 0;

    public $url_segments;

	private function __construct()
	{
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
	}

	/*
	 * retrieve singleton class instance
	 * @return instance reference to plugin
	 */
	public static function get_instance()
	{
		if (self::$_instance === NULL) {
            self::$_instance = new self();
        }

		return (self::$_instance);
	}

	/**
	 * Enqueues the scripts used in this plugin only.
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script('peepso-photos');
	}

	/**
	 * Registers the shortcodes to wordpress.
	 */
	public static function register_shortcodes()
	{
		add_shortcode(self::SHORTCODE_LATEST, array(self::get_instance(), 'shortcode_latest'));
		add_shortcode(self::SHORTCODE_ALBUM, array(self::get_instance(), 'shortcode_album'));
		add_shortcode(self::SHORTCODE_GROUP_LATEST_PHOTOS, array(self::get_instance(), 'shortcode_group_latest_photos'));
		add_shortcode(self::SHORTCODE_GROUP_ALBUM, array(self::get_instance(), 'shortcode_group_album'));
	}

	/**
	 * Displays the main photos page wrapped in the profile
	 */
	public function shortcode_latest()
	{
	    PeepSo::reset_query();

		$this->view_user_id = isset($this->view_user_id) ? $this->view_user_id : get_current_user_id();

		if (FALSE == apply_filters('peepso_access_content', TRUE, PeepSoPhotosShortcode::SHORTCODE_LATEST, PeepSoSharePhotos::MODULE_ID)) {
            return PeepSoTemplate::do_404();
		}

        wp_enqueue_script('peepso-photos');

        wp_enqueue_script('peepso-page-photos');

        return PeepSoTemplate::exec_template('photos', 'photos', array(
            'view_user_id' => $this->view_user_id,
            'current' => 'latest',
            'type' => 'photos'
        ), TRUE);
	}

	/**
	 * Displays the list album page.
	 */
	public function shortcode_album()
	{
		if (FALSE == apply_filters('peepso_access_content', TRUE, PeepSoPhotosShortcode::SHORTCODE_ALBUM, PeepSoSharePhotos::MODULE_ID)) {
			return PeepSoTemplate::do_404();
		}

        $photos_album_model = new PeepSoPhotosAlbumModel();

		$album_id = $this->url_segments->get(4);
		if ( !empty($album_id) ) {
			$album = $photos_album_model->get_photo_album($this->view_user_id, $album_id);
	        if ( 0 !== count($album)) {

	            wp_enqueue_script('peepso-photos');

	            $privacy = PeepSoPrivacy::get_instance();
	            $access_settings = $privacy->get_access_settings();

	            $the_album = $album[0];

				if (
						!PeepSo::is_admin() &&
						$the_album->pho_owner_id != get_current_user_id() &&
						( $the_album->pho_album_acc == PeepSo::ACCESS_PRIVATE ||
						( $the_album->pho_album_acc == PeepSo::ACCESS_MEMBERS && get_current_user_id() === 0 ) ||
						( class_exists('PeepSoFriendsPlugin') && $the_album->pho_album_acc == PeepSoFriendsPlugin::ACCESS_FRIENDS && PeepSoFriendsModel::get_instance()->are_friends($the_album->pho_owner_id, get_current_user_id()) === FALSE ) )
					) {
					return PeepSoTemplate::do_404();
				}

				$post_id = NULL;
	            $act_id = NULL;
	            if(intval($the_album->pho_system_album) === 0 ) {
	                global $post;

	                $post_id = $the_album->pho_post_id;

	                $PeepSoActivity = PeepSoActivity::get_instance();
	                $_activity = $PeepSoActivity->get_activity_data($post_id, PeepSoSharePhotos::MODULE_ID);
	                $act_id = $_activity->act_id;

	                $activity = $PeepSoActivity->get_activity($act_id);
	                $act_post = $PeepSoActivity->activity_get_post(NULL, $activity, $this->view_user_id, get_current_user_id());
	                setup_postdata($act_post);

	                $can_delete = PeepSo::is_admin() || ($the_album->pho_owner_id == get_current_user_id());
	                $can_edit = ($the_album->pho_owner_id == get_current_user_id());

	                $template = 'photo-album-custom';
	            } else {
	                $can_edit = FALSE;
	                $can_delete = FALSE;
	                $template = 'photo-album';
	            }

	            $view_user = PeepSoUser::get_instance($this->view_user_id);
	            $photos_url = $view_user->get_profileurl() . 'photos/album';

	            wp_enqueue_script('peepso-page-photos');

	            return PeepSoTemplate::exec_template('photos', $template, array(
	                'photos_url' => $photos_url,
	                'the_album' => $the_album,
	                'access_settings' => $access_settings,
					'album_id' => $album_id,
	                'post_ID' => $post_id,
	                'act_id' => $act_id,
	                'can_edit' => $can_edit,
	                'can_delete' => $can_delete), TRUE);
	        }
	        else
	        {
	            $user = PeepSoUser::get_instance($this->view_user_id);
	            PeepSo::redirect($user->get_profileurl() . 'photos');
	            die();
	        }
		} else {

			wp_enqueue_script('peepso-page-albums');

			return PeepSoTemplate::exec_template('photos', 'photos', array(
	            'view_user_id' => $this->view_user_id,
	            'current' => 'album',
	            'type' => 'albums'
	        ), TRUE);
		}
	}


	// Used to hook the shortcode methods into profile pages
	function profile_segment($view_user_id, $url_segments)
	{
		$this->url_segments = $url_segments;
		$this->view_user_id = $view_user_id;

		if ('album' == $this->url_segments->get(3)) {
			return $this->shortcode_album();
		}

		return $this->shortcode_latest();
	}




	/**
	 * Displays the main photos page wrapped in the profile
	 */
	public function shortcode_group_latest_photos($args)
	{
		$this->view_group_id = isset($this->view_group_id) ? $this->view_group_id : get_current_user_id();

		if (FALSE == apply_filters('peepso_access_content', TRUE, PeepSoPhotosShortcode::SHORTCODE_GROUP_LATEST_PHOTOS, PeepSoSharePhotos::MODULE_ID)) {
            return PeepSoTemplate::do_404();
		}

        wp_enqueue_script('peepso-photos');

        wp_enqueue_script('peepso-page-photos');

        return PeepSoTemplate::exec_template('photos', 'photos-group', array_merge(
        	array(
            'view_group_id' => $this->view_group_id,
            'current' => 'latest',
            'type' => 'photos'), $args
        ), TRUE);
	}

	/**
	 * Displays the list album page.
	 */
	public function shortcode_group_album($args)
	{
		if (FALSE == apply_filters('peepso_access_content', TRUE, PeepSoPhotosShortcode::SHORTCODE_GROUP_ALBUM, PeepSoSharePhotos::MODULE_ID)) {
            return PeepSoTemplate::do_404();
		}

        $photos_album_model = new PeepSoPhotosAlbumModel();

		$album_id = $this->url_segments->get(4);
		if ( !empty($album_id) ) {
			$album = $photos_album_model->get_photo_album($args['group']->id, $album_id, 0, PeepSoGroupsPlugin::MODULE_ID);
	        if ( 0 !== count($album)) {

	            wp_enqueue_script('peepso-photos');

	            $privacy = PeepSoPrivacy::get_instance();
	            $access_settings = $privacy->get_access_settings();

	            $the_album = $album[0];

	            $post_id = NULL;
	            $act_id = NULL;
				$act_post = NULL;
				
				$can_edit = $can_delete = $can_upload = FALSE;

	            if(intval($the_album->pho_system_album) === 0 ) {
	                global $post;

	                $post_id = $the_album->pho_post_id;

	                $PeepSoActivity = PeepSoActivity::get_instance();
	                $_activity = $PeepSoActivity->get_activity_data($post_id, PeepSoSharePhotos::MODULE_ID);
	                $act_id = $_activity->act_id;

	                $activity = $PeepSoActivity->get_activity($act_id);
	                $act_post = $PeepSoActivity->activity_get_post(NULL, $activity, $this->view_group_id, get_current_user_id());
	                setup_postdata($act_post);

					$can_edit = PeepSo::check_permissions(intval($act_post->post_author), PeepSo::PERM_POST_EDIT, get_current_user_id());
					$can_delete = PeepSo::check_permissions(intval($act_post->post_author), PeepSo::PERM_POST_DELETE, get_current_user_id());
					$can_upload = intval($post->post_author) === get_current_user_id() ? TRUE : FALSE;
	                $template = 'photo-group-album-custom';
	            } else {
	                $template = 'photo-group-album';
	            }

	            $group_user = new PeepSoGroupUser($args['group']->id);
				$group = new PeepSoGroup($args['group']->id);

	            $photos_url = $group->get_url() . 'photos/album';

	            wp_enqueue_script('peepso-page-photos');

	            return PeepSoTemplate::exec_template('photos', $template, array_merge(array(
	                'photos_url' => $photos_url,
	                'the_album' => $the_album,
	                'access_settings' => $access_settings,
					'album_id' => $album_id,
	                'post_ID' => $post_id,
	                'act_id' => $act_id,
	                'act_post' => $act_post,
					'can_edit' => $can_edit,
					'can_upload' => $can_upload,
	                'can_delete' => $can_delete), $args), TRUE);
	        }
	        else
	        {
				$group = new PeepSoGroup($args['group']->id);
	            PeepSo::redirect($group->get_url() . 'photos');
	            die();
	        }
		} else {

			wp_enqueue_script('peepso-page-albums');

			return PeepSoTemplate::exec_template('photos', 'photos-group', array_merge(array(
	            'view_group_id' => $args['group']->id,
	            'current' => 'album',
	            'type' => 'albums'
	        ), $args), TRUE);
		}
	}

	// Used to hook the shortcode methods into groups pages
	function group_segment($args, $url_segments)
	{
		$this->url_segments = $url_segments;
		$this->view_group_id = $args['group']->id;

		if ('album' == $this->url_segments->get(3)) {
			return $this->shortcode_group_album($args);
		}

		return $this->shortcode_group_latest_photos($args);
	}
}
