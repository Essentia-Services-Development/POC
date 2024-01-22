<?php

class PeepSoNotifications
{
	const TABLE = 'peepso_notifications';
	private $table = self::TABLE;

	private $data = NULL;

	private static $instance;


	private $unread_count_for_user = array();

	public static function get_instance() {
	    if(!self::$instance) {
	        self::$instance = new self();
        }

        return self::$instance;
    }

	public function __construct($id = NULL)
	{
		global $wpdb;

		if (is_integer($id)) {
			$sql = "SELECT * FROM `{$wpdb->prefix}{$this->table}` " .
					" WHERE `not_id`=%d " .
					" LIMIT 1 ";
			$res = $wpdb->get_row($wpdb->prepare($sql, $id), OBJECT);
			if (NULL !== $res)
				$this->data = $res;
		}
	}

	/*
	 * Create a notification record
	 * @param int $from_user The user_id of the one creating the notification (sender)
	 * @param int $to_user The user_id of the on getting the notification (recipient)
	 * @param string $msg The message to be sent
	 * @param string $type The type or category of the message
	 * @param int $module The module id creating the notification
	 * @param int $external The ID of the external reference
	 * @return int The id of the newly created notification or FALSE if no Notification created
	 */
    public function add_notification($from_user, $to_user, $msg, $type, $module_id, $external = 0, $act_id = 0)
    {
        delete_user_option($to_user, 'peepso_should_get_notifications');

        $notifications = get_user_meta($to_user, 'peepso_notifications');
        // do not send any notification when it's disabled

        if (
            isset($notifications[0]) &&
            in_array($type . '_notification', $notifications[0]))
            return (FALSE);

        $block_users = new PeepSoBlockUsers();
        // do not send any notification when blocking
        if ($block_users->is_user_blocking($from_user, $to_user, TRUE))
            return (FALSE);

        $data = array(
            'not_user_id' => $to_user,
            'not_from_user_id' => $from_user,
            'not_module_id' => $module_id,
            'not_external_id' => $external,
            'not_act_id' => $act_id,
            'not_type' => $type,
            'not_message' => substr($msg, 0, 200),
            'not_timestamp' => current_time('mysql')
        );

        if(isset($msg_args)) {
            $data['not_message_args'] = $msg_args;
        }

        $data = apply_filters('peepso_notifications_data_before_add', $data);

        global $wpdb;
        $id = $wpdb->insert($wpdb->prefix . self::TABLE, $data);
        PeepSoSSEEvents::trigger('get_notifications', $to_user);

        do_action('peepso_action_create_notification_after', $wpdb->insert_id);
        return ($id);
    }

    public function add_notification_new($from_user, $to_user, string $msg, array $args, $type, $module_id, $external = 0, $act_id = 0)
    {
        delete_user_option($to_user, 'peepso_should_get_notifications');

        $notifications = get_user_meta($to_user, 'peepso_notifications');
        // do not send any notification when it's disabled

        if (
            isset($notifications[0]) &&
            in_array($type . '_notification', $notifications[0]))
            return FALSE;

        $block_users = new PeepSoBlockUsers();
        // do not send any notification when blocking
        if ($block_users->is_user_blocking($from_user, $to_user, TRUE))
            return FALSE;

        $data = array(
            'not_user_id' => $to_user,
            'not_from_user_id' => $from_user,
            'not_module_id' => $module_id,
            'not_external_id' => $external,
            'not_act_id' => $act_id,
            'not_type' => $type,
            'not_message' => substr($msg, 0, 200),
            'not_message_args' => json_encode($args),
            'not_timestamp' => current_time('mysql')
        );

        $data = apply_filters('peepso_notifications_data_before_add', $data);

        global $wpdb;
        $id = $wpdb->insert($wpdb->prefix . self::TABLE, $data);
        PeepSoSSEEvents::trigger('get_notifications', $to_user);

        do_action('peepso_action_create_notification_after', $wpdb->insert_id);
        return ($id);
    }


	/*
	 * Return message for this notification, replacing any tokens
	 * @return String The message associated with this notification instance
	 */
	public function get_message()
	{
		$msg = NULL;
		if (NULL !== $this->data) {
			$msg = $this->data->not_message;
			$tokens = $this->get_tokens();

			$mag = str_replace(array_keys($tokens), array_values($tokens), $msg);
		}

		return ($msg);
	}

	/**
	 * Return replacement tokens for the user
	 * @return array The replacement tokens
	 */
	private function get_tokens()
	{
		$PeepSoUser = PeepSoUser::get_instance($this->data->not_from_user_id);

		$ret = array(
			'%from_user_name%' => $PeepSoUser->get_fullname(),
			'%from_user_link%' => $PeepSoUser->get_profileurl(),
			'%item%' => $this->data->not_type,
		);
		return ($ret);
	}

    /**
     * Return a parsed notification message (without the leading name)
     * @param $notification object or array representing a peepso notification
     * @return string parsed notification text without actor name
     */
    public static function parse($notification) {
        if(is_object($notification)) {
            $notification = get_object_vars($notification);
        }

        $message = $notification['not_message'];

        if(isset($notification['not_message_args']) && strlen($notification['not_message_args'])) {
            if ($args = json_decode($notification['not_message_args'], TRUE)) {
                $textdomain = array_shift($args);
            }

            $args = apply_filters('peepso_filter_notification_args', $args);

            // fire translation on args as well
            if (count($args)) {
                foreach ($args as &$arg) {
                    $arg = __($arg, $textdomain);
                }
            }

            $message = vsprintf(__($notification['not_message'], $textdomain), $args);
        }

        return $message;
    }


	/*
	 * Get number of notification for the given user
	 * @param int $user_id The user id to count notifications for
	 * @return int Number of notifications for the given user
	 */
	public function get_count_for_user($user_id)
	{
		global $wpdb;

		$sql = "SELECT COUNT(*) AS `count` " .
				" FROM `{$wpdb->prefix}{$this->table}` " .
				" WHERE `not_user_id`=%d ";
		$ret = intval($wpdb->get_var($wpdb->prepare($sql, $user_id)));
		return ($ret);
	}


	/*
	 * Get number of unread notification for the given user
	 * @param int $user_id The user id to count notifications for
	 * @return int Number of notifications for the given user
	 */
	public function get_unread_count_for_user($user_id = NULL)
	{
        if(NULL == $user_id) {
            $user_id = get_current_user_id();
        }

	    if(!array_key_exists($user_id, $this->unread_count_for_user)) {
            global $wpdb;

            $access = ' (IF (`act`.`act_id` IS NOT NULL, (`act_access`=' . PeepSo::ACCESS_PRIVATE . ' AND `act_owner_id`=' . get_current_user_id() . ') OR ' .
                ' (`act_access`=' . PeepSo::ACCESS_MEMBERS . ') OR (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ') ';

            // Hooked methods must wrap the string within a paranthesis
            $access = apply_filters('peepso_activity_post_filter_access', $access);
            $access .= ', 1=1))';

            $sql = "SELECT COUNT(*) AS `count` " .
                " FROM `{$wpdb->prefix}{$this->table}` `not`" .
                " LEFT JOIN `{$wpdb->users}` `fu` ON `fu`.`ID` = `not`.`not_from_user_id` " .
                " LEFT JOIN `{$wpdb->posts}` `p` ON `p`.ID = `not`.`not_external_id` " .
                " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`p`.`id` " .
                " WHERE `not_user_id`=%d AND `not_read`=0 AND " . $access . " AND (`p`.`post_type` IN ('peepso-post', 'peepso-comment', 'peepso-group', 'event_listing') OR `not`.`not_external_id` = 0)";

            $this->unread_count_for_user[$user_id] = intval($wpdb->get_var($wpdb->prepare($sql, $user_id)));
        }

		return $this->unread_count_for_user[$user_id];
	}


	/*
	 * Return notification data by user id
	 * @param int $user_id The ID of the user who's notifications are to be retrieved
	 * @return array Notification data by user
	 */
	public function get_by_user($user_id, $limit = 40, $offset = 0, $unread_only = 0)
	{
		global $wpdb;

		// TODO: instead of filtering the results, if the user doesn't have access to the post the notification record should not be created
		$access = ' (IF (`act`.`act_id` IS NOT NULL, (`act_access`=' . PeepSo::ACCESS_PRIVATE . ' AND `act_owner_id`=' . get_current_user_id() . ') OR ' .
				' (`act_access`=' . PeepSo::ACCESS_MEMBERS . ') OR (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ') ';

		// Hooked methods must wrap the string within a paranthesis
		$access = apply_filters('peepso_activity_post_filter_access', $access);
		$access .= ', 1=1))';

		$sql = "SELECT `not`.*, `fu`.`user_login`, `p`.`post_title`, `p`.`post_content` " .
				" FROM `{$wpdb->prefix}{$this->table}` `not` " .
				" LEFT JOIN `{$wpdb->users}` `fu` ON `fu`.`ID` = `not`.`not_from_user_id` " .
				" LEFT JOIN `{$wpdb->posts}` `p` ON `p`.ID = `not`.`not_external_id` " .
				" LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`p`.`id` " .
				" WHERE `not`.`not_user_id`=%d AND " . $access . " AND (`p`.`post_type` IN ('peepso-post', 'peepso-comment', 'peepso-group','event_listing') OR `not`.`not_external_id` = 0)";

        if(1 == $unread_only) {
            $sql .= " AND `not_read`=0 ";
        }


        $sql .= " ORDER BY `not_timestamp` DESC " .
				" LIMIT %d, %d ";

		$res = $wpdb->get_results($wpdb->prepare($sql, $user_id, $offset, $limit), OBJECT); // ARRAY_A);
		return ($res);
	}


	/*
	 * Deletes records from notifications table for current user by id
	 * @param array $ids An array of notification id numbers to delete
	 * @param int $user_id The user id that owns the notification records
	 */
	public function delete_by_id($ids, $user_id = NULL)
	{
		global $wpdb;

		$ids = implode(',', $ids);
		if (NULL === $user_id)
			$user_id = get_current_user_id();

		$sql = "DELETE FROM `{$wpdb->prefix}" . self::TABLE . "` " .
			" WHERE `not_user_id`=%d AND `not_id` IN ({$ids}) ";

		$res = $wpdb->query($wpdb->prepare($sql, $user_id));

		return $res;
	}


	/*
	 * Mark notification records as having been read
	 * @param int $user_id User id of notification records to update
	 */
	public function mark_as_read($user_id = NULL, $note_id = NULL)
	{
		global $wpdb;

		if (NULL === $user_id) {
			$user_id = get_current_user_id();
		}

		$where = '';
		if (NULL !== $note_id) {
			$where .= " AND `not_id` = %d ";
		}

		$sql = "UPDATE `{$wpdb->prefix}" . self::TABLE . "` " .
				" SET `not_read`=1 " .
				" WHERE `not_user_id`=%d " . $where;

		$res = FALSE;
		if (NULL === $note_id) {
			$res = $wpdb->query($wpdb->prepare($sql, $user_id));
		} else {
			$res = $wpdb->query($wpdb->prepare($sql, $user_id, $note_id));
		}

		return $res;
	}

	/**
	 * Get the latest user's notification
	 * @param int $user_id User ID
	 * @return array Numerically indexed array of row objects
	 */
	public function get_latest($user_id = NULL)
	{
		global $wpdb;

		$sql = "SELECT `not`.*, `fu`.`user_login`, `p`.`post_title`, `p`.`post_content` " .
				" FROM `{$wpdb->prefix}{$this->table}` `not` " .
				" LEFT JOIN `{$wpdb->users}` `fu` ON `fu`.`ID` = `not`.`not_from_user_id` " .
				" LEFT JOIN `{$wpdb->posts}` `p` ON `p`.ID = `not`.`not_external_id` " .
				" WHERE `not`.`not_user_id`=%d  AND (`p`.`post_type` IN ('peepso-post', 'peepso-comment', 'peepso-group','event_listing') OR `not`.`not_external_id` = 0)";
				" ORDER BY `not_timestamp` DESC " .
				" LIMIT 2 ";

		$res = $wpdb->get_results($wpdb->prepare($sql, $user_id), OBJECT); // ARRAY_A);
		return ($res);
	}
}

// EOF
