<?php

namespace FSPoster\App\SocialNetworks\discord\App;

use FSPoster\App\Libraries\discord\Discord;
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
		$media = $postData->images;

		if ( $postData->sendType == 'video' )
		{
			$media = [ $postData->videoURLLocale ];
		}
		else if ( $postData->sendType !== 'image' )
		{
			$postingType = ( int ) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'discord_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

			if ( $postingType === 1 )
			{
				$postData->setSendType( 'link' );
			}
			else if ( $postingType === 2 )
			{
				$postData->setSendType( 'text' );
			}
			else if ( $postingType === 3 )
			{
				$thumb = $postData->getPostThumbnail();

				if ( ! empty( $thumb ) )
				{
					$media = [ $thumb ];
				}
			}
			else if ( $postingType === 4 )
			{
				$postImages = array_slice( $postData->getPostGallery(), 0, 10, TRUE );

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

		$botInfo = json_decode( $postData->getOptions(), TRUE );

		if ( empty( $botInfo ) || empty( $botInfo[ 'bot_token' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Bot token is not present!' )
			];
		}

        $postData->message = trim( $postData->message );

        if(Helper::getOption( 'discord_auto_cut_message', '1' ) == '1')
        {
            if ( $postData->sendType === 'link' && ! empty( $postData->link ) )
            {
                $postData->message = mb_substr( $postData->message, 0, 1997 - mb_strlen( $postData->link, 'utf8' ) - 1, 'utf8' ) . '...';
            }
            else
            {
                $postData->message = mb_substr( $postData->message, 0, 1997, 'utf8' ) . '...';
            }
        }

		$res = Discord::post( $postData->message, $media, $postData->link, $postData->sendType, $postData->getNodeProfileId(), $botInfo[ 'bot_token' ], $postData->getProxy() );

		if ( isset( $res[ 'id' ] ) )
		{
			$res[ 'id2' ] = sprintf( "https://discord.com/channels/%d/%d/%d", $postData->getAccoundId(), $postData->getNodeProfileId(), $res[ 'id' ] );
		}

		return $res;
	}

	/**
	 * @param $params array
	 *
	 * @return array
	 */
	public static function addApp( $params )
	{
		$appId    = Request::postMust( 'app_id', 'string', fsp__( 'app_id field is empty!' ) );
		$botToken = Request::postMust( 'bot_token', 'string', fsp__( 'bot_token field is empty!' ) );
		$data     = json_encode( [ 'bot_token' => $botToken ] );

		$check = DB::DB()->get_row( DB::DB()->prepare( 'select true from ' . DB::table( 'apps' ) . ' where app_id=%s and data=%s', [
			$appId,
			$data
		] ) );

		if ( $check )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The App has already been added.' ) ] );
		}

		return [
			'app_id'    => $appId,
			'data'      => $data,
			'is_public' => 0,
			'name'      => $appId
		];
	}
}