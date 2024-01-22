<?php

namespace ContentEgg\application\modules\TradedoublerCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\tradedoubler\TradedoublerCoupons;
use ContentEgg\application\components\ContentCoupon;
use ContentEgg\application\admin\PluginAdmin;

/**
 * TradedoublerCouponsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class TradedoublerCouponsModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Tradedoubler Coupons',
			'description' => __( 'Adds coupons from Tradedoubler.', 'content-egg' ) . ' ' . __( 'You must get approve for each program separately.', 'content-egg' ),
		);
	}

	public function defaultTemplateName() {
		return 'coupons';
	}

	public function getParserType() {
		return self::PARSER_TYPE_COUPON;
	}

	public function isFree() {
		return false;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();
		if ( $is_autoupdate ) {
			$options['pageSize'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['pageSize'] = $this->config( 'entries_per_page' );
		}

		if ( isset( $query_params['voucherTypeId'] ) && $query_params['voucherTypeId'] ) {
			$options['voucherTypeId'] = $query_params['voucherTypeId'];
		} elseif ( $this->config( 'voucherTypeId' ) ) {
			$options['voucherTypeId'] = $this->config( 'voucherTypeId' );
		}

		$fields = array( 'programId', 'siteSpecific', 'voucherTypeId', 'languageId' );
		foreach ( $fields as $field ) {
			if ( $this->config( $field ) ) {
				$options[ $field ] = $this->config( $field );
			}
		}
		//$options['siteSpecific'] = var_export((bool) $this->config('siteSpecific'), true);
		//Set to 'iso8601' if you want dates returned in ISO8601 format
		$options['dateOutputFormat'] = 'iso8601';

		$results = $this->getApiClient()->search( $keyword, $options );
		if ( ! $results ) {
			return array();
		}

		return $this->prepareResults( $results );
	}

	private function prepareResults( $results ) {
		$data = array();

		foreach ( $results as $key => $r ) {
			$content            = new ContentCoupon;
			$content->unique_id = $r['id'];
			$content->title     = $r['title'];
			$content->img       = $r['logoPath'];
			$content->url       = $r['defaultTrackUri'];

			if ( $r['code'] ) {
				$content->code = $r['code'];
			}
			if ( $r['description'] ) {
				$content->description = $r['description'];
			} elseif ( $r['shortDescription'] ) {
				$content->description = $r['shortDescription'];
			}
			if ( $r['startDate'] ) {
				$content->startDate = strtotime( $r['startDate'] );
			}
			if ( $r['endDate'] ) {
				$content->endDate = strtotime( $r['endDate'] );
			}
			$content->extra = new ExtraDataTradedoublerCoupons;
			ExtraDataTradedoublerCoupons::fillAttributes( $content->extra, $r );

			$data[] = $content;
		}

		return $data;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new TradedoublerCoupons( $this->config( 'token' ) );
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
