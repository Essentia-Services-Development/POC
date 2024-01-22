<?php

namespace FSPoster\App\Providers;

use Exception;
use FSP_GuzzleHttp\Client;

trait URLHelper
{
	/**
	 * @param $url
	 *
	 * @return string
	 */
	public static function shortenerURL ( $url, $node_type, $node_id )
	{
		if ( ! Helper::getCustomSetting( 'url_shortener', '0', $node_type, $node_id ) )
		{
			return $url;
		}

		$shortener_service = Helper::getCustomSetting( 'shortener_service', '', $node_type, $node_id );

		switch ( $shortener_service )
		{
			case 'bitly':
				return self::shortURLbitly( $url, $node_type, $node_id );
			case 'tinyurl':
				return self::shortURLtinyurl( $url );
			case 'yourls':
				return self::shortURLyourls( $url, $node_type, $node_id );
			case 'polr':
				return self::shortURLpolr( $url, $node_type, $node_id );
			case 'shlink':
				return self::shortURLshlink( $url, $node_type, $node_id );
			case 'rebrandly':
				return self::shortURLrebrandly( $url, $node_type, $node_id );
			default :
				return $url;
		}
	}

	/**
	 * @param $url
	 *
	 * @return string
	 */
	public static function shortURLtinyurl ( $url )
	{
		if ( empty( $url ) )
		{
			return $url;
		}

		$shortenURL = Curl::getURL( 'https://tinyurl.com/api-create.php?url=' . urlencode( $url ) );

		return filter_var( $shortenURL, FILTER_VALIDATE_URL ) ? $shortenURL : $url;
	}

	/**
	 * @param $url
	 *
	 * @return string
	 */
	public static function shortURLbitly ( $url, $nodeType, $nodeId )
	{
		$accessToken = Helper::getCustomSetting( 'url_short_access_token_bitly', '', $nodeType, $nodeId );

		if ( empty( $url ) || empty( $accessToken ) )
		{
			return $url;
		}

		$c = new Client();

		try
		{
			$response = $c->post( 'https://api-ssl.bit.ly/v4/shorten', [
				'body'    => json_encode( [ 'long_url' => $url ] ),
				'headers' => [
					'Authorization' => 'Bearer ' . $accessToken,
					'Content-Type'  => 'application/json'
				]
			] )->getBody()->getContents();

			$response = json_decode( $response, TRUE );

			return empty( $response[ 'link' ] ) ? $url : $response[ 'link' ];
		}
		catch ( Exception $e )
		{
			return $url;
		}
	}

	public static function shortURLyourls ( $url, $nodeType, $nodeId )
	{
		$secretToken = trim( Helper::getCustomSetting( 'url_short_api_token_yourls', '', $nodeType, $nodeId ) );
		$requestUrl  = trim( Helper::getCustomSetting( 'url_short_api_url_yourls', '', $nodeType, $nodeId ) );

		if ( empty( $url ) || empty( $secretToken ) || empty( $requestUrl ) )
		{
			return $url;
		}

		$client = new Client();

		try
		{
			$response = $client->post( $requestUrl, [
				'query' => [
					'signature' => $secretToken,
					'action'    => 'shorturl',
					'format'    => 'json',
					'url'       => $url
				]
			] );
		}
		catch ( Exception $e )
		{
			if ( ! method_exists( $e, 'getResponse' ) )
			{
				return $url;
			}

			$response = $e->getResponse();

			if ( is_null( $response ) )
			{
				return $url;
			}
		}

		$response = json_decode( $response->getBody()->getContents(), TRUE );

		return empty( $response[ 'shorturl' ] ) ? $url : $response[ 'shorturl' ];
	}

	public static function shortURLpolr ( $url, $nodeType, $nodeId )
	{
		$apiKey     = trim( Helper::getCustomSetting( 'url_short_api_key_polr', '', $nodeType, $nodeId ) );
		$requestUrl = trim( Helper::getCustomSetting( 'url_short_api_url_polr', '', $nodeType, $nodeId ) );

		if ( empty( $url ) || empty( $apiKey ) || empty( $requestUrl ) )
		{
			return $url;
		}

		$client = new Client();

		try
		{
			$response = $client->post( trim( $requestUrl, '/' ) . '/action/shorten', [
				'query' => [
					'key'           => $apiKey,
					'is_secret'     => FALSE,
					'response_type' => 'json',
					'url'           => $url
				]
			] );
		}
		catch ( Exception $e )
		{
			if ( ! method_exists( $e, 'getResponse' ) )
			{
				return $url;
			}

			$response = $e->getResponse();

			if ( is_null( $response ) )
			{
				return $url;
			}
		}

		$response = json_decode( $response->getBody()->getContents(), TRUE );

		return empty( $response[ 'result' ] ) ? $url : $response[ 'result' ];
	}

	public static function shortURLshlink ( $url, $nodeType, $nodeId )
	{
		$apiKey     = Helper::getCustomSetting( 'url_short_api_key_shlink', '', $nodeType, $nodeId );
		$requestUrl = Helper::getCustomSetting( 'url_short_api_url_shlink', '', $nodeType, $nodeId );

		if ( empty( $url ) || empty( $apiKey ) || empty( $requestUrl ) )
		{
			return $url;
		}

		$client = new Client();

		try
		{
			$response = $client->post( trim( $requestUrl, '/' ) . '/short-urls', [
				'body'    => json_encode( [
					'longUrl'      => $url,
					'validateUrl'  => FALSE,
					'findIfExists' => TRUE
				] ),
				'headers' => [
					'X-Api-Key' => $apiKey
				]
			] );
		}
		catch ( Exception $e )
		{
			if ( ! method_exists( $e, 'getResponse' ) )
			{
				return $url;
			}

			$response = $e->getResponse();

			if ( is_null( $response ) )
			{
				return $url;
			}
		}

		$response = json_decode( $response->getBody()->getContents(), TRUE );

		return empty( $response[ 'shortUrl' ] ) ? $url : $response[ 'shortUrl' ];
	}

	public static function shortURLrebrandly ( $url, $nodeType, $nodeId )
	{
		$apiKey = Helper::getCustomSetting( 'url_short_api_key_rebrandly', '', $nodeType, $nodeId );
		$domain = Helper::getCustomSetting( 'url_short_domain_rebrandly', '', $nodeType, $nodeId );

		if ( empty( $url ) || empty( $apiKey ) || empty( $domain ) )
		{
			return $url;
		}

		$client = new Client();

		try
		{
			$response = $client->post( 'https://api.rebrandly.com/v1/links', [
				'body'    => json_encode( [
					'destination' => $url,
					'domain'      => [
						'fullName' => $domain
					]
				] ),
				'headers' => [
					'Content-Type' => 'application/json',
					'apikey'       => $apiKey
				]
			] );
		}
		catch ( Exception $e )
		{
			if ( ! method_exists( $e, 'getResponse' ) )
			{
				return $url;
			}

			$response = $e->getResponse();

			if ( is_null( $response ) )
			{
				return $url;
			}
		}

		$response = json_decode( $response->getBody()->getContents(), TRUE );

		return empty( $response[ 'shortUrl' ] ) ? $url : ( 'https://' . $response[ 'shortUrl' ] );
	}

	/**
	 * @param $post_id
	 * @param $driver
	 * @param string $username
	 *
	 * @return string
	 */
	public static function postLink ( $post_id, $driver, $username = '' )
	{
		if ( $driver === 'fb' )
		{
			return 'https://fb.com/' . $post_id;
		}
		else if ( $driver === 'instagram' )
		{
			return 'https://www.instagram.com/p/' . $post_id . '/';
		}
		else if ( $driver === 'instagramstory' )
		{
			return 'https://www.instagram.com/stories/' . $username . '/';
		}
        else if ( $driver === 'threads' )
        {
            return 'https://threads.net/t/' . $post_id;
        }
		else if ( $driver === 'twitter' )
		{
			return 'https://twitter.com/' . $username . '/status/' . $post_id;
		}
		else if ( $driver === 'planly' )
		{
			return 'https://app.planly.com/calendar/schedules/' . $post_id;
		}
		else if ( $driver === 'linkedin' )
		{
			return 'https://www.linkedin.com/feed/update/' . $post_id . '/';
		}
		else if ( $driver === 'pinterest' )
		{
			return 'https://www.pinterest.com/pin/' . $post_id;
		}
		else if ( $driver === 'telegram' )
		{
			return "http://t.me/" . esc_html( $username );
		}
		else if ( $driver === 'reddit' )
		{
			return 'https://www.reddit.com/' . $post_id;
		}
		else if ( $driver === 'youtube_community' )
		{
			return sprintf( "https://www.youtube.com/post/%s", $post_id );
		}
		else if ( $driver === 'tumblr' )
		{
			return 'https://' . $username . '.tumblr.com/post/' . $post_id;
		}
		else if ( $driver === 'ok' )
		{
			if ( strpos( $post_id, 'topic' ) !== FALSE )
			{
				return 'https://ok.ru/group/' . $post_id;
			}
			else
			{
				return 'https://ok.ru/profile/' . $post_id;
			}
		}
		else if ( $driver === 'vk' )
		{
			return 'https://vk.com/wall' . $post_id;
		}
		else if ( $driver === 'google_b' )
		{
			if ( ! empty( $username ) )
			{
				return 'https://business.google.com/n/' . $username . '/profile';
			}

			return 'https://local.google.com/place?use=posts&lspid=' . $post_id;
		}
		else if ( $driver === 'medium' )
		{
			return "https://medium.com/p/" . esc_html( $post_id );
		}
		else if ( $driver === 'wordpress' )
		{
			return rtrim( $username, '/' ) . '/?p=' . $post_id;
		}
		else if ( $driver === 'webhook' )
		{
			return admin_url( 'admin.php?page=fs-poster-logs&webhook_feed_id=' . $post_id );
		}
		else if ( $driver === 'blogger' )
		{
			return $username;
		}
		else if ( $driver === 'plurk' )
		{
			return 'https://plurk.com/p/' . base_convert( $post_id, 10, 36 );
		}
		else if ( $driver === 'xing' )
		{
			$post_id = explode( '.', $post_id )[ 0 ];

			if ( is_numeric( $post_id ) )
			{
				return 'https://www.xing.com/home/stories/' . $post_id;
			}

			return 'https://www.xing.com' . $post_id;
		}
		else if ( $driver === 'discord' )
		{
			return $post_id;
		}
		else if ( $driver === 'mastodon' )
		{
			return $username . '/' . $post_id;
		}
	}
}
