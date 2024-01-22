<?php

namespace ContentEgg\application\libs\clickbank;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * ClickbankApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class ClickbankApi extends RestClient {

	const API_URI_BASE = 'https://accounts.clickbank.com/api2/';

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
	public function __construct( $responseType = 'xml' ) {
		$this->setResponseType( $responseType );
		$this->setUri( self::API_URI_BASE );
	}

	public function search( $keywords, array $options ) {
		$options['includeKeywords'] = $keywords;
		$response                   = $this->restGet( '/marketplace', $options );

		return $this->_decodeResponse( $response );
	}

}
