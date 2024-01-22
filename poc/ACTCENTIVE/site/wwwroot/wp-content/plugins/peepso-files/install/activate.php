<?php
require_once(PeepSo::get_plugin_dir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'install.php');

class PeepSoFileUploadsInstall extends PeepSoInstall
{
	protected $default_config = array(
		'fileuploads_enable' => 1,
		'fileuploads_allowed_filetype' => 'PDF' . PHP_EOL . 'ZIP',
		'fileuploads_max_upload_size' => 20,
	);

	public function plugin_activation( $is_core = FALSE )
	{
		parent::plugin_activation($is_core);

		return (TRUE);
	}
}