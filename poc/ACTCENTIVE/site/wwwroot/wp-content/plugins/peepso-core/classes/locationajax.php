<?php

class PeepSoLocationAjax extends PeepSoAjaxCallback
{
	private static $_peepsolocation = NULL;

	protected function __construct()
	{
		parent::__construct();
		self::$_peepsolocation = PeepSoLocation::get_instance();
	}
}

// EOF
