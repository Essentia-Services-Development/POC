<?php

namespace FSPoster\App\SocialNetworks\pinterest\App;

use FSPoster\App\Providers\Addon;

class PinterestAddon extends Addon
{
	protected static $SLUG = 'pinterest';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
		self::addFilter( 'add_new_app', [ Listener::class, 'addApp' ] );
		self::addFilter( 'get_insights', [ Listener::class, 'getInsights' ], 10, 3 );
	}
}