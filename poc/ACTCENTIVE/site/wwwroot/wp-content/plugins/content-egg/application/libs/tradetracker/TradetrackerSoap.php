<?php

namespace ContentEgg\application\libs\tradetracker;

defined( '\ABSPATH' ) || exit;

/**
 * AffiliatewindowSoap class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 *
 * @link: https://affiliate.tradetracker.com/webService
 */
class TradetrackerSoap {

	const WSDL_URI1 = 'http://ws.tradetracker.com/soap/affiliate?wsdl';
	const WSDL_URI2 = 'https://ws.tradetracker.com/soap/affiliate?wsdl';

	protected $soapClient;
	protected $affiliateSiteID;

	/**
	 * @var array Options for soap calling
	 */
	protected $options = array(
		'connection_timeout' => 15,
		'trace'              => false,
	);

	public function __construct( $customerID, $passphrase, $locale, $affiliateSiteID = null ) {
		if ( ! class_exists( '\SoapClient', false ) ) {
			throw new \Exception( 'You are missing the PHP SoapClient. You need to install the PHP SOAP extension.' );
		}

		if ( $affiliateSiteID ) {
			$this->setAffiliateSiteID( $affiliateSiteID );
		}

		if ( version_compare( PHP_VERSION, '7.3', '>=' ) ) {
			$wsfl = self::WSDL_URI2;
		} else {
			$wsfl = self::WSDL_URI1;
		}

		try {
			$this->soapClient = new \SoapClient( $wsfl, $this->options );
			$this->soapClient->authenticate( $customerID, $passphrase, $sandbox = false, $locale, $demo = false );
		} catch ( \SoapFault $fault ) {
			throw new \Exception( "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring} )" );
		}
	}

	public function setAffiliateSiteID( $id ) {
		$this->affiliateSiteID = $id;
	}

	/**
	 * @link: https://affiliate.tradetracker.com/webService/index/method/getFeedProducts
	 */
	public function getFeedProducts( $keywords, array $options ) {
		$options['query'] = $keywords;

		try {
			$response = $this->soapClient->getFeedProducts( $this->affiliateSiteID, $options );
		} catch ( \SoapFault $fault ) {
			throw new \Exception( "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})" );
		}

		return json_decode( json_encode( $response ), true );
	}

	protected function getMaterialIncentiveItems( $methodName, $options, $outputType = 'html' ) {
		if ( ! $this->affiliateSiteID ) {
			throw new \Exception( "AffiliateSiteID can not be empty." );
		}

		try {
			$response = $this->soapClient->$methodName( $this->affiliateSiteID, $outputType, $options );
		} catch ( \SoapFault $fault ) {
			throw new \Exception( "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})" );
		}

		return json_decode( json_encode( $response ), true );
	}

	/**
	 * @link: https://affiliate.tradetracker.com/webService/index/method/getMaterialIncentiveVoucherItems
	 */
	public function getMaterialIncentiveVoucherItems( $keywords, array $options, $outputType = 'html' ) {
		$options['query'] = $keywords;

		return $this->getMaterialIncentiveItems( 'getMaterialIncentiveVoucherItems', $options, $outputType );
	}

	public function getMaterialIncentiveTextItems( $keywords, array $options, $outputType = 'html' ) {
		$options['query'] = $keywords;

		return $this->getMaterialIncentiveItems( 'getMaterialTextItems', $options, $outputType );
	}

	public function getMaterialIncentiveOfferItems( $keywords, array $options, $outputType = 'html' ) {
		$options['query'] = $keywords;

		return $this->getMaterialIncentiveItems( 'getMaterialIncentiveOfferItems', $options, $outputType );
	}

}
