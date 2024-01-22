<?php

namespace ContentEgg\application\modules\SkimlinksCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\skimlinks\SkimlinksMerchant;
use ContentEgg\application\components\ContentCoupon;
use ContentEgg\application\admin\PluginAdmin;

/**
 * SkimlinksCouponsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class SkimlinksCouponsModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Skimlinks Coupons',
			'description' => sprintf( __( 'Adds offers from %s.', 'content-egg' ), '<a target="_blank" href="http://www.keywordrush.com/go/skimlinks">Skimlinks</a>' ) . ' ' .
			                 __( 'You can search by keyword or merchant domain.', 'content-egg' ),
		);
	}

	public function releaseVersion() {
		return '4.0.0';
	}

	public function getParserType() {
		return self::PARSER_TYPE_COUPON;
	}

	public function defaultTemplateName() {
		return 'coupons';
	}

	public function isFree() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		if ( ! $this->config( 'siteId' ) ) {
			throw new \Exception( 'You must set Site ID option in the plugin settings.' );
		}

		$options = array();

		if ( $is_autoupdate ) {
			$options['limit'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['limit'] = $this->config( 'entries_per_page' );
		}

		if ( ! empty( $query_params['offer_type'] ) ) {
			$options['offer_type'] = $query_params['offer_type'];
		} elseif ( $this->config( 'offer_type' ) ) {
			$options['offer_type'] = $this->config( 'offer_type' );
		}


		$fields = array(
			'merchant_id',
			'country',
			'period',
			'sort_by',
			'sort_dir',
		);

		foreach ( $fields as $f ) {
			if ( $this->config( $f ) ) {
				$options[ $f ] = $this->config( $f );
			}
		}
		if ( $this->config( 'favourite_type' ) ) {
			$options['favourite_type'] = 'favourite';
		}
		if ( $this->config( 'vertical' ) ) {
			$options['vertical'] = (int) $options['vertical'];
		}


		$results = $this->getApiClient()->search( $keyword, $options );
		if ( ! is_array( $results ) || ! isset( $results['offers'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['offers'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content            = new ContentCoupon;
			$content->unique_id = $r['id'];

			if ( $r['title'] ) {
				$content->title = strip_tags( $r['title'] );
				if ( $r['description'] ) {
					$content->description = strip_tags( $r['description'] );
				}
			} elseif ( $r['description'] ) {
				$content->title = strip_tags( $r['description'] );
			} else {
				continue;
			}

			$content->url      = $this->creatAffiliateUrl( $r['url'] );
			$content->domain   = $r['merchant_details']['domain'];
			$content->merchant = $r['merchant_details']['name'];
			if ( ! empty( $r['merchant_details']['metadata']['logo'] ) ) {
				$content->logo = $r['merchant_details']['metadata']['logo'];
				$content->img  = $r['merchant_details']['metadata']['logo'];
			}
			if ( $r['offer_ends'] ) {
				$content->endDate = strtotime( $r['offer_ends'] );
			}
			if ( $r['offer_starts'] ) {
				$content->startDate = strtotime( $r['offer_starts'] );
			}
			if ( $r['coupon_code'] ) {
				$content->code = $r['coupon_code'];
			}


			$content->extra = new ExtraDataSkimlinksCoupons;
			ExtraDataSkimlinksCoupons::fillAttributes( $content->extra, $r );

			$data[] = $content;
		}

		return $data;
	}

	/**
	 * @link: http://developers.skimlinks.com/link.html
	 */
	private function creatAffiliateUrl( $url ) {
		if ( ! $this->config( 'siteId' ) ) {
			return $url;
		}

		return 'http://go.redirectingat.com/?'
		       . 'url=' . urlencode( $url )
		       . '&id=' . $this->config( 'siteId' )
		       . '&xs=1';
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new SkimlinksMerchant( $this->config( 'publicKey' ), $this->config( 'accountId' ), 'publisher_admin' );
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
