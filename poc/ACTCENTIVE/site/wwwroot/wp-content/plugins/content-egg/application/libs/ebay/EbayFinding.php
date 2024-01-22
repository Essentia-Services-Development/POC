<?php

namespace ContentEgg\application\libs\ebay;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * EbayFinding class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * Ebay Finding API
 * @link: http://developer.ebay.com/DevZone/finding/Concepts/FindingAPIGuide.html
 */
class EbayFinding extends RestClient {

	const API_URI_BASE = 'https://svcs.ebay.com/services/search/FindingService/v1';
	const SERVICE_VERSION = '1.10.0';
	const MESSAGE_ENCODING = 'UTF-8';

	private $global_id;
	private $app_id;
	protected $_responseTypes = array(
		'json',
		'xml'
	);

	public function __construct( $app_id, $global_id = 'EBAY-US', $responseType = 'xml' ) {
		$this->app_id    = $app_id;
		$this->global_id = $global_id;
		$this->setResponseType( $responseType );
		$this->setUri( self::API_URI_BASE );
	}

	/**
	 * Finds items by a keyword
	 * @link: http://developer.ebay.com/DevZone/finding/CallRef/findItemsAdvanced.html
	 */
	public function findItemsAdvanced( $keywords, $options = array() ) {
		$options['keywords'] = $keywords;
		$response            = $this->restGet( 'findItemsAdvanced', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $options = null ) {
		// default outputSelector
		$output_selector = array(
			'outputSelector' => array( 'GalleryInfo', 'PictureURLLarge', 'PictureURLSuperSize', 'UnitPriceInfo' )
		);

		// generate default options
		// constructor load global-id and application-id values
		$default = array(
			'OPERATION-NAME'       => $path,
			'MESSAGE-ENCODING'     => self::MESSAGE_ENCODING,
			'SERVICE-VERSION'      => self::SERVICE_VERSION,
			'GLOBAL-ID'            => $this->global_id,
			'SECURITY-APPNAME'     => $this->app_id,
			'RESPONSE-DATA-FORMAT' => strtoupper( $this->getResponseType() ),
			'REST-PAYLOAD'         => '',
			//	use this parameter to separate the payload part of the URL from the standard headers.
		);

		// prepare options to ebay syntax
		$options = $default +
		           $this->_optionsToNameValueSyntax( $options ) +
		           $this->_optionsToNameValueSyntax( $output_selector );


		return parent::restGet( '', $options );
	}

	/**
	 * Implements Name-value Syntax translator.
	 *
	 * Example:
	 *
	 * array(
	 *     'paginationInput' => array(
	 *         'entriesPerPage' => 5,
	 *         'pageNumber'     => 2
	 *     ),
	 *     'itemFilter' => array(
	 *         array(
	 *             'name'       => 'MaxPrice',
	 *             'value'      => 25,
	 *             'paramName'  => 'Currency',
	 *             'paramValue' => 'USD'
	 *         ),
	 *         array(
	 *             'name'  => 'FreeShippingOnly',
	 *             'value' => true
	 *         ),
	 *         array(
	 *             'name'  => 'ListingType',
	 *             'value' => array(
	 *                 'AuctionWithBIN',
	 *                 'FixedPrice',
	 *                 'StoreInventory'
	 *             )
	 *         )
	 *     ),
	 *     'productId' => array(
	 *         ''     => 123,
	 *         'type' => 'UPC'
	 *     )
	 * )
	 *
	 * this above is translated to
	 *
	 * array(
	 *     'paginationInput.entriesPerPage' => '5',
	 *     'paginationInput.pageNumber'     => '2',
	 *     'itemFilter(0).name'             => 'MaxPrice',
	 *     'itemFilter(0).value'            => '25',
	 *     'itemFilter(0).paramName'        => 'Currency',
	 *     'itemFilter(0).paramValue'       => 'USD',
	 *     'itemFilter(1).name'             => 'FreeShippingOnly',
	 *     'itemFilter(1).value'            => '1',
	 *     'itemFilter(2).name'             => 'ListingType',
	 *     'itemFilter(2).value(0)'         => 'AuctionWithBIN',
	 *     'itemFilter(2).value(1)'         => 'FixedPrice',
	 *     'itemFilter(2).value(2)'         => 'StoreInventory',
	 *     'productId'                      => '123',
	 *     'productId.@type'                => 'UPC'
	 * )
	 *
	 * @param  $options array
	 *
	 * @return array A simple array of strings
	 * @link   http://developer.ebay.com/DevZone/finding/Concepts/MakingACall.html#nvsyntax
	 */
	protected function _optionsToNameValueSyntax( array $options ) {
		ksort( $options );
		$new      = array();
		$runAgain = false;
		foreach ( $options as $name => $value ) {
			if ( is_array( $value ) ) {
				// parse an array value, check if it is associative
				$keyRaw    = array_keys( $value );
				$keyNumber = range( 0, count( $value ) - 1 );
				$isAssoc   = count( array_diff( $keyRaw, $keyNumber ) ) > 0;
				// check for tag representation, like <name att="sometinhg"></value>
				// empty key refers to text value
				// when there is a root tag, attributes receive flags
				$hasAttribute = array_key_exists( '', $value );
				foreach ( $value as $subName => $subValue ) {
					// generate new key name
					if ( $isAssoc ) {
						// named keys
						$newName = $name;
						if ( $subName !== '' ) {
							// when $subName is empty means that current value
							// is the main value for the main key
							$glue    = $hasAttribute ? '.@' : '.';
							$newName .= $glue . $subName;
						}
					} else {
						// numeric keys
						$newName = $name . '(' . $subName . ')';
					}
					// save value
					if ( is_array( $subValue ) ) {
						// it is necessary run this again, value is an array
						$runAgain = true;
					} else {
						// parse basic type
						$subValue = $this->toEbayValue( $subValue );
					}
					$new[ $newName ] = $subValue;
				}
			} else {
				// parse basic type
				$new[ $name ] = $this->toEbayValue( $value );
			}
		}
		if ( $runAgain ) {
			// this happens if any $subValue found is an array
			$new = $this->_optionsToNameValueSyntax( $new );
		}

		return $new;
	}

	/**
	 * Translate native PHP values format to ebay format for request.
	 *
	 * Boolean is translated to "0" or "1"
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function toEbayValue( $value ) {
		if ( is_bool( $value ) ) {
			$value = $value ? '1' : '0';
		} else {
			$value = (string) $value;
		}

		return $value;
	}

}
