<?php

namespace FSPoster\App\Libraries\planly;

use Exception;
use ArrayObject;
use DateInterval;
use DateTime;
use FSPoster\App\Providers\Helper;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\DB;

class Planly
{
	/**
	 * @var Client
	 */
	private $client;

	public function __construct ( $token, $proxy )
	{
		$this->client = new Client( [
			"proxy"       => empty( $proxy ) ? NULL : $proxy,
			"verify"      => FALSE,
			"http_errors" => FALSE,
			"headers"     => [
				"Authorization" => "Bearer {$token}",
				"Content-Type"  => "application/json"
			]
		] );
	}

	public function getUser ()
	{
		$nodes = [];

		$ping = $this->cmd( "ping" );

		if ( is_array( $ping ) )
		{
            return [
                "status"    => FALSE,
                "error_msg" => $ping[ "error_msg" ]
            ];
		}

        $userData = json_decode( $ping->getBody(), TRUE );

        if ( ! $userData )
        {
            return [
                "status"    => FALSE,
                "error_msg" => fsp__( "Couldn't retrieve user's data" )
            ];
        }

        if ( !empty( $userData[ "error" ] ) )
        {
            return [
                "status"    => FALSE,
                "error_msg" => $userData[ "error" ][ "message" ]
            ];
        }

        $data = $userData[ "data" ];
        $teams = $data[ "teams" ];
        $user  = [
            "id"      => $data[ "id" ],
            "picture" => $data[ "picture" ],
            "email"   => $data[ "email" ],
            "name"    => $data[ "fullname" ],
        ];

        foreach ( $teams as $team )
        {
            if ( ! $team[ "id" ] )
            {
                continue;
            }

            $response = $this->cmd( "channels/list", json_encode( [ "team_id" => $team[ "id" ] ] ) );

            if ( is_array( $response ) )
            {
                return [
                    "status"    => FALSE,
                    "error_msg" => $response[ "error_msg" ]
                ];
            }

            $channels = json_decode( $response->getBody(), TRUE );

            if ( $channels )
            {
                $channels = $channels[ "data" ][ "channels" ];

                foreach ( $channels as $channel )
                {
                    $url  = "";
                    $name = $channel[ "name" ];
                    $type = $channel[ "social_network" ];

                    if ( $type === "pinterest" )
                    {
                        continue;
                    }

                    switch ( $type )
                    {
                        case "tiktok":
                        case "tiktok_business":
                            $url = "https://www.tiktok.com/@{$name}";
                            break;
                        case "instagram":
                            $url = "https://www.instagram.com/{$name}";
                            break;
                        case "twitter":
                            $url = "https://www.twitter.com/{$name}";
                            break;
                        case "linkedin":
                            $url = "https://www.linkedin.com/in/me/";
                            break;
                        case "pinterest":
                            $url = "https://www.pinterest.com/{$name}/";
                            break;
                        case "facebook";
                            $url = "https://www.facebook.com/{$name}/";
                            break;
                    }

                    $nodes[] = [
                        "id"      => $channel[ "id" ],
                        "picture" => $channel[ "picture" ],
                        "name"    => "{$name} @{$team[ "name" ]}",
                        "url"     => $url,
                        "type"    => $type,
                        "team_id" => $team[ "id" ]
                    ];
                }
            }
        }

        return [
            "status" => TRUE,
            "user"   => $user,
            "nodes"  => $nodes
		];
	}

	public function checkAccount ()
	{
		$user = $this->getUser();

		return [
			"error" => ! $user[ "status" ],
			"error_msg" => isset( $user[ "error_msg" ] ) ? $user[ "error_msg" ] : ""
		];
	}

	public function refetch ( $id )
	{
		$data = $this->getUser();

		if ( ! $data[ "status" ] )
		{
            //error happened during fetch
            DB::DB()->update( DB::table( "accounts" ), [
                "status"    => "error",
                "error_msg" => ! empty( $data[ "error_msg" ] ) ? $data[ "error_msg" ] : fsp__( "The account has lost connection to Planly" ),
            ], [ "id" => $id ] );

            return $data;
		}

        DB::DB()->update( DB::table( "accounts" ), [
            "name"        => $data[ "user" ][ "name" ],
            "profile_pic" => $data[ "user" ][ "picture" ],
        ], [ "id" => $id ] );

        $getNodes = DB::DB()->get_results( DB::DB()->prepare( "SELECT id, node_id FROM " . DB::table( "account_nodes" ) . " WHERE account_id = %d", [ $id ] ), ARRAY_A );
        $myNodes  = [];

        foreach ( $getNodes as $node )
        {
            $myNodes[ $node[ "node_id" ] ] = $node[ "id" ];
        }

        foreach ( $data[ "nodes" ] as $channel )
        {
            if ( isset( $myNodes[ $channel[ "id" ] ] ) )
            {
                DB::DB()->update( DB::table( "account_nodes" ), [
                    "name"        => $channel[ "name" ],
                    "screen_name" => $channel[ "url" ],
                    "cover"       => $channel[ "picture" ]
                ], [ "id" => $myNodes[ $channel[ "id" ] ] ] );
            }
            else
            {
                DB::DB()->insert( DB::table( "account_nodes" ), [
                    "node_type"    => $channel[ "type" ],
                    "user_id"      => get_current_user_id(),
                    "blog_id"      => Helper::getBlogId(),
                    "node_id"      => $channel[ "id" ],
                    "access_token" => $channel[ "team_id" ],
                    "screen_name"  => $channel[ "url" ],
                    "driver"       => "planly",
                    "account_id"   => $id,
                    "name"         => $channel[ "name" ],
                    "cover"        => $channel[ "picture" ]
                ] );
            }

            unset( $myNodes[ $channel[ "id" ] ] );
        }

        if ( ! empty( $myNodes ) )
        {
            DB::DB()->query( "DELETE FROM " . DB::table( "account_nodes" ) . " WHERE id IN (" . implode( ",", array_values( $myNodes ) ) . ")" );
            DB::DB()->query( "DELETE FROM " . DB::table( "account_node_status" ) . " WHERE node_id IN (" . implode( ",", array_values( $myNodes ) ) . ")" );
        }

        return [ "status" => TRUE ];
	}

	public function post ( $message, $media, $teamId, $channelId, $sn )
	{
		$options  = new ArrayObject();
        $mediaObj = [];

        foreach ( $media as $url )
        {
            $media = $this->upload( $url, $teamId );

            if ( $media[ "status" ] !== "ok" )
            {
                return $media;
            }

            $mediaObj[] = [ "id" => $media[ "id" ] ];
        }

        switch ($sn)
        {
            case "tiktok":
            case "tiktok_business":
                $options = [
                    "can_users_comment"   => TRUE,
                    "video_accessibility" => "public"
                ];
                break;
            case "instagram":
                $options = [
                    "post_type" => 0 // 0 - post, 1 - reel
                ];
        }

        $response = $this->cmd( "schedules/create", json_encode( [
            "publish_on"      => ( new DateTime() )->add( DateInterval::createFromDateString( "6 seconds" ) )->format( DateTime::ATOM ),    //ISO timestamp
            "status"          => 1,                                                                                                                        //1 or 0
            "contents"        => [
                $sn => [
                    "content" => $message,
                    "media"   => $mediaObj,
                    "options" => $options
                ]
            ],
            "social_channels" => [ $channelId ],
        ] ) );

        if ( is_array( $response ) )
        {
            return [
                "status"    => "error",
                "error_msg" => $response[ "error_msg" ]
            ];
        }

        $body = json_decode( $response->getBody(), TRUE );

        if ( empty( $body ) )
        {
            return [
                "status"    => "error",
                "error_msg" => fsp__( "No proper response from Planly. Please check your Planly account and try again!" )
            ];
        }

        if ( ! empty( $body[ "error" ] ) )
        {
            return [
                "status"    => "error",
                "error_msg" => $body[ "error" ][ "message" ]
            ];
        }

        $id = $body[ "data" ][ 0 ][ "id" ];

        return [
			"status"    => "ok",
			"id"        => $id,
			"post_link" => "https://app.planly.com/calendar/schedules/{$id}", //TODO undo this
		];
	}

	public function repost ( $id )
	{
		$error    = "";
		$response = $this->cmd( "schedules/reschedule", json_encode( [
			"id"         => $id,
			"publish_on" => ( new DateTime() )->add( DateInterval::createFromDateString( "6 seconds" ) )->format( DateTime::ATOM ),
			//ISO timestamp
			"status"     => 1,
		] ) );

		if ( is_array( $response ) )
		{
			$error = $response[ "error_msg" ];
		}
		else
		{
			$body = $response->getBody();

			if ( ! $body )
			{
				$error = fsp__( "No proper response from Planly. Please check your Planly account and try again!" );
			}
		}

		return ! empty( $error ) ? [
			"status"    => "error",
			"error_msg" => $error
		] : [
			"status"    => "ok",
			"id"        => $id,
			"post_link" => "https://app.planly.com/calendar/schedules/{$id}",
		];
	}

	public function getSchedule ( $id )
	{
		$schedule = $this->cmd( "schedules/get", json_encode( [ "id" => $id ] ) );

		if ( is_array( $schedule ) )
		{
            return [
                "status"    => FALSE,
                "error_msg" => $schedule[ "error_msg" ]
            ];
		}

        $schedule = json_decode( $schedule->getBody(), TRUE );

        if ( empty( $schedule ) )
        {
            return [
                "status"    => FALSE,
                "error_msg" => fsp__( "No proper response from Planly. Please check your Planly account and try again!" )
            ];
        }

        if ( isset( $schedule[ "error" ] ) )
        {
            return[
                "status"    => FALSE,
                "error_msg" => $schedule[ "error" ][ "message" ]
            ];
        }

        return [
			"status"   => TRUE,
			"schedule" => $schedule[ "data" ],
		];
	}

	private function cmd ( $endpoint, $body = "", $headers = [] )
	{
		try
		{
			$response = $this->client->request( "POST", "https://app.planly.com/api/{$endpoint}", [
				"headers" => $headers,
				"body"    => $body
			] );
		}
		catch ( Exception $e )
		{
			if ( ! method_exists( $e, 'getResponse' ) )
			{
                return [
                    'status'    => FALSE,
                    'error_msg' => $e->getMessage()
                ];
			}

            $response = $e->getResponse();

            if ( is_null( $response ) )
            {
                return [
                    'status'    => FALSE,
                    'error_msg' => $e->getMessage()
                ];
            }
		}

		return $response;
	}

	private function upload ( $url, $teamId )
	{
		$media = file_get_contents( $url );

		$contentType = Helper::mimeContentType($url);

		$headers = [
			"x-planly-file-name" => rawurlencode( basename( $url ) ),
			"x-planly-team-id"   => $teamId,
			"Content-Type"       => $contentType
		];

		$response = $this->cmd( "media/get-by-hash", json_encode( [
			"sha_256" => hash( "sha256", $media ),
			"team_id" => $teamId,
		] ) );

		if ( is_array( $response ) )
		{
            return [
                "status"    => "error",
                "error_msg" => $response[ "error_msg" ]
            ];
		}

        $body = json_decode( $response->getBody(), TRUE );

        if ( empty( $body ) )
        {
            return [
                "status"    => "error",
                "error_msg" => fsp__( "No proper response from Planly. Please check your Planly account and try again!" )
            ];
        }

        if ( ! empty( $body[ "error" ] ) )
        {
            return [
                "status"    => "error",
                "error_msg" => $body[ "error" ][ "message" ]
            ];
        }

        if ( ! empty( $body[ "data" ][ "id" ] ) )
        {
            return [
                "status" => "ok",
                "id"     => $body[ "data" ][ "id" ]
            ];
        }

        $response = $this->cmd( "media/upload", $media, $headers );

        if ( empty( $response ) )
        {
            return [
                "status"    => "error",
                "error_msg" => fsp__( "No proper response from Planly. Please check your Planly account and try again!" )
            ];

        }

        if ( is_array( $response ) )
        {
            return [
                "status"    => "error",
                "error_msg" => $response[ "error_msg" ]
            ];
        }

        $body = json_decode( $response->getBody(), TRUE );

        if ( empty( $body ) )
        {
            return [
                "status"    => "error",
                "error_msg" => fsp__( "Couldn't upload the media!" )
            ];
        }

        if ( !empty( $body[ "error" ] ) )
        {
            return [
                "status"    => "error",
                "error_msg" => $body[ "error" ][ "message" ]
            ];
        }

        return [
			"status" => "ok",
			"id"     => $body[ "data" ][ "id" ]
		];
	}
}
