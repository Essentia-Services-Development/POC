<?php

namespace ContentEgg\application\modules\Zanox;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\zanox\ZanoxApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * ZanoxModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ZanoxModule extends AffiliateParserModule {

	private $merchants = array();
	private $api_client = null;

	public function info() {
		return array(
			'name'          => 'Zanox',
			'api_agreement' => 'http://apps.zanox.com/web/guest/tac_developer',
			'description'   => __( 'Adds products from zanox.com. You must have approval from each program separately.', 'content-egg' ),
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
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$options['items'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['items'] = $this->config( 'entries_per_page' );
		}

		if ( (int) $this->config( 'adspace' ) ) {
			$options['adspace'] = (int) $this->config( 'adspace' );
		}
		if ( $this->config( 'searchtype' ) ) {
			$options['searchtype'] = $this->config( 'searchtype' );
		}
		if ( $this->config( 'region' ) ) {
			$options['region'] = $this->config( 'region' );
		}

		if ( ! empty( $query_params['minprice'] ) ) {
			$options['minprice'] = (int) $query_params['minprice'];
		} elseif ( $this->config( 'minprice' ) ) {
			$options['minprice'] = (int) $this->config( 'minprice' );
		}
		if ( ! empty( $query_params['maxprice'] ) ) {
			$options['maxprice'] = (int) $query_params['maxprice'];
		} elseif ( $this->config( 'maxprice' ) ) {
			$options['maxprice'] = (int) $this->config( 'maxprice' );
		}
		/*
		  if ($this->config('hasimages'))
		  $options['hasimages'] = (bool) $this->config('hasimages');
		 *
		 */
		if ( $this->config( 'partnership' ) ) {
			$options['partnership'] = $this->config( 'partnership' );
		}
		if ( $this->config( 'programs' ) ) {
			$options['programs'] = $this->config( 'programs' );
		}

		if ( TextHelper::isEan( $keyword ) ) {
			$results = $this->getZanoxClient()->searchEan( $keyword, $options );
		} // EAN search
		else {
			$results = $this->getZanoxClient()->search( $keyword, $options );
		} // keyword search

		if ( ! isset( $results['productItems']['productItem'] ) || ! is_array( $results ) ) {
			return array();
		}

		return $this->prepareResults( $results['productItems']['productItem'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content               = new ContentProduct;
			$content->unique_id    = $r['@id'];
			$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			$content->title        = $r['name'];
			$content->price        = (float) $r['price'];
			if ( (float) $r['priceOld'] ) {
				$content->priceOld = (float) $r['priceOld'];
			}
			$content->currencyCode = $r['currency'];
			$content->currency     = TextHelper::currencyTyping( $content->currencyCode );
			if ( ! empty( $r['image'] ) ) {
				if ( isset( $r['image']['large'] ) ) {
					$content->img = $r['image']['large'];
				} elseif ( isset( $r['image']['medium'] ) ) {
					$content->img = $r['image']['medium'];
				} elseif ( isset( $r['image']['small'] ) ) {
					$content->img = $r['image']['small'];
				}
			}
			if ( ! empty( $r['trackingLinks'] ) ) {
				$content->url = $r['trackingLinks']['trackingLink'][0]['ppc'];
			}
			if ( ! empty( $r['descriptionLong'] ) ) {
				$content->description = strip_tags( $r['descriptionLong'] );
			}
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}

			if ( ! empty( $r['manufacturer'] ) ) {
				$content->manufacturer = $r['manufacturer'];
			}
			if ( ! empty( $r['category'] ) && isset( $r['category']['$'] ) ) {
				$content->category = $r['category']['$'];
			}
			if ( ! empty( $r['program'] ) && isset( $r['program']['$'] ) ) {
				$content->merchant = $r['program']['$'];
			}

			$content->extra            = new ExtraDataZanox;
			$content->extra->modified  = strtotime( $r['modified'] );
			$content->extra->programId = $r['program']['@id'];
			if ( ! empty( $r['deliveryTime'] ) ) {
				$content->extra->deliveryTime = $r['deliveryTime'];
			}
			if ( ! empty( $r['shippingCosts'] ) ) {
				$content->extra->shippingCosts = $r['shippingCosts'];
			}
			if ( ! empty( $r['shipping'] ) ) {
				$content->extra->shipping = $r['shipping'];
			}
			if ( ! empty( $r['merchantCategory'] ) ) {
				$content->extra->merchantCategory = $r['merchantCategory'];
			}
			if ( ! empty( $r['merchantProductId'] ) ) {
				$content->extra->merchantProductId = $r['merchantProductId'];
			}
			if ( ! empty( $r['trackingImg'] ) ) {
				$content->extra->trackingImg = $r['trackingLinks']['trackingLink'][0]['ppv'];
			}

			// get merchant info
			$this->fillMerchantInfo( $content );

			$data[] = $content;
		}

		return $data;
	}

	private function fillMerchantInfo( $content ) {
		if ( ! $merchant_id = $content->extra->programId ) {
			return;
		}

		if ( ! isset( $this->merchants[ $merchant_id ] ) ) {
			try {
				$result = $this->getZanoxClient()->program( $merchant_id );
			} catch ( \Exception $e ) {
				return;
			}
			if ( ! is_array( $result ) || ! isset( $result['programItem'] ) ) {
				return;
			}
			$this->merchants[ $merchant_id ] = $result['programItem'][0];
		}
		$content->logo = str_replace( 'http://', 'https://', $this->merchants[ $merchant_id ]['image'] );
		//$content->merchant = $this->merchants[$merchant_id]['name']; //Asos.com RU
		$content->domain = TextHelper::getHostName( $this->merchants[ $merchant_id ]['url'] );
	}

	public function doRequestItems( array $items ) {
		foreach ( $items as $key => $item ) {
			$result = $this->getZanoxClient()->product( $item['unique_id'] );

			if ( ! is_array( $result ) || ! isset( $result['productItem'] ) ) {
				throw new \Exception( 'doRequestItems request error.' );
			}
			$result = $result['productItem'][0];

			// assign new price
			$items[ $key ]['price'] = (float) $result['price'];
			if ( (float) $result['priceOld'] ) {
				$items[ $key ]['priceOld'] = (float) $result['priceOld'];
			}

			// url
			if ( ! empty( $result['trackingLinks'] ) ) {
				$items[ $key ]['url'] = $result['trackingLinks']['trackingLink'][0]['ppc'];
			}
		}

		return $items;
	}

	private function getZanoxClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new ZanoxApi( $this->config( 'connectid' ) );
		}

		return $this->api_client;
	}

	public function viewDataPrepare( $data ) {
		foreach ( $data as $key => $d ) {
			$data[ $key ]['merchant'] = self::fixMegratedMerchant( $d['merchant'] );
		}

		return parent::viewDataPrepare( $data );
	}

	public static function fixMegratedMerchant( $name ) {
		$name = preg_replace( '/migrated.+/ims', '', $name );
		$name = trim( $name, " \t\n\r\0\x0B:,-" );

		return $name;
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
