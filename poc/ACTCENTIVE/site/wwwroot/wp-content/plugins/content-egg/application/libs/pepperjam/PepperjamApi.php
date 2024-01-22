<?php

namespace ContentEgg\application\libs\pepperjam;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * PepperjamApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 *
 * @link: http://help.pepperjamnetwork.com/publisher/api
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class PepperjamApi extends RestClient {

	const API_URI_BASE = 'https://api.pepperjamnetwork.com/20120402';

	protected $api_key;

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
	public function __construct( $api_key, $responseType = 'json' ) {
		$this->api_key = $api_key;
		$this->setUri( self::API_URI_BASE );
		$this->setResponseType( $responseType );
	}

	/**
	 * The product creative resource allows a publisher to pull product creatives for the advertisers they're working with.
	 * @link: http://help.pepperjamnetwork.com/publisher/api?version=20120402#creative-product
	 */
	public function search( $keywords, array $options ) {
		$options['keywords'] = $keywords;
		$response            = $this->restGet( '/publisher/creative/product', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$query['apiKey'] = $this->api_key;
		$query['format'] = $this->getResponseType();

		return parent::restGet( $path, $query );
	}

}
