<?php

namespace ContentEgg\application\modules\Linkwise;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\linkwise\LinkwiseApi;
use ContentEgg\application\modules\Linkwise\ExtraDataLinkwise;

/**
 * LinkwiseModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class LinkwiseModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Linkwise',
			'description' => sprintf( __( 'Adds products from %s affiliate marketing network.', 'content-egg' ), '<a target="_blank" href="https://linkwi.se/">Linkwi.se</a>' ),
		);
	}

	public function releaseVersion() {
		return '4.9.0';
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

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$limit = $this->config( 'entries_per_page_update' );
		} else {
			$limit = $this->config( 'entries_per_page' );
		}
		$options['limit'] = (int) $limit;


		// price filter
		if ( ! empty( $query_params['min_price'] ) ) {
			$options['min_price'] = (float) $query_params['min_price'];
		} elseif ( $this->config( 'min_price' ) ) {
			$options['min_price'] = (float) $this->config( 'min_price' );
		}

		if ( ! empty( $query_params['max_price'] ) ) {
			$options['max_price'] = (float) $query_params['max_price'];
		} elseif ( $this->config( 'max_price' ) ) {
			$options['max_price'] = (float) $this->config( 'max_price' );
		}

		$params = array(
			'joined',
			//'in_stock', //There is no need to filter results with this tag, as all products are instock.
			'subids',
		);
		foreach ( $params as $param ) {
			if ( $value = $this->config( $param ) ) {
				$options[ $param ] = $value;
			}
		}
		$pipe_delimited_params = array(
			'prod_categories',
			'programs',
			'program_ids',
			'feed_ids',
			'categories',
			'countries',
		);

		foreach ( $pipe_delimited_params as $param ) {
			if ( $value = $this->config( $param ) ) {
				$values            = TextHelper::getArrayFromCommaList( $values );
				$options[ $param ] = join( ',', $values );
			}
		}
		if ( (bool) $this->config( 'has_images' ) ) {
			$options['has_images'] = 1;
		}

		$results = $this->getApiClient()->search( $keyword, $options );

		if ( ! is_array( $results ) || ! isset( $results[0]['lw_product_id'] ) ) {
			return array();
		}

		return $this->prepareResults( $results );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content            = new ContentProduct;
			$content->unique_id = $r['lw_product_id'];
			$content->title     = $r['product_name'];
			if ( isset( $r['brand_name'] ) ) {
				$content->manufacturer = $r['brand_name'];
			}
			$content->orig_url = $r['site_url'];
			$content->url      = $r['tracking_url'];
			$content->domain   = TextHelper::getHostName( $content->orig_url );
			$content->img      = $r['image_url'];
			$content->price    = str_replace( ',', '.', $r['price'] );
			if ( isset( $r['full_price'] ) ) {
				$content->priceOld = str_replace( ',', '.', $r['full_price'] );
			}
			if ( ! empty( $r['currency'] ) ) {
				$content->currencyCode = $r['currency'];
			} else {
				$content->currencyCode = 'EUR';
			}

			$content->merchant = $r['program']['name'];

			if ( ! empty( $r['in_stock'] ) ) {
				if ( $r['in_stock'] == 'Y' ) {
					$r['in_stock'] = 'yes';
				}
				if ( $r['in_stock'] == 'N' ) {
					$r['in_stock'] = 'no';
				}
				if ( filter_var( $r['in_stock'], FILTER_VALIDATE_BOOLEAN ) ) {
					$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
				} else {
					$content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
				}
			} else {
				$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			}

			if ( isset( $r['availability'] ) ) {
				$content->availability = $r['availability'];
			}
			if ( isset( $r['description'] ) ) {
				$content->description = strip_tags( $r['description'] );
				if ( $max_size = $this->config( 'description_size' ) ) {
					$content->description = TextHelper::truncateHtml( $content->description, $max_size );
				}
			}

			$content->extra = new ExtraDataLinkwise();
			ExtraDataLinkwise::fillAttributes( $content->extra, $r );

			$data[] = $content;
		}

		return $data;
	}

	public function doRequestItems( array $items ) {
		$product_ids = array_keys( $items );
		$results     = $this->getApiClient()->products( $product_ids );
		if ( ! is_array( $results ) || ! isset( $results[0]['lw_product_id'] ) ) {
			return $items;
		}

		$new = array();
		foreach ( $results as $r ) {
			$new[ $r['lw_product_id'] ] = $r;
		}

		foreach ( $items as $key => $item ) {
			if ( ! isset( $new[ $key ] ) ) {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
				continue;
			}
			$r = $new[ $key ];
			// assign new data
			$items[ $key ]['price'] = str_replace( ',', '.', $r['price'] );
			if ( isset( $r['full_price'] ) ) {
				$items[ $key ]['priceOld'] = str_replace( ',', '.', $r['full_price'] );
			}

			if ( ! empty( $r['in_stock'] ) ) {
				if ( $r['in_stock'] == 'Y' ) {
					$r['in_stock'] = 'yes';
				}
				if ( $r['in_stock'] == 'N' ) {
					$r['in_stock'] = 'no';
				}
				if ( filter_var( $r['in_stock'], FILTER_VALIDATE_BOOLEAN ) ) {
					$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
				} else {
					$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
				}
			}

			if ( isset( $r['availability'] ) ) {
				$items[ $key ]['availability'] = $r['availability'];
			}
		}

		return $items;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new LinkwiseApi( $this->config( 'username' ), $this->config( 'password' ) );
		}

		return $this->api_client;
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
