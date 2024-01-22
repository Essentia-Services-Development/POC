<?php

namespace ContentEgg\application\libs\cj;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * CjGraphQlApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 *
 * @link: https://developers.cj.com/graphql/reference/Product%20Search
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class CjGraphQlApi extends RestClient {

	const API_URI_BASE = 'https://ads.api.cj.com';

	private $accessToken;
	protected $_responseTypes = array(
		'json',
	);

	public function __construct( $accessToken ) {
		$this->setResponseType( 'json' );
		$this->setUri( self::API_URI_BASE );
		$this->accessToken = $accessToken;
	}

	public function search( $payload ) {
		$response = $this->restPost( '/query', $payload );

		return $this->_decodeResponse( $response );
	}

	public function restPost( $path, $data = null, $enctype = null, $opts = array() ) {
		$this->setCustomHeaders( array(
			'Authorization' => 'Bearer ' . $this->accessToken,
			'Accept'        => 'application/json'
		) );

		return parent::restPost( $path, $data );
	}

}
