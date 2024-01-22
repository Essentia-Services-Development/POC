<?php

namespace ContentEgg\application\libs\admitad;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * AdmitadProducts class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class AdmitadProducts extends RestClient {

	const API_URI_BASE = 'http://185.58.206.88/wp';

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'php',
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct() {
		$this->setResponseType( 'php' );
		$this->setUri( self::API_URI_BASE );
	}

	public function search( $keyword, array $options ) {
		$options['q'] = $keyword;
		//"vendor"     => $vendor,
		//"offset"     => $offset,
		$response = $this->restGet( '/index.php', $options );

		return $this->_decodeResponse( $response );
	}

	public function update( array $items ) {
		$response = $this->restGet( '/up.php', array( "items" => $items ) );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$query['plugin'] = \ContentEgg\application\Plugin::slug();
		$this->setCustomHeaders( array( 'Referer' => parse_url( \site_url(), PHP_URL_HOST ) ) );

		return parent::restGet( $path, $query );
	}

}
