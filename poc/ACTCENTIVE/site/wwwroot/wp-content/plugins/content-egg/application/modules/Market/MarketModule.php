<?php

namespace ContentEgg\application\modules\Market;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\yandex\MarketContentApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\components\ExtraData;
use ContentEgg\application\helpers\TextHelper;

/**
 * MarketModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class MarketModule extends ParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'          => 'Market',
			'api_agreement' => 'https://legal.yandex.ru/market_api_content/',
			'description'   => __( 'Add products from the Russian-speaking catalog of Yandex.Market', 'content-egg' )
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_CONTENT;
	}

	public function isDeprecated() {
		return true;
	}

	public function defaultTemplateName() {
		return 'data_item';
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$params = array();
		if ( $is_autoupdate ) {
			$params['count'] = $this->config( 'entries_per_page_update' );
		} else {
			$params['count'] = $this->config( 'entries_per_page' );
		}

		$params['geo_id'] = $this->config( 'geo_id' );

		try {
			$results = $this->getMarketClient()->search( $keyword, $params );
		} catch ( Exception $e ) {
			throw new \Exception( strip_tags( $e->getMessage() ) );
		}

		if ( ! $results || ! isset( $results['searchResult'] ) || ! is_array( $results['searchResult']['results'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['searchResult']['results'] );
	}

	private function prepareResults( $results ) {
		$data = array();

		foreach ( $results as $key => $r ) {
			if ( empty( $r['model'] ) ) {
				continue;
			}

			$r                  = $r['model'];
			$model_id           = $r['id'];
			$content            = new ContentProduct;
			$content->unique_id = $model_id;
			$content->extra     = new ExtraDataMarket();

			if ( $this->config( 'get_offers' ) ) {
				try {
					$content->extra->offers = $this->getOffers( $model_id );
				} catch ( Exception $e ) {
					continue;
				}
			}
			if ( $this->config( 'get_opinions' ) ) {
				try {
					$content->extra->opinions = $this->getOpinions( $model_id );
				} catch ( Exception $e ) {
					continue;
				}
			}

			$content->title       = $r['name'];
			$content->description = $r['description'];
			if ( ! empty( $r['mainPhoto'] ) ) {
				$content->img = $r['mainPhoto']['url'];
			}
			$content->url = $r['link'];

			if ( ! empty( $r['prices'] ) ) {
				$content->price        = $r['prices']['avg'];
				$content->currencyCode = $r['prices']['curCode'];
				$content->currency     = $r['prices']['curName'];

				$content->extra->priceMax = $r['prices']['max'];
				$content->extra->priceMin = $r['prices']['min'];
			}

			$content->rating       = $r['rating'];
			$content->reviewsCount = $r['reviewsCount'];

			ExtraDataMarket::fillAttributes( $content->extra, $r );
			$data[] = $content;
		}

		return $data;
	}

	private function getOffers( $model_id ) {
		$params           = array();
		$params['geo_id'] = $this->config( 'geo_id' );
		$params['count']  = $this->config( 'offers_count' );

		$results = $this->getMarketClient()->offers( $model_id, $params );
		if ( ! isset( $results['offers'] ) || ! isset( $results['offers']['items'] ) ) {
			return array();
		}

		$data = array();
		foreach ( $results['offers']['items'] as $r ) {
			$offer = new ExtraMarketOffer();

			if ( isset( $r['price'] ) ) {
				$offer->price        = $r['price']['value'];
				$offer->currency     = $r['price']['currencyName'];
				$offer->currencyCode = $r['price']['currencyCode'];
			}
			$offer->onStock = (bool) $r['onStock'];
			$offer->name    = $r['name'];
			$offer->url     = $r['url'];
			$offer->id      = $r['id'];
			if ( isset( $offer->description ) ) {
				$offer->description = $r['description'];
			}
			$offer->shopId         = $r['shopInfo']['id'];
			$offer->shopName       = $r['shopInfo']['name'];
			$offer->shopRating     = $r['shopInfo']['rating'];
			$offer->shopGradeTotal = $r['shopInfo']['gradeTotal'];
			$offer->delivery       = $r['delivery'];
			$offer->warranty       = (bool) $r['warranty'];
			if ( isset( $r['previewPhotos'] ) ) {
				$offer->img = $r['previewPhotos'][0]['url'];
			}
			$data[] = $offer;
		}

		return $data;
	}

	private function getOpinions( $model_id ) {
		$params          = array();
		$params['count'] = $this->config( 'opinions_count' );
		$params['sort']  = $this->config( 'opinions_sort' );
		$results         = $this->getMarketClient()->opinions( $model_id, $params );
		if ( ! isset( $results['modelOpinions'] ) || ! isset( $results['modelOpinions']['opinion'] ) ) {
			return array();
		}
		$data = array();

		$opinions_size = $this->config( 'opinions_size' );
		foreach ( $results['modelOpinions']['opinion'] as $r ) {
			$opinion = new ExtraMarketOpinion();
			ExtraData::fillAttributes( $opinion, $r );
			if ( $opinions_size ) {
				$opinion->text   = TextHelper::truncate( $opinion->text, $opinions_size );
				$opinion->pro    = TextHelper::truncate( $opinion->pro, $opinions_size );
				$opinion->contra = TextHelper::truncate( $opinion->contra, $opinions_size );
			}
			/**
			 * grade - Возможные значения: 2; 1; 0; -1; -2.
			 */
			$opinion->grade += 3;

			// 64-bit (?) timestamp fix
			$opinion->date = (string) $r['date'];
			if ( strstr( $opinion->date, '.' ) ) {
				$opinion->date = (float) $opinion->date / 1000;
			} elseif ( strlen( $opinion->date ) > 10 ) {
				$opinion->date = preg_replace( '/000$/', '', $opinion->date );
			}
			$opinion->date = (int) $opinion->date;


			$data[] = $opinion;
		}

		return $data;
	}

	private function getMarketClient() {
		if ( $this->api_client === null ) {
			if ( $this->config( 'api_key' ) ) {
				$this->api_client = new MarketContentApi( $this->config( 'api_key' ) );
			} else {
				throw \Exception( 'API key must be specified.' );
			}
			/*
			  // @todo: web parser
			  $this->api_client = new MarketContentParser();
			 */
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
