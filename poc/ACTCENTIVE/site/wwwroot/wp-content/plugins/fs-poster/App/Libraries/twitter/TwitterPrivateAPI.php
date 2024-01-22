<?php

namespace FSPoster\App\Libraries\twitter;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;

class TwitterPrivateAPI
{
	private $proxy;
	private $twid;
	private $username;
	private $name;
    private $profile_picture;
	private $client;
	private $csrfToken;
	private $bearerToken = 'AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA';
	private $auth_token;

	public function __construct ( $auth_token, $proxy )
	{
		$this->proxy      = $proxy;
		$this->auth_token = $auth_token;
		$this->setClient();
	}

	private function setClient ()
	{
		$options = [
			'verify'      => FALSE,
			'http_errors' => FALSE,
			'proxy'       => empty( $this->proxy ) ? NULL : $this->proxy,
			'headers'     => [
				'authorization' => 'Bearer ' . $this->bearerToken,
				'user-agent'    => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:75.0) Gecko/20100101 Firefox/75.0',
				'content-type'  => 'application/json',
				'origin'        => 'https://twitter.com',
			]
		];

		if ( ! empty( $this->proxy ) )
		{
			$options[ 'proxy' ] = $this->proxy;
		}

		$this->client = new Client( $options );
	}

	/**
	 * @return array
	 */
	public function checkAccount ()
	{
		$user = $this->getUserInfo();

		if ( empty( $user[ 'profile_id' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'The account is disconnected from the plugin. Please add your account to the plugin again by getting the cookie on the browser <a href=\'https://www.fs-poster.com/documentation/fs-poster-schedule-auto-publish-wordpress-posts-to-twitter\' target=\'_blank\'>Incognito mode</a>. And close the browser without logging out from the account.', [], FALSE )
			];
		}

		return [ 'status' => TRUE ];
	}

	public function sendPost ( $sendType, $text, $comment, $username, $link, $images = NULL, $video = NULL )
	{
		$this->username = $username;
		$sendMedia = [];

		if ( $sendType === 'link' )
		{
			$text .= "\n" . $link;
		}
		else if ( $sendType === 'image' && ! empty( $images ) && is_array( $images ) )
		{
            $c = 1;
			foreach ( $images as $image )
			{
                if( $c > 4 )
                {
                    break;
                }

				$mediaId = $this->uploadMedia( $image, 'tweet_image' );

				if ( ! empty( $mediaId ) )
				{
					$sendMedia[] = [
						'media_id'     => $mediaId,
						'tagged_users' => []
					];
				}

                $c++;
			}
		}
		else if ( $sendType === 'video' && ! empty( $video ) && is_string( $video ) )
		{
			$mediaId = $this->uploadMedia( $video, 'tweet_video' );

			if ( ! empty( $mediaId ) )
			{
				$sendMedia[] = [
					'media_id'     => $mediaId,
					'tagged_users' => []
				];
			}
		}

		$sendJSON = json_encode( [
			'variables' => json_encode( [
				'tweet_text'                  => $text,
				'media'                       => [
					'media_entities'     => $sendMedia,
					'possibly_sensitive' => FALSE
				],
				'withReactionsMetadata'       => FALSE,
				'withReactionsPerspective'    => FALSE,
				'withSuperFollowsTweetFields' => TRUE,
				'withSuperFollowsUserFields'  => TRUE,
				'withNftAvatar'               => FALSE,
				'semantic_annotation_ids'     => [],
				'dark_request'                => FALSE,
				'withUserResults'             => TRUE,
				'withBirdwatchPivots'         => FALSE
			] ),
			'queryId'   => 'YNCZu_D_6qMc1yulCeQiCw',
		] );

		try
		{
			$csrfToken = $this->getCSRFToken();

			$post = $this->client->post( 'https://twitter.com/i/api/graphql/YNCZu_D_6qMc1yulCeQiCw/CreateTweet', [
				'headers' => [
					'x-csrf-token' => $csrfToken,
					'cookie'       => 'ct0=' . $csrfToken . '; auth_token=' . $this->auth_token . ';'
					//'cookie'        => 'ct0=' . $csrfToken . '; auth_token=4caf2cd88b435a3e8ce8b2fea9cf47e3b3a07793;'
				],
				'body'    => $sendJSON
			] )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			$post = $e->getResponse()->getBody()->getContents();
		}

		$resArr = json_decode( $post, TRUE );

		if ( ! is_array( $resArr ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( $post )
			];
		}

		if ( ! empty( $resArr[ 'data' ][ 'create_tweet' ][ 'tweet_results' ][ 'result' ][ 'rest_id' ] ) )
		{
			$responseArr = [
				'status' => 'ok',
				'id'     => $resArr[ 'data' ][ 'create_tweet' ][ 'tweet_results' ][ 'result' ][ 'rest_id' ]
			];

			if ( ! empty( $comment ) )
			{
				$responseArr[ 'comment' ] = $this->writeComment( $comment, $responseArr[ 'id' ] );
			}

			return $responseArr;
		}
		else if ( isset( $resArr[ 'errors' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => empty( $resArr[ 'errors' ][ 0 ][ 'message' ] ) ? fsp__( 'Unknown error!' ) : fsp__( $resArr[ 'errors' ][ 0 ][ 'message' ] )
			];
		}

		return [
			'status'    => 'error',
			'error_msg' => fsp__( 'Unknown error' )
		];
	}

	private function getCSRFToken ()
	{
		if ( empty( $this->csrfToken ) )
		{
			$randomCSRF = md5( mt_rand( 1000, 9999 ) . microtime() );

			try
			{
				$getCSRF = $this->client->get( 'https://twitter.com/i/api/graphql/hXkPYUuiQAltqmDjG3G9Dw/Viewer', [
					'headers' => [
						'cookie'       => 'ct0=' . $randomCSRF . '; auth_token=' . $this->auth_token . ';',
						//'cookie'        => 'ct0=' . $randomCSRF . '; auth_token=4caf2cd88b435a3e8ce8b2fea9cf47e3b3a07793;',
						'x-csrf-token' => $randomCSRF
					],
					'query'   => [
						'variables' => json_encode( [
							'withUserResults'            => TRUE,
							'withSuperFollowsUserFields' => TRUE,
							'withNftAvatar'              => FALSE
						] )
					]
				] );

				$body = $getCSRF->getBody()->getContents();

				$body = json_decode( $body, TRUE );

				$this->twid            = isset( $body[ 'data' ][ 'viewer' ][ 'user_results' ][ 'result' ][ 'rest_id' ] ) ? $body[ 'data' ][ 'viewer' ][ 'user_results' ][ 'result' ][ 'rest_id' ] : '';
				$this->username        = isset( $body[ 'data' ][ 'viewer' ][ 'user_results' ][ 'result' ][ 'legacy' ][ 'screen_name' ] ) ? $body[ 'data' ][ 'viewer' ][ 'user_results' ][ 'result' ][ 'legacy' ][ 'screen_name' ] : '';
				$this->name            = isset( $body[ 'data' ][ 'viewer' ][ 'user_results' ][ 'result' ][ 'legacy' ][ 'name' ] ) ? $body[ 'data' ][ 'viewer' ][ 'user_results' ][ 'result' ][ 'legacy' ][ 'name' ] : '';
                $this->profile_picture = isset( $body[ 'data' ][ 'viewer' ][ 'user_results' ][ 'result' ][ 'legacy' ][ 'profile_image_url_https' ] ) ? $body[ 'data' ][ 'viewer' ][ 'user_results' ][ 'result' ][ 'legacy' ][ 'profile_image_url_https' ] : '';

				foreach ( $getCSRF->getHeader( 'set-cookie' ) as $setCookie )
				{
					preg_match( '/ct0=(.*?);/', $setCookie, $cookie );

					if ( ! empty( $cookie[ 1 ] ) )
					{
						$this->csrfToken = $cookie[ 1 ];
					}
				}
			}
			catch ( Exception $e )
			{
			}
		}

		return $this->csrfToken;
	}

	public function writeComment ( $comment, $mediaId )
	{
		$sendData = [
			"features" => [
				"dont_mention_me_view_api_enabled" => true,
				"interactive_text_enabled" => true,
				"responsive_web_edit_tweet_api_enabled" => false,
				"responsive_web_enhance_cards_enabled" => false,
				"responsive_web_uc_gql_enabled" => false,
				"standardized_nudges_misinfo" => false,
				"vibe_tweet_context_enabled" => false
			],
			"queryId" => "Olwyi5wlRl8zaMAJh-GQ6Q",
			"variables" => [
				"dark_request" => false,
				"media" => [
					"media_entities" => [
					],
					"possibly_sensitive" => false
				],
				"reply" => [
					"exclude_reply_user_ids" => [
					],
					"in_reply_to_tweet_id" => $mediaId
				],
				"semantic_annotation_ids" => [
				],
				"tweet_text" => $comment,
				"withDownvotePerspective" => false,
				"withReactionsMetadata" => false,
				"withReactionsPerspective" => false,
				"withSuperFollowsTweetFields" => true,
				"withSuperFollowsUserFields" => true
			]
		];

		$csrfToken = $this->getCSRFToken();

		$response = $this->client->post( 'https://twitter.com/i/api/graphql/Olwyi5wlRl8zaMAJh-GQ6Q/CreateTweet',  [
			'headers' => [
				'x-csrf-token' => $csrfToken,
				'cookie'       => 'ct0=' . $csrfToken . '; auth_token=' . $this->auth_token . ';'
			],
			'body' => json_encode( $sendData )
		] )->getBody();

		$resArr = json_decode( $response, true );

		if ( ! is_array( $resArr ) )
		{
			return [
				'error' => $response
			];
		}

		if ( isset( $resArr[ 'errors' ] ) )
		{
			return [
				'error' => empty( $resArr[ 'errors' ][ 0 ][ 'message' ] ) ? fsp__( 'Unknown error!' ) : fsp__( $resArr[ 'errors' ][ 0 ][ 'message' ] )
			];
		}

		if ( empty( $resArr[ 'data' ][ 'create_tweet' ][ 'tweet_results' ][ 'result' ][ 'rest_id' ] ) )
		{

			return [
				'error' => fsp__( 'Unknown error' )
			];
		}
		else
		{
			$id = $resArr[ 'data' ][ 'create_tweet' ][ 'tweet_results' ][ 'result' ][ 'rest_id' ];

			return [
				'url' => sprintf( 'https://twitter.com/%s/status/%s', $this->username, $id )
			];
		}
	}

	private function uploadMedia ( $file, $type )
	{
		$csrfToken = $this->getCSRFToken();
		$header    = [
			'x-csrf-token' => $csrfToken,
			'cookie'       => 'ct0=' . $csrfToken . '; auth_token=' . $this->auth_token . ';',
			//'cookie'        => 'ct0=' . $csrfToken . '; auth_token=4caf2cd88b435a3e8ce8b2fea9cf47e3b3a07793;',
		];

		try
		{
			$uploadINIT = ( string ) $this->client->post( 'https://upload.twitter.com/i/media/upload.json', [
				'headers' => $header,
				'query'   => [
					'command'        => 'INIT',
					'total_bytes'    => filesize( $file ),
					'media_type'     => Helper::mimeContentType( $file ),
					'media_category' => $type
				]
			] )->getBody();
			$uploadINIT = json_decode( $uploadINIT );

			if ( empty( $uploadINIT->media_id ) )
			{
				throw new Exception();
			}

			$mediaID = $uploadINIT->media_id_string;
			//$mediaID = ( int ) $uploadINIT->media_id;
		}
		catch ( Exception $e )
		{
			return FALSE;
		}

		try
		{
			$segmentIndex = 0;
			$handle       = fopen( $file, 'rb' );

			if ( empty( $handle ) )
			{
				throw new Exception();
			}

			while ( ! feof( $handle ) )
			{
				$this->client->post( 'https://upload.twitter.com/i/media/upload.json', [
					'headers'   => $header,
					'query'     => [
						'command'       => 'APPEND',
						'segment_index' => $segmentIndex,
						'media_id'      => $mediaID,
					],
					'multipart' => [
						[
							'name'     => 'media',
							'contents' => fread( $handle, 250000 ),
							'filename' => 'blob',
							'headers'  => [
								'Content-Type' => 'application/octet-stream',
							]
						]
					],
				] );

				$segmentIndex++;
			}

			fclose( $handle );
		}
		catch ( Exception $e )
		{
			return FALSE;
		}

		try
		{
			$uploadFINALIZE = ( string ) $this->client->post( 'https://upload.twitter.com/i/media/upload.json', [
				'headers' => $header,
				'query'   => [
					'command'  => 'FINALIZE',
					'media_id' => $mediaID,
				],
			] )->getBody();

			$uploadFINALIZE = json_decode( $uploadFINALIZE );

			if ( empty( $uploadFINALIZE->media_id ) )
			{
				throw new Exception();
			}

			if ( $type === 'tweet_video' )
			{
				if ( ! empty( $uploadFINALIZE->processing_info->state ) )
				{
					$uploaded = FALSE;

					while ( ! $uploaded )
					{
						$uploadSTATUS = ( string ) $this->client->get( 'https://upload.twitter.com/i/media/upload.json', [
							'headers' => $header,
							'query'   => [
								'command'  => 'STATUS',
								'media_id' => $mediaID,
							],
						] )->getBody();
						$uploadSTATUS = json_decode( $uploadSTATUS );

						if ( ! empty( $uploadSTATUS->processing_info->state ) && $uploadSTATUS->processing_info->state === 'succeeded' )
						{
							$uploaded = TRUE;
						}

						sleep( 0.2 );
					}
				}
				else
				{
					throw new Exception();
				}
			}
		}
		catch ( Exception $e )
		{
			return FALSE;
		}

		return $mediaID;
	}

	public function getUserInfo ()
	{
		$this->getCSRFToken();

		return [
			'name'        => $this->name,
			'username'    => $this->username,
			'profile_id'  => $this->twid,
			'proxy'       => $this->proxy,
            'profile_pic' => $this->profile_picture
		];
	}

	public function authorize ()
	{
		if ( ! get_current_user_id() > 0 )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'The current WordPress user ID is not available. Please, check if your security plugins prevent user authorization.' )
			];
		}

		$user = $this->getUserInfo();

		if ( empty( $user[ 'profile_id' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Couldn\'t get Twitter user!' )
			];
		}

		$checkUserExist = DB::fetch( 'accounts', [
			'blog_id'    => Helper::getBlogId(),
			'user_id'    => get_current_user_id(),
			'driver'     => 'twitter',
			'profile_id' => $user[ 'profile_id' ]
		] );


		$dataSQL = [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
			'driver'      => 'twitter',
			'name'        => $user[ 'name' ],
			'profile_id'  => $user[ 'profile_id' ],
			'email'       => '',
			'username'    => $user[ 'username' ],
			'proxy'       => $user[ 'proxy' ],
			'options'     => $this->auth_token,
			'status'      => NULL,
			'error_msg'   => NULL,
            'profile_pic' => $user[ 'profile_pic' ]
		];

		if ( $checkUserExist && ! empty( $checkUserExist[ 'options' ] ) )
		{
			$accId = $checkUserExist[ 'id' ];
			DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $accId ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );
		}

		return [
			'status' => 'ok'
		];
	}

	public function getStats ( $post_id )
	{
		return [
			'comments' => 0,
			'like'     => 0,
			'shares'   => 0,
			'details'  => ''
		];
	}
}
