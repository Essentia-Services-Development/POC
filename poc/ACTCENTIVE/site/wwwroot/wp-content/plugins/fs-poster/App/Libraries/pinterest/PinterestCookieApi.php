<?php

namespace FSPoster\App\Libraries\pinterest;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSP_GuzzleHttp\Cookie\CookieJar;
use FSP_GuzzleHttp\Psr7\MultipartStream;

class PinterestCookieApi
{
	/**
	 * @var Client
	 */
	private $client;
	private $cookie;
	private $proxy;
	private $domain = 'www.pinterest.com';

	public function __construct ( $cookie, $proxy )
	{
		$this->cookie = $cookie;
		$this->proxy  = $proxy;

		$this->setClient();
		$this->findDomainAlias();
	}

	private function setClient ( $max_redirects = 0 )
	{
		$csrf_token = base64_encode( microtime( 1 ) . rand( 0, 99999 ) );

		$cookieJar = new CookieJar( FALSE, [
			[
				"Name"     => "_pinterest_sess",
				"Value"    => $this->cookie,
				"Domain"   => '.' . $this->domain,
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => FALSE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE,
				"Priority" => "HIGH"
			],
			[
				"Name"     => "csrftoken",
				"Value"    => $csrf_token,
				"Domain"   => '.' . $this->domain,
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => FALSE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE,
				"Priority" => "HIGH"
			]
		] );

		$this->client = new Client( [
			'cookies'         => $cookieJar,
			'allow_redirects' => [ 'max' => $max_redirects ],
			'proxy'           => empty( $this->proxy ) ? NULL : $this->proxy,
			'verify'          => FALSE,
			'http_errors'     => FALSE,
			'headers'         => [
				'User-Agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:75.0) Gecko/20100101 Firefox/75.0',
				'x-CSRFToken' => $csrf_token
			]
		] );
	}

	private function findDomainAlias ()
	{
		$result     = $this->client->get( 'https://' . $this->domain );
		$locationTo = $result->getHeader( 'Location' );

		if ( isset( $locationTo[ 0 ] ) && is_string( $locationTo[ 0 ] ) )
		{
			$domain       = parse_url( $locationTo[ 0 ] );
			$this->domain = $domain[ 'host' ];
		}

		$this->setClient( 10 );
	}

	public function sendPost ( $boardId, $title, $message, $link, $images, $altText )
	{
		if ( mb_strlen( $message ) > 500 )
		{
			$message = Helper::cutText( $message, 497 );
		}

		$image = reset( $images );

		if ( function_exists( 'getimagesize' ) )
		{
			$result = @getimagesize( $image );

			if ( isset( $result[ 0 ], $result[ 1 ] ) )
			{
				$width  = $result[ 0 ];
				$height = $result[ 1 ];

				if ( $width < 200 || $height < 300 )
				{
					return [
						'status'    => 'error',
						'error_msg' => fsp__( 'Pinterest supports images bigger than 200x300. Your image is %sx%s.', [
							$width,
							$height
						] )
					];
				}
			}
		}

		if ( empty( $image ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'An image is required to pin on board!' )
			];
		}

		try
		{
			$response = $this->client->post( 'https://' . $this->domain . '/resource/VIPResource/create/', [
				'form_params' => [
					'source_url' => '/pin-builder/',
					'data'       => '{"options":{"type":"pinimage"},"context":{}}'
				]
			] )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			$response = '';

			if ( method_exists( $e, 'getResponse' ) )
			{
				$response = $e->getResponse();
			}

			if ( empty( $response ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => $e->getMessage()
				];
			}

			if ( ! method_exists( $response, 'getBody' ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Unknown error!' )
				];
			}

			$response = $response->getBody()->getContents();

		}

		$response = json_decode( $response, TRUE );

		if ( empty( $response[ 'resource_response' ][ 'data' ][ 'upload_id' ] ) || empty( $response[ 'resource_response' ][ 'data' ][ 'upload_url' ] ) || empty( $response[ 'resource_response' ][ 'data' ][ 'upload_parameters' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => $this->errorMessage( $response )
			];
		}

		$uploadData = [];

		foreach ( $response[ 'resource_response' ][ 'data' ][ 'upload_parameters' ] as $k => $v )
		{
			$uploadData[] = [
				'name'     => $k,
				'contents' => $v
			];
		}

		$uploadData[] = [
			'name'     => 'file',
			'filename' => 'blob',
			'contents' => fopen( $image, 'r' ),
			'headers'  => [ 'Content-Type' => Helper::mimeContentType( $image ) ]
		];

		$body = new MultipartStream( $uploadData, '----WebKitFormBoundaryIddk0tpr7i6Kd6Bz' );

		$c = new Client();
		try
		{
			$uploaded = $c->post( $response[ 'resource_response' ][ 'data' ][ 'upload_url' ], [
				'proxy'   => $this->proxy,
				'headers' => [
					'Content-Length'     => strlen( $body ),
					'Accept'             => '*/*',
					'Accept-Encoding'    => 'gzip',
					'User-Agent'         => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36',
					'Origin'             => 'https://www.pinterest.com',
					'Referer'            => 'https://www.pinterest.com',
					'sec-ch-ua'          => '".Not/A)Brand";v="99", "Google Chrome";v="103", "Chromium";v="103"',
					'sec-ch-ua-mobile'   => '?0',
					'sec-ch-ua-full'     => '?1',
					'sec-ch-ua-platform' => '"Windows"',
					'Sec-Fetch-Dest'     => 'empty',
					'Sec-Fetch-Mode'     => 'cors',
					'Sec-Fetch-Site'     => 'same-origin',
					'Connection'         => 'keep-alive',
					'Content-Type'       => 'multipart/form-data; boundary=----WebKitFormBoundaryIddk0tpr7i6Kd6Bz'
				],
				'body'    => $body
			] )->getHeaders();
		}
		catch ( Exception $e )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Couldn\'t upload the image!' )
			];
		}

		if ( empty( $uploaded[ 'ETag' ][ 0 ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Unknown error' )
			];
		}

		$etag     = trim( $uploaded[ 'ETag' ][ 0 ], '"' );
		$imageUrl = sprintf( 'https://i.pinimg.com/736x/%s%s/%s%s/%s%s/%s.jpg', $etag[ 0 ], $etag[ 1 ], $etag[ 2 ], $etag[ 3 ], $etag[ 4 ], $etag[ 5 ], $etag );

		$sendData = [
			'options' => [
				'board_id'                     => $boardId,
				'field_set_key'                => 'create_success',
				'skip_pin_create_log'          => TRUE,
				'description'                  => $message,
				'alt_text'                     => $altText,
				'link'                         => $link,
				'title'                        => $title,
				'image_url'                    => $imageUrl,
				'method'                       => 'uploaded',
				'upload_metric'                => [
					'source' => 'pinner_upload_standalone'
				],
				'user_mention_tags'            => [],
				'no_fetch_context_on_resource' => FALSE
			],
			'context' => []
		];

		try
		{
			$response = $this->client->post( 'https://' . $this->domain . '/resource/PinResource/create/', [
				'form_params' => [
					'source_url' => '/pin-builder/',
					'data'       => json_encode( $sendData )
				]
			] )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			$response = $e->getResponse()->getBody()->getContents();
		}

		$response = json_decode( $response, TRUE );

		$pinId = isset( $response[ 'resource_response' ][ 'data' ][ 'id' ] ) ? $response[ 'resource_response' ][ 'data' ][ 'id' ] : '';

		if ( empty( $pinId ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => $this->errorMessage( $response )
			];
		}

		return [
			'status' => 'ok',
			'id'     => $pinId
		];
	}

	private function errorMessage ( $result, $defaultError = '' )
	{
		if ( isset( $result[ 'resource_response' ][ 'message' ] ) && is_string( $result[ 'resource_response' ][ 'message' ] ) )
		{
			return esc_html( $result[ 'resource_response' ][ 'message' ] );
		}

		if ( isset( $result[ 'resource_response' ][ 'error' ][ 'message' ] ) && is_string( $result[ 'resource_response' ][ 'error' ][ 'message' ] ) )
		{
			return esc_html( $result[ 'resource_response' ][ 'error' ][ 'message' ] . ( isset( $result[ 'resource_response' ][ 'error' ][ 'message_detail' ] ) && is_string( $result[ 'resource_response' ][ 'error' ][ 'message_detail' ] ) ? ' ' . $result[ 'resource_response' ][ 'error' ][ 'message_detail' ] : '' ) );
		}

		if ( ! empty( $defaultError ) )
		{
			return $defaultError;
		}

		return fsp__( 'Couldn\'t upload the image!' );
	}

	public function getAccountData ()
	{
		try
		{
			$response = (string) $this->client->get( 'https://' . $this->domain . '/resource/HomefeedBadgingResource/get/' )->getBody();
		}
		catch ( Exception $e )
		{
			$response = '';
		}

		if ( strpos( $response, 'a bot running on your network' ) > -1 )
		{
			Helper::response( FALSE, fsp__( 'Error! Your domain has been blocked by Pinterest. You can use a proxy to avoid the issue.' ) );
		}

		$result = json_decode( $response, TRUE );

		$id        = isset( $result[ 'client_context' ][ 'user' ][ 'id' ] ) ? $result[ 'client_context' ][ 'user' ][ 'id' ] : '';
		$image     = isset( $result[ 'client_context' ][ 'user' ][ 'image_medium_url' ] ) ? $result[ 'client_context' ][ 'user' ][ 'image_medium_url' ] : '';
		$username  = isset( $result[ 'client_context' ][ 'user' ][ 'username' ] ) ? $result[ 'client_context' ][ 'user' ][ 'username' ] : '';
		$full_name = isset( $result[ 'client_context' ][ 'user' ][ 'full_name' ] ) ? $result[ 'client_context' ][ 'user' ][ 'full_name' ] : '';

		if ( empty( $id ) || empty( $username ) )
		{
			Helper::response( FALSE, $this->errorMessage( $result, fsp__( 'Error! Please check the data and try again!' ) ) );
		}

		return [
			'id'          => $id,
			'full_name'   => $full_name,
			'profile_pic' => $image,
			'username'    => $username
		];
	}

	public function getBoards ( $userName )
	{
		$data = [
			"options" => [
				"isPrefetch"           => FALSE,
				"privacy_filter"       => "all",
				"sort"                 => "custom",
				"field_set_key"        => "profile_grid_item",
				"username"             => $userName,
				"page_size"            => 25,
				"group_by"             => "visibility",
				"include_archived"     => TRUE,
				"redux_normalize_feed" => TRUE
			],
			"context" => []
		];

		$boards_arr = [];
		$bookmark   = '';

		while ( TRUE )
		{
			if ( ! empty( $bookmark ) )
			{
				$data[ 'options' ][ 'bookmarks' ] = [ $bookmark ];
			}

			try
			{
				$response = (string) $this->client->get( 'https://' . $this->domain . '/resource/BoardsResource/get/?data=' . urlencode( json_encode( $data ) ) )->getBody();
				$response = json_decode( $response, TRUE );
			}
			catch ( Exception $e )
			{
				$response = [];
			}

			if ( ! isset( $response[ 'resource_response' ][ 'data' ] ) || ! is_array( $response[ 'resource_response' ][ 'data' ] ) )
			{
				$boards = [];
			}
			else
			{
				$boards = $response[ 'resource_response' ][ 'data' ];
			}

			foreach ( $boards as $board )
			{
				$boards_arr[] = [
					'id'    => $board[ 'id' ],
					'name'  => $board[ 'name' ],
					'url'   => ltrim( $board[ 'url' ], '/' ),
					'cover' => isset( $board[ 'image_cover_url' ] ) ? $board[ 'image_cover_url' ] : ''
				];
			}

			if ( isset( $response[ 'resource_response' ][ 'bookmark' ] ) && is_string( $response[ 'resource_response' ][ 'bookmark' ] ) && ! empty( $response[ 'resource_response' ][ 'bookmark' ] ) && $response[ 'resource_response' ][ 'bookmark' ] != '-end-' )
			{
				$bookmark = $response[ 'resource_response' ][ 'bookmark' ];
			}
			else
			{
				break;
			}
		}

		return $boards_arr;
	}

	/**
	 * @return array
	 */
	public function checkAccount ()
	{
		$result = [
			'error'     => TRUE,
			'error_msg' => NULL
		];

		try
		{
			$response = (string) $this->client->get( 'https://' . $this->domain . '/resource/HomefeedBadgingResource/get/' )->getBody();
		}
		catch ( Exception $e )
		{
			$response = '';
		}

		$json_result = json_decode( $response, TRUE );

		$id       = isset( $json_result[ 'client_context' ][ 'user' ][ 'id' ] ) ? $json_result[ 'client_context' ][ 'user' ][ 'id' ] : '';
		$username = isset( $json_result[ 'client_context' ][ 'user' ][ 'username' ] ) ? $json_result[ 'client_context' ][ 'user' ][ 'username' ] : '';

		if ( empty( $id ) || empty( $username ) )
		{
			$result[ 'error_msg' ] = fsp__( 'The account is disconnected from the FS Poster plugin. Please update your account cookie to connect it to the plugin again. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue8\' target=\'_blank\'>How to?</a>.', [], FALSE );
		}
		else
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}

	public function refetch_account ( $account_id )
	{
		$details   = $this->getAccountData();
		$get_nodes = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $account_id ] ), ARRAY_A );
		$my_nodes  = [];

		foreach ( $get_nodes as $node )
		{
			$my_nodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
		}

		foreach ( $this->getBoards( $details[ 'username' ] ) as $board )
		{
			if ( ! in_array( $board[ 'id' ], $my_nodes ) )
			{
				DB::DB()->insert( DB::table( 'account_nodes' ), [
					'blog_id'     => Helper::getBlogId(),
					'user_id'     => get_current_user_id(),
					'account_id'  => $account_id,
					'driver'      => 'pinterest',
					'node_type'   => 'board',
					'node_id'     => $board[ 'id' ],
					'name'        => $board[ 'name' ],
					'cover'       => $board[ 'cover' ],
					'screen_name' => $board[ 'url' ],
				] );
			}
			else
			{
				DB::DB()->update( DB::table( 'account_nodes' ), [
					'name'        => $board[ 'name' ],
					'cover'       => $board[ 'cover' ],
					'screen_name' => $board[ 'url' ],
				], [
					'account_id' => $account_id,
					'node_id'    => $board[ 'id' ]
				] );
			}

			unset( $my_nodes[ array_search( $board[ 'id' ], $my_nodes ) ] );
		}

		if ( ! empty( $my_nodes ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
		}

		return [ 'status' => TRUE ];
	}
}
