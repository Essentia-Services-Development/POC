<?php

namespace FSPoster\App\Libraries\discord;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\Session;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\SocialNetwork;

class Discord extends SocialNetwork
{
	public static function callbackURL ()
	{
		return site_url() . '/?discord_callback=1';
	}

	public static function getLoginURL ( $appId )
	{
		$proxy = Request::get( 'proxy', '', 'string' );

		$appInfo = DB::DB()->get_row( DB::DB()->prepare( 'select app_id from `' . DB::table( 'apps' ) . '` where id=%d and driver = "discord"', $appId ), ARRAY_A );

		if ( empty( $appInfo ) )
		{
			self::error( fsp__( 'Error! The App not found!' ) );
		}

		$url    = 'https://discord.com/api/oauth2/authorize';
		$params = [
			'client_id'     => $appInfo[ 'app_id' ],
			'permissions'   => 51200,
			'redirect_uri'  => self::callbackURL(),
			'response_type' => 'code',
			'scope'         => 'bot identify',
			'prompt'        => 'none',
		];

		Session::set( 'discord_app_id', $appId );
		Session::set( 'discord_proxy', $proxy );

		return $url . '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
	}

	public static function addServer ()
	{
		$guildID     = Request::get( 'guild_id', 0, 'int' );
		$permissions = Request::get( 'permissions', 0, 'int', [ 51200 ] );
		$appID       = Session::get( 'discord_app_id' );
		$proxy       = Session::get( 'discord_proxy' );

		if ( $permissions === 0 )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'Required permission not given!' )
			];
		}

		if ( empty( $appID ) || ! is_numeric( $appID ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'App not found!' )
			];
		}

		if ( empty( $guildID ) || ! is_numeric( $guildID ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'Server not found!' )
			];
		}

		$appInfo = DB::DB()->get_row( DB::DB()->prepare( 'select data from `' . DB::table( 'apps' ) . '` where id=%s', $appID ), ARRAY_A );

		if ( empty( $appInfo ) || empty( $appInfo[ 'data' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'App not found!' )
			];
		}

		$tokenInfo = json_decode( $appInfo[ 'data' ], TRUE );

		if ( empty( $tokenInfo ) || empty( $tokenInfo[ 'bot_token' ] ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'Server not found!' )
			];
		}

		$data = Discord::getGuild( $guildID, $tokenInfo[ 'bot_token' ], $proxy );

		if ( isset( $data[ 'status' ] ) && $data[ 'status' ] === 'error' )
		{
			return [
				'status'    => FALSE,
				'error_msg' => isset( $data[ 'error_msg' ] ) ? esc_html( $data[ 'error_msg' ] ) : fsp__( 'Couldn\'t add the server!' )
			];
		}

		$existingGuildInfo = DB::DB()->get_row( DB::DB()->prepare( 'SELECT profile_id, id FROM ' . DB::table( 'accounts' ) . ' WHERE driver=%s and user_id = %d and blog_id=%d and profile_id = %d', [
			'discord',
			get_current_user_id(),
			Helper::getBlogId(),
			$guildID
		] ), ARRAY_A );

		if ( ! empty( $existingGuildInfo ) )
		{
			DB::DB()->update( DB::table( 'accounts' ), [
				'status'      => NULL,
				'error_msg'   => NULL,
				'name'        => $data[ 'name' ],
				'profile_pic' => $data[ 'profile_pic' ],
				'proxy'       => empty( $proxy ) ? NULL : $proxy,
				'options'     => json_encode( [ 'bot_token' => $tokenInfo[ 'bot_token' ] ] ),
			], [ 'id' => $existingGuildInfo[ 'id' ] ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'accounts' ), [
				'name'        => $data[ 'name' ],
				'driver'      => 'discord',
				'options'     => json_encode( [ 'bot_token' => $tokenInfo[ 'bot_token' ] ] ),
				'user_id'     => get_current_user_id(),
				'blog_id'     => Helper::getBlogId(),
				'profile_id'  => $guildID,
				'profile_pic' => $data[ 'profile_pic' ],
				'proxy'       => empty( $proxy ) ? NULL : $proxy
			] );
		}

		return [
			'status'    => TRUE,
			'error_msg' => NULL
		];
	}

	public static function post ( $message, $images, $link, $type, $channelId, $botToken, $proxy )
	{
        if ( $type === 'link' && ! empty( $link ) )
        {
            $message = sprintf( "%s\n%s", $message, $link );
        }

		$options = [];

		if ( $type === 'image' || $type === 'video' )
		{
			if ( empty( $images ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => fsp__( 'No medias found!' ),
				];
			}

			if ( ! empty( $message ) )
			{
				$options[ 'multipart' ][] = [
					'name'     => 'payload_json',
					'contents' => json_encode( [ 'content' => $message ] ),
				];
			}

			foreach ( $images as $image )
			{
				$name                     = md5( mt_rand( 1000, 9999 ) . microtime() );
				$explode                  = explode( '/', $image );
				$options[ 'multipart' ][] = [
					'name'     => sprintf( "files[%s]", $name ),
					'contents' => file_get_contents( $image ),
					'filename' => end( $explode ),
				];
			}
		}
		else if ( ! empty( $message ) )
		{
			$options = [
				'json' => [
					'content' => $message,
				],
				//				'headers' => [ 'Content-Type' => 'application/json' ]
			];
		}
		else
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( "Cannot send an empty message!" ),
			];
		}

		$endpoint = sprintf( "channels/%s/messages", $channelId );

		$result = self::cmd( 'POST', $endpoint, $botToken, $proxy, $options );

		if ( ! isset( $result[ 'id' ] ) )
		{
			$errorMsg = '';

			if ( isset( $result[ 'error_msg' ] ) )
			{
				$errorMsg = $result[ 'error_msg' ];
			}
			else if ( isset( $result[ 'message' ] ) )
			{
				$errorMsg = $result[ 'message' ];
			}
			else if ( isset( $result[ 'content' ] ) && is_array( $result[ 'content' ] ) )
			{
				$errorMsg = reset( $result[ 'content' ] );
			}
			else if ( isset( $result[ '_misc' ] ) && is_array( $result[ '_misc' ] ) )
			{
				$errorMsg = reset( $result[ '_misc' ] );
			}

			return [
				'status'    => 'error',
				'error_msg' => ! empty( $errorMsg ) ? esc_html( $errorMsg ) : fsp__( 'Error!!' ),
			];
		}

		return [
			'status' => 'ok',
			'id'     => $result[ 'id' ],
		];
	}

	public static function cmd ( $method, $endpoint, $botToken, $proxy = '', $options = [] )
	{
		$url = sprintf( 'https://discord.com/api/%s', $endpoint );

		if ( ! empty( $proxy ) )
		{
			$options[ 'proxy' ] = $proxy;
		}

		$options[ 'headers' ][ 'Authorization' ] = sprintf( 'Bot %s', $botToken );

		try
		{
			$response = ( new Client() )->request( $method, $url, $options )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			if ( method_exists( $e, 'getResponse' ) )
			{
				$response = $e->getResponse();

				if ( !method_exists( $response, 'getBody' ) )
				{
					return [
						'status'    => FALSE,
						'error_msg' => $e->getMessage()
					];
				}

				$response = $response->getBody()->getContents();
			}
			else
			{
				return [
					'status'    => FALSE,
					'error_msg' => $e->getMessage()
				];
			}
		}

		$responseArray = json_decode( $response, TRUE );

		if ( ! $responseArray )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Request error!' ),
			];
		}

		if ( isset( $responseArray[ 'error' ] ) )
		{
			$errorMsg = fsp__( 'Error!' );

			if ( isset( $responseArray[ 'error' ][ 'message' ] ) )
			{
				$errorMsg = $responseArray[ 'error' ][ 'message' ];
			}
			else if ( $responseArray[ 'error_description' ] )
			{
				$errorMsg = $responseArray[ 'error_description' ];
			}

			return [
				'status'    => 'error',
				'error_msg' => $errorMsg,
			];
		}

		return $responseArray;
	}

	public static function getGuild ( $id, $botToken, $proxy )
	{
		$endpoint = sprintf( 'guilds/%d', $id );
		$guild    = self::cmd( 'GET', $endpoint, $botToken, $proxy );

		if ( empty( $guild ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error!' ),
			];
		}

		if ( isset( $guild[ 'status' ] ) && $guild[ 'status' ] === 'error' )
		{
			return $guild;
		}

		if ( ! isset( $guild[ 'id' ] ) || ! isset( $guild[ 'name' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => isset( $guild[ 'message' ] ) ? esc_html( $guild[ 'message' ] ) : fsp__( 'Error!' )
			];
		}

		return [
			'name'        => $guild[ 'name' ],
			'profile_pic' => sprintf( 'https://cdn.discordapp.com/icons/%s/%s.png', $guild[ 'id' ], $guild[ 'icon' ] ),
		];
	}

	public static function getGuildChannels ( $accountInfo )
	{
		$options = json_decode( $accountInfo[ 'options' ], TRUE );

		if ( empty( $options[ 'bot_token' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Bot token is not present!' ),
			];
		}

		$endpoint = sprintf( 'guilds/%s/channels', $accountInfo[ 'profile_id' ] );
		$channels = self::cmd( 'GET', $endpoint, $options[ 'bot_token' ], $accountInfo[ 'proxy' ] );

		if ( empty( $channels ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error!' ),
			];
		}

		if ( isset( $channels[ 'status' ] ) && $channels[ 'status' ] === 'error' )
		{
			return $channels;
		}

		$checkNodes    = DB::DB()->get_results( DB::DB()->prepare( 'select node_id from `' . DB::table( 'account_nodes' ) . '` where account_id=%d', $accountInfo[ 'id' ] ), ARRAY_A );
		$existingNodes = [];
		$nodes         = [];

		if ( ! empty( $checkNodes ) )
		{
			foreach ( $checkNodes as $node )
			{
				$existingNodes[] = $node[ 'node_id' ];
			}
		}

		foreach ( $channels as $channel )
		{
			if ( empty( $channel ) || ! isset( $channel[ 'type' ] ) || ($channel[ 'type' ] != '0' && $channel[ 'type' ] != '5') )
			{
				continue;
			}

			if ( in_array( $channel[ 'id' ], $existingNodes ) )
			{
				continue;
			}

			$nodes[] = [
				'id'   => $channel[ 'id' ],
				'name' => $channel[ 'name' ],
			];
		}

		return $nodes;
	}

	public static function refetchAccount ( $accountId, $guildId, $botToken, $proxy )
	{
		$endpoint = sprintf( 'guilds/%s', $guildId );
		$guild    = self::cmd( 'GET', $endpoint, $botToken, $proxy );

		if ( empty( $guild ) )
		{
			return [
				'status'    => FALSE,
				'error_msg' => fsp__( 'Error!' ),
			];
		}

		if ( isset( $guild[ 'status' ] ) && $guild[ 'status' ] === 'error' )
		{
			return [
				'status'    => FALSE,
				'error_msg' => $guild[ 'error_msg' ]
			];
		}

		if ( ! isset( $guild[ 'id' ] ) )
		{
			DB::DB()->update( DB::table( 'accounts' ), [
				'status'    => 'error',
				'error_msg' => isset( $guild[ 'message' ] ) ? $guild[ 'message' ] : fsp__( 'The Bot has lost connection to the server' ),
			], [ 'id' => $accountId ] );

			return [
				'status'    => FALSE,
				'error_msg' => isset( $guild[ 'message' ] ) ? $guild[ 'message' ] : fsp__( 'Re-fetch failed. The Bot has lost connection to the server' ),
			];
		}

		DB::DB()->update( DB::table( 'accounts' ), [
			'name'        => $guild[ 'name' ],
			'profile_pic' => sprintf( 'https://cdn.discordapp.com/icons/%s/%s.png', $guild[ 'id' ], $guild[ 'icon' ] ),
		], [ 'id' => $accountId ] );

		$endpoint = sprintf( 'guilds/%s/channels', $guildId );
		$channels = self::cmd( 'GET', $endpoint, $botToken, $proxy );

		if ( isset( $channels[ 'status' ] ) && $channels[ 'status' ] === 'error' )
		{
			return [
				'status'    => FALSE,
				'error_msg' => $channels[ 'error_msg' ]
			];
		}

		$getNodes = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $accountId ] ), ARRAY_A );
		$myNodes  = [];

		foreach ( $getNodes as $node )
		{
			$myNodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
		}

		foreach ( $channels as $channel )
		{
			if ( empty( $channel ) || ! isset( $channel[ 'type' ] ) || $channel[ 'type' ] != '0' )
			{
				continue;
			}

			if ( in_array( $channel[ 'id' ], $myNodes ) )
			{
				DB::DB()->update( DB::table( 'account_nodes' ), [ 'name' => $channel[ 'name' ] ], [
					'account_id' => $accountId,
					'node_type'  => 'channel',
					'node_id'    => $channel[ 'id' ],
				] );
			}

			unset( $myNodes[ array_search( $channel[ 'id' ], $myNodes ) ] );
		}

		if ( ! empty( $myNodes ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $myNodes ) ) . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $myNodes ) ) . ')' );
		}

		return [
			'status' => 'success',
		];
	}
}