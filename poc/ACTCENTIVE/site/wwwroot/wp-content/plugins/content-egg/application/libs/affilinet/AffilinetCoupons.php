<?php

namespace ContentEgg\application\libs\affilinet;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * AffilinetCoupons class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * @link: http://publisher.affili.net/Solutions/Webservices_Webservices.aspx?nr=1&pnp=54#Voucher
 * @link: http://publisher.affili.net/HtmlContent/de/downloads/Web%20Services/Documentation_SearchVoucherCodes.pdf
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class AffilinetCoupons extends RestClient {

	const API_URI_BASE = 'https://publisher-webservices.affili.net/Publisher/Inbox.asmx';

	protected $password;
	protected $publisher_id;

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
	public function __construct( $password, $publisher_id ) {
		$this->setPassword( $password );
		$this->setPublisherId( $publisher_id );
		$this->setResponseType( 'xml' );
		$this->setUri( self::API_URI_BASE );
	}

	public function setPassword( $password ) {
		$this->password = $password;
	}

	public function setPublisherId( $publisher_id ) {
		$this->publisher_id = $publisher_id;
	}

	/**
	 * @link: http://publisher.affili.net/Solutions/Webservices_Webservices.aspx?nr=1&pnp=54#Voucher
	 */
	public function search( $keywords, array $options ) {
		$options['PublisherId'] = $this->publisher_id;
		$options['Password']    = $this->password;
		$options['Query']       = $keywords;

		// All fields are required
		$defaults = array(
			'ProgramId'           => - 1,
			'VoucherCode'         => '',
			'VoucherCodeContent'  => - 1,
			'StartDate'           => '',
			'EndDate'             => '',
			'VoucherType'         => - 1,
			'PartnershipStatus'   => 0,
			'MinimumOrderValue'   => - 1,
			'CustomerRestriction' => 0,
			'ExclusivesOnly'      => 'false',
			'CurrentPage'         => 1,
			'PageSize'            => 10,
			'OrderBy'             => 1,
			'SortDesc'            => 'true',
		);

		foreach ( $defaults as $key => $value ) {
			if ( ! isset( $options[ $key ] ) || $options[ $key ] === '' ) {
				$options[ $key ] = $value;
			}
		}
		$response = $this->restGet( '/SearchVoucherCodes', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * @link: http://publisher.affili.net/HtmlContent/de/downloads/Web%20Services/Documentation_GetPrograms.pdf
	 * @todo
	 */
	public function getPrograms() {
		throw new \Exception( 'This method not implemented yet.' );
	}

}
