<?php

class PeepSoGroupUsers
{
	const TABLE = 'peepso_group_members';

	private $_group_id;

	private $_table;

	public function __construct($group_id)
	{
		global $wpdb;

		$this->_group_id = intval($group_id);
		$this->_table = $wpdb->prefix.PeepSoGroupUsers::TABLE;

		if( !$this->_group_id > 0) {
			trigger_error('Invalid group_id');
		}
	}

	public function get_management($args = array(), $query = '', $order_by = 'gm_joined', $order = 'desc', $offset=NULL, $limit=NULL)
    {
        return $this->_get_users(array('member_owner','member_manager','member_moderator'), $args, $query, $order_by, $order, $offset, $limit);
    }

    public function get_owners_and_managers($args = array(), $query = '', $order_by = 'gm_joined', $order = 'desc', $offset=NULL, $limit=NULL)
    {
        return $this->_get_users(['member_owner','member_manager'], $args, $query,  $order_by, $order, $offset, $limit);
    }

	public function get_owners($args = array(), $query = '', $order_by = 'gm_joined', $order = 'desc', $offset=NULL, $limit=NULL)
	{
		return $this->_get_users('member_owner', $args, $query,  $order_by, $order, $offset, $limit);
	}

	public function get_managers($args = array(), $query = '', $order_by = 'gm_joined', $order = 'desc', $offset=NULL, $limit=NULL)
	{
		return $this->_get_users('member_manager', $args, $query,  $order_by, $order, $offset, $limit);
	}

	public function get_moderators($args = array(), $query = '', $order_by = 'gm_joined', $order = 'desc', $offset=NULL, $limit=NULL)
	{
		return $this->_get_users('member_moderator', $args, $query,  $order_by, $order, $offset, $limit);
	}

	public function get_members($args = array(), $query = '', $order_by = 'gm_joined', $order = 'desc', $offset = NULL, $limit=NULL)
	{
		return $this->_get_users('member', $args, $query,  $order_by, $order, $offset, $limit);
	}

    public function get_pending_user($args = array(), $query = '', $order_by = 'gm_joined', $order = 'desc', $offset = NULL, $limit=NULL)
    {
        return $this->_get_users('pending_user', $args, $query,  $order_by, $order, $offset, $limit);
    }

    public function get_pending_admin($args = array(), $query = '', $order_by = 'gm_joined', $order = 'desc', $offset = NULL, $limit=NULL)
    {
        return $this->_get_users('pending_admin', $args, $query,  $order_by, $order, $offset, $limit);
    }

    public function get_banned($args = array(), $query = '', $order_by = 'gm_joined', $order = 'desc', $offset = NULL, $limit=NULL)
    {
        return $this->_get_users('banned', $args, $query,  $order_by, $order, $offset, $limit);
    }

    private function _get_users($role, $args = array(), $query = '', $order_by = NULL, $order = NULL, $offset = NULL, $limit = NULL)
	{
		$args = array_merge($args, array(
			'_peepso_group_role' => $role
		));

		if( NULL !== $order_by && strlen($order_by) ) {
			if('ASC' !== $order && 'DESC' !== $order) {
				$order = 'DESC';
			}

			$args['_peepso_group_orderby'] = $order_by;
			$args['_peepso_group_order'] = $order;
		}

		if(NULL !== $offset && NULL !== $limit) {
			$args['offset']= $offset;
			$args['number']	= $limit;
		}

		add_action('peepso_pre_user_query', array(&$this, 'members_query'));
		$query_results = new PeepSoUserSearch($args, get_current_user_id(), $query);
		remove_action('peepso_pre_user_query', array(&$this, 'members_query'));

		$members = array();

		if(count($query_results->results)) {
			foreach($query_results->results as $row) {
			    $PeepSoGroupUser = new PeepSoGroupUser($this->_group_id, $row);
				$members[] = $PeepSoGroupUser;
			}
		}

		return $members;
	}



	/**
	 * Count members and update the members_count meta key
	 * @return int
	 */
	public function update_members_count($role = NULL)
	{
		global $wpdb;


		if(NULL == $role) {
			// Count everyone with a "member*" status
			$meta ='peepso_group_members_count';
			$query = "SELECT COUNT(`gm_user_id`) as members_count FROM {$this->_table} LEFT JOIN `{$wpdb->prefix}".PeepSoUser::TABLE."` as `f` ON `{$this->_table}`.`gm_user_id` = `f`.`usr_id` WHERE `f`.`usr_role` NOT IN ('register', 'ban', 'verified') AND `gm_group_id`={$this->_group_id} AND `gm_user_status` LIKE 'member%'";
		} else {
			// Count everyone with a given role
			$meta ='peepso_group_'.$role.'_members_count';
			$query = "SELECT COUNT(`gm_user_id`) as members_count FROM {$this->_table} LEFT JOIN `{$wpdb->prefix}".PeepSoUser::TABLE."` as `f` ON `{$this->_table}`.`gm_user_id` = `f`.`usr_id` WHERE `f`.`usr_role` NOT IN ('register', 'ban', 'verified') AND `gm_group_id`={$this->_group_id} AND `gm_user_status`= '$role'";
		}

		$result = $wpdb->get_row($query, ARRAY_A);
		$members_count  = intval($result['members_count']);

		if(0 === $members_count && NULL == $role) {
			new PeepSoError('[GROUPS] Group member count should never be 0 (zero)');
		}

		// Update the post meta
		update_post_meta($this->_group_id, $meta, $members_count);

		return($members_count);
	}

	public function search_to_invite($args, $query)
	{
		add_action('peepso_pre_user_query', array(&$this, 'not_members_query'));
		$query_results = new PeepSoUserSearch($args, get_current_user_id(), $query);
		remove_action('peepso_pre_user_query', array(&$this, 'not_members_query'));

		return $query_results;
	}

	/**
	 * Modifies a WP_User_Query instance to only return users that are friends.
	 * @param  WP_User_Query $wp_user_query
	 */
	public function not_members_query(WP_User_Query $wp_user_query)
	{
		global $wpdb;

		$wp_user_query->query_from .= $wpdb->prepare(" LEFT JOIN {$this->_table} ON `{$wpdb->users}`.`ID` = `{$this->_table}`.`gm_user_id` AND `{$this->_table}`.`gm_group_id` = %d ", $this->_group_id);
		$wp_user_query->query_where .= " AND `{$this->_table}`.`gm_user_id` IS NULL ";

		return $wp_user_query;
	}

	public function members_query(WP_User_Query $wp_user_query)
	{
		global $wpdb;

		$wp_user_query->query_from .= $wpdb->prepare(" LEFT JOIN {$this->_table} ON `{$wpdb->users}`.`ID` = `{$this->_table}`.`gm_user_id` AND `{$this->_table}`.`gm_group_id` = %d ", $this->_group_id);
		
		$query = '';
		if (isset($wp_user_query->query_vars['_peepso_group_role'])) {
			$role = $wp_user_query->query_vars['_peepso_group_role'];

			if (!is_array($role)) {
				$role = array($role);
			}
	
			if (count($role) > 1) {
				$query .= " AND (";
				$sub_query ='';
				foreach($role as $r) {
					$sub_query .= "OR `gm_user_status` LIKE '{$r}%' ";
				}
	
				$sub_query = trim($sub_query, 'OR');
	
				$query.= $sub_query . " ) ";
	
			} else {
				$query .= " AND `gm_user_status` LIKE '{$role[0]}%'";
			}
	
			$wp_user_query->query_where .= $query;
		}

		$wp_user_query->query_orderby = ' ORDER BY ' . $wp_user_query->query_vars['_peepso_group_orderby'] . ' ' . $wp_user_query->query_vars['_peepso_group_order'];

		return $wp_user_query;
	}
}

// EOF