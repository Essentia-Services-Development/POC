<?php

namespace ContentEgg\application\libs\yandex;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * MarketContentApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * @link: https://tech.yandex.ru/market/content/doc/dg/concepts/about-docpage/
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class MarketContentApi extends RestClient implements MarketContentInterface {

	const API_URI_BASE = 'https://api.content.market.yandex.ru/v1';

	private $apiKey;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'json',
		'xml'
	);

	/**
	 * Constructor
	 *
	 * @param string API Key
	 * @param string $responseType
	 */
	public function __construct( $key, $rp = 'json' ) {
		$this->setUri( self::API_URI_BASE );
		$this->setApiKey( $key );
		$this->setResponseType( $rp );
	}

	public function setApiKey( $key ) {
		$this->apiKey = $key;
	}

	public function getApiKey() {
		return $this->apiKey;
	}

	/**
	 * Market Search API
	 * @link: https://tech.yandex.ru/market/content/doc/dg/reference/search-docpage/
	 */
	public function search( $query, $params ) {
		$_query         = array();
		$_query['text'] = $query;

		foreach ( $params as $key => $param ) {
			switch ( $key ) {
				case 'geo_id':
				case 'remote_ip':
				case 'adult':
				case 'boundary':
				case 'category_id':
				case 'check_spelling':
				case 'delivery':
				case 'how':
				case 'latitude':
				case 'longitude':
				case 'onstock':
				case 'price_max':
				case 'price_min':
				case 'page':
				case 'shipping':
				case 'shop_id':
				case 'sort':
				case 'warranty':
				case 'yamoney':
					$_query[ $key ] = $param;
					break;
				case 'count':
					$_query[ $key ] = ( (int) $param > 30 ) ? 30 : (int) $param;
					break;
			}
		}

		// @debug
		if ( isset( $_SERVER['BRUSH_SERVER_CODE'] ) ) {
			$response = include dirname( __FILE__ ) . '/debug/search_data.php';
		} else {
			$response = $this->restGet( '/search.' . $this->getResponseType(), $_query );
		}

		return $this->_decodeResponse( $response );
	}

	/**
	 * Список характеристик модели
	 * @link: https://tech.yandex.ru/market/content/doc/dg/reference/model-id-details-docpage/
	 */
	public function details( $model_id, $params = array() ) {
		//@todo: {"errors":["Forbidden resource"]}
		throw new \Exception( 'Details method do not implemented yet.' );

		$_query = array();
		foreach ( $params as $key => $param ) {
			switch ( $key ) {
				case 'details_set':
					$_query[ $key ] = $param;
					break;
			}
		}
		// @debug
		if ( isset( $_SERVER['BRUSH_SERVER_CODE'] ) ) {
			$response = include dirname( __FILE__ ) . '/debug/details_data.php';
		} else {
			$response = $this->restGet( '/model/' . $model_id . '/details.' . $this->getResponseType(), $_query );
		}

		return $this->_decodeResponse( $response );
	}

	/**
	 * Отзывы о модели
	 * @link: https://tech.yandex.ru/market/content/doc/dg/reference/model-id-opinion-docpage/
	 */
	public function opinions( $model_id, $params = array() ) {
		$_query = array();
		foreach ( $params as $key => $param ) {
			switch ( $key ) {
				case 'sort':
				case 'how':
					$_query[ $key ] = $param;
					break;
				case 'grade':
				case 'max_comments':
				case 'page':
				case 'count':
					$_query[ $key ] = (int) $param;
					break;
			}
		}
		// @debug
		if ( isset( $_SERVER['BRUSH_SERVER_CODE'] ) ) {
			$response = include dirname( __FILE__ ) . '/debug/opinions_data.php';
		} else {
			$response = $this->restGet( '/model/' . $model_id . '/opinion.' . $this->getResponseType(), $_query );
		}

		return $this->_decodeResponse( $response );
	}

	/**
	 * Список предложений на модель
	 * @link: https://tech.yandex.ru/market/content/doc/dg/reference/model-id-offers-docpage/
	 */
	public function offers( $model_id, $params = array() ) {
		$_query = array();
		foreach ( $params as $key => $param ) {
			switch ( $key ) {
				case 'remote_ip':
				case 'geo_id':
				case 'delivery':
				case 'groupBy':
				case 'how':
				case 'latitude':
				case 'longitude':
				case 'page':
				case 'shipping':
				case 'shop_id':
				case 'sort':
					$_query[ $key ] = $param;
					break;
				case 'count':
					$_query[ $key ] = (int) $param;
					break;
			}
		}
		// @debug
		if ( isset( $_SERVER['BRUSH_SERVER_CODE'] ) ) {
			$response = include dirname( __FILE__ ) . '/debug/offers_data.php';
		} else {
			$response = $this->restGet( '/model/' . $model_id . '/offers.' . $this->getResponseType(), $_query );
		}

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		// api auth
		$this->setCustomHeaders( array( 'Authorization' => $this->getApiKey() ) );

		return parent::restGet( $path, $query );
	}

}
