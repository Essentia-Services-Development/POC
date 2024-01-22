<?php

namespace ContentEgg\application\modules\Optimisemedia;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\optimisemedia\OptimisemediaApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * OptimisemediaModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class OptimisemediaModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Optimisemedia',
			'description' => __( 'Module adds products from Optimise Network.', 'content-egg' ),
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
		return false;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$options['NumberOfRecords'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['NumberOfRecords'] = $this->config( 'entries_per_page' );
		}

		$options['AgencyID'] = (int) $this->config( 'AgencyID' );
		$options['AID']      = (int) $this->config( 'AffiliateID' );

		if ( (float) $this->config( 'MinPrice' ) ) {
			$options['MinPrice'] = (float) $this->config( 'MinPrice' );
		}
		if ( (float) $this->config( 'MaxPrice' ) ) {
			$options['MaxPrice'] = (float) $this->config( 'MaxPrice' );
		}

		if ( ! empty( $query_params['MinPrice'] ) ) {
			$options['MinPrice'] = (float) $query_params['MinPrice'];
		}
		if ( ! empty( $query_params['MaxPrice'] ) ) {
			$options['MaxPrice'] = (float) $query_params['MaxPrice'];
		}

		if ( $this->config( 'Currency' ) ) {
			$options['Currency'] = $this->config( 'Currency' );
		}
		if ( $this->config( 'DiscountedOnly' ) ) {
			$options['DiscountedOnly'] = true;
		}

		$results = $this->getApiClient()->search( $keyword, $options );

		if ( ! isset( $results['GetProductsFeedsResult'] ) ) {
			return array();
		}

		if ( ! isset( $results['GetProductsFeedsResult'][0] ) && isset( $results['GetProductsFeedsResult']['ProductID'] ) ) {
			$results['GetProductsFeedsResult'] = array( $results['GetProductsFeedsResult'] );
		}

		return $this->prepareResults( $results['GetProductsFeedsResult'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content            = new ContentProduct;
			$content->unique_id = $r['ProductID'];
			$content->logo      = $r['MerchantLogoURL'];
			$content->title     = $r['ProductName'];
			$content->url       = $r['ProductURL'];
			$content->category  = $r['CategoryName'];
			if ( $r['ProductLargeImageURL'] ) {
				$imgs         = explode( ';', $r['ProductLargeImageURL'] );
				$content->img = $imgs[0];
			} elseif ( ! empty( $r['ProductMediumImageURL'] ) ) {
				$imgs         = explode( ';', $r['ProductMediumImageURL'] );
				$content->img = $imgs[0];
			}

			if ( $r['MerchantDomain'] ) {
				$content->domain = $r['MerchantDomain'];
			} else {
				$content->domain = TextHelper::parseDomain( $r['ProductURL'], 'r' );
			}

			$content->currencyCode = $r['ProductPriceCurrency'];
			$content->currency     = TextHelper::currencyTyping( $content->currencyCode );
			$content->sku          = $r['ProductSKU'];
			if ( $r['Brand'] && $r['Brand'] !== 'Unbrand' ) {
				$content->manufacturer = $r['Brand'];
			}
			/*
			  if ($r['StockAvailability'] == 'in stock')
			  $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			  else
			  $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			 *
			 */
			//$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;

			if ( ! empty( $r['ProductSKU'] ) ) {
				$content->sku = $r['ProductSKU'];
			}

			// price
			$r['DiscountedPrice'] = (float) $r['DiscountedPrice'];
			$r['ProductPrice']    = (float) $r['ProductPrice'];
			$r['WasPrice']        = (float) $r['WasPrice'];

			if ( $r['WasPrice'] ) {
				$priceOld = $r['WasPrice'];
			} elseif ( $r['DiscountedPrice'] ) {
				$priceOld = $r['DiscountedPrice'];
			} else {
				$priceOld = 0;
			}

			if ( $r['DiscountedPrice'] && $r['DiscountedPrice'] < $r['ProductPrice'] ) {
				$content->price = $r['DiscountedPrice'];
			} else {
				$content->price = $r['ProductPrice'];
			}
			$content->priceOld = $priceOld;

			if ( $r['ProductDescription'] ) {
				$content->description = strip_tags( $r['ProductDescription'] );
			}
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}

			$content->extra = new ExtraDataOptimisemedia;
			ExtraDataOptimisemedia::fillAttributes( $content->extra, $r );

			$data[] = $content;
		}

		return $data;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new OptimisemediaApi( $this->config( 'api_key' ), $this->config( 'private_key' ) );
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
