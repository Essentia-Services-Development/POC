<?php

namespace FSPoster\App\SocialNetworks\ok\App;

use FSPoster\App\Libraries\ok\OdnoKlassniki;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\v7\PostData;
use FSPoster\App\Providers\WPPostThumbnail;

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
		if ( $postData->sendType != 'image' && $postData->sendType != 'video' )
		{
			$pMethod = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'ok_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

			if ( $pMethod === 2 )
			{
				$thumbnailPath = $postData->getPostThumbnail();

				if ( ! empty( $thumbnailPath ) )
				{
					$postData->setSendType( 'image' )
					         ->setImagesLocale( [ $thumbnailPath] );
				}
			}
			else if ( $pMethod === 3 )
			{
				$postData->setImagesLocale( WPPostThumbnail::getPostGallery( $postData->post, $postData->postType ) );

				if ( ! empty( $postData->imagesLocale ) )
				{
					$postData->setSendType( 'image' );
				}
			}
			else if ( $pMethod === 4 )
			{
				$postData->setSendType( 'custom_message' );
			}
		}

		$app = DB::fetch( 'apps', [ 'id' => $postData->getAppId() ] );

		return OdnoKlassniki::sendPost( $postData->node[ 'info' ], $postData->sendType, $postData->message, $postData->link, $postData->imagesLocale, $postData->videoURLLocale, $postData->accessToken, $app[ 'app_key' ], $app[ 'app_secret' ], $postData->getProxy() );
	}

	/**
	 * @param $params array
	 *
	 * @return array
	 */
	public static function addApp( $params )
	{
		$appId     = Request::postMust( 'app_id', 'string', fsp__( 'app_id field is empty!' ) );
		$appKey    = Request::postMust( 'app_key', 'string', fsp__( 'app_key field is empty!' ) );
		$appSecret = Request::postMust( 'app_secret', 'string', fsp__( 'app_secret field is empty!' ) );

		$check = DB::DB()->get_row( DB::DB()->prepare( 'select true from ' . DB::table( 'apps' ) . ' where app_id=%s and app_key=%s and app_secret=%s', [
			$appId,
			$appKey,
			$appSecret
		] ) );

		if ( $check )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The App has already been added.' ) ] );
		}

		return [
			'app_id'     => $appId,
			'app_key'    => $appKey,
			'app_secret' => $appSecret,
			'is_public'  => 0,
			'name'       => $appId
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
		$app = DB::fetch( 'apps', $node[ 'app_id' ] );

		$postId = explode( '/', $feed[ 'driver_post_id' ] );
		$postId = end( $postId );

		return OdnoKlassniki::getStats( $postId, $node[ 'access_token' ], $app[ 'app_key' ], $app[ 'app_secret' ], $node[ 'info' ][ 'proxy' ] );
	}
}