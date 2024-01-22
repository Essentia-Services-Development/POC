<?php

namespace ContentEgg\application\libs\stopwords;

defined( '\ABSPATH' ) || exit;

/**
 * StopWords class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2011 keywordrush.com
 *
 */
class StopWords {

	private static $available_languages = array( 'en', 'ru', 'de', 'fr' );

	public function words( $lang ) {
		if ( ! self::isLangAvailable( $lang ) ) {
			throw new \Exception( "StopWords do not support '$lang' language." );
		}

		return require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'words' . DIRECTORY_SEPARATOR . $lang . '.php';
	}

	public static function isLangAvailable( $lang ) {
		if ( in_array( $lang, self::$available_languages ) ) {
			return true;
		} else {
			return false;
		}
	}

}
