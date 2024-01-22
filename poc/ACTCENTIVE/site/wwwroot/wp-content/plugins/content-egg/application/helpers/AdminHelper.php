<?php

namespace ContentEgg\application\helpers;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\components\ModuleManager;

/**
 * AdminHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 *
 */
class AdminHelper {

	public static function getCategoryList() {
		$taxonomy = array( 'category' );

		// @todo: widget is initialized before woo? taxonomy does not exist
		if ( in_array( 'product', GeneralConfig::getInstance()->option( 'post_types' ) ) && \taxonomy_exists( 'product_cat' ) ) {
			$taxonomy[] = 'product_cat';
		}

		$cat_args   = array( 'taxonomy' => $taxonomy, 'orderby' => 'name', 'order' => 'asc', 'hide_empty' => false );
		$categories = \get_terms( $cat_args );

		$results = array();
		foreach ( $categories as $key => $category ) {
			$results[ $category->term_id ] = $category->name;
			if ( $category->taxonomy == 'product_cat' ) {
				$results[ $category->term_id ] .= ' [product]';
			}
		}

		return $results;
	}

	/**
	 * Tabs as sections
	 */
	public static function doTabsSections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		echo '<div id="cegg-tabs">';
		echo '<ul>';
		$i = 1;
		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			echo '<li><a href="#tabs-' . esc_attr($i) . '">' . esc_html($section['title']) . '</a></li>';
			$i ++;
		}
		echo '</ul>';
		$i = 1;
		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			echo '<div id="tabs-' . esc_attr($i) . '">';
			echo '<table class="form-table" role="presentation">';
			\do_settings_fields( $page, $section['id'] );
			echo '</table>';
			echo '</div>';
			$i ++;
		}
		echo '</div>';
		echo '<script type="text/javascript">' . 'jQuery(document).ready(function($){$(\'#cegg-tabs\').tabs();});' . '</script>';
	}

	public static function getProductModules() {
		$modules = ModuleManager::getInstance()->getConfigurableModules();
		$results = array();
		foreach ( $modules as $module ) {
			if ( $module->isDeprecated() && ! $module->isActive() ) {
				continue;
			}

			if ( $module->isAffiliateParser() && $module->isProductParser() && ! $module->isAeParser() && ! $module->isFeedParser() ) {
				$results[] = $module;
			}
		}

		return $results;
	}

	public static function getAeProductModules() {
		$modules = ModuleManager::getInstance()->getConfigurableModules();
		$results = array();
		foreach ( $modules as $module ) {
			if ( $module->isDeprecated() && ! $module->isActive() ) {
				continue;
			}

			if ( $module->isAffiliateParser() && $module->isProductParser() && $module->isAeParser() ) {
				$results[] = $module;
			}
		}

		return $results;
	}

	public static function getFeedProductModules() {
		$modules = ModuleManager::getInstance()->getConfigurableModules();
		$results = array();
		foreach ( $modules as $module ) {
			if ( $module->isDeprecated() && ! $module->isActive() ) {
				continue;
			}

			if ( $module->isAffiliateParser() && $module->isProductParser() && $module->isFeedParser() ) {
				$results[] = $module;
			}
		}

		return $results;
	}

	public static function getCouponModules() {
		$modules = ModuleManager::getInstance()->getConfigurableModules();
		$results = array();
		foreach ( $modules as $module ) {
			if ( $module->isDeprecated() && ! $module->isActive() ) {
				continue;
			}

			if ( $module->isAffiliateParser() && $module->isCouponParser() ) {
				$results[] = $module;
			}
		}

		return $results;
	}

	public static function getContentModules() {
		$modules = ModuleManager::getInstance()->getConfigurableModules();
		$results = array();
		foreach ( $modules as $module ) {
			if ( $module->isDeprecated() && ! $module->isActive() ) {
				continue;
			}

			if ( ! $module->isAffiliateParser() ) {
				$results[] = $module;
			}
		}

		return $results;
	}

}
