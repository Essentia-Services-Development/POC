<?php

namespace ContentEgg\application\libs\cj;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * CjProductsRest class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 *
 * @link: https://cjcommunity.force.com/s/article/4777058
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class CjProductsRest extends RestClient {

	const API_URI_BASE = 'https://product-search.api.cj.com/v2';

	private $access_token;
	private $dev_key;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'xml',
	);

	public function __construct( $access_token, $dev_key = '', $responseType = 'xml' ) {
		$this->setResponseType( $responseType );
		$this->setUri( self::API_URI_BASE );
		$this->access_token = $access_token;
		$this->dev_key      = $dev_key;
	}

	/*
	 * @link: https://cjcommunity.force.com/s/article/Product-Catalog-Search-API-4777185
	 */

	public function search( $query, array $params = array() ) {
		$params['keywords'] = $query;
		if ( $this->access_token ) {
			$this->setCustomHeaders( array( 'Authorization' => 'Bearer ' . $this->access_token ) );
		} else {
			$this->setCustomHeaders( array( 'Authorization' => $this->dev_key ) );
		}

		$response = $this->restGet( '/product-search', $params );

		return $this->_decodeResponse( $response );
	}

}
