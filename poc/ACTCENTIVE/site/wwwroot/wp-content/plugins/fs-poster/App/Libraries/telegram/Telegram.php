<?php

namespace FSPoster\App\Libraries\telegram;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\Helper;
use FSP_GuzzleHttp\Exception\GuzzleException;

class Telegram
{
	private $token;
	private $client;

	public function __construct ( $botToken, $proxy = '' )
	{
		$this->token = $botToken;

		$this->client = new Client( [
			'allow_redirects' => [ 'max' => 20 ],
			'proxy'           => empty( $proxy ) ? NULL : $proxy,
			'verify'          => FALSE,
			'http_errors'     => FALSE,
			'headers'         => [ 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0' ]
		] );
	}

	public function getBotInfo ()
	{
		$myInfo = $this->cmd( 'getMe' );

		if ( ! $myInfo[ 'ok' ] )
		{
			return [
				'id'   => 0,
				'name' => ''
			];
		}

		return [
			'id'       => isset( $myInfo[ 'result' ][ 'id' ] ) ? $myInfo[ 'result' ][ 'id' ] : '',
			'name'     => isset( $myInfo[ 'result' ][ 'first_name' ] ) ? $myInfo[ 'result' ][ 'first_name' ] : '',
			'username' => isset( $myInfo[ 'result' ][ 'username' ] ) ? $myInfo[ 'result' ][ 'username' ] : ''
		];
	}

	public function getChatInfo ( $chatId )
	{
		if ( ! is_numeric( $chatId ) && strpos( $chatId, '@' ) !== 0 )
		{
			$chatId = '@' . $chatId;
		}

		$myInfo = $this->cmd( 'getChat', [ 'chat_id' => $chatId ] );

		if ( ! $myInfo[ 'ok' ] )
		{
			return [
				'id'       => 0,
				'name'     => '',
				'username' => '',
				'type'     => ''
			];
		}

		return [
			'id'       => isset( $myInfo[ 'result' ][ 'id' ] ) ? $myInfo[ 'result' ][ 'id' ] : '',
			'name'     => isset( $myInfo[ 'result' ][ 'title' ] ) ? $myInfo[ 'result' ][ 'title' ] : ( isset( $myInfo[ 'result' ][ 'first_name' ] ) ? $myInfo[ 'result' ][ 'first_name' ] : '' ),
			'username' => isset( $myInfo[ 'result' ][ 'username' ] ) ? $myInfo[ 'result' ][ 'username' ] : '',
			'type'     => isset( $myInfo[ 'result' ][ 'type' ] ) ? $myInfo[ 'result' ][ 'type' ] : ''
		];
	}

	public function getActiveChats ()
	{
		$updates = $this->cmd( 'getUpdates', [ 'allowed_updates' => 'message,channel_post' ] );

		if ( ! $updates[ 'ok' ] )
		{
			return [];
		}

		$list      = [];
		$uniqChats = [];

		foreach ( $updates[ 'result' ] as $update )
		{
			if ( ! isset( $update[ 'message' ] ) && ! isset( $update[ 'my_chat_member' ] ) )
			{
				continue;
			}

			$chat = isset( $update[ 'message' ][ 'chat' ] ) ? $update[ 'message' ][ 'chat' ] : ( isset( $update[ 'my_chat_member' ][ 'chat' ] ) ? $update[ 'my_chat_member' ][ 'chat' ] : [] );

			$chatId = isset( $chat[ 'id' ] ) ? $chat[ 'id' ] : '';

			if ( empty( $chatId ) )

			{
				if ( isset( $uniqChats[ $chatId ] ) )
				{
					continue;
				}
			}

			$uniqChats[ $chatId ] = TRUE;

			if ( isset( $chat[ 'first_name' ] ) )
			{
				$name = $chat[ 'first_name' ];
			}
			else if ( isset( $chat[ 'title' ] ) )
			{
				$name = $chat[ 'title' ];
			}
			else
			{
				$name = '[unnamed]';
			}

			$list[] = [
				'id'   => $chatId,
				'name' => $name
			];
		}

		return $list;
	}

	public function sendPost ( $chatId, $text, $sendType, $media = '', $link = '' )
	{
		$sendSilent = Helper::getOption( 'telegram_silent_notifications', '0' ) == 1;

		if ( $sendType === 'image' )
		{
			if ( Helper::getOption( 'telegram_autocut_text', '1' ) == 1 && mb_strlen( $text, 'UTF-8' ) >= 1021 )
			{
				$text = mb_substr( $text, 0, 1021 ) . '...';
			}

			$post = $this->upload_photo( $chatId, $text, $media, $link, $sendSilent );
		}
		else if ( $sendType === 'video' )
		{
			if ( Helper::getOption( 'telegram_autocut_text', '1' ) == 1 && mb_strlen( $text, 'UTF-8' ) >= 1021 )
			{
				$text = mb_substr( $text, 0, 1021 ) . '...';
			}

			$data = [
				'chat_id'              => $chatId,
				'caption'              => $text,
				'parse_mode'           => 'HTML',
				'video'                => $media,
				'disable_notification' => $sendSilent
			];

			$data = $this->buildReplyMarkup( $data, $link );

			$post = $this->cmd( 'sendVideo', $data );
		}
		else
		{
			if ( Helper::getOption( 'telegram_autocut_text', '1' ) == 1 && mb_strlen( $text, 'UTF-8' ) >= 4093 )
			{
				$text = mb_substr( $text, 0, 4093 ) . '...';
			}

			$data = [
				'chat_id'              => $chatId,
				'text'                 => $text,
				'parse_mode'           => 'HTML',
				'disable_notification' => $sendSilent
			];

			$data = $this->buildReplyMarkup( $data, $link );

			$post = $this->cmd( 'sendMessage', $data );
		}

		if ( ! $post[ 'ok' ] )
		{
			return [
				'status'    => 'error',
				'error_msg' => isset( $post[ 'description' ] ) && is_string( $post[ 'description' ] ) ? esc_html( $post[ 'description' ] ) : 'Error! Can\'t send message!'
			];
		}

		return [
			'status' => 'ok',
			'id'     => isset( $post[ 'result' ][ 'message_id' ] ) ? $post[ 'result' ][ 'message_id' ] : 0
		];
	}

	private function cmd ( $method, $params = [] )
	{
		$url = 'https://api.telegram.org/bot' . $this->token . '/' . $method;

		if ( ! empty( $params ) )
		{
			$url .= '?' . http_build_query( $params );
		}

		try
		{
			$request = (string) $this->client->request( 'GET', $url )->getBody();
		}
		catch ( GuzzleException $e )
		{
			return [
				'ok'          => FALSE,
				'description' => fsp__( 'Error! %s', [ $e->getMessage() ] )
			];
		}

		$request = json_decode( $request, TRUE );

		if ( ! isset( $request[ 'ok' ] ) || ( $request[ 'ok' ] && ! isset( $request[ 'result' ] ) ) )
		{
			return [
				'ok'          => FALSE,
				'description' => fsp__( 'Unknown error!' )
			];
		}

		return $request;
	}

	private function upload_photo ( $chat_id, $text, $photo, $link, $sendSilent )
	{
		try
		{
			$data = [
				'multipart' => [
					[
						'name'     => 'photo',
						'contents' => file_get_contents( $photo ),
						'filename' => 'image',
						'headers'  => [ 'Content-Type' => 'image/jpeg' ]
					],
					[
						'name'     => 'caption',
						'contents' => $text
					],
					[
						'name'     => 'disable_notification',
						'contents' => $sendSilent
					],
					[
						'name'     => 'parse_mode',
						'contents' => 'HTML'
					],
					[
						'name'     => 'chat_id',
						'contents' => $chat_id
					]
				]
			];

			$data = $this->buildReplyMarkup( $data, $link, TRUE );

			$response = (string) $this->client->post( 'https://api.telegram.org/bot' . $this->token . '/sendPhoto', $data )->getBody();

			$response = json_decode( $response, TRUE );
		}
		catch ( Exception $e )
		{
			return [
				'ok'          => FALSE,
				'description' => fsp__( 'Error! %s', [ $e->getMessage() ] )
			];
		}

		if ( ! isset( $response[ 'ok' ] ) || ( $response[ 'ok' ] && ! isset( $response[ 'result' ] ) ) )
		{
			return [
				'ok'          => FALSE,
				'description' => fsp__( 'Unknown error!' )
			];
		}

		return $response;
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
		$myInfo = $this->cmd( 'getMe' );

		if ( ! $myInfo[ 'ok' ] )
		{
			$result[ 'error_msg' ] = $myInfo[ 'description' ];
		}
		else
		{
			$result[ 'error' ] = FALSE;
		}

		return $result;
	}

	public function refetch_account ( $account_id )
	{
		return [];
	}

	private function buildReplyMarkup ( $data, $link, $multipart = FALSE )
	{
		if ( Helper::getOption( 'telegram_use_read_more_button', '0' ) && ! empty( $link ) )
		{
			$buttonText  = Helper::getOption( 'telegram_custom_button_text' );
			$buttonText  = trim( $buttonText );
			$replyMarkup = json_encode( [
				'inline_keyboard' => [
					[
						[
							'text' => ! empty( $buttonText ) ? $buttonText : fsp__( 'READ MORE' ),
							'url'  => $link,
						],
					]
				]
			] );

			if ( $multipart )
			{
				$data[ 'multipart' ][] = [
					'name'     => 'reply_markup',
					'contents' => $replyMarkup
				];
			}
			else
			{
				$data[ 'reply_markup' ] = $replyMarkup;
			}
		}

		return $data;
	}
}
