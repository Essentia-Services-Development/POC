<?php

namespace ContentEgg\application\libs\google;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * FreebaseRest class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
/**
 * FreebaseRest класс для работы с Freebase API.
 * @link: https://developers.google.com/freebase/index
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class FreebaseRest extends RestClient {

	const API_URI_BASE = 'https://www.googleapis.com/freebase/v1';

	private $apiKey;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'json',
	);

	/**
	 * Constructor
	 *
	 * @param string API Key
	 * @param string $responseType
	 */
	public function __construct( $key ) {
		$this->setUri( self::API_URI_BASE );
		$this->setApiKey( $key );
		$this->setResponseType( 'json' );
	}

	public function setApiKey( $key ) {
		$this->apiKey = $key;
	}

	/**
	 * Freebase Search API
	 * @link: https://developers.google.com/freebase/v1/search
	 *
	 * @param string $query
	 * @param array $params
	 */
	public function search( $query, $params ) {
		$_query          = array();
		$_query['query'] = $query;
		$_query['key']   = $this->apiKey;
		foreach ( $params as $key => $param ) {
			switch ( $key ) {
				case 'lang':
				case 'filter':
					$_query[ $key ] = $param;
					break;
				case 'limit':
				case 'start':
					$_query[ $key ] = ( (int) $param > 100 ) ? 100 : (int) $param;
					break;
			}
		}
		$response = $this->restGet( '/search', $_query );

		return $this->_decodeResponse( $response );
	}

	/**
	 * Freebase Topic API
	 * @link: https://developers.google.com/freebase/v1/topic-overview
	 *
	 * @param string $topic_id Topic ID
	 * @param string $filter
	 */
	public function topic( $topic_id, $lang = null, $filter = null ) {
		$_query        = array();
		$_query['key'] = $this->apiKey;
		if ( $lang ) {
			$_query['lang'] = $lang;
		}
		if ( $filter ) {
			$_query['filter'] = $filter;
		}
		$response = $this->restGet( '/topic' . $topic_id, $_query );

		return $this->_decodeResponse( $response );
	}

	/**
	 * Сначала находит ID топиков, а потом запрашивает подробную
	 * инфу по каждому топику.
	 *
	 * @param string $query
	 * @param array $params
	 * @param string $filter
	 */
	public function fullSearch( $query, $params, $filter = null ) {

		$results = $this->search( $query, $params );
		if ( empty( $results['result'] ) ) {
			return array();
		}

		$data = array();
		foreach ( $results['result'] as $res ) {
			if ( empty( $res['mid'] ) ) {
				continue;
			}
			try {
				if ( ! empty( $params['lang'] ) ) {
					$lang = $params['lang'];
				} else {
					$lang = null;
				}
				$topic = $this->topic( $res['mid'], $lang, $filter );
			} catch ( Exception $e ) {
				// Не получили инфу по топику. Пропускаем?
				continue;
			}
			$data[] = $topic;
		}

		return $data;
	}

}
