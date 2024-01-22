<?php

namespace ContentEgg\application\modules\Webgains;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateFeedParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\modules\Webgains\models\WebgainsProductModel;

/**
 * WebgainsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class WebgainsModule extends AffiliateFeedParserModule {

	public function info() {
		return array(
			'name'     => 'Webgains',
			'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/webgains',
		);
	}

	public function releaseVersion() {
		return '6.7.0';
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

	public function getProductModel() {
		return WebgainsProductModel::model();
	}

	public function isZippedFeed() {
		return true;
	}

	public function isUrlSearchAllowed() {
		return true;
	}

	public function getFeedUrl() {
		if ( ! $feed_url = $this->buildFeedUrl() ) {
			throw new \Exception( 'Wrong format of Datafeed URL.' );
		}

		return $feed_url;
	}

	protected function feedProductPrepare( array $data ) {
		$product       = array();
		$product['id'] = $data['product_id'];
		if ( ! isset( $data['product_name'] ) ) {
			return false;
		}
		$product['title']    = \sanitize_text_field( $data['product_name'] );
		$product['price']    = (float) $data['price'];
		$product['orig_url'] = TextHelper::parseOriginalUrl( $data['deeplink'], 'wgtarget' );

		if ( ! empty( $data['european_article_number'] ) && TextHelper::isEan( $data['european_article_number'] ) ) {
			$product['ean'] = $data['european_article_number'];
		} elseif ( TextHelper::isEan( $data['product_id'] ) ) {
			$product['ean'] = $data['product_id'];
		} else {
			$product['ean'] = '';
		}

		if ( ! empty( $data['in_stock'] ) ) {
			$stock = $data['in_stock'];
		} elseif ( isset( $data['additionalproductdetails_3'] ) ) {
			$stock = $data['additionalproductdetails_3'];
		} else {
			$stock = '';
		}

		if ( $stock && in_array( $stock, array( 'out of stock', 'Out Of Stock', 'Out of Stock' ) ) ) {
			$product['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
		} else {
			$product['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
		}

		$product['product'] = serialize( $data );

		return $product;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$this->maybeImportProducts();

		if ( $is_autoupdate ) {
			$limit = $this->config( 'entries_per_page_update' );
		} else {
			$limit = $this->config( 'entries_per_page' );
		}

		if ( TextHelper::isEan( $keyword ) ) {
			$results = $this->product_model->searchByEan( $keyword, $limit );
		} elseif ( filter_var( $keyword, FILTER_VALIDATE_URL ) ) {
			$results = $this->product_model->searchByUrl( $keyword, $this->config( 'partial_url_match' ), $limit );
		} else {
			$options = array();
			if ( ! empty( $query_params['price_min'] ) ) {
				$options['price_min'] = (float) $query_params['price_min'];
			}
			if ( ! empty( $query_params['price_min'] ) ) {
				$options['price_max'] = (float) $query_params['price_max'];
			}

			$results = $this->product_model->searchByKeyword( $keyword, $limit, $options );
		}

		if ( ! $results ) {
			return array();
		}

		return $this->prepareResults( $results );
	}

	private function prepareResults( $results ) {

		$data = array();
		foreach ( $results as $key => $product ) {
			if ( ! $r = unserialize( $product['product'] ) ) {
				continue;
			}

			$content              = new ContentProduct;
			$content->unique_id   = $r['product_id'];
			$content->title       = $r['product_name'];
			$content->url         = $r['deeplink'];
			$content->orig_url    = $product['orig_url'];
			$content->img         = $r['image_url'];
			$content->description = $r['description'];
			$content->category    = $r['category_name'];
			$content->price       = $r['price'];
			if ( isset( $r['currency'] ) ) {
				$content->currencyCode = $r['currency'];
			}
			if ( isset( $r['brand'] ) ) {
				$content->manufacturer = $r['brand'];
			} elseif ( isset( $r['manufacturer'] ) ) {
				$content->manufacturer = $r['manufacturer'];
			}

			if ( (float) $content->price && isset( $r['normal_price'] ) && $r['normal_price'] != $r['program_id'] ) {
				$content->priceOld = $r['normal_price'];
			} elseif ( (float) $content->price && isset( $r['recommended_retail_price'] ) && $r['recommended_retail_price'] != $r['program_id'] ) {
				$content->priceOld = $r['recommended_retail_price'];
			}

			$content->ean = $product['ean'];

			if ( isset( $r['program_name'] ) ) {
				$content->merchant = $r['program_name'];
			} elseif ( isset( $r['merchant_name'] ) ) {
				$content->merchant = $r['merchant_name'];
			}

			$content->domain = TextHelper::getHostName( $content->orig_url );

			$content->stock_status = $product['stock_status'];
			$data[]                = $content;
		}

		return $data;
	}

	public function doRequestItems( array $items ) {
		$this->maybeImportProducts();
		foreach ( $items as $key => $item ) {
			$product = $this->product_model->searchById( $item['unique_id'] );
			if ( ! $product ) {
				if ( $this->product_model->count() ) {
					$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
				}
				continue;
			}
			if ( empty( $product['product'] ) || ! $r = unserialize( $product['product'] ) ) {
				continue;
			}

			$items[ $key ]['price']        = (float) $r['price'];
			$items[ $key ]['stock_status'] = $product['stock_status'];
			if ( (float) $items[ $key ]['price'] && isset( $r['normal_price'] ) && $r['normal_price'] != $r['program_id'] ) {
				$items[ $key ]['priceOld'] = (float) $r['normal_price'];
			} else {
				$items[ $key ]['priceOld'] = 0;
			}
		}

		return $items;
	}

	private function buildFeedUrl() {
		$url = $this->config( 'datafeed_url' );

		parse_str( parse_url( $url, PHP_URL_QUERY ), $query );

		$params    = array( 'campaign', 'feeds', 'categories', 'apikey', 'username', 'password' );
		$res_query = array_intersect_key( $query, array_flip( $params ) );

		$default_params = array(
			'action'        => 'download',
			'fields'        => 'extended',
			'format'        => 'csv',
			'separator'     => 'comma',
			'zipformat'     => 'zip',
			'stripNewlines' => '0',
			'fieldIds'      => 'category_id,category_name,category_path,deeplink,description,image_url,last_updated,merchant_category,price,product_id,product_name,program_id,program_name',
		);

		$extended_fields            = 'brand,currency,delivery_cost,european_article_number,in_stock,manufacturer,normal_price,recommended_retail_price';
		$default_params['fieldIds'] .= $extended_fields;

		$res_query = array_merge( $res_query, $default_params );

		$res = 'https://www.webgains.com/affiliates/datafeed.html?';
		$res .= http_build_query( $res_query );

		return $res;
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

}
