<?php

namespace FSPoster\App\Pages\Accounts\Controllers;

use FSP_GuzzleHttp\Client;
use FSPoster\App\Libraries\discord\Discord;
use FSPoster\App\Libraries\planly\Planly;
use FSPoster\App\Libraries\threads\Threads;
use FSPoster\App\Libraries\xing\Xing;
use FSPoster\App\Libraries\youtube\YoutubeCommunity;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Libraries\vk\Vk;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;
use PHPMailer\PHPMailer\Exception;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\CatWalker;
use FSPoster\App\Libraries\fb\Facebook;
use FSPoster\App\Libraries\plurk\Plurk;
use FSPoster\App\Libraries\reddit\Reddit;
use FSPoster\App\Libraries\tumblr\Tumblr;
use FSPoster\App\Libraries\medium\Medium;
use FSPoster\App\Libraries\blogger\Blogger;
use FSPoster\App\Libraries\ok\OdnoKlassniki;
use FSPoster\App\Libraries\telegram\Telegram;
use FSPoster\App\Libraries\linkedin\Linkedin;
use FSPoster\App\Libraries\wordpress\Wordpress;
use FSPoster\App\Libraries\pinterest\Pinterest;
use FSPoster\App\Libraries\fb\FacebookCookieApi;
use FSPoster\App\Libraries\instagram\InstagramApi;
use FSPoster\App\Libraries\google\GoogleMyBusiness;
use FSPoster\App\Libraries\twitter\TwitterPrivateAPI;
use FSPoster\App\Libraries\google\GoogleMyBusinessAPI;
use FSPoster\App\Libraries\pinterest\PinterestCookieApi;
use FSPoster\App\Libraries\instagram\InstagramAppMethod;
use FSPoster\App\Libraries\tumblr\TumblrLoginPassMethod;
use FSPoster\App\Libraries\instagram\InstagramCookieMethod;
use FSPoster\App\Libraries\instagram\InstagramLoginPassMethod;

trait Ajax
{
	public function add_new_plurk_account ()
	{
		$app_key            = Request::post( 'app' );
		$requestToken       = Request::post( 'requestToken' );
		$requestTokenSecret = Request::post( 'requestTokenSecret' );
		$verifier           = Request::post( 'verifier' );
		$proxy              = Request::post( 'proxy' );

		$app = DB::fetch( 'apps', [ 'app_key' => $app_key ] );

		if ( $app )
		{
			$plurk        = new Plurk( $app[ 'app_key' ], $app[ 'app_secret' ], $proxy );
			$request_link = $plurk->getApiLink( Plurk::GET, Plurk::ACCESS_TOKEN_LINK, $requestToken, $requestTokenSecret, $verifier );
			$access_token = $plurk->getToken( $request_link );

			$apiLink = $plurk->getApiLink( Plurk::GET, Plurk::GET_USER_INFO, $access_token[ 'token' ], $access_token[ 'secret' ] );
			$plurk->authorizePlurkUser( $apiLink, $access_token[ 'token' ], $access_token[ 'secret' ] );
			Helper::response( TRUE );
		}
	}

	public function get_plurk_authorization_link ()
	{
		$app_key = Request::post( 'app' );
		$app     = DB::fetch( 'apps', [ 'app_key' => $app_key ] );

		if ( $app )
		{
			$plurk         = new Plurk( $app[ 'app_key' ], $app[ 'app_secret' ] );
			$request_link  = $plurk->getApiLink( Plurk::GET, Plurk::REQUEST_TOKEN_LINK );
			$request_token = $plurk->getToken( $request_link );
			$auth_link     = Plurk::AUTH_APP_LINK . $request_token[ 'token' ];
			Helper::response( TRUE, [ 'link' => $auth_link, 'request_token' => $request_token ] );
		}
	}

	public function add_new_fb_account_with_cookie ()
	{
		$cookieCuser = Request::post( 'cookie_c_user', '', 'string' );
		$cookieXs    = Request::post( 'cookie_xs', '', 'string' );
		$proxy       = Request::post( 'proxy', '', 'string' );

		$fb   = new FacebookCookieApi( $cookieCuser, $cookieXs, $proxy );
		$data = $fb->authorizeFbUser();

		if ( $data === FALSE )
		{
			Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
		}

		Helper::response( TRUE );
	}

	public function update_fb_account_cookie ()
	{
		$id       = Request::post( 'account_id', '', 'string' );
		$cookieXs = Request::post( 'cookie_xs', '', 'string' );
		$proxy    = Request::post( 'proxy', '', 'string' );

		$fbUser = DB::fetch( 'accounts', [
			'blog_id' => Helper::getBlogId(),
			'driver'  => 'fb',
			'id'      => $id
		] );

		if ( $fbUser )
		{
			$cookieCuser = $fbUser[ 'profile_id' ];
			$is_owner    = $fbUser[ 'user_id' ] == get_current_user_id();
			$is_public   = (bool) $fbUser[ 'is_public' ];
			$can_update  = $is_owner || $is_public;

			if ( $can_update )
			{
				$fb   = new FacebookCookieApi( $cookieCuser, $cookieXs, $proxy );
				$data = $fb->updateFbCookie( $id );
				if ( $data === FALSE )
				{
					Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
				}
			}
		}
		else
		{
			Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
		}

		Helper::response( TRUE );
	}

	public function account_activity_change ()
	{
		$id          = Request::post( 'id', '0', 'num' );
		$checked     = Request::post( 'checked', -1, 'num', [ '0', '1' ] );
		$for_all     = Request::post( 'for_all', 0, 'int', [ '0', '1' ] );
		$filter_type = Request::post( 'filter_type', '', 'string', [ 'in', 'ex' ] );
		$categories  = Request::post( 'categories', [], 'array' );

		if ( ! ( $id > 0 && $checked > -1 ) )
		{
			Helper::response( FALSE );
		}

		$categories_arr = [];
		foreach ( $categories as $categId )
		{
			if ( is_numeric( $categId ) && $categId > 0 )
			{
				$categories_arr[] = (int) $categId;
			}
		}
		$categories_arr = implode( ',', $categories_arr );

		if ( ( ! empty( $categories_arr ) && empty( $filter_type ) ) || ( empty( $categories_arr ) && ! empty( $filter_type ) ) )
		{
			Helper::response( FALSE, fsp__( 'Please select categories and filter type!' ) );
		}

		$categories_arr = empty( $categories_arr ) ? NULL : $categories_arr;
		$filter_type    = empty( $filter_type ) || empty( $categories_arr ) ? 'no' : $filter_type;
		$for_all        = $for_all && ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) );

		$res = Action::activate_deactivate_account( get_current_user_id(), $id, $checked, $filter_type, $categories_arr, $for_all );

		if ( $res[ 'status' ] === FALSE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function add_instagram_account ()
	{
		$username = Request::post( 'username', '', 'string' );
		$password = Request::post( 'password', '', 'string' );
		$proxy    = Request::post( 'proxy', '', 'string' );

		if ( empty( $username ) || empty( $password ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter the username and password!' ) ] );
		}

		// delete old session
		DB::DB()->delete( DB::table( 'account_sessions' ), [ 'driver' => 'instagram', 'username' => $username ] );

		$ig     = new InstagramLoginPassMethod( $username, $password, $proxy );
		$result = $ig->login();

		InstagramApi::handleResponse( $result, $username, $password, $proxy );
	}

    public function add_threads_account ()
    {
        $username = Request::post( 'username', '', 'string' );
        $password = Request::post( 'password', '', 'string' );
        $proxy    = Request::post( 'proxy', '', 'string' );

        if ( empty( $username ) || empty( $password ) )
        {
            Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter the username and password!' ) ] );
        }

        $threads = new Threads( [ 'username' => $username, 'password' => $password ], $proxy );
        $result  = $threads->login();

        if( ! $result['status'] )
        {
            Helper::response( false, $result['error_msg'] );
        }

        if( $result['data']['needs_challenge'] )
        {
            Helper::response( true, [ 'options' => $result['data']['options'] ] );
        }

        $existingAccount = DB::fetch('accounts', [
            'driver'     => 'threads',
            'profile_id' => $result['data']['profile_id'],
            'blog_id'    => Helper::getBlogId()
        ]);

        if( ! empty( $existingAccount ) )
        {
            DB::DB()->update(DB::table('accounts'), [
                'name'        => $result['data']['name'],
                'profile_pic' => $result['data']['profile_pic'],
                'options'     => json_encode( array_merge( json_decode( $existingAccount[ 'options' ], true ), $result['data']['options'] ) ),
                'proxy'       => $proxy,
                'status'      => null,
                'error_msg'   => null
            ], [
                'id' => $existingAccount['id']
            ]);

            $id = $existingAccount['id'];
        }
        else
        {
            DB::DB()->insert( DB::table('accounts'), [
                'driver'      => 'threads',
                'blog_id'     => Helper::getBlogId(),
                'user_id'     => get_current_user_id(),
                'username'    => $username,
                'name'        => $result['data']['name'],
                'profile_id'  => $result['data']['profile_id'],
                'profile_pic' => $result['data']['profile_pic'],
                'options'     => json_encode( $result['data']['options'] ),
                'proxy'       => $proxy
            ] );

            $id = DB::DB()->insert_id;
        }

        Helper::response( true, [ 'id' => $id ] );
    }

    public function do_threads_challenge()
    {
        $options = Request::post( 'options', [], 'ARRAY' );
        $code    = Request::post( 'code', '', 'string' );
        $proxy   = Request::post( 'proxy', '', 'string' );

        if( empty( $options ) )
        {
            Helper::response(false);
        }

        if ( empty( $code ) )
        {
            Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter the code!' ) ] );
        }

        $threads = new Threads( $options, $proxy );
        $result  = $threads->doTwoFactorAuth( $options[ 'two_factor_identifier' ], $code, $options['verification_method'] );

        if( ! $result['status'] )
        {
            Helper::response( false, $result['error_msg'] );
        }

        $existingAccount = DB::fetch('accounts', [
            'driver'     => 'threads',
            'profile_id' => $result['data']['profile_id'],
            'blog_id'    => Helper::getBlogId()
        ]);

        if( ! empty( $existingAccount ) )
        {
            DB::DB()->update(DB::table('accounts'), [
                'name'        => $result['data']['name'],
                'profile_pic' => $result['data']['profile_pic'],
                'options'     => json_encode( array_merge( json_decode( $existingAccount[ 'options' ], true ), $result['data']['options'] ) ),
                'proxy'       => $proxy,
                'status'      => null,
                'error_msg'   => null
            ], [
                'id' => $existingAccount['id']
            ]);

            $id = $existingAccount['id'];
        }
        else
        {
            DB::DB()->insert( DB::table('accounts'), [
                'driver'      => 'threads',
                'blog_id'     => Helper::getBlogId(),
                'user_id'     => get_current_user_id(),
                'username'    => $options[ 'username' ],
                'name'        => $result['data']['name'],
                'profile_id'  => $result['data']['profile_id'],
                'profile_pic' => $result['data']['profile_pic'],
                'options'     => json_encode( $result['data']['options'] ),
                'proxy'       => $proxy
            ] );

            $id = DB::DB()->insert_id;
        }

        Helper::response( true, [ 'id' => $id ] );
    }

	public function add_instagram_account_cookie_method ()
	{
		$cookie_csrf_token = Request::post( 'cookie_csrf_token', '', 'string' );
		$cookie_ds_user_id = Request::post( 'cookie_ds_user_id', '', 'string' );
		$cookie_sessionid  = Request::post( 'cookie_sessionid', '', 'string' );
		$proxy             = Request::post( 'proxy', '', 'string' );

		$password = '*****';

		if ( empty( $cookie_sessionid ) || empty( $cookie_ds_user_id ) || empty( $cookie_csrf_token ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter Instagram cookies!' ) ] );
		}

		$cookiesArr = [
			[
				"Name"     => "csrftoken",
				"Value"    => $cookie_csrf_token,
				"Domain"   => ".instagram.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => TRUE
			],
			[
				"Name"     => "ds_user_id",
				"Value"    => $cookie_ds_user_id,
				"Domain"   => ".instagram.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => TRUE
			],
			[
				"Name"     => "sessionid",
				"Value"    => $cookie_sessionid,
				"Domain"   => ".instagram.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => TRUE
			]
		];

		$ig   = new InstagramCookieMethod( $cookiesArr, $proxy );
		$info = $ig->profileInfo();

		if ( ! $info )
		{
			Helper::response( FALSE, fsp__( 'The cookie values aren\'t valid! Please get the new ones.' ) );
		}

		$cookiesArr[] = [
			"Name"     => "mcd",
			"Value"    => 3,
			"Domain"   => ".instagram.com",
			"Path"     => "/",
			"Max-Age"  => NULL,
			"Expires"  => NULL,
			"Secure"   => TRUE,
			"Discard"  => FALSE,
			"HttpOnly" => FALSE
		];

		$username = $info[ 'username' ];

		DB::DB()->delete( DB::table( 'account_sessions' ), [ 'driver' => 'instagram', 'username' => $username ] );
		DB::DB()->insert( DB::table( 'account_sessions' ), [
			'driver'   => 'instagram',
			'username' => $username,
			'settings' => NULL,
			'cookies'  => json_encode( $cookiesArr )
		] );

		$insertedId = DB::DB()->insert_id;
		$name       = json_decode( '"' . str_replace( '"', '\\"', $info[ 'full_name' ] ) . '"' );

		$sqlData = [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
			'profile_id'  => $info[ 'id' ],
			'username'    => $username,
			'password'    => $password,
			'proxy'       => $proxy,
			'driver'      => 'instagram',
			'name'        => $name,
			'profile_pic' => $info[ 'profile_pic_url' ],
			'status'      => NULL,
			'error_msg'   => NULL
		];

		$checkIfExists = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'profile_id' => $info[ 'id' ],
			'driver'     => 'instagram'
		] );

		if ( $checkIfExists )
		{
			DB::DB()->update( DB::table( 'accounts' ), $sqlData, [ 'id' => $checkIfExists[ 'id' ] ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $sqlData );
		}

		Helper::response( TRUE );
	}

	public function update_instagram_account_cookie ()
	{
		$id                = Request::post( 'account_id', '', 'string' );
		$cookie_csrf_token = Request::post( 'cookie_csrf_token', '', 'string' );
		$cookie_sessionid  = Request::post( 'cookie_sessionid', '', 'string' );
		$proxy             = Request::post( 'proxy', '', 'string' );

		if ( empty( $cookie_sessionid ) || empty( $cookie_csrf_token ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter Instagram cookies!' ) ] );
		}

		$instaUser = DB::fetch( 'accounts', [
			'blog_id' => Helper::getBlogId(),
			'id'      => $id,
			'driver'  => 'instagram'
		] );

		$cookiesArr = [
			[
				"Name"     => "sessionid",
				"Value"    => $cookie_sessionid,
				"Domain"   => ".instagram.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => TRUE
			],
			[
				"Name"     => "csrftoken",
				"Value"    => $cookie_csrf_token,
				"Domain"   => ".instagram.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => TRUE
			],
			[
				"Name"     => "ds_user_id",
				"Value"    => $instaUser[ 'profile_id' ],
				"Domain"   => ".instagram.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => TRUE
			]
		];

		$ig   = new InstagramCookieMethod( $cookiesArr, $proxy );
		$info = $ig->profileInfo();

		if ( ! $info )
		{
			Helper::response( FALSE, fsp__( 'The cookie values aren\'t valid! Please get the new ones.' ) );
		}

		$cookiesArr[] = [
			"Name"     => "mcd",
			"Value"    => 3,
			"Domain"   => ".instagram.com",
			"Path"     => "/",
			"Max-Age"  => NULL,
			"Expires"  => NULL,
			"Secure"   => TRUE,
			"Discard"  => FALSE,
			"HttpOnly" => FALSE
		];

		if ( $instaUser )
		{
			if ( $instaUser[ 'profile_id' ] !== $info[ 'id' ] )
			{
				Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
			}

			$username   = $instaUser[ 'username' ];
			$is_owner   = $instaUser[ 'user_id' ] == get_current_user_id();
			$is_public  = (bool) $instaUser[ 'is_public' ];
			$can_update = $is_owner || $is_public;

			if ( $can_update )
			{
				DB::DB()->update( DB::table( 'accounts' ), [ 'status' => NULL, 'error_msg' => NULL ], [ 'id' => $id ] );
				DB::DB()->delete( DB::table( 'account_sessions' ), [
					'driver'   => 'instagram',
					'username' => $username
				] );
				DB::DB()->insert( DB::table( 'account_sessions' ), [
					'driver'   => 'instagram',
					'username' => $username,
					'settings' => NULL,
					'cookies'  => json_encode( $cookiesArr )
				] );
				Helper::response( TRUE );
			}
		}

		Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
	}

	public function add_tumblr_account ()
	{
		$email    = Request::post( 'email', '', 'string' );
		$password = Request::post( 'password', '', 'string' );
		$proxy    = Request::post( 'proxy', '', 'string' );

		if ( empty( $email ) || empty( $password ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter the email and password!' ) ] );
		}

		$tumblr = new TumblrLoginPassMethod( $email, $password, $proxy, FALSE );
		$result = $tumblr->authorize();

		if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'error' )
		{
			$error_msg = empty( $result[ 'error_msg' ] ) ? fsp__( 'Unknown error!' ) : fsp__( $result[ 'error_msg' ] );

			Helper::response( FALSE, [ 'error_msg' => $error_msg ] );
		}

		Helper::response( TRUE );
	}

	public function add_twitter_account ()
	{
		$auth_token = Request::post( 'auth_token', '', 'string' );
		$proxy      = Request::post( 'proxy', '', 'string' );

		if ( empty( $auth_token ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter the cookie auth_token!' ) ] );
		}

		$twitter = new TwitterPrivateAPI( $auth_token, $proxy );
		$result  = $twitter->authorize();

		if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'error' )
		{
			$error_msg = empty( $result[ 'error_msg' ] ) ? fsp__( 'Unknown error!' ) : fsp__( $result[ 'error_msg' ] );

			Helper::response( FALSE, [ 'error_msg' => $error_msg ] );
		}

		Helper::response( TRUE );
	}

	public function update_twitter_account_cookie ()
	{
		$account_id        = Request::post( 'id', '', 'string' );
		$cookie_auth_token = Request::post( 'auth_token', '', 'string' );
		$proxy             = Request::post( 'proxy', '', 'string' );

		if ( empty( $cookie_auth_token ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter the cookie auth_token!' ) ] );
		}

		$twitter = new TwitterPrivateAPI( $cookie_auth_token, $proxy );

		$info = $twitter->getUserInfo();

		if ( ! $info )
		{
			Helper::response( FALSE, fsp__( 'The cookie value isn\'t valid! Please get the new one.' ) );
		}

		$twitterUser = DB::fetch( 'accounts', [
			'blog_id' => Helper::getBlogId(),
			'id'      => $account_id,
			'driver'  => 'twitter'
		] );

		if ( $twitterUser )
		{
			if ( $twitterUser[ 'profile_id' ] !== $info[ 'profile_id' ] )
			{
				Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
			}

			$is_owner  = $twitterUser[ 'user_id' ] == get_current_user_id();
			$is_public = (bool) $twitterUser[ 'is_public' ];

			if ( $is_owner || $is_public )
			{
				DB::DB()->update( DB::table( 'accounts' ),
					[
						'status'      => NULL,
						'error_msg'   => NULL,
						'options'     => $cookie_auth_token,
						'profile_pic' => $info[ 'profile_pic' ],
						'username'    => $info[ 'username' ]
					],
					[ 'id' => $account_id ] );

				Helper::response( TRUE );
			}
		}

		Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
	}

	public function add_vk_account ()
	{
		$accessToken = Request::post( 'at', '', 'string' );
		$app         = Request::post( 'app', '0', 'int' );
		$proxy       = Request::post( 'proxy', '0', 'string' );

		if ( empty( $accessToken ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The access token is empty!' ) ] );
		}

		preg_match( '/access_token=([^&]+)/', $accessToken, $accessToken2 );

		if ( isset( $accessToken2[ 1 ] ) )
		{
			$accessToken = $accessToken2[ 1 ];
		}

		$get_app = DB::fetch( 'apps', [ 'driver' => 'vk', 'app_id' => $app ] );

		$result = Vk::authorizeVkUser( (int) $get_app[ 'id' ], $accessToken, $proxy );

		if ( isset( $result[ 'error' ] ) )
		{
			Helper::response( FALSE, $result[ 'error' ] );
		}

		Helper::response( TRUE );
	}

	public function add_telegram_bot ()
	{
		$bot_token = Request::post( 'bot_token', '', 'string' );
		$proxy     = Request::post( 'proxy', '', 'string' );

		if ( empty( $bot_token ) )
		{
			Helper::response( FALSE, fsp__( 'Please type your Bot Token!' ) );
		}

		$tg   = new Telegram( $bot_token, $proxy );
		$data = $tg->getBotInfo();

		if ( empty( $data[ 'id' ] ) )
		{
			Helper::response( FALSE, fsp__( 'The entered Bot Token is invalid!' ) );
		}

		if ( ! get_current_user_id() > 0 )
		{
			Helper::response( FALSE, fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' ) );
		}

		$sqlData = [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'profile_id' => $data[ 'id' ],
			'username'   => $data[ 'username' ],
			'proxy'      => $proxy,
			'driver'     => 'telegram',
			'name'       => $data[ 'name' ],
			'options'    => $bot_token,
			'status'     => NULL,
			'error_msg'  => NULL
		];

		$checkIfExists = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'driver'     => 'telegram',
			'user_id'    => get_current_user_id(),
			'profile_id' => $data[ 'id' ]
		] );

		if ( $checkIfExists )
		{
			DB::DB()->update( DB::table( 'accounts' ), $sqlData, [ 'id' => $checkIfExists[ 'id' ] ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $sqlData );
		}

		Helper::response( TRUE );
	}

	public function add_planly_account ()
	{
		$token = Request::post( 'access_token', '', 'string' );
		$proxy = Request::post( 'proxy', '', 'string' );

		if ( empty( $token ) )
		{
			Helper::response( FALSE, fsp__( "Please type your Access Token!" ) );
		}

		$data = ( new Planly( $token, $proxy ) )->getUser();

		if ( ! $data[ "status" ] )
		{
			Helper::response( FALSE, $data[ "error_msg" ] );
		}
		else if ( empty( $data[ "user" ] ) )
		{
			Helper::response( FALSE, fsp__( "The entered Access Token is invalid!" ) );
		}

		if ( ! get_current_user_id() > 0 )
		{
			Helper::response( FALSE, fsp__( "The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization." ) );
		}

		$sqlData = [
			"blog_id"     => Helper::getBlogId(),
			"user_id"     => get_current_user_id(),
			"profile_id"  => $data[ "user" ][ "id" ],
			"name"        => $data[ "user" ][ "name" ],
			"profile_pic" => $data[ "user" ][ "picture" ],
			"proxy"       => $proxy,
			"driver"      => "planly",
			"options"     => $token,
			"status"      => NULL,
			"error_msg"   => NULL
		];

		$checkIfExists = DB::DB()->get_row( DB::DB()->prepare( "select a.id, group_concat(n.node_id) as node_ids from `" . DB::table( "accounts" ) . "` a left join `" . DB::table( "account_nodes" ) . "` n on n.account_id=a.id where a.blog_id=%d and a.user_id=%d and a.profile_id=%s and a.driver=%s", [
			Helper::getBlogId(),
			get_current_user_id(),
			$data[ "user" ][ "id" ],
			"planly"
		] ), ARRAY_A );

		if ( ! empty( $checkIfExists[ "id" ] ) )
		{
			$accountId = $checkIfExists[ "id" ];
			DB::DB()->update( DB::table( 'accounts' ), $sqlData, [ "id" => $accountId ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $sqlData );

			$accountId = DB::DB()->insert_id;
		}

		if ( $data[ "nodes" ] && $accountId )
		{
			$ids = $checkIfExists ? explode( ",", $checkIfExists[ "node_ids" ] ) : [];

			foreach ( $data[ "nodes" ] as $channel )
			{
				if ( in_array( $channel[ "id" ], $ids ) )
				{
					DB::DB()->update( DB::table( "account_nodes" ), [
						"name"        => $channel[ "name" ],
						"screen_name" => $channel[ "url" ],
						"cover"       => $channel[ "picture" ],
					], [
						"account_id"   => $accountId,
						"access_token" => $channel[ "team_id" ],
						"node_id"      => $channel[ "id" ]
					] );
				}
				else
				{
					DB::DB()->insert( DB::table( "account_nodes" ), [
						"node_type"    => $channel[ "type" ],
						"user_id"      => get_current_user_id(),
						"blog_id"      => Helper::getBlogId(),
						"node_id"      => $channel[ "id" ],
						"access_token" => $channel[ "team_id" ],
						"screen_name"  => $channel[ "url" ],
						"driver"       => "planly",
						"account_id"   => $accountId,
						"name"         => $channel[ "name" ],
						"cover"        => $channel[ "picture" ]
					] );
				}
			}
		}

		Helper::response( TRUE );
	}

	public function add_youtube_community_account ()
	{
		$loginInfo     = Request::post( 'login_info', '', 'string' );
		$secure3ApiSid = Request::post( 'api_sid', '', 'string' );
		$secure3PSid   = Request::post( 'p_sid', '', 'string' );
		$proxy         = Request::post( 'proxy', '', 'string' );

		if ( empty( $loginInfo ) || empty( $secure3ApiSid ) || empty( $secure3PSid ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter Youtube cookies!' ) ] );
		}

		$cookies = [
			[
				"Name"     => "LOGIN_INFO",
				"Value"    => $loginInfo,
				"Domain"   => ".youtube.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => FALSE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE,
				"Priority" => "HIGH"
			],
			[
				"Name"     => "__Secure-3PAPISID",
				"Value"    => $secure3ApiSid,
				"Domain"   => ".youtube.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => FALSE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE,
				"Priority" => "HIGH"
			],
			[
				"Name"     => "__Secure-3PSID",
				"Value"    => $secure3PSid,
				"Domain"   => ".youtube.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => FALSE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE,
				"Priority" => "HIGH"
			]
		];

		$response = ( new YoutubeCommunity( $cookies, $proxy ) )->channel();

		if ( $response[ "status" ] )
		{
			$sqlData = [
				"blog_id"     => Helper::getBlogId(),
				"user_id"     => get_current_user_id(),
				"profile_id"  => $response[ "channel" ][ "id" ],
				"username"    => NULL,
				"password"    => NULL,
				"proxy"       => $proxy,
				"options"     => json_encode( $response[ "channel" ][ "cookies" ] ),
				"driver"      => 'youtube_community',
				"name"        => $response[ "channel" ][ "name" ],
				"profile_pic" => $response[ "channel" ][ "image" ],
				"status"      => NULL,
				"error_msg"   => NULL
			];

			$checkIfExists = DB::DB()->get_row( DB::DB()->prepare( "select id from `" . DB::table( "accounts" ) . "` where blog_id=%d and user_id=%d and profile_id=%s and driver=%s", [
				Helper::getBlogId(),
				get_current_user_id(),
				$response[ "channel" ][ "id" ],
				'youtube_community'
			] ) );

			if ( $checkIfExists )
			{
				DB::DB()->update( DB::table( 'accounts' ), $sqlData, [ 'id' => $checkIfExists[ 'id' ] ] );
			}
			else
			{
				DB::DB()->insert( DB::table( 'accounts' ), $sqlData );
			}
		}
		else
		{
			Helper::response( FALSE, $response[ "error_msg" ] );
		}

		Helper::response( TRUE );
	}

	public function update_youtube_community_account ()
	{
		$loginInfo     = Request::post( 'login_info', '', 'string' );
		$secure3ApiSid = Request::post( 'api_sid', '', 'string' );
		$secure3PSid   = Request::post( 'p_sid', '', 'string' );
		$id            = Request::post( 'account_id', '', 'string' );
		$proxy         = Request::post( 'proxy', '', 'string' );

		if ( empty( $loginInfo ) || empty( $secure3ApiSid ) || empty( $secure3PSid ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter Youtube cookies!' ) ] );
		}

		$user = DB::DB()->get_row( DB::DB()->prepare( "select user_id, is_public, profile_id from `" . DB::table( 'accounts' ) . "` where id=%d and blog_id=%d and driver=%s", [
			$id,
			Helper::getBlogId(),
			'youtube_community'
		] ) );

		if ( $user )
		{
			$is_owner  = $user->user_id == get_current_user_id();
			$is_public = ( bool ) $user->is_public;

			if ( $is_owner || $is_public )
			{
				$cookies = [
					[
						"Name"     => "LOGIN_INFO",
						"Value"    => $loginInfo,
						"Domain"   => ".youtube.com",
						"Path"     => "/",
						"Max-Age"  => NULL,
						"Expires"  => NULL,
						"Secure"   => FALSE,
						"Discard"  => FALSE,
						"HttpOnly" => FALSE,
						"Priority" => "HIGH"
					],
					[
						"Name"     => "__Secure-3PAPISID",
						"Value"    => $secure3ApiSid,
						"Domain"   => ".youtube.com",
						"Path"     => "/",
						"Max-Age"  => NULL,
						"Expires"  => NULL,
						"Secure"   => FALSE,
						"Discard"  => FALSE,
						"HttpOnly" => FALSE,
						"Priority" => "HIGH"
					],
					[
						"Name"     => "__Secure-3PSID",
						"Value"    => $secure3PSid,
						"Domain"   => ".youtube.com",
						"Path"     => "/",
						"Max-Age"  => NULL,
						"Expires"  => NULL,
						"Secure"   => FALSE,
						"Discard"  => FALSE,
						"HttpOnly" => FALSE,
						"Priority" => "HIGH"
					]
				];

				$response = ( new YoutubeCommunity( $cookies, $proxy ) )->channel();

				if ( $response[ 'status' ] )
				{
					if ( $response[ 'channel' ][ 'id' ] === $user->profile_id )
					{
						DB::DB()->update( DB::table( 'accounts' ), [
							'proxy'       => $proxy,
							'options'     => json_encode( $response[ 'channel' ][ 'cookies' ] ),
							'name'        => $response[ 'channel' ][ 'name' ],
							'profile_pic' => $response[ 'channel' ][ 'image' ],
							'status'      => NULL,
							'error_msg'   => NULL
						], [ 'id' => $id ] );

						Helper::response( TRUE );
					}
				}
				else
				{
					Helper::response( FALSE, $response[ 'error_msg' ] );
				}
			}
		}

		Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
	}

	public function add_xing_account ()
	{
		$login         = Request::post( 'login', '', 'string' );
		$csrf_token    = Request::post( 'csrf_token', '', 'string' );
		$csrf_checksum = Request::post( 'csrf_checksum', '', 'string' );
		$proxy         = Request::post( 'proxy', '', 'string' );

		if ( empty( $login ) || empty( $csrf_token ) || empty( $csrf_checksum ) )
		{
			Helper::response( FALSE, fsp__( 'Please fill all the fields correctly!' ) );
		}

		$cookies = [
			'login'              => $login,
			'xing_csrf_token'    => $csrf_token,
			'xing_csrf_checksum' => $csrf_checksum,
		];

		$xing = new Xing( $cookies, $proxy );

		$accountData = $xing->getAccountData();

		if ( empty( $accountData[ 'id' ] ) )
		{
			Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
		}

		if ( ! get_current_user_id() > 0 )
		{
			Helper::response( FALSE, fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' ) );
		}

		$sqlData = [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
			'profile_id'  => $accountData[ 'id' ],
			'username'    => $accountData[ 'xingId' ][ 'pageName' ],
			'proxy'       => $proxy,
			'driver'      => 'xing',
			'name'        => $accountData[ 'xingId' ][ 'displayName' ],
			'profile_pic' => $accountData[ 'xingId' ][ 'profileImage' ][ 0 ][ 'url' ],
			'options'     => json_encode( $cookies ),
			'status'      => NULL,
			'error_msg'   => NULL
		];

		$checkIfExists = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'driver'     => 'xing',
			'user_id'    => get_current_user_id(),
			'profile_id' => $accountData[ 'id' ]
		] );

		if ( $checkIfExists )
		{
			$accountId = $checkIfExists[ 'id' ];

			DB::DB()->update( DB::table( 'accounts' ), $sqlData, [ 'id' => $accountId ] );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN ( SELECT id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = "' . $accountId . '")' );
			DB::DB()->delete( DB::table( 'account_nodes' ), [ 'account_id' => $accountId ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $sqlData );

			$accountId = DB::DB()->insert_id;
		}

		$nodesData = $xing->getCompanies();

		if ( ! empty( $nodesData ) )
		{
			foreach ( $nodesData as $node )
			{
				$node = $node[ 'node' ];
				$url  = ! $node[ 'isMeetup' ] ? $node[ 'links' ][ 'public' ] : str_replace( 'about', 'posts', $node[ 'links' ][ 'public' ] );

				DB::DB()->insert( DB::table( 'account_nodes' ), [
					'blog_id'     => Helper::getBlogId(),
					'user_id'     => get_current_user_id(),
					'account_id'  => $accountId,
					'driver'      => 'xing',
					'node_type'   => 'group',
					'node_id'     => $node[ 'id' ],
					'name'        => $node[ 'name' ],
					'cover'       => $node[ 'logo' ][ 'urls' ][ 'medium' ],
					'screen_name' => $url
				] );
			}
		}

		Helper::response( TRUE );
	}

	public function update_xing_account ()
	{
		$login         = Request::post( 'login', '', 'string' );
		$csrf_token    = Request::post( 'csrf_token', '', 'string' );
		$csrf_checksum = Request::post( 'csrf_checksum', '', 'string' );
		$id            = Request::post( 'account_id', '', 'string' );
		$proxy         = Request::post( 'proxy', '', 'string' );

		if ( empty( $login ) || empty( $csrf_token ) || empty( $csrf_checksum ) )
		{
			Helper::response( FALSE, fsp__( 'Please fill all the fields correctly!' ) );
		}

		$user = DB::DB()->get_row( DB::DB()->prepare( "select user_id, is_public, profile_id from `" . DB::table( 'accounts' ) . "` where id=%d and blog_id=%d and driver=%s", [
			$id,
			Helper::getBlogId(),
			'xing'
		] ) );

		if ( $user )
		{
			$is_owner  = $user->user_id == get_current_user_id();
			$is_public = ( bool ) $user->is_public;

			if ( $is_owner || $is_public )
			{
				$cookies = [
					'login'              => $login,
					'xing_csrf_token'    => $csrf_token,
					'xing_csrf_checksum' => $csrf_checksum,
				];

				$data = ( new Xing( $cookies, $proxy ) )->updateCookies( $id, $user->profile_id );

				if ( $data )
				{
					Helper::response( TRUE );
				}
			}
		}

		Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
	}

	private function change_xing_group_selected_forum ()
	{
		$fs_xing_group_selected_forum = Request::post( 'fs_xing_group_selected_forum', '', 'integer' );
		$fs_xing_group_id             = Request::post( 'fs_xing_group_id', '', 'integer' );

		$forum_types = json_decode( Helper::getCustomSetting( 'xing_group_forums', '{}', 'node', $fs_xing_group_id ), TRUE );

		if ( ! empty( $forum_types ) )
		{
			if ( isset( $forum_types[ $fs_xing_group_selected_forum ] ) )
			{
				Helper::setCustomSetting( 'xing_group_selected_forum', $fs_xing_group_selected_forum, 'node', $fs_xing_group_id );

				Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
			}

			Helper::response( FALSE, fsp__( 'Forum ID is not valid!' ) );
		}

		Helper::response( FALSE, fsp__( 'There\'re no matched forums for the given group' ) );
	}

	public function add_pinterest_account_cookie_method ()
	{
		$cookie_sess = Request::post( 'cookie_sess', '', 'string' );
		$proxy       = Request::post( 'proxy', '', 'string' );

		if ( empty( $cookie_sess ) )
		{
			Helper::response( FALSE, fsp__( 'Please enter the Pinterest cookie!' ) );
		}

		$pinterest = new PinterestCookieApi( $cookie_sess, $proxy );
		$details   = $pinterest->getAccountData();

		$sqlData = [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
			'profile_id'  => $details[ 'id' ],
			'proxy'       => $proxy,
			'driver'      => 'pinterest',
			'options'     => json_encode( [ 'auth_method' => 'cookie' ] ),
			'name'        => $details[ 'full_name' ],
			'profile_pic' => $details[ 'profile_pic' ],
			'username'    => $details[ 'username' ],
			'status'      => NULL,
			'error_msg'   => NULL
		];

		$checkIfExists = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'profile_id' => $details[ 'id' ],
			'driver'     => 'pinterest'
		] );

		if ( $checkIfExists )
		{
			DB::DB()->update( DB::table( 'accounts' ), $sqlData, [ 'id' => $checkIfExists[ 'id' ] ] );
			$accountId = $checkIfExists[ 'id' ];
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN ( SELECT id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = "' . $accountId . '")' );
			DB::DB()->delete( DB::table( 'account_nodes' ), [ 'account_id' => $accountId ] );
			DB::DB()->delete( DB::table( 'account_sessions' ), [
				'driver'   => 'pinterest',
				'username' => $details[ 'username' ]
			] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $sqlData );
			$accountId = DB::DB()->insert_id;
		}

		DB::DB()->insert( DB::table( 'account_sessions' ), [
			'driver'   => 'pinterest',
			'username' => $details[ 'username' ],
			'cookies'  => $cookie_sess
		] );

		foreach ( $pinterest->getBoards( $details[ 'username' ] ) as $board )
		{
			DB::DB()->insert( DB::table( 'account_nodes' ), [
				'blog_id'     => Helper::getBlogId(),
				'user_id'     => get_current_user_id(),
				'account_id'  => $accountId,
				'driver'      => 'pinterest',
				'node_type'   => 'board',
				'node_id'     => $board[ 'id' ],
				'name'        => $board[ 'name' ],
				'cover'       => $board[ 'cover' ],
				'screen_name' => $board[ 'url' ],
			] );
		}

		Helper::response( TRUE );
	}

	public function update_pinterest_account_cookie ()
	{
		$id          = Request::post( 'account_id', '', 'string' );
		$cookie_sess = Request::post( 'cookie_sess', '', 'string' );
		$proxy       = Request::post( 'proxy', '', 'string' );

		if ( empty( $cookie_sess ) )
		{
			Helper::response( FALSE, fsp__( 'Please enter the Pinterest cookie!' ) );
		}

		$pinterest = new PinterestCookieApi( $cookie_sess, $proxy );
		$details   = $pinterest->getAccountData();

		if ( ! $details )
		{
			Helper::response( FALSE, fsp__( 'The cookie values aren\'t valid!' ) );
		}

		$sqlData = [
			'proxy'     => $proxy,
			'options'   => json_encode( [ 'auth_method' => 'cookie' ] ),
			'status'    => NULL,
			'error_msg' => NULL
		];

		$pinUser = DB::fetch( 'accounts', [
			'blog_id' => Helper::getBlogId(),
			'id'      => $id,
			'driver'  => 'pinterest'
		] );

		if ( $pinUser )
		{
			if ( $pinUser[ 'profile_id' ] !== $details[ 'id' ] )
			{
				Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
			}

			$username   = $pinUser[ 'username' ];
			$is_owner   = $pinUser[ 'user_id' ] == get_current_user_id();
			$is_public  = (bool) $pinUser[ 'is_public' ];
			$can_update = $is_owner || $is_public;

			if ( $can_update )
			{
				DB::DB()->update( DB::table( 'accounts' ), $sqlData, [ 'id' => $id ] );

				DB::DB()->delete( DB::table( 'account_sessions' ), [
					'driver'   => 'pinterest',
					'username' => $username
				] );
				DB::DB()->insert( DB::table( 'account_sessions' ), [
					'driver'   => 'pinterest',
					'username' => $pinUser[ 'username' ],
					'settings' => NULL,
					'cookies'  => $cookie_sess
				] );
				Helper::response( TRUE );
			}

		}

		Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
	}

	public function add_new_medium_account_with_token ()
	{
		$accessToken = Request::post( 'access_token', '', 'string' );
		$proxy       = Request::post( 'proxy', '', 'string' );

		Medium::authorizeMediumUser( '', $accessToken, '', '', $proxy );

		Helper::response( TRUE );
	}

	public function add_wordpress_site ()
	{
		$site_url = Request::post( 'site_url', '', 'string' );
		$username = Request::post( 'username', '', 'string' );
		$password = Request::post( 'password', '', 'string' );
		$proxy    = Request::post( 'proxy', '', 'string' );

		if ( empty( $site_url ) || empty( $username ) || empty( $password ) )
		{
			Helper::response( FALSE, fsp__( 'Please fill all inputs correctly!' ) );
		}

		if ( ! preg_match( '/^http(s|):\/\//i', $site_url ) )
		{
			Helper::response( FALSE, fsp__( 'The URL must start with http(s)!' ) );
		}

		$wordpress = new Wordpress( $site_url, $username, $password, $proxy );
		$check     = $wordpress->checkUser();

		if ( $check !== TRUE )
		{
			Helper::response( FALSE, $check );
		}

		$password = '(-F-S-P-)' . str_rot13( base64_encode( $password . '(-F-S-P-)' . Date::epoch() ) );

		if ( ! get_current_user_id() > 0 )
		{
			Helper::response( FALSE, fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' ) );
		}

		$sqlData = [
			'blog_id'   => Helper::getBlogId(),
			'user_id'   => get_current_user_id(),
			'username'  => $username,
			'password'  => $password,
			'proxy'     => $proxy,
			'driver'    => 'wordpress',
			'name'      => $site_url,
			'options'   => $site_url,
			'status'    => NULL,
			'error_msg' => NULL
		];

		$checkIfExists = DB::fetch( 'accounts', [
			'driver'   => 'wordpress',
			'user_id'  => get_current_user_id(),
			'options'  => $site_url,
			'username' => $username
		] );

		if ( $checkIfExists )
		{
			DB::DB()->update( DB::table( 'accounts' ), $sqlData, [ 'id' => $checkIfExists[ 'id' ] ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $sqlData );
		}

		Helper::response( TRUE );
	}

	public function add_google_b_account ()
	{
		$cookie_sid     = Request::post( 'cookie_sid', '', 'string' );
		$cookie_hsid    = Request::post( 'cookie_hsid', '', 'string' );
		$cookie_ssid    = Request::post( 'cookie_ssid', '', 'string' );
		$cookie_sapisid = Request::post( 'cookie_sapisid', '', 'string' );
		$proxy          = Request::post( 'proxy', '', 'string' );

		if ( empty( $cookie_sid ) || empty( $cookie_hsid ) || empty( $cookie_ssid ) )
		{
			Helper::response( FALSE, 'Please type your Cookies!' );
		}

		$google = new GoogleMyBusiness( $cookie_sid, $cookie_hsid, $cookie_ssid, $cookie_sapisid, $proxy );
		$data   = $google->getUserInfo();

		if ( empty( $data[ 'id' ] ) )
		{
			Helper::response( FALSE, 'The entered cookies are wrong!' );
		}

		$options = json_encode( [
			'sid'     => $cookie_sid,
			'hsid'    => $cookie_hsid,
			'ssid'    => $cookie_ssid,
			'sapisid' => $cookie_sapisid,
		] );

		if ( ! get_current_user_id() > 0 )
		{
			Helper::response( FALSE, fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' ) );
		}

		$sqlData = [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
			'profile_id'  => $data[ 'id' ],
			'username'    => isset( $data[ 'email' ] ) ? $data[ 'email' ] : '',
			'password'    => '',
			'proxy'       => $proxy,
			'driver'      => 'google_b',
			'name'        => isset( $data[ 'name' ] ) ? $data[ 'name' ] : '',
			'profile_pic' => isset( $data[ 'profile_image' ] ) ? $data[ 'profile_image' ] : '',
			'options'     => $options,
			'error_msg'   => NULL,
			'status'      => NULL
		];

		$checkIfExists = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'driver'     => 'google_b',
			'user_id'    => get_current_user_id(),
			'profile_id' => $data[ 'id' ]
		] );

		if ( $checkIfExists )
		{
			DB::DB()->update( DB::table( 'accounts' ), $sqlData, [ 'id' => $checkIfExists[ 'id' ] ] );
			$accountId = $checkIfExists[ 'id' ];
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $sqlData );
			$accountId = DB::DB()->insert_id;
		}

		$google->refetch_account( $accountId );

		Helper::response( TRUE );
	}

	public function update_google_b_cookie ()
	{
		$id             = Request::post( 'account_id', '', 'string' );
		$cookie_sid     = Request::post( 'cookie_sid', '', 'string' );
		$cookie_hsid    = Request::post( 'cookie_hsid', '', 'string' );
		$cookie_ssid    = Request::post( 'cookie_ssid', '', 'string' );
		$cookie_sapisid = Request::post( 'cookie_sapisid', '', 'string' );
		$proxy          = Request::post( 'proxy', '', 'string' );

		if ( empty( $cookie_sid ) || empty( $cookie_hsid ) || empty( $cookie_ssid ) || empty( $cookie_sapisid ) )
		{
			Helper::response( FALSE, 'Please type your Cookies!' );
		}

		$google = new GoogleMyBusiness( $cookie_sid, $cookie_hsid, $cookie_ssid, $cookie_sapisid, $proxy );
		$data   = $google->getUserInfo();

		if ( empty( $data[ 'id' ] ) )
		{
			Helper::response( FALSE, 'The entered cookies are wrong!' );
		}

		$options = json_encode( [
			'sid'     => $cookie_sid,
			'hsid'    => $cookie_hsid,
			'ssid'    => $cookie_ssid,
			'sapisid' => $cookie_sapisid,
		] );

		$sqlData = [
			'proxy'     => $proxy,
			'options'   => $options,
			'status'    => NULL,
			'error_msg' => NULL
		];

		$googleUser = DB::fetch( 'accounts', [
			'blog_id' => Helper::getBlogId(),
			'driver'  => 'google_b',
			'id'      => $id
		] );

		if ( $googleUser )
		{
			if ( $googleUser[ 'profile_id' ] !== $data[ 'id' ] )
			{
				Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
			}

			$is_owner   = $googleUser[ 'user_id' ] == get_current_user_id();
			$is_public  = (bool) $googleUser[ 'is_public' ];
			$can_update = $is_owner || $is_public;

			if ( $can_update )
			{
				DB::DB()->update( DB::table( 'accounts' ), $sqlData, [ 'id' => $id ] );
				Helper::response( TRUE );
			}

		}

		Helper::response( FALSE, 'The entered cookies are wrong!' );
	}

	public function save_webhook_account ()
	{
		$id          = Request::post( 'id', 0, 'int' );
		$icon        = Request::post( 'icon', '', 'string' );
		$name        = Request::post( 'name', '', 'string' );
		$method      = Request::post( 'method', 'post', 'string', [
			'post',
			'get',
			'put',
			'delete'
		] );
		$url         = Request::post( 'url', '', 'string' );
		$headers     = Request::post( 'headers', [], 'array' );
		$postContent = Request::post( 'content', 'none', 'string', [
			'none',
			'form',
			'json'
		] );
		$json        = Request::post( 'json', '', 'string' );
		$form        = Request::post( 'form', [], 'array' );
		$proxy       = Request::post( 'proxy', NULL, 'string' );

		$urlParsed = parse_url( $url, PHP_URL_HOST );

		$error_msg = '';
		if ( ! get_current_user_id() > 0 )
		{
			$error_msg = fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' );
		}
		else if ( empty( $name ) )
		{
			$error_msg = fsp__( 'Name cannot be left empty!' );
		}
		else if ( filter_var( $url, FILTER_VALIDATE_URL ) === FALSE || empty( $urlParsed ) )
		{
			$error_msg = fsp__( 'The request URL must be a valid url!' );
		}
		else if ( $postContent === 'json' && empty( json_decode( $json, TRUE ) ) )
		{
			$error_msg = fsp__( 'The JSON data must be valid JSON' );
		}

		if ( ! empty( $error_msg ) )
		{
			Helper::response( FALSE, $error_msg );
		}

		$webhook = [
			'title'        => $name,
			'method'       => $method,
			'url'          => $url,
			'post_content' => $postContent,
			'headers'      => $headers
		];

		if ( ! empty( $icon ) )
		{
			$webhook[ 'icon' ] = $icon;
		}

		if ( ! empty( $proxy ) )
		{
			$webhook[ 'proxy' ] = $proxy;
		}

		if ( $method === 'post' || $method === 'put' )
		{
			if ( $postContent === 'json' )
			{
				$webhook[ 'json' ] = $json;
			}
			else if ( $postContent === 'form' )
			{
				$webhook[ 'form_data' ] = $form;
			}
		}

		$dataSQL = [
			'user_id'     => get_current_user_id(),
			'driver'      => 'webhook',
			'name'        => $name,
			'username'    => $urlParsed,
			'profile_pic' => $icon,
			'options'     => json_encode( $webhook ),
			'proxy'       => $proxy,
			'blog_id'     => Helper::getBlogId()
		];

		if ( $id > 0 )
		{
			$ifExists = DB::fetch( 'accounts', [
				'id'     => $id,
				'driver' => 'webhook'
			] );

			if ( empty( $ifExists ) )
			{
				Helper::response( FALSE, fsp__( 'Account doesn\'t exists!' ) );
			}

			$dataSQL[ 'id' ] = $id;

			DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [
				'id' => $id
			] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );
		}

		Helper::response( TRUE );
	}

	public function test_webhook_request ()
	{
		$method      = Request::post( 'method', 'post', 'string', [
			'post',
			'get',
			'put',
			'delete'
		] );
		$url         = Request::post( 'url', '', 'string' );
		$headers     = Request::post( 'headers', [], 'array' );
		$postContent = Request::post( 'content', 'none', 'string', [
			'none',
			'form',
			'json'
		] );
		$json        = Request::post( 'json', '', 'string' );
		$form        = Request::post( 'form', [], 'array' );
		$proxy       = Request::post( 'proxy', NULL, 'string' );

		$options = [
			'headers' => $headers
		];

		if ( ! empty( $proxy ) )
		{
			$options[ 'proxy' ] = $proxy;
		}

		if ( $method === 'post' || $method === 'put' )
		{
			if ( $postContent === 'json' )
			{
				$options[ 'body' ] = $json;
			}
			else if ( $postContent === 'form' )
			{
				$options[ 'form_params' ] = $form;
			}
		}

		try
		{
			$client = new Client();
			$client->request( strtoupper( $method ), $url, $options );
		}
		catch ( \Exception $e )
		{
		}

		Helper::response( TRUE );
	}

	public function bulk_account_action ()
	{
		$action = Request::post( 'act', '', 'string', [
			'public',
			'private',
			'activate',
			'activate_all',
			'activate_condition',
			'deactivate',
			'deactivate_all',
			'delete',
			'hide',
			'unhide'
		] );
		$ids    = Request::post( 'ids', '', 'array' );

		if ( empty( $action ) || ( ! ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) && ( $action === 'activate_all' || $action === 'deactivate_all' ) ) )
		{
			Helper::response( FALSE, fsp__( 'Required action not found!' ) );
		}

		if ( empty( $ids ) )
		{
			Helper::response( FALSE, fsp__( 'You didn\'t select any account!' ) );
		}

		$res = Pages::action( 'Accounts', 'bulk_action_' . $action, $ids );

		if ( $res[ 'status' ] !== TRUE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function bulk_activate_conditionally ()
	{
		$ids         = Request::post( 'ids', '', 'string' );
		$for_all     = Request::post( 'for_all', 0, 'int', [ '0', '1' ] );
		$filter_type = Request::post( 'filter_type', '', 'string', [ 'in', 'ex' ] );
		$categories  = Request::post( 'categories', [], 'array' );

		if ( empty( $ids ) )
		{
			Helper::response( FALSE );
		}

		try
		{
			$ids = json_decode( $ids, TRUE );
		}
		catch ( Exception $e )
		{
			Helper::response( FALSE );
		}

		$categories_arr = [];
		foreach ( $categories as $categId )
		{
			if ( is_numeric( $categId ) && $categId > 0 )
			{
				$categories_arr[] = (int) $categId;
			}
		}
		$categories_arr = implode( ',', $categories_arr );

		if ( ( ! empty( $categories_arr ) && empty( $filter_type ) ) || ( empty( $categories_arr ) && ! empty( $filter_type ) ) )
		{
			Helper::response( FALSE, fsp__( 'Please select categories and filter type!' ) );
		}

		$categories_arr = empty( $categories_arr ) ? NULL : $categories_arr;
		$filter_type    = empty( $filter_type ) || empty( $categories_arr ) ? 'no' : $filter_type;
		$for_all        = $for_all && ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) );

		$res = Action::bulk_action_activate_condition( $ids, $filter_type, $categories_arr, $for_all );

		if ( $res[ 'status' ] === FALSE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function delete_account ()
	{
		$id = Request::post( 'id', 0, 'num' );

		if ( ! ( $id > 0 ) )
		{
			exit();
		}

		$res = Action::delete_account( $id );

		if ( $res[ 'status' ] === FALSE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function get_accounts ()
	{
		$social_networks = [
			'fb',
			'instagram',
			'threads',
			'twitter',
			'planly',
			'linkedin',
			'pinterest',
			'telegram',
			'reddit',
			'youtube_community',
			'tumblr',
			'ok',
			'vk',
			'google_b',
			'medium',
			'wordpress',
			'webhook',
			'blogger',
			'plurk',
			'xing',
			'discord',
			'mastodon'
		];
		$name            = Request::post( 'name', '', 'string' );
		$filter_by       = Request::post( 'filter_by', 'all', 'string', [
			'all',
			'active',
			'inactive',
			'visible',
			'hidden',
			'failed'
		] );

		if ( empty( $name ) || ! in_array( $name, $social_networks ) )
		{
			Helper::response( FALSE );
		}

		$data = Pages::action( 'Accounts', 'get_' . $name . '_accounts', $filter_by );

		if ( $name === 'telegram' )
		{
			$data[ 'button_text' ] = fsp__( 'ADD A BOT' );
			$data[ 'err_text' ]    = fsp__( 'bots' );
		}
		else if ( $name === 'wordpress' )
		{
			$data[ 'button_text' ] = fsp__( 'ADD A SITE' );
			$data[ 'err_text' ]    = fsp__( 'sites' );
		}
		else if ( $name === 'webhook' )
		{
			$data[ 'button_text' ] = fsp__( 'ADD A WEBHOOK' );
			$data[ 'err_text' ]    = fsp__( 'requests' );
		}
		else
		{
			$data[ 'button_text' ] = fsp__( 'ADD AN ACCOUNT' );
			$data[ 'err_text' ]    = fsp__( 'accounts' );
		}

		Pages::modal( 'Accounts', $name . '/index', $data, [ 'button_text' => $data[ 'button_text' ] ] );
	}

	public function get_tags_and_cats ()
	{
		$search  = Request::post( 'search', '', 'string' );
		$not_all = Request::post( 'not_all', '0', 'int', [ 0, 1 ] ) == 1;

		$search        = mb_strlen( $search ) > 1 ? $search : NULL;
		$tags_and_cats = CatWalker::get_cats( $search );

		if ( ! $not_all )
		{
			$tags_and_cats[] = [
				'children' => [
					[
						'text' => 'All',
						'id'   => ''
					]
				]
			];
		}

		Helper::response( TRUE, [ 'result' => $tags_and_cats ] );
	}

	public function hide_unhide_account ()
	{
		$id      = Request::post( 'id', '0', 'num' );
		$checked = Request::post( 'hidden', 0, 'num', [ '0', '1' ] );

		if ( ! ( $id > 0 && $checked >= 0 ) )
		{
			Helper::response( FALSE );
		}

		$res = Action::hide_unhide_account( $id, $checked );

		if ( $res[ 'status' ] === FALSE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function hide_unhide_node ()
	{
		$id      = Request::post( 'id', '0', 'num' );
		$checked = Request::post( 'hidden', 0, 'num', [ '0', '1' ] );

		if ( ! ( $id > 0 && $checked >= 0 ) )
		{
			Helper::response( FALSE );
		}

		$res = Action::hide_unhide_node( $id, $checked );

		if ( $res[ 'status' ] === FALSE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function make_account_public ()
	{
		$id      = Request::post( 'id', 0, 'num' );
		$checked = Request::post( 'checked', '', 'string' );

		if ( ! ( ( $checked === '1' || $checked === '0' ) && $id > 0 ) )
		{
			Helper::response( FALSE );
		}

		$res = Action::public_private_account( $id, $checked );

		if ( $res[ 'status' ] === FALSE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function instagram_confirm_challenge ()
	{
		$username   = Request::post( 'username', '', 'string' );
		$password   = Request::post( 'password', '', 'string' );
		$proxy      = Request::post( 'proxy', '', 'string' );
		$code       = Request::post( 'code', '', 'string' );
		$user_id    = Request::post( 'user_id', '', 'string' );
		$nonce_code = Request::post( 'nonce_code', '', 'string' );

		if ( empty( $username ) || empty( $password ) || empty( $code ) || empty( $user_id ) || empty( $nonce_code ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter the code!' ) ] );
		}

		$ig     = new InstagramLoginPassMethod( $username, $password, $proxy );
		$result = $ig->finishChallenge( $code, $nonce_code, $user_id );

		InstagramApi::handleResponse( $result, $username, $password, $proxy );
	}

	public function instagram_confirm_two_factor ()
	{
		$username              = Request::post( 'username', '', 'string' );
		$password              = Request::post( 'password', '', 'string' );
		$proxy                 = Request::post( 'proxy', '', 'string' );
		$code                  = Request::post( 'code', '', 'string' );
		$two_factor_identifier = Request::post( 'two_factor_identifier', '', 'string' );

		if ( empty( $username ) || empty( $password ) || empty( $code ) || empty( $two_factor_identifier ) )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'Please enter the code!' ) ] );
		}

		$ig     = new InstagramLoginPassMethod( $username, $password, $proxy );
		$result = $ig->finishTwoFactorLogin( $two_factor_identifier, $code );

		InstagramApi::handleResponse( $result, $username, $password, $proxy );
	}

	public function reddit_get_subreddt_flairs ()
	{
		$accountId = Request::post( 'account_id', '0', 'num' );
		$subreddit = Request::post( 'subreddit', '', 'string' );

		$subreddit = basename( $subreddit );
		$userId    = get_current_user_id();

		$account_info = DB::DB()->get_row( DB::DB()->prepare( "SELECT * FROM " . DB::table( 'accounts' ) . " tb1 WHERE id=%d AND driver='reddit' AND (user_id=%d OR is_public=1)", [
			$accountId,
			$userId
		] ), ARRAY_A );

		if ( ! $account_info )
		{
			Helper::response( FALSE, fsp__( 'You have not a permission for adding subreddit in this account!' ) );
		}

		$accessTokenGet = DB::fetch( 'account_access_tokens', [ 'account_id' => $accountId ] );
		$accessToken    = Reddit::accessToken( $accessTokenGet );
		$flairs         = Reddit::cmd( 'https://oauth.reddit.com/r/' . $subreddit . '/api/link_flair', 'GET', $accessToken, $account_info[ 'proxy' ] );

		$new_arr = [];

		if ( ! isset( $flairs[ 'error' ] ) )
		{
			foreach ( $flairs as $flair )
			{
				$new_arr[] = [
					'text' => htmlspecialchars( $flair[ 'text' ] ),
					'id'   => htmlspecialchars( $flair[ 'id' ] )
				];
			}

			if ( ! empty( $new_arr ) )
			{
				array_unshift( $new_arr, [
					'id'   => '',
					'text' => fsp__( 'no flair' )
				] );
			}
		}

		Helper::response( TRUE, [ 'flairs' => $new_arr ] );
	}

	public function reddit_subreddit_save ()
	{
		$accountId = Request::post( 'account_id', '0', 'num' );
		$subreddit = Request::post( 'subreddit', '', 'string' );
		$flairId   = Request::post( 'flair', '', 'string' );
		$flairName = Request::post( 'flair_name', '', 'string' );

		if ( ! ( ! empty( $subreddit ) && $accountId > 0 ) )
		{
			Helper::response( FALSE );
		}

		$userId = get_current_user_id();

		$account_info = DB::DB()->get_row( DB::DB()->prepare( "SELECT * FROM " . DB::table( 'accounts' ) . " WHERE id=%d AND driver='reddit' AND (user_id=%d OR is_public=1)", [
			$accountId,
			$userId
		] ), ARRAY_A );

		if ( ! $account_info )
		{
			Helper::response( FALSE, fsp__( 'You have not a permission for adding subreddit in this account!' ) );
		}

		DB::DB()->insert( DB::table( 'account_nodes' ), [
			'blog_id'      => Helper::getBlogId(),
			'user_id'      => $userId,
			'driver'       => 'reddit',
			'account_id'   => $accountId,
			'node_type'    => 'subreddit',
			'screen_name'  => $subreddit,
			'name'         => $subreddit,
			'access_token' => $flairId,
			'category'     => $flairName
		] );

		$nodeId = DB::DB()->insert_id;

		Helper::response( TRUE, [ 'id' => $nodeId ] );
	}

	public function refetch_account ()
	{
		$account_id = Request::post( 'account_id', 0, 'int' );

		if ( ! ( $account_id > 0 ) )
		{
			Helper::response( FALSE, fsp__( 'Account not found!' ) );
		}

		$get_account = DB::DB()->get_row( DB::DB()->prepare( 'SELECT * FROM ' . DB::table( 'accounts' ) . ' WHERE id = %d AND ( user_id = %d OR is_public = 1 ) AND blog_id = %d', [
			$account_id,
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		if ( ! $get_account )
		{
			Helper::response( FALSE, fsp__( 'Account not found!' ) );
		}

		if ( $get_account[ 'status' ] === 'error' )
		{
			Helper::response( FALSE, fsp__( 'Failed accounts can not be re-fetched. Please add your account to the plugin without deleting the account from the plugin; as a result, account settings will remain as it is.' ) );
		}

		$info                = Helper::getAccessToken( 'account', $account_id );
		$driver              = $info[ 'driver' ];
		$access_token        = $info[ 'access_token' ];
		$access_token_secret = $info[ 'access_token_secret' ];
		$proxy               = $info[ 'info' ][ 'proxy' ];
		$options             = $info[ 'options' ];
		$app_id              = $info[ 'app_id' ];
		$profile_id          = $info[ 'account_id' ];
		$password            = $info[ 'password' ];
		$email               = $info[ 'email' ];

		if ( is_array( $access_token ) )
		{
			Helper::response( FALSE, $access_token[ 'error_msg' ] );
		}

		if ( $driver === 'fb' )
		{
			if ( empty( $options ) )
			{
				$app_info = DB::fetch( 'apps', [ 'id' => $app_id ] );
				$fb       = new Facebook( $app_info, $access_token, $proxy );
				$res      = $fb->refetch_account( $account_id );
			}
			else
			{
				$fb  = new FacebookCookieApi( $profile_id, $options, $proxy );
				$res = $fb->refetch_account( $account_id );
			}
		}
		else if ( $driver === 'instagram' )
		{
			if ( $password == '#####' )
			{
				$res = InstagramAppMethod::refetch_account( $account_id, $access_token, $proxy );
			}
		}
		else if ( $driver === 'linkedin' )
		{
			$res = Linkedin::refetch_account( $account_id, $access_token, $proxy );
		}
		else if ( $driver === 'vk' )
		{
			$res = Vk::refetch_account( $account_id, $access_token, $proxy );
		}
		else if ( $driver === 'pinterest' )
		{
			if ( empty( $options ) )
			{
				$res = Pinterest::refetch_account( $account_id, $access_token, $proxy );
			}
			else
			{
				$getCookie = DB::fetch( 'account_sessions', [
					'driver'   => 'pinterest',
					'username' => $info[ 'username' ]
				] );

				$pinterest = new PinterestCookieApi( $getCookie[ 'cookies' ], $proxy );
				$res       = $pinterest->refetch_account( $account_id );
			}
		}
		else if ( $driver === 'tumblr' )
		{
			if ( empty( $password ) )
			{
				$app_info = DB::fetch( 'apps', [ 'id' => $app_id ] );

				$res = Tumblr::refetch_account( $account_id, $app_info[ 'app_key' ], $app_info[ 'app_secret' ], $access_token, $access_token_secret, $proxy );
			}
			else
			{
				$tm  = new TumblrLoginPassMethod( $email, $password, $proxy );
				$res = $tm->refetchAccount();
			}
		}
		else if ( $driver === 'ok' )
		{
			$app_info = DB::fetch( 'apps', [ 'id' => $app_id ] );

			$res = OdnoKlassniki::refetch_account( $account_id, $access_token, $app_info[ 'app_key' ], $app_info[ 'app_secret' ], $proxy );
		}
		else if ( $driver === 'google_b' )
		{
			if( empty( $options ) )
			{
				$res = GoogleMyBusinessAPI::refetch_account( $access_token, $account_id, $profile_id, $proxy );
			}
			else
			{
				$options = json_decode( $options, TRUE );
				$res = ( new GoogleMyBusiness( $options['sid'], $options['hsid'], $options['ssid'], $options['sapisid'], $proxy ) )->refetch_account( $account_id );
			}
		}
		else if ( $driver === 'blogger' )
		{
			$app_info = DB::fetch( 'apps', [ 'id' => $app_id ] );
			$res      = Blogger::refetch_account( $app_info, $access_token, $proxy );
		}
		else if ( $driver === 'medium' )
		{
			$res = Medium::refetch_account( $account_id, $profile_id, $access_token, $proxy );
		}
		else if ( $driver === 'xing' )
		{
			if ( empty( $options ) )
			{
				Helper::response( FALSE );
			}

			$res = ( new Xing( json_decode( $options, TRUE ), $proxy ) )->refetchAccount( $account_id );
		}
		else if ( $driver === 'discord' )
		{
			if ( empty( $options ) )
			{
				Helper::response( FALSE );
			}

			$botInfo = json_decode( $options, TRUE );

			if ( empty( $botInfo[ 'bot_token' ] ) )
			{
				Helper::response( FALSE );
			}

			$res = Discord::refetchAccount( $account_id, $profile_id, $botInfo[ 'bot_token' ], $proxy );
		}
		else if ( $driver === 'planly' )
		{
			if ( empty( $options ) )
			{
				Helper::response( FALSE );
			}

			$res = ( new Planly( $options, $proxy ) )->refetch( $account_id );
		}
		else
		{
			$res = [ 'status' => FALSE, 'error_msg' => fsp__( 'Re-fetching failed!' ) ];
		}

		if ( $res[ 'status' ] === FALSE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function telegram_chat_save ()
	{
		$account_id = Request::post( 'account_id', '', 'int' );
		$chat_id    = Request::post( 'chat_id', '', 'string' );

		if ( empty( $account_id ) || empty( $chat_id ) )
		{
			Helper::response( FALSE );
		}

		$account_info = DB::fetch( 'accounts', [ 'id' => $account_id ] );
		if ( ! $account_info )
		{
			Helper::response( FALSE );
		}

		$tg   = new Telegram( $account_info[ 'options' ], $account_info[ 'proxy' ] );
		$data = $tg->getChatInfo( $chat_id );

		if ( empty( $data[ 'id' ] ) )
		{
			Helper::response( FALSE, fsp__( 'Chat not found!' ) );
		}

		DB::DB()->insert( DB::table( 'account_nodes' ), [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
			'account_id'  => $account_id,
			'node_type'   => 'chat',
			'node_id'     => $data[ 'id' ],
			'name'        => $data[ 'name' ],
			'screen_name' => $data[ 'username' ],
			'category'    => $data[ 'type' ],
			'driver'      => 'telegram'
		] );

		Helper::response( TRUE, [
			'id'        => DB::DB()->insert_id,
			'chat_pic'  => Pages::asset( 'Base', 'img/telegram.svg' ),
			'chat_name' => htmlspecialchars( $data[ 'name' ] ),
			'chat_link' => Helper::profileLink( [ 'driver' => 'telegram', 'username' => $data[ 'username' ] ] )
		] );
	}

	public function telegram_last_active_chats ()
	{
		$account_id = Request::post( 'account', '', 'int' );

		if ( ! ( is_numeric( $account_id ) && $account_id > 0 ) )
		{
			Helper::response( FALSE );
		}

		$account_info = DB::fetch( 'accounts', [ 'id' => $account_id ] );
		if ( ! $account_info )
		{
			Helper::response( FALSE );
		}

		$tg   = new Telegram( $account_info[ 'options' ], $account_info[ 'proxy' ] );
		$data = $tg->getActiveChats();

		Helper::response( TRUE, [ 'list' => $data ] );
	}

	public function discord_get_available_channels ()
	{
		$accountId = Request::post( 'account_id', '', 'int' );

		if ( ! ( is_numeric( $accountId ) && $accountId > 0 ) )
		{
			Helper::response( FALSE );
		}

		$accountInfo = DB::DB()->get_row( DB::DB()->prepare( 'select profile_id, options, proxy from `' . DB::table( 'accounts' ) . '` where id=%d', $accountId ), ARRAY_A );

		if ( ! $accountInfo || empty( $accountInfo[ 'profile_id' ] ) || empty( $accountInfo[ 'options' ] ) )
		{
			Helper::response( FALSE );
		}

		$accountInfo[ 'id' ] = $accountId;

		$data = Discord::getGuildChannels( $accountInfo );

		if ( empty( $data ) )
		{
			Helper::response( FALSE, fsp__( 'No available channel(s) found.' ) );
		}

		if ( ! empty( $data[ 'status' ] === 'error' ) )
		{
			Helper::response( FALSE, [ 'error_msg' => $data[ 'error_msg' ] ] );
		}

		Helper::response( TRUE, [ 'list' => $data ] );
	}

	public function discord_save_channels ()
	{
		$accountId = Request::post( 'account_id', '', 'int' );
		$channel   = Request::post( 'channel', '', 'string' );

		if ( empty( $accountId ) || empty( $channel ) || ( ! ( is_numeric( $accountId ) && $accountId > 0 ) ) )
		{
			Helper::response( FALSE );
		}

		list( $channelId, $channelName ) = explode( '|', $channel );

		if ( empty( $channelId ) || empty( $channelName ) )
		{
			Helper::response( FALSE );
		}

		$accountInfo = DB::DB()->get_row( DB::DB()->prepare( 'select 1 from `' . DB::table( 'accounts' ) . '` where id=%d', $accountId ), ARRAY_A );

		if ( ! $accountInfo )
		{
			Helper::response( FALSE );
		}

		$check = DB::DB()->get_row( DB::DB()->prepare( 'select id from `' . DB::table( 'account_nodes' ) . '` where account_id=%d and node_id=%s', [
			$accountId,
			$channelId
		] ), ARRAY_A );

		if ( empty( $check ) || empty( $check[ 'id' ] ) )
		{
			DB::DB()->insert( DB::table( 'account_nodes' ), [
				'name'       => $channelName,
				'driver'     => 'discord',
				'user_id'    => get_current_user_id(),
				'blog_id'    => Helper::getBlogId(),
				'node_id'    => $channelId,
				'node_type'  => 'channel',
				'account_id' => $accountId
			] );
		}
		else
		{
			DB::DB()->update( DB::table( 'account_nodes' ), [ 'name' => $channelName ], [ 'id' => $check[ 'id' ] ] );
		}

		Helper::response( TRUE );
	}

	public function settings_node_activity_change ()
	{
		$id          = Request::post( 'id', '0', 'num' );
		$checked     = Request::post( 'checked', -1, 'num', [ '0', '1' ] );
		$for_all     = Request::post( 'for_all', 0, 'int', [ '0', '1' ] );
		$filter_type = Request::post( 'filter_type', '', 'string', [ 'in', 'ex' ] );
		$categories  = Request::post( 'categories', [], 'array' );

		if ( ! ( $id > 0 && $checked > -1 ) )
		{
			Helper::response( FALSE );
		}

		$categories_arr = [];
		foreach ( $categories as $categId )
		{
			if ( is_numeric( $categId ) && $categId > 0 )
			{
				$categories_arr[] = (int) $categId;
			}
		}
		$categories_arr = implode( ',', $categories_arr );

		if ( ( ! empty( $categories_arr ) && empty( $filter_type ) ) || ( empty( $categories_arr ) && ! empty( $filter_type ) ) )
		{
			Helper::response( FALSE, fsp__( 'Please select categories and filter type!' ) );
		}

		$categories_arr = empty( $categories_arr ) ? NULL : $categories_arr;
		$filter_type    = empty( $filter_type ) ? 'no' : $filter_type;
		$for_all        = $for_all && ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) );

		$res = Action::activate_deactivate_node( get_current_user_id(), $id, $checked, $filter_type, $categories_arr, $for_all );

		if ( $res[ 'status' ] === FALSE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function search_subreddits ()
	{
		$accountId = Request::post( 'account_id', '0', 'num' );
		$search    = Request::post( 'search', '', 'string' );

		$userId = get_current_user_id();

		$account_info = DB::DB()->get_row( DB::DB()->prepare( "SELECT * FROM " . DB::table( 'accounts' ) . " tb1 WHERE id=%d AND driver='reddit' AND (user_id=%d OR is_public=1) ", [
			$accountId,
			$userId
		] ), ARRAY_A );

		if ( ! $account_info )
		{
			Helper::response( FALSE, fsp__( 'You have not a permission for adding subreddit in this account!' ) );
		}

		$accessTokenGet = DB::fetch( 'account_access_tokens', [ 'account_id' => $accountId ] );

		$accessToken = $accessTokenGet[ 'access_token' ];

		if ( ( Date::epoch() + 30 ) > Date::epoch( $accessTokenGet[ 'expires_on' ] ) )
		{
			$accessToken = Reddit::refreshToken( $accessTokenGet );
		}

		$searchSubreddits = Reddit::cmd( 'https://oauth.reddit.com/api/search_subreddits', 'POST', $accessToken, $account_info[ 'proxy' ], [
			'query'                  => $search,
			'include_over_18'        => TRUE,
			'exact'                  => FALSE,
			'include_unadvertisable' => TRUE
		] );

		$new_arr           = [];
		$preventDublicates = [];

		foreach ( $searchSubreddits[ 'subreddits' ] as $subreddit )
		{
			$preventDublicates[ $subreddit[ 'name' ] ] = TRUE;

			$new_arr[] = [
				'text' => htmlspecialchars( $subreddit[ 'name' ] . ' ( ' . $subreddit[ 'subscriber_count' ] . ' subscribers )' ),
				'id'   => htmlspecialchars( $subreddit[ 'name' ] )
			];
		}

		// for fixing Reddit API bug
		$searchSubreddits = Reddit::cmd( 'https://oauth.reddit.com/api/search_subreddits', 'POST', $accessToken, $account_info[ 'proxy' ], [
			'query' => $search,
			'exact' => TRUE
		] );

		foreach ( $searchSubreddits[ 'subreddits' ] as $subreddit )
		{
			if ( isset( $preventDublicates[ $subreddit[ 'name' ] ] ) )
			{
				continue;
			}

			$new_arr[] = [
				'text' => htmlspecialchars( $subreddit[ 'name' ] . ' ( ' . $subreddit[ 'subscriber_count' ] . ' subscribers )' ),
				'id'   => htmlspecialchars( $subreddit[ 'name' ] )
			];
		}

		Helper::response( TRUE, [ 'subreddits' => $new_arr ] );
	}

	public function settings_node_make_public ()
	{
		$id      = Request::post( 'id', 0, 'num' );
		$checked = Request::post( 'checked', '', 'string' );

		if ( ! ( ( $checked === '1' || $checked === '0' ) && $id > 0 ) )
		{
			Helper::response( FALSE );
		}

		$res = Action::public_private_node( $id, $checked );

		if ( $res[ 'status' ] === FALSE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function settings_node_delete ()
	{
		$id = Request::post( 'id', 0, 'num' );

		if ( ! $id > 0 )
		{
			Helper::response( FALSE );
		}

		$res = Action::delete_node( $id );

		if ( $res[ 'status' ] === FALSE )
		{
			Helper::response( FALSE, $res[ 'error_msg' ] );
		}

		Helper::response( TRUE );
	}

	public function save_fb_group_poster ()
	{
		$group_id = Request::post( 'group_id', 0, 'int' );
		$page_id  = Request::post( 'page_id', 0, 'int' );

		if ( ! ( $group_id > 0 ) )
		{
			Helper::response( FALSE, fsp__( 'Group not found!' ) );
		}

		$get_group = DB::DB()->get_row( DB::DB()->prepare( "SELECT account_id FROM " . DB::table( 'account_nodes' ) . " WHERE id = %d AND node_type = 'group' AND ( user_id = %d OR is_public = 1 ) AND blog_id = %d", [
			$group_id,
			get_current_user_id(),
			Helper::getBlogId()
		] ), ARRAY_A );

		if ( ! $get_group )
		{
			Helper::response( FALSE, fsp__( 'Group not found!' ) );
		}

		if ( $page_id > 0 )
		{
			$get_page = DB::DB()->get_row( DB::DB()->prepare( "SELECT name, node_id FROM " . DB::table( 'account_nodes' ) . " WHERE id = %d AND node_type = 'ownpage' AND ( user_id = %d OR is_public = 1 ) AND blog_id = %d", [
				$page_id,
				get_current_user_id(),
				Helper::getBlogId()
			] ), ARRAY_A );

			if ( ! $get_page )
			{
				Helper::response( FALSE, fsp__( 'Page not found!' ) );
			}

			$id = $get_page[ 'node_id' ];
		}
		else
		{
			$id = NULL;
		}

		DB::DB()->update( DB::table( 'account_nodes' ), [
			'poster_id' => $id
		], [
			'id' => $group_id
		] );

		Helper::response( TRUE, [ 'message' => fsp__( 'Saved successfully!' ) ] );
	}

	function save_custom_settings ()
	{
		if ( ! ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) )
		{
			exit();
		}

		$receivedParams = array_keys( $_POST );

		$node_id     = Request::post( 'fs_node_id', '0', 'num' );
		$node_type   = Request::post( 'fs_node_type', 'account', 'string', [ 'account', 'node' ] );
		$node_driver = Request::post( 'fs_node_driver', '', 'string' );

		$fs_instagram_update_bio_link = Request::post( 'fs_instagram_update_bio_link', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_checkbox_posting_type     = Request::post( 'fs_checkbox_posting_type', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;
		$fs_posting_type              = Request::post( 'fs_posting_type', 0, 'num', [
			'1',
			'2',
			'3',
			'4',
			'5'
		] );

		$requestData = [
			'fs_unique_link'                              => Request::post( 'fs_unique_link', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0,
			'fs_url_shortener'                            => Request::post( 'fs_url_shortener', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0,
			'fs_shortener_service'                        => Request::post( 'fs_shortener_service', 'tinyurl', 'string', [
				'tinyurl',
				'bitly',
				'yourls',
				'polr',
				'shlink',
				'rebrandly'
			] ),
			'fs_url_short_access_token_bitly'             => Request::post( 'fs_url_short_access_token_bitly', '', 'string' ),
			'fs_url_short_api_url_yourls'                 => Request::post( 'fs_url_short_api_url_yourls', '', 'string' ),
			'fs_url_short_api_token_yourls'               => Request::post( 'fs_url_short_api_token_yourls', '', 'string' ),
			'fs_url_short_api_url_polr'                   => Request::post( 'fs_url_short_api_url_polr', '', 'string' ),
			'fs_url_short_api_key_polr'                   => Request::post( 'fs_url_short_api_key_polr', '', 'string' ),
			'fs_url_short_api_url_shlink'                 => Request::post( 'fs_url_short_api_url_shlink', '', 'string' ),
			'fs_url_short_api_key_shlink'                 => Request::post( 'fs_url_short_api_key_shlink', '', 'string' ),
			'fs_url_short_domain_rebrandly'               => Request::post( 'fs_url_short_domain_rebrandly', 'rebrand.ly', 'string' ),
			'fs_url_short_api_key_rebrandly'              => Request::post( 'fs_url_short_api_key_rebrandly', '', 'string' ),
			'fs_url_additional'                           => Request::post( 'fs_url_additional', '', 'string' ),
			'fs_share_custom_url'                         => Request::post( 'fs_share_custom_url', '', 'string' ),
			'fs_custom_url_to_share'                      => Request::post( 'fs_custom_url_to_share', '', 'string' ),
			'fs_account_post_message'                     => Request::post( 'fs_account_post_message', '', 'string' ),
			'fs_fetch_facebook_comments'                  => Request::post( 'fs_fetch_facebook_comments', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0,
			'fs_fb_fetch_comments_for_posts_published_at' => Request::post( 'fs_fb_fetch_comments_for_posts_published_at', 30, 'int', [
				7,
				14,
				21,
				30
			] )
		];

		$requestData[ 'fs_custom_url_to_share' ] = str_replace( ' ', '', $requestData[ 'fs_custom_url_to_share' ] );
		$requestData[ 'fs_url_additional' ]      = str_replace( ' ', '', $requestData[ 'fs_url_additional' ] );

		if ( in_array( 'fs_instagram_update_bio_link', $receivedParams ) && $node_driver == 'instagram' && $node_type == 'account' )
		{
			$password = DB::DB()->get_row( DB::DB()->prepare( 'SELECT `password` FROM `' . DB::table( 'accounts' ) . '` WHERE id=%d',
				[
					$node_id
				] ), 'ARRAY_A' );

			if ( $password[ 'password' ] != '#####' )
			{
				Helper::setCustomSetting( 'update_bio_link', (string) $fs_instagram_update_bio_link, $node_type, $node_id );
			}
		}

		if ( in_array( 'fs_posting_type', $receivedParams ) && ! empty( Action::getNodeCustomPostingTypeSettings( $node_driver ) ) )
		{
			if ( $node_driver === 'blogger' || $node_driver === 'wordpress' )
			{
				$fs_posting_type = $fs_checkbox_posting_type;
			}

			Helper::setCustomSetting( 'posting_type', $fs_posting_type, $node_type, $node_id );
		}

		foreach ( $requestData as $key => $value )
		{
			if ( in_array( $key, $receivedParams ) )
			{
				Helper::setCustomSetting( str_replace( 'fs_', '', $key ), (string) $value, $node_type, $node_id );
			}
		}
		
		if ( in_array( 'fs_post_allow_first_comment', $receivedParams ) && in_array( $node_driver, [
				'instagram',
				'twitter',
				'reddit'
			] ) )
		{
			$fs_post_allow_first_comment = Request::post( 'fs_post_allow_first_comment', 0, 'string', [ 'on' ] ) === 'on' ? 1 : 0;

			Helper::setCustomSetting( 'post_allow_first_comment', $fs_post_allow_first_comment, $node_type, $node_id );
		}

		if( in_array( 'fs_post_first_comment', $receivedParams ) && in_array( $node_driver, [
				'instagram',
				'twitter',
				'reddit'
			] ) )
		{
			$fs_post_first_comment = Request::post( 'fs_post_first_comment', '', 'string' );

			Helper::setCustomSetting( 'post_first_comment', $fs_post_first_comment, $node_type, $node_id );
		}

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	function reset_custom_settings ()
	{
		if ( ! ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) )
		{
			exit();
		}

		$node_id   = Request::post( 'fs_node_id', '0', 'num' );
		$node_type = Request::post( 'fs_node_type', 'account', 'string', [ 'account', 'node' ] );

		Helper::deleteCustomSettings( $node_type, $node_id );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Saved successfully!' ) ] );
	}

	public function get_group_nodes ()
	{
		$group_id = Request::post( 'group_id', '', 'num' );

		if ( empty( $group_id ) )
		{
			Helper::response( FALSE );
		}

		$data = Action::get_group_nodes( $group_id );

		Pages::modal( 'Accounts', 'groups/index', $data );
	}

	public function create_account_group ()
	{
		$name  = Request::post( 'name', '', 'string' );
		$group = DB::fetch( 'account_groups', [ 'name' => $name ] );

		if ( empty( $name ) )
		{
			Helper::response( FALSE, fsp__( 'Name can\'t be empty' ) );
		}

		if ( ! empty( $group ) )
		{
			Helper::response( FALSE, fsp__( 'A group with this name already exists.' ) );
		}

		DB::DB()->insert( DB::table( 'account_groups' ), [
			'name'    => $name,
			'user_id' => get_current_user_id(),
			'blog_id' => Helper::getBlogId(),
			'color'   => '#55D56E'
		] );

		Helper::response( TRUE, [
			'status' => 'ok',
			'id'     => DB::DB()->insert_id
		] );
	}

	public function get_account_groups ()
	{
		$groups_table = DB::table( 'account_groups' );
		$groups       = DB::DB()->get_results(
			DB::DB()->prepare(
				"SELECT id, name AS text FROM `$groups_table` WHERE user_id=%d AND blog_id=%d",
				[
					get_current_user_id(),
					Helper::getBlogId()
				]
			),
			'ARRAY_A'
		);

		Helper::response( TRUE, [
			'status' => 'ok',
			'result' => $groups
		] );
	}

	public function add_to_groups ()
	{
		$node_id   = Request::post( 'node_id', '', 'num' );
		$node_type = Request::post( 'node_type', 'account', 'string', [ 'account', 'node' ] );
		$groups    = Request::post( 'groups', [], 'array' );

		$groups_table      = DB::table( 'account_groups' );
		$groups_data_table = DB::table( 'account_groups_data' );

		$delete_sql = "
			DELETE gdt FROM `$groups_data_table` gdt
			WHERE 
			      gdt.node_id=%d 
			  AND gdt.node_type=%s 
			  AND gdt.group_id 
			          IN (
			              SELECT gt.id 
			              FROM `$groups_table` gt 
			              WHERE 
			                    gt.user_id=%d 
			                AND gt.blog_id=%d
			              )
		";

		DB::DB()->query( DB::DB()->prepare( $delete_sql, [
			$node_id,
			$node_type,
			get_current_user_id(),
			Helper::getBlogId()
		] ) );

		$rows = [];

		foreach ( $groups as $group_id )
		{
			$rows[] = [ 'node_type' => $node_type, 'node_id' => $node_id, 'group_id' => $group_id ];
		}

		DB::insertAll( 'account_groups_data', [ 'node_type', 'node_id', 'group_id' ], $rows );

		Helper::response( TRUE );
	}

	public function add_node_to_group ()
	{
		$nodeId   = Request::post( 'node_id', '', 'num' );
		$nodeType = Request::post( 'node_type', 'account', 'string', [ 'account', 'node' ] );
		$group_id = Request::post( 'group_id', '', 'num' );

		$rows[] = [
			'node_type' => $nodeType,
			'node_id'   => $nodeId,
			'group_id'  => $group_id
		];

		DB::insertAll( 'account_groups_data', [ 'node_type', 'node_id', 'group_id' ], $rows );

		Helper::response( TRUE );
	}

	function remove_from_group ()
	{
		$nodeId   = Request::post( 'node_id', '', 'num' );
		$nodeType = Request::post( 'node_type', 'account', 'string', [ 'account', 'node' ] );
		$groupId  = Request::post( 'group_id', '', 'num' );

		if ( empty( $nodeId ) || empty( $nodeType ) || empty( $groupId ) )
		{
			Helper::response( FALSE );
		}

		DB::DB()->delete( DB::table( 'account_groups_data' ), [
			'node_id'   => $nodeId,
			'node_type' => $nodeType,
			'group_id'  => $groupId
		] );

		Helper::response( TRUE );
	}

	function delete_account_group ()
	{
		$group_id = Request::post( 'group_id', '', 'num' );

		$deleted = DB::DB()->delete( DB::table( 'account_groups' ), [
			'id'      => $group_id,
			'blog_id' => Helper::getBlogId(),
			'user_id' => get_current_user_id()
		] );

		if ( $deleted > 0 )
		{
			DB::DB()->delete( DB::table( 'account_groups_data' ), [
				'group_id' => $group_id
			] );

			Helper::response( TRUE );
		}

		Helper::response( FALSE );
	}

	function edit_account_group ()
	{
		$groupId = Request::post( 'group_id', '', 'num' );
		$name    = Request::post( 'name', '', 'string' );
		$group   = DB::fetch( 'account_groups', [ 'name' => $name ] );

		if ( ! empty( $group ) )
		{
			Helper::response( FALSE, fsp__( 'A group with this name already exists.' ) );
		}

		$updated = DB::DB()->update( DB::table( 'account_groups' ),
			[
				'name' => $name
			],
			[
				'id'      => $groupId,
				'blog_id' => Helper::getBlogId(),
				'user_id' => get_current_user_id()
			] );

		if ( $updated )
		{
			Helper::response( TRUE );
		}
		else
		{
			Helper::response( FALSE );
		}
	}

	function get_webhook_add_body ()
	{
		$title    = Request::post( 'title', '', 'str' );
		$icon     = Request::post( 'icon', '', 'str' );
		$template = Request::post( 'template', '', 'arr' );

		if ( gettype( $template ) !== 'array' )
		{
			Helper::response( FALSE );
		}

		if ( empty( $template[ 'title' ] ) && ! empty( $title ) )
		{
			$template[ 'title' ] = $title;
		}

		if ( empty( $template[ 'icon' ] ) && ! empty( $icon ) )
		{
			$template[ 'icon' ] = $icon;
		}

		Pages::modal( 'Accounts', 'webhook/template', $template );
	}

	public function export_webhook ()
	{

		$id = Request::post( 'id', 0, 'int' );

		$webhook = DB::fetch( 'accounts', [
			'id'      => $id,
			'driver'  => 'webhook',
			'user_id' => get_current_user_id()
		] );

		$options = json_decode( $webhook[ 'options' ], TRUE );

		if ( empty( $options ) )
		{
			Helper::response( FALSE );
		}

		$file_id = wp_generate_password( 8, FALSE );

		$fsPurchaseKey = Helper::getOption( 'poster_plugin_purchase_key', '', TRUE );
		$url           = FS_API_URL . '?get_webhook_templates.php?purchase_code=' . $fsPurchaseKey . '&domain=' . network_site_url();

		Helper::setOption( 'exported_json_' . $file_id, json_encode( $options, JSON_PRETTY_PRINT ) );
		Helper::response( TRUE, [
			'redirect_url' => $url,
			'file_id'      => $file_id,
			'msg'          => fsp__( 'Export is successful. The download process is starting...' ),
		] );
	}
}
