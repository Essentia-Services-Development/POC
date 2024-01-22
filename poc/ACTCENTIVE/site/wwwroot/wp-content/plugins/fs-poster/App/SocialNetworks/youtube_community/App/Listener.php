<?php

namespace FSPoster\App\SocialNetworks\youtube_community\App;

use FSPoster\App\Libraries\youtube\YoutubeCommunity;
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
		$cookies = json_decode( $postData->getOptions(), TRUE );

		if ( empty( $cookies ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error!' )
			];
		}

		$media = $postData->images;

		if ( $postData->sendType !== 'image' )
		{
			$postingType = ( int ) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'youtube_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

			if ( $postingType === 1 )
			{
				$postData->setSendType( 'text' );
			}
			else if ( $postingType === 2 )
			{
				$thumbURL = $postData->getPostThumbnailURL();

				if ( ! empty( $thumbURL ) )
				{
					$media = [ $thumbURL ];
				}
			}
			else if ( $postingType === 3 )
			{
				$postImages = array_slice( $postData->getPostGalleryURL(), 0, 5, TRUE );

				if ( count( $postImages ) > 0 )
				{
					$media = $postImages;
				}
			}

			if ( ! empty( $media ) )
			{
				$postData->setSendType( 'image' );
			}
		}

		//todo: status burdan string kimi qayıtsın, ya da digerlerinden de boolean.
		$res = ( new YoutubeCommunity( $cookies, $postData->getProxy() ) )->post( $postData->message, $postData->sendType, $media );

		$res[ 'status' ] = $res[ 'status' ] ? 'ok' : 'error';

		return $res;
	}
}