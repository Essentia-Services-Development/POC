<?php

namespace FSPoster\App\SocialNetworks\twitter\App;

use FSPoster\App\Libraries\twitter\Twitter;
use FSPoster\App\Libraries\twitter\TwitterPrivateAPI;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\TwitterAutoCut;
use FSPoster\App\Providers\v7\PostData;
use FSPoster\App\Providers\WPPostThumbnail;

class Listener
{
	/**
	 * @param PostData $postData
	 *
	 * @return PostData
	 */
	private static function filterPostData( $postData )
	{
		if ( $postData->sendType != 'image' && $postData->sendType != 'video' )
		{
			$pMethod = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'twitter_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

			if ( $pMethod === 2 )
			{
				$thumbnailPath = $postData->getPostThumbnail();

				if ( ! empty( $thumbnailPath ) )
				{
					$postData->setSendType( 'image' )
					         ->setImagesLocale( [ $thumbnailPath ] );
				}
			}
			else if ( $pMethod === 3 )
			{
				$imagesLocale = WPPostThumbnail::getPostGallery( $postData->post, $postData->postType );

				if ( ! empty( $imagesLocale ) )
				{
					$postData->setSendType( 'image' )->setImagesLocale( $imagesLocale );
				}
			}
			else if ( $pMethod === 4 )
			{
				$postData->setSendType( 'custom_message' );
			}
		}

		if ( Helper::getOption( 'twitter_auto_cut_tweets', '1' ) == 1 )
		{
			$message = preg_replace( '/\n{2,}/', "\n\n", $postData->message );
			$message = preg_replace( '/[\t ]+/', ' ', $message );

			$postData->message = TwitterAutoCut::cut( $message, $postData->sendType === 'link' );
		}

		return $postData;
	}

	/**
	 * @param $postData PostData
	 * @return array
	 */
	private static function appMethod( $postData )
	{
		return Twitter::sendPost( $postData->getAppId(), $postData->sendType, $postData->message, $postData->comment, $postData->node[ 'username' ], $postData->link, $postData->imagesLocale, $postData->videoURLLocale, $postData->accessToken, $postData->getAccessTokenSecret(), $postData->getProxy() );
	}

	/**
	 * @param $postData PostData
	 * @return array
	 */
	private static function cookieMethod( $postData )
	{
		$twitterPrivateAPI = new TwitterPrivateAPI( $postData->getOptions(), $postData->getProxy() );

		return $twitterPrivateAPI->sendPost( $postData->sendType, $postData->message, $postData->comment, $postData->node[ 'username' ], $postData->link, $postData->imagesLocale, $postData->videoURLLocale );
	}

	/**
	 * @param $result array
	 * @param $postData PostData
	 *
	 * @return array
	 * */
	public static function sharePost( $result, $postData )
	{
		$postData = self::filterPostData( $postData );

		if ( empty( $postData->getOptions() ) )
		{
			return self::appMethod( $postData );
		}

		return self::cookieMethod( $postData );
	}

	/**
	 * @param $params array
	 *
	 * @return array
	 */
	public static function addApp( $params )
	{
		$appKey    = Request::postMust( 'app_key', 'string', fsp__( 'app_key field is empty!' ) );
		$appSecret = Request::postMust( 'app_secret', 'string', fsp__( 'app_secret field is empty!' ) );

		$check = DB::DB()->get_row( DB::DB()->prepare( 'select true from ' . DB::table( 'apps' ) . ' where app_key=%s and app_secret=%s', [
			$appKey,
			$appSecret
		] ) );

		if ( $check )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The App has already been added.' ) ] );
		}

		return [
			'app_key'    => $appKey,
			'app_secret' => $appSecret,
			'is_public'  => 0,
			'name'       => $appKey
		];
	}


	/**
	 * @param $insights array
	 * @param $feed array
	 * @param $node array
	 *
	 * @return array
	 */
	public static function getInsights( $insights, $feed, $node )
	{
		$proxy = $node[ 'info' ][ 'proxy' ];

		if ( empty( $options ) )
		{
			return Twitter::getStats( $feed[ 'driver_post_id' ], $node[ 'access_token' ], $node[ 'access_token_secret' ], $node[ 'app_id' ], $proxy );
		}

		return ( new TwitterPrivateAPI( $options, $proxy ) )->getStats( $feed[ 'driver_post_id' ] );

	}
}