<?php

namespace ContentEgg\application\modules\AdmitadProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\admitad\AdmitadProducts;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\LinkHandler;

/**
 * AdmitadProductsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class AdmitadProductsModule extends AffiliateParserModule {

	public function info() {
		return array(
			'name'        => 'Admitad Products',
			'description' => sprintf( __( 'Add products from %s.', 'content-egg' ), '<a href="http://www.keywordrush.com/go/admitad">Admitad</a>' ) . ' ' . __( 'You must get approve for each program separately.', 'content-egg' )
		);
	}

	public function isDeprecated() {
		return true;
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

	public function isFree() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		throw new \Exception( 'This module is deprecated. Admitad API was closed.' );

		$options = array();

		$offer_id            = (int) $this->config( 'offer_id' );
		$options['offer_id'] = $offer_id;

		if ( $this->config( 'only_sale' ) ) {
			$options['only_sale'] = 1;
		}


		if ( ! empty( $query_params['price_from'] ) ) {
			$options['price_from'] = $query_params['price_from'];
		} elseif ( $this->config( 'price_from' ) ) {
			$options['price_from'] = (int) $this->config( 'price_from' );
		}

		if ( ! empty( $query_params['price_to'] ) ) {
			$options['price_to'] = $query_params['price_to'];
		} elseif ( $this->config( 'price_to' ) ) {
			$options['price_to'] = (int) $this->config( 'price_to' );
		}

		$client  = new AdmitadProducts();
		$results = $client->search( $keyword, $options );

		if ( ! is_array( $results ) || ! isset( $results['items'] ) ) {
			return array();
		}
		if ( $is_autoupdate ) {
			$limit = $this->config( 'entries_per_page_update' );
		} else {
			$limit = $this->config( 'entries_per_page' );
		}
		$results = array_slice( $results['items'], 0, $limit );

		return $this->prepareResults( $results, $offer_id );
	}

	public function prepareResults( $results, $offer_id ) {
		$data = array();

		foreach ( $results as $key => $r ) {
			$content               = new ContentProduct;
			$content->unique_id    = $offer_id . '-' . $r['id_item'];
			$content->category     = $r['categoryId'];
			$content->currencyCode = $r['currencyId'];
			$content->currency     = TextHelper::currencyTyping( $content->currencyCode );
			$content->title        = $r['name'];
			$content->priceOld     = (float) $r['oldprice'];
			$content->price        = (float) $r['price'];
			$content->img          = $r['picture'];
			$content->manufacturer = $r['vendor'];

			if ( ! $orig_url = TextHelper::parseOriginalUrl( $r['url'], 'ulp' ) ) {
				continue;
			}
			$content->orig_url = $orig_url;
			$content->domain   = TextHelper::parseDomain( $r['url'], 'ulp' );

			$content->url = LinkHandler::createAffUrl( $content->orig_url, $this->config( 'deeplink' ), null, 'i=13' );

			$content->description = $r['description'];
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}

			$content->extra           = new ExtraDataAdmitadProducts;
			$content->extra->offer_id = $offer_id;
			$content->extra->id_item  = $r['id_item'];
			ExtraDataAdmitadProducts::fillAttributes( $content->extra, $r );

			if ( ! $content->extra->param ) {
				$content->extra->param = array();
			}

			foreach ( $content->extra->param as $f_name => $f_value ) {
				$feature             = array(
					'name'  => $f_name,
					'value' => $f_value,
				);
				$content->features[] = $feature;
			}

			$data[] = $content;
		}

		return $data;
	}

	public function doRequestItems( array $items ) {
		throw new \Exception( 'This module is deprecated. Admitad API was closed.' );

		$productsToUpdate = array();
		foreach ( $items as $item ) {
			if ( empty( $item['extra']['offer_id'] ) || empty( $item['extra']['id_item'] ) || empty( $item['extra']['id'] ) ) {
				continue;
			}
			$productsToUpdate[ $item['extra']['offer_id'] ][] = $item['extra']['id_item'];
		}

		$client  = new AdmitadProducts();
		$results = $client->update( $productsToUpdate );
		if ( ! is_array( $results ) || ! isset( $results[0]['id_item'] ) ) {
			throw new \Exception( 'doRequestItems request error.' );
		}

		// assign new price
		foreach ( $results as $r ) {
			foreach ( $items as $key => $item ) {
				if ( (int) $item['extra']['id_item'] == (int) $r['id_item'] ) {
					$items[ $key ]['priceOld'] = (float) $r['oldprice'];
					$items[ $key ]['price']    = (float) $r['price'];
					break;
				}
			}
		}

		return $items;
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

	public function viewDataPrepare( $data ) {
		$deeplink = $this->config( 'deeplink' );
		foreach ( $data as $key => $d ) {
			/**
			 * &i=13 подставить
			 * это метка адмитада чтоб понимать что с плагина действия
			 */
			if ( $deeplink && $d['orig_url'] ) {
				$data[ $key ]['url'] = LinkHandler::createAffUrl( $d['orig_url'], $deeplink, $d, 'i=13' );
			}
		}

		return parent::viewDataPrepare( $data );
	}

	public function renderSearchPanel() {
		$this->render( 'search_panel', array( 'module_id' => $this->getId() ) );
	}

}
