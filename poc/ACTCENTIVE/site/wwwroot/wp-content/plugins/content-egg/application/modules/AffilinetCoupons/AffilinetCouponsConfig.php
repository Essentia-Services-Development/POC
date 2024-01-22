<?php

namespace ContentEgg\application\modules\AffilinetCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * AffilinetCouponsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class AffilinetCouponsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'PublisherId'             => array(
				'title'       => 'Publisher ID  <span class="cegg_required">*</span>',
				'description' => __( 'Publisher ID - your login in affili.net.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "Publisher ID" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'service_password'        => array(
				'title'       => 'Publisher Webservice Password <span class="cegg_required">*</span>',
				'description' => __( 'Publisher Webservice access key. You can get it <a href="https://publisher.affili.net/Account/techSettingsPublisherWS.aspx">here</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "Publisher Webservice Password" can not be empty.', 'content-egg' ),
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
				),
				'section'     => 'default',
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for updates and autoblogging', 'content-egg' ),
				'description' => __( 'Number of results for automatic updates and autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 3,
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
			'ProgramId'               => array(
				'title'       => 'Program ID',
				'description' => 'Only vouchers of this program are returned.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'VoucherCodeContent'      => array(
				'title'            => 'Voucher code',
				'description'      => 'Vouchers can come with or without an actual voucher code. If they don’t have a voucher code, then the customer gets the benefit automatically. With this parameter, you can limit the results to only those vouchers, which have a voucher code, or those, which don’t have a voucher code.',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'0.' => 'Any',
					'1.' => 'Empty',
					'2.' => 'Filled',
				),
				'default'          => '0.',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'VoucherType'             => array(
				'title'            => 'Voucher type',
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'-1.' => 'Any',
					'0.'  => 'All products',
					'1.'  => 'Specific products',
					'2.'  => 'Multi buy discount',
					'3.'  => 'Free shipping',
					'4.'  => 'Free product',
					'5.'  => 'Competition',
				),
				'default'          => '0.',
				'section'          => 'default',
				'metaboxInit'      => true,
			),
			'MinimumOrderValue'       => array(
				'title'       => 'Minimum order value',
				'description' => 'Many vouchers can only be used on shopping baskets, which exceed a certain minimum value. When you set this parameter, only those vouchers are returned, which have a minimum order value configured and whose minimum order value don’t exceed this amount.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'CustomerRestriction'     => array(
				'title'            => 'Customer restriction',
				'description'      => 'Some vouchers can only be used by new customers. With this parameter you can restrict the results to either get only those vouchers, which all customers can use, or those vouchers, which only new customers can use.',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'0.' => 'No restrictions',
					'1.' => 'All customers',
					'2.' => 'Only new customers',
				),
				'default'          => '0.',
				'section'          => 'default',
			),
			'ExclusivesOnly'          => array(
				'title'       => 'Exclusives only',
				'description' => 'Restrict the returned vouchers to exclusives only.',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
				'section'     => 'default',
			),
			'OrderBy'                 => array(
				'title'            => 'Sort',
				'description'      => 'Specifies the logic that shall be applied to the list of shops which is specified in Shop IDs. If you choose "Exclude", then products are not returned, if they come from any of the shops specified in Shop IDs.',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'0.' => 'ID',
					'1.' => 'Program ID',
					'2.' => 'Title',
					'3.' => 'Last change date',
					'4.' => 'Start date',
					'5.' => 'End date',
				),
				'default'          => '0.',
				'section'          => 'default',
			),
			'SortDesc'                => array(
				'title'            => 'Sort order',
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'false' => 'Ascending',
					'true'  => 'Descending',
				),
				'default'          => 'true',
				'section'          => 'default',
			),
		);
		$parent  = parent::options();
		unset( $parent['featured_image'] );

		return array_merge( $parent, $optiosn );
	}

}
