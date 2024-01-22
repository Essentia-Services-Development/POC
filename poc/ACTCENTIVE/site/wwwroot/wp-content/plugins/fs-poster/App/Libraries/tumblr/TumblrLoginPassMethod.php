<?php

namespace FSPoster\App\Libraries\tumblr;

use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSP_GuzzleHttp\Cookie\CookieJar;
use FSP_GuzzleHttp\Exception\GuzzleException;

class TumblrLoginPassMethod
{
	private $email;
	private $pass;
	private $cookies;
	private $proxy;
	private $headers;

	public function __construct ( $login = '', $pass = '', $proxy = '', $decode = TRUE )
	{
		$this->proxy = $proxy;
		$this->email = $login;
		$this->pass  = $decode ? $this->decodePass( $pass ) : $pass;

		$this->headers = [
			'Origin'     => 'https://www.tumblr.com',
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36'
		];

		$cookies                   = $this->getCookies();
		$cookies[ 'euconsent-v2' ] = 'CPPg3k1PPg3weECABAENB0CgAPLAAHLAAKiQIVtf_X__bX9n-_79__t0eY1f9_r3v-QzjhfNt-8F2L_W_L0X_2E7NF36pq4KuR4ku3bBIQNtHMnUTUmxaolVrzHsak2cpyNKJ7LkmnsZe2dYGHtPn9lT-ZKZ7_7___f73z___9_-39z3_9f___d_____-_v___9____________9________ghWASYal5AF2ZY4Mm0aVQogRhWEhUAoAKKAYWiKwAYHBTsrAI9YQsAEJqAjAiBBiCjBgEAAgkASERASAFggEQBEAgABAChAQgAImAQWAFgYBAAKAaFiBFAEIEhBkcFRymBAVItFBLZWAJQV7GmEAZb4EUCi-iowEazRAsDISFg5jgCQEvFkgeAAA';
		$this->cookies             = CookieJar::fromArray( $cookies, 'tumblr.com' );
	}

	private function getCookies ()
	{
		$response = $this->cmd( 'GET', 'https://www.tumblr.com/login' )->getBody()->getContents();

		$csrfPattern = '/\"csrfToken\":\"(.+?)\"/';
		preg_match( $csrfPattern, $response, $matches );
		$csrfToken = $matches[ 1 ];

		$keyPattern = '/\"API_TOKEN\":\"(.+?)\"/';
		preg_match( $keyPattern, $response, $matches );
		$apiKey = $matches[ 1 ];

		$headers = [
			'Authorization' => 'Bearer ' . $apiKey,
			'X-CSRF'        => $csrfToken,
			'Referer'       => 'https://www.tumblr.com/login',
			'Content-Type'  => 'application/json'
		];

		$data = '{"password":"' . $this->pass . '","grant_type":"password","username":"' . $this->email . '"}';

		$response = $this->cmd( 'POST', 'https://www.tumblr.com/api/v2/oauth2/token', $headers, $data );

		$header  = $response->getHeader( 'Set-Cookie' );
		$cookies = [];

		foreach ( $header as $h )
		{
			$c                  = explode( ';', $h )[ 0 ];
			$c                  = explode( '=', $c );
			$cookies[ $c[ 0 ] ] = $c[ 1 ];
		}

		return $cookies;
	}

	private function getConsent ()
	{
		$dashboard = self::cmd( 'GET', 'https://www.tumblr.com/privacy/consent/begin?redirect=%2Fdashboard' )->getBody()->getContents();

		$keyPattern = '/\"API_TOKEN\":\"(.+?)\"/';
		preg_match( $keyPattern, $dashboard, $matches );
		$apiKey = $matches[ 1 ];

		preg_match( '/\"csrfToken\":\"(.+?)\"/', $dashboard, $matches );
		$csrfToken = $matches[ 1 ];

		$headers = [
			'Authorization' => 'Bearer ' . $apiKey,
			'X-CSRF'        => $csrfToken,
			'Referer'       => 'https://www.tumblr.com/privacy/consent/begin?redirect=%2Fdashboard',
			'Content-Type'  => 'application/json'
		];

		$this->cmd( 'POST', 'https://www.tumblr.com/api/v2/privacy/consent', $headers, '{"tcfv2_consent":"{\"tcString\":\"CPPg3k1PPhIJcECABAENB0CgAPLAAHLAAKiQIVtf_X__bX9n-_79__t0eY1f9_r3v-QzjhfNt-8F2L_W_L0X_2E7NF36pq4KuR4ku3bBIQNtHMnUTUmxaolVrzHsak2cpyNKJ7LkmnsZe2dYGHtPn9lT-ZKZ7_7___f73z___9_-39z3_9f___d_____-_v___9____________9________ghWASYal5AF2ZY4Mm0aVQogRhWEhUAoAKKAYWiKwAYHBTsrAI9YQsAEJqAjAiBBiCjBgEAAgkASERASAFggEQBEAgABAChAQgAImAQWAFgYBAAKAaFiBFAEIEhBkcFRymBAVItFBLZWAJQV7GmEAZb4EUCi-iowEazRAsDISFg5jgCQEvFkgeAAA\",\"tcfPolicyVersion\":2,\"cmpId\":258,\"cmpVersion\":1,\"gdprApplies\":true,\"eventStatus\":\"useractioncomplete\",\"cmpStatus\":\"loaded\",\"isServiceSpecific\":true,\"useNonStandardStacks\":false,\"publisherCC\":\"US\",\"purposeOneTreatment\":true,\"purpose\":{\"consents\":\"111100101100000000000000\"},\"vendor\":{\"consents\":\"11010111111111110101111111111111110110110101111111011001111111101111111110111111011111111111111011011101000111100110001101010111111111011111111010111101111011111111100100001100111000111000010111110011011011011111101111000001011101100010111111110101101111110010111101000101111111111101100001001110110011010001011101111110101010011010101110000010101011100100011110001001001011101101110110110000010010000100000011011011010001110011001001110101000100110101001001101100010110101010001001010101011010111100110001111011000110101001001101100111001010011100100011010010100010011110110010111001001001101001111011000110010111101101100111010110000001100001111011010011111001111111011001010100111111100110010010100110011110111111111110111111111111111111110111111110111101111100111111111111111111111111011111111111101101111111011100111101111111111111010111111111111111111111110111011111111111111111111111111111111111101111111011111111111111111111111111011111111111111111111111111111111111111111111111111111111111111111111111111111011111111111111111111111111111111111111111111111111\"},\"specialFeatureOptIns\":{},\"x-tumblr-nonIabVendorConsents\":{\"1\":true,\"5\":true}}"}' )->getBody()->getContents();
	}

	private function cmd ( $method, $url, $headers = [], $body = '', $query = '' )
	{
		$client  = new Client();
		$options = [];

		$options[ 'headers' ] = $this->headers;

		if ( ! empty( $headers ) )
		{
			$options[ 'headers' ] = array_merge( $options[ 'headers' ], $headers );
		}

		if ( ! empty( $this->cookies ) )
		{
			$options[ 'cookies' ] = $this->cookies;
		}

		if ( ! empty( $this->proxy ) )
		{
			$options[ 'proxy' ] = $this->proxy;
		}

		if ( ! empty( $body ) )
		{
			$options[ 'body' ] = $body;
		}

		if ( ! empty( $query ) )
		{
			$options[ 'query' ] = $query;
		}

		try
		{
			$response = $client->request( $method, $url, $options );
		}
		catch ( GuzzleException $e )
		{
			$response = $e->getResponse();
		}

		return $response;
	}

	private function getFormKey ()
	{
		$keyPattern = "/<meta name=\"tumblr-form-key\" id=\"tumblr_form_key\" content=\"(.+?)\">/";

		$result = $this->cmd( 'GET', 'https://www.tumblr.com/neue_web/iframe/new/text' )->getBody()->getContents();

		preg_match( $keyPattern, $result, $matches );

		return isset( $matches[ 1 ] ) ? $matches[ 1 ] : '';
	}

	private function secureKey ( $type )
	{
		$key = $this->getFormKey();

		$result = $this->cmd( 'POST', 'https://www.tumblr.com/svc/secure_form_key', [
			'X-tumblr-form-key' => $key,
			'X-Requested-With'  => 'XMLHttpRequest',
			'Referer'           => 'https://www.tumblr.com/neue_web/iframe/new/' . $type
		] )->getHeaders();

		return [
			'csrf'       => isset( $result[ 'X-Csrf' ][ 0 ] ) ? $result[ 'X-Csrf' ][ 0 ] : '',
			'x-rid'      => isset( $result[ 'X-Rid' ][ 0 ] ) ? $result[ 'X-Rid' ][ 0 ] : '',
			'secure_key' => isset( $result[ 'X-Tumblr-Secure-Form-Key' ][ 0 ] ) ? $result[ 'X-Tumblr-Secure-Form-Key' ][ 0 ] : '',
			'form_key'   => $key
		];
	}

	private function upload ( $name, $type )
	{
		$type = $type == 'video' ? $type : 'photo';
		$url  = 'https://www.tumblr.com/svc/post/upload_' . $type . '?source=post_type_form';

		$client = new Client();

		$formKey = $this->getFormKey();

		$headers = [
			'Referer'           => 'https://www.tumblr.com/neue_web/iframe/new/video',
			'X-tumblr-form-key' => $formKey
		];

		$headers = array_merge( $headers, $this->headers );

		try
		{
			$response = $client->request( 'POST', $url, [
				'cookies'   => $this->cookies,
				'headers'   => $headers,
				'multipart' => [
					[
						'contents'     => file_get_contents( $name ),
						'name'         => 'video',
						'filename'     => $name,
						'Content-Type' => 'video/mp4'
					]
				]
			] )->getBody()->getContents();
		}
		catch ( GuzzleException $e )
		{
			$response = $e->getResponse()->getBody()->getContents();
		}

		$response = json_decode( $response, TRUE );

		if ( isset( $response[ 'meta' ][ 'status' ] ) && $response[ 'meta' ][ 'status' ] == 200 )
		{
			return [
				'ch'  => $response[ 'response' ][ 0 ][ 'ch' ],
				'url' => $response[ 'response' ][ 0 ][ 'key' ]
			];
		}

		return [
			'status' => 'error'
		];
	}

	public function sendPost ( $username, $type, $title, $message, $link, $tags = '', $images = [], $video = '', $thumbnail = '', $excerpt = '' )
	{
		$this->getConsent();
		$sendData = [
			'channel_id'           => $username,
			'context_id'           => $username,
			'members_only'         => FALSE,
			'can_be_liked'         => TRUE,
			'send_to_twitter'      => FALSE,
			'owner_flagged_nsfw'   => FALSE,
			'can_be_reblogged'     => TRUE,
			'enable_cta'           => FALSE,
			'tsp_skip_lightbox'    => TRUE,
			'cta_text_code'        => 0,
			'enable_redirect_urls' => FALSE,
			'redirect_url_primary' => '',
			'redirect_url_ios'     => '',
			'redirect_url_android' => '',
			'editor_type'          => 'html',
			'pt'                   => '',
			'context_bundle'       => 'redpop',
			'post[date]'           => '',
			'post[publish_on]'     => '',
			'post[slug]'           => '',
			'post[state]'          => 0
			//'loggingData'          => []
		];

		if ( $type === 'image' )
		{
			$sendData[ 'post[type]' ] = 'photo';

			$i  = 0;
			$s1 = '';
			$s2 = [];
			foreach ( $images as $image )
			{
				$i++;
				$sendData[ 'images[o' . $i . ']' ] = $image;
				$s1                                .= '1';
				$s2[]                              = 'o' . $i;
			}

			$sendData[ 'post[two]' ] = $message;

			$sendData[ 'post[photoset_layout]' ] = $s1;
			$sendData[ 'post[photoset_order]' ]  = implode( ',', $s2 );
		}
		else if ( $type === 'video' )
		{
			$vu                            = $this->upload( $video, 'video' );
			$sendData[ 'post[type]' ]      = 'video';
			$sendData[ 'post[two]' ]       = $message;
			$sendData[ 'preuploaded_ch' ]  = $vu[ 'ch' ];
			$sendData[ 'preuploaded_url' ] = $vu[ 'url' ];
			$sendData[ 'confirm_tos' ]     = TRUE;
		}
		else if ( $type === 'custom_message' || $type === 'text' )
		{
			$sendData[ 'post[type]' ] = 'regular';
			$sendData[ 'post[one]' ]  = $title;
			$sendData[ 'post[two]' ]  = $message;
		}
		else if ( $type == 'quote' )
		{
			$sendData[ 'post[type]' ] = 'quote';
			$sendData[ 'post[one]' ]  = $message;
		}
		else //type=link
		{
			$sendData[ 'post[type]' ]           = 'link';
			$sendData[ 'post[three]' ]          = $message;
			$sendData[ 'post[two]' ]            = $link;
			$sendData[ 'post[one]' ]            = $title;
			$sendData[ 'thumbnail_pre_upload' ] = 1;

			if ( ! empty( $thumbnail ) )
			{
				$sendData[ 'thumbnail' ] = $thumbnail;
			}

			if ( ! empty( $excerpt ) )
			{
				$sendData[ 'excerpt' ] = $excerpt;
			}
		}

		if ( ! empty( $tags ) )
		{
			$sendData[ 'post[tags]' ] = $tags;
		}

		$secureKey = $this->secureKey( $sendData[ 'post[type]' ] );

		$headers = [
			'X-tumblr-puppies'  => $secureKey[ 'secure_key' ],
			'X-tumblr-form-key' => $secureKey[ 'form_key' ],
			'Content-Type'      => 'application/json',
			'X-Requested-With'  => 'XMLHttpRequest',
			'Referer'           => 'https://www.tumblr.com/neue_web/iframe/new/' . $sendData[ 'post[type]' ]
		];

		$response = $this->cmd( 'POST', 'https://www.tumblr.com/svc/post/update', $headers, json_encode( $sendData ) )->getBody()->getContents();

		$responseArr = json_decode( $response, TRUE );
		$response    = empty( $responseArr ) ? $response : $responseArr;

		if ( is_string( $response ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( $response )
			];
		}

		if ( isset( $response[ 'errors' ][ '0' ][ 'message' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( $response[ 'errors' ][ '0' ][ 'message' ] )
			];
		}

		if ( ! empty( $response[ 'post' ][ 'id' ] ) )
		{
			return [
				'status' => 'ok',
				'id'     => $response[ 'post' ][ 'id' ]
			];
		}

		return [
			'status'    => 'error',
			'error_msg' => fsp__( 'Unknown error!' )
		];
	}

	private function getUserInfo ()
	{
		$dashboard = self::cmd( 'GET', 'https://www.tumblr.com/dashboard' )->getBody()->getContents();

		$keyPattern = '/\"API_TOKEN\":\"(.+?)\"/';
		preg_match( $keyPattern, $dashboard, $matches );
		$apiKey = $matches[ 1 ];

		$headers = [
			'Authorization' => 'Bearer ' . $apiKey,
			'Referer'       => 'https://www.tumblr.com/dashboard'
		];

		$userInfo = self::cmd( 'GET', 'https://www.tumblr.com/api/v2/user/info', $headers, '', [
			'fields[blogs]' => 'avatar,name,title,url,can_message,description,is_adult,uuid,is_private_channel,posts,is_group_channel,?primary,?admin,?drafts,?followers,?queue,?has_flagged_posts,messages,ask,?can_submit,?tweet,mention_key,?timezone_offset,?analytics_url,?is_premium_partner,?is_blogless_advertiser,?can_onboard_to_paywall,?is_tumblrpay_onboarded,?is_paywall_on,?linked_accounts,theme'
		] )->getBody()->getContents();

		$userInfoArr = json_decode( $userInfo, TRUE );

		if ( empty( $userInfoArr ) )
		{
			return [
				'status'  => 'error',
				'message' => $userInfo
			];
		}

		if ( ! isset( $userInfoArr[ 'meta' ][ 'status' ] ) || $userInfoArr[ 'meta' ][ 'status' ] != 200 )
		{
			return [
				'status'    => 'error',
				'error_msg' => isset( $userInfoArr[ 'meta' ][ 'msg' ] ) ? $userInfoArr[ 'meta' ][ 'msg' ] : 'Unknown error!'
			];
		}

		if ( ! isset( $userInfoArr[ 'response' ][ 'user' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => 'Couldn\'t retrieve the user!'
			];
		}

		$user            = [];
		$user[ 'blogs' ] = [];

		$user[ 'name' ] = $userInfoArr[ 'response' ][ 'user' ][ 'name' ];

		foreach ( $userInfoArr[ 'response' ][ 'user' ][ 'blogs' ] as $blog )
		{
			$user[ 'blogs' ][] = [
				'name'        => $blog[ 'name' ],
				'title'       => $blog[ 'title' ],
				'url'         => $blog[ 'url' ],
				'profile_pic' => $blog[ 'avatar' ][ 0 ][ 'url' ]
			];
		}

		return $user;
	}

	public function authorize ()
	{
		$userInfo = $this->getUserInfo();

		if ( empty( $userInfo ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Couldn\'t get the account!' )
			];
		}

		if ( isset( $userInfo[ 'status' ] ) && $userInfo[ 'status' ] = 'error' )
		{
			return $userInfo;
		}

		$username = $userInfo[ 'name' ];

		$checkLoginRegistered = DB::fetch( 'accounts', [
			'blog_id'  => Helper::getBlogId(),
			'user_id'  => get_current_user_id(),
			'driver'   => 'tumblr',
			'username' => $username,
			'email'    => $this->email
		] );

		$dataSQL = [
			'blog_id'   => Helper::getBlogId(),
			'user_id'   => get_current_user_id(),
			'name'      => $username,
			'email'     => $this->email,
			'password'  => $this->encodePass( $this->pass ),
			'driver'    => 'tumblr',
			'username'  => $username,
			'proxy'     => $this->proxy,
			'status'    => NULL,
			'error_msg' => NULL
		];

		if ( $checkLoginRegistered && ! empty( $checkLoginRegistered[ 'password' ] ) )
		{
			$accId = $checkLoginRegistered[ 'id' ];

			DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $accId ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), $dataSQL );

			$accId = DB::DB()->insert_id;
		}

		$get_nodes = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $accId ] ), ARRAY_A );
		$my_nodes  = [];

		foreach ( $get_nodes as $node )
		{
			$my_nodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
		}

		foreach ( $userInfo[ 'blogs' ] as $blogInf )
		{
			if ( ! in_array( $blogInf[ 'name' ], $my_nodes ) )
			{
				DB::DB()->insert( DB::table( 'account_nodes' ), [
					'blog_id'      => Helper::getBlogId(),
					'user_id'      => get_current_user_id(),
					'driver'       => 'tumblr',
					'screen_name'  => $blogInf[ 'name' ],
					'account_id'   => $accId,
					'node_type'    => 'blog',
					'node_id'      => $blogInf[ 'name' ],
					'name'         => $blogInf[ 'name' ],
					'access_token' => NULL,
					'category'     => $blogInf[ 'primary' ] ? 'primary' : 'not-primary'
				] );
			}
			else
			{
				DB::DB()->update( DB::table( 'account_nodes' ), [
					'screen_name' => $blogInf[ 'name' ],
					'node_id'     => $blogInf[ 'name' ],
					'name'        => $blogInf[ 'name' ],
					'category'    => $blogInf[ 'primary' ] ? 'primary' : 'not-primary'
				], [
					'screen_name' => $blogInf[ 'name' ],
					'account_id'  => $accId,
					'node_id'     => $blogInf[ 'name' ]
				] );
			}

			unset( $my_nodes[ array_search( $blogInf[ 'name' ], $my_nodes ) ] );
		}

		if ( ! empty( $my_nodes ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
		}

		return [
			'status' => 'ok'
		];
	}

	public function checkAccount ()
	{
		$user = $this->getUserInfo();

		if ( empty( $user[ 'name' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'The account is disconnected from the plugin. Please add your account to the plugin again by getting the cookie on the browser <a href=\'https://www.fs-poster.com/documentation/fs-poster-schedule-auto-publish-wordpress-posts-to-tumblr\' target=\'_blank\'>Incognito mode</a>. And close the browser without logging out from the account.', [], FALSE )
			];
		}

		return [ 'status' => TRUE ];
	}

	public function refetchAccount ()
	{
		$res = $this->authorize();

		if ( $res[ 'status' ] == 'ok' )
		{
			return [ 'status' => TRUE ];
		}

		return [ 'status' => FALSE ];
	}

	public static function getStats ( $post_id, $accessToken, $proxy )
	{
		return [
			'comments' => 0,
			'like'     => 0,
			'shares'   => 0,
			'details'  => 0
		];
	}

	private function encodePass ( $password )
	{
		return '(-F-S-P-)' . str_rot13( base64_encode( $password . '(-F-S-P-)' . 'xxx' ) );
	}

	private function decodePass ( $password )
	{
		return substr( $password, 0, 9 ) === '(-F-S-P-)' ? explode( '(-F-S-P-)', base64_decode( str_rot13( substr( $password, 9 ) ) ) )[ 0 ] : $password;
	}
}