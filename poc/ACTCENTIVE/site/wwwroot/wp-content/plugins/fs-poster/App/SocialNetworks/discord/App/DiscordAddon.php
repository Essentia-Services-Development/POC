<?php

namespace FSPoster\App\SocialNetworks\discord\App;

use FSPoster\App\Providers\Addon;

class DiscordAddon extends Addon
{
	protected static $SLUG = 'discord';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
		self::addFilter( 'add_new_app', [ Listener::class, 'addApp' ] );
	}
}