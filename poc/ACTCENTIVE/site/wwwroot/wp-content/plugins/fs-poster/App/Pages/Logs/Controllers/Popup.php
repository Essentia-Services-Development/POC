<?php

namespace FSPoster\App\Pages\Logs\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Request;

trait Popup
{
    public function logs_filter()
    {
        $filter_results = Request::post( 'filter_results', 'all', 'string', [ 'all', 'error', 'ok' ] );
        $sn             = Request::post( 'sn_filter', 'all', 'string', [ 'all', 'fb', 'threads', 'twitter', 'instagram', 'linkedin', 'vk', 'pinterest', 'reddit', 'tumblr', 'ok', 'plurk', 'google_b', 'blogger', 'telegram', 'medium', 'wordpress', 'discord', 'mastodon', 'webhook' ] );

        Pages::modal( 'Logs', 'logs_filter', [
            'filter_results' => $filter_results,
            'sn_filter'      => $sn
        ] );
    }

    public function logs_webhook_response()
    {
        $id = Request::post( 'id', 0, 'int' );

        if ( ! $id > 0 )
        {
            Helper::response( FALSE );
        }

        $feed = DB::fetch('feeds', [ 'id' => $id, 'driver' => 'webhook' ]);

        if ( empty( $feed ) )
        {
            Helper::response( FALSE );
        }

        $dataOBJ = json_decode( $feed[ 'data' ], TRUE );
        $text = '';
        $json = '';

        if( ! empty( $dataOBJ ) && ! empty( $dataOBJ[ 'response' ] ) )
        {
            if ( empty( json_decode( $dataOBJ[ 'response' ], TRUE ) ) )
            {
                $text = $dataOBJ[ 'response' ];
            }
            else{
                $json = $dataOBJ[ 'response' ];
            }
        }

        Pages::modal( 'Logs', 'logs_webhook_response', [
            'text' => $text,
            'json' => htmlentities( $json )
        ] );
    }
}