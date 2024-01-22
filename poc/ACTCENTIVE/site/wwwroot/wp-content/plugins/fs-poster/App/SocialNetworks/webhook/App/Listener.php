<?php

namespace FSPoster\App\SocialNetworks\webhook\App;

use FSPoster\App\Libraries\webhook\Webhook;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\v7\PostData;

class Listener
{
	/**
	 * @param $result array
	 * @param $postData PostData
	 *
	 * @return array
	 * */
	public static function sharePost( $result, $postData )
	{
		$options = json_decode( $postData->getOptions(), TRUE );

		if ( empty( $options ) )
		{
			return Webhook::sendPost( [] );
		}

		$options = self::filterOption( $options, $postData );

		return Webhook::sendPost( $options );
	}

	/**
	 * @param $options array
	 * @param $postData PostData
	 *
	 * @return array
	*/
	private static function filterOption( $options, $postData )
	{
		if ( isset( $options[ 'url' ] ) )
		{
			$options[ 'url' ] = Helper::replaceTags( $options[ 'url' ], $postData->post, $postData->link, $postData->shortLink );
		}

		if ( isset( $options[ 'json' ] ) )
		{
			$options[ 'json' ] = Helper::replaceTags( $options[ 'json' ], $postData->post, $postData->link, $postData->shortLink );
		}

		if ( isset( $options[ 'headers' ] ) )
		{
			foreach ( $options[ 'headers' ] as &$v )
			{
				$v = Helper::replaceTags( $v, $postData->post, $postData->link, $postData->shortLink );
			}
		}

		if ( isset( $options[ 'form_data' ] ) )
		{
			foreach ( $options[ 'form_data' ] as &$v )
			{
				$v = Helper::replaceTags( $v, $postData->post, $postData->link, $postData->shortLink );
			}
		}

		return $options;
	}
}