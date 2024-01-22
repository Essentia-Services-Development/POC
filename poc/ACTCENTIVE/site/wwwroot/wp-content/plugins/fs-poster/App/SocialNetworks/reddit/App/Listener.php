<?php

namespace FSPoster\App\SocialNetworks\reddit\App;

use FSPoster\App\Libraries\reddit\Reddit;
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
		$pMethod = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'reddit_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

		if ( $pMethod === 3 && $postData->sendType !== 'image' )
		{
			$thumbnailPath = $postData->getPostThumbnail();

			if ( ! empty( $thumbnailPath ) )
			{
				$postData->setSendType( 'image' )
				         ->setImagesLocale( [ $thumbnailPath ] );
			}
		}
		else if ( $pMethod === 4 && $postData->sendType !== 'image' )
		{
			$postData->setImagesLocale( WPPostThumbnail::getPostGallery( $postData->post, $postData->postType ) );

			if ( ! empty( $postData->imagesLocale ) )
			{
				$postData->setSendType( 'image' );
			}
		}

		$app            = DB::fetch( 'apps', [ 'id' => $postData->getAppId() ] );
		$canSendComment = empty( $app[ 'slug' ] ) && empty( $app[ 'is_standard' ] );

		return Reddit::sendPost( $postData->node[ 'info' ], $postData->node[ 'username' ], $postData->sendType, $postData->getPostTitle(), $postData->message, $postData->comment, $canSendComment, $postData->longLink, $postData->imagesLocale, $postData->videoURL, $postData->accessToken, $postData->getProxy(), $pMethod );
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
		return Reddit::getStats( $feed[ 'driver_post_id' ], $node[ 'access_token' ], $node[ 'info' ][ 'proxy' ] );
	}
}