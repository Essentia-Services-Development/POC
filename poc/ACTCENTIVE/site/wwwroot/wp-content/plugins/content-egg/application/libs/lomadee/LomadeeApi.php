<?php

namespace ContentEgg\application\libs\lomadee;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * LomadeeApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 *
 * @link: https://developer.lomadee.com/afiliados/ofertas/
 * @link: https://developer.lomadee.com/afiliados/
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class LomadeeApi extends RestClient {

	protected static $timeout = 120; //sec

	const API_URI_BASE = 'https://api.lomadee.com';

	protected $sourceId;
	protected $token;
	protected $_responseTypes = array(
		'json',
	);

	public function __construct( $token, $sourceId ) {
		$this->token    = $token;
		$this->sourceId = $sourceId;
		$this->setUri( self::API_URI_BASE );
		$this->setResponseType( 'json' );
	}

	/**
	 * Search for offers
	 * @link: https://developer.lomadee.com/afiliados/ofertas/recursos-v3/buscar-ofertas/
	 */
	public function offers( $keywords, array $options ) {
		$options['keyword'] = $keywords;
		$response           = $this->restGet( '/v3/{token}/offer/_search', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * Get offer
	 * @link: https://developer.lomadee.com/afiliados/ofertas/recursos-v3/buscar-ofertas/
	 */
	public function offer( $offer_id, $store_id, $options = array() ) {
		$options['storeId'] = $store_id; // Required param
		$response           = $this->restGet( '/v3/{token}/offer/_id/' . urlencode( $offer_id ), $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * Search for coupons
	 * @link: https://developer.lomadee.com/afiliados/cupons/recursos/buscar-cupons/
	 */
	public function coupons( $keywords, array $options ) {
		$options['keyword'] = $keywords;

		$response = $this->restGet( '/v2/{token}/coupon/_all', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * https://developer.lomadee.com/afiliados/deeplink/recursos/criar-deeplink/
	 */
	public function deeplink( $urls, array $options = array() ) {
		if ( ! is_array( $urls ) ) {
			$urls = array( $urls );
		}

		$path_urls = array();
		foreach ( $urls as $url ) {
			$path_urls[] = 'url=' . urlencode( $url );
		}
		$path = '/v2/{token}/deeplink/_create?' . join( '&', $path_urls );

		$response = $this->restGet( $path, $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$path              = str_replace( '{token}', $this->token, $path );
		$query['sourceId'] = $this->sourceId;

		return parent::restGet( $path, $query );
	}

}
