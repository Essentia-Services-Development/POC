<?php

namespace FSPoster\App\Libraries\webhook;

use Exception;
use FSP_GuzzleHttp\Client;
use FSP_GuzzleHttp\Exception\GuzzleException;

class Webhook
{
	public static function sendPost ( $sendData )
	{
		$options = [];

		if ( ! empty( $sendData[ 'headers' ] ) )
		{
			$options[ 'headers' ] = $sendData[ 'headers' ];
		}

		if ( ! empty( $sendData[ 'proxy' ] ) )
		{
			$options[ 'proxy' ] = $sendData[ 'proxy' ];
		}

		if ( strtoupper( $sendData[ 'method' ] ) === 'POST' || strtoupper( $sendData[ 'method' ] ) === 'PUT' )
		{
			if ( ! empty( $sendData[ 'form_data' ] ) && $sendData[ 'post_content' ] === 'form' )
			{
				$options[ 'form_params' ] = $sendData[ 'form_data' ];
			}
			else if ( ! empty( $sendData[ 'json' ] ) && $sendData[ 'post_content' ] === 'json' )
			{
				$options[ 'body' ] = json_encode( $sendData[ 'json' ] );
			}
		}

		try
		{
			$client   = new Client();
			$response = $client->request( strtoupper( $sendData[ 'method' ] ), $sendData[ 'url' ], $options );
		}
		catch ( Exception $e )
		{
			if ( method_exists( $e, 'getResponse' ) )
			{
				$response = $e->getResponse();

				if ( is_null( $response ) )
				{
					return [
						'status'    => 'error',
						'error_msg' => $e->getMessage()
					];
				}
			}
			else
			{
				return [
					'status'    => 'error',
					'error_msg' => $e->getMessage()
				];
			}
		}

		$statusCode      = $response->getStatusCode();
		$responseContent = $response->getBody()->getContents();

		if ( $statusCode >= 200 && $statusCode <= 299 )
		{
			$status   = 'ok';
			$response = 'response';
		}
		else
		{
			$status   = 'error';
			$response = 'error_msg';
		}

		if ( is_null( json_decode( $responseContent ) ) )
		{
			$responseContent = htmlspecialchars( $responseContent );
		}

		return [
			'status'  => $status,
			$response => $responseContent
		];
	}
}