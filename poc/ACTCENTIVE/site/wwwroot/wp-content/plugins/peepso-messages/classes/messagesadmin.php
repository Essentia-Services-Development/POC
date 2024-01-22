<?php

class PeepSoMessagesAdmin
{
	private static $_instance = NULL;

	/**
	 * Class constructor
	 */
	private function __construct()
	{
		add_filter('peepso_config_email_messages', array(&$this, 'config_email_messages'));
		add_filter('peepso_config_email_messages_defaults', array(&$this, 'config_email_messages_defaults'));
	}

	/**
	 * Retrieve singleton class instance
	 * @return PeepSoMessagesAdmin instance
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
	public static  function config_email_messages($emails)
	{
		$emails['email_new_message'] = array(
			'title' => __('New message email', 'msgso'),
			'description' => __('This will be sent to a user when a new message is received.', 'msgso')
		);

		return ($emails);
	}

	public function config_email_messages_defaults( $emails )
	{
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../install' . DIRECTORY_SEPARATOR . 'activate.php');
		$install = new PeepSoMessagesInstall();
		$defaults = $install->get_email_contents();

		return array_merge($emails, $defaults);
	}
}

// EOF
