<?php

namespace ContentEgg\application\modules\Freebase;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\google\FreebaseRest;
use ContentEgg\application\components\Content;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\admin\GeneralConfig;

/**
 * FreebaseModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class FreebaseModule extends ParserModule {

	const MAX_IMG_WIDTH = 300;
	const MAX_IMG_HEIGHT = 300;

	public function info() {
		return array(
			'name'        => 'Freebase',
			'description' => '<span style="color:red;">' . __( '<span style="color:red;">This module is deprecated because of closing Freebase API. The module is left for compatibility with previous versions of the plugin</span>', 'content-egg' ) . '</span>',
			//'api_agreement' => 'https://developers.google.com/freebase/terms',
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_CONTENT;
	}

	public function isFree() {
		return true;
	}

	public function isDeprecated() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		// The Freebase API will be completely shut-down on Aug 31 2016.
		throw new \Exception( 'The Freebse API has been officially closed.' );

		$params = array();
		/**
		 * For a list of all currently supported language codes, visit the following:
		 * @link: https://www.googleapis.com/freebase/v1/search?help=langs&indent=true
		 */
		$params['lang'] = GeneralConfig::getInstance()->option( 'lang' );

		if ( $is_autoupdate ) {
			$params['limit'] = $this->config( 'entries_per_page_update' );
		} else {
			$params['limit'] = $this->config( 'entries_per_page' );
		}

		try {
			$client  = new FreebaseRest( $this->config( 'api_key' ) );
			$results = $client->fullSearch( $keyword, $params, 'commons' );
		} catch ( Exception $e ) {
			throw new \Exception( strip_tags( $e->getMessage() ) );
		}

		if ( ! $results || ! is_array( $results ) ) {
			return array();
		}

		$data = array();
		foreach ( $results as $key => $result ) {
			$result  = $result['property'];
			$content = new Content;

			$content->unique_id = $result['/type/object/mid']['values'][0]['value'];
			$content->title     = self::getLeaf( $result, '/type/object/name' );
			if ( $description = self::getLeaf( $result, '/common/topic/description' ) ) {
				$content->description = nl2br( $description );
			} elseif ( $description = self::getLeaf( $result, '/common/document/text' ) ) {
				$content->description = nl2br( $description );
			} else {
				continue;
			}

			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}

			if ( ! empty( $result['/common/topic/description']['values'][0]['citation']['uri'] ) ) {
				$content->url = strip_tags( $result['/common/topic/description']['values'][0]['citation']['uri'] );
			}
			if ( ! empty( $result['/common/topic/image'] ) ) {
				$content->img = 'https://usercontent.googleapis.com/freebase/v1/image';
				$content->img .= strip_tags( $result['/common/topic/image']['values'][0]['id'] ) .
				                 '?maxwidth=' . self::MAX_IMG_WIDTH . '&maxheight=' . self::MAX_IMG_HEIGHT;
			}
			$content->extra = self::fillExtra( $result );
			$data[]         = $content;
		}


		return $data;
	}

	static private function fillExtra( $result ) {
		$extra = new ExtraDataFreebase;

		$extra->freebaseId        = self::getLeaf( $result, '/type/object/mid' );
		$extra->dateOfBirth       = self::getLeaf( $result, '/people/person/date_of_birth' );
		$extra->officialWebsite   = self::getLeaf( $result, '/common/topic/official_website', 'array' );
		$extra->equivalentWebpage = self::getLeaf( $result, '/common/topic/topic_equivalent_webpage', 'array' );
		$extra->topicalWebpage    = self::getLeaf( $result, '/common/topic/topical_webpage', 'array' );
		$extra->notableFor        = self::getLeaf( $result, '/common/topic/notable_for', 'array' );
		$extra->notableTypes      = self::getLeaf( $result, '/common/topic/notable_types', 'array' );
		$extra->awardNominations  = self::getLeaf( $result, '/award/award_nominee/award_nominations', 'array' );
		$extra->artistTrack       = self::getLeaf( $result, '/music/artist/track', 'array' );

		if ( isset( $result['/common/topic/article'] ) && isset( $result['/common/topic/article']['values'][0]['property']['/common/document/text'] ) ) {
			$extra->article = $result['/common/topic/article']['values'][0]['property']['/common/document/text']['values'][0]['value'];
			$extra->article = strip_tags( $extra->article );
			$extra->article = nl2br( trim( $extra->article ) );
		}
		if ( isset( $result['/common/topic/image'] ) ) {
			foreach ( $result['/common/topic/image']['values'] as $img ) {
				$extra->gallery[] = 'https://usercontent.googleapis.com/freebase/v1/image' . $img['id'] .
				                    '?maxwidth=' . self::MAX_IMG_WIDTH . '&maxheight=' . self::MAX_IMG_HEIGHT;
			}
		}

		return $extra;
	}

	static private function getLeaf( $data, $index, $type = 'scalar' ) {
		if ( empty( $data[ $index ] ) || empty( $data[ $index ]['values'] ) ) {
			if ( $type == 'array' ) {
				return array();
			} else {
				return '';
			}
		}

		if ( $type == 'scalar' ) {
			if ( isset( $data[ $index ]['values'][0]['value'] ) ) {
				return strip_tags( $data[ $index ]['values'][0]['value'] );
			} elseif ( isset( $data[ $index ]['values'][0]['text'] ) ) {
				return strip_tags( $data[ $index ]['values'][0]['text'] );
			} else {
				return '';
			}
		} elseif ( $type == 'array' ) {
			$result = array();
			foreach ( $data[ $index ]['values'] as $d ) {
				if ( isset( $d['text'] ) ) {
					$result[] = strip_tags( $d['text'] );
				} elseif ( isset( $d['value'] ) ) {
					$result[] = strip_tags( $d['value'] );
				}
			}

			return $result;
		}

		return '';
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

}
