<?php

namespace ContentEgg\application\libs\google;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * CustomSearchApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 *
 * @link: https://developers.google.com/custom-search/json-api/v1/overview
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class CustomSearchApi extends RestClient {

	const API_URI_BASE = 'https://www.googleapis.com/customsearch/v1';

	protected $cx;
	protected $key;

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
	public function __construct( $cx, $key, $responseType = 'json' ) {
		$this->cx  = $cx;
		$this->key = $key;
		$this->setResponseType( $responseType );
		$this->setUri( self::API_URI_BASE );
	}

	public function images( $query, array $options = array() ) {
		$options['q']          = $query;
		$options['searchType'] = 'image';
		$response              = $this->restGet( '', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$query['cx']  = $this->cx;
		$query['key'] = $this->key;

		return parent::restGet( $path, $query );
	}

}
