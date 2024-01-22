<?php

namespace FSPoster\App\Libraries\threads;

use Exception;
use FSP\phpseclib3\Crypt\AES;
use FSP\phpseclib3\Crypt\PublicKeyLoader;
use FSP\phpseclib3\Crypt\RSA;
use FSP_GuzzleHttp\Client;
use FSP_GuzzleHttp\Cookie\CookieJar;
use FSPoster\App\Providers\Helper;

class Threads
{
    private $username;
    private $pass;
    private $phone_id;
    private $android_device_id;
    private $device_id;
    private $mid = null;
    private $authorization = '';
    private $user_id = '0';

    private $device = [
        'manufacturer' => 'Xiaomi',
        'model' => 'MI 5s',
        'os_version' => 26,
        'os_release' => '8.0.0'
    ];
    /**
     * @var mixed
     */
    private $proxy;

    /**
     * @param $options array
     */
    public function __construct( $options, $proxy )
    {
        $this->username = $options['username'];
        $this->proxy    = $proxy;

        if(isset($options['password']))
        {
            $this->pass = $options['password'];
        }

        if( isset( $options[ 'mid' ] ) )
        {
            $this->mid = $options[ 'mid' ];
        }

        if( isset( $options[ 'authorization' ] ) )
        {
            $this->authorization = $options[ 'authorization' ];
        }

        if( isset( $options[ 'instagram_id' ] ) )
        {
            $this->user_id = $options[ 'instagram_id' ];
        }

        if( isset($options[ 'phone_id' ]) )
        {
            $this->phone_id = $options[ 'phone_id' ];
        }
        else
        {
            $this->setPhoneID();
        }

        if( isset($options[ 'device_id' ]) )
        {
            $this->device_id = $options[ 'device_id' ];
        }
        else
        {
            $this->setDeviceID();
        }

        if( isset($options[ 'android_device_id' ]) )
        {
            $this->android_device_id = $options[ 'android_device_id' ];
        }
        else
        {
            $this->setAndroidDeviceID();
        }
    }

    public function sendPost($sendType, $message, $link, $images)
    {
        $postURL = 'https://i.instagram.com/api/v1/media/configure_text_only_post/';
        $uploadID = (string)(int)(microtime(true) * 1000);
        $data = [
            'text_post_app_info' => json_encode([ 'reply_control' => 0 ]),
            'timezone_offset' => date( 'Z' ),
            'source_type' => '4',
            '_uid' => $this->user_id, //'50563231916',
            'device_id' => $this->getAndroidDeviceID(),
            'caption' => $message,
            'publish_mode' => 'text_post',
            'upload_id' => $uploadID,
            'device' => $this->device,
        ];

        if( $sendType === 'image' )
        {
            $postURL = 'https://i.instagram.com/api/v1/media/configure_text_post_app_feed/';

            $uploaded = $this->uploadIgPhoto( $uploadID, reset($images) );

            if( !isset( $uploaded['status'] ) || $uploaded['status'] !== 'ok' )
            {
                return [
                    'status'    => 'error',
                    'error_msg' => isset( $uploaded['message'] ) ? $uploaded['message'] : fsp__( 'Failed to upload the image!' )
                ];
            }

            $data['scene_capture_type'] = '';

            unset($data['publish_mode']);
        }
        else if( $sendType === 'link' )
        {
            $data['text_post_app_info'] = [
                'link_attachment_url' => $link
            ];
        }

        try
        {
            $client = new Client();
            $response = $client->post( $postURL, [
                //'headers'     => $this->getDefaultHeaders(),
                'headers' => [
                    'User-Agent' => 'Barcelona 289.0.0.77.109 Android',
                    'Sec-Fetch-Site' => 'same-origin',
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Authorization' =>   $this->authorization
                ],
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode($data)
                ],
                'proxy' => empty( $this->proxy ) ? null : $this->proxy,
            ] )->getBody()->getContents();
        }
        catch (Exception $e)
        {
            if( method_exists( $e, 'getResponse' ) && is_object($e->getResponse()) && method_exists( $e->getResponse(), 'getBody' ) )
            {
                $response = $e->getResponse()->getBody()->getContents();
            }
            else
            {
                return [
                    'status' => 'error',
                    'error_msg' => $e->getMessage()
                ];
            }
        }

        $response = json_decode( $response, true );

        if( isset( $response[ 'media' ][ 'code' ] ) )
        {
            return [
                'status' => 'ok',
                'id'     => $response[ 'media' ][ 'code' ],
                'id2'    => isset( $response[ 'media' ][ 'id' ] ) ? esc_html( $response[ 'media' ][ 'id' ] ) : '?'
            ];
        }

        return [
            'status'    => 'error',
            'error_msg' => isset( $response['message'] ) ? $response['message'] : fsp__( 'Unknown error!' )
        ];
    }

    public function uploadIgPhoto ( $uploadId, $photo )
    {
        $params = [
            'media_type'            => '1',
            'upload_id'             => $uploadId,
            'image_compression'     => '{"lib_name":"moz","lib_version":"3.1.m","quality":"87"}',
            'xsharing_user_ids'     => '[]',
            'sticker_burnin_params' => '[]',
            'retry_context'         => json_encode( [
                'num_step_auto_retry'   => 0,
                'num_reupload'          => 0,
                'num_step_manual_retry' => 0
            ] ),
            'IG-FB-Xpost-entry-point-v2' => 'feed'
        ];

        $entity_name = sprintf( '%s_%d_%d', $uploadId, 0, mt_rand( 1000000000, 9999999999) );
        $endpoint    = 'https://i.instagram.com/rupload_igphoto/' . $entity_name;

        try
        {
            $client = new Client();
            $response = (string) $client->post( $endpoint, [
                'headers' => [
                    'X_FB_PHOTO_WATERFALL_ID'    => $this->generateUUID(),
                    'X-Requested-With'           => 'XMLHttpRequest',
                    //'X-CSRFToken'                => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Type'              => Helper::mimeContentType($photo),
                    'X-Entity-Name'              => $entity_name,
                    'X-Entity-Length'            => filesize( $photo ),
                    'Offset'                     => '0',
                    'Authorization'              => $this->authorization
                ],
                'body'    => file_get_contents($photo),
                'proxy'   => empty( $this->proxy ) ? null : $this->proxy
            ] )->getBody();

            $response = json_decode( $response, TRUE );
        }
        catch ( Exception $e )
        {
            $response = [];

            if( method_exists( $e, 'getResponse' ) && is_object($e->getResponse()) && method_exists( $e->getResponse(), 'getBody' ) )
            {
                $response = json_decode($e->getResponse()->getBody()->getContents(), true);
            }
        }

        return $response;
    }

    private function getDefaultHeaders()
    {
        return [
            "User-Agent" => "Instagram 203.0.0.29.118 Android (26/8.0.0; 480dpi; 1080x1920; Xiaomi; MI 5s; capricorn; qcom; en_US; 314665256)",
            "Accept-Encoding" => "gzip, deflate",
            "Accept" => "*/*",
            "Connection" => "keep-alive",
            "X-IG-App-Locale" => "en_US",
            "X-IG-Device-Locale" => "en_US",
            "X-IG-Mapped-Locale" => "en_US",
            "X-Pigeon-Session-Id" => "UFS-" . $this->generateUUID() . "-1",
            "X-Pigeon-Rawclienttime" => sprintf('%.3f', microtime(true)),
            "X-IG-Bandwidth-Speed-KBPS" => sprintf('%.3f', mt_rand(2500000, 3000000)/1000),
            "X-IG-Bandwidth-TotalBytes-B" => (string) mt_rand(5000000, 90000000),
            "X-IG-Bandwidth-TotalTime-MS" => (string) mt_rand(2000, 9000),
            "X-IG-App-Startup-Country" => "US",
            "X-Bloks-Version-Id" => "5fd5e6e0f986d7e592743211c2dda24efc502cff541d7a7cfbb69da25b293bf1",
            "X-IG-WWW-Claim" => "0",
            "X-Bloks-Is-Layout-RTL" => "false",
            "X-Bloks-Is-Panorama-Enabled" => "true",
            "X-IG-Device-ID" => $this->getDeviceID(),
            "X-IG-Family-Device-ID" => $this->getPhoneID(),
            "X-IG-Android-ID" => $this->getAndroidDeviceID(),
            "X-IG-Timezone-Offset" => "-14400",
            "X-IG-Connection-Type" => "WIFI",
            "X-IG-Capabilities" => "3brTvx0=",
            "X-IG-App-ID" => "567067343352427",
            "Priority" => "u=3",
            "Accept-Language" => "en-US",
            "X-MID" => $this->mid,
            "Host" => "i.instagram.com",
            "X-FB-HTTP-Engine" => "Liger",
            "X-FB-Client-IP" => "True",
            "X-FB-Server-Cluster" => "True",
            "IG-INTENDED-USER-ID" => $this->user_id,
            "X-IG-Nav-Chain" => "9MV =>self_profile =>2,ProfileMediaTabFragment =>self_profile =>3,9Xf =>self_following =>4",
            "X-IG-SALT-IDS" => (string) mt_rand(1061162222, 1061262222),
            "Authorization" => $this->authorization,
            "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8"
        ];
    }

    public function login()
    {
        $this->prefill();
        $key = $this->sync();

        if( $key === false )
        {
            return [
                'status' => false,
                'error_msg' => fsp__( 'Login failed!' )
            ];
        }

        $encPass = $this->encPass( $this->pass, $key['key_id'], $key['pub_key'] );

        $data = [
            'jazoest' => '22578',
            'country_codes' => [ json_encode([
                'country_code' => '1',
                'source' => ['default']
            ], JSON_UNESCAPED_SLASHES) ],
            'phone_id' => $this->getPhoneID(),
            'enc_password' => $encPass,
            'username' => $this->username,
            'adid' => $this->generateUUID(),
            'guid' => $this->getDeviceID(),
            'device_id' => $this->getAndroidDeviceID(),
            'google_tokens' => '[]',
            'login_attempt_count' => 0,
        ];

        $client = new Client();

        try{
            $resp = $client->post('https://i.instagram.com/api/v1/accounts/login/', [
                'headers' => $this->getDefaultHeaders(),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode($data, JSON_UNESCAPED_SLASHES)
                ],
                'proxy' => empty( $this->proxy ) ? null : $this->proxy,
            ]);
            $respArr = json_decode($resp->getBody()->getContents(), true);

            if( isset($respArr['logged_in_user']['pk_id']) && !empty($resp->getHeader('ig-set-authorization')[0]) )
            {
                $this->authorization = $resp->getHeader('ig-set-authorization')[0];
                $this->user_id = $respArr['logged_in_user']['pk_id'];

                $this->sendPostLoginFlow();

                /*
                if( empty( $respArr['logged_in_user']['text_post_app_joiner_number'] ) )
                {
                    return [
                        'status' => false,
                        'error_msg' => fsp__( 'User have not joined Threads yet!' )
                    ];
                }
                */

                return [
                    'status' => true,
                    'data'   => [
                        'needs_challenge' => false,
                        'name'            => $respArr['logged_in_user']['full_name'],
                        'username'        => $this->username,
                        'profile_id'      => $respArr['logged_in_user']['pk_id'], //$respArr['logged_in_user']['text_post_app_joiner_number'],
                        'profile_pic'     => $respArr['logged_in_user']['profile_pic_url'],
                        'options'         => [
                            'username'          => $this->username,
                            'instagram_id'      => $respArr['logged_in_user']['pk_id'],
                            'mid'               => $this->mid,
                            'authorization'     => $resp->getHeader('ig-set-authorization')[0],
                            'phone_id'          => $this->phone_id,
                            'device_id'         => $this->device_id,
                            'android_device_id' => $this->android_device_id
                        ],
                    ]
                ];
            }
        }
        catch ( Exception $e )
        {
            if( ! method_exists( $e, 'getResponse' ) || !is_object($e->getResponse()) )
            {
                return [
                    'status' => false,
                    'error_msg' => fsp__( 'Login failed!' )
                ];
            }

            if( ! method_exists( $e->getResponse(), 'getBody' ) )
            {
                return [
                    'status' => false,
                    'error_msg' => fsp__( 'Login failed!' )
                ];
            }

            $resp = $e->getResponse();
        }

        $resp = $resp->getBody()->getContents();

        $resp = json_decode( $resp, true );

        if( ! isset( $resp[ 'two_factor_info' ] ) )
        {
            return [
                'status' => false,
                'error_msg' => isset($resp['message']) ? $resp['message'] : fsp__( 'Login failed!' )
            ];
        }

        $verification_method = '1';

        /*
        if ($resp['two_factor_info']['sms_two_factor_on'])
        {
            $verification_method = '1';
        }
        */

        if ($resp['two_factor_info']['whatsapp_two_factor_on'])
        {
            $verification_method = '6';
        }

        if ($resp['two_factor_info']['totp_two_factor_on'])
        {
            $verification_method = '3';
        }

        return [
            'status' => true,
            'data'   => [
                'needs_challenge' => true,
                'options'         => [
                    'username'                => $this->username,
                    'instagram_id'            => $resp['two_factor_info']['pk'],
                    'mid'                     => $this->mid,
                    'phone_id'                => $this->phone_id,
                    'device_id'               => $this->device_id,
                    'android_device_id'       => $this->android_device_id,
                    'verification_method'     => $verification_method,
                    'two_factor_identifier'   => $resp['two_factor_info']['two_factor_identifier'],
                    'obfuscated_phone_number' => isset( $resp['two_factor_info']['obfuscated_phone_number'] ) ? $resp['two_factor_info']['obfuscated_phone_number'] : ( isset($resp['two_factor_info']['obfuscated_phone_number_2']) ? $resp['two_factor_info']['obfuscated_phone_number_2'] : '' )
                ],
            ]
        ];
    }

    public function doTwoFactorAuth ( $two_factor_identifier, $code, $verification_method = '1' )
    {
        $code = preg_replace( '/\s+/', '', $code );
        $data = [
            "verification_code" => $code,
            "phone_id" => $this->getPhoneID(),
            "_csrftoken" => $this->generateToken(64),
            "two_factor_identifier" => $two_factor_identifier,
            "username" => $this->username,
            "trust_this_device" => "0",
            "guid" => $this->getDeviceID(),
            "device_id" => $this->getAndroidDeviceID(),
            "waterfall_id" => $this->generateUUID(),
            "verification_method" => $verification_method
        ];

        $client = new Client();

        try{
            $resp = $client->post('https://i.instagram.com/api/v1/accounts/two_factor_login/', [
                'headers' => $this->getDefaultHeaders(),
                'form_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode($data, JSON_UNESCAPED_SLASHES)
                ],
                'proxy'   => empty( $this->proxy ) ? null : $this->proxy
            ]);
        }
        catch ( Exception $e )
        {
            if( ! method_exists( $e, 'getResponse' ) || ! is_object($e->getResponse()) )
            {
                return [
                    'status' => false,
                    'error_msg' => fsp__( '2FA failed!' )
                ];
            }

            if( ! method_exists( $e->getResponse(), 'getBody' ) )
            {
                return [
                    'status' => false,
                    'error_msg' => fsp__( '2FA failed!' )
                ];
            }

            $resp = $e->getResponse();
        }

        $auth = $resp->getHeader( 'ig-set-authorization' );
        $body = json_decode($resp->getBody(), true);

        if( empty($auth[0]) )
        {
            return [
                'status'    => false,
                'error_msg' => isset($body['message']) ? $body['message'] : ''
            ];
        }

        $this->authorization = $auth[0];

        $data = [
            'name'            => $body['logged_in_user']['full_name'],
            'username'        => $this->username,
            'profile_id'      => $body['logged_in_user']['text_post_app_joiner_number'],
            'profile_pic'     => $body['logged_in_user']['profile_pic_url'],
            'options'         => [
                'username'          => $this->username,
                'instagram_id'      => $body['logged_in_user']['pk_id'],
                'mid'               => $this->mid,
                'authorization'     => $auth[0],
                'phone_id'          => $this->phone_id,
                'device_id'         => $this->device_id,
                'android_device_id' => $this->android_device_id
            ]
        ];

        return [
            'status' => true,
            'data'   => $data
        ];
    }

    public function prefill()
    {
        $client = new Client();

        try{
            $resp = $client->post('https://i.instagram.com/api/v1/accounts/contact_point_prefill/', [
                'headers' => $this->getDefaultHeaders(),
                'post_params' => [
                    'signed_body' => 'SIGNATURE.' . json_encode( [
                            'phone_id' => $this->getPhoneID(),
                            'usage'    => 'prefill'
                        ] )
                ],
                'proxy' => empty( $this->proxy ) ? null : $this->proxy,
            ]);

            if( ! empty($resp->getHeader('ig-set-x-mid')[0]) )
            {
                $this->mid = $resp->getHeader('ig-set-x-mid')[0];
            }
        }
        catch ( Exception $e )
        {
            if( method_exists( $e, 'getResponse' ) && is_object($e->getResponse()) && method_exists( $e->getResponse(), 'getHeader' ) && ! empty($e->getResponse()->getHeader('ig-set-x-mid')[0]) )
            {
                $this->mid = $e->getResponse()->getHeader('ig-set-x-mid')[0];
            }
        }
    }

    private function sync(){
        $client = new Client();

        try{
            $resp = $client->get('https://i.instagram.com/api/v1/qe/sync/', [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15',
                    'Accept-Encoding' => 'gzip,deflate',
                    'Accept' => '*/*',
                    'Connection' => 'Keep-Alive',
                    'Accept-Language' => 'en-US'
                ],
                'cookies' => CookieJar::fromArray([
                    'csrftoken' => $this->generateToken(32),
                    'ig_did' => strtoupper($this->generateUUID()),
                    'ig_nrcb' => '1',
                    'mid' => $this->generateToken(28)
                ], 'i.instagram.com'),
                'proxy' => empty( $this->proxy ) ? null : $this->proxy,
            ]);
        }
        catch ( Exception $e )
        {
            if( ! method_exists( $e, 'getResponse' ) || !is_object($e->getResponse()) )
            {
                return false;
            }

            if( ! method_exists( $e->getResponse(), 'getHeader' ) )
            {
                return false;
            }

            $resp = $e->getResponse();
        }

        foreach ($resp->getHeader('Set-Cookie') as $cookie)
        {
            if(strpos($cookie, 'mid') === 0)
            {
                $mid = explode( ';', $cookie )[0];
                $mid = explode('=', $mid)[1];
                if( ! empty($mid) )
                {
                    $this->mid = $mid;
                }
            }
        }

        if( isset($resp->getHeader('Ig-Set-Password-Encryption-Key-Id')[0], $resp->getHeader('Ig-Set-Password-Encryption-Pub-Key')[0]) )
        {
            return [
                'key_id'  => $resp->getHeader('Ig-Set-Password-Encryption-Key-Id')[0],
                'pub_key' => $resp->getHeader('Ig-Set-Password-Encryption-Pub-Key')[0]
            ];
        }

        return false;
    }

    private function encPass ( $password, $publicKeyId, $publicKey )
    {
        $key  = substr( md5( uniqid( mt_rand() ) ), 0, 32 );
        $iv   = substr( md5( uniqid( mt_rand() ) ), 0, 12 );
        $time = time();

        $rsa          = PublicKeyLoader::loadPublicKey( base64_decode( $publicKey ) );
        $rsa          = $rsa->withPadding( RSA::ENCRYPTION_PKCS1 );
        $encryptedRSA = $rsa->encrypt( $key );

        $aes = new AES( 'gcm' );
        $aes->setNonce( $iv );
        $aes->setKey( $key );
        $aes->setAAD( strval( $time ) );
        $encrypted = $aes->encrypt( $password );

        $payload = base64_encode( "\x01" | pack( 'n', intval( $publicKeyId ) ) . $iv . pack( 's', strlen( $encryptedRSA ) ) . $encryptedRSA . $aes->getTag() . $encrypted );

        return sprintf( '#PWD_INSTAGRAM:4:%s:%s', $time, $payload );
    }

    /**
     * X-IG-Family-Device-ID
     */
    private function setPhoneID()
    {
        $this->phone_id = $this->generateUUID();
    }

    /**
     * X-IG-Family-Device-ID
     */
    private function getPhoneID()
    {
        return $this->phone_id;
    }

    /**
     * X-IG-Adroid-ID
     */
    private function setAndroidDeviceID()
    {
        $this->android_device_id = 'android-' . strtolower($this->generateToken(20));
    }

    /**
     * X-IG-Android-ID
     */
    private function getAndroidDeviceID()
    {
        return $this->android_device_id;
    }

    /**
     * X-IG-Device-ID
     */
    private function setDeviceID()
    {
        $this->device_id = $this->generateUUID();
    }

    /**
     * X-IG-Device-ID
     */
    private function getDeviceID()
    {
        return $this->device_id;
    }

    private function generateUUID ()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0x0fff ) | 0x4000, mt_rand( 0, 0x3fff ) | 0x8000, mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
    }

    private function generateToken( $len = 10 )
    {
        $letters = 'QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';

        $token = '';
        mt_srand(time());
        for( $i = 0; $i < $len; $i++ ){
            $token .= $letters[mt_rand()%strlen($letters)];
        }

        return $token;
    }

    private function sendPostLoginFlow ()
    {
    }

    public function checkAccount(){
        $client = new Client();
        try
        {
            $res = $client->post('https://www.threads.net/api/graphql', [
                'headers' => [
                    'Authorization' =>  $this->authorization,
                    'User-Agent' => 'Barcelona 289.0.0.77.109 Android',
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'authority' => 'www.threads.net',
                    'accept' => '*/*',
                    'accept-language' => 'ko',
                    'cache-control' => 'no-cache',
                    'origin' => 'https://www.threads.net',
                    'pragma' => 'no-cache',
                    'Sec-Fetch-Site' => 'same-origin',
                    'x-asbd-id' => '129477',
                    'x-fb-lsd' => 'NjppQDEgONsU_1LCzrmp6q',
                    'x-ig-app-id' => '567067343352427',
                    'referer' => 'https://www.threads.net/@' . $this->username
                ],
                'query' => [
                    'lsd' => 'NjppQDEgONsU_1LCzrmp6q',
                    'variables' => '{"userID":"' . $this->user_id . '"}',
                    'doc_id' => '23996318473300828'
                ],
                'proxy' => empty( $this->proxy ) ? null : $this->proxy
            ])->getBody()->getContents();

            $userInfo = json_decode( $res, true );

            if(isset( $userInfo['data']['userData']['user'] ))
            {
                return [ 'status' => true ];
            }
        }
        catch ( Exception $e )
        {}

        return [
            'status'    => false,
            'error_msg' => fsp__( 'The account is disconnected from the plugin. Please add your account to the plugin again without deleting the account from the plugin; as a result, account settings will remain as it is.' )
        ];
    }
}