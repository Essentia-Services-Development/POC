<?php

namespace ContentEgg\application\modules\Ozon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\ozon\OzonRest;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\LinkHandler;

/**
 * OzonModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class OzonModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Ozon',
			'description' => __( 'Adds goods from OZON.ru.', 'content-egg' ),
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_PRODUCT;
	}

	public function defaultTemplateName() {
		return 'item';
	}

	public function isItemsUpdateAvailable() {
		return true;
	}

	public function isDeprecated() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		if ( $is_autoupdate ) {
			$options['itemsOnPage'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['itemsOnPage'] = $this->config( 'entries_per_page' );
		}

		if ( $this->config( 'search_category' ) ) {
			$options['startGroupName'] = $this->config( 'search_category' );
		}
		if ( $this->config( 'items_sort_tag' ) ) {
			$options['itemsSortTag'] = $this->config( 'items_sort_tag' );
		}

		$client  = $this->getApiClient();
		$results = $client->search( $keyword, $options );

		if ( isset( $results['SearchedItems'][0]['Id'] ) ) {
			$results = $results['SearchedItems'];
		} else {
			return array();
		}

		$results = $this->parseFeatures( $results );
		$results = $this->parseReviews( $results );

		return $this->prepareResults( $results );
	}

	private function parseFeatures( $results ) {
		if ( ! $this->config( 'features' ) ) {
			return $results;
		}
		$client = $this->getApiClient();
		foreach ( $results as $key => $r ) {
			try {
				$details = $client->details( $r['Id'] );
			} catch ( Exception $e ) {
				continue;
			}
			if ( isset( $details['Detail']['ClassAttributes'][0] ) ) {
				$results[ $key ]['Detail'] = $details['Detail'];
			}
		}

		return $results;
	}

	private function parseReviews( $results ) {
		if ( ! $this->config( 'customer_reviews' ) ) {
			return $results;
		}
		$client = $this->getApiClient();
		foreach ( $results as $key => $r ) {
			if ( $key >= $this->config( 'review_products_number' ) || $key > 2 ) {
				break;
			}

			$params['detailId']    = $r['Id'];
			$params['sortTag']     = $this->config( 'review_sort' );
			$params['itemsOnPage'] = $this->config( 'review_number' );

			try {
				$reviews = $client->reviews( $params );
			} catch ( Exception $e ) {
				continue;
			}
			if ( isset( $reviews['Comments'][0] ) ) {
				$results[ $key ]['Reviews'] = $reviews['Comments'];
			}
		}

		return $results;
	}

	private function prepareResults( $results ) {
		$data = array();

		$deeplink = $this->getDeeplink();
		foreach ( $results as $key => $r ) {
			$content        = new ContentProduct;
			$content->extra = new ExtraDataOzon;

			$content->unique_id    = $r['Id'];
			$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			$content->domain       = 'ozon.ru';
			$content->merchant     = 'OZON.ru';
			$content->orig_url     = 'http://www.ozon.ru/context/detail/id/' . $r['Id'] . '/';
			$content->url          = LinkHandler::createAffUrl( $content->orig_url, $deeplink );

			$content->title        = strip_tags( $r['Name'] );
			$content->currencyCode = 'RUB';
			$content->currency     = TextHelper::currencyTyping( $content->currencyCode );

			$r['Price']         = (float) $r['Price'];
			$r['DiscountPrice'] = (float) $r['DiscountPrice'];

			if ( $r['DiscountPrice'] < $r['Price'] ) {
				$content->price    = $r['DiscountPrice'];
				$content->priceOld = $r['Price'];
			} else {
				$content->price = $r['Price'];
			}

			if ( $r['Annotation'] ) {
				$content->description = trim( strip_tags( $r['Annotation'] ) );
			}

			if ( $r['Path'] ) {
				if ( ! preg_match( "/^http:/msi", $r['Path'] ) ) {
					$content->img = "http://static2.ozone.ru/multimedia/" . trim( strip_tags( $r['Path'] ) );
				} else {
					$content->img = trim( strip_tags( $r['Path'] ) );
				}

				$content->img = str_replace( '/l60/', '/spare_covers/', $content->img );
			}

			if ( $r['ClientRating'] ) {
				$content->rating = ceil( $r['ClientRating'] );
			}

			ExtraDataOzon::fillAttributes( $content->extra, $r );

			$this->fillFromDetails( $r, $content );
			foreach ( $content->extra->Detail as $f_name => $f_value ) {
				$feature             = array(
					'name'  => $f_name,
					'value' => $f_value,
				);
				$content->features[] = $feature;
			}
			$content->extra->Detail = array();

			$this->fillFromReviews( $r, $content );
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}
			$data[] = $content;
		}

		return $data;
	}

	public function doRequestItems( array $items ) {
		$deeplink = $this->getDeeplink();
		foreach ( $items as $key => $item ) {
			$result = $this->getApiClient()->price( $item['unique_id'] );
			if ( ! is_array( $result ) || ! isset( $result['ItemPrice'] ) ) {
				throw new \Exception( 'doRequestItems request error.' );
			}

			$r = $result['ItemPrice'];

			// assign new price
			$r['Price']         = (float) $r['Price'];
			$r['DiscountPrice'] = (float) $r['DiscountPrice'];
			if ( $r['DiscountPrice'] < $r['Price'] ) {
				$items[ $key ]['price']    = $r['DiscountPrice'];
				$items[ $key ]['priceOld'] = $r['Price'];
			} else {
				$items[ $key ]['price'] = $r['Price'];
			}
			$items[ $key ]['domain'] = 'ozon.ru';

			if ( $r['ItemAvailabilityID'] == 1 ) // На складе
			{
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
			} else {
				$items[ $key ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			}

			// update url if deeplink changed
			if ( $item['orig_url'] ) {
				$items[ $key ]['url'] = LinkHandler::createAffUrl( $item['orig_url'], $deeplink, $item );
			}
		}

		return $items;
	}

	private function getDeeplink() {
		if ( $partner_id = $this->config( 'partner_id' ) ) {
			return "partner=" . $partner_id;
		} else {
			return $this->config( 'deeplink' );
		}
	}

	private function fillFromDetails( $r, $content ) {
		if ( ! isset( $r['Detail']['ClassAttributes'][0] ) || ! $r['Detail']['ClassAttributes'][0] ) {
			return;
		}

		$extra             = $content->extra;
		$extra->Detail     = array();
		$extra->Capability = array();
		$extra->Gallery    = array();

		foreach ( $r['Detail']['ClassAttributes'] as $key => $d ) {
			//Изображение
			$value = trim( strip_tags( $d['Value'] ) );

			if ( $d['Tag'] == 'Name' ) {
				$content->title = strip_tags( $value );
			} elseif ( $d['Tag'] == 'Annotation' ) {
				$content->description = $value;
			} elseif ( $d['Tag'] == 'Picture' && ! $content->img ) {
				$content->img = "http://static2.ozone.ru/multimedia/" . $value;
			} elseif ( $d['Tag'] == 'Images' ) {
				$extra->Gallery[] = "http://static2.ozone.ru/multimedia/" . $value;
			} elseif ( ! empty( $d['Detail']['ClassAttributes'][0] ) && $d['Detail']['ClassAttributes'][0] ) {
				if ( $d['Tag'] == 'Capability' ) {
					//характеристики
					foreach ( $d['Detail']['ClassAttributes'] as $capability ) {
						if ( $capability['Tag'] == 'Name' || $capability['Tag'] == 'Type' ) {
							continue;
						}
						if ( $capability['Tag'] == 'Annotation' ) //Annotation может быть как в основых Details так и в Annotation=>Capability
						{
							$content->description = trim( strip_tags( $capability['Value'] ) );
						} elseif ( $capability['Value'] != null ) {
							if ( isset( $extra->Detail[ trim( strip_tags( $capability['Name'] ) ) ] ) ) {
								$extra->Detail[ trim( strip_tags( $capability['Name'] ) ) ] = $extra->Detail[ trim( strip_tags( $capability['Name'] ) ) ] . ", " . trim( strip_tags( $capability['Value'] ) );
							} else {
								$extra->Detail[ trim( strip_tags( $capability['Name'] ) ) ] = trim( strip_tags( $capability['Value'] ) );
							}
						}
					}
				} else {
					$max_size = $this->config( 'description_size' );
					foreach ( $d['Detail']['ClassAttributes'] as $subdetail ) {
						$name = trim( strip_tags( $d['Name'] ) ) . ' ' . trim( strip_tags( $subdetail['Name'] ) );
						if ( ! preg_match( '/\.jpg$/', $subdetail['Value'] ) && $subdetail['Value'] != '' ) //не сохраняем картинки и пустые значения
						{
							$extra->Detail[ $name ] = trim( strip_tags( $subdetail['Value'] ) );
						}
						if ( $subdetail['Tag'] == 'Comment' && $max_size ) {
							$extra->Detail[ $name ] = TextHelper::truncate( $extra->Detail[ $name ], $max_size );
						}
					}
				}
			} elseif ( $value != null ) {
				$extra->Detail[ trim( strip_tags( $d['Name'] ) ) ] = $value;
			}
		}
	}

	private function fillFromReviews( $r, $content ) {
		if ( empty( $r['Reviews'] ) ) {
			return;
		}

		$truncate = $this->config( 'truncate_reviews' );

		$content->extra->Reviews = array();
		foreach ( $r['Reviews'] as $key => $rw ) {
			$review = new ExtraDataOzonReviews();
			ExtraDataOzon::fillAttributes( $review, $rw );
			$review->Date = preg_replace( '/[^\d]/msi', '', $rw['Date']['DateTime'] );
			$review->Date = substr_replace( $review->Date, '', - 3, 3 );
			if ( $truncate ) {
				$review->Comment = TextHelper::truncate( $review->Comment, $truncate );
			}
			$content->extra->Reviews[] = $review;
		}
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new OzonRest( base64_decode( 'YmlnYWRnZXRfYmxvZw==' ), base64_decode( 'VU9Hd2FNY00xRQ==' ) );
		}

		return $this->api_client;
	}

	public function viewDataPrepare( $data ) {
		$deeplink = $this->getDeeplink();
		foreach ( $data as $key => $d ) {
			if ( $d['orig_url'] ) {
				$data[ $key ]['url'] = LinkHandler::createAffUrl( $d['orig_url'], $deeplink, $d );
			}
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
