<?php

class PeepSoVideosAjax extends PeepSoAjaxCallback
{
    private $_aws_error = NULL;                     // last error from AWS
    public static $notices = array();               // error messages to be returned to user
    private static $_peepsovideos = NULL;

    public $allowed_mime_types = array(
        // video
        'video/x-ms-asf',
        'video/x-ms-wmv',
        'video/x-ms-wmx',
        'video/x-ms-wm',
        'video/avi',
        'video/divx',
        'video/x-flv',
        'video/quicktime',
        'video/mpeg',
        'video/mp4',
        'video/ogg',
        'video/webm',
        'video/x-matroska',
        'video/3gpp',
        'video/3gpp2',
    );

    public $allowed_mime_types_audio = array(
        // audio
        'audio/mpeg',
        'audio/m4a',
        'audio/aac',
        'audio/x-realaudio',
        'audio/wav',
        'audio/ogg',
        'audio/flac',
        'audio/midi',
        'audio/x-ms-wma',
        'audio/x-ms-wax',
        'audio/x-matroska'
    );

	protected function __construct()
	{
		parent::__construct();
		self::$_peepsovideos = PeepSoVideos::get_instance();
	}

    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        return apply_filters('peepso_videos_ajax_auth_exceptions', array());;
    }

	/**
	 * Returns the video preview as HTML, if the url is not valid returns success false.
	 * @param  PeepSoAjaxResponse $resp
	 */
	public function get_preview(PeepSoAjaxResponse $resp)
	{
	    // SQL safe, parsed immediately
		$url = trim($this->_input->value('url', '', FALSE));
        $accepted_type = trim($this->_input->value('accepted_type', 'video', array('audio','video')));

		$response = self::$_peepsovideos->parse_oembed_url($url, $accepted_type);

		// make iframe full-width
		if (preg_match('/<iframe/i', $response['content'])) {
			$width_pattern = "/width=\"[0-9]*\"/";
			$response['content'] = preg_replace($width_pattern, "width='100%'", $response['content']);
			$response['content'] = '<div class="ps-media ps-media--iframe ps-media-iframe">' . $response['content'] . '</div>';
		}

		if (FALSE === $response) {
			$resp->success(FALSE);
            if ($accepted_type == 'video') {
                $resp->error(__('Sorry, this is not a valid video.<br><small>Please make sure you\'re using a valid URL, not an embed code.</small>', 'vidso'));
            } else {
                $resp->error(__('Sorry, this is not a valid audio.<br><small>Please make sure you\'re using a valid URL, not an embed code.</small>', 'vidso'));
            }
		} else {
			$resp->success(TRUE);
			$resp->set('content', PeepSoTemplate::exec_template('activity', 'content-media', $response, TRUE));

			$resp->set('title', $response['title']);
			$resp->set('host', $response['host']);
			$resp->set('url', $response['url']);
			$resp->set('description', $response['description']);
			$resp->set('target', $response['target']);

			$resp->set('html', PeepSoTemplate::exec_template('activity', 'content-media', $response, TRUE));
		}
	}

	/**
	 * Post a content with Video
	 * @param  PeepSoAjaxResponse $resp
	 */
	public function post_video(PeepSoAjaxResponse $resp)
	{
		$postbox = PeepSoPostbox::get_instance();
		add_action('peepso_activity_after_add_post', array(self::$_peepsovideos, 'after_add_post'));
		add_filter('peepso_activity_insert_data', array(self::$_peepsovideos, 'activity_insert_data'));

		$postbox->post($resp);

		remove_action('peepso_activity_after_add_post', array(self::$_peepsovideos, 'after_add_post'));
		remove_filter('peepso_activity_insert_data', array(self::$_peepsovideos, 'activity_insert_data'));

		$resp->success(TRUE);
	}


    public function get_user_videos(PeepSoAjaxResponse $resp)
    {
        $page = $this->_input->int('page', 1);
        $sort = $this->_input->value('sort', 'desc', array('asc','desc'));

        $limit = $this->_input->int('limit', 1);
        $offset = ($page - 1) * $limit;

        if ($page < 1) {
            $page = 1;
            $offset = 0;
        }

        $owner = $this->_input->int('user_id');
        $module_id = $this->_input->int('module_id', 0);

        $videos_model = new PeepSoVideosModel();
        $media_type = 'all';
        $videos = $videos_model->get_user_videos($owner, $media_type, $offset, $limit, $sort, $module_id);

        ob_start();

        if (count($videos)) {
            foreach ($videos as $video) {
                echo PeepSoTemplate::exec_template('videos', 'video-item-page', (array)$video);
            }

            $resp->success(1);
            $resp->set('found_videos', count($videos));
            $resp->set('videos', ob_get_clean());
        } else {
            $resp->success(FALSE);

            $owner_name = PeepSoUser::get_instance($owner)->get_firstname();
            if ($module_id != 0) {
            	$owner_name = apply_filters('peepso_videos_filter_owner_name', $owner);
            }

            $message = (($module_id == 0) && (get_current_user_id() == $owner)) ? __('You don\'t have any videos yet', 'vidso') : sprintf(__('%s doesn\'t have any videos yet', 'vidso'), $owner_name);

            $resp->error(PeepSoTemplate::exec_template('profile','no-results-ajax', array('message'=> $message), TRUE));
        }


    }

    public function get_user_videos_count(PeepSoAjaxResponse $resp)
    {
        $videos_model = new PeepSoVideosModel();

        $media_type = 'all';

        $resp->success(1);
        $resp->set('found_videos', $videos_model->get_num_videos($this->_input->int('user_id'), $media_type));
    }

    /** Videos upload */


    /**
     * Called before uploading a video to the tmp directory
     * @param  PeepSoAjaxResponse $resp
     */
    public function validate_video_upload(PeepSoAjaxResponse $resp)
    {
        $is_audio = $this->_input->int('is_audio', 0);

        if ($is_audio) {
            $max_upload_size = intval(PeepSo::get_option('videos_audio_max_upload_size'));
        } else {
            $max_upload_size = intval(PeepSo::get_option('videos_max_upload_size'));
        }
        // $daily_limit = intval(PeepSo::get_option('videos_daily_video_upload_limit'));
        // $max_upload_limit = intval(PeepSo::get_option('videos_max_user_video'));

        // use WP max upload size if it is smaller than PeepSo max upload size
        $wp_max_size = max(wp_max_upload_size(), 0);
        $wp_max_size /= pow(1024, 2);
        if ($wp_max_size < $max_upload_size) {
            $max_upload_size = $wp_max_size;
        }

        $user = PeepSoUser::get_instance(get_current_user_id());
        $videos_model = new PeepSoVideosModel();
        $videos_count_today = $videos_model->count_author_post($user->get_id(), TRUE) + 1;
        $error = NULL;

        // if ($videos_count_today > $daily_limit && 0 !== $daily_limit)
        //     $error = __('Maximum daily video upload quota reached. Delete posts with videos to free some space.', 'vidso');

        // $videos_count = $videos_model->count_author_post($user->get_id()) + 1;
        // if ($videos_count >= $max_upload_limit && 0 != $max_upload_limit)
        //     $error = __('Maximum video upload quota reached. Delete posts with videos to free some space.', 'vidso');

        if ($this->_input->int('size', 0) >= $max_upload_size * 1048576) {
            $error = sprintf(__('Only files up to %1$dMB are allowed.', 'vidso'), $max_upload_size);
        } else if (!$videos_model->video_size_can_fit($user->get_id(), $this->_input->int('size', 0), $is_audio)) {
            $type = ($is_audio) ? __('audio', 'vidso') : __('videos', 'vidso');
            $error = sprintf( __('Maximum file upload quota reached. Delete posts with %s to free some space.', 'vidso'), $type);
        }

        $resp->success(TRUE);
        if (NULL !== $error) {
            $resp->error($error);
            $resp->success(FALSE);
        }
    }

    /**
     * Saves the uploaded video to the USER_ID/videos/tmp folder and returns the unique filename
     * also performs validation
     * @param  PeepSoAjaxResponse $resp
     */
    public function upload_video(PeepSoAjaxResponse $resp)
    {
        if (count($_FILES) > 0 && isset($_FILES['filedata'])) {

            $user = PeepSoUser::get_instance(get_current_user_id());
            $videos_model = new PeepSoVideosModel();

            $is_audio = $this->_input->int('is_audio', 0);

            if (!$videos_model->video_size_can_fit($user->get_id(), $this->_input->int('size', 0), $is_audio)) {
                $resp->error(__('Maximum file upload quota reached. Delete posts with videos to free some space', 'vidso'));
                $resp->success(FALSE);
                return;
            }

            $image_dir = $videos_model->get_video_dir();
            if (!is_dir($image_dir)) {
                mkdir($image_dir, 0755, TRUE);
            }

            $tmp_file = $videos_model->get_tmp_file($_FILES['filedata']['name']);
            $mime_allowed = $this->allowed_mime_types;
            if ($is_audio) {
                $mime_allowed = $this->allowed_mime_types_audio;
            }
            $filetype = wp_check_filetype($tmp_file['path'], PeepSoVideosUpload::$allowed_mime_types);
            if (isset($filetype['type']) && !in_array($filetype['type'], $mime_allowed)) {
                $resp->error(__('Invalid filetype', 'vidso'));
                $resp->success(FALSE);
                return;
            }

            $do_conversion = PeepSo::get_option('videos_conversion_mode', 'no');
            if ($do_conversion == 'no' && !$is_audio) {

                $allowed_extensions = PeepSoVideosUpload::no_conversion_mode_filetypes();

                if (is_array($allowed_extensions) && count($allowed_extensions)) {

                    $test_extension = strtolower($filetype['ext']);

                    if (!in_array($test_extension, $allowed_extensions)) {
                        $resp->error(__('File extension is not allowed', 'vidso'));
                        $resp->success(FALSE);
                        return;
                    }
                }
            }

            $filehash = md5($tmp_file['name'] . time());
            $filename =  $filehash . '.' . $filetype['ext'];

            $tmp_file['path'] = str_replace($tmp_file['name'], $filename, $tmp_file['path']);
            $tmp_file['name'] = $filename;

            move_uploaded_file($_FILES['filedata']['tmp_name'], $tmp_file['path']);

            $file = $tmp_file['path'];

            if(!file_exists($file)) {
                $error = __('Failed to upload media.', 'vidso');

                $resp->error($error);
                $resp->success(FALSE);
            } else {

                $resp->set('file', $filename);
                $resp->set('is_audio', $is_audio);
                $resp->success(TRUE);
            }

        }
    }
}

// EOF
