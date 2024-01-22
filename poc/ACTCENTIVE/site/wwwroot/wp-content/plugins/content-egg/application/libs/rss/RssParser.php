<?php

namespace ContentEgg\application\libs\rss;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * RssParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class RssParser extends RestClient {

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'xml'
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct() {
		$this->setResponseType( 'xml' );
	}

	public function search( $query, $uri ) {
		$uri = str_replace( '%KEYWORD%', rawurlencode( $query ), $uri );
		$uri = str_replace( '%KEY-WORD%', urlencode( str_replace( ' ', '-', $query ) ), $uri );
		$uri = str_replace( '%KEY+WORD%', urlencode( str_replace( ' ', '+', $query ) ), $uri );

		$url_parts = @parse_url( $uri );
		if ( isset( $url_parts['scheme'] ) && isset( $url_parts['host'] ) ) {
			$uri = $url_parts['scheme'] . '://' . $url_parts['host'];
			$this->setUri( $uri );
		} else {
			throw new \Exception( 'No valid URI scheme was provided. ' );
		}


		$path = '';
		if ( isset( $url_parts['path'] ) ) {
			$path = $url_parts['path'];
		}
		if ( isset( $url_parts['query'] ) ) {
			$path .= '?' . $url_parts['query'];
		}

		$response = $this->restGet( $path );

		return $this->_decodeResponse( $response );
	}

}
