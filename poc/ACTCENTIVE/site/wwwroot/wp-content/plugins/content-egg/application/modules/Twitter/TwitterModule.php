<?php

namespace ContentEgg\application\modules\Twitter;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModule;
use ContentEgg\application\libs\twitter\TwitterRest;
use ContentEgg\application\components\Content;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\admin\GeneralConfig;

/**
 * Twitter class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class TwitterModule extends ParserModule {

	public function info() {
		return array(
			'name'          => 'Twitter',
			'api_agreement' => 'https://dev.twitter.com/terms/api-terms',
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_CONTENT;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$params = array();

		if ( isset( $query_params['result_type'] ) ) {
			$params['result_type'] = $query_params['result_type'];
		} elseif ( $this->config( 'result_type' ) ) {
			$params['result_type'] = $this->config( 'result_type' );
		}

		if ( $is_autoupdate ) {
			$params['count'] = $this->config( 'entries_per_page_update' );
		} else {
			$params['count'] = $this->config( 'entries_per_page' );
		}

		$params['lang'] = GeneralConfig::getInstance()->option( 'lang' );

		try {
			$client  = new TwitterRest( $this->config( 'consumer_key' ), $this->config( 'consumer_secret' ), $this->config( 'oauth_access_token' ), $this->config( 'oauth_access_token_secret' ) );
			$results = $client->search( $keyword, $params );
		} catch ( Exception $e ) {
			throw new \Exception( strip_tags( $e->getMessage() ) );
		}

		if ( ! isset( $results['statuses'] ) || ! is_array( $results['statuses'] ) ) {
			return array();
		}

		$data = array();
		foreach ( $results['statuses'] as $key => $r ) {
			$content              = new Content;
			$content->unique_id   = $r['id_str'];
			$content->description = strip_tags( $r['text'] );
			$content->title       = TextHelper::truncate( $content->description );

			if ( isset( $r['entities']['media'] ) ) {
				$content->img = $r['entities']['media'][0]['media_url'];
			}

			$extra                 = new ExtraDataTwitter;
			$extra->author         = strip_tags( $r['user']['screen_name'] );
			$extra->date           = strtotime( $r['created_at'] );
			$extra->userId         = $r['user']['id_str'];
			$extra->statusesCount  = (int) ( $r['user']['statuses_count'] );
			$extra->followersCount = (int) ( $r['user']['followers_count'] );
			$extra->friendsCount   = (int) ( $r['user']['friends_count'] );

			$content->url = 'http://twitter.com/' . $extra->author;

			$content->profileImage = strip_tags( $r['user']['profile_image_url'] );


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

	/*
	  public function renderSearchPanel()
	  {
	  $this->render('search_panel', array('module_id' => $this->getId()));
	  }
	 *
	 */
}
