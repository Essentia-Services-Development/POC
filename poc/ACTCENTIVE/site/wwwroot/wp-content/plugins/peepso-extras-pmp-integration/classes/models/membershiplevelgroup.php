<?php

/**
 * @todo what's missing
 *
 */
class PeepSoMembershipLevelGroup
{	
	const TABLE = 'peepso_membership_level_group';

	private $_table;

	public function __construct()
	{
		global $wpdb;

		$this->_table = $wpdb->prefix . PeepSoMembershipLevelGroup::TABLE;
	}

	public function toggle_membership_level_group($membership_level_id, $group)
	{
		global $wpdb;
		$group = intval($group);

		
		$sql = "REPLACE INTO {$this->_table} (`membership_level_id`,`group_id`) VALUES ('$membership_level_id','$group')";
		$wpdb->query($sql);
		
		if($wpdb->last_error) { return $wpdb->last_error; }

		return true;
	}

	public function update_membership_level_group($membership_level_id, $groups)
	{
		global $wpdb;

		// remove all existing links...
		$sqlQuery = "DELETE FROM $this->_table WHERE `membership_level_id` = '" . esc_sql($membership_level_id) . "'";
		$wpdb->query($sqlQuery);
		
		if($wpdb->last_error) { return $wpdb->last_error; }

		// add the given links [back?] in...
		foreach($groups as $group)
		{
			if(is_string($r = $this->toggle_membership_level_group( $membership_level_id, $group)))
			{
				//uh oh, error
				return $r;
			}
		}

		//all good
		return true;
	}

	public function get_groups_by_level($membership_level_id)
	{
		$membership_level_id = intval($membership_level_id);

		global $wpdb;
		$groups = $wpdb->get_col("SELECT c.group_id
											FROM {$this->_table} AS c
											WHERE c.membership_level_id = '" . $membership_level_id . "'");

		return $groups;
	}

}
