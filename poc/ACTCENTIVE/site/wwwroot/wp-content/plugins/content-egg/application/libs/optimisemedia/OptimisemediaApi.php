<?php

namespace ContentEgg\application\libs\optimisemedia;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * OptimisemediaApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 * @link: https://kb.optimisemedia.com/?article=omg-network-api-affiliate
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class OptimisemediaApi extends RestClient {

	const API_URI_BASE = 'https://api.omgpm.com/network/OMGNetworkApi.svc/v1.2';

	/*
	  protected $AffiliateID;
	  protected $AgencyID;
	 *
	 */

	protected $api_key;
	protected $private_key;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'json',
	);

	public function __construct( $api_key, $private_key ) {
		$this->api_key     = $api_key;
		$this->private_key = $private_key;
		$this->setResponseType( 'json' );
		$this->setUri( self::API_URI_BASE );
	}

	/**
	 * ProductFeeds
	 * @link: https://kb.optimisemedia.com/?article=omg-network-api-affiliate#GetProducts
	 */
	public function search( $keywords, array $options ) {
		$options['Keyword'] = $keywords;

		$response = $this->restGet( '/ProductFeeds/GetProducts', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		// API authentication
		$query['Key'] = $this->api_key;
		list( $query['SigData'], $query['Sig'] ) = $this->getSig();

		$this->setCustomHeaders( array( 'Content-Type' => 'application/json', 'Accept' => 'application/json' ) );

		return parent::restGet( $path, $query );
	}

	private function getSig() {
		//date_default_timezone_set("UTC");
		$t        = microtime( true );
		$micro    = sprintf( "%03d", ( $t - floor( $t ) ) * 1000 );
		$sig_data = gmdate( 'Y-m-d H:i:s.', $t ) . $micro;

		$sig = md5( $this->private_key . $sig_data );

		return array( $sig_data, $sig );
	}

}
