<?php

namespace ContentEgg\application\libs\zanox;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * ZanoxApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * @link: https://developer.zanox.com/web/guest/publisher-api-2011
 * @link: http://wiki.zanox.com/en/Step_1_-_Connect_your_application_or_site_with_zanox
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class ZanoxApi extends RestClient {

	const API_URI_BASE = 'https://api.zanox.com/json/2011-03-01';

	protected $_api_key;

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
	public function __construct( $api_key, $responseType = 'json' ) {
		$this->setApiKey( $api_key );
		$this->setResponseType( $responseType );
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
	 * @link: https://developer.zanox.com/web/guest/publisher-api-2011/get-products
	 */
	public function search( $keywords, array $options ) {
		$options['q'] = $keywords;

		if ( isset( $options['items'] ) && $options['items'] > 50 ) {
			$options['items'] = 50;
		}
		$response = $this->restGet( '/products', $options );

		return $this->_decodeResponse( $response );
	}

	public function searchEan( $ean, array $options ) {
		$options['ean'] = $ean;
		if ( isset( $options['items'] ) && $options['items'] > 50 ) {
			$options['items'] = 50;
		}
		$response = $this->restGet( '/products', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * Coupons
	 * @link: https://developer.zanox.com/web/guest/publisher-api-2011/get-incentives
	 */
	public function incentives( array $options ) {
		$response = $this->restGet( '/incentives', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * @link: https://developer.zanox.com/web/guest/publisher-api-2011/get-programs-program
	 */
	public function program( $program_id ) {
		$response = $this->restGet( '/programs/program/' . urlencode( $program_id ) );

		return $this->_decodeResponse( $response );
	}

	/**
	 * @link: https://developer.zanox.com/web/guest/publisher-api-2011/get-products-product
	 */
	public function product( $product_id, $options = array() ) {
		$response = $this->restGet( '/products/product/' . urlencode( $product_id ), $options );

		return $this->_decodeResponse( $response, $options );
	}

	public function restGet( $path, array $query = null ) {
		if ( empty( $query['connectid'] ) ) {
			$query['connectid'] = $this->getApiKey();
		}
		$this->setCustomHeaders( array( 'Content-Type' => 'application/json' ) );

		return parent::restGet( $path, $query );
	}

}
