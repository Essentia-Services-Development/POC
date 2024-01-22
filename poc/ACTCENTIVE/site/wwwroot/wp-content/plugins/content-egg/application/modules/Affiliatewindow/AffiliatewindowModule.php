<?php

namespace ContentEgg\application\modules\Affiliatewindow;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\affiliatewindow\AffiliatewindowSoap;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * AffiliatewindowModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class AffiliatewindowModule extends AffiliateParserModule {

	private $api_client = null;
	private $merchants = array();

	public function info() {
		return array(
			'name'        => 'Affiliatewindow',
			'description' => __( 'Module adds products from Affiliatewindow.', 'content-egg' ),
		);
	}

	public function isDeprecated() {
		return true;
	}

	public function getParserType() {
		return self::PARSER_TYPE_PRODUCT;
	}

	public function defaultTemplateName() {
		return 'list';
	}

	public function isItemsUpdateAvailable() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		throw new \Exception( 'This module is deprecated. ProductServe API was closed.' );

		$options = array();

		if ( $is_autoupdate ) {
			$options['iLimit'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['iLimit'] = $this->config( 'entries_per_page' );
		}

		$fields = array(
			'sSort',
			'sMode',
			'bHotPick',
			'iAdult',
		);

		foreach ( $fields as $f ) {
			if ( $this->config( $f ) ) {
				$options[ $f ] = $this->config( $f );
			}
		}
		if ( $this->config( 'merchantID' ) ) {
			$options['merchantID'] = TextHelper::commaList( $this->config( 'merchantID' ) );
		}
		if ( $this->config( 'categoryID' ) ) {
			$options['categoryID'] = TextHelper::commaList( $this->config( 'categoryID' ) );
		}

		$sColumnToReturn            = array(
			'bHotPick',
			'iUpc',
			'iEan',
			'sMpn',
			'iIsbn',
			'sDescription',
			'sSpecification',
			'sPromotion',
			'sBrand',
			'sModel',
			'sAwImageUrl',
			'sMerchantImageUrl',
			'sDeliveryTime',
			'sCurrency',
			'fStorePrice',
			'fDeliveryCost',
			'sWarranty'
		);
		$options['sColumnToReturn'] = $sColumnToReturn;

		$results = $this->getApiClient()->getProductList( $keyword, $options );
		if ( ! is_array( $results ) || ! isset( $results['oProduct'] ) ) {
			return array();
		}

		$results = $results['oProduct'];
		if ( ! isset( $results[0] ) && isset( $results['iId'] ) ) {
			$results = array( $results );
		}

		return $this->prepareResults( $results );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content            = new ContentProduct;
			$content->unique_id = $r['iId'];
			$content->title     = $r['sName'];
			if ( ! empty( $r['sMerchantImageUrl'] ) ) {
				$content->img = $r['sMerchantImageUrl'];
			} else {
				$content->img = $r['sMerchantImageUrl'];
			}
			$content->url          = $r['sAwDeepLink'];
			$content->price        = $r['fPrice'];
			$content->currencyCode = $r['sCurrency'];
			$content->currency     = TextHelper::currencyTyping( $content->currencyCode );
			if ( ! empty( $r['sBrand'] ) ) {
				$content->manufacturer = $r['sBrand'];
			}

			if ( ! empty( $r['sDescription'] ) ) {
				$content->description = strip_tags( $r['sDescription'] );
				if ( $max_size = $this->config( 'description_size' ) ) {
					$content->description = TextHelper::truncate( $content->description, $max_size );
				}
			}

			$content->ean  = ( ! empty( $r['iEan'] ) ) ? $r['iEan'] : '';
			$content->mpn  = ( ! empty( $r['sMpn'] ) ) ? $r['sMpn'] : '';
			$content->upc  = ( ! empty( $r['iUpc'] ) ) ? $r['iUpc'] : '';
			$content->isbn = ( ! empty( $r['iIsbn'] ) ) ? $r['iIsbn'] : '';

			$content->extra = new ExtraDataAffiliatewindow;
			ExtraDataAffiliatewindow::fillAttributes( $content->extra, $r );

			// get merchant info
			$this->fillMerchantInfo( $content );
			$data[] = $content;
		}

		return $data;
	}

	private function fillMerchantInfo( $content ) {
		$merchant_id = $content->extra->iMerchantId;

		if ( ! isset( $this->merchants[ $merchant_id ] ) ) {

			$options                    = array();
			$options['sColumnToReturn'] = array( 'sName', 'sLogoUrl', 'sDisplayUrl' );
			try {
				$result = $this->getApiClient()->getMerchant( $merchant_id, $options );
			} catch ( \Exception $e ) {
				return;
			}

			if ( ! is_array( $result ) || ! isset( $result['oMerchant'] ) ) {
				return;
			}
			$this->merchants[ $merchant_id ] = $result['oMerchant'];
		}
		$content->logo     = str_replace( 'http://', 'https://', $this->merchants[ $merchant_id ]['sLogoUrl'] );
		$content->merchant = $this->merchants[ $merchant_id ]['sName'];
		$content->domain   = TextHelper::getHostName( $this->merchants[ $merchant_id ]['sDisplayUrl'] );
	}

	public function doRequestItems( array $items ) {
		throw new \Exception( 'This module is deprecated. ProductServe API was closed.' );

		foreach ( $items as $key => $item ) {
			$result = $this->getApiClient()->getProduct( $item['unique_id'] );

			if ( ! is_array( $result ) || ! isset( $result['oProduct'] ) ) {
				throw new \Exception( 'doRequestItems request error.' );
			}
			$result = $result['oProduct'];

			// assign new price
			$items[ $key ]['price'] = (float) $result['fPrice'];
		}

		return $items;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new AffiliatewindowSoap( $this->config( 'api_key' ) );
		}

		return $this->api_client;
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

}
