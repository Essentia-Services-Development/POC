<?php

namespace FSPoster\App\Libraries\twitter;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\Session;
use FSPoster\App\Providers\SocialNetwork;

class Twitter extends SocialNetwork
{
	/**
	 * @param int $appId
	 * @param string $type
	 * @param string $message
	 * @param string $link
	 * @param array $images
	 * @param string $video
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function sendPost ( $appId, $type, $message, $comment, $username, $link, $images, $video, $accessToken, $accessTokenSecret, $proxy )
	{
		$appInfo = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'twitter' ] );

		if ( ! $appInfo )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! There isn\'t a Twitter App!' )
			];
		}

        if ( ! empty( $message ) )
        {
            $parameters[ 'text' ] = $message;
        }

		$connection = new TwitterOAuth( $appInfo[ 'app_key' ], $appInfo[ 'app_secret' ], $accessToken, $accessTokenSecret, $proxy );

		if ( $type === 'link' )
		{
			$parameters[ 'text' ] .= "\n" . $link;
		}

		if ( $type === 'image' && ! empty( $images ) && is_array( $images ) )
		{
			$uploadedImages = [];
			$c              = 1;
			foreach ( $images as $imageURL )
			{
				if ( $c > 4 )
				{
					break;
				}

				if ( empty( $imageURL ) || ! is_string( $imageURL ) )
				{
					continue;
				}

				$uploadImage = $connection->upload( $imageURL );

				if ( ! empty( $uploadImage[ 'media_id_string' ] ) )
				{
					$uploadedImages[] = $uploadImage[ 'media_id_string' ];

					$c++;
				}
			}

			if ( ! empty( $uploadedImages ) )
			{
                $parameters[ 'media' ][ 'media_ids' ] = $uploadedImages;
			}
		}

		if ( $type === 'video' && ! empty( $video ) && is_string( $video ) )
		{
			$uploadVideo = $connection->upload( $video );

			if ( ! empty( $uploadVideo[ 'media_id_string' ] ) )
			{
                $parameters[ 'media' ][ 'media_ids' ][] = $uploadVideo[ 'media_id_string' ];
			}
		}

		$result = $connection->post( 'https://api.twitter.com/2/tweets', [] ,$parameters );

		if ( ! empty( $result[ 'errors' ] ) && is_array( $result[ 'errors' ] ) )
		{
			if ( isset( $result[ 'errors' ][ 0 ][ 'code' ] ) && $result[ 'errors' ][ 0 ][ 'code' ] == 185 )
			{
                if( empty( $appInfo[ 'slug' ] ) )
                {
                    $errorMsg = fsp__( 'The Standard APP has reached the hourly limit for sharing posts. The limit is assigned by Twitter and you either need to <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-twitter-automatically" target="_blank">create a Twitter App</a> for your own use or use the <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-twitter-automatically" target="_blank">Cookie method</a> to add your account to the plugin.', [], FALSE );
                }
                else
                {
                    $errorMsg = fsp__( 'The FS Poster Standard App should be used only for testing purposes, please create your personal App to share posts on Twitter. <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-twitter-automatically">How?</a>' );
                }
			}
			else if ( isset( $result[ 'errors' ][ 0 ][ 'message' ] ) )
			{
				$errorMsg = $result[ 'errors' ][ 0 ][ 'message' ];
			}
			else
			{
				$errorMsg = fsp__( 'Unknown error!' );
			}

			return [
				'status'    => 'error',
				'error_msg' => htmlspecialchars( $errorMsg )
			];
		}
		else if ( ! empty( $result[ 'data' ][ 'id' ] ) )
		{
			$mediaId = $result[ 'data' ][ 'id' ];

			if ( ! empty( $comment ) )//post a comment
			{
                $resp = $connection->post( 'https://api.twitter.com/2/tweets', [], [
					'text'                => sprintf( "@%s \n%s", $username, $comment ),
					'reply' => [
                        'in_reply_to_tweet_id' => $mediaId
                    ]
				] );
			}

			return [
				'status' => 'ok',
				'id'     => $mediaId
			];
		}
		else if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 429 && !empty( $appInfo[ 'slug' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'The FS Poster Standard App should be used only for testing purposes, please create your personal App to share posts on Twitter. <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-twitter-automatically">How?</a>' )
			];
		}
        else if ( isset( $result[ 'detail' ] ) )
        {
            return [
                'status'    => 'error',
                'error_msg' => $result[ 'detail' ]
            ];
        }
        else
        {
            return [
                'status'    => 'error',
                'error_msg' => fsp__( 'Error!' )
            ];
        }
	}

    /**
	 * @param $appId
	 *
	 * @return string
	 */
	public static function getLoginURL ( $appId )
	{
		$proxy = Request::get( 'proxy', '', 'string' );

		Session::set( 'app_id', $appId );
		Session::set( 'proxy', $proxy );

		$appInf = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'twitter' ] );

		$connection = new TwitterOAuth( $appInf[ 'app_key' ], $appInf[ 'app_secret' ], NULL, NULL, $proxy );

		$tokens = $connection->oauth( 'request_token', [
			'oauth_callback' => self::callbackURL()
		] );

		if ( empty( $tokens[ 'oauth_token' ] ) || empty( $tokens[ 'oauth_token_secret' ] ) )
		{
			if ( isset( $tokens[ 'errors' ][ 0 ][ 'code' ] ) && $tokens[ 'errors' ][ 0 ][ 'code' ] == 453 )
			{
				self::error( fsp__( 'You need to apply for Elevated access via the Developer Portal to share posts. <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-twitter-automatically">How to?</a>', [], FALSE ), FALSE );
			}
			else if ( isset( $tokens[ 'errors' ][ 0 ][ 'message' ] ) )
			{
				self::error( fsp__( $tokens[ 'errors' ][ 0 ][ 'message' ] ) );
			}
			else
			{
				self::error( fsp__( 'Unknown error!' ) );
			}
		}

		Session::set( 'oauth_token', $tokens[ 'oauth_token' ] );
		Session::set( 'oauth_token_secret', $tokens[ 'oauth_token_secret' ] );

		return 'https://api.twitter.com/oauth/authorize?oauth_token=' . $tokens[ 'oauth_token' ];
	}

	/**
	 * @return string
	 */
	public static function callbackURL ()
	{
		return site_url() . '/?twitter_callback=1';
	}

	public static function getAccessToken ()
	{
		$appId          = (int) Session::get( 'app_id' );
		$oauth_verifier = Request::get( 'oauth_verifier', '', 'str' );
		$oauth_token    = Request::get( 'oauth_token', '', 'str' );

		if ( empty( $appId ) || empty( $oauth_verifier ) || empty( $oauth_token ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => ''
			];
		}

		$appInf = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'twitter' ] );

		$proxy              = Session::get( 'proxy' );
		$oauth_token        = Session::get( 'oauth_token' );
		$oauth_token_secret = Session::get( 'oauth_token_secret' );

		$connection = new TwitterOAuth( $appInf[ 'app_key' ], $appInf[ 'app_secret' ], $oauth_token, $oauth_token_secret, $proxy );

		$access_token = $connection->oauth( "access_token", [
			'oauth_verifier' => $oauth_verifier
		] );

		Session::remove( 'app_id' );
		Session::remove( 'proxy' );
		Session::remove( 'oauth_token' );
		Session::remove( 'oauth_token_secret' );

		if ( empty( $access_token[ 'oauth_token' ] ) || empty( $access_token[ 'oauth_token_secret' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => ''
			];
		}

		return self::authorize( $appInf, $access_token[ 'oauth_token' ], $access_token[ 'oauth_token_secret' ], $proxy );
	}

	/**
	 * @param array $appInf
	 * @param string $oauth_token
	 * @param string $oauth_token_secret
	 * @param string $proxy
	 */
	public static function authorize ( $appInf, $oauth_token, $oauth_token_secret, $proxy )
	{
		$connection = new TwitterOAuth( $appInf[ 'app_key' ], $appInf[ 'app_secret' ], $oauth_token, $oauth_token_secret, $proxy );
		$user       = $connection->get( "https://api.twitter.com/1.1/account/verify_credentials.json" );

		if ( isset( $user[ 'errors' ][ 0 ][ 'code' ] ) && $user[ 'errors' ][ 0 ][ 'code' ] == 453 )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'You need to apply for Elevated access via the Developer Portal to share posts. <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-twitter-automatically">How to?</a>', [], FALSE ),
				'esc_html'  => FALSE
			];
		}

		if ( isset( $user[ 'errors' ][ 0 ][ 'message' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( $user[ 'errors' ][ 0 ][ 'message' ] )
			];
		}

		if ( ( empty( $user ) || ! isset( $user[ 'id' ] ) ) && !empty( $appInf[ 'slug' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'The FS Poster Standard App should be used only for testing purposes, please create your personal App to share posts on Twitter. <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-twitter-automatically">How?</a>' ),
                'esc_html'  => FALSE
			];
		}
        else if( empty( $user ) || ! isset( $user[ 'id' ] ) )
        {
            return [
                'status'    => FALSE,
                'error_msg' => ''
            ];
        }

		if ( ! get_current_user_id() > 0 )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' )
			];
		}

		$checkUserExist = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'driver'     => 'twitter',
			'profile_id' => $user[ 'id_str' ]
		] );

		$dataSQL = [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
			'driver'      => 'twitter',
			'name'        => $user[ 'name' ],
			'profile_id'  => $user[ 'id_str' ],
			'email'       => '',
			'username'    => $user[ 'screen_name' ],
			'proxy'       => $proxy,
			'status'      => NULL,
			'error_msg'   => NULL,
			'profile_pic' => $user[ 'profile_image_url' ]
		];

		if ( $checkUserExist && empty ( $checkUserExist[ 'options' ] ) )
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
			'account_id'          => $accId,
			'app_id'              => $appInf[ 'id' ],
			'access_token'        => $oauth_token,
			'access_token_secret' => $oauth_token_secret
		] );

		return [
			'status' => TRUE,
			'id'     => $accId
		];
	}

	/**
	 * @param integer $post_id
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param integer $appId
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function getStats ( $post_id, $accessToken, $accessTokenSecret, $appId, $proxy )
	{
		$appInfo = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'twitter' ] );

		$connection = new TwitterOAuth( $appInfo[ 'app_key' ], $appInfo[ 'app_secret' ], $accessToken, $accessTokenSecret, $proxy );
		$stat       = $connection->get( 'https://api.twitter.com/1.1/statuses/show/' . $post_id . '.json' );

		return [
			'comments' => 0,
			'like'     => isset( $stat[ 'favorite_count' ] ) ? (int) $stat[ 'favorite_count' ] : 0,
			'shares'   => isset( $stat[ 'retweet_count' ] ) ? (int) $stat[ 'retweet_count' ] : 0,
			'details'  => ''
		];
	}

	/**
	 * @param integer $appId
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function checkAccount ( $appId, $accessToken, $accessTokenSecret, $proxy )
	{
		$result  = [
			'error'     => TRUE,
			'error_msg' => NULL
		];
		$appInfo = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'twitter' ] );

		if ( ! $appInfo )
		{
			$result[ 'error_msg' ] = fsp__( 'Error! There isn\'t a Twitter App!' );
		}
		else
		{
			$connection = new TwitterOAuth( $appInfo[ 'app_key' ], $appInfo[ 'app_secret' ], $accessToken, $accessTokenSecret, $proxy );
			$user       = $connection->get( "https://api.twitter.com/1.1/account/verify_credentials.json" );

			if ( ! empty( $user ) && isset( $user[ 'id' ] ) )
			{
				$result[ 'error' ] = FALSE;
			}
		}

		return $result;
	}
}