<?php

namespace ContentEgg\application\libs\daisycon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * DaisyconApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 *
 * @link: https://developers.daisycon.com/api/
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class DaisyconApi extends RestClient {

	const API_URI_BASE = 'https://services.daisycon.com';

	protected $publisher_id;
	protected $username;
	protected $password;
	protected $_responseTypes = array(
		'json',
	);

	public function __construct( $publisher_id, $username, $password ) {
		$this->publisher_id = $publisher_id;
		$this->username     = $username;
		$this->password     = $password;
		$this->setUri( self::API_URI_BASE );
		$this->setResponseType( 'json' );
	}

	public function programs( $program_ids ) {
		if ( ! is_array( $program_ids ) ) {
			$program_ids = array( $program_ids );
		}

		$options  = array(
			'page'       => 1,
			'per_page'   => count( $program_ids ),
			'program_id' => join( ',', $program_ids ),
		);
		$response = $this->restGet( '/publishers/' . urlencode( $this->publisher_id ) . '/programs', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$this->setCustomHeaders( array( 'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->password ) ) );

		return parent::restGet( $path, $query );
	}

}
