<?php
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');

class PeepSoWPJMInstall extends PeepSoInstall
{
	protected $default_config = array(
		'wpjm_enable' => 1,
		'wpjm_stream_enable' => 1,
	);

	public function plugin_activation( $is_core = FALSE )
	{
		parent::plugin_activation($is_core);

		return (TRUE);
	}
}