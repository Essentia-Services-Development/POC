<?php

namespace ContentEgg\application\libs\cj;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * CjLinksRest class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 *
 * @link: http://cjsupport.custhelp.com/app/answers/detail/a_id/1552/kw/api
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class CjLinksRest extends RestClient {

	const API_URI_BASE = 'https://linksearch.api.cj.com/v2';

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

	public function search( $query, array $params = array() ) {
		$params['keywords'] = $query;
		if ( $this->access_token ) {
			$this->setCustomHeaders( array( 'Authorization' => 'Bearer ' . $this->access_token ) );
		} else {
			$this->setCustomHeaders( array( 'Authorization' => $this->dev_key ) );
		}

		$response = $this->restGet( '/link-search', $params );

		return $this->_decodeResponse( $response );
	}

}
