<?php

namespace ContentEgg\application\modules\Youtube;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\google\YouTubeSearch;
use ContentEgg\application\components\Content;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\admin\GeneralConfig;

/**
 * YoutubeModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class YoutubeModule extends ParserModule {

	public function info() {
		return array(
			'name'          => 'Youtube',
			'api_agreement' => 'http://code.google.com/apis/youtube/terms.html',
			'docs_uri'      => 'https://ce-docs.keywordrush.com/modules/content/youtube',
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_VIDEO;
	}

	public function isFree() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {

		$params = array();

		if ( $is_autoupdate ) {
			$params['maxResults'] = $this->config( 'entries_per_page_update' );
		} else {
			$params['maxResults'] = $this->config( 'entries_per_page' );
		}

		$params['relevanceLanguage'] = GeneralConfig::getInstance()->option( 'lang' );
		$params['key']               = $this->config( 'api_key' );

		if ( ! empty( $query_params['order'] ) ) {
			$params['order'] = $query_params['order'];
		} else {
			$params['order'] = $this->config( 'order' );
		}

		if ( ! empty( $query_params['license'] ) ) {
			$params['videoLicense'] = $query_params['license'];
		} else {
			$params['videoLicense'] = $this->config( 'license' );
		}

		try {
			//$keyword = str_replace('-', ' ', $keyword);
			$client = new YouTubeSearch( 'json' );
			$data   = $client->search( $keyword, $params );
		} catch ( Exception $e ) {
			throw new \Exception( strip_tags( $e->getMessage() ) );
		}

		if ( ! isset( $data['items'] ) || ! isset( $data['items'][0] ) ) {
			$data['items'] = array();
		}

		$results = array();
		foreach ( $data['items'] as $r ) {
			if ( ! isset( $r['id']['videoId'] ) ) {
				continue;
			}

			$guid = $r['id']['videoId'];

			$content            = new Content;
			$content->unique_id = $guid;
			$content->title     = strip_tags( $r['snippet']['title'] );
			$content->img       = isset( $r['snippet']['thumbnails'] ) ? $r['snippet']['thumbnails']['high']['url'] : '';

			if ( isset( $r['snippet']['description'] ) && ! is_array( $r['snippet']['description'] ) ) {
				$content->description = strip_tags( $r['snippet']['description'] );
				if ( $max_size = $this->config( 'description_size' ) ) {
					$content->description = TextHelper::truncate( $content->description, $max_size );
				}
			} else {
				$content->description = '';
			}
			$content->url = 'https://www.youtube.com/watch?v=' . $r['id']['videoId'];

			$extra                = new ExtraDataYoutube;
			$extra->author        = strip_tags( $r['snippet']['channelTitle'] );
			$extra->date          = strtotime( $r['snippet']['publishedAt'] );
			$extra->guid          = $guid;
			$extra->category      = isset( $r['snippet']['category'][1] ) ? $r['snippet']['category'][1] : '';
			$extra->channel_title = isset( $r['snippet']['channelTitle'] ) ? $r['snippet']['channelTitle'] : '';

			$content->extra = $extra;
			$results[]      = $content;
		}

		return $results;
	}

	public function enqueueScripts() {
		\wp_enqueue_script( 'cegg-youtube', \plugins_url( 'application/modules/Youtube/js/module.js', \ContentEgg\PLUGIN_FILE ), array( 'contentegg-metabox-app' ), null, false );
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		//PluginAdmin::render('_metabox_search_results_images', array('module_id' => $this->getId()));
		$this->render( 'search_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchPanel() {
		$this->render( 'search_panel', array( 'module_id' => $this->getId() ) );
	}

}
