<?php

namespace ContentEgg\application\modules\Coupon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * OfferModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class CouponModule extends AffiliateParserModule {

	public function info() {
		return array(
			'name'        => 'Coupon',
			'description' => __( 'Add a coupon code manually.', 'content-egg' ),
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_COUPON;
	}

	public function releaseVersion() {
		return '4.1.0';
	}

	public function defaultTemplateName() {
		return 'coupons';
	}

	public function isFree() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		return array();
	}

	public function presavePrepare( $data, $post_id ) {
		$data   = parent::presavePrepare( $data, $post_id );
		$return = array();
		foreach ( $data as $key => $item ) {
			$item['title']       = trim( sanitize_text_field( $item['title'] ) );
			$item['description'] = trim( \wp_kses_post( $item['description'] ) );
			$item['url']         = trim( $item['url'] );
			$item['img']         = trim( $item['img'] );
			if ( ! empty( $item['code'] ) ) {
				$item['code'] = trim( sanitize_text_field( $item['code'] ) );
			}

			if ( ! empty( $item['domain'] ) ) {
				$item['domain'] = strip_tags( $item['domain'] );
				if ( $d = TextHelper::getHostName( $item['domain'] ) ) {
					$item['domain'] = $d;
				}
			}

			if ( ! empty( $item['startDate'] ) ) {
				if ( is_numeric( $item['startDate'] ) ) {
					$item['startDate'] = $item['startDate'] / 1000;
				} else {
					$item['startDate'] = strtotime( $item['startDate'] );
				}
			}
			if ( ! $item['startDate'] ) {
				$item['startDate'] = '';
			}
			if ( ! empty( $item['endDate'] ) ) {
				if ( is_numeric( $item['endDate'] ) ) {
					$item['endDate'] = $item['endDate'] / 1000;
				} else {
					$item['endDate'] = strtotime( $item['endDate'] );
				}
			}
			if ( ! $item['endDate'] ) {
				$item['endDate'] = '';
			}

			if ( ! $item['title'] ) {
				continue;
			}
			if ( ! filter_var( $item['url'], FILTER_VALIDATE_URL ) ) {
				continue;
			}
			if ( $item['img'] && ! filter_var( $item['img'], FILTER_VALIDATE_URL ) ) {
				continue;
			}

			$return[ $key ] = $item;
		}

		return $return;
	}

	public function viewDataPrepare( $data ) {
		$hide_expired = $this->config( 'hide_expired' );
		$hide_future  = $this->config( 'hide_future' );
		foreach ( $data as $key => $d ) {
			if ( isset( $d['endDate'] ) && $hide_expired && $d['endDate'] && time() > $d['endDate'] ) {
				unset( $data[ $key ] );
			} elseif ( isset( $d['startDate'] ) && $hide_future && $d['startDate'] && $d['startDate'] > time() ) {
				unset( $data[ $key ] );
			}
		}

		return parent::viewDataPrepare( $data );
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderMetaboxModule() {
		$this->render( 'metabox_module', array( 'module_id' => $this->getId(), 'module' => $this ) );
	}

}
