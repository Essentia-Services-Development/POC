<?php

namespace FSPoster\App\Providers;

class WPPostThumbnail
{
	/**
	 * @var array
	 */
	private static $saveCacheFiles = [];

	/**
	 * @param $post_id
	 *
	 * @return false|string
	 */
	public static function getPostThumbnailURL ( $post_id, $driver = '' )
	{
		$thumbnail_source = 1;

		if ( $thumbnail_source == '2' )
		{
			$cf = Helper::getOption( $driver . '_thumbnail_from_cf' );

			return str_replace( ' ', '%20', get_post_meta( $post_id, $cf, TRUE ) );
		}
		else if ( $thumbnail_source == '3' )
		{
			return str_replace( ' ', '%20', Helper::getOption( $driver . '_thumbnail_from_gallery_url' ) );
		}
		else
		{
			$mediaId = get_post_meta( $post_id, '_fs_featured_image', TRUE );

			if ( empty( $mediaId ) )
			{
				$mediaId = get_post_thumbnail_id( $post_id );
			}

			if ( empty( $mediaId ) )
			{
				$media = get_attached_media( 'image', $post_id );
				$first = reset( $media );

				$mediaId = isset( $first->ID ) ? $first->ID : 0;
			}

			$url = $mediaId > 0 ? wp_get_attachment_url( $mediaId ) : '';

			return empty( $url ) ? FALSE : str_replace( ' ', '%20', $url );
		}
	}

	/**
	 * @param $post_id
	 *
	 * @return false|string
	 */
	public static function getPostThumbnail ( $post_id, $driver = '' )
	{
		$thumbnail_source = 1;

		if ( $thumbnail_source == '2' )
		{
			$cf        = Helper::getOption( $driver . '_thumbnail_from_cf' );
			$imagePath = get_post_meta( $post_id, $cf, TRUE );
		}
		else if ( $thumbnail_source == '3' )
		{
			$imagePath = Helper::getOption( $driver . '_thumbnail_from_gallery_url' );
		}
		else
		{
			$mediaId = get_post_meta( $post_id, '_fs_featured_image', TRUE );

			if ( empty( $mediaId ) )
			{
				$mediaId = get_post_thumbnail_id( $post_id );
			}

			if ( empty( $mediaId ) )
			{
				$media   = get_attached_media( 'image', $post_id );
				$first   = reset( $media );
				$mediaId = isset( $first->ID ) ? $first->ID : 0;
			}

			$imagePath = self::getOrCreateImagePath( $mediaId );
		}

		return empty( $imagePath ) ? FALSE : $imagePath;
	}

	/**
	 * @param $post_id
	 *
	 * @return array
	 */
	public static function getPostGalleryURL ( $post, $postType )
	{
		$images  = [];
		$post_id = $post[ 'ID' ];

		$mediaId = get_post_meta( $post_id, '_fs_featured_image', TRUE );

		$ignoredFeaturedImage = 0;

		if ( empty( $mediaId ) )
		{
			$mediaId = get_post_thumbnail_id( $post_id );
		}
		else
		{
			$ignoredFeaturedImage = get_post_thumbnail_id( $post_id );
		}

		if ( $mediaId > 0 )
		{
			$images[] = wp_get_attachment_url( $mediaId );
		}

		if ( ( $postType === 'product' || $postType === 'product_variation' ) && function_exists( 'wc_get_product' ) )
		{
			$product        = wc_get_product( $post_id );
			$attachment_ids = $product->get_gallery_image_ids();

			foreach ( $attachment_ids as $attachmentId )
			{
				if ( $attachmentId == $ignoredFeaturedImage )
				{
					continue;
				}

				$_imageURL = wp_get_attachment_url( $attachmentId );

				if ( ! in_array( $_imageURL, $images ) )
				{
					$images[] = $_imageURL;
				}
			}
		}
		else
		{
			$all_images = [];

			$all_attached_images = get_attached_media( 'image', $post_id );

			foreach ( $all_attached_images as $attached_image )
			{
				if ( isset( $attached_image->ID ) )
				{
					$all_images[] = $attached_image->ID;
				}
			}

			preg_match_all( '/<img.*?data-id="(\d+)"|wp-image-(\d+)/', $post[ 'post_content' ], $all_wp_images );

			$all_wp_images = array_merge( $all_wp_images[ 1 ], $all_wp_images[ 2 ] );

			foreach ( $all_wp_images as $wp_image )
			{
				if ( ! in_array( $wp_image, $all_images ) )
				{
					$all_images[] = $wp_image;
				}
			}

			foreach ( $all_images as $mediaId2 )
			{
				if ( $mediaId2 > 0 && $mediaId2 != $ignoredFeaturedImage )
				{
					$_imageURL = wp_get_attachment_url( $mediaId2 );

					if ( ! in_array( $_imageURL, $images ) )
					{
						$images[] = $_imageURL;
					}
				}
			}
		}

		foreach ( $images as &$image )
		{
			$image = str_replace( ' ', '%20', $image );
		}

		return array_filter($images, function ($image){
            return ! empty($image);
        });
	}

	/**
	 * @param $post
	 * @param $postType
	 *
	 * @return array
	 */
	public static function getPostGallery ( $post, $postType )
	{
		$images  = [];
		$post_id = $post[ 'ID' ];

		$mediaId = get_post_meta( $post_id, '_fs_featured_image', TRUE );

		$ignoredFeaturedImage = 0;

		if ( empty( $mediaId ) )
		{
			$mediaId = get_post_thumbnail_id( $post_id );
		}
		else
		{
			$ignoredFeaturedImage = get_post_thumbnail_id( $post_id );
		}

		if ( $mediaId > 0 )
		{
			$images[] = get_attached_file( $mediaId );
		}

		if ( ( $postType === 'product' || $postType === 'product_variation' ) && function_exists( 'wc_get_product' ) )
		{
			$product        = wc_get_product( $post_id );
			$attachment_ids = $product->get_gallery_image_ids();

			foreach ( $attachment_ids as $attachmentId )
			{
				if ( $attachmentId == $ignoredFeaturedImage )
				{
					continue;
				}

				$_imageURL = self::getOrCreateImagePath( $attachmentId );

				if ( $_imageURL !== FALSE )
				{
					$images[] = $_imageURL;
				}
			}
		}
		else
		{
			$all_images = [];

			$all_attached_images = get_attached_media( 'image', $post_id );

			foreach ( $all_attached_images as $attached_image )
			{
				if ( isset( $attached_image->ID ) )
				{
					$all_images[] = $attached_image->ID;
				}
			}

			preg_match_all( '/wp-image-(\d+)/', $post[ 'post_content' ], $all_wp_images );

			foreach ( $all_wp_images[ 1 ] as $wp_image )
			{
				$all_images[] = $wp_image;
			}

			foreach ( $all_images as $mediaId2 )
			{
				if ( empty( $mediaId2 ) || $mediaId2 == $ignoredFeaturedImage )
				{
					continue;
				}

				$_imageURL = self::getOrCreateImagePath( $mediaId2 );

				if ( $_imageURL !== FALSE )
				{
					$images[] = $_imageURL;
				}
			}
		}

		return array_unique( $images );
	}

	/**
	 * Clear cache
	 */
	public static function clearCache ()
	{
		foreach ( self::$saveCacheFiles as $cacheFile )
		{
			if ( file_exists( $cacheFile ) )
			{
				unlink( $cacheFile );
			}
		}

		self::$saveCacheFiles = [];
	}

	public static function getOrCreateImagePath ( $mediaID )
	{
		if ( empty( $mediaID ) )
		{
			return FALSE;
		}

		$imagePath = get_attached_file( $mediaID );

		if ( empty( $imagePath ) )
		{
			return FALSE;
		}

		if ( file_exists( $imagePath ) )
		{
			return $imagePath;
		}

		if ( ! function_exists( 'tempnam' ) || ! function_exists( 'sys_get_temp_dir' ) )
		{
			return FALSE;
		}

		$imagePath = tempnam( sys_get_temp_dir(), 'FS_tmpfile_' );

		$mediaURL = wp_get_attachment_url( $mediaID );

		if ( empty( $mediaURL ) )
		{
			return FALSE;
		}

		Helper::downloadRemoteFile( $imagePath, wp_get_attachment_url( $mediaID ) );

		self::$saveCacheFiles[] = $imagePath;

		return $imagePath;
	}
}
