<?php

namespace ContentEgg\application\modules\GoogleBooks;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\google\GBooks;
use ContentEgg\application\components\Content;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\admin\GeneralConfig;

/**
 * GoogleBooks class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class GoogleBooksModule extends ParserModule {

	public function info() {
		return array(
			'name'          => 'Google Books',
			'api_agreement' => 'http://code.google.com/intl/en-EN/apis/books/terms.html',
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_CONTENT;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$params = array();

		if ( $is_autoupdate ) {
			$params['maxResults'] = $this->config( 'entries_per_page_update' );
		} else {
			$params['maxResults'] = $this->config( 'entries_per_page' );
		}

		$params['langRestrict'] = GeneralConfig::getInstance()->option( 'lang' );
		$params['orderBy']      = $this->config( 'orderby' );
		$params['printType']    = $this->config( 'printType' );
		if ( $this->config( 'country' ) ) {
			$params['country'] = $this->config( 'country' );
		}

		try {
			$gb      = new GBooks( $this->config( 'api_key' ) );
			$results = $gb->search( $keyword, $params, (int) $this->config( 'entries_per_page' ) );
		} catch ( Exception $e ) {
			throw new \Exception( strip_tags( $e->getMessage() ) );
		}

		if ( ! isset( $results['items'] ) || ! is_array( $results['items'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['items'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $r ) {
			if ( empty( $r['volumeInfo']['title'] ) ) {
				continue;
			}

			$content            = new Content;
			$content->unique_id = $r['id'];
			$content->title     = $r['volumeInfo']['title'];
			if ( isset( $r['volumeInfo']['description'] ) ) {
				$content->description = strip_tags( $r['volumeInfo']['description'], '<br><p>' );
			}
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncateHtml( $content->description, $max_size );
			}
			$content->url = strip_tags( $r['volumeInfo']['previewLink'] );

			// images
			if ( isset( $r['volumeInfo']['imageLinks'] ) ) {
				$content->img = $r['volumeInfo']['imageLinks']['thumbnail'];
				$content->img = str_replace( 'http://', 'https://', $content->img );
			}

			$extra = new ExtraDataGoogleBooks;
			// isbn
			if ( isset( $r['volumeInfo']['industryIdentifiers'] ) ) {
				$extra->isbn = array();
				foreach ( $r['volumeInfo']['industryIdentifiers'] as $identifier ) {
					if ( $identifier['type'] == 'ISBN_10' ) {
						$extra->isbn[] = $identifier['identifier'];
					}
					if ( $identifier['type'] == 'ISBN_13' ) {
						$extra->isbn[] = $identifier['identifier'];
					}
				}
			}

			//language
			if ( isset( $r['volumeInfo']['language'] ) ) {
				$extra->language = $r['volumeInfo']['language'];
			} elseif ( isset( $r['language'] ) ) {
				$extra->language = $r['language'];
			}


			//subtitle
			if ( isset( $r['volumeInfo']['subtitle'] ) ) {
				$extra->subtitle = $r['volumeInfo']['subtitle'];
			}

			if ( isset( $r['volumeInfo']['authors'] ) ) {
				$extra->authors = is_array( $r['volumeInfo']['authors'] ) ? implode( ", ", $r['volumeInfo']['authors'] ) : $r['volumeInfo']['authors'];
			}

			if ( isset( $r['volumeInfo']['pageCount'] ) ) {
				$extra->pageCount = (int) $r['volumeInfo']['pageCount'];
			}

			$extra->printType = $r['volumeInfo']['printType'];

			if ( isset( $r['volumeInfo']['categories'] ) ) {
				$extra->categories = is_array( $r['volumeInfo']['categories'] ) ? implode( ", ", $r['volumeInfo']['categories'] ) : $r['volumeInfo']['categories'];
			}

			if ( isset( $r['volumeInfo']['publisher'] ) ) {
				$extra->publisher = $r['volumeInfo']['publisher'];
			}
			if ( isset( $r['volumeInfo']['publishedDate'] ) ) {
				$extra->date = strtotime( $r['volumeInfo']['publishedDate'] );
			}

			$content->extra = $extra;
			$data[]         = $content;
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
