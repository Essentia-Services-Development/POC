<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use ContentEgg\application\Plugin;
use ContentEgg\application\models\ProductModel;

/**
 * ReviewNotice class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ReviewNotice
{

	private static $instance = null;

	const MIN_PRODUCTS_TRIGGER = 100;
	const PRODUC_COUNT_TTL = 86400;

	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function adminInit()
	{
		\add_action('admin_notices', array($this, 'displayNotice'));
		$this->hideNotice();
	}

	public function displayNotice()
	{

		if (!isset($_SERVER['REQUEST_URI']))
		{
			return;
		}

		if (\get_transient('cegg_hide_notice_review_products_trigger'))
		{
			return;
		}

		$last_sync = ProductModel::model()->getLastSync();
		if (!$last_sync || time() - $last_sync > self::PRODUC_COUNT_TTL)
		{
			ProductModel::model()->maybeScanProducts();
		}

		$total = ProductModel::model()->count();
		if ($total < self::MIN_PRODUCTS_TRIGGER)
		{
			return;
		}

		$rate_url = 'https://wordpress.org/support/plugin/' . Plugin::getSlug() . '/reviews/?filter=5#new-post';
		$page_url = \get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-product');

		$hide_notice_uri = \add_query_arg(array(
			'cegg_hide_notice'   => 'review_products_trigger',
			'_cegg_notice_nonce' => \wp_create_nonce('hide_notice')
		), esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])));

		$this->addInlineCss();
		echo '<div class="notice notice-success egg-notice"><p>';
		echo '<img src=" ' . esc_url_raw(\ContentEgg\PLUGIN_RES) . '/img/logo.png' . '" width="40" />';
		echo '<b>' . 'Congrats, you have <a href="' . esc_url_raw($page_url) . '">' . esc_html($total) . ' products</a> added with ' . esc_html(Plugin::getName()) . '</b>';
		echo '<br>';
		echo ' ' . 'We would very much appreciate if you could quickly rate the plugin on WP.';
		echo ' ' . 'Just to help us spread the word and boost our motivation.';
		echo '<br><em> - ' . sprintf('Your %s team', 'Keywordrush'), '</em>';
		echo '<br>';
		echo sprintf('<a style="color:#00a32a;font-weight:bold;" target="_blank" href="%s">&#9733; %s</a>', esc_url_raw($rate_url), 'Give it a 5-star rating');
		echo ' | ';
		echo sprintf('<a href="%s">%s</a>', esc_url_raw($hide_notice_uri), '&#x2715 ' . 'Dismiss this notice');

		echo '</p></div>';
	}

	public function hideNotice()
	{
		if (!isset($_SERVER['REQUEST_URI']))
		{
			return;
		}

		if (!isset($_GET['cegg_hide_notice']))
		{
			return;
		}

		if (!isset($_GET['_cegg_notice_nonce']) || !\wp_verify_nonce(sanitize_key($_GET['_cegg_notice_nonce']), 'hide_notice'))
		{
			return;
		}

		$notice = \sanitize_text_field(wp_unslash($_GET['cegg_hide_notice']));

		if (!in_array($notice, array('review_products_trigger')))
		{
			return;
		}

		$expiration = 0;
		\set_transient('cegg_hide_notice_' . $notice, time(), $expiration);

		\wp_safe_redirect(\remove_query_arg(array(
			'cegg_hide_notice',
			'_cegg_notice_nonce'
		), esc_url_raw(\wp_unslash($_SERVER['REQUEST_URI']))));
		exit;
	}

	public function addInlineCss()
	{
		echo '<style>.egg-notice a.egg-notice-close {position:static;float:right;top:0;right0;padding:0;margin-top:-20px;line-height:1.23076923;text-decoration:none;}.egg-notice a.egg-notice-close::before{position: relative;top: 18px;left: -20px;}.egg-notice img {float:left;width:40px;padding-right:12px;}</style>';
	}
}
