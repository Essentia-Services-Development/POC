<?php

class PeepSoTutorCourseAutoGroups
{	
	const TABLE = 'peepso_tutor_course_group_auto';

	private $_table;

	public function __construct()
	{
		global $wpdb;

		$this->_table = $wpdb->prefix . PeepSoTutorCourseAutoGroups::TABLE;
	}

	public function toggle_course_group($tutor_course_id, $group)
	{
		global $wpdb;
        $group = intval($group);
        $tutor_course_id = intval($tutor_course_id);

		
		$sql = "REPLACE INTO {$this->_table} (`tutor_course_id`,`group_id`) VALUES ('$tutor_course_id','$group')";
		$wpdb->query($sql);
		
		if($wpdb->last_error) { return $wpdb->last_error; }

		return true;
	}

	public function update_course_group($tutor_course_id, $groups)
	{
		global $wpdb;

		$sqlQuery = "DELETE FROM $this->_table WHERE `tutor_course_id` = '" . esc_sql($tutor_course_id) . "'";
		$wpdb->query($sqlQuery);
		
		if($wpdb->last_error) { return $wpdb->last_error; }

		// add the given links [back?] in...
		foreach($groups as $group)
		{
			if(is_string($r = $this->toggle_course_group( $tutor_course_id, $group)))
			{
				return $r;
			}
		}

		return true;
	}

    public function get_groups_by_course($tutor_course_id)
    {
        $tutor_course_id = intval($tutor_course_id);

        global $wpdb;
        $groups = $wpdb->get_col("SELECT c.group_id
											FROM {$this->_table} AS c
											WHERE c.tutor_course_id = '" . $tutor_course_id . "'");

        return $groups;
    }



    public function get_courses_by_group($group_id )
    {
        $group_id  = intval($group_id );

        global $wpdb;
        $courses = $wpdb->get_col("SELECT c.tutor_course_id
											FROM {$this->_table} AS c
											WHERE c.group_id = '" . $group_id . "'");

        return $courses;
    }



}
