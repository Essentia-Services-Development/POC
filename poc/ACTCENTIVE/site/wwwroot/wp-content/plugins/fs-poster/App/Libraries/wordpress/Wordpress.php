<?php

namespace FSPoster\App\Libraries\wordpress;

use Exception;
use IXR_Base64;
use IXR_Message;
use IXR_Request;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\Helper;

class Wordpress
{
	private $site_url;
	private $username;
	private $password;
	private $client;
	private $proxy;

	public function __construct ( $site_url, $username, $password, $proxy = '' )
	{
		$this->proxy    = $proxy;
		$this->site_url = rtrim( $site_url, '/' );
		$this->username = $username;
		$this->password = $password;

		include_once( ABSPATH . WPINC . '/class-IXR.php' );
		//include_once( ABSPATH . WPINC . '/class-wp-http-ixr-client.php' );

		$this->client = new Client( [
			'proxy'       => empty( $proxy ) ? NULL : $proxy,
			'verify'      => FALSE,
			'http_errors' => FALSE,
			'headers'     => [
				'Content-Type' => 'text/xml',
				'User-Agent'   => 'wp-android'
			]
		] );
	}

	public function sendPost ( $postInf, $post_type, $title, $excerpt, $content, $feedInf, $thumbnail = '' )
	{
		$postTypes = $this->cmd( 'wp.getPostTypes' );

		if ( ! array_key_exists( $post_type, $postTypes ) )
		{
			$post_method = Helper::getCustomSetting( 'posting_type', Helper::getOption( 'wordpress_posting_type', '1' ), $feedInf[ 'node_type' ], $feedInf[ 'node_id' ] );

			if ( $post_method === '1' )
			{
				$post_type = 'post';
			}
			else
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Failed to share the post because the post type is not available on the remote website.' )
				];
			}
		}

		$params = [
			'post_title'   => $title,
			'post_excerpt' => $excerpt,
			'post_content' => $content,
			'post_type'    => $post_type,
			'post_status'  => Helper::getOption( 'wordpress_post_status', 'publish' ),
			'terms'        => [],
		];

		if ( Helper::getOption( 'wordpress_post_with_categories', '1' ) == 1 )
		{
			$cats = $this->terms( $postInf[ 'ID' ], 'category' );

			if ( isset( $cats[ 'error_msg' ] ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( $cats[ 'error_msg' ] )
				];
			}

			$params[ 'terms' ][ 'category' ] = $cats;
		}

		if ( Helper::getOption( 'wordpress_post_with_tags', '1' ) == 1 )
		{
			$tags = $this->terms( $postInf[ 'ID' ], 'post_tag' );

			if ( isset( $tags[ 'error_msg' ] ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( $tags[ 'error_msg' ] )
				];
			}

			$params[ 'terms' ][ 'post_tag' ] = $tags;
		}

		if ( empty( $params[ 'terms' ] ) )
		{
			unset( $params[ 'terms' ] );
		}

		$mediaId = $this->uploadPhoto( $thumbnail );

		if ( ! empty( $mediaId ) )
		{
			$params[ 'post_thumbnail' ] = $mediaId;
		}

		$createPost = $this->cmd( 'wp.newPost', $params );

		if ( isset( $createPost[ 'faultString' ] ) || ! is_numeric( $createPost ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => isset( $createPost[ 'faultString' ] ) ? $createPost[ 'faultString' ] : fsp__( 'An error occurred while processing the request!' )
			];
		}

		$post_id = (int) $createPost;

        $this->cmd('wp.editPost', $mediaId, [ 'post_parent' => $post_id ] );

		return [
			'status' => 'ok',
			'id'     => $post_id
		];
	}

	public function terms ( $post_id, $taxonomy )
	{
		$postTerms = wp_get_post_terms( $post_id, $taxonomy );
		$termIDs   = [];

		foreach ( $postTerms as $term )
		{
			$termId = $this->syncTerm( $term->slug, $term->name, $taxonomy );

			if ( isset( $termId[ 'faultCode' ] ) )
			{

				if ( $termId[ 'faultCode' ] == 500 )
				{
					continue;
				}
				else
				{
					return [ 'error_msg' => $termId[ 'faultString' ] ];
				}

			}

			if ( ! empty( $termId ) )
			{
				$termIDs[] = $termId;
			}
		}

		return $termIDs;
	}

	public function syncTerm ( $termSlug, $termName, $taxonomy )
	{
		$termId = $this->findTerm( $termSlug, $taxonomy );

		if ( empty( $termId ) )
		{
			$termId = $this->cmd( 'wp.newTerm', [
				'name'     => $termName,
				'slug'     => $termSlug,
				'taxonomy' => $taxonomy
			] );
		}

		return $termId;
	}

	public function findTerm ( $termSlug, $taxonomy )
	{
		$result = $this->cmd( 'wp.getTerms', $taxonomy, [ 'search' => $termSlug ] );

		foreach ( $result as $termInf )
		{
			if ( isset( $termInf[ 'slug' ] ) && $termInf[ 'slug' ] == $termSlug )
			{
				return $termInf[ 'term_id' ];
			}
		}

		return FALSE;
	}

	public function cmd ()
	{
		$args    = func_get_args();
		$command = array_shift( $args );

		$params = array_merge( [ 0, $this->username, $this->password ], $args );

		$request = new IXR_Request( $command, $params );
		$xml     = $request->getXml();

		try
		{
			$result = $this->client->request( 'POST', $this->site_url . '/xmlrpc.php', [
				'body' => $xml
			] );
		}
		catch ( Exception $e )
		{
			return [
				'faultString' => $e->getMessage()
			];
		}

		$message = new IXR_Message( (string) $result->getBody() );
		$message->parse();

		if ( ! isset( $message->params[ 0 ] ) )
		{
			return [
				'faultString' => fsp__( 'An error occurred while processing the request!' )
			];
		}

		return $message->params[ 0 ];
	}

	public function uploadPhoto ( $image = NULL )
	{
		if ( empty( $image ) )
		{
			return FALSE;
		}

		$content = [
			'name' => basename( $image ),
			'type' => Helper::mimeContentType( $image ),
			'bits' => new IXR_Base64( file_get_contents( $image ) ),
			TRUE
		];

		$result = $this->cmd( 'metaWeblog.newMediaObject', $content );

		return isset( $result[ 'id' ] ) ? $result[ 'id' ] : FALSE;
	}

	/**
	 * @return array
	 */
	public function checkAccount ()
	{
		$result    = [
			'error'     => TRUE,
			'error_msg' => NULL
		];
		$checkUser = $this->checkUser();

		if ( $checkUser !== TRUE )
		{
			$result[ 'error_msg' ] = $checkUser;
		}
		else if ( $checkUser === TRUE )
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}

	public function checkUser ()
	{
		$info = $this->cmd( 'wp.getProfile' );

		if ( isset( $info[ 'faultString' ] ) )
		{
			return $info[ 'faultString' ];
		}

		if ( isset( $info[ 'user_id' ] ) )
		{
			return TRUE;
		}

		return fsp__( 'The account is disconnected from the plugin. Please add your account to the plugin again by getting the cookie on the browser <a href=\'https://www.fs-poster.com/documentation/fs-poster-schedule-auto-publish-wordpress-posts-to-pinterest\' target=\'_blank\'>Incognito mode</a>. And close the browser without logging out from the account.', [], FALSE );
	}

	public function refetch_account ( $account_id )
	{
		return [];
	}
}
