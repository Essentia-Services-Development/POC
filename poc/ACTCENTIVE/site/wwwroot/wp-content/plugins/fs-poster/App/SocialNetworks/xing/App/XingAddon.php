<?php

namespace FSPoster\App\SocialNetworks\xing\App;

use FSPoster\App\Providers\Addon;

class XingAddon extends Addon
{
	protected static $SLUG = 'xing';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
	}
}