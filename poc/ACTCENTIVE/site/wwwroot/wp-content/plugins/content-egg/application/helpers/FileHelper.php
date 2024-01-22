<?php

namespace ContentEgg\application\helpers;

defined( '\ABSPATH' ) || exit;

/**
 * TextHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class FileHelper {

	public static function array2Csv( array $array ) {
		if ( ob_get_length() > 0 ) {
			ob_clean();
		}

		if ( ! $array ) {
			return '';
		}
		ob_start();
		$df = fopen( "php://output", 'w' );
		fputcsv( $df, array_keys( reset( $array ) ) );
		foreach ( $array as $row ) {
			fputcsv( $df, $row );
		}
		fclose( $df );

		return ob_get_clean();
	}

	public static function sendDownloadHeaders( $filename ) {
		$now = gmdate( "D, d M Y H:i:s" );
		header( "Expires: Tue, 03 Jul 2001 06:00:00 GMT" );
		header( "Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate" );
		header( "Last-Modified: {$now} GMT" );

		header( "Content-Type: application/force-download" );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Type: application/download" );

		header( "Content-Disposition: attachment;filename={$filename}" );
		header( "Content-Transfer-Encoding: binary" );
	}

}
