<?php

namespace FSPoster\App\Providers;

class Addon
{
	protected static $SLUG;

	public static function getSlug()
	{
		return static::$SLUG;
	}

	/**
	 * @param $hook string
	 * @param $method callable|array
	 * @param int $priority
	 * @param int $args
	 *
	 * @return void
	*/
	protected static function addFilter( $hook, $method, $priority = 10, $args = 1 )
	{
		$hook = sprintf( "fsp_%s_%s", $hook, static::$SLUG );

		add_filter( $hook, $method, $priority, $args );
	}
}