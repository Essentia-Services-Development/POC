<?php

class PeepSoFriendsBirthdayModel
{
	const TABLE = 'peepso_friends';

	private static $_instance = NULL;

	public $_friends_birthday = NULL;

	private function construct() {}

	public static function get_instance()
	{
		if (self::$_instance === NULL) {
			self::$_instance = new self();
		}

		return (self::$_instance);
	}

	/**
	 * Get friends birthday
	 * @param  int $user_id The user to get friends birthday
	 * @param  array $args Arguments to be passed to get_users()
	 * @return array An array of user ID's
	 */
	public function get_today_birthday($user_id, $args = array())
	{
		global $wpdb;

		$date = date( 'Y-m-d', current_time( 'timestamp' ) );

		$friends = array();

		$sql = "SELECT `ps_friend`.*, `usermeta`.`meta_value` AS `birthdate` FROM ( " .
			" SELECT * FROM `{$wpdb->prefix}" . PeepSoUser::TABLE . "` ps_fnd" .
			" WHERE `usr_role` NOT IN ('register', 'verified', 'ban') AND `usr_id` IN ( SELECT 
				CASE WHEN `fnd_user_id` = " . $user_id . "
					  THEN `fnd_friend_id`
					  ELSE `fnd_user_id`
			   END AS `friendID`
			 FROM `" . $wpdb->prefix . self::TABLE. "` 
			 WHERE (`fnd_user_id` = " . $user_id . " OR `fnd_friend_id` = " . $user_id . ") "
			. "))  AS `ps_friend`, `{$wpdb->base_prefix}usermeta` AS `usermeta`, `{$wpdb->base_prefix}usermeta` AS `usermeta_acc` "
			. " WHERE `ps_friend`.`usr_id` = `usermeta`.`user_id` AND `usermeta`.`meta_key` = 'peepso_user_field_birthdate' "
			. " AND `ps_friend`.`usr_id` = `usermeta_acc`.`user_id` AND `usermeta_acc`.`meta_key` = 'peepso_user_field_birthdate_acc' "
			. " AND `usermeta_acc`.`meta_value` <> " . PeepSo::ACCESS_PRIVATE . " "
			. "AND DATE_ADD(`usermeta`.`meta_value`, INTERVAL YEAR('$date')-YEAR(`usermeta`.`meta_value`) YEAR) = '$date' "
			." ORDER BY `usr_id` DESC";

		if ((!PeepSo::is_admin()) && (1 === intval(PeepSo::get_option('allow_hide_user_from_user_listing', 0)))) {
			$sql = "SELECT DISTINCT `a`.* FROM (" . $sql . ") AS `a` 
				   	LEFT JOIN `{$wpdb->usermeta}` AS `b`
                   	ON `a`.`usr_id` = `b`.`user_id`
                   	WHERE (`b`.`meta_key` = 'peepso_is_hide_profile_from_user_listing'
                   		AND `b`.`meta_value` = 0) OR 
						NOT EXISTS (SELECT * from `{$wpdb->usermeta}` AS `um` WHERE `a`.`usr_id` = `um`.`user_id` AND `um`.`meta_key`='peepso_is_hide_profile_from_user_listing')";
		}

		$limit = $args["number"];

		if (!is_null($limit)) {
			$sql .= ' LIMIT ' . $limit;
		}

		$result = $wpdb->get_results($sql, ARRAY_A);

		$this->set_friends_birthday($result);

		return ($this->_friends_birthday);
	}

	/**
	 * Get upcoming friends birthday of a single user
	 * @param  int $user_id The user to get friends birthday
	 * @param  array $args Arguments to be passed to get_users()
	 * @return array An array of user ID's
	 */
	public function get_upcoming_birthday($user_id, $args = array())
	{
		global $wpdb;

		$date = date( 'Y-m-d', current_time( 'timestamp' ) );

		$friends = array();
		/*
		select * from (SELECT * FROM `wp_peepso_users` ps_fnd 
	WHERE `usr_id` IN ( 
        SELECT CASE 
        WHEN `fnd_user_id` = 2 
        THEN `fnd_friend_id` 
        ELSE `fnd_user_id` END AS `friendID` 
    FROM `wp_peepso_friends` WHere (`fnd_user_id` = 2 OR `fnd_friend_id` = 2) )) a, wp_usermeta b WHERE a.usr_id = b.user_id AND b.meta_key = 'peepso_user_field_birthdate' AND (DATE_ADD(`meta_value`, INTERVAL YEAR('$date')-YEAR(`meta_value`) + IF(DAYOFYEAR('$date') > DAYOFYEAR(`meta_value`),1,0) YEAR) BETWEEN '$date' AND DATE_ADD('$date', INTERVAL 4 DAY)) ORDER BY `usr_id` DESC
		*/
		$sql = "SELECT `ps_friend`.*, DATE_ADD(`usermeta`.`meta_value`, 
				INTERVAL YEAR('$date')-YEAR(`usermeta`.`meta_value`)
					+ IF(DAYOFYEAR('$date') >= DAYOFYEAR(`usermeta`.`meta_value`),1,0) YEAR) AS `birthdate` FROM ( " .
			" SELECT * FROM `{$wpdb->prefix}" . PeepSoUser::TABLE . "` ps_fnd" .
			" WHERE `usr_role` NOT IN ('register', 'verified', 'ban') AND `usr_id` IN ( SELECT 
				CASE WHEN `fnd_user_id` = " . $user_id . "
					  THEN `fnd_friend_id`
					  ELSE `fnd_user_id`
			   END AS `friendID`
			 FROM `" . $wpdb->prefix . self::TABLE
				. "` WHERE (`fnd_user_id` = " . $user_id . " OR `fnd_friend_id` = " . $user_id . ") "
		. "))  AS `ps_friend`, `{$wpdb->base_prefix}usermeta` AS `usermeta`, `{$wpdb->base_prefix}usermeta` AS `usermeta_acc` "
		. " WHERE `ps_friend`.`usr_id` = `usermeta`.`user_id` AND `usermeta`.`meta_key` = 'peepso_user_field_birthdate' "
		. " AND `ps_friend`.`usr_id` = `usermeta_acc`.`user_id` AND `usermeta_acc`.`meta_key` = 'peepso_user_field_birthdate_acc' "
		. " AND `usermeta_acc`.`meta_value` <> " . PeepSo::ACCESS_PRIVATE . " "
		. " AND (DATE_ADD(`usermeta`.`meta_value`, 
				INTERVAL YEAR('$date')-YEAR(`usermeta`.`meta_value`)
					+ IF(DAYOFYEAR('$date') > DAYOFYEAR(`usermeta`.`meta_value`),1,0) YEAR)  
				> '$date') AND (DATE_ADD(`usermeta`.`meta_value`, 
				INTERVAL YEAR('$date')-YEAR(`usermeta`.`meta_value`)
					+ IF(DAYOFYEAR('$date') > DAYOFYEAR(`usermeta`.`meta_value`),1,0) YEAR) <= DATE_ADD('$date', INTERVAL ".$args["days_ahead"]." DAY)) "
		." ORDER BY `birthdate` ASC";

		if ((!PeepSo::is_admin()) && (1 === intval(PeepSo::get_option('allow_hide_user_from_user_listing', 0)))) {
			$sql = "SELECT DISTINCT `a`.* FROM (" . $sql . ") AS `a` 
				   	LEFT JOIN `{$wpdb->usermeta}` AS `b`
                   	ON `a`.`usr_id` = `b`.`user_id`
                   	WHERE (`b`.`meta_key` = 'peepso_is_hide_profile_from_user_listing'
                   		AND `b`.`meta_value` = 0) OR 
						NOT EXISTS (SELECT * from `{$wpdb->usermeta}` AS `um` WHERE `a`.`usr_id` = `um`.`user_id` AND `um`.`meta_key`='peepso_is_hide_profile_from_user_listing')";
		}

		$limit = $args["number"];

		if (!is_null($limit)) {
			$sql .= ' LIMIT ' . $limit;
		}

		$result = $wpdb->get_results($sql, ARRAY_A);

		$this->set_friends_birthday($result);

		return ($this->_friends_birthday);
	}	

	/**
	 * Set the $_friends property
	 * @param array $friends An array of user IDs
	 *
	 */
	public function set_friends_birthday($friends)
	{
		$this->_friends_birthday = new ArrayObject($friends);
	}
}

// EOF
