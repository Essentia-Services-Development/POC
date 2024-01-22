<?php

require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');
/*
 * Performs installation process
 * @package PeepSoTags
 * @author PeepSo
 */

class PeepSoEmailDigestInstall extends PeepSoInstall {

	protected $default_config = array(
		'email_digest_enable' => 1,
		'email_digest_use_images' => 1,
		'email_digest_schedule_weekly_day' => 'monday',
		'email_digest_schedule_type' => 'weekly',
		'email_digest_hour_weekly' => 10,
		'email_digest_minute_weekly' => 10,
		'email_digest_am_pm_weekly' => 'am',
		'email_digest_hour_daily' => 10,
		'email_digest_minute_daily' => 10,
		'email_digest_am_pm_daily' => 'am',
		'email_digest_most_liked' => 1,
		'email_digest_most_commented' => 1,
		'email_digest_limit_post_enable' => 1,
		'email_digest_limit_post_length' => 400,
		'email_digest_send_inactive' => 3,
		'email_digest_role_admin' => 1,
		'email_digest_role_member' => 1,
		'email_digest_per_batch' => 100
	);

	/*
	 * called on plugin activation; performs all installation tasks
	 */

	public function plugin_activation($is_core = FALSE)
	{
		parent::plugin_activation($is_core);
		return (TRUE);
	}

	public function get_email_contents()
	{
		$emails = array(
			'email_digest' => "Hello {userfullname},

Look what you might have missed happening in our community. You can like, or comment and join in on the discussion!

{digestemailcontent}

You can see all of the community posts here {activityurl}

Thank you.",
		);

		return $emails;
	}
	
	public static function get_table_data()
	{
		$aRet = array(
			'email_digest_log' => "
				CREATE TABLE email_digest_log (
					edl_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					edl_user_last_login TIMESTAMP,
					edl_sent TIMESTAMP,
					edl_user_id BIGINT(20) UNSIGNED NULL DEFAULT '0',
					edl_recipient VARCHAR(128) NOT NULL,
					edl_content_id BIGINT(20) UNSIGNED NULL DEFAULT '0',
					PRIMARY KEY (edl_id),
					INDEX user (edl_user_id)
				) ENGINE=InnoDB",
			'email_digest_content' => "
				CREATE TABLE email_digest_content (
					edc_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					edc_subject VARCHAR(200) NOT NULL,
					edc_message TEXT NOT NULL,
					PRIMARY KEY (edc_id)
				) ENGINE=InnoDB"
		);

		return ($aRet);
	}

}
