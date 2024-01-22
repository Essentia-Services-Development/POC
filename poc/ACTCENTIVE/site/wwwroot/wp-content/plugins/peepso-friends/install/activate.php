<?php
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');
/*
 * Performs installation process
 * @package PeepSoFriends
 * @author PeepSo
 */
class PeepSoFriendsInstall extends PeepSoInstall
{
	protected $default_config = array(
		'friends_request_expiry' => 30,
		'friends_can_send_message_to' => PeepSoFriendsPlugin::MESSAGE_ALL,
        'friends_max_amount' => 50,
	);


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
			'friend_requests' => "
				CREATE TABLE friend_requests (
					freq_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					freq_user_id BIGINT(20) UNSIGNED NOT NULL,
					freq_friend_id BIGINT(20) UNSIGNED NOT NULL,
					freq_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					freq_msg TEXT NULL,
					freq_viewed TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
					PRIMARY KEY (freq_id),
					INDEX freq_owner (freq_user_id),
					INDEX freq_friend (freq_friend_id)
				) ENGINE=InnoDB",
			'friends' => "
				CREATE TABLE friends (
					fnd_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					fnd_user_id BIGINT(20) UNSIGNED NOT NULL,
					fnd_friend_id BIGINT(20) UNSIGNED NOT NULL,
					fnd_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (fnd_id),
					INDEX freq_owner (fnd_user_id),
					INDEX freq_friend (fnd_friend_id)
				) ENGINE=InnoDB",
			'friends_cache' => "
				CREATE TABLE friends_cache (
					user_id BIGINT(20) UNSIGNED NOT NULL,
					friend_id BIGINT(20) UNSIGNED NOT NULL,
					PRIMARY KEY (user_id, friend_id),
					INDEX owner (user_id),
					INDEX friend (friend_id)
				) ENGINE=InnoDB",
			'user_autofriends' => "
				CREATE TABLE user_autofriends (
					af_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					af_user_id BIGINT(20) UNSIGNED NOT NULL,
					af_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (af_id)
				) ENGINE=InnoDB"
		);

		return $aRet;
	}

	/*
	 * return default page names information
	 */
	protected function get_page_data()
	{
        return array();
		// default page names/locations
		$aRet = array(
			'friends' => array(
				'title' => __('Friends', 'friendso'),
				'slug' => 'friends',
				'content' => '[' . PeepSoFriendsShortcode::SHORTCODE_FRIENDS . ']',
				'children' => array(
					'requests' => array(
						'title' => __('Friend Requests', 'friendso'),
						'slug' => 'requests',
						'content' => '[' . PeepSoFriendsShortcode::SHORTCODE_PENDING . ']',
					),
				)
			),
		);

		return ($aRet);
	}

	/*
	 * return default email templates
	 */
	public function get_email_contents()
	{
		$emails = array(
			'email_friend_request_send' => 'Hello {recepientfullname},

You have a new friend request from {senderfullname}
You can manage your friend requests here: {profileurl}

Thank you.',
			'email_friend_request_accept' => 'Hello {senderfullname},

{recepientfullname} accepted your friend request.
View {recepientfullname}&rsquo;s profile here: {recepientprofile}.

Thank you.',
		);
		
		return ($emails);
	}	
}
