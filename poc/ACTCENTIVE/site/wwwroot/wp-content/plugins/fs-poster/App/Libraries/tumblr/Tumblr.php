<?php

namespace FSPoster\App\Libraries\tumblr;

use Exception;
use Tumblr\API\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Session;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\SocialNetwork;

class Tumblr extends SocialNetwork
{
	private static $apps = [];

	/**
	 * @return string
	 */
	public static function callbackURL ()
	{
		return site_url() . '/?tumblr_callback=1';
	}

	/**
	 * @param integer $appId
	 * @param string $consumerKey
	 * @param string $consumerSecret
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param string $proxy
	 */
	public static function authorize ( $appId, $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret, $proxy )
	{
		$client = new Client( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret, $proxy );

		try
		{
			$me = $client->getUserInfo();
		}
		catch ( Exception $e )
		{
            $error_msg = esc_html( $e->getMessage() );

            if ( $e->getCode() == 429 )
            {
                $error_msg = fsp__('The Standard APP has reached the hourly limit for adding accounts. The limit is assigned by Tumblr and you either need to <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-tumblr-automatically" target="_blank">create a Tumblr App</a> for your own use or use the <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-tumblr-automatically" target="_blank">email & pass method</a> to add your account to the plugin.', [], FALSE);
            }

            return [
                'status'    => FALSE,
                'error_msg' => $error_msg,
                'esc_html'  => FALSE
            ];
		}

		$username = $me->user->name;

		if ( ! get_current_user_id() > 0 )
		{
            return [
                'status'    => FALSE,
                'error_msg' => fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' )
            ];
		}

		$checkLoginRegistered = DB::fetch( 'accounts', [
			'blog_id'  => Helper::getBlogId(),
			'user_id'  => get_current_user_id(),
			'driver'   => 'tumblr',
			'username' => $username
		] );

		$dataSQL = [
			'blog_id'  => Helper::getBlogId(),
			'user_id'  => get_current_user_id(),
			'name'     => $username,
			'driver'   => 'tumblr',
			'username' => $username,
			'proxy'    => $proxy,
            'status'    => NULL,
            'error_msg' => NULL
		];

		if ( $checkLoginRegistered && empty($checkLoginRegistered['password']) )
		{
			$accId = $checkLoginRegistered[ 'id' ];

			DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $accId ] );

			DB::DB()->delete( DB::table( 'account_access_tokens' ), [ 'account_id' => $accId, 'app_id' => $appId ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );

			$accId = DB::DB()->insert_id;
		}

		// acccess token
		DB::DB()->insert( DB::table( 'account_access_tokens' ), [
			'account_id'          => $accId,
			'app_id'              => $appId,
			'access_token'        => $accessToken,
			'access_token_secret' => $accessTokenSecret
		] );

		self::refetch_account( $accId, $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret, $proxy );

		return [
            'status' => TRUE,
            'id'     => $accId
        ];
	}

	/**
	 * @param integer $appId
	 *
	 * @return mixed
	 */
	private static function getAppInf ( $appId )
	{
		if ( ! isset( self::$apps[ $appId ] ) )
		{
			self::$apps[ $appId ] = DB::fetch( 'apps', $appId );
		}

		return self::$apps[ $appId ];
	}

	/**
	 * @param array $blogInfo
	 * @param string $type
	 * @param string $title
	 * @param string $message
	 * @param string $link
	 * @param array $images
	 * @param string $video
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param integer $appId
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function sendPost ( $blogInfo, $type, $title, $message, $link, $images, $video, $accessToken, $accessTokenSecret, $appId, $proxy, $tags = [], $thumbnail = '', $excerpt = '' ) {
		$appInf = self::getAppInf( $appId );

		$sendData = [];

		$client = new Client( $appInf['app_key'], $appInf['app_secret'], $accessToken, $accessTokenSecret, $proxy );

        $message  = trim(htmlspecialchars_decode( strip_tags( $message ), ENT_QUOTES ));
        $messages = self::messageBlocks( $message );
        $title    = trim(htmlspecialchars_decode( strip_tags( $title ), ENT_QUOTES ));
        $excerpt  = trim(htmlspecialchars_decode( strip_tags( $excerpt ), ENT_QUOTES ));

        $sendData['layout'] = [
            [
                'type'    => 'rows',
                'display' => []
            ]
        ];

		if ( $type === 'image' )
		{
            if( ! empty( $link ) )
            {
                $sendData[ 'source_url' ] = $link;
            }

			$i = 0;

			if ( ! empty( $title ) )
			{
				$sendData[ 'content' ][] = [
					'type'    => 'text',
					'subtype' => 'heading1',
					'text'    => $title
				];
			}

            //put thumbnail
            $sendData[ 'content' ][] = [
                'type'  => 'image',
                'media' => [
                    'url'  => reset( $images )
                ]
            ];

            foreach ( $messages as $blockText )
            {
                $sendData[ 'content' ][] = [
                    'type' => 'text',
                    'text' => $blockText
                ];
            }

			for ( $j = 1; $j < count( $images ); $j++ )
			{
				$sendData[ 'content' ][] = [
					'type'  => 'image',
					'media' => [
						'url'  => $images[ $j ]
					]
				];
			}
		}
	  /*else if ( $type === 'video' )
		{
 			$sendData[ 'content' ][] = [
				'type' => 'video',
				'url'=> $video
			];

			$sendData['type']    = 'video';
			$sendData['data']    = $video;
			$sendData['caption'] = $message;
		}*/
		else if ( $type === 'text' )
		{
            if( ! empty( $link ) )
            {
                $sendData[ 'source_url' ] = $link;
            }

            if ( ! empty( $title ) )
			{
				$sendData[ 'content' ][] = [
					'type'    => 'text',
					'subtype' => 'heading1',
					'text'    => $title
				];
			}

            foreach ( $messages as $blockText )
            {
                $sendData[ 'content' ][] = [
                    'type' => 'text',
                    'text' => $blockText
                ];
            }
		}
		else if ( $type == 'quote' )
		{
            if( ! empty( $link ) )
            {
                $sendData[ 'source_url' ] = $link;
            }

            foreach ( $messages as $blockText )
            {
                $sendData[ 'content' ][] = [
                    'type' => 'text',
                    'subtype' => 'quote',
                    'text' => $blockText
                ];
            }
		}
		else
		{
            $sendData['content'][] = [
                'type'  => 'link',
                'url'   => $link,
                'title' => empty( $title ) ? '‎' : html_entity_decode( $title ), /*empty-dirsə boş character göndərsin deyə*/
            ];

            if ( ! empty( $thumbnail ) )
            {
                $sendData[ 'content' ][ 0 ][ 'poster' ] =
                    [
                        [
                            'url' => $thumbnail
                        ]
                    ];
            }

            if ( ! empty( $excerpt ) )
            {
                $sendData[ 'content' ][ 0 ][ 'description' ] = html_entity_decode( $excerpt );
            }

            foreach ( $messages as $blockText )
            {
                $sendData[ 'content' ][] = [
                    'type' => 'text',
                    'text' => $blockText
                ];
            }
		}

        for( $i = 0; $i < count( $sendData[ 'content' ] ); $i++ )
        {
            $sendData[ 'layout' ][0][ 'display' ][] = [
                'blocks' => [ $i ]
            ];
        }

		if ( ! empty( $tags ) )
		{
			$sendData[ 'tags' ] = $tags;
		}

		try
		{
			$result = $client->createPost( $blogInfo[ 'node_id' ], $sendData );
		}
		catch ( Exception $e )
		{
            $error_msg = $e->getMessage();

            if ( $e->getCode() == 429 )
            {
                $error_msg = fsp__('The Standard APP has reached the hourly limit for sharing posts. The limit is assigned by Tumblr and you either need to <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-tumblr-automatically" target="_blank">create a Tumblr App</a> for your own use or use the <a href="https://www.fs-poster.com/documentation/fs-poster-schedule-share-wordpress-posts-to-tumblr-automatically" target="_blank">email & pass method</a> to add your account to the plugin.', [], FALSE);
            }

			return [
				'status'    => 'error',
				'error_msg' => htmlspecialchars($error_msg)
			];
		}

		return [
			'status' => 'ok',
			'id'     => $type === 'video' ? '' : strval( $result->id )
		];
	}

    private static function messageBlocks( $message )
    {
        $messages      = [];
        $messageLength = mb_strlen( $message );
        $lastCut       = 0;
        $blockText     = $message;

        do{
            $cutLength = 4096;

            if ( ! $messageLength < 4096 )
            {
                $searchText  = html_entity_decode( mb_substr( $message, 0, $lastCut + 4095 ) );
                $needles     = [ "\n", "<br>", "<br/>", ".", " ", "&nbsp;", "&#160;" ];

                foreach ( $needles as $needle )
                {
                    if( empty( $searchText ) || ( $lastCut + 4000 > $messageLength ) )
                    {
                        break;
                    }
                    else
                    {
                        $pos = mb_strpos( $searchText, $needle, $lastCut + 4000 );
                    }

                    if ( $pos !== FALSE )
                    {
                        $cutLength = $pos - $lastCut;

                        if( $needle == '.' )
                        {
                            $cutLength += 1;
                        }

                        break;
                    }
                }

                $blockText = html_entity_decode( mb_substr( $message, $lastCut, $cutLength ) );
            }

            $lastCut   = $lastCut + $cutLength;

            if ( ! empty( $blockText ) )
            {
                $messages[] = trim($blockText);
            }
        } while( $messageLength > 4096 && $lastCut < $messageLength );

        return $messages;
    }

	/**
	 * @param integer $appId
	 *
	 * @return string
	 */
	public static function getLoginURL ( $appId )
	{
		$proxy = Request::get( 'proxy', '', 'string' );

		Session::set( 'app_id', $appId );
		Session::set( 'proxy', $proxy );

		$appInf = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'tumblr' ] );
		if ( ! $appInf )
		{
			self::error( fsp__( 'Error! The App isn\'t found!' ) );
		}

		$consumerKey    = urlencode( $appInf[ 'app_key' ] );
		$consumerSecret = urlencode( $appInf[ 'app_secret' ] );
		$callbackUrl    = self::callbackUrl();

		$client = new Client( $consumerKey, $consumerSecret, NULL, NULL, $proxy );

		$requestHandler = $client->getRequestHandler();
		$requestHandler->setBaseUrl( 'https://www.tumblr.com/' );

		try
		{
			$resp = $requestHandler->request( 'POST', 'oauth/request_token', [
				'oauth_callback' => $callbackUrl
			] );
		}
		catch ( Exception $e )
		{
			self::error( $e->getMessage() );
		}

		$result = (string) $resp->body;
		parse_str( $result, $keys );

		Session::set( 'tmp_oauth_token', $keys[ 'oauth_token' ] );
		Session::set( 'tmp_oauth_token_secret', $keys[ 'oauth_token_secret' ] );

		return 'https://www.tumblr.com/oauth/authorize?oauth_token=' . $keys[ 'oauth_token' ];
	}

	/**
	 * @return array
	 */
	public static function getAccessToken ()
	{
		$appId                  = (int) Session::get( 'app_id' );
		$tmp_oauth_token        = Session::get( 'tmp_oauth_token' );
		$tmp_oauth_token_secret = Session::get( 'tmp_oauth_token_secret' );

		if ( empty( $appId ) || empty( $tmp_oauth_token ) || empty( $tmp_oauth_token_secret ) )
		{
			return [
                'status' => FALSE,
                'error'  => ''
            ];
		}

		$code = Request::get( 'oauth_verifier', '', 'string' );

		if ( empty( $code ) )
		{
			$error_message = Request::get( 'error_message', '', 'str' );

            return [
                'status' => FALSE,
                'error'  => $error_message
            ];
		}

		$appInf         = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'tumblr' ] );
		$consumerKey    = urlencode( $appInf[ 'app_key' ] );
		$consumerSecret = urlencode( $appInf[ 'app_secret' ] );

		$proxy = Session::get( 'proxy' );

		Session::remove( 'app_id' );
		Session::remove( 'tmp_oauth_token' );
		Session::remove( 'tmp_oauth_token_secret' );
		Session::remove( 'proxy' );

		$client = new Client( $consumerKey, $consumerSecret, $tmp_oauth_token, $tmp_oauth_token_secret, $proxy );

		$requestHandler = $client->getRequestHandler();
		$requestHandler->setBaseUrl( 'https://www.tumblr.com/' );

		try
		{
			$resp = $requestHandler->request( 'POST', 'oauth/access_token', [ 'oauth_verifier' => $code ] );
		}
		catch ( Exception $e )
		{
            return [
                'status' => FALSE,
                'error'  => $e->getMessage()
            ];
		}

		$out  = (string) $resp->body;
		$data = [];
		parse_str( $out, $data );

		$access_token        = $data[ 'oauth_token' ];
		$access_token_secret = $data[ 'oauth_token_secret' ];

		return self::authorize( $appId, $consumerKey, $consumerSecret, $access_token, $access_token_secret, $proxy );
	}

	/**
	 * @param integer $post_id
	 * @param string $accessToken
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function getStats ( $post_id, $accessToken, $proxy )
	{
		return [
			'comments' => 0,
			'like'     => 0,
			'shares'   => 0,
			'details'  => 0
		];
	}

	/**
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param integer $appId
	 * @param string $proxy
	 *
	 * @return array
	 */
	public static function checkAccount ( $accessToken, $accessTokenSecret, $appId, $proxy )
	{
		$result = [
			'error'     => TRUE,
			'error_msg' => NULL
		];
		$appInf = self::getAppInf( $appId );

		$client = new Client( $appInf[ 'app_key' ], $appInf[ 'app_secret' ], $accessToken, $accessTokenSecret, $proxy );

		try
		{
			$client->getUserInfo();

			$result[ 'error' ] = FALSE;
		}
		catch ( Exception $e )
		{
			$result[ 'error_msg' ] = $e->getMessage();
		}

		return $result;
	}

	public static function refetch_account ( $account_id, $app_key, $app_secret, $access_token, $access_token_secret, $proxy )
	{
		$client = new Client( $app_key, $app_secret, $access_token, $access_token_secret, $proxy );

		try
		{
			$me = $client->getUserInfo();
		}
		catch ( Exception $e )
		{
			return [ 'status' => FALSE, 'error_msg' => $e->getMessage() ];
		}

		$get_nodes = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $account_id ] ), ARRAY_A );
		$my_nodes  = [];

		foreach ( $get_nodes as $node )
		{
			$my_nodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
		}

		foreach ( $me->user->blogs as $blogInf )
		{
			if ( ! in_array( $blogInf->name, $my_nodes ) )
			{
				DB::DB()->insert( DB::table( 'account_nodes' ), [
					'blog_id'      => Helper::getBlogId(),
					'user_id'      => get_current_user_id(),
					'driver'       => 'tumblr',
					'screen_name'  => $blogInf->name,
					'account_id'   => $account_id,
					'node_type'    => 'blog',
					'node_id'      => $blogInf->name,
					'name'         => $blogInf->name,
					'access_token' => NULL,
					'category'     => $blogInf->primary ? 'primary' : 'not-primary'
				] );
			}
			else
			{
				DB::DB()->update( DB::table( 'account_nodes' ), [
					'screen_name' => $blogInf->name,
					'node_id'     => $blogInf->name,
					'name'        => $blogInf->name,
					'category'    => $blogInf->primary ? 'primary' : 'not-primary'
				], [
					'screen_name' => $blogInf->name,
					'account_id'  => $account_id,
					'node_id'     => $blogInf->name
				] );
			}

			unset( $my_nodes[ array_search( $blogInf->name, $my_nodes ) ] );
		}

		if ( ! empty( $my_nodes ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
		}

		return [ 'status' => TRUE ];
	}
}