<?php

namespace ContentEgg\application\modules\Kelkoo;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * KelkooConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class KelkooConfig extends AffiliateParserModuleConfig {

	public function options() {
		$options = array(
			'token'                   => array(
				'title'       => 'Token <span class="cegg_required">*</span>',
				'description' => __( 'In order to access the Kelkoo Group Shopping API you must have a token. You can generate these from the credentials page from the left menu of your account.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Tracking ID' ),
					),
				),
			),
			'region'                  => array(
				'title'            => __( 'Region <span class="cegg_required">*</span>', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''   => __( '- Choose your region -', 'content-egg' ),
					'ae' => 'ae',
					'at' => 'at',
					'au' => 'au',
					'be' => 'be',
					'br' => 'br',
					'ca' => 'ca',
					'ch' => 'ch',
					'cz' => 'cz',
					'de' => 'de',
					'dk' => 'dk',
					'es' => 'es',
					'fi' => 'fi',
					'fr' => 'fr',
					'gr' => 'gr',
					'hk' => 'hk',
					'hu' => 'hu',
					'id' => 'id',
					'ie' => 'ie',
					'in' => 'in',
					'it' => 'it',
					'jp' => 'jp',
					'kr' => 'kr',
					'mx' => 'mx',
					'my' => 'my',
					'nb' => 'nb',
					'nl' => 'nl',
					'no' => 'no',
					'nz' => 'nz',
					'ph' => 'ph',
					'pl' => 'pl',
					'pt' => 'pt',
					'ro' => 'ro',
					'ru' => 'ru',
					'se' => 'se',
					'sg' => 'sg',
					'sk' => 'sk',
					'tr' => 'tr',
					'uk' => 'uk',
					'us' => 'us',
					'vn' => 'vn',
					'za' => 'za',
				),
				'default'          => '',
				'validator'        => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Region' ),
					),
				),
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for one search query.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 10,
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
				'title'       => __( 'Results for updates', 'content-egg' ),
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
			'queryMatchStrength'      => array(
				'title'            => __( 'Match strength', 'content-egg' ),
				'description'      => __( 'Query match strength for query terms.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''     => __( 'Default', 'content-egg' ),
					'all'  => __( 'All the offers matching term 1 AND term 2', 'content-egg' ),
					'some' => __( 'All the offers matching term 1 OR term 2', 'content-egg' ),
					'any'  => __( 'Query operator OR if no result with AND', 'content-egg' ),
				),
				'default'          => '',
			),
			'price_min'               => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => __( 'Example, 8.99', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'price_max'               => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => __( 'Example, 98.50', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'rebatePercentage'        => array(
				'title'            => __( 'Rebate percentage', 'content-egg' ),
				'description'      => __( 'When set to 30 for example, the response will return offers that have a sale price discounted by 30% or more.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''    => __( 'Any', 'content-egg' ),
					'5%'  => '5%',
					'10%' => '10%',
					'15%' => '15%',
					'20%' => '20%',
					'25%' => '25%',
					'30%' => '30%',
					'35%' => '35%',
					'40%' => '40%',
					'45%' => '45%',
					'50%' => '50%',
					'60%' => '60%',
					'70%' => '70%',
					'80%' => '80%',
					'90%' => '90%',
					'95%' => '95%',
				),
				'default'          => '',
				'metaboxInit'      => true,
			),
			'merchantId'              => array(
				'title'       => __( 'Merchant ID', 'content-egg' ),
				'description' => __( 'Limit the search to a specific merchant.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'description_size'        => array(
				'title'       => __( 'Trim description', 'content-egg' ),
				'description' => __( 'Description size in characters.', 'content-egg' ),
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

		$parent = parent::options();

		/**
		 * Limited lifespan
		 * @link: https://developers.kelkoogroup.com/app/documentation/navigate/_publisher/leadServicePublic/_/_/GoUrls
		 */
		$parent['ttl_items']['validator'] = array(
			'trim',
			'absint',
			array(
				'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
				'arg'     => 604800,
				'message' => sprintf( __( 'The field "%s" can\'t be more than %d.', 'content-egg' ), __( 'Price update', 'content-egg' ), 604800 ),
			),
		);

		return self::moveRequiredUp( array_merge( $parent, $options ) );
	}
}
