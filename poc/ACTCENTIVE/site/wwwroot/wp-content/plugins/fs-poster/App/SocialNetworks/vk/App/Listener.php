<?php

namespace FSPoster\App\SocialNetworks\vk\App;

use FSPoster\App\Libraries\vk\Vk;
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
		if ( Helper::getOption( 'vk_upload_image', '1' ) == 1 && $postData->sendType != 'image' && $postData->sendType != 'video' )
		{
			$thumbnailPath = $postData->getPostThumbnail();

			if ( ! empty( $thumbnailPath ) )
			{
				$postData->setSendType( 'image_link' )
				         ->setImagesLocale( [ $thumbnailPath ] );
			}

			$pMethod = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'vk_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

			if ( $pMethod === 2 )
			{
				if ( ! empty( $thumbnailPath ) )
				{
					$postData->setSendType( 'image' );
				}
			}
			else if ( $pMethod === 3 )
			{
				$images = $postData->getPostGalleryURL();

				if ( ! empty( $images ) )
				{
					$postData->setSendType( 'image' )
					         ->setImagesLocale( $images );
				}
			}
			else if ( $pMethod === 4 && ! empty( $message ) )
			{
				$postData->setSendType( 'text' );
			}
		}

		return Vk::sendPost( $postData->getNodeProfileId(), $postData->sendType, $postData->message, $postData->link, $postData->imagesLocale, $postData->videoURLLocale, $postData->accessToken, $postData->getProxy() );
	}


	/**
	 * @param $params array
	 *
	 * @return array
	 */
	public static function addApp( $params )
	{
		$appId     = Request::postMust( 'app_id', 'string', fsp__( 'app_id field is empty!' ) );
		$appSecret = Request::postMust( 'app_secret', 'string', fsp__( 'app_secret field is empty!' ) );

		$check = DB::DB()->get_row( DB::DB()->prepare( 'select true from ' . DB::table( 'apps' ) . ' where app_id=%s and app_secret=%s', [
			$appId,
			$appSecret
		] ) );

		if ( $check )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The App has already been added.' ) ] );
		}

		return [
			'app_id'    => $appId,
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
		return Vk::getStats( $feed[ 'driver_post_id' ], $node[ 'access_token' ], $node[ 'info' ][ 'proxy' ] );
	}
}