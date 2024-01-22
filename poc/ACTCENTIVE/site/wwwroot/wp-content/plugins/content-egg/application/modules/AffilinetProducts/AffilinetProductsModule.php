<?php

namespace ContentEgg\application\modules\AffilinetProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\affilinet\AffilinetProducts;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * AffilinetProductsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class AffilinetProductsModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Affilinet Products',
			'description' => __( 'Adds products from Affili.net. You must have approval from each program separately.', 'content-egg' ),
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
			$options['PageSize'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['PageSize'] = $this->config( 'entries_per_page' );
		}

		if ( $this->config( 'ShopIds' ) ) {
			$options['ShopIds'] = TextHelper::commaList( $this->config( 'ShopIds' ) );
		}
		if ( $this->config( 'CategoryIds' ) ) {
			$options['CategoryIds'] = TextHelper::commaList( $this->config( 'CategoryIds' ) );
		}
		if ( $this->config( 'ShopIdMode' ) ) {
			$options['ShopIdMode'] = $this->config( 'ShopIdMode' );
		}

		if ( ! empty( $query_params['MinimumPrice'] ) ) {
			$options['MinimumPrice'] = (float) $query_params['MinimumPrice'];
		} elseif ( $this->config( 'MinimumPrice' ) ) {
			$options['MinimumPrice'] = (float) $this->config( 'MinimumPrice' );
		}

		if ( ! empty( $query_params['MaximumPrice'] ) ) {
			$options['MaximumPrice'] = (float) $query_params['MaximumPrice'];
		} elseif ( $this->config( 'MaximumPrice' ) ) {
			$options['MaximumPrice'] = (float) $this->config( 'MaximumPrice' );
		}

		if ( $this->config( 'WithImageOnly' ) ) {
			$options['WithImageOnly'] = 'true';
		} else {
			$options['WithImageOnly'] = 'false';
		}

		$options['UseAffilinetCategories'] = $this->config( 'UseAffilinetCategories' );
		$options['SortBy']                 = $this->config( 'SortBy' );
		$options['SortOrder']              = $this->config( 'SortOrder' );

		/*
		 * A comma separated list of the standard image size variants you
		  want to obtain for the products. This comma separated list can
		  contain the following entries:
		  - NoImage
		  - Image30
		  - Image60
		  - Image90
		  - Image120
		  - Image180
		  - OriginalImage
		  If no image scale is specified, then the product gets delivered
		  with no image information.
		 */
		$options['ImageScales'] = 'OriginalImage';

		/*
		  - NoLogo
		  - Logo50
		  - Logo90
		  - Logo120
		  - Logo150
		  - Logo468
		 */
		$options['LogoScales'] = 'Logo150';

		if ( TextHelper::isEan( $keyword ) ) {
			// wrong format in affilinet?
			if ( strlen( $keyword ) == 13 ) {
				$keyword = '0' . $keyword;
			}
			$results = $this->getApiClient()->searchEan( $keyword, $options ); // EAN search
		} else {
			$results = $this->getApiClient()->search( $keyword, $options );
		} // keyword search

		if ( ! is_array( $results ) || ! isset( $results['Products'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['Products'] );
	}

	public function doRequestItems( array $items ) {
		$item_ids = array_keys( $items );
		$results  = $this->getApiClient()->products( $item_ids );

		if ( ! is_array( $results ) || ! isset( $results['Products'] ) ) {
			throw new \Exception( 'doRequestItems request error.' );
		}

		$results = $results['Products'];

		$sorted_results = array();
		foreach ( $results as $r ) {
			$sorted_results[ $r['ProductId'] ] = $r;
		}

		foreach ( $items as $i => $item ) {
			if ( ! isset( $sorted_results[ $i ] ) ) {
				continue;
			}

			$r = $sorted_results[ $i ];
			// API do not guarantee the uniqueness of the productId. They replace one product with another with the same productId.
			//if ($r['ProductName'] != $item['title']) // product title can be edited
			if ( ( ! empty( $item['extra']['ShopId'] ) && $item['extra']['ShopId'] != $r['ShopId'] ) || ( ! empty( $item['extra']['ArticleNumber'] ) && $item['extra']['ArticleNumber'] != $r['ArticleNumber'] ) ) {
				$items[ $i ]['availability'] = false;
				$items[ $i ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
				continue;
			}
			$items[ $i ]['stock_status']               = ContentProduct::STOCK_STATUS_IN_STOCK;
			$items[ $i ]['price']                      = (float) $r['PriceInformation']['PriceDetails']['Price'];
			$items[ $i ]['priceOld']                   = (float) $r['PriceInformation']['PriceDetails']['PriceOld'];
			$items[ $i ]['extra']['DisplayPrice']      = $r['PriceInformation']['DisplayPrice'];
			$items[ $i ]['extra']['PricePrefix']       = $r['PriceInformation']['PriceDetails']['PricePrefix'];
			$items[ $i ]['extra']['PriceSuffix']       = $r['PriceInformation']['PriceDetails']['PriceSuffix'];
			$items[ $i ]['extra']['DisplayShipping']   = $r['PriceInformation']['DisplayShipping'];
			$items[ $i ]['extra']['ShippingPrefix']    = $r['PriceInformation']['ShippingDetails']['ShippingPrefix'];
			$items[ $i ]['extra']['ShippingSuffix']    = $r['PriceInformation']['ShippingDetails']['ShippingSuffix'];
			$items[ $i ]['extra']['DisplayBasePrice']  = $r['PriceInformation']['DisplayBasePrice'];
			$items[ $i ]['extra']['BasePricePrefix']   = $r['PriceInformation']['BasePriceDetails']['BasePricePrefix'];
			$items[ $i ]['extra']['BasePriceSuffix']   = $r['PriceInformation']['BasePriceDetails']['BasePriceSuffix'];
			$items[ $i ]['extra']['LastShopUpdate']    = $r['LastShopUpdate'];
			$items[ $i ]['extra']['LastProductChange'] = $r['LastProductChange'];
			$items[ $i ]['url']                        = $r['Deeplink1'];
			if ( \is_ssl() ) {
				$items[ $i ]['url'] = preg_replace( '/^http:\/\//', 'https://', $items[ $i ]['url'] );
			}
		}

		return $items;
	}

	private function prepareResults( $results ) {
		$data = array();

		foreach ( $results as $key => $r ) {
			$content        = new ContentProduct;
			$content->title = $r['ProductName'];
			//$content->merchant = $r['ShopTitle'];
			$content->logo         = ( ! empty( $r['Logos'] ) ) ? $r['Logos'][0]['URL'] : '';
			$content->logo         = str_replace( 'http://', 'https://', $content->logo );
			$content->domain       = TextHelper::parseDomain( $r['Deeplink1'], 'diurl' );
			$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;

			if ( $content->domain == 'doubleclick.net' ) {
				$query = parse_url( $r['Deeplink1'], PHP_URL_QUERY );
				parse_str( $query, $params );
				if ( preg_match( '/tag_for_child_directed_treatment=\?(.+?)\?/', $params['diurl'], $matches ) ) {
					$content->domain = TextHelper::getHostName( $matches[1] );
				}
			} elseif ( $content->domain == 'cptrack.de' ) {
				$query = parse_url( $r['Deeplink1'], PHP_URL_QUERY );
				parse_str( $query, $params );
				if ( preg_match( '/rdlink=\?(.+?)\?/', $params['diurl'], $matches ) ) {
					$content->domain = TextHelper::getHostName( $matches[1] );
				}
			}

			$content->category = $r['AffilinetCategoryPath'];
			$content->url      = $r['Deeplink1'];

			// Affili.net reported a problem as my site runs on https and the affiliate
			// link runs on http. It is not possible to track the referrer for the affiliate
			// network, which is a big problem is the eyes of the Quality Management of affili.net.
			if ( \is_ssl() ) {
				$content->url = preg_replace( '/^http:\/\//', 'https://', $content->url );
			}

			$content->unique_id    = $r['ProductId'];
			$content->manufacturer = $r['Manufacturer'];
			$content->ean          = $r['EAN'];
			$content->img          = ( ! empty( $r['Images'] ) ) ? $r['Images'][0][0]['URL'] : '';
			$content->currencyCode = $r['PriceInformation']['Currency'];
			$content->currency     = TextHelper::currencyTyping( $content->currencyCode );
			$content->price        = (float) $r['PriceInformation']['PriceDetails']['Price'];
			$content->priceOld     = (float) $r['PriceInformation']['PriceDetails']['PriceOld'];
			$content->img          = ( ! empty( $r['Images'] ) ) ? $r['Images'][0][0]['URL'] : '';
			$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;

			/*
			  //Currently not in use
			  if ($r['Availability'])
			  $content->availability = (bool)$r['Availability'];
			 */
			if ( ! empty( $r['Description'] ) ) {
				$content->description = strip_tags( $r['Description'] );
			} elseif ( ! empty( $r['DescriptionShort'] ) && $r['DescriptionShort'] != $content->title ) {
				$content->description = strip_tags( $r['DescriptionShort'] );
			}
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}


			$content->extra = new ExtraDataAffilinetProducts;
			ExtraDataAffilinetProducts::fillAttributes( $content->extra, $r );

			$content->extra->DisplayPrice = $r['PriceInformation']['DisplayPrice'];
			$content->extra->PricePrefix  = $r['PriceInformation']['PriceDetails']['PricePrefix'];
			$content->extra->PriceSuffix  = $r['PriceInformation']['PriceDetails']['PriceSuffix'];

			$content->extra->DisplayShipping = $r['PriceInformation']['DisplayShipping'];
			$content->extra->ShippingPrefix  = $r['PriceInformation']['ShippingDetails']['ShippingPrefix'];
			$content->extra->ShippingSuffix  = $r['PriceInformation']['ShippingDetails']['ShippingSuffix'];

			$content->extra->DisplayBasePrice = $r['PriceInformation']['DisplayBasePrice'];
			$content->extra->BasePricePrefix  = $r['PriceInformation']['BasePriceDetails']['BasePricePrefix'];
			$content->extra->BasePriceSuffix  = $r['PriceInformation']['BasePriceDetails']['BasePriceSuffix'];
			$content->extra->LastShopUpdate   = (int) $content->extra->LastShopUpdate;

			/* The URL, through which you not only reach the detail page of this
			 * product on the shop site, but this product is at the same time added
			 * to the customer’s shopping cart (not available for all shops).
			 */
			$content->extra->addToCartUrl = $r['Deeplink2'];

			foreach ( $r['Properties'] as $property ) {
				$p_name              = str_replace( 'CF_', '', $property['PropertyName'] );
				$p_name              = str_replace( '_', ' ', $p_name );
				$feature             = array(
					'name'  => $p_name,
					'value' => $property['PropertyValue'],
				);
				$content->features[] = $feature;
			}
			$content->extra->Properties = array();


			$data[] = $content;
		}

		return $data;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new AffilinetProducts( $this->config( 'service_password' ), $this->config( 'PublisherId' ) );
		}

		return $this->api_client;
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
