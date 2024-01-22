<?php

namespace ContentEgg\application\modules\Zanox;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * ZanoxConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ZanoxConfig extends AffiliateParserModuleConfig {

	public function options() {
		$optiosn = array(
			'connectid'               => array(
				'title'       => 'Connect ID <span class="cegg_required">*</span>',
				'description' => __( 'Special key for Zanox API, also this is your connection with the partner program.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'required' ),
						'when'    => 'is_active',
						'message' => __( 'The field "Connect ID" can not be empty.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'adspace'                 => array(
				'title'       => 'Ad Space ID',
				'description' => __( 'Return partnership links for this ad space', 'content-egg' ),
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
				'default'     => 10,
				'validator'   => array(
					'trim',
					'absint',
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 50,
						'message' => __( 'The field "Results" can not be more than 50.', 'content-egg' ),
					),
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
					array(
						'call'    => array( '\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to' ),
						'arg'     => 50,
						'message' => __( 'The field "Results" can not be more than 50.', 'content-egg' ),
					),
				),
				'section'     => 'default',
			),
			'searchtype'              => array(
				'title'            => __( 'Search type', 'content-egg' ),
				'description'      => __( '"Automatically" means, that searching type will be chosen depending on length of the search phrase.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''           => __( 'Automatically', 'content-egg' ),
					'phrase'     => __( 'Phrase', 'content-egg' ),
					'contextual' => __( 'Context', 'content-egg' ),
				),
				'default'          => '',
				'section'          => 'default',
			),
			'region'                  => array(
				'title'            => __( 'Region', 'content-egg' ),
				'description'      => __( 'Limit the search of goods by this region.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					''   => __( 'Any region', 'content-egg' ),
					'AD' => 'Andorra',
					'AE' => 'United Arab Emirates',
					'AG' => 'Antigua and Barbuda',
					'AR' => 'Argentina',
					'AT' => 'Austria',
					'AU' => 'Australia',
					'BE' => 'Belgium',
					'BG' => 'Bulgaria',
					'BH' => 'Bahrain',
					'BO' => 'Bolivia',
					'BR' => 'Brazil',
					'BZ' => 'Belize',
					'CA' => 'Canada',
					'CH' => 'Switzerland',
					'CL' => 'Chile',
					'CN' => 'China',
					'CO' => 'Colombia',
					'CR' => 'Costa Rica',
					'CU' => 'Cuba',
					'CY' => 'Cyprus',
					'CZ' => 'Czech Republic',
					'DE' => 'Germany',
					'DK' => 'Denmark',
					'DO' => 'Dominican Republic',
					'EC' => 'Ecuador',
					'EE' => 'Estonia',
					'EG' => 'Egypt',
					'ES' => 'Spain',
					'FI' => 'Finland',
					'FR' => 'France',
					'GB' => 'United Kingdom',
					'GF' => 'French Guiana',
					'GP' => 'Guadeloupe',
					'GR' => 'Greece',
					'GT' => 'Guatemala',
					'HK' => 'Hong Kong',
					'HN' => 'Honduras',
					'HR' => 'Croatia',
					'HU' => 'Hungary',
					'ID' => 'Indonesia',
					'IE' => 'Ireland',
					'IL' => 'Israel',
					'IN' => 'India',
					'IS' => 'Iceland',
					'IT' => 'Italy',
					'JP' => 'Japan',
					'KR' => 'Korea, Republic of',
					'KW' => 'Kuwait',
					'LI' => 'Liechtenstein',
					'LT' => 'Lithuania',
					'LU' => 'Luxembourg',
					'LV' => 'Latvia',
					'MC' => 'Monaco',
					'MD' => 'Moldova, Republic of',
					'MQ' => 'Martinique',
					'MT' => 'Malta',
					'MX' => 'Mexico',
					'MY' => 'Malaysia',
					'NI' => 'Nicaragua',
					'NL' => 'Netherlands',
					'NO' => 'Norway',
					'NZ' => 'New Zealand',
					'PA' => 'Panama',
					'PE' => 'Peru',
					'PF' => 'French Polynesia',
					'PH' => 'Philippines',
					'PL' => 'Poland',
					'PM' => 'Saint Pierre and Miquelon',
					'PT' => 'Portugal',
					'PY' => 'Paraguay',
					'QA' => 'Qatar',
					'RE' => 'Reunion Réunion',
					'RO' => 'Romania',
					'RU' => 'Russian Federation',
					'RW' => 'Rwanda',
					'SA' => 'Saudi Arabia',
					'SE' => 'Sweden',
					'SG' => 'Singapore',
					'SI' => 'Slovenia',
					'SK' => 'Slovakia',
					'SM' => 'San Marino',
					'SV' => 'El Salvador',
					'TF' => 'French Southern Territories',
					'TH' => 'Thailand',
					'TR' => 'Turkey',
					'TW' => 'Taiwan, Province of China',
					'UA' => 'Ukraine',
					'US' => 'United States',
					'UY' => 'Uruguay',
					'VA' => 'Holy See (Vatican City State)',
					'VC' => 'Saint Vincent and the Grenadines',
					'VE' => 'Venezuela, Bolivarian Republic of',
					'YT' => 'Mayotte',
					'ZA' => 'South Africa',
				),
				'default'          => '',
				'section'          => 'default',
			),
			'minprice'                => array(
				'title'       => __( 'Minimal price', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'maxprice'                => array(
				'title'       => __( 'Maximal price', 'content-egg' ),
				'description' => '',
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'metaboxInit' => true,
			),
			'programs'                => array(
				'title'       => __( 'Id of program', 'content-egg' ),
				'description' => __( 'Limit search results to the specific program. You can specify several ID through a comma. For example: "1234 ", "1234,5678,9123 ". If it isn\'t specified, search will be run for all your connected programs.', 'content-egg' ),
				'callback'    => array( $this, 'render_input' ),
				'default'     => '',
				'validator'   => array(
					'trim',
				),
				'section'     => 'default',
			),
			/*
			 * It does not work?..
			  'hasimages' => array(
			  'title' => __('Товары картинками', 'content-egg'),
			  'description' => __('Только товары с картинками.', 'content-egg'),
			  'callback' => array($this, 'render_checkbox'),
			  'default' => true,
			  'section' => 'default',
			  ),
			 *
			 */
			'partnership'             => array(
				'title'            => __( 'Partnership', 'content-egg' ),
				'description'      => __( 'If you choose "All ", partner links won\'t be included in result. Use only for an assessment of available products.', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array(
					'all'       => __( 'All', 'content-egg' ),
					'confirmed' => __( 'Approved', 'content-egg' ),
				),
				'default'          => 'confirmed',
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
