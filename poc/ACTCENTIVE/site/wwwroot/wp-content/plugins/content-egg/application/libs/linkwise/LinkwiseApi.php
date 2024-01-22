<?php

namespace ContentEgg\application\libs\linkwise;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * LinkwiseApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class LinkwiseApi extends RestClient {

	const API_URI_BASE = 'https://affiliate.linkwi.se/api/1.1';

	protected $api_username;
	protected $api_password;
	protected $_responseTypes = array(
		'json',
		'xml'
	);

	public function __construct( $api_username, $api_password ) {
		$this->api_username = $api_username;
		$this->api_password = $api_password;
		$this->setUri( self::API_URI_BASE );
		$this->setResponseType( 'json' );
	}

	/**
	 * Product Search
	 * @link: https://affiliate.linkwi.se/api/1.1/products.html
	 */
	public function search( $keywords, array $options = array() ) {
		$options['keyword'] = $keywords;

		$response = $this->restGet( '/products.html', $options );

		return $this->_decodeResponse( $response );
	}

	public function products( $lw_prod_ids, array $options = array() ) {
		if ( ! is_array( $lw_prod_ids ) ) {
			$lw_prod_ids = array( $lw_prod_ids );
		}
		$options['lw_prod_ids'] = join( ',', $lw_prod_ids );
		$options['joined']      = 'all'; // Required parameter
		$response               = $this->restGet( '/products.html', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$query['format'] = $this->getResponseType();
		$this->setCustomHeaders( array( 'Authorization' => 'Basic ' . base64_encode( $this->api_username . ':' . $this->api_password ) ) );

		return parent::restGet( $path, $query );
	}

}
