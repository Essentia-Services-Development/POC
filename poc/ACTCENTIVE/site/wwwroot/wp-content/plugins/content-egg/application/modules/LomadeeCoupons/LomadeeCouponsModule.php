<?php

namespace ContentEgg\application\modules\LomadeeCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\lomadee\LomadeeApi;
use ContentEgg\application\components\ContentCoupon;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * SkimlinksCouponsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class LomadeeCouponsModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Lomadee Coupons',
			'description' => sprintf( __( 'Adds coupons from %s.', 'content-egg' ), __( 'Lomadee affiliate network', 'content-egg' ) ),
		);
	}

	public function releaseVersion() {
		return '4.3.0';
	}

	public function getParserType() {
		return self::PARSER_TYPE_COUPON;
	}

	public function defaultTemplateName() {
		return 'coupons';
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$entries_per_page = $this->config( 'entries_per_page_update' );
		} else {
			$entries_per_page = $this->config( 'entries_per_page' );
		}

		$params = array(
			'categoryId',
			'storeId',
		);

		foreach ( $params as $param ) {
			$value = $this->config( $param );
			if ( $value !== '' ) {
				$options[ $param ] = $value;
			}
		}

		$results = $this->getApiClient()->coupons( $keyword, $options );
		if ( ! isset( $results['coupons'] ) || ! is_array( $results['coupons'] ) ) {
			return array();
		}

		return $this->prepareResults( array_slice( $results['coupons'], 0, $entries_per_page ) );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $r ) {
			$content            = new ContentCoupon;
			$content->unique_id = $r['id'];
			$content->title     = strip_tags( $r['description'] );
			$content->url       = $r['link'];
			$content->domain    = TextHelper::getHostName( $r['store']['link'] );
			$content->merchant  = $r['store']['name'];
			$content->logo      = $r['store']['image'];
			$content->img       = $r['store']['image'];

			if ( ! empty( $r['vigency'] ) ) {
				$content->endDate = \DateTime::createFromFormat( '!d/m/Y G:i:s', $r['vigency'] )->getTimestamp();
			}
			if ( ! empty( $r['code'] ) ) {
				$content->code = $r['code'];
			}

			$content->extra = new ExtraDataLomadeeCoupons;
			ExtraDataLomadeeCoupons::fillAttributes( $content->extra, $r );

			$data[] = $content;
		}

		return $data;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new LomadeeApi( '15071999399311f734bd1', $this->config( 'sourceId' ) );
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
