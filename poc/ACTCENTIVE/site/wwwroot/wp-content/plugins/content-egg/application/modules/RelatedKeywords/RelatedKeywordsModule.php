<?php

namespace ContentEgg\application\modules\RelatedKeywords;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\bing\CognitiveSearch;
use ContentEgg\application\components\Content;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * RelatedKeywords class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class RelatedKeywordsModule extends ParserModule {

	public function info() {
		return array(
			'name'        => 'Related Keywords',
			'description' => __( 'Finds relative keywords and shows them in post.', 'content-egg' ),
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_CONTENT;
	}

	public function isFree() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		if ( ! $this->config( 'subscription_key' ) ) {
			throw new \Exception( 'The "Subscription Key" can not be empty. You must configure the module for new Cognitive Services API.' );
		}

		$options = array();
		if ( $this->config( 'mkt' ) ) {
			$options['mkt'] = $this->config( 'mkt' );
		}

		if ( $is_autoupdate ) {
			$entries_per_page = $this->config( 'entries_per_page_update' );
		} else {
			$entries_per_page = $this->config( 'entries_per_page' );
		}

		$rand_key = TextHelper::getRandomFromCommaList( $this->config( 'subscription_key' ) );
		try {
			$api_client = new CognitiveSearch( $rand_key );
			$results    = $api_client->autosuggest( $keyword, $options );
		} catch ( Exception $e ) {
			throw new \Exception( strip_tags( $e->getMessage() ) );
		}

		if ( ! isset( $results['suggestionGroups'] ) || ! isset( $results['suggestionGroups'][0]['searchSuggestions'] ) ) {
			return array();
		}

		$results = $results['suggestionGroups'][0]['searchSuggestions'];
		$results = array_slice( $results, 0, $entries_per_page );

		$data = array();
		foreach ( $results as $r ) {
			$content            = new Content;
			$content->title     = strip_tags( $r['displayText'] );
			$content->unique_id = $content->title;
			$content->url       = $r['url'];
			$data[]             = $content;
		}

		return $data;
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

}
