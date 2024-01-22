<?php

namespace ContentEgg\application\modules\CityadsProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\cityads\CityadsApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * CityadsProductsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class CityadsProductsModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'CityAds Products',
			'description' => __( 'Adds goods from cityads.com', 'content-egg' ),
		);
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
			$options['limit'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['limit'] = $this->config( 'entries_per_page' );
		}
		if ( $this->config( 'subaccount' ) ) {
			$options['subaccount'] = $this->config( 'subaccount' );
		}
		if ( $this->config( 'shop' ) ) {
			$options['shop'] = TextHelper::commaList( $this->config( 'shop' ) );
		}
		if ( $this->config( 'categories' ) ) {
			$options['categories'] = (int) $this->config( 'categories' );
		}
		if ( $this->config( 'min_price' ) ) {
			$options['min_price'] = (int) $this->config( 'min_price' );
		}
		if ( $this->config( 'max_price' ) ) {
			$options['max_price'] = (int) $this->config( 'max_price' );
		}
		if ( $this->config( 'currency' ) ) {
			$options['currency'] = $this->config( 'currency' );
		}
		if ( $this->config( 'sort' ) ) {
			$options['sort'] = $this->config( 'sort' );
			if ( $this->config( 'sort_type' ) ) {
				$options['sort_type'] = $this->config( 'sort_type' );
			}
		}
		if ( $this->config( 'available' ) ) {
			$options['available'] = 'true';
		}

		if ( $this->config( 'geo' ) ) {
			$options['geo'] = TextHelper::commaList( $this->config( 'geo' ) );
		}

		$client  = new CityadsApi( $this->config( 'remote_auth' ) );
		$results = $client->products( $keyword, $options );

		if ( ! isset( $results['data']['items'] ) ) {
			return array();
		}
		$results = $results['data']['items'];

		return $this->prepareResults( $results );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content = new ContentProduct;

			$content->unique_id = $r['id'];
			$content->title     = strip_tags( $r['name'] );
			if ( $r['text'] ) {
				$content->description = trim( strip_tags( html_entity_decode( $r['text'] ), '<br>' ) );
				if ( $max_size = $this->config( 'description_size' ) ) {
					$content->description = TextHelper::truncate( $content->description, $max_size );
				}
			}
			$content->url = $r['url'];
			if ( ! preg_match( '/^http/', $content->url ) ) {
				$content->url = 'http:' . $content->url;
			}

			$content->price        = (float) $r['price'];
			$content->priceOld     = (float) $r['old_price'];
			$content->currencyCode = $r['currency'];
			$content->currency     = TextHelper::currencyTyping( $content->currencyCode );
			$content->img          = $r['image'];
			$content->manufacturer = $r['brand'];
			$content->rating       = $r['rating'];
			$content->category     = $r['category'];
			$content->merchant     = $r['offer_name'];
			$content->upc          = $r['upc'];
			$content->sku          = $r['sku'];

			$content->extra = new ExtraDataCityadsProducts;
			ExtraDataCityadsProducts::fillAttributes( $content->extra, $r );
			if ( ! empty( $r['properties'] ) ) {
				foreach ( $r['properties'] as $property ) {
					$feature             = array(
						'name'  => $property['name'],
						'value' => $property['value'],
					);
					$content->features[] = $feature;
				}
				//$content->extra->properties = $r['properties'];
			}
			$data[] = $content;
		}

		return $data;
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

}
