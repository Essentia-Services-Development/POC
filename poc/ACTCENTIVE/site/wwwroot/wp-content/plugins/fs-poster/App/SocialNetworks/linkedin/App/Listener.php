<?php

namespace FSPoster\App\SocialNetworks\linkedin\App;

use FSPoster\App\Libraries\linkedin\Linkedin;
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
		if ( $postData->postType == 'attachment' && strpos( $postData->post[ 'post_mime_type' ], 'image' ) !== FALSE )
		{
			$postData->setSendType( 'image' )
			         ->setImagesLocale( [ get_attached_file( $postData->post[ 'ID' ] ) ] );
		}
		else if ( $postData->postType == 'attachment' && strpos( $postData->post[ 'post_mime_type' ], 'video' ) !== FALSE )
		{
			$postData->setSendType( 'video' );

			$postData->videoURL = get_attached_file( $postData->post[ 'ID' ] );
		}

		if ( $postData->sendType != 'image' && $postData->sendType != 'video' )
		{
			$pMethod = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'linkedin_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

			if ( $pMethod === 1 )
			{
				$thumbnailPath = $postData->getPostThumbnail();

				if ( ! empty( $thumbnailPath ) )
				{
					$postData->setImagesLocale( [ $thumbnailPath ] );
				}
			}
			else if ( $pMethod === 2 )
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
				$imagesLocale = $postData->getPostGallery();

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

		return Linkedin::sendPost( $postData->node[ 'info' ], $postData->sendType, $postData->message, $postData->getPostTitle(), $postData->link, $postData->imagesLocale, $postData->videoURL, $postData->accessToken, $postData->getProxy() );
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
		return Linkedin::getStats( NULL, $node[ 'info' ][ 'proxy' ] );
	}
}