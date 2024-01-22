<?php

namespace ContentEgg\application\libs\affiliatewindow;

defined( '\ABSPATH' ) || exit;

/**
 * AffiliatewindowSoap class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 * @link: http://wiki.affiliatewindow.com/index.php/ProductServe_API
 */
class AffiliatewindowSoap {

	const WSDL_URI = 'http://v3.core.com.productserve.com/ProductServeService.wsdl';
	const API_NAMESPACE = 'http://v3.core.com.productserve.com/';

	protected $api_key;
	protected $soapClient;

	/**
	 * @var array Options for soap calling
	 */
	protected $options = array(
		'connection_timeout' => 15,
		//"exceptions" => 1,
		//"encoding" => 'UTF-8',
		'trace'              => false,
	);

	public function __construct( $api_key ) {
		if ( ! class_exists( '\SoapClient', false ) ) {
			throw new \Exception( 'You are missing the PHP SoapClient. You need to install the PHP SOAP extension.' );
		}

		$this->api_key = $api_key;

		try {
			$this->soapClient = new \SoapClient( self::WSDL_URI, $this->options );
		} catch ( \SoapFault $fault ) {
			throw new \Exception( "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring} )" );
		}
	}

	protected function call( $methodName, $parameters = array() ) {
		$oUser          = new \stdClass();
		$oUser->sApiKey = $this->api_key;
		$oHeader        = new \SoapHeader( self::API_NAMESPACE, 'UserAuthentication', $oUser );
		$this->soapClient->__setSoapHeaders( $oHeader );

		try {
			$response = $this->soapClient->$methodName( $parameters );
		} catch ( \SoapFault $fault ) {
			throw new \Exception( "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})" );
		}

		return $response;
	}

	/**
	 * @link: http://wiki.affiliatewindow.com/index.php/GetProductList
	 */
	public function getProductList( $keywords, array $options ) {
		$parameters = array();
		foreach ( $options as $key => $value ) {
			switch ( $key ) {
				case 'iAdult':
				case 'bHotPick':
				case 'sMode':
				case 'sSort':
				case 'iLimit':
				case 'iOffset':
				case 'sColumnToReturn':
					$parameters[ $key ] = $value;
					break;
			}
		}
		$parameters['sQuery'] = $keywords;

		if ( ! isset( $options['merchantID'] ) ) {
			$options['merchantID'] = array();
		}

		//@link: http://wiki.affiliatewindow.com/index.php/RefineByGroup
		$oRefineBy                = new \stdClass();
		$oRefineBy->iId           = 3; //Merchant
		$oRefineBy->sName         = 'Merchant';
		$oRefineByDefinitionArray = array();

		// merchantID
		if ( ! is_array( $options['merchantID'] ) ) {
			$options['merchantID'] = explode( ',', $options['merchantID'] );
		}
		foreach ( $options['merchantID'] as $merchantID ) {
			$oRefineByDefinition        = new \stdClass();
			$oRefineByDefinition->sId   = $merchantID;
			$oRefineByDefinition->sName = '';
			$oRefineByDefinitionArray[] = $oRefineByDefinition;
		}
		$oRefineBy->oRefineByDefinition     = $oRefineByDefinitionArray;
		$parameters['oActiveRefineByGroup'] = $oRefineBy;

		$response = $this->call( 'getProductList', $parameters );

		return json_decode( json_encode( $response ), true );
	}

	/**
	 * @link: http://wiki.affiliatewindow.com/index.php/GetProduct
	 */
	public function getProduct( $product_id, array $options = array() ) {
		$parameters = array();

		foreach ( $options as $key => $value ) {
			switch ( $key ) {
				case 'iAdult':
				case 'sColumnToReturn':
					$parameters[ $key ] = $value;
					break;
			}
		}

		// PHP Soap extensions used to only support 32 bit ints? casting to float seems to work.
		$parameters['iProductId'] = (float) $product_id;

		$response = $this->call( 'getProduct', $parameters );

		return json_decode( json_encode( $response ), true );
	}

	/**
	 * @link: http://wiki.affiliatewindow.com/index.php/GetMerchant
	 */
	public function getMerchant( $merchant_id, array $options = array() ) {
		$parameters = array();

		foreach ( $options as $key => $value ) {
			switch ( $key ) {
				case 'iAdult':
				case 'sColumnToReturn':
					$parameters[ $key ] = $value;
					break;
			}
		}
		$parameters['iMerchantId'] = (float) $merchant_id;

		$response = $this->call( 'getMerchant', $parameters );

		return json_decode( json_encode( $response ), true );
	}

}
