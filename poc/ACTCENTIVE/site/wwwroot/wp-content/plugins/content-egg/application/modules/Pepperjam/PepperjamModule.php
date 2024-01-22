<?php

namespace ContentEgg\application\modules\Pepperjam;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\pepperjam\PepperjamApi;

/**
 * PepperjamModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class PepperjamModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Pepperjam',
			'description' => sprintf( __( 'Adds products from %s.', 'content-egg' ), 'Pepperjamnetwork' ),
		);
	}

	public function releaseVersion() {
		return '3.2.0';
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
			$limit = $this->config( 'entries_per_page_update' );
		} else {
			$limit = $this->config( 'entries_per_page' );
		}

		if ( $this->config( 'programId' ) ) {
			$options['programId'] = TextHelper::commaList( $this->config( 'programId' ) );
		}

		if ( $this->config( 'websiteId' ) ) {
			$options['websiteId'] = $this->config( 'websiteId' );
		}
		if ( $this->config( 'sid' ) ) {
			$options['sid'] = $this->config( 'sid' );
		}
		if ( $this->config( 'category' ) ) {
			$options['category'] = $this->config( 'category' );
		}

		$results = $this->getApiClient()->search( $keyword, $options );

		if ( ! isset( $results['data'] ) || ! is_array( $results['data'] ) ) {
			return array();
		}

		$results = array_slice( $results['data'], 0, $limit );

		return $this->prepareResults( $results );
	}

	private function prepareResults( $results ) {
		$data = array();

		foreach ( $results as $key => $r ) {
			$content               = new ContentProduct;
			$content->merchant     = $r['program_name'];
			$content->currencyCode = $r['currency'];
			$content->url          = $r['buy_url'];
			if ( $r['description_long'] ) {
				$content->description = strip_tags( $r['description_long'] );
			} elseif ( $r['description_short'] ) {
				$content->description = strip_tags( $r['description_short'] );
			}
			if ( $content->description && $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}
			$content->img          = $r['image_url'];
			$content->isbn         = $r['isbn'];
			$content->manufacturer = $r['manufacturer'];
			$content->title        = $r['name'];
			$content->sku          = $r['sku'];
			$content->upc          = $r['upc'];
			$content->availability = $r['in_stock'];
			if ( $r['in_stock'] == 'no' ) {
				$content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			} else {
				$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			}
			$price_sale   = (float) $r['price_sale'];
			$price_retail = (float) $r['price_retail'];
			$price        = (float) $r['price'];

			if ( $price_sale ) {
				$content->price = $price_sale;
			} else {
				$content->price = $price;
			}

			if ( $price_retail ) {
				$content->priceOld = $price_retail;
			} else {
				$content->priceOld = $price;
			}

			if ( $content->priceOld <= $content->price ) {
				$content->priceOld = null;
			}

			$content->domain = TextHelper::parseDomain( $content->url, 'url' );

			if ( $durl = self::getDirectUrl( $r['buy_url'] ) ) {
				$content->unique_id = md5( $durl );
			} else {
				$content->unique_id = md5( $r['buy_url'] );
			}

			$content->extra = new ExtraDataPepperjam();
			ExtraDataPepperjam::fillAttributes( $content->extra, $r );
			$data[] = $content;
		}

		return $data;
	}

	protected static function getDirectUrl( $url ) {
		if ( ! $query = parse_url( $url, PHP_URL_QUERY ) ) {
			return '';
		}
		parse_str( $query, $arr );
		if ( isset( $arr['url'] ) ) {
			return $arr['url'];
		} else {
			return '';
		}
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new PepperjamApi( $this->config( 'api_key' ) );
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
