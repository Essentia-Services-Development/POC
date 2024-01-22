<?php

namespace ContentEgg\application\modules\QwantImages;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\qwant\QwantApi;
use ContentEgg\application\components\Content;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\admin\GeneralConfig;

/**
 * BingImagesModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class QwantImagesModule extends ParserModule {

	public function info() {
		return array(
			'name' => 'Qwant Images',
		);
	}

	public function releaseVersion() {
		return '4.3.0';
	}

	public function getParserType() {
		return self::PARSER_TYPE_IMAGE;
	}

	public function defaultTemplateName() {
		return 'data_image';
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();
		if ( $is_autoupdate ) {
			$options['count'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['count'] = $this->config( 'entries_per_page' );
		}

		if ( $this->config( 'safesearch' ) ) {
			$options['safesearch'] = 1;
		} else {
			$options['safesearch'] = 0;
		}
		// $options['locale'] = GeneralConfig::getInstance()->option('lang');

		$api_client = new QwantApi();
		$results    = $api_client->images( $keyword, $options );

		if ( ! isset( $results['data'] ) || empty( $results['data']['result']['items'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['data']['result']['items'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content            = new Content;
			$content->unique_id = $r['_id'];
			$content->title     = trim( str_replace( '...', '', strip_tags( $r['title'] ) ) );
			$content->img       = $r['media'];
			$content->url       = $r['url'];
			if ( ! empty( $r['desc'] ) ) {
				$content->description = strip_tags( $r['desc'] );
			}

			$extra = new ExtraDataQwantImages;
			ExtraDataQwantImages::fillAttributes( $extra, $r );
			$extra->source  = parse_url( $content->url, PHP_URL_HOST );
			$content->extra = $extra;
			$data[]         = $content;
		}

		return $data;
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results_images', array( 'module_id' => $this->getId() ) );
	}

}
