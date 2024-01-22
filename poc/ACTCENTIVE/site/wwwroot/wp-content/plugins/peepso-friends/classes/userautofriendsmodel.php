<?php

class PeepSoUserAutoFriendsModel
{
	const TABLE = 'peepso_user_autofriends';

	public function __construct()
	{

	}

	public static function get_table_name()
	{
		global $wpdb;

		return $wpdb->prefix . self::TABLE;
	}


	/*
	 * Adds item to user autofriends table
	 * @param int $user_id The User Id of the person being autofriends
	 * @return Boolean TRUE on success; FALSE on failure
	 */
	public function add_user($user_id)
	{
		$data = array(
			'af_user_id' => $user_id
		);

		global $wpdb;
		$wpdb->insert($wpdb->prefix . self::TABLE, $data);
		return (TRUE);
	}


	/*
	 * Retrives a list of Reported items
	 * @param string $orderby The data column to perform ordering on
	 * $param string $order The ordering type, 'ASC' or 'DESC'
	 * @param int $offset The offset used in the LIMIT clause
	 * $param int $limit The limit used in the LIMIT clause
	 * @return array The collection of items queried
	 */
	public function get_users($orderby = 'af_user_id', $order = 'ASC', $offset = 0, $limit = 0)
	{
		global $wpdb;

		$sql = "SELECT * " .
				" FROM `" . $this->get_table_name(). "` AS `af` " .
				(!empty($orderby) ? " ORDER BY {$orderby} {$order} " : '') ;
		if($limit > 0) {
			$sql .= " LIMIT {$offset},{$limit} ";
		}
		$aItems = $wpdb->get_results($sql, ARRAY_A);

		return ($aItems);
	}


	/*
	 * Return the number of users
	 * @return int Number of users
	 */
	public function get_num_reported_items()
	{
		global $wpdb;

		$sql = "SELECT COUNT(DISTINCT `af_user_id`) " .
				" FROM `" . $this->get_table_name() . "` ";
		$totalItems = $wpdb->get_var($sql);
		return ($totalItems);
	}

	/**
	 * Deletes the users from the 'peepso_user_autofriends' table.
	 * @param  int $user_id The user ID from the database.
	 * @return mixed Returns the number of rows deleted or FALSE on error.
	 */
	public function remove_user($user_id)
	{
		global $wpdb;

        if (!is_null($user_id))
        {
            return $wpdb->delete(self::get_table_name(), 
                array
                (
                    'af_user_id' => $user_id
                )
            );
        }
	}

	/**
	 * Befriends with all users.
	 * @param  int $user_id The user ID from the database.
	 * @return mixed Returns the number of rows deleted or FALSE on error.
	 */
	public function befriends($user_id)
	{
		global $wpdb;

		$sql = "SELECT COUNT(DISTINCT `af_user_id`) " .
				" FROM `" . $this->get_table_name() . "` " .
				" WHERE `af_user_id` = " . $user_id;
		$exists = $wpdb->get_var($sql);

		$limit = PeepSo::get_option_new('friends_max_amount');
		$limit = intval($limit);

        if ($exists > 0 && $limit > 0)
        {
			$args = array(
				'orderby' => 'user_registered',
				'order' => 'DESC'
			);

			add_filter('pre_user_query', array(&$this, 'filter_list_community_user_query'));
			$user_query = new WP_User_Query($args);
			remove_filter('pre_user_query', array(&$this, 'filter_list_community_user_query'));

			if (0 === $user_query->total_users) {
				return (FALSE);
			} else {
				$PeepSoFriends = PeepSoFriends::get_instance();
				$PeepSoFriendsModel = PeepSoFriendsModel::get_instance();
				$from_num_friends = $PeepSoFriends->get_num_friends($user_id, TRUE);
				$from_over_limit =  ($from_num_friends >= $limit);
				
				if ($from_over_limit) {
					return (FALSE);
				}
                PeepSoFriendsCache::_([$user_id]);
				foreach ($user_query->results as $user) {
					$to_id = $user->ID;

					if ($user_id !== $to_id) {
						$to_num_friends = $PeepSoFriends->get_num_friends($to_id, TRUE);
                		$to_over_limit = ($to_num_friends >= $limit);
						
						if( ($from_num_friends < $limit) && !$to_over_limit ) {
							$model = PeepSoFriendsModel::get_instance();
							if ($model->add_friend($user_id, $to_id)) {
								// $wpdb->delete($wpdb->prefix . self::TABLE,
								// 	array('freq_id' => $request_id)
								// );

								// do_action('peepso_friends_requests_after_accept', $from_id, $to_id);
								$PeepSoUserFollower = new PeepSoUserFollower($user_id, $to_id, TRUE);
								$PeepSoUserFollower = new PeepSoUserFollower($to_id, $user_id, TRUE);

								$from_num_friends++;
                                // update cache
                                PeepSoFriendsCache::_([$to_id]);
							}
						}
					} 
				}
                PeepSoFriendsCache::_([$user_id]);
				return (TRUE);
			}
        }

        return (FALSE);
	}

	public function search_user($key)
	{
		global $wpdb;

		$args = array(
			'orderby' => 'user_registered',
			'order' => 'DESC',
			'search'         => '*'.esc_attr( $key ).'*',
		    'search_columns' => array(
		        'user_login',
		        'user_nicename',
		        'user_email',
		        'user_url',
		    ),
		);

		add_filter('pre_user_query', array(&$this, 'filter_user_query'));
		$user_query = new WP_User_Query($args);
		remove_filter('pre_user_query', array(&$this, 'filter_user_query'));

		$users = [];
		if ($user_query->total_users > 0) {
			foreach ($user_query->results as $user) {
				$users[] = array(
					'value' => $user->user_login,
					'id' => $user->ID,
				);
			}
		}

		return $users;
	}

	/**
	 * Filters the WP_User_Query, adding the WHERE clause to look for PeepSo roles
	 * @param WP_User_query $query The query object to filter
	 * @return WP_User_Query The modified query object
	 */
	public function filter_list_community_user_query($query)
	{
		global $wpdb;

		$query->query_from .= " LEFT JOIN `{$wpdb->prefix}" . PeepSoUser::TABLE . "` ON `{$wpdb->users}`.ID = `usr_id` ";
		$query->query_where .= " AND `usr_role`='member' ";
		return ($query);
	}

	/**
	 * Filters the WP_User_Query, adding the WHERE clause to look for Autofriends ta le
	 * @param WP_User_query $query The query object to filter
	 * @return WP_User_Query The modified query object
	 */
	public function filter_user_query($query)
	{
		global $wpdb;
		$input = new PeepSoInput();

		$query->query_from .= " LEFT JOIN `{$wpdb->prefix}" . self::TABLE . "` ON `{$wpdb->users}`.ID = `af_user_id` ";
		$query->query_where .= " AND `af_user_id` IS NULL ";
		return ($query);
	}
}

// EOF
