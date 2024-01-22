<?php

namespace ContentEgg\application\modules\Envato;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\envato\EnvatoApi;
use ContentEgg\application\modules\Envato\ExtraDataEnvato;
use ContentEgg\application\components\LinkHandler;

/**
 * EnvatoModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class EnvatoModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Envato',
			'description' => sprintf( __( 'Adds items from <a href="https://envato.com/?ref=keywordrush">Envato Market</a>.', 'content-egg' ) ),
		);
	}

	public function releaseVersion() {
		return '3.4.0';
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
			$limit = $this->config( 'entries_per_page_update' );
		} else {
			$limit = $this->config( 'entries_per_page' );
		}

		$options['page_size'] = $limit;
		$options['page']      = 1;

		$params = array(
			'site',
			'rating_min',
			'price_min',
			'price_max',
			'price_max',
			'date',
			'username',
			'sort_by',
			'sort_direction',
			'resolution_min',
			'vocals_in_audio',
		);

		foreach ( $params as $param ) {
			$value = $this->config( $param );
			if ( $value ) {
				$options[ $param ] = $value;
			}
		}
		$results = $this->getApiClient()->search( $keyword, $options );

		if ( ! isset( $results['matches'] ) || ! is_array( $results['matches'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['matches'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content = new ContentProduct;

			$content->unique_id    = $r['id'];
			$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			$content->domain       = $r['site'];
			$content->title        = $r['name'];
			$content->description  = $r['description'];
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncateHtml( $content->description, $max_size );
			}
			$content->category = $r['classification'];

			$content->price        = (int) $r['price_cents'] / 100;
			$content->currencyCode = 'USD';
			$content->orig_url     = $r['url'];
			$content->url          = $this->createAffUrl( $content->orig_url );
			$content->rating       = TextHelper::ratingPrepare( $r['rating']['rating'] );
			$content->reviewsCount = (int) $r['rating']['count'];
			if ( ! empty( $r['previews']['landscape_preview'] ) ) {
				$content->img = $r['previews']['landscape_preview']['landscape_url'];
			} elseif ( ! empty( $r['previews']['thumbnail_preview'] ) ) {
				$content->img = $r['previews']['thumbnail_preview']['large_url'];
			} elseif ( ! empty( $r['previews']['icon_with_thumbnail_preview'] ) ) {
				$content->img = $r['previews']['icon_with_thumbnail_preview']['thumbnail_url'];
			} elseif ( ! empty( $r['previews']['icon_with_square_preview'] ) ) {
				$content->img = $r['previews']['icon_with_square_preview']['icon_url'];
			} elseif ( ! empty( $r['previews']['icon_with_audio_preview'] ) ) {
				$content->img = $r['previews']['icon_with_audio_preview']['icon_url'];
			} elseif ( ! empty( $r['previews']['icon_with_video_preview'] ) ) {
				$content->img = $r['previews']['icon_with_video_preview']['icon_url'];
			}

			$content->extra = new ExtraDataEnvato();
			ExtraDataEnvato::fillAttributes( $content->extra, $r );
			$content->extra->updated_at   = strtotime( $content->extra->updated_at );
			$content->extra->published_at = strtotime( $content->extra->published_at );
			$data[]                       = $content;
		}

		return $data;
	}

	public function doRequestItems( array $items ) {
		foreach ( $items as $key => $item ) {
			$result = $this->getApiClient()->product( $item['unique_id'] );
			if ( ! is_array( $result ) || ! isset( $result['id'] ) ) {
				throw new \Exception( 'doRequestItems request error.' );
			}

			$r = $result;

			// assign new price
			if ( isset( $r['price_cents'] ) ) {
				$items[ $key ]['price'] = (int) $r['price_cents'] / 100;
			}

			// new url for theme sync
			$items[ $key ]['url'] = $this->createAffUrl( $item['orig_url'], $item );
		}

		return $items;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new EnvatoApi( $this->config( 'token' ) );
		}

		return $this->api_client;
	}

	public function viewDataPrepare( $data ) {
		foreach ( $data as $key => $d ) {
			$data[ $key ]['url'] = $this->createAffUrl( $d['orig_url'], $d );
		}

		return parent::viewDataPrepare( $data );
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

	private function createAffUrl( $url, $item = array() ) {
		$deeplink = $this->config( 'deeplink' );
		if ( $deeplink ) {
			return LinkHandler::createAffUrl( $url, $deeplink, $item );
		}

		$user_name = $this->config( 'your_username' );
		if ( $user_name ) {
			$ref_param = 'ref=' . $this->config( 'your_username' );

			return LinkHandler::createAffUrl( $url, $ref_param, $item );
		}

		return $url;
	}

}
