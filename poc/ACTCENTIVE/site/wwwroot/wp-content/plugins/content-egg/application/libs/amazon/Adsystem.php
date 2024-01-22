<?php

namespace ContentEgg\application\libs\amazon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 *
 */
class Adsystem extends RestClient {

	private $locale;
	protected $_responseTypes = array(
		'json',
	);

	public function __construct( $locale = 'us' ) {
		$this->setResponseType( 'json' );
		$this->setLocale( $locale );
	}

	public function setLocale( $locale ) {
		$this->locale = $locale;
		$this->setUri( AmazonLocales::getAdsystemEndpoint( $this->locale ) );
	}

	public function getLocale() {
		return $this->locale;
	}

	public function search( $keyword ) {
		$payload             = array();
		$payload['Keywords'] = $keyword;

		$locale = $this->getLocale();
		if ( $locale == 'uk' ) {
			$locale = 'gb';
		}
		$payload['MarketPlace']    = strtoupper( $locale );
		$payload['callback']       = 'search_callback';
		$payload['Operation']      = 'GetResults';
		$payload['TemplateId']     = 'MobileSearchResults';
		$payload['ServiceVersion'] = '20070822';
		$payload['InstanceId']     = '';
		$payload['dataType']       = 'jsonp';

		$response = $this->restGet( '/widgets/q', $payload );

		return $this->_decodeResponse( $this->fixSearchResponse( $response ) );
	}

	public function getItem( $asin ) {
		$products = $this->search( $asin );

		if ( empty( $products['results'] ) || ! isset( $products['results'][0] ) ) {
			return false;
		}

		if ( $products['results'][0]['ASIN'] != $asin ) {
			return false;
		}

		return $products['results'][0];
	}

	public function itemSearch( $keyword, array $payload ) {
		$payload['Keywords']       = $keyword;
		$payload['Operation']      = 'ItemSearch';
		$payload['TemplateId']     = 'PubStudio';
		$payload['ServiceVersion'] = '20070822';
		$payload['InstanceId']     = '';
		$payload['dataType']       = 'json';
		$payload['SearchIndex']    = 'All';
		$payload['multipageStart'] = 0;

		$response = $this->restGet( '/q', $payload );

		return $this->_decodeResponse( $response );
	}

	private function fixSearchResponse( $response ) {
		$response = trim( $response );
		$response = str_replace( 'search_callback', '', $response );
		$response = trim( $response, "()" );
		$response = preg_replace( '/(\w+) :/i', '"$1" :', $response );
		$response = str_replace( 'MarketPlace: ', '"MarketPlace": ', $response );
		$response = str_replace( 'InstanceId: ', '"InstanceId": ', $response );

		return $response;
	}

}
