<?php

namespace FSPoster\App\Pages\Schedules\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

class Action
{
	public function get_list ()
    {
        $schedule_page = Request::get( 'schedule_page', 1, 'int' );

		return [
            'schedule_page' => $schedule_page,
		];
	}
}
