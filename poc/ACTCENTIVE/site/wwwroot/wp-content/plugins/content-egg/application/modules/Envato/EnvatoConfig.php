<?php

namespace ContentEgg\application\modules\Envato;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * EnvatoConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class EnvatoConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'token'                   => array(
				'title'       => 'Token <span class="cegg_required">*</span>',
				'description' => __( 'You can <a href="https://build.envato.com/create-token/">generate a personal token</a> to access Envato API.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Token' ),
					),
				),
				'section'     => 'default',
			),
			'deeplink'                => array(
				'title'       => 'Deeplink',
				'description' => __( 'Set this option, if you want to send traffic through one of affiliate network with Envato support', 'content-egg' )
				                 . ', ' . __( 'eg.', 'content-egg' ) . ' https://1.envato.market/c/1234567/123456/1234?u={{url_encoded}}',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'your_username'           => array(
				'title'       => 'Your Envato username',
				'description' => __( 'Deprecated. Use Deeplink instead.', 'content-egg' ),
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
				'default'     => 8,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 100,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 100 ),
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
						'arg'     => 100,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 100 ),
					),
				),
			),
			'site'                    => array(
				'title'            => __( 'Site', 'content-egg' ),
				'description'      => __( 'The site to match.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''                 => __( 'All', 'content-egg' ),
					'themeforest.net'  => 'themeforest.net',
					'photodune.net'    => 'photodune.net',
					'codecanyon.net'   => 'codecanyon.net',
					'videohive.net'    => 'videohive.net',
					'audiojungle.net'  => 'audiojungle.net',
					'graphicriver.net' => 'graphicriver.net',
					'3docean.net'      => '3docean.net',
				),
				'default'          => '',
			),
			'rating_min'              => array(
				'title'       => __( 'Rating min', 'content-egg' ),
				'description' => __( 'Minimum rating to filter by.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'price_min'               => array(
				'title'       => __( 'Price min', 'content-egg' ),
				'description' => __( 'Minimum price to include.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'price_max'               => array(
				'title'       => __( 'Price max', 'content-egg' ),
				'description' => __( 'Maximum price to include.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'date'                    => array(
				'title'            => __( 'Date', 'content-egg' ),
				'description'      => __( 'Restrict items by original uploaded date.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''           => __( 'Any', 'content-egg' ),
					'this-year'  => 'this year',
					'this-month' => 'this month',
					'this-week'  => 'this week',
					'this-day'   => 'this day',
				),
				'default'          => '',
			),
			'username'                => array(
				'title'       => __( 'Username', 'content-egg' ),
				'description' => __( 'Username to restrict by.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'sort_by'                 => array(
				'title'            => __( 'Order', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''          => __( 'Default', 'content-egg' ),
					'relevance' => 'relevance',
					'rating'    => 'rating',
					'sales'     => 'sales',
					'price'     => 'price',
					'date'      => 'date',
					'updated'   => 'updated',
					'category'  => 'category',
					'name'      => 'name',
					'trending'  => 'trending',
				),
				'default'          => '',
			),
			'sort_direction'          => array(
				'title'            => __( 'Order direction', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''     => __( 'Default', 'content-egg' ),
					'asc'  => 'asc',
					'desc' => 'desc',
				),
				'default'          => '',
			),
			'resolution_min'          => array(
				'title'            => __( 'Resolution', 'content-egg' ),
				'description'      => __( 'The minimum resolution for video content.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''      => __( 'All', 'content-egg' ),
					'720p'  => '720p',
					'1080p' => '1080p',
					'2K'    => '2K',
					'4K'    => '4K',
				),
				'default'          => '',
			),
			'vocals_in_audio'         => array(
				'title'            => __( 'Vocals', 'content-egg' ),
				'description'      => __( 'The type of vocal content in audio files.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''                              => __( 'All', 'content-egg' ),
					'background vocals'             => 'background vocals',
					'lead vocals'                   => 'lead vocals',
					'instrumental version included' => 'instrumental version included',
					'vocal samples'                 => 'vocal samples',
				),
				'default'          => '',
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
