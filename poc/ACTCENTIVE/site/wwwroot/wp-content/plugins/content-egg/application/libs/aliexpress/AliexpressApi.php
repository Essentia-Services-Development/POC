<?php

namespace ContentEgg\application\libs\aliexpress;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * AliexpressApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * REST Aliexpress API
 * @link: http://portals.aliexpress.com/help/help_center_API.html
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class AliexpressApi extends RestClient {

	const API_URI_BASE = 'http://gw.api.alibaba.com/openapi/param2/2/portals.open';

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
	 * listPromotionProduct
	 * @link: http://portals.aliexpress.com/help/help_center_API.html
	 */
	public function search( $keywords, array $options ) {
		$options['keywords'] = $keywords;

		if ( isset( $options['pageSize'] ) && $options['pageSize'] > 40 ) {
			$options['pageSize'] = 40;
		}

		$response = $this->restGet( '/api.listPromotionProduct/' . $this->getApiKey(), $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * getPromotionLinks
	 * @link: http://portals.aliexpress.com/help/help_center_API.html
	 */
	public function getLinks( $trackingId, $urls, array $options = array() ) {
		if ( is_array( $urls ) ) {
			$urls = join( ',', $urls );
		}

		$options['trackingId'] = $trackingId;
		$options['urls']       = $urls;

		$response = $this->restGet( '/api.getPromotionLinks/' . $this->getApiKey(), $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * getPromotionProductDetail
	 * @link: http://portals.aliexpress.com/help/help_center_API.html
	 */
	public function getProduct( $productId, array $options = array() ) {
		$options['productId'] = $productId;
		$response             = $this->restGet( '/api.getPromotionProductDetail/' . $this->getApiKey(), $options );

		return $this->_decodeResponse( $response );
	}

}
