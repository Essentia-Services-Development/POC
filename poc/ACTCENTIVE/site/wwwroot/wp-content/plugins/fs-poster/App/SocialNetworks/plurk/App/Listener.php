<?php

namespace FSPoster\App\SocialNetworks\plurk\App;

use FSPoster\App\Libraries\plurk\Plurk;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
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
		if ( $postData->sendType != 'image' && $postData->sendType != 'video' )
		{
			$pMethod = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'plurk_posting_type', '2' ), $postData->nodeType, $postData->node[ 'id' ] );

			if ( $pMethod === 1 )
			{
				$postData->setSendType( 'custom_message' );
			}
			else if ( $pMethod === 2 )
			{
				$postData->setSendType( 'link' );
			}
			else if ( $pMethod == 3 )
			{
				$thumbnail = $postData->getPostThumbnailURL();

				if ( ! empty( $thumbnail ) )
				{
					$postData->setSendType( 'image' )
					         ->setImages( [ $thumbnail ] );
				}
			}
			else if ( $pMethod == 4 )
			{
				$images = $postData->getPostGalleryURL();

				if ( ! empty( $images ) )
				{
					$postData->setSendType( 'image' );
				}
			}
		}

		$autoCut = Helper::getOption( 'fs_plurk_auto_cut_plurks' );
		$app     = DB::fetch( 'apps', [ 'id' => $postData->getAppId(), 'driver' => $postData->getDriver() ] );

		if ( ! $app )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! There isn\'t a Plurk App!' )
			];
		}

		$qualifier = (string) Helper::getOption( 'plurk_qualifier', ':' );

		$plurk = new Plurk( $app[ 'app_key' ], $app[ 'app_secret' ], $postData->getProxy() );

		return $plurk->sendPost( $postData->accessToken, $postData->getAccessTokenSecret(), $postData->sendType, $postData->message, $qualifier, $autoCut, $postData->link, $postData->images );

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
			'app_id'    => $appKey,
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
		$app   = DB::fetch( 'apps', $node[ 'app_id' ] );
		$plurk = new Plurk( $app[ 'app_key' ], $app[ 'app_secret' ], $node[ 'info' ][ 'proxy' ] );

		return $plurk->getStats( $node[ 'access_token' ], $node[ 'access_token_secret' ], $feed[ 'driver_post_id' ] );
	}
}