<?php

/**
 * @todo what's missing
 *
 */
class PeepSoMembershipLevelVIP
{	
	const TABLE = 'peepso_membership_level_vip';

	private $_table;

	public function __construct()
	{
		global $wpdb;

		$this->_table = $wpdb->prefix . PeepSoMembershipLevelVIP::TABLE;
	}

	public function toggle_membership_level_vip($membership_level_id, $vip)
	{
		global $wpdb;
		$vip = intval($vip);

		
		$sql = "REPLACE INTO {$this->_table} (`membership_level_id`,`vip_id`) VALUES ('$membership_level_id','$vip')";
		$wpdb->query($sql);
		
		if($wpdb->last_error) { return $wpdb->last_error; }

		return true;
	}

	public function update_membership_level_vip($membership_level_id, $vips)
	{
		global $wpdb;

		// remove all existing links...
		$sqlQuery = "DELETE FROM $this->_table WHERE `membership_level_id` = '" . esc_sql($membership_level_id) . "'";
		$wpdb->query($sqlQuery);
		
		if($wpdb->last_error) { return $wpdb->last_error; }

		// add the given links [back?] in...
		foreach($vips as $vip)
		{
			if(is_string($r = $this->toggle_membership_level_vip( $membership_level_id, $vip)))
			{
				//uh oh, error
				return $r;
			}
		}

		//all good
		return true;
	}

	public function get_vip_by_level($membership_level_id)
	{
		$membership_level_id = intval($membership_level_id);

		global $wpdb;
		$groups = $wpdb->get_var("SELECT c.vip_id
											FROM {$this->_table} AS c
											WHERE c.membership_level_id = '" . $membership_level_id . "'");

		return $groups;
	}

}
