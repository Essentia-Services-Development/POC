<?php

class PeepSoVipIconAdmin
{
	public static function administration()
	{
		self::enqueue_scripts();

		$PeepSoVipIconsModel = new PeepSoVipIconsModel();

		PeepSoTemplate::exec_template('vip','admin_vipicons', $PeepSoVipIconsModel->vipicons);
	}

	public static function enqueue_scripts()
	{
		wp_register_script('peepso-admin-vip', PeepSo::get_asset('js/vip/admin.js'),
			array('jquery', 'jquery-ui-sortable', 'underscore', 'peepso'), PeepSo::PLUGIN_VERSION, TRUE);

		wp_register_script('peepso-admin-vip', PeepSo::get_asset('js/vip/admin-profiles.min.js'),
			array('jquery', 'jquery-ui-sortable', 'underscore', 'peepso'), PeepSo::PLUGIN_VERSION, TRUE);

		wp_enqueue_script('peepso-admin-vip');
	}
}
