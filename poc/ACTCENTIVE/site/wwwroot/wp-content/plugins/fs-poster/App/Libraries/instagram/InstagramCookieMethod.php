<?php

namespace FSPoster\App\Libraries\instagram;

use Exception;
use FSP_GuzzleHttp\Client;
use FSPoster\App\Providers\Date;
use FSP_GuzzleHttp\Cookie\CookieJar;

class InstagramCookieMethod
{
    /**
     * @var Client
     */
    private $_client;
    private $cookies;

    public function __construct ( $cookies, $proxy )
    {
        $this->cookies = $cookies;

        $this->_client = new Client( [
            'cookies'         => new CookieJar( FALSE, $cookies ),
            'allow_redirects' => [ 'max' => 10 ],
            'proxy'           => empty( $proxy ) ? NULL : $proxy,
            'verify'          => FALSE,
            'http_errors'     => FALSE,
            'headers'         => [
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1'
            ]
        ] );
    }

    public function profileInfo ()
    {
        try
        {
            $response = (string)$this->_client->get( 'https://www.instagram.com/' )->getBody();
        }
        catch ( Exception $e )
        {
            $response = '';
        }

        preg_match( '/\\\"id\\\"\:\\\"([0-9]+)\\\"/iU', $response, $id );
        preg_match( '/profile_pic_url\\\":\\\"(.+)\\\"/iU', $response, $profile_pic_url );
        preg_match( '/username\\\":\\\"(.+)\\\"/iU', $response, $username );
        preg_match( '/csrf_token\":\"(.+)\"/iU', $response, $csrfToken );
        
        if ( isset( $profile_pic_url[ 1 ] ) )
        {
            $profile_pic_url = str_replace( ['\\\\u0026', '\/'], ['&', '/'], $profile_pic_url[ 1 ] );
        }
        else
        {
            $profile_pic_url = '';
        }

        if ( empty( $id ) || empty( $username ) || empty( $csrfToken ) )
        {
            return FALSE;
        }

        return [
            'id'              => $id[ 1 ],
            'full_name'       => $username[ 1 ],
            'username'        => $username[ 1 ],
            'csrf'            => $csrfToken[ 1 ],
            'profile_pic_url' => $profile_pic_url
        ];
    }

    public function uploadCarouselItem ( $photo )
    {
        $uploadId = $this->createUploadId();

        $params = [
            'media_type'          => '1',
            'upload_media_height' => (string)$photo[ 'height' ],
            'upload_media_width'  => (string)$photo[ 'width' ],
            'upload_id'           => $uploadId,
        ];

        try
        {
            $response = (string)$this->_client->post( 'https://www.instagram.com/rupload_igphoto/fb_uploader_' . $uploadId, [
                'headers' => [
                    'X-Requested-With'           => 'XMLHttpRequest',
                    'X-CSRFToken'                => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Name'              => 'feed_' . $uploadId,
                    'X-Entity-Length'            => filesize( $photo[ 'path' ] ),
                    'Offset'                     => '0'
                ],
                'body'    => fopen( $photo[ 'path' ], 'r' )
            ] )->getBody();

            $result = json_decode( $response, TRUE );
            if ( $result[ 'status' ] == 'fail' )
            {
                return [
                    'status'    => 'error',
                    'error_msg' => isset( $result[ 'message' ] ) ? $result[ 'message' ] : fsp__( 'Error' )
                ];
            }
            return $result;
        }
        catch ( Exception $e )
        {
            return [
                'status'    => 'error',
                'error_msg' => $e->getMessage()
            ];
        }

    }

    public function generateAlbum ( $account_id, $photos, $caption, $instagramPinThePost = 0 )
    {
        $body = [
            "caption"                       => $caption,
            "children_metadata"             => [],
            "client_sidecar_id"             => $this->createUploadId(),
            "disable_comments"              => "0",
            "like_and_view_counts_disabled" => false,
            "source_type"                   => "library"
        ];

        foreach ( $photos as $photo )
        {
            $response = $this->uploadCarouselItem( $photo );
            if ( $response[ 'status' ] == 'ok' )
            {
                $body[ "children_metadata" ][] = [
                    "upload_id" => $response[ 'upload_id' ]
                ];
            }
            else
            {
                return $response; // when failed to upload image
            }
        }

        if ( count( $body[ 'children_metadata' ] ) == 0 )
        {
            return [
                'status'    => 'error',
                'error_msg' => fsp__( 'Error' )
            ];
        }

        try
        {
            $response = (string)$this->_client->post( "https://i.instagram.com/api/v1/media/configure_sidecar/", [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'X-CSRFToken'      => $this->getCsrfToken(),
                    'Offset'           => '0',
                    "x-ig-app-id"      => "936619743392459",
                    "x-csrf-token"     => $this->getCsrfToken()
                ],
                "json"    => $body
            ] )->getBody();

            $result = json_decode( $response, TRUE );

            if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'fail' )
            {
                return [
                    'status'    => 'error',
                    'error_msg' => ! empty( $result[ 'message' ] ) && is_string( $result[ 'message' ] ) ? InstagramApi::error( $result[ 'message' ], $account_id ) : InstagramApi::error()
                ];
            }

            return [
                'status' => 'ok',
                'id'     => isset( $result[ 'media' ][ 'code' ] ) ? $result[ 'media' ][ 'code' ] : '?',
                'id2'    => isset( $result[ 'media' ][ 'id' ] ) ? $result[ 'media' ][ 'id' ] : '?'
            ];
        }
        catch ( Exception $e )
        {
            return [
                'status'    => 'error',
                'error_msg' => InstagramApi::error( $e->getMessage(), 1 )
            ];
        }
    }

    public function uploadPhoto ( $account_id, $photo, $caption, $link = '', $target = 'timeline', $instagramPinThePost = 0 )
    {
        $uploadId = $this->createUploadId();

        $params = [
            'media_type'          => '1',
            'upload_media_height' => (string)$photo[ 'height' ],
            'upload_media_width'  => (string)$photo[ 'width' ],
            'upload_id'           => $uploadId,
        ];

        try
        {
            $response = (string)$this->_client->post( 'https://www.instagram.com/rupload_igphoto/fb_uploader_' . $uploadId, [
                'headers' => [
                    'X-Requested-With'           => 'XMLHttpRequest',
                    'X-CSRFToken'                => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Name'              => 'feed_' . $uploadId,
                    'X-Entity-Length'            => filesize( $photo[ 'path' ] ),
                    'Offset'                     => '0'
                ],
                'body'    => fopen( $photo[ 'path' ], 'r' )
            ] )->getBody();
        }
        catch ( Exception $e )
        {
            return [
                'status'    => 'error',
                'error_msg' => InstagramApi::error( $e->getMessage(), $account_id )
            ];
        }

        $response = json_decode( $response, TRUE );

        if ( ! isset( $response[ 'upload_id' ] ) || $response[ 'upload_id' ] != $uploadId )
        {
            return [
                'status'    => 'error',
                'error_msg' => ! empty( $response[ 'message' ] ) && is_string( $response[ 'message' ] ) ? InstagramApi::error( $response[ 'message' ], $account_id ) : ( ! empty( $response[ 'debug_info' ][ 'message' ] ) && is_string( $response[ 'debug_info' ][ 'message' ] ) ? InstagramApi::error( $response[ 'debug_info' ][ 'message' ], $account_id ) : InstagramApi::error() )
            ];
        }

        switch ( $target )
        {
            case 'timeline':
                $endpoint = 'configure';

                $params = [
                    'upload_id'                    => $uploadId,
                    'caption'                      => $caption,
                    'usertags'                     => '',
                    'custom_accessibility_caption' => '',
                    'retry_timeout'                => ''
                ];
                break;
            default:
                $endpoint = 'configure_to_story';

                $params = [
                    'upload_id' => $uploadId,
                    'story_cta' => json_encode( [ [ "links" => [ [ "webUri" => $link ] ] ] ] )
                ];
        }

        try
        {
            $result = (string)$this->_client->post( 'https://www.instagram.com/create/' . $endpoint . '/', [
                'form_params' => $params,
                'headers'     => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'X-CSRFToken'      => $this->getCsrfToken()
                ]
            ] )->getBody();
        }
        catch ( Exception $e )
        {
            return [
                'status'    => 'error',
                'error_msg' => InstagramApi::error( $e->getMessage(), $account_id )
            ];
        }

        $result = json_decode( $result, TRUE );

        if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'fail' )
        {
            return [
                'status'    => 'error',
                'error_msg' => ! empty( $result[ 'message' ] ) && is_string( $result[ 'message' ] ) ? InstagramApi::error( $result[ 'message' ], $account_id ) : InstagramApi::error()
            ];
        }

        return [
            'status' => 'ok',
            'id'     => isset( $result[ 'media' ][ 'code' ] ) ? $result[ 'media' ][ 'code' ] : '?',
            'id2'    => isset( $result[ 'media' ][ 'id' ] ) ? $result[ 'media' ][ 'id' ] : '?'
        ];
    }

    public function uploadVideo ( $account_id, $video, $caption, $link = '', $target = 'timeline', $instagramPinThePost = 0 )
    {
        $uploadId = $this->createUploadId();

        $params = [
            'is_igtv_video'            => FALSE,
            'media_type'               => '2',
            'video_format'             => 'video/mp4',
            'upload_media_height'      => (string)$video[ 'height' ],
            'upload_media_width'       => (string)$video[ 'width' ],
            'upload_media_duration_ms' => (string)( $video[ 'duration' ] * 1000 ),
            'upload_id'                => $uploadId,
        ];

        try
        {
            $response = $this->_client->post( 'https://www.instagram.com/rupload_igvideo/feed_' . $uploadId, [
                'headers' => [
                    'X-Requested-With'           => 'XMLHttpRequest',
                    'X-CSRFToken'                => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Name'              => 'feed_' . $uploadId,
                    'X-Entity-Length'            => filesize( $video[ 'path' ] ),
                    'Offset'                     => '0'
                ],
                'body'    => fopen( $video[ 'path' ], 'r' )
            ] )->getBody();
        }
        catch ( Exception $e )
        {
            return [
                'status'    => 'error',
                'error_msg' => InstagramApi::error( $e->getMessage(), $account_id )
            ];
        }

        $response = json_decode( $response, TRUE );

        if ( isset( $response[ 'status' ] ) && $response[ 'status' ] == 'fail' )
        {
            return [
                'status'    => 'error',
                'error_msg' => ! empty( $response[ 'message' ] ) && is_string( $response[ 'message' ] ) ? InstagramApi::error( $response[ 'message' ], $account_id ) : InstagramApi::error()
            ];
        }

        $videoThumbnail = $video[ 'thumbnail' ];

        $params = [
            'media_type'          => '2',
            'upload_media_height' => (string)$videoThumbnail[ 'height' ],
            'upload_media_width'  => (string)$videoThumbnail[ 'width' ],
            'upload_id'           => $uploadId
        ];

        try
        {
            $response = $this->_client->post( 'https://www.instagram.com/rupload_igphoto/feed_' . $uploadId, [
                'headers' => [
                    'X-Requested-With'           => 'XMLHttpRequest',
                    'X-CSRFToken'                => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Name'              => 'feed_' . $uploadId,
                    'X-Entity-Length'            => filesize( $videoThumbnail[ 'path' ] ),
                    'Offset'                     => '0'
                ],
                'body'    => fopen( $videoThumbnail[ 'path' ], 'r' )
            ] );
        }
        catch ( Exception $e )
        {
            return [
                'status'    => 'error',
                'error_msg' => InstagramApi::error( $e->getMessage(), $account_id )
            ];
        }

        try
        {
            $result = (string)$this->_client->post( 'https://www.instagram.com/create/configure/', [
                'form_params' => [
                    'upload_id'                    => $uploadId,
                    'caption'                      => $caption,
                    'usertags'                     => '',
                    'custom_accessibility_caption' => '',
                    'retry_timeout'                => '12'
                ],
                'headers'     => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'X-CSRFToken'      => $this->getCsrfToken()
                ]
            ] )->getBody();
        }
        catch ( Exception $e )
        {
            return [
                'status'    => 'error',
                'error_msg' => InstagramApi::error( $e->getMessage(), $account_id )
            ];
        }

        $result = json_decode( $result, TRUE );

        if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'fail' )
        {
            return [
                'status'    => 'error',
                'error_msg' => ! empty( $result[ 'message' ] ) && is_string( $result[ 'message' ] ) ? InstagramApi::error( $result[ 'message' ], $account_id ) : InstagramApi::error()
            ];
        }

        return [
            'status' => 'ok',
            'id'     => isset( $result[ 'media' ][ 'code' ] ) ? $result[ 'media' ][ 'code' ] : '?',
            'id2'    => isset( $result[ 'media' ][ 'id' ] ) ? $result[ 'media' ][ 'id' ] : '?'
        ];
    }

    public function getPostInfo ( $postId )
    {
        try
        {
            $response = (string)$this->_client->get( 'https://www.instagram.com/p/' . $postId . '/' )->getBody();
        }
        catch ( Exception $e )
        {
            $response = '';
        }

        preg_match( "/\"edge_media_preview_comment\"\:\{\"count\"\:([0-9]+)\,/i", $response, $commentsCount );
        preg_match( "/\"edge_media_preview_like\"\:\{\"count\"\:([0-9]+)\,/i", $response, $likesCount );

        $commentsCount = isset( $commentsCount[ 1 ] ) ? $commentsCount[ 1 ] : 0;
        $likesCount = isset( $likesCount[ 1 ] ) ? $likesCount[ 1 ] : 0;

        return [
            'likes'    => $likesCount,
            'comments' => $commentsCount
        ];
    }

    public function updateBioLink ( $url )
    {
        try
        {
            $userBio = (string)$this->_client->get( 'https://i.instagram.com/api/v1/accounts/edit/web_form_data/', [
                'cookies' => new CookieJar( FALSE, $this->cookies ),
                'headers' => [
                    'X-CSRFToken' => $this->getCsrfToken(),
                    "X-IG-App-ID" => "936619743392459"
                ]
            ] )->getBody();

            $userBio = json_decode( $userBio, TRUE );
        }
        catch ( Exception $e )
        {
            $userBio = [];
        }

        if ( empty( $userBio ) )
        {
            return FALSE;
        }

        if ( empty( $userBio[ 'form_data' ] ) )
        {
            return FALSE;
        }

        $userBio = $userBio[ 'form_data' ];

        if ( ! empty( $userBio[ 'external_url' ] ) && $url == $userBio[ 'external_url' ] )
        {
            return TRUE;
        }

        $sendData = [
            'external_url'     => $url,
            'username'         => empty( $userBio[ 'username' ] ) ? '' : $userBio[ 'username' ],
            'biography'        => empty( $userBio[ 'biography' ] ) ? '' : $userBio[ 'biography' ],
            'phone_number'     => empty( $userBio[ 'phone_number' ] ) ? '' : $userBio[ 'phone_number' ],
            'email'            => empty( $userBio[ 'email' ] ) ? '' : $userBio[ 'email' ],
            'first_name'       => empty( $userBio[ 'first_name' ] ) ? '' : $userBio[ 'first_name' ],
            'chaining_enabled' => 'on'
        ];

        try
        {
            $body = http_build_query( $sendData, '', '&' );
            $result = (string)$this->_client->post( 'https://www.instagram.com/accounts/edit/', [
                'query'   => [
                    '__d' => 'dis'
                ],
                'body'    => $body,
                'headers' => [
                    'Accept'                      => '*/*',
                    'Accept-Encoding'             => 'gzip',
                    'User-Agent'                  => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36',
                    'viewport-width'              => 1229,
                    'Content-Length'              => strlen( $body ),
                    'Content-Type'                => 'application/x-www-form-urlencoded',
                    'X-Requested-With'            => 'XMLHttpRequest',
                    'X-CSRFToken'                 => $this->getCsrfToken(),
                    'Origin'                      => 'https://www.instagram.com',
                    'sec-ch-prefers-color-scheme' => 'light',
                    'sec-ch-ua'                   => '".Not/A)Brand";v="99", "Google Chrome";v="103", "Chromium";v="103"',
                    'sec-ch-ua-mobile'            => '?0',
                    'sec-ch-ua-platform'          => '"Windows"',
                    'Sec-Fetch-Dest'              => 'empty',
                    'Sec-Fetch-Mode'              => 'cors',
                    'Sec-Fetch-Site'              => 'same-origin',
                    'Connection'                  => 'keep-alive',
                    'Host'                        => 'www.instagram.com',
                    'Referer'                     => 'https://www.instagram.com/accounts/edit/'
                ]
            ] )->getBody();

            $result = json_decode( $result, TRUE );

            return ! empty( $result[ 'status' ] ) && $result[ 'status' ] == 'ok';
        }
        catch ( Exception $e )
        {
            return FALSE;
        }
    }

    public function writeComment ( $comment, $mediaId )
    {
        $endpoint = sprintf( "https://www.instagram.com/web/comments/%s/add/", $mediaId );

        try
        {
            $response = $this->_client->post( $endpoint, [
                "form_params" => [
                    "comment_text"          => $comment,
                    "replied_to_comment_id" => ""
                ],
                'headers'     => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'X-CSRFToken'      => $this->getCsrfToken()
                ]
            ] )->getBody();

            $response = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
            $response = '';
        }

        if ( isset( $response[ 'status' ] ) )
        {
            if ( $response[ 'status' ] == 'ok' )
            {
                return [
                    'id' => $response[ 'id' ]
                ];
            }
            else if ( $response[ 'status' ] == 'fail' )
            {
                return [
                    'error' => $response[ 'message' ]
                ];
            }
        }

        return [
            'error' => fsp__( 'Unknown error' )
        ];
    }

    private function getCsrfToken ()
    {
        $cookies = $this->_client->getConfig( 'cookies' )->toArray();
        $csrf = '';

        foreach ( $cookies as $cookieInf )
        {
            if ( $cookieInf[ 'Name' ] == 'csrftoken' )
            {
                $csrf = $cookieInf[ 'Value' ];
            }
        }

        return $csrf;
    }

    private function createUploadId ()
    {
        return Date::epoch() . rand( 100, 999 );
    }

    public function __destruct ()
    {
        foreach ( InstagramApi::$recycle_bin as $image )
        {
			if( file_exists( $image ) )
			{
				unlink( $image );
			}

        }
    }
}
