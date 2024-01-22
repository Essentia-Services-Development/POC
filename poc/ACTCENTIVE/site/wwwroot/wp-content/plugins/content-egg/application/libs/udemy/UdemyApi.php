<?php

namespace ContentEgg\application\libs\udemy;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * UdemyApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 *
 * @link: https://www.udemy.com/developers
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class UdemyApi extends RestClient {

	const API_URI_BASE = 'https://www.udemy.com/api-2.0';

	protected $client_id;
	protected $client_secret;
	protected static $useragent = 'Content Egg WP Plugin (https://www.keywordrush.com/contentegg)';

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
	public function __construct( $client_id, $client_secret ) {
		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;
		$this->setUri( self::API_URI_BASE );
		$this->setResponseType( 'json' );
	}

	/**
	 * Returns list of courses
	 * @link: https://www.udemy.com/developers/methods/get-courses-list/
	 */
	public function search( $keywords, array $options ) {
		$options['search'] = $keywords;
		$response          = $this->restGet( '/courses/', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * Returns course with specified pk
	 * @link: https://www.udemy.com/developers/methods/get-courses-list/
	 */
	public function product( $pk, array $options ) {
		$response = $this->restGet( '/courses/' . urlencode( $pk ) . '/', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$this->setCustomHeaders( array(
			'Accept'        => 'application/json, text/plain, */*',
			'Content-Type'  => 'application/json;charset=utf-8',
			'Authorization' => 'Basic ' . base64_encode( $this->client_id . ":" . $this->client_secret )
		) );

		return parent::restGet( $path, $query );
	}

}
