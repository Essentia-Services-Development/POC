<?php

namespace ContentEgg\application\modules\GdeSlon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\gdeslon\GdeSlonApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * GdeSlonModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class GdeSlonModule extends AffiliateParserModule {

	private $api_client;
	private $merchants;

	const VENDOR_ID = '5fd4e7033b';

	public function info() {
		return array(
			'name'        => 'GdeSlon',
			'description' => sprintf( __( 'Adds products from %s.', 'content-egg' ), '<a target="_blank" href="http://www.keywordrush.com/go/gdeslon">GdeSlon.ru</a>' ),
		);
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

	public function isFree() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$options['l'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['l'] = $this->config( 'entries_per_page' );
		}

		if ( $m = $this->config( 'merchant_id' ) ) {
			$options['m'] = TextHelper::commaList( $m );
		}
		if ( $no_m = $this->config( 'no_m' ) ) {
			$options['no_m'] = TextHelper::commaList( $no_m );
		}
		if ( $tid = $this->config( 'search_category' ) ) {
			$options['tid'] = TextHelper::commaList( $tid );
		}
		if ( $parked_domain_name = $this->config( 'parked_domain_name' ) ) {
			$options['parked_domain_name'] = $parked_domain_name;
		}

		if ( $this->config( 'order' ) && $this->config( 'order' ) != 'default' ) {
			$options['order'] = $this->config( 'order' );
		}

		$client  = $this->getApiClient();
		$results = $client->search( $keyword, $options );

		if ( ! isset( $results['offers'] ) || ! isset( $results['offers']['offer'] ) ) {
			return array();
		}
		$results = $results['offers']['offer'];
		if ( ! isset( $results[0] ) && isset( $results['name'] ) ) {
			$results = array( $results );
		}

		return $this->prepareResults( $results );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content = new ContentProduct;

			if ( ! $r['name'] && $r['description'] ) {
				$r['name'] = TextHelper::truncate( strip_tags( $r['description'] ) );
			}

			if ( ! $r['name'] ) {
				continue;
			}

			$content->unique_id = $r['@attributes']['gs_product_key'] . $r['@attributes']['id'];
			$content->title     = trim( $r['name'] );

			if ( $r['description'] ) {
				$content->description = strip_tags( trim( $r['description'] ) );
				$content->description = preg_replace( "/\n\W*\n/msi", "\n", $content->description );
				$content->description = nl2br( $content->description );
			}
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}

			$content->url = self::generateAffiliateUrl( $r['url'] );

			$content->price = (float) $r['price'];
			if ( ! empty( $r['oldprice'] ) ) {
				$content->priceOld = (float) $r['oldprice'];
			}
			$content->currencyCode = $r['currencyId'];
			$content->currency     = TextHelper::currencyTyping( $content->currencyCode );

			if ( $r['original_picture'] ) {
				$content->img = $r['original_picture'];
			} elseif ( $r['picture'] && $r['picture'] != 'http://www.gdeslon.ru/images/default_picture/small.png' ) {
				$imgs         = explode( ',', $r['picture'] );
				$content->img = $imgs[0];
			}
			$content->availability = (bool) $r['@attributes']['available'];
			if ( (bool) $r['@attributes']['available'] ) {
				$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			} else {
				$content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			}
			$content->manufacturer        = $r['vendor'];
			$content->extra               = new ExtraDataGdeSlon;
			$content->extra->productId    = $r['@attributes']['id'];
			$content->extra->gsCategoryId = ( isset( $r['@attributes']['gs_category_id'] ) ) ? (int) $r['@attributes']['gs_category_id'] : '';
			$content->extra->merchantId   = ( isset( $r['@attributes']['merchant_id'] ) ) ? (int) $r['@attributes']['merchant_id'] : '';
			$content->extra->gsProductKey = ( isset( $r['@attributes']['gs_product_key'] ) ) ? $r['@attributes']['gs_product_key'] : '';
			$content->extra->article      = ( isset( $r['@attributes']['article'] ) ) ? $r['@attributes']['article'] : '';
			if ( ! empty( $r['destination-url-do-not-send-traffic'] ) ) {
				$content->extra->original_url = $r['destination-url-do-not-send-traffic'];
			}
			if ( ! empty( $r['original_picture'] ) ) {
				$content->extra->original_url = $r['original_picture'];
			}
			// get merchant info
			$this->fillMerchantInfo( $content );

			$data[] = $content;
		}

		return $data;
	}

	public function doRequestItems( array $items ) {
		$product_ids = array();
		foreach ( $items as $key => $item ) {
			if ( empty( $item['extra']['article'] ) ) {
				throw new \Exception( 'doRequestItems request error.' );
			}

			$product_ids[] = $item['extra']['article'];
		}

		$client  = $this->getApiClient();
		$results = $client->product( $product_ids );

		if ( ! isset( $results['offers'] ) || ! isset( $results['offers']['offer'] ) ) {
			throw new \Exception( 'doRequestItems request error.' );
		}
		$results = $results['offers']['offer'];
		if ( ! isset( $results[0] ) && isset( $results['name'] ) ) {
			$results = array( $results );
		}

		// article ID not unique?!..
		$ordered_results = array();
		foreach ( $results as $r ) {
			$ordered_results[ $r['@attributes']['id'] ] = $r;
		}

		// assign new price
		foreach ( $items as $key => $item ) {
			if ( ! isset( $ordered_results[ $item['extra']['productId'] ] ) ) {
				continue;
			}
			$r                      = $ordered_results[ $item['extra']['productId'] ];
			$items[ $key ]['price'] = (float) $r['price'];
			if ( ! empty( $r['oldprice'] ) ) {
				$items[ $key ]['priceOld'] = (float) $r['oldprice'];
			}
			$items[ $key ]['availability'] = (bool) $r['@attributes']['available'];

			if ( (bool) $r['@attributes']['available'] ) {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
			} else {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			}


			$items[ $key ]['url'] = self::generateAffiliateUrl( $r['url'] );
		}

		return $items;
	}

	private function fillMerchantInfo( $content ) {
		$merchant_id = $content->extra->merchantId;

		if ( $this->merchants === null ) {
			try {
				$results = $this->getApiClient()->getMerhants();
			} catch ( \Exception $e ) {
				$results = array();
			}
			if ( ! is_array( $results ) ) {
				$results = array();
			}

			foreach ( $results as $r ) {
				$this->merchants[ $r['_id'] ] = $r['name'];
			}
		}
		if ( isset( $this->merchants[ $merchant_id ] ) ) {
			$merhant_name = $this->merchants[ $merchant_id ];
			$merhant_name = preg_replace( '/_new$/', '', $merhant_name );

			if ( TextHelper::isValidDomainName( $merhant_name ) ) {
				$content->domain = $merhant_name;
			} elseif ( ! empty( $content->extra->original_url ) ) {
				$content->domain = TextHelper::getHostName( $content->extra->original_url );
			} elseif ( ! empty( $content->extra->original_picture ) ) {
				$content->domain = TextHelper::getHostName( $content->extra->original_picture );
				if ( $content->domain == 'alicdn.com' ) {
					$content->domain = 'aliexpress.com';
				}
			}
		}
	}

	private function generateAffiliateUrl( $url ) {
		$sub_id    = $this->config( 'subid' );
		$url_parts = parse_url( $url );

		// add sub_id
		if ( isset( $url_parts['query'] ) ) {
			$query = $url_parts['query'];
		} else {
			$query = '';
		}
		parse_str( $query, $query_array );
		if ( $sub_id ) {
			$query_array['sub_id'] = $sub_id;
		}

		// add vendor id
		$path_array    = explode( '/', $url_parts['path'] );
		$path_array[1] = self::VENDOR_ID;
		$path          = join( '/', $path_array );

		$url = $url_parts['scheme'] . '://' . $url_parts['host'] . $path;
		if ( $query_array ) {
			$url .= '?' . http_build_query( $query_array );
		}

		return $url;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new GdeSlonApi( $this->config( 'api_key' ) );
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
