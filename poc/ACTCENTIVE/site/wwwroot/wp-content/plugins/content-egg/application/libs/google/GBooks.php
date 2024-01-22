<?php

namespace ContentEgg\application\libs\google;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * GBooks class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * Google Books API
 * @link: http://code.google.com/intl/ru/apis/books/docs/v1/using.html
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class GBooks extends RestClient {

	const API_URI_BASE = 'https://www.googleapis.com/books/v1';

	private $api_key;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'atom',
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
		$this->api_key = $api_key;
	}

	public function getApiKey() {
		return $this->api_key;
	}

	public function search( $query, array $params = array() ) {
		$_query        = array();
		$_query['key'] = $this->getApiKey();
		$_query['q']   = $query;
		$_query['alt'] = $this->getResponseType();
		foreach ( $params as $key => $param ) {
			switch ( $key ) {
				case 'langRestrict':
				case 'maxResults':
				case 'orderBy':
				case 'userIp':
				case 'download':
				case 'filter':
				case 'printType':
				case 'projection':
				case 'country':
					$_query[ $key ] = $param;
					break;
				case 'startIndex':
				case 'maxResults':
					$_query[ $key ] = ( (int) $param > 40 ) ? 40 : (int) $param;
					break;
			}
		}
		$response = $this->restGet( '/volumes', $_query );

		return $this->_decodeResponse( $response );
	}

}
