<?php

namespace FSPoster\App\SocialNetworks\threads\App;

use FSPoster\App\Providers\Addon;

class ThreadsAddon extends Addon
{
	protected static $SLUG = 'threads';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
		self::addFilter( 'get_insights', [ Listener::class, 'getInsights' ], 10, 3 );
	}
}