<?php
/*
 * Performs tasks for Admin page requests
 * @package GroupSo
 * @author PeepSo
 */

class PeepSoGroupsAdmin
{
	private function __construct()
	{

	}

	public static function admin_page()
	{
		PeepSoTemplate::exec_template('admin', 'groups', array() );
	}

	public static function admin_header($title)
	{
		echo '<h2><img src="', PeepSo::get_asset('images/admin/logo.png'), '" width="150" />';
		echo ' v' . PeepSoGroupsPlugin::PLUGIN_VERSION;

		if(strlen(PeepSoGroupsPlugin::PLUGIN_RELEASE)) {
			echo "-" . PeepSoGroupsPlugin::PLUGIN_RELEASE;
		}

		echo ' - ' ,  $title , '</h2>', PHP_EOL;
	}
}