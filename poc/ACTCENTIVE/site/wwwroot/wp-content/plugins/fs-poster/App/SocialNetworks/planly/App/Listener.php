<?php

namespace FSPoster\App\SocialNetworks\planly\App;

use FSPoster\App\Libraries\planly\Planly;
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
		$media = $postData->images;

		if ( $postData->nodeType === 'tiktok' || $postData->nodeType === 'tiktok_business' )
		{
			if ( $postData->sendType != 'video' )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'You can only share videos to the social network Tiktok!' )
				];
			}

			$media = [ $postData->videoURLLocale ];
		}
		else if ( $postData->sendType !== 'image' )
		{
			$postingType = ( int ) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'planly_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

			if ( $postingType === 1 )
			{
				$thumbURL = $postData->getPostThumbnail();

				if ( ! empty( $thumbURL ) )
				{
					$media = [ $thumbURL ];

					$postData->setSendType( 'image' );
				}
			}
			else if ( $postingType === 2 )
			{
				$postImages = array_slice( $postData->getPostGallery(), 0, 10, TRUE );

				if ( count( $postImages ) > 0 )
				{
					$media = $postImages;

					$postData->setSendType( 'image' );
				}
			}
		}

		if ( empty( $postData->getOptions() ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Authorization token is not present!' )
			];
		}

		$planly = new Planly( $postData->getOptions(), $postData->getProxy() );

		if ( empty( $postData->feed[ 'driver_post_id' ] ) )
		{
			return $planly->post( $postData->message, $media, $postData->node[ 'info' ][ 'access_token' ], $postData->getNodeProfileId(), $postData->nodeType );
		}

		return $planly->repost( $postData->feed[ 'driver_post_id' ] );
	}
}