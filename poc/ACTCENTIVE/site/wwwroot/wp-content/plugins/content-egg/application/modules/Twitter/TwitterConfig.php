<?php

namespace ContentEgg\application\modules\Twitter;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModuleConfig;

/**
 * TwitterConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class TwitterConfig extends ParserModuleConfig {

	public function options() {
		$optiosn = array(
			'consumer_key'              => array(
				'title'       => 'Consumer key <span class="cegg_required">*</span>',
				'description' => __( 'Can get <a href="https://dev.twitter.com/apps/">here<a/>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Account Key" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'consumer_secret'           => array(
				'title'       => 'Consumer secret <span class="cegg_required">*</span>',
				'description' => __( 'Can get <a href="https://dev.twitter.com/apps/">here<a/>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Account Key" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'oauth_access_token'        => array(
				'title'       => 'Access token <span class="cegg_required">*</span>',
				'description' => __( 'Can get <a href="https://dev.twitter.com/apps/">here<a/>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Account Key" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'oauth_access_token_secret' => array(
				'title'       => 'Access token secret <span class="cegg_required">*</span>',
				'description' => __( 'Can get <a href="https://dev.twitter.com/apps/">here<a/>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The "Account Key" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page'          => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for a single query', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 12,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 100,
						'message' => __( 'The "Results" can not be more than 10.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page_update'   => array(
				'title'       => __( 'Results for autoblogging ', 'content-egg' ),
				'description' => __( 'Number of results for autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 5,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 100,
						'message' => __( 'Field "Results for autoblogging" can not be more than 100.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'result_type'               => array(
				'title'            => __( 'Sorting', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'recent'  => __( 'New', 'content-egg' ),
					'popular' => __( 'Popular', 'content-egg' ),
					'mixed'   => __( 'Mix', 'content-egg' ),
				),
				'default'          => 'mixed',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'save_img'                  => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
		);
		$parent  = parent::options();
		unset( $parent['featured_image'] );

		return array_merge( $parent, $optiosn );
	}

}
