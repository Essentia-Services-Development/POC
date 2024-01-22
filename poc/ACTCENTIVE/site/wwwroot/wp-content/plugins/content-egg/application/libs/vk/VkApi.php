<?php

namespace ContentEgg\application\libs\vk;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * VkApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 *
 * @link: https://vk.com/dev/apiusage
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class VkApi extends RestClient {

	const API_URI_BASE = 'https://api.vk.com/method';

	private $apiKey;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'json',
	);

	/**
	 * Constructor
	 *
	 * @param string API Key
	 * @param string $responseType
	 */
	public function __construct( $key = null ) {
		$this->setUri( self::API_URI_BASE );
		$this->setApiKey( $key );
		$this->setResponseType( 'json' );
	}

	public function setApiKey( $key ) {
		$this->apiKey = $key;
	}

	/**
	 * @link: https://vk.com/dev/newsfeed.search
	 */
	public function newsfeedSearch( $query, $params ) {
		$_query      = array();
		$_query['q'] = $query;
		foreach ( $params as $key => $param ) {
			switch ( $key ) {
				case 'extended':
				case 'latitude':
				case 'longitude':
				case 'start_time':
				case 'end_time':
				case 'start_from':
				case 'fields':
					$_query[ $key ] = $param;
					break;
				case 'count':
					$_query[ $key ] = ( (int) $param > 200 ) ? 200 : (int) $param;
					break;
			}
		}
		$response = $this->restGet( '/newsfeed.search', $_query );

		return $this->_decodeResponse( $response );
	}

}
