<?php

class PeepSoVideosAdmin
{
	private static $_instance = NULL;

	private function __construct()
	{
		add_filter('peepso_report_column_title', array(&$this, 'report_column_title'), 10, 3);
		add_filter('peepso_config_email_messages', array(&$this, 'config_email'));
		add_filter('peepso_config_email_messages_defaults', array(&$this, 'config_email_messages_defaults'));
	}

	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}
	
	/**
	 * Get the data from the $item based on the given $column_name
	 * @param string $title The default title
	 * @param array $item An array of single report row/item
	 * @param string $column_name The field or column name
	 * @return string
	 */
	public function report_column_title($title, $item, $column_name)
	{
		if ('post_title' === $column_name) {

			if (PeepSoVideos::MODULE_ID === intval($item['rep_module_id'])) {
				PeepSoVideosModel::get_video($item['rep_external_id']);

				$title = sprintf(
					__('Video of post %s', 'vidso'),
					'<a href="' . PeepSo::get_page('activity') . '?status/' . $item['post_name'] . '/" target="_blank">' . $item['post_name'] . '</a>');
				
			} else if (PeepSoSharePhotos::MODULE_ID === intval($item['rep_module_id']))
				$title = '<a href="' . PeepSo::get_page('activity') . '?status/' . $item[$column_name] . '/" target="_blank">' . $item[$column_name] . '</a>';
		}

		return ($title);
	}	

    /**
     * Add the Like/comment/share video emails to the list of editable emails on the config page
     * @param  array $emails Array of editable emails
     * @return array
     */
    public static function config_email($emails)
    {
        // @TODO CLEANUP

//        $emails['email_like_video'] = array(
//            'title' => __('Like Video', 'vidso'),
//            'description' => __('This will be sent when a user "likes" another user\'s video.', 'vidso')
//        );
//
//        $emails['email_user_comment_video'] = array(
//			'title' => __('User Comment Video', 'vidso'),
//			'description' => __('This will be sent to a video owner when another user comments on the video', 'vidso')
//		);
//
//		if (PeepSo::get_option('site_repost_enable', TRUE)) {
//			$emails['email_share_video'] = array(
//				'title' => __('User Share video', 'vidso'),
//				'description' => __('This will be sent to a video owner when another user share the video', 'vidso')
//			);
//		}

		$emails['email_video_conversion_complete'] = array(
			'title' => __('Video Conversion Complete', 'vidso'),
			'description' => __('This will be sent to a video owner when video conversion is complete', 'vidso')
		);

		$emails['email_video_conversion_failed'] = array(
			'title' => __('Video Conversion Failed', 'vidso'),
			'description' => __('This will be sent to a video owner when video conversion is failed', 'vidso')
		);
        return ($emails);
    }

    public function config_email_messages_defaults( $emails )
    {
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../install' . DIRECTORY_SEPARATOR . 'activate.php');
        $install = new PeepSoVideosInstall();
        $defaults = $install->get_email_contents();

        return array_merge($emails, $defaults);
    } 
}

// EOF
