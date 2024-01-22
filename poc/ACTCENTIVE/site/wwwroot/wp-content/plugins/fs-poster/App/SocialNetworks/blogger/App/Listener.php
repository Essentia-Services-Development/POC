<?php

namespace FSPoster\App\SocialNetworks\blogger\App;

use FSPoster\App\Libraries\blogger\Blogger;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\v7\PostData;

class Listener
{
	/**
	 * @param PostData $postData
	 *
	 * @return PostData
	 */
	private static function filterPostMessage( $postData )
	{
		if( $postData->postType == 'fs_post' || $postData->postType == 'fs_post_tmp' )
		{
			$postData->message = str_replace( "\n", '<br>', $postData->message );
		}

		if ( $postData->sendType === 'image' )
		{
			$imgTag = '<img src="' . reset( $postData->images ) . '" width="100%" height="auto">';

			if ( Helper::getOption( 'blogger_share_image_first', '0' ) )
			{
				$postData->message = $imgTag . $postData->message;
			}
			else
			{
				$postData->message .= $imgTag;
			}
		}
		else if ( $postData->sendType === 'video' )
		{
			$postData->message .= '<video width="100%" height="auto" controls><source src="' . $postData->videoURL . '"></video>';
		}
		else
		{
			$postData->message .= "<style>[class^='wp-image']{width:100%;height:auto;}</style>";
		}

		return $postData;
	}

	/**
	 * @param $result array
	 * @param $postData PostData
	 *
	 * @return array
	 * */
	public static function sharePost( $result, $postData )
	{
		$pageAsPage = Helper::getCustomSetting( 'posting_type', Helper::getOption( 'blogger_posting_type', '0' ), $postData->nodeType, $postData->node[ 'id' ] ) == '1';
		$postType   = ( $postData->postType === 'page' && $pageAsPage ) ? 'page' : 'post';
		$isDraft    = Helper::getOption( 'blogger_post_status', 'publish' ) !== 'publish';
		$postData->setPostTitle( Helper::getOption( 'post_title_blogger', "{title}" ) );
		$labels     = [];

		if ( Helper::getOption( 'blogger_post_with_terms', 1 ) == 1 )
		{
			$labels = Helper::getPostTerms( $postData->post, NULL, FALSE, TRUE, ',' );
		}

		$labels_cut = [];

		foreach ( $labels as $label )
		{
			$labels_cut_next   = $labels_cut;
			$labels_cut_next[] = $label;

			if ( strlen( implode( ',', $labels_cut_next ) ) > 200 )
			{
				break;
			}

			$labels_cut[] = $label;
		}

		$postData = self::filterPostMessage( $postData );

		return Blogger::sendPost( $postData->getPostTitle(), $postData->message, $labels_cut, $postType, $isDraft, $postData->getNodeProfileId(), $postData->getAccoundId(), $postData->accessToken, $postData->getProxy() );
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