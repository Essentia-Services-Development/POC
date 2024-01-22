<?php

namespace ContentEgg\application\libs\shareasale;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * ShareasaleApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class ShareasaleApi extends RestClient {

	protected static $timeout = 30; //sec

	const API_URI_BASE = 'https://api.shareasale.com/x.cfm';
	const API_VERSION = '2.0';

	protected $token;
	protected $api_secret;

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'xml',
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct( $token, $api_secret ) {
		$this->token      = $token;
		$this->api_secret = $api_secret;

		$this->setResponseType( 'xml' );
		$this->setUri( self::API_URI_BASE );
	}

	public function products( $keywords, array $options ) {
		$options['action']  = 'getProducts';
		$options['keyword'] = $keywords;
		$response           = $this->restGet( '', $options );
		$result             = $this->_decodeResponse( $response );

		if ( ! $result && strstr( $response, 'Error Code' ) ) {
			throw new \Exception( trim( strip_tags( $response ) ) );
		}

		return $result;
	}

	public function restGet( $path, array $query = null ) {
		$query['XMLFormat'] = 1;
		$query['token']     = $this->token;
		$query['version']   = self::API_VERSION;

		$date    = gmdate( 'D, d M Y H:i:s T' );
		$sig     = $this->token . ':' . $date . ':' . $query['action'] . ':' . $this->api_secret;
		$sigHash = strtoupper( hash( "sha256", $sig ) );
		$this->setCustomHeaders( array( 'x-ShareASale-Date' => $date, 'x-ShareASale-Authentication' => $sigHash ) );

		return parent::restGet( $path, $query );
	}

}
