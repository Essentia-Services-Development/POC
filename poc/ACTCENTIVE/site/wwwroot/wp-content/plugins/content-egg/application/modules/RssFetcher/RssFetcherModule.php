<?php

namespace ContentEgg\application\modules\RssFetcher;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\rss\RssParser;
use ContentEgg\application\components\Content;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\admin\GeneralConfig;

/**
 * RssFetcher class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class RssFetcherModule extends ParserModule {

	public function info() {
		return array(
			'name'        => 'RSS Fetcher',
			'description' => __( 'Parse any RSS', 'content-egg' ) . ' ' .
			                 __( 'It\'s important, that you can have keyword in URL. So rss string must have results by keyword searching.', 'content-egg' )
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_CONTENT;
	}

	public function isFree() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$entries_per_page = $this->config( 'entries_per_page_update' );
		} else {
			$entries_per_page = $this->config( 'entries_per_page' );
		}

		try {
			$client  = new RssParser();
			$results = $client->search( $keyword, $this->config( 'uri' ) );
		} catch ( Exception $e ) {
			throw new \Exception( strip_tags( $e->getMessage() ) );
		}


		$results = $this->normaliseResults( $results, $entries_per_page );

		$data = array();
		foreach ( $results as $r ) {
			if ( empty( $r['title'] ) ) {
				throw new \Exception( 'Can\'t parse RSS feed.' );
			}

			$content              = new Content;
			$content->title       = strip_tags( $r['title'], $this->config( 'allowed_tags' ) );
			$content->description = strip_tags( $r['description'], $this->config( 'allowed_tags' ) );
			$content->url         = $r['url'];
			if ( $content->url ) {
				$content->unique_id = md5( $content->url );
			} else {
				$content->title = md5( $content->title );
			}
			$content->extra = new ExtraDataRssFetcher;
			unset( $r['title'] );
			unset( $r['description'] );
			unset( $r['url'] );
			$content->extra->allData = $r;

			$data[] = $content;
		}

		return $data;
	}

	private function normaliseResults( $feed, $entries_per_page = 10 ) {
		$results = array();
		if ( isset( $feed['items'] ) ) {
			$results = $feed['items'];
		} elseif ( isset( $feed['channel']['item'] ) ) {//rss 2.0
			if ( isset( $feed['channel']['item'][0] ) && is_array( $feed['channel']['item'][0] ) ) {
				$results = $feed['channel']['item'];
			} else {
				$results[] = $feed['channel']['item'];
			}
		} elseif ( isset( $feed['entry'] ) ) { //atom
			if ( isset( $feed['entry'][0] ) && is_array( $feed['entry'][0] ) ) {
				$results = $feed['entry'];
			} else {
				$results[] = $feed['entry'];
			}
		}

		$results = array_slice( $results, 0, $entries_per_page );

		foreach ( $results as $key => $res ) {
			//получаем title
			$results[ $key ]['title'] = ( isset( $res['title'] ) && $res['title'] ) ? $res['title'] : '';

			//получаем description
			if ( isset( $res['content:encoded'] ) && $res['content:encoded'] ) {
				$results[ $key ]['description'] = $res['content:encoded'];
				unset( $results[ $key ]['content:encoded'] );
			} elseif ( isset( $res['content'] ) && $res['content'] ) {
				$results[ $key ]['description'] = $res['content'];
				unset( $results[ $key ]['content'] );
			} elseif ( isset( $res['description'] ) && $res['description'] ) {
				$results[ $key ]['description'] = $res['description'];
			} else {
				$results[ $key ]['description'] = '';
			}

			//получаем url
			$results[ $key ]['url'] = '';
			if ( isset( $res['link'] ) && ! is_array( $res['link'] ) ) {
				$results[ $key ]['url'] = $res['link'];
			} elseif ( isset( $res['link']['@attributes']['href'] ) ) {
				$results[ $key ]['url'] = $res['link']['@attributes']['href'];
			} elseif ( isset( $res['link'][0] ) ) {
				foreach ( $res['link'] as $lnk ) {
					if ( isset( $lnk['@attributes']['rel'] ) && $lnk['@attributes']['rel'] == 'alternate' ) {
						$results[ $key ]['url'] = $lnk['@attributes']['href'];
					}
				}
			}
		}

		return $results;
	}

	/*
	  {
	  $content->title = strip_tags($this->config('allowed_tags'));
	  $content->unique_id = $r['ID'];
	  $content->url = $r['BingUrl'];
	  }
	 *
	 */

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

}
