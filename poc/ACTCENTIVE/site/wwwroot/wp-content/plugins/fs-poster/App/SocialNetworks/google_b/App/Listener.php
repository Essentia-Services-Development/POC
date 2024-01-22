<?php

namespace FSPoster\App\SocialNetworks\google_b\App;

use FSPoster\App\Libraries\google\GoogleMyBusiness;
use FSPoster\App\Libraries\google\GoogleMyBusinessAPI;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\v7\PostData;

class Listener
{
	/**
	 * @param $postData PostData
	 *
	 * @return array
	 */
	private static function appMethod ( $postData )
	{
		$thumbnail = $postData->getPostThumbnailURL();

		if ( ! empty( $thumbnail ) )
		{
			$postData->setImages( [ $thumbnail ] );
		}

		if ( $postData->sendType != 'image' && $postData->sendType != 'video' )
		{
			$pMethod = (int) Helper::getCustomSetting( 'posting_type', Helper::getOption( 'google_b_posting_type', '1' ), $postData->nodeType, $postData->node[ 'id' ] );

			if ( $pMethod === 2 )
			{
				$thumbnail = $postData->getPostThumbnailURL();

				if ( ! empty( $thumbnail ) )
				{
					$postData->setSendType( 'image' )
					         ->setImages( [ $thumbnail ] );
				}
			}
			else if ( $pMethod === 3 )
			{
				$postData->setImages( $postData->getPostGalleryURL() );

				if ( ! empty( $postData->images ) )
				{
					$postData->setSendType( 'image' );
				}
			}
			else if ( $pMethod === 4 )
			{
				$postData->setSendType( 'custom_message' );
			}
		}

		return GoogleMyBusinessAPI::sendPost( $postData->getAppId(), $postData->getAccoundId(), $postData->getNodeProfileId(), $postData->sendType, $postData->message, $postData->link, $postData->images, $postData->videoURL, $postData->accessToken, $postData->getProxy() );
	}

	/**
	 * @param $postData PostData
	 *
	 * @return array
	 */
	private static function cookieMethod ( $postData )
	{
		if ( $postData->sendType == 'video' )
		{
			return [
				'status'    => 'error',
				'error_msg' => fsp__( 'Google Business Profile doesn\'t support sharing videos by the cookie method!' )
			];
		}

		if ( $postData->sendType != 'image' )
		{
			$thumbURL = $postData->getPostThumbnail();

			if ( ! empty( $thumbURL ) )
			{
				$postData->setSendType( 'image' )
				         ->setImagesLocale( [ $thumbURL ] );
			}
		}

		$imageUrl = is_array( $postData->imagesLocale ) ? reset( $postData->imagesLocale ) : '';

		$options       = json_decode( $postData->getOptions(), TRUE );
		$cookieSID     = isset( $options[ 'sid' ] ) ? $options[ 'sid' ] : '';
		$cookieHSID    = isset( $options[ 'hsid' ] ) ? $options[ 'hsid' ] : '';
		$cookieSSID    = isset( $options[ 'ssid' ] ) ? $options[ 'ssid' ] : '';
		$cookieSAPISID = isset( $options[ 'sapisid' ] ) ? $options[ 'sapisid' ] : '';

		$linkButton = Helper::getOption( 'google_b_button_type', 'LEARN_MORE' );

		$gbShareAsProduct = ( $postData->postType == 'product' || $postData->postType == 'product_variation' ) && Helper::getOption( 'google_b_share_as_product', '0' ) && function_exists( 'wc_get_product' );

		$productName     = $gbShareAsProduct ? $postData->getPostTitle() : NULL;
		$productPrice    = $gbShareAsProduct ? (float) Helper::getProductPrice( $postData->post, 'price', FALSE ) : NULL;
		$productCurrency = $gbShareAsProduct ? get_woocommerce_currency() : NULL;
		$productCategory = NULL;

		if ( $gbShareAsProduct )
		{
			$productCategory = wp_get_post_terms( $postData->postId, 'product_cat' );

			if ( isset( $productCategory[ 0 ] ) )
			{
				$productCategory = $productCategory[ 0 ]->name;
			}
			else
			{
				$productCategory = fsp__( 'Product' );
			}
		}

		$google = new GoogleMyBusiness( $cookieSID, $cookieHSID, $cookieSSID, $cookieSAPISID, $postData->getProxy() );

		return $google->sendPost( $postData->getNodeProfileId(), self::getNodeData( $postData ), $gbShareAsProduct, $postData->message, $postData->link, $linkButton, $imageUrl, $productName, $productPrice, $productCurrency, $productCategory );
	}

	public static function getNodeData ( $postData )
	{
		$data = $postData->node[ 'info' ][ 'data' ];
		$data = json_decode( $data, ARRAY_A );

		return empty( $data ) ? [] : $data;
	}

	/**
	 * @param $result array
	 * @param $postData PostData
	 *
	 * @return array
	 * */
	public static function sharePost ( $result, $postData )
	{
		if ( empty( $postData->getOptions() ) )
		{
			return self::appMethod( $postData );
		}

		return self::cookieMethod( $postData );
	}

	/**
	 * @param $params array
	 *
	 * @return array
	 */
	public static function addApp ( $params )
	{
		$appId     = Request::postMust( 'app_id', 'string', fsp__( 'app_id field is empty!' ) );
		$appSecret = Request::postMust( 'app_secret', 'string', fsp__( 'app_secret field is empty!' ) );

		$check = DB::DB()->get_row( DB::DB()->prepare( 'select true from ' . DB::table( 'apps' ) . ' where app_id=%s and app_secret=%s', [
			$appId,
			$appSecret
		] ) );

		if ( $check )
		{
			Helper::response( FALSE, [ 'error_msg' => fsp__( 'The App has already been added.' ) ] );
		}

		return [
			'app_id'     => $appId,
			'app_secret' => $appSecret,
			'is_public'  => 0,
			'name'       => $appId
		];
	}
}