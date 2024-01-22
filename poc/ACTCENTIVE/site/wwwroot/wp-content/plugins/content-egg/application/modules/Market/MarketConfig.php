<?php

namespace ContentEgg\application\modules\Market;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ParserModuleConfig;

/**
 * MarketConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class MarketConfig extends ParserModuleConfig {

	public function options() {
		$optiosn = array(
			'api_key'                 => array(
				'title'       => 'API Key <span class="cegg_required">*</span>',
				'description' => __( 'Access key to Yandex Market API. Send request to obtain is possible  <a href="http://feedback2.yandex.ru/api-market-content/key/">here<a/>.', 'content-egg' ),
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
			'geo_id'                  => array(
				'title'            => __( 'Region', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'225' => __( 'Russia', 'content-egg' ),
					'187' => __( 'Ukraine', 'content-egg' ),
					'159' => __( 'Kazakhstan', 'content-egg' ),
					'149' => __( 'Belarus', 'content-egg' ),
				),
				'default'          => '225',
				'section'          => 'default',
			),
			'entries_per_page'        => array(
				'title'       => __( 'Results', 'content-egg' ),
				'description' => __( 'Number of results for a single query', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 8,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 10,
						'message' => __( 'The "Results" can not be more than 10.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'entries_per_page_update' => array(
				'title'       => __( 'Results for autoblogging ', 'content-egg' ),
				'description' => __( 'Number of results for autoblogging.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => 1,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 10,
						'message' => __( 'Field "Results for autoblogging" can not be more than 10.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'get_offers'              => array(
				'title'       => __( 'Offers', 'content-egg' ),
				'description' => __( 'Get a list of the offers on the model', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => true,
				'section'     => 'default',
			),
			'offers_count'            => array(
				'title'       => __( 'Number of offers', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => 5,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 30,
						'message' => __( 'The "Number of offers" can not be more than 30.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'get_opinions'            => array(
				'title'       => __( 'Reviews', 'content-egg' ),
				'description' => __( 'Get review about the model.', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => true,
				'section'     => 'default',
			),
			'opinions_count'          => array(
				'title'       => __( 'Number of reviews', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => 5,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 30,
						'message' => __( 'The "Number of reviews" can not be more than 30.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'opinions_sort'           => array(
				'title'            => __( 'Sorting of reviews', 'content-egg' ),
				'description'      => '',
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'grade' => __( 'Sorting by user ratings models', 'content-egg' ),
					'date'  => __( 'Sorting by date of review', 'content-egg' ),
					'rank'  => __( 'Sorting by usefulness of review', 'content-egg' ),
				),
				'default'          => 'date',
				'section'          => 'default',
			),
			'opinions_size'           => array(
				'title'       => __( 'Cut reviews', 'content-egg' ),
				'description' => __( 'Size of reviews in characters (0 - do not cut)', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '300',
				'validator'   => array(
					'trim',
					'absint',
				),
				'section'     => 'default',
			),
			/*
			  'get_details' => array(
			  'title' => __('Description', 'content-egg'),
			  'description' => __('Получить характеристики модели.', 'content-egg'),
			  'callback' => array($this, 'render_checkbox'),
			  'default' => true,
			  'section' => 'default',
			  ),
			  'details_set' => array(
			  'title' => __('Тип характеристик', 'content-egg'),
			  'description' => '',
			  'callback' => array($this, 'render_dropdown'),
			  'dropdown_options' => array(
			  'all' => __('Все характеристики модели', 'content-egg'),
			  'main' => __('Только основные характеристики', 'content-egg'),
			  ),
			  'default' => 'all',
			  'section' => 'default',
			  ),
			 *
			 */
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
