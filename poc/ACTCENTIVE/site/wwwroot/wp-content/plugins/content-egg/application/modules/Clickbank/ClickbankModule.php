<?php

namespace ContentEgg\application\modules\Clickbank;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\clickbank\ClickbankApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * ClickbankModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ClickbankModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Clickbank',
			'description' => __( 'Adds goods from clickbank.com.', 'content-egg' ) . '<br>' . __( 'Module is in test mode.', 'content-egg' ),
		);
	}

	public function getParserType() {
		return self::PARSER_TYPE_PRODUCT;
	}

	public function defaultTemplateName() {
		return 'simple';
	}

	public function isItemsUpdateAvailable() {
		return false;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$entries_per_page = $this->config( 'entries_per_page_update' );
		} else {
			$entries_per_page = $this->config( 'entries_per_page' );
		}
		if ( $entries_per_page > 10 ) {
			$options['resultsPerPage'] = $entries_per_page;
		}

		if ( isset( $query_params['sortField'] ) ) {
			$options['sortField'] = $query_params['sortField'];
		} elseif ( $this->config( 'promotion_type' ) ) {
			$options['sortField'] = $this->config( 'sortField' );
		}

		if ( (int) $this->config( 'mainCategoryId' ) ) {
			$options['mainCategoryId'] = (int) $this->config( 'mainCategoryId' );
		}

		if ( (int) $this->config( 'gravityV1' ) ) {
			$options['gravityEnabled']  = 'true';
			$options['_gravityEnabled'] = 'on';
			$options['gravityType']     = 'HIGHER';
			$options['gravityV2']       = '';
			$options['gravityV1']       = (int) $this->config( 'gravityV1' );
		}

		if ( $this->config( 'productLanguages' ) ) {
			$options['_productLanguages'] = 'on';
			$options['productLanguages']  = $this->config( 'productLanguages' );
		}

		/*
		  if ($this->config('productTypes'))
		  {
		  $options['productTypes'] = $this->config('productTypes');
		  }
		 *
		 */
		$results = $this->getClickbankClient()->search( $keyword, $options );
		if ( ! isset( $results['details'] ) ) {
			return array();
		}
		if ( ! isset( $results['details'][0] ) ) {
			$results['details'] = array( $results['details'] );
		}
		$results['details'] = array_slice( $results['details'], 0, $entries_per_page );

		return $this->prepareResults( $results['details'] );
	}

	private function prepareResults( $results ) {
		$data = array();

		foreach ( $results as $key => $r ) {
			$content              = new ContentProduct;
			$content->unique_id   = $r['site'];
			$content->title       = $r['title'];
			$content->description = $r['description'];
			$content->url         = self::createHopLink( $this->config( 'nickname' ), $r['site'] );
			if ( $max_size = $this->config( 'description_size' ) ) {
				$content->description = TextHelper::truncate( $content->description, $max_size );
			}

			$content->extra = new ExtraDataClickbank;
			ExtraDataClickbank::fillAttributes( $content->extra, $r['marketplaceStats'] );
			$content->extra->activateDate = strtotime( $content->extra->activateDate );
			$data[]                       = $content;
		}

		return $data;
	}

	private function getClickbankClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new ClickbankApi();
		}

		return $this->api_client;
	}

	/**
	 * @link: https://support.clickbank.com/entries/22803362-All-About-HopLinks
	 */
	private static function createHopLink( $affiliate, $vendor, $tracking_id = null ) {
		$link = 'http://' . $affiliate . '.' . $vendor . '.hop.clickbank.net';
		if ( $tracking_id ) {
			$link .= '/?tid=' . $tracking_id;
		}

		return $link;
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
