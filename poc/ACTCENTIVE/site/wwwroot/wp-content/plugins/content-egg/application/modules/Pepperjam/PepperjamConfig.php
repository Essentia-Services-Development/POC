<?php

namespace ContentEgg\application\modules\Pepperjam;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * PepperjamConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class PepperjamConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'api_key'                 => array(
				'title'       => 'API Key <span class="cegg_required">*</span>',
				'description' => __( 'You can generate publisher API Key <a href="http://www.pepperjamnetwork.com/affiliate/api/">here</a>.', 'content-egg' ),
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
				'section'     => 'default',
			),
			'websiteId'               => array(
				'title'       => 'Website ID',
				'description' => __( 'A single website id used for website tracking. Adding this will add this parameter to your tracking link.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'sid'                     => array(
				'title'       => 'SID',
				'description' => __( 'A single SID parameter used for publisher tracking. Adding this will add this parameter to your tracking link.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for one search query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 20,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 500,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 500 ),
					),
				),
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for updates and autoblogging', 'content-egg' ),
				'description' => __( 'Number of results for automatic updates and autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 6,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 500,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 500 ),
					),
				),
			),
			'programId'               => array(
				'title'       => __( 'Program ID', 'content-egg' ),
				'description' => __( 'A comma-separated list of program ids to filter by.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'category'                => array(
				'title'            => __( 'Category ', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''    => __( 'All categories', 'content-egg' ),
					'1.'  => 'Commerce',
					'2.'  => 'Computer & Electronics',
					'3.'  => 'Education',
					'7.'  => 'Accessories',
					'9.'  => 'Art/Photo/Music',
					'11.' => 'Automotive',
					'13.' => 'Books/Media',
					'15.' => 'Business',
					'17.' => 'Careers',
					'19.' => 'Clothing/Apparel',
					'23.' => 'Entertainment',
					'24.' => 'Family',
					'25.' => 'Financial Services',
					'27.' => 'Food & Drinks',
					'29.' => 'Games & Toys',
					'31.' => 'Gifts & Flowers',
					'33.' => 'Health & Beauty',
					'35.' => 'Home & Garden',
					'37.' => 'Insurance',
					'39.' => 'Legal',
					'41.' => 'Marketing',
					'43.' => 'Medical',
					'45.' => 'Phonecard Services',
					'47.' => 'Recreation & Leisure',
					'49.' => 'Shops/Malls',
					'51.' => 'Sports & Fitness',
					'53.' => 'Travel',
					'55.' => 'Web Services',
					'57.' => 'Canada',
					'58.' => 'Jewelry',
					'59.' => 'Pets',
					'60.' => 'Dating',
					'64.' => 'Baby',
					'67.' => 'Adult',
				),
				'default'          => '',
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
				'default'     => '300',
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
