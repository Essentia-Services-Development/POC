<?php

namespace FSPoster\App\SocialNetworks\pinterest\App;

use FSPoster\App\Libraries\pinterest\Pinterest;
use FSPoster\App\Libraries\pinterest\PinterestCookieApi;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\v7\PostData;
use FSPoster\App\Providers\WPPostThumbnail;

class Listener
{
	/**
	 * @param $postData PostData
	 * @param $altText string
	 * @param $imageCount int
	 *
	 * @return array
	 */
	private static function appMethod( $postData, $altText, $imageCount )
	{
		//todo: bu line-i bu if-in en sonuna atmaq isteyirem - bir-basha return etmek ucun, cunki $res hec yerde ishlenmir, amma emin ola bilmedim.
		$res = Pinterest::sendPost( $postData->getNodeProfileId(), $postData->sendType, $postData->getPostTitle(), $postData->message, $postData->longLink, $altText, [ reset( $postData->imagesLocale ) ], $postData->accessToken, $postData->getProxy() );
		$imageCount--;

		while ( $pinImg = next( $postData->imagesLocale ) )
		{
			if ( $imageCount <= 0 )
			{
				break;
			}

			$imageCount--;

			Pinterest::sendPost( $postData->getNodeProfileId(), $postData->sendType, $postData->getPostTitle(), $postData->message, $postData->longLink, $altText, [ $pinImg ], $postData->accessToken, $postData->getProxy() );
		}

		return $res;
	}

	/**
	 * @param $postData PostData
	 * @param $altText string
	 * @param $imageCount int
	 *
	 * @return array
	 */
	private static function cookieMethod( PostData $postData, $altText, $imageCount )
	{
		$getCookie = DB::fetch( 'account_sessions', [
			'driver'   => $postData->getDriver(),
			'username' => $postData->node[ 'username' ]
		] );

		$pinterest = new PinterestCookieApi( $getCookie[ 'cookies' ], $postData->getProxy() );

		$res = $pinterest->sendPost( $postData->getNodeProfileId(), $postData->getPostTitle(), $postData->message, $postData->longLink, [ reset( $postData->imagesLocale ) ], $altText );
		$imageCount--;

		while ( $pinImg = next( $postData->imagesLocale ) )
		{
			if ( empty( $imageCount ) )
			{
				break;
			}

			$imageCount--;

			$pinterest->sendPost( $postData->getNodeProfileId(), $postData->getPostTitle(), $postData->message, $postData->longLink, [ $pinImg ], $altText );
		}

		return $res;
	}

	/**
	 * @param $result array
	 * @param $postData PostData
	 *
	 * @return array
	 * */
	public static function sharePost( $result, $postData )
	{
		$altText = Helper::getOption( 'alt_text_pinterest', '' );

		if ( ! empty( $altText ) )
		{
			$altText = Helper::replaceAltTextTags( $altText, $postData->post );
			$altText = Helper::cutText( strip_tags( $altText ), 497 );
		}

        $postData->setPostTitle( Helper::getOption( 'post_title_pinterest', "{title}" ) );

        if ( Helper::getOption( 'pinterest_autocut_title', '1' ) == 1 && mb_strlen( $postData->getPostTitle() ) > 100 )
		{
			$postData->post[ 'post_title' ] = mb_substr( $postData->getPostTitle(), 0, 97 ) . '...';
		}

		$imageCount = (int) Helper::getOption( 'pinterest_send_images_count', 1 );

		if ( $postData->sendType != 'image' )
		{
			if ( $imageCount == 1 )
			{
				$thumbURL = $postData->getPostThumbnail();

				if ( ! empty( $thumbURL ) )
				{
					$postData->setSendType( 'image' )
					         ->setImagesLocale( [ $thumbURL ] );
				}
			}
			else
			{
				$postData->setImagesLocale( WPPostThumbnail::getPostGallery( $postData->post, $postData->postType ) );

				if ( ! empty( $postData->imagesLocale ) )
				{
					$postData->setSendType( 'image' );
				}
			}
		}

		if ( empty( $postData->imagesLocale ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'An image is required to pin on board!' )
			];
		}

		if ( empty( $postData->getOptions() ) ) // App method
		{
			return self::appMethod( $postData, $altText, $imageCount );
		}

		return self::cookieMethod( $postData, $altText, $imageCount );
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
		return Pinterest::getStats( $feed[ 'driver_post_id' ], $node[ 'access_token' ], $node[ 'info' ][ 'proxy' ] );
	}
}