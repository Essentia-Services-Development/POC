<?php

class PeepSoAdminProfileTypes
{
	public static function administration()
	{
		self::enqueue_scripts();

		PeepSoTemplate::exec_template('admin','profile_types', array());
	}

	public static function enqueue_scripts()
	{
		// enqueue scripts
	}
}

// EOF
