<?php

namespace ContentEgg\application\modules\CjLinks;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\cj\CjLinksRest;
use ContentEgg\application\components\ContentCoupon;
use ContentEgg\application\admin\PluginAdmin;

/**
 * CjLinksModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class CjLinksModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'          => 'CJ Links',
			'api_agreement' => 'http://www.cj.com/legal/ws-terms',
			'description'   => __( 'Adds text links, coupons, banners from CJ.com. You must have approval from each program separately.', 'content-egg' ) .
			                   '<br>You may use simple Boolean logic operators (\' + \', \' - \') to obtain more relevant search results.'
		);
	}

	public function defaultTemplateName() {
		return 'universal';
	}

	public function getParserType() {
		return self::PARSER_TYPE_COUPON;
	}

	public function isItemsUpdateAvailable() {
		return false;
	}

	public function isFree() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$options['records-per-page'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['records-per-page'] = $this->config( 'entries_per_page' );
		}

		if ( isset( $query_params['promotion_type'] ) ) {
			$options['promotion-type'] = $query_params['promotion_type'];
		} elseif ( $this->config( 'promotion_type' ) ) {
			$options['promotion-type'] = $this->config( 'promotion_type' );
		}
		if ( isset( $query_params['link_type'] ) ) {
			$options['link-type'] = $query_params['link_type'];
		} elseif ( $this->config( 'link_type' ) ) {
			$options['link-type'] = $this->config( 'link_type' );
		}

		$fields = array(
			'website_id',
			'advertiser_ids',
			'category',
			//'promotion_type',
			//'link_type'
		);

		foreach ( $fields as $f ) {
			if ( $this->config( $f ) ) {
				$options[ str_replace( '_', '-', $f ) ] = $this->config( $f );
			}
		}

		$results = $this->getCJClient()->search( $keyword, $options );
		if ( ! is_array( $results ) || ! isset( $results['links']['link'] ) ) {
			return array();
		}
		if ( ! isset( $results[0] ) && isset( $results['link-id'] ) ) {
			$results = array( $results );
		}

		return $this->prepareResults( $results['links']['link'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			// Note: This field is blank for advertisers with which you do not
			// have a relationship (non-joined).
			if ( empty( $r['link-code-html'] ) ) {
				continue;
			}

			$content            = new ContentCoupon;
			$content->unique_id = $r['link-id'];

			if ( ! empty( $r['clickUrl'] ) ) {
				$content->url = $r['clickUrl'];
			}

			$content->title = trim( $r['link-name'] );

			// parse link code html
			$doc = new \DOMDocument();
			@$doc->loadHTML( $r['link-code-html'] );

			if ( $images = $doc->getElementsByTagName( 'img' ) ) {
				// pixel img?
				if ( $images->item( 0 )->getAttribute( 'height' ) != 1 ) {
					$content->img = $images->item( 0 )->getAttribute( 'src' );
				}
			}

			// Text Link
			if ( $r['link-type'] == 'Text Link' && $links = $doc->getElementsByTagName( 'a' ) ) {
				$content->title = trim( $links->item( 0 )->nodeValue );
			}

			$content->extra                 = new ExtraDataCjLinks;
			$content->extra->advertiserId   = ( $r['advertiser-id'] ) ? $r['advertiser-id'] : '';
			$content->extra->advertiserName = ( $r['advertiser-name'] ) ? $r['advertiser-name'] : '';
			$content->extra->creativeHeight = ( $r['creative-height'] ) ? (int) $r['creative-height'] : '';
			$content->extra->creativeWidth  = ( $r['creative-width'] ) ? (int) $r['creative-width'] : '';
			$content->extra->language       = ( $r['language'] ) ? $r['language'] : '';

			if ( ! empty( $r['destination'] ) ) {
				$adv_site                       = parse_url( $r['destination'], PHP_URL_HOST );
				$content->extra->advertiserSite = preg_replace( '/^www\./', '', $adv_site );
			}

			$r['description'] = trim( $r['description'] );
			if ( $r['description'] != $content->title ) {
				$content->description = $r['description'];
			}

			$content->extra->destination   = ( $r['destination'] ) ? $r['destination'] : '';
			$content->extra->linkName      = ( $r['link-name'] ) ? $r['link-name'] : '';
			$content->extra->linkType      = ( $r['link-type'] ) ? $r['link-type'] : '';
			$content->startDate            = $content->extra->promotionStartDate = ( $r['promotion-start-date'] ) ? strtotime( $r['promotion-start-date'] ) : '';
			$content->endDate              = $content->extra->promotionEndDate = ( $r['promotion-end-date'] ) ? strtotime( $r['promotion-end-date'] ) : '';
			$content->extra->promotionType = ( $r['promotion-type'] ) ? $r['promotion-type'] : '';
			$content->code                 = $content->extra->couponCode = ( $r['coupon-code'] ) ? $r['coupon-code'] : '';
			$content->extra->category      = ( $r['category'] ) ? $r['category'] : '';
			$content->extra->linkHtml      = ( $r['link-code-html'] ) ? $r['link-code-html'] : '';

			$data[] = $content;
		}

		return $data;
	}

	private function getCJClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new CjLinksRest( $this->config( 'access_token' ), $this->config( 'dev_key' ) );
		}

		return $this->api_client;
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		$this->render( 'search_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchPanel() {
		$this->render( 'search_panel', array( 'module_id' => $this->getId() ) );
	}

}
