<?php

namespace ContentEgg\application\libs\cityads;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * CityadsApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * @link: http://cityads.com/api/dev/interface/rest
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class CityadsApi extends RestClient {

	protected static $timeout = 30; //sec

	const API_URI_BASE = 'http://cityads.com/api/rest/webmaster/json';

	protected $_api_key;

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
	public function __construct( $api_key, $type = 'json' ) {
		$this->setApiKey( $api_key );
		$this->setResponseType( $type );
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
	 * @link: http://cityads.com/api/dev/webmaster/goods-coupons?lang=ru#GETgoods
	 */
	public function products( $keywords, array $options ) {
		$options['keyword']     = $keywords;
		$options['remote_auth'] = $this->getApiKey();
		$response               = $this->restGet( '/goods', $options );

		return $this->_decodeResponse( $response );
	}

}
