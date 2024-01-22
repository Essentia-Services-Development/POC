<?php

namespace ContentEgg\application\modules\RssFetcher;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModuleConfig;

/**
 * RssFetcherConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class RssFetcherConfig extends ParserModuleConfig {

	public function options() {
		$optiosn = array(
			'uri'                     => array(
				'title'       => 'RSS URL <span class="cegg_required">*</span>',
				'description' => __( 'For getting current keyword use <em>%KEYWORD%</em>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 'http://www.bing.com/search?format=rss&FORM=RSRE&q=%KEYWORD%',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'Field "RSS URL" can not be empty', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for a single query', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 10,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 50,
						'message' => __( 'The field "Results" can not be more than 50.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for autoblogging ', 'content-egg' ),
				'description' => __( 'Number of results for autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 5,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 50,
						'message' => __( 'Field "Results for autoblogging" can not be more than 50.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'allowed_tags'            => array(
				'title'       => __( 'Allowed tags', 'content-egg' ),
				'description' => __( 'Tags, which are allowed in title and description', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '<p><br><img>',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
		);
		$parent  = parent::options();
		unset( $parent['featured_image'] );

		return array_merge( $parent, $optiosn );
	}

}
