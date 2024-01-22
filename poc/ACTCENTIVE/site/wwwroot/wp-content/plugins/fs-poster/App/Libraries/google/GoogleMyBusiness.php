<?php

namespace FSPoster\App\Libraries\google;

use stdClass;
use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSP_GuzzleHttp\Cookie\CookieJar;

class GoogleMyBusiness
{
	private $at;
	private $cookies;
	private $client;
	private $main_page_html;
	private $fsid;
	private $postFormAt;
	private $uiserver;

	public function __construct ( $sid, $hsid, $ssid, $sapisid, $proxy = '' )
	{
		$this->cookies = [
			[
				"Name"     => "SID",
				"Value"    => $sid,
				"Domain"   => ".google.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => TRUE
			],
			[
				"Name"     => "HSID",
				"Value"    => $hsid,
				"Domain"   => ".google.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE
			],
			[
				"Name"     => "SSID",
				"Value"    => $ssid,
				"Domain"   => ".google.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE
			],
			[
				"Name"     => "SAPISID",
				"Value"    => $sapisid,
				"Domain"   => ".google.com",
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => TRUE,
				"Discard"  => FALSE,
				"HttpOnly" => TRUE
			]
		];

		$cookieJar = new CookieJar( FALSE, $this->cookies );

		$this->client = new Client( [
			'cookies'         => $cookieJar,
			'allow_redirects' => [ 'max' => 20 ],
			'proxy'           => empty( $proxy ) ? NULL : $proxy,
			'verify'          => FALSE,
			'http_errors'     => FALSE,
			'headers'         => [ 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0' ]
		] );
	}

	public function getUserInfo ( $html = NULL )
	{
		if ( is_null( $html ) )
		{
			$html = $this->getMainPageHTML();
		}

		$html = str_replace( "\n", "", $html );

		preg_match( '/window\.WIZ_global_data = (\{.*?});/mi', $html, $matches );
		if ( isset( $matches[ 1 ] ) )
		{
			$jsonInf = json_decode( str_replace( [ '\x', "'", ',]' ], [ '', '"', ']' ], $matches[ 1 ] ), TRUE );

			preg_match( '/url\((https?:\/\/.+googleusercontent\.com.+)\)/Ui', $html, $profilePhoto );

			$accountId    = isset( $jsonInf[ 'S06Grb' ] ) ? $jsonInf[ 'S06Grb' ] : NULL;
			$accountEmail = isset( $jsonInf[ 'oPEP7c' ] ) ? $jsonInf[ 'oPEP7c' ] : NULL;

			$userInfo = [
				'id'            => $accountId,
				'name'          => $accountEmail,
				'email'         => $accountEmail,
				'profile_image' => isset( $profilePhoto[ 1 ] ) ? $profilePhoto[ 1 ] : NULL
			];

		}
		else
		{
			$userInfo = [
				'id'            => NULL,
				'name'          => NULL,
				'email'         => NULL,
				'profile_image' => NULL
			];
		}

		return $userInfo;
	}

	public function sendPost ( $postTo, $nodeDataArr, $isProduct, $text, $link = NULL, $linkButton = 'LEARN_MORE', $imageURL = '', $productName = NULL, $productPrice = NULL, $productCurrency = NULL, $productCategory = NULL )
	{
		if ( ! $nodeDataArr[ 'is_verified' ] )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'You need to verify your locations to share posts on it!' )
			];
		}

		$postFormParams = $this->getPostFormParams( $postTo );

		if ( $postFormParams === FALSE )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'The account is disconnected from the FS Poster plugin. Please update your account cookies to connect your account to the plugin again. <a href=\'https://www.fs-poster.com/documentation/commonly-encountered-issues#issue8\' target=\'_blank\'>How to?</a>.', [], FALSE )
			];
		}

		if ( Helper::getOption( 'gmb_autocut', '1' ) == 1 && mb_strlen( $text ) > 1500 )
		{
			$text = mb_substr( $text, 0, 1497 ) . '...';
		}

		if ( $isProduct )
		{
			return $this->addProduct( $postTo, $nodeDataArr, $productName, $text, $productCategory, $productCurrency, $productPrice, $imageURL, $link );
		}
		else
		{
			return $this->addUpdate( $postTo, $nodeDataArr, $text, $imageURL, $link, $linkButton );
		}
	}

	public function addProduct ( $profileID, $nodeDataArr, $title, $description, $category, $currency, $price, $image, $link )
	{
		if ( empty( $image ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Product image is required!' )
			];
		}

		if ( empty( $title ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Product title cannot be empty!' )
			];
		}

		if ( mb_strlen( $title ) > 58 )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Product title must not exceed 58 characters!' )
			];
		}

		if ( empty( $category ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'A product category must be set!' )
			];
		}

		if ( mb_strlen( $category ) > 58 )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Product product category must not exceed 58 characters!' )
			];
		}

		$priceArr = empty( $currency ) || empty( $price ) ? NULL : [
			[ $currency, (float) $price, NULL ],
			[ $currency, (float) $price, NULL ]
		];

		$fReqParam = [
			[
				[
					"u72bYd",
					json_encode( [
						$nodeDataArr[ 'listing_id' ],
						[
							NULL,
							$title,
							$description,
							$category,
							$priceArr,
							$this->uploadProductPhoto( $image, $nodeDataArr ),
							[ $link, 'VISIT_SITE' ],
							NULL,
							[],
							NULL,
							NULL,
							NULL,
							NULL,
							[],
							1,
							NULL,
							NULL,
							[]
						]
					], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
					NULL,
					"generic"
				]
			]
		];

		try
		{
			$body = http_build_query( [
					'f.req' => json_encode( $fReqParam, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
					'at'    => $this->postFormAt
				] ) . '&';

			$post = ( string ) $this->client->post( 'https://www.google.com/local/business/_/GeoMerchantFrontendEmbeddedUi/data/batchexecute', [
				'query'   => [
					'rpcids'       => 'u72bYd',
					'source-path'  => '/local/business/' . $profileID . '/editprofile/products/add',
					'f.sid'        => $this->fsid,
					'bl'           => $this->uiserver,
					//'boq_geomerchantfrontenduiserver_20230502.00_p0' cfb2h -fsid olan yerdə
					'hl'           => 'en-GB',
					'ih'           => 'lu',
					'soc-app'      => 'soc-app',
					'soc-platform' => '1',
					'soc-device'   => '1',
					'_reqid'       => '166735',
					//?
					'rt'           => 'c'
				],
				'body'    => $body,
				'headers' => [
					'Content-Length'   => strlen( $body ),
					'content-type'     => 'application/x-www-form-urlencoded;charset=UTF-8',
					'sec-ch-ua-mobile' => '?0',
					'User-Agent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
					'sec-ch-ua'        => '"Google Chrome";v="87", " Not;A Brand";v="99", "Chromium";v="87"',
					'Referer'          => 'https://business.google.com/'
				]
			] )->getBody();

			preg_match( '/(\[\[.+]])(\r\n|\r|\n)/', $post, $matches );

			if ( empty( $matches[ 1 ] ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Unknown error!' )
				];
			}

			$post = json_decode( $matches[ 1 ], TRUE );

			if ( ! is_array( $post ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Unknown error!' )
				];
			}

			if ( empty( $post[ 0 ][ 2 ] ) )
			{
				return $this->getPostErrors( $post );
			}

			$postInfo = json_decode( $post[ 0 ][ 2 ], TRUE );

			if ( empty( $postInfo ) || empty( $postInfo[ 0 ] ) || ! is_string( $postInfo[ 0 ] ) )
			{
				return $this->getPostErrors( $post );
			}

			$postURL = explode( '/', $postInfo[ 0 ] );

			if ( $postURL === FALSE || count( $postURL ) !== 6 )
			{
				return $this->getPostErrors( $post );
			}

			return [
				'status' => 'ok',
				'id'     => 'products/' . $postURL[ 5 ]
			];
		}
		catch ( Exception $e )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Unknown error!' )
			];
		}
	}

	public function addUpdate ( $profileID, $nodeData, $message, $image, $link, $linkButton )
	{
		$uploadedImage = empty( $image ) ? NULL : $this->uploadPostPhoto( $profileID, $image, $nodeData );
		$fReqParam     = [
			[
				[
					'h6IfIc',
					json_encode( [
						$nodeData[ 'listing_id' ],
						[
							NULL,
							$message,
							NULL,
							NULL,
							( ! empty( $link ) && $linkButton !== '-' ? [
								NULL,
								$link,
								$linkButton,
								$linkButton
							] : [] ),
							[],
							NULL,
							NULL,
							NULL,
							NULL,
							NULL,
							NULL,
							NULL,
							$uploadedImage,
							1,
							NULL,
							NULL,
							NULL,
							NULL,
							NULL,
							NULL,
							NULL,
							[]
						],
						NULL,
						[]
					], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
					NULL,
					'generic',
				],
			],
		];

		try
		{
			$body = http_build_query( [
					'f.req' => json_encode( $fReqParam, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
					'at'    => $this->postFormAt
				] ) . '&';

			$post = ( string ) $this->client->post( 'https://www.google.com/local/business/_/GeoMerchantFrontendEmbeddedUi/data/batchexecute', [
				'query'   => [
					'rpcids'       => 'h6IfIc',
					'source-path'  => '/local/business/' . $profileID . '/promote/updates/add',
					'f.sid'        => $this->fsid,
					'bl'           => $this->uiserver,
					//'boq_geomerchantfrontenduiserver_20230502.04_p0', cfb2h -fsid olan yerdə
					'hl'           => 'en-GB',
					'ih'           => 'lu',
					'soc-app'      => 'soc-app',
					'soc-platform' => '1',
					'soc-device'   => '1',
					'_reqid'       => '366925', //?
					'rt'           => 'c'
				],
				'body'    => $body,
				'headers' => [
					'Content-Length'   => strlen( $body ),
					'content-type'     => 'application/x-www-form-urlencoded;charset=UTF-8',
					'sec-ch-ua-mobile' => '?0',
					'User-Agent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
					'sec-ch-ua'        => '"Google Chrome";v="87", " Not;A Brand";v="99", "Chromium";v="87"',
					'Referer'          => 'https://business.google.com/'
				]
			] )->getBody();

			preg_match( '/(\[\[.+]])(\r\n|\r|\n)/', $post, $matches );

			if ( empty( $matches[ 1 ] ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Unknown error!' )
				];
			}

			$post = json_decode( $matches[ 1 ], TRUE );

			if ( ! is_array( $post ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'Unknown error!' )
				];
			}

			if ( empty( $post[ 0 ][ 2 ] ) )
			{
				return $this->getPostErrors( $post );
			}

			$postInfo = json_decode( $post[ 0 ][ 2 ], TRUE );

			if ( empty( $postInfo ) || empty( $postInfo[ 0 ][ 0 ] ) || ! is_string( $postInfo[ 0 ][ 0 ] ) )
			{
				return $this->getPostErrors( $post );
			}

			$postURL = explode( '/', $postInfo[ 0 ][ 0 ] );

			if ( $postURL === FALSE || count( $postURL ) !== 6 )
			{
				return $this->getPostErrors( $post );
			}

			return [
				'status' => 'ok',
				'id'     => 'localPosts/' . $postURL[ 5 ]
			];
		}
		catch ( Exception $e )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Unknown error!' )
			];
		}
	}

	function getPostErrors ( $post )
	{
		if ( empty( $post ) || ( empty( $post[ 0 ][ 5 ][ 2 ][ 0 ][ 1 ][ 0 ][ 0 ][ 2 ] ) && empty( $post[ 0 ][ 5 ][ 2 ][ 0 ][ 1 ][ 1 ][ 0 ][ 2 ] ) ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Unknown error!' )
			];
		}

		if ( isset( $post[ 0 ][ 5 ][ 2 ][ 0 ][ 1 ][ 0 ][ 0 ][ 2 ] ) && is_string( $post[ 0 ][ 5 ][ 2 ][ 0 ][ 1 ][ 0 ][ 0 ][ 2 ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error: ' ) . $post[ 0 ][ 5 ][ 2 ][ 0 ][ 1 ][ 0 ][ 0 ][ 2 ]
			];
		}

		if ( isset( $post[ 0 ][ 5 ][ 2 ][ 0 ][ 1 ][ 1 ][ 0 ][ 2 ] ) && is_string( $post[ 0 ][ 5 ][ 2 ][ 0 ][ 1 ][ 1 ][ 0 ][ 2 ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error: ' ) . $post[ 0 ][ 5 ][ 2 ][ 0 ][ 1 ][ 1 ][ 0 ][ 2 ]
			];
		}

		return [
			'status'    => 'error',
			'error_msg' => fsp__( 'Unknown error!' )
		];
	}

	private function getAT ()
	{
		if ( is_null( $this->at ) )
		{
			$plusMainPage = $this->getMainPageHTML();

			preg_match( '/\"SNlM0e\":\"([^\"]+)/', $plusMainPage, $at );
			$this->at = isset( $at[ 1 ] ) ? $at[ 1 ] : NULL;
		}

		return $this->at;
	}

	public function getPostFormParams ( $profileID )
	{
		if ( ! empty( $this->fsid ) && ! empty( $this->postFormAt ) && ! empty( $this->uiserver ) )
		{
			return [
				'fsid'       => $this->fsid,
				'postFormAt' => $this->postFormAt,
				'uiserver'   => $this->uiserver
			];
		}

		try
		{
			$response = $this->client->get( 'https://www.google.com/local/business/' . $profileID . '/promote/updates/add' )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			$response = '';
		}

		if ( empty( $response ) )
		{
			return FALSE;
		}

		preg_match( '/\"FdrFJe\":\"([^\"]+)/', $response, $fsid );
		$this->fsid = isset( $fsid[ 1 ] ) ? $fsid[ 1 ] : NULL;

		preg_match( '/\"SNlM0e\":\"([^\"]+)/', $response, $att );
		$this->postFormAt = isset( $att[ 1 ] ) ? $att[ 1 ] : NULL;

		preg_match( '/\"cfb2h\":\"([^\"]+)/', $response, $att );
		$this->uiserver = isset( $att[ 1 ] ) ? $att[ 1 ] : NULL;

		if ( ! empty( $this->fsid ) && ! empty( $this->postFormAt ) && ! empty( $this->uiserver ) )
		{
			return [
				'fsid'       => $this->fsid,
				'postFormAt' => $this->postFormAt,
				'uiserver'   => $this->uiserver
			];
		}

		return FALSE;
	}

	public function getEffectiveId ( $id )
	{
		try
		{
			$response = $this->client->get( 'https://www.google.com/local/business/' . $id . '/promote/updates/add' )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			$response = '';
		}

		if ( empty( $response ) )
		{
			return '';
		}

		preg_match( '/AF_initDataCallback\(\{key: \'ds:2\', hash: \'2\', data:(.+?), sideChannel/', $response, $matches );

		if ( empty( $matches[ 1 ] ) )
		{
			return '';
		}

		$arr = json_decode( $matches[ 1 ], TRUE );

		if ( empty( $arr ) || ! isset( $arr[ 0 ][ 8 ][ 4 ] ) )
		{
			return '';
		}

		return $arr[ 0 ][ 8 ][ 4 ];
	}

	public function getUploadURLForProductImage ( $file, $nodeData )
	{
		$fileSize = filesize( $file );
		$body     = [
			'protocolVersion'      => '0.8',
			'createSessionRequest' => [
				'fields' => [
					[
						'external' => [
							'name'     => 'file',
							'filename' => basename( $file ),
							'size'     => $fileSize
						]
					],
					[
						'inlined' => [
							'name'        => 'effective_id',
							'content'     => $nodeData[ 'effective_id' ],//'107777652114006747362',
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'listing_id',
							'content'     => $nodeData[ 'listing_id' ],//'09695599114779657952',
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'upload_source',
							'content'     => 'GMB_PRODUCTS_WEB',
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'silo_id',
							'content'     => '7',
							'contentType' => 'text/plain'
						]
					]
				]
			]
		];

		$body = json_encode( $body );

		try
		{
			$uploadURLHeader = $this->client->post( 'https://www.google.com/local/business/_/upload/dragonfly', [
				'headers' => [
					'Content-Length'               => strlen( $body ),
					'X-Goog-Upload-Command'        => 'start',
					'X-Goog-Upload-Content-Length' => $fileSize,
					'X-Goog-Upload-File-Name'      => basename( $file ),
					'X-Goog-Upload-Protocol'       => 'resumable'
				],
				'body'    => $body
			] )->getHeader( 'X-Goog-Upload-URL' );

			if ( empty( $uploadURLHeader[ 0 ] ) )
			{
				return FALSE;
			}

			return $uploadURLHeader[ 0 ];
		}
		catch ( Exception $e )
		{
			return FALSE;
		}
	}

	public function getUploadURLForPostImage ( $file, $nodeData )
	{
		$fileSize = filesize( $file );

		//onepick data collected from: https://docs.google.com/picker?protocol=gadgets
		$body = [
			'protocolVersion'      => '0.8',
			'createSessionRequest' => [
				'fields' => [
					[
						'external' => [
							'name'     => 'file',
							'filename' => basename( $file ),
							'put'      => new stdClass(),
							'size'     => $fileSize
						]
					],
					[
						'inlined' => [
							'name'        => 'title',
							'content'     => basename( $file ),
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'addtime',
							'content'     => time() . rand( 100, 999 ),
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'onepick_version',
							'content'     => 'v2',
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'onepick_host_id',
							'content'     => '20',
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'onepick_host_usecase',
							'content'     => 'bfe',
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'listing_id',
							'content'     => $nodeData[ 'listing_id' ],
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'upload_source',
							'content'     => 'GMB_POSTS_WEB',
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'effective_id',
							'content'     => $nodeData[ 'effective_id' ],
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'photo_metadata',
							'content'     => json_encode( [
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								(string) $nodeData[ 'photo_metadata' ]
							], JSON_UNESCAPED_SLASHES ),
							'contentType' => 'text/plain'
						]
					],
					[
						'inlined' => [
							'name'        => 'silo_id',
							'content'     => '7',
							'contentType' => 'text/plain'
						]
					]
				]
			]
		];

		$body = json_encode( $body, JSON_UNESCAPED_SLASHES );

		try
		{
			$uploadURLHeader = $this->client->post( 'https://docs.google.com/upload/gmb/dragonfly', [
				'headers' => [
					'Content-Length'                      => strlen( $body ),
					'X-Goog-Upload-Command'               => 'start',
					'X-Goog-Upload-Header-Content-Length' => $fileSize,
					//'X-Goog-Upload-File-Name'      => basename( $file ),
					'X-Goog-Upload-Protocol'              => 'resumable',
					'X-Goog-Upload-Header-Content-Type'   => Helper::mimeContentType( $file )
				],
				'body'    => $body
			] );

			$uploadURLHeader = $uploadURLHeader->getHeader( 'X-Goog-Upload-URL' );
			if ( empty( $uploadURLHeader[ 0 ] ) )
			{
				return FALSE;
			}

			return $uploadURLHeader[ 0 ];
		}
		catch ( Exception $e )
		{
			return FALSE;
		}
	}

	public function uploadImageContent ( $uploadURL, $file )
	{
		$handle = fopen( $file, 'r' );

		$read = 0;
		while ( ! feof( $handle ) )
		{
			$chunk = fread( $handle, 524288 );

			$headers = [
				'X-Goog-Upload-Offset' => (string) $read,
				'Content-Length'       => (string) strlen( $chunk ),
				'User-Agent'           => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
				'sec-ch-ua-mobile'     => '?0',
				'sec-ch-ua'            => '"Chromium";v="112", "Google Chrome";v="112", "Not:A-Brand";v="99"',
				'Referer'              => 'https://www.google.com/'
			];

			if ( feof( $handle ) )
			{
				$headers[ 'X-Goog-Upload-Command' ]    = 'upload, finalize';
				$headers[ 'X-Goog-Upload-Entity-MD5' ] = md5_file( $file );
			}

			try
			{
				$response = $this->client->put( $uploadURL, [
					'headers' => $headers,
					'body'    => $chunk
				] );

				if ( $response->getStatusCode() !== 200 )
				{
					return FALSE;
				}

				if ( feof( $handle ) )
				{
					$responseBody = $response->getBody()->getContents();

					if ( empty( $responseBody ) )
					{
						return FALSE;
					}

					$responseBody = json_decode( $responseBody, TRUE );

					if ( empty( $responseBody ) )
					{
						return FALSE;
					}

					if ( ! empty( $responseBody[ 'sessionStatus' ][ 'additionalInfo' ][ 'uploader_service.GoogleRupioAdditionalInfo' ][ 'completionInfo' ][ 'customerSpecificInfo' ][ 'image_url' ] ) )
					{
						return $responseBody[ 'sessionStatus' ][ 'additionalInfo' ][ 'uploader_service.GoogleRupioAdditionalInfo' ][ 'completionInfo' ][ 'customerSpecificInfo' ];
					}
				}
			}
			catch ( Exception $e )
			{
				return FALSE;
			}

			$read += 524288;
		}

		return FALSE;
	}

	public function uploadProductPhoto ( $file, $nodeData )
	{
		$uploadURL = self::getUploadURLForProductImage( $file, $nodeData );

		if ( $uploadURL === FALSE )
		{
			return NULL;
		}

		$media = $this->uploadImageContent( $uploadURL, $file );

		if ( $media === FALSE || ! isset( $media[ 'image_url' ], $media[ 'media_key' ] ) )
		{
			return NULL;
		}

		// [ $imageurl, null, $mediakey, $imageurl, $imageurl ]
		return [
			$media[ 'image_url' ],
			NULL,
			$media[ 'media_key' ],
			$media[ 'image_url' ],
			$media[ 'image_url' ]
		];
	}

	public function uploadPostPhoto ( $profileID, $file, $nodeData )
	{
		$uploadURL = self::getUploadURLForPostImage( $file, $nodeData );

		if ( $uploadURL === FALSE )
		{
			return NULL;
		}

		$media = $this->uploadImageContent( $uploadURL, $file );

		if ( $media === FALSE || ! isset( $media[ 'image_url' ], $media[ 'media_key' ] ) )
		{
			return NULL;
		}

		try
		{
			$req_params = [
				[
					[
						"iWixD",
						json_encode( [
							$nodeData[ 'listing_id' ],
							$media[ 'image_url' ],
							[
								NULL,
								$media[ 'image_url' ],
								NULL,
								NULL,
								1
							]
						], JSON_UNESCAPED_SLASHES ),
						NULL,
						"generic"
					]
				]
			];

			$body = http_build_query( [
					'f.req' => json_encode( $req_params, JSON_UNESCAPED_SLASHES ),
					'at'    => $this->postFormAt
				] ) . '&';

			$request = ( string ) $this->client->post( 'https://www.google.com/local/business/_/GeoMerchantFrontendEmbeddedUi/data/batchexecute', [
				'query'   => [
					'rpcids'       => 'iWixD',
					'source-path'  => '/local/business/' . $profileID . '/promote/updates/add',
					'f.sid'        => $this->fsid,
					'bl'           => $this->uiserver,
					//'boq_geomerchantfrontenduiserver_20230502.00_p0', cfb2h -fsid olan yerdə
					'hl'           => 'en-GB',
					'ih'           => 'gmbweb',
					'soc-app'      => 'soc-app',
					'soc-platform' => '1',
					'soc-device'   => '1',
					'_reqid'       => '232061',
					//?
					'rt'           => 'c'
				],
				'body'    => $body,
				'headers' => [
					'Content-Length'   => strlen( $body ),
					'content-type'     => 'application/x-www-form-urlencoded;charset=UTF-8',
					'sec-ch-ua-mobile' => '?0',
					'User-Agent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36',
					'sec-ch-ua'        => '"Google Chrome";v="87", " Not;A Brand";v="99", "Chromium";v="87"',
					'Referer'          => 'https://business.google.com/'
				]
			] )->getBody();

			preg_match( '/(\[\[.+]])(\r\n|\r|\n)/', $request, $matches );

			if ( empty( $matches[ 1 ] ) )
			{
				return NULL;
			}

			$json = json_decode( $matches[ 1 ], TRUE );

			if ( empty( $json ) || empty( $json[ 0 ][ 2 ] ) )
			{
				return NULL;
			}

			$json = json_decode( $json[ 0 ][ 2 ], TRUE );

			if ( empty( $json ) || empty( $json[ 2 ] ) )
			{
				return NULL;
			}

			//[ [$location_url, $image_url, null, null, 1, [ sizex, sizey ], $id, 1] ]
			return [ $json[ 2 ] ];
		}
		catch ( Exception $e )
		{
			return NULL;
		}
	}

	public function getMyLocations ()
	{
		$locations_arr = [];

		foreach ( $this->getLocationGroups() as $groupInf )
		{
			$locationsInGroup = $this->getLocationsByGroup( $groupInf[ 0 ], $groupInf[ 1 ] );

			$locations_arr = array_merge( $locations_arr, $locationsInGroup );
		}

		return $locations_arr;
	}

	private function getLocationGroups ()
	{
		$html = str_replace( "\n", "", $this->getMainPageHTML() );

		if ( ! preg_match( '/AF_initDataCallback\((\{key: \'ds:4.+})\);/Umi', $html, $locationGroups ) )
		{
			return [];
		}

		$locationGroups = preg_replace( '/([{, ])([a-zA-Z0-9_]+):/i', '$1"$2":', $locationGroups[ 1 ] );
		$locationGroups = str_replace( [ '\x', "'", ',]' ], [ '', '"', ']' ], $locationGroups );

		$jsonInf = json_decode( $locationGroups, TRUE );

		if ( ! isset( $jsonInf[ 'data' ][ 0 ] ) || ! is_array( $jsonInf[ 'data' ][ 0 ] ) )
		{
			return [];
		}

		$groups = $jsonInf[ 'data' ][ 0 ];

		$groupsArr = [];
		foreach ( $groups as $group )
		{
			if ( isset( $group[ 0 ][ 0 ] ) && isset( $group[ 0 ][ 1 ] ) )
			{
				$groupsArr[] = [ $group[ 0 ][ 0 ], $group[ 0 ][ 1 ] ];
			}
		}

		return $groupsArr;
	}

	private function getLocationsByGroup ( $groupId, $groupName, $nextPageToken = NULL )
	{
		if ( is_null( $nextPageToken ) )
		{
			$fReqParam = [
				[
					[
						"VlgRab",
						"[[null,[],\"" . $groupId . "\",null,null,[],null,[],null,[]],100]",
						NULL,
						"1"
					]
				]
			];
		}
		else
		{
			$fReqParam = [ [ [ "VlgRab", "[null,100,\"" . $nextPageToken . "\"]", NULL, "1" ] ] ];
		}

		try
		{
			$getLocationsJson = (string) $this->client->post( 'https://business.google.com/_/GeoMerchantFrontendUi/data/batchexecute', [
				'form_params' => [ 'f.req' => json_encode( $fReqParam ), 'at' => $this->getAT() ],
				'headers'     => [ 'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8' ]
			] )->getBody();
		}
		catch ( Exception $e )
		{
			$getLocationsJson = '';
		}

		$getLocationsJson = str_replace( [ "\n", '\n' ], '', $getLocationsJson );

		$getLocationsJson = preg_replace( '/^.+\[/iU', '[', $getLocationsJson );
		$getLocationsJson = preg_replace( '/[0-9]+\[.+$/iU', '', $getLocationsJson );
		$getLocationsJson = json_decode( $getLocationsJson, TRUE );

		if ( ! is_array( $getLocationsJson ) || ! isset( $getLocationsJson[ 0 ][ 2 ] ) )
		{
			return [];
		}

		$locationsJson = json_decode( $getLocationsJson[ 0 ][ 2 ], TRUE );

		if ( ! is_array( $locationsJson ) || ! isset( $locationsJson[ 0 ] ) || ! is_array( $locationsJson[ 0 ] ) )
		{
			return [];
		}

		$locationsArr = [];

		foreach ( $locationsJson[ 0 ] as $locationInf )
		{
			$isVerified = ! isset( $locationInf[ 9 ][ 0 ] );

			$effectiveID = '';

			if ( $isVerified )
			{
				$effectiveID = self::getEffectiveId( $locationInf[ 21 ] );
			}

			$locationsArr[] = [
				'id'       => $locationInf[ 21 ],
				'name'     => $locationInf[ 3 ],
				'category' => $groupName,
				'data'     => json_encode( [
					'listing_id'     => $locationInf[ 1 ],
					'is_verified'    => $isVerified,
					'effective_id'   => $effectiveID,
					'photo_metadata' => isset( $locationInf[ 8 ][ 6 ][ 1 ] ) ? $locationInf[ 8 ][ 6 ][ 1 ] : NULL
				] )
			];
		}

		if ( ! empty( $locationsJson[ 1 ] ) )
		{
			$locationsArr = array_merge( $locationsArr, $this->getLocationsByGroup( $groupId, $groupName, $locationsJson[ 1 ] ) );
		}

		return $locationsArr;
	}

	private function getMainPageHTML ()
	{
		if ( is_null( $this->main_page_html ) )
		{
			try
			{
				$this->main_page_html = (string) $this->client->get( 'https://business.google.com/locations' )->getBody();
			}
			catch ( Exception $e )
			{
				$this->main_page_html = '';
			}
		}

		return $this->main_page_html;
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

		if ( $this->getAT() )
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}

	public function refetch_account ( $accountId )
	{
		$get_nodes = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $accountId ] ), ARRAY_A );
		$my_nodes  = [];

		foreach ( $get_nodes as $node )
		{
			$my_nodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
		}

		$locations = $this->getMyLocations();
		foreach ( $locations as $location )
		{
			if ( ! in_array( $location[ 'id' ], $my_nodes ) )
			{
				DB::DB()->insert( DB::table( 'account_nodes' ), [
					'blog_id'    => Helper::getBlogId(),
					'user_id'    => get_current_user_id(),
					'account_id' => $accountId,
					'node_type'  => 'location',
					'node_id'    => $location[ 'id' ],
					'name'       => $location[ 'name' ],
					'category'   => $location[ 'category' ],
					'driver'     => 'google_b',
					'data'       => $location[ 'data' ]
				] );
			}
			else
			{
				DB::DB()->update( DB::table( 'account_nodes' ), [
					'name'       => $location[ 'name' ],
					'category'   => $location[ 'category' ],
					'driver'     => 'google_b',
					'data'       => $location[ 'data' ]
				], [
					'account_id' => $accountId,
					'node_id'    => $location[ 'id' ]
				] );
			}

			unset( $my_nodes[ array_search( $location[ 'id' ], $my_nodes ) ] );
		}

		if ( ! empty( $my_nodes ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $my_nodes ) ) . ')' );
		}

		return [ 'status' => TRUE ];
	}
}
