<?php

class PeepSoFriendsModel
{
	const TABLE = 'peepso_friends';

	private static $_instance = NULL;

	public $_friends = NULL;

	private function __construct() {}

	public static function get_instance()
	{
		if (self::$_instance === NULL) {
			self::$_instance = new self();
		}

		return (self::$_instance);
	}

	/**
	 * Get friends of a single user
	 * @param  int $user_id The user to get friends of
	 * @param  array $args Arguments to be passed to get_users()
	 * @return array An array of user ID's
	 */
    public function get_friends($user_id, $args = array())
    {
        if (!array_key_exists('orderby', $args)) {
            $args['orderby'] = 'meta_value';
        }

        if (!array_key_exists('meta_key', $args)) {
            $args['meta_key'] = 'last_name';
        }

        add_action('peepso_pre_user_query', array(&$this, 'friends_only_query'));
        $friends = new PeepSoUserSearch($args, $user_id);
        remove_action('peepso_pre_user_query', array(&$this, 'friends_only_query'));

        $this->set_friends($friends->results);

        return ($this->_friends);
    }

    public static function get_friends_ids(int $user_id, bool $reset = FALSE) {

        $key = 'friends_ids_' . $user_id;

        // MayFly cache?
        if(!$reset) {
            $cache = PeepSo3_Mayfly_Int::get($key);

            if (NULL !== $cache) {
                $cache = (array) $cache;
                return $cache;
            }
        }

        $list = (array) self::get_instance()->get_friends($user_id);

        PeepSo3_Mayfly::set($key, $list, PeepSoFriendsCache::CACHE_TIME);

        return $list;
    }

	/**
	 * Modifies a WP_User_Query instance to only return users that are friends.
	 * @param  WP_User_Query $wp_user_query
	 */
	public function friends_only_query(WP_User_Query $wp_user_query)
	{
		$wp_user_query->query_where .= ' AND `ps_fnd`.`fnd_id` IS NOT NULL ';
	}

	/**
	 * Returns friends that have made a post or an activity recently.
	 * @param  int $user_id Get friends of $user_id
	 * @param  int $limit   Limits the number of results.
	 * @return array An array of user_id's
	 */
	public function get_friends_by_post($user_id, $limit = NULL)
	{
		global $wpdb;

		if ($this->has_friends($user_id)) {
			$sql = "SELECT * FROM `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` " .
				" WHERE `act_owner_id` IN (" . implode(',', $this->_friends->getArrayCopy()) . ") GROUP BY `act_owner_id` ORDER BY `act_id` DESC";

			if (!is_null($limit)) {
				$sql .= ' LIMIT ' . $limit;
			}

			$result = $wpdb->get_results($sql, ARRAY_A);

			$friends = array();

			if ($wpdb->num_rows > 0) {
				foreach ($result as $friend) {
					$friends[] = $friend['act_owner_id'];
				}
			}
		} else {
			$friends = array();
		}

		$this->set_friends($friends);

		return ($this->_friends);
	}

	/**
	 * Returns the number of friends a user has.
	 * @param  int $user_id The user ID to search friends of.
	 * @return int The number of the user's friends found
	 */
	public function get_num_friends($user_id, $actual = FALSE)
	{
		global $wpdb;

		$sql = "SELECT CASE WHEN `fnd_user_id` = %d
						  THEN `fnd_friend_id`
						  ELSE `fnd_user_id`
				   END AS `friendID` FROM `{$wpdb->prefix}" . self::TABLE . "` WHERE `fnd_user_id` = %d OR `fnd_friend_id` = %d";

		if ((!PeepSo::is_admin()) && (1 === intval(PeepSo::get_option('allow_hide_user_from_user_listing', 0))) && !$actual) {
			$sql = "SELECT DISTINCT a.friendID from (".$sql.") as a
				   	LEFT JOIN `{$wpdb->usermeta}` as `b`
				   	ON `a`.`friendID` = `b`.`user_id`
                   	WHERE (`b`.`meta_key` = 'peepso_is_hide_profile_from_user_listing'
                   		AND `b`.`meta_value` = 0) OR
						NOT EXISTS (SELECT * from `{$wpdb->usermeta}` AS `um` WHERE `a`.`friendID` = `um`.`user_id` AND `um`.`meta_key`='peepso_is_hide_profile_from_user_listing')";
		}

		$sql = "SELECT COUNT(*) AS num_friends FROM (" . $sql . ") AS listFriend
			LEFT JOIN `{$wpdb->prefix}".PeepSoUser::TABLE."` as `f`
				   	ON `listFriend`.`friendID` = `f`.`usr_id`
			WHERE `f`.`usr_role` NOT IN ('register', 'ban', 'verified')";

		$friends = $wpdb->get_var($wpdb->prepare($sql, $user_id, $user_id, $user_id),0,0);

		return intval($friends);
	}

	/**
	 * Checks if two users are friends
	 * @param  int $from_id
	 * @param  int $to_id
	 * @return boolean
	 */
	public function are_friends($from_id, $to_id)
	{
		global $wpdb;

		$sql = 'SELECT `fnd_id` FROM `' . $wpdb->prefix . self::TABLE
			. '` WHERE (`fnd_user_id` = %d AND `fnd_friend_id` = %d) '
			. ' OR (`fnd_friend_id` = %d AND `fnd_user_id` = %d)'; // Do vice-versa

		$friends = $wpdb->get_var($wpdb->prepare($sql, $from_id, $to_id, $from_id, $to_id));

		return ((!is_null($friends)) ? $friends : FALSE);
	}

	/**
	 * Returns the sql query to use in getting mutual friends
	 * @param  int $user_id The User asking for the query.
	 * @param  int $friend_id The User getting mutual friends from.
	 * @return string
	 */
	private function _get_mutual_friends_query($user_id, $friend_id, $args=array())
	{
		global $wpdb;

		$sql = 'SELECT a.friendID
		FROM
			( SELECT CASE WHEN `fnd_user_id` = %1$d
						  THEN `fnd_friend_id`
						  ELSE `fnd_user_id`
				   END AS `friendID`
			FROM `' . $wpdb->prefix . self::TABLE . '`
			WHERE `fnd_user_id` = %1$d OR `fnd_friend_id` = %1$d
			) a
		JOIN
			( SELECT CASE WHEN `fnd_user_id` = %2$d
						  THEN `fnd_friend_id`
						  ELSE `fnd_user_id`
				   END AS `friendID`
			FROM `' . $wpdb->prefix . self::TABLE . '`
			WHERE `fnd_user_id` = %2$d OR `fnd_friend_id` = %2$d
			) b
		ON `b`.`friendID` = `a`.`friendID` ';

		if ((!PeepSo::is_admin()) && (1 === intval(PeepSo::get_option('allow_hide_user_from_user_listing', 0)))) {
			$sql = "SELECT DISTINCT x.friendID AS friendID from (".$sql.") as x
				   	LEFT JOIN `{$wpdb->usermeta}` as `y`
                   	ON `x`.`friendID` = `y`.`user_id`
                   	WHERE (`y`.`meta_key` = 'peepso_is_hide_profile_from_user_listing'
                   		AND `y`.`meta_value` = 0) OR
						NOT EXISTS (SELECT * from `{$wpdb->usermeta}` AS `um` WHERE `x`.`friendID` = `um`.`user_id` AND `um`.`meta_key`='peepso_is_hide_profile_from_user_listing')";
		}

		$sql = "SELECT friendID FROM (" . $sql . ") AS listFriend
			LEFT JOIN `{$wpdb->prefix}".PeepSoUser::TABLE."` as `f`
				   	ON `listFriend`.`friendID` = `f`.`usr_id`
			WHERE `f`.`usr_role` NOT IN ('register', 'ban', 'verified')";

		if (isset($args['offset']))
  			$sql .= ' LIMIT ' . $args['offset'] . ', ' . $args['number'];

		return sprintf($sql, intval($user_id), intval($friend_id));
	}

	/**
	 * Set the $_friends property
	 * @param array $friends An array of user IDs
	 *
	 */
	public function set_friends($friends)
	{
		$this->_friends = new ArrayObject($friends);
	}

	/**
	 * Returns the current _friends object's iterator.
	 * @return ArrayIterator
	 */
	public function get_iterator()
	{
		static $iterator = NULL;

		if (is_null($iterator)) {
			$iterator = $this->_friends->getIterator();
		}

		return ($iterator);
	}

	/**
	 * Returns the number mutual friends of $user_id and $friend_id.
	 * @param  int $user_id The User asking for the query.
	 * @param  int $friend_id The User getting mutual friends from.
	 * @return int number of both users' mutual friends' user ID's.
	 */
	public function _get_num_mutual_friends($user_id, $friend_id)
	{
		global $wpdb;

		$sql = 'SELECT a.friendID AS friendID
		FROM
			( SELECT CASE WHEN `fnd_user_id` = ' . $user_id . '
						  THEN `fnd_friend_id`
						  ELSE `fnd_user_id`
				   END AS `friendID`
			FROM `' . $wpdb->prefix . self::TABLE . '`
			WHERE `fnd_user_id` = ' . $user_id . ' OR `fnd_friend_id` = ' . $user_id . '
			) a
		JOIN
			( SELECT CASE WHEN `fnd_user_id` = ' . $friend_id . '
						  THEN `fnd_friend_id`
						  ELSE `fnd_user_id`
				   END AS `friendID`
			FROM `' . $wpdb->prefix . self::TABLE . '`
			WHERE `fnd_user_id` = ' . $friend_id . ' OR `fnd_friend_id` = ' . $friend_id . '
			) b
		ON `b`.`friendID` = `a`.`friendID` ';

		if ((!PeepSo::is_admin()) && (1 === intval(PeepSo::get_option('allow_hide_user_from_user_listing', 0)))) {
			$sql = "SELECT DISTINCT x.friendID from (".$sql.") as x
				   	LEFT JOIN `{$wpdb->usermeta}` as `y`
                   	ON `x`.`friendID` = `y`.`user_id`
                   	WHERE (`y`.`meta_key` = 'peepso_is_hide_profile_from_user_listing'
                   		AND `y`.`meta_value` = 0) OR
						NOT EXISTS (SELECT * from `{$wpdb->usermeta}` AS `um` WHERE `x`.`friendID` = `um`.`user_id` AND `um`.`meta_key`='peepso_is_hide_profile_from_user_listing')";
		}

		$sql = "SELECT COUNT(*) AS num_friends FROM (" . $sql . ") AS listFriend
			LEFT JOIN `{$wpdb->prefix}".PeepSoUser::TABLE."` as `f`
				   	ON `listFriend`.`friendID` = `f`.`usr_id`
			WHERE `f`.`usr_role` NOT IN ('register', 'ban', 'verified')";

		$mutualfriend = $wpdb->get_var($sql, 0, 0);

		return ($mutualfriend);
	}

	/**
	 * Returns the mutual friends of $user_id and $friend_id.
	 * @param  int $user_id The User asking for the query.
	 * @param  int $friend_id The User getting mutual friends from.
	 * @return array An array of both users' mutual friends' user ID's.
	 */
	public function get_mutual_friends($user_id, $friend_id, $args=array())
	{
		global $wpdb;

		$sql = $this->_get_mutual_friends_query($user_id, $friend_id, $args);

		$result = $wpdb->get_results($sql, ARRAY_A);
		foreach($result as $key => &$friend) {
			$friend['num_mutual_friends'] = $this->_get_num_mutual_friends($user_id, $friend['friendID']);
		}

		$result = $this->array_sort($result, 'num_mutual_friends', SORT_DESC);

		return ($result);
	}

	public function get_mutual_friends_count($user_id, $friend_id, $args=array()) {
		$key = 'mutual_count_' . $user_id . '_' . $friend_id;
		$cache_count = PeepSo3_Mayfly_Int::get($key);

		if (NULL !== $cache_count) {
			return intval($cache_count);
		} else {
			$count = count($this->get_mutual_friends($user_id, $friend_id, $args));
			PeepSo3_Mayfly_Int::set($key, $count, DAY_IN_SECONDS);
			return intval($count);
		}
	}

function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

	/**
	 * Adds an entry to the table
	 * @param int $from_id
	 * @param int $to_id
	 */
	public function add_friend($from_id, $to_id)
	{
		// Prevent duplicate entries
		if (FALSE === $this->are_friends($from_id, $to_id)) {
			global $wpdb;

			$wpdb->insert($wpdb->prefix . self::TABLE, array('fnd_user_id' => $from_id, 'fnd_friend_id' => $to_id));
			
			$PeepSoUserFollower = new PeepSoUserFollower($to_id, $from_id, TRUE);
			$PeepSoUserFollower = new PeepSoUserFollower($from_id, $to_id, TRUE);

			do_action('peepsofriends_after_add', $from_id, $to_id);

			return (TRUE);
		}

		return (FALSE);
	}

	/**
	 * Removes a friend entry between two users
	 * @param  int $from_id
	 * @param  int $to_id
	 */
	public function delete($from_id, $to_id)
	{
		global $wpdb;

		$wpdb->delete($wpdb->prefix . self::TABLE, array('fnd_user_id' => $from_id, 'fnd_friend_id' => $to_id));
		$wpdb->delete($wpdb->prefix . self::TABLE, array('fnd_user_id' => $to_id, 'fnd_friend_id' => $from_id));

		do_action('peepso_friends_after_delete', $from_id, $to_id);

		return (TRUE);
	}
}

// EOF
