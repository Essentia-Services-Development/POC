<?php

namespace FSPoster\App\SocialNetworks\wordpress\App;

use FSPoster\App\Libraries\wordpress\Wordpress;
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
		if ( ! $postData->isDriver( 'wordpress' ) )
			return $result;

		$thumbnailPath = $postData->getPostThumbnail();

		$wpPostTitle   = Helper::getOption( 'post_title_wordpress', "{title}" );
		$wpPostExcerpt = Helper::getOption( 'post_excerpt_wordpress', "{excerpt}" );

		$wpPostTitle   = Helper::replaceTags( $wpPostTitle, $postData->post, $postData->longLink, $postData->shortLink );
		$wpPostExcerpt = Helper::replaceTags( $wpPostExcerpt, $postData->post, $postData->longLink, $postData->shortLink );

		$postData->node[ 'password' ] = substr( $postData->node[ 'password' ], 0, 9 ) === '(-F-S-P-)' ? explode( '(-F-S-P-)', base64_decode( str_rot13( substr( $postData->node[ 'password' ], 9 ) ) ) )[ 0 ] : $postData->node[ 'password' ];

		$wordpress = new Wordpress( $postData->getOptions(), $postData->node[ 'username' ], $postData->node[ 'password' ], $postData->getProxy() );

		return $wordpress->sendPost( $postData->post, $postData->postType, $wpPostTitle, $wpPostExcerpt, $postData->message, $postData->feed, $thumbnailPath );

	}
}