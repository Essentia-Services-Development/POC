<?php

class PeepSoVideosVimeo
{
	private static $_instance = NULL;

	private function __construct()
	{
		add_filter('peepso_videos_attachment', array(&$this, 'videos_attachment'), 10, 2);
	}

	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}

	/**
	 * Attach videos to html
	 * @param array $video
	 * @param object $post WP_Post object
	 */
	public function videos_attachment($video, $post)
	{
		$video_id = $this->get_video_id($video['url']);

		if ($video_id) {
			echo '<div class="cstream-attachment" style="display: none;"><div style="width: 100%"></div></div>';

			$video['content'] = '
				<div class="video-thumbnail">
				    <a href="#" onclick="ps_videos.play_vimeo_video(this, { id: ' . $video_id . ' }); return false;">
				        <div class="image">
				        	<img src="' . $video['thumbnail'] . '" alt="" />
					        <span class="play">
					            <span></span>
					        </span>
				        </div>
				    </a>
				</div>
			';
		}

		return ($video);
	}

	/**
	 * Generate/extract video id based on video URL
	 * @param string $url video url
	 * @return mixed string as id otherwise FALSE
	 */
	private function get_video_id($url)
	{
		if (preg_match('/vimeo\.com\/(\d+)\/?/i', $url, $match))
			return ($match[1]);

		return (FALSE);
	}
}

// EOF
