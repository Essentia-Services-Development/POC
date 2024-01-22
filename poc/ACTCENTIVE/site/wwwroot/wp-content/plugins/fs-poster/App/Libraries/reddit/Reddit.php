<?php

namespace FSPoster\App\Libraries\reddit;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\Session;
use FSP_GuzzleHttp\Psr7\MultipartStream;
use FSPoster\App\Providers\SocialNetwork;

class Reddit extends SocialNetwork
{
	/**
	 * @param array $account_info
	 * @param string $type
	 * @param string $title
	 * @param string $message
	 * @param string $link
	 * @param array $images
	 * @param string $video
	 * @param string $accessToken
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function sendPost ( $account_info, $username, $type, $title, $message, $comment, $canSendComment, $link, $images, $video, $accessToken, $proxy, $pMethod )
	{
		$endpoint = 'https://oauth.reddit.com/api/submit';
		$body     = NULL;

		if ( $pMethod !== 2 )
		{
			$sendData[ 'title' ] = $message;
		}

		if ( Helper::getOption( 'reddit_autocut_title', '1' ) == 1 && mb_strlen( $sendData[ 'title' ] ) > 300 )
		{
			$sendData[ 'title' ] = mb_substr( $sendData[ 'title' ], 0, 297 ) . '...';
		}

		if ( isset( $account_info[ 'screen_name' ] ) )
		{
			$sendData[ 'sr' ] = $account_info[ 'screen_name' ];

			if ( ! empty( $account_info[ 'access_token' ] ) )
			{
				$sendData[ 'flair_text' ] = $account_info[ 'category' ];
				$sendData[ 'flair_id' ]   = $account_info[ 'access_token' ];
			}
		}
		else
		{
			$sendData[ 'sr' ]          = 'u_' . $account_info[ 'username' ];
			$sendData[ 'submit_type' ] = 'profile';
		}

		if ( $type === 'image' && count( $images ) == 1 )
		{
			$imageurl = self::uploadImage( reset( $images ), $accessToken, $proxy );

			if ( $imageurl === FALSE )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Failed to upload the image!' )
				];
			}

			if ( ! empty( $message ) )
			{
				$sendData[ 'text' ] = $message;
			}

			$sendData[ 'title' ]        = $title;
			$sendData[ 'resubmit' ]     = 'true';
			$sendData[ 'send_replies' ] = 'true';
			$sendData[ 'api_type' ]     = 'json';
			$sendData[ 'kind' ]         = 'image';
			$sendData[ 'url' ]          = $imageurl;
		}
		else if ( $type === 'image' && count( $images ) > 1 )
		{
			$endpoint                         = 'https://oauth.reddit.com/api/submit_gallery_post.json?raw_json=1';
			$sendData[ 'title' ]              = $title;
			$sendData[ 'send_replies' ]       = TRUE;
			$sendData[ 'api_type' ]           = 'json';
			$sendData[ 'kind' ]               = 'self';
			$sendData[ 'show_error_list' ]    = TRUE;
			$sendData[ 'spoiler' ]            = FALSE;
			$sendData[ 'nsfw' ]               = FALSE;
			$sendData[ 'original_content' ]   = FALSE;
			$sendData[ 'post_to_twitter' ]    = FALSE;
			$sendData[ 'sendreplies' ]        = TRUE;
			$sendData[ 'validate_on_submit' ] = TRUE;

			if ( ! empty( $message ) )
			{
				$sendData[ 'text' ] = $message;
			}

			foreach ( $images as $image )
			{
				$imageurl = self::uploadImage( $image, $accessToken, $proxy );

				if ( $imageurl !== FALSE )
				{
					$imageID = explode( '/', $imageurl );

					if ( $imageID !== FALSE )
					{
						$sendData[ 'items' ][] = [
							'caption'      => '',
							'outbound_url' => '',
							'media_id'     => end( $imageID )
						];
					}
				}
			}

			$body     = json_encode( $sendData );
			$sendData = NULL;
		}
		else if ( $type === 'video' )
		{
			$sendData[ 'kind' ] = 'video';
			$sendData[ 'url' ]  = $video;
		}
		else if ( $pMethod === 1 )
		{
			$sendData[ 'kind' ] = 'link';
			$sendData[ 'url' ]  = $link;
		}
		else if ( $pMethod === 2 )
		{
			$sendData[ 'kind' ] = 'self';
			$sendData[ 'text' ] = $message;
			$sendData[ 'title' ] = $title;
		}

		$result = self::cmd( $endpoint, 'POST', $accessToken, $proxy, $sendData, $body );

		if ( isset( $result[ 'error' ] ) && isset( $result[ 'error' ][ 'message' ] ) )
		{
			$result2 = [
				'status'    => 'error',
				'error_msg' => esc_html( $result[ 'error' ][ 'message' ] )
			];
		}
		else if ( isset( $result[ 'json' ][ 'errors' ] ) && is_array( $result[ 'json' ][ 'errors' ] ) && ! empty( $result[ 'json' ][ 'errors' ] ) )
		{
			$error = reset( $result[ 'json' ][ 'errors' ] );
			$error = self::getErrorMessage( $error );

			$result2 = [
				'status'    => 'error',
				'error_msg' => esc_html( $error )
			];
		}
		else if ( isset( $result[ 'jquery' ], $result[ 'success' ] ) )
		{
			$result2 = [
				'status'    => 'error',
				'error_msg' => fsp__( 'Unknown error!' )
			];

			if ( $result[ 'success' ] === TRUE )
			{
                preg_match( '/comments\/(.+?)\//', json_encode($result, JSON_UNESCAPED_SLASHES), $matches );

                if ( ! empty( $matches[ 1 ] ) )
                {
                    $result2 = [
                        'status' => 'ok',
                        'id'     => $matches[ 1 ]
                    ];
                }
			}
			else
			{
				if ( ! empty( $result[ 'jquery' ][ 22 ][ 3 ][ 0 ] ) && is_string( $result[ 'jquery' ][ 22 ][ 3 ][ 0 ] ) )
				{
					$result2[ 'error_msg' ] = $result[ 'jquery' ][ 22 ][ 3 ][ 0 ];
				}
			}
		}
		else if ( ! isset( $result[ 'json' ] ) )
		{
			if ( isset( $result[ 'error' ] ) && $result[ 'error' ] == 403 )
			{
				$result2_error_text = fsp__( 'It seems that your account is banned by Reddit or you do not have permission to share posts on Reddit' );
			}
			else if ( ! empty( $result[ 'error' ] ) && ! empty( $result[ 'message' ] ) )
			{
				$result2_error_text = $result[ 'message' ];
			}
			else
			{
				$result2_error_text = fsp__( 'Error result!' ) . esc_html( json_encode( $result ) );
			}
			$result2 = [
				'status'    => 'error',
				'error_msg' => $result2_error_text
			];
		}
		else
		{
			if ( empty( $result[ 'json' ][ 'data' ][ 'id' ] ) )
			{
				sleep( 10 );
				$id = self::getLastPostID( $username, $accessToken, $proxy );
			}
			else
			{
				$id = $result[ 'json' ][ 'data' ][ 'id' ];
			}

			if ( strpos( $id, '_' ) !== FALSE )
			{
				$id1 = explode( '_', $id );

				if ( isset( $id1[ 1 ] ) )
				{
					$id = $id1[ 1 ];
				}
			}

			$commentThingId = empty( $result[ 'json' ][ 'data' ][ 'name' ] ) ? NULL : $result[ 'json' ][ 'data' ][ 'name' ];

			$result2 = [
				'status' => 'ok',
				'id'     => esc_html( $id ),
			];
		}

		if ( ! empty( $comment ) && $canSendComment && ! empty( $result2[ 'id' ] ) )
		{
			$thingID = empty($commentThingId) ? ( 't3_' . $result2[ 'id' ] ) : $commentThingId;
			$result2[ 'comment' ] = self::writeComment( $comment, $thingID, $accessToken, $proxy );
		}

		return $result2;
	}

	static function uploadImage ( $image, $accessToken, $proxy )
	{
		$res = self::cmd( 'https://oauth.reddit.com/api/media/asset.json?raw_json=1', 'POST', $accessToken, $proxy, [
			'api_type' => 'json',
			'filepath' => basename( $image ),
			'mimetype' => Helper::mimeContentType( $image )
		] );

		if ( ! isset( $res[ 'args' ][ 'fields' ], $res[ 'args' ][ 'action' ] ) )
		{
			return FALSE;
		}

		$uploadData = [];

		foreach ( $res[ 'args' ][ 'fields' ] as $field )
		{
			if ( ! isset( $field[ 'name' ], $field[ 'value' ] ) )
			{
				return FALSE;
			}

			$uploadData[] = [
				'name'     => $field[ 'name' ],
				'contents' => $field[ 'value' ]
			];
		}

		$uploadData[] = [
			'name'     => 'file',
			'filename' => 'blob',
			'contents' => file_get_contents( $image ),
			'headers'  => [ 'Content-Type' => Helper::mimeContentType( $image ) ]
		];

		$body = new MultipartStream( $uploadData, '----WebKitFormBoundaryo1KdMBb4Cj4G8xhU' );

		$uploadURL = trim( $res[ 'args' ][ 'action' ], '/' );

		if ( strpos( $uploadURL, 'https' ) === FALSE )
		{
			$uploadURL = 'https://' . $uploadURL;
		}

		$c = new Client();
		try
		{
			$uploaded = $c->post( $uploadURL, [
				'proxy'   => empty( $proxy ) ? NULL : $proxy,
				'headers' => [
					'Content-Length' => strlen( $body ),
					'User-Agent'     => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36',
					'Origin'         => 'https://www.reddit.com',
					'Referer'        => 'https://www.reddit.com/',
					'Content-Type'   => 'multipart/form-data; boundary=----WebKitFormBoundaryo1KdMBb4Cj4G8xhU'
				],
				'body'    => $body
			] )->getHeaders();
		}
		catch ( Exception $e )
		{
			return FALSE;
		}

		if ( ! isset( $uploaded[ 'Location' ][ 0 ] ) )
		{
			return FALSE;
		}

		return urldecode( $uploaded[ 'Location' ][ 0 ] );
	}

	static function getLastPostID ( $username, $accessToken, $proxy )
	{
		$c = new Client();

		try
		{
			$get = $c->get( sprintf( 'https://www.reddit.com/user/%s', $username ), [
				'headers' => [
					'Authorization' => 'bearer ' . $accessToken,
					'User-Agent'    => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36'
				],
				'proxy'   => empty( $proxy ) ? NULL : $proxy
			] )->getBody()->getContents();

			if ( ! preg_match( '/comments\/(.+?)\//', $get, $matches ) )
			{
				return '';
			}

			if ( empty( $matches[ 1 ] ) )
			{
				return '';
			}

			return $matches[ 1 ];
		}
		catch ( Exception $e )
		{
			return FALSE;
		}
	}

	/**
	 * @param string $comment
	 * @param string $mediaId
	 * @param string $accessToken
	 *
	 * @return array|string[]|string[][]
	 */
	public static function writeComment ( $comment, $mediaId, $accessToken, $proxy )
	{
		$sendData = [
			'api_type' => 'json',
			'thing_id' => $mediaId,
			'text'     => $comment,
		];

		$response = self::cmd( 'https://oauth.reddit.com/api/comment', 'POST', $accessToken, $proxy, $sendData );

		if ( isset( $response[ 'error' ] ) )
		{
			$error = isset( $response[ 'error' ][ 'message' ] ) ? $response[ 'error' ][ 'message' ] : ( isset( $response[ 'message' ] ) ? $response[ 'message' ] : $response[ 'error' ] );

			return [
				'error' => $error
			];
		}

		if ( ! isset( $response[ 'json' ] ) )
		{
			return [
				'error' => fsp__( 'Unknown error' )
			];
		}

		$response = $response[ 'json' ];

		if ( isset( $response[ 'errors' ] ) && ! empty( $response[ 'errors' ] ) )
		{
			return [
				'error' => fsp__( 'Unknown error' )
			];
		}

		if ( ! isset( $response[ 'data' ] ) && ! isset( $response[ 'data' ][ 'things' ] ) && ! isset( $response[ 'data' ][ 'things' ][ 0 ] ) && ! isset( $response[ 'data' ][ 'things' ][ 0 ][ 'data' ] ) )
		{
			return [
				'error' => fsp__( 'Unknown error' )
			];
		}

		$response = $response[ 'data' ][ 'things' ][ 0 ][ 'data' ];

		if ( ! empty( $response[ 'permalink' ] ) )
		{
			$url = str_replace( "/r/u_", "", $response[ 'permalink' ] );

			return [
				'url' => sprintf( "https://www.reddit.com/user/%s", $url )
			];
		}

		return [
			'error' => fsp__( 'Unknown error' )
		];
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
	public static function cmd ( $url, $method, $accessToken, $proxy, $data = NULL, $body = NULL, $isAuthTypeBearer = TRUE )
	{
		$method = strtolower( $method ) === 'post' ? 'post' : 'get';

		$c = new Client();

		$authType = $isAuthTypeBearer ? 'bearer' : 'Basic';

		$options = [
			'headers'     => [
				'Authorization' => sprintf( '%s %s', $authType, $accessToken ),
				'User-Agent'    => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36'
			],
			'form_params' => $data,
			'body'        => $body,
			'proxy'       => empty( $proxy ) ? NULL : $proxy
		];

		if ( ! empty( $body ) )
		{
			$options[ 'headers' ][ 'Content-Type' ] = 'application/json';
		}

		try
		{
			$result = $c->$method( $url, $options )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			if ( ! method_exists( $e, 'getResponse' ) )
			{
				return [
					'error' => [ 'message' => $e->getMessage() ]
				];
			}

			$resp = $e->getResponse();

			if ( is_null( $resp ) || ! method_exists( $resp, 'getBody' ) )
			{
				return [
					'error' => [ 'message' => 'Unknown error!' ]
				];
			}

			$result = $resp->getBody()->getContents();
		}

		$result_arr = json_decode( $result, TRUE );

		if ( ! is_array( $result_arr ) )
		{
			$result_arr = [
				'error' => [ 'message' => 'Error data!' ]
			];
		}

		return $result_arr;
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

		$appInf = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'reddit' ] );

		if ( ! $appInf )
		{
			self::error( fsp__( 'Error! The App isn\'t found!' ) );
		}

		$appId       = urlencode( $appInf[ 'app_id' ] );
		$callbackUrl = urlencode( self::callbackUrl() );

		return sprintf( 'https://www.reddit.com/api/v1/authorize?client_id=%s&response_type=code&redirect_uri=%s&duration=permanent&scope=identity,submit,flair,read&state=%s', $appId, $callbackUrl, $state );
	}

	/**
	 * @return string
	 */
	public static function callbackURL ()
	{
		return site_url() . '/?reddit_callback=1';
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

		$appInf    = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'reddit' ] );
		$appSecret = urlencode( $appInf[ 'app_secret' ] );
		$appId2    = urlencode( $appInf[ 'app_id' ] );

		$url = 'https://www.reddit.com/api/v1/access_token';

		$postData = [
			'grant_type'   => 'authorization_code',
			'code'         => $code,
			'redirect_uri' => self::callbackURL(),
		];

		$response = self::cmd( $url, 'POST', base64_encode( $appId2 . ':' . $appSecret ), $proxy, $postData, NULL, FALSE );

		if ( isset( $response[ 'error' ][ 'message' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => $response[ 'error' ][ 'message' ]
			];
		}

		$access_token = esc_html( $response[ 'access_token' ] );
		$refreshToken = esc_html( $response[ 'refresh_token' ] );
		$expiresIn    = Date::dateTimeSQL( 'now', '+' . (int) $response[ 'expires_in' ] . ' seconds' );

		return self::authorize( $appId, $access_token, $refreshToken, $expiresIn, $proxy );
	}

	/**
	 * @param integer $appId
	 * @param string $accessToken
	 * @param string $refreshToken
	 * @param string $expiresIn
	 * @param string $proxy
	 */
	public static function authorize ( $appId, $accessToken, $refreshToken, $expiresIn, $proxy )
	{
		$me = self::cmd( 'https://oauth.reddit.com/api/v1/me', 'GET', $accessToken, $proxy );

		if ( isset( $me[ 'error' ] ) && isset( $me[ 'error' ][ 'message' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => $me[ 'error' ][ 'message' ]
			];
		}

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
			'driver'     => 'reddit',
			'profile_id' => $meId
		] );

		$dataSQL = [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
			'name'        => isset( $me[ 'subreddit' ][ 'title' ] ) && ! empty( $me[ 'subreddit' ][ 'title' ] ) ? $me[ 'subreddit' ][ 'title' ] : $me[ 'name' ],
			'driver'      => 'reddit',
			'profile_id'  => $meId,
			'profile_pic' => $me[ 'icon_img' ],
			'username'    => $me[ 'name' ],
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
			DB::DB()->delete( DB::table( 'account_access_tokens' ), [ 'account_id' => $accId, 'app_id' => $appId ] );
		}

		DB::DB()->insert( DB::table( 'account_access_tokens' ), [
			'account_id'    => $accId,
			'app_id'        => $appId,
			'access_token'  => $accessToken,
			'refresh_token' => $refreshToken,
			'expires_on'    => $expiresIn
		] );

		return [
			'status' => TRUE,
			'id'     => $meId
		];
	}

	/**
	 * @param $tokenInfo
	 *
	 * @return false|string
	 */
	public static function accessToken ( $tokenInfo )
	{
		if ( ( Date::epoch() + 30 ) > Date::epoch( $tokenInfo[ 'expires_on' ] ) )
		{
			return self::refreshToken( $tokenInfo );
		}

		return $tokenInfo[ 'access_token' ];
	}

	/**
	 * @param array $tokenInfo
	 *
	 * @return string
	 */
	public static function refreshToken ( $tokenInfo )
	{
		$appId = $tokenInfo[ 'app_id' ];

		$account_info = DB::fetch( 'accounts', $tokenInfo[ 'account_id' ] );
		$proxy        = $account_info[ 'proxy' ];

		$appInf    = DB::fetch( 'apps', $appId );
		$appId2    = urlencode( $appInf[ 'app_id' ] );
		$appSecret = urlencode( $appInf[ 'app_secret' ] );

		$url = 'https://www.reddit.com/api/v1/access_token';

		$postData = [
			'grant_type'    => 'refresh_token',
			'refresh_token' => $tokenInfo[ 'refresh_token' ]
		];

		$response = self::cmd( $url, 'POST', base64_encode( $appId2 . ':' . $appSecret ), $proxy, $postData, NULL, FALSE );

		if ( isset( $response[ 'error' ][ 'message' ] ) )
		{
			return FALSE;
		}

		$access_token = esc_html( $response[ 'access_token' ] );
		$expiresIn    = Date::dateTimeSQL( 'now', '+' . (int) $response[ 'expires_in' ] . ' seconds' );

		DB::DB()->update( DB::table( 'account_access_tokens' ), [
			'access_token' => $access_token,
			'expires_on'   => $expiresIn
		], [ 'id' => $tokenInfo[ 'id' ] ] );

		$tokenInfo[ 'access_token' ] = $access_token;
		$tokenInfo[ 'expires_on' ]   = $expiresIn;

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
		$me     = self::cmd( 'https://oauth.reddit.com/api/v1/me', 'GET', $accessToken, $proxy );

		if ( isset( $me[ 'error' ] ) && isset( $me[ 'error' ][ 'message' ] ) )
		{
			$result[ 'error_msg' ] = $me[ 'error' ][ 'message' ];
		}
		else if ( ! isset( $me[ 'error' ] ) )
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}

	public static function getErrorMessage ( $error )
	{
		$reddit_errors = [
			'NO_URL'   => fsp__( 'Required URL (or featured image path) not found!' ),
			'TOO_LONG' => fsp__( 'Title is too long. Maximum allowed character limit is 300. You can enable auto-cut option from Reddit settings.' ),
			'NO_TEXT'  => fsp__( 'Content can\'t be empty!' )
		];

		if ( isset( $error[ 0 ] ) && ! empty( $error[ 0 ] ) && array_key_exists( $error[ 0 ], $reddit_errors ) )
		{
			return $reddit_errors[ $error[ 0 ] ];
		}
		else
		{
			return json_encode( $error );
		}
	}

	public static function refetch_account ( $account_id, $access_token, $proxy )
	{
		return [];
	}
}