<?php

namespace FSPoster\App\SocialNetworks\xing\App;

use FSPoster\App\Libraries\xing\Xing;
use FSPoster\App\Providers\Helper;
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
		if ( $postData->sendType === 'video' )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Xing doesn\'t support sharing videos.' )
			];
		}

		$thumbnailPath = '';
		$postingType   = ( int ) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'xing_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

		if ( $postData->sendType === 'image' )
		{
			$thumbnailPath = reset( $postData->imagesLocale );
			$postingType   = 3;
		}
		else if ( $postingType === 3 )
		{
			$thumbnailPath = $postData->getPostThumbnail();
		}

		if ( empty( $thumbnailPath ) && $postingType === 3 )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Featured image is not set!' )
			];
		}

		if ( $postData->node[ 'info' ][ 'node_type' ] === 'group' )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'XING groups are deprecated. Please refetch your accounts.' )
			];
		}

		$xing = new Xing( json_decode( $postData->getOptions(), TRUE ), $postData->getProxy() );

		return $xing->send( $postData->node[ 'info' ], $postingType, $postData->message, $postData->link, $thumbnailPath );
	}
}