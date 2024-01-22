<?php

namespace ContentEgg\application\modules\VkNews;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\vk\VkApi;
use ContentEgg\application\components\Content;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\PluginAdmin;

/**
 * VkNewsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class VkNewsModule extends ParserModule {

	public function info() {
		return array(
			'name'          => 'VK News',
			'api_agreement' => 'https://vk.com/dev/terms',
			'description'   => __( 'Adds news from Russian-language social network vk.com', 'content-egg' )
		);
	}

	public function isDeprecated() {
		return true;
	}

	public function getParserType() {
		return self::PARSER_TYPE_CONTENT;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$params = array();

		if ( $is_autoupdate ) {
			$params['count'] = $this->config( 'entries_per_page_update' );
		} else {
			$params['count'] = $this->config( 'entries_per_page' );
		}

		try {
			$client  = new VkApi();
			$results = $client->newsfeedSearch( $keyword, $params );
		} catch ( Exception $e ) {
			throw new \Exception( strip_tags( $e->getMessage() ) );
		}

		if ( ! $results || ! is_array( $results['response'] ) ) {
			return array();
		}

		$data = array();
		foreach ( $results['response'] as $key => $result ) {
			if ( ! is_array( $result ) ) {
				continue;
			}
			if ( ! $result['text'] ) {
				continue;
			}

			$content = new Content;

			$content->unique_id   = $result['id'];
			$content->title       = '';
			$content->description = $result['text'];
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}
			if ( ! empty( $result['attachment'] ) && $result['attachment']['type'] == 'photo' ) {
				$content->img = $result['attachment']['photo']['src_big'];
			}

			$content->extra = new ExtraDataVkNews();
			if ( isset( $result['comments'] ) ) {
				$content->extra->comments = $result['comments']['count'];
			}
			if ( isset( $result['likes'] ) ) {
				$content->extra->likes = $result['likes']['count'];
			}
			if ( isset( $result['reposts'] ) ) {
				$content->extra->reposts = $result['reposts']['count'];
			}

			$data[] = $content;
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
