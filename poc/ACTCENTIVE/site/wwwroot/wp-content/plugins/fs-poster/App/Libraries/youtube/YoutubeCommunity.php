<?php

namespace FSPoster\App\Libraries\youtube;

use Exception;
use FSP_GuzzleHttp\Client;
use FSP_GuzzleHttp\Cookie\CookieJar;
use FSP_GuzzleHttp\Exception\GuzzleException;

class YoutubeCommunity
{
	/**
	 * @var Client
	 */
	private $client;
	/**
	 * @var CookieJar
	 */
	private $cookies;
	private $proxy;
	private $origin  = "https://www.youtube.com";
	private $channelId;
	private $ytCfg = [];
	private $referer = "https://www.youtube.com";

	public function __construct ( array $cookies, $proxy = NULL )
	{
		$this->cookies = new CookieJar( FALSE, $cookies );
		$this->proxy   = $proxy;

		$this->setClient();
	}

	public function channel ()
	{
		$name  = "";
		$image = "";

		if ( ! $error = $this->init() )
		{
			$response = $this->cmd( "GET", sprintf( 'channel/%s/', $this->channelId ), "", [
				"Referer" => $this->referer
			] );

			if ( is_array( $response ) )
			{
				$error = $response[ "error_msg" ];
			}
			else
			{
				$response = $response->getBody();

				preg_match( '/<meta property=\"og:image\" content=\"(.+?)\">/', $response, $matchedImage );
				preg_match( '/<meta property=\"og:title\" content=\"(.+?)\">/', $response, $matchedName );

				if ( ! empty( $matchedImage ) )
				{
					$image = $matchedImage[ 1 ];
				}

				if ( ! empty( $matchedName ) )
				{
					$name = $matchedName[ 1 ];
				}

				if ( empty( $image ) || empty( $name ) )
				{
					$error = fsp__( "Couldn't fetch the channel" );
				}
			}
		}

		return ! empty( $error ) ? [
			"status"    => FALSE,
			"error_msg" => $error
		] : [
			"status"  => TRUE,
			"channel" => [
				"name"    => $name,
				"image"   => $image,
				"id"      => $this->channelId,
				"cookies" => $this->cookies->toArray(),
			]
		];
	}

	public function checkAccount ()
	{
		$channel = $this->channel();

		return [
			"error" => ! $channel[ "status" ],
			"error_msg" => isset( $channel[ "error_msg" ] ) ? $channel[ "error_msg" ] : ""
		];
	}

	public function post ( $message, $type, $images )
	{
		$id = "";

		if ( ! $error = $this->init() )
		{
			$this->referer = sprintf( "https://www.youtube.com/channel/%s", $this->channelId );

			$backstage = $this->backStageParams();

			if ( $backstage[ "status" ] )
			{
				$this->referer = sprintf( "%s/community", $this->referer );

				$body = [
					"context"                   => $this->ytCfg[ "context" ],
					"createBackstagePostParams" => $backstage[ "params" ],
					"commentText"               => $message
				];

				if ( $type === 'image' && ! empty( $images ) )
				{
					for ( $i = 0; $i < count( $images ); $i++ )
					{
						$response = $this->upload( $images[ $i ] );

						if ( $response[ "status" ] )
						{
							$body[ "imagesAttachment" ][ "imagesData" ][ $i ] = $response[ 'image' ];
						}
						else
						{
							$error = $response[ "error_msg" ];
							break;
						}
					}
				}

				$time     = time();
				$apiSid   = $this->cookies->getCookieByName( "__Secure-3PAPISID" )->getValue();
				$hash     = sha1( sprintf( "%d %s %s", $time, $apiSid, $this->origin ) );
				$endpoint = sprintf( "youtubei/v1/backstage/create_post?key=%s&prettyPrint=false", $this->ytCfg[ "apiKey" ] );

				$response = $this->cmd( "POST", $endpoint, json_encode( $body ), [
					"X-Origin"                      => $this->origin,
					"X-Youtube-Bootstrap-Logged-In" => "true",
					"Authorization"                 => sprintf( "SAPISIDHASH %s_%s", $time, $hash ),
					"Content-Type"                  => "application/json",
					"X-Youtube-Client-Name"         => 1,
					"X-Youtube-Client-Version"      => $this->ytCfg[ "context" ][ "client" ][ "clientVersion" ],
					"X-Goog-AuthUser"               => 0,
					"X-Goog-PageId"                 => $this->ytCfg[ "pageId" ],
					"X-Goog-Visitor-Id"             => $this->ytCfg[ "context" ][ "client" ][ "visitorData" ],
					"Accept"                        => "*/*",
					"Sec-GPC"                       => 1,
					"Accept-Language"               => "en-US,en;q=0.5"
				] );

				if ( is_array( $response ) )
				{
					$error = $response[ "error_msg" ];
				}
				else
				{
					preg_match( '/\"postId\":\"(.+?)\"/', $response->getBody(), $matchedId );

					if ( ! empty( $matchedId ) )
					{
						$id = $matchedId[ 1 ];
					}
					else
					{
						$error = fsp__( "Error!" );
					}
				}
			}
			else
			{
				$error = $backstage[ "error_msg" ];
			}
		}

		return ! empty( $error ) ? [
			"status"    => FALSE,
			"error_msg" => $error
		] : [
			"status" => "ok",
			"id"     => $id
		];
	}

	private function upload ( $url )
	{
		$image = [];
		$raw   = file_get_contents( $url );

		$response = $this->uploadUrl( strlen( $raw ) );

		if ( $response[ "status" ] )
		{
			$response = $this->cmd( "POST", $response[ "url" ], $raw, [
				"X-Goog-Upload-Command" => "upload, finalize",
				"X-Goog-Upload-Offset"  => "0",
				"X-YouTube-ChannelId"   => $this->channelId,
				"Content-Type"          => "application/x-www-form-urlencoded;charset=utf-8"
			] );

			if ( is_array( $response ) )
			{
				$error = $response[ "error_msg" ];
			}
			else
			{
				$response = json_decode( $response->getBody(), TRUE );

				if ( empty( $response ) || empty( $response[ "encryptedBlobId" ] ) )
				{
					$error = fsp__( "Couldn't upload the image!" );
				}
				else
				{
					list( $width, $height ) = getimagesize( $url ); //contains images width and height

					if ( ! $width || ! $height )
					{
						$error = fsp__( "Couldn't resize the image!" );
					}
					else
					{
						if ( $width > $height )
						{
							$top  = 0;
							$left = ( $width - $height ) / ( 2 * $width );
						}
						else
						{
							$left = 0;
							$top  = ( $height - $width ) / ( 2 * $height );
						}

						$image = [
							"encryptedBlobId"    => $response[ "encryptedBlobId" ],
							"previewCoordinates" => [
								"top"    => $top,
								"right"  => 1 - $left,
								"bottom" => 1 - $top,
								"left"   => $left
							]
						];
					}
				}
			}
		}
		else
		{
			$error = $response[ "error_msg" ];
		}

		return ! empty( $error ) ? [
			"status"    => FALSE,
			"error_msg" => $error
		] : [
			"status" => TRUE,
			"image"  => $image
		];
	}

	private function uploadUrl ( $length )
	{
		$uploadUrl = "";

		$url = $this->cmd( "POST", "channel_image_upload/posts", "", [
			"X-YouTube-ChannelId"                 => $this->channelId,
			"X-Goog-Upload-Protocol"              => "resumable",
			"X-Goog-Upload-Header-Content-Length" => $length,
			"X-Goog-Upload-Command"               => "start",
			"Content-Type"                        => "application/x-www-form-urlencoded;charset=UTF-8"
		] );

		if ( is_array( $url ) )
		{
			$error = $url[ "error_msg" ];
		}
		else
		{
			$uploadUrl = $url->getHeader( "X-Goog-Upload-URL" );

			if ( empty( $uploadUrl ) )
			{
				$error = fsp__( "Error!" );
			}
			else
			{
				$uploadUrl = str_replace( "https://www.youtube.com/", "", $uploadUrl[ 0 ] );
			}
		}

		return ! empty( $error ) ? [
			"status"    => FALSE,
			"error_msg" => $error
		] : [
			"status" => TRUE,
			"url"    => $uploadUrl
		];
	}

	private function setClient ()
	{
		$this->client = new Client( [
			"cookies"     => $this->cookies,
			"proxy"       => empty( $this->proxy ) ? NULL : $this->proxy,
			"verify"      => FALSE,
			"http_errors" => FALSE,
			"headers"     => [
				"User-Agent"                => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36,gzip(gfe)",
				"Host"                      => "www.youtube.com",
				"Connection"                => "keep-alive",
				"Cache-Control"             => "max-age=0",
				"DNT"                       => 1,
				"Upgrade-Insecure-Requests" => 1,
			]
		] );
	}

	private function cmd ( $method = "GET", $endpoint = "", $body = "", $headers = [] )
	{
		$headers[ "Referer" ] = $this->referer;

		try
		{
			$response = $this->client->request( $method, sprintf( "https://www.youtube.com/%s", $endpoint ), [
				"headers" => $headers,
				"body"    => $body
			] );
		}
		catch ( GuzzleException $e )
		{
			return [
				"status"    => FALSE,
				"error_msg" => fsp__( "Error! %s", [ $e->getMessage() ] )
			];
		}
		catch ( Exception $e )
		{
			if ( method_exists( $e, 'getResponse' ) )
			{
				$response = $e->getResponse();

				if ( is_null( $response ) )
				{
					return [
						'status'    => FALSE,
						'error_msg' => $e->getMessage()
					];
				}
			}
			else
			{
				return [
					'status'    => FALSE,
					'error_msg' => $e->getMessage()
				];
			}
		}

		return $response;
	}

	private function init ()
	{
		$error       = "";
		$response    = $this->cmd(); //fetches homepage

		if ( is_array( $response ) )
		{
			$error = $response[ "error_msg" ];
		}
		else
		{
			$response = $response->getBody();

			preg_match( '/window\.ytplayer=\{};\nytcfg\.set\((.*?)\);/', $response, $matchedConfig );

			if ( ! empty( $matchedConfig ) )
			{
				$config = json_decode( $matchedConfig[ 1 ], TRUE );

				if ( ! empty( $config ) )
				{
					if ( ! empty( $config[ "DELEGATED_SESSION_ID" ] ) )
					{
						$this->ytCfg[ "pageId" ] = $config[ "DELEGATED_SESSION_ID" ];
					}
					else
					{
						$this->ytCfg[ "pageId" ] = NULL;
					}

					$this->ytCfg[ "apiKey" ]  = $config[ "INNERTUBE_API_KEY" ];
					$this->ytCfg[ "context" ] = $config[ "INNERTUBE_CONTEXT" ];
				}
			}

			preg_match( '/\/channel\/([A-Za-z0-9_-]+)\/community/', $response, $matchedChannel );

			if ( ! empty( $matchedChannel ) )
			{
				$this->channelId = $matchedChannel[ 1 ];
			}
		}

		if ( empty( $this->ytCfg ) || empty( $this->ytCfg[ "apiKey" ] ) )
		{
			$error = fsp__( "Couldn't fetch the state!" );
		}
		else if ( empty( $this->channelId ) )
		{
			$error = fsp__( "Your channel doesn't meet all eligibility requirements to access the Community posts. <a href='https://support.google.com/youtube/answer/9409631?hl=en' target='_blank'>Why?</a>", [], FALSE );
		}

		return $error;
	}

	private function backStageParams ()
	{
		$params   = "";
		$response = $this->cmd( "GET", sprintf( "channel/%s/community", $this->channelId ) );

		if ( is_array( $response ) )
		{
			$error = $response[ "error_msg" ];
		}
		else
		{
			preg_match( '/\"createBackstagePostParams\":\"(.+?)\"/', $response->getBody(), $matchedBackstage );

			if ( $matchedBackstage )
			{
				$params = $matchedBackstage[ 1 ];
			}
			else
			{
				$error = fsp__( "Couldn't fetch the state" );
			}
		}

		return ! empty( $error ) ? [
			"status"    => FALSE,
			"error_msg" => $error
		] : [
			"status" => TRUE,
			"params" => $params
		];
	}
}