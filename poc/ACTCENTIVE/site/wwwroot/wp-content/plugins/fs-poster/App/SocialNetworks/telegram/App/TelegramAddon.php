<?php

namespace FSPoster\App\SocialNetworks\telegram\App;

use FSPoster\App\Providers\Addon;

class TelegramAddon extends Addon
{
	protected static $SLUG = 'telegram';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
	}
}