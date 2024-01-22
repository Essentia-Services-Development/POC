<?php

namespace FSPoster\App\SocialNetworks\facebook\App;

use FSPoster\App\Libraries\fb\Facebook;
use FSPoster\App\Libraries\fb\FacebookCookieApi;
use FSPoster\App\Providers\AccountService;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
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
		if ( $postData->feed[ 'feed_type' ] === 'story' || $postData->sendType === 'image' || $postData->sendType === 'video' )
		{
			return $postData;
		}

        $pMethod   = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'facebook_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );
        $thumbnail = $postData->getPostThumbnailURL();

        if ( $pMethod === 2 )
        {
            if ( ! empty( $thumbnail ) )
            {
                $postData->setSendType( 'image' )
                    ->setImages( [ $thumbnail ] );
            }
        }
        else if ( $pMethod === 3 )
        {
            $images = $postData->getPostGalleryURL();

            if ( ! empty( $images ) )
            {
                $postData->setSendType( 'image' )->setImages( $images );
            }

            $postData->setImages( $images );
        }
        else if ( $pMethod === 4 )
        {
            $postData->setSendType( 'custom_message' );
        }

		return $postData;
	}

	/**
	 * @param $postData PostData
	 * @return array
	*/
	private static function appMethod( $postData )
	{
		if ( $postData->feed[ 'feed_type' ] === 'story' )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Facebook API does not support sharing posts on the story so that accounts have to be added to the plugin via the cookie method to share posts on the story.' )
			];
		}

		if ( ! is_null( $postData->getPosterId() ) )
		{
			$accessToken = DB::DB()->get_row( DB::DB()->prepare( 'SELECT access_token FROM ' . DB::table( 'account_nodes' ) . ' WHERE node_id = %d AND driver = "fb" AND blog_id = %d', [
				$postData->getPosterId(),
				Helper::getBlogId()
			] ), ARRAY_A );

			$postData->setAccessToken( $accessToken[ 'access_token' ] );
		}

		$app = DB::fetch( 'apps', [ 'id' => $postData->getAppId(), 'driver' => 'fb' ] );

		$fb  = new Facebook( $app, $postData->accessToken, $postData->getProxy() );

		return $fb->sendPost( $postData->getNodeProfileId(), $postData->sendType, $postData->message, $postData->link, $postData->images, $postData->videoURL, $postData->getPosterId() );
	}

	/**
	 * @param $postData PostData
	 * @return array
	 */
	private static function cookieMethod( $postData )
	{
		// Cookie method
		$nodeInfoData = isset( $postData->node[ 'info' ][ 'data' ] ) ? json_decode( $postData->node[ 'info' ][ 'data' ], TRUE ) : [];
		$newPageID    = isset( $nodeInfoData[ 'delegate_page_id' ] ) ? $postData->getNodeProfileId() : NULL;

		if ( ! empty( $postData->getPosterId() ) )
		{
			$fbGroupPoster = DB::fetch( 'account_nodes', [
				'node_id' => $postData->getPosterId(),
				'driver'  => $postData->getDriver()
			] );

			if ( ! empty( $fbGroupPoster[ 'data' ] ) )
			{
				$fgpData   = json_decode( $fbGroupPoster[ 'data' ], TRUE );
				$newPageID = isset( $fgpData[ 'delegate_page_id' ] ) ? $postData->getPosterId() : NULL;
			}
		}

		$fbDriver = new FacebookCookieApi( $postData->getAccoundId(), $postData->getOptions(), $postData->getProxy(), $newPageID );

		if ( empty( $fbDriver->getDTSG() ) )
		{
			if ( $postData->nodeType === 'account' )
			{
				$accID = $postData->node[ 'id' ];
			}
			else
			{
				$nodeAccID = DB::fetch( 'account_nodes', [ 'id' => $postData->node[ 'id' ] ] );
				$accID     = $nodeAccID[ 'account_id' ];
			}

			AccountService::disable_account( $accID, fsp__( 'The account is disconnected from the FS Poster plugin. Please add your account to the plugin without deleting the account from the plugin; as a result, account settings will remain as it is.' ) );

			return [
				'status'    => 'error',
				'error_msg' => fsp__( ' The account is disconnected from the FS Poster plugin. Please update your account cookie to connect it to the plugin again. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue8\' target=\'_blank\'>How to?</a>.', [], FALSE )
			];
		}

		if ( $postData->feed[ 'feed_type' ] === 'story' )
		{
			$facebookStoryImageCount = (int) Helper::getOption( 'facebook_story_images_count', 1 );

			if ( $postData->sendType != 'image' ) {
                $postData->setImagesLocale(WPPostThumbnail::getPostGallery($postData->post, $postData->postType));

                if ($facebookStoryImageCount == 1) {
                    $thumbnailPath = $postData->getPostThumbnail();
                    if (!empty($thumbnailPath)) {
                        $postData->setImagesLocale([$thumbnailPath]);
                    }
                }
            }

			if ( empty( $postData->imagesLocale ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Error! An image is required to share a story on Facebook. Please add media to the post.' )
				];
			}

			if ( $postData->nodeType === 'group' )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Sharing stories on groups is not supported.' )
				];
			}
			$res = $fbDriver->sendStory( $postData->getNodeProfileId(), $postData->message, reset( $postData->imagesLocale ), $postData->nodeType, Helper::getOption( 'facebook_story_send_link', '1' ) == '1' ? $postData->link : '' );

			if ( isset( $res[ 'status' ] ) && $res[ 'status' ] == 'ok' )
			{
				$facebookStoryImageCount--;

				while ( $imagePath = next( $postData->imagesLocale ) )
				{
					if ( $facebookStoryImageCount <= 0 )
					{
						break;
					}

					$facebookStoryImageCount--;

					$fbDriver->sendStory( $postData->getNodeProfileId(), '', $imagePath, $postData->nodeType, '' );
				}
			}

			return $res;
		}

		//feed_type is post
		return $fbDriver->sendPost( $postData->getNodeProfileId(), $postData->nodeType, $postData->sendType, $postData->message, $postData->link, $postData->images, $postData->videoURL, $postData->getPosterId() );
	}

	/**
	 * @param $result array
	 * @param $postData PostData
	 *
	 * @return array
	 * */
	public static function sharePost( $result, $postData )
	{
		//todo://it'll be a hook as well...if possible...
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
		$appId     = Request::postMust( 'app_id', 'string', fsp__( 'app_id field is empty!' ) );
		$appSecret = Request::postMust( 'app_secret', 'string', fsp__( 'app_secret field is empty!' ) );
		$version   = Request::postMust( 'version', 'int', fsp__( 'version field is empty!' ), [ 0, 31, 32, 33, 40, 50, 60, 70, 80 ] );

		$check = DB::DB()->get_row( DB::DB()->prepare( 'select true from ' . DB::table( 'apps' ) . ' where app_id=%s and app_secret=%s and version=%d', [
			$appId,
			$appSecret,
			$version
		] ) );

		if ( $check )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The App has already been added.' ) ] );
		}

		$params = [
			'app_id'     => $appId,
			'app_secret' => $appSecret,
			'version'    => $version,
			'is_public'  => 0
		];

		$params[ 'name' ] = ( new Facebook( $params ) )->checkApp( $params[ 'version' ] );

		return $params;
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

		if ( empty( $node[ 'options' ] ) ) //App method
		{
			$app = DB::fetch( 'apps', $node[ 'app_id' ] );

			$fb = new Facebook( $app, $node[ 'access_token' ], $proxy );

			return $fb->getStats( $node['node_id'], $feed[ 'driver_post_id' ] );
		}

		//Cookie method
		$fb = new FacebookCookieApi( $node[ 'account_id' ], $node[ 'options' ], $proxy );

		return $fb->getStats( $feed[ 'driver_post_id' ] );
	}
}