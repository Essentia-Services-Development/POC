<?php

namespace ContentEgg\application\modules\TradedoublerProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\tradedoubler\TradedoublerProducts;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * TradedoublerProductsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class TradedoublerProductsModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Tradedoubler Products',
			'description' => __( 'Module adds products from Tradedoubler. You must have approval from each program separately.', 'content-egg' ),
		);
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
		$options = array();

		if ( $is_autoupdate ) {
			$options['limit'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['limit'] = $this->config( 'entries_per_page' );
		}

		$fields = array(
			'minPrice',
			'maxPrice',
			'language',
			'currency',
			'orderBy',
		);

		foreach ( $fields as $f ) {
			if ( $this->config( $f ) ) {
				$options[ $f ] = $this->config( $f );
			}
		}

		if ( ! empty( $query_params['minPrice'] ) ) {
			$options['minPrice'] = $query_params['minPrice'];
		}
		if ( ! empty( $query_params['maxPrice'] ) ) {
			$options['maxPrice'] = $query_params['maxPrice'];
		}

		if ( $this->config( 'fid' ) ) {
			$options['fid'] = TextHelper::commaList( $this->config( 'fid' ) );
		}

		if ( $this->config( 'tdCategoryId' ) ) {
			$options['tdCategoryId'] = (int) $this->config( 'tdCategoryId' );
		}

		if ( TextHelper::isEan( $keyword ) ) {
			$results = $this->getApiClient()->searchEan( $keyword, $options );
		} // EAN search
		else {
			$results = $this->getApiClient()->search( $keyword, $options );
		} // keyword search
		if ( empty( $results['products'] ) || ! is_array( $results ) ) {
			return array();
		}

		$results = $results['products'];
		if ( ! isset( $results[0] ) && isset( $results['name'] ) ) {
			$results = array( $results );
		}

		return $this->prepareResults( $results );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content        = new ContentProduct;
			$content->title = $r['name'];

			if ( $r['productImage']['url'] ) {
				$imgs         = explode( ',', $r['productImage']['url'] );
				$content->img = $imgs[0];
			}

			if ( isset( $r['offers'][0]['id'] ) ) {
				$content->unique_id = $r['offers'][0]['id'];
			} else {
				$content->unique_id = $r['offers'][0]['sourceProductId'];
			}

			$content->url          = $r['offers'][0]['productUrl'];
			$content->currencyCode = $r['offers'][0]['priceHistory'][0]['price']['currency'];
			$content->currency     = TextHelper::currencyTyping( $content->currencyCode );
			// $content->merchant = $r['offers'][0]['programName']; // Microsoft Public Affiliate Program UK (GBP)
			$content->logo = $r['offers'][0]['programLogo'];

			if ( isset( $r['offers'][0]['availability'] ) && in_array( $r['offers'][0]['availability'], array(
					'N',
					'Out Of Stock',
					'No'
				) ) ) {
				$content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			} else {
				$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			}

			// price
			if ( isset( $r['fields'] ) && isset( $r['fields'][0] ) && $r['fields'][0]['name'] == 'New Price' ) {
				$content->price    = $r['fields'][0]['value'];
				$content->priceOld = $r['offers'][0]['priceHistory'][0]['price']['value'];
			} else {
				$content->price = $r['offers'][0]['priceHistory'][0]['price']['value'];
			}

			if ( ! empty( $r['brand'] ) ) {
				$content->manufacturer = $r['brand'];
			} elseif ( ! empty( $r['manufacturer'] ) ) {
				$content->manufacturer = $r['manufacturer'];
			}

			$content->description = strip_tags( $r['description'] );
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}

			$original_url    = $this->getOriginalUrl( $content->url );
			$content->domain = TextHelper::getHostName( $original_url );
			foreach ( $r['fields'] as $field ) {
				if ( $field['name'] == 'Rating' ) {
					$content->rating = $field['value'];
					break;
				}
			}
			if ( isset( $r['identifiers'] ) ) {
				$content->ean  = ( ! empty( $r['identifiers']['ean'] ) ) ? $r['identifiers']['ean'] : '';
				$content->mpn  = ( ! empty( $r['identifiers']['mpn'] ) ) ? $r['identifiers']['mpn'] : '';
				$content->sku  = ( ! empty( $r['identifiers']['sku'] ) ) ? $r['identifiers']['sku'] : '';
				$content->upc  = ( ! empty( $r['identifiers']['upc'] ) ) ? $r['identifiers']['upc'] : '';
				$content->isbn = ( ! empty( $r['identifiers']['isbn'] ) ) ? $r['identifiers']['isbn'] : '';
			}

			if ( isset( $r['categories'] ) && isset( $r['categories'][0]['name'] ) ) {
				$content->category = $r['categories'][0]['name'];
			}

			$content->extra = new ExtraDataTradedoublerProducts;
			ExtraDataTradedoublerProducts::fillAttributes( $content->extra, $r );
			ExtraDataTradedoublerProducts::fillAttributes( $content->extra, $r['offers'][0] );
			$content->extra->fields = $r['fields'];

			$data[] = $content;
		}

		return $data;
	}

	public function doRequestItems( array $items ) {
		foreach ( $items as $key => $item ) {
			// Tradedoubler api changes the products id. Try to find by advertiser(!) product ID
			if ( ! empty( $item['extra']['sourceProductId'] ) && ! empty( $item['extra']['feedId'] ) ) {
				$params = array(
					'spId' => $item['extra']['sourceProductId'],
					'fid'  => $item['extra']['feedId'],
				);
				$result = $this->getApiClient()->query( $params );
			} else {
				$result = $this->getApiClient()->product( $item['unique_id'] );
			}

			if ( empty( $result['products'] ) || ! is_array( $result ) ) {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
				continue;
			}
			$result = $result['products'][0];

			// assign new price
			if ( isset( $result['fields'] ) && isset( $result['fields'][0] ) && $result['fields'][0]['name'] == 'New Price' ) {
				$items[ $key ]['price']    = $result['fields'][0]['value'];
				$items[ $key ]['priceOld'] = $result['offers'][0]['priceHistory'][0]['price']['value'];
			} else {
				$items[ $key ]['price'] = $result['offers'][0]['priceHistory'][0]['price']['value'];
			}

			if ( isset( $result['offers'][0]['availability'] ) && in_array( $result['offers'][0]['availability'], array(
					'N',
					'Out Of Stock',
					'No'
				) ) ) {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			} else {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
			}

			$items[ $key ]['url'] = $result['offers'][0]['productUrl'];
		}

		return $items;
	}

	private function getOriginalUrl( $url ) {
		if ( preg_match( '/.+url\((.+?)\)/', $url, $matches ) ) {
			return urldecode( $matches[1] );
		} else {
			return '';
		}
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new TradedoublerProducts( $this->config( 'token' ) );
		}

		return $this->api_client;
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchPanel() {
		$this->render( 'search_panel', array( 'module_id' => $this->getId() ) );
	}

}
