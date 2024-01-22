<?php

namespace ContentEgg\application\libs\flickr;

defined( '\ABSPATH' ) || exit;

/**
 * FlickrHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class FlickrHelper {

	static public function getFlickrUri( array $item, $size = 500 ) {
		if ( ! isset( $item['img_id'] ) ) {
			return '';
		}
		if ( ! isset( $item['secret'] ) ) {
			return '';
		}
		if ( ! isset( $item['server'] ) ) {
			return '';
		}
		if ( ! isset( $item['farm'] ) ) {
			return '';
		}

		return self::getImgUri( $item['img_id'], $item['secret'], $item['server'], $item['farm'], $size );
	}

	/**
	 * Строит URI картинки на flickr по известным параметрам
	 *
	 * @param string $id
	 * @param string $secret
	 * @param string $server
	 * @param string $farm
	 * @param string $size (square, thumbnail, small, default, medium, large)
	 *
	 * @link http://www.flickr.com/services/api/misc.urls.html
	 */
	static public function getImgUri( $id, $secret, $server, $farm, $size = 500 ) {
		$size          = (int) $size;
		$size_suffixes = array(
			75   => 's',
			150  => 'q',
			100  => 't',
			240  => 'm',
			320  => 'n',
			500  => '',
			640  => 'z',
			800  => 'c',
			1024 => 'b',
			1600 => 'h',
			2048 => 'k'
		);
		if ( array_key_exists( $size, $size_suffixes ) ) {
			$suffix = $size_suffixes[ $size ];
		} else {
			$suffix = '';
		}

		$uri = "https://farm{$farm}.static.flickr.com/{$server}/{$id}_{$secret}";
		if ( $suffix ) {
			$uri .= '_' . $suffix;
		}
		$uri .= '.jpg';

		return $uri;
	}

	static public function getImgLink( $owner, $id ) {
		return 'http://www.flickr.com/photos/' . $owner . '/' . $id;
	}

	static public function getProfileLink( $owner, $id ) {
		return 'http://www.flickr.com/people/' . $owner . '/';
	}

}
