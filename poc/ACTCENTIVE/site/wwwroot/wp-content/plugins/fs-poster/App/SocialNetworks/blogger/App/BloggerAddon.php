<?php

namespace FSPoster\App\SocialNetworks\blogger\App;

use FSPoster\App\Providers\Addon;

class BloggerAddon extends Addon
{
	protected static $SLUG = 'blogger';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
		self::addFilter( 'add_new_app', [ Listener::class, 'addApp' ] );
	}
}