<?php

namespace ContentEgg\application\modules\Flickr;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModuleConfig;

/**
 * FlickrConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class FlickrConfig extends ParserModuleConfig {

	public function options() {
		$optiosn = array(
			'api_key'                 => array(
				'title'       => 'API Key <span class="cegg_required">*</span>',
				'description' => __( 'The key for use Flickr API. You can get <a href="http://www.flickr.com/services/api/misc.api_keys.html">here</a>.', 'content-egg' ),
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
				'default'     => '18',
				'validator'   => array(
					'trim',
					'absint',
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
				),
				'section'     => 'default',
			),
			'sort'                    => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'relevance'            => __( 'Relevance', 'content-egg' ),
					'date-posted-desc'     => __( 'Date of post', 'content-egg' ),
					'date-taken-desc'      => __( 'Date of shooting', 'content-egg' ),
					'interestingness-desc' => __( 'First interesting', 'content-egg' ),
				),
				'default'          => 'relevance',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'license'                 => array(
				'title'            => __( 'Type of license', 'content-egg' ),
				'description'      => __( 'Many photos on Flickr have Creative Commons license. <a href="http://www.flickr.com/creativecommons/">Know more</a>.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''            => __( 'Any license', 'content-egg' ),
					'4,6,3,2,1,5' => __( 'Any Creative Commons', 'content-egg' ),
					'4,6,5'       => __( 'With Allow of commercial use', 'content-egg' ),
					'4,2,1,5'     => __( 'Allowed change', 'content-egg' ),
					'4,5'         => __( 'Commercial use and change', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'size'                    => array(
				'title'            => __( 'Size', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'75p'   => __( '75x75 pixels', 'content-egg' ),
					'150p'  => __( '150x150 pixels', 'content-egg' ),
					'100p'  => __( '100 pixels on the long side', 'content-egg' ),
					'240p'  => __( '240 pixels on the long side', 'content-egg' ),
					'320p'  => __( '320 pixels on the long side', 'content-egg' ),
					'500p'  => __( '500 pixels on the long side', 'content-egg' ),
					'640p'  => __( '640 pixels on the long side', 'content-egg' ),
					'800p'  => __( '800 pixels on the long side', 'content-egg' ),
					'1024p' => __( '1024 pixels on the long side', 'content-egg' ),
				),
				'default'          => '500p',
				'section'          => 'default',
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
				'default'     => '220',
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
			'user_id'                 => array(
				'title'       => 'User ID',
				'description' => __( 'Limit search to only those user Flickr', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
		);

		return array_merge( parent::options(), $optiosn );
	}

}
