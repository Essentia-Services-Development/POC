<?php

namespace ContentEgg\application\libs\pixabay;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * PixabaySearch class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 * @link: https://pixabay.com/api/docs/
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class PixabaySearch extends RestClient {

	const API_URI_BASE = 'https://pixabay.com';

	private $accountKey = null;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'json'
	);

	/**
	 * Constructor
	 */
	public function __construct( $accountKey, $responseType = 'json' ) {
		$this->setAccountKey( $accountKey );
		$this->setResponseType( $responseType );
		$this->setUri( self::API_URI_BASE );
	}

	public function setAccountKey( $accountKey ) {
		$this->accountKey = $accountKey;
	}

	public function getAccountKey() {
		return $this->accountKey;
	}

	public function search( $query, array $params = array() ) {
		$params['q']   = $query;
		$params['key'] = $this->getAccountKey();

		$response = $this->restGet( '/api', $params );

		return $this->_decodeResponse( $response );
	}

}
