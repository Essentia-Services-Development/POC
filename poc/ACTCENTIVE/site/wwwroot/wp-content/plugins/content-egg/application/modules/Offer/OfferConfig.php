<?php

namespace ContentEgg\application\modules\Offer;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\helpers\CurrencyHelper;

/**
 * OfferConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class OfferConfig extends AffiliateParserModuleConfig {

	public function options() {
		$currencies = CurrencyHelper::getCurrenciesList();

		$options = array(
			'save_img'         => array(
				'title'       => __( 'Save images', 'content-egg' ),
				'description' => __( 'Save images on server', 'content-egg' ),
				'callback'    => array( $this, 'render_checkbox' ),
				'default'     => false,
			),
			'global'           => array(
				'title'       => __( 'Global settings', 'content-egg' ),
				'description' => __( 'Global settings by domain.', 'content-egg' ) . ' ' . sprintf( __( 'Read more <a target="_blank" href="%s">here</a>.', 'content-egg' ), 'https://www.keywordrush.com/docs/content-egg/OfferModule.html' ),
				'callback'    => array( $this, 'render_xpath_line_block' ),
				'default'     => array(),
				'validator'   => array(
					array(
						'call' => array( $this, 'xpathFormat' ),
						'type' => 'filter',
					),
				),
			),
			'default_currency' => array(
				'title'            => __( 'Default currency', 'content-egg' ),
				'callback'         => array( $this, 'render_dropdown' ),
				'dropdown_options' => array_combine( $currencies, $currencies ),
				'default'          => 'USD',
			),
		);
		$parent  = parent::options();
		unset( $parent['ttl'] );
		$parent['ttl_items']['default'] = 2592000;

		return array_merge( $parent, $options );
	}

	public function render_xpath_line( $args ) {
		if ( isset( $args['_field'] ) ) {
			$i = $args['_field'];
		} else {
			$i = 0;
		}
		if ( isset( $args['value'][ $i ]['domain'] ) ) {
			$domain = $args['value'][ $i ]['domain'];
		} else {
			$domain = '';
		}
		if ( isset( $args['value'][ $i ]['xpath'] ) ) {
			$xpath = $args['value'][ $i ]['xpath'];
		} else {
			$xpath = '';
		}
		if ( isset( $args['value'][ $i ]['deeplink'] ) ) {
			$deeplink = $args['value'][ $i ]['deeplink'];
		} else {
			$deeplink = '';
		}
		if ( isset( $args['value'][ $i ]['in_priority'] ) && (bool) $args['value'][ $i ]['in_priority'] ) {
			$checked = ' checked="checked" ';
		} else {
			$checked = '';
		}

		echo '<input name="' . \esc_attr( $args['option_name'] ) . '['
		     . \esc_attr( $args['name'] ) . '][' . esc_attr($i) . '][domain]" value="'
		     . \esc_attr( $domain ) . '" class="cegg_domain" type="text" placeholder="Domain name" />';
		echo '<input name="' . \esc_attr( $args['option_name'] ) . '['
		     . \esc_attr( $args['name'] ) . '][' . esc_attr($i) . '][xpath]" value="'
		     . \esc_attr( $xpath ) . '" class="cegg_xpath" type="text" placeholder="XPath to get product price" />';
		echo '<input name="' . \esc_attr( $args['option_name'] ) . '['
		     . \esc_attr( $args['name'] ) . '][' . esc_attr($i) . '][deeplink]" value="'
		     . \esc_attr( $deeplink ) . '" class="cegg_deeplink" type="text" placeholder="Deeplink" />';
		echo '&nbsp;<label for="in_priority' . esc_attr($i) . '">';
		echo '<input type="checkbox" name="' . \esc_attr( $args['option_name'] ) . '['
		     . \esc_attr( $args['name'] ) . '][' . esc_attr($i) . '][in_priority]" id="in_priority' . esc_attr($i) . '"';
		if ($checked) echo ' checked="checked" ';
		echo ' value="1" />';
		echo esc_html(__( 'override custom settings', 'content-egg' ));
		echo '</label>';
	}

	public function render_xpath_line_block( $args ) {
		$total = count( $args['value'] ) + 5;
		for ( $i = 0; $i < $total; $i ++ ) {
			echo '<div class="cegg_xpath_block_wrap" style="padding-bottom: 10px;">';
			$args['_field'] = $i;
			$this->render_xpath_line( $args );
			echo '</div>';
		}
		if ( $args['description'] ) {
			echo '<p class="description">' . esc_html($args['description']) . '</p>';
		}
	}

	public function xpathFormat( $values ) {
		$domains = array();
		foreach ( $values as $k => $value ) {
			$value['domain'] = trim( $value['domain'] );
			$value['domain'] = preg_replace( '/^www\./', '', $value['domain'] );
			if ( $d = TextHelper::getHostName( $value['domain'] ) ) {
				$value['domain'] = $d;
			}
			$values[ $k ]['domain'] = $value['domain'];
			if ( ! $value['domain'] || in_array( $value['domain'], $domains ) ) {
				unset( $values[ $k ] );
				continue;
			}
			$domains[] = $value['domain'];
		}
		$values = array_values( $values );

		return $values;
	}

}
