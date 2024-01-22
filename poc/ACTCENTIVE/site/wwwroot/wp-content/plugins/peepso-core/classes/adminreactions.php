<?php

class PeepSoAdminReactions
{
	public static function administration()
	{
		self::enqueue_scripts();

		$PeepSoReactionsModel = new PeepSoReactionsModel();

		PeepSoTemplate::exec_template('reactions','admin_reactions', $PeepSoReactionsModel->reactions);
	}

	public static function enqueue_scripts()
	{
		wp_deregister_script('peepso-admin-manage-reactions');
		wp_enqueue_script('peepso-admin-manage-reactions', PeepSo::get_asset('js/admin/manage-reactions.js'),
			array('peepso', 'jquery-ui-sortable'), PeepSo::PLUGIN_VERSION, TRUE);

		wp_enqueue_style('peepso-admin-manage-reactions', PeepSo::get_asset('css/admin-reactions.css'),
			array(), PeepSo::PLUGIN_VERSION, 'all');
	}
}