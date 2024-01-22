<?php

namespace FSPoster\App\SocialNetworks\medium\App;

use FSPoster\App\Providers\Addon;

class MediumAddon extends Addon
{
	protected static $SLUG = 'medium';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
		self::addFilter( 'add_new_app', [ Listener::class, 'addApp' ] );
	}
}