<?php

namespace FSPoster\App\Providers\v7;

use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\WPPostThumbnail;

class PostData
{
	public  $feed;
	public  $post;
	public  $node;
	public  $postId;
	private $driver;
	public  $message;
	public  $customMessage;
	public  $accountCustomMessage;
	public  $comment;
	public  $accessToken;
	public  $postType;
	public  $sendType;
	public  $nodeType;
	public  $link;
	public  $longLink;
	public  $shortLink;
	public  $images;
	public  $imagesLocale;
	public  $videoURL;
	public  $videoURLLocale;
	/**
	 * @var mixed
	 */
	private $wpmlLangSaved;

	public function init ()
	{
		$this->setSiteLang();
		$this->setAttributes();
		$this->setAttachments();

		//if driver supports html
		if ( ! in_array( $this->driver, [ 'google_b', 'medium', 'reddit' ] ) )
		{
			$this->post[ 'post_content' ] = str_replace( '<br>', "\n", $this->post[ 'post_content' ] );
		}

		if ( $this->postType == 'fs_post' || $this->postType == 'fs_post_tmp' )
		{
			$this->initDirectShare();
		}
		else
		{
			$this->initWP();
		}

		$this->setMessage();

		$this->resetSiteLang();

		return $this;
	}

	private function initDirectShare ()
	{
		$message = json_decode( $this->post[ 'post_content' ], TRUE );

		if ( ! is_null( $message ) )
		{
			$message = ! empty( $message[ $this->driver ] ) ? $message[ $this->driver ] : $message[ 'default' ];
		}
		else
		{
			$message = $this->post[ 'post_content' ];
		}

		$this->message  = Helper::spintax( $message );
		$link1          = get_post_meta( $this->postId, '_fs_link', TRUE );
		$link1          = Helper::spintax( $link1 );
		$this->longLink = $link1;

		$mediaId  = get_post_thumbnail_id( $this->postId );
		$mediaIds = get_post_meta( $this->postId, '_fs_post_type_gallery', TRUE );

		if ( $mediaId > 0 || ! empty( $mediaIds ) )
		{
			$this->images       = [];
			$this->imagesLocale = [];

			if ( ! empty( $mediaIds ) )
			{
				$imgIDArr = explode( ',', $mediaIds );
				foreach ( $imgIDArr as $mediaId )
				{
					$url1 = wp_get_attachment_url( $mediaId );
					$url2 = WPPostThumbnail::getOrCreateImagePath( $mediaId );

					if ( empty( $url1 ) || empty( $url2 ) )
					{
						continue;
					}

					$this->images[]       = $url1;
					$this->imagesLocale[] = $url2;
				}
			}
			else
			{
				$url1 = wp_get_attachment_url( $mediaId );
				$url2 = WPPostThumbnail::getOrCreateImagePath( $mediaId );

				if ( ! empty( $url1 ) && ! empty( $url2 ) )
				{
					$this->images       = [ $url1 ];
					$this->imagesLocale = [ $url2 ];
				}
			}
		}

		if ( ! empty( $this->images ) && ! empty( $this->imagesLocale ) )
		{
			$this->sendType = 'image';
		}
		else if ( empty( $link1 ) )
		{
			$this->sendType = 'custom_message';
		}

		if ( ! empty( $link1 ) )
		{
			if ( Helper::getCustomSetting( 'unique_link', '1', $this->nodeType, $this->node[ 'id' ] ) == 1 )
			{
				$link1 .= ( strpos( $link1, '?' ) === FALSE ? '?' : '&' ) . '_unique_id=' . uniqid();
			}

			$this->shortLink = Helper::shortenerURL( $link1, $this->nodeType, $this->node[ 'id' ] );
            $this->link      = $this->shortLink;
		}
	}

	private function initWP ()
	{
		$this->link = Helper::getPostLink( $this->post, $this->feed[ 'id' ], $this->node[ 'info' ] );

		if ( $this->feed[ 'feed_type' ] != 'story' && ! empty( $this->accountCustomMessage ) )
		{
			$this->customMessage = $this->accountCustomMessage;
		}
		else if ( empty( $this->customMessage ) )
		{
			$default_value       = $this->driver == 'wordpress' ? '{content_full}' : '{title}';
			$this->customMessage = Helper::getOption( 'post_text_message_' . $this->driver . ( ( $this->driver == 'instagram' || $this->driver == 'fb' ) && $this->feed[ 'feed_type' ] == 'story' ? '_h' : '' ), $default_value );
		}

		$this->longLink  = $this->link;
		$this->shortLink = Helper::shortenerURL( $this->link, $this->nodeType, $this->node[ 'id' ] );

		$message = Helper::replaceTags( $this->customMessage, $this->post, $this->longLink, $this->shortLink );
		$message = Helper::spintax( $message );

		if ( Helper::getOption( 'replace_wp_shortcodes', 'off' ) === 'on' )
		{
			$message = do_shortcode( $message );
		}
		else if ( Helper::getOption( 'replace_wp_shortcodes', 'off' ) === 'del' )
		{
			$message = strip_shortcodes( $message );
			//remove Divi shortcodes
			$message = preg_replace( '/\[\/?et_pb.*?\]/', '', $message );
		}

		$this->message = htmlspecialchars_decode( $message, ENT_QUOTES );
		$this->comment = Helper::configureComment( $this->post, $this->longLink, $this->shortLink, $this->driver, $this->nodeType, $this->node[ 'id' ] );
		$this->link    = $this->shortLink;
	}

	private function setMessage ()
	{
        if ( $this->driver != 'medium' && $this->driver != 'wordpress' && $this->driver != 'blogger' )
		{
			if ( $this->driver === 'telegram' )
			{
				$this->message = strip_tags( $this->message, '<b><u><i><a>' );
			}
			else
			{
				$this->message = strip_tags( $this->message );
			}

			$this->message = str_replace( [ '&nbsp;', "\r\n" ], [ ' ', "\n" ], $this->message );
		}

		if ( Helper::getOption( 'multiple_newlines_to_single', '0' ) == '1' && ! ( $this->postType == 'fs_post' || $this->postType == 'fs_post_tmp' ) )
		{
			$this->message = preg_replace( "/\n\s*\n\s*/", "\n\n", $this->message );
			//$message = preg_replace( "/(\n\s*){2,}/", "\n\n", $message );
		}
	}

	private function setAttributes ()
	{
		$this->accessToken          = $this->node[ 'access_token' ];
		$this->postId               = $this->feed[ 'post_id' ];
		$this->customMessage        = $this->feed[ 'custom_post_message' ];
		$this->driver               = $this->node[ 'driver' ];
		$this->nodeType             = $this->feed[ 'node_type' ];
		$this->sendType             = 'link';
		$this->accountCustomMessage = Helper::getCustomSetting( 'account_custom_post_message', '', $this->nodeType, $this->node[ 'id' ] );
		$this->post                 = get_post( $this->postId, ARRAY_A );
		$this->postType             = $this->post[ 'post_type' ];
	}

	private function setAttachments ()
	{
		if ( $this->postType == 'attachment' && strpos( $this->post[ 'post_mime_type' ], 'image' ) !== FALSE )
		{
			$this->sendType       = 'image';
			$this->images[]       = $this->post[ 'guid' ];
			$this->imagesLocale[] = get_attached_file( $this->postId );
		}
		else if ( $this->postType == 'attachment' && strpos( $this->post[ 'post_mime_type' ], 'video' ) !== FALSE )
		{
			$this->sendType       = 'video';
			$this->videoURL       = $this->post[ 'guid' ];
			$this->videoURLLocale = get_attached_file( $this->postId );
		}
	}

	/*---HELPERS---*/

	public function isDriver ( $driver )
	{
		return $this->driver === $driver;
	}

	public function hasMedia ()
	{
		return ! empty( $this->imagesLocale ) || ! empty( $this->videoURLLocale ) || ! empty( $this->images ) || ! empty( $this->videoURL );
	}

	/*---GETTERS---*/

	public function getNodeProfileId ()
	{
		return $this->node[ 'node_id' ];
	}

	public function getAppId ()
	{
		return $this->node[ 'app_id' ];
	}

	public function getAccessTokenSecret ()
	{
		return $this->node[ 'access_token_secret' ];

	}

	public function getProxy ()
	{
		return $this->node[ 'info' ][ 'proxy' ];
	}

	public function getOptions ()
	{
		return $this->node[ 'options' ];
	}

	public function getAccoundId ()
	{
		return $this->node[ 'account_id' ];
	}

	public function getPosterId ()
	{
		return $this->node[ 'poster_id' ];
	}

	public function getPostTitle ()
	{
		return $this->post[ 'post_title' ];
	}

	public function getDriver ()
	{
		return $this->driver;
	}

	public function getFeedData ()
	{
		$data = empty( $this->feed ) ? '{}' : $this->feed[ 'data' ];

		return json_decode( $data, TRUE );
	}

	public function getPostThumbnailURL ()
	{
		return WPPostThumbnail::getPostThumbnailURL( $this->postId, $this->driver );
	}

	public function getPostThumbnail ()
	{
		return WPPostThumbnail::getPostThumbnail( $this->postId, $this->driver );
	}

	public function getPostGalleryURL ()
	{
		return WPPostThumbnail::getPostGalleryURL( $this->post, $this->postType );
	}

	public function getPostGallery ()
	{
		return WPPostThumbnail::getPostGallery( $this->post, $this->postType );
	}

	/*---SETTERS---*/

	public function setFeed ( $feed )
	{
		$this->feed = $feed;

		return $this;
	}

	public function setNode ( $node )
	{
		$this->node = $node;

		return $this;
	}

	public function setSendType ( $type )
	{
		$this->sendType = $type;

		return $this;
	}

	public function setImages ( $images )
	{
		$this->images = $images;

		return $this;
	}

	public function setAccessToken ( $token )
	{
		$this->accessToken = $token;

		return $this;
	}

	public function setImagesLocale ( $imagesLocale )
	{
		$this->imagesLocale = $imagesLocale;

		return $this;
	}

	public function setPostTitle ( $title )
	{
		$this->post[ 'post_title' ] = Helper::replaceTags( $title, $this->post, $this->longLink, $this->shortLink );
	}

	/**
	 * sets site wpml language to post's lang
	 */
	private function setSiteLang ()
	{
		if ( empty( $this->feed[ 'post_id' ] ) )
		{
			return;
		}

		$postLang = apply_filters( 'wpml_post_language_details', NULL, $this->feed[ 'post_id' ] );

		if ( ! empty( $postLang[ 'language_code' ] ) )
		{
			$this->wpmlLangSaved = apply_filters( 'wpml_current_language', NULL );
			do_action( 'wpml_switch_language', $postLang[ 'language_code' ] );
		}
	}

	/**
	 * resets wpml language
	 */
	private function resetSiteLang ()
	{
		if ( ! empty( $this->wpmlLangSaved ) )
		{
			do_action( 'wpml_switch_language', $this->wpmlLangSaved );
		}
	}
}