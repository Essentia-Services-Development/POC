<?php

namespace ContentEgg\application\libs\flipkart;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * FlipkartApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 * @link: https://affiliate.flipkart.com/api-docs/
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class FlipkartApi extends RestClient {

	protected static $timeout = 30; //sec

	const API_URI_BASE = 'https://affiliate-api.flipkart.net/affiliate/1.0';

	protected $_tracking_id;
	protected $_token;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'json',
		'xml'
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct( $tracking_id, $token, $response_type = 'json' ) {
		$this->setTrackingId( $tracking_id );
		$this->setToken( $token );
		$this->setResponseType( $response_type );
		$this->setUri( self::API_URI_BASE );
	}

	public function setTrackingId( $tracking_id ) {
		$this->_tracking_id = $tracking_id;
	}

	public function setToken( $token ) {
		$this->_token = $token;
	}

	public function getTrackingId() {
		return $this->_tracking_id;
	}

	public function getToken() {
		return $this->_token;
	}

	/**
	 * Product Feed API
	 * @link: https://affiliate.flipkart.com/api-docs/af_prod_ref.html#get-1-0-search-format
	 */
	public function search( $keyword, array $options ) {
		$options['query'] = $keyword;
		$response         = $this->restGet( '/search.json', $options );

		// strange api error, no valid json
		$response = preg_replace( '/^.+?{"productInfoList/ims', '{"productInfoList', $response );
		$response = trim( $response );

		return $this->_decodeResponse( $response );
	}

	/**
	 * Search Query based on Product ID API
	 * @link: https://affiliate.flipkart.com/api-docs/af_prod_ref.html#pidsearchapi-new
	 */
	public function product( $product_id ) {
		$options  = array( 'id' => $product_id );
		$response = $this->restGet( '/product.json', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$this->setCustomHeaders( array(
			'Fk-Affiliate-Id'    => $this->getTrackingId(),
			'Fk-Affiliate-Token' => $this->getToken()
		) );

		return parent::restGet( $path, $query );
	}

}
