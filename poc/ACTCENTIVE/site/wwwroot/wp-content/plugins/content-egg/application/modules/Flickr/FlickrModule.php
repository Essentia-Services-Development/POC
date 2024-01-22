<?php

namespace ContentEgg\application\modules\Flickr;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\flickr\FlickrApi;
use ContentEgg\application\libs\flickr\FlickrHelper;
use ContentEgg\application\components\Content;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\PluginAdmin;

/**
 * FlickrModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class FlickrModule extends ParserModule {

	public function info() {
		return array(
			'name'          => 'Flickr',
			'api_agreement' => 'http://www.flickr.com/services/api/tos/',
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_IMAGE;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( isset( $query_params['license'] ) ) {
			$options['license'] = $query_params['license'];
		} elseif ( $this->config( 'license' ) ) {
			$options['license'] = $this->config( 'license' );
		}

		if ( ! empty( $query_params['sort'] ) ) {
			$options['sort'] = $query_params['sort'];
		} else {
			$options['sort'] = $this->config( 'sort' );
		}

		if ( $is_autoupdate ) {
			$options['per_page'] = $this->config( 'entries_per_page_update' );
		} else {
			$options['per_page'] = $this->config( 'entries_per_page' );
		}


		if ( $this->config( 'user_id' ) ) {
			$options['user_id'] = $this->config( 'user_id' );
		}

		$options['extras'] = 'description,license,date_taken,owner_name,tags';

		// You can exclude results that match a term by prepending it with a - character.
		$keyword = str_replace( '-', ' ', $keyword );

		$api_client = new FlickrApi( $this->config( 'api_key' ) );
		$results    = $api_client->photosSearch( $keyword, $options );

		if ( ! empty( $results['stat'] ) && $results['stat'] == 'fail' ) {
			throw new \Exception( $results['message'] );
		};

		if ( ! isset( $results['photos'] ) || ! isset( $results['photos']['photo'] ) || ! is_array( $results['photos']['photo'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['photos']['photo'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		foreach ( $results as $key => $r ) {
			$content = new Content;

			$content->unique_id   = $r['id'];
			$content->title       = $r['title'];
			$content->description = trim( $r['description']['_content'] );
			$content->description = strip_tags( $content->description, '<br>' );
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}

			$content->img = FlickrHelper::getImgUri( $r['id'], $r['secret'], $r['server'], $r['farm'], $this->config( 'size' ) );
			$content->url = FlickrHelper::getImgLink( $r['owner'], $r['id'] );

			$extra = new ExtraDataFlickr;
			ExtraDataFlickr::fillAttributes( $extra, $r );

			$extra->author = $r['ownername'];
			$extra->date   = strtotime( $r['datetaken'] );

			$content->extra = $extra;
			$data[]         = $content;
		}

		return $data;
	}

	public function enqueueScripts() {
		\wp_enqueue_script( 'cegg-flickr', \plugins_url( 'application/modules/Flickr/js/module.js', \ContentEgg\PLUGIN_FILE ), array( 'contentegg-metabox-app' ), null, false );
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
