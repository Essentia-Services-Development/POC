<?php

namespace ContentEgg\application\libs\tradedoubler;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * TradedoublerCoupons class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 * @link: http://dev.tradedoubler.com/vouchers/publisher/
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class TradedoublerCoupons extends RestClient {

	const API_URI_BASE = 'http://api.tradedoubler.com/1.0/vouchers';

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

	public function search( $query, array $params = array() ) {
		$uri                = $this->getUri();
		$uri                .= '.' . $this->getResponseType();
		$params['keywords'] = $query;

		// All services in Tradedoubler APIs use the matrix syntax.
		foreach ( $params as $key => $value ) {
			$uri .= ';' . urlencode( $key ) . '=' . urlencode( $value );
		}
		$o          = array();
		$o['token'] = $this->token;
		$response   = $this->restGet( $uri, $o );

		return $this->_decodeResponse( $response );
	}

}
