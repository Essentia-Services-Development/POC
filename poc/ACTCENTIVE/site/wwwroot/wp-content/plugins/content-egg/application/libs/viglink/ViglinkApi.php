<?php

namespace ContentEgg\application\libs\viglink;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * ViglinkApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 *
 * @link: https://viglink-developer-center.readme.io/
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class ViglinkApi extends RestClient {

	protected static $timeout = 40; //sec

	const API_URI_BASE = 'https://rest.viglink.com/api';

	protected $apiKey;
	protected $secretKey;
	protected $_responseTypes = array(
		'json',
		'xml'
	);

	public function __construct( $apiKey, $secretKey, $response_type = 'json' ) {
		$this->apiKey    = $apiKey;
		$this->secretKey = $secretKey;
		$this->setResponseType( $response_type );
		$this->setUri( self::API_URI_BASE );
	}

	/**
	 * Product Search
	 * @link: https://viglink-developer-center.readme.io/docs/product-search
	 */
	public function search( $keyword, array $options ) {
		$options['query'] = $keyword;
		$response         = $this->restGet( '/product/search', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * Get back the metadata about an product (Product URL) with this endpoint.
	 * @link: https://viglink-developer-center.readme.io/docs/metadata
	 */
	public function getMetadata( $url ) {
		$options['url'] = $url;
		$response       = $this->restGet( '/product/metadata', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		if ( ! $query ) {
			$query = array();
		}
		$query['apiKey'] = $this->apiKey;
		$this->setCustomHeaders( array( 'Authorization' => 'secret ' . $this->secretKey ) );

		return parent::restGet( $path, $query );
	}

}
