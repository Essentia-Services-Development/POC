<?php
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');
/*
 * Performs installation process
 * @package PeepSoVideos
 * @author PeepSo
 */
class PeepSoVideosInstall extends PeepSoInstall
{
	protected $default_config = array(
		'videos_allowed_user_space' => '0',
		'videos_audio_allowed_user_space' => '0',
        'videos_max_upload_size' => '20',
		'videos_audio_max_upload_size' => '20',
        'max_video_length' => 0,
        'videos_allowed_extensions' => "mp4" . PHP_EOL . "mov",
        // 'videos_max_user_video' => '0',
        // 'videos_daily_video_upload_limit' => '0',
		);
		
	const DBVERSION_OPTION_NAME = 'peepso_videos_database_version';
	const DBVERSION = '2';
	/*
	 * called on plugin activation; performs all installation tasks
	 */
	public function plugin_activation( $is_core = FALSE )
	{
		parent::plugin_activation($is_core);
		return (TRUE);
	}

	public static function get_table_data()
	{
		$aRet = array(
			'videos' => "
				CREATE TABLE videos (
					vid_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					vid_album_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
					vid_post_id BIGINT(20) UNSIGNED NOT NULL,
					vid_acc TINYINT(1) UNSIGNED DEFAULT 0,
					vid_stored TINYINT(1) UNSIGNED DEFAULT 0,
					vid_stored_failed TINYINT(1) UNSIGNED DEFAULT 0,
					vid_title VARCHAR(200),
					vid_artist VARCHAR(200),
					vid_album VARCHAR(200),
					vid_description TEXT,
					vid_thumbnail TEXT,
					vid_animated TEXT,
					vid_animated_webm TEXT,
					vid_url TEXT,
					vid_embed TEXT,
					vid_size INT(11) UNSIGNED,
					vid_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					vid_token VARCHAR(200) NULL,
					vid_module_id INT(11) UNSIGNED DEFAULT 0,
					vid_conversion_status TINYINT(1) NOT NULL DEFAULT 0,
					vid_upload_s3_status TINYINT(1) NOT NULL DEFAULT 0,
					vid_upload_s3_retry_count TINYINT(1) NOT NULL DEFAULT 0,
					vid_error_messages TEXT,
					vid_transcoder_job_id VARCHAR(200),
					PRIMARY KEY (vid_id),
					INDEX post (vid_post_id)
				) ENGINE=InnoDB",
		);

		return $aRet;
	}

	/*
	 * return default email templates
	 */
	public function get_email_contents()
	{
		$emails = array(
			'email_like_video' => "Hello {userfirstname},

{fromfirstname} likes your video!

You can see all of your notifications here:
{permalink}

Thank you.",
			'email_user_comment_video' => "Hello {userfirstname},

{fromfirstname} had something to say about your video!

You can see the video here:
{permalink}

Thank you.",
			'email_share_video' => "Hello {userfirstname},

{fromfirstname} had shared your video!

You can see the post here:
{permalink}

Thank you.",
			'email_video_conversion_complete' => "Hello {userfirstname},

Your video is available now. You can see it here:
{permalink}

Thank you.",
			'email_video_conversion_failed' => "Hello {userfirstname},

Your video is failed to convert, please try again.

Thank you.");
		
		return ($emails);
	}	

	protected function migrate_database_tables()
	{
		$current = intval(get_option(self::DBVERSION_OPTION_NAME, -1));
		if (-1 === $current) {
			$current = 0;
			add_option(self::DBVERSION_OPTION_NAME, $current, NULL, 'no');
		}

		global $wpdb;
		$wpdb->query('START TRANSACTION');	// start the transaction

		$rollback = FALSE;

		switch ($current)
		{
			case 0:
				$sql = "ALTER TABLE {$wpdb->prefix}peepso_videos CHANGE vid_url vid_url TEXT NULL DEFAULT NULL";
					$wpdb->query($sql);
			// fall through to next migration, if it exists
			case 1:
		}

		// finalize the transaction
		if ($rollback)
			$wpdb->query('ROLLBACK');
		else
			$wpdb->query('COMMIT');				// commit the database changes

		// set the dbversion in the option so we don't keep migrating
		update_option(self::DBVERSION_OPTION_NAME, self::DBVERSION);
	}
}