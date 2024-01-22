<?php

namespace ContentEgg\application\libs\flickr;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * FlickrApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2011 keywordrush.com
 *
 * REST Flickr API
 * @link: http://www.flickr.com/services/api/
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class FlickrApi extends RestClient {

	const API_URI_BASE = 'https://api.flickr.com/services/rest/';

	protected $_api_key;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'php_serial',
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct( $api_key, $responseType = 'php_serial' ) {
		$this->setApiKey( $api_key );
		$this->setResponseType( $responseType );
		$this->setUri( self::API_URI_BASE );
	}

	public function setApiKey( $api_key ) {
		$this->_api_key = $api_key;
	}

	public function getApiKey() {
		return $this->_api_key;
	}

	/**
	 * Поиск картинок по ключевому слову
	 *
	 * @param string $keywords
	 * @param array $options flickr api options
	 *
	 * @return array
	 * @link: http://www.flickr.com/services/api/flickr.photos.search.html
	 */
	public function photosSearch( $keywords, array $options ) {
		$options['text']    = $keywords;
		$options['api_key'] = $this->getApiKey();
		$options['format']  = $this->getResponseType();

		// Filter results by media type. Possible values
		// are all (default), photos or videos
		$options['media'] = 'photos';

		if ( isset( $options['per_page'] ) && $options['per_page'] > 500 ) {
			$options['per_page'] = 500;
		}

		$response = $this->restGet( '?method=flickr.photos.search', $options );

		return $this->_decodeResponse( $response );
	}

}
