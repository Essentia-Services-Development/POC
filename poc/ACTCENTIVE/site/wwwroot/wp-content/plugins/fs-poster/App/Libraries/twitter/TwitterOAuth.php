<?php

namespace FSPoster\App\Libraries\twitter;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\Helper;

class TwitterOAuth
{
	private $apiHost = 'https://api.twitter.com';
	private $appKey;
	private $appSecret;
	private $accessToken;
	private $accessTokenSecret;
	private $proxy;

	function __construct ( $appKey, $appSecret, $accessToken, $accessTokenSecret, $proxy )
	{
		$this->appKey            = $appKey;
		$this->appSecret         = $appSecret;
		$this->accessToken       = $accessToken;
		$this->accessTokenSecret = $accessTokenSecret;
		$this->proxy             = $proxy;
	}

	private function error ( $message )
	{
		return [
			'errors' =>
				[
					[
						'message' => $message,
						'code'    => 0
					]
				]
		];
	}

	private function signRequest ( $requestMethod, $apiLink, $nonce, $timeStamp, $content = [] )
	{
		$defaults = [
			'oauth_consumer_key'     => $this->appKey,
			'oauth_nonce'            => $nonce,
			'oauth_timestamp'        => $timeStamp,
			'oauth_token'            => $this->accessToken,
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_version'          => '1.0'
		];

		$args = array_merge( $defaults, $content );

		$args_purified = array_filter( $args, function ( $value ) {
			return $value !== NULL && $value !== '';
		} );

		ksort( $args_purified );

		$url_params = [];

		foreach ( $args_purified as $key => $value )
		{
			$url_params[] = rawurlencode( $key ) . '=' . rawurlencode( $value );
		}

		$url_params_str = implode( '&', $url_params );

		$base = strtoupper( $requestMethod ) . '&' . rawurlencode( $apiLink ) . '&' . rawurlencode( $url_params_str );

		$key = rawurlencode( $this->appSecret ) . '&' . rawurlencode( $this->accessTokenSecret );

		return rawurlencode( base64_encode( hash_hmac( 'SHA1', $base, $key, TRUE ) ) );
	}

	private function getClient ( $method, $url, $data, $body = [] )
	{
		$nonce     = mt_rand( 10000000, 99999999 );
		$timeStamp = time();

		$headerParts = [
			'oauth_consumer_key'     => $this->appKey,
			'oauth_nonce'            => $nonce,
			'oauth_signature'        => $this->signRequest( strtoupper( $method ), $url, $nonce, $timeStamp, $data ),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => $timeStamp,
			'oauth_version'          => '1.0'
		];

		if ( ! empty( $this->accessToken ) )
		{
			$headerParts [ 'oauth_token' ] = $this->accessToken;
		}

		$authHeader = sprintf( 'OAuth ' . implode( '="%s", ', array_keys( $headerParts ) ) . '="%s"', ...array_values( $headerParts ) );

		return new Client(
			[
				'query'   => $data,
				'headers' => [
					'Authorization' => $authHeader
				],
                'json'    => empty( $body ) ? NULL : $body,
				'verify'  => false,
				'proxy'   => $this->proxy
			]
		);
	}

	private function request ( $method, $url, $data = [], $body = [] )
	{
		try
		{
			$c        = $this->getClient( $method, $url, $data, $body );
			$response = $c->request( $method, $url );
		}
		catch ( Exception $e )
		{
			if ( ! method_exists( $e, 'getResponse' ) || empty( $e->getResponse() ) )
			{
                return $this->error( fsp__( $e->getMessage() ) );
			}

			$response = $e->getResponse();
		}

		$response = $response->getBody()->getContents();

		$arr = json_decode( $response, TRUE );

		if ( empty( $arr ) )
		{
			return $response;
		}

		return $arr;
	}

	public function get ( $url, $data = [] )
	{
		$res = $this->request( 'GET', $url, $data );

		if ( ! is_array( $res ) )
		{
			return $this->error( fsp__( 'Unknown error!' ) );
		}

		return $res;
	}

	public function post ( $url, $data = [], $body = [] )
	{
		$res = $this->request( 'POST', $url, $data, $body );

		if ( ! is_array( $res ) )
		{
			return $this->error( fsp__( 'Unknown error!' ) );
		}

		return $res;
	}

	public function oauth ( $endpoint, $data )
	{
		$url = $this->apiHost . '/oauth/' . $endpoint;

		$response = $this->request( 'POST', $url, $data );

		if ( is_array( $response ) )
		{
			return $response;
		}

		parse_str( $response, $res );

		return $res;
	}

	public function upload ( $file, $type = '' )
	{
		$uploadURL = 'https://upload.twitter.com/1.1/media/upload.json';

		$initData = [
			'command'     => 'INIT',
			'media_type'  => Helper::mimeContentType( $file ),
			'total_bytes' => strlen( file_get_contents( $file ) )
		];

		$mime = Helper::mimeContentType( $file );

		if ( strpos( strtolower( $mime ), 'video' ) !== FALSE )
		{
			$initData[ 'media_category' ] = 'TWEET_VIDEO';
		}
		else if ( strpos( strtolower( $mime ), 'gif' ) !== FALSE )
		{
			$initData[ 'media_category' ] = 'TWEET_GIF';
		}
		else
		{
			$initData[ 'media_category' ] = 'TWEET_IMAGE';
		}

		$init = $this->post( $uploadURL, $initData );

		if ( empty( $init[ 'media_id' ] ) || empty( $init[ 'media_id_string' ] ) )
		{
			return $init;
		}

		$segmentIndex = 0;
		$media        = fopen( $file, 'rb' );

		while ( ! feof( $media ) )
		{
			$c = $this->getClient( 'POST', $uploadURL, [
				'command'       => 'APPEND',
				'media_id'      => $init[ 'media_id_string' ],
				'segment_index' => $segmentIndex++
			] );

			$appended = $c->post( $uploadURL, [
				'multipart' => [
					[
						'name'     => 'media',
						'contents' => fread( $media, 500000 )
					]
				]
			] )->getStatusCode();

			if ( $appended < 200 || $appended > 299 )
			{
				return $this->error( fsp__( 'Failed to upload "%s"', [ $file ], FALSE ) );
			}
		}
		fclose( $media );

		$uploadedVideo = $this->post( $uploadURL, [
			'command'  => 'FINALIZE',
			'media_id' => $init[ 'media_id_string' ]
		] );

		if ( isset( $uploadedVideo[ 'error' ] ) )
		{
			if ( is_string( $uploadedVideo[ 'error' ] ) )
			{
				return $this->error( fsp__( $uploadedVideo[ 'error' ] ) );
			}
		}
		else if ( isset( $uploadedVideo[ 'errors' ] ) || empty( $uploadedVideo[ 'media_id' ] ) || empty( $uploadedVideo[ 'processing_info' ] ) || empty( $uploadedVideo[ 'processing_info' ][ 'state' ] ) || $uploadedVideo[ 'processing_info' ][ 'state' ] === 'succeeded' )
		{
			return $uploadedVideo;
		}

		if ( $uploadedVideo[ 'processing_info' ][ 'state' ] === 'failed' )
		{
			if ( isset( $uploadedVideo[ 'processing_info' ][ 'error' ][ 'message' ] ) )
			{
				return $this->error( fsp__( $uploadedVideo[ 'processing_info' ][ 'error' ][ 'message' ] ) );
			}
		}

		if ( in_array( $uploadedVideo[ 'processing_info' ][ 'state' ], [ 'pending', 'in_progress' ] ) )
		{
			if ( isset( $uploadedVideo[ 'processing_info' ][ 'check_after_secs' ] ) )
			{
				sleep( $uploadedVideo[ 'processing_info' ][ 'check_after_secs' ] + 1 );
			}

			do
			{
				if ( isset( $checkState[ 'processing_info' ][ 'check_after_secs' ] ) )
				{
					sleep( $checkState[ 'processing_info' ][ 'check_after_secs' ] + 1 );
				}

				$checkState = $this->get( $uploadURL, [
					'command'  => 'STATUS',
					'media_id' => $uploadedVideo[ 'media_id_string' ]
				] );
			} while ( isset( $checkState[ 'processing_info' ][ 'check_after_secs' ] ) );

			if ( $checkState[ 'processing_info' ][ 'state' ] === 'failed' )
			{
				if ( isset( $checkState[ 'processing_info' ][ 'error' ][ 'message' ] ) )
				{
					return $this->error( fsp__( $checkState[ 'processing_info' ][ 'error' ][ 'message' ] ) );
				}
			}
		}

		return $uploadedVideo;
	}
}