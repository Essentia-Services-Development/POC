<?php

namespace FSPoster\App\SocialNetworks\medium\App;

use FSPoster\App\Libraries\medium\Medium;
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
		$tags = [];

		if ( Helper::getOption( 'medium_send_tags', '0' ) == '1' )
		{
			$tags = Helper::getPostTerms( $postData->post, NULL, FALSE );
		}

		$postData->message = str_replace( "\n", "<br>", $postData->message );
		$postData->message = preg_replace( '/(<noscript>.*?<\/noscript>)/s', '', $postData->message );

		if ( $postData->sendType === 'image' && ( $postData->postType == 'fs_post' || $postData->postType == 'fs_post_tmp' ) )
		{
			$imgTag = '';

			foreach ($postData->images as $image){
				$imgTag .= '<img src="' . $image . '" width="100%" height="auto">';
			}

			$postData->message .= $imgTag;
		}

		return Medium::sendPost( $postData->node[ 'info' ], $postData->getPostTitle(), $postData->message, $postData->accessToken, $postData->getProxy(), $tags );
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
}