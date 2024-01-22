<?php

namespace FSPoster\App\SocialNetworks\youtube_community\App;

use FSPoster\App\Providers\Addon;

class YoutubeCommunityAddon extends Addon
{
	protected static $SLUG = 'youtube_community';

	public static function init()
	{
		self::addFilter( 'share_post', [ Listener::class, 'sharePost' ], 10, 2 );
	}
}