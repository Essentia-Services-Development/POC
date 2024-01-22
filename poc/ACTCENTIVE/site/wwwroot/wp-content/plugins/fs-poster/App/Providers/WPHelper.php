<?php

namespace FSPoster\App\Providers;

use WC_Product_Variation;

trait WPHelper
{
	private static $originalBlogId;

	public static function setBlogId ( $new_blog_id )
	{
		if ( ! is_multisite() )
		{
			return;
		}

		if ( is_null( self::$originalBlogId ) )
		{
			self::$originalBlogId = self::getBlogId();
		}

		switch_to_blog( $new_blog_id );
	}

	public static function getBlogId ()
	{
		return get_current_blog_id();
	}

	public static function resetBlogId ()
	{
		if ( ! is_multisite() )
		{
			return;
		}

		if ( ! is_null( self::$originalBlogId ) )
		{
			switch_to_blog( self::$originalBlogId );
		}
	}

	public static function getBlogs ()
	{
		if ( ! is_multisite() )
		{
			return [ 1 ];
		}

		$sites   = get_sites();
		$siteIDs = [];

		foreach ( $sites as $site )
		{
			$siteIDs[] = $site->blog_id;
		}

		return $siteIDs;
	}

	/**
	 * @param $productInf
	 * @param string $getType
	 *
	 * @return array|string
	 */
	public static function getProductPrice ( $productInf, $getType = '', $format = TRUE )
	{
		$productRegularPrice = '';
		$productSalePrice    = '';
		$productId           = $productInf[ 'post_type' ] === 'product_variation' ? $productInf[ 'post_parent' ] : $productInf[ 'ID' ];

		if ( ( $productInf[ 'post_type' ] === 'product' || $productInf[ 'post_type' ] === 'product_variation' ) && function_exists( 'wc_get_product' ) )
		{
			$product = wc_get_product( $productId );

			if ( $product->is_type( 'variable' ) )
			{
				$variations = wc_products_array_orderby(
					$product->get_available_variations( 'objects' ),
					'price',
					'asc'
				);

				$variations_in_stock = [];

				foreach ( $variations as $variation )
				{
					if ( $variation->is_in_stock() )
					{
						$variations_in_stock[] = $variation;
					}
				}

				if ( empty( $variations_in_stock ) )
				{
					$variable_product = empty( $variations ) ? $product : $variations[ 0 ];
				}
				else
				{
					$variable_product = $variations_in_stock[ 0 ];
				}

				$productRegularPrice = $variable_product->get_regular_price();
				$productSalePrice    = $variable_product->get_sale_price();
			}
			else //else if ( $product->is_type( 'simple' ) )
			{
				$productRegularPrice = $product->get_regular_price();
				$productSalePrice    = $product->get_sale_price();
			}
		}

		if ( empty( $productRegularPrice ) && $productSalePrice > $productRegularPrice )
		{
			$productRegularPrice = $productSalePrice;
		}

		$productRegularPrice = self::formatProductPrice( $productRegularPrice, $format );
		$productSalePrice    = self::formatProductPrice( $productSalePrice, $format );

		if ( $getType === 'price' )
		{
			return empty( $productSalePrice ) ? $productRegularPrice : $productSalePrice;
		}
		else if ( $getType === 'regular' )
		{
			return $productRegularPrice;
		}
		else if ( $getType === 'sale' )
		{
			return $productSalePrice;
		}
		else
		{
			return [
				'regular' => $productRegularPrice,
				'sale'    => $productSalePrice
			];
		}
	}

	public static function formatProductPrice ( $price, $format )
	{
		if ( $format === FALSE || $price === '' || is_null( $price ) || ! function_exists( 'wc_get_price_decimal_separator' ) || ! function_exists( 'wc_get_price_thousand_separator' ) || ! function_exists( 'wc_get_price_decimals' ) )
		{
			return $price;
		}

		return number_format( (float) $price, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );
	}

	public static function getPostTerms ( $postInf, $tax_name = NULL, $addSharp = TRUE, $asArray = TRUE, $separator = ' ' )
	{
		if ( empty( $tax_name ) )
		{
			$allowed_taxes = Helper::getOption( 'hashtag_taxonomies' );
			$allowed_taxes = empty( $allowed_taxes ) ? [] : explode( '|', $allowed_taxes );
		}
		else
		{
			$allowed_taxes = [ $tax_name ];
		}

		$post_taxes = get_post_taxonomies( $postInf[ 'ID' ] );

		$taxes = [];

		if ( empty( $allowed_taxes ) )
		{
			$taxes = $post_taxes;
		}
		else
		{
			foreach ( $post_taxes as $post_tax )
			{
				if ( in_array( $post_tax, $allowed_taxes ) )
				{
					$taxes[] = $post_tax;
				}
			}
		}

		$terms = [];

		foreach ( $taxes as $tax )
		{
			if ( ( get_post_type( $postInf[ 'ID' ] ) === 'product' || get_post_type( $postInf[ 'ID' ] ) === 'product_variation' ) && function_exists( 'wc_get_product' ) && in_array( $tax, [
					'product_tag',
					'product_cat'
				] ) )
			{
				if ( get_post_type( $postInf[ 'ID' ] ) === 'product' )
				{
					$tax_terms = wp_get_post_terms( $postInf[ 'ID' ], $tax, [ 'fields' => 'names' ] );
				}
				else
				{
					$tax_terms = wp_get_post_terms( $postInf[ 'post_parent' ], 'product_tag' );
				}
			}
			else
			{
				$tax_terms = wp_get_post_terms( $postInf[ 'ID' ], $tax, [ 'fields' => 'names' ] );
			}

			if ( is_array( $tax_terms ) )
			{
				$terms = array_merge( $terms, $tax_terms );
			}
		}

		$replaceWhitespaces = Helper::getOption( 'replace_whitespaces_with_underscore', '0' ) == 1 ? '_' : '';
		$terms_arr          = [];

		foreach ( $terms as $termInf )
		{
			$formatted_tag = htmlspecialchars_decode( $termInf );
			$formatted_tag = preg_replace( [ '/\s+/', '/&+/', '/-+/' ], $replaceWhitespaces, $formatted_tag );
			$formatted_tag = preg_replace( '/[!@#\$%^*()=+{}\[\]\'\",>\/?;:]/', '', $formatted_tag );
			$formatted_tag = preg_replace( '/_+/', '_', $formatted_tag );

			$uppercase = Helper::getOption( 'uppercase_hashtags', '0' ) == '1';

			if ( $uppercase )
			{
				$formatted_tag = mb_strtoupper( $formatted_tag );
			}

			$sharp       = $addSharp ? '#' : '';
			$terms_arr[] = $sharp . trim( $formatted_tag, ' _' );
		}

		if ( $asArray )
		{
			return $terms_arr;
		}
		else
		{
			return implode( $separator, $terms_arr );
		}
	}

	/**
	 * @param $postInf
	 * @param $feedId
	 * @param $account_info
	 *
	 * @return string
	 */
	public static function getPostLink ( $postInf, $feedId, $account_info )
	{
		if ( Helper::getCustomSetting( 'share_custom_url', '0', $account_info[ 'node_type' ], $account_info[ 'id' ] ) )
		{
			$link = Helper::getCustomSetting( 'custom_url_to_share', '{site_url}/?feed_id={feed_id}', $account_info[ 'node_type' ], $account_info[ 'id' ] );

			$post_id   = isset( $postInf[ 'ID' ] ) ? $postInf[ 'ID' ] : 0;
			$postTitle = isset( $postInf[ 'post_title' ] ) ? $postInf[ 'post_title' ] : '';
			$post_name = isset( $postInf[ 'post_name' ] ) ? $postInf[ 'post_name' ] : '';
			$network   = isset( $account_info[ 'driver' ] ) ? $account_info[ 'driver' ] : '-';

			$networks = [
				'fb'                => [ 'FB', 'Facebook' ],
				'instagram'         => [ 'IG', 'Instagram' ],
				'twitter'           => [ 'TW', 'Twitter' ],
				'planly'            => [ 'PY', 'Planly' ],
				'linkedin'          => [ 'LN', 'LinkedIn' ],
				'pinterest'         => [ 'PI', 'Pinterest' ],
				'telegram'          => [ 'TG', 'Telegram' ],
				'reddit'            => [ 'RE', 'Reddit' ],
				'youtube_community' => [ 'YC', 'Youtube Community' ],
				'tumblr'            => [ 'TU', 'Tumblr' ],
				'ok'                => [ 'OK', 'Odnoklassniki' ],
				'vk'                => [ 'VK', 'VKontakte' ],
				'google_b'          => [ 'GB', 'Google Business Profile' ],
				'medium'            => [ 'ME', 'Medium' ],
				'wordpress'         => [ 'WP', 'WordPress' ],
				'webhook'           => [ 'WH', 'Webhook' ],
				'plurk'             => [ 'PL', 'Plurk' ],
				'xing'              => [ 'XI', 'Xing' ],
				'discord'           => [ 'DC', 'Discord' ],
				'mastodon'          => [ 'MS', 'Mastodon' ],
			];

			$networkCode = isset( $networks[ $network ] ) ? $networks[ $network ][ 0 ] : '';
			$networkName = isset( $networks[ $network ] ) ? $networks[ $network ][ 1 ] : '';

			$userInf     = get_userdata( $postInf[ 'post_author' ] );
			$accountName = isset( $userInf->user_login ) ? $userInf->user_login : '-';

			$link = str_replace( [
				'{post_id}',
				'{feed_id}',
				'{post_title}',
				'{post_name}',
				'{post_slug}',
				'{network_name}',
				'{network_code}',
				'{account_name}',
				'{site_name}',
				'{uniq_id}',
				'{site_url}',
				'{site_url_encoded}',
				'{post_url}',
				'{post_url_encoded}',
			], [
				rawurlencode( $post_id ),
				rawurlencode( $feedId ),
				rawurlencode( $postTitle ),
				rawurlencode( $post_name ),
				rawurlencode( $post_name ),
				rawurlencode( $networkName ),
				rawurlencode( $networkCode ),
				rawurlencode( $accountName ),
				rawurlencode( get_bloginfo( 'name' ) ),
				uniqid(),
				site_url(),
				rawurlencode( site_url() ),
				get_permalink( $postInf[ 'ID' ] ),
				rawurlencode( get_permalink( $postInf[ 'ID' ] ) )
			], $link );

			// custom fields
			$link = preg_replace_callback( '/\{cf_(.*?)_raw\}/iU', function ( $n ) use ( $postInf ) {
				$customField = isset( $n[ 1 ] ) ? $n[ 1 ] : '';

				return get_post_meta( $postInf[ 'ID' ], $customField, TRUE );
			}, $link );

			$link = preg_replace_callback( '/\{cf_(.*?)\}/iU', function ( $n ) use ( $postInf ) {
				$customField = isset( $n[ 1 ] ) ? $n[ 1 ] : '';

				return rawurlencode( get_post_meta( $postInf[ 'ID' ], $customField, TRUE ) );
			}, $link );
		}
		else
		{
			$link = get_permalink( $postInf[ 'ID' ] );
			$link = Helper::customizePostLink( $link, $feedId, $postInf, $account_info );
		}

		return $link;
	}

	/**
	 * @param $link
	 * @param $feedId
	 * @param array $postInf
	 * @param array $account_info
	 *
	 * @return string
	 */
	public static function customizePostLink ( $link, $feedId, $postInf = [], $account_info = [] )
	{
		$parameters = [];

		if ( Helper::getOption( 'collect_statistics', '1' ) )
		{
			$parameters[] = 'feed_id=' . $feedId;
		}

		if ( Helper::getCustomSetting( 'unique_link', '1', $account_info[ 'node_type' ], $account_info[ 'id' ] ) == 1 )
		{
			$parameters[] = '_unique_id=' . uniqid();
		}

		$fs_url_additional = Helper::getCustomSetting( 'url_additional', '', $account_info[ 'node_type' ], $account_info[ 'id' ] );
		if ( ! empty( $fs_url_additional ) )
		{
			$post_id   = isset( $postInf[ 'ID' ] ) ? $postInf[ 'ID' ] : 0;
			$postTitle = isset( $postInf[ 'post_title' ] ) ? $postInf[ 'post_title' ] : '';
			$network   = isset( $account_info[ 'driver' ] ) ? $account_info[ 'driver' ] : '-';

			$networks = [
				'fb'                => [ 'FB', 'Facebook' ],
				'instagram'         => [ 'IG', 'Instagram' ],
				'twitter'           => [ 'TW', 'Twitter' ],
				'planly'            => [ 'PY', 'Planly' ],
				'linkedin'          => [ 'LN', 'LinkedIn' ],
				'pinterest'         => [ 'PI', 'Pinterest' ],
				'telegram'          => [ 'TG', 'Telegram' ],
				'reddit'            => [ 'RE', 'Reddit' ],
				'youtube_community' => [ 'YC', 'Youtube Community' ],
				'tumblr'            => [ 'TU', 'Tumblr' ],
				'ok'                => [ 'OK', 'Odnoklassniki' ],
				'vk'                => [ 'VK', 'VKontakte' ],
				'google_b'          => [ 'GB', 'Google Business Profile' ],
				'medium'            => [ 'ME', 'Medium' ],
				'wordpress'         => [ 'WP', 'WordPress' ],
				'webhook'           => [ 'WH', 'Webhook' ],
				'plurk'             => [ 'PL', 'Plurk' ],
				'xing'              => [ 'XI', 'Xing' ],
				'discord'           => [ 'DC', 'Discord' ],
				'mastodon'          => [ 'MS', 'Mastodon' ],
			];

			$networkCode = isset( $networks[ $network ] ) ? $networks[ $network ][ 0 ] : '';
			$networkName = isset( $networks[ $network ] ) ? $networks[ $network ][ 1 ] : '';

			$userInf     = get_userdata( $postInf[ 'post_author' ] );
			$accountName = isset( $userInf->user_login ) ? $userInf->user_login : '-';

			$fs_url_additional = str_replace( [
				'{post_id}',
				'{post_title}',
				'{network_name}',
				'{network_code}',
				'{account_name}',
				'{site_name}',
				'{uniq_id}'
			], [
				rawurlencode( $post_id ),
				rawurlencode( $postTitle ),
				rawurlencode( $networkName ),
				rawurlencode( $networkCode ),
				rawurlencode( $accountName ),
				rawurlencode( get_bloginfo( 'name' ) ),
				uniqid()
			], $fs_url_additional );

			$parameters[] = $fs_url_additional;
		}

		if ( ! empty( $parameters ) )
		{
			$link .= strpos( $link, '?' ) !== FALSE ? '&' : '?';

			$parameters = implode( '&', $parameters );

			$link .= $parameters;
		}

		return $link;
	}
}
