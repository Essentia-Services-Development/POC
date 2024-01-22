<?php

namespace ContentEgg\application\modules\TradetrackerCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\tradetracker\TradetrackerSoap;
use ContentEgg\application\components\ContentCoupon;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * TradetrackerCouponsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class TradetrackerCouponsModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Tradetracker Coupons',
			'description' => sprintf( __( 'Adds coupons from %s.', 'content-egg' ), 'Tradetracker' ),
		);
	}

	public function releaseVersion() {
		return '3.6.0';
	}

	public function getParserType() {
		return self::PARSER_TYPE_COUPON;
	}

	public function defaultTemplateName() {
		return 'coupons';
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

		if ( isset( $query_params['itemsType'] ) ) {
			$itemsType = $query_params['itemsType'];
		} elseif ( $this->config( 'itemsType' ) ) {
			$itemsType = $this->config( 'itemsType' );
		}

		$fields = array(
			'campaignID',
			'campaignCategoryID',
			'materialBannerDimensionID',
		);

		foreach ( $fields as $f ) {
			if ( $this->config( $f ) ) {
				$options[ $f ] = $this->config( $f );
			}
		}
		if ( $this->config( 'includeUnsubscribedCampaigns' ) ) {
			$options['includeUnsubscribedCampaigns'] = 1;
		}

		switch ( $itemsType ) {
			case 'offer':
				$methodName = 'getMaterialIncentiveOfferItems';
				break;
			case 'text':
				$methodName = 'getMaterialIncentiveTextItems';
				break;
			default: //voucher
				$methodName = 'getMaterialIncentiveVoucherItems';
				break;
		}
		$results = $this->getApiClient()->$methodName( $keyword, $options, 'html' );

		if ( ! is_array( $results ) ) {
			return array();
		}
		if ( ! isset( $results[0] ) && isset( $results['ID'] ) ) {
			$results = array( $results );
		}

		return $this->prepareResults( $results );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content              = new ContentCoupon;
			$content->unique_id   = $r['ID'];
			$content->description = strip_tags( $r['description'] );
			$content->domain      = TextHelper::getHostName( $r['campaign']['URL'] );
			$content->title       = strip_tags( $r['name'] );

			if ( $r['validFromDate'] ) {
				$content->startDate = strtotime( $r['validFromDate'] );
			}
			if ( $r['validToDate'] ) {
				$content->endDate = strtotime( $r['validToDate'] );
			}
			if ( $r['voucherCode'] ) {
				$content->code = $r['voucherCode'];
			}

			// parse link code html
			$doc = new \DOMDocument();
			@$doc->loadHTML( $r['code'] );

			if ( $links = $doc->getElementsByTagName( 'a' ) ) {
				if ( ! $content->title ) {
					$content->title = trim( $links->item( 0 )->nodeValue );
				}
				if ( ! $content->url ) {
					$content->url = trim( $links->item( 0 )->getAttribute( 'href' ) );
				}
			}

			if ( ! $content->description ) {
				$content->description = strip_tags( $r['code'] );
			}

			$content->extra = new ExtraDataTradetrackerCoupons;
			ExtraDataTradetrackerCoupons::fillAttributes( $content->extra, $r );

			$data[] = $content;
		}

		return $data;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new TradetrackerSoap( $this->config( 'customerID' ), $this->config( 'passphrase' ), $this->config( 'locale' ), $this->config( 'affiliateSiteID' ) );
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
