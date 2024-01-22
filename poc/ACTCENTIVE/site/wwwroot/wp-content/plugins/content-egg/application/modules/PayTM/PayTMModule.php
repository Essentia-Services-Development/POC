<?php

namespace ContentEgg\application\modules\PayTM;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\paytm\PayTMApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\LinkHandler;

/**
 * PayTMModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class PayTMModule extends AffiliateParserModule {

	public function info() {
		return array(
			'name'        => 'PayTM',
			'description' => __( 'Adds items from paytm.com', 'content-egg' ),
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_PRODUCT;
	}

	public function defaultTemplateName() {
		return 'data_item';
	}

	public function isItemsUpdateAvailable() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$options['items_per_page'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['items_per_page'] = $this->config( 'entries_per_page' );
		}

		if ( $this->config( 'category' ) ) {
			$options['category'] = (int) $this->config( 'category' );
		}
		if ( $this->config( 'availability' ) ) {
			$options['availability'] = 1;
		}
		$sort = $this->config( 'sort' );
		if ( $sort ) {
			$sort_parts                = explode( '=', $sort );
			$options[ $sort_parts[0] ] = $sort_parts[1];
		}

		$price_min = $price_max = 0;
		if ( ! empty( $query_params['price_min'] ) ) {
			$price_min = $query_params['price_min'];
		} elseif ( $this->config( 'price_min' ) ) {
			$price_min = $this->config( 'price_min' );
		}

		if ( ! empty( $query_params['price_max'] ) ) {
			$price_max = (int) $query_params['price_max'];
		} elseif ( $this->config( 'price_max' ) ) {
			$price_max = $this->config( 'price_max' );
		}

		if ( $price_min || $price_max ) {
			if ( $price_min ) {
				$options['price'] = (int) $price_min;
			} else {
				$options['price'] = 0;
			}
			$options['price'] .= ',';
			if ( $price_max ) {
				$options['price'] .= (int) $price_max;
			} else {
				$options['price'] .= 9999999;
			}
		}
		$client  = new PayTMApi;
		$results = $client->search( $keyword, $options );
		if ( ! is_array( $results ) || ! isset( $results['grid_layout'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['grid_layout'] );
	}

	public function prepareResults( $results ) {
		$data = array();

		$deeplink = $this->config( 'deeplink' );
		foreach ( $results as $key => $r ) {
			$content            = new ContentProduct;
			$content->unique_id = $r['product_id'];
			$content->domain    = 'paytmmall.com';
			$content->merchant  = 'PaytmMall';
			$content->title     = $r['name'];
			$content->orig_url  = $this->getOriginalUrl( $r['newurl'] );
			$content->url       = LinkHandler::createAffUrl( $content->orig_url, $deeplink );

			$content->img   = $r['image_url'];
			$content->price = $r['offer_price'];
			if ( $r['actual_price'] && (float) $r['actual_price'] > (float) $r['offer_price'] ) {
				$content->priceOld = $r['actual_price'];
			}
			$content->manufacturer = $r['brand'];
			$content->availability = (bool) $r['stock'];
			if ( (bool) $r['stock'] ) {
				$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			} else {
				$content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			}

			$content->percentageSaved = (int) $r['discount'];
			$content->currencyCode    = 'INR';
			$content->currency        = TextHelper::currencyTyping( $content->currencyCode );

			$content->extra = new ExtraDataPayTM;
			ExtraDataPayTM::fillAttributes( $content->extra, $r );
			$content->extra->product_code = $this->getProductCode( $content->url );

			$data[] = $content;
		}

		return $data;
	}

	public function doRequestItems( array $items ) {
		$client   = new PayTMApi;
		$deeplink = $this->config( 'deeplink' );
		foreach ( $items as $key => $item ) {
			if ( ! empty( $item['extra']['product_code'] ) ) {
				$product_code = $item['extra']['product_code'];
			} else {
				$product_code = $this->getProductCode( $item['orig_url'] );
			}

			try {
				$r = $client->product( $product_code );
			} catch ( \Exception $e ) {
				continue;
			}
			if ( ! is_array( $r ) || ! isset( $r['product_id'] ) ) {
				continue;
			}

			// assign new price
			$items[ $key ]['price'] = $r['offer_price'];
			if ( $r['actual_price'] && (float) $r['actual_price'] > (float) $r['offer_price'] ) {
				$items[ $key ]['priceOld'] = $r['actual_price'];
			}
			$items[ $key ]['availability'] = (bool) $r['instock'];

			if ( (bool) $r['instock'] ) {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
			} else {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			}


			$items[ $key ]['domain'] = 'paytmmall.com';

			// update url
			$items[ $key ]['orig_url'] = $this->getOriginalUrl( $r['redirect_url'] );
			$items[ $key ]['url']      = LinkHandler::createAffUrl( $items[ $key ]['orig_url'], $deeplink, $item );
		}

		return $items;
	}

	private function getProductCode( $url ) {
		if ( ! $path = parse_url( $url, PHP_URL_PATH ) ) {
			return false;
		}
		$pats = explode( '/', $path );
		$code = end( $pats );
		$code = preg_replace( '/\-pdp$/', '', $code );

		return $code;
	}

	private function getOriginalUrl( $url ) {
		$url_parts = parse_url( $url );
		$res       = 'https://paytmmall.com' . $url_parts['path'];
		if ( ! preg_match( '/\-pdp$/', $res ) ) {
			$res .= '-pdp';
		}

		return $res;
	}

	public function viewDataPrepare( $data ) {
		$deeplink = $this->config( 'deeplink' );
		foreach ( $data as $key => $d ) {
			// fix for old urls
			if ( strstr( $data[ $key ]['orig_url'], 'paytm.com/shop/p/' ) ) {
				$data[ $key ]['orig_url'] = str_replace( 'paytm.com/shop/p/', 'paytmmall.com/', $data[ $key ]['orig_url'] );
				$data[ $key ]['orig_url'] .= '-pdp';
				$data[ $key ]['domain']   = 'paytmmall.com';
			}

			$data[ $key ]['url'] = LinkHandler::createAffUrl( $data[ $key ]['orig_url'], $deeplink, $data[ $key ] );
		}

		return parent::viewDataPrepare( $data );
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
