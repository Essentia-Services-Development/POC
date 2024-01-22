<?php

class PeepSoFriendsConfig
{
	protected static $_instance = NULL;
	private $_friendsrequests = NULL;

	/**
	 * Class constructor
	 */
	private function __construct()
	{
		add_filter('peepso_admin_register_config_group-site', array(&$this, 'register_config_options'));
		add_filter('peepso_config_email_messages', array(&$this, 'config_email'));
		add_filter('peepso_config_email_messages_defaults', array(&$this, 'config_email_messages_defaults'));
	}

	/*
	 * return singleton instance
	 */
	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}

	/**
	 * Adds the Friends config options to the admin page.
	 * @param  array $config_groups An array of existing configuration options
	 * @return array $config_groups
	 */
	public function register_config_options($config_groups)
	{
		$fields = array();

		if (is_plugin_active('peepsomessages/peepsomessages.php')) {
			$fields[] = array(
				'name' => 'friends_can_send_message_to',
				'label' => __('Members can send messages to', 'friendso'),
				'type' => 'select',
				'int' => TRUE,
				'validation' => array('numeric'),
				'field_wrapper_class' => 'controls col-sm-8',
				'field_label_class' => 'control-label col-sm-4',
				'options' => array(
					PeepSoFriendsPlugin::MESSAGE_ALL => 'All',
					PeepSoFriendsPlugin::MESSAGE_FRIENDS => 'Friends Only'
				),
				'value' => intval(PeepSo::get_option('friends_can_send_message_to'))
			);			
		}

		if(!count($fields)) {
			return $config_groups;
		}

		$config_groups[] = array(
			'name' => 'friends',
			'title' => __('Friends Settings', 'friendso'),
			'fields' => $fields,
			'context' => 'right'
		);

		return ($config_groups);
	}

	/**
	 * Add the Friend request emails to the list of editable emails on the config page
	 * @param  array $emails Array of editable emails
	 * @return array
	 */
	public static function config_email($emails)
	{
		$emails['email_friend_request_send'] = array(
			'title' => __('Friend Request Received', 'friendso'),
			'description' => __('This will be sent to a user when a friend request is received.', 'friendso')
		);

		$emails['email_friend_request_accept'] = array(
			'title' => __('Friend Request Accepted', 'friendso'),
			'description' => __('This will be sent to a user when a friend request is accepted.', 'friendso')
		);

		return ($emails);
	}

	public function config_email_messages_defaults( $emails )
	{
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../install' . DIRECTORY_SEPARATOR . 'activate.php');
		$install = new PeepSoFriendsInstall();
		$defaults = $install->get_email_contents();

		return array_merge($emails, $defaults);
	}
}

// EOF	
