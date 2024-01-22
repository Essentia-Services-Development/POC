<?php

namespace FSPoster\App\Libraries\xing;

use FSP_GuzzleHttp\Client;
use FSP_GuzzleHttp\Exception\GuzzleException;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSP_GuzzleHttp\Cookie\CookieJar;

class Xing
{
	/**
	 * @var Client
	 */
	private $client;
	private $cookies;
	private $proxy;
	private $endpoint;
	private $domain = 'www.xing.com';

	public function __construct ( array $cookies, $proxy = NULL )
	{
		$this->endpoint = 'https://www.xing.com/xing-one/api';
		$this->cookies  = $cookies;
		$this->proxy    = $proxy;

		$this->setClient();
	}

	public function send ( $nodeInfo, $postType, $message, $link, $images )
	{
		$message    = trim( $message );
		$visibility = ( int ) Helper::getOption( 'xing_post_visibility', '1' );
		$actorID    = $nodeInfo[ 'node_type' ] === 'account' ? ( 'surn:x-xing:users:user:' . $nodeInfo[ 'profile_id' ] ) : ( 'surn:x-xing:entitypages:page:' . $nodeInfo[ 'node_id' ] );

		$data = [
			'operationName' => 'CreateTextPosting',
			'variables'     => [
				'actorGlobalId'    => $actorID,
				'comment'          => '',
				'commentArticleV1' => [
					[
						'articleParagraph' => [
							'text'    => $message,
							'markups' => []
						]
					]
				],
				'visibility'       => $visibility === 1 ? 'PUBLIC' : 'PRIVATE',
				'links'            => NULL,
				'images'           => NULL,
				'audience'         => $visibility === 1 ? NULL : 'surn:x-xing:contacts:network:' . $nodeInfo[ 'profile_id' ] . ( $visibility === 3 ? ':same_city' : '' )
			],
			'query'         => 'mutation CreateTextPosting($actorGlobalId: GlobalID!, $comment: String!, $commentArticleV1: [ArticlesCreateArticleBlocksInput!], $visibility: PostingsVisibility, $images: [PostingsCreateImageAttachmentInput!], $links: [PostingsCreateLinkAttachmentInput!], $audience: [GlobalID!]) {  postingsCreatePosting(    input: {actorGlobalId: $actorGlobalId, comment: $comment, commentArticleV1: $commentArticleV1, visibility: $visibility, images: $images, links: $links, audience: $audience}  ) {    success {      id      actorGlobalId      activityId      comment      __typename    }    error {      message      details      __typename    }    __typename  }}'
		];

		if ( $postType === 1 && ! empty( $link ) )
		{
			$data[ 'variables' ][ 'links' ] = [
				[
					'url' => $link
				]
			];

			$body = json_encode( [
				"operationName" => "SharePreview",
				"variables"     => [ "url" => $link ],
				'query'         => 'query SharePreview($url: URL!) {  viewer {    id    linkPreview(url: $url) {      success {        title        description        sourceDomain        cachedImageUrl        metadata {          sourceActor {            title            subtitle            image            message            __typename          }          __typename        }        __typename      }      __typename    }    __typename  }}'
			], JSON_UNESCAPED_SLASHES );

			$CVPResponse = self::cmd( 'POST', $this->endpoint, $body, [ 'content-type' => 'application/json' ] );
		}
		else if ( $postType === 3 && ! empty( $images ) )
		{
			$uploadId = $this->uploadRequest( $images );

			if ( is_array( $uploadId ) && ! empty( $uploadId[ 'error_msg' ] ) )
			{
				return $uploadId;
			}

			$data[ 'variables' ][ 'images' ] = [
				[
					'uploadId' => $uploadId
				]
			];
		}

		$data = json_encode( $data, JSON_UNESCAPED_SLASHES );

		$result = self::cmd( 'POST', $this->endpoint, $data, [ 'content-type' => 'application/json' ] );

		if ( ! is_array( $result ) )
		{
			return $this->error( fsp__( 'Unknown error!' ) );
		}

		if ( ! empty( $result[ 'error_msg' ] ) )
		{
			return $result;
		}

        if ( isset( $result[ 'data' ][ 'postingsCreatePosting' ]['error']['details']['0'] ) )
        {
            return $this->error( $result[ 'data' ][ 'postingsCreatePosting' ]['error']['details']['0'] );
        }

		if ( ! empty( $result[ 'errors' ] ) )
		{
			return $this->error( $result[ 'errors' ][ 0 ][ 'message' ] );
		}

		if ( empty( $result[ 'data' ] ) || empty( $result[ 'data' ][ 'postingsCreatePosting' ] ) || empty( $result[ 'data' ][ 'postingsCreatePosting' ]['success'] ) )
		{
			return $this->error( fsp__( 'Unknown error!' ) );
		}

		if ( ! empty( $postingArray[ 'error' ] ) )
		{
			 return $this->error( $postingArray[ 'error' ][ 'message' ] );
		}

		return [
			'status' => 'ok',
			'id'     => $result[ 'data' ][ 'postingsCreatePosting' ][ 'success' ][ 'activityId' ]
		];
	}

	public function cmd ( $method, $endpoint, $body, $headers = [], $isArray = TRUE )
	{
		try
		{
			$response = $this->client->request( $method, $endpoint, [
				'headers' => $headers,
				'body'    => $body
			] )->getBody();
		}
		catch ( GuzzleException $e )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Error! %s', [ $e->getMessage() ] )
			];
		}

		if ( $isArray )
		{
			return json_decode( $response, TRUE );
		}

		return $response;
	}

	public function getAccountData ()
	{
		$data = [
			'operationName' => 'xingFrameQuery',
			'variables'     => [],
			'query'         => 'query xingFrameQuery {  viewer {    id    webTrackingData {      PropHashedUserId      __typename    }    xingId {      academicTitle      birthday      displayName      displayFlag      userFlags {        displayFlag        __typename      }      firstName      gender      globalId      id      lastName      pageName      profileImage(size: [SQUARE_128]) {        url        __typename      }      profileOccupation {        occupationOrg        occupationTitle        __typename      }      occupations {        headline        subline        __typename      }      __typename    }    features {      isXingEmployee      isJobsPoster      isAdmasterUser      isBrandPageCollaborator      isBasic      isPremium      isExecutive      isSales      hasProJobsMembership      isCraUser      isSeatManagerAdmin      showProbusinessInNavigation      showUpsellHint      showJobSeekerBoneyardUpsellHint      showPremiumBoneyardUpsellHint      hasCoachProfile      hasNewSettings      isBrandManagerEditor      __typename    }    featureSwitches    loginState    __typename  }}'
		];

		$accountData = self::cmd( 'POST', $this->endpoint, json_encode( $data ), [ 'content-type' => 'application/json' ] );

		if ( ! empty( $accountData[ 'status' ] ) && $accountData[ 'status' ] === 'error' )
		{
			Helper::response( FALSE, $accountData[ 'error_msg' ] );
		}

		if ( empty( $accountData[ 'data' ] ) || empty( $accountData[ 'data' ][ 'viewer' ][ 'id' ] ) )
		{
			Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
		}

		return $accountData[ 'data' ][ 'viewer' ];
	}

	public function getCompanies ()
	{
		$accountData = self::cmd( 'POST', $this->endpoint, '{"operationName":"xcpManagedCompanies","variables":{"first":9},"query":"query xcpManagedCompanies($first: Int, $after: String) {\n  viewer {\n    id\n    managedCompanies(first: $first, after: $after) {\n      pageInfo {\n        hasNextPage\n        endCursor\n        __typename\n      }\n      edges {\n        node {\n          company {\n            ...CompanyData\n            __typename\n          }\n          __typename\n        }\n        __typename\n      }\n      __typename\n    }\n    __typename\n  }\n}\n\nfragment CompanyData on Company {\n  id\n  entityPageId\n  companyName\n  entityPage {\n    publicationStatus\n    slug\n    contract {\n      type\n      __typename\n    }\n    coverImage(dimensions: [{height: 600, width: 600, reference: \"xcp_medium\"}]) {\n      url\n      __typename\n    }\n    __typename\n  }\n  logos {\n    logo128px\n    __typename\n  }\n  industry {\n    localizationValue\n    __typename\n  }\n  kununuData {\n    ratingAverage\n    ratingCount\n    __typename\n  }\n  links {\n    default\n    public\n    __typename\n  }\n  address {\n    city\n    __typename\n  }\n  userContext {\n    followState {\n      isFollowing\n      __typename\n    }\n    __typename\n  }\n  __typename\n}\n"}', [ 'content-type' => 'application/json' ] );

		if ( ! empty( $accountData[ 'status' ] ) && $accountData[ 'status' ] === 'error' )
		{
			Helper::response( FALSE, $accountData[ 'error_msg' ] );
		}

		if ( empty( $accountData[ 'data' ] ) || empty( $accountData[ 'data' ][ 'viewer' ][ 'id' ] ) )
		{
			Helper::response( FALSE, fsp__( 'The entered cookies are wrong!' ) );
		}

		return $accountData[ 'data' ][ 'viewer' ][ 'managedCompanies' ][ 'edges' ];
	}

	public function uploadRequest ( $photo )
	{
		$photoData = file_get_contents( $photo );

		if ( empty( $photoData ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'The given file path is not valid!' )
			];
		}

		$data = [
			'operationName' => 'UploadRequest',
			'variables'     => [
				'application' => 'POSTINGS',
				'fileSize'    => strlen( $photoData ),
				'fileType'    => Helper::mimeContentType( $photo ) //only supports png/jpeg and gif formats
			],
			'query'         => 'mutation UploadRequest($application: UploadApplication!, $fileSize: Long!, $fileType: String) {  uploadRequest(    input: {application: $application, fileSize: $fileSize, fileType: $fileType}  ) {    success {      id      authToken      url      __typename    }    error {      id      message      __typename    }    __typename  }}'
		];

		$resp = self::cmd( 'POST', $this->endpoint, json_encode( $data ), [ 'content-type' => 'application/json' ] );

		if ( empty( $resp ) || empty( $resp[ 'data' ] ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => ! empty( $resp[ 'errors' ] ) ? $resp[ 'errors' ][ 0 ][ 'message' ] : ( isset( $resp[ 'error_msg' ] ) ? $resp[ 'error_msg' ] : fsp__( 'Couldn\'t upload the image or unknown error!' ) )
			];
		}

		$uploadResp = $resp[ 'data' ][ 'uploadRequest' ];

		if ( ! isset( $uploadResp[ 'success' ] ) )
		{
			if ( ! empty( $resp[ 'data' ][ 'uploadRequest' ][ 'error' ][ 'message' ] ) )
			{
				return [
					'status'    => 'error',
					'error_msg' => $resp[ 'data' ][ 'uploadRequest' ][ 'error' ][ 'message' ]
				];
			}

			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Couldn\'t upload the image or unknown error!' )
			];
		}

		$result = self::cmd( 'PATCH', $uploadResp[ 'success' ][ 'url' ], $photoData, [
			'Tus-Resumable' => '1.0.0',
			'Upload-Offset' => 0,
			'Authorization' => 'Bearer ' . $uploadResp[ 'success' ][ 'authToken' ],
			'content-type'  => 'application/offset+octet-stream'
		] );

		if ( ! empty( $result ) )
		{
			return [
				'status'    => 'error',
				'error_msg' => substr( $result, 0, 300 )
			];
		}

		return $uploadResp[ 'success' ][ 'id' ];
	}

	private function setClient ()
	{
		$cookieArr = [];

		foreach ( $this->cookies as $k => $v )
		{
			$cookieArr[] = [
				"Name"     => $k,
				"Value"    => $v,
				"Domain"   => '.' . $this->domain,
				"Path"     => "/",
				"Max-Age"  => NULL,
				"Expires"  => NULL,
				"Secure"   => FALSE,
				"Discard"  => FALSE,
				"HttpOnly" => FALSE,
				"Priority" => "HIGH"
			];
		}

		$cookieJar = new CookieJar( FALSE, $cookieArr );

		$this->client = new Client( [
			'cookies'     => $cookieJar,
			'proxy'       => empty( $this->proxy ) ? NULL : $this->proxy,
			'verify'      => FALSE,
			'http_errors' => FALSE,
			'headers'     => [
				'User-Agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:75.0) Gecko/20100101 Firefox/75.0',
				'X-CSRF-Token' => $this->cookies[ 'xing_csrf_token' ],
				'Host'         => $this->domain
			]
		] );
	}

	private function error ( $msg )
	{
		return [
			'status'    => 'error',
			'error_msg' => $msg
		];
	}

	public function refetchAccount ( $accountId )
	{
		$companies = $this->getCompanies();
		$getNodes  = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id, node_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d', [ $accountId ] ), ARRAY_A );
		$myNodes   = [];

		foreach ( $getNodes as $node )
		{
			$myNodes[ $node[ 'id' ] ] = $node[ 'node_id' ];
		}

		if ( ! empty( $companies ) )
		{
			foreach ( $companies as $node )
			{
				$node = $node[ 'node' ][ 'company' ];

				if ( ! in_array( $node[ 'id' ], $myNodes ) )
				{

					DB::DB()->insert( DB::table( 'account_nodes' ), [
						'blog_id'     => Helper::getBlogId(),
						'user_id'     => get_current_user_id(),
						'account_id'  => $accountId,
						'driver'      => 'xing',
						'node_type'   => 'company',
						'node_id'     => $node[ 'entityPageId' ],
						'name'        => $node[ 'companyName' ],
						'cover'       => isset( $node[ 'logos' ][ 'logo128px' ] ) ? $node[ 'logos' ][ 'logo128px' ] : NULL,
						'screen_name' => isset( $node[ 'links' ][ 'public' ] ) ? $node[ 'links' ][ 'public' ] : ( isset( $node[ 'links' ][ 'default' ] ) ? $node[ 'links' ][ 'default' ] : NULL )
					] );
				}
				else
				{
					DB::DB()->update( DB::table( 'account_nodes' ), [
						'name'        => $node[ 'companyName' ],
						'cover'       => isset( $node[ 'logos' ][ 'logo128px' ] ) ? $node[ 'logos' ][ 'logo128px' ] : NULL,
						'screen_name' => isset( $node[ 'links' ][ 'public' ] ) ? $node[ 'links' ][ 'public' ] : ( isset( $node[ 'links' ][ 'default' ] ) ? $node[ 'links' ][ 'default' ] : NULL )
					], [
						'account_id' => $accountId,
						'node_id'    => $node[ 'entityPageId' ]
					] );

					$id = array_search( $node[ 'id' ], $myNodes );

					unset( $myNodes[ $id ] );
				}
			}
		}

		if ( ! empty( $myNodes ) )
		{
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (' . implode( ',', array_keys( $myNodes ) ) . ')' );
			DB::DB()->query( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (' . implode( ',', array_keys( $myNodes ) ) . ')' );

			foreach ( $myNodes as $k => $v )
			{
				Helper::deleteCustomSettings( 'node', $k );
			}
		}

		return [ 'status' => TRUE ];
	}

	public function updateCookies ( $id, $profileId )
	{
		$myInfo = $this->getAccountData();

		if ( ! isset( $myInfo[ 'id' ] ) || ( $profileId !== $myInfo[ 'id' ] ) )
		{
			return FALSE;
		}

		$dataSQL = [
			'proxy'     => $this->proxy,
			'options'   => json_encode( $this->cookies ),
			'status'    => NULL,
			'error_msg' => NULL
		];

		DB::DB()->update( DB::table( 'accounts' ), $dataSQL, [ 'id' => $id ] );

		return TRUE;
	}

	public function checkAccount ()
	{
		$data = [
			'operationName' => 'xingFrameQuery',
			'variables'     => [],
			'query'         => 'query xingFrameQuery {  viewer {    id    webTrackingData {      PropHashedUserId      __typename    }    xingId {      academicTitle      birthday      displayName      displayFlag      userFlags {        displayFlag        __typename      }      firstName      gender      globalId      id      lastName      pageName      profileImage(size: [SQUARE_128]) {        url        __typename      }      profileOccupation {        occupationOrg        occupationTitle        __typename      }      occupations {        headline        subline        __typename      }      __typename    }    features {      isXingEmployee      isJobsPoster      isAdmasterUser      isBrandPageCollaborator      isBasic      isPremium      isExecutive      isSales      hasProJobsMembership      isCraUser      isSeatManagerAdmin      showProbusinessInNavigation      showUpsellHint      showJobSeekerBoneyardUpsellHint      showPremiumBoneyardUpsellHint      hasCoachProfile      hasNewSettings      isBrandManagerEditor      __typename    }    featureSwitches    loginState    __typename  }}'
		];

		$response = self::cmd( 'POST', $this->endpoint, json_encode( $data ), [ 'content-type' => 'application/json' ] );

		if ( ! $response )
		{
			return [ 'error' => TRUE ];
		}
		else if ( isset( $response[ 'error_msg' ] ) )
		{
			return [
				'error'     => TRUE,
				'error_msg' => $response[ 'error_msg' ]
			];
		}

		return $response;
	}
}
