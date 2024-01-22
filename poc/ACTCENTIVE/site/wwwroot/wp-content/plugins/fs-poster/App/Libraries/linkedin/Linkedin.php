<?php

namespace FSPoster\App\Libraries\linkedin;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Curl;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\Session;
use FSPoster\App\Providers\URLScraper;
use FSPoster\App\Providers\SocialNetwork;
use FSPoster\App\Providers\AccountService;

class Linkedin extends SocialNetwork
{
	public static function sendPost ( $node_info, $type, $message, $title, $link, $images, $video, $accessToken, $proxy )
	{
		$message = str_replace( [
			'\\',
			'|',
			'{',
			'}',
			'@',
			'[',
			']',
			'(',
			')',
			'<',
			'>',
			'#',
			'*',
			'_',
			'~'
		], [ '\\\\', '\|', '\{', '\}', '\@', '\[', '\]', '\(', '\)', '\<', '\>', '\#', '\*', '\_', '\~' ], $message );

		//todo bu limit artib
		if ( Helper::getOption( 'linkedin_autocut_text', '1' ) == 1 && mb_strlen( $message ) > 1300 )
		{
			$message = mb_substr( $message, 0, 1297 ) . '...';
		}

		$sendData = [
			'commentary'                => $message,
			'visibility'                => 'PUBLIC',
			'distribution'              => [
				'feedDistribution'               => 'MAIN_FEED',
				'targetEntities'                 => [],
				'thirdPartyDistributionChannels' => []
			],
			'lifecycleState'            => 'PUBLISHED',
			'isReshareDisabledByAuthor' => FALSE
		];

		if ( isset( $node_info[ 'node_type' ] ) && $node_info[ 'node_type' ] === 'company' )
		{
			$sendData[ 'author' ] = 'urn:li:organization:' . $node_info[ 'node_id' ];
		}
		else
		{
			$sendData[ 'author' ] = 'urn:li:person:' . $node_info[ 'profile_id' ];
		}

		if ( $type === 'link' && ! empty( $link ) )
		{
			$sendData[ 'content' ] = [
				'article' => self::scrapeURL( $link, $sendData[ 'author' ], $accessToken, $proxy )
			];
		}
		else if ( $type === 'image' && ! empty( $images ) && is_array( $images ) )
		{
			$uploadedImages = self::uploadImages( $images, $sendData[ 'author' ], $accessToken, $proxy );

			if ( ! empty( $uploadedImages ) )
			{
				if ( count( $uploadedImages ) == 1 )
				{
					$sendData[ 'content' ][ 'media' ] = reset( $uploadedImages );
				}
				else
				{
					$sendData[ 'content' ][ 'multiImage' ][ 'images' ] = $uploadedImages;
				}
			}
		}
		else if ( $type === 'video' )
		{
			$videoUploaded = self::uploadVideo( $sendData[ 'author' ], $video, $accessToken, $proxy );

			if ( ! $videoUploaded )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Failed to upload the video' )
				];
			}

			$sendData[ 'content' ][ 'media' ] = [
				'id'    => $videoUploaded,
				'title' => $title
			];
		}

		$client  = new Client();
		$options = [
			'headers' => [
				'Connection'                => 'Keep-Alive',
				'X-li-format'               => 'json',
				'Content-Type'              => 'application/json',
				'X-RestLi-Protocol-Version' => '2.0.0',
				'LinkedIn-Version'          => 202304,
				'Authorization'             => 'Bearer ' . $accessToken
			],
			'body'    => json_encode( $sendData )
		];

		if ( ! empty( $proxy ) )
		{
			$options[ 'proxy' ] = $proxy;
		}

		try
		{
			$result = $client->post( 'https://api.linkedin.com/rest/posts', $options );

			if ( ! empty( $result->getHeader( 'x-restli-id' )[ 0 ] ) )
			{
				return [
					'status' => 'ok',
					'id'     => $result->getHeader( 'x-restli-id' )[ 0 ]
				];
			}

			$result = json_decode( $result->getBody()->getContents(), TRUE );
		}
		catch ( Exception $e )
		{
			if ( method_exists( $e, 'getResponse' ) )
			{
				$result = json_decode( $e->getResponse()->getBody()->getContents(), TRUE );
			}
			else
			{
				return [
					'status'    => 'error',
					'error_msg' => $e->getMessage()
				];
			}
		}

		if ( isset( $result[ 'error' ] ) && isset( $result[ 'error' ][ 'message' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => $result[ 'error' ][ 'message' ]
			];
		}
		else if ( isset( $result[ 'message' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => $result[ 'message' ]
			];
		}

		return [
			'status'    => 'error',
			'error_msg' => fsp__( 'Error!' )
		];
	}

	private static function scrapeURL ( $url, $author, $accessToken, $proxy )
	{
		$scrapeData = [
			'source' => $url
		];

		$scraped = URLScraper::scrape( $url );

		$scrapeData[ 'title' ]       = $scraped[ 'title' ];
		$scrapeData[ 'description' ] = $scraped[ 'description' ];

		if ( ! empty( $scraped[ 'image' ] ) )
		{
			$image = self::saveRemoteImage( $scraped[ 'image' ] );

			if ( $image !== FALSE )
			{
				$uploadThumb = self::uploadImages( [ $image ], $author, $accessToken, $proxy );

				if ( ! empty( $uploadThumb ) )
				{
					$scrapeData[ 'thumbnail' ] = reset( $uploadThumb )[ 'id' ];
				}
			}

		}

		return $scrapeData;
	}

    private static function uploadImages ( $images, $author, $accessToken, $proxy )
    {
        $send_upload_data = [
			'registerUploadRequest' => [
				'owner'                    => $author,
				'recipes'                  => [
					'urn:li:digitalmediaRecipe:feedshare-image'
				],
				'serviceRelationships'     => [
					[
						'identifier'       => 'urn:li:userGeneratedContent',
						'relationshipType' => 'OWNER'
					]
				],
				'supportedUploadMechanism' => [
					'SYNCHRONOUS_UPLOAD'
				]
            ]
        ];
        $uploaded_images  = [];
        $client           = new Client();

        foreach ( $images as $imageURL )
        {
            try
            {
				$result = self::cmd( 'assets?action=registerUpload', 'POST', $accessToken, $send_upload_data, $proxy );

				if ( ! isset( $result[ 'value' ][ 'uploadMechanism' ][ 'com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest' ][ 'uploadUrl' ] ) || empty( $result[ 'value' ][ 'uploadMechanism' ][ 'com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest' ][ 'uploadUrl' ] ) )
                {
                    throw new Exception();
                }

				$uploadURL = $result[ 'value' ][ 'uploadMechanism' ][ 'com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest' ][ 'uploadUrl' ];
				$mediaID   = explode( ':', $result[ 'value' ][ 'asset' ] )[ 3 ];

				$mimeType = Helper::mimeContentType( $imageURL );

				$fileContent = FALSE;

				if( $mimeType !== FALSE && strpos( $mimeType, 'webp' ) !== FALSE )
				{
					$fileContent = Helper::webpToJpg( $imageURL );
				}

				if ( $fileContent === FALSE )
				{
					$fileContent = file_get_contents( $imageURL );
				}

				$resp = $client->request( 'PUT', $uploadURL, [
					'body'    => $fileContent,
					'headers' => [
						'Authorization' => 'Bearer ' . $accessToken,
						'proxy'         => empty( $proxy ) ? NULL : $proxy,
					]
				] );

				$mediaStatus = self::cmd( 'assets/' . $mediaID, 'GET', $accessToken, [], $proxy );

				if ( isset( $mediaStatus[ 'recipes' ][ 0 ][ 'status' ] ) && $mediaStatus[ 'recipes' ][ 0 ][ 'status' ] === 'AVAILABLE' )
                {
					$uploaded_images[] = $result[ 'value' ][ 'asset' ];
                }
                else
                {
                    throw new Exception();
                }
            }
            catch ( Exception $e )
            {
            }
        }

		return array_map( function ( $e ) {
			if ( strpos( $e, ':' ) !== FALSE )
			{
				$e = explode( ':', $e );
				$e = end( $e );
			}

			return [
				'id' => 'urn:li:image:' . $e
			];
		}, $uploaded_images );
    }

	private static function uploadVideo ( $owner, $file, $accessToken, $proxy )
	{

		$initialData = [
			'initializeUploadRequest' => [
				'owner'           => $owner,
				'fileSizeBytes'   => strlen( file_get_contents( $file ) ),
				'uploadCaptions'  => FALSE,
				'uploadThumbnail' => FALSE
			]
		];

        $client = new Client();

        $etags = [];

		try
		{
			$res = $client->post( 'https://api.linkedin.com/rest/videos?action=initializeUpload', [
				'headers' => self::makeHeaders( $accessToken ),
				'proxy'   => empty( $proxy ) ? NULL : $proxy,
				'body'    => json_encode( $initialData )
			] )->getBody()->getContents();

			$res = json_decode( $res, TRUE );
		}
		catch ( Exception $e )
		{
			return FALSE;
		}

		if ( ! isset( $res[ 'value' ][ 'uploadInstructions' ] ) || ! isset( $res[ 'value' ][ 'video' ] ) )
		{
			return FALSE;
		}

		$video       = $res[ 'value' ][ 'video' ];
		$uploadToken = isset( $res[ 'value' ][ 'uploadToken	' ] ) ? $res[ 'value' ][ 'uploadToken	' ] : '';

		$fileContent = file_get_contents( $file );

		foreach ( $res[ 'value' ][ 'uploadInstructions' ] as $part )
		{
			try
			{
				$headers = $client->post( $res[ 'value' ][ 'uploadInstructions' ][ 0 ][ 'uploadUrl' ], [
						'headers' => [
							'X-RestLi-Protocol-Version' => '2.0.0',
							'Authorization'             => 'Bearer ' . $accessToken,
							'LinkedIn-Version'          => 202304,
							'Content-Type'              => 'application/octet-stream'
						],
						'proxy'   => empty( $proxy ) ? NULL : $proxy,
						'body'    => substr( $fileContent, $part[ 'firstByte' ], $part[ 'lastByte' ] - $part[ 'firstByte' ] + 1 )
					]
				)->getHeaders();

				if ( ! isset( $headers[ 'ETag' ][ 0 ] ) )
				{
					return FALSE;
				}

				$etags[] = $headers[ 'ETag' ][ 0 ];
			}
			catch ( Exception $e )
			{
				return FALSE;
			}
		}

		//finalize
		$final = [
			'finalizeUploadRequest' => [
				'video'           => $video,
				'uploadToken'     => $uploadToken,
				'uploadedPartIds' => $etags
			]
		];

		try
		{
			$done = $client->post( 'https://api.linkedin.com/rest/videos?action=finalizeUpload', [
				'body'    => json_encode( $final ),
				'headers' => self::makeHeaders( $accessToken ),
				'proxy'   => empty( $proxy ) ? NULL : $proxy
			] )->getStatusCode();

			if ( $done == 200 )
			{
				return $video;
			}
		}
		catch ( Exception $e )
		{
			return FALSE;
		}

		return FALSE;
	}

	//for upload video
	private static function makeHeaders ( $accessToken )
	{
		return [
			'Content-Type'              => 'application/json',
			'X-RestLi-Protocol-Version' => '2.0.0',
			'Authorization'             => 'Bearer ' . $accessToken,
			'LinkedIn-Version'          => 202304
		];
	}

	public static function cmd ( $cmd, $method, $accessToken, array $data = [], $proxy = '' )
	{
		$url = 'https://api.linkedin.com/v2/' . $cmd;

		$method = $method === 'POST' ? 'POST' : ( $method === 'DELETE' ? 'DELETE' : 'GET' );

		$headers = [
			'Connection'                => 'Keep-Alive',
			'X-li-format'               => 'json',
			'Content-Type'              => 'application/json',
			'X-RestLi-Protocol-Version' => '2.0.0',
			'Authorization'             => 'Bearer ' . $accessToken,
            'LinkedIn-Version'          => 202304,
        ];

		if ( $method === 'POST' )
		{
			$data = json_encode( $data );
		}

		$data1 = Curl::getContents( $url, $method, $data, $headers, $proxy );
		$data  = json_decode( $data1, TRUE );

		if ( ! is_array( $data ) )
		{
			$data = [
				'error' => [ 'message' => fsp__( 'Error data!' ) ]
			];
		}

		return $data;
	}

	public static function getLoginURL ( $appId )
	{
		Session::set( 'app_id', $appId );
		Session::set( 'proxy', Request::get( 'proxy', '', 'string' ) );

		$appInf = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'linkedin' ] );
		$appId  = $appInf[ 'app_id' ];

		$permissions = self::getScope();

		$callbackUrl = self::callbackUrl();

		return sprintf( 'https://www.linkedin.com/oauth/v2/authorization?redirect_uri=%s&scope=%s&response_type=code&client_id=%s&state=%s', $callbackUrl, $permissions, $appId, uniqid() );
	}

	public static function getScope ()
	{
        $permissions = [ 'rw_organization_admin', 'w_member_social', 'w_organization_social', 'openid', 'profile' ];

		return implode( ',', array_map( 'urlencode', $permissions ) );
	}

	public static function callbackURL ()
	{
		return site_url() . '/?linkedin_callback=1';
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
			$error_description = Request::get( 'error_description', '', 'str' );

			return [
				'status'    => FALSE,
				'error_msg' => $error_description
			];
		}

		$proxy = Session::get( 'proxy' );

		Session::remove( 'app_id' );
		Session::remove( 'proxy' );

		$appInf    = DB::fetch( 'apps', [ 'id' => $appId, 'driver' => 'linkedin' ] );
		$appSecret = $appInf[ 'app_secret' ];
		$appId2    = $appInf[ 'app_id' ];

		$token_url = "https://www.linkedin.com/oauth/v2/accessToken?" . "client_id=" . $appId2 . "&redirect_uri=" . urlencode( self::callbackUrl() ) . "&client_secret=" . $appSecret . "&code=" . $code . '&grant_type=authorization_code';

		$response = Curl::getURL( $token_url, $proxy );
		$params   = json_decode( $response, TRUE );

		if ( isset( $params[ 'error' ][ 'message' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => $params[ 'error' ][ 'message' ]
			];
		}

		$access_token  = esc_html( $params[ 'access_token' ] );
		$refresh_token = esc_html( $params[ 'refresh_token' ] );
		$expireIn      = Date::dateTimeSQL( 'now', '+' . (int) $params[ 'expires_in' ] . ' seconds' );

		return self::authorize( $appId, $access_token, $expireIn, $refresh_token, $proxy );
	}

	public static function accessToken ( $account_id, $token_info )
	{
		if ( ( Date::epoch() + 30 ) > Date::epoch( $token_info[ 'expires_on' ] ) )
		{
			$app     = DB::fetch( 'apps', [ 'id' => $token_info[ 'app_id' ] ] );
			$account = DB::fetch( 'accounts', [ 'id' => $account_id ] );

			$sendData = [
				'grant_type'    => 'refresh_token',
				'refresh_token' => $token_info[ 'refresh_token' ],
				'client_id'     => $app[ 'app_id' ],
				'client_secret' => $app[ 'app_secret' ]
			];

			$token_url = 'https://www.linkedin.com/oauth/v2/accessToken';
			$response  = Curl::getContents( $token_url, 'POST', $sendData, [], $account[ 'proxy' ], TRUE );

			$token_data = json_decode( $response, TRUE );

			if ( is_array( $token_data ) && isset( $token_data[ 'access_token' ] ) )
			{
				$expires_on = Date::dateTimeSQL( 'now', '+' . (int) $token_data[ 'expires_in' ] . ' seconds' );
				DB::DB()->update( DB::table( 'account_access_tokens' ), [
					'access_token' => $token_data[ 'access_token' ],
					'expires_on'   => $expires_on
				], [ 'id' => $token_info[ 'id' ] ] );
			}
			else
			{
				AccountService::disable_account( $account_id, fsp__( 'LinkedIn API access token life is a year and it is expired. Please add your account to the plugin again without deleting the account from the plugin; as a result, account settings will remain as it is.' ) );

				return [
					'status'    => FALSE,
					'error_msg' => fsp__( 'LinkedIn API access token life is a year and it is expired. Please add your account to the plugin again without deleting the account from the plugin; as a result, account settings will remain as it is.' )
				];
			}

		}

		return $token_info[ 'access_token' ];
	}

	public static function authorize ( $appId, $accessToken, $scExpireIn, $refreshToken, $proxy )
	{
        try{
            $c = new Client();
            $me = $c->get('https://api.linkedin.com/v2/userinfo', [
                'headers' => [
                    'Connection'                => 'Keep-Alive',
                    'X-li-format'               => 'json',
                    'Content-Type'              => 'application/json',
                    'X-RestLi-Protocol-Version' => '2.0.0',
                    'Authorization'             => 'Bearer ' . $accessToken,
                    'LinkedIn-Version'          => 202308,
                ],
                'proxy' => empty($proxy) ? null : $proxy
            ])->getBody()->getContents();
        }
        catch (Exception $e)
        {
            if( method_exists($e, 'getResponse') && method_exists($e->getResponse(), 'getBody') && method_exists( $e->getResponse()->getBody(), 'getContents' ) )
            {
                $me = $e->getResponse()->getBody()->getContents();
            }
            else
            {
                $me = json_encode(['message' => $e->getMessage()]);
            }
        }

        $me = json_decode( $me, true );
        $me = empty($me) ? [] : $me;

		if ( isset( $me[ 'error' ] ) && isset( $me[ 'error' ][ 'message' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => $me[ 'error' ][ 'message' ]
			];
		}
        else if( isset( $me['message'] ) )
        {
            return [
                'status'    => FALSE,
                'error_msg' => $me['message']
            ];
        }
		else if ( isset( $me[ 'status' ] ) && $me[ 'status' ] === '401' )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'LinkedIn API access token life is a year and it is expired. Please add your account to the plugin again without deleting the account from the plugin; as a result, account settings will remain as it is.' )
			];
		}
		else if ( isset( $me[ 'status' ] ) && $me[ 'status' ] === '429' )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'You reached a limit. Please try again later.' )
			];
		}

        if ( ! isset( $me[ 'sub' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'Unknown error!' )
			];
		}

        $meId = $me[ 'sub' ];

		// temp
		if ( in_array( $meId, [
			'DgzRPOUDFh',
			'WVbjJSf2gE',
			'TwndIiDvx5',
			'Bzzo611rFa',
			'2SrrGk2mIR',
			'q8zf4uDnAj',
			'8D9foESFIM',
			'hqRK4ThVjU'
		] ) )
		{
			exit( 'Your use of the FS Poster Standard APP is suspended due to suspicious activity. If you think it is a mistake, please contact us via email at <b>support@fs-poster.com</b>.' );
		}

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
			'driver'     => 'linkedin',
			'profile_id' => $meId
		] );

		$dataSQL = [
			'blog_id'     => Helper::getBlogId(),
			'user_id'     => get_current_user_id(),
            'name'        => isset($me['name']) ? $me['name'] : ' ',
			'driver'      => 'linkedin',
			'profile_id'  => $meId,
            'profile_pic' => isset($me['picture']) ? $me['picture'] : Pages::asset( 'Base', 'img/no-photo.png' ),
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

		// acccess token
		DB::DB()->insert( DB::table( 'account_access_tokens' ), [
			'account_id'    => $accId,
			'app_id'        => $appId,
			'expires_on'    => $scExpireIn,
			'access_token'  => $accessToken,
			'refresh_token' => $refreshToken
		] );

		// my pages load
		self::refetch_account( $accId, $accessToken, $proxy );

		return [
			'status' => TRUE,
			'id'     => $accId
		];
	}

	public static function getStats ( $post_id, $proxy )
	{
		return [
			'comments' => 0,
			'like'     => 0,
			'shares'   => 0,
			'details'  => ''
		];
	}

	public static function checkAccount ( $accessToken, $proxy )
	{
		$result = [
			'error'     => TRUE,
			'error_msg' => NULL
		];

		$me = self::cmd( 'me', 'GET', $accessToken, [], $proxy );

		if ( isset( $me[ 'error' ] ) && isset( $me[ 'error' ][ 'message' ] ) )
		{
			$result[ 'error_msg' ] = $me[ 'error' ][ 'message' ];
        }
		else if ( isset( $me[ 'status' ] ) && $me[ 'status' ] === '401' )
        {
			$result[ 'error_msg' ] = fsp__( 'LinkedIn API access token life is a year and it is expired. Please add your account to the plugin again without deleting the account from the plugin; as a result, account settings will remain as it is.' );
		}
		else if ( isset( $me[ 'status' ] ) && $me[ 'status' ] === '429' )
            {
			$result[ 'error_msg' ] = fsp__( 'You reached a limit. Please try again later.' );
            }
		else if ( ! isset( $me[ 'error' ] ) )
            {
			$result[ 'error' ] = FALSE;
        }

            // temp
		$meId = $me[ 'id' ];

            if ( in_array( $meId, [
                'DgzRPOUDFh',
                'WVbjJSf2gE',
                'TwndIiDvx5',
                'Bzzo611rFa',
                '2SrrGk2mIR',
                'q8zf4uDnAj',
                '8D9foESFIM',
                'hqRK4ThVjU',
                'Yx-6vHSGm7'
            ] ) )
            {
			$result[ 'error' ]     = TRUE;
			$result[ 'error_msg' ] = 'Your use of the FS Poster Standard APP is suspended due to suspicious activity. If you think it is a mistake, please contact us via email at support@fs-poster.com.';
		}

		return $result;
	}

	public static function refetch_account ( $account_id, $access_token, $proxy )
	{
		$companies = self::cmd( 'organizationalEntityAcls', 'GET', $access_token, [
			'q'          => 'roleAssignee',
			'role'       => 'ADMINISTRATOR',
			'projection' => '(elements*(organizationalTarget~(id,localizedName,vanityName,logoV2(original~:playableStreams))))'
		], $proxy );
		$get_nodes = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $account_id ] ), ARRAY_A );
		$my_nodes  = [];

		foreach ( $get_nodes as $node )
		{
			$my_nodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
		}

		if ( isset( $companies[ 'elements' ] ) && is_array( $companies[ 'elements' ] ) )
		{
			foreach ( $companies[ 'elements' ] as $company )
			{
				$node_id = isset( $company[ 'organizationalTarget~' ][ 'id' ] ) ? $company[ 'organizationalTarget~' ][ 'id' ] : 0;

				$cover = '';

				if ( isset( $company[ 'organizationalTarget~' ][ 'logoV2' ][ 'original~' ][ 'elements' ][ 0 ][ 'identifiers' ][ 0 ][ 'identifier' ] ) )
				{
					$cover = $company[ 'organizationalTarget~' ][ 'logoV2' ][ 'original~' ][ 'elements' ][ 0 ][ 'identifiers' ][ 0 ][ 'identifier' ];
				}

				if ( ! in_array( $node_id, $my_nodes ) )
				{
					DB::DB()->insert( DB::table( 'account_nodes' ), [
						'blog_id'    => Helper::getBlogId(),
						'user_id'    => get_current_user_id(),
						'driver'     => 'linkedin',
						'account_id' => $account_id,
						'node_type'  => 'company',
						'node_id'    => $node_id,
						'name'       => isset( $company[ 'organizationalTarget~' ][ 'localizedName' ] ) ? $company[ 'organizationalTarget~' ][ 'localizedName' ] : '-',
						'category'   => isset( $company[ 'organizationalTarget~' ][ 'organizationType' ] ) && is_string( $company[ 'organizationalTarget~' ][ 'organizationType' ] ) ? $company[ 'organizationalTarget~' ][ 'organizationType' ] : '',
						'cover'      => $cover
					] );
				}
				else
				{
					DB::DB()->update( DB::table( 'account_nodes' ), [
						'name'  => isset( $company[ 'organizationalTarget~' ][ 'localizedName' ] ) ? $company[ 'organizationalTarget~' ][ 'localizedName' ] : '-',
						'cover' => $cover
					], [
						'account_id' => $account_id,
						'node_id'    => $node_id
					] );
				}

				unset( $my_nodes[ array_search( $node_id, $my_nodes ) ] );
			}
		}

		if ( ! empty( $my_nodes ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
		}

		return [ 'status' => TRUE ];
	}

	private static function saveRemoteImage ( $file )
	{
		if ( ! function_exists( 'tempnam' ) || ! function_exists( 'sys_get_temp_dir' ) )
		{
			return FALSE;
		}

		$imagePath = tempnam( sys_get_temp_dir(), 'FS_tmpfile_' );

		if ( $imagePath === FALSE )
		{
			return FALSE;
		}

		$fc = file_put_contents( $imagePath, Curl::getURL( $file ) );

		if ( $fc === FALSE )
		{
			return FALSE;
		}

		return $imagePath;
	}
}
