<?php

namespace FSPoster\App\Libraries\mastodon;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Session;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\SocialNetwork;

class Mastodon extends SocialNetwork
{
	/**
	 * @var mixed
	 */
	private $server;
	/**
	 * @var mixed
	 */
	private $accessToken;
	/**
	 * @var mixed
	 */
	private $proxy;
	private $appID;

	function __construct ( $appInfo = NULL, $accessToken = NULL, $proxy = NULL )
	{
		$this->server      = self::getAppServer( $appInfo );
		$this->appID       = $appInfo[ 'id' ];
		$this->accessToken = $accessToken;
		$this->proxy       = $proxy;
	}

	public function sendPost ( $sendType, $message, $link, $images, $video )
	{
		$parameters = [];

		if ( ! empty( $message ) )
		{
			$parameters[ 'status' ] = $message;
		}

		if ( $sendType === 'link' )
		{
			$nl                     = empty( $parameters[ 'status' ] ) ? '' : "\n";
			$parameters[ 'status' ] .= $nl . $link;
		}

		if ( $sendType === 'image' && ! empty( $images ) && is_array( $images ) )
		{
			$images   = array_slice( $images, 0, 4 );
			$uploaded = $this->uploadMedia( $images );

			if ( $uploaded[ 'status' ] === FALSE )
			{
				return [
					'status'    => 'error',
					'error_msg' => $uploaded[ 'error_msg' ]
				];
			}

			$parameters[ 'media_ids' ] = $uploaded[ 'data' ];
		}

		if ( $sendType === 'video' && ! empty( $video ) && is_string( $video ) )
		{
			$uploadedVideo = $this->uploadMedia( [ $video ] );

			if ( $uploadedVideo[ 'status' ] === FALSE )
			{
				return [
					'status'    => 'error',
					'error_msg' => $uploadedVideo[ 'error_msg' ]
				];
			}

			$parameters[ 'media_ids' ] = $uploadedVideo[ 'data' ];
		}

		$post = $this->cmd( 'post', 'api/v1/statuses', [
			'json' => $parameters
		] );

		if ( $post[ 'status' ] === FALSE )
		{
			return [
				'status'    => 'error',
				'error_msg' => $post[ 'error_msg' ]
			];
		}

		if ( ! isset( $post[ 'data' ][ 'id' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Unknown error!' )
			];
		}

		return [
			'status'    => 'ok',
			'id'        => $post[ 'data' ][ 'id' ],
			'post_link' => isset( $post[ 'data' ][ 'url' ] ) ? $post[ 'data' ][ 'url' ] : ''
		];
	}

	private function uploadMedia ( $medias )
	{
		$data = [];

		foreach ( $medias as $media )
		{
			$response = $this->cmd( 'post', 'api/v1/media', [
				'multipart' => [
					[
						'name'     => 'file',
						'filename' => basename($media),
						'contents' => file_get_contents( $media ),
						'headers'  => [
							'Content-Type' => Helper::mimeContentType( $media )
						]
					]
				]
			] );

			if ( $response[ 'status' ] === FALSE )
			{
				return $response;
			}

			if ( ! isset( $response[ 'data' ][ 'id' ] ) )
			{
				return [
					'status'    => FALSE,
					'error_msg' => fsp__( 'Unknown error!' )
				];
			}

			$data[] = $response[ 'data' ][ 'id' ];
		}

		return [
			'status' => TRUE,
			'data'   => $data
		];
	}

	private function cmd ( $method, $endpoint, $options = [] )
	{
		if ( ! empty( $this->proxy ) )
		{
			$options[ 'proxy' ] = $this->proxy;
		}

		if ( ! empty( $this->accessToken ) )
		{
			$options[ 'headers' ][ 'Authorization' ] = 'Bearer ' . $this->accessToken;
		}

		$method   = strtolower( $method );
		$endpoint = trim( $endpoint, '/' );
		try
		{
			$c = new Client();
			$response = $c->$method( $this->server . '/' . $endpoint, $options );
		}
		catch ( Exception $e )
		{
			if ( method_exists( $e, 'getResponse' ) )
			{
				$response = $e->getResponse();

				if ( is_null( $response ) || ! method_exists( $response, 'getBody' ) )
				{
					return [
						'status'    => FALSE,
						'error_msg' => $e->getMessage()
					];
				}
			}
			else
			{
				return [
					'status'    => FALSE,
					'error_msg' => $e->getMessage()
				];
			}
		}

		$response = $response->getBody()->getContents();
		$response = json_decode( $response, TRUE );

		if ( isset( $response[ 'error' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => isset( $response[ 'error_description' ] ) ? $response[ 'error_description' ] : $response[ 'error' ]
			];
		}
		else if ( ! is_array( $response ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'Unknown error!' )
			];
		}
		else
		{
			return [
				'status' => TRUE,
				'data'   => $response
			];
		}
	}

	public static function getAppServer ( $appInfo )
	{
		$data = $appInfo[ 'data' ];
		$data = json_decode( $data, TRUE );

		return $data[ 'server' ];
	}

	public static function getLoginURL ( $appId )
	{
		Session::set( 'app_id', $appId );
		Session::set( 'proxy', Request::get( 'proxy', '', 'string' ) );

		$appInf = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'mastodon' ] );

		$appId  = $appInf[ 'app_key' ];
		$server = self::getAppServer( $appInf );

		$permissions = implode( ' ', [ 'read', 'write' ] );
		$callback    = self::callbackURL();

		return trim( $server, '/' ) . '/oauth/authorize?' . http_build_query( [
				'redirect_uri'  => $callback,
				'response_type' => 'code',
				'scope'         => $permissions,
				'client_id'     => $appId
			] );
	}

	public static function callbackURL ()
	{
		return site_url() . '/?mastodon_callback=1';
	}

	public static function getAccessToken ()
	{
		$appId = (int) Session::get( 'app_id' );

		if ( empty( $appId ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => ''
			];
		}

		$code = Request::get( 'code', '', 'string' );

		if ( empty( $code ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => ''
			];
		}

		$proxy = Session::get( 'proxy' );

		Session::remove( 'app_id' );
		Session::remove( 'proxy' );

		$appInf = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'mastodon' ] );

		$mastodon = new self( $appInf, NULL, $proxy );
		$response = $mastodon->cmd( 'post', 'oauth/token', [
			'query' => [
				'code'          => $code,
				'grant_type'    => 'authorization_code',
				'client_id'     => $appInf[ 'app_key' ],
				'client_secret' => $appInf[ 'app_secret' ],
				'redirect_uri'  => self::callbackURL(),
				'scope'         => implode( ' ', [ 'read', 'write' ] )
			]
		] );

		if ( ! $response[ 'status' ] )
		{
			return $response;
		}

		if ( empty( $response[ 'data' ][ 'access_token' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'Unknown error!' )
			];
		}

		$mastodon->accessToken = $response[ 'data' ][ 'access_token' ];

		return $mastodon->authorize();

	}

	private function authorize ()
	{
		$response = $this->cmd( 'get', 'api/v1/accounts/verify_credentials' );

		if ( ! $response[ 'status' ] )
		{
			return $response;
		}

		if ( empty( $response[ 'data' ][ 'id' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'Unknown error!' )
			];
		}

		$accountsWithSameId = DB::fetchAll( 'accounts', [
			'profile_id' => $response[ 'data' ][ 'id' ],
			'driver'     => 'mastodon',
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id()
		] );

		$accountExists = FALSE;

		foreach ( $accountsWithSameId as $account )
		{
			$options = json_decode( $account[ 'options' ], TRUE );
			if ( ! empty( $options ) && isset( $options[ 'server' ] ) && $options[ 'server' ] === $this->server )
			{
				$accountExists = $account[ 'id' ];
				break;
			}
		}

		$dataSQL = [
			'user_id'     => get_current_user_id(),
			'blog_id'     => Helper::getBlogId(),
			'driver'      => 'mastodon',
			'profile_id'  => $response[ 'data' ][ 'id' ],
			'username'    => $response[ 'data' ][ 'username' ],
			'name'        => is_string( $response[ 'data' ][ 'display_name' ] ) ? $response[ 'data' ][ 'display_name' ] : '',
			'profile_pic' => $response[ 'data' ][ 'avatar_static' ],
			'options'     => json_encode( [ 'server' => $this->server ] ),
			'proxy'       => $this->proxy
		];

		if ( $accountExists === FALSE )
		{
			DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );
			$accountID = DB::DB()->insert_id;
		}
		else
		{
			DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [
				'id' => $accountExists
			] );

			$accountID = $accountExists;
		}

		DB::DB()->delete( DB::table( 'account_access_tokens' ), [
			'account_id' => $accountID
		] );

		DB::DB()->insert( DB::table( 'account_access_tokens' ), [
			'account_id'   => $accountID,
			'app_id'       => $this->appID,
			'access_token' => $this->accessToken,
		] );

		return [
			'status' => TRUE,
			'id'     => $accountID
		];
	}

	public function checkAccount ()
	{
		$result = [
			'error'     => TRUE,
			'error_msg' => NULL
		];

		$response = $this->cmd( 'get', 'api/v1/accounts/verify_credentials' );

		if ( ! $response[ 'status' ] )
		{
			$result[ 'error_msg' ] = $response[ 'error_msg' ];
		}
		else
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}
}