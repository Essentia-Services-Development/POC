<?php

namespace ContentEgg\application\libs\twitter;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * TwitterRest class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class TwitterRest extends RestClient {

	const API_URI_BASE = 'https://api.twitter.com/1.1';

	/**
	 * @link: https://dev.twitter.com/apps/
	 */
	private $consumer_key;
	private $consumer_secret;
	private $oauth_access_token;
	private $oauth_access_token_secret;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'json',
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct( $consumer_key, $consumer_secret, $oauth_access_token, $oauth_access_token_secret ) {
		$this->consumer_key              = $consumer_key;
		$this->consumer_secret           = $consumer_secret;
		$this->oauth_access_token        = $oauth_access_token;
		$this->oauth_access_token_secret = $oauth_access_token_secret;
		$this->setResponseType( 'json' );
		$this->setUri( self::API_URI_BASE );
	}

	/**
	 * Twitter search
	 * @link: https://dev.twitter.com/docs/api/1.1/get/search/tweets
	 */
	public function search( $query, array $params = array() ) {
		$_query      = array();
		$_query['q'] = $query;
		foreach ( $params as $key => $param ) {
			switch ( $key ) {
				case 'geocode':
				case 'lang':
				case 'until':
				case 'since_id':
				case 'max_id':
					$_query[ $key ] = $param;
					break;
				case 'result_type':
					$_query[ $key ] = ( ! in_array( $param, array(
						'mixed',
						'recent',
						'popular'
					) ) ) ? 'mixed' : $param;
					break;
				case 'count':
					$_query[ $key ] = ( (int) $param > 100 ) ? 100 : (int) $param;
					break;
			}
		}
		$response = $this->restGet( '/search/tweets.json', $_query );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		//all queries need auth for api 1.1
		$this->_setAuthorizationHeader( $path, $query, 'GET' );

		return parent::restGet( $path, $query );
	}

	/**
	 * @link: https://dev.twitter.com/docs/auth/authorizing-request
	 */
	private function _setAuthorizationHeader( $path, $query, $method ) {
		$url = self::API_URI_BASE . $path;

		$oauth                    = array(
			'oauth_consumer_key'     => $this->consumer_key,
			'oauth_nonce'            => time() . rand( 0, 1000 ),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_token'            => $this->oauth_access_token,
			'oauth_timestamp'        => time(),
			'oauth_version'          => '1.0'
		);
		$oauth['oauth_signature'] = $this->_buildSignature( $url, $method, array_merge( $query + $oauth ) );

		$header = $this->_buildAuthorizationHeader( $oauth );
		$this->setCustomHeaders( array( 'Authorization' => $header ) );
	}

	/**
	 * @link: https://dev.twitter.com/docs/auth/creating-signature
	 */
	private function _buildSignature( $baseURI, $method, $params ) {
		$r = array();
		ksort( $params );
		foreach ( $params as $key => $value ) {
			$r[] = "$key=" . rawurlencode( $value );
		}
		$base_info = $method . "&" . rawurlencode( $baseURI ) . '&' . rawurlencode( implode( '&', $r ) );

		$composite_key = rawurlencode( $this->consumer_secret ) . '&' . rawurlencode( $this->oauth_access_token_secret );

		return base64_encode( hash_hmac( 'sha1', $base_info, $composite_key, true ) );
	}

	private function _buildAuthorizationHeader( $oauth ) {
		$r      = 'OAuth ';
		$values = array();
		foreach ( $oauth as $key => $value ) {
			$values[] = "$key=\"" . rawurlencode( $value ) . "\"";
		}
		$r .= implode( ', ', $values );

		return $r;
	}

}
