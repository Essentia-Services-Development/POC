<?php

namespace FSPoster\App\Pages\Base\Controllers;

use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Ajax
{
	public function save_metabox ()
	{
		$id              = Request::post( 'id', 0, 'int' );
		$share_checked   = Request::post( 'share_checked', 0, 'int', [ 0, 1 ] );
		$instagram_pin   = Request::post( 'instagramPin', 0, 'int', [ 0, 1 ] );
		$accounts        = Request::post( 'accounts', [], 'array' );
		$custom_messages = Request::post( 'custom_messages', [], 'array' );

		if ( ! ( $id > 0 ) )
		{
			Helper::response( 'ok' );
		}

		update_post_meta( $id, '_fs_is_manual_action', 1 );
		update_post_meta( $id, '_fs_poster_share', $share_checked );
		update_post_meta( $id, '_fs_poster_node_list', $accounts );

        if ( ! add_post_meta( $id, '_fs_instagram_pin_the_post', $instagram_pin, TRUE ) )
        {
            update_post_meta( $id, '_fs_instagram_pin_the_post', $instagram_pin );
        }

		foreach ( $custom_messages as $driver => $cm )
		{
			update_post_meta( $id, '_fs_poster_cm_' . $driver, $cm );
		}

		Helper::response( 'ok' );
	}

    public function save_featured_image()
    {
        $postId  = Request::post( 'postId', 0, 'int' );
        $imageId = Request::post( 'imageId', 0, 'int' );

        if( $postId > 0 && $imageId > 0 )
        {
            update_post_meta( $postId, '_fs_featured_image', $imageId );
        }
        else
        {
            delete_post_meta( $postId, '_fs_featured_image' );
        }

        Helper::response( 'ok' );
    }
}