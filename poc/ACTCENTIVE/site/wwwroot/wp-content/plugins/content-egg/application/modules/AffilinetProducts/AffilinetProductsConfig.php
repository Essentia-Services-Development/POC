<?php

namespace ContentEgg\application\modules\AffilinetProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * AffilinetProductsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class AffilinetProductsConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'PublisherId'             => array(
				'title'       => 'Publisher ID  <span class="cegg_required">*</span>',
				'description' => 'Ваш Publisher ID. Вы используете его для логина в affili.net.',
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
				'title'       => 'Product Webservice Password <span class="cegg_required">*</span>',
				'description' => __( 'Access key for Product Webservice. You can get it <a href="https://publisher.affili.net/Account/techSettingsPublisherWS.aspx">here</a>.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "Product Webservice Password" can not be empty.', 'content-egg' ),
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
				'default'     => 6,
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
			'ShopIds'                 => array(
				'title'       => 'Shop IDs',
				'description' => 'A comma separated list of Shop IDs, lets you restrict the search results to only those of the specified shops.<br>Please note the difference between Shop ID and Program ID: each program (= advertiser) has one Program ID, but might have more than one Shop ID, e.g. if the program supplies its electronics products separately from its clothing products.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'ShopIdMode'              => array(
				'title'            => 'Shop ID mode',
				'description'      => 'Specifies the logic that shall be applied to the list of shops which is specified in Shop IDs. If you choose "Exclude", then products are not returned, if they come from any of the shops specified in Shop IDs.',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'Include' => 'Include',
					'Exclude' => 'Exclude',
				),
				'default'          => 'Include',
				'section'          => 'default',
			),
			'WithImageOnly'           => array(
				'title'       => 'With image only',
				'description' => 'Limit the search results to products, for which we successfully downloaded an image from the advertiser.',
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => true,
				'section'     => 'default',
			),
			'MinimumPrice'            => array(
				'title'       => 'Minimum price',
				'description' => 'Minimum price in the search results (not including shipping costs). Must be a decimal, 0 or greater. If not specified, ‘0‘ is assumed. Decimal separator must be ‘.’ (dot), thousand separators are not allowed.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'MaximumPrice'            => array(
				'title'       => 'Maximum price',
				'description' => 'Maximum price in the search results (not including shipping costs). Must be a decimal, 0 or greater. If not specified, then no upper price limit is applied. Decimal separator must be ‘.’ (dot), thousand separators are not allowed.',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'SortBy'                  => array(
				'title'            => 'Sort',
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'Score'        => 'Relevance',
					'Price'        => 'Price',
					'ProductName'  => 'ProductName',
					'LastImported' => 'Last Imported',
				),
				'default'          => 'Score',
				'section'          => 'default',
			),
			'SortOrder'               => array(
				'title'            => 'Sort order',
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'ascending'  => 'Ascending',
					'descending' => 'Descending',
				),
				'default'          => 'descending',
				'section'          => 'default',
			),
			'CategoryIds'             => array(
				'title'       => 'Category IDs',
				'description' => 'A comma separated list of the Ids of the categories, you wish to restrict the search on. Whether the specified Ids are to be interpreted as shop categories or as affilinet categories, must be specified with the parameter Use Affilinet Categories',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			'UseAffilinetCategories'  => array(
				'title'            => 'Use affilinet categories',
				'description'      => 'Here you can define, whether the Ids given in Category IDs are to be interpreted as affilinet category Ids or shop category Ids.',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'true'  => 'Affilinet categories',
					'false' => 'Shop categories',
				),
				'default'          => 'false',
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
