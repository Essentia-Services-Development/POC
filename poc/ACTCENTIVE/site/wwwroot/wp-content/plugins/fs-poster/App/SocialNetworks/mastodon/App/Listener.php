<?php

namespace FSPoster\App\SocialNetworks\mastodon\App;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\v7\PostData;
use FSPoster\App\Providers\WPPostThumbnail;
use FSPoster\App\Libraries\mastodon\Mastodon;

class Listener
{
	/**
	 * @param PostData $postData
	 *
	 * @return PostData
	 */
	private static function filterPostData ( $postData )
	{
		$pMethod = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'mastodon_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

		if ( $postData->sendType != 'image' && $postData->sendType != 'video' )
		{
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
					$postData->setSendType( 'image' )->setImagesLocale($imagesLocale);
				}
			}
			else if ( $pMethod === 4 )
			{
				$postData->setSendType( 'custom_message' );
			}
		}

		$cut = 497;

		if ( ( $pMethod === 1 || $postData->sendType === 'link' ) && ! empty( $postData->link ) )
		{
			$cut = $cut - mb_strlen( $postData->link ) - 1;
		}

		$postData->message = Helper::cutText( $postData->message, $cut );

		return $postData;
	}

	/**
	 * @param $result array
	 * @param $postData PostData
	 *
	 * @return array
	 * */
	public static function sharePost ( $result, $postData )
	{
		$postData = self::filterPostData( $postData );

		$app      = DB::fetch( 'apps', [ 'id' => $postData->getAppId(), 'driver' => 'mastodon' ] );
		$mastodon = new Mastodon( $app, $postData->accessToken, $postData->getProxy() );

		return $mastodon->sendPost( $postData->sendType, $postData->message, $postData->link, $postData->imagesLocale, $postData->videoURLLocale );
	}

	/**
	 * @param $params array
	 *
	 * @return array
	 */
	public static function addApp ( $params )
	{
		$server    = Request::postMust( 'server', 'string', 'Mastodon server field is empty' );
		$appKey    = Request::postMust( 'app_key', 'string', fsp__( 'app_key field is empty!' ) );
		$appSecret = Request::postMust( 'app_secret', 'string', fsp__( 'app_secret field is empty!' ) );

		if ( ! filter_var( $server, FILTER_VALIDATE_URL ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The server must be a valid URL.' ) ] );
		}

		$check = DB::DB()->get_row( DB::DB()->prepare( 'select true from ' . DB::table( 'apps' ) . ' where app_key=%s and app_secret=%s', [
			$appKey,
			$appSecret
		] ) );

		if ( $check )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The App has already been added.' ) ] );
		}

		$server = trim( $server, '/' );

		return [
			'app_key'    => $appKey,
			'app_secret' => $appSecret,
			'is_public'  => 0,
			'name'       => $appKey,
			'data'       => json_encode( [ 'server' => $server ] ),
		];
	}

	/**
	 * @param $insights array
	 * @param $feed array
	 * @param $node array
	 *
	 * @return array
	 */
	public static function getInsights ( $insights, $feed, $node )
	{
		//todo
		return [];
	}
}