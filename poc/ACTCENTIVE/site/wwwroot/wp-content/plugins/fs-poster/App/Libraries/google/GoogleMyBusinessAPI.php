<?php

namespace FSPoster\App\Libraries\google;

use Exception;
use FSP_GuzzleHttp\Exception\BadResponseException;
use FSP_GuzzleHttp\Exception\GuzzleException;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\Session;
use FSPoster\App\Providers\SocialNetwork;

class GoogleMyBusinessAPI extends SocialNetwork
{
    public static function sendPost ( $app_id, $account_id, $profile_id, $type, $message, $link, $images, $video, $access_token, $proxy )
	{
		$app_info = DB::fetch( 'apps', [ 'id' => $app_id, 'driver' => 'google_b' ] );

		if ( ! $app_info )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! There isn\'t a Google Business Profile App!' )
			];
		}

		if ( Helper::getOption( 'gmb_autocut', '1' ) == 1 && mb_strlen( $message ) > 1500 )
		{
			$message = mb_substr( $message, 0, 1497 ) . '...';
		}

        $post  = [];
        $media = [];

        $post[ 'summary' ]     = $message;
        $post[ 'topicType' ]   = 'STANDARD';

        if ( $type === 'image' && ! empty( $images ) && is_array( $images ) )
        {
            foreach ( $images as $image )
            {
                $media[] = [
                    'mediaFormat' => 'PHOTO',
                    'sourceUrl'   => $image
                ];
            }
        }
        else if ( $type === 'video' && ! empty( $video ) && is_string( $video ) )
        {
            $media[] = [
                'mediaFormat' => 'VIDEO',
                'sourceUrl'   => $video
            ];
        }
        else if ( $type === 'link' )
        {
            $post[ 'callToAction' ] = [
                'actionType' => Helper::getOption( 'google_b_button_type', 'LEARN_MORE' ),
                'url'        => $link
            ];

            if ( ! empty( $images ) && is_array( $images ) )
            {
                $media[] = [
                    'mediaFormat' => 'PHOTO',
                    'sourceUrl'   => reset( $images )
                ];
            }
        }

        if ( ! empty( $media ) )
        {
            $post[ 'media' ] = $media;
        }

        $posted = self::cmd( 'POST', 'https://mybusiness.googleapis.com/v4/' . $account_id . '/' . $profile_id . '/localPosts', $proxy, $access_token, '', json_encode( $post ) );

        if ( isset( $posted[ 'status' ] ) && $posted[ 'status' ] === 'error' )
        {
            return $posted;
        }

        if ( isset( $posted[ 'state' ] ) && $posted[ 'state' ] === 'REJECTED' )
        {
            return [
                'status'    => 'error',
                'error_msg' => fsp__( 'Error! The post rejected by Google Business Profile!' )
            ];
        }

        $post_link   = isset( $posted[ 'searchUrl' ] ) ? $posted[ 'searchUrl' ] : '';
        $parsed_link = parse_url( $post_link );
        parse_str( $parsed_link[ 'query' ], $params );

        return [
            'status' => 'ok',
            'id'     => $params[ 'lpsid' ] . '&id=' . $params[ 'id' ]
        ];
	}

    public static function cmd ( $method, $url, $proxy, $accessToken, $data = [], $body = '' )
    {
        $options  = [];

        $method = strtoupper( $method ) === 'GET' ? 'GET' : 'POST';

        if ( ! empty( $proxy ) )
        {
            $options[ 'proxy' ] = $proxy;
        }

        if ( ! empty( $body ) )
        {
            $body              = is_array( $body ) ? json_encode( $body ) : $body;
            $options[ 'body' ] = $body;
        }

        if ( ! empty( $data ) )
        {
            $options[ 'query' ] = $data;
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
                $error_msg = fsp__( 'You need to verify your locations to share posts on it' );
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

		$app_info = DB::fetch( 'apps', [ 'id' => $app_id, 'driver' => 'google_b' ] );

		$authURL = 'https://accounts.google.com/o/oauth2/auth';

        $scopes = [
            'https://www.googleapis.com/auth/business.manage',
            'https://www.googleapis.com/auth/userinfo.profile',
            'email',
            'profile'
        ];

        $params = [
            'response_type' => 'code',
            'access_type'   => 'offline',
            'client_id'     => $app_info['app_id'],
            'redirect_uri'  => self::callbackURL(),
            'state'         => NULL,
            'scope'         => implode( ' ', $scopes ),
            'prompt'        => 'consent'
        ];

		return $authURL . '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
	}

	public static function callbackURL ()
	{
		return site_url() . '/?google_b_callback=1';
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

		$appInfo = DB::fetch( 'apps', [ 'id' => $app_id, 'driver' => 'google_b' ] );

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

		return self::authorize( $appInfo, $tokenInfo[ 'access_token' ], $tokenInfo[ 'refresh_token' ], $proxy );
	}

	public static function authorize ( $app_info, $access_token, $refresh_token, $proxy )
	{
        $profile = self::cmd( 'GET', 'https://www.googleapis.com/oauth2/v3/userinfo', $proxy, $access_token );
        $picture = isset( $profile[ 'picture' ] ) ? $profile[ 'picture' ] : NULL;

        try
		{
			$accounts = self::cmd( 'GET', 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts', $proxy, $access_token );

            if ( ! empty( $accounts[ 'status' ] ) )
            {
                return $accounts;
            }

            $accounts = empty( $accounts[ 'accounts' ] ) ? [] : $accounts[ 'accounts' ];

			foreach ( $accounts as $account )
			{
				$id          = $account[ 'name' ];
				$name        = isset( $account[ 'accountName' ] ) && ! empty( $account[ 'accountName' ] ) ? esc_html( $account[ 'accountName' ] ) : '-';

				$checkUserExist = DB::fetch( 'accounts', [
					'blog_id'    => Helper::getBlogId(),
					'user_id'    => get_current_user_id(),
					'driver'     => 'google_b',
					'profile_id' => $id
				] );

				if ( ! get_current_user_id() > 0 )
				{
                    return [
                        'status'    => FALSE,
                        'error_msg' => fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' )
                    ];
				}

				$dataSQL = [
					'blog_id'     => Helper::getBlogId(),
					'user_id'     => get_current_user_id(),
					'driver'      => 'google_b',
					'name'        => $name,
					'profile_id'  => $id,
					'email'       => '',
					'username'    => $name,
					'profile_pic' => $picture,
					'proxy'       => $proxy,
                    'status'      => NULL,
                    'error_msg'   => NULL
				];

				if ( $checkUserExist )
				{
					$accId = $checkUserExist[ 'id' ];

					DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $accId ] );
					DB::DB()->delete( DB::table( 'account_access_tokens' ), [ 'account_id' => $accId ] );
				}
				else
				{
					DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );
					$accId = DB::DB()->insert_id;
				}

				DB::DB()->insert( DB::table( 'account_access_tokens' ), [
					'account_id'    => $accId,
					'app_id'        => $app_info[ 'id' ],
					'access_token'  => $access_token,
					'refresh_token' => $refresh_token,
					'expires_on'    => Date::dateTimeSQL( 'now', '+55 minutes' )
				] );

				self::refetch_account( $access_token, $accId, $id, $proxy);
			}

            return [
                'status'    => TRUE
            ];
		}
		catch ( Exception $e )
		{
            return [
                'status'    => FALSE,
                'error_msg' => $e->getMessage()
            ];
		}
	}

	public static function getStats ( $post_id, $accessToken, $accessTokenSecret, $appId, $proxy )
	{
		return [
			'comments' => 0,
			'like'     => 0,
			'shares'   => 0,
			'details'  => ''
		];
	}

	public static function checkAccount ( $account, $access_token, $proxy )
	{
        $account = self::cmd( 'GET', 'https://mybusinessaccountmanagement.googleapis.com/v1/' . $account, $proxy, $access_token );
        $result   = [
			'error'     => TRUE,
			'error_msg' => NULL
		];

        if ( isset( $account[ 'status' ] ) && $account[ 'status' ] === 'error' )
        {
            $error_msg = isset( $account[ 'error_msg' ] ) ? $account[ 'error_msg' ] : '';
            $result[ 'error_msg' ] = fsp__( 'Error! %s', [ esc_html( $error_msg ) ] );
        }
        else if ( isset( $account[ 'name' ] ) )
        {
            $result[ 'error' ] = FALSE;
        }

		return $result;
	}

	public static function refetch_account ( $accessToken, $account_id, $profile_id, $proxy )
	{
        $get_nodes = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $account_id ] ), ARRAY_A );
        $my_nodes  = [];

        foreach ( $get_nodes as $node )
        {
            $my_nodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
        }

        do
        {
            $queryData = [
                'readMask' => 'title,name,categories.primaryCategory.displayName',
                'pageSize' => 100
            ];

            if ( ! empty( $nextPages ) )
            {
                $queryData[ 'pageToken' ] = $nextPages;
            }

            $response = self::cmd( 'GET', 'https://mybusinessbusinessinformation.googleapis.com/v1/' . $profile_id . '/locations', $proxy, $accessToken, $queryData );

            $locations = empty( $response[ 'locations' ] ) ? [] : $response[ 'locations' ];
            $nextPages = empty( $response[ 'nextPageToken' ] ) ? FALSE : $response[ 'nextPageToken' ];

            foreach ( $locations as $location )
            {
                if ( ! in_array( $location[ 'name' ], $my_nodes ) )
                {
                    DB::DB()->insert( DB::table( 'account_nodes' ), [
                        'blog_id'    => Helper::getBlogId(),
                        'user_id'    => get_current_user_id(),
                        'driver'     => 'google_b',
                        'account_id' => $account_id,
                        'node_type'  => 'location',
                        'node_id'    => $location[ 'name' ],
                        'name'       => $location[ 'title' ],
                        'category'   => isset( $location[ 'categories' ][ 'primaryCategory' ][ 'displayName' ] ) ? $location[ 'categories' ][ 'primaryCategory' ][ 'displayName' ] : ''
                    ] );
                }
                else
                {
                    DB::DB()->update( DB::table( 'account_nodes' ), [
                        'name' => $location[ 'title' ]
                    ], [
                        'account_id' => $account_id,
                        'node_id'    => $location[ 'name' ]
                    ] );
                }

                unset( $my_nodes[ array_search( $location[ 'name' ], $my_nodes ) ] );
            }
        } while ( ! empty( $nextPages ) );

        if ( ! empty( $my_nodes ) )
        {
            DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
            DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
        }


		return [ 'status' => TRUE ];
	}

	public static function accessToken ( $token_info )
	{
		if ( ( Date::epoch() + 30 ) > Date::epoch( $token_info[ 'expires_on' ] ) )
		{
			return self::refreshToken( $token_info );
		}

		return $token_info[ 'access_token' ];
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

		$access_token = isset( $refreshed_token[ 'access_token' ] ) ? $refreshed_token[ 'access_token' ] : '';

		DB::DB()->update( DB::table( 'account_access_tokens' ), [
			'access_token' => $access_token,
			'expires_on'   => Date::dateTimeSQL( 'now', '+55 minutes' )
		], [ 'id' => $token_info[ 'id' ] ] );

		return $access_token;
	}
}
