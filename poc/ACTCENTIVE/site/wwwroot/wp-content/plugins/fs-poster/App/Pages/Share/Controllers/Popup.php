<?php

namespace FSPoster\App\Pages\Share\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Popup
{
	public function share_feeds ()
	{
		$post_id         = Request::post( 'post_id', '0', 'num' );
		$is_paused_feeds = Request::post( 'is_paused_feeds', 0, 'int' );
		$dont_reload     = Request::post( 'dont_reload', '0', 'num', [ 0, 1 ] );

		if ( $is_paused_feeds !== 1 && ! ( $post_id > 0 ) )
		{
			exit();
		}

		if ( $is_paused_feeds === 1 )
		{
			$feeds = DB::DB()->get_results( DB::DB()->prepare( 'SELECT * FROM ' . DB::table( 'feeds' ) . ' WHERE blog_id = %d AND is_sended = %d AND share_on_background = %d', [
				Helper::getBlogId(),
				0,
				0
			] ), ARRAY_A );
		}
		else
		{
			$feeds = DB::DB()->get_results( DB::DB()->prepare( 'SELECT * FROM ' . DB::table( 'feeds' ) . ' WHERE blog_id = %d AND is_sended = %d AND post_id = %d AND send_time >= %s  AND share_on_background = %d', [
				Helper::getBlogId(),
				0,
				$post_id,
				Date::dateTimeSQL( '-30 seconds' ),
				0
			] ), ARRAY_A );
		}

		if(Helper::getOption('post_interval_type') == 1){
			$sn_list = [
				'fb',
				'threads',
				'twitter',
				'instagram',
				'planly',
				'linkedin',
				'pinterest',
				'telegram',
				'reddit',
				'youtube_community',
				'tumblr',
				'ok',
				'vk',
				'google_b',
				'medium',
				'wordpress',
				'blogger',
				'plurk',
				'xing',
				'discord',
				'mastodon',
			];

			$temp = [];

			foreach ( $sn_list as $sn )
			{
				$temp[ $sn ] = [];
			}

			foreach ( $feeds as $feed )
			{
				$temp[ $feed[ 'driver' ] ][] = $feed;
			}

			foreach ( $sn_list as $sn )
			{
				if ( empty( $temp[ $sn ] ) )
				{
					unset( $temp[ $sn ] );
				}
			}

			$i = 0;
			$result = [];

			while ( ! empty( $temp ) )
			{
				$flag = TRUE;
				foreach ( $temp as $j => &$t )
				{
					if($flag){
						$flag = FALSE;
					}else{
						$t[ $i ]['interval'] = 0;
					}
					$result[] = $t[ $i ];
					unset( $t[ $i ] );

					if ( empty( $t ) )
					{
						unset( $temp[ $j ] );
					}
				}
				$i++;
			}

			$feeds = $result;
		}

		Pages::modal( 'Share', 'share_feeds', [
			'parameters' => [
				'feeds'       => $feeds,
				'dont_reload' => $dont_reload
			]
		] );
	}

	public function share_saved_post ()
	{
		$post_id = Request::post( 'post_id', '0', 'num' );

		if ( ! ( $post_id > 0 ) )
		{
			exit();
		}

		Pages::modal( 'Share', 'share_saved_post', [
			'parameters' => [
				'post_id' => $post_id
			]
		] );
	}
}