<?php

namespace FSPoster\App\Pages\Share\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Request;

class Action
{
	public function get_share ()
	{
		$post_id = (int) Request::get( 'post_id', '0', 'int' );
		$postInf = FALSE;

		if ( $post_id > 0 )
		{
			DB::DB()->query( "DELETE FROM " . DB::WPtable( 'posts', TRUE ) . " WHERE post_type='fs_post_tmp' AND id<>'{$post_id}' AND CAST(post_date AS DATE)<CAST(NOW() AS DATE)" );
			$postInf = get_post( $post_id, ARRAY_A );
		}

		if ( ! $postInf || ! in_array( $postInf[ 'post_type' ], [
				'fs_post',
				'fs_post_tmp'
			] ) || $postInf[ 'post_author' ] != get_current_user_id() )
		{
			$post_id  = 0;
			$link     = '';
			$imageURL = '';
			$imageId  = '';
			$message  = [
				'default'           => '',
				'fb'                => '',
				'instagram'         => '',
				'threads'           => '',
				'twitter'           => '',
				'planly'            => '',
				'linkedin'          => '',
				'pinterest'         => '',
				'telegram'          => '',
				'reddit'            => '',
				'youtube_community' => '',
				'tumblr'            => '',
				'ok'                => '',
				'vk'                => '',
				'google_b'          => '',
				'medium'            => '',
				'wordpress'         => '',
				'blogger'           => '',
				'plurk'             => '',
				'xing'              => '',
				'discord'           => '',
				'mastodon'          => '',
			];
		}
		else
		{
			$link     = get_post_meta( $post_id, '_fs_link', TRUE );
			$images   = [];

			$postGallery = get_post_meta($post_id, '_fs_post_type_gallery', TRUE);
			$postGallery = empty($postGallery) ? [(int) get_post_thumbnail_id( $post_id )] : explode(',', $postGallery);

			foreach ($postGallery as $imageId){
				$imageURL = wp_get_attachment_url( $imageId );

				if(!empty($imageId) && !empty($imageURL)){
					$images[] = [
						'id' => $imageId,
						'url' => $imageURL
					];
				}
			}

			$message  = json_decode( $postInf[ 'post_content' ], TRUE );

			if ( is_null( $message ) )
			{
				$message = [
					'default'           => $postInf[ 'post_content' ],
					'fb'                => '',
					'instagram'         => '',
					'threads'           => '',
					'twitter'           => '',
					'planly'            => '',
					'linkedin'          => '',
					'pinterest'         => '',
					'telegram'          => '',
					'reddit'            => '',
					'youtube_community' => '',
					'tumblr'            => '',
					'ok'                => '',
					'vk'                => '',
					'google_b'          => '',
					'medium'            => '',
					'wordpress'         => '',
					'blogger'           => '',
					'plurk'             => '',
					'xing'              => '',
					'discord'           => '',
					'mastodon'          => '',
				];
			}
		}

		$currentUserId = get_current_user_id();

		$posts = DB::DB()->get_results( 'SELECT * FROM ' . DB::WPtable( 'posts', TRUE ) . " WHERE post_type='fs_post' AND post_author='" . $currentUserId . "' ORDER BY ID DESC", ARRAY_A );

		$title = ($post_id > 0) ? get_the_title( $post_id ) : '';

        $instagramPinThePost = get_post_meta( $post_id, '_fs_instagram_pin_the_post', TRUE );
        $instagramPinThePost = empty( $post_id ) || empty( $instagramPinThePost ) ? 0 : 1;

		return [
			'title'               => $title,
			'message'             => $message,
			'link'                => $link,
			'imageURL'            => $imageURL,
			'post_id'             => $post_id,
			'images'              => $images,
			'posts'               => $posts,
            'instagramPinThePost' => $instagramPinThePost,
			'sn_list'  => [
				'default',
				'fb',
				'instagram',
				'threads',
				'twitter',
				'planly',
				'linkedin',
				'pinterest',
				'telegram',
				'reddit',
				'youtube_community',
				'tumblr',
				'ok',
				'vk',
				'google_b',
				'medium',
				'wordpress',
				'blogger',
				'plurk',
				'xing',
				'discord',
				'mastodon',
			]
		];
	}
}