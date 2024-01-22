<?php

namespace FSPoster\App\SocialNetworks\instagram\App;

use Exception;
use FSPoster\App\Libraries\instagram\InstagramApi;
use FSPoster\App\Libraries\instagram\InstagramAppMethod;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\v7\PostData;
use FSPoster\App\Providers\WPPostThumbnail;

class Listener
{
	/**
	 * @param $postData PostData
	 * @param $storyImageCount int
	 *
	 * @return array
	 * */
	private static function createStory( $postData, $storyImageCount )
	{
		try
		{
			$res = InstagramApi::sendStory( $postData->node[ 'info' ], $postData->sendType, $postData->message, $postData->link, [ reset( $postData->imagesLocale ) ], $postData->videoURLLocale );
		}
		catch ( Exception $e )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! %s', [ $e->getMessage() ] )
			];
		}

		if ( $res[ 'status' ] != 'error' && $postData->sendType == 'image' )
		{

			$storyImageCount--;

			while ( $storyImg = next( $postData->imagesLocale ) )
			{
				if ( $storyImageCount <= 0 )
				{
					break;
				}

				$storyImageCount--;

				try
				{
					InstagramApi::sendStory( $postData->node[ 'info' ], $postData->sendType, '', '', [ $storyImg ], NULL );
				}
				catch ( Exception $e )
				{
				}
			}
		}

		return $res;
	}

	/**
	 * @param $postData PostData
	 *
	 * @return array
	 * */
	private static function createPost( $postData )
	{
		try
		{
			$feedData   = $postData->getFeedData();
			$pinThePost = empty( $feedData[ 'instagram_pin_the_post' ] ) ? 0 : $feedData[ 'instagram_pin_the_post' ];

			return InstagramApi::sendPost( $postData->node[ 'info' ], $postData->sendType, $postData->message, $postData->comment, $postData->link, $postData->imagesLocale, $postData->videoURLLocale, $postData->videoURL, $pinThePost );
		}
		catch ( Exception $e )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! %s', [ $e->getMessage() ] )
			];
		}
	}

	/**
	 * @param $result array
	 * @param $postData PostData
	 *
	 * @return array
	 * */
	public static function sharePost( $result, $postData )
	{
		$pType           = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'instagram_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );
		$storyImageCount = (int) Helper::getOption( 'instagram_story_images_count', 1 );

		if ( $postData->sendType != 'image' && $postData->sendType != 'video' )
		{
			if ( $pType === 2 || ( $postData->feed[ 'feed_type' ] == 'story' && $storyImageCount == 1 ) )
			{
				$postData->setImagesLocale( WPPostThumbnail::getPostGallery( $postData->post, $postData->postType ) )
				         ->setImages( $postData->getPostGalleryURL() );
			}
			else
			{
				$thumbPath = $postData->getPostThumbnail();
				$thumbURL  = $postData->getPostThumbnailURL();

				if ( ! empty( $thumbPath ) )
				{
					$postData->setImagesLocale( [ $thumbPath ] );
				}

				if ( ! empty( $thumbURL ) )
				{
					$postData->setImages( [ $thumbURL ] );
				}
			}

			$postData->setSendType( 'image' );
		}

		if ( ! $postData->hasMedia() )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! An image or video is required to share a post on Instagram. Please add media to the post.' )
			];
		}

		if ( $postData->feed[ 'feed_type' ] == 'story' )
		{
			return self::createStory( $postData, $storyImageCount );
		}

		if ( $pType === 2 && is_array( $postData->imagesLocale ) )
		{
			$postImages = array_slice( $postData->imagesLocale, 0, 10, TRUE );

			if ( count( $postImages ) > 1 )
			{
				$postData->setSendType( 'carousel' )
				         ->setImagesLocale( $postImages );
			}
		}

		return self::createPost( $postData );
	}

	/**
	 * @param $params array
	 *
	 * @return array
	*/
	public static function addApp( $params )
	{
		$appId  = Request::postMust( 'app_id', 'string', fsp__( 'app_id field is empty!' ) );
		$appKey = Request::postMust( 'app_key', 'string', fsp__( 'app_key field is empty!' ) );

		$check = DB::DB()->get_row( DB::DB()->prepare( 'select true from ' . DB::table( 'apps' ) . ' where app_id=%s and app_key=%s', [
			$appId,
			$appKey
		] ) );

		if ( $check )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The App has already been added.' ) ] );
		}

		return [
			'app_id'    => $appId,
			'app_key'   => $appKey,
			'is_public' => 0,
			'name'      => InstagramAppMethod::checkApp( $appId, $appKey )
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
		return InstagramApi::getStats( $feed[ 'driver_post_id2' ], $feed[ 'driver_post_id' ], $node[ 'info' ] );
	}
}