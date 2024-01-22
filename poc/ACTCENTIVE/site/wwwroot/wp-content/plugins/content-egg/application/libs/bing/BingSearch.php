<?php

namespace ContentEgg\application\libs\bing;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * BingSearch class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * @link: https://msdn.microsoft.com/en-us/library/dd251056.aspx
 * @link: https://onedrive.live.com/view.aspx?resid=9C9479871FBFA822!112&app=Word&authkey=!ANNnJQREB0kDC04
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class BingSearch extends RestClient {

	const API_URI_BASE = 'https://api.datamarket.azure.com';

	private $accountKey = null;
	private $serviceOperation = 'Web';

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'json'
	);

	/**
	 * Constructor
	 */
	public function __construct( $accountKey, $responseType = 'json' ) {
		$this->setAccountKey( $accountKey );
		$this->setResponseType( $responseType );
		$this->setUri( self::API_URI_BASE );
	}

	public function setAccountKey( $accountKey ) {
		$this->accountKey = $accountKey;
	}

	public function getAccountKey() {
		return $this->accountKey;
	}

	public function setServiceOperation( $serviceOperation = 'Web' ) {
		//@todo: Composite Service Operation
		if ( ! in_array( $serviceOperation, array(
			'Web',
			'Image',
			'News',
			'RelatedSearch',
			'SpellingSuggestion',
			'Video'
		) ) ) {
			throw new \Exception( 'Wrong Service Operation param.' );
		}
		$this->serviceOperation = $serviceOperation;
	}

	public function getServiceOperation() {
		return $this->serviceOperation;
	}

	public function search( $query, $source = 'Web', array $params = array() ) {
		$this->setServiceOperation( $source );

		$_query          = array();
		$_query['Query'] = $query;
		// Specifies the format of the OData response. Current options are Atom (for XML) or JSON.
		$_query['$format'] = $this->getResponseType();
		foreach ( $params as $key => $param ) {
			switch ( $key ) {
				case '$top': // Specifies the number of results to return.
					$_query[ $key ] = ( (int) $param > 50 ) ? 50 : (int) $param;
					break;
				case '$skip': // Specifies the offset requested for the starting point of results returned.
					$_query[ $key ] = ( (int) $param > 1000 ) ? 1000 : (int) $param;
					break;

				case 'Market':
					$_query[ $key ] = $param;
					break;
				case 'Adult':
					$_query[ $key ] = ( ! in_array( $param, array(
						'Off',
						'Moderate',
						'Strict'
					) ) ) ? 'Moderate' : $param;
					break;
				case 'ImageFilters':
					// format: &Image.Filters=Color:Monochrome&Image.Filters=Style:Photo
					$_query[ $key ] = $param;
					break;
				case 'NewsSortBy': //for News
				case 'NewsCategory': //for News
				case 'WebFileType': //for Web
					$_query[ $key ] = $param;
					break;
			}
		}

		$add_url = '';

		foreach ( $_query as $k => $q ) {
			if ( ! strstr( $k, '$' ) ) {
				$_query[ $k ] = "'" . $q . "'";
			}
		}

		//$add_url = "?ImageFilters='Style:Photo'&ImageFilters='Size:Small'";
		// Bing API Basic Authorization
		$this->setCustomHeaders( array( 'Authorization' => 'Basic ' . base64_encode( $this->getAccountKey() . ":" . $this->getAccountKey() ) ) );

		$response = $this->restGet( '/Bing/Search/' . $this->getServiceOperation() . $add_url, $_query );

		return $this->_decodeResponse( $response );
	}

}
