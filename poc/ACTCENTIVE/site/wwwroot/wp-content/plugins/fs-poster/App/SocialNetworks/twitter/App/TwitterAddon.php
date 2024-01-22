<?php

namespace FSPoster\App\SocialNetworks\twitter\App;

use FSPoster\App\Providers\Addon;

class TwitterAddon extends Addon
{
	protected static $SLUG = 'twitter';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
		self::addFilter( 'add_new_app', [ Listener::class, 'addApp' ] );
		self::addFilter( 'get_insights', [ Listener::class, 'getInsights' ], 10, 3 );
	}
}