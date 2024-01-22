<?php

namespace ContentEgg\application\libs\envato;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * EnvatoApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 *
 * @link: https://build.envato.com/api/
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class EnvatoApi extends RestClient {

	const API_URI_BASE = 'https://api.envato.com';

	protected $token;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'json',
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct( $tokent ) {
		$this->token = $tokent;
		$this->setUri( self::API_URI_BASE );
		$this->setResponseType( 'json' );
	}

	/**
	 * Search for items
	 * @link: https://build.envato.com/api/#search_GET_search_item_json
	 */
	public function search( $keywords, array $options ) {
		$options['term'] = $keywords;
		$response        = $this->restGet( '/v1/discovery/search/search/item', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * Returns all details of a particular item on Envato Market
	 * @link: https://build.envato.com/api/#market_0_Catalog_Item
	 */
	public function product( $item_id, $options = array() ) {
		$options['id'] = $item_id;
		$response      = $this->restGet( '/v3/market/catalog/item', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * Return available licenses and prices for the given item ID
	 * @link: https://build.envato.com/api/#market_ItemPrices
	 */
	public function price( $item_id, array $options ) {
		$response = $this->restGet( '/v1/market/item-prices:' . urlencode( $item_id ) . '.json', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$this->setCustomHeaders( array( 'Authorization' => 'Bearer ' . $this->token ) );

		return parent::restGet( $path, $query );
	}

}
