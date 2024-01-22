<?php

namespace FSPoster\App\SocialNetworks\wordpress\App;

use FSPoster\App\Providers\Addon;

class WordpressAddon extends Addon
{
	protected static $SLUG = 'wordpress';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
	}
}