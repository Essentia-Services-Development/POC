<?php
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');

class PeepSoTutorLMSInstall extends PeepSoInstall 
{
	public function plugin_activation( $is_core = FALSE )
	{
		parent::plugin_activation($is_core);

		return (TRUE);
	}

    public static function get_table_data()
    {
        $aRet = array(
            'tutor_course_group' => "
				CREATE TABLE tutor_course_group (
					tutor_course_id int(11) unsigned NOT NULL,
					group_id int(11) unsigned NOT NULL,
					modified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					UNIQUE KEY course_group (tutor_course_id,group_id),
					UNIQUE KEY group_course (group_id,tutor_course_id)
				) ENGINE=InnoDB",
            'tutor_course_group_auto' => "
				CREATE TABLE tutor_course_group_auto (
					tutor_course_id int(11) unsigned NOT NULL,
					group_id int(11) unsigned NOT NULL,
					modified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					UNIQUE KEY course_group (tutor_course_id,group_id),
					UNIQUE KEY group_course (group_id,tutor_course_id)
				) ENGINE=InnoDB",
            'tutor_course_vip' => "
				CREATE TABLE tutor_course_vip (
					tutor_course_id int(11) unsigned NOT NULL,
					vip_id int(11) unsigned NOT NULL,
					modified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					UNIQUE KEY course_vip (tutor_course_id,vip_id),
					UNIQUE KEY vip_course (vip_id,tutor_course_id)
				) ENGINE=InnoDB",
        );

        return $aRet;
    }
}