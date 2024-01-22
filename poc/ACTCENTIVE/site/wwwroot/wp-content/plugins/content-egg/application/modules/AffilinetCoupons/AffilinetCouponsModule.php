<?php

namespace ContentEgg\application\modules\AffilinetCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\affilinet\AffilinetCoupons;
use ContentEgg\application\components\ContentCoupon;
use ContentEgg\application\admin\PluginAdmin;

/**
 * AffilinetCouponsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class AffilinetCouponsModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Affilinet Coupons',
			'description' => __( 'Adds coupons from Affili.net. You must have approval from each program separately.', 'content-egg' ),
		);
	}

	public function isDeprecated() {
		return true;
	}

	public function defaultTemplateName() {
		return 'coupons';
	}

	public function getParserType() {
		return self::PARSER_TYPE_COUPON;
	}

	public function isFree() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$options['PageSize'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['PageSize'] = $this->config( 'entries_per_page' );
		}


		if ( isset( $query_params['VoucherType'] ) ) {
			$options['VoucherType'] = (int) $query_params['VoucherType'];
		} else {
			$options['VoucherType'] = (int) $this->config( 'VoucherType' );
		}
		if ( isset( $query_params['VoucherCodeContent'] ) ) {
			$options['VoucherCodeContent'] = (int) $query_params['VoucherCodeContent'];
		} else {
			$options['VoucherCodeContent'] = (int) $this->config( 'VoucherCodeContent' );
		}

		$fields = array( 'ProgramId', 'MinimumOrderValue', 'SortDesc' );
		foreach ( $fields as $field ) {
			$options[ $field ] = $this->config( $field );
		}
		$fields = array( 'OrderBy', 'CustomerRestriction' );
		foreach ( $fields as $field ) {
			$options[ $field ] = (int) $this->config( $field );
		}


		$options['ExclusivesOnly'] = var_export( (bool) $this->config( 'ExclusivesOnly' ), true );

		// 0 = NoRestriction, 1 = Accepted, Waiting, DeclinedOrDeleted, NoPartnership
		$options['PartnershipStatus'] = 1;
		$results                      = $this->getApiClient()->search( $keyword, $options );

		if ( ! is_array( $results ) || ! isset( $results['VoucherCodeCollection']['VoucherCodeItem'] ) ) {
			return array();
		}

		if ( ! isset( $results['VoucherCodeCollection']['VoucherCodeItem'][0] ) && isset( $results['VoucherCodeCollection']['VoucherCodeItem']['Id'] ) ) {
			$results['VoucherCodeCollection']['VoucherCodeItem'] = array( $results['VoucherCodeCollection']['VoucherCodeItem'] );
		}

		return $this->prepareResults( $results['VoucherCodeCollection']['VoucherCodeItem'] );
	}

	private function prepareResults( $results ) {
		$data = array();

		foreach ( $results as $key => $r ) {
			$content            = new ContentCoupon;
			$content->unique_id = $r['Id'];
			$content->title     = $r['Title'];
			$content->img       = 'http://logos.affili.net/120x40/' . $r['ProgramId'] . '.gif';
			if ( $r['Code'] ) {
				$content->code = $r['Code'];
			}
			if ( $r['Description'] ) {
				$content->description = $r['Description'];
			}
			if ( $r['StartDate'] ) {
				$content->startDate = strtotime( $r['StartDate'] );
			}
			if ( $r['EndDate'] ) {
				$content->endDate = strtotime( $r['EndDate'] );
			}

			// parse link code html
			$doc = new \DOMDocument();
			@$doc->loadHTML( $r['IntegrationCode'] );
			if ( $images = $doc->getElementsByTagName( 'img' ) ) {
				// pixel img?
				if ( $images->item( 0 )->getAttribute( 'height' ) != 1 ) {
					$content->img = $images->item( 0 )->getAttribute( 'src' );
				}
			}
			if ( $links = $doc->getElementsByTagName( 'a' ) ) {
				$content->url = trim( $links->item( 0 )->getAttribute( 'href' ) );
				if ( \is_ssl() ) {
					$content->url = preg_replace( '/^http/', 'https', $content->url );
				}
			}

			$content->extra                  = new ExtraDataAffilinetCoupons;
			$content->extra->ProgramId       = $r['ProgramId'];
			$content->extra->VoucherType     = $r['VoucherTypes']['VoucherType'];
			$content->extra->LastChangeDate  = strtotime( $r['LastChangeDate'] );
			$content->extra->IntegrationCode = $r['IntegrationCode'];
			$content->extra->IsExclusive     = (bool) $r['IsExclusive'];

			if ( (float) $r['MinimumOrderValue'] ) {
				$content->extra->IsExclusive = (float) $r['MinimumOrderValue'];
			}
			$content->extra->CustomerRestriction = $r['CustomerRestriction'];

			$data[] = $content;
		}

		return $data;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new AffilinetCoupons( $this->config( 'service_password' ), $this->config( 'PublisherId' ) );
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
