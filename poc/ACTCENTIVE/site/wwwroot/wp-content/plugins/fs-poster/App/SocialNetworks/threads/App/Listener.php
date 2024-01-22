<?php

namespace FSPoster\App\SocialNetworks\threads\App;

use FSPoster\App\Libraries\threads\Threads;
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
        $thumbnailPath = $postData->getPostThumbnail();

        if ( ! empty( $thumbnailPath ) )
        {
            $postData->setImagesLocale( [ $thumbnailPath ] );
        }

        $pMethod = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'threads_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

        if ( $postData->sendType != 'image' && $postData->sendType != 'video' )
        {
            if( $pMethod === 1 )
            {
                if( !empty( $postData->link ) )
                {
                    $postData->setSendType( 'link' );
                }
            }
            else if ( $pMethod === 3 )
            {
                if( ! empty($postData->imagesLocale) )
                {
                    $postData->setSendType( 'image' );
                }
            }
        }

        $postData->message = (int) Helper::getOption('threads_auto_cut_message', 1) === 1 ? Helper::cutText($postData->message, 497) : $postData->message;
        $threads = new Threads( json_decode( $postData->getOptions(), true ), $postData->getProxy() );

		return $threads->sendPost( $postData->sendType, $postData->message, $postData->link, $postData->imagesLocale );
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
        return [
            'comments' => 0,
            'like'     => 0,
            'shares'   => 0,
            'details'  => 0
        ];
	}
}