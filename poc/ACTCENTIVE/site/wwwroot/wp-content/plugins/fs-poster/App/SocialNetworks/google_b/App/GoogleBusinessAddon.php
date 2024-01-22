<?php

namespace FSPoster\App\SocialNetworks\google_b\App;

use FSPoster\App\Providers\Addon;

class GoogleBusinessAddon extends Addon
{
	protected static $SLUG = 'google_b';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
		self::addFilter( 'add_new_app', [ Listener::class, 'addApp' ] );
	}
}