<?php

namespace ContentEgg\application\libs\admitad;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * AdmitadCoupons class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class AdmitadCoupons extends RestClient {

	const API_BASE = 'https://export.admitad.com';

	protected static $timeout = 35; //sec

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'xml',
	);

	public function __construct() {
		$this->setResponseType( 'xml' );
		$this->setUri( self::API_BASE );
	}

	public function search( $keywords, $uri ) {
		$url_parts = @parse_url( $uri );

		if ( empty( $url_parts['host'] ) || ! preg_match( '/admitad\.com$/', $url_parts['host'] ) ) {
			throw new \Exception( 'No valid XML URL was provided. ' );
		}

		// redirect loop for http://export.admitad.com ...
		// path
		if ( ! empty( $url_parts['path'] ) ) {
			$path = $url_parts['path'];
		} else {
			$path = '';
		}

		// params
		if ( ! empty( $url_parts['query'] ) ) {
			parse_str( $url_parts['query'], $params );
		} else {
			$params = array();
		}

		if ( empty( $params['format'] ) || $params['format'] != 'xml' ) {
			throw new \Exception( 'No valid XML URL was provided. ' );
		}

		// keyword
		$params['keyword'] = $keywords;

		$response = $this->restGet( $path, $params );

		return $this->_decodeResponse( $response );
	}

}
