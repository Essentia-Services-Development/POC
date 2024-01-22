<?php

namespace ContentEgg\application\modules\BingImages;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\bing\CognitiveSearch;
use ContentEgg\application\components\Content;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * BingImagesModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class BingImagesModule extends ParserModule {

	public function info() {
		return array(
			'name' => 'Bing Images',
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_IMAGE;
	}

	public function defaultTemplateName() {
		return 'data_image';
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		if ( ! $this->config( 'subscription_key' ) ) {
			throw new \Exception( 'The "Subscription Key" can not be empty. You must configure the module for new Cognitive Services API.' );
		}

		$options = array();
		if ( $is_autoupdate ) {
			$options['count'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['count'] = $this->config( 'entries_per_page' );
		}

		if ( isset( $query_params['license'] ) ) {
			$options['license'] = $query_params['license'];
		} elseif ( $this->config( 'license' ) ) {
			$options['license'] = $this->config( 'license' );
		}

		$parms_list = array( 'mkt', 'safeSearch', 'aspect', 'color', 'freshness', 'imageContent', 'imageType', 'size' );
		foreach ( $parms_list as $param ) {
			if ( $this->config( $param ) ) {
				$options[ $param ] = $this->config( $param );
			}
		}
		$options['modules'] = 'BRQ,Caption'; //All

		if ( $this->config( 'domain_name' ) ) {
			$keyword = 'site:' . $this->config( 'domain_name' ) . ' ' . $keyword;
		}

		$rand_key = TextHelper::getRandomFromCommaList( $this->config( 'subscription_key' ) );
		try {
			$api_client = new CognitiveSearch( $rand_key );
			$results    = $api_client->images( $keyword, $options );
		} catch ( Exception $e ) {
			throw new \Exception( strip_tags( $e->getMessage() ) );
		}

		if ( ! isset( $results['value'] ) || ! is_array( $results['value'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['value'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content            = new Content;
			$content->unique_id = $r['imageId'];
			$content->title     = strip_tags( $r['name'] );
			$content->img       = $r['contentUrl'];
			$content->url       = $r['hostPageUrl'];

			$extra = new ExtraDataBingImages;
			ExtraDataBingImages::fillAttributes( $extra, $r );
			$extra->thumbnail = $extra->thumbnailUrl;
			$extra->source    = parse_url( $content->url, PHP_URL_HOST );
			$content->extra   = $extra;
			$data[]           = $content;
		}

		return $data;
	}

	private function parseRedirectUrl( $url ) {
		$query = parse_url( $url, PHP_URL_QUERY );
		if ( ! $query ) {
			return $url;
		}
		parse_str( $query, $query_arr );
		if ( isset( $query_arr['r'] ) ) {
			return $query_arr['r'];
		} else {
			return $url;
		}
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
