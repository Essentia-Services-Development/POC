<?php

namespace ContentEgg\application\libs\kelkoo;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * KelkooApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 *
 * @link: https://developers.kelkoogroup.com/
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class KelkooApi extends RestClient {

	const API_URI_BASE = 'https://api.kelkoogroup.net/publisher/shopping/v2';

	protected $token;
	protected $_responseTypes = array(
		'json',
		'xml',
	);

	public function __construct( $token ) {
		$this->token = $token;
		$this->setUri( self::API_URI_BASE );
		$this->setResponseType( 'json' );
	}

	/**
	 * Search offers
	 * @link: https://developers.kelkoogroup.com/app/documentation/navigate/_publisher/shoppingAPIPublic/_/_Features_OfferSearch/Request
	 */
	public function search( $keywords, array $options ) {
		$options['query'] = $keywords;
		$response         = $this->restGet( '/search/offers', $options );

		return $this->_decodeResponse( $response );
	}

	public function searchEan( $ean, array $options ) {
		$options['filterBy'] = 'codeEan:' . $ean;
		$response            = $this->restGet( '/search/offers', $options );

		return $this->_decodeResponse( $response );
	}

	public function offer( $offer_id, array $options ) {
		$options['filterBy'] = 'offerId:' . $offer_id;
		$response            = $this->restGet( '/search/offers', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * @link: https://developers.kelkoogroup.com/app/documentation/navigate/_publisher/shoppingAPIPublic/_/_Guides/AuthenticationWithJWTGuide
	 */
	public function restGet( $path, array $query = null ) {
		$this->setCustomHeaders( array(
			'Authorization' => 'Bearer ' . $this->token,
			'Content-Type'  => 'application/json'
		) );

		return parent::restGet( $path, $query );
	}

}
