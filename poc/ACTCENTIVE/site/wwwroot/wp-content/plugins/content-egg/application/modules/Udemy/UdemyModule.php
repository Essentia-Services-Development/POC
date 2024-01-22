<?php

namespace ContentEgg\application\modules\Udemy;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\udemy\UdemyApi;
use ContentEgg\application\modules\Udemy\ExtraDataUdemy;
use ContentEgg\application\components\LinkHandler;

/**
 * UdemyModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class UdemyModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Udemy',
			'description' => sprintf( __( 'Adds cources from %s.', 'content-egg' ), 'Udemy.com' ),
		);
	}

	public function releaseVersion() {
		return '3.4.0';
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

		$options['page_size'] = $limit;
		$options['page']      = 1;

		$options['fields[course]'] = '@all,-description';

		$params = array(
			'language',
			'ordering',
			'category',
			'subcategory',
			'price',
			'is_affiliate_agreed',
			'is_fixed_priced_deals_agreed',
			'is_percentage_deals_agreed',
			'has_closed_caption',
			'has_coding_exercises',
			'has_simple_quiz',
			'instructional_level',
		);

		foreach ( $params as $param ) {
			$value = $this->config( $param );
			if ( $value ) {
				$param_parts = explode( '_', $param, 2 );
				// is boolean param?
				if ( in_array( $param_parts[0], array( 'is', 'has' ) ) ) {
					$options[ $param ] = 'True';
				} else {
					$options[ $param ] = $value;
				}
			}
		}

		$results = $this->getApiClient()->search( $keyword, $options );

		if ( ! isset( $results['results'] ) || ! is_array( $results['results'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['results'] );
	}

	private function prepareResults( $results ) {
		$data     = array();
		$deeplink = $this->config( 'deeplink' );
		foreach ( $results as $key => $r ) {
			$content = new ContentProduct;

			$content->unique_id    = $r['id'];
			$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			$content->domain       = 'udemy.com';
			$content->title        = $r['title'];
			$content->orig_url     = $r['url'];
			if ( ! preg_match( '/^https?:\/\//', $content->orig_url ) ) {
				$content->orig_url = 'https://www.udemy.com' . $content->orig_url;
			}
			$content->url         = LinkHandler::createAffUrl( $content->orig_url, $deeplink );
			$content->description = $r['headline'];

			if ( ! empty( $r['discount_price'] ) ) {
				$content->price        = (float) $r['discount_price']['amount'];
				$content->currencyCode = $r['discount_price']['currency'];
				$content->priceOld     = (float) $r['price_detail']['amount'];
			} else {
				$content->price        = (float) $r['price_detail']['amount'];
				$content->currencyCode = $r['price_detail']['currency'];
			}
			$content->img          = $r['image_480x270'];
			$content->rating       = TextHelper::ratingPrepare( $r['avg_rating'] );
			$content->reviewsCount = (int) $r['num_reviews'];
			$content->category     = $r['primary_category']['title'];

			$content->extra = new ExtraDataUdemy();
			ExtraDataUdemy::fillAttributes( $content->extra, $r );
			$data[] = $content;
		}

		return $data;
	}

	public function doRequestItems( array $items ) {
		$options                   = array();
		$options['fields[course]'] = 'discount_price,price_detail';

		$deeplink = $this->config( 'deeplink' );
		foreach ( $items as $key => $item ) {
			$result = $this->getApiClient()->product( $item['unique_id'], $options );
			if ( ! is_array( $result ) || ! isset( $result['id'] ) ) {
				throw new \Exception( 'doRequestItems request error.' );
			}

			$r = $result;

			// assign new price
			if ( ! empty( $r['discount_price'] ) ) {
				$items[ $key ]['price']        = (float) $r['discount_price']['amount'];
				$items[ $key ]['priceOld']     = (float) $r['price_detail']['amount'];
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
			} elseif ( ! empty( $r['price_detail']['amount'] ) ) {
				$items[ $key ]['price']        = (float) $r['price_detail']['amount'];
				$items[ $key ]['priceOld']     = 0;
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
			} else {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			}

			// new url for theme sync
			$items[ $key ]['url'] = LinkHandler::createAffUrl( $item['orig_url'], $deeplink, $item );
		}

		return $items;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new UdemyApi( $this->config( 'client_id' ), $this->config( 'client_secret' ) );
		}

		return $this->api_client;
	}

	public function viewDataPrepare( $data ) {
		$deeplink = $this->config( 'deeplink' );
		foreach ( $data as $key => $d ) {
			$data[ $key ]['url'] = LinkHandler::createAffUrl( $d['orig_url'], $deeplink, $d );
		}

		return parent::viewDataPrepare( $data );
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

}
