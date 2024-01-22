<?php

namespace ContentEgg\application\libs\tradedoubler;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * TradedoublerProducts class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 *
 * @link: http://dev.tradedoubler.com/products/publisher/
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class TradedoublerProducts extends RestClient {

	protected static $timeout = 25; //sec
	const API_URI_BASE = 'https://api.tradedoubler.com/1.0/products';

	private $token;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'xml',
		'json',
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct( $token, $responseType = 'json' ) {
		$this->setResponseType( $responseType );
		$this->setUri( self::API_URI_BASE );
		$this->token = $token;
	}

	public function query( array $params ) {
		$uri = $this->getUri();
		$uri .= '.' . $this->getResponseType();

		// All services in Tradedoubler APIs use the matrix syntax.
		foreach ( $params as $key => $value ) {
			$uri .= ';' . urlencode( $key ) . '=' . urlencode( $value );
		}
		$uri        = str_replace( '%2C', ',', $uri );
		$o          = array();
		$o['token'] = $this->token;

		$response = $this->restGet( $uri, $o );

		return $this->_decodeResponse( $response );
	}

	public function search( $query, array $params = array() ) {
		$params['q'] = $query;

		return $this->query( $params );
	}

	public function searchEan( $ean, array $params = array() ) {
		$params['ean'] = $ean;

		return $this->query( $params );
	}

	public function product( $product_id ) {
		$params['tdId'] = $product_id;

		return $this->query( $params );
	}

}
