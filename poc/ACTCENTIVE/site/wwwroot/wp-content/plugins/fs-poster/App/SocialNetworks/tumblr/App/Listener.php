<?php

namespace FSPoster\App\SocialNetworks\tumblr\App;

use FSPoster\App\Libraries\tumblr\Tumblr;
use FSPoster\App\Libraries\tumblr\TumblrLoginPassMethod;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\TwitterAutoCut;
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
		$postTitleTumblr = Helper::getOption( 'post_title_tumblr', "" );
		$postTitleTumblr = Helper::replaceTags( $postTitleTumblr, $postData->post, $postData->longLink, $postData->shortLink );

		$thumbnail = $postData->getPostThumbnailURL();

		if ( $postData->sendType != 'image' && $postData->sendType != 'video' )
		{
			$pMethod = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'tumblr_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

			if ( ! empty( $postData->message ) )
			{
				$postData->message = strip_tags( $postData->message, '<b><u><i><a>' );
			}

			if ( $postData->sendType == 'custom_message' )
			{
				$postData->setSendType( 'text' );
			}
			else if ( $pMethod === 2 )
			{
				if ( ! empty( $thumbnail ) )
				{
					$postData->setSendType( 'image' )
					         ->setImages( [ $thumbnail ] );
				}
			}
			else if ( $pMethod === 3 )
			{
				$postData->setImages( $postData->getPostGalleryURL() );

				if ( ! empty( $postData->images ) )
				{
					$postData->setSendType( 'image' );
				}
			}
			else if ( ! empty( $postData->message ) )
			{
				if ( $pMethod === 4 )
				{
					$postData->setSendType( 'text' );
				}
				else if ( $pMethod === 5 )
				{
					$postData->setSendType( 'quote' );
				}
			}
		}

		$excerpt = '';

		if ( $postData->sendType == 'link' )
		{
			$excerpt = $postData->postType == 'fs_post' || $postData->postType == 'fs_post_tmp' ? $postData->message : strip_tags( get_the_excerpt( $postData->postId ) );

			$excerpt = preg_replace( '/\n{2,}/', "\n\n", $excerpt );
			$excerpt = preg_replace( '/[\t ]+/', ' ', $excerpt );

			$excerpt = empty( $excerpt ) ? TwitterAutoCut::cut( $postData->message, FALSE ) : $excerpt;
		}

		$tags = '';

		if ( Helper::getOption( 'tumblr_send_tags', '0' ) == '1' )
		{
			$tags = implode( ',', Helper::getPostTerms( $postData->post, NULL, FALSE ) );
		}

		//app method
		if ( empty( $postData->node[ 'password' ] ) )
		{
			return Tumblr::sendPost( $postData->node[ 'info' ], $postData->sendType, $postTitleTumblr, $postData->message, $postData->link, $postData->images, $postData->videoURLLocale, $postData->accessToken, $postData->getAccessTokenSecret(), $postData->getAppId(), $postData->getProxy(), $tags, $thumbnail, $excerpt );
		}

		//login pass method
		$tumblr = new TumblrLoginPassMethod( $postData->node[ 'email' ], $postData->node[ 'password' ], $postData->getProxy() );

		return $tumblr->sendPost( $postData->node[ 'info' ][ 'screen_name' ], $postData->sendType, $postTitleTumblr, $postData->message, $postData->link, $tags, $postData->images, $postData->videoURLLocale, $thumbnail, $excerpt );
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
}