<?php

namespace FSPoster\App\SocialNetworks\telegram\App;

use FSPoster\App\Libraries\telegram\Telegram;
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
		$tgTypeOfSharing = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'telegram_type_of_sharing', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

		if ( ( $tgTypeOfSharing === 1 || $tgTypeOfSharing === 4 ) && ! empty( $postData->link ) )
		{
			$postData->message .= "\n" . $postData->link;
		}

		if ( ( $tgTypeOfSharing === 3 || $tgTypeOfSharing === 4 ) && $postData->sendType != 'image' && $postData->sendType != 'video' )
		{
			$thumbURL = $postData->getPostThumbnail();

			if ( ! empty( $thumbURL ) )
			{
				$postData->setSendType( 'image' )
				         ->setImagesLocale( [ $thumbURL ] );
			}
		}

		$mediaURL = '';

		if ( $postData->sendType == 'image' )
		{
			$mediaURL = reset( $postData->imagesLocale );
		}
		else if ( $postData->sendType == 'video' )
		{
			$mediaURL = $postData->videoURL;
		}

		$tg = new Telegram( $postData->getOptions(), $postData->getProxy() );

		return $tg->sendPost( $postData->getNodeProfileId(), $postData->message, $postData->sendType, $mediaURL, $postData->link );
	}
}