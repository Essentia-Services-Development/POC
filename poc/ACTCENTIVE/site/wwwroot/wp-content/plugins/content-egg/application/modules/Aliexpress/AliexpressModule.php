<?php

namespace ContentEgg\application\modules\Aliexpress;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\aliexpress\AliexpressApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\LinkHandler;

/**
 * AliexpressModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AliexpressModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'     => 'Aliexpress (legacy)',
			'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/aliexpress',
		);
	}

	public function isDeprecated() {
		return true;
	}

	public function getParserType() {
		return self::PARSER_TYPE_PRODUCT;
	}

	public function defaultTemplateName() {
		return 'grid';
	}

	public function isItemsUpdateAvailable() {
		return true;
	}

	public function isUrlSearchAllowed() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$options['pageSize'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['pageSize'] = $this->config( 'entries_per_page' );
		}

		if ( (int) $this->config( 'category_id' ) ) {
			$options['categoryId'] = $this->config( 'category_id' );
		}

		if ( $this->config( 'commission_rate_from' ) ) {
			$options['commissionRateFrom'] = $this->config( 'commission_rate_from' ) / 100;
		}

		if ( ! empty( $query_params['original_price_from'] ) ) {
			$options['originalPriceFrom'] = $query_params['original_price_from'];
		} elseif ( $this->config( 'original_price_from' ) ) {
			$options['originalPriceFrom'] = $this->config( 'original_price_from' );
		}

		if ( ! empty( $query_params['original_price_to'] ) ) {
			$options['originalPriceTo'] = $query_params['original_price_to'];
		} elseif ( $this->config( 'original_price_to' ) ) {
			$options['originalPriceTo'] = $this->config( 'original_price_to' );
		}

		if ( $this->config( 'volume_from' ) ) {
			$options['volumeFrom'] = $this->config( 'volume_from' );
		}
		if ( $this->config( 'volume_to' ) ) {
			$options['volumeTo'] = $this->config( 'volume_to' );
		}
		if ( $this->config( 'sort' ) ) {
			$options['sort'] = $this->config( 'sort' );
		}
		if ( $this->config( 'start_credit_score' ) ) {
			$options['startCreditScore'] = $this->config( 'start_credit_score' );
		}
		if ( $this->config( 'high_quality_items' ) ) {
			$options['highQualityItems'] = 'true';
		}
		if ( $this->config( 'local_currency' ) != 'USD' ) {
			$options['localCurrency'] = $this->config( 'local_currency' );
		}
		$options['language'] = $this->config( 'language' );
		$fields              = array(
			'productId',
			'productTitle',
			'productUrl',
			'imageUrl',
			'originalPrice',
			'salePrice',
			'discount',
			'evaluateScore',
			'commission',
			'commissionRate',
			'30daysCommission',
			'volume',
			'packageType',
			'lotNum',
			'validTime',
			'localPrice'
		);
		$options['fields']   = join( ',', $fields );

		// Is URL passed? Search by product URL
		if ( filter_var( $keyword, FILTER_VALIDATE_URL ) && $product_id = AliexpressModule::parseIdFromUrl( $keyword ) ) {
			$keyword = $product_id;
		}

		// Product ID search?
		if ( AliexpressModule::isProductId( $keyword ) ) {
			$result = $this->getAliexpressClient()->getProduct( $keyword, $options );
			if ( ! is_array( $result ) || ! isset( $result['result']['productId'] ) ) {
				return array();
			}
			$results = array( $result['result'] );
		} else {
			$results = $this->getAliexpressClient()->search( $keyword, $options );
			if ( ! is_array( $results ) || ! isset( $results['result']['products'] ) ) {
				return array();
			}
			if ( ! $results = $results['result']['products'] ) {
				return array();
			}
		}

		return $this->prepareResults( $results );
	}

	private function prepareResults( $results ) {
		$data = array();

		$deeplink = $this->config( 'deeplink' );
		foreach ( $results as $key => $r ) {
			$content               = new ContentProduct;
			$content->unique_id    = $r['productId'];
			$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			$content->merchant     = 'AliExpress';
			$content->domain       = 'aliexpress.com';
			$content->orig_url     = $r['productUrl'];

			if ( $deeplink ) {
				$content->url = LinkHandler::createAffUrl( $content->orig_url, $deeplink );
			} else {
				$content->url = $r['productUrl'];
			}

			if ( isset( $r['imageUrl'] ) ) {
				$content->img = preg_replace( '/\.jpg$/', '.jpg_350x350.jpg', $r['imageUrl'] );
			}

			$content->title    = strip_tags( $r['productTitle'] );
			$prices            = self::_getPrices( $r );
			$content->price    = $prices['price'];
			$content->priceOld = $prices['priceOld'];

			if ( strstr( $r['originalPrice'], '€' ) ) {
				$content->currencyCode = 'EUR';
			} else {
				$content->currencyCode = $this->config( 'local_currency' );
			}

			$content->currency        = TextHelper::currencyTyping( $content->currencyCode );
			$content->percentageSaved = floor( preg_replace( '/[^0-9\.]/', '', $r['discount'] ) );

			$extra                             = new ExtraDataAliexpress;
			$content->extra                    = $extra;
			$content->extra->_30daysCommission = (float) preg_replace( '/[^0-9\.]/', '', $r['30daysCommission'] );
			if ( isset( $r['commissionRate'] ) ) {
				$content->extra->commissionRate = (float) preg_replace( '/[^0-9\.]/', '', $r['commissionRate'] );
			}
			$content->extra->validTime = strtotime( $r['validTime'] );
			if ( isset( $r['commission'] ) ) {
				$content->extra->commission = (float) preg_replace( '/[^0-9\.]/', '', $r['commission'] );
			}
			$content->extra->lotNum        = (int) $r['lotNum'];
			$content->extra->packageType   = $r['packageType'];
			$content->extra->volume        = (int) $r['volume'];
			$content->extra->evaluateScore = $r['evaluateScore'];

			$data[] = $content;
		}

		$tracking_id = $this->config( 'tracking_id' );
		if ( ! $deeplink && $tracking_id ) {
			$urls = array();
			foreach ( $data as $d ) {
				$urls[] = $d->url;
			}
			$aff_links = $this->getAliexpressClient()->getLinks( $tracking_id, $urls );

			if ( ! isset( $aff_links['result'] ) || ! is_array( $aff_links['result']['promotionUrls'] ) ) {
				throw new \Exception( 'API error: can not fetch affiliate links.' );
			}

			$aff_links = $aff_links['result']['promotionUrls'];
			$i         = 0;
			foreach ( $data as $key => $d ) {
				if ( $d->url != $aff_links[ $i ]['url'] ) {
					continue;
				} // @todo: ?
				$data[ $key ]->url = $aff_links[ $i ]['promotionUrl'];
				$i ++;
			}

			return $data;
		}

		return $data;
	}

	private static function _getPrices( $r ) {
		if ( isset( $r['localPrice'] ) ) {
			$localPrice = (float) preg_replace( '/[^0-9\.,]/', '', $r['localPrice'] );
		} else {
			$localPrice = 0;
		}
		$originalPrice = (float) preg_replace( '/[^0-9\.,]/', '', $r['originalPrice'] );
		$salePrice     = (float) preg_replace( '/[^0-9\.,]/', '', $r['salePrice'] );

		$result = array( 'price' => 0, 'priceOld' => 0 );
		if ( $localPrice ) {
			$result['price'] = $localPrice;
		} else {
			$result['price'] = $salePrice;
		}
		if ( $originalPrice > $salePrice ) {
			if ( $localPrice ) {
				$currency_rate      = $localPrice / $salePrice;
				$result['priceOld'] = round( $originalPrice * $currency_rate, 2 );
			} else {
				$result['priceOld'] = $originalPrice;
			}
		}

		return $result;
	}

	public function doRequestItems( array $items ) {
		$options           = array();
		$options['fields'] = 'productId,originalPrice,salePrice,localPrice,discount';
		if ( $this->config( 'local_currency' ) != 'USD' ) {
			$options['localCurrency'] = $this->config( 'local_currency' );
		}
		$options['language'] = $this->config( 'language' );

		$deeplink = $this->config( 'deeplink' );
		foreach ( $items as $key => $item ) {
			$result = $this->getAliexpressClient()->getProduct( $item['unique_id'], $options );

			// out of stock
			if ( isset( $result['errorCode'] ) && $result['errorCode'] == 20010000 && ! isset( $result['result']['productId'] ) ) {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
				$items[ $key ]['price']        = 0;
				$items[ $key ]['priceOld']     = 0;
				continue;
			}

			if ( ! is_array( $result ) || ! isset( $result['result']['productId'] ) ) {
				throw new \Exception( 'doRequestItems request error.' );
			}
			$r                             = $result['result'];
			$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;

			// assign new price
			$prices                        = self::_getPrices( $r );
			$items[ $key ]['price']        = $prices['price'];
			$items[ $key ]['priceOld']     = $prices['priceOld'];
			$items[ $key ]['currencyCode'] = $this->config( 'local_currency' );
			$items[ $key ]['currency']     = TextHelper::currencyTyping( $items[ $key ]['currencyCode'] );

			$items[ $key ]['percentageSaved'] = floor( preg_replace( '/[^0-9\.]/', '', $r['discount'] ) );
			$items[ $key ]['domain']          = 'aliexpress.com';

			// update url if deeplink changed
			if ( $deeplink && $item['orig_url'] ) {
				$items[ $key ]['url'] = LinkHandler::createAffUrl( $item['orig_url'], $deeplink, $item );
			}
		}

		return $items;
	}

	private function getAliexpressClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new AliexpressApi( $this->config( 'api_key' ) );
		}

		return $this->api_client;
	}

	public function viewDataPrepare( $data ) {
		$deeplink = $this->config( 'deeplink' );
		foreach ( $data as $key => $d ) {
			if ( $deeplink && $d['orig_url'] ) {
				$data[ $key ]['url'] = LinkHandler::createAffUrl( $d['orig_url'], $deeplink, $d );
			}
		}

		return parent::viewDataPrepare( $data );
	}

	public function renderSearchPanel() {
		$this->render( 'search_panel', array( 'module_id' => $this->getId() ) );
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderUpdatePanel() {
		$this->render( 'update_panel', array( 'module_id' => $this->getId() ) );
	}

	private static function isProductId( $str ) {
		if ( preg_match( '/[0-9]{10,}/', $str ) ) {
			return true;
		} else {
			return false;
		}
	}

	private static function parseIdFromUrl( $url ) {
		$regex = '/aliexpress.+?\/.+?([0-9]{10,})\.html/';
		if ( preg_match( $regex, $url, $matches ) ) {
			return $matches[1];
		} else {
			return '';
		}
	}

}
