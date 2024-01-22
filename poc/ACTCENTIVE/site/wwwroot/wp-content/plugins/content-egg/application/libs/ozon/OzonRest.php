<?php

namespace ContentEgg\application\libs\ozon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * OzonRest class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * @link: https://www.ozon.ru/context/partner_xml/
 * @link: http://mmedia.ozon.ru/multimedia/download/api_2.1_120115.pdf
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class OzonRest extends RestClient {

	const API_URI_BASE = 'https://ows.ozon.ru';

	/**
	 * @var array required login and pass for any request
	 */
	protected $_loginParams = array();

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'xml',
		'json',
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct( $login, $pass ) {
		$this->_loginParams['login']    = $login;
		$this->_loginParams['password'] = $pass;
		$this->setResponseType( 'json' );
		$this->setUri( self::API_URI_BASE );
	}

	/**
	 * SearchItemsGet - поиск товаров по ключевому слову и критериям поиска
	 * @link: https://ows.ozon.ru/PartnerService/SearchService/help/operations/SearchItemsGet
	 */
	public function search( $query, array $params = array() ) {
		$params['pageNumber'] = 1;
		$params['searchText'] = $query;
		$params               += $this->_loginParams;
		$response             = $this->restGet( '/PartnerService/SearchService/SearchItemsGet/', $params );

		return $this->_decodeResponse( $response );
	}

	/**
	 * DetailGet
	 * получаем детали по всем товарам, картинки, характеристики и проч.
	 * @link: https://ows.ozon.ru/PartnerService/DetailService/help/operations/DetailGet
	 * https://ows.ozon.ru/PartnerService/DetailService/DetailGet/?login={login}&password={password}&detailId={detailId}
	 */
	public function details( $item_id ) {
		$params             = array();
		$params             += $this->_loginParams;
		$params['detailId'] = $item_id;

		$response = $this->restGet( '/PartnerService/DetailService/DetailGet/', $params );

		return $this->_decodeResponse( $response );
	}

	/**
	 * DetailCommentsGet
	 * получаем отзывы по товару
	 * @link: https://ows.ozon.ru/PartnerService/DetailService/help/operations/DetailCommentsGet
	 * https://ows.ozon.ru/PartnerService/DetailService/DetailCommentsGet/?login={login}&password={password}&detailId={detailId}&sortTag={sortTag}&pageNumber={pageNumber}&itemsOnPage={itemsOnPage}
	 */
	public function reviews( $params ) {
		$params               += $this->_loginParams;
		$params['pageNumber'] = 1;

		$response = $this->restGet( '/PartnerService/DetailService/DetailCommentsGet/', $params );

		return $this->_decodeResponse( $response );
	}

	/**
	 * ItemPriceGet
	 * @link: https://ows.ozon.ru/PartnerService/ItemService/help/operations/ItemPriceGet
	 * https://ows.ozon.ru/PartnerService/ItemService/ItemPriceGet/?login={login}&password={password}&partnerClientId={partnerClientId}&itemId={itemId}
	 */
	public function price( $item_id ) {
		$params           = array();
		$params           += $this->_loginParams;
		$params['itemId'] = $item_id;

		$response = $this->restGet( '/PartnerService/ItemService/ItemPriceGet/', $params );

		return $this->_decodeResponse( $response );
	}

}
