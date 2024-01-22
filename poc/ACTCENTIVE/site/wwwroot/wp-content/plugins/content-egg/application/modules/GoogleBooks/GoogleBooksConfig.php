<?php

namespace ContentEgg\application\modules\GoogleBooks;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModuleConfig;

/**
 * GoogleBooksConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class GoogleBooksConfig extends ParserModuleConfig {

	public function options() {
		$optiosn = array(
			'api_key'                 => array(
				'title'       => 'API Key <span class="cegg_required">*</span>',
				'description' => __( 'API access key. You can get it in Google <a href="http://code.google.com/apis/console">API console</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "API Key" can not be empty', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for a single query', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 8,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 40,
						'message' => __( 'The "Results" can not be more than 40.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for autoblogging ', 'content-egg' ),
				'description' => __( 'Number of results for autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 3,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 40,
						'message' => __( 'Field "Results for autoblogging" can not be more than 40.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'country'                 => array(
				'title'       => __( 'Country', 'content-egg' ),
				'description' => __( 'The appropriate <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">2 letter</a> code represent the country which you are wanting to search from.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'save_img'                => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'description_size'        => array(
				'title'       => __( 'Trim description', 'content-egg' ),
				'description' => __( 'Description size in characters (0 - do not cut)', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '500',
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
			'orderby'                 => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'relevance' => __( 'Relevance', 'content-egg' ),
					'newest'    => __( 'Newness', 'content-egg' ),
				),
				'default'          => 'relevance',
				'section'          => 'default',
			),
			'printType'               => array(
				'title'            => __( 'Publication type', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'all'       => __( 'Any', 'content-egg' ),
					'books'     => __( 'Books', 'content-egg' ),
					'magazines' => __( 'Magazines', 'content-egg' ),
				),
				'default'          => 'all',
				'section'          => 'default',
			),
		);
		$parent  = parent::options();
		unset( $parent['featured_image'] );

		return array_merge( $parent, $optiosn );
	}

}
