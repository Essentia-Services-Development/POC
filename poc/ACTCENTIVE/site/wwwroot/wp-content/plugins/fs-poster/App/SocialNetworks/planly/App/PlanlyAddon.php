<?php

namespace FSPoster\App\SocialNetworks\planly\App;

use FSPoster\App\Providers\Addon;

class PlanlyAddon extends Addon
{
	protected static $SLUG = 'planly';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
	}
}