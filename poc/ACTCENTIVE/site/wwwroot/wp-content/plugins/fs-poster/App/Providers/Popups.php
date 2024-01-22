<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Pages\Base\Controllers\Popup as BasePopup;
use FSPoster\App\Pages\Apps\Controllers\Popup as AppsPopup;
use FSPoster\App\Pages\Share\Controllers\Popup as SharePopup;
use FSPoster\App\Pages\Accounts\Controllers\Popup as AccountsPopup;
use FSPoster\App\Pages\Schedules\Controllers\Popup as SchedulesPopup;
use FSPoster\App\Pages\Logs\Controllers\Popup as LogsPopup;

class Popups
{
	use BasePopup, AccountsPopup, SchedulesPopup, SharePopup, AppsPopup, LogsPopup;

	public function __construct ()
	{
		$methods = get_class_methods( $this );

		foreach ( $methods as $method )
		{
			if ( $method === '__construct' )
			{
				continue;
			}

			add_action( 'wp_ajax_popup_' . $method, function () use ( $method ) {
				define( 'MODAL', TRUE );
				$this->$method();
				exit();
			} );
		}
	}
}
