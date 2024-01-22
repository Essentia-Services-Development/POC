<?php

namespace FSPoster\App\Pages\Base\Controllers;

use FSPoster\App\Providers\Pages;

class MetaBox
{
	public function post_meta_box ( $params )
	{
		$post_id            = $params[ 'post_id' ];
		$data               = Pages::action( 'Base', 'get_post_meta_box', $post_id );
		$data[ 'minified' ] = isset( $params[ 'minified_metabox' ] ) && $params[ 'minified_metabox' ] === TRUE;

		if ( isset( $params[ 'active_nodes' ] ) )
		{
			$data[ 'active_nodes' ] = $params[ 'active_nodes' ];
		}

        if( isset( $params[ 'instagram_pin_the_post' ] ) )
        {
            $data[ 'instagramPinThePost' ] = $params[ 'instagram_pin_the_post' ];
        }

		Pages::view( 'Base', 'post_meta_box', $data );
	}

	/*
	 * self note:
	 *
	 * new version of metabox
	 * old one will be removed in future
	 */
	public function post_metabox_v2 ( $params )
	{
		$post_id = $params[ 'post_id' ];

		$data    = Pages::action( 'Base', 'get_post_meta_box', $post_id );

		if ( isset( $params[ 'active_nodes' ] ) )
		{
			$data[ 'active_nodes' ] = $params[ 'active_nodes' ];
		}

        $data[ 'imageID' ]  = get_post_meta( $post_id, '_fs_featured_image', TRUE );
        $data[ 'imageURL' ] = ! empty( $data[ 'imageID' ] ) ? wp_get_attachment_url( $data[ 'imageID' ] ) : '';

		Pages::view( 'Base', 'post_metabox_v2', $data );
	}

	public function post_meta_box_edit ( $params )
	{
		$data               = Pages::action( 'Base', 'get_post_meta_box_edit', $params );
		$data[ 'minified' ] = isset( $params[ 'minified_metabox' ] ) && $params[ 'minified_metabox' ] === TRUE;

		Pages::view( 'Base', 'post_meta_box_edit', $data );
	}
}