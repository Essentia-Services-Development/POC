<?php

namespace ContentEgg\application\modules\SkimlinksCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * SkimlinksCouponsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class SkimlinksCouponsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'publicKey'               => array(
				'title'       => 'Client ID <span class="cegg_required">*</span>',
				'description' =>
					sprintf( __( 'You can apply for an API key by logging in to the %s and requesting approval under Toolbox ->  API Authentication credentials', 'content-egg' ), '<a target="_blank" href="http://www.keywordrush.com/go/skimlinks">Skimlinks Publisher Hub</a>' ) . ' ' .
					__( 'Once you are approved the same page will display your Client ID and your User Id.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Client ID' ),
					),
				),
				'section'     => 'default',
			),
			/*
			  'accountType' => array(
			  'title' => 'Account Type <span class="cegg_required">*</span>',
			  'callback' => array($this, 'render_input'),
			  'default' => '',
			  'validator' => array(
			  'trim',
			  array(
			  'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
			  'when' => 'is_active',
			  'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), 'Account Type'),
			  ),
			  ),
			  'default' => 'publisher_admin'
			  ),
			 *
			 */
			'accountId'               => array(
				'title'     => 'Publisher Id <span class="cegg_required">*</span>',
				'callback'  => array( $this, 'render_input' ),
				'default'   => '',
				'validator' => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Publisher Id' ),
					),
				),
				'section'   => 'default',
			),
			'siteId'                  => array(
				'title'       => 'Site ID <span class="cegg_required">*</span>',
				'description' => __( 'You can find your SiteID <a target="_blank" href="https://hub.skimlinks.com/settings/sites">here</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => sprintf( __( 'The field "%s" can not be empty.', 'content-egg' ), 'Site ID' ),
					),
				),
				'section'     => 'default',
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
						'arg'     => 200,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 200 ),
					),
				),
				'section'     => 'default',
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
						'arg'     => 200,
						'message' => sprintf( __( 'The field "%s" can not be more than %d.', 'content-egg' ), 'Results', 200 ),
					),
				),
				'section'     => 'default',
			),
			'merchant_id'             => array(
				'title'       => __( 'Merchant ID', 'content-egg' ),
				'description' => __( 'List offers from this merchant only.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'favourite_type'          => array(
				'title'       => __( 'Favourites', 'content-egg' ),
				'description' => __( 'Retrieve your favourites only.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
			),
			'country'                 => array(
				'title'       => __( 'Country', 'content-egg' ),
				'description' => __( 'Two character country code.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
			),
			'vertical'                => array(
				'title'            => __( 'Category', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array( ''     => __( 'All', 'content-egg' ),
				                             '177.' => 'Automotive',
				                             '169.' => 'Business',
				                             '68.'  => 'Coupons/Deals',
				                             '180.' => 'Drugstore & Pharmacy',
				                             '174.' => 'Entertainment',
				                             '168.' => 'Fashion & Accessories',
				                             '176.' => 'Food & Drink',
				                             '181.' => 'Gambling',
				                             '3.'   => 'Gender - Female',
				                             '17.'  => 'Gender - Male',
				                             '173.' => 'Gifts',
				                             '175.' => 'Home',
				                             '172.' => 'Interests',
				                             '166.' => 'Medical Equipment',
				                             '179.' => 'Multi-Category Retailers',
				                             '156.' => 'Paid Social Networks',
				                             '72.'  => 'Personal Finance',
				                             '170.' => 'Services',
				                             '178.' => 'Technology',
				                             '171.' => 'Tickets',
				                             '150.' => 'Tobacco',
				                             '182.' => 'Travel'
				),
				'default'          => '',
				'metaboxInit'      => true,
			),
			'offer_type'              => array(
				'title'            => __( 'Offer type', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''              => __( 'All', 'content-egg' ),
					'coupon'        => __( 'Coupon', 'content-egg' ),
					'sweepstake'    => __( 'Sweepstake', 'content-egg' ),
					'hot_product'   => __( 'Hot product', 'content-egg' ),
					'sale'          => __( 'Sales', 'content-egg' ),
					'free_shipping' => __( 'Free shipping', 'content-egg' ),
					'seasonal'      => __( 'Seasonal', 'content-egg' ),
				),
				'default'          => '',
				'metaboxInit'      => true,
			),
			'period'                  => array(
				'title'            => __( 'Period', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'all'            => __( 'All', 'content-egg' ),
					'upcoming'       => __( 'Upcoming', 'content-egg' ),
					'ongoing'        => __( 'Ongoing', 'content-egg' ),
					'finished'       => __( 'Finished', 'content-egg' ),
					'finishing_soon' => __( 'Finishing Soon', 'content-egg' ),
					'not_expired'    => __( 'Not expired', 'content-egg' ),
				),
				'default'          => 'all',
			),
			'sort_by'                 => array(
				'title'            => __( 'Sort by', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'id'           => __( 'Date added', 'content-egg' ),
					'offer_starts' => __( 'Srart date', 'content-egg' ),
					'offer_ends'   => __( 'End date', 'content-egg' ),
				),
				'default'          => 'id',
			),
			'sort_dir'                => array(
				'title'            => __( 'Sort order', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'asc'  => __( 'Asc', 'content-egg' ),
					'desc' => __( 'Desc', 'content-egg' ),
				),
				'default'          => 'desc',
			),
		);

		return array_merge( parent::options(), $optiosn );
	}

}
