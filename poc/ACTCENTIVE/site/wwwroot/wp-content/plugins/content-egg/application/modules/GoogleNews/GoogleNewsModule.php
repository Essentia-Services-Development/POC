<?php

namespace ContentEgg\application\modules\GoogleNews;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\google\GNews;
use ContentEgg\application\components\Content;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\admin\GeneralConfig;

/**
 * GoogleNews class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class GoogleNewsModule extends ParserModule {

	public function info() {
		return array(
			'name'          => 'Google News',
			'api_agreement' => 'https://support.google.com/news/answer/40796',
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_CONTENT;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$params = array();

		if ( $is_autoupdate ) {
			$entries_per_page = $this->config( 'entries_per_page_update' );
		} else {
			$entries_per_page = $this->config( 'entries_per_page' );
		}

		$params['hl'] = GeneralConfig::getInstance()->option( 'lang' );
		try {
			$gn      = new GNews;
			$results = $gn->search( $keyword, $params, (int) $entries_per_page );
		} catch ( Exception $e ) {
			throw new \Exception( strip_tags( $e->getMessage() ) );
		}
		if ( ! is_array( $results ) ) {
			return array();
		}

		$data = array();
		foreach ( $results as $key => $r ) {
			$content              = new Content;
			$content->unique_id   = md5( $r['url'] );
			$content->title       = $r['title'];
			$content->description = $r['description'];
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			} else {
				$content->description .= '...';
			}

			$content->url = $r['url'];

			if ( ! empty( $r['img'] ) ) {
				$content->img = $r['img'];
			}
			$extra = new ExtraDataGoogleNews;
			ExtraDataGoogleNews::fillAttributes( $extra, $r );

			if ( ! empty( $r['links'] ) ) {
				foreach ( $r['links'] as $link_r ) {
					$link = new ExtraGoogleNewsLinks;
					ExtraDataGoogleNews::fillAttributes( $link, $link_r );
					$extra->links = $link;
				}
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
