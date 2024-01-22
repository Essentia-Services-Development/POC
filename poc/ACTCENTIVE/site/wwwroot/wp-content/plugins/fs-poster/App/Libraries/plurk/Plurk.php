<?php

namespace FSPoster\App\Libraries\plurk;

use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Curl;
use FSPoster\App\Providers\Helper;
use PHPMailer\PHPMailer\Exception;
use FSPoster\App\Providers\SocialNetwork;

class Plurk extends SocialNetwork
{
	const REQUEST_TOKEN_LINK  = 'https://www.plurk.com/OAuth/request_token';

	const ACCESS_TOKEN_LINK   = 'https://www.plurk.com/OAuth/access_token';

	const AUTH_APP_LINK       = "https://www.plurk.com/OAuth/authorize?oauth_token=";

	const ADD_PLURK_LINK      = 'https://www.plurk.com/APP/Timeline/plurkAdd';

	const GET_USER_INFO       = 'https://www.plurk.com/APP/Users/me';

	const UPLOAD_PICTURE_LINK = 'https://www.plurk.com/APP/Timeline/uploadPicture';

	const GET_PLURK_LINK      = 'https://www.plurk.com/APP/Timeline/getPlurk';

	const GET                 = 'GET';

	const POST                = 'POST';

	public $signatureMethod = 'HMAC-SHA1';
	public $version         = '1.0';
	public $consumer_key;
	public $consumer_secret;
	public $client;
	public $proxy;

	public function __construct ( $consumer_key, $consumer_secret, $proxy = '' )
	{
		$this->consumer_key    = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		$this->proxy           = $proxy;
		$this->client          = new Client( [
			'allow_redirects' => [ 'max' => 20 ],
			'proxy'           => empty( $proxy ) ? NULL : $proxy,
			'verify'          => FALSE,
			'http_errors'     => FALSE,
			'headers'         => [ 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0' ]
		] );
	}

	public function authorizePlurkUser ( $apiLink, $access_token, $access_token_secret )
	{
		$info_json = Curl::getURL( $apiLink, $this->proxy );
		$info      = json_decode( $info_json );

		if ( ! isset( $info->id ) )
		{
			Helper::response( FALSE );
		}

		if ( ! get_current_user_id() > 0 )
		{
			Helper::response( FALSE, fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' ) );
		}

		$checkLoginRegistered = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'driver'     => 'plurk',
			'profile_id' => $info->id
		] );

		$dataSQL = [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
			'name'        => $info->full_name,
			'username'    => $info->nick_name,
			'driver'      => 'plurk',
			'profile_id'  => $info->id,
			'proxy'       => $this->proxy,
			'profile_pic' => $info->avatar_big,
            'status'      => NULL,
            'error_msg'   => NULL
		];

		$checkApp = DB::fetch( 'apps', [ 'app_key' => $this->consumer_key, 'app_secret' => $this->consumer_secret ] );

		if ( ! isset( $checkApp[ 'id' ] ) )
		{
			Helper::response( FALSE );
		}

		$tokensDataSQL = [
			'app_id'              => $checkApp[ 'id' ],
			'access_token'        => $access_token,
			'access_token_secret' => $access_token_secret
		];

		if ( $checkLoginRegistered )
		{
			DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $checkLoginRegistered[ 'id' ] ] );
			DB::DB()->update( DB::table( 'account_access_tokens' ), $tokensDataSQL, [
				'account_id' => $checkLoginRegistered[ 'id' ],
				'app_id'     => $checkApp[ 'id' ]
			] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );
			$tokensDataSQL[ 'account_id' ] = DB::DB()->insert_id;
			DB::DB()->insert( DB::table( 'account_access_tokens' ), $tokensDataSQL );
		}
	}

	public function getToken ( $apiLink )
	{
		$info = Curl::getURL( $apiLink, $this->proxy );

		if ( strpos( $info, '=' ) === FALSE || strpos( $info, '&' ) === FALSE )
		{
			Helper::response( FALSE, fsp__( 'Couldn\'t get token' ) );
		}

		$auth_token = explode( '&', $info );
		$token      = explode( '=', $auth_token[ 0 ] )[ 1 ];
		$secret     = explode( '=', $auth_token[ 1 ] )[ 1 ];

		return [
			'token'  => $token,
			'secret' => $secret
		];
	}

	public function getApiLink ( $request_method, $apiLink, $oauthToken = '', $oauthTokenSecret = '', $verifier = '', $content = [] )
	{

		$defaults = [
			'oauth_consumer_key'     => $this->consumer_key,
			'oauth_nonce'            => mt_rand( 10000000, 99999999 ),
			'oauth_timestamp'        => date( 'U' ),
			'oauth_token'            => $oauthToken,
			'oauth_verifier'         => $verifier,
			'oauth_signature_method' => $this->signatureMethod,
			'oauth_version'          => $this->version
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

		$base = $request_method . '&' . rawurlencode( $apiLink ) . '&' . rawurlencode( $url_params_str );

		$key       = rawurlencode( $this->consumer_secret ) . '&' . rawurlencode( $oauthTokenSecret );
		$signature = rawurlencode( base64_encode( hash_hmac( 'SHA1', $base, $key, TRUE ) ) );

		return $apiLink . '?' . $url_params_str . '&oauth_signature=' . $signature;
	}

	public function getStats ( $accessToken, $accessTokenSecret, $plurkId )
	{
		$stats = [
			'comments' => 0,
			'like'     => 0,
			'shares'   => 0,
			'details'  => ''
		];

		$apiLink  = $this->getApiLink( self::GET, self::GET_PLURK_LINK, $accessToken, $accessTokenSecret, '', [ 'plurk_id' => $plurkId ] );
		$response = Curl::getURL( $apiLink, $this->proxy );
		$info     = json_decode( $response );

		if ( isset( $info->plurk ) )
		{
			$stats[ 'comments' ] = $info->plurk->response_count;
			$stats[ 'like' ]     = $info->plurk->favorite_count;
			$stats[ 'shares' ]   = $info->plurk->replurkers_count;
		}

		return $stats;
	}

	public static function callbackURL ()
	{
		return '-';
	}

	public function sendPost ( $accessToken, $accessTokenSecret, $sendType, $message, $qualifier, $autoCut, $link, $images = '' )
	{
		$messageLimit = 360;

		if ( $autoCut === '1' )
		{
			$len = mb_strlen( $message );
			if ( $len > $messageLimit )
			{
				$limit   = $messageLimit - mb_strlen( $link ) - 3;
				$message = mb_substr( $message, 0, $limit ) . '...';
			}
		}

		if ( $sendType === 'image' && ! empty( $images ) )
		{
			foreach ( $images as $image )
			{
				$msg = $message . "\n" . $image;

				if ( $autoCut === '1' && mb_strlen( $msg ) > $messageLimit )
				{
					break;
				}

				$message = $msg;
			}
		}
		else if ( $sendType === 'link' )
		{
			if ( $autoCut === '1' && mb_strlen( $link ) > $messageLimit )
			{
				$link = '';
			}
			$msg = $message . "\n" . $link;

			if ( $autoCut === '1' && mb_strlen( $msg ) > $messageLimit && $link !== '' )
			{
				$limit   = $messageLimit - mb_strlen( $link ) - 3;
				$message = mb_substr( $message, 0, $limit ) . '...';
				$message .= "\n" . $link;
			}
			else
			{
				$message = $msg;
			}
		}

		$postContent = [
			'content'   => $message,
			'qualifier' => $qualifier
		];

		$apiLink = $this->getApiLink( Plurk::GET, Plurk::ADD_PLURK_LINK, $accessToken, $accessTokenSecret, '', $content = $postContent );

		try
		{
			$response = Curl::getURL( $apiLink, $this->proxy );
			$info     = json_decode( $response );

			if ( isset( $info->plurk_id ) )
			{
				return [
					'status' => 'ok',
					'id'     => $info->plurk_id
				];
			}
			else
			{
				if ( ! empty( $info->error_text ) && $info->error_text === 'anti-flood-same-content' )
				{
					$error_msg = fsp__( 'It seems that you have recently shared this content. Duplicate content is not allowed in a short time.' );
				}
				else
				{
					$error_msg = ! empty( $info->error_text ) ? esc_html( $info->error_text ) : fsp__( 'Error! Couldn\'t share post!' );
				}

				return [
					'status'    => 'error',
					'error_msg' => $error_msg
				];
			}
		}
		catch ( Exception $e )
		{
			return [
				'status'    => 'error',
				'error_msg' => esc_html( $e->getMessage() )
			];
		}
	}
}