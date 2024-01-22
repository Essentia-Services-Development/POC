<?php

namespace ContentEgg\application\modules\GoogleImages;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModuleConfig;

/**
 * GoogleImagesConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class GoogleImagesConfig extends ParserModuleConfig {

	public function options() {
		$optiosn = array(
			'cx'                      => array(
				'title'       => 'Search engine ID <span class="cegg_required">*</span>',
				'description' => __( 'The custom <a target="_blank" href="https://support.google.com/customsearch/answer/2649143">search engine ID</a>. Don\'t forget to <a target="_blank" href="https://support.google.com/customsearch/answer/2630972">enable image search</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Search engine ID' ),
					),
				),
			),
			'key'                     => array(
				'title'       => 'API Key <span class="cegg_required">*</span>',
				'description' => __( 'API access key. You can get in Google <a href="http://code.google.com/apis/console">API console</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'API Key' ),
					),
				),
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for one query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 10,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 10,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), __( 'Results', 'content-egg' ), 10 ),
					),
				),
				array(
					'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
					'arg'     => 10,
					'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 10 ),
				),
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for updates', 'content-egg' ),
				'description' => __( 'Number of results for autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 6,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 10,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results for updates', 10 ),
					),
				),
			),
			'rights'                  => array(
				'title'            => __( 'Type of license', 'content-egg' ),
				'description'      => __( 'Filters based on licensing. (<a target="_blank" href="https://support.google.com/websearch/answer/29508">Read more</a>).', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''                                                                               => __( 'Any license', 'content-egg' ),
					'(cc_publicdomain|cc_attribute|cc_sharealike|cc_noncommercial|cc_nonderived)'    => __( 'Any Creative Commons', 'content-egg' ),
					'(cc_publicdomain|cc_attribute|cc_sharealike|cc_nonderived).-(cc_noncommercial)' => __( 'With Allow of commercial use', 'content-egg' ),
					'(cc_publicdomain|cc_attribute|cc_sharealike|cc_noncommercial).-(cc_nonderived)' => __( 'Allowed change', 'content-egg' ),
					'(cc_publicdomain|cc_attribute|cc_sharealike).-(cc_noncommercial|cc_nonderived)' => __( 'Commercial use and change', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'imgSize'                 => array(
				'title'            => __( 'Size', 'content-egg' ),
				'description'      => __( 'Returns images of a specified size.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''        => __( 'Any size', 'content-egg' ),
					'icon'    => __( 'Icon', 'content-egg' ),
					'small'   => __( 'Small', 'content-egg' ),
					'medium'  => __( 'Medium', 'content-egg' ),
					'large'   => __( 'Large', 'content-egg' ),
					'xlarge'  => __( 'XLarge', 'content-egg' ),
					'xxlarge' => __( 'XXLarge', 'content-egg' ),
					'huge'    => __( 'Huge', 'content-egg' ),
				),
				'metaboxInit'      => true,
				'section'          => 'default',
			),
			'imgColorType'            => array(
				'title'            => __( 'Color type', 'content-egg' ),
				'description'      => __( 'Returns black and white, grayscale, or color images.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''      => __( 'Any', 'content-egg' ),
					'color' => __( 'Color', 'content-egg' ),
					'gray'  => __( 'Gray', 'content-egg' ),
					'mono'  => __( 'Mono', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'imgDominantColor'        => array(
				'title'            => __( 'Dominant color', 'content-egg' ),
				'description'      => __( 'Returns images of a specific dominant color.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''       => __( 'Any', 'content-egg' ),
					'black'  => __( 'Black', 'content-egg' ),
					'blue'   => __( 'Blue', 'content-egg' ),
					'brown'  => __( 'Brown', 'content-egg' ),
					'gray'   => __( 'Gray', 'content-egg' ),
					'green'  => __( 'Green', 'content-egg' ),
					'pink'   => __( 'Pink', 'content-egg' ),
					'purple' => __( 'Purple', 'content-egg' ),
					'teal'   => __( 'Teal', 'content-egg' ),
					'white'  => __( 'White', 'content-egg' ),
					'yellow' => __( 'Yellow', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'imgType'                 => array(
				'title'            => __( 'Type', 'content-egg' ),
				'description'      => __( 'Returns images of a type.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''        => __( 'Any', 'content-egg' ),
					'face'    => __( 'Faces', 'content-egg' ),
					'photo'   => __( 'Photo', 'content-egg' ),
					'clipart' => __( 'Clip-art', 'content-egg' ),
					'lineart' => __( 'B/w pictures', 'content-egg' ),
					'news'    => __( 'News', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'safe'                    => array(
				'title'            => __( 'Safe search', 'content-egg' ),
				'description'      => __( 'Search safety level.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'high'   => __( 'Highest level', 'content-egg' ),
					'medium' => __( 'Moderate', 'content-egg' ),
					'off'    => __( 'Disabled', 'content-egg' ),
				),
				'default'          => 'medium',
				'section'          => 'default',
			),
			'siteSearch'              => array(
				'title'       => __( 'Search', 'content-egg' ),
				'description' => __( 'Limit search to only that domain. For example ask: photobucket.com', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'save_img'                => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
		);

		return array_merge( parent::options(), $optiosn );
	}

}
