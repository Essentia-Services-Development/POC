<?php

namespace FSPoster\App\SocialNetworks\tumblr\App;

use FSPoster\App\Providers\Addon;

class TumblrAddon extends Addon
{
	protected static $SLUG = 'tumblr';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
		self::addFilter( 'add_new_app', [ Listener::class, 'addApp' ] );
	}
}