<?php

class PeepSoVideosYoutube
{
	private static $_instance = NULL;

	private function __construct()
	{
		add_filter('peepso_videos_attachment', array(&$this, 'videos_attachment'), 10, 2);

		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
	}

	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}

	/**
	 * enqueue scripts for peepsovideosyoutube
	 */
	public function enqueue_scripts()
	{
		// wp_register_script('peepsovideosyoutubeiframeapi', 'https://www.youtube.com/iframe_api', NULL, PeepSoVideos::PLUGIN_VERSION, TRUE);
		// wp_register_script('peepsovideosyoutube', PeepSoVideos::get_plugin_dir() . 'assets/js/peepso-youtube.min.js', array('peepsovideosyoutubeiframeapi'), PeepSoVideos::PLUGIN_VERSION, TRUE);
	}

	/**
	 * Attach videos to html
	 * @param array $video
	 * @param object $post WP_Post object
	 */
	public function videos_attachment($video, $post)
	{
		$video_id = $this->_get_video_id($video['url']);
		$unique_id = uniqid();

		if ($video_id && ps_isempty($post->is_repost)) {
			// Display the container for the iframe player
			echo '<div class="cstream-attachment" style="display: none;"><div id="peepso-youtube-player-', $video_id, '-', $unique_id, '" style="width: 100%"></div></div>';

			$video['content'] = sprintf('
				<div class="video-thumbnail ex1">
				    <a href="#" onclick="ps_videos.play_youtube_video(this); return false;" data-post-id="%2$d" data-video-id="%1$s" data-unique-id="%3$s">
				        <div class="image">
				        	<img src="http://i.ytimg.com/vi/%1$s/hqdefault.jpg" alt="" />
					        <span class="play">
					            <span></span>
					        </span>
				        </div>
				    </a>
				</div>
			', $video_id, $post->ID, $unique_id);
		}

		return ($video);
	}

	/**
	 * Generate/extract video id based on video URL
	 * @param string $url video url
	 * @return mixed string as id otherwise FALSE
	 */
	private function _get_video_id($url)
	{
		if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match))
    		return ($match[1]);
		return (FALSE);
	}
}

// EOF
