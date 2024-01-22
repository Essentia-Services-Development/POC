<?php
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');
/*
 * Performs installation process
 * @package PeepSoPMP
 * @author PeepSo
 */

class PeepSoPMPInstall extends PeepSoInstall
{

	/*
	 * called on plugin activation; performs all installation tasks
	 */
	public function plugin_activation($is_core=TRUE)
	{
		$activated = parent::plugin_activation($is_core);
		if($activated) {
			// Set some default settings
			$settings = PeepSoConfigSettings::get_instance();
			$settings->set_option('peepso_pmp_integration_enabled', 0);
			$settings->set_option('peepso_pmp_email_notification_enabled', 0);
		}
		return ($activated);
	}

	public static function get_table_data()
	{
		$aRet = array(
			'membership_level_group' => "
				CREATE TABLE membership_level_group (
					membership_level_id int(11) unsigned NOT NULL,
					group_id int(11) unsigned NOT NULL,
					modified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					UNIQUE KEY membership_category (membership_level_id,group_id),
					UNIQUE KEY category_membership (group_id,membership_level_id)
				) ENGINE=InnoDB",
			'membership_level_vip' => "
				CREATE TABLE membership_level_vip (
					membership_level_id int(11) unsigned NOT NULL,
					vip_id int(11) unsigned NOT NULL,
					modified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					UNIQUE KEY membership_category_vip (membership_level_id,vip_id),
					UNIQUE KEY category_membership_vip (vip_id,membership_level_id)
				) ENGINE=InnoDB",
		);

		return $aRet;
	}
}