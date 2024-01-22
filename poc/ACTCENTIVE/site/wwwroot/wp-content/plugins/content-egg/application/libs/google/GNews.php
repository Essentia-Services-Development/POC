<?php

namespace ContentEgg\application\libs\google;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * GNews class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class GNews extends RestClient {

	const API_URI_BASE = 'https://news.google.com';

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'rss'
	);

	//protected $_uri = 'https://news.google.com/news?hl={lang}&ie=UTF-8&output=rss&q={keyword}';

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct( $responseType = 'rss' ) {
		$this->setResponseType( $responseType );
		$this->setUri( self::API_URI_BASE );
	}

	public function search( $query, array $params = array(), $count = 10 ) {
		$_query           = array();
		$_query['q']      = $query;
		$_query['ie']     = 'UTF-8';
		$_query['output'] = $this->getResponseType();

		foreach ( $params as $key => $param ) {
			switch ( $key ) {
				case 'hl':
					if ( $param == 'br' || $param == 'BR' ) {
						$param = 'pt-BR';
					}
					$_query[ $key ] = $param;
					break;
			}
		}
		$response = $this->restGet( '/news', $_query );
		$response = str_replace( '<media:content', '<media', $response );
		$response = $this->_decodeResponse( $response );

		return $this->_prepareResults( $response, $count );
	}

	protected function _prepareResults( $data, $count = 10 ) {
		if ( ! isset( $data['channel']['item'] ) ) {
			return array();
		}
		$data = $data['channel']['item'];

		if ( ! isset( $data[0] ) && isset( $data['title'] ) ) {
			$data = array( 0 => $data );
		}

		$data = array_slice( $data, 0, $count );

		$results = array();
		foreach ( $data as $k => $g ) {
			// 'title' => 'This RSS feed URL is deprecated' December 1, 2017
			if ( $g['link'] == 'https://news.google.com/news' ) {
				continue;
			};

			$result = array();

			$result['title']  = strip_tags( $g['title'] );
			$result['url']    = strip_tags( $g['link'] );
			$result['source'] = strip_tags( $g['source'] );
			if ( preg_match( '/<p>.+/', $g['description'], $matches ) ) {
				$result['description'] = $matches[0];
			} else {
				$result['description'] = $g['description'];
			}
			$result['description'] = trim( \wp_strip_all_tags( $result['description'] ) );
			$result['description'] = html_entity_decode( $result['description'] );
			$result['description'] = str_replace( "...", "", $result['description'] );

			$result['date'] = preg_replace( "/\sGMT/", "", $g['pubDate'] );
			$result['date'] = strtotime( $result['date'] );
			if ( isset( $g['media'] ) && $g['media']['@attributes']['medium'] == 'image' ) {
				$result['img'] = $g['media']['@attributes']['url'];
			}


			$results[] = $result;
		}

		return $results;
	}

}
