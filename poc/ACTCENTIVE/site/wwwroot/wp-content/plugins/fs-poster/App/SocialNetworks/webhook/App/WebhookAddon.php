<?php

namespace FSPoster\App\SocialNetworks\webhook\App;

use FSPoster\App\Providers\Addon;

class WebhookAddon extends Addon
{
	protected static $SLUG = 'webhook';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
	}
}