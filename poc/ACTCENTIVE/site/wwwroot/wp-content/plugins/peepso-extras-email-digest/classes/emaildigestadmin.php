<?php

class PeepSoEmailDigestAdmin
{

	private static $_instance = NULL;

	/**
	 * Class constructor
	 */
	private function __construct()
	{
		if (isset($_GET['clear_logs'])) {
			PeepSoEmailDigest::clear_logs();
		}

		add_filter('peepso_config_email_messages', array(&$this, 'config_email_messages'));
		add_filter('peepso_config_email_messages_defaults', array(&$this, 'config_email_messages_defaults'));
		add_action('peepso_config_after_save-email-digest', array(&$this, 'after_save_site'));
	}

	/**
	 * Retrieve singleton class instance
	 * @return PeepSoEmailDigestAdmin instance
	 */
	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}

	/**
	 * Adds the email template to the config section
	 * @param  array $emails Available emails.
	 * @return array
	 */
	public function config_email_messages($emails)
	{
		$emails['email_digest'] = array(
			'title' => __('Digest Email', 'peepso-email-digest'),
			'description' => __('This will be sent to user based on schedule.', 'peepso-email-digest')
		);

		return ($emails);
	}

	public function config_email_messages_defaults($emails)
	{
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../install' . DIRECTORY_SEPARATOR . 'activate.php');
		$install = new PeepSoEmailDigestInstall();
		$defaults = $install->get_email_contents();

		return array_merge($emails, $defaults);
	}

	public function after_save_site()
	{
		self::clear_scheduled_cron();

		if (PeepSo::get_option('email_digest_enable') === 1) {
			self::set_schedule_time(self::generate_schedule());
			PeepSoConfigSettings::get_instance()->remove_option('email_digest_batch');
		}
	}

	public static function clear_scheduled_cron()
	{
		wp_clear_scheduled_hook(PeepSoEmailDigest::CRON_EMAIL_DIGEST_EVENT, array('daily'));
		wp_clear_scheduled_hook(PeepSoEmailDigest::CRON_EMAIL_DIGEST_EVENT, array('weekly'));
	}

	public static function get_scheduled_time($schedule_type)
	{
		if ($schedule_type == 'biweekly') {
			$schedule_type = 'weekly';
		}
		$hour = PeepSo::get_option('email_digest_hour_' . $schedule_type);
		$minute = PeepSo::get_option('email_digest_minute_' . $schedule_type);
		$am_pm = PeepSo::get_option('email_digest_am_pm_' . $schedule_type);

		return array($hour, $minute, $am_pm);
	}

	public static function set_schedule_time($param = array())
	{
		self::clear_scheduled_cron();
		wp_schedule_event($param['time'], $param['type'], PeepSoEmailDigest::CRON_EMAIL_DIGEST_EVENT, $param['args']);

		if (PeepSo::get_option('email_digest_external_cron', 0) === 1) {
			$timestamp = wp_next_scheduled(PeepSoEmailDigest::CRON_EMAIL_DIGEST_EVENT, $param['args']);
			if (empty($timestamp) || $timestamp < $param['time'] || $param['args'][0] == 'monthly') {
				$timestamp = $param['time'];
			}

			new PeepSoError('[ED] Next Schedule : ' . date('Y-m-d H:i:s', $timestamp) . ' - ' . $timestamp);
			PeepSoConfigSettings::get_instance()->set_option('email_digest_schedule_timestamp', $timestamp);
			self::clear_scheduled_cron();
		}
	}

	public static function generate_schedule()
	{
		$schedule_type = PeepSo::get_option('email_digest_schedule_type', 'daily');

		// get the time
		$time = self::get_scheduled_time($schedule_type);

		// create cron event daily
		if ($schedule_type === 'daily') {
			$schedule_time = strtotime(date('h:i a', strtotime($time[0] . ':' . $time[1] . ' ' . $time[2])));
			$current_time = strtotime(current_time('H:i:s'));

			// if time already passed, use the time next day
			if ($current_time > $schedule_time) {
				$schedule_time = strtotime('+1 day', $schedule_time);
			}

			$schedule_time = strtotime(date_i18n('Y-m-d h:i a', $schedule_time), get_option('gmt_offset'));
		} else

		// create cron event weekly
		if ($schedule_type === 'weekly') {
			$next_day = strtotime('next ' . PeepSo::get_option('email_digest_schedule_weekly_day'));
			$date_time = date_create_from_format('Y-m-d h:i a', date('Y-m-d', $next_day) . ' ' . $time[0] . ':' . $time[1] . ' ' . $time[2]);
			$schedule_time = strtotime(date_i18n('Y-m-d h:i a', $date_time->getTimestamp(), get_option('gmt_offset')));
		} else

		// create cron event biweekly
		if ($schedule_type === 'biweekly') {
			$next_day = strtotime('next ' . PeepSo::get_option('email_digest_schedule_weekly_day'));
			$next_week = strtotime('+7 days',  $next_day);
			$date_time = date_create_from_format('Y-m-d h:i a', date('Y-m-d', $next_week) . ' ' . $time[0] . ':' . $time[1] . ' ' . $time[2]);
			$schedule_time = strtotime(date_i18n('Y-m-d h:i a', $date_time->getTimestamp(), get_option('gmt_offset')));
		} else

		// create cron event weekly
		if ($schedule_type === 'monthly') {
			$last_date_this_month = date('t', time());
			$last_date_next_month = date('t', strtotime('+1 month'));
			$date = PeepSo::get_option('email_digest_schedule_monthly_date');
			$current_date = date('d');

			if ($date < $current_date) {
				if ($date > $last_date_next_month) {
					$new_date = date('Y-m-', strtotime('+1 month')) . $last_date_next_month;
				} else {
					$new_date = date('Y-m-', strtotime('+1 month')) . $date;
				}
			} else {
				if ($date > $last_date_this_month) {
					$new_date = date('Y-m-t');
				} else if ($date == $current_date) {
					$new_date = date('Y-m-', strtotime('+1 month')) . $date;
				} else {
					$new_date = date('Y-m-') . $date;
				}				
			}

			$date_time = date_create_from_format('Y-m-d h:i a', $new_date . ' ' . $time[0] . ':' . $time[1] . ' ' . $time[2]);
			$schedule_time = strtotime(date_i18n('Y-m-d h:i a', $date_time->getTimestamp(), get_option('gmt_offset')));
		}

		// testing only
		//$schedule_time = strtotime('+1 minute', current_time('timestamp'));

		$new_schedule_param = array(
			'time' => $schedule_time,
			'type' => $schedule_type,
			'args' => array($schedule_type)
		);

		return $new_schedule_param;
	}
	
}

// EOF