<?php

namespace FSPoster\App\Libraries\blogger;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\Session;
use FSPoster\App\Providers\SocialNetwork;
use FSP_GuzzleHttp\Exception\GuzzleException;
use FSP_GuzzleHttp\Exception\BadResponseException;

class Blogger extends SocialNetwork
{
	public static function sendPost ( $title, $content, $labels, $postType, $isDraft, $blog_id, $author_id, $access_token, $proxy = '' )
	{
		$post[ 'kind' ]           = 'blogger#' . $postType;
		$post[ 'blog' ][ 'id' ]   = $blog_id;
		$post[ 'title' ]          = $title;
		$post[ 'content' ]        = $content;
		$post[ 'author' ][ 'id' ] = $author_id;

		if ( ! empty( $labels ) )
		{
			$post[ 'labels' ] = implode( ',', $labels );
		}

		$params = [ 'isDraft' => $isDraft ];

		$response = self::cmd( "blogs/$blog_id/{$postType}s", $proxy, $access_token, 'POST', $post, $params );

		if ( isset( $response[ 'status' ] ) && $response[ 'status' ] === 'error' )
		{
			return $response;
		}

		if ( empty( $response[ 'id' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Unknown error!' )
			];
		}

		if ( $isDraft || ! isset( $response[ 'url' ] ) )
		{
			$url = sprintf( 'https://www.blogger.com/blog/%s/edit/%s/%s', $postType, $blog_id, $response[ 'id' ] );
		}
		else
		{
			$url = $response[ 'url' ];
		}

		return [
			'status'    => 'ok',
			'id'        => $response[ 'id' ],
			'id2'       => $url,
			'feed_type' => $postType
		];
	}

	public static function callbackURL ()
	{
		return site_url() . '/?blogger_callback=1';
	}

	public static function cmd ( $endpoint, $proxy, $accessToken = '', $method = 'GET', $body = '', $params = [] )
	{
		$api = $endpoint === 'userinfo' ? 'oauth2' : 'blogger';
		$url = 'https://www.googleapis.com/' . $api . '/v3/' . $endpoint;

		$options = [];

		if ( ! empty( $body ) )
		{
			$body              = is_array( $body ) ? json_encode( $body ) : $body;
			$options[ 'body' ] = $body;
		}

		if ( ! empty( $params ) )
		{
			$options[ 'query' ] = $params;
		}

		if ( ! empty( $accessToken ) )
		{
			$options[ 'headers' ] = [
				'Connection'                => 'Keep-Alive',
				'X-li-format'               => 'json',
				'Content-Type'              => 'application/json',
				'X-RestLi-Protocol-Version' => '2.0.0',
				'Authorization'             => 'Bearer ' . $accessToken
			];
		}

		if ( ! empty( $proxy ) )
		{
			$options[ 'proxy' ] = $proxy;
		}

		$client = new Client();

		try
		{
			$response = $client->request( $method, $url, $options )->getBody();
		}
		catch ( BadResponseException $e )
		{
			$response = $e->getResponse()->getBody();
		}
		catch ( GuzzleException $e )
		{
			$response = $e->getMessage();
		}

		$response1 = json_decode( $response, TRUE );

		if ( ! $response1 )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( "Request error!" )
			];
		}
		else
		{
			$response = $response1;
		}

		if ( isset( $response[ 'error' ] ) )
		{
			$error_msg = 'Error!';

			if ( isset( $response[ 'error' ][ 'status' ] ) && $response[ 'error' ][ 'status' ] === 'PERMISSION_DENIED' )
			{
				$error_msg = fsp__( 'You need to check all the required checkboxes to add your account to the plugin.' );
			}
			else if ( isset( $response[ 'error' ][ 'message' ] ) )
			{
				$error_msg = $response[ 'error' ][ 'message' ];
			}
			else if ( $response[ 'error_description' ] )
			{
				$error_msg = $response[ 'error_description' ];
			}

			return [
				'status'    => 'error',
				'error_msg' => fsp__( $error_msg )
			];
		}

		return $response;
	}

	public static function getLoginURL ( $app_id )
	{
		$proxy = Request::get( 'proxy', '', 'string' );

		Session::set( 'app_id', $app_id );
		Session::set( 'proxy', $proxy );

		$app_info = DB::fetch( 'apps', [ 'id' => $app_id, 'driver' => 'blogger' ] );

		$authURL = 'https://accounts.google.com/o/oauth2/auth';

		$scopes = [
			'https://www.googleapis.com/auth/blogger',
			'email',
			'profile'
		];

		$params = [
			'response_type' => 'code',
			'access_type'   => 'offline',
			'client_id'     => $app_info[ 'app_id' ],
			'redirect_uri'  => self::callbackURL(),
			'state'         => NULL,
			'scope'         => implode( ' ', $scopes ),
			'prompt'        => 'consent'
		];

		return $authURL . '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );

	}

	public static function getAccessToken ()
	{
		$app_id = Session::get( 'app_id' );
		$proxy  = Session::get( 'proxy' );
		$code   = Request::get( 'code', '', 'str' );

		if ( empty( $app_id ) || empty( $code ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => ''
			];
		}

		$appInfo = DB::fetch( 'apps', [ 'id' => $app_id, 'driver' => 'blogger' ] );

		Session::remove( 'app_id' );
		Session::remove( 'proxy' );

		try
		{
			$client = new Client();

			$options = [
				'query' => [
					'client_id'     => $appInfo[ 'app_id' ],
					'client_secret' => $appInfo[ 'app_secret' ],
					'code'          => $code,
					'grant_type'    => 'authorization_code',
					'redirect_uri'  => self::callbackURL()
				]
			];

			if ( ! empty( $proxy ) )
			{
				$options[ 'proxy' ] = $proxy;
			}

			$tokenInfo = $client->post( 'https://oauth2.googleapis.com/token', $options )->getBody()->getContents();
			$tokenInfo = json_decode( $tokenInfo, TRUE );

			if ( ! ( isset( $tokenInfo[ 'access_token' ] ) && isset( $tokenInfo[ 'refresh_token' ] ) ) )
			{
				return [
					'status'    => FALSE,
					'error_msg' => fsp__( 'Failed to get access token!' )
				];
			}
		}
		catch ( Exception $e )
		{
			return [
				'status'    => FALSE,
				'error_msg' => ''
			];
		}

		return self::authorize( $appInfo, $tokenInfo[ 'access_token' ], $proxy, $tokenInfo[ 'refresh_token' ], $tokenInfo[ 'expires_in' ] );
	}

	public static function authorize ( $app_info, $access_token, $proxy, $refresh_token, $expires_in )
	{
		$result = self::insert_account( $app_info, $access_token, $proxy, $refresh_token, $expires_in );

		if ( isset( $result[ 'status' ] ) && $result[ 'status' ] === 'error' )
		{
			return [
				'status'    => FALSE,
				'error_msg' => $result[ 'error_msg' ]
			];
		}

		return [
			'status' => TRUE
		];
	}

	public static function refetch_account ( $app_info, $access_token, $proxy )
	{
		$result = self::insert_account( $app_info, $access_token, $proxy );

		if ( isset( $result[ 'status' ] ) && $result[ 'status' ] === 'error' )
		{
			return [
				'status'    => FALSE,
				'error_msg' => $result[ 'error_msg' ]
			];
		}
		else
		{
			return [
				'status' => TRUE
			];
		}
	}

	private static function insert_account ( $app_info, $access_token, $proxy, $refresh_token = '', $expires_in = '' )
	{
		$blogger_info = self::cmd( 'users/self', $proxy, $access_token );

		if ( isset( $blogger_info[ 'status' ] ) && $blogger_info[ 'status' ] === 'error' )
		{
			return $blogger_info;
		}

		$google_info = self::cmd( 'userinfo', $proxy, $access_token );

		if ( isset( $google_info[ 'status' ] ) && $google_info[ 'status' ] === 'error' )
		{
			return $google_info;
		}

		//$google_name  = $google_info['name'];

		$current_user = get_current_user_id();

		if ( ! $current_user > 0 )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' )
			];
		}

		$sql_account = [
			'user_id'     => $current_user,
			'driver'      => 'blogger',
			'profile_id'  => $blogger_info[ 'id' ],
			'name'        => $blogger_info[ 'displayName' ],
			'profile_pic' => isset( $google_info[ 'picture' ] ) ? $google_info[ 'picture' ] : NULL,
			'email'       => $google_info[ 'email' ],
			'proxy'       => $proxy,
			'blog_id'     => get_current_blog_id()
		];

		$sql_access_token = [
			'access_token'  => $access_token,
			'refresh_token' => $refresh_token,
			'expires_on'    => Date::dateTimeSQL( Date::epoch() + $expires_in ),
			'app_id'        => $app_info[ 'id' ]
		];

		$account_check_data = [
			'user_id'    => get_current_user_id(),
			'driver'     => 'blogger',
			'profile_id' => $blogger_info[ 'id' ],
			'blog_id'    => Helper::getBlogId()
		];

		$check_account_exists = DB::fetch( 'accounts', $account_check_data );

		if ( $check_account_exists )
		{
			$account_id = $check_account_exists[ 'id' ];
			DB::DB()->update( DB::table( 'accounts' ), $sql_account, $account_check_data );

			if ( ! empty( $refresh_token ) && ! empty( $expires_in ) )
			{
				DB::DB()->update( DB::table( 'account_access_tokens' ), $sql_access_token, [ 'account_id' => $account_id ] );
			}
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $sql_account );
			$account_id = DB::DB()->insert_id;

			if ( ! empty( $refresh_token ) && ! empty( $expires_in ) )
			{
				$sql_access_token[ 'account_id' ] = $account_id;
				DB::DB()->insert( DB::table( 'account_access_tokens' ), $sql_access_token );
			}
		}

		if ( empty( $account_id ) || ! $account_id > 0 )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( "Couldn't add account." )
			];
		}

		return self::fetch_blogs( $account_id, $access_token, $proxy );

	}

	private static function fetch_blogs ( $account_id, $access_token, $proxy )
	{
		$added = [];
		$blogs = self::cmd( 'users/self/blogs', $proxy, $access_token );

		if ( isset( $blogs[ 'status' ] ) && $blogs[ 'status' ] === 'error' )
		{
			return $blogs;
		}

		if ( isset( $blogs[ 'items' ] ) )
		{
			foreach ( $blogs[ 'items' ] as $blog )
			{
				$blog_check_data = [
					'account_id' => $account_id,
					'node_id'    => $blog[ 'id' ],
					'user_id'    => get_current_user_id(),
					'driver'     => 'blogger',
					'blog_id'    => Helper::getBlogId(),
					'node_type'  => 'blog'
				];

				$check_node_exists = DB::fetch( 'account_nodes', $blog_check_data );

				$sql_blog                  = $blog_check_data;
				$sql_blog[ 'name' ]        = $blog[ 'name' ];
				$sql_blog[ 'screen_name' ] = $blog[ 'url' ];

				if ( $check_node_exists )
				{
					DB::DB()->update( DB::table( 'account_nodes' ), $sql_blog, $blog_check_data );
					$added[] = $check_node_exists[ 'id' ];
				}
				else
				{
					DB::DB()->insert( DB::table( 'account_nodes' ), $sql_blog );
					$added[] = DB::DB()->insert_id;
				}
			}
		}

		if ( empty( $added ) )
		{
			$nodes = DB::DB()->get_row( 'SELECT group_concat(id) as nodes FROM `' . DB::table( 'account_nodes' ) . '` WHERE account_id=' . $account_id, 'ARRAY_A' );
		}
		else
		{
			$nodes = DB::DB()->get_row( 'SELECT group_concat(id) as nodes FROM `' . DB::table( 'account_nodes' ) . '` WHERE account_id=' . $account_id . ' AND id NOT IN (' . implode( ',', $added ) . ')', 'ARRAY_A' );
		}

		if ( ! empty( $nodes[ 'nodes' ] ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . $nodes[ 'nodes' ] . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . $nodes[ 'nodes' ] . ')' );
		}

		return [
			'status' => 'ok'
		];

	}

	private static function refreshToken ( $token_info )
	{
		$app_id = $token_info[ 'app_id' ];

		$account_info = DB::fetch( 'accounts', $token_info[ 'account_id' ] );
		$proxy        = $account_info[ 'proxy' ];

		$app_info      = DB::fetch( 'apps', $app_id );
		$refresh_token = $token_info[ 'refresh_token' ];

		$client = new Client();

		$options = [
			'query' => [
				'client_id'     => $app_info[ 'app_id' ],
				'client_secret' => $app_info[ 'app_secret' ],
				'grant_type'    => 'refresh_token',
				'refresh_token' => $refresh_token
			]
		];

		if ( ! empty( $proxy ) )
		{
			$options[ 'proxy' ] = $proxy;
		}

		try
		{
			$refreshed_token = $client->post( 'https://oauth2.googleapis.com/token', $options )->getBody()->getContents();
			$refreshed_token = json_decode( $refreshed_token, TRUE );
		}
		catch ( Exception $e )
		{
			return '';
		}

		$access_token = $refreshed_token[ 'access_token' ];

		DB::DB()->update( DB::table( 'account_access_tokens' ), [
			'access_token' => $access_token,
			'expires_on'   => Date::dateTimeSQL( 'now', '+55 minutes' )
		], [ 'id' => $token_info[ 'id' ] ] );

		return $access_token;
	}

	public static function accessToken ( $token_info )
	{
		if ( ( Date::epoch() + 30 ) > Date::epoch( $token_info[ 'expires_on' ] ) )
		{
			return self::refreshToken( $token_info );
		}

		return $token_info[ 'access_token' ];
	}
}