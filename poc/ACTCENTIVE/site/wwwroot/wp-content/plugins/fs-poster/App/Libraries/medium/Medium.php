<?php

namespace FSPoster\App\Libraries\medium;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Curl;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\Session;
use FSPoster\App\Providers\SocialNetwork;

class Medium extends SocialNetwork
{
	/**
	 * @param array $account_info
	 * @param string $type
	 * @param string $title
	 * @param string $message
	 * @param string $accessToken
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function sendPost ( $account_info, $title, $message, $accessToken, $proxy, $tags = [] )
	{
		$sendData = [
			'title'         => $title,
			'contentFormat' => 'html',
			'content'       => '<h1>' . $title . '</h1>' . $message
		];

		if ( ! empty( $tags ) )
		{
			$sendData[ 'tags' ] = $tags;
		}

		if ( isset( $account_info[ 'screen_name' ] ) )
		{
			$endpoint = 'https://api.medium.com/v1/publications/' . $account_info[ 'node_id' ] . '/posts';
		}
		else
		{
			$endpoint = 'https://api.medium.com/v1/users/' . $account_info[ 'profile_id' ] . '/posts';
		}

		$result = self::cmd( $endpoint, 'POST', $accessToken, $sendData, $proxy );

		if ( isset( $result[ 'errors' ] ) && isset( $result[ 'errors' ][ 0 ][ 'message' ] ) )
		{
			$result2 = [
				'status'    => 'error',
				'error_msg' => $result[ 'errors' ][ 0 ][ 'message' ]
			];
		}
		else
		{
			$result2 = [
				'status' => 'ok',
				'id'     => isset( $result[ 'data' ][ 'id' ] ) ? $result[ 'data' ][ 'id' ] : 0
			];
		}

		return $result2;
	}

	/**
	 * @param string $cmd
	 * @param string $method
	 * @param string $accessToken
	 * @param array $data
	 * @param string $proxy
	 *
	 * @return mixed
	 */
	public static function cmd ( $cmd, $method, $accessToken, $data = [], $proxy = '' )
	{
		$url = $cmd;

		$method = $method === 'POST' ? 'POST' : ( $method === 'DELETE' ? 'DELETE' : 'GET' );

		$data1 = Curl::getContents( $url, $method, json_encode( $data ), [
			'Authorization'  => 'Bearer ' . $accessToken,
			'Content-Type'   => 'application/json',
			'Accept'         => 'application/json',
			'Accept-Charset' => 'utf-8'
		], $proxy, FALSE );

		$data = json_decode( $data1, TRUE );

		if ( ! is_array( $data ) )
		{
			$data = [
				'error' => [ 'message' => 'Error data!' ]
			];
		}

		return $data;
	}

	/**
	 * @param integer $appId
	 *
	 * @return string
	 */
	public static function getLoginURL ( $appId )
	{
		$state = md5( rand( 111111111, 911111111 ) );

		Session::set( 'app_id', $appId );
		Session::set( 'state', $state );
		Session::set( 'proxy', Request::get( 'proxy', '', 'string' ) );

		$appInf = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'medium' ] );
		if ( ! $appInf )
		{
			self::error( fsp__( 'Error! The App isn\'t found!' ) );
		}
		$appId = urlencode( $appInf[ 'app_id' ] );

		$callbackUrl = urlencode( self::callbackUrl() );

		return "https://medium.com/m/oauth/authorize?client_id={$appId}&response_type=code&redirect_uri={$callbackUrl}&scope=basicProfile,listPublications,publishPost&state=" . $state;
	}

	/**
	 * @return string
	 */
	public static function callbackURL ()
	{
		return site_url() . '/?medium_callback=1';
	}

	/**
	 * @return array
	 */
	public static function getAccessToken ()
	{
		$appId     = (int) Session::get( 'app_id' );
		$stateSess = Session::get( 'state' );

		if ( empty( $appId ) || empty( $stateSess ) )
		{
			return [
                'status'    => FALSE,
                'error_msg' => ''
            ];
		}

		$code  = Request::get( 'code', '', 'string' );
		$state = Request::get( 'state', '', 'string' );

		if ( empty( $code ) || $state != $stateSess )
		{
			$error_message = Request::get( 'error_message', '', 'str' );

            return [
                'status'    => FALSE,
                'error_msg' => $error_message
            ];
		}

		$proxy = Session::get( 'proxy' );

		Session::remove( 'app_id' );
		Session::remove( 'state' );
		Session::remove( 'proxy' );

		$appInf    = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'medium' ] );
		$appSecret = urlencode( $appInf[ 'app_secret' ] );
		$appId2    = urlencode( $appInf[ 'app_id' ] );

		$url = 'https://api.medium.com/v1/tokens';

		$postData = [
			'grant_type'    => 'authorization_code',
			'code'          => $code,
			'client_id'     => $appId2,
			'client_secret' => $appSecret,
			'redirect_uri'  => self::callbackURL(),
		];

		$headers = [
			'Content-Type'   => 'application/x-www-form-urlencoded',
			'Accept'         => 'application/json',
			'Accept-Charset' => 'utf-8'
		];

		$response = Curl::getContents( $url, 'POST', $postData, $headers, $proxy, TRUE );

		$params = json_decode( $response, TRUE );

		if ( isset( $params[ 'errors' ][ 0 ][ 'message' ] ) )
		{
            return [
                'status'    => FALSE,
                'error_msg' => $params[ 'errors' ][ 0 ][ 'message' ]
            ];
		}

		$access_token = esc_html( $params[ 'access_token' ] );
		$refreshToken = esc_html( $params[ 'refresh_token' ] );
		$expiresIn    = Date::dateTimeSQL( intval( $params[ 'expires_at' ] / 1000 ) );

		return self::authorizeMediumUser( $appId, $access_token, $refreshToken, $expiresIn, $proxy );
	}

	/**
	 * @param integer $appId
	 * @param string $accessToken
	 * @param string $refreshToken
	 * @param string $expiresIn
	 * @param string $proxy
	 */
	public static function authorizeMediumUser ( $appId, $accessToken, $refreshToken, $expiresIn, $proxy )
	{
		$me = self::cmd( 'https://api.medium.com/v1/me', 'GET', $accessToken, [], $proxy );

		if ( isset( $me[ 'errors' ][ 0 ][ 'message' ] ) )
		{
            return [
                'status'    => FALSE,
                'error_msg' => $me[ 'errors' ][ 0 ][ 'message' ]
            ];
		}

		$me = $me[ 'data' ];

		$meId = $me[ 'id' ];

		if ( ! get_current_user_id() > 0 )
		{
            return [
                'status'    => FALSE,
                'error_msg' => fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' )
            ];
		}

		$checkLoginRegistered = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'driver'     => 'medium',
			'username'   => $me[ 'username' ]
		] );

		$dataSQL = [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
			'name'        => $me[ 'name' ],
			'driver'      => 'medium',
			'profile_id'  => $meId,
			'profile_pic' => $me[ 'imageUrl' ],
			'username'    => $me[ 'username' ],
			'proxy'       => $proxy,
            'status'      => NULL,
            'error_msg'   => NULL
		];

		if ( ! $checkLoginRegistered )
		{
			DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );

			$accId = DB::DB()->insert_id;
		}
		else
		{
			$accId = $checkLoginRegistered[ 'id' ];

			DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $accId ] );

			DB::DB()->delete( DB::table( 'account_access_tokens' ), [ 'account_id' => $accId ]  );
		}

		// access token
		if ( ! empty( $appId ) )
		{
			$accessTokenDataSQL = [
				'account_id'    => $accId,
				'app_id'        => $appId,
				'access_token'  => $accessToken,
				'refresh_token' => $refreshToken,
				'expires_on'    => $expiresIn
			];
		}
		else
		{
			$accessTokenDataSQL = [
				'account_id'    => $accId,
				'access_token'  => $accessToken,
			];
		}

		DB::DB()->insert( DB::table( 'account_access_tokens' ), $accessTokenDataSQL );

		self::refetch_account( $accId, $meId, $accessToken, $proxy );

		if ( ! empty( $appId ) )
		{
			return [
                'status' => TRUE,
                'id'     => $accId
            ];
		}

        return [
            'status'    => FALSE,
            'error_msg' => ''
        ];
	}

	/**
	 * @param $tokenInfo
	 */
	public static function accessToken ( $tokenInfo )
	{
		if ( ! empty( $tokenInfo[ 'app_id' ] ) && ( ( Date::epoch() + 30 ) > Date::epoch( $tokenInfo[ 'expires_on' ] ) ) )
		{
			return self::refreshToken( $tokenInfo );
		}

		return $tokenInfo[ 'access_token' ];
	}

	/**
	 * @param array $tokenInfo
	 *
	 * @return string|array
	 */
	public static function refreshToken ( $tokenInfo )
	{
		$appId = $tokenInfo[ 'app_id' ];

		$account_info = DB::fetch( 'accounts', $tokenInfo[ 'account_id' ] );
		$proxy        = $account_info[ 'proxy' ];

		$appInf    = DB::fetch( 'apps', $appId );
		$appId2    = urlencode( $appInf[ 'app_id' ] );
		$appSecret = urlencode( $appInf[ 'app_secret' ] );

		$url = 'https://api.medium.com/v1/tokens';

		$postData = [
			'grant_type'    => 'refresh_token',
			'client_id'     => $appId2,
			'client_secret' => $appSecret,
			'refresh_token' => $tokenInfo[ 'refresh_token' ]
		];

		$headers  = [
			'Accept'         => 'application/json',
			'Accept-Charset' => 'utf-8'
		];

		$response = Curl::getContents( $url, 'POST', $postData, $headers, $proxy );
		$params   = json_decode( $response, TRUE );

		if ( isset( $params[ 'error' ][ 'message' ] ) )
		{
            return [
                'status'    => FALSE,
                'error_msg' => $params[ 'error' ][ 'message' ]
            ];
		}

		$access_token = esc_html( $params[ 'access_token' ] );
		$expiresIn    = Date::dateTimeSQL( 'now', '+' . (int) $params[ 'expires_at' ] . ' seconds' );

		DB::DB()->update( DB::table( 'account_access_tokens' ), [
			'access_token' => $access_token,
			'expires_on'   => $expiresIn
		], [ 'id' => $tokenInfo[ 'id' ] ] );

		return $access_token;
	}

	/**
	 * @param integer $post_id
	 * @param string $accessToken
	 *
	 * @return array
	 */
	public static function getStats ( $post_id, $accessToken, $proxy )
	{
		return [
			'comments' => 0,
			'like'     => 0,
			'shares'   => 0,
			'details'  => ''
		];
	}

	/**
	 * @param string $accessToken
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function checkAccount ( $accessToken, $proxy )
	{
		$result = [
			'error'     => TRUE,
			'error_msg' => NULL
		];
		$me     = self::cmd( 'https://api.medium.com/v1/me', 'GET', $accessToken, [], $proxy );

		if ( isset( $me[ 'errors' ][ 0 ][ 'message' ] ) )
		{
			$result[ 'error_msg' ] = $me[ 'errors' ][ 0 ][ 'message' ];
		}
		else
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}

	public static function refetch_account ( $account_id, $profile_id, $access_token, $proxy )
	{
		$publications = self::cmd( 'https://api.medium.com/v1/users/' . $profile_id . '/publications', 'GET', $access_token, [], $proxy );
		$get_nodes    = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $account_id ] ), ARRAY_A );
		$my_nodes     = [];

		foreach ( $get_nodes as $node )
		{
			$my_nodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
		}

		if ( isset( $publications[ 'data' ] ) && is_array( $publications[ 'data' ] ) )
		{
			foreach ( $publications[ 'data' ] as $publicationInf )
			{
				if ( ! in_array( $publicationInf[ 'id' ], $my_nodes ) )
				{
					DB::DB()->insert( DB::table( 'account_nodes' ), [
						'blog_id'     => Helper::getBlogId(),
						'user_id'     => get_current_user_id(),
						'driver'      => 'medium',
						'screen_name' => str_replace( 'https://medium.com/', '', $publicationInf[ 'url' ] ),
						'account_id'  => $account_id,
						'node_type'   => 'publication',
						'node_id'     => $publicationInf[ 'id' ],
						'name'        => $publicationInf[ 'name' ],
						'cover'       => $publicationInf[ 'imageUrl' ]
					] );
				}
				else
				{
					DB::DB()->update( DB::table( 'account_nodes' ), [
						'name'  => $publicationInf[ 'name' ],
						'cover' => $publicationInf[ 'imageUrl' ]
					], [
						'account_id' => $account_id,
						'node_id'    => $publicationInf[ 'id' ]
					] );
				}

				unset( $my_nodes[ array_search( $publicationInf[ 'id' ], $my_nodes ) ] );
			}
		}

		if ( ! empty( $my_nodes ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
		}

		return [ 'status' => TRUE ];
	}
}