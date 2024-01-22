<?php

namespace ContentEgg\application\modules\Pixabay;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModuleConfig;

/**
 * PixabayConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class PixabayConfig extends ParserModuleConfig {

	public function options() {
		$optiosn = array(
			'key'                     => array(
				'title'       => 'API Key <span class="cegg_required">*</span>',
				'description' => __( 'Key access to Pixabay API. You can get <a href="https://pixabay.com/api/docs/">here</a> (you need to have account).', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Key" can not be empty', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for a single query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 20,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 200,
						'message' => __( 'Field "Results" can not be more than 200.', 'content-egg' ),
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
						'arg'     => 200,
						'message' => __( 'Field "Results for autoblogging" can not be more than 200.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'image_size'              => array(
				'title'            => __( 'Size', 'content-egg' ),
				'description'      => __( 'Height size of image', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'_180' => '180px',
					'_340' => '340px',
					'_640' => '640px',
					'_960' => '960px',
				),
				'default'          => '_640',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'image_type'              => array(
				'title'            => __( 'Type of image', 'content-egg' ),
				'description'      => 'A media type to search within.',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'all'          => __( 'All', 'content-egg' ),
					'photo'        => 'Photo',
					'illustration' => 'Illustration',
					'vector'       => 'Vector',
				),
				'default'          => 'all',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'orientation'             => array(
				'title'            => __( 'Orientation', 'content-egg' ),
				'description'      => 'Whether an image is wider than it is tall, or taller than it is wide.',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'all'        => __( 'All', 'content-egg' ),
					'horizontal' => 'Horizontal',
					'vertical'   => 'Vertical',
				),
				'default'          => 'all',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'category'                => array(
				'title'            => __( 'Category ', 'content-egg' ),
				'description'      => 'Filter images by category.',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''               => __( 'All', 'content-egg' ),
					'fashion'        => 'Fashion',
					'nature'         => 'Nature',
					'backgrounds'    => 'Backgrounds',
					'science'        => 'Science',
					'education'      => 'Education',
					'people'         => 'People',
					'feelings'       => 'Feelings',
					'religion'       => 'Religion',
					'health'         => 'Health',
					'places'         => 'Places',
					'animals'        => 'Animals',
					'industry'       => 'Industry',
					'food'           => 'Food',
					'computer'       => 'Computer',
					'sports'         => 'Sports',
					'transportation' => 'Transportation',
					'travel'         => 'Travel',
					'buildings'      => 'Buildings',
					'business'       => 'Business',
					'music'          => 'Music',
				),
				'default'          => '',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'editors_choice'          => array(
				'title'       => __( 'Choose editor', 'content-egg' ),
				'description' => "Select images that have received an Editor's Choice award.",
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'safesearch'              => array(
				'title'       => __( 'Safe search', 'content-egg' ),
				'description' => 'A flag indicating that only images suitable for all ages should be returned.',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'order'                   => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => 'How the results should be ordered.',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'popular' => 'Popular',
					'latest'  => 'Latest',
				),
				'default'          => 'popular',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'save_img'                => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images to your server. Hotlinking is prohibited by Pixabay. If you don\'t save images to your server, external pixabay links will be valid only 24 hours.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => true,
				'section'     => 'default',
			),
		);

		return array_merge( parent::options(), $optiosn );
	}

}
