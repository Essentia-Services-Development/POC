<?php

namespace ContentEgg\application\libs\gdeslon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * GdeSlonApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * @link: https://www.gdeslon.ru/affiliate-examples/xml-api
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class GdeSlonApi extends RestClient {

	const API_URI_BASE = 'http://api.gdeslon.ru';

	protected $_api_key;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'xml',
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct( $api_key ) {
		$this->setApiKey( $api_key );
		$this->setResponseType( 'xml' );
		$this->setUri( self::API_URI_BASE );
	}

	public function setApiKey( $api_key ) {
		$this->_api_key = $api_key;
	}

	public function getApiKey() {
		return $this->_api_key;
	}

	/**
	 * Products
	 */
	public function search( $keywords, array $options ) {
		$options['q']      = $keywords;
		$options['_gs_at'] = $this->getApiKey();
		if ( ! empty( $options['m'] ) && is_array( $options['m'] ) ) {
			$options['m'] = join( ',', $options['m'] );
		}
		if ( ! empty( $options['tid'] ) && is_array( $options['tid'] ) ) {
			$options['tid'] = join( ',', $options['tid'] );
		}
		$response = $this->restGet( '/api/search.xml', $options );

		return $this->_decodeResponse( $response );
	}

	public function product( $product_id ) {
		if ( is_array( $product_id ) ) {
			$product_id = join( ',', $product_id );
		}

		$options             = array();
		$options['articles'] = $product_id;
		$options['_gs_at']   = $this->getApiKey();

		$response = $this->restGet( '/api/search.xml', $options );

		return $this->_decodeResponse( $response );
	}

	public function getMerhants() {
		$response = $this->restGet( '/merchants.json' );

		return json_decode( $response, true );
	}

}
