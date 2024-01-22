<?php

namespace ContentEgg\application\modules\GoogleImages;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\google\CustomSearchApi;
use ContentEgg\application\components\Content;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\admin\GeneralConfig;

/**
 * GoogleImagesModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class GoogleImagesModule extends ParserModule {

	public function info() {
		return array(
			'name'        => 'Google Images',
			'description' => __( 'Google custom search for images.', 'content-egg' ),
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

	public function isFree() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		if ( ! $this->config( 'cx' ) || ! $this->config( 'key' ) ) {
			throw new \Exception( 'You must set Search engine ID and API Key in the plugin settings.' );
		}

		$options = array();
		if ( $is_autoupdate ) {
			$options['num'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['num'] = $this->config( 'entries_per_page' );
		}

		if ( ! empty( $query_params['rights'] ) ) {
			$options['rights'] = $query_params['rights'];
		} elseif ( $this->config( 'rights' ) ) {
			$options['rights'] = $this->config( 'rights' );
		}

		if ( ! empty( $query_params['imgSize'] ) ) {
			$options['imgSize'] = $query_params['imgSize'];
		} elseif ( $this->config( 'imgSize' ) ) {
			$options['imgSize'] = $this->config( 'imgSize' );
		}

		$options['hl'] = GeneralConfig::getInstance()->option( 'lang' );

		$options_list = array( 'imgColorType', 'imgDominantColor', 'imgType', 'safe', 'siteSearch' );
		foreach ( $options_list as $o ) {
			if ( $this->config( $o ) ) {
				$options[ $o ] = $this->config( $o );
			}
		}
		$api_client = new CustomSearchApi( $this->config( 'cx' ), $this->config( 'key' ) );
		$results    = $api_client->images( $keyword, $options );

		if ( empty( $results['items'] ) || ! is_array( $results['items'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['items'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $r ) {
			if ( ! isset( $r['image'] ) ) {
				continue;
			}
			$content = new Content;

			$content->title     = trim( str_replace( '...', '', strip_tags( $r['title'] ) ) );
			$content->img       = $r['link'];
			$content->unique_id = md5( $r['link'] );
			$description        = trim( str_replace( '...', '', strip_tags( $r['snippet'] ) ) );
			if ( $description != $content->title ) {
				$content->description = $description;
			}
			$content->url = $r['image']['contextLink'];

			$content->extra         = new ExtraDataGoogleImages();
			$content->extra->source = parse_url( $content->url, PHP_URL_HOST );
			ExtraDataGoogleImages::fillAttributes( $content->extra, $r );
			$data[] = $content;
		}

		return $data;
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results_images', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchPanel() {
		$this->render( 'search_panel', array( 'module_id' => $this->getId() ) );
	}

}
